<?php
/**
 * 仅相册分类归档：微信朋友圈式九宫格（随机媒体库图片）。其它页面勿引用。
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}

$attach_ids = xghome_classic_album_random_attachment_ids(9);
?>
<section class="panel album-moments-wrap album-moments-centered" aria-label="<?php esc_attr_e('相册图片', 'xghome-classic'); ?>">
    <?php if (!empty($attach_ids)) : ?>
        <div class="album-moments-inner">
            <div class="album-moments-grid" role="list">
                <?php foreach ($attach_ids as $aid) : ?>
                    <?php
                    $full_url = wp_get_attachment_image_url((int) $aid, 'full');
                    if ($full_url === false) {
                        $full_url = wp_get_attachment_url((int) $aid);
                    }
                    ?>
                    <button
                        type="button"
                        class="album-moments-cell js-album-lightbox-trigger"
                        role="listitem"
                        data-full="<?php echo esc_url($full_url); ?>"
                        aria-label="<?php esc_attr_e('查看大图', 'xghome-classic'); ?>"
                    >
                        <?php
                        echo wp_get_attachment_image(
                            (int) $aid,
                            'medium',
                            false,
                            [
                                'class'    => 'album-moments-thumb',
                                'loading'  => 'lazy',
                                'decoding' => 'async',
                                'alt'      => '',
                            ]
                        );
                        ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else : ?>
        <p class="album-moments-hint muted"><?php esc_html_e('媒体库中暂无可展示的图片，请先在后台上传图片附件。', 'xghome-classic'); ?></p>
    <?php endif; ?>
</section>

<div id="xghomeAlbumLightbox" class="album-lightbox" hidden aria-hidden="true">
    <button type="button" class="album-lightbox-close" aria-label="<?php esc_attr_e('关闭', 'xghome-classic'); ?>">&times;</button>
    <div class="album-lightbox-stage">
        <img src="" alt="" class="album-lightbox-img">
    </div>
</div>
