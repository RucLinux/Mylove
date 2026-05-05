<?php
/**
 * ISO 3166-1 alpha-2（及历史/扩展码）→ 中文国家/地区名。
 * 自 TwentyTen 改版主题 transCode 迁移；修正重复键 BF、KP/KR 与键名大小写。
 *
 * 官方分配码中文以联合国 UNTERM Chinese Short（datasets/country-codes CSV）为主，
 * 经脚注星号清理与若干常用简称覆盖；港澳台等沿用站内固定表述；历史/兼容码（如 UK、CS、TP）另行合并。
 * 更新码表：在 BLOG 根目录放置 `country-codes.csv` 后运行 `python _gen_country_zh_map.py --apply`。
 *
 * @package xghome-classic
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return array<string, string>
 */
function xghome_ip_geo_country_code_zh_map(): array
{
    static $map = null;
    if ($map !== null) {
        return $map;
    }
    $map = [
        'AA' => '阿鲁巴',
        'AD' => '安道尔',
        'AE' => '阿联酋',
        'AF' => '阿富汗',
        'AG' => '安提瓜和巴布达',
        'AI' => '安圭拉',
        'AL' => '阿尔巴尼亚',
        'AM' => '亚美尼亚',
        'AN' => '荷属安德列斯',
        'AO' => '安哥拉',
        'AQ' => '南极洲',
        'AR' => '阿根廷',
        'AS' => '美属萨摩亚',
        'AT' => '奥地利',
        'AU' => '澳大利亚',
        'AV' => '安圭拉岛',
        'AW' => '阿鲁巴',
        'AX' => '奥兰群岛',
        'AZ' => '阿塞拜疆',
        'BA' => '波黑',
        'BB' => '巴巴多斯',
        'BD' => '孟加拉国',
        'BE' => '比利时',
        'BF' => '布基纳法索',
        'BG' => '保加利亚',
        'BH' => '巴林',
        'BI' => '布隆迪',
        'BJ' => '贝宁',
        'BL' => '圣巴泰勒米',
        'BM' => '百慕大',
        'BN' => '文莱',
        'BO' => '玻利维亚',
        'BQ' => '博奈尔、圣尤斯特歇斯和萨巴',
        'BR' => '巴西',
        'BS' => '巴哈马',
        'BT' => '不丹',
        'BV' => '布维岛',
        'BW' => '博茨瓦纳',
        'BY' => '白俄罗斯',
        'BZ' => '伯利兹',
        'CA' => '加拿大',
        'CB' => '柬埔寨',
        'CC' => '科科斯（基林）群岛',
        'CD' => '刚果（金）',
        'CF' => '中非',
        'CG' => '刚果（布）',
        'CH' => '瑞士',
        'CI' => '科特迪瓦',
        'CK' => '库克群岛',
        'CL' => '智利',
        'CM' => '喀麦隆',
        'CN' => '中国',
        'CO' => '哥伦比亚',
        'CR' => '哥斯达黎加',
        'CS' => '捷克斯洛伐克',
        'CU' => '古巴',
        'CV' => '佛得角',
        'CW' => '库拉索',
        'CX' => '圣诞岛',
        'CY' => '塞浦路斯',
        'CZ' => '捷克',
        'DE' => '德国',
        'DJ' => '吉布提',
        'DK' => '丹麦',
        'DM' => '多米尼加',
        'DO' => '多米尼加共和国',
        'DZ' => '阿尔及利亚',
        'EC' => '厄瓜多尔',
        'EE' => '爱沙尼亚',
        'EG' => '埃及',
        'EH' => '西撒哈拉',
        'ER' => '厄立特里亚',
        'ES' => '西班牙',
        'ET' => '埃塞俄比亚',
        'FI' => '芬兰',
        'FJ' => '斐济',
        'FK' => '福克兰群岛',
        'FM' => '密克罗尼西亚',
        'FO' => '法罗群岛',
        'FR' => '法国',
        'FX' => '法国-主教区',
        'GA' => '加蓬',
        'GB' => '英国',
        'GD' => '格林纳达',
        'GE' => '格鲁吉亚',
        'GF' => '法属圭亚那',
        'GG' => '格恩西',
        'GH' => '加纳',
        'GI' => '直布罗陀',
        'GL' => '格陵兰',
        'GM' => '冈比亚',
        'GN' => '几内亚',
        'GP' => '瓜德罗普',
        'GQ' => '赤道几内亚',
        'GR' => '希腊',
        'GS' => '南乔治亚和南桑威奇群岛',
        'GT' => '危地马拉',
        'GU' => '关岛',
        'GW' => '几内亚比绍',
        'GY' => '圭亚那',
        'HK' => '中国香港特区',
        'HM' => '赫德岛和麦克唐纳岛',
        'HN' => '洪都拉斯',
        'HR' => '克罗地亚',
        'HT' => '海地',
        'HU' => '匈牙利',
        'ID' => '印度尼西亚',
        'IE' => '爱尔兰',
        'IL' => '以色列',
        'IM' => '马恩岛',
        'IN' => '印度',
        'IO' => '英属印度洋领地',
        'IQ' => '伊拉克',
        'IR' => '伊朗',
        'IS' => '冰岛',
        'IT' => '意大利',
        'JE' => '泽西',
        'JM' => '牙买加',
        'JO' => '约旦',
        'JP' => '日本',
        'KE' => '肯尼亚',
        'KG' => '吉尔吉斯斯坦',
        'KH' => '柬埔寨',
        'KI' => '基里巴斯',
        'KM' => '科摩罗',
        'KN' => '圣基茨和尼维斯',
        'KP' => '朝鲜',
        'KR' => '韩国',
        'KW' => '科威特',
        'KY' => '开曼群岛',
        'KZ' => '哈萨克斯坦',
        'LA' => '老挝',
        'LB' => '黎巴嫩',
        'LC' => '圣卢西亚',
        'LI' => '列支敦士登',
        'LK' => '斯里兰卡',
        'LR' => '利比里亚',
        'LS' => '莱索托',
        'LT' => '立陶宛',
        'LU' => '卢森堡',
        'LV' => '拉脱维亚',
        'LY' => '利比亚',
        'MA' => '摩洛哥',
        'MC' => '摩纳哥',
        'MD' => '摩尔多瓦',
        'ME' => '黑山',
        'MF' => '圣马丁（法属）',
        'MG' => '马达加斯加',
        'MH' => '马绍尔群岛',
        'MK' => '北马其顿',
        'ML' => '马里',
        'MM' => '缅甸',
        'MN' => '蒙古',
        'MO' => '中国澳门特区',
        'MP' => '北马里亚纳群岛',
        'MQ' => '马提尼克',
        'MR' => '毛里塔尼亚',
        'MS' => '蒙特塞拉特',
        'MT' => '马耳他',
        'MU' => '毛里求斯',
        'MV' => '马尔代夫',
        'MW' => '马拉维',
        'MX' => '墨西哥',
        'MY' => '马来西亚',
        'MZ' => '莫桑比克',
        'NA' => '纳米比亚',
        'NC' => '新喀里多尼亚',
        'NE' => '尼日尔',
        'NF' => '诺福克岛',
        'NG' => '尼日利亚',
        'NI' => '尼加拉瓜',
        'NL' => '荷兰',
        'NO' => '挪威',
        'NP' => '尼泊尔',
        'NR' => '瑙鲁',
        'NT' => '中立区(沙特-伊拉克间)',
        'NU' => '纽埃',
        'NZ' => '新西兰',
        'OM' => '阿曼',
        'PA' => '巴拿马',
        'PE' => '秘鲁',
        'PF' => '法属波利尼西亚',
        'PG' => '巴布亚新几内亚',
        'PH' => '菲律宾',
        'PK' => '巴基斯坦',
        'PL' => '波兰',
        'PM' => '圣皮埃尔和密克隆',
        'PN' => '皮特凯恩',
        'PR' => '波多黎各',
        'PS' => '巴勒斯坦',
        'PT' => '葡萄牙',
        'PW' => '帕劳',
        'PY' => '巴拉圭',
        'QA' => '卡塔尔',
        'RE' => '留尼汪',
        'RO' => '罗马尼亚',
        'RS' => '塞尔维亚',
        'RU' => '俄罗斯',
        'RW' => '卢旺达',
        'SA' => '沙特阿拉伯',
        'SB' => '所罗门群岛',
        'SC' => '塞舌尔',
        'SD' => '苏丹',
        'SE' => '瑞典',
        'SG' => '新加坡',
        'SH' => '圣赫勒拿',
        'SI' => '斯洛文尼亚',
        'SJ' => '斯瓦尔巴群岛和扬马延岛',
        'SK' => '斯洛伐克',
        'SL' => '塞拉利昂',
        'SM' => '圣马力诺',
        'SN' => '塞内加尔',
        'SO' => '索马里',
        'SR' => '苏里南',
        'SS' => '南苏丹',
        'ST' => '圣多美和普林西比',
        'SU' => '前苏联',
        'SV' => '萨尔瓦多',
        'SX' => '圣马丁（荷属）',
        'SY' => '叙利亚',
        'SZ' => '斯威士兰',
        'TC' => '特克斯和凯科斯群岛',
        'TD' => '乍得',
        'TF' => '法属南方领地',
        'TG' => '多哥',
        'TH' => '泰国',
        'TJ' => '塔吉克斯坦',
        'TK' => '托克劳',
        'TL' => '东帝汶',
        'TM' => '土库曼斯坦',
        'TN' => '突尼斯',
        'TO' => '汤加',
        'TP' => '东帝汶',
        'TR' => '土耳其',
        'TT' => '特立尼达和多巴哥',
        'TV' => '图瓦卢',
        'TW' => '中国台湾省',
        'TZ' => '坦桑尼亚',
        'UA' => '乌克兰',
        'UG' => '乌干达',
        'UK' => '英国',
        'UM' => '美国本土外小岛屿',
        'US' => '美国',
        'UY' => '乌拉圭',
        'UZ' => '乌兹别克斯坦',
        'VA' => '梵蒂冈',
        'VC' => '圣文森特和格林纳丁斯',
        'VE' => '委内瑞拉',
        'VG' => '英属维尔京群岛',
        'VI' => '美属维尔京群岛',
        'VN' => '越南',
        'VU' => '瓦努阿图',
        'WF' => '瓦利斯和富图纳',
        'WS' => '萨摩亚',
        'XK' => '科索沃',
        'YE' => '也门',
        'YT' => '马约特',
        'YU' => '南斯拉夫',
        'ZA' => '南非',
        'ZM' => '赞比亚',
        'ZR' => '扎伊尔',
        'ZW' => '津巴布韦',
    ];
    return $map;
}

function xghome_ip_geo_country_code_to_zh(string $code): string
{
    $c = strtoupper(trim($code));
    if ($c === '') {
        return '';
    }
    $map = xghome_ip_geo_country_code_zh_map();

    return $map[$c] ?? '';
}

/**
 * 与旧版 TwentyTen transCode 行为一致：未知码返回「局域网」。
 */
function xghome_ip_geo_trans_code_compat($code): string
{
    $s = is_string($code) ? trim($code) : '';
    if ($s === '') {
        return '局域网';
    }
    $zh = xghome_ip_geo_country_code_to_zh($s);

    return $zh !== '' ? $zh : '局域网';
}

if (!function_exists('transCode')) {
    /**
     * @param mixed $code
     */
    function transCode($code): string
    {
        return xghome_ip_geo_trans_code_compat($code);
    }
}

/**
 * 去掉管道串末尾的「|两字母国家码」（供 ip-api 第四段或展示用），不影响 ip2region 五段串的倒数第一段解析。
 */
function xghome_ip_geo_strip_trailing_iso_pipe_suffix(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '' || stripos($raw, '|') === false) {
        return $raw;
    }
    if (preg_match('/^(.+)\|([A-Za-z]{2})$/', $raw, $m)) {
        return $m[1];
    }

    return $raw;
}

/**
 * 从「…|国家|省|市|运营商|ISO」或「…|country|region|city|ISO」取末尾两字母国家码（不依赖 array_filter，避免 0 被吃掉）。
 */
function xghome_ip_geo_iso_code_from_pipe_tail(string $raw): string
{
    $raw = trim($raw);
    if ($raw === '' || stripos($raw, '|') === false) {
        return '';
    }
    $parts = array_map('trim', explode('|', $raw));
    if ($parts === []) {
        return '';
    }
    $last = strtoupper((string) end($parts));
    if (preg_match('/^[A-Z]{2}$/', $last)) {
        return $last;
    }

    return '';
}

/**
 * 文章页访客国旗 emoji（按国家码）。
 */
function xghome_ip_geo_flag_emoji_for_code(string $code): string
{
    static $emoji = [
        'CN' => '🇨🇳',
        'HK' => '🇭🇰',
        'MO' => '🇲🇴',
        'TW' => '🇹🇼',
        'US' => '🇺🇸',
        'GB' => '🇬🇧',
        'UK' => '🇬🇧',
        'JP' => '🇯🇵',
        'KR' => '🇰🇷',
        'KP' => '🇰🇵',
        'DE' => '🇩🇪',
        'FR' => '🇫🇷',
        'RU' => '🇷🇺',
        'CA' => '🇨🇦',
        'AU' => '🇦🇺',
        'IN' => '🇮🇳',
        'BR' => '🇧🇷',
        'SG' => '🇸🇬',
        'TH' => '🇹🇭',
        'VN' => '🇻🇳',
    ];
    $c = strtoupper(trim($code));

    return $emoji[$c] ?? '🌍';
}
