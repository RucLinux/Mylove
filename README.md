# Mylove 主题使用说明

面向 WordPress 的博客主题：经典三栏思路（左侧导航 / 中间内容 / 右侧信息栏），含伪静态、归档与相册虚拟路由、评论与 Markdown 等扩展。  
**环境要求：** WordPress 6.0+，PHP 8.0+（与 `style.css` 中声明一致）。

## 主题版权与信息

| 项目 | 内容 |
|------|------|
| 主题名称 | Mylove |
| 主题作者 | RucLinux |
| 官方网站 | [https://www.myzhenai.com.cn/](https://www.myzhenai.com.cn/) |
| 演示网站 | [https://www.myzhenai.com.cn/](https://www.myzhenai.com.cn/) |
| 演示网站 | [https://www.mybabya.com/](https://www.mybabya.com/) |

主题文件头与 `functions.php`、`assets/css/theme.css` 顶部注释中均包含上述信息；`style.css` 的 WordPress 主题头（Theme Name / Author / URI）与之一致。

---

## 1. 安装与基础

1. 将主题目录上传到 `wp-content/themes/`（例如线上路径 `.../wp-content/themes/mylove/`）。
2. 后台 **外观 → 主题** 中启用 **Mylove**。
3. **外观 → 菜单**：创建菜单并指派到 **Primary Menu**（`primary`），用于桌面端左侧导航等区域。
4. **外观 → 小工具**：主题注册 **`Right Sidebar`（`sidebar-right`）**。侧栏默认已包含「热门 / 评论 / 随机 / 博客信息 / 标签云」等区块；可在同一侧栏区域追加小工具。
5. **站点图标 / Logo**：支持 `custom-logo` 与站点图标；左侧头像区域优先使用主题目录下 `images/logo.png`（若存在）。

---

## 2. 后台主题菜单（外观下）

| 菜单项 | 作用 |
|--------|------|
| **Mylove 设置** | 伪静态与文章链接格式、分类/标签/页面 `.html`、顶栏「统计 / 时光机」开关、二维码 API、评论联系方式正则、正文中图片/音视频尺寸、外链视频解析 API、图片水印、默认列表缩略图、赞赏码、头像源、防采集、**左右栏布局互换**、左侧头像尺寸等。保存后会按设置刷新重写规则。 |
| **备案管理** | 维护 ICP / 公安备案等 HTML 片段，支持默认内容与**按访问域名**的独立规则（数据写入选项表）。当前主题包内 `footer.php` 若仍写死备案链接，可与子主题或自定义页脚结合使用这些选项。 |
| **统计代码** | 头部 / 底部统计脚本（经允许的 HTML/JS），分别注入 `wp_head` / `wp_footer`。 |

**提示：** 修改伪静态或 `.html` 相关选项后，若前台链接异常，到 **设置 → 固定链接** 点一次「保存更改」以刷新规则。

---

## 3. 固定链接与 `.html`（Mylove 设置）

- 可选择文章链接预设（如 `/%postname%.html`、`/archives/%post_id%.html` 等），并可为文章路径设置**前缀**（避免与根目录页面 `.html` 冲突）。
- 可分别开启：**分类**、**标签**、**页面** 以 `.html` 结尾（会注册对应 `rewrite_rule`）。
- 若同时开启「页面 `.html`」且文章规则也在根目录生成 `.html`，主题会提示可能的**页面与文章同名冲突**，建议为文章加前缀。

---

## 4. 虚拟路由（无需新建页面也可访问）

在 **设置 → 固定链接** 保存后，以下地址由主题注册（具体是否带 `.html` 与「页面 `.html`」开关一致）：

| 地址 | 说明 |
|------|------|
| **`/archives/`** 或 **`/archives.html`** | 文章归档树（年月分组）。若已存在 **固定页面** 且别名为 `archives`，则优先使用该页面的固定链接与 `page-archives.php` 模板。 |
| **`/album/`**、**`/album/page/2/`** … | 虚拟相册页：九宫格展示随机媒体库图片；若存在分类 **别名 `image`**，下方会列出该分类文章。 |

另：访问分类 **slug 为 `image`** 的归档时，使用 `category-image.php`，效果为「顶部相册九宫格 + 该分类列表」。

---

## 5. 固定页面与模板文件（按 WordPress 模板层级）

通过新建**页面**并设置合适的**别名（slug）**，可使用下列 PHP 模板（文件名在主题根目录）：

| 文件 | 典型用途 |
|------|----------|
| `page-about.php` | 关于页（展示修改日期等） |
| `page-archives.php` | 归档页（与虚拟 `/archives/` 二选一或并存） |
| `page-guestbook.php` | 留言板：含评论排行榜、说明文案，并加载评论表单 |
| `page-links.php` | 友情链接：正文 + 自动列出 **链接管理** 中的友链（按链接分类分组） |

**友链：** 需在后台启用 **链接**（若被隐藏，可用插件或 `functions.php` 打开「链接管理」）。左侧导航与移动抽屉中的友链也依赖 `wp_list_bookmarks`。

---

## 6. 文章与阅读体验

- **特色图像 / 缩略图：** 列表与侧栏缩略图顺序大致为：特色图 → 附件图 → 正文 `[gallery]` → 区块中的图片 → 正文首张 `<img>` 等（见 `xghome_classic_get_list_thumbnail_url`）。
- **浏览量：** 单篇文章访问会累加 `_xghome_post_views`，用于「热门」等排序。
- **Markdown：** 文章/页面编辑侧栏 **「Markdown 模式」** 勾选后，前台按主题内置规则渲染（代码块、部分行内语法等）。
- **代码高亮：** 引入 Highlight.js；正文 `<pre><code>` 会尝试高亮。
- **工具按钮：** 字号循环、朗读（Web Speech）、阅读模式（隐藏侧栏等）。
- **赞赏：** 在 Mylove 设置中开启并填写微信 / 支付宝收款码图片 URL。
- **文章二维码：** 配置二维码图片 API 列表（每行一个 URL，可用 `{url}` 占位符）。
- **文末展示：** 可显示最后修改时间；部分环境会展示访客 IP / 地理与 UA 图标（依赖主题内相关函数，需服务器正常提供 `$_SERVER` 信息）。

---

## 7. 评论

- 评论表单中 **「联系方式」** 为一栏，可填**邮箱或手机号**（正则可在 Mylove 设置中分别配置）。
- 手机号会写入评论 meta（`xghome_phone`）。
- 评论框支持 **表情选择器**（与主题 JS 一致）；文章编辑器下方也可插入表情（编辑文章界面）。

---

## 8. 媒体与水印

- 正文区域图片 / 视频 / 音频 的最大宽度或高度可通过 CSS 变量控制（在 Mylove 设置中调整百分比或像素）。
- 可配置 **图片 URL 水印** 与 **文字水印**（由前端脚本读取 `window.XGHOME_WATERMARK` 处理，具体以当前 `theme.js` 行为为准）。
- **视频解析 API：** 若填写，用于部分外链视频的播放解析（按主题实现）。

---

## 9. 防采集（可选）

在 Mylove 设置中可开启：**禁用右键**、**禁用常见复制/开发者快捷键**、**复制时追加来源链接** 等；配置注入 `window.XGHOME_ANTI_SCRAPE`。仅增加采集成本，无法完全防止拷贝。

---

## 10. 布局与其它

- **顶栏增强：** 关闭后，顶部「统计 / 时光机」按钮及移动端顶栏相关入口可按主题设计隐藏或简化。
- **左右互换：** 开启后，左侧菜单栏与右侧小工具栏在布局上互换（见 `xghome_layout_swap`）。
- **左侧头像尺寸：** 约 40–120px 范围内可调。
- **Cravatar 等头像源：** 在设置中选择预设头像加速源（若已配置）。

---

## 11. 静态资源与缓存

主题主要样式与脚本为：

- `assets/css/theme.css`
- `assets/js/theme.js`

版本号在 `functions.php` 的 `xghome_classic_enqueue_assets` 中通过 `wp_enqueue_*` 的第四个参数传递；更新主题后若浏览器缓存旧文件，可适当提高版本号或强制刷新。

---

## 12. 文件结构速查（常用）

| 路径 | 说明 |
|------|------|
| `functions.php` | 主题逻辑、选项、重写规则、评论、Markdown、侧栏数据等 |
| `header.php` / `footer.php` | 站点头部、布局闭合与页脚 |
| `single.php` | 单篇文章 |
| `index.php` / `archive.php` / `category.php` / `tag.php` / `search.php` | 列表与归档 |
| `sidebar.php` | 右侧栏默认 HTML（Tab、博客信息、标签云等） |
| `comments.php` | 评论模板 |
| `album-page-standalone.php` / `archive-tree-standalone.php` | 虚拟相册 / 虚拟归档 |
| `category-image.php` | `image` 分类归档 |
| `page-*.php` | 各固定页面样式 |
| `template-parts/` | 可复用片段（如相册、归档树） |
| `assets/` | CSS、JS、第三方库（Bootstrap、Highlight 等） |

---

## 13. 故障排查简要

- **归档或相册 404：** 到 **设置 → 固定链接** 保存；确认服务器允许 WordPress 重写（如 Nginx / Apache 规则）。
- **`.html` 不生效：** 检查 Mylove 设置中对应开关是否勾选，并再次保存固定链接。
- **友链不显示：** 确认已安装/启用链接功能且添加了链接；移动端见抽屉内「友链」区块。

---

*文档版本与主题代码同步维护；若行为与后台文案不一致，以 `functions.php` 及实际模板为准。*
