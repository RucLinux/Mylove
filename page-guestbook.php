<?php
/**
 * Guestbook page template.
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

        <section class="panel-lite">
            <h3>总评论排行榜</h3>
            <ol class="leaderboard-list">
                <?php foreach (xghome_classic_get_comment_leaderboard(10) as $item) : ?>
                    <li>
                        <strong><?php echo esc_html((string) $item['total']); ?></strong>
                        <span><?php echo esc_html((string) $item['author']); ?></span>
                        <?php if (!empty($item['url'])) : ?>
                            <a href="<?php echo esc_url((string) $item['url']); ?>" target="_blank" rel="noopener"><?php echo esc_html((string) $item['url']); ?></a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </section>

        <section class="panel-lite">
            <p>欢迎来到小站~ 如有什么问题或建议请留言，我会尽快回复您。</p>
            <p><strong>为了防止垃圾评论和留言，本博客限制了超链接。</strong></p>
        </section>

        <div class="entry-content">
            <?php the_content(); ?>
        </div>
    </article>

    <?php comments_template(); ?>
<?php endwhile; ?>

<?php
get_footer();
