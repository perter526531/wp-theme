<?php
/**
 * Block patterns — insert-ready Kabel pages and sections, all OUR blocks.
 *
 * Two pattern categories in the inserter:
 *   Kabel Pages    — whole pages (the same markup the one-click seeder writes)
 *   Kabel Sections — single self-developed blocks pre-filled with real Kabel
 *                    content, ready to restyle from the sidebar
 *
 * Because every section pattern IS one of our blocks, editing a pattern
 * after insert is exactly the same experience as editing a seeded page.
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the pattern categories (the inserter's block category itself is
 * registered by inc/assets.php — pinned to the top).
 */
function kabel_blocks_register_categories(): void {
        if ( function_exists( 'register_block_pattern_category' ) ) {
                register_block_pattern_category( 'kabel-pages', array(
                        'label'       => __( 'Kabel 整页', 'kabel-blocks' ),
                        'description' => __( '用 Kabel 自研区块拼好的完整页面——插入后随意修改。', 'kabel-blocks' ),
                ) );
                register_block_pattern_category( 'kabel-sections', array(
                        'label'       => __( 'Kabel 区块段落', 'kabel-blocks' ),
                        'description' => __( '预填真实 Kabel 内容的单个自研区块——挑选、插入、改样式。', 'kabel-blocks' ),
                ) );
        }
}
add_action( 'init', 'kabel_blocks_register_categories', 5 );

/**
 * Register the patterns.
 */
function kabel_blocks_register_patterns(): void {
        if ( ! function_exists( 'register_block_pattern' ) ) {
                return;
        }

        $register = static function ( string $slug, string $title, string $description, array $keywords, string $content, array $categories ): void {
                register_block_pattern( 'kabel-blocks/' . $slug, array(
                        'title'       => $title,
                        'description' => $description,
                        'categories'  => $categories,
                        'keywords'    => $keywords,
                        'content'     => $content,
                ) );
        };

        /* ---------- Full pages (byte-identical to the seeder output). ---------- */

        $pages = array(
                'home'                     => __( '整页 — 首页', 'kabel-blocks' ),
                'about'                    => __( '整页 — 关于我们', 'kabel-blocks' ),
                'eco-friendly'             => __( '整页 — 环保', 'kabel-blocks' ),
                'designer'                 => __( '整页 — 设计师', 'kabel-blocks' ),
                'products'                 => __( '整页 — 产品', 'kabel-blocks' ),
                'morandi-colors'           => __( '整页 — 莫兰迪色系', 'kabel-blocks' ),
                'van-gogh-color'           => __( '整页 — 梵高色', 'kabel-blocks' ),
                'macaron-colors'           => __( '整页 — 马卡龙色系', 'kabel-blocks' ),
                'museum-collection-colors' => __( '整页 — 博物馆典藏色', 'kabel-blocks' ),
                'projects'                 => __( '整页 — 工程', 'kabel-blocks' ),
                'project-procurement'      => __( '整页 — 工程集采', 'kabel-blocks' ),
                'customize'                => __( '整页 — 定制', 'kabel-blocks' ),
                'distributor'              => __( '整页 — 经销商', 'kabel-blocks' ),
                'blog'                     => __( '整页 — 博客', 'kabel-blocks' ),
                'contact'                  => __( '整页 — 联系我们', 'kabel-blocks' ),
                'faq'                      => __( '整页 — 常见问题', 'kabel-blocks' ),
                'landing'                  => __( '整页 — 活动落地页', 'kabel-blocks' ),
                'starter'                  => __( '整页 — 起步页', 'kabel-blocks' ),
        );

        $layouts = kabel_block_layouts();
        foreach ( $pages as $slug => $title ) {
                $register(
                        'page-' . $slug,
                        $title,
                        $layouts[ $slug ]['desc'],
                        array( 'kabel', 'page', $slug, '整页', '页面' ),
                        (string) kabel_get_block_layout( $slug ),
                        array( 'kabel-pages', 'featured' )
                );
        }

        /* ---------- Sections — one real-content block each. ---------- */

        $register( 'hero-velvet',
                __( 'Hero — 天鹅绒质感开场', 'kabel-blocks' ),
                __( '分栏 Hero：左侧 Kabel 大标题，右侧天鹅绒质感产品图，带咨询按钮。', 'kabel-blocks' ),
                array( 'hero', 'banner', 'velvet', 'opener', '开场', '首屏', '横幅' ),
                kbl_b( 'hero', array(
                        'zone'         => 'a',
                        'layout'       => 'split',
                        'eyebrow'      => __( 'HIGH-END ARTISTIC COATING SERIES', 'kabel-blocks' ),
                        'title'        => __( 'Velvet Texture', 'kabel-blocks' ),
                        'sub'          => __( 'Luxury velvet matte finish.', 'kabel-blocks' ),
                        'image'        => kbl_asset_id( 'velvet-texture-1-1.webp' ),
                        'primaryLabel' => __( 'CONSULT NOW', 'kabel-blocks' ),
                ) ),
                array( 'kabel-sections' )
        );

        $register( 'split-nobel',
                __( '图文 — 诺贝尔合作', 'kabel-blocks' ),
                __( 'Kabel 荣誉首席科学家故事与 Stoddart 致敬图并列。', 'kabel-blocks' ),
                array( 'split', 'two column', 'nobel', 'story', '图文', '故事' ),
                kbl_b( 'split', array(
                        'eyebrow' => __( 'KABEL HONORARY CHIEF SCIENTIST', 'kabel-blocks' ),
                        'title'   => __( 'Collaborative Tribute to Nobel Laureate Professor James Fraser Stoddart', 'kabel-blocks' ),
                        'body'    => __( 'Centered on material technology and design aesthetics, we continuously explore the possibilities of wall art to create spatial experiences that combine performance, beauty, and health benefits.', 'kabel-blocks' ),
                        'image'   => kbl_asset_id( '2efa8714-324e-445b-8d3e-4f68e917cc8a2-1.webp' ),
                ) ),
                array( 'kabel-sections' )
        );

        $register( 'features-why-kabel',
                __( '卡片 — 为什么建筑师选 Kabel', 'kabel-blocks' ),
                __( '五张图标卡：羊绒触感、墙地一体、防水性能、净化甲醛、无限色彩。', 'kabel-blocks' ),
                array( 'features', 'cards', 'reasons', 'architects', '卡片', '特性' ),
                kbl_b( 'features', array(
                        'eyebrow' => __( 'WHY ARCHITECTS CHOOSE KABEL', 'kabel-blocks' ),
                        'sub'     => __( 'Every KABEL finish is engineered to deliver performance that matches its beauty', 'kabel-blocks' ),
                        'columns' => 3,
                        'items'   => array(
                                array(
                                        'icon'  => 'sparkle',
                                        'title' => __( 'Cashmere Touch', 'kabel-blocks' ),
                                        'text'  => __( 'Refined through six artisanal techniques, the surface reveals a soft, cashmere-like touch — subtle, smooth, and quietly luxurious.', 'kabel-blocks' ),
                                ),
                                array(
                                        'icon'  => 'cube',
                                        'title' => __( 'Wall & Floor Integration', 'kabel-blocks' ),
                                        'text'  => __( 'Seamless cement texture that unifies walls and floors as a single continuous surface — creating pure, unified spatial experiences.', 'kabel-blocks' ),
                                ),
                                array(
                                        'icon'  => 'drop',
                                        'title' => __( 'Waterproof Performance', 'kabel-blocks' ),
                                        'text'  => __( 'Exceptional resistance to moisture and stains, preserving the wall’s pristine elegance over years of daily use.', 'kabel-blocks' ),
                                ),
                                array(
                                        'icon'  => 'leaf',
                                        'title' => __( 'Purify formaldehyde', 'kabel-blocks' ),
                                        'text'  => __( 'Active formaldehyde degradation technology purifies indoor air — 94.1% purification rate, certified by international standards.', 'kabel-blocks' ),
                                ),
                                array(
                                        'icon'  => 'medal',
                                        'title' => __( 'Unlimited Colour', 'kabel-blocks' ),
                                        'text'  => __( 'From Morandi tones to Museum Collections — our colour system delivers precise, fade-resistant hues for every design vision.', 'kabel-blocks' ),
                                ),
                        ),
                ) ),
                array( 'kabel-sections' )
        );

        $register( 'process-procurement-6',
                __( '流程 — 工程集采六步', 'kabel-blocks' ),
                __( '从需求沟通到全球货运支持——Kabel 工程服务体系。', 'kabel-blocks' ),
                array( 'process', 'steps', 'procurement', 'workflow', '流程', '步骤' ),
                kbl_b( 'process', array(
                        'eyebrow' => __( 'PROFESSIONAL SERVICES', 'kabel-blocks' ),
                        'items'   => array(
                                array(
                                        'title' => __( 'Project requirements communication', 'kabel-blocks' ),
                                        'text'  => __( 'Understand the project positioning, spatial requirements, design style, and application environment to develop a tailored solution for the project.', 'kabel-blocks' ),
                                ),
                                array(
                                        'title' => __( 'Product Solution Design', 'kabel-blocks' ),
                                        'text'  => __( 'Recommend suitable decorative coating systems, texture effects, and color schemes based on the characteristics of the architectural space.', 'kabel-blocks' ),
                                ),
                                array(
                                        'title' => __( 'Sample customization confirmation', 'kabel-blocks' ),
                                        'text'  => __( 'Provide professional-quality mock-ups to help the design team verify the final material finish.', 'kabel-blocks' ),
                                ),
                                array(
                                        'title' => __( 'Construction technical support', 'kabel-blocks' ),
                                        'text'  => __( 'Provide guidance on construction techniques, technical training, and on-site application support to ensure the desired final outcome.', 'kabel-blocks' ),
                                ),
                                array(
                                        'title' => __( 'Mass production supply', 'kabel-blocks' ),
                                        'text'  => __( 'Leveraging a modern production system to ensure stable supply and batch project delivery.', 'kabel-blocks' ),
                                ),
                                array(
                                        'title' => __( 'Global Freight Support', 'kabel-blocks' ),
                                        'text'  => __( 'Support international transportation and project delivery coordination, and provide efficient supply chain services for overseas customers.', 'kabel-blocks' ),
                                ),
                        ),
                ) ),
                array( 'kabel-sections' )
        );

        $register( 'gallery-morandi',
                __( '图库 — 莫兰迪色卡', 'kabel-blocks' ),
                __( '三列网格中的六款莫兰迪色卡。', 'kabel-blocks' ),
                array( 'gallery', 'images', 'morandi', 'palette', '色卡', '图库' ),
                kbl_b( 'gallery', array(
                        'zone'    => 'alt',
                        'columns' => 3,
                        'ids'     => kbl_asset_ids( array(
                                '_03.webp',
                                '_04.webp',
                                '_05.webp',
                                '_07.webp',
                                '_08.webp',
                                '_09.webp',
                        ) ),
                ) ),
                array( 'kabel-sections' )
        );

        $register( 'stats-purify',
                __( '数据 — 净化指标', 'kabel-blocks' ),
                __( '两经独立检测的 Kabel 数字：94.1% 净化率、82.1% 持久率。', 'kabel-blocks' ),
                array( 'stats', 'numbers', 'data', 'eco', '数据', '数字' ),
                kbl_b( 'stats', array(
                        'zone'  => 'a',
                        'pad'   => 'compact',
                        'title' => __( 'Formaldehyde degradation effect', 'kabel-blocks' ),
                        'items' => array(
                                array( 'value' => '94.1%', 'text' => __( 'formaldehyde purification rate', 'kabel-blocks' ) ),
                                array( 'value' => '82.1%', 'text' => __( 'persistent purification rate', 'kabel-blocks' ) ),
                        ),
                ) ),
                array( 'kabel-sections' )
        );

        $register( 'faq-surface',
                __( '常见问题 — 墙面养护', 'kabel-blocks' ),
                __( '三个手风琴问答：耐擦洗、潮湿季节、颜色持久。', 'kabel-blocks' ),
                array( 'faq', 'accordion', 'surface', 'questions', '问答', '常见问题' ),
                kbl_b( 'faq', array(
                        'eyebrow' => __( 'FAQ', 'kabel-blocks' ),
                        'items'   => array(
                                array(
                                        'q' => __( 'Is this paint scrub-resistant? What if children stain the walls?', 'kabel-blocks' ),
                                        'a' => __( 'Our artistic paints are highly wear-resistant. Most stains can be removed by simply wiping with a damp cloth without damaging the artistic texture of the wall.', 'kabel-blocks' ),
                                ),
                                array(
                                        'q' => __( 'Is artistic paint suitable for humid environments, such as the rainy season?', 'kabel-blocks' ),
                                        'a' => __( 'Absolutely. Kabel artistic paint features excellent anti-mold and moisture-resistant properties; its breathability effectively prevents peeling or mold growth on walls.', 'kabel-blocks' ),
                                ),
                                array(
                                        'q' => __( 'Will artistic paint fade over time?', 'kabel-blocks' ),
                                        'a' => __( 'No. Kabel uses high-quality weather-resistant pigments with excellent UV resistance, ensuring colors stay vibrant and fresh for years.', 'kabel-blocks' ),
                                ),
                        ),
                ) ),
                array( 'kabel-sections' )
        );

        $register( 'cta-consult',
                __( '行动横幅 — 找到家的色彩', 'kabel-blocks' ),
                __( '色彩服务转化横幅，带联系按钮。', 'kabel-blocks' ),
                array( 'cta', 'conversion', 'colors', 'banner', '转化', '行动' ),
                kbl_b( 'cta', array(
                        'title'    => __( 'Find colors that feel like home', 'kabel-blocks' ),
                        'sub'      => __( 'Start your next project with expert guidance and a palette that’s tailored to your space, light and lifestyle, for both the inside and outside of your home.', 'kabel-blocks' ),
                        'btnLabel' => __( 'CONTACT US', 'kabel-blocks' ),
                ) ),
                array( 'kabel-sections' )
        );

        $register( 'inquiry-block',
                __( '询盘 — 留资表单', 'kabel-blocks' ),
                __( 'Kabel 询盘区块——提交进入后台询盘列表，配 Kabel 主题时可邮件转发。', 'kabel-blocks' ),
                array( 'inquiry', 'form', 'lead', 'contact', '询盘', '表单' ),
                kbl_b( 'inquiry', array(
                        'eyebrow' => __( 'CONSULT NOW', 'kabel-blocks' ),
                        'title'   => __( 'CONTACT US FOR ANY QUESTIONS', 'kabel-blocks' ),
                        'message' => __( 'Hello Kabel, I would like to consult about:', 'kabel-blocks' ),
                ) ),
                array( 'kabel-sections' )
        );

        $register( 'contact-cards',
                __( '联系 — Kabel 信息卡', 'kabel-blocks' ),
                __( '总部、分公司、WhatsApp 与邮箱的四卡网格。', 'kabel-blocks' ),
                array( 'contact', 'cards', 'info', 'address', '联系', '信息卡' ),
                kbl_b( 'contact', array(
                        'eyebrow' => __( 'REACH KABEL', 'kabel-blocks' ),
                        'columns' => 4,
                        'items'   => array(
                                array(
                                        'icon'  => 'pin',
                                        'title' => __( 'Kabel Headquarters', 'kabel-blocks' ),
                                        'text'  => __( 'Druento, Torino, Italy', 'kabel-blocks' ),
                                ),
                                array(
                                        'icon'  => 'pin',
                                        'title' => __( 'Kabel Branch', 'kabel-blocks' ),
                                        'text'  => __( 'Foshan, Guangdong, China', 'kabel-blocks' ),
                                ),
                                array(
                                        'icon'  => 'phone',
                                        'title' => __( 'WhatsApp', 'kabel-blocks' ),
                                        'text'  => __( '+86 18033205148', 'kabel-blocks' ),
                                ),
                                array(
                                        'icon'  => 'mail',
                                        'title' => __( 'Email', 'kabel-blocks' ),
                                        'text'  => __( 'kabelpaints@gmail.com', 'kabel-blocks' ),
                                ),
                        ),
                ) ),
                array( 'kabel-sections' )
        );

        $register( 'logos-partners',
                __( '标识 — 材料合作伙伴', 'kabel-blocks' ),
                __( '巴斯夫 / 陶氏化学 / 万华化学伙伴墙（含文字兜底）。', 'kabel-blocks' ),
                array( 'logos', 'partners', 'basf', 'materials', '伙伴', '标识' ),
                kbl_b( 'logos', array(
                        'eyebrow' => __( 'GREAT MATERIALS', 'kabel-blocks' ),
                        'ids'     => kbl_asset_ids( array(
                                '5dccc95a-3135-4baa-8581-3a40e7cdb7e2.webp',
                                '06b15871-66d3-40be-8853-870e74df4dae.webp',
                                '300912f0-6103-4e18-9af9-a580c050f410.webp',
                        ) ),
                        'names'   => __( "BASF\nDow Chemical\nWanhua Chemical", 'kabel-blocks' ),
                ) ),
                array( 'kabel-sections' )
        );

        $register( 'milestones-heritage',
                __( '里程碑 — Kabel 传承', 'kabel-blocks' ),
                __( '时间线形式的 Kabel 故事：都灵起源、百年传承、材料伙伴、诺贝尔合作、新加坡展厅。', 'kabel-blocks' ),
                array( 'milestones', 'timeline', 'heritage', 'history', '里程碑', '时间线' ),
                kbl_b( 'milestones', array(
                        'eyebrow' => __( 'OUR HERITAGE', 'kabel-blocks' ),
                        'items'   => array(
                                array(
                                        'year' => __( 'Druento', 'kabel-blocks' ),
                                        'text' => __( 'KABEL originates from Druento, Torino — the heart of Italian decorative coating artistry.', 'kabel-blocks' ),
                                ),
                                array(
                                        'year' => __( 'Heritage', 'kabel-blocks' ),
                                        'text' => __( 'With over a century of heritage, KABEL blends Italian aesthetics with modern innovation, continuously redefining the possibilities of wall finishes.', 'kabel-blocks' ),
                                ),
                                array(
                                        'year' => __( 'Materials', 'kabel-blocks' ),
                                        'text' => __( 'We partner with BASF, Dow Chemical, and Wanhua Chemical to safely convert formaldehyde into water using dual purification technology.', 'kabel-blocks' ),
                                ),
                                array(
                                        'year' => __( 'Science', 'kabel-blocks' ),
                                        'text' => __( 'We are honored to collaborate with Nobel Laureate Professor James Fraser Stoddart, appointing him as Kabel Honorary Chief Scientist.', 'kabel-blocks' ),
                                ),
                                array(
                                        'year' => __( '2026', 'kabel-blocks' ),
                                        'text' => __( 'Kabel Decorative Coatings: First Singapore Showroom Officially Unveiled.', 'kabel-blocks' ),
                                ),
                        ),
                ) ),
                array( 'kabel-sections' )
        );
}
add_action( 'init', 'kabel_blocks_register_patterns', 20 );
