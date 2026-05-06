<?php
/**
 * Single post template.
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<?php while (have_posts()) : the_post(); ?>
    <nav class="panel breadcrumb-wrap">
        <a href="<?php echo esc_url(home_url('/')); ?>">首页</a> / <span>正文</span>
    </nav>

    <article <?php post_class('panel single-post'); ?>>
        <header class="single-header">
            <div class="single-title-row">
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <div class="single-title-tools">
                    <button type="button" class="post-tool-btn" id="fontSizeToggle" title="字号切换">T</button>
                    <button type="button" class="post-tool-btn" id="speechToggle" title="朗读文章">
                        <img src="<?php echo esc_url(xghome_classic_icon_uri('activity')); ?>" alt="" aria-hidden="true">
                    </button>
                    <button type="button" class="post-tool-btn" id="readModeToggle" title="阅读模式">
                        <img src="<?php echo esc_url(xghome_classic_icon_uri('file')); ?>" alt="" aria-hidden="true">
                    </button>
                </div>
            </div>
            <p class="entry-meta">
                <?php echo wp_kses_post(xghome_classic_meta_line()); ?>
                <span class="meta-item"><img src="<?php echo esc_url(xghome_classic_icon_uri('file')); ?>" alt="" aria-hidden="true"><?php echo esc_html((string) mb_strlen(wp_strip_all_tags(get_the_content()), 'UTF-8')); ?> 字</span>
                <span class="meta-item"><img src="<?php echo esc_url(xghome_classic_icon_uri('hash')); ?>" alt="" aria-hidden="true"><?php echo wp_kses_post(get_the_category_list('、')); ?></span>
            </p>
        </header>

        <?php if (post_password_required()) : ?>
            <div class="entry-content">
                <p class="protected-note">此文章已开启密码保护，请先输入访问密码。</p>
                <?php echo get_the_password_form(); ?>
            </div>
        <?php else : ?>
            <?php $share = xghome_classic_share_payload(get_the_ID()); ?>
            <?php if (has_post_thumbnail()) : ?>
                <div class="single-thumb">
                    <?php the_post_thumbnail('large', ['class' => 'img-responsive']); ?>
                </div>
            <?php endif; ?>

            <div class="entry-content">
                <?php xghome_classic_render_post_content(get_the_ID()); ?>
            </div>
            <?php $post_tags = get_the_tag_list('', '、', ''); ?>
            <?php if ($post_tags !== '') : ?>
                <div class="panel-lite single-tags">
                    <strong>标签：</strong>
                    <span><?php echo wp_kses_post($post_tags); ?></span>
                </div>
            <?php endif; ?>

            <footer class="single-footer-tools">
                <div class="single-updated"><img src="<?php echo esc_url(xghome_classic_icon_uri('clock')); ?>" alt="" aria-hidden="true">最后修改：<?php echo esc_html(get_the_modified_date('Y 年 m 月 d 日')); ?></div>
            </footer>
            <?php $reward = xghome_classic_reward_config(); ?>
            <?php if ($reward['enabled'] && ($reward['wechat'] !== '' || $reward['alipay'] !== '')) : ?>
                <section class="panel reward-box">
                    <div class="single-block-head">
                        <h3>赞赏支持</h3>
                        <button type="button" class="single-block-toggle js-single-collapse-toggle" data-target="#singleRewardBody" aria-expanded="false" aria-label="展开赞赏支持">⏬</button>
                    </div>
                    <div class="single-block-body is-collapsed" id="singleRewardBody">
                        <p><?php echo esc_html($reward['text']); ?></p>
                        <div class="reward-grid">
                            <?php if ($reward['wechat'] !== '') : ?>
                                <figure>
                                    <img src="<?php echo esc_url($reward['wechat']); ?>" alt="微信赞赏码">
                                    <figcaption>微信</figcaption>
                                </figure>
                            <?php endif; ?>
                            <?php if ($reward['alipay'] !== '') : ?>
                                <figure>
                                    <img src="<?php echo esc_url($reward['alipay']); ?>" alt="支付宝赞赏码">
                                    <figcaption>支付宝</figcaption>
                                </figure>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>
            <?php $qrcode_apis = xghome_classic_qrcode_api_list(get_permalink()); ?>
            <?php if (!empty($qrcode_apis)) : ?>
                <section class="panel post-qrcode">
                    <div class="single-block-head">
                        <h3>文章二维码</h3>
                        <button type="button" class="single-block-toggle js-single-collapse-toggle" data-target="#singleQrcodeBody" aria-expanded="false" aria-label="展开文章二维码">⏬</button>
                    </div>
                    <div class="single-block-body is-collapsed" id="singleQrcodeBody">
                        <p>扫码在手机上继续阅读</p>
                        <img class="js-qrcode-image" src="" alt="文章二维码" data-qrcode-apis="<?php echo esc_attr(wp_json_encode($qrcode_apis)); ?>">
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </article>

    <?php if (!post_password_required()) : ?>
        <nav class="panel post-nav">
            <div class="row">
                <div class="col-xs-6 text-left"><?php previous_post_link('%link', '上一篇'); ?></div>
                <div class="col-xs-6 text-right"><?php next_post_link('%link', '下一篇'); ?></div>
            </div>
        </nav>

        <?php
        if (comments_open() || get_comments_number()) {
            comments_template();
        }
        ?>
    <?php endif; ?>
<?php endwhile; ?>

<?php
get_footer();
