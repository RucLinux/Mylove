<?php
/**
 * Search template.
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<header class="panel page-header-block">
    <h1 class="page-title">
        <?php
        printf(
            esc_html__('搜索结果：%s', 'xghome-classic'),
            esc_html(get_search_query())
        );
        ?>
    </h1>
</header>

<?php if (have_posts()) : ?>
    <div class="post-list">
        <?php while (have_posts()) : the_post(); ?>
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
                    <p class="entry-excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
                    <p class="entry-meta"><?php echo wp_kses_post(xghome_classic_meta_line()); ?></p>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
    <nav class="panel pagination-wrap">
        <?php the_posts_pagination(); ?>
    </nav>
<?php else : ?>
    <?php get_template_part('template-parts/content', 'none'); ?>
<?php endif; ?>

<?php
get_footer();
