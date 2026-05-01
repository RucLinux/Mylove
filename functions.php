<?php
/**
 * Theme setup and helpers — Mylove (GPL-2.0-or-later).
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}

function xghome_classic_setup(): void
{
    load_theme_textdomain('xghome-classic', get_template_directory() . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', [
        'height'      => 80,
        'width'       => 80,
        'flex-width'  => true,
        'flex-height' => true,
    ]);

    register_nav_menus([
        'primary' => __('Primary Menu', 'xghome-classic'),
    ]);
}
add_action('after_setup_theme', 'xghome_classic_setup');

function xghome_classic_widgets_init(): void
{
    register_sidebar([
        'name'          => __('Right Sidebar', 'xghome-classic'),
        'id'            => 'sidebar-right',
        'description'   => __('Widgets shown on the right panel.', 'xghome-classic'),
        'before_widget' => '<section id="%1$s" class="widget %2$s panel">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
}
add_action('widgets_init', 'xghome_classic_widgets_init');

/**
 * 右侧边栏里无实质内容的小工具不输出外壳，避免出现白底空框。
 */
function xghome_classic_should_display_widget($instance, $widget, $args)
{
    if (empty($args['id']) || $args['id'] !== 'sidebar-right') {
        return $instance;
    }
    if ($widget instanceof WP_Widget_Text) {
        $text = isset($instance['text']) ? (string) $instance['text'] : '';
        if (trim($text) === '') {
            return false;
        }
    }
    if ($widget instanceof WP_Widget_Custom_HTML) {
        $content = isset($instance['content']) ? (string) $instance['content'] : '';
        if (trim($content) === '') {
            return false;
        }
    }
    if (class_exists('WP_Widget_Block') && $widget instanceof WP_Widget_Block) {
        $content = isset($instance['content']) ? (string) $instance['content'] : '';
        if (trim($content) === '') {
            return false;
        }
        $rendered = do_blocks($content);
        $visible = trim(str_replace("\xc2\xa0", ' ', wp_strip_all_tags($rendered, true)));
        $visible = trim(str_replace('&nbsp;', ' ', $visible));
        if ($visible === '') {
            return false;
        }
    }
    return $instance;
}
add_filter('widget_display_callback', 'xghome_classic_should_display_widget', 10, 3);

function xghome_classic_enqueue_assets(): void
{
    wp_enqueue_style(
        'bootstrap',
        get_template_directory_uri() . '/assets/vendor/bootstrap/3.4.1/css/bootstrap.min.css',
        [],
        '3.4.1'
    );

    wp_enqueue_style(
        'xghome-theme',
        get_template_directory_uri() . '/assets/css/theme.css',
        ['bootstrap'],
        '1.0.8'
    );

    wp_enqueue_style(
        'highlightjs',
        get_template_directory_uri() . '/assets/vendor/highlight/11.10.0/github-dark.min.css',
        [],
        '11.10.0'
    );

    wp_enqueue_script(
        'highlightjs',
        get_template_directory_uri() . '/assets/vendor/highlight/11.10.0/highlight.min.js',
        [],
        '11.10.0',
        true
    );

    wp_enqueue_script(
        'xghome-theme',
        get_template_directory_uri() . '/assets/js/theme.js',
        ['jquery', 'highlightjs'],
        '1.0.4',
        true
    );
}
add_action('wp_enqueue_scripts', 'xghome_classic_enqueue_assets');

function xghome_classic_excerpt_length(int $length): int
{
    if (is_admin()) {
        return $length;
    }

    return 120;
}
add_filter('excerpt_length', 'xghome_classic_excerpt_length', 999);

function xghome_classic_excerpt_more(string $more): string
{
    if (is_admin()) {
        return $more;
    }

    return '...';
}
add_filter('excerpt_more', 'xghome_classic_excerpt_more');

function xghome_classic_icon_uri(string $name): string
{
    return trailingslashit(get_template_directory_uri()) . 'images/icons/' . $name . '.svg';
}

function xghome_classic_copyright_years(): string
{
    $current_year = (int) wp_date('Y');
    $first_posts = get_posts([
        'post_type'              => 'post',
        'post_status'            => 'publish',
        'posts_per_page'         => 1,
        'orderby'                => 'date',
        'order'                  => 'ASC',
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'ignore_sticky_posts'    => true,
        'suppress_filters'       => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ]);

    if (empty($first_posts)) {
        return (string) $current_year;
    }

    $first_year = (int) get_the_date('Y', (int) $first_posts[0]);
    if ($first_year <= 0 || $first_year >= $current_year) {
        return (string) $current_year;
    }

    return $first_year . '-' . $current_year;
}

function xghome_classic_meta_line(): string
{
    $author = get_the_author();
    $date   = get_the_date('Y 年 m 月 d 日');
    $count  = get_comments_number();
    $views  = xghome_classic_get_post_views(get_the_ID());

    return sprintf(
        '<span class="meta-item"><img src="%s" alt="" aria-hidden="true">%s</span><span class="meta-item"><img src="%s" alt="" aria-hidden="true">%s</span><span class="meta-item"><img src="%s" alt="" aria-hidden="true">%d 条评论</span><span class="meta-item"><img src="%s" alt="" aria-hidden="true">%d 次浏览</span>',
        esc_url(xghome_classic_icon_uri('user')),
        esc_html($author),
        esc_url(xghome_classic_icon_uri('clock')),
        esc_html($date),
        esc_url(xghome_classic_icon_uri('message-square')),
        (int) $count,
        esc_url(xghome_classic_icon_uri('eye')),
        (int) $views
    );
}

function xghome_classic_popular_posts(int $limit = 5): WP_Query
{
    return new WP_Query([
        'post_type'           => 'post',
        'posts_per_page'      => $limit,
        'ignore_sticky_posts' => true,
        'orderby'             => 'comment_count',
        'order'               => 'DESC',
    ]);
}

function xghome_classic_random_posts(int $limit = 5): WP_Query
{
    return new WP_Query([
        'post_type'           => 'post',
        'posts_per_page'      => $limit,
        'ignore_sticky_posts' => true,
        'orderby'             => 'rand',
    ]);
}

function xghome_classic_running_days(): int
{
    $first_post = get_posts([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'orderby'        => 'date',
        'order'          => 'ASC',
        'fields'         => 'ids',
    ]);

    if (empty($first_post)) {
        return 0;
    }

    $created = get_post_time('U', true, $first_post[0]);
    if (!$created) {
        return 0;
    }

    return (int) max(1, floor((time() - $created) / DAY_IN_SECONDS));
}

function xghome_classic_last_activity_date(): string
{
    $post_ts = (int) get_lastpostmodified('U');
    $latest_comment = get_comments([
        'number'  => 1,
        'status'  => 'approve',
        'orderby' => 'comment_date_gmt',
        'order'   => 'DESC',
    ]);

    $comment_ts = 0;
    if (!empty($latest_comment) && isset($latest_comment[0]->comment_date_gmt)) {
        $parsed = strtotime((string) $latest_comment[0]->comment_date_gmt . ' GMT');
        $comment_ts = $parsed !== false ? (int) $parsed : 0;
    }

    $login_ts = (int) get_option('xghome_last_login_time', 0);
    $latest = max($post_ts, $comment_ts, $login_ts);
    if ($latest <= 0) {
        return '-';
    }

    return wp_date('Y-m-d', $latest);
}

function xghome_classic_update_last_login(string $user_login, WP_User $user): void
{
    update_option('xghome_last_login_time', time());
}
add_action('wp_login', 'xghome_classic_update_last_login', 10, 2);

function xghome_classic_default_thumbnail_url(): string
{
    return (string) get_option('xghome_default_thumbnail', '');
}

/**
 * 列表/侧栏用缩略图 URL：特色图 → 子附件图片 → gallery 短代码 ids → 正文首张图 → 区块 JSON 中的 url。
 */
function xghome_classic_get_list_thumbnail_url(int $post_id, string $size = 'medium'): string
{
    if ($post_id <= 0) {
        return '';
    }

    $featured = get_the_post_thumbnail_url($post_id, $size);
    if ($featured !== false && $featured !== '') {
        return $featured;
    }

    $attached = get_attached_media('image', $post_id);
    if (!empty($attached)) {
        foreach ($attached as $att) {
            if (!$att instanceof WP_Post) {
                continue;
            }
            $u = wp_get_attachment_image_url($att->ID, $size);
            if ($u !== false && $u !== '') {
                return $u;
            }
        }
    }

    $post = get_post($post_id);
    if (!$post instanceof WP_Post) {
        return '';
    }

    $html = (string) $post->post_content;
    if ($html !== '' && preg_match('/\[gallery[^\]]*ids=["\']([0-9,\s]+)["\']/', $html, $m)) {
        foreach (preg_split('/\s*,\s*/', trim($m[1])) as $idstr) {
            $aid = (int) $idstr;
            if ($aid > 0) {
                $u = wp_get_attachment_image_url($aid, $size);
                if ($u !== false && $u !== '') {
                    return $u;
                }
            }
        }
    }

    if ($html !== '' && preg_match_all('/"id"\s*:\s*(\d+)/', $html, $id_matches)) {
        foreach ($id_matches[1] as $idstr) {
            $aid = (int) $idstr;
            if ($aid > 0 && wp_attachment_is_image($aid)) {
                $u = wp_get_attachment_image_url($aid, $size);
                if ($u !== false && $u !== '') {
                    return $u;
                }
            }
        }
    }

    if ($html !== '' && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
        $src = trim($m[1]);
        if ($src !== '') {
            if (strpos($src, '//') === 0) {
                $src = (is_ssl() ? 'https:' : 'http:') . $src;
            } elseif (!preg_match('#^(https?:)?//#i', $src)) {
                $src = home_url('/' . ltrim($src, '/'));
            }
            return esc_url_raw($src);
        }
    }

    if ($html !== '' && preg_match('/"url"\s*:\s*"(https?:[^"]+\.(?:jpg|jpeg|png|gif|webp)[^"]*)"/i', $html, $m)) {
        return esc_url_raw(str_replace('\\/', '/', $m[1]));
    }

    return '';
}

function xghome_classic_get_post_views(int $post_id): int
{
    return (int) get_post_meta($post_id, '_xghome_post_views', true);
}

function xghome_classic_increase_post_views(): void
{
    if (!is_single()) {
        return;
    }

    $post_id = get_queried_object_id();
    if ($post_id <= 0) {
        return;
    }

    $views = xghome_classic_get_post_views($post_id);
    update_post_meta($post_id, '_xghome_post_views', $views + 1);
}
add_action('wp', 'xghome_classic_increase_post_views');

function xghome_classic_get_permalink_presets(): array
{
    return [
        ''                                     => '默认（Plain）?p=123',
        '/%postname%.html'                     => '/post-name.html',
        '/%year%/%monthnum%/%postname%.html'  => '/2026/04/post-name.html',
        '/archives/%post_id%.html'             => '/archives/123.html',
    ];
}

function xghome_classic_get_permalink_options(): array
{
    return [
        'category_html' => (int) get_option('xghome_category_html', 0),
        'tag_html'      => (int) get_option('xghome_tag_html', 0),
        'page_html'     => (int) get_option('xghome_page_html', 0),
        'post_prefix'   => (string) get_option('xghome_post_prefix', ''),
        'top_enhanced'  => (int) get_option('xghome_top_enhanced', 1),
        'footer_tongji_url' => (string) get_option('xghome_footer_tongji_url', ''),
        'qrcode_apis'   => (string) get_option('xghome_qrcode_apis', ''),
        'comment_email_regex' => (string) get_option('xghome_comment_email_regex', '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'),
        'comment_phone_regex' => (string) get_option('xghome_comment_phone_regex', '^1[3-9]\d{9}$'),
        'media_max_width' => (int) get_option('xghome_media_max_width', 100),
        'image_max_height' => (int) get_option('xghome_image_max_height', 960),
        'video_max_height' => (int) get_option('xghome_video_max_height', 680),
        'audio_max_width' => (int) get_option('xghome_audio_max_width', 100),
        'video_parse_api' => (string) get_option('xghome_video_parse_api', ''),
        'watermark_image' => (string) get_option('xghome_watermark_image', ''),
        'watermark_text'  => (string) get_option('xghome_watermark_text', ''),
        'default_thumbnail' => (string) get_option('xghome_default_thumbnail', ''),
        'reward_enabled'  => (int) get_option('xghome_reward_enabled', 0),
        'reward_text'     => (string) get_option('xghome_reward_text', '如果这篇文章对你有帮助，欢迎赞赏支持。'),
        'reward_wechat'   => (string) get_option('xghome_reward_wechat', ''),
        'reward_alipay'   => (string) get_option('xghome_reward_alipay', ''),
        'avatar_set'      => (string) get_option('xghome_avatar_set', ''),
        'anti_scrape_enabled' => (int) get_option('xghome_anti_scrape_enabled', 0),
        'anti_scrape_disable_right_click' => (int) get_option('xghome_anti_scrape_disable_right_click', 1),
        'anti_scrape_disable_shortcuts' => (int) get_option('xghome_anti_scrape_disable_shortcuts', 1),
        'anti_scrape_append_copy' => (int) get_option('xghome_anti_scrape_append_copy', 1),
        'layout_swap'      => (int) get_option('xghome_layout_swap', 0),
        'left_avatar_size' => (int) get_option('xghome_left_avatar_size', 56),
    ];
}

/** 左侧菜单栏与右侧边栏是否互换位置（相对默认：左菜单、右小工具）。 */
function xghome_classic_layout_swap_enabled(): bool
{
    return (int) get_option('xghome_layout_swap', 0) === 1;
}

/** 左侧头像圆圈边长（px），已限制在安全范围内并保持正圆。 */
function xghome_classic_get_left_avatar_size(): int
{
    return max(40, min(120, (int) get_option('xghome_left_avatar_size', 56)));
}

function xghome_classic_top_enhanced_enabled(): bool
{
    return (int) get_option('xghome_top_enhanced', 1) === 1;
}

function xghome_classic_add_theme_menu(): void
{
    add_theme_page(
        __('Mylove 主题设置', 'xghome-classic'),
        __('Mylove 设置', 'xghome-classic'),
        'manage_options',
        'xghome-permalink',
        'xghome_classic_render_permalink_page'
    );
    add_theme_page(
        __('备案管理', 'xghome-classic'),
        __('备案管理', 'xghome-classic'),
        'manage_options',
        'xghome-records',
        'xghome_classic_render_records_page'
    );
    add_theme_page(
        __('统计代码', 'xghome-classic'),
        __('统计代码', 'xghome-classic'),
        'manage_options',
        'xghome-analytics',
        'xghome_classic_render_analytics_page'
    );
}
add_action('admin_menu', 'xghome_classic_add_theme_menu');

function xghome_classic_record_allowed_html(): array
{
    return [
        'a' => [
            'href' => true,
            'title' => true,
            'target' => true,
            'rel' => true,
            'class' => true,
            'id' => true,
        ],
        'img' => [
            'src' => true,
            'alt' => true,
            'title' => true,
            'width' => true,
            'height' => true,
            'style' => true,
            'class' => true,
        ],
        'span' => [
            'class' => true,
            'style' => true,
        ],
        'strong' => [],
        'em' => [],
        'i' => [
            'class' => true,
        ],
        'small' => [],
        'br' => [],
    ];
}

function xghome_classic_analytics_allowed_html(): array
{
    return [
        'script' => [
            'src' => true,
            'async' => true,
            'defer' => true,
            'type' => true,
            'id' => true,
            'charset' => true,
            'crossorigin' => true,
            'referrerpolicy' => true,
        ],
        'noscript' => [],
        'img' => [
            'src' => true,
            'alt' => true,
            'style' => true,
            'width' => true,
            'height' => true,
            'referrerpolicy' => true,
        ],
        'iframe' => [
            'src' => true,
            'style' => true,
            'height' => true,
            'width' => true,
            'title' => true,
        ],
        'div' => [
            'id' => true,
            'class' => true,
            'style' => true,
            'data-site' => true,
        ],
    ];
}

function xghome_classic_handle_permalink_save(): void
{
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    if (!isset($_POST['xghome_permalink_nonce'])) {
        return;
    }

    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['xghome_permalink_nonce'])), 'xghome_save_permalink')) {
        return;
    }

    $presets = xghome_classic_get_permalink_presets();
    $saved_options = xghome_classic_get_permalink_options();
    $target  = isset($_POST['xghome_permalink_structure'])
        ? sanitize_text_field(wp_unslash($_POST['xghome_permalink_structure']))
        : (string) get_option('permalink_structure', '');
    $prefix  = isset($_POST['xghome_post_prefix']) ? sanitize_title(wp_unslash($_POST['xghome_post_prefix'])) : '';

    if (!array_key_exists($target, $presets) && $prefix === '') {
        $target = (string) get_option('permalink_structure', '');
    }

    if ($prefix !== '') {
        $target = '/' . trim($prefix, '/') . '/%postname%.html';
    }

    $category_html = isset($_POST['xghome_category_html']) ? 1 : 0;
    $tag_html      = isset($_POST['xghome_tag_html']) ? 1 : 0;
    $page_html     = isset($_POST['xghome_page_html']) ? 1 : 0;
    $top_enhanced  = isset($_POST['xghome_top_enhanced']) ? 1 : 0;
    $footer_tongji_url = isset($_POST['xghome_footer_tongji_url'])
        ? esc_url_raw(wp_unslash($_POST['xghome_footer_tongji_url']))
        : (string) $saved_options['footer_tongji_url'];
    $qrcode_apis   = isset($_POST['xghome_qrcode_apis'])
        ? sanitize_textarea_field(wp_unslash($_POST['xghome_qrcode_apis']))
        : (string) $saved_options['qrcode_apis'];
    $comment_email_regex = isset($_POST['xghome_comment_email_regex'])
        ? sanitize_text_field(wp_unslash($_POST['xghome_comment_email_regex']))
        : (string) $saved_options['comment_email_regex'];
    $comment_phone_regex = isset($_POST['xghome_comment_phone_regex'])
        ? sanitize_text_field(wp_unslash($_POST['xghome_comment_phone_regex']))
        : (string) $saved_options['comment_phone_regex'];
    $media_max_width = isset($_POST['xghome_media_max_width'])
        ? (int) sanitize_text_field(wp_unslash($_POST['xghome_media_max_width']))
        : (int) $saved_options['media_max_width'];
    $image_max_height = isset($_POST['xghome_image_max_height'])
        ? (int) sanitize_text_field(wp_unslash($_POST['xghome_image_max_height']))
        : (int) $saved_options['image_max_height'];
    $video_max_height = isset($_POST['xghome_video_max_height'])
        ? (int) sanitize_text_field(wp_unslash($_POST['xghome_video_max_height']))
        : (int) $saved_options['video_max_height'];
    $audio_max_width = isset($_POST['xghome_audio_max_width'])
        ? (int) sanitize_text_field(wp_unslash($_POST['xghome_audio_max_width']))
        : (int) $saved_options['audio_max_width'];
    $video_parse_api = isset($_POST['xghome_video_parse_api'])
        ? esc_url_raw(wp_unslash($_POST['xghome_video_parse_api']))
        : (string) $saved_options['video_parse_api'];
    $watermark_image = isset($_POST['xghome_watermark_image'])
        ? esc_url_raw(wp_unslash($_POST['xghome_watermark_image']))
        : (string) $saved_options['watermark_image'];
    $watermark_text = isset($_POST['xghome_watermark_text'])
        ? sanitize_text_field(wp_unslash($_POST['xghome_watermark_text']))
        : (string) $saved_options['watermark_text'];
    $default_thumbnail = isset($_POST['xghome_default_thumbnail'])
        ? esc_url_raw(wp_unslash($_POST['xghome_default_thumbnail']))
        : (string) $saved_options['default_thumbnail'];
    $reward_enabled = isset($_POST['xghome_reward_enabled']) ? 1 : 0;
    $reward_text = isset($_POST['xghome_reward_text'])
        ? sanitize_text_field(wp_unslash($_POST['xghome_reward_text']))
        : (string) $saved_options['reward_text'];
    $reward_wechat = isset($_POST['xghome_reward_wechat'])
        ? esc_url_raw(wp_unslash($_POST['xghome_reward_wechat']))
        : (string) $saved_options['reward_wechat'];
    $reward_alipay = isset($_POST['xghome_reward_alipay'])
        ? esc_url_raw(wp_unslash($_POST['xghome_reward_alipay']))
        : (string) $saved_options['reward_alipay'];
    $avatar_set = isset($_POST['xghome_avatar_set'])
        ? sanitize_text_field(wp_unslash($_POST['xghome_avatar_set']))
        : (string) $saved_options['avatar_set'];
    $anti_scrape_enabled = isset($_POST['xghome_anti_scrape_enabled']) ? 1 : 0;
    $anti_scrape_disable_right_click = isset($_POST['xghome_anti_scrape_disable_right_click']) ? 1 : 0;
    $anti_scrape_disable_shortcuts = isset($_POST['xghome_anti_scrape_disable_shortcuts']) ? 1 : 0;
    $anti_scrape_append_copy = isset($_POST['xghome_anti_scrape_append_copy']) ? 1 : 0;
    $layout_swap = isset($_POST['xghome_layout_swap']) ? 1 : 0;
    $left_avatar_size = isset($_POST['xghome_left_avatar_size'])
        ? (int) sanitize_text_field(wp_unslash($_POST['xghome_left_avatar_size']))
        : (int) $saved_options['left_avatar_size'];

    update_option('xghome_category_html', $category_html);
    update_option('xghome_tag_html', $tag_html);
    update_option('xghome_page_html', $page_html);
    update_option('xghome_post_prefix', $prefix);
    update_option('xghome_top_enhanced', $top_enhanced);
    update_option('xghome_footer_tongji_url', $footer_tongji_url);
    update_option('xghome_qrcode_apis', $qrcode_apis);
    update_option('xghome_comment_email_regex', $comment_email_regex);
    update_option('xghome_comment_phone_regex', $comment_phone_regex);
    update_option('xghome_media_max_width', max(30, min(100, $media_max_width)));
    update_option('xghome_image_max_height', max(120, min(2000, $image_max_height)));
    update_option('xghome_video_max_height', max(120, min(2000, $video_max_height)));
    update_option('xghome_audio_max_width', max(30, min(100, $audio_max_width)));
    update_option('xghome_video_parse_api', $video_parse_api);
    update_option('xghome_watermark_image', $watermark_image);
    update_option('xghome_watermark_text', $watermark_text);
    update_option('xghome_default_thumbnail', $default_thumbnail);
    update_option('xghome_reward_enabled', $reward_enabled);
    update_option('xghome_reward_text', $reward_text);
    update_option('xghome_reward_wechat', $reward_wechat);
    update_option('xghome_reward_alipay', $reward_alipay);
    update_option('xghome_avatar_set', $avatar_set);
    update_option('xghome_anti_scrape_enabled', $anti_scrape_enabled);
    update_option('xghome_anti_scrape_disable_right_click', $anti_scrape_disable_right_click);
    update_option('xghome_anti_scrape_disable_shortcuts', $anti_scrape_disable_shortcuts);
    update_option('xghome_anti_scrape_append_copy', $anti_scrape_append_copy);
    update_option('xghome_layout_swap', $layout_swap);
    update_option('xghome_left_avatar_size', max(40, min(120, $left_avatar_size)));

    global $wp_rewrite;
    $wp_rewrite->set_permalink_structure($target);
    $wp_rewrite->flush_rules(true);

    $warning = '';
    if ($page_html === 1 && in_array($target, ['/%postname%.html'], true)) {
        $warning = 'page_post_conflict';
    }

    wp_safe_redirect(
        add_query_arg(
            [
                'page' => 'xghome-permalink',
                'updated' => '1',
                'warning' => $warning,
            ],
            admin_url('themes.php')
        )
    );
    exit;
}
add_action('admin_init', 'xghome_classic_handle_permalink_save');

function xghome_classic_handle_records_save(): void
{
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }

    if (!isset($_POST['xghome_records_nonce'])) {
        return;
    }

    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['xghome_records_nonce'])), 'xghome_save_records')) {
        return;
    }

    $icp_html = isset($_POST['xghome_icp_record_html'])
        ? wp_kses(wp_unslash($_POST['xghome_icp_record_html']), xghome_classic_record_allowed_html())
        : (string) get_option('xghome_icp_record_html', '');
    $public_html = isset($_POST['xghome_public_security_record_html'])
        ? wp_kses(wp_unslash($_POST['xghome_public_security_record_html']), xghome_classic_record_allowed_html())
        : (string) get_option('xghome_public_security_record_html', '');
    $domains = isset($_POST['xghome_record_domain']) ? (array) wp_unslash($_POST['xghome_record_domain']) : [];
    $icp_by_domain = isset($_POST['xghome_record_icp_html']) ? (array) wp_unslash($_POST['xghome_record_icp_html']) : [];
    $public_by_domain = isset($_POST['xghome_record_public_html']) ? (array) wp_unslash($_POST['xghome_record_public_html']) : [];
    $domain_rules = [];
    $rule_count = max(count($domains), count($icp_by_domain), count($public_by_domain));

    for ($i = 0; $i < $rule_count; $i++) {
        $domain = isset($domains[$i]) ? strtolower(trim(sanitize_text_field($domains[$i]))) : '';
        $domain = preg_replace('/:\d+$/', '', $domain);
        if ($domain === '') {
            continue;
        }
        if (!preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9-]{2,63}$/i', $domain)) {
            continue;
        }

        $domain_rules[] = [
            'domain' => $domain,
            'icp_html' => isset($icp_by_domain[$i]) ? wp_kses($icp_by_domain[$i], xghome_classic_record_allowed_html()) : '',
            'public_html' => isset($public_by_domain[$i]) ? wp_kses($public_by_domain[$i], xghome_classic_record_allowed_html()) : '',
        ];
    }

    update_option('xghome_icp_record_html', $icp_html);
    update_option('xghome_public_security_record_html', $public_html);
    update_option('xghome_domain_record_rules', $domain_rules);

    wp_safe_redirect(
        add_query_arg(
            [
                'page' => 'xghome-records',
                'updated' => '1',
            ],
            admin_url('themes.php')
        )
    );
    exit;
}
add_action('admin_init', 'xghome_classic_handle_records_save');

function xghome_classic_handle_analytics_save(): void
{
    if (!is_admin() || !current_user_can('manage_options')) {
        return;
    }
    if (!isset($_POST['xghome_analytics_nonce'])) {
        return;
    }
    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['xghome_analytics_nonce'])), 'xghome_save_analytics')) {
        return;
    }

    $head_code = isset($_POST['xghome_analytics_head_code'])
        ? wp_kses(wp_unslash($_POST['xghome_analytics_head_code']), xghome_classic_analytics_allowed_html())
        : (string) get_option('xghome_analytics_head_code', '');
    $footer_code = isset($_POST['xghome_analytics_footer_code'])
        ? wp_kses(wp_unslash($_POST['xghome_analytics_footer_code']), xghome_classic_analytics_allowed_html())
        : (string) get_option('xghome_analytics_footer_code', '');

    update_option('xghome_analytics_head_code', $head_code);
    update_option('xghome_analytics_footer_code', $footer_code);

    wp_safe_redirect(
        add_query_arg(
            [
                'page' => 'xghome-analytics',
                'updated' => '1',
            ],
            admin_url('themes.php')
        )
    );
    exit;
}
add_action('admin_init', 'xghome_classic_handle_analytics_save');

function xghome_classic_render_analytics_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $head_code = (string) get_option('xghome_analytics_head_code', '');
    $footer_code = (string) get_option('xghome_analytics_footer_code', '');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('统计代码管理', 'xghome-classic'); ?></h1>
        <p><?php esc_html_e('可粘贴网站统计 HTML/JS 代码，分别注入到页面头部和底部。', 'xghome-classic'); ?></p>
        <?php if (isset($_GET['updated'])) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('统计代码已保存。', 'xghome-classic'); ?></p></div>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field('xghome_save_analytics', 'xghome_analytics_nonce'); ?>
            <table class="form-table" role="presentation">
                <tbody>
                <tr>
                    <th scope="row"><label for="xghome_analytics_head_code">头部统计代码</label></th>
                    <td>
                        <textarea id="xghome_analytics_head_code" name="xghome_analytics_head_code" rows="8" class="large-text code"><?php echo esc_textarea($head_code); ?></textarea>
                        <p class="description">输出在 &lt;head&gt; 中，适合多数统计脚本。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="xghome_analytics_footer_code">底部统计代码</label></th>
                    <td>
                        <textarea id="xghome_analytics_footer_code" name="xghome_analytics_footer_code" rows="8" class="large-text code"><?php echo esc_textarea($footer_code); ?></textarea>
                        <p class="description">输出在页面底部，适合延迟加载的统计代码。</p>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php submit_button(__('保存统计代码', 'xghome-classic')); ?>
        </form>
    </div>
    <?php
}

function xghome_classic_print_analytics_head_code(): void
{
    if (is_admin()) {
        return;
    }
    $code = (string) get_option('xghome_analytics_head_code', '');
    if ($code !== '') {
        echo wp_kses($code, xghome_classic_analytics_allowed_html());
    }
}
add_action('wp_head', 'xghome_classic_print_analytics_head_code', 999);

function xghome_classic_print_analytics_footer_code(): void
{
    if (is_admin()) {
        return;
    }
    $code = (string) get_option('xghome_analytics_footer_code', '');
    if ($code !== '') {
        echo wp_kses($code, xghome_classic_analytics_allowed_html());
    }
}
add_action('wp_footer', 'xghome_classic_print_analytics_footer_code', 999);

function xghome_classic_current_host(): string
{
    $host = isset($_SERVER['HTTP_HOST']) ? (string) wp_unslash($_SERVER['HTTP_HOST']) : '';
    $host = strtolower(trim($host));
    return (string) preg_replace('/:\d+$/', '', $host);
}

function xghome_classic_record_html_for_current_host(): array
{
    $result = [
        'icp_html' => (string) get_option('xghome_icp_record_html', ''),
        'public_html' => (string) get_option('xghome_public_security_record_html', ''),
    ];
    $host = xghome_classic_current_host();
    if ($host === '') {
        return $result;
    }

    $rules = get_option('xghome_domain_record_rules', []);
    if (!is_array($rules)) {
        return $result;
    }

    foreach ($rules as $rule) {
        if (!is_array($rule) || !isset($rule['domain'])) {
            continue;
        }
        $rule_domain = strtolower(trim((string) $rule['domain']));
        if ($rule_domain === '' || $rule_domain !== $host) {
            continue;
        }
        return [
            'icp_html' => isset($rule['icp_html']) ? (string) $rule['icp_html'] : '',
            'public_html' => isset($rule['public_html']) ? (string) $rule['public_html'] : '',
        ];
    }

    return $result;
}

function xghome_classic_render_records_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $icp_html = (string) get_option('xghome_icp_record_html', '');
    $public_html = (string) get_option('xghome_public_security_record_html', '');
    $domain_rules = get_option('xghome_domain_record_rules', []);
    if (!is_array($domain_rules)) {
        $domain_rules = [];
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('备案管理', 'xghome-classic'); ?></h1>
        <p><?php esc_html_e('支持粘贴 HTML 备案代码，页脚将按图标位显示。', 'xghome-classic'); ?></p>
        <?php if (isset($_GET['updated'])) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('备案信息已保存。', 'xghome-classic'); ?></p></div>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field('xghome_save_records', 'xghome_records_nonce'); ?>
            <table class="form-table" role="presentation">
                <tbody>
                <tr>
                    <th scope="row"><label for="xghome_icp_record_html">ICP备案</label></th>
                    <td>
                        <textarea id="xghome_icp_record_html" name="xghome_icp_record_html" rows="4" class="large-text code"><?php echo esc_textarea($icp_html); ?></textarea>
                        <p class="description">示例：&lt;a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener"&gt;粤ICP备xxxx号&lt;/a&gt;</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="xghome_public_security_record_html">公安备案</label></th>
                    <td>
                        <textarea id="xghome_public_security_record_html" name="xghome_public_security_record_html" rows="4" class="large-text code"><?php echo esc_textarea($public_html); ?></textarea>
                        <p class="description">支持粘贴带图标的备案 HTML 代码。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">多域名备案规则</th>
                    <td>
                        <p class="description">用于独立域名精确匹配（例如：www.domain-a.com 与 www.domain-b.com）。未命中时使用上方默认备案。</p>
                        <table class="widefat striped" style="max-width: 1100px;">
                            <thead>
                            <tr>
                                <th style="width: 22%;">域名（不带协议）</th>
                                <th style="width: 39%;">ICP备案 HTML</th>
                                <th style="width: 39%;">公安备案 HTML</th>
                            </tr>
                            </thead>
                            <tbody id="xghome-record-rules-body">
                            <?php foreach ($domain_rules as $rule) : ?>
                                <tr>
                                    <td><input type="text" name="xghome_record_domain[]" class="regular-text code" value="<?php echo esc_attr((string) ($rule['domain'] ?? '')); ?>" placeholder="www.example.com"></td>
                                    <td><textarea name="xghome_record_icp_html[]" rows="3" class="large-text code"><?php echo esc_textarea((string) ($rule['icp_html'] ?? '')); ?></textarea></td>
                                    <td><textarea name="xghome_record_public_html[]" rows="3" class="large-text code"><?php echo esc_textarea((string) ($rule['public_html'] ?? '')); ?></textarea></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td><input type="text" name="xghome_record_domain[]" class="regular-text code" value="" placeholder="www.example.com"></td>
                                <td><textarea name="xghome_record_icp_html[]" rows="3" class="large-text code"></textarea></td>
                                <td><textarea name="xghome_record_public_html[]" rows="3" class="large-text code"></textarea></td>
                            </tr>
                            </tbody>
                        </table>
                        <p><button type="button" class="button" id="xghome-add-record-rule">新增一条域名规则</button></p>
                        <script>
                            (function () {
                                var addBtn = document.getElementById('xghome-add-record-rule');
                                var body = document.getElementById('xghome-record-rules-body');
                                if (!addBtn || !body) return;
                                addBtn.addEventListener('click', function () {
                                    var row = document.createElement('tr');
                                    row.innerHTML = '<td><input type="text" name="xghome_record_domain[]" class="regular-text code" placeholder="www.example.com"></td><td><textarea name="xghome_record_icp_html[]" rows="3" class="large-text code"></textarea></td><td><textarea name="xghome_record_public_html[]" rows="3" class="large-text code"></textarea></td>';
                                    body.appendChild(row);
                                });
                            })();
                        </script>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php submit_button(__('保存备案信息', 'xghome-classic')); ?>
        </form>
    </div>
    <?php
}

function xghome_classic_render_permalink_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $current = (string) get_option('permalink_structure', '');
    $presets = xghome_classic_get_permalink_presets();
    $options = xghome_classic_get_permalink_options();
    $avatar_sets = xghome_classic_get_avatar_sets();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Mylove 主题设置', 'xghome-classic'); ?></h1>
        <p><?php esc_html_e('用于控制文章页链接格式。发布新文章后将自动使用当前规则。', 'xghome-classic'); ?></p>

        <?php if (isset($_GET['updated'])) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e('伪静态规则已保存并刷新。', 'xghome-classic'); ?></p></div>
        <?php endif; ?>
        <?php if (isset($_GET['warning']) && sanitize_text_field(wp_unslash($_GET['warning'])) === 'page_post_conflict') : ?>
            <div class="notice notice-warning is-dismissible">
                <p>当前开启了“页面 .html”且文章规则为根目录 .html，可能发生页面/文章同名冲突。建议设置文章前缀，如 <code>/post/%postname%.html</code>。</p>
            </div>
        <?php endif; ?>

        <form method="post">
            <?php wp_nonce_field('xghome_save_permalink', 'xghome_permalink_nonce'); ?>
            <table class="form-table" role="presentation">
                <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e('文章链接格式', 'xghome-classic'); ?></th>
                    <td>
                        <?php foreach ($presets as $value => $label) : ?>
                            <p>
                                <label>
                                    <input type="radio" name="xghome_permalink_structure" value="<?php echo esc_attr($value); ?>" <?php checked($current, $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </label>
                            </p>
                        <?php endforeach; ?>
                        <p>
                            <label>
                                <input type="radio" name="xghome_permalink_structure" value="/%postname%.html" <?php checked($options['post_prefix'] !== ''); ?>>
                                自定义前缀 + .html
                            </label>
                        </p>
                        <p>
                            <label for="xghome_post_prefix">前缀（示例：post）</label><br>
                            <input id="xghome_post_prefix" type="text" name="xghome_post_prefix" value="<?php echo esc_attr($options['post_prefix']); ?>" class="regular-text" placeholder="post">
                            <span class="description">生成格式：/前缀/post-name.html</span>
                        </p>
                        <p class="description"><?php esc_html_e('说明：需服务器已启用 URL Rewrite（Apache/Nginx 伪静态）', 'xghome-classic'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('其他页面后缀', 'xghome-classic'); ?></th>
                    <td>
                        <p>
                            <label>
                                <input type="checkbox" name="xghome_category_html" value="1" <?php checked($options['category_html'], 1); ?>>
                                分类链接追加 .html（如 /category/news.html）
                            </label>
                        </p>
                        <p>
                            <label>
                                <input type="checkbox" name="xghome_tag_html" value="1" <?php checked($options['tag_html'], 1); ?>>
                                标签链接追加 .html（如 /tag/wordpress.html）
                            </label>
                        </p>
                        <p>
                            <label>
                                <input type="checkbox" name="xghome_page_html" value="1" <?php checked($options['page_html'], 1); ?>>
                                独立页面链接追加 .html（如 /about.html）
                            </label>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('界面增强开关', 'xghome-classic'); ?></th>
                    <td>
                        <p>
                            <label>
                                <input type="checkbox" name="xghome_top_enhanced" value="1" <?php checked($options['top_enhanced'], 1); ?>>
                                启用顶部增强面板（统计 / 时光机 / 登录）及移动端对应交互
                            </label>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">布局与侧栏</th>
                    <td>
                        <p>
                            <label>
                                <input type="checkbox" name="xghome_layout_swap" value="1" <?php checked($options['layout_swap'], 1); ?>>
                                左侧菜单（首页、相册、归档等）与右侧边栏（热门、标签云等）互换位置
                            </label>
                        </p>
                        <p class="description">默认：左侧为菜单，右侧为小工具。勾选后主栏仍在中间，仅左右两列对调。</p>
                        <p><label for="xghome_left_avatar_size">左侧 RSS 旁 Logo 圆圈大小（像素）</label></p>
                        <input id="xghome_left_avatar_size" type="number" min="40" max="120" step="1" name="xghome_left_avatar_size" value="<?php echo esc_attr((string) $options['left_avatar_size']); ?>" class="small-text">
                        <span class="description">正方形边长，样式强制宽与高相等并保持圆形，建议 40～120。</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">页脚「网站统计」链接</th>
                    <td>
                        <input id="xghome_footer_tongji_url" type="url" name="xghome_footer_tongji_url" value="<?php echo esc_attr($options['footer_tongji_url']); ?>" class="large-text code" placeholder="https://">
                        <p class="description">留空则不显示页脚「网站统计」。百度统计 / Google Analytics 等脚本请使用下方「统计代码」菜单注入。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('二维码接口列表', 'xghome-classic'); ?></th>
                    <td>
                        <p>每行一个二维码接口，按顺序自动故障切换。支持 <code>{url}</code> 占位符。留空则文章页不显示文章二维码区块。</p>
                        <textarea name="xghome_qrcode_apis" rows="6" class="large-text code"><?php echo esc_textarea($options['qrcode_apis']); ?></textarea>
                        <p class="description">示例：<code>https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={url}</code></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('评论校验正则', 'xghome-classic'); ?></th>
                    <td>
                        <p><label for="xghome_comment_email_regex">邮箱正则</label></p>
                        <input id="xghome_comment_email_regex" type="text" name="xghome_comment_email_regex" value="<?php echo esc_attr($options['comment_email_regex']); ?>" class="large-text code">
                        <p><label for="xghome_comment_phone_regex">手机号正则</label></p>
                        <input id="xghome_comment_phone_regex" type="text" name="xghome_comment_phone_regex" value="<?php echo esc_attr($options['comment_phone_regex']); ?>" class="large-text code">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('媒体尺寸控制', 'xghome-classic'); ?></th>
                    <td>
                        <p><label for="xghome_media_max_width">媒体最大宽度（%）</label></p>
                        <input id="xghome_media_max_width" type="number" min="30" max="100" name="xghome_media_max_width" value="<?php echo esc_attr((string) $options['media_max_width']); ?>" class="small-text">
                        <p><label for="xghome_image_max_height">图片最大高度（px）</label></p>
                        <input id="xghome_image_max_height" type="number" min="120" max="2000" name="xghome_image_max_height" value="<?php echo esc_attr((string) $options['image_max_height']); ?>" class="small-text">
                        <p><label for="xghome_video_max_height">视频最大高度（px）</label></p>
                        <input id="xghome_video_max_height" type="number" min="120" max="2000" name="xghome_video_max_height" value="<?php echo esc_attr((string) $options['video_max_height']); ?>" class="small-text">
                        <p><label for="xghome_audio_max_width">音频最大宽度（%）</label></p>
                        <input id="xghome_audio_max_width" type="number" min="30" max="100" name="xghome_audio_max_width" value="<?php echo esc_attr((string) $options['audio_max_width']); ?>" class="small-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('视频解析接口', 'xghome-classic'); ?></th>
                    <td>
                        <input id="xghome_video_parse_api" type="text" name="xghome_video_parse_api" value="<?php echo esc_attr($options['video_parse_api']); ?>" class="large-text code" placeholder="https://www.xxxx.com/video.php?video=">
                        <p class="description">输入接口后，插入视频时会自动拼接。可用 <code>{url}</code> 占位符，例如：<code>https://www.xxxx.com/video.php?video={url}</code></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('图片水印设置', 'xghome-classic'); ?></th>
                    <td>
                        <p><label for="xghome_watermark_image">水印图片地址（可选）</label></p>
                        <input id="xghome_watermark_image" type="text" name="xghome_watermark_image" value="<?php echo esc_attr($options['watermark_image']); ?>" class="large-text code" placeholder="https://example.com/watermark.png">
                        <p><label for="xghome_watermark_text">水印文字（可选）</label></p>
                        <input id="xghome_watermark_text" type="text" name="xghome_watermark_text" value="<?php echo esc_attr($options['watermark_text']); ?>" class="regular-text" placeholder="">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('默认缩略图', 'xghome-classic'); ?></th>
                    <td>
                        <p><label for="xghome_default_thumbnail">默认缩略图 URL（无特色图时使用）</label></p>
                        <input id="xghome_default_thumbnail" type="text" name="xghome_default_thumbnail" value="<?php echo esc_attr($options['default_thumbnail']); ?>" class="large-text code" placeholder="https://example.com/default-thumb.jpg">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('打赏设置', 'xghome-classic'); ?></th>
                    <td>
                        <p><label><input type="checkbox" name="xghome_reward_enabled" value="1" <?php checked($options['reward_enabled'], 1); ?>> 启用打赏模块</label></p>
                        <p><label for="xghome_reward_text">打赏提示文字</label></p>
                        <input id="xghome_reward_text" type="text" name="xghome_reward_text" value="<?php echo esc_attr($options['reward_text']); ?>" class="large-text">
                        <p><label for="xghome_reward_wechat">微信收款码图片地址</label></p>
                        <input id="xghome_reward_wechat" type="text" name="xghome_reward_wechat" value="<?php echo esc_attr($options['reward_wechat']); ?>" class="large-text code">
                        <p><label for="xghome_reward_alipay">支付宝收款码图片地址</label></p>
                        <input id="xghome_reward_alipay" type="text" name="xghome_reward_alipay" value="<?php echo esc_attr($options['reward_alipay']); ?>" class="large-text code">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('防内容采集', 'xghome-classic'); ?></th>
                    <td>
                        <p><label><input type="checkbox" name="xghome_anti_scrape_enabled" value="1" <?php checked($options['anti_scrape_enabled'], 1); ?>> 启用防采集</label></p>
                        <p><label><input type="checkbox" name="xghome_anti_scrape_disable_right_click" value="1" <?php checked($options['anti_scrape_disable_right_click'], 1); ?>> 禁用右键菜单</label></p>
                        <p><label><input type="checkbox" name="xghome_anti_scrape_disable_shortcuts" value="1" <?php checked($options['anti_scrape_disable_shortcuts'], 1); ?>> 禁用复制/开发者常见快捷键</label></p>
                        <p><label><input type="checkbox" name="xghome_anti_scrape_append_copy" value="1" <?php checked($options['anti_scrape_append_copy'], 1); ?>> 复制时自动追加来源链接</label></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('评论头像包', 'xghome-classic'); ?></th>
                    <td>
                        <select name="xghome_avatar_set">
                            <option value="">默认头像（Gravatar）</option>
                            <?php foreach ($avatar_sets as $set_name => $set_label) : ?>
                                <option value="<?php echo esc_attr($set_name); ?>" <?php selected($options['avatar_set'], $set_name); ?>>
                                    <?php echo esc_html($set_label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">头像目录位置：<code>/images/avatar/头像包名称/</code></p>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php submit_button(__('保存设置', 'xghome-classic')); ?>
        </form>
        <?php $samples = xghome_classic_rewrite_samples(); ?>
        <h2>伪静态服务器规则示例</h2>
        <p>若访问 404，请确认服务器已启用重写规则，可参考以下配置：</p>
        <h3>Nginx</h3>
        <pre><?php echo esc_html($samples['nginx']); ?></pre>
        <h3>Apache (.htaccess)</h3>
        <pre><?php echo esc_html($samples['apache']); ?></pre>
    </div>
    <?php
}

function xghome_classic_filter_category_link(string $term_link, $term): string
{
    if ((int) get_option('xghome_category_html', 0) !== 1) {
        return $term_link;
    }

    if (str_contains($term_link, '.html')) {
        return $term_link;
    }

    return untrailingslashit($term_link) . '.html';
}
add_filter('category_link', 'xghome_classic_filter_category_link', 10, 2);

function xghome_classic_filter_tag_link(string $term_link, $term): string
{
    if ((int) get_option('xghome_tag_html', 0) !== 1) {
        return $term_link;
    }

    if (str_contains($term_link, '.html')) {
        return $term_link;
    }

    return untrailingslashit($term_link) . '.html';
}
add_filter('tag_link', 'xghome_classic_filter_tag_link', 10, 2);

function xghome_classic_filter_page_link(string $link, int $post_id, bool $sample): string
{
    if ($sample || (int) get_option('xghome_page_html', 0) !== 1) {
        return $link;
    }

    if (str_contains($link, '.html')) {
        return $link;
    }

    return untrailingslashit($link) . '.html';
}
add_filter('page_link', 'xghome_classic_filter_page_link', 10, 3);

function xghome_classic_add_html_rewrite_rules(): void
{
    global $wp_rewrite;

    if ((int) get_option('xghome_category_html', 0) === 1) {
        $category_base = $wp_rewrite->category_base ? trim($wp_rewrite->category_base, '/') : 'category';
        add_rewrite_rule('^' . preg_quote($category_base, '/') . '/(.+?)\.html$', 'index.php?category_name=$matches[1]', 'top');
    }

    if ((int) get_option('xghome_tag_html', 0) === 1) {
        $tag_base = $wp_rewrite->tag_base ? trim($wp_rewrite->tag_base, '/') : 'tag';
        add_rewrite_rule('^' . preg_quote($tag_base, '/') . '/(.+?)\.html$', 'index.php?tag=$matches[1]', 'top');
    }

    if ((int) get_option('xghome_page_html', 0) === 1) {
        add_rewrite_rule('^(.+?)\.html$', 'index.php?pagename=$matches[1]', 'bottom');
    }
}
add_action('init', 'xghome_classic_add_html_rewrite_rules');

/**
 * 归档页链接：若已发布 slug 为 archives 的页面则用其固定链接；否则用虚拟路由（/archives/ 或 /archives.html）。
 */
function xghome_classic_get_archives_page_url(): string
{
    $page = get_page_by_path('archives', OBJECT, 'page');
    if ($page instanceof WP_Post && $page->post_status === 'publish') {
        return get_permalink($page);
    }

    if ((int) get_option('xghome_page_html', 0) === 1) {
        return home_url('/archives.html');
    }

    return home_url('/archives/');
}

function xghome_classic_archives_tree_query_vars(array $vars): array
{
    $vars[] = 'xghome_archives_tree';
    return $vars;
}
add_filter('query_vars', 'xghome_classic_archives_tree_query_vars');

function xghome_classic_register_archives_tree_rewrite(): void
{
    add_rewrite_rule('^archives/?$', 'index.php?xghome_archives_tree=1', 'top');
    add_rewrite_rule('^archives\.html$', 'index.php?xghome_archives_tree=1', 'top');
}
add_action('init', 'xghome_classic_register_archives_tree_rewrite', 5);

function xghome_classic_archives_tree_template(string $template): string
{
    if ((int) get_query_var('xghome_archives_tree') !== 1) {
        return $template;
    }

    $file = get_template_directory() . '/archive-tree-standalone.php';

    return is_readable($file) ? $file : $template;
}
add_filter('template_include', 'xghome_classic_archives_tree_template', 99);

/** 当前请求的 URI path（去掉站点子目录前缀），不含首尾斜杠。 */
function xghome_classic_get_request_path_after_home(): string
{
    $raw = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
    $path = wp_parse_url((string) $raw, PHP_URL_PATH);
    $path = $path ? trim((string) $path, '/') : '';

    $home_path = wp_parse_url(home_url('/'), PHP_URL_PATH);
    $home_path = $home_path ? trim((string) $home_path, '/') : '';
    if ($home_path !== '' && strpos($path, $home_path) === 0) {
        $path = trim(substr($path, strlen($home_path)), '/');
    }

    return $path;
}

function xghome_classic_archives_tree_404_fallback(): void
{
    if (is_admin() || !is_404()) {
        return;
    }

    $page = get_page_by_path('archives', OBJECT, 'page');
    if ($page instanceof WP_Post && $page->post_status === 'publish') {
        return;
    }

    $path = xghome_classic_get_request_path_after_home();

    $page_html = (int) get_option('xghome_page_html', 0) === 1;
    $match = ($path === 'archives');
    if (!$match && $page_html) {
        $match = ($path === 'archives.html');
    }

    if (!$match) {
        return;
    }

    status_header(200);
    nocache_headers();

    global $wp_query;
    $wp_query->is_404 = false;
    set_query_var('xghome_archives_tree', 1);

    include get_template_directory() . '/archive-tree-standalone.php';
    exit;
}
add_action('template_redirect', 'xghome_classic_archives_tree_404_fallback', 0);

/** 虚拟归档 / 相册页的浏览器标题（避免仍显示「页面未找到」）。 */
function xghome_classic_virtual_pages_document_title(array $title): array
{
    if ((int) get_query_var('xghome_archives_tree') === 1) {
        $title['title'] = __('文章归档', 'xghome-classic');
        return $title;
    }
    if ((int) get_query_var('xghome_album_page') === 1) {
        $title['title'] = __('相册', 'xghome-classic');
        return $title;
    }

    return $title;
}
add_filter('document_title_parts', 'xghome_classic_virtual_pages_document_title', 99);

function xghome_classic_album_page_query_vars(array $vars): array
{
    $vars[] = 'xghome_album_page';
    return $vars;
}
add_filter('query_vars', 'xghome_classic_album_page_query_vars');

function xghome_classic_register_album_page_rewrite(): void
{
    add_rewrite_rule('^album/?$', 'index.php?xghome_album_page=1', 'top');
    add_rewrite_rule('^album/page/([0-9]{1,8})/?$', 'index.php?xghome_album_page=1&paged=$matches[1]', 'top');
}
add_action('init', 'xghome_classic_register_album_page_rewrite', 5);

function xghome_classic_album_page_template(string $template): string
{
    if ((int) get_query_var('xghome_album_page') !== 1) {
        return $template;
    }

    $file = get_template_directory() . '/album-page-standalone.php';

    return is_readable($file) ? $file : $template;
}
add_filter('template_include', 'xghome_classic_album_page_template', 99);

/** 相册页固定链接（虚拟 /album/，无需先有 category/image 分类）。 */
function xghome_classic_get_album_page_url(): string
{
    return home_url('/album/');
}

/**
 * 相册：未建 image 分类时 /category/image/ 会 404；此处兜底与虚拟路由一致。
 */
function xghome_classic_album_page_404_fallback(): void
{
    if (is_admin() || !is_404()) {
        return;
    }

    $path = xghome_classic_get_request_path_after_home();
    $page_html = (int) get_option('xghome_page_html', 0) === 1;

    $paged = 1;
    $match_album = ($path === 'album');
    if (preg_match('#^album/page/(\d+)/?$#', $path, $m)) {
        $match_album = true;
        $paged = max(1, (int) $m[1]);
    }

    $match_legacy = ($path === 'category/image');
    if (!$match_legacy && $page_html) {
        $match_legacy = ($path === 'category/image.html');
    }

    if (!$match_album && !$match_legacy) {
        return;
    }

    if ($match_legacy) {
        $term = get_term_by('slug', 'image', 'category');
        if ($term && !is_wp_error($term)) {
            return;
        }
    }

    status_header(200);
    nocache_headers();

    global $wp_query;
    $wp_query->is_404 = false;

    set_query_var('xghome_album_page', 1);
    set_query_var('paged', $paged);

    include get_template_directory() . '/album-page-standalone.php';
    exit;
}
add_action('template_redirect', 'xghome_classic_album_page_404_fallback', 0);

function xghome_classic_flush_rewrite_rules_after_theme_switch(): void
{
    flush_rewrite_rules(false);
}
add_action('after_switch_theme', 'xghome_classic_flush_rewrite_rules_after_theme_switch');

function xghome_classic_rewrite_samples(): array
{
    return [
        'nginx' => "location / {\n    try_files \$uri \$uri/ /index.php?\$args;\n}\n",
        'apache' => "<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteBase /\nRewriteRule ^index\\.php$ - [L]\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule . /index.php [L]\n</IfModule>\n",
    ];
}

/**
 * 从媒体库随机取图片附件 ID（相册九宫格，全库图片范围内随机）。
 *
 * @return int[]
 */
function xghome_classic_album_random_attachment_ids(int $limit = 9): array
{
    $limit = max(1, min(9, $limit));
    $query = new WP_Query([
        'post_type'               => 'attachment',
        'post_mime_type'          => 'image',
        'post_status'             => 'inherit',
        'posts_per_page'          => $limit,
        'orderby'                 => 'rand',
        'fields'                  => 'ids',
        'no_found_rows'           => true,
        'update_post_meta_cache'  => false,
        'update_post_term_cache'  => false,
    ]);

    return array_map('intval', $query->posts ?? []);
}

/**
 * 文章归档树：按年 → 月分组，每条含标题与发布时间（用于归档页）。
 *
 * @return array<int, array{year:string,year_label:string,months:array<int, array{month:string,month_label:string,posts:array<int, array{title:string,url:string,datetime_display:string}>}>}>
 */
function xghome_classic_get_archives_year_month_tree(): array
{
    global $wpdb;

    $rows = $wpdb->get_results(
        "SELECT ID, post_title, post_date
         FROM {$wpdb->posts}
         WHERE post_type = 'post'
           AND post_status = 'publish'
         ORDER BY post_date DESC"
    );

    if (empty($rows)) {
        return [];
    }

    $nested = [];
    foreach ($rows as $row) {
        $year = mysql2date('Y', $row->post_date);
        $month = mysql2date('m', $row->post_date);
        if (!isset($nested[$year])) {
            $nested[$year] = [];
        }
        if (!isset($nested[$year][$month])) {
            $nested[$year][$month] = [];
        }

        $nested[$year][$month][] = [
            'title'             => $row->post_title,
            'url'               => get_permalink((int) $row->ID),
            'datetime_display'  => mysql2date('Y-m-d H:i', $row->post_date),
        ];
    }

    krsort($nested, SORT_NUMERIC);

    $out = [];
    foreach ($nested as $year => $months) {
        krsort($months, SORT_NUMERIC);
        $month_blocks = [];
        foreach ($months as $month => $posts) {
            $month_blocks[] = [
                'month'       => $month,
                'month_label' => (string) (int) $month . '月',
                'posts'       => $posts,
            ];
        }
        $out[] = [
            'year'       => $year,
            'year_label' => $year . '年',
            'months'     => $month_blocks,
        ];
    }

    return $out;
}

function xghome_classic_get_comment_leaderboard(int $limit = 10): array
{
    global $wpdb;
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT comment_author AS author, comment_author_url AS url, COUNT(comment_ID) AS total
             FROM {$wpdb->comments}
             WHERE comment_approved = '1'
               AND comment_author <> ''
             GROUP BY comment_author, comment_author_url
             ORDER BY total DESC
             LIMIT %d",
            $limit
        ),
        ARRAY_A
    );

    return is_array($rows) ? $rows : [];
}

function xghome_classic_month_post_counts(int $months = 10): array
{
    global $wpdb;
    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT DATE_FORMAT(post_date, '%%Y-%%m') AS ym, COUNT(ID) AS total
             FROM {$wpdb->posts}
             WHERE post_type = 'post' AND post_status = 'publish'
             GROUP BY ym
             ORDER BY ym DESC
             LIMIT %d",
            $months
        ),
        ARRAY_A
    );

    return is_array($rows) ? $rows : [];
}

function xghome_classic_top_terms(string $taxonomy, int $limit = 5): array
{
    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => true,
        'number'     => $limit,
        'orderby'    => 'count',
        'order'      => 'DESC',
    ]);

    if (is_wp_error($terms) || empty($terms)) {
        return [];
    }

    return $terms;
}

function xghome_classic_left_nav_categories(int $limit = 100): array
{
    $terms = get_categories([
        'taxonomy'   => 'category',
        'hide_empty' => true,
        'number'     => max(1, $limit),
        'orderby'    => 'count',
        'order'      => 'DESC',
        'parent'     => 0,
    ]);

    if (is_wp_error($terms) || empty($terms)) {
        return [];
    }

    return $terms;
}

function xghome_classic_get_emoji_sets(): array
{
    $base_dir = trailingslashit(get_template_directory()) . 'images/emoji';
    $base_uri = trailingslashit(get_template_directory_uri()) . 'images/emoji';

    if (!is_dir($base_dir)) {
        return [];
    }

    $sets = [];
    $dirs = glob($base_dir . '/*', GLOB_ONLYDIR);
    if ($dirs === false) {
        return [];
    }

    foreach ($dirs as $dir) {
        $set_name = basename($dir);
        $images = glob($dir . '/*.{png,jpg,jpeg,gif,webp,svg}', GLOB_BRACE);
        if ($images === false || empty($images)) {
            continue;
        }

        $items = [];
        foreach ($images as $img) {
            $items[] = [
                'name' => pathinfo($img, PATHINFO_FILENAME),
                'url'  => trailingslashit($base_uri) . rawurlencode($set_name) . '/' . rawurlencode(basename($img)),
            ];
        }

        $sets[] = [
            'name'  => $set_name,
            'items' => $items,
        ];
    }

    return $sets;
}

function xghome_classic_render_emoji_picker(string $target_selector = '#comment'): void
{
    $emoji_sets = xghome_classic_get_emoji_sets();
    if (empty($emoji_sets)) {
        return;
    }
    ?>
    <div class="emoji-picker-wrap" data-emoji-target="<?php echo esc_attr($target_selector); ?>">
        <div class="emoji-set-tabs">
            <?php foreach ($emoji_sets as $index => $set) : ?>
                <button type="button" class="emoji-set-tab<?php echo $index === 0 ? ' is-active' : ''; ?>" data-emoji-set="<?php echo esc_attr((string) $index); ?>">
                    <?php echo esc_html($set['name']); ?>
                </button>
            <?php endforeach; ?>
        </div>
        <?php foreach ($emoji_sets as $index => $set) : ?>
            <div class="emoji-set-panel<?php echo $index === 0 ? ' is-active' : ''; ?>" data-emoji-panel="<?php echo esc_attr((string) $index); ?>">
                <?php foreach ($set['items'] as $emoji) : ?>
                    <button type="button" class="emoji-item-btn" data-emoji-insert="<?php echo esc_attr('<img src="' . esc_url($emoji['url']) . '" alt="' . esc_attr($emoji['name']) . '">'); ?>">
                        <img src="<?php echo esc_url($emoji['url']); ?>" alt="<?php echo esc_attr($emoji['name']); ?>">
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

function xghome_classic_enqueue_admin_assets(string $hook): void
{
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }

    wp_enqueue_style(
        'xghome-theme-admin',
        get_template_directory_uri() . '/assets/css/theme.css',
        [],
        '1.0.0'
    );
    wp_enqueue_script(
        'xghome-theme-admin',
        get_template_directory_uri() . '/assets/js/theme.js',
        ['jquery'],
        '1.0.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'xghome_classic_enqueue_admin_assets');

function xghome_classic_render_editor_emoji_picker(WP_Post $post): void
{
    if ($post->post_type !== 'post') {
        return;
    }

    echo '<div class="xghome-editor-emoji-box"><p><strong>文章表情</strong>（点击插入到编辑器）</p>';
    xghome_classic_render_emoji_picker('#content');
    echo '</div>';
}
add_action('edit_form_after_editor', 'xghome_classic_render_editor_emoji_picker');

function xghome_classic_qrcode_api_list(string $content_url): array
{
    $raw = (string) get_option('xghome_qrcode_apis', '');
    $lines = preg_split('/\r\n|\r|\n/', $raw);
    if (!is_array($lines)) {
        return [];
    }

    $result = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $result[] = str_replace('{url}', rawurlencode($content_url), $line);
    }
    return array_values(array_unique($result));
}

function xghome_classic_share_payload(int $post_id): array
{
    $url = get_permalink($post_id);
    $title = get_the_title($post_id);
    $summary = wp_trim_words(wp_strip_all_tags(get_post_field('post_excerpt', $post_id) ?: get_post_field('post_content', $post_id)), 40, '...');
    $pic = '';
    if (has_post_thumbnail($post_id)) {
        $pic = (string) get_the_post_thumbnail_url($post_id, 'large');
    }
    if ($pic === '') {
        $content = (string) get_post_field('post_content', $post_id);
        if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m) === 1) {
            $pic = (string) $m[1];
        }
    }
    if ($pic === '') {
        $pic = xghome_classic_icon_uri('image');
    }
    return [
        'url' => $url,
        'title' => $title,
        'summary' => $summary,
        'pic' => $pic,
    ];
}

function xghome_classic_get_comment_regex(): array
{
    $email = (string) get_option('xghome_comment_email_regex', '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$');
    $phone = (string) get_option('xghome_comment_phone_regex', '^1[3-9]\d{9}$');
    return ['email' => $email, 'phone' => $phone];
}

function xghome_classic_safe_regex_pattern(string $raw, string $fallback): string
{
    $raw = trim($raw);
    if ($raw === '' || strlen($raw) > 200) {
        return $fallback;
    }

    $pattern = '/' . str_replace('/', '\/', $raw) . '/u';
    if (@preg_match($pattern, '') === false) {
        return $fallback;
    }

    return $raw;
}

function xghome_classic_safe_set_comment_cookie(string $name, string $value): void
{
    $expire = time() + MONTH_IN_SECONDS;
    $secure = is_ssl();
    if (PHP_VERSION_ID >= 70300) {
        setcookie($name, $value, [
            'expires'  => $expire,
            'path'     => COOKIEPATH,
            'domain'   => COOKIE_DOMAIN,
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        return;
    }

    setcookie($name, $value, $expire, COOKIEPATH . '; samesite=Lax', COOKIE_DOMAIN, $secure, true);
}

function xghome_classic_comment_form_fields(array $fields): array
{
    $commenter = wp_get_current_commenter();
    $author_value = isset($commenter['comment_author']) ? (string) $commenter['comment_author'] : '';
    $contact_value = isset($_COOKIE['comment_xghome_contact']) ? sanitize_text_field(wp_unslash($_COOKIE['comment_xghome_contact'])) : '';
    $url_value = isset($commenter['comment_author_url']) ? (string) $commenter['comment_author_url'] : '';
    $fields['author'] = '<p class="comment-form-author"><label for="author">显示名称 <span class="required">*</span></label><input id="author" name="author" type="text" value="' . esc_attr($author_value) . '" size="30" maxlength="245" autocomplete="name" required></p>';
    $fields['email'] = '<p class="comment-form-email"><label for="email">联系方式 <span class="required">*</span></label><input id="email" name="email" type="text" value="' . esc_attr($contact_value) . '" size="30" placeholder="邮箱或手机号" required></p>';
    $fields['url'] = '<p class="comment-form-url"><label for="url">您的网址</label><input id="url" name="url" type="url" value="' . esc_attr($url_value) . '" size="30" placeholder="https://example.com"></p>';
    return $fields;
}
add_filter('comment_form_default_fields', 'xghome_classic_comment_form_fields');

function xghome_classic_disable_require_name_email($value)
{
    return 0;
}
add_filter('pre_option_require_name_email', 'xghome_classic_disable_require_name_email');

function xghome_classic_validate_comment(array $commentdata): array
{
    $regex = xghome_classic_get_comment_regex();
    $email_pattern_raw = xghome_classic_safe_regex_pattern((string) $regex['email'], '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$');
    $phone_pattern_raw = xghome_classic_safe_regex_pattern((string) $regex['phone'], '^1[3-9]\d{9}$');
    $email_pattern = '/' . str_replace('/', '\/', $email_pattern_raw) . '/u';
    $phone_pattern = '/' . str_replace('/', '\/', $phone_pattern_raw) . '/u';
    $author = isset($_POST['author']) ? sanitize_text_field(wp_unslash($_POST['author'])) : '';
    $contact = isset($_POST['email']) ? sanitize_text_field(wp_unslash($_POST['email'])) : '';
    $email = '';
    $phone = '';

    if ($author === '') {
        wp_die('请输入显示名称');
    }

    if ($contact === '') {
        wp_die('请输入邮箱或手机号');
    }

    if (preg_match($email_pattern, $contact)) {
        $email = $contact;
    } elseif (preg_match($phone_pattern, $contact)) {
        $phone = $contact;
    } else {
        wp_die('格式不正确');
    }

    $commentdata['comment_author_email'] = $email;
    $_POST['xghome_phone'] = $phone;
    xghome_classic_safe_set_comment_cookie('comment_xghome_contact', $contact);

    return $commentdata;
}
add_filter('preprocess_comment', 'xghome_classic_validate_comment');

function xghome_classic_save_comment_phone(int $comment_id): void
{
    if (!isset($_POST['xghome_phone']) || $_POST['xghome_phone'] === '') {
        return;
    }
    $phone = sanitize_text_field(wp_unslash($_POST['xghome_phone']));
    if ($phone !== '') {
        add_comment_meta($comment_id, 'xghome_phone', $phone, true);
        xghome_classic_safe_set_comment_cookie('comment_xghome_phone', $phone);
    }
}
add_action('comment_post', 'xghome_classic_save_comment_phone');

function xghome_classic_print_media_style_vars(): void
{
    $media_width = max(30, min(100, (int) get_option('xghome_media_max_width', 100)));
    $image_height = max(120, min(2000, (int) get_option('xghome_image_max_height', 960)));
    $video_height = max(120, min(2000, (int) get_option('xghome_video_max_height', 680)));
    $audio_width = max(30, min(100, (int) get_option('xghome_audio_max_width', 100)));
    $avatar_px = xghome_classic_get_left_avatar_size();
    echo '<style>:root{--xghome-media-max-width:' . esc_html((string) $media_width) . '%;--xghome-image-max-height:' . esc_html((string) $image_height) . 'px;--xghome-video-max-height:' . esc_html((string) $video_height) . 'px;--xghome-audio-max-width:' . esc_html((string) $audio_width) . '%;--xghome-left-avatar-size:' . esc_html((string) $avatar_px) . 'px;}</style>';
}
add_action('wp_head', 'xghome_classic_print_media_style_vars', 99);

function xghome_classic_print_watermark_vars(): void
{
    $img = (string) get_option('xghome_watermark_image', '');
    $text = (string) get_option('xghome_watermark_text', '');
    echo '<script>window.XGHOME_WATERMARK=' . wp_json_encode(['image' => $img, 'text' => $text]) . ';</script>';
}
add_action('wp_head', 'xghome_classic_print_watermark_vars', 100);

function xghome_classic_md_inline(string $text): string
{
    $text = preg_replace('/\[(.+?)\]\((https?:\/\/[^\s)]+)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $text);
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    return $text ?? '';
}

function xghome_classic_markdown_to_html(string $markdown): string
{
    $markdown = str_replace(["\r\n", "\r"], "\n", $markdown);
    $code_blocks = [];
    $markdown = preg_replace_callback('/```([a-zA-Z0-9_-]*)\n([\s\S]*?)```/m', function ($matches) use (&$code_blocks) {
        $lang = isset($matches[1]) ? trim((string) $matches[1]) : '';
        $code = htmlspecialchars((string) ($matches[2] ?? ''), ENT_QUOTES, 'UTF-8');
        $token = '__XGHOME_CODE_BLOCK_' . count($code_blocks) . '__';
        $class = $lang !== '' ? ' class="language-' . $lang . '"' : '';
        $code_blocks[$token] = '<pre><code' . $class . '>' . $code . '</code></pre>';
        return $token;
    }, $markdown);
    $markdown = htmlspecialchars($markdown, ENT_QUOTES, 'UTF-8');
    $blocks = preg_split("/\n{2,}/", trim((string) $markdown));
    if (!is_array($blocks)) {
        return '';
    }

    $html = [];
    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        if (isset($code_blocks[$block])) {
            $html[] = $code_blocks[$block];
            continue;
        }
        if (preg_match('/^(#{1,6})\s+(.+)$/', $block, $h)) {
            $level = strlen((string) $h[1]);
            $html[] = '<h' . $level . '>' . xghome_classic_md_inline((string) $h[2]) . '</h' . $level . '>';
            continue;
        }
        if (preg_match('/^>\s?/m', $block)) {
            $quote = preg_replace('/^>\s?/m', '', $block);
            $html[] = '<blockquote>' . nl2br(xghome_classic_md_inline((string) $quote)) . '</blockquote>';
            continue;
        }
        $lines = explode("\n", $block);
        $is_ul = true;
        $is_ol = true;
        foreach ($lines as $line) {
            if (!preg_match('/^\s*[-*]\s+.+$/', $line)) {
                $is_ul = false;
            }
            if (!preg_match('/^\s*\d+\.\s+.+$/', $line)) {
                $is_ol = false;
            }
        }
        if ($is_ul) {
            $items = array_map(static fn($line) => '<li>' . xghome_classic_md_inline((string) preg_replace('/^\s*[-*]\s+/', '', $line)) . '</li>', $lines);
            $html[] = '<ul>' . implode('', $items) . '</ul>';
            continue;
        }
        if ($is_ol) {
            $items = array_map(static fn($line) => '<li>' . xghome_classic_md_inline((string) preg_replace('/^\s*\d+\.\s+/', '', $line)) . '</li>', $lines);
            $html[] = '<ol>' . implode('', $items) . '</ol>';
            continue;
        }
        $html[] = '<p>' . nl2br(xghome_classic_md_inline($block)) . '</p>';
    }

    $output = implode("\n", $html);
    foreach ($code_blocks as $token => $block_html) {
        $output = str_replace(htmlspecialchars($token, ENT_QUOTES, 'UTF-8'), $block_html, $output);
    }
    return $output;
}

function xghome_classic_markdown_enabled(int $post_id): bool
{
    return (int) get_post_meta($post_id, '_xghome_markdown_enabled', true) === 1;
}

function xghome_classic_render_post_content(int $post_id): void
{
    if (xghome_classic_markdown_enabled($post_id)) {
        $raw = (string) get_post_field('post_content', $post_id);
        echo wp_kses_post(xghome_classic_markdown_to_html($raw));
        return;
    }
    the_content();
}

function xghome_classic_markdown_box(WP_Post $post): void
{
    wp_nonce_field('xghome_markdown_box', 'xghome_markdown_nonce');
    $checked = xghome_classic_markdown_enabled((int) $post->ID);
    echo '<label><input type="checkbox" name="xghome_markdown_enabled" value="1" ' . checked($checked, true, false) . '> 启用 Markdown 渲染</label>';
    echo '<p>启用后，该文章内容按 Markdown 语法解析。</p>';
}

function xghome_classic_add_markdown_metabox(): void
{
    add_meta_box('xghome-markdown-box', 'Markdown 模式', 'xghome_classic_markdown_box', ['post', 'page'], 'side');
}
add_action('add_meta_boxes', 'xghome_classic_add_markdown_metabox');

function xghome_classic_save_markdown_metabox(int $post_id): void
{
    if (!isset($_POST['xghome_markdown_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['xghome_markdown_nonce'])), 'xghome_markdown_box')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    update_post_meta($post_id, '_xghome_markdown_enabled', isset($_POST['xghome_markdown_enabled']) ? 1 : 0);
}
add_action('save_post', 'xghome_classic_save_markdown_metabox');

function xghome_classic_admin_markdown_helper(): void
{
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->base, ['post', 'post-new'], true)) {
        return;
    }
    $video_parse_api = (string) get_option('xghome_video_parse_api', '');
    ?>
    <script>
    (function () {
        var videoParseApi = <?php echo wp_json_encode($video_parse_api); ?> || '';

        function buildVideoUrl(rawUrl) {
            if (!videoParseApi) {
                return rawUrl;
            }
            if (videoParseApi.indexOf('{url}') !== -1) {
                return videoParseApi.replace('{url}', encodeURIComponent(rawUrl));
            }
            return videoParseApi + encodeURIComponent(rawUrl);
        }

        function insertVideo() {
            var url = window.prompt('请输入视频地址（mp4 或 iframe 链接）');
            if (!url) return;
            url = url.trim();
            if (!url) return;
            var html = '';
            if (url.indexOf('<iframe') === 0) {
                html = url;
            } else {
                html = '<video controls preload="metadata" src="' + buildVideoUrl(url).replace(/"/g, '&quot;') + '"></video>';
            }
            if (window.tinymce && window.tinymce.get('content') && !window.tinymce.get('content').isHidden()) {
                window.tinymce.get('content').execCommand('mceInsertContent', false, html);
                return;
            }
            var el = document.getElementById('content');
            if (!el) return;
            el.value += "\n" + html + "\n";
        }

        function insertMarkdownTemplate() {
            var tpl = "# 标题\n\n正文内容\n\n```php\necho 'hello';\n```\n";
            if (window.tinymce && window.tinymce.get('content') && !window.tinymce.get('content').isHidden()) {
                window.tinymce.get('content').execCommand('mceInsertContent', false, tpl.replace(/\n/g, '<br>'));
                return;
            }
            var el = document.getElementById('content');
            if (!el) return;
            if (!el.value.trim()) {
                el.value = tpl;
                return;
            }
            el.value += "\n" + tpl;
        }

        var target = document.getElementById('poststuff');
        if (!target || document.getElementById('xghome-md-helper')) return;
        var wrap = document.createElement('div');
        wrap.id = 'xghome-md-helper';
        wrap.style.margin = '8px 0';
        wrap.innerHTML = '<button type="button" class="button" id="xghome-video-insert">插入视频</button> <button type="button" class="button" id="xghome-md-insert">插入 Markdown 模板</button> <span style="color:#666">勾选右侧“Markdown 模式”后生效</span>';
        target.insertBefore(wrap, target.firstChild);
        document.getElementById('xghome-video-insert').addEventListener('click', insertVideo);
        document.getElementById('xghome-md-insert').addEventListener('click', insertMarkdownTemplate);
    })();
    </script>
    <?php
}
add_action('admin_footer', 'xghome_classic_admin_markdown_helper');

function xghome_classic_reward_config(): array
{
    return [
        'enabled' => (int) get_option('xghome_reward_enabled', 0) === 1,
        'text' => (string) get_option('xghome_reward_text', '如果这篇文章对你有帮助，欢迎赞赏支持。'),
        'wechat' => (string) get_option('xghome_reward_wechat', ''),
        'alipay' => (string) get_option('xghome_reward_alipay', ''),
    ];
}

function xghome_classic_anti_scrape_config(): array
{
    return [
        'enabled' => (int) get_option('xghome_anti_scrape_enabled', 0) === 1,
        'disableRightClick' => (int) get_option('xghome_anti_scrape_disable_right_click', 1) === 1,
        'disableShortcuts' => (int) get_option('xghome_anti_scrape_disable_shortcuts', 1) === 1,
        'appendCopy' => (int) get_option('xghome_anti_scrape_append_copy', 1) === 1,
        'siteName' => get_bloginfo('name'),
        'siteUrl' => home_url('/'),
    ];
}

function xghome_classic_print_anti_scrape_vars(): void
{
    echo '<script>window.XGHOME_ANTI_SCRAPE=' . wp_json_encode(xghome_classic_anti_scrape_config()) . ';</script>';
}
add_action('wp_head', 'xghome_classic_print_anti_scrape_vars', 101);

function xghome_classic_get_avatar_sets(): array
{
    $base_dir = trailingslashit(get_template_directory()) . 'images/avatar';
    if (!is_dir($base_dir)) {
        return [];
    }

    $dirs = glob($base_dir . '/*', GLOB_ONLYDIR);
    if ($dirs === false) {
        return [];
    }

    $sets = [];
    foreach ($dirs as $dir) {
        $name = basename($dir);
        $images = glob($dir . '/*.{png,jpg,jpeg,gif,webp,svg}', GLOB_BRACE);
        if ($images === false || empty($images)) {
            continue;
        }
        $sets[$name] = $name;
    }
    return $sets;
}

function xghome_classic_avatar_pool(string $set_name): array
{
    $set_name = trim($set_name);
    if ($set_name === '') {
        return [];
    }
    $base_dir = trailingslashit(get_template_directory()) . 'images/avatar/' . $set_name;
    $base_uri = trailingslashit(get_template_directory_uri()) . 'images/avatar/' . rawurlencode($set_name);
    if (!is_dir($base_dir)) {
        return [];
    }
    $images = glob($base_dir . '/*.{png,jpg,jpeg,gif,webp,svg}', GLOB_BRACE);
    if ($images === false || empty($images)) {
        return [];
    }
    $urls = [];
    foreach ($images as $img) {
        $urls[] = trailingslashit($base_uri) . rawurlencode(basename($img));
    }
    return $urls;
}

function xghome_classic_pick_avatar_url(string $seed): string
{
    $set_name = (string) get_option('xghome_avatar_set', '');
    if ($set_name === '') {
        return '';
    }
    $pool = xghome_classic_avatar_pool($set_name);
    if (empty($pool)) {
        return '';
    }
    $idx = abs(crc32($seed)) % count($pool);
    return (string) $pool[$idx];
}

function xghome_classic_avatar_seed($id_or_email): string
{
    $seed = '';
    if (is_object($id_or_email) && isset($id_or_email->comment_author_email)) {
        $seed = (string) $id_or_email->comment_author_email;
        if ($seed === '' && isset($id_or_email->comment_author)) {
            $seed = (string) $id_or_email->comment_author;
        }
        if ($seed === '' && isset($id_or_email->comment_ID)) {
            $seed = 'comment-' . (string) $id_or_email->comment_ID;
        }
    } elseif (is_object($id_or_email) && isset($id_or_email->user_id)) {
        $seed = 'user-' . (string) $id_or_email->user_id;
    } elseif (is_string($id_or_email)) {
        $seed = $id_or_email;
    } elseif (is_numeric($id_or_email)) {
        $seed = (string) $id_or_email;
    }

    return trim($seed);
}

function xghome_classic_override_comment_avatar(string $avatar, $id_or_email, int $size, string $default, string $alt, array $args): string
{
    if (isset($args['force_display']) && $args['force_display'] === false) {
        return $avatar;
    }

    $seed = xghome_classic_avatar_seed($id_or_email);
    if ($seed === '') {
        return $avatar;
    }

    $selected_set = (string) get_option('xghome_avatar_set', '');
    if ($selected_set === '') {
        return $avatar;
    }

    $url = xghome_classic_pick_avatar_url($seed);
    if ($url === '') {
        return $avatar;
    }

    $class = isset($args['class']) ? (array) $args['class'] : ['avatar', 'avatar-' . $size, 'photo'];
    return '<img alt="' . esc_attr($alt) . '" src="' . esc_url($url) . '" class="' . esc_attr(implode(' ', $class)) . '" height="' . (int) $size . '" width="' . (int) $size . '" loading="lazy" decoding="async">';
}
add_filter('get_avatar', 'xghome_classic_override_comment_avatar', 10, 6);

function xghome_classic_override_avatar_url(string $url, $id_or_email, array $args): string
{
    $selected_set = (string) get_option('xghome_avatar_set', '');
    if ($selected_set === '') {
        return $url;
    }

    $seed = xghome_classic_avatar_seed($id_or_email);
    if ($seed === '') {
        return $url;
    }

    $picked = xghome_classic_pick_avatar_url($seed);
    if ($picked === '') {
        return $url;
    }

    return $picked;
}
add_filter('get_avatar_url', 'xghome_classic_override_avatar_url', 10, 3);

/** Memorial dates: full-page grayscale (9·18 / 国家公祭日). Festival lanterns removed. */
if (!function_exists('xghome_classic_memorial_grayscale_style')) {
    function xghome_classic_memorial_grayscale_style(): string
    {
        return <<<Eof
<style>
html{
    -webkit-filter: grayscale(100%);
    -moz-filter: grayscale(100%);
    -ms-filter: grayscale(100%);
    -o-filter: grayscale(100%);
    filter: grayscale(100%);
    filter: gray;
}
</style>
Eof;
    }
}

if (!function_exists('xghome_classic_memorial_grayscale_boot')) {
    function xghome_classic_memorial_grayscale_boot(): void
    {
        if (is_admin()) {
            return;
        }
        $di = date('n-d');
        if ($di === '12-13' || $di === '9-18') {
            echo xghome_classic_memorial_grayscale_style(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }
    add_action('wp_head', 'xghome_classic_memorial_grayscale_boot', 2);
}
