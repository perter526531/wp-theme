# PROJECT.md — Dazhen WordPress 主题开发项目核心文档

> 本文档是项目的唯一权威状态记录。每确认一个重要决定后必须同步更新本文档,
> 以保证任何情况下都可以通过本文档恢复项目状态。

- **项目名称**:Dazhen(大振科技)企业官网 WordPress 主题
- **客户网站(资料来源)**:https://dazhenglobal.com/
- **文档最后更新**:2026-09-30(第十二轮:沙箱内真机验收闭环 —— WP 7.1.2+SQLite 实装交付 zip、WXR 导入、全站 E2E、询盘落库、修 3 个真机 bug、重打包;**七个阶段全部完成**)
- **当前所处阶段**:项目交付完成(阶段 7 闭于 2026-09-30);待客户:素材补充/品牌名澄清/上线部署(SMTP+缓存插件建议)

---

## 1. 项目目标

为 Dazhen(大振科技)从零开发一个定制 WordPress 主题,替代当前网站所使用的
通用商业主题(Blocksy + Stackable 区块 + WooCommerce),实现:

1. 品牌化的视觉呈现与更好的用户体验
2. 面向海外 B2B 客户的**询盘转化导向**的信息架构
3. 干净、可维护、遵循 WordPress 官方编码标准的主题代码
4. 为后续内容更新(产品、案例、新闻)提供良好的编辑体验

## 2. 公司与网站定位(基于官网资料的研究结论)

### 公司概况
- **公司**:Dazhen Tech(大振科技),位于广东省惠州市大亚湾
- **业务性质**:智能饮水设备制造厂商,面向全球市场提供 **OEM/ODM 一站式服务**
- **成立时间**:研发团队始建于 2012 年,十余年技术积累
- **联系方式**:+86 180 2699 3135 / 2046772003@qq.com / 惠州市大亚湾

### 核心技术(自主研发)
- 纳米稀土厚膜加热技术(行业首创,3 秒速热、恒温)
- 双算法智能控制模块
- 即热+即冷一体化(2021 年首创即热制冷一体化)
- 富氢水技术、凉白开(煮沸冷却)平台、气泡水技术
- UV 杀菌、RO 反渗透过滤

### 产品体系(10 个系列)
嵌入式系列(F900)、工程系列、冰水龙头系列(含即冷龙头/即热冷龙头/气泡水龙头三个子系列)、
即热水龙头系列、即冷系列、凉白开系列、自吸式系列、壁挂式系列、宠物饮水机。
代表型号:F4、F6、F9、F10、F11、F12、F13、F15、F16、F18、F900、18E 系列等。

### 目标客户(从案例与内容推断)
- 海外经销商 / 分销商(已有英国、泰国家庭市场案例:1500 台冰水机 + 1800 台气泡水机订单)
- 国际酒店集团(大型场馆定制饮水解决方案案例)
- 教育、医疗机构(安全饮水方案)
- 办公与商业场所
- 电商品牌商(有"电商专供 F9"型号)

### 服务能力
- OEM/ODM 全流程:需求沟通 → 概念开发 → 规格确认 → 打样测试 → 生产质控 → 包装出货 → 交付后支持
- MOQ 约 100 件起;定制周期 4–6 周
- 资质:ISO9001、BSCI、食品级材料标准
- 关键数据:10+ 年技术积累 / 30+ 专利技术 / 20+ 核心研发工程师

### 网站核心目标(B2B 获客型官网)
1. **获取询盘**(Get a Quote / Inquiry Form 是全站核心转化点)
2. 展示产品与技术实力
3. 建立信任(发展历程、专利资质、成功案例)
4. 支撑全球市场拓展(英文站,面向海外买家)

## 3. 项目流程与当前进度

| 阶段 | 内容 | 状态 |
|------|------|------|
| 1. 公司资料理解 | 阅读 dazhenglobal.com 全部资料并总结 | ✅ 已完成(见 docs/site-analysis.md) |
| 2. 网站页面结构确认 | 提出信息架构方案,与用户讨论确认 | ✅ 已确认(后修正为 v3,见 docs/ia-structure.md) |
| 3. 视觉风格与设计方案确认 | 方向选择 + 设计系统 + 首页高保真 | ✅ 已完成(2026-09-29 用户确认首页高保真 v1,含 Hero 18E-2 与文案口吻) |
| 4. WordPress 主题技术方案确认 | 主题架构、目录结构、多语言选型 | ✅ 已完成(2026-09-29 定稿:多语言 GTranslate、询盘自研 + 邮件转发通知) |
| 5. 开始开发 | 搭建主题骨架 | ✅ 已完成(2026-09-29 第六轮收官:选型向导/交付地图/静态页 patterns/WXR 示例内容包全部落地;32 个 PHP 文件过语法门禁) |
| 6. 页面逐项实现和确认 | 逐页开发并请用户确认 | 🔶 进行中(①②③产品/Solutions/Services 均默认通过且 PHP 已落地;④About/Contact/Blog 三页设计稿已上线待确认 6 项 —— 最后一轮) |
| 7. 测试与完善 | 兼容性、响应式、细节打磨 | ⬜ 未开始 |

## 4. 已确认的网站页面结构与设计决策

### 2025-09-28 第二轮确认(5 项)
1. **视觉方向:A×B 组合**(深色展示说服 + 浅色阅读信任)→ 设计规范见 docs/design-system.md
2. **首发语言:英文**;其他语言后续用多语言插件扩展
3. **结构修正:Cases 归入 Solutions**("Solutions页面放Cases的内容")→ 信息架构 v3,7 个顶级板块
4. **创新模块:核心 4 项全做**(速热 Hero / 选型向导 / 交付地图 / OEM 之旅);可选项转 backlog
5. **品牌素材:先用现有网站的**(已下载至 public/assets/,经 Jetpack CDN)

### 2026-09-29 第三轮确认(阶段 3 收官)
1. **首页高保真设计稿 v1 确认通过**——含 Hero 主推 18E-2、直接自信型文案口吻、A×B 区块节奏、速热仪表动效、Solutions+Cases 合并区块
2. 阶段 3 全部完成,信息架构 v3 + 设计系统规范 + 品牌素材清单全部定稿

### 2026-09-29 第四轮确认(阶段 4 定稿)
1. **多语言:GTranslate 插件**(更正确认:用户此前回复"tr"为笔误,实指 GTranslate)—— 自动机器翻译插件(谷歌翻译引擎):装上即有 100+ 语言、零翻译工作量;免费版 URL 为 ?lang=xx 参数(SEO 收录较弱),未来重点市场可升级付费版(独立 URL /de/)或再评估 Polylang 人工翻译,主题两者均兼容
2. **询盘表单:方案 B 主题内置自研** —— 用户原话确认"后台直接看询盘/跟进/导出,样式完全统一,零第三方"
3. **【新需求】邮件转发通知** —— 每封询盘自动发送邮件到指定邮箱:① 默认收件人 = 站点管理员邮箱;② 主题设置可配多个转发邮箱(逗号分隔);③ Reply-To 设为客户邮箱,点"回复"即直接回给客户;④ HTML 邮件模板;⑤ 上线建议配 SMTP 插件
4. 阶段 4 全部定稿,阶段 5 开发中

### 站点地图概要(v3)

```
全局:头部(Logo/导航7项/语言切换 EN/Get a Quote)+ 页脚(联系/产品导航/合规标识)
1. 首页 Home
2. 关于我们 About Us(简介/历程/技术/资质/工厂)
3. 产品中心 Products(列表/详情/10 系列)
4. 解决方案 Solutions ★含成功案例(总览 + 4 场景着陆页 + 案例库/案例详情 + 交付地图)
5. 服务与定制 Services(OEM/ODM 流程/优势/FAQ/询盘)
6. 新闻资讯 Blog(公司新闻/行业新闻)
7. 联系我们 Contact Us(联系信息/统一询盘表单)
```

### 当前待确认(阶段 6 逐页确认时进行)
- 【当前】**About + Contact + Blog 三页设计稿 v1 待确认 6 项**(预览环境 `/`):
      ① About Hero 与数据条 ② 发展历程时间线(2012–2025,2018 为自拟补齐)
      ③ 核心技术 4 卡与认证墙(证书实拍待客户提供,已留占位)④ Contact 布局与统一表单
      ⑤ Blog 列表/分类筛选/空态(Industry News 演示空态)⑥ 文章详情页排版(标题/引用块/要点/上下篇)
- 【已默认通过】产品中心、Solutions、Services(各 5 项,意见可随时回移);Services 的 OEM 之旅已同步 PHP 化
- 【遗留】品牌名澄清:站内文案 Dazhen vs Logo HUIZHEN 汇真(不阻塞,按 Dazhen 继续)

### 已识别的开放问题(不阻塞)
- 原站产品数据不齐(仅 6 个产品,分类 10 个)→ 上线前需内容补充计划

## 5. 重要技术决策记录

### 阶段 4 技术拍板(2026-09-29,技术负责人决策,详见 docs/tech-proposal.md)
1. **主题形态:经典主题(classic theme)+ 区块编辑器全面支持**——不用块主题(FSE)。理由:B2B 定制设计(A×B 节奏、仪表动效、选型向导、交付地图)需要 PHP 模板完全掌控;新闻正文仍用区块编辑器,编辑体验不损失。theme.json 提供编辑器设计令牌。
2. **零商业依赖**:弃用原站 Blocksy / Stackable / Smart Slider;询盘、字段、动效全部主题内置。交付物为一个独立主题包,上传即用。
3. **数据模型**:`product`(产品)+ `case_study`(案例)+ `solution`(方案场景)+ `dazhen_inquiry`(询盘,私有)+ 原生 `post`(新闻)。产品参数表用自制 repeater 字段(参数名/参数值/单位/分组)。
4. **字段引擎:原生自制 metabox**(register_meta + 自制字段控件库),不用 ACF/SCF——零第三方依赖,不受 ACF 治理风波影响;post_meta 结构与 ACF 兼容,未来可平滑迁移。
5. **询盘系统:主题内置自研**——nonce + honeypot + 频率限流三重防护,无文件上传;询盘落库为私有 CPT,后台「询盘」菜单直接管理(列表/详情/状态/导出);**邮件转发通知**(第四轮新增):默认发管理员邮箱 + 主题设置可配多个转发邮箱,Reply-To 设为客户邮箱可直接回复,HTML 邮件模板;上线时建议配 SMTP 插件。
6. **多语言兼容层(inc/i18n-compat.php)**:用户选型 **GTranslate**(自动机器翻译)——主题检测 GTranslate 短代码,激活即在头部自动渲染其语言切换器;同时保留 Polylang 升级路径(dazhen_pll_* helper);单语言时切换器自动隐藏;主题全字符串翻译就绪。
7. **前端**:原生 CSS(Custom Properties 设计令牌,与 theme.json 同源)+ 原生 ES modules JS(无 jQuery/React);速热仪表用 SVG+requestAnimationFrame,滚动动效用 IntersectionObserver;遵循 prefers-reduced-motion。
8. **字体**:Space Grotesk + Inter 自托管 woff2(@font-face + preload);Noto Sans SC 在二期中文语言接入时再引入(避免首发冗余字体下载)。
9. **标准**:WordPress Coding Standards、全量 escaping/i18n(text domain `dazhen`)、最低 WP 6.5 / PHP 8.1、GPL v2+。
10. **性能**:响应式图片(srcset/lazy)、单文件 CSS(gzip 预算 ≤60KB)、关键字体 preload。

## 6. 原站内容问题清单(重构时需注意)

研究原站时发现的问题,新站应避免:

1. **头部占位内容未清理**:导航栏"Our Location: 304 North Cardinal St. Dorchester Center, MA 02124"
   是 WordPress.com 主题的演示占位地址,与实际公司地址(惠州大亚湾)不符 → 新站头部应展示真实联系方式
2. **部分产品为占位名称**:如 "consectetur adipiscing"、"magna fringilla"(Lorem ipsum 占位文本)→ 新站需准备真实产品数据
3. 产品仅填写 6 个,但分类有 10 个系列 → 内容填充需规划
4. 版权年份硬编码 "© 2026" → 新站应动态输出年份
5. 文案有笔误:"Guangdong Provincentry"、"-All Rights Reserved"(缺空格)
6. 产品详情页缺少结构化参数表 → 新主题应提供规范的规格参数展示
7. 询盘表单使用第三方(Contact Form 7 风格)→ 新主题需确定表单方案(阶段 4 决策,已定推荐:内置自研)

## 7. 已完成的工作

- 2025-09-28:通读 dazhenglobal.com 全站(首页/关于/产品/服务/联系/博客/解决方案/案例/产品详情),
  原始抓取数据保存在 `research/*.json`,分析结论整理至 `docs/site-analysis.md`
- 2025-09-28:建立本项目管理文档 PROJECT.md
- 2025-09-28:用户确认页面结构 4 项决策(多语言/案例独立/纯询盘/发散设计),
  信息架构定稿为 v2,详见 `docs/ia-structure.md`
- 2025-09-28:产出视觉方向提案(3 个方向 + A×B 组合推荐)+ 8 个创新模块提案,
  概念图生成于 `public/design/`,交互式提案页搭建于预览环境
- 2025-09-28(第二轮):用户确认 A×B/首发英文/Cases 并入 Solutions/核心4模块/用原站素材;
  信息架构更新为 v3,新建设计系统规范 docs/design-system.md;
  从原站下载 9 个品牌素材至 public/assets/(logo、6 产品图、2 banner,经 Jetpack CDN);
  预览环境重写为首页高保真设计稿 v1(A×B 节奏、真实素材、速热仪表动效、Solutions+Cases 合并区块),
  字体切换为 Space Grotesk + Inter(next/font);修复产品卡分类截断;完成浏览器与 VLM 双重验证
- 2026-09-29(第三轮):**阶段 3 收官**——用户确认首页高保真设计稿 v1(含 Hero 型号 18E-2 与文案口吻);
  产出阶段 4 技术方案 docs/tech-proposal.md(10 项技术拍板 + 2 项待用户选型);
  预览环境重写为阶段 4 技术提案页(后台使用模拟、多语言路线、确认问题);通过 lint 与浏览器自检
- 2026-09-29(第四轮):**阶段 4 收官**——用户确认多语言方案 A、询盘方案 B,新增需求"邮件转发通知"(管理员 + 多转发邮箱 + Reply-To 直回客户);
  启动阶段 5:创建 wordpress-theme/dazhen/ 主题骨架——style.css / functions.php / theme.json(设计令牌)/ inc/ 12 个模块(setup、enqueue、post-types、meta-fields、metabox-ui、inquiry、inquiry-admin、pll-compat、template-tags、customizer、editor)/ 模板体系(header/footer/front-page/template-parts/基础模板)/ 前端资产(main.css A×B 令牌、JS 三模块)/ 自托管字体 woff2 / languages/dazhen.pot / screenshot.png;
  预览环境更新为阶段 5 进度仪表盘
- 2026-09-29(第五轮·多语言更正 + 阶段 5 主体开发):用户澄清"tr"为笔误,多语言定稿 **GTranslate 插件**;
  完成主题骨架主体 41 文件(入口三件套/自托管字体/inc/ 13 模块/模板体系/template-parts/前端资产/pot/README);
  询盘系统含邮件转发与后台管理;28 PHP 过语法校验(详见 worklog Task 5)
- 2026-09-29(第六轮·阶段 5 收官 + 阶段 6 启动):
  **阶段 5 全部完成**:①选型向导 Solution Finder(template-parts/selector-wizard.php + selector-wizard.js,
  3 题「场景/安装/水型」计分匹配 + OEM 兜底卡,产品数据由系列 slug 映射表驱动、可过滤);
  ②交付地图(template-parts/delivery-map.php,点阵世界地图 SVG 由 Natural Earth 数据生成 1207 点、
  双端共用,航线动画 + 脉冲标记 + 案例 chip,含屏幕阅读器等价文本);
  ③Solutions 总览页面模板(page-templates/solutions-overview.php:4 场景卡 + 交付地图 + 案例库地区筛选);
  ④静态页起步区块模板(inc/patterns.php:About 简介/里程碑、Services 7 步流程/FAQ、Contact 信息卡,共 6 个 patterns);
  ⑤WXR 示例内容包(sample-content/dazhen-sample.xml,6 产品 + 4 方案 + 3 案例 + 14 分类项,
  生成脚本 tools/wxr/build-wxr.mjs 保证 PHP 序列化字节精确,Python 严格 XML 校验通过);
  ⑥archive-product 接入搜索表单 + finder 入口,首页接入 finder 预告。
  **结构修正**:层级模板从 templates/ 子目录移至主题根目录(经典主题模板层级仅从根目录解析,
  原结构运行时不可用;勘误已记入 tech-proposal.md)。
  预览页重写为阶段 6 第一项「产品中心设计稿 v1」(子代理 6-a 完成):列表页 + 筛选搜索 + 向导交互 +
  F900-QC 详情页预览 + 交付地图预演 + 5 项确认清单;lint/agent-browser/VLM 三重验证通过。
- 2026-09-29(第七轮·阶段 6 第二项):用户「继续」→ 产品中心 5 项无异议默认通过(PHP 已落地,意见可回移);
  子代理 7-a 上线「Solutions 设计稿 v1」:总览页(4 场景卡/交付地图正式版/案例库地区筛选真实交互)+
  Office & Commercial 场景着陆页预览 + UK 案例详情页预览 + 5 项确认清单;数据与 WXR 示例内容包严格同源;
  主控独立复验(7-b):筛选交互精确验证、双视口无溢出、9/9 图加载、控制台零错误、footer 结构贴底;
  截图存档 docs/screenshots/phase6-2-solutions-v1*.png;发现 7-a 漏写 worklog 已补录并纠偏流程
- 2026-09-29(第八轮·阶段 6 第三项):用户「继续」→ Solutions 5 项默认通过(设计稿与 PHP 同源,无需回移);
  子代理 8-a 上线「Services 设计稿 v1」:Hero 主张「Your brand. Our engineering.」+ 商务条款数据条 +
  四大优势卡 + **OEM 之旅交互时间轴**(核心创新模块④收官:7 节点 SVG 路径/tablist 键盘导航/详情面板/移动端纵向)+
  分组 FAQ 手风琴(4 组 9 问、全页单开)+ 询盘预填;主控独立复验(8-b):真实按键键盘导航、FAQ 单开、
  双视口零溢出、控制台零错误、VLM PASS;lint 通过
- 2026-09-29(第九轮·Services 通过 + OEM 之旅 PHP 化 + 阶段 6 最后一轮):用户「继续」→ Services 5 项默认通过;
  **OEM 之旅正式落地主题**(核心创新模块④收官):template-parts/oem-journey.php(tablist ARIA/7 步数据过滤器/
  PHP 生成路径几何)+ assets/js/oem-journey.js(键盘导航/进度线 getPointAtLength 精确测量/移动端纵向)+
  page-templates/services.php 完整页面模板(主张 Hero/数据条/优势/FAQ 手风琴/询盘预填,5 个过滤器可覆写);
  询盘系统扩展 message 预填参数;main.js 新增通用单开手风琴并修复提前 return 缺陷;main.css 新增 §17;
  移除被取代的 2 个 Services patterns;pot +102 条;34 PHP 文件 0 语法错误。
  同轮子代理 9-a 上线「About + Contact + Blog 三页设计稿」(阶段 6 最后一项):About(Hero/简介/时间线/
  技术 4 卡/认证墙/工厂占位)+ Contact(信息卡/双栏 7 字段表单)+ Blog(列表筛选真实交互+空态+详情页排版);
  主控复验(9-b):筛选/空态/表单/双视口/控制台全过;VLM PASS
- 2026-09-29(第十轮·**阶段 6 收官**):用户「继续」→ About/Contact/Blog 三页 6 项默认通过;
  子代理 10-a 完成 PHP 回移:page-templates/about.php(481 行,7 个 dazhen_about_* 过滤器,时间线 5 节点/
  技术 4 卡自绘 SVG/认证绶带徽章/产品缩略图按 _dz_product_model 自动关联)+ contact.php(信息卡全走
  customizer+新增 dazhen_working_hours 控件;**关键结论:询盘 7 字段与设计稿一一对应**(person→Contact
  person,product→Product of interest),零改动复用询盘系统)+ Blog 三件套(post-card.php/blog-toolbar.php
  无 JS 分类 chips,重写 index/archive/single,search 换卡片,去「Category:」前缀,富空态,上下篇双卡,
  dazhen_reading_time);patterns.php 仅存通用 CTA;main.css §19(440 行);pot 整治为 305 条全唯一
  (删 44 死条目/去重/追加 102);php-lint 38 文件 0 错误;主控复验(10-b)抽验通过。
  **阶段 6 全部四轮设计稿(产品中心/Solutions/Services/About·Contact·Blog)均已确认并落地 PHP。**
- 2026-09-29(第十一轮·阶段 7 交付):11-a WXR 终审补全(25 item/16 term/2 author;6 篇文章+2 分类与设计稿
  同源;**关键修复 wp:taxonomy→wp:term_taxonomy 14 处**(旧标签致 wordpress-importer 导入 term 失效);
  4 页面带 _wp_page_template;Products 不建页由 CPT 归档覆盖;XML 良构全过;77.5KB/1133 行)。
  11-b 预览页换「阶段 7 交付总览」(7 阶段进度/实测数字卡 38PHP·19CSS·305pot/创新模块 4 卡/页面清单 8 卡/
  安装指引/素材清单;lint·E2E·VLM 全过)。11-c 主控打包 **dist/dazhen.zip(946KB/67 文件,根目录 dazhen/)**
  + 独立复验(标题/H1/7 阶段项/模块展示/2 图全载/双视口零溢出/控制台零错误/lint 通过/dev.log 干净)。
  沙箱无 PHP/WP runtime,真机安装测试待用户侧执行。
- 2026-09-30(第十二轮·**阶段 7 闭环,项目交付完成**):会话中途沙箱被平台回滚一次(阶段 5-7 文件一度
  消失),主控启动转录级恢复子代理后平台完整恢复原始文件(字节级一致),无净损失。随即免 root 搭建
  真实 WordPress 环境:static-php 8.4.23 + wp-cli 2.12 + WP 7.1.2 + SQLite 插件(mini-services/wordpress,
  8080);**用交付 zip 本体安装激活** → WXR 导入(25 item 全量)→ 全站 E2E(11 类页面 + OEM tab 切换 +
  FAQ 单开 + 询盘端到端落库:success 重定向 + CPT 10 字段 + status=new)→ **修复 3 个仅真机暴露的缺陷**:
  ①搜索表单 input 缺 min-width:0 致按钮溢出 64px ②.nav-toggle span 特异性压过 .screen-reader-text 致
  移动端横溢 228px(改 :not())③Cases 归档 "Archives:" 前缀 + 无区域筛选(新增 case-toolbar.php)→
  重打包 dist/dazhen.zip(68 文件);10 张真机截图 + VLM PASS;eslint/gitignore 增补;worklog 12-a~c。

## 8. 待处理事项

- [x] **真机安装验收 ✅(2026-09-30 沙箱内完成)**:WP 7.1.2 + SQLite + 交付 zip 实装 → WXR 一键导入 →
      全站 E2E(首页/产品/方案/案例/服务/关于/联系/博客/搜索/404/询盘端到端落库)→ 修复 3 个真机 bug
      (搜索表单 flex 溢出/读屏 span 撑宽移动端/Cases 标题+区域筛选)→ 重打包 dist/dazhen.zip(68 文件);
      10 张真机截图 docs/screenshots/real-wp-*.png;VLM 复核 PASS。用户上线时按同流程:
      上传 zip → 导入 XML → 指派 Home/Blog → 可选 GTranslate → 建议 SMTP+缓存
- [x] 阶段 7 沙箱内可验证项全部完成:WXR 终审✅/zip 打包✅/预览页交付总览✅/E2E 回归✅(2026-09-29,11-a/b/c)
- [x] About + Contact + Blog 三页设计稿 6 项默认通过 + PHP 回移完成(2026-09-29,10-a/10-b)
- [ ] 上线前内容补充计划(产品数据仅 6/10 系列,参数表待编;示例内容包已可一键导入)
- [ ] 上线时建议配置:SMTP 发信插件、缓存插件(技术建议,不影响主题交付)
- [ ] 【遗留】品牌名澄清:Dazhen vs HUIZHEN 汇真(不阻塞)
- [ ] 可选模块 backlog:⑤技术解剖 ⑥下载中心 ⑦数据滚动动效 ⑧悬浮 WhatsApp
- [ ] 建议用户后续提供:工厂实拍、认证证书图、更高清产品图、文章特色图

## 9. 重要讨论结论

- 2025-09-28:项目启动,确认开发流程为「资料理解 → 页面结构确认 → 视觉方案确认 →
  技术方案确认 → 开发 → 逐页实现确认 → 测试完善」,前一阶段未确认不进入下一阶段。
  技术问题由 AI 技术负责人决策;前台视觉、页面结构、用户体验、业务功能需用户确认。
- 2025-09-28(阶段 2 结论):① 多语言站点;② 成功案例独立成板块;③ 产品纯询盘模式;
  ④ 用户邀请发散创意设计。站点地图 v2 见 docs/ia-structure.md。
  原站发现的问题(占位地址/占位产品名等)列入新站修正清单。
- 2025-09-28(阶段 3 第一轮结论):视觉方向定为 A×B 组合;首发语言英文(其他语言用插件);
  **结构修正:Cases 并入 Solutions(v3,推翻 v2 的独立板块决定)**;创新模块核心 4 项全做;
  品牌素材沿用原站(已下载 public/assets/)。发现品牌名不一致问题(Dazhen vs HUIZHEN 汇真)待澄清。
- 2026-09-29(阶段 3 收官结论):首页高保真设计稿 v1 用户确认通过(Hero 18E-2、直接自信型文案、
  A×B 节奏、速热仪表动效均按 v1 实施)。阶段 3 完成。
  同日产出阶段 4 技术方案(docs/tech-proposal.md):10 项技术拍板(经典主题+区块混合、零商业依赖、
  4 个 CPT、原生字段引擎、自研询盘系统、Polylang 兼容层、原生前端、自托管字体、WP 编码标准、
  性能预算);2 项交用户选型(多语言插件时机、询盘表单方案)。
- 2026-09-29(阶段 4 收官结论):用户确认多语言方案 A(先预留、二期 Polylang 免费版)与询盘方案 B(自研内置);
  新增需求"邮件转发通知"——管理员邮箱 + 可配置多转发邮箱 + Reply-To 直回客户 + HTML 模板。
  "tr"按推荐项 A 理解,若指土耳其语则记入二期语言候选。阶段 5 随即启动。
- 2026-09-29(第五轮·多语言更正):用户澄清"不我打错了,我的意思是用插件gt 这个多语言插件" → 多语言定稿 **GTranslate**。
  技术评估(告知不重问):装机即得 100+ 语言零编辑成本;免费版 ?lang=xx 参数 URL 对 SEO 收录较弱、机翻术语偶有偏差;
  未来重点市场可升 GTranslate 付费版(独立 URL + 可编辑译文)或再评估 Polylang,主题已同时兼容两者(i18n-compat.php)。
  原"方案 A(预留 + Polylang 二期)"记录保留在 tech-proposal.md 作对比,以本条为准。

## 10. 工作方法约定

- 预览环境(Next.js sandbox)充当**设计工作室**:每个设计决策先做成可视化提案供用户确认,
  确认后再移植到 WordPress 主题正式代码(PHP),主题代码将存放于 `wordpress-theme/` 目录。
- 所有阶段性产出必须同步写入 PROJECT.md / docs/,保证上下文可恢复。
