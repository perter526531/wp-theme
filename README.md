# Kabel WordPress 双包 —— 主题 + 自研区块引擎

> **KABEL（卡百利艺术涂料）品牌官网的完整 WordPress 重建方案**
> 自研区块引擎 + 黑金奢华设计系统，零商业插件依赖。
>
> 当前版本：**kabel 主题 v1.6.6** ＋ **kabel-blocks 插件 v3.12.0**（2026-10-10）

---

## 一、项目背景

原站托管在 Hostinger，由商业组件堆叠而成：Blocksy 主题（免费版）＋ Stackable 区块 ＋
Smart Slider 3 ＋ WPForms ＋ WooCommerce（装而未用）＋ Hostinger 全家桶插件。

本项目把这整套体系**全部替换为自研代码**：

| 原站组件 | 自研替代 |
|----------|----------|
| Blocksy 主题 | **kabel 主题**（黑金设计系统 + 行业框架） |
| Stackable 区块 | **kabel-blocks 引擎**（23 个 kbl/* 区块，spec 驱动） |
| Smart Slider 3 | 自研 hero / 画廊 / 轮播区块 |
| WPForms | 自研询盘系统（表单 → 私有 CPT → 邮件转发 + RFQ 弹窗） |
| WooCommerce | 自研产品 CPT（系列/规格表/多图画廊） |
| Hostinger 插件 ×4 | 全部移除 |

**品牌与内容**：KABEL 艺术涂料（总部 Druento, Torino, 意大利；分部佛山）——6 个产品家族
（Velvet / Lime Plaster / Stone / Solid / Microcement / Exterior）、4 个色彩系列
（Morandi / Van Gogh / Macaron / Museum）、家装 + 工程两类项目、诺奖得主 Stoddart 致敬、
BASF / Dow / 万华合作伙伴、净醛 94.1% / 82.1%。全部真实内容已落入区块种子。

**设计基因**（从原站 Blocksy 全局样式提取）：纯黑画布 `#000`、藏青 `#222E5E`、古铜金
`#B39A74`、白正文、直角按钮、自托管 Barlow（全字重）+ Oregano 手写体点缀。

> ⚠️ **安全备注**：分析原站时在其根目录发现 `fileclient.php` 恶意后门（伪装成 WP 核心文件，
> 监听特定 HTTP 请求头并执行任意载荷）。原站务必：删除该文件 → 修改所有密码 → 重装 WP 核心。

---

## 二、仓库导航

| 路径 | 内容 |
|------|------|
| **`dist/kabel.zip`** | **交付包（推荐下载）**：主题 + 捆绑插件，WP 后台直接上传安装 |
| **`dist/kabel-blocks.zip`** | 交付包：仅插件（任何 WordPress 站可独立使用） |
| `kabel/` | 主题源码 v1.6.6（README 内含完整安装/使用说明） |
| `kabel-blocks/` | 插件源码 v3.12.0（含 src/ JSX 源码 + build.mjs 构建链） |
| **`REQUIREMENTS.md`** | 需求全记录：原始需求 + 18 轮用户反馈演进 + 硬性需求清单 |
| **`CHANGELOG.md`** | 版本变更史：主题 1.0.0→1.6.6 / 插件 1.0.0→3.12.0 |
| **`worklog.md`** | 946 行完整开发日志（前身 dazhen 时代 → kabel 全程，每轮任务的工作记录与决策） |
| `docs/kabel-brief.md` | Kabel 重建蓝图：品牌事实 / 设计基因 / 19 页内容规格 / 资产清单 / 架构分工（唯一事实源） |
| `docs/site-structure.json` | 19+9 页图文结构化数据 |
| `docs/design-system.md` `ia-structure.md` `site-analysis.md` `tech-proposal.md` | 前身项目的原站分析 / 设计系统 / 信息架构 / 技术方案（用户确认稿） |
| `docs/screenshots/` | 163 张 E2E 证据截图（每轮迭代的真机验证记录，v1→v21） |
| `research/` | 前身项目原站研究数据（抓取 JSON） |
| `dazhen/` `dazhen-blocks/` | **前身项目**（大振科技官网，kabel 的 fork 源，详见 `PROJECT.md`） |

> 开发期间源码位于 `wordpress-theme/kabel/`、`wordpress-theme/kabel-blocks/`——worklog.md
> 中的路径引用以此为准，本仓库将其提升到根目录（`wordpress-theme/` 前缀已去除）。

---

## 三、双包架构

### kabel 主题（v1.6.6）

- **行业框架**：产品系列 CPT（结构化规格表 + 多图画廊 + 相关产品）、项目案例 CPT、
  新闻 CPT、询盘系统（7 字段表单 → 私有 Inquiries CPT → 后台管理 → 邮件多收件人转发）
- **转化组件**：RFQ 弹窗（产品页 CTA 预填型号、免跳转提交）、询盘清单（B2B 购物车式收集）
- **页眉页脚**：私有块页面可视化编辑 + Classic（带联系方式两行）/ Minimal（单行）布局一键切换
- **前台体验**：回到顶部、阅读进度条、相关文章、产品画廊灯箱、超级菜单、博客侧边栏
- **一键引导**：外观 → Kabel Setup（装引擎 / 建页面 / 状态卡 / 演示内容回收工具）
- **演示内容**：14 页 + 120+ 压缩样图；老站导入时按上传路径**复用已有媒体库**，不产生重复
- **多语言**：GTranslate 桥接（自动跟随语种配置）+ Polylang 升级路径 + 手写列表三模式

### kabel-blocks 插件（v3.12.0）

**Spec 驱动双端引擎**——这是整个项目最核心的架构：

```
src/*.js（JSX 源码）
   │  build.mjs（bun + esbuild，@wordpress/* 别名到 window.wp）
   ▼
assets/js/blocks.js —— 自包含编辑器 bundle（一个文件）
   │
   ├── 编辑器侧：通用运行时，读 spec 自动注册全部区块 + 自动生成侧栏控件
   └── 前台侧：inc/emitter.php = 每个 save() 的逐字节 PHP 孪生
        （布局 / 模式 / 一键种子器产出与编辑器手写完全一致的标记，
          种子页面打开零块校验警告）
```

- **23 个 `kbl/*` 区块**：容器（Section / Columns / Column / 组）、内容（Heading / Text /
  Button / Button row / Image / Icon / Icon box / List / Spacer / Separator / Stat /
  FAQ / Card / Tabs / Testimonial / Video / Team / Pricing）、动态（询盘表单 / 文章网格）、
  页眉元素（logo / nav-menu / search / language-switcher / contact-info）
- **Stackable 级控制力**：三断点排版 / 设备可见性 / 悬停效果 / 入场动画 / 渐变背景 /
  形状分隔符 / 自由列宽（任意占比如 40+40+20，增删列）/ 间距面板 / 全局颜色系统
  （一处改全站跟随）/ 自定义颜色（拾色器 + alpha）/ 图片尺寸与宽高比
- **18 种整页布局**：Kabel → Layout Library 画廊（真实截图缩略）+ 独立窗口实时预览 + 一键建页
- **纯插件模式（site-chrome）**：不用主题也能接管块主题的页眉页脚模板部件，
  与主题共享部件内容 / 布局状态 / 菜单，跨模式无缝切换
- **Tools 工具箱**：演示页一键回收（只进回收站）+ hex → 全局色令牌迁移器（可撤销）
- **全中文编辑器**：287+ 词条中文化（名称 / 提示语 / 侧栏标签全量）
- **性能**：bundle minify -45%、前台皮肤 -39%、条件加载（无 kbl 区块的页面 0 插件字节）、
  请求级缓存 ×4、字体预加载

### 三种运行形态（共享同一套区块）

1. **主题 + 插件**——完整形态，全部能力
2. **纯插件**——site-chrome 自带页眉页脚，任何块主题可用
3. **独立区块**——任何主题下作为区块套件使用

---

## 四、安装（零技术用户）

### 完整站点

1. 下载本仓库 `dist/kabel.zip`（已捆绑插件副本）
2. WP 后台 → 外观 → 主题 → 安装主题 → 上传主题 → 现在安装 → 启用
3. 自动跳转 **外观 → Kabel Setup** → 点 **Install & activate now** 安装区块引擎
4. **全新站点**：14 页完整站点、菜单、页眉页脚自动生成；
   **已有站点**：点 **Create the pages**（绝不覆盖任何已有内容）

### 仅用插件

1. 下载 `dist/kabel-blocks.zip` → 插件 → 安装插件 → 上传 → 启用
2. 左侧 **Kabel** 菜单 → Header & Footer 管理页眉页脚（块主题模式下接管模板部件）
3. Kabel → Layout Library 从 18 种布局一键建页

### 多语言

安装 GTranslate 后，页眉语言块自动跟随其语种配置（9 语种实测：en / zh-CN / it / fr /
de / es / pt / ru / ja + 母语名显示）；也支持 Polylang 或手写语言列表。
语言切换器为「图标 + 按钮」下拉形态（v3.10.0 起）。

### 更新已有安装

上传双 zip 覆盖安装即可。主题会自动检测插件版本落后并给出一键更新入口
（Setup 页 / 布局屏 / 页眉页脚编辑器三处防呆提醒）。

---

## 五、系统要求与开发

- **要求**：WordPress 6.5+（已实测 6.8.2 / 7.1.2）、PHP 8.1+
- **测试沙箱**：WP + SQLite（drop-in）+ static-php 8.4.23 + wp-cli 2.12，全程真浏览器
  E2E 验证；每轮证据截图归档于 `docs/screenshots/`（v1 → v21）
- **插件构建链**：`cd kabel-blocks && bun install && bun run build`（含中文词条完整性门禁）
- **i18n**：主题 text domain `kabel`，插件 `kabel-blocks`，pot 模板随源码维护

## 六、项目历史一句话版

前身 **Dazhen**（大振科技 B2B 官网，0.1.0 → 0.4.2 / 2.0.0 → 2.0.2）验证了「自研区块引擎」
架构 → fork 全量重命名为 **Kabel** 双包并黑金重设计 → 18 轮真实用户反馈迭代
（v7 行内编辑 → v3 仿 Stackable 重写 → v8 对标三巨头 → v9-v11 细粒度控制 → 页眉页脚块化 →
布局切换 / 多语言桥 → 全局颜色 / 布局库 → 纯插件模式 → 性能大版）→ 当前 1.6.6 / 3.12.0。

完整细节：`REQUIREMENTS.md`（需求）· `CHANGELOG.md`（变更）· `worklog.md`（全程工作日志）
