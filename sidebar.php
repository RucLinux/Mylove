<?php
/**
 * Sidebar template.
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="panel sidebar-panel">
    <section class="widget tab-widget">
        <ul class="tab-nav">
            <li><a href="#" class="is-active" data-tab-target="hot-posts"><img src="<?php echo esc_url(xghome_classic_icon_uri('thumbs-up')); ?>" alt="" aria-hidden="true"><span>热门</span></a></li>
            <li><a href="#" data-tab-target="latest-comments"><img src="<?php echo esc_url(xghome_classic_icon_uri('message-square')); ?>" alt="" aria-hidden="true"><span>评论</span></a></li>
            <li><a href="#" data-tab-target="random-posts"><img src="<?php echo esc_url(xghome_classic_icon_uri('gift')); ?>" alt="" aria-hidden="true"><span>随机</span></a></li>
        </ul>

        <div class="tab-panel is-active" data-tab-panel="hot-posts">
            <ul class="post-mini-list">
                <?php
                $hot_posts = xghome_classic_popular_posts(5);
                $default_thumb = xghome_classic_default_thumbnail_url();
                if ($hot_posts->have_posts()) :
                    while ($hot_posts->have_posts()) : $hot_posts->the_post();
                        ?>
                        <li class="media-list-item">
                            <a class="mini-thumb" href="<?php the_permalink(); ?>">
                                <?php
                                $mini_thumb = xghome_classic_get_list_thumbnail_url(get_the_ID(), 'thumbnail');
                                if ($mini_thumb !== '') :
                                    ?>
                                    <img src="<?php echo esc_url($mini_thumb); ?>" class="img-responsive" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
                                <?php elseif ($default_thumb !== '') : ?>
                                    <img src="<?php echo esc_url($default_thumb); ?>" class="img-responsive" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
                                <?php else : ?>
                                    <span class="thumb-fallback" aria-hidden="true"></span>
                                <?php endif; ?>
                            </a>
                            <div class="mini-content">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                <small><?php comments_number('0 评论', '1 评论', '% 评论'); ?></small>
                            </div>
                        </li>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <li>暂无内容</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="tab-panel" data-tab-panel="latest-comments">
            <ul class="post-mini-list">
                <?php
                $comments = get_comments(['number' => 5, 'status' => 'approve']);
                if (!empty($comments)) :
                    foreach ($comments as $comment) :
                        ?>
                        <li class="media-list-item">
                            <a class="mini-avatar" href="<?php echo esc_url(get_comment_link($comment)); ?>">
                                <?php echo get_avatar($comment, 40); ?>
                            </a>
                            <div class="mini-content">
                                <a href="<?php echo esc_url(get_comment_link($comment)); ?>">
                                    <?php echo esc_html($comment->comment_author); ?>
                                </a>
                                <small><?php echo esc_html(wp_trim_words($comment->comment_content, 16)); ?></small>
                            </div>
                        </li>
                        <?php
                    endforeach;
                else :
                    ?>
                    <li>暂无评论</li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="tab-panel" data-tab-panel="random-posts">
            <ul class="post-mini-list">
                <?php
                $random_posts = xghome_classic_random_posts(5);
                $default_thumb = xghome_classic_default_thumbnail_url();
                if ($random_posts->have_posts()) :
                    while ($random_posts->have_posts()) : $random_posts->the_post();
                        ?>
                        <li class="media-list-item">
                            <a class="mini-thumb" href="<?php the_permalink(); ?>">
                                <?php
                                $mini_thumb = xghome_classic_get_list_thumbnail_url(get_the_ID(), 'thumbnail');
                                if ($mini_thumb !== '') :
                                    ?>
                                    <img src="<?php echo esc_url($mini_thumb); ?>" class="img-responsive" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
                                <?php elseif ($default_thumb !== '') : ?>
                                    <img src="<?php echo esc_url($default_thumb); ?>" class="img-responsive" alt="<?php the_title_attribute(); ?>" loading="lazy" decoding="async">
                                <?php else : ?>
                                    <span class="thumb-fallback" aria-hidden="true"></span>
                                <?php endif; ?>
                            </a>
                            <div class="mini-content">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                <small><?php comments_number('0 评论', '1 评论', '% 评论'); ?></small>
                            </div>
                        </li>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <li>暂无内容</li>
                <?php endif; ?>
            </ul>
        </div>
    </section>

    <?php $sidebar_qr = get_template_directory() . '/images/qrcode.png'; ?>
    <?php if (is_file($sidebar_qr)) : ?>
    <section class="widget qrcode-widget">
        <h3 class="widget-title"><img src="<?php echo esc_url(xghome_classic_icon_uri('hash')); ?>" alt="" aria-hidden="true"><span>扫码访问</span></h3>
        <img class="sidebar-qrcode" src="<?php echo esc_url(get_template_directory_uri() . '/images/qrcode.png'); ?>" alt="二维码">
    </section>
    <?php endif; ?>

    <section class="widget">
        <h3 class="widget-title"><img src="<?php echo esc_url(xghome_classic_icon_uri('activity')); ?>" alt="" aria-hidden="true"><span>博客信息</span></h3>
        <ul class="list-unstyled blog-info-list">
            <li>文章数目 <span><?php echo esc_html(wp_count_posts()->publish); ?></span></li>
            <li>评论数目 <span><?php echo esc_html(wp_count_comments()->approved); ?></span></li>
            <li>运行天数 <span><?php echo esc_html((string) xghome_classic_running_days()); ?> 天</span></li>
            <li>最后活动 <span><?php echo esc_html(xghome_classic_last_activity_date()); ?></span></li>
        </ul>
    </section>

    <?php
    $tag_cloud_html = trim((string) (wp_tag_cloud(['echo' => false]) ?: ''));
    if ($tag_cloud_html !== '') :
        ?>
    <section class="widget">
        <h3 class="widget-title"><img src="<?php echo esc_url(xghome_classic_icon_uri('hash')); ?>" alt="" aria-hidden="true"><span>标签云</span></h3>
        <div class="tag-cloud-wrap"><?php echo $tag_cloud_html; ?></div>
    </section>
    <?php endif; ?>

    <?php if (is_active_sidebar('sidebar-right')) : ?>
        <?php dynamic_sidebar('sidebar-right'); ?>
    <?php endif; ?>
</div>
