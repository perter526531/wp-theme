# 版本变更史（CHANGELOG）

> 主题 **kabel** 与插件 **kabel-blocks** 的全部版本记录。
> 每条对应的完整工作过程见 `worklog.md`（同 Task ID）。
> 项目周期：2026-09-28 → 2026-10-10。

---

## Kabel 时代（当前）

### kabel 主题

| 版本 | 轮次 | 变更 |
|------|------|------|
| **1.0.0** | k-5~k-11 | 初版交付。fork dazhen 0.4.2 全量重命名；黑金奢华重设计（main.css 1987 行 / Barlow+Oregano 7 字体自托管 / 藏青+古铜金 tokens / 直角按钮 / 金下划线菜单 / 深色卡片）；19 页版式 + 双分类模式库 + 种子器；产品/项目/新闻 CPT + RFQ 弹窗 + 轮播 + 超级菜单；整站初始化器 + Kabel Setup 引导屏；121 张压缩样图入包 |
| **1.2.0** | k-12~17 | v3 引擎适配：enqueue 兜底 bundled v3 css、plugin-guide 更新；旧版区块自动迁移 |
| **1.3.0** | v8-main | Blocksy/WooCommerce 对标四件套：回到顶部、阅读进度条、相关文章、产品画廊灯箱 |
| **1.4.0** | v9-main | 细粒度控制主题侧：自定义颜色根源修复 + 扩色板 + Blocksy 式 Customizer 强调色 |
| **1.5.0** | 12-e/f | **页眉页脚私有块页面**（可视化编辑，空则回退经典设计）；询盘表单块页面歪斜修复 |
| **1.5.1** | 10 | Edit Header 崩溃修复配套；pot 补词条 |
| **1.6.0** | 11 | **页眉布局切换器**：Classic（带联系方式两行）/ Minimal（单行、多语言右侧）一键应用 + 编辑器内切换 |
| **1.6.1** | 12 | **版本差防呆**：插件落后于主题时 Setup 页 / 布局屏 / 页眉页脚编辑器三处显示 Update available + 一键更新（绕开 WP 隐藏 admin notices 怪癖） |
| **1.6.2** | 14 | 双导航去重 + GT 语种桥配套；种子列宽修正 |
| **1.6.3** | 15-b | 对标审查修正：theme supports（align-wide + wp-block-styles）、cta-quote pattern 双通道重写（字节级还原验证）、theme.json 清理 customSections、Setup 屏 Tools 卡（14 演示页探测 + 一键回收站 + 阅读设置自愈） |
| **1.6.4** | 16-main | **inc/cache-purge.php**：LiteSpeed/WPSC/W3TC/Rocket/SiteGround/Cache Enabler + wp_cache_flush + 通用 action，挂载四处（布局应用/种子写入/部件保存/换主题）；菜单蓝图 7 顶级收敛 + classic/minimal 列宽配平；bundled 3.10.0 |
| **1.6.5** | 17-main | 纯插件模式配平：种子同步配平列宽/字号/gap；chrome 关闭时全局色变量兜底；bundled 3.11.0 |
| **1.6.6** | 18-main | 性能版主题侧收尾；bundled 同步 3.12.0。**当前版本** |

### kabel-blocks 插件

| 版本 | 轮次 | 变更 |
|------|------|------|
| **1.0.0** | k-8~k-11 | 初版交付（fork dazhen-blocks 2.0.2 引擎重命名，17 块 spec 驱动） |
| **3.0.0** | k-12~17 | **v3 全新块套件**（仿 Stackable 一比一）：17 客户端区块自由嵌套 + emitter.php 逐字节 PHP 孪生（种子零校验警告）+ kbl_b() 旧块迁移器 + dynamic.php（询盘表单/文章网格）+ migrate.php（byte-hash 自动升级旧页标记） |
| **3.1.0** | v8-main | Stackable 六大核心能力（三断点排版/设备可见性/悬停效果/入场动画/渐变背景/形状分隔符）+ 6 新块（card/tabs/testimonial/video/team/pricing）+ 询盘清单（B2B 购物车） |
| **3.2.0** | v9-main | 细粒度第一批：间距面板 / 列宽拖拽 / section 最小高度 / button 内边距边框 / icon-box 图标大小 / text 颜色 / alpha 透明度 / 边框样式（JS+PHP twin+CSS 三端） |
| **3.3.0** | v10 | 自由版式：任意列占比（40+40+20 等）+ 增删列 + 图标框图标位置/更换 + 分区自定义 + **编辑器全中文**（287 词条，构建门禁保证完整） |
| **3.4.0** | v11 | **Stackable 列系统完整移植**（拉官方源码逐文件研究）：列系统工作流一比一 |
| **3.5.0** | 13-a | **5 个页眉元素区块**：kbl/logo、kbl/nav-menu（动态读后台菜单）、kbl/search、kbl/language-switcher、kbl/contact-info + front.js 汉堡交互 |
| **3.6.0** | 9-support-10 | 图片控制：尺寸（宽 px/高 px）/ 宽高比 / 原图选项 / 拖拽手柄（编辑器原生） |
| **3.7.0** | 10 | Edit Header 编辑器崩溃修复（React #130）+ **GTranslate 桥**（data-gt-lang/notranslate 锚点、编辑器内 gtranslate 模式切换） |
| **3.7.1** | 11 | 页眉布局切换支持（kabel-header 种子双布局） |
| **3.8.0** | 14 | 双导航去重 + **GT 桥 source 属性**（items 直通 GTranslate 语种配置，区块不再双份维护；种子升级路径） |
| **3.9.0** | 15-main | **全局颜色系统**（6 槽、设置页、区块色板注入、var 令牌存储、一处改全站跟随）+ **布局库**（18 布局 × 6 分类、SVG 线框画廊、独立预览、一键建页草稿）+ onboarding 欢迎通知 + uninstall.php + package.json 版本对齐 3.9.0 |
| **3.10.0** | 16-main | **语言切换器下拉形态**（图标+当前语言+chevron 按钮，绝对定位面板，默认 display=dropdown，旧块升级自动切换；GT 桥与 ?lang= 双通道感知；Esc/点外关闭）+ 菜单蓝图 7 顶级 + 种子列宽配平 |
| **3.11.0** | 17-main | **site-chrome 纯插件模式**：kabel-header/footer 私有页（与主题互认）、块主题模板部件替换渲染（render_block 过滤器，恰好一个不叠加）、经典主题选配注入、Kabel→Header & Footer 管理屏、管理栏入口、预览模板、主题 logo→插件 logo URL 改写；菜单链路（无位置主题也建 Primary Menu / 按名解析 / 一次性补救）；插件版缓存清理 twin；Barlow/Oregano 前台自带；编辑器通知双通道修复；版本头对齐 |
| **3.12.0** | 18-main | **性能大版**：build minify（编辑器 bundle 292K→157K，皮肤/legacy/form .min 副本）；条件加载 kbl3_front_renders_kbl()（无 kbl 页面 91KB→0）；请求级缓存 ×4 + 导航共享查询 + 字体 preload；**Tools 屏**（演示页回收 + hex→全局色令牌迁移器，双正则精确匹配、revision 可撤销）；18 张真实布局截图缩略图（webp）；6 项深挖修复（旧 zip 缺 blocks/ 目录——从 zip 安装编辑器无 Kabel 块的重大交付 bug、zh 字典 44 条缺失致构建门禁断裂、bootstrap resume 反杀、GT 桥安装时序缺口、legacy 前台泄漏、语言面板 17px 裁切）。**当前版本** |

## Dazhen 时代（前身，2026-09-28 → 10-01）

### dazhen 主题

| 版本 | 变更 |
|------|------|
| 0.1.0 | 初版（七个阶段：分析→设计→IA→方案→开发→集成→真机验收） |
| 0.4.0 | RFQ 弹窗 / 产品轮播 / 超级菜单 / 博客侧边栏 / 评论 / 多语言下拉 / Setup 引导页；块接管 6 模板 |
| 0.4.1 | 整站初始化器（全新站自动 7 页 + 菜单 + 静态首页）；DAZHEN_DIR 路径 bug / stdClass 500 / 超级菜单 walker 三修 |
| 0.4.2 | 版本不匹配死按钮根治（能力双检 + 旧版检出 + 一键更新 + 未登录跳登录） |

### dazhen-blocks 插件

| 版本 | 变更 |
|------|------|
| 2.0.0 | **自研区块引擎**（spec→engine 双端）：17 块、8 版式、18 模式、一键填充、独立可用 |
| 2.0.1 | 整站初始化器 bootstrap.php（蓝图/状态机/守卫式菜单） |
| 2.0.2 | admin-post nopriv 修复 + 权限显式报错 |

---

## 交付物版本对照

| 日期 | dist/kabel.zip | dist/kabel-blocks.zip |
|------|----------------|----------------------|
| 2026-10-10（当前） | 440 文件：主题 1.6.6 + bundled 3.12.0 | 190 文件：3.12.0（23 个 block.json 齐全） |
