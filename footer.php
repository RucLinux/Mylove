<?php
/**
 * Footer template.
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}

$record_html = xghome_classic_record_html_for_current_host();
$record_kses = xghome_classic_record_allowed_html();
$icp_record = (string) $record_html['icp_html'];
$public_record = (string) $record_html['public_html'];
$tongji_url = trim((string) get_option('xghome_footer_tongji_url', ''));
$show_footer_records = ($icp_record !== '' || $public_record !== '' || $tongji_url !== '');
?>
        </main>

        <aside class="right-sidebar col-sm-3">
            <?php get_sidebar(); ?>
        </aside>
    </div>

    <footer class="site-footer panel">
        <?php if ($show_footer_records) : ?>
            <div class="footer-icons footer-record-icons">
                <?php if ($icp_record !== '') : ?>
                    <span class="footer-record-item"><?php echo wp_kses($icp_record, $record_kses); ?></span>
                <?php endif; ?>
                <?php if ($public_record !== '') : ?>
                    <span class="footer-record-item"><?php echo wp_kses($public_record, $record_kses); ?></span>
                <?php endif; ?>
                <?php if ($tongji_url !== '') : ?>
                    <span class="footer-record-item"><a target="_blank" rel="noopener" href="<?php echo esc_url($tongji_url); ?>"><?php esc_html_e('网站统计', 'xghome-classic'); ?></a></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <p>
            &copy; <?php echo esc_html(xghome_classic_copyright_years()); ?>
            <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener"><?php bloginfo('name'); ?></a>.
            <?php esc_html_e('All rights reserved.', 'xghome-classic'); ?>
        </p>
        <p>
            <a target="_blank" rel="noopener" href="https://www.aliyun.com/">Aliyun</a>
            ·
            <a target="_blank" rel="noopener" href="https://wordpress.org/">WordPress <?php echo esc_html(get_bloginfo('version')); ?></a>
        </p>
    </footer>
</div>

<div class="scroll-stack" id="scrollStack">
    <button type="button" class="scroll-fab" id="scrollToTop" title="回到顶部" aria-label="回到顶部">
        <img src="<?php echo esc_url(xghome_classic_icon_uri('chevron-up')); ?>" alt="" width="20" height="20">
    </button>
    <button type="button" class="scroll-fab" id="scrollToBottom" title="回到底部" aria-label="回到底部">
        <img src="<?php echo esc_url(xghome_classic_icon_uri('chevron-down')); ?>" alt="" width="20" height="20">
    </button>
</div>

<?php wp_footer(); ?>
</body>
</html>
