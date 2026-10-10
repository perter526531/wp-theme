<?php
/**
 * Page layouts — full pages composed from Dazhen blocks.
 *
 * Every layout is a sequence of OUR blocks (dzb_block() serialization), so
 * anything the one-click seeder writes is exactly what a user would build
 * by hand from the inserter — and everything stays editable in the sidebar.
 *
 * Eight pages: home, about, services, solutions, contact, faq, landing,
 * starter (generic).
 *
 * @package Dazhen_Blocks
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * Section shortcuts (keep the layouts readable)
 * ------------------------------------------------------------------- */

/**
 * Serialize one of our blocks.
 */
function dzbl( string $short, array $attributes = array(), string $inner = '' ): string {
	return dzb_block( dzb_name( $short ), $attributes, $inner );
}

/* ---------------------------------------------------------------------
 * The eight pages
 * ------------------------------------------------------------------- */

/**
 * Home — hero, stats, features, split, products, cta.
 */
function dazhen_layout_home(): string {
	$out  = dzbl( 'hero', array(
		'zone'           => 'a',
		'layout'         => 'split',
		'eyebrow'        => __( 'HIGH-END ICE & HEAT TECHNOLOGY SPECIALIST', 'dazhen-blocks' ),
		'title'          => __( 'OEM / ODM drinking-water equipment, engineered in Daya Bay', 'dazhen-blocks' ),
		'sub'            => __( 'From countertop ice makers to smart pet fountains — one factory, the full cold-and-hot water chain, certified for EU and US export.', 'dazhen-blocks' ),
		'primaryLabel'   => __( 'Get a Quote', 'dazhen-blocks' ),
		'primaryProduct' => __( 'OEM / ODM program', 'dazhen-blocks' ),
		'secondaryLabel' => __( 'Browse products', 'dazhen-blocks' ),
		'secondaryUrl'   => '/products/',
	) );

	$out .= dzbl( 'stats', array(
		'zone'   => 'a',
		'pad'    => 'compact',
		'items'  => array(
			array( 'value' => '12+', 'text' => __( 'years manufacturing', 'dazhen-blocks' ) ),
			array( 'value' => '99%', 'text' => __( 'passed inspection rate', 'dazhen-blocks' ) ),
			array( 'value' => '38', 'text' => __( 'countries served', 'dazhen-blocks' ) ),
			array( 'value' => '24h', 'text' => __( 'quote turnaround', 'dazhen-blocks' ) ),
		),
	) );

	$out .= dzbl( 'features', array(
		'eyebrow' => __( 'WHY DAZHEN', 'dazhen-blocks' ),
		'title'   => __( 'Four reasons importers stay with us', 'dazhen-blocks' ),
		'sub'     => __( 'A manufacturing partner, not just a supplier.', 'dazhen-blocks' ),
		'columns' => 4,
		'items'   => array(
			array( 'icon' => 'medal', 'title' => __( 'Certified quality system', 'dazhen-blocks' ), 'text' => __( 'ISO 9001 factory with CE / RoHS / FCC product compliance handled in-house.', 'dazhen-blocks' ) ),
			array( 'icon' => 'wrench', 'title' => __( 'Real engineering team', 'dazhen-blocks' ), 'text' => __( 'Thick-film heating, compressor cooling and control boards developed on site.', 'dazhen-blocks' ) ),
			array( 'icon' => 'truck', 'title' => __( 'Export-ready logistics', 'dazhen-blocks' ), 'text' => __( 'FOB Shenzhen / Yantian, mixed-SKU containers, retail-ready packaging.', 'dazhen-blocks' ) ),
			array( 'icon' => 'shield', 'title' => __( 'After-sales you can resell', 'dazhen-blocks' ), 'text' => __( '1% spare parts per order, 12-month warranty, video-guided repairs.', 'dazhen-blocks' ) ),
		),
	) );

	$out .= dzbl( 'split', array(
		'zone'    => 'alt',
		'eyebrow' => __( 'THE FACTORY', 'dazhen-blocks' ),
		'title'   => __( 'One roof in Huizhou Daya Bay', 'dazhen-blocks' ),
		'sub'     => __( 'Injection, assembly, testing and packaging under the same roof — that is how lead times stay short and quality stays accountable.', 'dazhen-blocks' ),
		'body'    => __( 'Two SMT lines, four assembly lines and a 100%通电 aging room. You are welcome to audit us on site or by video call before the first order.', 'dazhen-blocks' ),
		'bullets' => __( "Own tooling workshop\n100% powered-on aging test\nLabel & manual in your language\nNDA-friendly R&D collaboration" ),
	) );

	$out .= dzbl( 'products', array(
		'eyebrow'  => __( 'PRODUCT LINES', 'dazhen-blocks' ),
		'title'    => __( 'Best sellers this season', 'dazhen-blocks' ),
		'sub'      => __( 'Every model supports logo printing, color customization and retail packaging.', 'dazhen-blocks' ),
		'count'    => 6,
		'columns'  => 3,
		'ctaLabel' => __( 'View all products', 'dazhen-blocks' ),
		'ctaUrl'   => '/products/',
	) );

	$out .= dzbl( 'cta', array(
		'zone'    => 'a',
		'eyebrow' => __( 'NEXT STEP', 'dazhen-blocks' ),
		'title'   => __( 'Send us your spec — get a quote in 24 hours', 'dazhen-blocks' ),
		'sub'     => __( 'A drawing, a competitor link or even a rough idea is enough to start.', 'dazhen-blocks' ),
	) );

	return $out;
}

/**
 * About — split, milestones, features, cta.
 */
function dazhen_layout_about(): string {
	$out  = dzbl( 'hero', array(
		'zone'         => 'a',
		'layout'       => 'center',
		'eyebrow'      => __( 'ABOUT DAZHEN', 'dazhen-blocks' ),
		'title'        => __( 'Built for water, grown by reputation', 'dazhen-blocks' ),
		'sub'          => __( 'From a 12-person workshop to a 200-worker export factory — a short story of the Daya Bay drinking-water specialist.', 'dazhen-blocks' ),
		'primaryLabel' => __( 'Work with us', 'dazhen-blocks' ),
		'secondaryLabel' => __( 'Contact', 'dazhen-blocks' ),
		'secondaryUrl'   => '/contact/',
	) );

	$out .= dzbl( 'split', array(
		'eyebrow' => __( 'OUR STORY', 'dazhen-blocks' ),
		'title'   => __( 'Twelve years, one focus', 'dazhen-blocks' ),
		'body'    => __( 'Dazhen was founded in 2013 around a single belief: drinking-water equipment should be engineered, not assembled. Today our ice makers, water dispensers and smart pet fountains ship to 38 countries under our partners\' brands.', 'dazhen-blocks' ),
	) );

	$out .= dzbl( 'milestones', array(
		'zone'    => 'alt',
		'eyebrow' => __( 'MILESTONES', 'dazhen-blocks' ),
		'title'   => __( 'The road so far', 'dazhen-blocks' ),
		'items'   => array(
			array( 'year' => '2013', 'text' => __( 'Founded in Huizhou as a dispenser assembly workshop.', 'dazhen-blocks' ) ),
			array( 'year' => '2016', 'text' => __( 'First own-tooling ice maker line; ISO 9001 certified.', 'dazhen-blocks' ) ),
			array( 'year' => '2019', 'text' => __( 'Thick-film heating lab opens; EU export begins.', 'dazhen-blocks' ) ),
			array( 'year' => '2022', 'text' => __( 'Smart pet fountain series ships 400k units in 12 months.', 'dazhen-blocks' ) ),
			array( 'year' => '2025', 'text' => __( 'New Daya Bay plant: 2 SMT + 4 assembly lines.', 'dazhen-blocks' ) ),
		),
	) );

	$out .= dzbl( 'features', array(
		'eyebrow' => __( 'HOW WE WORK', 'dazhen-blocks' ),
		'title'   => __( 'What a partner can expect', 'dazhen-blocks' ),
		'columns' => 3,
		'items'   => array(
			array( 'icon' => 'cube', 'title' => __( 'One window', 'dazhen-blocks' ), 'text' => __( 'A bilingual engineer-owner handles your account from RFQ to after-sales.', 'dazhen-blocks' ) ),
			array( 'icon' => 'search', 'title' => __( 'Documented everything', 'dazhen-blocks' ), 'text' => __( 'Spec sheets, test reports, drawings and inspection photos shared by default.', 'dazhen-blocks' ) ),
			array( 'icon' => 'shield', 'title' => __( 'Long view', 'dazhen-blocks' ), 'text' => __( 'We plan tooling and spare parts two product generations ahead.', 'dazhen-blocks' ) ),
		),
	) );

	$out .= dzbl( 'cta', array(
		'zone'  => 'a',
		'title' => __( 'Come audit the factory', 'dazhen-blocks' ),
		'sub'   => __( 'On site in Daya Bay or by live video call.', 'dazhen-blocks' ),
	) );

	return $out;
}

/**
 * Services — hero, stats, process, faq, inquiry.
 */
function dazhen_layout_services(): string {
	$out  = dzbl( 'hero', array(
		'zone'         => 'a',
		'layout'       => 'center',
		'eyebrow'      => __( 'OEM / ODM SERVICES', 'dazhen-blocks' ),
		'title'        => __( 'Your brand, our production line', 'dazhen-blocks' ),
		'sub'          => __( 'From a drawing on a napkin to a container on the water — seven steps, one accountable factory.', 'dazhen-blocks' ),
		'primaryLabel' => __( 'Start an OEM project', 'dazhen-blocks' ),
		'primaryProduct' => __( 'OEM / ODM program', 'dazhen-blocks' ),
	) );

	$out .= dzbl( 'stats', array(
		'zone'  => 'a',
		'pad'   => 'compact',
		'items' => array(
			array( 'value' => '7', 'text' => __( 'steps from idea to shipment', 'dazhen-blocks' ) ),
			array( 'value' => '45d', 'text' => __( 'typical new-model lead time', 'dazhen-blocks' ) ),
			array( 'value' => '300', 'text' => __( 'MOQ for custom color', 'dazhen-blocks' ) ),
		),
	) );

	$out .= dzbl( 'process', array(
		'eyebrow' => __( 'THE JOURNEY', 'dazhen-blocks' ),
		'title'   => __( 'How an OEM order runs', 'dazhen-blocks' ),
		'sub'     => __( 'You always know which step your project is on.', 'dazhen-blocks' ),
		'items'   => array(
			array( 'title' => __( 'Requirement & spec freeze', 'dazhen-blocks' ), 'text' => __( 'We turn your idea or reference into a frozen spec sheet within 48 hours.', 'dazhen-blocks' ) ),
			array( 'title' => __( 'Proposal & costing', 'dazhen-blocks' ), 'text' => __( 'Transparent BOM-level quote: unit price, tooling, certifications, lead time.', 'dazhen-blocks' ) ),
			array( 'title' => __( 'Engineering & samples', 'dazhen-blocks' ), 'text' => __( 'First functional samples in 2–3 weeks, couriered with test reports.', 'dazhen-blocks' ) ),
			array( 'title' => __( 'Certification', 'dazhen-blocks' ), 'text' => __( 'CE / RoHS / FCC / UKCA handled by our compliance team.', 'dazhen-blocks' ) ),
			array( 'title' => __( 'Tooling & pilot run', 'dazhen-blocks' ), 'text' => __( 'Pilot batch of 50–100 units for your market test.', 'dazhen-blocks' ) ),
			array( 'title' => __( 'Mass production', 'dazhen-blocks' ), 'text' => __( '100% powered-on aging, AQL 1.0 final inspection, photo documentation.', 'dazhen-blocks' ) ),
			array( 'title' => __( 'Shipment & after-sales', 'dazhen-blocks' ), 'text' => __( 'FOB / DDP options, 1% free spare parts, 12-month warranty support.', 'dazhen-blocks' ) ),
		),
	) );

	$out .= dzbl( 'faq', array(
		'zone'    => 'alt',
		'eyebrow' => __( 'FAQ', 'dazhen-blocks' ),
		'title'   => __( 'OEM questions we hear most', 'dazhen-blocks' ),
		'items'   => array(
			array( 'q' => __( 'What is the minimum order?', 'dazhen-blocks' ), 'a' => __( 'Existing models: 500 units (mixed models OK). Full custom: from 1,000 units or tooling amortization.', 'dazhen-blocks' ) ),
			array( 'q' => __( 'Can you do our language?', 'dazhen-blocks' ), 'a' => __( 'Yes — labels, manuals and packaging in EN / DE / FR / ES / RU / JP and more.', 'dazhen-blocks' ) ),
			array( 'q' => __( 'Who owns the tooling?', 'dazhen-blocks' ), 'a' => __( 'You do, once paid. We store and maintain it for your orders only.', 'dazhen-blocks' ) ),
			array( 'q' => __( 'How do you handle after-sales?', 'dazhen-blocks' ), 'a' => __( '1% spare parts per shipment, 12-month warranty, replacement parts couriered within 72h.', 'dazhen-blocks' ) ),
		),
	) );

	$out .= dzbl( 'inquiry', array(
		'eyebrow' => __( 'START A PROJECT', 'dazhen-blocks' ),
		'title'   => __( 'Tell us what you want to build', 'dazhen-blocks' ),
		'sub'     => __( 'Requirement, target price, timeline — the more you share, the sharper our first quote.', 'dazhen-blocks' ),
		'product' => __( 'OEM / ODM program', 'dazhen-blocks' ),
		'message' => __( 'Hello Dazhen, I would like to discuss an OEM project:', 'dazhen-blocks' ),
	) );

	return $out;
}

/**
 * Solutions — hero, features, products, cta.
 */
function dazhen_layout_solutions(): string {
	$out  = dzbl( 'hero', array(
		'zone'   => 'a',
		'layout' => 'center',
		'eyebrow' => __( 'SOLUTIONS', 'dazhen-blocks' ),
		'title'  => __( 'Water equipment for every place people drink', 'dazhen-blocks' ),
		'sub'    => __( 'Home, office, education, hospitality — pick the scenario, we fit the models.', 'dazhen-blocks' ),
	) );

	$out .= dzbl( 'features', array(
		'eyebrow' => __( 'SCENARIOS', 'dazhen-blocks' ),
		'title'   => __( 'Four spaces, four answers', 'dazhen-blocks' ),
		'columns' => 4,
		'items'   => array(
			array( 'icon' => 'drop', 'title' => __( 'Home', 'dazhen-blocks' ), 'text' => __( 'Countertop dispensers and ice makers sized for family kitchens.', 'dazhen-blocks' ) ),
			array( 'icon' => 'cube', 'title' => __( 'Office', 'dazhen-blocks' ), 'text' => __( 'High-capacity floor-standing units with filter reminders.', 'dazhen-blocks' ) ),
			array( 'icon' => 'sparkle', 'title' => __( 'Education', 'dazhen-blocks' ), 'text' => __( 'Anti-scald safety locks and vandal-resistant taps.', 'dazhen-blocks' ) ),
			array( 'icon' => 'medal', 'title' => __( 'Hospitality', 'dazhen-blocks' ), 'text' => __( 'Quiet hotel-room kettles and corridor ice stations.', 'dazhen-blocks' ) ),
		),
	) );

	$out .= dzbl( 'products', array(
		'zone'    => 'alt',
		'eyebrow' => __( 'MATCHING MODELS', 'dazhen-blocks' ),
		'title'   => __( 'Popular picks by scenario', 'dazhen-blocks' ),
		'count'   => 6,
		'columns' => 3,
	) );

	$out .= dzbl( 'cta', array(
		'zone'  => 'a',
		'title' => __( 'Not sure which model fits?', 'dazhen-blocks' ),
		'sub'   => __( 'Describe the installation space — we will shortlist for you in a day.', 'dazhen-blocks' ),
	) );

	return $out;
}

/**
 * Contact — hero, contact, inquiry.
 */
function dazhen_layout_contact(): string {
	$out  = dzbl( 'hero', array(
		'zone'   => 'a',
		'layout' => 'center',
		'pad'    => 'compact',
		'eyebrow' => __( 'CONTACT', 'dazhen-blocks' ),
		'title'  => __( 'Talk to the factory directly', 'dazhen-blocks' ),
		'sub'    => __( 'No agents, no trading company — your message reaches the engineer-owners.', 'dazhen-blocks' ),
	) );

	$out .= dzbl( 'contact', array(
		'eyebrow' => __( 'REACH US', 'dazhen-blocks' ),
		'title'   => __( 'Three ways, one team', 'dazhen-blocks' ),
		'columns' => 3,
		'items'   => array(
			array( 'icon' => 'phone', 'title' => __( 'Phone / WhatsApp', 'dazhen-blocks' ), 'text' => __( '+86 752 5555 018\nMon–Sat 9:00–19:00 (GMT+8)', 'dazhen-blocks' ) ),
			array( 'icon' => 'mail', 'title' => __( 'Email', 'dazhen-blocks' ), 'text' => __( 'sales@dazhen.example\nRFQs answered within 24h', 'dazhen-blocks' ) ),
			array( 'icon' => 'pin', 'title' => __( 'Factory', 'dazhen-blocks' ), 'text' => __( 'No. 28 Longshan Road, Daya Bay\nHuizhou, Guangdong, China', 'dazhen-blocks' ) ),
		),
	) );

	$out .= dzbl( 'inquiry', array(
		'zone' => 'alt',
		'eyebrow' => __( 'INQUIRY', 'dazhen-blocks' ),
		'title'   => __( 'Leave your requirement', 'dazhen-blocks' ),
		'sub'    => __( 'We reply within one working day with next steps.', 'dazhen-blocks' ),
	) );

	return $out;
}

/**
 * FAQ page — hero + big faq block + cta.
 */
function dazhen_layout_faq(): string {
	$out  = dzbl( 'hero', array(
		'zone'   => 'a',
		'layout' => 'center',
		'pad'    => 'compact',
		'eyebrow' => __( 'HELP CENTER', 'dazhen-blocks' ),
		'title'  => __( 'Frequently asked questions', 'dazhen-blocks' ),
		'sub'    => __( 'Ordering, quality, logistics and after-sales — the honest short answers.', 'dazhen-blocks' ),
	) );

	$out .= dzbl( 'faq', array(
		'eyebrow' => __( 'ALL TOPICS', 'dazhen-blocks' ),
		'title'   => __( 'Everything buyers ask', 'dazhen-blocks' ),
		'items'   => array(
			array( 'q' => __( 'Do you support small trial orders?', 'dazhen-blocks' ), 'a' => __( 'Yes — 100–300 unit trial orders on existing models so your market can test before committing.', 'dazhen-blocks' ) ),
			array( 'q' => __( 'What certifications can you provide?', 'dazhen-blocks' ), 'a' => __( 'CE, RoHS, FCC and UKCA on most models; food-contact material reports on request.', 'dazhen-blocks' ) ),
			array( 'q' => __( 'How are goods packed?', 'dazhen-blocks' ), 'a' => __( 'Retail-ready color boxes or neutral brown boxes, palletized for container loading.', 'dazhen-blocks' ) ),
			array( 'q' => __( 'Which incoterms do you ship on?', 'dazhen-blocks' ), 'a' => __( 'FOB Shenzhen / Yantian as default; EXW, CIF and DDP available.', 'dazhen-blocks' ) ),
			array( 'q' => __( 'Can we visit the factory?', 'dazhen-blocks' ), 'a' => __( 'Welcome any working day — we arrange airport pickup from Shenzhen Bao\'an.', 'dazhen-blocks' ) ),
		),
	) );

	$out .= dzbl( 'cta', array(
		'zone'  => 'a',
		'title' => __( 'Question not answered?', 'dazhen-blocks' ),
		'sub'   => __( 'Ask directly — the engineer who builds the product will reply.', 'dazhen-blocks' ),
	) );

	return $out;
}

/**
 * Landing page — campaign-oriented: hero, features, testimonials, faq, cta.
 */
function dazhen_layout_landing(): string {
	$out  = dzbl( 'hero', array(
		'zone'         => 'a',
		'layout'       => 'center',
		'pad'          => 'spacious',
		'eyebrow'      => __( 'NEW SERIES 2026', 'dazhen-blocks' ),
		'title'        => __( 'The smart pet fountain your list will reorder', 'dazhen-blocks' ),
		'sub'          => __( 'Wireless pump, 2.5L, app-free simplicity — the model behind 400k repeat orders. Launch it under your brand in 45 days.', 'dazhen-blocks' ),
		'primaryLabel' => __( 'Get the launch quote', 'dazhen-blocks' ),
		'primaryProduct' => __( 'Smart pet fountain 2026', 'dazhen-blocks' ),
	) );

	$out .= dzbl( 'features', array(
		'eyebrow' => __( 'WHY THIS MODEL', 'dazhen-blocks' ),
		'title'   => __( 'Built to be re-ordered', 'dazhen-blocks' ),
		'columns' => 3,
		'items'   => array(
			array( 'icon' => 'drop', 'title' => __( 'Truly quiet', 'dazhen-blocks' ), 'text' => __( '< 30dB wireless pump — the #1 review driver in this category.', 'dazhen-blocks' ) ),
			array( 'icon' => 'shield', 'title' => __( 'Zero-return filter', 'dazhen-blocks' ), 'text' => __( 'Triple-stage filter with 30-day cartridge subscription potential.', 'dazhen-blocks' ) ),
			array( 'icon' => 'bolt', 'title' => __( 'Ships flat', 'dazhen-blocks' ), 'text' => __( 'Flat-pack box: 40% more units per container, lower landed cost.', 'dazhen-blocks' ) ),
		),
	) );

	$out .= dzbl( 'testimonials', array(
		'eyebrow' => __( 'PARTNERS SAY', 'dazhen-blocks' ),
		'title'   => __( 'Early adopters, honest words', 'dazhen-blocks' ),
		'columns' => 2,
		'items'   => array(
			array( 'quote' => __( 'Sold out our first container in six weeks. The reorder was a no-brainer.', 'dazhen-blocks' ), 'name' => __( 'M. Weber', 'dazhen-blocks' ), 'role' => __( 'Pet retail chain, DE', 'dazhen-blocks' ) ),
			array( 'quote' => __( 'The only supplier whose first sample matched the spec sheet exactly.', 'dazhen-blocks' ), 'name' => __( 'R. Tanaka', 'dazhen-blocks' ), 'role' => __( 'E-commerce brand, JP', 'dazhen-blocks' ) ),
		),
	) );

	$out .= dzbl( 'cta', array(
		'zone'  => 'a',
		'title' => __( 'Launch window: Q2 production slots', 'dazhen-blocks' ),
		'sub'   => __( 'Reserve a pilot run before the season.', 'dazhen-blocks' ),
	) );

	return $out;
}

/**
 * Starter — a clean generic beginning for any new page.
 */
function dazhen_layout_generic(): string {
	$out  = dzbl( 'hero', array(
		'zone'   => 'a',
		'layout' => 'center',
		'pad'    => 'compact',
		'eyebrow' => __( 'PAGE EYEBROW', 'dazhen-blocks' ),
		'title'  => __( 'Your page title', 'dazhen-blocks' ),
		'sub'    => __( 'One line that says what this page promises the visitor.', 'dazhen-blocks' ),
	) );

	$out .= dzbl( 'split', array(
		'eyebrow' => __( 'SECTION EYEBROW', 'dazhen-blocks' ),
		'title'   => __( 'A two-column opener', 'dazhen-blocks' ),
		'body'    => __( 'Replace this text with your story, spec or argument — then use the sidebar to swap the image, flip the columns or restyle the background.', 'dazhen-blocks' ),
		'bullets' => __( "First checkable point\nSecond point\nThird point" ),
	) );

	$out .= dzbl( 'features', array(
		'zone'    => 'alt',
		'eyebrow' => __( 'HIGHLIGHTS', 'dazhen-blocks' ),
		'title'   => __( 'Three things worth knowing', 'dazhen-blocks' ),
		'columns' => 3,
		'items'   => array(
			array( 'icon' => 'cube', 'title' => __( 'Point one', 'dazhen-blocks' ), 'text' => __( 'Keep each card to two short lines — scannability wins.', 'dazhen-blocks' ) ),
			array( 'icon' => 'bolt', 'title' => __( 'Point two', 'dazhen-blocks' ), 'text' => __( 'Pick a different icon per card from the sidebar.', 'dazhen-blocks' ) ),
			array( 'icon' => 'check', 'title' => __( 'Point three', 'dazhen-blocks' ), 'text' => __( 'Add or remove cards with the repeater control.', 'dazhen-blocks' ) ),
		),
	) );

	$out .= dzbl( 'cta', array(
		'zone'  => 'a',
		'title' => __( 'Close with one clear action', 'dazhen-blocks' ),
		'sub'   => __( 'The amber button opens the inquiry popup by default.', 'dazhen-blocks' ),
	) );

	return $out;
}

/* ---------------------------------------------------------------------
 * Registry + matching (consumed by the seeder)
 * ------------------------------------------------------------------- */

/**
 * The layout registry.
 *
 * @return array<string, array{label:string, desc:string}>
 */
function dazhen_block_layouts(): array {
	return array(
		'home'      => array(
			'label' => __( 'Home — hero, stats, features, factory, products, CTA', 'dazhen-blocks' ),
			'desc'  => __( 'The full homepage for a B2B manufacturer.', 'dazhen-blocks' ),
		),
		'about'     => array(
			'label' => __( 'About — story, milestones, principles', 'dazhen-blocks' ),
			'desc'  => __( 'Company story with a year timeline.', 'dazhen-blocks' ),
		),
		'services'  => array(
			'label' => __( 'Services — OEM journey, FAQ, inquiry form', 'dazhen-blocks' ),
			'desc'  => __( 'The OEM/ODM pitch with a 7-step process.', 'dazhen-blocks' ),
		),
		'solutions' => array(
			'label' => __( 'Solutions — scenarios + product picks', 'dazhen-blocks' ),
			'desc'  => __( 'Scenario cards plus a live product grid.', 'dazhen-blocks' ),
		),
		'contact'   => array(
			'label' => __( 'Contact — info cards + inquiry form', 'dazhen-blocks' ),
			'desc'  => __( 'How to reach the factory, then the form.', 'dazhen-blocks' ),
		),
		'faq'       => array(
			'label' => __( 'FAQ — questions page', 'dazhen-blocks' ),
			'desc'  => __( 'A standalone frequently-asked-questions page.', 'dazhen-blocks' ),
		),
		'landing'   => array(
			'label' => __( 'Landing — campaign page', 'dazhen-blocks' ),
			'desc'   => __( 'Product-launch page with testimonials.', 'dazhen-blocks' ),
		),
		'starter'   => array(
			'label' => __( 'Starter — clean generic page', 'dazhen-blocks' ),
			'desc'  => __( 'Hero, split, three cards and a CTA to begin any page.', 'dazhen-blocks' ),
		),
	);
}

/**
 * Get one layout's block markup (or null).
 */
function dazhen_get_block_layout( string $slug ): ?string {
	switch ( sanitize_key( $slug ) ) {
		case 'home':
			return dazhen_layout_home();
		case 'about':
			return dazhen_layout_about();
		case 'services':
			return dazhen_layout_services();
		case 'solutions':
			return dazhen_layout_solutions();
		case 'contact':
			return dazhen_layout_contact();
		case 'faq':
			return dazhen_layout_faq();
		case 'landing':
			return dazhen_layout_landing();
		case 'starter':
		case 'generic':
			return dazhen_layout_generic();
	}
	return null;
}

/**
 * Guess the best layout for a page (front page / template / slug).
 */
function dazhen_match_page_layout( $post ): string {
	$post = get_post( $post );
	if ( ! $post instanceof WP_Post ) {
		return 'starter';
	}

	if ( (int) get_option( 'page_on_front' ) === $post->ID ) {
		return 'home';
	}

	$template = (string) get_post_meta( $post->ID, '_wp_page_template', true );
	$slug_map = array(
		'page-templates/about.php'            => 'about',
		'page-templates/services.php'         => 'services',
		'page-templates/solutions-overview.php' => 'solutions',
		'page-templates/contact.php'          => 'contact',
	);

	if ( isset( $slug_map[ $template ] ) ) {
		return $slug_map[ $template ];
	}

	switch ( $post->post_name ) {
		case 'about-us':
		case 'about':
			return 'about';
		case 'services':
			return 'services';
		case 'solutions':
			return 'solutions';
		case 'contact':
		case 'contact-us':
			return 'contact';
		case 'faq':
			return 'faq';
		case 'home':
			return 'home';
	}

	return 'starter';
}
