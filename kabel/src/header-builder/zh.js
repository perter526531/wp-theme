/**
 * Simplified-Chinese UI dictionary for the Header Builder Customizer app.
 *
 * Every UI string is authored as an English key passed through t(); t()
 * returns the Chinese entry when present and falls back to the key itself
 * (so an untranslated string stays readable, never blank). Entries provided
 * by PHP via window.KabelHBData.l10n act as a second fallback layer.
 */

export const ZH = {
        /* ---- Shell / top bar ---- */
        'Header Builder': '页眉构建器',
        'Builder active': '构建器生效',
        'Blocks header active': '块页眉生效',
        'Classic header active': '内置页眉生效',
        'Which header source is live on the site': '当前站点正在使用的页眉来源',
        'History': '撤销/重做',
        'Undo': '撤销',
        'Redo': '重做',
        'Preview device': '预览设备',
        'Desktop': '桌面',
        'Mobile': '手机',
        'Save': '保存',
        'Saving…': '保存中…',
        'Saved': '已保存',
        'Save failed.': '保存失败',
        'Unsaved changes': '有未保存的修改',
        'Open Front End': '打开前台',
        'More actions': '更多操作',
        'Clear Builder Data': '清空构建器数据（回退到块页眉/内置页眉）',
        'Edit Header Blocks (Legacy)': '编辑块页眉（旧方案）',
        'Clear builder data?': '清空构建器数据？',
        'This removes the saved header model. The site falls back to the blocks header or the classic header. This cannot be undone.':
                '将删除已保存的页眉模型，站点会回退到块页眉或内置页眉。此操作不可撤销。',
        'Clear': '清空',
        'Delete failed.': '删除失败',
        'Setup error: KabelHBData is missing, the Header Builder cannot start.':
                '配置错误：缺少 KabelHBData，页眉构建器无法启动。',

        /* ---- Sidebar / element library ---- */
        'Elements': '元素',
        'Settings': '设置',
        'Drag an element onto the structure, or click to add it.':
                '把元素拖到上方结构条，或直接点击添加。',
        'Requires WooCommerce': '需要 WooCommerce',
        'Select an element, a row, or mobile settings to edit.':
                '选择一个元素、行或移动端设置进行编辑。',

        /* ---- Element names (registry) ---- */
        'Site Logo': '站点标志',
        'Menu': '菜单',
        'Button': '按钮',
        'Search': '搜索',
        'Social Icons': '社交图标',
        'Custom HTML': '自定义 HTML',
        'Contact Info': '联系信息',
        'Text': '文本',
        'Language Switcher': '语言切换',
        'Account': '账户',
        'Cart': '购物车',
        'Unknown element': '未知元素',

        /* ---- Structure bar ---- */
        'Structure': '结构',
        'Row {n}': '第 {n} 行',
        'Row settings': '行设置',
        'Delete row': '删除行',
        'Delete this row?': '删除这行？',
        'The row and its element placements will be removed. Elements used elsewhere stay.':
                '该行及其元素布局将被移除，其他位置仍在使用的元素会保留。',
        'Delete': '删除',
        'Add Row': '添加行',
        'Maximum of 3 rows reached.': '最多 3 行。',
        'Left': '左',
        'Center': '居中',
        'Right': '右',
        'Browse elements': '浏览元素',
        'Remove from this zone': '从该区移除',
        'Move up': '上移',
        'Move down': '下移',
        'Remove item': '移除条目',
        'Mobile Header': '移动端页眉',
        'Mobile settings': '移动端设置',
        'Drawer panel (opens via hamburger)': '抽屉面板（点汉堡展开）',
        'Drawer elements': '抽屉元素',
        'Drawer is empty. Drag elements here.': '抽屉是空的，拖动元素到这里。',
        'Hamburger side': '汉堡位置',
        'Drawer side': '抽屉滑出方向',
        'Breakpoint': '断点',
        'Below this viewport width the mobile header is shown.':
                '视口窄于该宽度时显示移动端页眉。',

        /* ---- Preview ---- */
        'Live preview': '实时预览',
        'Loading preview…': '预览加载中…',
        'Preview updated': '预览已更新',
        'Preview update failed.': '预览更新失败',

        /* ---- Logo ---- */
        'Source': '来源',
        'Site identity': '站点标志',
        'Image': '图片',
        'Logo text': '标志文本',
        'Tagline': '副标题',
        'Show tagline': '显示副标题',
        'Choose Image': '选择图片',
        'Replace Image': '更换图片',
        'Remove Image': '移除图片',
        'Logo width': '标志宽度',
        'Uses the site logo from Appearance → Settings.': '使用「外观 → 设置」中的站点标志。',
        'Site name': '站点名称',
        'Media library is not available.': '媒体库不可用。',

        /* ---- Menu ---- */
        'Auto (first menu)': '自动（第一个菜单）',
        'Font size': '字号',
        'Gap': '间距',
        'Alignment': '对齐',
        'Link color': '链接颜色',
        'Hover color': '悬停颜色',
        'Menu style': '菜单样式',
        'Plain': '朴素',
        'Underline': '下划线',
        'Pill': '胶囊',
        'Submenu indicator': '子菜单指示箭头',

        /* ---- Button ---- */
        'Button text': '按钮文字',
        'URL': '链接地址',
        'Variant': '样式变体',
        'Gold': '金色',
        'Outline': '描边',
        'Ghost': '幽灵',
        'Dark': '深色',
        'Light': '浅色',
        'Size': '尺寸',
        'Small': '小',
        'Medium': '中',
        'Open in new tab': '新窗口打开',

        /* ---- Search ---- */
        'Search style': '搜索样式',
        'Icon': '图标',
        'Field': '输入框',
        'Placeholder': '占位提示文字',
        'Field width': '输入框宽度',
        'Applies to the field style.': '仅对输入框样式生效。',

        /* ---- Social ---- */
        'Social links': '社交链接',
        'No icons yet — click “Add Icon” to create one.':
                '还没有图标，点击「添加图标」创建一个。',
        'Add Icon': '添加图标',
        'Icon size': '图标大小',
        'Icon gap': '图标间距',
        'WhatsApp': 'WhatsApp',
        'Facebook': 'Facebook',
        'Instagram': 'Instagram',
        'LinkedIn': 'LinkedIn',
        'YouTube': 'YouTube',
        'X (Twitter)': 'X（Twitter）',
        'TikTok': 'TikTok',
        'Email': '邮箱',
        'Phone': '电话',

        /* ---- HTML / contact / text / lang / account / cart ---- */
        'HTML content': 'HTML 内容',
        'Will be filtered in safe mode because your account lacks the unfiltered_html capability.':
                '当前账号没有 unfiltered_html 权限，内容将以安全模式过滤。',
        'Phone number': '电话号码',
        'Email address': '邮箱地址',
        'Address': '地址',
        'Show phone': '显示电话',
        'Show email': '显示邮箱',
        'Show address': '显示地址',
        'Content': '内容',
        'Color': '颜色',
        'Language style': '语言样式',
        'Code': '代码',
        'Name': '名称',
        'Login text': '登录文字',
        'Login URL': '登录链接',
        'Show item count': '显示数量角标',

        /* ---- Row settings ---- */
        'Background color': '背景颜色',
        'Text color': '文字颜色',
        'Vertical padding': '上下内边距',
        'Bottom border': '底部边框',
        'Sticky': '吸附',
        'When any row is sticky, the whole header stays pinned while scrolling.':
                '开启任一行的吸附后，整个页眉随滚动悬停。',
        'Transparent': '透明',
        'Transparent rows overlay the page content — great with hero sections.':
                '透明行悬浮在页面内容之上，适合配合首屏大图。',

        /* ---- Shared controls / dialogs ---- */
        'Default': '默认',
        'Cancel': '取消',
        'Confirm': '确认',
};

/* PHP-side l10n entries (window.KabelHBData.l10n) act as a second layer. */
const PHP_L10N =
        ( typeof window !== 'undefined' &&
                window.KabelHBData &&
                window.KabelHBData.l10n ) ||
        {};

/** t('English key') -> Chinese, with key + PHP l10n fallbacks. */
export function t( key ) {
        if ( Object.prototype.hasOwnProperty.call( ZH, key ) ) {
                return ZH[ key ];
        }
        if ( Object.prototype.hasOwnProperty.call( PHP_L10N, key ) ) {
                return PHP_L10N[ key ];
        }
        return key;
}
