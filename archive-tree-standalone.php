<?php
/**
 * 虚拟归档页（无需后台新建「archives」页面）。由重写规则或 404 兜底加载。
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<article class="panel single-post single-page">
    <header class="single-header">
        <h1 class="entry-title"><?php esc_html_e('文章归档', 'xghome-classic'); ?></h1>
        <p class="entry-meta">
            <?php
            printf(
                /* translators: %s: post count */
                esc_html__('共 %s 篇文章，按年、月分组。', 'xghome-classic'),
                esc_html((string) wp_count_posts()->publish)
            );
            ?>
        </p>
    </header>

    <?php get_template_part('template-parts/archives-tree', 'body'); ?>
</article>

<?php
get_footer();
