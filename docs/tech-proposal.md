# 阶段 4:WordPress 主题技术方案

> 状态:✅ 2026-09-29 用户确认完毕(多语言方案 A / 询盘方案 B + 邮件转发通知),阶段 5 开发已启动。
> 前序依据:信息架构 v3(docs/ia-structure.md)、设计系统(docs/design-system.md)、
> 首页高保真稿 v1(已确认)。
> 本文档中「我拍板」为技术负责人决策项(纯技术,只汇报);「待确认」为需用户知情的选型。

## 0. 一句话概括

把已确认的视觉与结构,工程化为一个**独立、零商业依赖、上传即用的 WordPress 主题包**
(`dazhen.zip`),替换原站的 Blocksy + Stackable + Smart Slider + WooCommerce 组合。

---

## 1. 主题形态:经典主题 + 区块编辑器全面支持(我拍板)

**决策:开发经典主题(classic theme),同时全面适配区块编辑器。不用块主题(FSE)。**

| 维度 | 经典主题(选定) | 块主题 FSE |
|------|----------------|-----------|
| 模板控制 | PHP 模板 100% 掌控 | 需适配 blocks.json/html 模板体系 |
| A×B 深浅节奏、仪表动效、选型向导、交付地图 | 完全可控 | 实现成本极高,编辑体验差 |
| 新闻正文编辑 | 照常用区块编辑器(所见即所得) | 同样用区块编辑器 |
| 团队上手 | 传统 WP 开发者都懂 | 较新生态,定制模式文档分散 |

- 内容策略:**结构化内容(产品参数/案例数据/方案字段)走自定义字段表单;叙述性长文(新闻正文、关于页文案)走区块编辑器**——两类内容各用最合适的编辑方式。
- `theme.json` 定义编辑器设计令牌(A/B 双色板、字号阶、间距尺度),编辑新闻时与前台所见一致。
- 最低环境:**WordPress 6.5+ / PHP 8.1+**,GPL v2+ 许可,text domain `dazhen`。

## 2. 主题目录结构(我拍板)

```
wordpress-theme/dazhen/
├── style.css                 # 主题头信息 + 版本注释(规范要求)
├── functions.php             # 引导文件:仅 require,不放逻辑
├── theme.json                # 设计令牌:色板/字号/间距 → 编辑器与前台共享
├── inc/                      # 核心逻辑(按职责分文件,每文件单一职责)
│   ├── setup.php             # 主题支持(add_theme_support)、菜单注册、图像尺寸
│   ├── enqueue.php           # CSS/JS 资源加载(版本号与 filemtime 绑定)
│   ├── post-types.php        # CPT + taxonomy 注册(见 §3)
│   ├── meta-fields.php       # 字段注册(register_meta + show_in_rest)
│   ├── metabox-ui.php        # 自制字段控件库(文本/选择/图片/repeater/编辑器)
│   ├── inquiry.php           # 询盘表单:渲染/校验/防垃圾/落库/邮件
│   ├── inquiry-admin.php     # 后台询盘管理中心(列表列/状态/导出 CSV)
│   ├── pll-compat.php        # Polylang 兼容层(dazhen_pll_* helper)
│   ├── template-tags.php     # 模板辅助函数(面包屑/分页/卡片渲染)
│   ├── editor.php            # 区块编辑器适配(editor styles/区块分类)
│   └── customizer.php        # 主题定制器(联系信息/社交链接/CTA 文案)
├── templates/                # ~~模板层级~~ ⚠️ 勘误(2026-09-29 实施修正):
│                              # 经典主题的模板层级只从主题根目录解析,
│                              # 实施时 header/footer/front-page/single/archive
│                              # 等层级模板已移至主题根目录(见 README),
│                              # templates/ 子目录已取消;子目录仅保留
│                              # template-parts/ / page-templates/ / inc/ 等
│   ├── header.php / footer.php
│   ├── front-page.php        # 首页(A×B 节奏全貌)
│   ├── index.php / single.php / page.php / archive.php
│   ├── single-product.php / archive-product.php
│   ├── single-case_study.php / archive-solution.php
│   └── search.php / 404.php
├── template-parts/           # 可复用区块(与设计系统 §5 节奏对应)
│   ├── hero-home.php         # 含速热仪表动效
│   ├── section-stats.php / section-features.php / section-tech.php
│   ├── card-product.php / card-case.php / card-solution.php / card-post.php
│   ├── finder.php            # 智能选型向导
│   ├── delivery-map.php      # 全球交付地图
│   ├── form-inquiry.php      # 统一询盘表单
│   └── cta-*.php             # 底部 CTA 等转化区块
├── assets/
│   ├── css/                  # 构建产物 + source
│   ├── js/                   # ES modules:heating-gauge / finder / reveal / nav
│   ├── fonts/                # 自托管 woff2(Space Grotesk / Inter)
│   └── img/                  # 主题静态图(纹理/图标/默认 OG 图)
├── languages/                # dazhen.pot(翻译模板)+ en_US 编译文件
└── page-templates/           # 静态页面模板(about/solutions 总览/services/contact)
```

## 3. 数据模型(我拍板)

### 内容类型(CPT)

| 类型 | 标识 | 用途 | 关键字段(metabox) |
|------|------|------|---------------------|
| 产品 | `product` | 10 系列产品 | 型号、副标题、卖点 bullets、**参数表 repeater**(组名/参数名/参数值/单位)、图集、分类:系列(taxonomy `product_series`)、NEW/热销标记 |
| 案例 | `case_study` | Solutions 内案例库 | 客户国家、客户类型(分销商/酒店/教育/医疗/办公)、订单量、需求、挑战、方案、客户反馈、关联产品(多选)、地区(taxonomy `case_region`) |
| 方案场景 | `solution` | 4 个场景着陆页 | 场景图标、痛点列表、方案构成、推荐产品(多选)、关联案例(自动按 taxonomy 汇聚) |
| 询盘 | `dazhen_inquiry` | 询盘落库(**private**,不在前台暴露) | 公司名/联系人/电话/邮箱/国家地区/感兴趣产品/留言/来源页面/状态(新询盘/已跟进/已成交)/IP 与 UA(审计) |
| 新闻 | 原生 `post` | 公司/行业新闻 | 原生分类两个:Company News / Industry News |

### 字段引擎:原生自制,不用 ACF(我拍板,关键取舍)

- 自制轻量 metabox 控件库(`inc/metabox-ui.php`):文本/文本域/下拉/媒体选择(调用 WP 媒体库 modal)/repeater(JS 增删行),预计 ~900 行。
- **理由**:① 零第三方依赖,交付物干净,不受 ACF/SCF 治理风波影响;② post_meta 存储结构与 ACF 兼容,未来要换 SCF 可平滑迁移;③ 性能无插件开销。
- **代价**(如实告知):字段编辑界面是"够用、朴素"的 WP 原生风格,不如 ACF 精致;不影响日常编辑效率。

## 4. 询盘系统(方案 B 推荐,待用户确认)

### 推荐:主题内置自研

```
前台表单(统一 7 字段,全站 3 处入口:Services/Contact/产品页按钮)
  → POST admin-post.php + nonce 校验
  → honeypot 隐藏字段 + 频率限流(同 IP transients,如 60 秒 3 次)
  → 校验(必填/邮箱格式/长度,失败带错误回显与已填保留)
  → 落库:CPT dazhen_inquiry(private)
  → 通知:wp_mail() 邮件转发(见下方邮件转发通知,2026-09-29 用户新增需求)
  → 用户看到感谢页(带继续浏览引导)
```

### 邮件转发通知(用户确认的新需求,2026-09-29)

1. 默认收件人:站点管理员邮箱(get_option('admin_email'))
2. 转发邮箱:主题设置(定制器)可配置多个,逗号分隔(如 sales@ / boss@)
3. Reply-To 头设为客户填写的邮箱 → 销售/老板在邮件里点"回复"即直接回给客户
4. 邮件为 HTML 模板(简洁 A×B 风格表格,含全部询盘字段 + 来源页面 + 时间)
5. 送达率建议:上线时配置 SMTP 发信插件(WP Mail SMTP 等),主题不绑定具体插件

**后台「询盘」管理中心**(模拟界面见预览页):独立菜单、列表(公司/国家/产品/时间/状态)、
详情查看、状态流转、CSV 导出;新询盘后台角标提醒。

### 备选对比

| 方案 | 成本 | 后台管理 | 样式统一 | 维护 |
|------|------|----------|----------|------|
| **B. 主题内置自研(推荐)** | 开发量计入主题 | ✅ 原生集成,开箱即用 | ✅ 完全继承 A×B | 主题维护,零外部依赖 |
| A. Contact Form 7(原站同类) | 免费 | ❌ 默认不落库,需再装 Flamingo 类插件 | ⚠️ 重度自定义 CSS | 两三个插件叠加 |
| C. 商业表单(WPForms 等) | ~$50+/年 | ✅ | ⚠️ 可定制但受限 | 年费 + 第三方 |

## 5. 多语言策略(用户最终选型:GTranslate)

### 定稿(2026-09-29 用户更正确认)

**采用 GTranslate 插件**(自动机器翻译,谷歌翻译引擎):

- 安装激活即得 100+ 语言,**零翻译编辑工作量**——对首发英文、快速覆盖全球买家最省力
- 免费版:翻译在前端即时完成,URL 形如 `yoursite.com/?lang=de`(搜索引擎收录较弱)
- 付费版(未来可选):独立语言 URL(`yoursite.com/de/`)+ 可编辑译文 + neural 翻译,适合重点市场 SEO
- 主题适配(i18n-compat.php):检测 GTranslate 短代码,激活后头部自动渲染其切换器;保留 Polylang 人工翻译升级路径;单语言时切换器隐藏

### 历史对比(当时推荐,已被用户选型取代,保留作参考)

```
现在(主题开发)            上线(首发)              二期(按市场需求)
────────────────────      ────────────────────      ────────────────────
dazhen_pll_* 兼容层    →   仅 EN,无插件、零开销   →   安装 Polylang 免费版
全字符串翻译就绪            语言切换器自动隐藏          添加泰语/西语 → 切换器出现
hreflang / 语言前缀预留     URL 结构干净               案例语言关联开箱即用
```

- 兼容层设计:所有 Polylang 函数调用经 `dazhen_pll_*` 封装(函数存在性检测),
  插件未激活时自动降级为单语言行为 → **上线更干净,加语言时不需要改主题代码**。

### 插件选型对比(若二期启用)

| 插件 | 费用 | 特点 | 适配本站 |
|------|------|------|----------|
| **Polylang(免费版)** | $0 | 80 万+ 安装,轻量,CPT/taxonomy 翻译支持完善 | ✅ 覆盖需求 |
| Polylang Pro | ~€99/年起 | 重复内容、翻译管理界面 | 二期量大再考虑 |
| WPML | ~$99/年起 | 功能最全,深度集成 WooCommerce,翻译工作流强 | 当前无电商,略重 |
| MultilingualPress | 免费起 | 多站点架构,运维复杂 | ❌ 不适合 |

## 6. 前端架构与性能(我拍板)

- **CSS**:原生现代 CSS(Custom Properties 承载设计令牌,与 theme.json 同源映射);
  构建仅 autoprefixer + minify,产物单文件,gzip 预算 ≤60KB。
- **JS**:原生 ES modules,按页面条件加载:
  `heating-gauge.js`(SVG + requestAnimationFrame 速热仪表)、`finder.js`(选型向导)、
  `reveal.js`(IntersectionObserver 滚动显现)、`nav.js`(头部/移动菜单)。
  无 jQuery、无 React、无第三方轮播库(轮播原生实现)。
- **动效无障碍**:全部动效遵循 `prefers-reduced-motion`(用户系统开启减弱动态时自动静止)。
- **图片**:WP 原生 responsive images(srcset/sizes)+ 原生 lazy;产品图注册专属尺寸
  (卡片方形 600×600、详情大图 1200×1200、banner 1920×800 → 避免原图直出)。
- **字体**:Space Grotesk(标题 500/700)+ Inter(正文 400/500/600)自托管 woff2 子集
  (latin),`@font-face` + 关键字重 `<link rel="preload">`;**Noto Sans SC 在二期中文
  接入时再加载**(首发英文站不下载中文字体,约省 1MB+)。
- **安全清单**:所有输出转义(esc_html/esc_attr/esc_url/wp_kses_post)、表单 nonce、
  honeypot、能力检查(current_user_can)、SQL 零手写(全部 WP_Query/prepare)、
  询盘表单无文件上传、禁用文件编辑器建议(上线 checklist)。
- **SEO**:语义化模板 + schema.org(Product/Article/BreadcrumbList JSON-LD)、
  动态 OG 图(默认品牌图)、sitemap 交给 WP 核心、`wp_title`/canonical 规范输出。

## 7. 交付物与开发计划(阶段 5 起执行)

**交付物**:`wordpress-theme/dazhen/` 完整主题源码 + `dazhen.zip`(可直接上传安装)+
内容补充清单(产品参数等待用户提供的数据)。

阶段 5 任务拆解(每步可验证):

1. 主题骨架:目录/style.css/functions.php/theme.json + 设计令牌落地(CSS 变量)
2. 字体自托管 + 基础布局(头部导航/页脚/容器栅格/响应式断点)
3. CPT + 字段引擎 + 示例内容(6 个现有产品入库,含参数表结构演示)
4. 首页模板移植(已确认的高保真 v1 → PHP,含速热仪表/数据条/各区块)
5. 产品中心(列表/筛选/详情/参数表/相关产品)
6. Solutions(总览/4 场景/案例库/案例详情/交付地图)
7. 选型向导 + Services(OEM 之旅)+ About + Contact + Blog
8. 询盘系统(表单/落库/后台管理/邮件)
9. 搜索/404/面包屑/JSON-LD/微交互打磨

其中 4~7 与用户逐页确认(阶段 6),8 完成后做全站联调(阶段 7)。

## 8. 用户确认结果(2026-09-29,含同日更正)

1. **多语言插件:GTranslate** ✅(更正确认:用户澄清"tr"为笔误,实指 GTranslate 自动机器翻译插件)
   —— 装机即得 100+ 语言零编辑成本;免费版 ?lang=xx URL;重点市场未来可升付费版;主题 i18n 兼容层同时支持 GTranslate 与 Polylang
2. **询盘表单方案:方案 B** ✅ —— 主题内置自研,用户原话确认:"后台直接看询盘/跟进/导出,样式完全统一,零第三方"
3. **新增需求:邮件转发通知** ✅ —— 已纳入询盘系统设计(见 §4 邮件转发通知)
4. 【遗留提醒】品牌名 Dazhen vs HUIZHEN 汇真:当前按 Dazhen 继续,不阻塞开发
