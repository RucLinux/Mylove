<?php
/**
 * 网站地图：后台一键合并写入根目录 sitemap.xml。
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}

function xghome_classic_sitemap_esc_xml(string $s): string
{
    return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function xghome_classic_sitemap_public_url(): string
{
    return home_url('/sitemap.xml');
}

function xghome_classic_sitemap_canonical_base(): string
{
    return untrailingslashit(home_url('/'));
}

function xghome_classic_sitemap_canonicalize_url(string $url, string $canonical_base): string
{
    $canonical_base = untrailingslashit($canonical_base);
    $scheme = (string) (wp_parse_url($canonical_base, PHP_URL_SCHEME) ?: 'https');
    $host  = (string) (wp_parse_url($canonical_base, PHP_URL_HOST) ?: '');
    if ($host === '') {
        return $url;
    }
    $port = wp_parse_url($canonical_base, PHP_URL_PORT);
    $port_str = $port ? ':' . (int) $port : '';
    $path = wp_parse_url($url, PHP_URL_PATH);
    $path = ($path === '' || $path === null) ? '/' : $path;
    $query = wp_parse_url($url, PHP_URL_QUERY);
    $frag  = wp_parse_url($url, PHP_URL_FRAGMENT);
    $q = $query !== null && $query !== '' ? '?' . $query : '';
    $f = $frag !== null && $frag !== '' ? '#' . $frag : '';

    return $scheme . '://' . $host . $port_str . $path . $q . $f;
}

/**
 * @return list<string>
 */
function xghome_classic_sitemap_collect_public_urls(string $canonical_base): array
{
    $canonical_base = untrailingslashit($canonical_base);
    $urls = [user_trailingslashit($canonical_base . '/')];

    $q = new WP_Query([
        'post_type'           => ['post', 'page'],
        'post_status'         => 'publish',
        'posts_per_page'      => -1,
        'fields'              => 'ids',
        'no_found_rows'       => true,
        'ignore_sticky_posts' => true,
    ]);
    foreach ($q->posts as $pid) {
        $pid = (int) $pid;
        $raw = get_permalink($pid);
        if (!$raw) {
            continue;
        }
        $urls[] = xghome_classic_sitemap_canonicalize_url($raw, $canonical_base);
    }
    $urls = array_values(array_unique(array_filter($urls)));

    sort($urls, SORT_STRING);

    return $urls;
}

/**
 * @return array{0: array<string, true>, 1: list<string>}
 */
function xghome_classic_sitemap_parse_xml_locs(string $path): array
{
    if (!is_readable($path)) {
        return [[], []];
    }
    $body = (string) file_get_contents($path);
    if ($body === '') {
        return [[], []];
    }
    $set = [];
    $order = [];
    if (preg_match_all('#<loc>\s*([^<]+?)\s*</loc>#i', $body, $m)) {
        foreach ($m[1] as $loc) {
            $u = trim(html_entity_decode($loc, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($u === '') {
                continue;
            }
            if (!isset($set[$u])) {
                $set[$u] = true;
                $order[] = $u;
            }
        }
    }

    return [$set, $order];
}

function xghome_classic_sitemap_lastmod_for_url(string $url): string
{
    $id = url_to_postid($url);
    if ($id <= 0) {
        $home = untrailingslashit(home_url());
        $alt = preg_replace('#^https?://[^/]+#', $home, $url);
        if ($alt !== $url) {
            $id = url_to_postid($alt);
        }
    }
    if ($id <= 0) {
        return '';
    }
    $t = get_post_modified_time('Y-m-d\TH:i:s+00:00', true, $id);
    return is_string($t) ? $t : '';
}

function xghome_classic_sitemap_build_xml(array $urls): string
{
    $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $out .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $u) {
        $loc = xghome_classic_sitemap_esc_xml($u);
        $out .= '  <url><loc>' . $loc . '</loc>';
        $lm = xghome_classic_sitemap_lastmod_for_url($u);
        if ($lm !== '') {
            $out .= '<lastmod>' . xghome_classic_sitemap_esc_xml($lm) . '</lastmod>';
        }
        $out .= "</url>\n";
    }
    $out .= '</urlset>' . "\n";

    return $out;
}

/**
 * @return array{xml_added: int, xml_path: string}|WP_Error
 */
function xghome_classic_sitemap_run_merge(): array
{
    if (!current_user_can('manage_options')) {
        return new WP_Error('forbidden', 'Permission denied');
    }
    $canonical = xghome_classic_sitemap_canonical_base();
    $new_urls = xghome_classic_sitemap_collect_public_urls($canonical);

    $xml_path = ABSPATH . 'sitemap.xml';

    [$xml_set, $xml_order] = xghome_classic_sitemap_parse_xml_locs($xml_path);
    $merged_xml_set = $xml_set;
    $merged_xml_order = $xml_order;
    $xml_added = 0;
    foreach ($new_urls as $u) {
        if (!isset($merged_xml_set[$u])) {
            $merged_xml_set[$u] = true;
            $merged_xml_order[] = $u;
            $xml_added++;
        }
    }
    $xml_body = xghome_classic_sitemap_build_xml($merged_xml_order);

    if (!is_writable(ABSPATH)) {
        return new WP_Error('nowrite', '站点根目录不可写，无法写入 sitemap.xml。');
    }

    if (file_put_contents($xml_path, $xml_body, LOCK_EX) === false) {
        return new WP_Error('xmlwrite', '写入失败：sitemap.xml');
    }
    @chmod($xml_path, 0644);

    return [
        'xml_added' => $xml_added,
        'xml_path'  => 'sitemap.xml',
    ];
}

function xghome_classic_render_sitemap_admin_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }
    $msg = isset($_GET['xghome_sitemap_done']) ? sanitize_text_field(wp_unslash((string) $_GET['xghome_sitemap_done'])) : '';
    ?>
    <div class="wrap">
        <h1>网站地图</h1>
        <p>在站点根目录生成或合并 <code>sitemap.xml</code>（sitemap 0.9）。已存在的 URL 保留，仅追加当前站点中尚未收录的公开文章与页面地址（<code><?php echo esc_html(xghome_classic_sitemap_canonical_base()); ?></code>）。</p>
        <?php if ($msg === '1') : ?>
            <div class="notice notice-success is-dismissible"><p>已更新。XML 新增 <?php echo (int) ($_GET['xa'] ?? 0); ?> 条 URL。</p></div>
        <?php elseif ($msg === 'err') : ?>
            <div class="notice notice-error"><p><?php echo esc_html(rawurldecode((string) ($_GET['em'] ?? '操作失败'))); ?></p></div>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('xghome_sitemap_generate'); ?>
            <input type="hidden" name="action" value="xghome_sitemap_generate" />
            <?php submit_button('一键合并生成 sitemap.xml', 'primary', 'submit', false); ?>
        </form>
    </div>
    <?php
}

add_action('admin_post_xghome_sitemap_generate', static function (): void {
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden');
    }
    check_admin_referer('xghome_sitemap_generate');
    $ret = xghome_classic_sitemap_run_merge();
    $ref = wp_get_referer() ?: admin_url('themes.php?page=xghome-sitemap');
    if (is_wp_error($ret)) {
        wp_safe_redirect(add_query_arg(
            [
                'xghome_sitemap_done' => 'err',
                'em'                  => rawurlencode($ret->get_error_message()),
            ],
            $ref
        ));
        exit;
    }
    wp_safe_redirect(add_query_arg(
        [
            'xghome_sitemap_done' => '1',
            'xa'                  => $ret['xml_added'],
        ],
        $ref
    ));
    exit;
});
