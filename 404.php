<?php
/**
 * 404 template.
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<article class="panel not-found">
    <h1 class="page-title">404</h1>
    <p>页面不存在，可能已被删除或更改链接。</p>
    <p><a class="btn btn-default" href="<?php echo esc_url(home_url('/')); ?>">返回首页</a></p>
</article>

<?php
get_footer();
