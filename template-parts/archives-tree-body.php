<?php
/**
 * 归档树（年 / 月 / 标题 + 时间），供固定页面模板与虚拟归档路由共用。
 *
 * @package xghome-classic
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="archives-tree archives-tree-nested">
    <?php foreach (xghome_classic_get_archives_year_month_tree() as $year_block) : ?>
        <section class="archive-year-block">
            <h2 class="archive-year-heading"><?php echo esc_html($year_block['year_label']); ?></h2>
            <?php foreach ($year_block['months'] as $month_block) : ?>
                <section class="archive-month-block">
                    <h3 class="archive-month-heading"><?php echo esc_html($month_block['month_label']); ?></h3>
                    <ul class="archive-post-list">
                        <?php foreach ($month_block['posts'] as $item) : ?>
                            <li>
                                <span class="archive-post-time"><?php echo esc_html($item['datetime_display']); ?></span>
                                <a class="archive-post-title" href="<?php echo esc_url($item['url']); ?>"><?php echo esc_html($item['title']); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endforeach; ?>
        </section>
    <?php endforeach; ?>
</div>
