<?php
/**
 * 手机端抽屉菜单：与电脑版左侧导航一致（首页、相册、归档、分类、页面、友链，可折叠）。
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="mobile-drawer-inner">
    <div class="left-profile-card mobile-drawer-profile">
        <?php
        $left_logo_path = get_template_directory() . '/images/logo.png';
        $left_logo_url = is_file($left_logo_path)
            ? get_template_directory_uri() . '/images/logo.png'
            : get_site_icon_url(128, xghome_classic_icon_uri('home'));
        ?>
        <a class="left-profile-avatar" href="<?php echo esc_url(home_url('/')); ?>">
            <img src="<?php echo esc_url($left_logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
        </a>
        <div class="left-profile-meta">
            <strong><?php bloginfo('name'); ?></strong>
            <span>RSS</span>
        </div>
    </div>
    <ul class="menu-block menu-compact mobile-drawer-menu">
        <li><a href="<?php echo esc_url(home_url('/')); ?>"><img src="<?php echo esc_url(xghome_classic_icon_uri('home')); ?>" alt="" aria-hidden="true"><span>首页</span></a></li>
        <li><a href="<?php echo esc_url(xghome_classic_get_album_page_url()); ?>"><img src="<?php echo esc_url(xghome_classic_icon_uri('image')); ?>" alt="" aria-hidden="true"><span>相册</span></a></li>
        <li><a href="<?php echo esc_url(xghome_classic_get_archives_page_url()); ?>"><img src="<?php echo esc_url(xghome_classic_icon_uri('calendar')); ?>" alt="" aria-hidden="true"><span>归档</span></a></li>
        <li class="menu-item-has-children">
            <button type="button" class="menu-link-toggle js-menu-toggle" data-target="#mobileDrawerCategoryMenu" aria-expanded="false">
                <span class="menu-link-main"><img src="<?php echo esc_url(xghome_classic_icon_uri('grid')); ?>" alt="" aria-hidden="true"><span>分类</span></span>
                <span class="menu-link-arrow">›</span>
            </button>
            <ul class="submenu-block is-collapsed" id="mobileDrawerCategoryMenu">
                <?php foreach (xghome_classic_left_nav_categories(80) as $cat) : ?>
                    <li>
                        <a class="cat-link" href="<?php echo esc_url(get_category_link($cat)); ?>">
                            <span class="cat-name"><?php echo esc_html($cat->name); ?></span>
                            <span class="cat-count"><?php echo esc_html((string) $cat->count); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </li>
        <li class="menu-item-has-children">
            <button type="button" class="menu-link-toggle js-menu-toggle" data-target="#mobileDrawerPageMenu" aria-expanded="false">
                <span class="menu-link-main"><img src="<?php echo esc_url(xghome_classic_icon_uri('file')); ?>" alt="" aria-hidden="true"><span>页面</span></span>
                <span class="menu-link-arrow">›</span>
            </button>
            <ul class="submenu-block is-collapsed" id="mobileDrawerPageMenu">
                <?php
                wp_list_pages([
                    'title_li' => '',
                    'depth'    => 1,
                ]);
                ?>
            </ul>
        </li>
        <li class="menu-item-has-children">
            <button type="button" class="menu-link-toggle js-menu-toggle" data-target="#mobileDrawerLinkMenu" aria-expanded="false">
                <span class="menu-link-main"><img src="<?php echo esc_url(xghome_classic_icon_uri('user')); ?>" alt="" aria-hidden="true"><span>友链</span></span>
                <span class="menu-link-arrow">›</span>
            </button>
            <ul class="submenu-block is-collapsed" id="mobileDrawerLinkMenu">
                <?php
                if (function_exists('wp_list_bookmarks')) {
                    wp_list_bookmarks([
                        'title_li'   => '',
                        'categorize' => 0,
                    ]);
                } else {
                    echo '<li><a href="https://example.com" target="_blank" rel="noopener">' . esc_html__('Example', 'xghome-classic') . '</a></li>';
                }
                ?>
            </ul>
        </li>
    </ul>
    <div class="left-bottom-actions mobile-drawer-footer-actions">
        <a href="<?php echo esc_url(function_exists('xghome_classic_sitemap_public_url') ? xghome_classic_sitemap_public_url() : home_url('/')); ?>" aria-label="网站地图" title="网站地图">
            <img src="<?php echo esc_url(xghome_classic_icon_uri('message-square')); ?>" alt="" aria-hidden="true">
        </a>
        <a href="<?php echo esc_url(get_bloginfo('rss2_url')); ?>" aria-label="RSS" title="RSS 订阅">
            <img src="<?php echo esc_url(xghome_classic_icon_uri('rss')); ?>" alt="" aria-hidden="true">
        </a>
        <a href="<?php echo esc_url(is_user_logged_in() ? admin_url() : wp_login_url()); ?>" aria-label="后台" title="进入后台">
            <img src="<?php echo esc_url(xghome_classic_icon_uri('settings')); ?>" alt="" aria-hidden="true">
        </a>
    </div>
</div>
