/**
 * Simplified-Chinese UI dictionary for the kabel-blocks editor bundle.
 *
 * The i18n shim (src/shims/i18n.js) intercepts every __( '…',
 * 'kabel-blocks' ) call in the editor and swaps the English source string
 * for the Chinese entry below. Front-end markup (save() + the PHP emitter)
 * never calls __(), so published pages stay byte-identical — only the
 * editing experience is localized.
 *
 * build.mjs enforces that EVERY kabel-blocks string in src/ has an entry
 * here, so a new control without a translation fails the build.
 */
export const ZH = {
        /* ---- Tabs / panels (inspector shell) ---- */
        'Layout': '布局',
        'Style': '样式',
        'Advanced': '高级',
        'Colors': '颜色',
        'Typography': '文字排版',
        'Spacing': '间距',
        'Visibility': '设备可见性',
        'Animation': '动画',
        'Background gradient': '背景渐变',
        'Hover effect': '悬停效果',
        'Border & shadow': '边框与阴影',
        'Background image': '背景图片',
        'Border color': '边框颜色',

        /* ---- Section ---- */
        'Section (Kabel)': 'Kabel 分区',
        'A full-width band with dark/light tone, background image and padding. Nest anything inside.':
                '全宽横幅分区：明暗色调、背景图与节奏内边距，内部可嵌套任何区块。',
        'Tone': '色调',
        'Dark (near-black)': '深色（近黑）',
        'Navy black': '藏青黑',
        'White (light)': '浅色（白）',
        'Vertical padding': '上下内边距节奏',
        'Compact': '紧凑',
        'Normal': '常规',
        'Spacious': '宽松',
        'Content width': '内容宽度',
        'Normal (1290px)': '常规（1290px）',
        'Narrow (820px)': '窄版（820px）',
        'Full bleed': '通栏全宽',
        'Minimum height': '最小高度',
        'Content vertical position': '内容垂直位置',
        'Top': '顶部',
        'Center': '居中',
        'Bottom': '底部',
        'Middle': '居中',
        'Divider top': '顶部分隔形状',
        'Divider bottom': '底部分隔形状',
        'Divider color': '分隔形状颜色',
        'None': '无',
        'Wave': '波浪',
        'Tilt': '斜切',
        'Arc': '弧形',
        'Text color': '文字颜色',
        'Background color': '背景颜色',
        'Dark overlay': '暗色遮罩',
        'Padding top (px)': '顶部内边距（px）',
        'Padding bottom (px)': '底部内边距（px）',
        'Custom width wins over the preset above.': '设置后覆盖上方的宽度预设。',
        'Custom content width (px)': '自定义内容宽度（px）',
        'Custom background color overrides the tone preset. Pair it with the text color.':
                '自定义背景色会覆盖色调预设，请同时搭配文字颜色。',

        /* ---- Columns / Column ---- */
        'Columns (Kabel)': 'Kabel 列行',
        'Add a row of columns — pick a layout, then add, remove, reorder or resize columns freely.':
                '插入一行列容器——先选布局，之后随时加列、删列、排序、拖列宽。',
        'Column count': '列数',
        'Column (Kabel)': 'Kabel 列',
        'A single column — free container with background, padding, radius and shadow.':
                '单个列：自由容器，可设背景、内边距、圆角与阴影。',
        'Columns': '列',
        'Vertical alignment': '垂直对齐',
        'Column gap': '列间距',
        'Row gap when stacked': '堆叠时的行间距',
        'Vertical breathing room between rows.': '小屏堆叠后每列之间的上下间距。',
        'Stack on mobile': '小屏自动堆叠',
        'Columns stack vertically on small screens.': '小屏幕下列会上下堆叠。',
        'Columns stay side-by-side on small screens.': '小屏幕下列保持并排。',
        'Reverse column order on mobile': '小屏反转列顺序',
        'Only applies while the columns are stacked on small screens.': '仅在列已堆叠的小屏上生效。',
        'Content alignment': '内容对齐',
        'Left': '左',
        'Right': '右',
        'Column width': '列宽（%）',
        'Column width is a share of the row; the neighbor column absorbs the difference so the row always sums to 100.':
                '列宽为整行占比；修改一列时相邻列自动吸收差值，整行始终合计 100。',
        'Equalize column widths': '均分所有列宽',
        'Add column': '加一列',
        'Remove column': '删除此列',
        'Auto': '自动',

        /* ---- v11 Stackable-style column kit ---- */
        'Column widths': '列宽比例',
        'Column layout preset': '列布局预设',
        'Drag the dividers or type exact numbers — e.g. 40 + 40 + 20.':
                '拖动分隔点调整比例，或在输入框直接填写数值（如 40 + 40 + 20），总和自动保持 100。',
        'Column order': '列顺序',
        'Move left': '左移一位',
        'Move right': '右移一位',
        'Pick a columns layout': '选择一个列布局',
        'One column': '单列',
        'Two columns; equal split': '两列均分',
        'Two columns; one-third, two-thirds split': '两列：三分之一 + 三分之二',
        'Two columns; two-thirds, one-third split': '两列：三分之二 + 三分之一',
        'Three columns; equal split': '三列均分',
        'Three columns; wide center column': '三列：中间加宽',
        'Four columns; equal split': '四列均分',
        'Click to type an exact width': '点击输入精确列宽',
        'Enter to apply · Esc to close · Tab for next column':
                '回车应用 · Esc 关闭 · Tab 跳到下一列',

        /* ---- Heading / Text ---- */
        'Heading (Kabel)': 'Kabel 标题',
        'Inline-editable title with brand sizes — click and type right on the page.':
                '画布直接编辑的标题，带品牌字号档位。',
        'Text (Kabel)': 'Kabel 段落',
        'Inline-editable paragraph — click and type right on the page.':
                '画布直接编辑的正文段落。',
        'Write a heading…': '输入标题…',
        'Write text…': '输入正文…',
        'Level': '标题级别',
        'Size': '字号档位',
        'Lead paragraph': '导语段落',
        'Larger, slightly muted intro copy.': '更大、稍柔和的导语文案。',
        'Eyebrow style': '眉标样式（小字引题）',
        'Small bronze uppercase intro line above sections.': '区块上方的铜色大写小字引题。',

        /* ---- Button / Button row ---- */
        'Button (Kabel)': 'Kabel 按钮',
        'Bronze/navy/outlined call-to-action with inline-editable label.':
                '铜色/藏青/描边三种质感的行为按钮，标签画布直改。',
        'Button row (Kabel)': 'Kabel 按钮组',
        'Several buttons side by side — add or remove them freely.':
                '多个按钮并排，可自由增删。',
        'Button label': '按钮文字',
        'Button label…': '按钮文字…',
        'Button label (optional)…': '按钮文字（可选）…',
        'Button link': '按钮链接',
        'Button style': '按钮样式',
        'Variant': '变体',
        'Bronze': '铜金',
        'Navy': '藏青',
        'Light': '浅色',
        'Outline': '描边',
        'Ghost': '幽灵',
        'Solid': '实心',
        'Full width': '通栏铺满',
        'Horizontal padding': '水平内边距',
        'Open inquiry popup': '打开询盘弹窗',
        'Opens the Kabel inquiry popup instead of following a link.':
                '点击后打开询盘弹窗，而不是跳转链接。',
        'Open in new tab': '新标签页打开',
        'Popup context (optional)': '弹窗上下文（可选）',
        'Pre-fills which product the inquiry is about.': '预填询盘所属的产品信息。',
        'Label color': '文字颜色',
        'Background': '背景',
        'Hover colors': '悬停颜色',

        /* ---- Heading size hint ---- */
        'S/M/L/XL are brand presets — set any px value in Style → Typography.':
                'S/M/L/XL 为品牌预设字号——任意像素值请到「样式 → 排版」设置。',

        /* ---- Image / Icon ---- */
        'Image (Kabel)': 'Kabel 图片',
        'Image with drag-to-resize width, aspect ratio, corner radius and optional caption + link.':
                '图片区块：拖拽调宽、宽高比例、圆角，可选说明文字与链接。',
        'Alt text': '替代文本',
        'Width': '宽度',
        'Width (%)': '宽度（%）',
        'Width (px)': '宽度（px）',
        'Height (px)': '高度（px）',
        'Aspect ratio': '宽高比例',
        'Original': '原图',
        'Custom': '自定义',
        'Drag the handles on the canvas, or set an exact width here. Wins over the % width while set.':
                '拖动画布上的手柄，或在此精确设定宽度。设置期间优先于百分比宽度。',
        'Applies only after the px width above is reset.':
                '只有清除上方像素宽度后才会生效。',
        'Custom size = the px width above plus this height.':
                '自定义尺寸 = 上方像素宽度 + 此高度。',
        'Write a caption…': '输入图片说明…',
        'Link (optional)': '链接（可选）',
        'Opens the image as a link.': '点击图片跳转链接。',
        'Image': '图片',
        'Choose image': '选择图片',
        'Replace image': '替换图片',
        'Remove': '移除',
        'Upload or pick an image from the library.': '上传或从媒体库选择图片。',
        'Icon (Kabel)': 'Kabel 图标',
        'Pick from 29 curated stroke icons — paint, plant, globe, service.':
                '28 个精选线性图标：涂料、植物、地球、服务等。',
        'Icon': '图标',
        'Shape': '形状',
        'Square': '方角',
        'Circle': '圆形',
        'Shape background': '图标底衬',

        /* ---- Icon box ---- */
        'Icon box (Kabel)': 'Kabel 图标框',
        'Icon + heading + paragraph in one card — edit every part inline.':
                '图标 + 标题 + 段落的组合卡片，全部画布直改。',
        'Icon position': '图标位置',
        'Icon size': '图标大小',
        'Icon color': '图标颜色',
        'Icon background': '图标底衬',
        'Icon background padding': '底衬内边距',
        'Icon background radius': '底衬圆角',
        'Icon vertical alignment': '图标垂直对齐',
        'Feature title…': '功能标题…',
        'Describe the feature…': '描述这个功能…',
        'Card background': '卡片背景',

        /* ---- List ---- */
        'List (Kabel)': 'Kabel 列表',
        'Bullet list with bronze check / dash / dot markers.':
                '带铜色对勾 / 短线 / 圆点符号的列表。',
        'Marker': '列表符号',
        'Check': '对勾',
        'Dash': '短线',
        'Dot': '圆点',
        'Add a list item…': '添加列表项…',

        /* ---- Spacer / Separator ---- */
        'Spacer (Kabel)': 'Kabel 占位',
        'Height': '高度',
        'Separator (Kabel)': 'Kabel 分隔线',
        'Bronze hairline divider with width and alignment.':
                '铜色发丝分隔线，可调宽度与对齐。',
        'Line color': '线条颜色',

        /* ---- Stat ---- */
        'Stat (Kabel)': 'Kabel 数据卡',
        'Big number with optional prefix/suffix and a label — the counters-row primitive.':
                '大数字 + 前后缀 + 标签的数据卡，适合一行多列数字。',
        'Prefix': '前缀',
        'Suffix': '后缀',
        'Number color': '数字颜色',
        'What it counts…': '它统计什么…',

        /* ---- Accordion ---- */
        'FAQ item (Kabel)': 'Kabel 手风琴',
        'Question + collapsible answer. No JS — native details/summary.':
                '问题 + 可折叠答案，原生 details/summary，无需 JS。',
        'Write the question…': '输入问题…',
        'Write the answer…': '输入答案…',
        'Open by default': '默认展开',
        'Close by default': '默认收起',

        /* ---- Card ---- */
        'Card (Kabel)': 'Kabel 卡片',
        'Cover image, title, text and an optional button in one bronze-lined card.':
                '封面图、标题、正文与可选按钮，一张铜线卡片搞定。',
        'Add cover image': '添加封面图',
        'Replace cover image': '替换封面图',
        'Remove cover image': '移除封面图',
        'Card title…': '卡片标题…',
        'Describe the card…': '描述卡片内容…',
        'Add feature': '添加特性',
        'Remove feature': '移除特性',
        'Features': '特性列表',

        /* ---- Tabs ---- */
        'Tabs (Kabel)': 'Kabel 选项卡',
        'Tabbed content with a bronze underline indicator — panels switch on click.':
                '选项卡内容，铜色下划线指示，点击切换面板。',
        'Add tab': '添加选项卡',
        'Remove tab': '移除选项卡',
        'Tab title': '选项卡标题',
        'Tab title…': '选项卡标题…',
        'Tab content…': '选项卡内容…',

        /* ---- Testimonial ---- */
        'Testimonial (Kabel)': 'Kabel 客户评价',
        'Customer review with bronze stars, an italic quote and an avatar line.':
                '客户评价卡：铜色星级、斜体引言与署名行。',
        'Star rating': '星级',
                'What did the customer say…': '客户说了什么…',
        'Customer name…': '客户姓名…',
        'Role or city…': '身份或城市…',
        'Quote color': '引言颜色',
        'Add avatar': '添加头像',
        'Replace avatar': '替换头像',
        'Remove avatar': '移除头像',

        /* ---- Video ---- */
        'Video (Kabel)': 'Kabel 视频',
        'Cover image with a bronze play button — plays YouTube, Vimeo or MP4 links on click.':
                '铜色播放按钮的封面图，点击播放 YouTube / Vimeo / MP4 链接。',
        'Video link': '视频链接',
        'Paste a YouTube or Vimeo link, or a direct .mp4 file link.':
                '粘贴 YouTube 或 Vimeo 链接，或 .mp4 直链。',
        
        /* ---- Team ---- */
        'Team (Kabel)': 'Kabel 团队',
        'Team member cards with grayscale-to-color portraits, names and roles.':
                '团队成员卡：头像灰度转彩色，姓名与职位。',
        'Members': '成员',
        'Add member': '添加成员',
        'Remove member': '移除成员',
        'Member': '成员',
        'name': '姓名',
        'Member name…': '成员姓名…',
        'Role or title…': '职位或头衔…',
        'Profile link (optional)': '主页链接（可选）',
        'Role': '职位',

        /* ---- Pricing ---- */
        'Pricing (Kabel)': 'Kabel 定价',
        'Package price card with a big number, check-marked features and a CTA button.':
                '套餐价格卡：大数字价格、对勾特性列表与行动按钮。',
        'Plan name…': '套餐名称…',
        'Footnote (optional)…': '备注（可选）…',
        /* NOTE: 'Most popular' (pricing badge), 'Rated %d out of 5' (testimonial
         * aria-label) and 'Play video' (video aria-label) are intentionally NOT
         * translated here: those three strings are serialized by save() into the
         * published markup, so they must stay byte-identical to the PHP emitter. */
        'Featured package': '推荐套餐',
        'Mark as featured': '设为推荐套餐',
        'Remove featured highlight': '取消推荐标记',

        /* ---- Inquiry form / Post grid ---- */
        'Inquiry form (Kabel)': 'Kabel 询盘表单',
        'The Kabel inquiry form — submissions land in the dashboard.':
                'Kabel 询盘表单，提交内容进入后台询盘列表。',
        'Live form — renders on the published page.':
                '真实表单——在已发布页面渲染并提交。',
        'Recipient email': '收件邮箱',
        'Leave empty to use the site administration email.':
                '留空则使用站点管理员邮箱。',
        'Post grid (Kabel)': 'Kabel 文章网格',
        'Latest products, projects or posts in a card grid — updates itself.':
                '最新产品 / 工程 / 文章的卡片网格，自动更新。',
        'Content type': '内容类型',
        'Products': '产品',
        'Projects': '工程',
        'Posts': '文章',
        'How many items': '显示数量',
        'Show excerpt': '显示摘要',
        'No published items of this type yet — it will fill in automatically.':
                '该类型暂无已发布内容——发布后会自动填充。',

        /* ---- Inspector kit ---- */
        'Alignment': '对齐',
        'Corner radius': '圆角',
        'Box shadow': '投影',
        'Border width': '边框粗细',
        'Border style': '边框样式',
        'Dashed': '虚线',
        'Dotted': '点线',
        'Inner padding': '内边距',
        'Margin top': '上外边距',
        'Margin bottom': '下外边距',
        'Margin top (mobile)': '上外边距（小屏）',
        'Margin bottom (mobile)': '下外边距（小屏）',
        'Adjust margins on mobile': '单独调整小屏外边距',
        'Margins push this block up or down — works in every container.':
                '外边距用于上下顶开本区块，任何容器内都生效。',
        'Hide on desktop': '桌面端隐藏',
        'Hide on tablet': '平板端隐藏',
        'Hide on mobile': '小屏隐藏',
        'Entrance animation': '入场动画',
        'Fade up': '上浮淡入',
        'Fade in': '淡入',
        'Zoom in': '放大淡入',
        'Slide from left': '从左滑入',
        'Slide from right': '从右滑入',
        'Delay (ms)': '延迟（毫秒）',
        'Gradient colors': '渐变颜色',
        'From': '起点色',
        'To': '终点色',
        'Angle (degrees)': '角度（度）',
        'A gradient replaces the solid background color. A background image replaces the gradient.':
                '渐变会替代纯色背景；背景图又会覆盖渐变。',
        'Lift on hover': '悬停上浮',
        'Font size (px)': '字号（px）',
        'Font weight': '字重',
        'Default': '默认',
        'Line height': '行高',
        'Letter spacing (px)': '字距（px）',
        'Letter case': '大小写',
        'UPPERCASE': '全大写',
        'Capitalize': '首字母大写',
        'Desktop': '桌面端',
        'Tablet': '平板端',
        'Mobile': '小屏',
        'Follows desktop': '跟随桌面端',
        'Kabel Blocks': 'Kabel 区块',
        'Link': '链接',
        'Title': '标题',
        'Subtitle': '副标题',
        'Gap': '间距',
        'Tabs': '选项卡',
        'Move up': '上移',
        'Move down': '下移',
        'Active tab color': '选中项颜色',
        'Feature': '特性',
        'Bronze outline plus a "Most popular" badge.': '铜色描边，附“最受欢迎”角标。',
        'Reset for an equal share.': '重置后恢复均分。',
        'Country / Company': '国家 / 公司',
        'Your name': '您的姓名',
        'Your email': '您的邮箱',
        'How can we help?': '想咨询什么？',

        /* ---- 3.5.0 header elements: logo / nav menu / search ---- */
        'Logo (Kabel)': 'Kabel Logo',
        'Site logo with width control and an optional link — the header element.':
                '站点 Logo：可调宽度、可选链接，页眉核心元素。',
        'Logo width (px)': 'Logo 宽度（px）',
        'Use the toolbar "Replace image" button to swap the logo.':
                '使用工具栏的“替换图片”按钮更换 Logo。',
        'Logo': 'Logo',
        'Nav menu (Kabel)': 'Kabel 导航菜单',
        'Header navigation from a WordPress menu — auto-highlights the current page.':
                '读取 WordPress 后台菜单的页眉导航，自动高亮当前页面。',
        'Menu': '菜单',
        'Auto (primary menu location)': '自动（跟随主题菜单位置）',
        'Menu item gap (px)': '菜单项间隔（px）',
        'Item padding X (px)': '菜单项水平内边距（px）',
        'Item padding Y (px)': '菜单项垂直内边距（px）',
        'No menus yet — create one under Appearance → Menus.':
                '还没有菜单——请先在“外观 → 菜单”中创建。',
        'Hover color': '悬停颜色',
        'Search (Kabel)': 'Kabel 搜索框',
        'Header search box — submits to the site search results.':
                '页眉搜索框，提交后跳转站点搜索结果页。',
        'Search': '搜索',
        'Search width (px)': '搜索框宽度（px）',
        'Placeholder text': '占位提示文字',
        'Input background': '输入框背景',

        /* ---- 3.5.0 header elements: language switcher / contact info ---- */
        'Language switcher (Kabel)': 'Kabel 多语言切换',
        'Compact multi-language link row for the header.':
                '页眉用的紧凑多语言链接行。',
        'Languages': '语言列表',
        'Add language': '添加语言',
        'Remove language': '移除语言',
        'Language': '语言',
        'Code': '语言代码',
        'Name': '名称',
        'Current language': '当前语言',
        'Active language color': '当前语言颜色',
        'Contact info (Kabel)': 'Kabel 联系方式',
        'Phone / email / address row with icons for the header top bar.':
                '带图标的电话 / 邮箱 / 地址一行，适合页眉顶栏。',
        'Contact items': '联系项目',
        'Add item': '添加项目',
        'Remove item': '移除项目',
        'Text': '文字',
        'Item gap (px)': '项目间隔（px）',
        'GTranslate code': 'GTranslate 代码',
        'GTranslate switches the language — no link needed. Use its codes, e.g. en, zh-CN, it.':
                'GTranslate 负责切换语言，无需填写链接。请使用它的语言代码，如 en、zh-CN、it。',
        'GTranslate is active — these links switch the language live on the front end.':
                'GTranslate 已激活——这些链接会在前台实时切换语言。',
        'GTranslate plugin not detected. Install & activate it (Plugins → Add New → "GTranslate"), then these links switch languages automatically.':
                '未检测到 GTranslate 插件。请在「插件 → 安装插件」搜索并激活 GTranslate，之后这些链接即可自动切换语言。',
        'Language source': '语言来源',
        'Custom links': '自定义链接',
        'GTranslate (auto translate)': 'GTranslate（自动翻译）',
        'The language list follows the GTranslate plugin — pick languages under Settings → GTranslate and the header updates automatically.':
                '语言列表由 GTranslate 插件统一管理——在「设置 → GTranslate」中勾选语言，页眉会自动跟随更新。',
        'GTranslate plugin not detected. Install & activate it (Plugins → Add New → "GTranslate"), then the languages it exposes appear here automatically.':
                '未检测到 GTranslate 插件。请在「插件 → 安装插件」搜索并激活 GTranslate，它开放的语言会自动出现在这里。',
        'Resync from GTranslate': '从 GTranslate 重新同步',
        'Open GTranslate settings ↗': '打开 GTranslate 设置 ↗',
        'Saved for the no-plugin fallback — the front end always rebuilds this row from the live GTranslate settings.':
                '此列表仅作无插件时的兜底——前台始终按 GTranslate 的实时设置重建这一行。',
        /* ---- Global colors ---- */
        'Global': '全局',
        'Manage global colors': '管理全局颜色',
        /* ---- 3.10.0 language dropdown ---- */
        'Multi-language switcher for the header — dropdown button or inline links.':
                '页眉多语言切换器——下拉按钮或平铺链接。',
        'Dropdown button': '下拉按钮',
        'Inline links': '平铺链接',

        /* ---- 3.12.0 header / nav / search / kit / style-clipboard ---- */
        'Header (Kabel)': '页眉（Kabel）',
        'Site header container — sticky / transparent / colors. Fill it with the logo, navigation, search, contact info and language blocks.':
                '站点页眉容器——吸顶、透明与配色。可放入徽标、导航、搜索、联系信息与语言区块。',
        'Position': '定位方式',
        'Static': '静态',
        'Sticky': '吸顶',
        'Hide on scroll': '下滚时隐藏',
        'Transparent over hero': '首屏透明',
        'Floats above the first section with no background until the page scrolls.':
                '悬浮于首屏分区之上，页面滚动前不显示背景。',
        'Height padding': '高度内边距',
        'Pick a color and drag the alpha slider for a frosted look.':
                '选择颜色并拖动透明度滑块，即可获得磨砂效果。',
        'Accent color': '强调色',
        'Backdrop blur': '背景模糊',
        'Frosted-glass effect over scrolling content.': '滚动内容之上的磨砂玻璃效果。',
        'Always': '总是',
        'Never': '从不',
        'Navigation (Kabel)': '导航（Kabel）',
        'The site header menu — reads a menu from Appearance → Menus; this block only styles it.':
                '站点页眉菜单——从「外观 → 菜单」读取；此区块仅负责样式。',
        'Auto — Primary Navigation location': '自动——主菜单位置',
        'Menu source': '菜单来源',
        '%d items · managed in Appearance → Menus': '%d 个菜单项 · 在「外观 → 菜单」中管理',
        'No menus yet — items below are a placeholder.': '还没有菜单——下方菜单项为占位内容。',
        'Manage menu items →': '管理菜单项 →',
        'Collapse below': '在此宽度以下收起',
        'Wide dropdowns': '宽幅下拉',
        'Parents with 4+ items (or the mega class) use the wide two-column dropdown.':
                '含 4 个以上子项（或带 mega 类）的父级使用宽幅双列下拉。',
        'Link color': '链接颜色',
        'Underline color': '下划线颜色',
        'Dropdown background': '下拉背景',
        'Dropdown link color': '下拉链接颜色',
        'This menu is read from Appearance → Menus. Create a menu there and it will appear here.':
                '此菜单读取自「外观 → 菜单」。在那里创建菜单后，会自动显示在这里。',
        'Search button (Kabel)': '搜索按钮（Kabel）',
        'Icon button that opens the site search overlay — for headers and navigation bars.':
                '打开站点搜索浮层的图标按钮——用于页眉与导航栏。',
        'Label': '标签文字',
        'Show label': '显示文字',
        'Responsive mode': '响应式模式',
        'Custom CSS': '自定义 CSS',
        'Scoped to this block only — every selector is prefixed automatically.':
                '仅作用于本区块——所有选择器都会自动加前缀。',
        'Selectors are prefixed with this block’s unique class; @media blocks are supported.':
                '选择器已加本区块唯一类名前缀；支持 @media 块。',
        'HTML anchor': 'HTML 锚点',
        'my-section': 'my-section',
        'Used as the id attribute — link to it with #anchor.':
                '用作 id 属性——通过 #锚点 链接到它。',
        'Copy styles': '复制样式',
        'Paste styles': '粘贴样式',
        'Paste styles (nothing copied yet)': '粘贴样式（尚未复制过）',
};

/**
 * Strings that are serialized by save() into published markup (the pricing
 * badge, the testimonial star aria-label, the video play aria-label) and
 * therefore must stay byte-identical to the PHP emitter. The build gate
 * exempts these from the dictionary-coverage check.
 */
export const ZH_SAVE_EXEMPT = [
        'Most popular',
        'Rated %d out of 5',
        'Play video',
        'Switch language',
];
