<?php
/**
 * Archives page template.
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
            <p class="entry-meta">共 <?php echo esc_html((string) wp_count_posts()->publish); ?> 篇文章，按年、月分组。</p>
        </header>

        <?php get_template_part('template-parts/archives-tree', 'body'); ?>
    </article>
<?php endwhile; ?>

<?php
get_footer();
