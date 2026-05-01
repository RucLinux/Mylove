<?php
/**
 * Search form template.
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<form role="search" method="get" class="searchform" action="<?php echo esc_url(home_url('/')); ?>">
    <div class="input-group">
        <input type="search" class="form-control" placeholder="搜索（Ctrl + K）" value="<?php echo esc_attr(get_search_query()); ?>" name="s">
        <span class="input-group-btn">
            <button class="btn btn-default" type="submit">搜索</button>
        </span>
    </div>
</form>
