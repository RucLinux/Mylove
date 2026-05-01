<?php
/**
 * 虚拟相册页（不依赖是否已建「image」分类）。中间栏九宫格 + 可选该分类文章列表。
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}

$paged = max(1, (int) get_query_var('paged'));
$cat_term = get_term_by('slug', 'image', 'category');

get_header();
?>

<header class="panel page-header-block">
    <h1 class="page-title"><?php esc_html_e('相册', 'xghome-classic'); ?></h1>
    <p class="muted"><?php esc_html_e('随机展示媒体库图片；下方为该分类下的文章（若已创建别名 image 的分类）。', 'xghome-classic'); ?></p>
</header>

<?php get_template_part('template-parts/album', 'moments'); ?>

<?php
if ($cat_term && !is_wp_error($cat_term)) {
    $album_q = new WP_Query([
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'cat'                 => (int) $cat_term->term_id,
        'posts_per_page'      => (int) get_option('posts_per_page'),
        'paged'               => $paged,
        'ignore_sticky_posts' => true,
    ]);
} else {
    $album_q = null;
}
?>

<?php if ($album_q && $album_q->have_posts()) : ?>
    <div class="post-list">
        <?php
        while ($album_q->have_posts()) :
            $album_q->the_post();
            ?>
            <article <?php post_class('panel post-card'); ?>>
                <a class="thumb-wrap" href="<?php the_permalink(); ?>">
                    <?php
                    $list_thumb = xghome_classic_get_list_thumbnail_url(get_the_ID(), 'medium');
                    if ($list_thumb !== '') :
                        ?>
                        <img src="<?php echo esc_url($list_thumb); ?>" class="img-responsive" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
                    <?php else : ?>
                        <?php $default_thumb = xghome_classic_default_thumbnail_url(); ?>
                        <?php if ($default_thumb !== '') : ?>
                            <img src="<?php echo esc_url($default_thumb); ?>" class="img-responsive" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
                        <?php else : ?>
                            <div class="thumb-placeholder">No Image</div>
                        <?php endif; ?>
                    <?php endif; ?>
                </a>
                <div class="post-content">
                    <h2 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <?php if (post_password_required()) : ?>
                        <p class="entry-excerpt protected-note">此内容被密码保护</p>
                    <?php else : ?>
                        <p class="entry-excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
                    <?php endif; ?>
                    <p class="entry-meta"><?php echo wp_kses_post(xghome_classic_meta_line()); ?></p>
                </div>
            </article>
            <?php
        endwhile;
        wp_reset_postdata();
        ?>
    </div>
    <?php if ($album_q->max_num_pages > 1) : ?>
        <nav class="panel pagination-wrap" aria-label="<?php esc_attr_e('分页', 'xghome-classic'); ?>">
            <?php
            echo paginate_links(
                [
                    'total'     => $album_q->max_num_pages,
                    'current'   => $paged,
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'type'      => 'list',
                    'base'      => trailingslashit(home_url('/album')) . 'page/%#%/',
                    'format'    => '',
                ]
            );
            ?>
        </nav>
    <?php endif; ?>
<?php elseif ($cat_term && !is_wp_error($cat_term) && $album_q && !$album_q->have_posts()) : ?>
    <?php get_template_part('template-parts/content', 'none'); ?>
<?php endif; ?>

<?php
get_footer();
