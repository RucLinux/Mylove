<?php
/**
 * Links page template.
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
        </header>

        <div class="entry-content">
            <?php the_content(); ?>
        </div>

        <section class="panel-lite">
            <h3>友情链接列表</h3>
            <?php if (function_exists('wp_list_bookmarks')) : ?>
                <?php
                $terms = get_terms([
                    'taxonomy'   => 'link_category',
                    'hide_empty' => false,
                ]);
                if (!is_wp_error($terms) && !empty($terms)) :
                    foreach ($terms as $term) :
                        ?>
                        <h4><?php echo esc_html($term->name); ?></h4>
                        <ul class="friend-links">
                            <?php
                            wp_list_bookmarks([
                                'category'   => (int) $term->term_id,
                                'categorize' => 0,
                                'title_li'   => '',
                                'show_description' => 1,
                            ]);
                            ?>
                        </ul>
                        <?php
                    endforeach;
                else :
                    ?>
                    <ul class="friend-links">
                        <?php
                        wp_list_bookmarks([
                            'categorize' => 0,
                            'title_li'   => '',
                            'show_description' => 1,
                        ]);
                        ?>
                    </ul>
                <?php endif; ?>
            <?php else : ?>
                <p>未启用链接管理模块。</p>
            <?php endif; ?>
        </section>
    </article>
<?php endwhile; ?>

<?php
get_footer();
