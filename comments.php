<?php
/**
 * Comments template.
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}

if (post_password_required()) {
    return;
}
?>

<section id="comments" class="panel comments-wrap">
    <?php $comment_regex = xghome_classic_get_comment_regex(); ?>
    <input type="hidden" id="xghome_comment_email_regex" value="<?php echo esc_attr($comment_regex['email']); ?>">
    <input type="hidden" id="xghome_comment_phone_regex" value="<?php echo esc_attr($comment_regex['phone']); ?>">
    <h3 class="comments-title">
        <?php
        printf(
            esc_html(_n('1 条评论', '%s 条评论', get_comments_number(), 'xghome-classic')),
            esc_html(number_format_i18n(get_comments_number()))
        );
        ?>
    </h3>

    <?php if (have_comments()) : ?>
        <ol class="comment-list">
            <?php
            wp_list_comments([
                'style'      => 'ol',
                'short_ping' => true,
                'avatar_size'=> 40,
            ]);
            ?>
        </ol>
        <?php the_comments_pagination(); ?>
    <?php endif; ?>

    <div class="emoji-collapse-block">
        <div class="single-block-head">
            <h3>表情面板</h3>
            <button type="button" class="single-block-toggle js-single-collapse-toggle" data-target="#commentEmojiBody" aria-expanded="false" aria-label="展开表情面板">⏬</button>
        </div>
        <div class="single-block-body is-collapsed" id="commentEmojiBody">
            <?php xghome_classic_render_emoji_picker('#comment'); ?>
        </div>
    </div>
    <p class="comments-form-tip">您的邮箱地址和手机号码不会被公开。 必填项已用 <span class="required">*</span> 标注</p>

    <?php
    comment_form([
        'comment_notes_before' => '',
        'comment_notes_after'  => '',
        'title_reply'          => '发表评论',
        'class_form'           => 'comment-form js-comment-validate-form',
    ]);
    ?>
</section>
