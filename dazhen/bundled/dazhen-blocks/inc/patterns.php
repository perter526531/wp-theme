<?php
/**
 * Block patterns — insert-ready pages and sections, all OUR blocks.
 *
 * Two pattern categories in the inserter:
 *   Dazhen Pages    — whole pages (same markup the one-click seeder writes)
 *   Dazhen Sections — single self-developed blocks pre-filled with demo
 *                     content, ready to restyle from the sidebar
 *
 * Because every section pattern IS one of our blocks, editing a pattern
 * after insert is exactly the same experience as editing a seeded page.
 *
 * @package Dazhen_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the pattern categories + the inserter block category.
 */
function dazhen_blocks_register_categories(): void {
	if ( function_exists( 'register_block_pattern_category' ) ) {
		register_block_pattern_category( 'dazhen-blocks-pages', array(
			'label'       => __( 'Dazhen Pages · 大振整页模板', 'dazhen-blocks' ),
			'description' => __( 'Complete pages assembled from Dazhen blocks — insert one, edit everything.', 'dazhen-blocks' ),
		) );
		register_block_pattern_category( 'dazhen-blocks', array(
			'label'       => __( 'Dazhen Sections · 大振区段', 'dazhen-blocks' ),
			'description' => __( 'Single Dazhen blocks with demo content — pick, insert, restyle.', 'dazhen-blocks' ),
		) );
	}

	add_filter( 'block_categories_all', static function ( array $categories ): array {
		$categories[] = array(
			'slug'  => 'dazhen-blocks',
			'title' => __( 'Dazhen Blocks · 大振内容块', 'dazhen-blocks' ),
			'icon'  => 'screenoptions',
		);
		return $categories;
	} );
}
add_action( 'init', 'dazhen_blocks_register_categories', 5 );

/**
 * Register the patterns.
 */
function dazhen_blocks_register_patterns(): void {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	$register = static function ( string $slug, string $title, string $description, array $keywords, string $content, array $categories ): void {
		register_block_pattern( 'dazhen-blocks/' . $slug, array(
			'title'       => $title,
			'description' => $description,
			'categories'  => $categories,
			'keywords'    => $keywords,
			'content'     => $content,
		) );
	};

	/* ---------- Full pages (byte-identical to the seeder output). ---------- */

	$pages = array(
		'home'      => __( 'Page — Home · 首页', 'dazhen-blocks' ),
		'about'     => __( 'Page — About Us · 关于我们', 'dazhen-blocks' ),
		'services'  => __( 'Page — OEM/ODM Services · 服务页', 'dazhen-blocks' ),
		'solutions' => __( 'Page — Solutions · 方案页', 'dazhen-blocks' ),
		'contact'   => __( 'Page — Contact · 联系我们', 'dazhen-blocks' ),
		'faq'       => __( 'Page — FAQ · 常见问题', 'dazhen-blocks' ),
		'landing'   => __( 'Page — Campaign Landing · 活动落地页', 'dazhen-blocks' ),
		'starter'   => __( 'Page — Starter · 通用起步页', 'dazhen-blocks' ),
	);

	foreach ( $pages as $slug => $title ) {
		$register(
			'page-' . $slug,
			$title,
			dazhen_block_layouts()[ $slug ]['desc'],
			array( 'dazhen', 'page', $slug ),
			(string) dazhen_get_block_layout( $slug ),
			array( 'dazhen-blocks-pages', 'featured' )
		);
	}

	/* ---------- Sections — one demo-filled block each. ---------- */

	$register( 's-hero',
		__( 'Hero — page opener · 英雄区', 'dazhen-blocks' ),
		__( 'Deep-sea hero with headline, intro and two buttons.', 'dazhen-blocks' ),
		array( 'hero', 'banner', 'hero region' ),
		dzbl( 'hero', array(
			'eyebrow'        => __( 'EYEBROW LABEL', 'dazhen-blocks' ),
			'title'          => __( 'Your strongest promise, in one line', 'dazhen-blocks' ),
			'sub'            => __( 'One supporting sentence that makes the promise concrete and credible.', 'dazhen-blocks' ),
			'primaryLabel'   => __( 'Primary action', 'dazhen-blocks' ),
			'secondaryLabel' => __( 'Secondary', 'dazhen-blocks' ),
			'secondaryUrl'   => '/contact/',
		) ),
		array( 'dazhen-blocks' )
	);

	$register( 's-stats',
		__( 'Stats — number strip · 数据条', 'dazhen-blocks' ),
		__( 'Big numbers with labels on the deep background.', 'dazhen-blocks' ),
		array( 'stats', 'numbers', 'data strip' ),
		dzbl( 'stats', array(
			'items' => array(
				array( 'value' => '12+', 'text' => __( 'years experience', 'dazhen-blocks' ) ),
				array( 'value' => '38', 'text' => __( 'countries served', 'dazhen-blocks' ) ),
				array( 'value' => '99%', 'text' => __( 'inspection pass rate', 'dazhen-blocks' ) ),
				array( 'value' => '24h', 'text' => __( 'quote turnaround', 'dazhen-blocks' ) ),
			),
		) ),
		array( 'dazhen-blocks' )
	);

	$register( 's-features',
		__( 'Cards — feature grid · 卖点卡片', 'dazhen-blocks' ),
		__( 'Icon cards for reasons and highlights.', 'dazhen-blocks' ),
		array( 'features', 'cards', 'selling points' ),
		dzbl( 'features', array(
			'eyebrow' => __( 'WHY US', 'dazhen-blocks' ),
			'title'   => __( 'Four reasons to choose you', 'dazhen-blocks' ),
			'columns' => 4,
			'items'   => array(
				array( 'icon' => 'medal', 'title' => __( 'Certified', 'dazhen-blocks' ), 'text' => __( 'ISO 9001, CE, RoHS handled in-house.', 'dazhen-blocks' ) ),
				array( 'icon' => 'wrench', 'title' => __( 'Engineering', 'dazhen-blocks' ), 'text' => __( 'Own labs and tooling workshop.', 'dazhen-blocks' ) ),
				array( 'icon' => 'truck', 'title' => __( 'Export ready', 'dazhen-blocks' ), 'text' => __( 'FOB Shenzhen, retail packaging.', 'dazhen-blocks' ) ),
				array( 'icon' => 'shield', 'title' => __( 'After-sales', 'dazhen-blocks' ), 'text' => __( '1% spares, 12-month warranty.', 'dazhen-blocks' ) ),
			),
		) ),
		array( 'dazhen-blocks' )
	);

	$register( 's-split',
		__( 'Text + Image — two columns · 图文分栏', 'dazhen-blocks' ),
		__( 'Story on one side, image on the other, with a checklist.', 'dazhen-blocks' ),
		array( 'split', 'two column', 'two-column layout' ),
		dzbl( 'split', array(
			'eyebrow' => __( 'SECTION EYEBROW', 'dazhen-blocks' ),
			'title'   => __( 'Show the factory, the process or the product', 'dazhen-blocks' ),
			'body'    => __( 'A short paragraph that carries the argument. Keep it to three or four lines and let the checklist do the persuading.', 'dazhen-blocks' ),
			'bullets' => __( "Point one with a check icon\nPoint two\nPoint three" ),
		) ),
		array( 'dazhen-blocks' )
	);

	$register( 's-products',
		__( 'Products — live grid · 产品网格(动态)', 'dazhen-blocks' ),
		__( 'Your product catalog, always up to date.', 'dazhen-blocks' ),
		array( 'products', 'catalog', 'grid' ),
		dzbl( 'products', array(
			'eyebrow'  => __( 'PRODUCT LINES', 'dazhen-blocks' ),
			'title'    => __( 'Featured products', 'dazhen-blocks' ),
			'count'    => 6,
			'columns'  => 3,
			'ctaLabel' => __( 'View all products', 'dazhen-blocks' ),
			'ctaUrl'   => '/products/',
		) ),
		array( 'dazhen-blocks' )
	);

	$register( 's-process',
		__( 'Process — steps · 流程步骤', 'dazhen-blocks' ),
		__( 'A numbered journey — the OEM workflow or any how-it-works.', 'dazhen-blocks' ),
		array( 'process', 'steps', 'workflow' ),
		dzbl( 'process', array(
			'eyebrow' => __( 'HOW IT WORKS', 'dazhen-blocks' ),
			'title'   => __( 'From idea to shipment', 'dazhen-blocks' ),
			'items'   => array(
				array( 'title' => __( 'Requirement', 'dazhen-blocks' ), 'text' => __( 'We freeze the spec within 48 hours.', 'dazhen-blocks' ) ),
				array( 'title' => __( 'Samples', 'dazhen-blocks' ), 'text' => __( 'Functional samples in 2–3 weeks.', 'dazhen-blocks' ) ),
				array( 'title' => __( 'Production', 'dazhen-blocks' ), 'text' => __( '100% aging test before packing.', 'dazhen-blocks' ) ),
				array( 'title' => __( 'Delivery', 'dazhen-blocks' ), 'text' => __( 'FOB Shenzhen with full documents.', 'dazhen-blocks' ) ),
			),
		) ),
		array( 'dazhen-blocks' )
	);

	$register( 's-testimonials',
		__( 'Testimonials — quotes · 客户评价', 'dazhen-blocks' ),
		__( 'Client quotes with name and role.', 'dazhen-blocks' ),
		array( 'testimonials', 'quotes', 'customer testimonials' ),
		dzbl( 'testimonials', array(
			'eyebrow' => __( 'PARTNERS SAY', 'dazhen-blocks' ),
			'title'   => __( 'Honest words from early adopters', 'dazhen-blocks' ),
			'items'   => array(
				array( 'quote' => __( 'Sold out our first container in six weeks.', 'dazhen-blocks' ), 'name' => __( 'M. Weber', 'dazhen-blocks' ), 'role' => __( 'Retail chain, DE', 'dazhen-blocks' ) ),
				array( 'quote' => __( 'First sample matched the spec exactly.', 'dazhen-blocks' ), 'name' => __( 'R. Tanaka', 'dazhen-blocks' ), 'role' => __( 'E-commerce brand, JP', 'dazhen-blocks' ) ),
			),
		) ),
		array( 'dazhen-blocks' )
	);

	$register( 's-faq',
		__( 'FAQ — accordions · 常见问题', 'dazhen-blocks' ),
		__( 'Questions and answers as native accordions.', 'dazhen-blocks' ),
		array( 'faq', 'accordion', 'common questions' ),
		dzbl( 'faq', array(
			'eyebrow' => __( 'FAQ', 'dazhen-blocks' ),
			'title'   => __( 'What buyers ask most', 'dazhen-blocks' ),
			'items'   => array(
				array( 'q' => __( 'What is the MOQ?', 'dazhen-blocks' ), 'a' => __( 'Existing models from 500 units, full custom from 1,000.', 'dazhen-blocks' ) ),
				array( 'q' => __( 'Lead time?', 'dazhen-blocks' ), 'a' => __( '45 days typical for a new model, 25 days for reorders.', 'dazhen-blocks' ) ),
			),
		) ),
		array( 'dazhen-blocks' )
	);

	$register( 's-cta',
		__( 'CTA — conversion banner · 行动号召', 'dazhen-blocks' ),
		__( 'Headline, promise and the amber quote button.', 'dazhen-blocks' ),
		array( 'cta', 'quote', 'conversion' ),
		dzbl( 'cta', array(
			'title' => __( 'Send us your spec — get a quote in 24 hours', 'dazhen-blocks' ),
			'sub'   => __( 'A drawing, a link or a rough idea is enough to start.', 'dazhen-blocks' ),
		) ),
		array( 'dazhen-blocks' )
	);

	$register( 's-inquiry',
		__( 'Inquiry — lead form · 询盘表单', 'dazhen-blocks' ),
		__( 'The B2B form — submissions land in Inquiries and forward by email (Dazhen theme).', 'dazhen-blocks' ),
		array( 'inquiry', 'form', 'lead' ),
		dzbl( 'inquiry', array(
			'eyebrow' => __( 'INQUIRY', 'dazhen-blocks' ),
			'title'   => __( 'Leave your requirement', 'dazhen-blocks' ),
			'sub'     => __( 'We reply within one working day.', 'dazhen-blocks' ),
		) ),
		array( 'dazhen-blocks' )
	);
}
add_action( 'init', 'dazhen_blocks_register_patterns', 20 );
