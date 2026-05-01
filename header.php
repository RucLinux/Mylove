<?php
/**
 * Header template.
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('xghome-body'); ?>>
<?php wp_body_open(); ?>

<div class="container app-container">
    <header class="site-header panel">
        <div class="site-branding">
            <a class="site-title" href="<?php echo esc_url(home_url('/')); ?>">
                <?php bloginfo('name'); ?>
            </a>
            <p class="site-description"><?php bloginfo('description'); ?></p>
        </div>

        <div class="site-tools">
            <?php get_search_form(); ?>
            <?php if (xghome_classic_top_enhanced_enabled()) : ?>
                <div class="top-actions">
                    <button type="button" class="top-action-btn" data-top-panel="stats-panel">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/images/icons/pie-chart.svg'); ?>" alt="" aria-hidden="true">
                        <span>统计</span>
                    </button>
                    <button type="button" class="top-action-btn" data-top-panel="talk-panel">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/images/icons/message-square.svg'); ?>" alt="" aria-hidden="true">
                        <span>时光机</span>
                    </button>
                    <a class="top-action-btn" href="<?php echo esc_url(is_user_logged_in() ? admin_url() : wp_login_url()); ?>">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/images/icons/key.svg'); ?>" alt="" aria-hidden="true">
                        <span>登录</span>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <?php if (xghome_classic_top_enhanced_enabled()) : ?>
        <div class="panel mobile-topbar visible-xs">
            <button type="button" class="mobile-topbar-btn" id="mobileMenuBtn">
                <img src="<?php echo esc_url(xghome_classic_icon_uri('grid')); ?>" alt="" aria-hidden="true">
                <span>菜单</span>
            </button>
            <a class="mobile-topbar-title" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
            <button type="button" class="mobile-topbar-btn" id="mobileSidebarBtn">
                <img src="<?php echo esc_url(xghome_classic_icon_uri('activity')); ?>" alt="" aria-hidden="true">
                <span>侧栏</span>
            </button>
        </div>
        <nav class="panel mobile-quick-nav visible-xs">
            <a href="<?php echo esc_url(home_url('/')); ?>">首页</a>
            <a href="<?php echo esc_url(home_url('/')); ?>">文章</a>
            <a href="<?php echo esc_url(xghome_classic_get_archives_page_url()); ?>">归档</a>
            <a href="<?php echo esc_url(home_url('/about/')); ?>">关于</a>
        </nav>
        <aside class="panel mobile-drawer visible-xs" id="mobileDrawer">
            <ul class="menu-block">
                <li>
                    <a href="<?php echo esc_url(home_url('/')); ?>">
                        <img src="<?php echo esc_url(xghome_classic_icon_uri('home')); ?>" alt="" aria-hidden="true">
                        <span>首页</span>
                    </a>
                </li>
            </ul>
            <h4 class="menu-title"><img src="<?php echo esc_url(xghome_classic_icon_uri('grid')); ?>" alt="" aria-hidden="true"><span>分类</span></h4>
            <ul class="menu-block">
                <?php foreach (xghome_classic_left_nav_categories(80) as $cat) : ?>
                    <li>
                        <a class="cat-link" href="<?php echo esc_url(get_category_link($cat)); ?>">
                            <span class="cat-name"><?php echo esc_html($cat->name); ?></span>
                            <span class="cat-count"><?php echo esc_html((string) $cat->count); ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <h4 class="menu-title"><img src="<?php echo esc_url(xghome_classic_icon_uri('file')); ?>" alt="" aria-hidden="true"><span>页面</span></h4>
            <ul class="menu-block">
                <?php
                wp_list_pages([
                    'title_li' => '',
                    'depth' => 1,
                ]);
                ?>
            </ul>
            <h4 class="menu-title"><img src="<?php echo esc_url(xghome_classic_icon_uri('user')); ?>" alt="" aria-hidden="true"><span>友链</span></h4>
            <ul class="menu-block">
                <?php
                if (function_exists('wp_list_bookmarks')) {
                    wp_list_bookmarks([
                        'title_li' => '',
                        'categorize' => 0,
                    ]);
                } else {
                    echo '<li><a href="https://example.com" target="_blank" rel="noopener">' . esc_html__('Example', 'xghome-classic') . '</a></li>';
                }
                ?>
            </ul>
        </aside>
        <div class="top-panels-wrap">
        <?php
        $month_stats = xghome_classic_month_post_counts(10);
        $top_categories = xghome_classic_top_terms('category', 5);
        $top_tags = xghome_classic_top_terms('post_tag', 8);
        $now_dt = new DateTimeImmutable('now', wp_timezone());
        $tm_year = (int) $now_dt->format('Y');
        $tm_month = (int) $now_dt->format('n');
        $time_machine_posts = new WP_Query([
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 12,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'date_query'     => [
                'relation' => 'AND',
                [
                    'month' => $tm_month,
                ],
                [
                    'year'    => $tm_year,
                    'compare' => '<',
                ],
            ],
        ]);
        ?>
        <section class="panel top-panel" data-top-panel-id="stats-panel">
            <h3>动态统计</h3>
            <div class="top-panel-grid">
                <div>
                    <strong>发布统计（近 10 个月）</strong>
                    <ul class="top-mini-list">
                        <?php foreach ($month_stats as $row) : ?>
                            <li><?php echo esc_html((string) $row['ym']); ?>：<?php echo esc_html((string) $row['total']); ?> 篇</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <strong>分类统计</strong>
                    <ul class="top-mini-list">
                        <?php foreach ($top_categories as $term) : ?>
                            <li><?php echo esc_html($term->name); ?>：<?php echo esc_html((string) $term->count); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <strong>标签统计</strong>
                    <ul class="top-mini-list">
                        <?php foreach ($top_tags as $term) : ?>
                            <li><?php echo esc_html($term->name); ?>：<?php echo esc_html((string) $term->count); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </section>
        <section class="panel top-panel" data-top-panel-id="talk-panel">
            <h3>时光机 <small class="time-machine-sub">往年本月</small></h3>
            <?php
            $time_machine_rows = [];
            if ($time_machine_posts->have_posts()) {
                while ($time_machine_posts->have_posts()) {
                    $time_machine_posts->the_post();
                    $time_machine_rows[] = [
                        'url'  => get_permalink(),
                        'title'=> get_the_title(),
                        'date' => get_the_date('Y-m-d'),
                    ];
                }
                wp_reset_postdata();
            }
            $time_machine_cols = [[], [], []];
            foreach ($time_machine_rows as $i => $row) {
                $time_machine_cols[$i % 3][] = $row;
            }
            ?>
            <?php if (!empty($time_machine_rows)) : ?>
                <div class="time-machine-columns" role="list">
                    <?php foreach ($time_machine_cols as $col) : ?>
                        <div class="time-machine-col" role="presentation">
                            <ul class="time-machine-col-list">
                                <?php foreach ($col as $row) : ?>
                                    <li>
                                        <a href="<?php echo esc_url($row['url']); ?>">
                                            <strong class="time-machine-title"><?php echo esc_html($row['title']); ?></strong>
                                            <small class="time-machine-date"><?php echo esc_html($row['date']); ?></small>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="time-machine-empty">当前月份在往年暂无文章</p>
            <?php endif; ?>
        </section>
        </div>
    <?php endif; ?>

    <div class="layout-wrap row<?php echo xghome_classic_layout_swap_enabled() ? ' xghome-layout-swap' : ''; ?>">
        <aside class="left-nav col-sm-2 hidden-xs">
            <div class="panel">
                <div class="left-profile-card">
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
                <ul class="menu-block menu-compact">
                    <li><a href="<?php echo esc_url(home_url('/')); ?>"><img src="<?php echo esc_url(xghome_classic_icon_uri('home')); ?>" alt="" aria-hidden="true"><span>首页</span></a></li>
                    <li><a href="<?php echo esc_url(xghome_classic_get_album_page_url()); ?>"><img src="<?php echo esc_url(xghome_classic_icon_uri('image')); ?>" alt="" aria-hidden="true"><span>相册</span></a></li>
                    <li><a href="<?php echo esc_url(xghome_classic_get_archives_page_url()); ?>"><img src="<?php echo esc_url(xghome_classic_icon_uri('calendar')); ?>" alt="" aria-hidden="true"><span>归档</span></a></li>
                    <li class="menu-item-has-children">
                        <button type="button" class="menu-link-toggle js-menu-toggle" data-target="#leftCategoryMenu" aria-expanded="false">
                            <span class="menu-link-main"><img src="<?php echo esc_url(xghome_classic_icon_uri('grid')); ?>" alt="" aria-hidden="true"><span>分类</span></span>
                            <span class="menu-link-arrow">›</span>
                        </button>
                        <ul class="submenu-block is-collapsed" id="leftCategoryMenu">
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
                        <button type="button" class="menu-link-toggle js-menu-toggle" data-target="#leftPageMenu" aria-expanded="false">
                            <span class="menu-link-main"><img src="<?php echo esc_url(xghome_classic_icon_uri('file')); ?>" alt="" aria-hidden="true"><span>页面</span></span>
                            <span class="menu-link-arrow">›</span>
                        </button>
                        <ul class="submenu-block is-collapsed" id="leftPageMenu">
                            <?php
                            wp_list_pages([
                                'title_li' => '',
                                'depth' => 1,
                            ]);
                            ?>
                        </ul>
                    </li>
                    <li class="menu-item-has-children">
                        <button type="button" class="menu-link-toggle js-menu-toggle" data-target="#leftLinkMenu" aria-expanded="false">
                            <span class="menu-link-main"><img src="<?php echo esc_url(xghome_classic_icon_uri('user')); ?>" alt="" aria-hidden="true"><span>友链</span></span>
                            <span class="menu-link-arrow">›</span>
                        </button>
                        <ul class="submenu-block is-collapsed" id="leftLinkMenu">
                            <?php
                            if (function_exists('wp_list_bookmarks')) {
                                wp_list_bookmarks([
                                    'title_li' => '',
                                    'categorize' => 0,
                                ]);
                            } else {
                                echo '<li><a href="https://example.com" target="_blank" rel="noopener">' . esc_html__('Example', 'xghome-classic') . '</a></li>';
                            }
                            ?>
                        </ul>
                    </li>
                </ul>
                <div class="left-bottom-actions">
                    <a href="<?php echo esc_url(home_url('/')); ?>" aria-label="快捷入口" title="快捷入口">
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
        </aside>

        <main class="content-area col-sm-7">
