<?php
/**
 * 历史评论 IP 属地 meta 回填（按 wp_comments.comment_author_IP 重算）。
 *
 * @package xghome-classic
 */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 分批处理评论，写入 xghome_ip_geo_label（与 comment_post 时规则一致）。
 *
 * @return array{processed:int, updated:int, skipped:int, next_offset:int, done:bool}
 */
function xghome_ip_geo_backfill_comments_batch(int $offset, int $limit): array
{
    $offset = max(0, $offset);
    $limit = max(1, min(100, $limit));

    $comments = get_comments([
        'status'  => ['approve', 'hold'],
        'orderby' => 'comment_ID',
        'order'   => 'ASC',
        'number'  => $limit,
        'offset'  => $offset,
    ]);

    if (!is_array($comments)) {
        return [
            'processed'   => 0,
            'updated'     => 0,
            'skipped'     => 0,
            'next_offset' => $offset,
            'done'        => true,
        ];
    }

    $updated = 0;
    $skipped = 0;

    foreach ($comments as $comment) {
        if (!($comment instanceof \WP_Comment)) {
            continue;
        }
        $ip = trim((string) $comment->comment_author_IP);
        if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) {
            $skipped++;

            continue;
        }

        $n = xghome_ip_geo_lookup_normalize($ip);
        $label = xghome_ip_geo_resolve_comment_meta_label($n);
        if ($label === '') {
            delete_comment_meta($comment->comment_ID, 'xghome_ip_geo_label');
            $skipped++;

            continue;
        }

        update_comment_meta($comment->comment_ID, 'xghome_ip_geo_label', $label);
        $updated++;
    }

    $processed = count($comments);
    $next = $offset + $processed;
    $done = $processed < $limit;

    return [
        'processed'   => $processed,
        'updated'     => $updated,
        'skipped'     => $skipped,
        'next_offset' => $next,
        'done'        => $done,
    ];
}

function xghome_ip_geo_render_backfill_controls(): void
{
    $nonce = wp_create_nonce('xghome_ip_geo_backfill');
    ?>
    <div class="xghome-ip-geo-backfill" style="margin-top:14px;padding-top:12px;border-top:1px solid #dcdcde;">
        <p><strong>历史评论回填</strong></p>
        <p class="description">
            根据数据库中每条评论的 IP（<code>comment_author_IP</code>）重新计算并写入 <code>xghome_ip_geo_label</code>，规则与当前评论一致（省/国家/中国+港澳台）。
            仅处理状态为「已通过」与「待审核」的评论；每批最多 100 条，自动连续执行直至完成。
        </p>
        <p class="description">
            <strong>无需</strong>先勾选「在文章与评论中显示 IP 属地」即可回填。该勾选仅控制<strong>前台是否展示</strong>：开启后才会在文章末尾显示「发布时 IP 属地」、在评论中显示属地；关闭时 meta 仍可按需写入，但访客看不到。
        </p>
        <p>
            <button type="button" class="button button-secondary" id="xghome-ip-geo-backfill-btn"
                data-nonce="<?php echo esc_attr($nonce); ?>">
                开始回填历史评论
            </button>
            <span id="xghome-ip-geo-backfill-status" class="description" style="margin-left:8px;"></span>
        </p>
    </div>
    <?php
}

function xghome_ip_geo_backfill_ajax(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => '权限不足'], 403);
    }

    check_ajax_referer('xghome_ip_geo_backfill', 'nonce');

    $offset = isset($_POST['offset']) ? (int) wp_unslash($_POST['offset']) : 0;
    $limit = isset($_POST['limit']) ? (int) wp_unslash($_POST['limit']) : 40;

    $out = xghome_ip_geo_backfill_comments_batch($offset, $limit);
    wp_send_json_success($out);
}
add_action('wp_ajax_xghome_ip_geo_backfill', 'xghome_ip_geo_backfill_ajax');

function xghome_ip_geo_backfill_admin_footer_script(): void
{
    if (!function_exists('get_current_screen')) {
        return;
    }
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'appearance_page_xghome-permalink') {
        return;
    }
    ?>
    <script>
    (function ($) {
        $(function () {
            var $btn = $('#xghome-ip-geo-backfill-btn');
            var $st = $('#xghome-ip-geo-backfill-status');
            if (!$btn.length) {
                return;
            }
            $btn.on('click', function () {
                var nonce = $btn.data('nonce');
                var offset = 0;
                var totalUp = 0;
                var totalSkip = 0;
                $btn.prop('disabled', true);
                $st.text('<?php echo esc_js(__('正在处理…', 'xghome-classic')); ?>');

                function step() {
                    $.post(ajaxurl, {
                        action: 'xghome_ip_geo_backfill',
                        nonce: nonce,
                        offset: offset,
                        limit: 40
                    })
                        .done(function (res) {
                            if (!res || !res.success) {
                                var msg = (res && res.data && res.data.message) ? res.data.message : '<?php echo esc_js(__('未知错误', 'xghome-classic')); ?>';
                                $st.text('<?php echo esc_js(__('失败：', 'xghome-classic')); ?>' + msg);
                                $btn.prop('disabled', false);
                                return;
                            }
                            var d = res.data;
                            offset = d.next_offset;
                            totalUp += d.updated;
                            totalSkip += d.skipped;
                            $st.text('<?php echo esc_js(__('本批', 'xghome-classic')); ?> ' + d.processed + ' <?php echo esc_js(__('条；累计更新', 'xghome-classic')); ?> ' + totalUp + ' <?php echo esc_js(__('，跳过', 'xghome-classic')); ?> ' + totalSkip);
                            if (d.done) {
                                $st.append(' — <?php echo esc_js(__('已完成', 'xghome-classic')); ?>');
                                $btn.prop('disabled', false);
                            } else {
                                step();
                            }
                        })
                        .fail(function () {
                            $st.text('<?php echo esc_js(__('请求失败', 'xghome-classic')); ?>');
                            $btn.prop('disabled', false);
                        });
                }

                step();
            });
        });
    })(jQuery);
    </script>
    <?php
}
add_action('admin_footer', 'xghome_ip_geo_backfill_admin_footer_script');
