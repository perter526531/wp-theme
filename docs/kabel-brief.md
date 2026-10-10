# KABEL 重建蓝图(内容 + 设计 + 架构唯一事实源)

> 任务:把用户 Hostinger 上的 Kabel Art Paint 站(darkcyan-locust-656023.hostingersite.com,
> Blocksy 免费主题 + Stackable + Smart Slider 3 + WPForms + WooCommerce 搭建,内容未完成)
> 整体迁移到我们自研体系:**kabel 主题 + kabel-blocks 插件**(fork 自已通过 E2E 的
> dazhen v0.4.2 / dazhen-blocks v2.0.2 引擎,全量重命名 + 黑金重设计 + Kabel 真实内容)。
> 移除一切第三方/收费/Pro 诱导组件;前端零 Stackable/SmartSlider 依赖。

## 1. 品牌事实(全部来自线上抓取,禁止编造)

- 品牌:KABEL(中文:卡百利)| 定位:Global high-end decorative paint brand
- 标语体系:
  - hero 眉标 "Global high-end decorative paint brand"
  - hero 主标 "Kabel Art Paint" + 次行 "from Druento"(手写体点缀)
  - 设计哲学 "Transform every wall into a timeless work of art" — KABEL Design Philosophy
- 地址:Kabel Headquarters: Druento, Torino, Italy / Kabel Branch: Foshan, Guangdong, China
- 电话(头部展示):+86 15989993891 | WhatsApp:+86 18033205148 | 邮箱:kabelpaints@gmail.com
- 社媒:FB https://www.facebook.com/profile.php?id=61575873133832 | IG https://www.instagram.com/kabelpaints/
  | YT https://www.youtube.com/@kabelpaint/videos | TikTok https://www.tiktok.com/@kabel.paint
- 核心技术主张:净醛科技 "Formaldehyde → Water";独立检测净化率 94.1%,长效净化 82.1%
- 原料伙伴:BASF、Dow Chemical、Wanhua Chemical(GREAT MATERIALS)
- 荣誉:Kabel Honorary Chief Scientist — 与诺贝尔奖得主 Professor James Fraser Stoddart 合作致敬
- 版权行:Copyrights © 2026 By Kabel - All right reserved. Privacy Policy

## 2. 设计基因(黑金奢华 · 意大利高端涂料)

- 底色:body 纯黑 #000000;区块交替 zone-a #0B0B0D(近黑)/ zone-b #101828(深藏青黑)
- 主色(按钮/链接/强调):深藏青 #222E5E;hover #101839
- 品牌点缀:古铜金 #B39A74(眉标、图标、分隔线、hover 强调、data 强调色)
- 文字:白 #FFFFFF 主文、rgba(255,255,255,.72) 次文、rgba(255,255,255,.55) 弱文
- 卡片:#26272B / #2A2C31(深灰卡面),1px rgba(255,255,255,.08) 描边,hover 提亮
- 按钮:直角(border-radius 0,与原站一致),实心藏青 #222E5E、hover #101839;
  ghost 白描边按钮;金色文字链强调
- 字体(主题内自托管,文件已备好,见 §5):
  - 标题/正文:Barlow(300/400/500/600/700 normal + 400 italic)
  - 手写点缀(eyebrow 副语/引言/“from Druento”):Oregano 400
- 头部:透明黑渐变叠 hero(顶部 transparent → 滚动后 #000),高 88px,白字菜单;
  菜单项 hover 金色下划线
- hero:全屏背景图 + 黑色遮罩(0.55-0.65),左对齐大标题(Barlow 600,clamp 2.4-4rem),金 eyebrow
- 通用:--radius 0(直角美学);focus 可见;图片圆角 0;分区留白 spacious 120px / compact 72px

## 3. 站点地图(19 页 + 归档,slug 必须与原站一致)

```
/ (front page, slug home, page)
├─ /about/            About Us(ABOUT 父项)
│  ├─ /eco-friendly/  ECO Friendly
│  └─ /designer/      Designer
├─ /products/         PRODUCTS(产品 CPT 归档,页 slug products 不建 page)
├─ COLORS(下拉)
│  ├─ /morandi-colors/          Morandi Colors
│  ├─ /van-gogh-color/          Van Gogh Color
│  ├─ /macaron-colors/          Macaron colors
│  └─ /museum-collection-colors/ Museum Collection Colors
├─ /projects/         PROJECTS(projects CPT 归档)
│  ├─ 分类 home-renovation-projects(Home Renovation Projects)
│  └─ 分类 engineering-projects(Engineering Projects)
├─ PROJECT PROCUREMENT(下拉)
│  ├─ /project-procurement/     Project Procurement
│  └─ /customize/               Customize
├─ /distributor/      DISTRIBUTOR
├─ NEWS(下拉)
│  ├─ /blog/          Blog(posts 页)
│  ├─ /company-news/  Company News(分类页)
│  ├─ /industry-news/ Industry News(分类页)
│  ├─ /knowledge/     Knowledge(分类页)
│  └─ /faq/           Faq
└─ /contact/          CONTACT
```

主导航文案层级(逐字保留):HOME / ABOUT{About Us, ECO Friendly, Designer} / PRODUCTS /
COLORS{Morandi Colors, Van Gogh Color, Macaron colors, Museum Collection Colors} /
PROJECTS{Home Renovation Projects, Engineering Projects} / PROJECT PROCUREMENT{Project
Procurement, Customize} / DISTRIBUTOR / NEWS{Company News, Industry News, Knowledge, Faq} / CONTACT
头部右侧:搜索图标 + 电话 +86 15989993891。语言切换显示 "EN"(无真实多语言,Polylang/GTranslate 存在时自动接管)。

页脚:品牌区(联系方式+社媒4图标) | 菜单列 ABOUT/Products/Colors/PROJECTS/Procurement/customize/
DISTRIBUTOR/NEWS/CONTACT | 版权行。

## 4. 页面内容规格(完整文本在 docs/kabel/site-structure.json,按本节结构落块)

通用:所有 CTA 按钮 = CONSULT NOW / Dealer Application / Request Custom Solution /
CONTACT US,锚到本页询盘区或 RFQ 弹窗(kabel_inquiry)。

**home**:hero(bg=Brand-scaled.png,eyebrow=Global high-end decorative paint brand,
h1=Kabel Art Paint,手写行=from Druento,btn=CONSULT NOW)→ split 诺奖致敬(img 2efa8714-324e-445b-8d3e-4f68e917cc8a2-1.jpg)→
split 净醛科技(img ECO-FRIENDLY-1920850-5.5版.jpg,数据 94.1% / 82.1%)→
products「Transform every wall into a timeless work of art」6 家族卡(Velvet Texture/Lime Plaster/
Stone texture/Solid Color/Microcement/Exterior paint,对应图见 json)→ features 3
(Craftsmanship effect 六道工匠技法绒感 / Wall and floor integration 墙地一体 /
Waterproof performance 防潮抗污)→ inquiry(home_inquiry)。
另:hero 顶部 loro.webp 为品牌标(可选放 footer 品牌区)。

**about**:hero(ABOUT KABEL)→ split Our Heritage(KABEL originates from Druento, Torino —
the heart of Italian decorative coating artistry;百年传承,img Stone-texture-1.webp)→
split Core Technology(Formaldehyde → Water,img 组合-1.webp)→ logos GREAT MATERIALS
(BASF/Dow/Wanhua 三图:5dccc95a/06b15871/300912f0)→ split 诺奖首席科学家(img 诺贝尔奖.jpg)→
features 5「Why Architects Choose KABEL」(Cashmere Touch/Wall & Floor Integration/
Waterproof Performance/Purify formaldehyde/Unlimited Colour,含图)→ cta。

**eco-friendly**:hero(eco friendly,Kabel is dedicated to protecting your health.)→
split 净醛原理(img 组合-2.webp)→ gallery 4 国际认证(1cc3bbaf/d3fdda34/5fdb666f/d9ae674f)→
split 净醛效果数据 → cta。

**designer**:hero(Designer)→ split 定位(An artistic coating system born for design,
img Image_20260401_085452)→ features 4(Emotion & Brand Attitude:Core Message/Visual Direction/
Key Words/Positioning Line,图 Image_20260331/施工教学/ChatGPT-Image/Gemini)→
process 3 步(01 Design Expressiveness/02 Design Freedom/03 Design Support)→
gallery 表现力效果(ltalian-stucco/图4/12412412412…)→ cta。

**products(归档)**:hero(High-end Artistic Coating Series)→ split 定位
(Inspired by Italian Aesthetics. Crafted for Timeless Spaces.,img Brand.png)→
products 6 家族(CPT 动态网格)→ cta。6 个 product CPT 条目(单页用主题产品模板:轮播+参数+RFQ):
1. Velvet Texture(雅绒)— luxury velvet matte finish(图 Velvet-Texture-1.webp/1-1.webp)
2. Lime Plaster(灰泥)— breathable mineral plaster(Lime-Plaster-1.webp/1-1.webp)
3. Stone Texture(石纹)— natural stone(Stone-texture-1.webp/1-1.webp)
4. Solid Color(净醛科莫雅绒Skin-feeling)— skin-feeling solid(Skin-feeling-paint 系列)
5. Microcement(微水泥)— seamless wall-floor(concrete-12 / Microcement-1 系列)
6. Exterior Paint(外墙)— weatherproof exterior(布艺感1 系列/sea-house…)
系列 taxonomy product_series 六项对应上面 6 家族;参数表 specs 用合理涂料参数
(成膜物/稀释/施工温度/重涂时间/理论耗量/净醛率 94.1%/VOC),不得虚构认证编号。

**morandi-colors**:hero(bg 22e3ee5c-dafe-496c-81ff-effeef9463dd.webp)→ split 灵感
(Giorgio Morandi 意大利大师经典画作)→ gallery 6 色卡
(卡百利官网-好色彩-莫兰迪色系_03/04/05/07/08/09.jpg)→ split 色彩定位/特征/应用场景
(见 json 全文)→ cta「Find colors that feel like home」。
**van-gogh-color / macaron-colors / museum-collection-colors** 同构,内容/图见 json
(van-gogh hero bg van-gogh-1.webp 等;macaron 用 1.webp 系列卡;museum 用 13.webp 色板+油画细节)。

**projects(归档+2 案例)**:hero → 筛选 chips(2 分类)→ 卡片。2 篇 projects CPT:
PROJECTS post test 1 / 2(slug projects-test-1, projects-post-test-2,图见 json,分类各一)。

**project-procurement**:hero → split 定位 → process 6 步(01 Project requirements
communication/02 Product Solution Design/03 Sample customization confirmation/04 Construction
technical support/05 Mass production supply/06 Global Freight Support)→ gallery 6 应用场景
(Velvet Texture→Luxury hotels & resorts/concrete-12→Real Estate Developers/
sea-house→Architecture Firms/净醛科莫雅绒2→Commercial Spaces/都灵雅绒1→Government Projects)→ cta。

**customize**:hero(Tailored Solutions for Your Market)→ split → features 6 定制服务
(Product performance/Color System/Artistic Effects/Packaging(图 2026/09/Image_20260327_171408)/
Global Supply Chain(cross-border-feature_nologo.jpg)/Technical & Service Support)→
process 定制流程(Submit→Evaluation→…,见 json)→ cta Request Custom Solution。

**distributor**:hero(Franchisees)→ split Bring Premium Decorative Coatings to Your Market →
features 5(Premium Products/Market Localization/Technical Training/Showroom Support/Reliable
Support,btn=Dealer Application)→ split More Than Paint(img 12412412412-2)→ features 3
(Product Differentiation(净醛科莫雅绒1)/Service-Based Revenue(Rome-crystal)/Channel
Expansion(Baby-paint))→ process 4(Multi-Level Product Portfolio/Market-Specific Product
Selection/Replicable Sample & Application System/Showroom and terminal support)→ cta。

**blog + 分类页 + 3 文章**:文章(slug 逐字保留):
1. kabel-redefines-the-fifth-wall-bringing-color-and-materiality-beyond-the-walls
2. kabel-decorative-coatings-first-singapore-showroom-officially-unveiled
3. waterproof-microcement-wall-paint-create-stylish-waterproof-textured-walls-for-modern-home-interiors
正文全文在 json 的 events 里(按序拼接),分类:1/2=company-news,3=industry-news 或 knowledge
(以 json cats 归属为准)。

**faq**:faq 块 15 问答,问题与答案逐字取 json(如 What is the most popular wall painting
trend for 2026? 等)。注:原站两题答案错位(Is this paint scrub-resistant 与 Are Kabel's
artistic paints water-based or oil-based 答案重复)——后者改为真实合理答案:水基环保
(water-based, low VOC)描述,与 eco 主张一致。

**contact**:contact-cards(总部 Druento, Torino, Italy/分公司 Foshan, Guangdong, China/
WhatsApp +86 18033205148/Email kabelpaints@gmail.com)+ inquiry 表单 + 社媒链接组。

## 5. 资产清单

- 图片:107 张被引用原图(源:/home/z/ref-site/wp-content/uploads/,映射表
  docs/kabel/used-images.json)。主控已压缩转 webp 放入
  wordpress-theme/kabel/sample-assets/(文件名=原文件名,全小写,后缀 .webp)。
  命名含中文/特殊字符的已转拼音安全名(见 sample-assets/manifest.json:orig→new 映射)。
  bootstrap 按「先按 _wp_attached_file 尾名复用、无则 sideload」接入,兼容用户现有站。
- 视频 3 支(产品特色视频1/2/3,共 94MB)不入包:迁移指南注明在原站媒体库已存在,无需搬运。
- 字体(源 /home/z/ref-site/wp-content/uploads/fonts/):barlow normal 300/400/500/600/700
  + barlow italic 400 + oregano 400(normal+italic 已备 wordpress-theme/kabel/assets/fonts/)。
- 丢弃:2022/10 全部(Blocksy 餐厅演示残留)、slider2/slide1-5(峡湾/极光/伦敦演示图)、
  slider3/thumbnail*(Smart Slider 演示缩略图)、所有 -150x150/-300x/-768x/-1024x/-scaled 缩图。
  (slider/cache/** 中被页面引用的 morandi/van-gogh 系列保留——主控已按 manifest 归一化。)

## 6. 架构与文件分工(不得越界修改)

引擎=三层:PAGE 层(布局/模式/引导)→ 内容块层(17 个 kabel-blocks/* 动态块)→
块层(section/cta-quote/inquiry)。spec(inc/blocks-*.php)→ engine.php 统一注册渲染;
编辑器 JS 为通用运行时(读 window.KabelBlocksSpecs)。**改内容不动 JS,改样式不动 PHP 结构。**

| 文件 | 职责 | 本轮 |
|---|---|---|
| kabel-blocks/inc/engine.php | dzb→kbl 引擎/壳/序列化 | 仅核对 |
| kabel-blocks/inc/blocks-{content,convert,proof}.php | 17 块 spec | 仅内容微调(默认文案/选项) |
| kabel-blocks/assets/js/blocks-editor.js | 通用编辑器运行时 | 不动 |
| kabel-blocks/inc/{layouts,patterns,seeder}.php | 布局/模式/种子 | **重写内容**(子代理B) |
| kabel-blocks/inc/bootstrap.php | 整站初始化器 | **重写蓝图**(子代理C) |
| kabel-blocks/inc/leads.php | 独立询盘 | 核对 |
| kabel/inc/{setup,enqueue,customizer,...}.php | 主题框架 | 仅文案 |
| kabel/assets/css/main.css + editor.css | 主样式 | **黑金重设计**(子代理A) |
| kabel/assets/js/main.js | 前台交互(RFQ/轮播/菜单) | 选择器已随重命名同步,不动逻辑 |
| kabel/inc/{post-types,meta-fields,metabox-ui}.php | product/projects CPT+参数面板 | 标签文案(子代理C) |
| kabel/inc/plugin-guide.php | Kabel Setup 引导页 | **重写**(子代理C) |
| kabel/inc/{patterns,layouts 相关主题侧} | 主题侧接线 | 核对 |
| kabel/sample-content/dazhen-sample.xml → kabel-sample.xml | 演示内容 | **重写**(子代理C) |
| kabel/bundled/kabel-blocks | 捆绑插件副本 | 主控同步 |

## 7. 命名规范(已完成的全局重命名,新代码必须遵守)

- 主题:slug/folder `kabel`,文本域 `kabel`,函数前缀 `kabel_`,常量 `KABEL_*`,
  CPT `kabel_product`… 保留:wp 标准 hook。
- 插件:`kabel-blocks`,文本域 `kabel-blocks`,函数前缀 `kbl_`/`kabel_blocks_`,
  块命名空间 `kabel-blocks/*`,块分类 `kabel-blocks`,CSS 类 `kbl-*`,spec 注入
  `window.KabelBlocksSpecs`,REST 命名空间 `kabel-blocks/v1`,admin-post 动作 `kabel_bootstrap`。
- 询盘:CPT `kabel_inquiry`(主题激活时)/`kbl_lead`(插件独立),表单域 `kbl_*`。
- 任何新字符串直接写英文(站点为英文站),pot 由主控统一补。

## 8. 红线

1. 不引入任何第三方付费/Pro 组件;不注册 Smart Slider/Stackable 兼容层。
2. 不虚构认证编号、专利号、检测机构编号;94.1%/82.1% 之外不新增数字主张
   (六道工艺/上千色彩库等 json 原文可继续用)。
3. 保持引擎架构(spec→engine→JS runtime)零改动——内容与样式外的一切结构冻结。
4. 文件只写自己分工内的;公共疑问在 worklog 记录,由主控裁决。
