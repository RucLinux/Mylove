<?php
/**
 * 评论列表 Walker：正文下展示日期+IP 属地+回复；子回复默认折叠。
 *
 * @package xghome-classic
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 当前文章下各评论的直接子评论数量（用于「展开 N 条回复」）。
 *
 * @return array<int, int> comment_ID => count
 */
function xghome_classic_get_comment_reply_counts(int $post_id): array
{
    if ($post_id <= 0) {
        return [];
    }
    $comments = get_comments([
        'post_id' => $post_id,
        'status'  => 'approve',
        'orderby' => 'comment_date_gmt',
        'order'   => 'ASC',
        'fields'  => 'all',
    ]);
    if (!is_array($comments)) {
        return [];
    }
    $counts = [];
    foreach ($comments as $c) {
        if (!($c instanceof \WP_Comment)) {
            continue;
        }
        $p = (int) $c->comment_parent;
        if ($p > 0) {
            $counts[$p] = ($counts[$p] ?? 0) + 1;
        }
    }

    return $counts;
}

/**
 * 列表日期：当年 m-d，否则 Y-m-d。
 */
function xghome_classic_comment_list_date(\WP_Comment $comment): string
{
    $y = (int) get_comment_date('Y', $comment);
    $cy = (int) current_time('Y');
    if ($y === $cy) {
        return get_comment_date('m-d', $comment);
    }

    return get_comment_date('Y-m-d', $comment);
}

/**
 * @phpstan-type Args array{style?:string, avatar_size?:int, max_depth?:int, reply_counts?:array<int,int>}
 */
class Xghome_Classic_Walker_Comment extends Walker_Comment
{
    /**
     * @var list<int>
     */
    protected $xghome_parent_stack = [];

    /**
     * @var list<bool>
     */
    protected $xghome_reply_wrap_stack = [];

    public function start_lvl(&$output, $depth = 0, $args = [])
    {
        $GLOBALS['comment_depth'] = $depth + 1;

        $parent_id = 0;
        if ($this->xghome_parent_stack !== []) {
            $parent_id = (int) array_pop($this->xghome_parent_stack);
        }

        $reply_counts = isset($args['reply_counts']) && is_array($args['reply_counts']) ? $args['reply_counts'] : [];
        $n = ($parent_id > 0 && isset($reply_counts[$parent_id])) ? (int) $reply_counts[$parent_id] : 0;

        $wrapped = false;
        $style = (string) ($args['style'] ?? 'ol');
        if ($style === 'ol' && $n > 0) {
            $wrapped = true;
            $expand = sprintf(
                /* translators: %d: number of replies */
                __('展开 %d 条回复', 'xghome-classic'),
                $n
            );
            $collapse = __('收起回复', 'xghome-classic');
            $output .= '<div class="xghome-comment-replies-wrap">';
            $output .= '<a href="#" class="xghome-comment-replies-toggle" role="button" aria-expanded="false"';
            $output .= ' data-label-expand="' . esc_attr($expand) . '"';
            $output .= ' data-label-collapse="' . esc_attr($collapse) . '">';
            $output .= esc_html($expand);
            $output .= '</a>';
            $output .= '<div class="xghome-comment-replies-panel" hidden>';
        }
        $this->xghome_reply_wrap_stack[] = $wrapped;

        switch ($style) {
            case 'div':
                break;
            case 'ol':
                $output .= '<ol class="children">' . "\n";
                break;
            case 'ul':
            default:
                $output .= '<ul class="children">' . "\n";
                break;
        }
    }

    public function end_lvl(&$output, $depth = 0, $args = [])
    {
        $GLOBALS['comment_depth'] = $depth + 1;
        $style = (string) ($args['style'] ?? 'ol');
        switch ($style) {
            case 'div':
                break;
            case 'ol':
                $output .= "</ol>\n";
                break;
            case 'ul':
            default:
                $output .= "</ul>\n";
                break;
        }
        if ($this->xghome_reply_wrap_stack !== []) {
            $wrapped = (bool) array_pop($this->xghome_reply_wrap_stack);
            if ($wrapped) {
                $output .= '</div></div>';
            }
        }
    }

    protected function html5_comment($comment, $depth, $args)
    {
        $tag = ($args['style'] === 'div') ? 'div' : 'li';

        $commenter = wp_get_current_commenter();
        $show_pending_links = !empty($commenter['comment_author']);

        if ($commenter['comment_author_email']) {
            $moderation_note = __('Your comment is awaiting moderation.');
        } else {
            $moderation_note = __('Your comment is awaiting moderation. This is a preview; your comment will be visible after it has been approved.');
        }
        ?>
        <<?php echo $tag; ?> id="comment-<?php comment_ID(); ?>" <?php comment_class($this->has_children ? 'parent' : '', $comment); ?>>
            <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
                <footer class="comment-meta">
                    <div class="comment-author vcard">
                        <?php
                        if (0 !== (int) $args['avatar_size']) {
                            echo get_avatar($comment, (int) $args['avatar_size']);
                        }
                        if ('0' === $comment->comment_approved && !$show_pending_links) {
                            $comment_author = '<span class="fn">' . esc_html(get_comment_author($comment)) . '</span>';
                        } else {
                            $comment_author = sprintf('<b class="fn">%s</b>', get_comment_author_link($comment));
                        }
                        echo $comment_author; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — link HTML from core
                        ?>
                    </div>
                    <?php if ('0' === $comment->comment_approved) : ?>
                        <em class="comment-awaiting-moderation"><?php echo esc_html($moderation_note); ?></em>
                    <?php endif; ?>
                </footer>

                <div class="comment-content">
                    <?php comment_text(); ?>
                </div>

                <div class="xghome-comment-footer-meta">
                    <time class="xghome-comment-date" datetime="<?php echo esc_attr(get_comment_time('c', true, true, $comment)); ?>">
                        <?php echo esc_html(xghome_classic_comment_list_date($comment)); ?>
                    </time>
                    <?php
                    $geo = function_exists('xghome_ip_geo_comment_footer_label')
                        ? xghome_ip_geo_comment_footer_label($comment)
                        : '';
                    if ($geo !== '') :
                        ?>
                        <span class="xghome-comment-geo"><?php echo esc_html($geo); ?></span>
                    <?php endif; ?>
                    <?php
                    if ('1' === $comment->comment_approved || $show_pending_links) {
                        comment_reply_link(array_merge($args, [
                            'add_below' => 'div-comment',
                            'depth'     => $depth,
                            'max_depth' => $args['max_depth'],
                            'before'    => '<span class="xghome-comment-reply">',
                            'after'     => '</span>',
                        ]));
                    }
                    ?>
                    <?php if (current_user_can('edit_comment', $comment->comment_ID)) : ?>
                        <span class="xghome-comment-edit"><?php edit_comment_link(__('编辑', 'xghome-classic')); ?></span>
                    <?php endif; ?>
                </div>
            </article>
        <?php
        if ($this->has_children) {
            $this->xghome_parent_stack[] = (int) $comment->comment_ID;
        }
    }
}
