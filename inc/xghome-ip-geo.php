<?php
/**
 * IP 属地：本地 ip2region 优先，可选 HTTP API 链与百度降级（仅 full 变体）。
 *
 * 在 functions.php 中 require 本文件之前定义：
 * define('XGHOME_IP_GEO_VARIANT', 'full'); // myzhenai
 * define('XGHOME_IP_GEO_VARIANT', 'lite'); // jiayublog / myzhenai-backup-personal
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/xghome-country-code-zh.php';

if (!defined('XGHOME_IP_GEO_VARIANT')) {
    define('XGHOME_IP_GEO_VARIANT', 'lite');
}

function xghome_ip_geo_is_full_variant(): bool
{
    return XGHOME_IP_GEO_VARIANT === 'full';
}

function xghome_ip_geo_enabled(): bool
{
    return (int) get_option('xghome_ip_geo_enabled', 0) === 1;
}

function xghome_ip_geo_resolve_data_dir(): string
{
    $dir = trim((string) get_option('xghome_ip_geo_data_dir', ''));
    if ($dir === '') {
        return wp_normalize_path(WP_CONTENT_DIR . '/ip2region-data');
    }
    $dir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $dir);
    if (preg_match('#^([a-zA-Z]:[\\\\/]|/|\\\\)#', $dir) === 1) {
        return wp_normalize_path(rtrim($dir, '/\\'));
    }

    return wp_normalize_path(rtrim(WP_CONTENT_DIR . '/' . ltrim($dir, '/\\'), '/\\'));
}

function xghome_ip_geo_client_ip(): string
{
    if ((int) get_option('xghome_ip_geo_trust_cf', 0) === 1 && !empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $cf = sanitize_text_field(wp_unslash((string) $_SERVER['HTTP_CF_CONNECTING_IP']));
        if (filter_var($cf, FILTER_VALIDATE_IP)) {
            return $cf;
        }
    }
    if (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = sanitize_text_field(wp_unslash((string) $_SERVER['REMOTE_ADDR']));
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return '';
}

/**
 * @param mixed $data
 */
function xghome_ip_geo_json_dig($data, string $path): string
{
    $path = trim($path);
    if ($path === '' || !is_array($data)) {
        return '';
    }
    $cur = $data;
    foreach (explode('.', $path) as $seg) {
        if ($seg === '' || !is_array($cur) || !array_key_exists($seg, $cur)) {
            return '';
        }
        $cur = $cur[$seg];
    }
    if (is_string($cur) || is_numeric($cur)) {
        return trim((string) $cur);
    }

    return '';
}

/**
 * ip2region 返回示例：中国|广东省|深圳市|移动|CN
 *
 * @return array{country:string,province:string,city:string,isp:string,country_code:string,raw:string}|null
 */
function xghome_ip_geo_normalize_ip2region_string(string $region): ?array
{
    $region = trim($region);
    if ($region === '') {
        return null;
    }
    $p = explode('|', $region);
    $country = isset($p[0]) ? trim((string) $p[0]) : '';
    $province = isset($p[1]) ? trim((string) $p[1]) : '';
    $city = isset($p[2]) ? trim((string) $p[2]) : '';
    $isp = isset($p[3]) ? trim((string) $p[3]) : '';
    $code = isset($p[4]) ? strtoupper(trim((string) $p[4])) : '';
    if ($province === '0') {
        $province = '';
    }
    if ($city === '0') {
        $city = '';
    }
    if ($isp === '0') {
        $isp = '';
    }
    if ($code === '' && ($country === '中国' || strcasecmp($country, 'china') === 0)) {
        $code = 'CN';
    }

    return [
        'country'      => $country,
        'province'     => $province,
        'city'         => $city,
        'isp'          => $isp,
        'country_code' => $code,
        'raw'          => $region,
    ];
}

function xghome_ip_geo_lookup_ip2region(string $ip): ?array
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return null;
    }
    $dir = xghome_ip_geo_resolve_data_dir();
    $v4 = $dir . DIRECTORY_SEPARATOR . 'ip2region_v4.xdb';
    $v6 = $dir . DIRECTORY_SEPARATOR . 'ip2region_v6.xdb';
    $is_v6 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    $file = $is_v6 ? $v6 : $v4;
    if (!is_readable($file)) {
        return null;
    }

    $searcher_path = get_template_directory() . '/inc/ip2region/xdb/Searcher.class.php';
    if (!is_readable($searcher_path)) {
        return null;
    }
    require_once $searcher_path;

    $version = $is_v6 ? \ip2region\xdb\IPv6::default() : \ip2region\xdb\IPv4::default();

    try {
        $searcher = \ip2region\xdb\Searcher::newWithFileOnly($version, $file);
        $region = $searcher->search($ip);
        $searcher->close();
    } catch (\Throwable $e) {
        return null;
    }

    if (!is_string($region) || $region === '') {
        return null;
    }

    return xghome_ip_geo_normalize_ip2region_string($region);
}

/**
 * @param array<string, mixed> $cfg
 * @return array{country:string,province:string,city:string,isp:string,country_code:string,raw:string}|null
 */
function xghome_ip_geo_try_http_api(string $ip, array $cfg): ?array
{
    $tpl = trim((string) ($cfg['url'] ?? ''));
    if ($tpl === '') {
        return null;
    }
    $url = str_replace(['{ip}', '%7Bip%7D'], [rawurlencode($ip), rawurlencode($ip)], $tpl);
    $timeout = max(1, min(12, (int) ($cfg['timeout'] ?? 3)));

    $resp = wp_remote_get(
        $url,
        [
            'timeout' => $timeout,
            'redirection' => 2,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]
    );
    if (is_wp_error($resp)) {
        return null;
    }
    if ((int) wp_remote_retrieve_response_code($resp) !== 200) {
        return null;
    }
    $body = wp_remote_retrieve_body($resp);
    if ($body === '') {
        return null;
    }
    $json = json_decode($body, true);
    if (!is_array($json)) {
        return null;
    }

    $code = xghome_ip_geo_json_dig($json, (string) ($cfg['country_code_path'] ?? ''));
    $country = xghome_ip_geo_json_dig($json, (string) ($cfg['country_path'] ?? ''));
    $province = xghome_ip_geo_json_dig($json, (string) ($cfg['province_path'] ?? ''));
    $city = xghome_ip_geo_json_dig($json, (string) ($cfg['city_path'] ?? ''));
    $isp = xghome_ip_geo_json_dig($json, (string) ($cfg['isp_path'] ?? ''));

    $code = strtoupper($code);
    if ($country === '' && $province === '' && $city === '' && $code === '') {
        return null;
    }

    return [
        'country'      => $country,
        'province'     => $province,
        'city'         => $city,
        'isp'          => $isp,
        'country_code' => $code,
        'raw'          => $body,
    ];
}

/**
 * 百度 opendata resource_id=6006：data[0].location 常为「广东省深圳市 电信」
 *
 * @return array{country:string,province:string,city:string,isp:string,country_code:string,raw:string}|null
 */
function xghome_ip_geo_try_baidu_opendata(string $ip): ?array
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return null;
    }
    $url = 'http://opendata.baidu.com/api.php?query=' . rawurlencode($ip) . '&resource_id=6006&oe=utf8&format=json';
    $resp = wp_remote_get($url, ['timeout' => 3, 'redirection' => 2]);
    if (is_wp_error($resp) || (int) wp_remote_retrieve_response_code($resp) !== 200) {
        return null;
    }
    $json = json_decode(wp_remote_retrieve_body($resp), true);
    if (!is_array($json) || (string) ($json['status'] ?? '') !== '0') {
        return null;
    }
    $data = $json['data'] ?? null;
    if (!is_array($data) || !isset($data[0]) || !is_array($data[0])) {
        return null;
    }
    $loc = trim((string) ($data[0]['location'] ?? ''));
    if ($loc === '') {
        return null;
    }
    $loc = preg_replace('/\s+(电信|联通|移动|铁通|网通|广电|长城宽带|教育网|CERNET)[\s\w]*$/u', '', $loc);
    $loc = trim((string) $loc);
    if ($loc === '') {
        return null;
    }

    $province = xghome_ip_geo_cn_region_from_blob($loc);
    $looks_cn = (bool) preg_match('/[\x{4e00}-\x{9fff}]/u', $loc);
    $code = $looks_cn ? 'CN' : '';
    $country = $looks_cn ? '中国' : '';

    return [
        'country'      => $country,
        'province'     => $province !== '' ? $province : $loc,
        'city'         => '',
        'isp'          => '',
        'country_code' => $code,
        'raw'          => $loc,
    ];
}

/**
 * 从「广东省深圳市」等串中取省级描述（含直辖市）
 */
function xghome_ip_geo_cn_region_from_blob(string $s): string
{
    $s = trim($s);
    if ($s === '') {
        return '';
    }
    if (preg_match('/^(北京|天津|上海|重庆)市/u', $s, $m)) {
        return $m[1] . '市';
    }
    if (preg_match('/^(.+?(?:省|自治区))/u', $s, $m)) {
        return $m[1];
    }
    if (preg_match('/^(.{2,12}?(?:州|盟|地区|林区))/u', $s, $m)) {
        return $m[1];
    }

    return '';
}

/**
 * @return list<array<string, mixed>>
 */
function xghome_ip_geo_get_api_configs(): array
{
    if (!xghome_ip_geo_is_full_variant()) {
        return [];
    }
    $raw = (string) get_option('xghome_ip_geo_api_config', '');
    if ($raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return [];
    }
    $out = [];
    foreach ($decoded as $row) {
        if (is_array($row) && !empty($row['url'])) {
            $out[] = $row;
        }
    }

    return $out;
}

/**
 * @param array{country:string,province:string,city:string,isp:string,country_code:string,raw:string}|null $n
 */
function xghome_ip_geo_format_public_label(?array $n): string
{
    if ($n === null) {
        return '';
    }
    $code = strtoupper(trim($n['country_code'] ?? ''));
    $country = trim($n['country'] ?? '');
    $province = trim($n['province'] ?? '');
    $city = trim($n['city'] ?? '');

    $is_cn = ($code === 'CN' || $country === '中国' || xghome_ip_geo_country_name_implies_china($country));

    if ($is_cn) {
        if ($province !== '' && strcasecmp($province, '0') !== 0) {
            return $province;
        }
        if ($city !== '' && strcasecmp($city, '0') !== 0) {
            return $city;
        }

        return '中国';
    }

    $zh_from_code = ($code !== '' && $code !== 'CN') ? xghome_ip_geo_country_code_to_zh($code) : '';

    $disp_country = $country;
    if ($zh_from_code !== '') {
        return $zh_from_code;
    }
    if ($disp_country !== '') {
        if (strtoupper($disp_country) === 'CN' || strcasecmp($disp_country, 'china') === 0) {
            return '中国';
        }

        return $disp_country;
    }
    if ($code !== '' && $code !== 'CN') {
        return $code;
    }

    return '';
}

function xghome_ip_geo_country_name_implies_china(string $name): bool
{
    $n = strtolower(trim($name));

    return in_array($n, ['china', 'cn', 'prc', 'people\'s republic of china'], true);
}

/**
 * 将主题内 get_ip_address()（与 single.php 访客区块同源）的返回值转为 lookup 结构。
 *
 * @return array{country:string,province:string,city:string,isp:string,country_code:string,raw:string}|null
 */
function xghome_ip_geo_normalize_get_ip_address_result(string $raw): ?array
{
    $raw = trim($raw);
    if ($raw === '' || strcasecmp($raw, 'errer:404') === 0) {
        return null;
    }
    if (stripos($raw, '|') !== false) {
        $parts = array_map('trim', explode('|', $raw));
        $code = '';
        if ($parts !== []) {
            $last = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) end($parts)));
            if (strlen($last) === 2) {
                $code = $last;
                array_pop($parts);
            }
        }
        $country = (string) ($parts[0] ?? '');
        $province = (string) ($parts[1] ?? '');
        $city = (string) ($parts[2] ?? '');

        return [
            'country' => $country,
            'province' => $province,
            'city' => $city,
            'isp' => '',
            'country_code' => $code,
            'raw' => $raw,
        ];
    }

    $geo = trim((string) preg_replace('/\s+(电信|联通|移动|网通|铁通|广电|长城|教育网|CABLE|DSL).*$/iu', '', $raw));
    if ($geo === '') {
        $geo = $raw;
    }

    if (preg_match('/台湾|台灣/u', $geo)) {
        return [
            'country' => '',
            'province' => $geo,
            'city' => '',
            'isp' => '',
            'country_code' => 'TW',
            'raw' => $raw,
        ];
    }
    if (preg_match('/香港/u', $geo)) {
        return [
            'country' => '',
            'province' => $geo,
            'city' => '',
            'isp' => '',
            'country_code' => 'HK',
            'raw' => $raw,
        ];
    }
    if (preg_match('/澳门|澳門/u', $geo)) {
        return [
            'country' => '',
            'province' => $geo,
            'city' => '',
            'isp' => '',
            'country_code' => 'MO',
            'raw' => $raw,
        ];
    }

    if (preg_match('/^[A-Za-z][A-Za-z\s,.-]*$/', $geo)) {
        return [
            'country' => trim($geo),
            'province' => '',
            'city' => '',
            'isp' => '',
            'country_code' => '',
            'raw' => $raw,
        ];
    }

    return [
        'country' => '中国',
        'province' => $geo,
        'city' => '',
        'isp' => '',
        'country_code' => 'CN',
        'raw' => $raw,
    ];
}

/**
 * 解析 IP 为结构化地域（lite：get_ip_address → ip2region；full：ip2region → HTTP API → 百度）。
 *
 * @return array{country:string,province:string,city:string,isp:string,country_code:string,raw:string}|null
 */
function xghome_ip_geo_lookup_normalize(string $ip): ?array
{
    if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) {
        return null;
    }

    $n = null;

    if (!xghome_ip_geo_is_full_variant() && function_exists('get_ip_address')) {
        $n = xghome_ip_geo_normalize_get_ip_address_result((string) get_ip_address($ip));
    }

    if ($n === null) {
        $n = xghome_ip_geo_lookup_ip2region($ip);
    }

    if ($n === null && xghome_ip_geo_is_full_variant()) {
        foreach (xghome_ip_geo_get_api_configs() as $cfg) {
            $try = xghome_ip_geo_try_http_api($ip, $cfg);
            if ($try !== null) {
                $n = $try;
                break;
            }
        }
    }

    if ($n === null && xghome_ip_geo_is_full_variant() && (int) get_option('xghome_ip_geo_baidu_fallback', 1) === 1) {
        $n = xghome_ip_geo_try_baidu_opendata($ip);
    }

    return $n;
}

/**
 * 评论列表用：省级名称缩短为「浙江」「北京」等形式。
 */
function xghome_ip_geo_short_cn_province(string $p): string
{
    $p = trim($p);
    if ($p === '' || strcasecmp($p, '0') === 0) {
        return '中国';
    }

    $direct = [
        '北京市' => '北京',
        '上海市' => '上海',
        '天津市' => '天津',
        '重庆市' => '重庆',
    ];
    if (isset($direct[$p])) {
        return $direct[$p];
    }

    if (preg_match('/^(北京|天津|上海|重庆)市$/u', $p, $m)) {
        return $m[1];
    }

    if (preg_match('/香港/u', $p)) {
        return '中国香港';
    }
    if (preg_match('/澳门/u', $p)) {
        return '中国澳门';
    }
    if (preg_match('/台湾|台灣/u', $p)) {
        return '中国台湾';
    }

    if (preg_match('/^(.+?)壮族自治区$/u', $p, $m)) {
        return $m[1];
    }
    if (preg_match('/^(.+?)回族自治区$/u', $p, $m)) {
        return $m[1];
    }
    if (preg_match('/^(.+?)维吾尔自治区$/u', $p, $m)) {
        return $m[1];
    }
    if (preg_match('/^(.+?)自治区$/u', $p, $m)) {
        return $m[1];
    }

    if (preg_match('/省$/u', $p)) {
        return (string) preg_replace('/省$/u', '', $p);
    }

    return $p;
}

/**
 * 写入评论 meta 的展示文案：full 主题用列表专用简称；lite 主题优先与文章相同的 public 标签，空时再回退为列表简称。
 *
 * @param array{country:string,province:string,city:string,isp:string,country_code:string,raw:string}|null $n
 */
function xghome_ip_geo_resolve_comment_meta_label(?array $n): string
{
    if ($n === null) {
        return '';
    }
    if (xghome_ip_geo_is_full_variant()) {
        return xghome_ip_geo_format_comment_stored_label($n);
    }
    $label = xghome_ip_geo_format_public_label($n);
    if ($label !== '') {
        return $label;
    }

    return xghome_ip_geo_format_comment_stored_label($n);
}

/**
 * 写入评论 meta 的展示用属地：境内→省/直辖市简称；境外→国家中文；台港澳→中国前缀。
 *
 * @param array{country:string,province:string,city:string,isp:string,country_code:string,raw:string}|null $n
 */
function xghome_ip_geo_format_comment_stored_label(?array $n): string
{
    if ($n === null) {
        return '';
    }

    $code = strtoupper(trim($n['country_code'] ?? ''));
    $country = trim($n['country'] ?? '');
    $province = trim($n['province'] ?? '');
    $city = trim($n['city'] ?? '');

    if ($code === 'TW') {
        return '中国台湾';
    }
    if ($code === 'HK') {
        return '中国香港';
    }
    if ($code === 'MO') {
        return '中国澳门';
    }

    $isCn = ($code === 'CN' || $country === '中国' || xghome_ip_geo_country_name_implies_china($country));

    if ($isCn) {
        $p = $province;
        if ($p === '' || strcasecmp($p, '0') === 0) {
            $p = $city;
        }
        $p = trim((string) $p);
        if ($p === '' || strcasecmp($p, '0') === 0) {
            return '中国';
        }
        if (preg_match('/香港/u', $p)) {
            return '中国香港';
        }
        if (preg_match('/澳门/u', $p)) {
            return '中国澳门';
        }
        if (preg_match('/台湾|台灣/u', $p)) {
            return '中国台湾';
        }

        return xghome_ip_geo_short_cn_province($p);
    }

    return xghome_ip_geo_format_public_label($n);
}

function xghome_ip_geo_resolve(string $ip): string
{
    if (!xghome_ip_geo_enabled() || $ip === '') {
        return '';
    }

    $n = xghome_ip_geo_lookup_normalize($ip);
    $label = xghome_ip_geo_format_public_label($n);

    return $label !== '' ? $label : '';
}

/**
 * 评论列表展示用：读已存 meta；若无则按 comment_author_IP 解析并写入 meta（首访补全，与回填规则一致）。
 */
function xghome_ip_geo_comment_footer_label(\WP_Comment $comment): string
{
    if (!xghome_ip_geo_enabled()) {
        return '';
    }
    $id = (int) $comment->comment_ID;
    if ($id <= 0) {
        return '';
    }
    $m = get_comment_meta($id, 'xghome_ip_geo_label', true);
    if (is_string($m) && $m !== '') {
        return $m;
    }
    $ip = trim((string) $comment->comment_author_IP);
    if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) {
        return '';
    }
    $n = xghome_ip_geo_lookup_normalize($ip);
    $label = xghome_ip_geo_resolve_comment_meta_label($n);
    if ($label !== '') {
        update_comment_meta($id, 'xghome_ip_geo_label', $label);
    }

    return $label;
}

function xghome_ip_geo_on_wp_insert_comment($comment_id, $comment): void
{
    if (!xghome_ip_geo_enabled()) {
        return;
    }
    if (!$comment instanceof \WP_Comment) {
        return;
    }
    $comment_id = (int) $comment_id;
    if ($comment_id <= 0) {
        return;
    }
    $ip = trim((string) $comment->comment_author_IP);
    if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) {
        return;
    }
    $n = xghome_ip_geo_lookup_normalize($ip);
    $label = xghome_ip_geo_resolve_comment_meta_label($n);
    if ($label !== '') {
        update_comment_meta($comment_id, 'xghome_ip_geo_label', $label);
    }
}
add_action('wp_insert_comment', 'xghome_ip_geo_on_wp_insert_comment', 10, 2);

function xghome_ip_geo_on_transition_post_status(string $new_status, string $old_status, \WP_Post $post): void
{
    if (!xghome_ip_geo_enabled()) {
        return;
    }
    if ($new_status !== 'publish') {
        return;
    }
    if (!in_array($post->post_type, ['post', 'page'], true)) {
        return;
    }
    if (!is_user_logged_in()) {
        return;
    }
    $ip = xghome_ip_geo_client_ip();
    if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) {
        return;
    }
    $label = xghome_ip_geo_resolve($ip);
    if ($label !== '') {
        update_post_meta($post->ID, 'xghome_ip_geo_label', $label);
    }
}
add_action('transition_post_status', 'xghome_ip_geo_on_transition_post_status', 10, 3);

function xghome_ip_geo_filter_the_content(string $content): string
{
    if (!xghome_ip_geo_enabled() || !is_singular(['post', 'page']) || !in_the_loop() || !is_main_query()) {
        return $content;
    }
    $id = (int) get_the_ID();
    if ($id <= 0) {
        return $content;
    }
    $label = get_post_meta($id, 'xghome_ip_geo_label', true);
    if (!is_string($label) || $label === '') {
        return $content;
    }

    return $content . '<p class="xghome-ip-geo xghome-ip-geo-post">' . esc_html__('发布时 IP 属地：', 'xghome-classic') . esc_html($label) . '</p>';
}
add_filter('the_content', 'xghome_ip_geo_filter_the_content', 20);

/**
 * 主题设置保存（在已通过 nonce 与权限校验的 handler 内调用）。
 *
 * @param array<string, mixed> $saved_options
 */
function xghome_ip_geo_handle_theme_save(array $saved_options): void
{
    $enabled = isset($_POST['xghome_ip_geo_enabled']) ? 1 : 0;
    update_option('xghome_ip_geo_enabled', $enabled);

    $dir = isset($_POST['xghome_ip_geo_data_dir'])
        ? sanitize_text_field(wp_unslash((string) $_POST['xghome_ip_geo_data_dir']))
        : (string) ($saved_options['ip_geo_data_dir'] ?? '');
    update_option('xghome_ip_geo_data_dir', $dir);

    $cf = isset($_POST['xghome_ip_geo_trust_cf']) ? 1 : 0;
    update_option('xghome_ip_geo_trust_cf', $cf);

    if (!xghome_ip_geo_is_full_variant()) {
        return;
    }

    $raw_api = isset($_POST['xghome_ip_geo_api_config']) ? wp_unslash((string) $_POST['xghome_ip_geo_api_config']) : '';
    $raw_api = trim($raw_api);
    if ($raw_api === '') {
        update_option('xghome_ip_geo_api_config', '');
    } else {
        $decoded = json_decode($raw_api, true);
        if (is_array($decoded)) {
            update_option('xghome_ip_geo_api_config', wp_json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
    }

    update_option('xghome_ip_geo_baidu_fallback', isset($_POST['xghome_ip_geo_baidu_fallback']) ? 1 : 0);
}

/**
 * @param array<string, mixed> $options
 */
function xghome_ip_geo_render_admin_fields(array $options): void
{
    $enabled = (int) ($options['ip_geo_enabled'] ?? 0);
    $dir = (string) ($options['ip_geo_data_dir'] ?? '');
    $cf = (int) ($options['ip_geo_trust_cf'] ?? 0);
    $api = (string) ($options['ip_geo_api_config'] ?? '');
    $baidu = (int) ($options['ip_geo_baidu_fallback'] ?? 1);
    $data_dir_hint = wp_normalize_path(WP_CONTENT_DIR . '/ip2region-data');
    ?>
    <tr>
        <th scope="row">IP 属地</th>
        <td>
            <p>
                <label>
                    <input type="checkbox" name="xghome_ip_geo_enabled" value="1" <?php checked($enabled, 1); ?>>
                    在文章与评论中显示 IP 属地（发布/评论时由服务端解析并保存，前台仅展示结果）
                </label>
            </p>
            <p class="description">
                开启后：文章页可展示「发布时 IP 属地」、评论可展示属地。文章 meta 仅在<strong>首次转为已发布且当时已勾选本项</strong>时写入；评论在提交时若已勾选则写入。关闭后前台不展示，已写入的 meta 仍保留；历史评论回填<strong>不依赖</strong>本项是否勾选。
            </p>
            <p><label for="xghome_ip_geo_data_dir">ip2region 数据目录</label></p>
            <input id="xghome_ip_geo_data_dir" type="text" name="xghome_ip_geo_data_dir" value="<?php echo esc_attr($dir); ?>" class="large-text code" placeholder="<?php echo esc_attr($data_dir_hint); ?>">
            <p class="description">
                留空则使用：<code><?php echo esc_html($data_dir_hint); ?></code>。请将
                <a href="https://github.com/lionsoul2014/ip2region/tree/master/data" target="_blank" rel="noopener">ip2region_v4.xdb</a>
                与
                <a href="https://github.com/lionsoul2014/ip2region/tree/master/data" target="_blank" rel="noopener">ip2region_v6.xdb</a>
                下载到此目录（与官方仓库说明一致）。查询顺序：<strong>本地 ip2region 优先</strong>。
            </p>
            <p>
                <label>
                    <input type="checkbox" name="xghome_ip_geo_trust_cf" value="1" <?php checked($cf, 1); ?>>
                    站点在 Cloudflare 后时，优先使用 <code>CF-Connecting-IP</code>（请勿在不可信代理前开启）
                </label>
            </p>
            <p class="description">
                纯真 IP：<a href="https://cz88.net/geo-public" target="_blank" rel="noopener">cz88.net/geo-public</a>（可下载本地库或使用其官网 API；本主题核心实现以 ip2region 本地文件与下方 HTTP 配置为主）。
            </p>
            <?php if (xghome_ip_geo_is_full_variant()) : ?>
                <p><label for="xghome_ip_geo_api_config">HTTP API 列表（JSON 数组，按顺序尝试；占位符 <code>{ip}</code>）</label></p>
                <textarea id="xghome_ip_geo_api_config" name="xghome_ip_geo_api_config" rows="10" class="large-text code" placeholder='[{"url":"https://example.com/ip?query={ip}","timeout":3,"country_code_path":"data.iso","country_path":"data.country","province_path":"data.region","city_path":"data.city"}]'><?php echo esc_textarea($api); ?></textarea>
                <p class="description">
                    每条需含 <code>url</code>；可选 <code>timeout</code>、<code>country_code_path</code>、<code>country_path</code>、<code>province_path</code>、<code>city_path</code>、<code>isp_path</code>（点号分隔路径，从 JSON 根开始）。境内展示省/直辖市，境外展示国家名；国家码为 <code>CN</code> 时前台不显示 “CN”，而按规则显示中文地域。
                </p>
                <p>
                    <label>
                        <input type="checkbox" name="xghome_ip_geo_baidu_fallback" value="1" <?php checked($baidu, 1); ?>>
                        本地与上述 API 均失败时，尝试百度开放数据（resource_id=6006，仅作兜底，稳定性与合规请自行评估）
                    </label>
                </p>
            <?php else : ?>
                <p class="description"><strong>本主题未启用内置 HTTP API 配置项</strong>（避免与站点已有 IP 接口代码冲突）。仅使用上方本地 ip2region 目录进行解析。</p>
            <?php endif; ?>
            <?php
            if (function_exists('xghome_ip_geo_render_backfill_controls')) {
                xghome_ip_geo_render_backfill_controls();
            }
            ?>
        </td>
    </tr>
    <?php
}

require_once __DIR__ . '/xghome-ip-geo-backfill.php';
