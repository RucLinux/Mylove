<?php
/**
 * Page template.
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<?php while (have_posts()) : the_post(); ?>
    <article <?php post_class('panel single-post single-page'); ?>>
        <header class="single-header">
            <h1 class="entry-title"><?php the_title(); ?></h1>
            <p class="entry-meta">
                <span><?php echo esc_html(get_the_date('Y 年 m 月 d 日')); ?></span>
                <span>最后修改：<?php echo esc_html(get_the_modified_date('Y 年 m 月 d 日')); ?></span>
            </p>
        </header>
        <div class="entry-content">
            <?php xghome_classic_render_post_content(get_the_ID()); ?>
        </div>
    </article>

    <?php
    if (comments_open() || get_comments_number()) {
        comments_template();
    }
    ?>
<?php endwhile; ?>

<?php
get_footer();
