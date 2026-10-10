<?php
/**
 * Page layouts — the Kabel site, composed entirely from our own blocks.
 *
 * Every layout is a sequence of KABEL BLOCKS (kbl_block() serialization), so
 * anything the one-click seeder writes is exactly what a user would build by
 * hand from the inserter — and everything stays editable in the sidebar.
 *
 * Layouts (page slugs, matching the original site map):
 *   home, about, eco-friendly, designer, products, morandi-colors,
 *   van-gogh-color, macaron-colors, museum-collection-colors, projects,
 *   project-procurement, customize, distributor, blog, contact, faq
 *   plus two generic ones: landing (campaign) and starter (blank page).
 *
 * All copy is taken verbatim from the Kabel content inventory
 * (docs/kabel/site-structure.json — the single source of truth).
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * Sample assets — file name → media library ID
 * ------------------------------------------------------------------- */

/**
 * Import mode for sample assets.
 *
 * OFF (default): kbl_asset_id() only reads the persistent map — one cached
 * option lookup, safe to call on every request (patterns register at init).
 *
 * ON (set by kabel_seed_page() around markup generation): missing assets are
 * resolved for real — reuse an existing attachment by its original upload
 * name (works on the client's current site), else sideload the compressed
 * webp from the Kabel theme's sample-assets/ folder — then remember the ID.
 *
 * External callers that build layout markup themselves (site bootstrap etc.)
 * should wrap their kabel_get_block_layout() call the same way.
 *
 * @param bool|null $on True/false to switch, null to just query.
 * @return bool Current mode.
 */
function kbl_asset_import_mode( $on = null ): bool {
        static $mode = false;
        if ( null !== $on ) {
                $mode = (bool) $on;
        }
        return $mode;
}

/**
 * The sample-assets manifest (orig upload path → shipped file name), indexed
 * by file name. Lives in the Kabel theme; parsed once per request.
 *
 * @return array<string, array{orig:string,file:string}>
 */
function kbl_asset_manifest(): array {
        static $manifest = null;
        if ( null !== $manifest ) {
                return $manifest;
        }

        $manifest = array();
        foreach ( kbl_asset_dir_candidates() as $dir ) {
                $file = $dir . 'manifest.json';
                if ( is_readable( $file ) ) {
                        $entries = wp_json_file_decode( $file, array( 'associative' => true ) );
                        if ( is_array( $entries ) ) {
                                foreach ( $entries as $entry ) {
                                        if ( ! empty( $entry['file'] ) && ! empty( $entry['orig'] ) ) {
                                                $manifest[ (string) $entry['file'] ] = array(
                                                        'orig' => (string) $entry['orig'],
                                                        'file' => (string) $entry['file'],
                                                );
                                        }
                                }
                        }
                        break;
                }
        }

        return $manifest;
}

/**
 * Candidate directories for the Kabel theme's sample-assets folder.
 *
 * The theme ships the compressed images + manifest; both the ACTIVE Kabel
 * theme and an INSTALLED-but-inactive Kabel theme (e.g. the client's current
 * site still running another theme) expose them.
 *
 * @return string[]
 */
function kbl_asset_dir_candidates(): array {
        $dirs = array();

        if ( kabel_blocks_theme_active() ) {
                $dirs[] = get_theme_file_path( 'sample-assets/' );
        }

        $root = get_theme_root( 'kabel' );
        if ( is_string( $root ) && '' !== $root ) {
                $dirs[] = trailingslashit( $root ) . 'kabel/sample-assets/';
        }

        return $dirs;
}

/**
 * The first readable sample-assets directory (or '').
 */
function kbl_asset_dir(): string {
        foreach ( kbl_asset_dir_candidates() as $dir ) {
                if ( is_dir( $dir ) && is_readable( $dir ) ) {
                        return $dir;
                }
        }
        return '';
}

/**
 * Normalize an asset reference to a safe file name.
 */
function kbl_asset_file_name( $file ): string {
        $file = wp_basename( trim( (string) $file ) );
        if ( ! preg_match( '/^[A-Za-z0-9._-]+$/', $file ) ) {
                return '';
        }
        return $file;
}

/**
 * The persistent file → attachment ID map (populated by import mode).
 *
 * @return array<string,int>
 */
function kbl_asset_map(): array {
        $map = get_option( 'kbl_asset_map', array() );
        return is_array( $map ) ? $map : array();
}

/**
 * Remember a resolved asset ID.
 */
function kbl_asset_remember( string $file, int $id ): void {
        if ( $id < 1 ) {
                return;
        }
        $map         = kbl_asset_map();
        $map[ $file ] = $id;
        update_option( 'kbl_asset_map', $map, true );
}

/**
 * Find an attachment whose _wp_attached_file ends in "/<tail>".
 *
 * Matches both freshly sideloaded assets (shipped file name) and the
 * client's existing uploads (original file name) — whichever tail is given.
 */
function kbl_asset_find_attachment( string $tail ): int {
        global $wpdb;

        $tail = kbl_asset_file_name( $tail );
        if ( '' === $tail ) {
                return 0;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        $id = (int) $wpdb->get_var(
                $wpdb->prepare(
                        "SELECT post_id FROM {$wpdb->postmeta}
                         WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s
                         ORDER BY post_id ASC LIMIT 1",
                        '%' . $wpdb->esc_like( '/' . $tail )
                )
        );

        if ( $id < 1 ) {
                return 0;
        }

        $post = get_post( $id );
        return ( $post && 'attachment' === $post->post_type ) ? $id : 0;
}

/**
 * Sideload one sample asset into the media library (import mode only).
 */
function kbl_asset_sideload( string $file ): int {
        if ( ! current_user_can( 'upload_files' ) ) {
                return 0;
        }

        $src = kbl_asset_dir() . $file;
        if ( '' === $src || ! is_readable( $src ) ) {
                return 0;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        /* media_handle_sideload() MOVES the temp file — always copy first. */
        $tmp = wp_tempnam( $file );
        if ( ! $tmp || ! copy( $src, $tmp ) ) {
                if ( $tmp ) {
                        @unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
                }
                return 0;
        }

        /*
         * Trim intermediate sizes while sideloading (pre-compressed webp
         * originals + a 30s shared-hosting budget = keep thumbnail only).
         */
        $kbl_trim = static function ( $sizes ) {
                if ( is_array( $sizes ) && isset( $sizes['thumbnail'] ) ) {
                        return array( 'thumbnail' => $sizes['thumbnail'] );
                }
                return $sizes;
        };
        add_filter( 'intermediate_image_sizes_advanced', $kbl_trim );
        $id = media_handle_sideload(
                array( 'name' => $file, 'tmp_name' => $tmp ),
                0,
                kbl_asset_title( $file ),
                array()
        );
        remove_filter( 'intermediate_image_sizes_advanced', $kbl_trim );

        if ( is_wp_error( $id ) ) {
                @unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
                return 0;
        }

        return (int) $id;
}

/**
 * A human attachment title from a file name ("nobel.webp" → "Nobel").
 */
function kbl_asset_title( string $file ): string {
        $name = pathinfo( $file, PATHINFO_FILENAME );
        $name = str_replace( array( '-', '_', '.' ), ' ', $name );
        $name = trim( preg_replace( '/\s+/', ' ', $name ) );
        return '' !== $name ? ucfirst( $name ) : $file;
}

/**
 * Resolve a sample-asset file name to a media library attachment ID.
 *
 * Accepts either a shipped file name from sample-assets/manifest.json
 * (preferred — that is what the layouts use) or an original uploads path.
 * Returns 0 when the image is not available; blocks degrade gracefully.
 */
function kbl_asset_id( $file ): int {
        static $resolved = array();

        $file = kbl_asset_file_name( $file );
        if ( '' === $file ) {
                return 0;
        }

        /* Positive results only: a 0 seen before import mode must not stick. */
        if ( isset( $resolved[ $file ] ) && $resolved[ $file ] > 0 ) {
                return $resolved[ $file ];
        }

        $id = 0;
        $map = kbl_asset_map();

        if ( ! empty( $map[ $file ] ) ) {
                $id = (int) $map[ $file ];
        } elseif ( kbl_asset_import_mode() ) {
                $entry = kbl_asset_manifest()[ $file ] ?? null;

                /* 1) Already uploaded under the shipped name. */
                $id = kbl_asset_find_attachment( $file );

                /* 2) The client's existing site: uploaded under the ORIGINAL name. */
                if ( $id < 1 && $entry ) {
                        $orig = kbl_asset_file_name( $entry['orig'] );
                        if ( '' !== $orig && $orig !== $file ) {
                                $id = kbl_asset_find_attachment( $orig );
                        }
                }

                /* 3) Sideload the compressed webp shipped with the theme. */
                if ( $id < 1 && $entry ) {
                        $id = kbl_asset_sideload( $file );
                }

                if ( $id > 0 ) {
                        kbl_asset_remember( $file, $id );
                }
        }

        if ( $id > 0 ) {
                $resolved[ $file ] = $id;
        }

        return $id;
}

/**
 * Resolve a list of sample-asset file names (keeps order).
 *
 * @param string[] $files
 * @return int[]
 */
function kbl_asset_ids( array $files ): array {
        $ids = array();
        foreach ( $files as $file ) {
                $ids[] = kbl_asset_id( $file );
        }
        return $ids;
}

/* ---------------------------------------------------------------------
 * Section shortcut (keeps the layouts readable)
 * ------------------------------------------------------------------- */

/**
 * Serialize one of our blocks — LEGACY v1/v2 output.
 *
 * Since v3 the layouts call the kbl/* emitter (inc/emitter.php) through the
 * same kbl_b() signature. This legacy serializer is kept ONLY for the
 * upgrader, which compares a page's stored markup against freshly generated
 * legacy markup to decide whether it is safe to re-emit the page with v3
 * blocks (byte-equal = untouched by the user).
 */
function kbl_b_legacy( string $short, array $attributes = array(), string $inner = '' ): string {
        return kbl_block( kbl_name( $short ), $attributes, $inner );
}

/* ---------------------------------------------------------------------
 * The Kabel pages
 * ------------------------------------------------------------------- */

/**
 * Home — hero, Nobel tribute, purifying tech, product families, features, inquiry.
 */
function kabel_layout_home(): string {
        $out  = kbl_b( 'hero', array(
                'zone'          => 'a',
                'layout'        => 'center',
                'pad'           => 'spacious',
                'eyebrow'       => __( 'Global high-end decorative paint brand', 'kabel-blocks' ),
                'title'         => __( 'Kabel Art Paint', 'kabel-blocks' ),
                'sub'           => '<em>from Druento</em>',
                'bgImage'       => kbl_asset_id( 'brand-hq.webp' ),
                'primaryLabel'  => __( 'CONSULT NOW', 'kabel-blocks' ),
                'secondaryLabel' => __( 'Explore Paint Series', 'kabel-blocks' ),
                'secondaryUrl'  => '/products/',
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'KABEL HONORARY CHIEF SCIENTIST', 'kabel-blocks' ),
                'title'   => __( 'Collaborative Tribute to Nobel Laureate Professor James Fraser Stoddart', 'kabel-blocks' ),
                'body'    => __( 'Centered on material technology and design aesthetics, we continuously explore the possibilities of wall art to create spatial experiences that combine performance, beauty, and health benefits.', 'kabel-blocks' ),
                'image'   => kbl_asset_id( '2efa8714-324e-445b-8d3e-4f68e917cc8a2-1.webp' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'alt',
                'reverse' => true,
                'eyebrow' => __( 'FORMALDEHYDE → WATER', 'kabel-blocks' ),
                'title'   => __( 'Create a healthier and more comfortable living environment', 'kabel-blocks' ),
                'body'    => __( 'KABEL has developed an innovative formaldehyde-purifying technology. Independently tested, it achieves a formaldehyde purification rate of up to 94.1% and a long-lasting purification efficiency of 82.1%. Beyond enhancing interior aesthetics, it helps improve indoor air quality and supports healthier, more comfortable living environments.', 'kabel-blocks' ),
                'image'   => kbl_asset_id( 'eco-friendly-1920850-5.5-.webp' ),
        ) );

        $out .= kbl_b( 'products', array(
                'eyebrow' => __( 'PRODUCTS', 'kabel-blocks' ),
                'title'   => __( 'Transform every wall into a timeless work of art', 'kabel-blocks' ),
                'count'   => 6,
                'columns' => 3,
        ) );

        $out .= kbl_b( 'features', array(
                'zone'    => 'alt',
                'eyebrow' => __( 'PRODUCT FEATURES', 'kabel-blocks' ),
                'columns' => 3,
                'items'   => array(
                        array(
                                'icon'  => 'sparkle',
                                'title' => __( 'Craftsmanship effect', 'kabel-blocks' ),
                                'text'  => __( 'Refined through six artisanal techniques, the surface reveals a soft, cashmere-like touch — subtle, smooth, and quietly luxurious.', 'kabel-blocks' ),
                        ),
                        array(
                                'icon'  => 'cube',
                                'title' => __( 'Wall and floor integration', 'kabel-blocks' ),
                                'text'  => __( 'The original cement texture, with the wall surface molded as a single piece, presents a simple yet profound sense of space.', 'kabel-blocks' ),
                        ),
                        array(
                                'icon'  => 'drop',
                                'title' => __( 'Waterproof performance', 'kabel-blocks' ),
                                'text'  => __( 'Exceptional resistance to moisture and stains, preserving the wall’s pristine elegance over time.', 'kabel-blocks' ),
                        ),
                ),
        ) );

        $out .= kbl_b( 'inquiry', array(
                'eyebrow' => __( 'CONSULT NOW', 'kabel-blocks' ),
                'title'   => __( 'CONTACT US FOR ANY QUESTIONS', 'kabel-blocks' ),
                'message' => __( 'Hello Kabel, I would like to consult about:', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * About — heritage, core technology, partners, Nobel tribute, architect reasons.
 */
function kabel_layout_about(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'eyebrow' => __( 'ABOUT KABEL', 'kabel-blocks' ),
                'title'   => __( 'Born in the heart of Italy’s decorative coating capital.', 'kabel-blocks' ),
                'sub'     => __( 'Over a century of heritage, blending Italian aesthetics with modern innovation.', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'OUR HERITAGE', 'kabel-blocks' ),
                'title'   => __( 'World-class high-end art paint brand', 'kabel-blocks' ),
                'sub'     => __( 'Transform every wall into a timeless work of art – KABEL Design Philosophy', 'kabel-blocks' ),
                'body'    => __( 'KABEL originates from Druento, Torino — the heart of Italian decorative coating artistry. With over a century of heritage, KABEL blends Italian aesthetics with modern innovation, continuously redefining the possibilities of wall finishes. From Europe to the world, KABEL has become a name in premium decorative coatings.', 'kabel-blocks' ),
                'image'   => kbl_asset_id( 'stone-texture-1.webp' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'alt',
                'reverse' => true,
                'eyebrow' => __( 'CORE TECHNOLOGY', 'kabel-blocks' ),
                'title'   => __( 'Formaldehyde → Water', 'kabel-blocks' ),
                'body'    => __( 'KABEL’s independently developed formaldehyde degradation technology doesn’t just cover — it actively purifies. Our paint film effectively captures free formaldehyde from the air and decomposes it into harmless substances and water molecules — not just masking, but truly eliminating. This means healthier indoor environments for every space your project creates: hotels, hospitals, residences, and commercial buildings.', 'kabel-blocks' ),
                'image'   => kbl_asset_id( 'tech-formaldehyde-1.webp' ),
        ) );

        $out .= kbl_b( 'logos', array(
                'zone'    => 'b',
                'eyebrow' => __( 'GREAT MATERIALS', 'kabel-blocks' ),
                'sub'     => __( 'We partner with BASF, Dow Chemical, and Wanhua Chemical — three globals in the chemical industry — to safely convert formaldehyde into water using dual purification technology, thereby creating healthier indoor environments.', 'kabel-blocks' ),
                'ids'     => kbl_asset_ids( array(
                        '5dccc95a-3135-4baa-8581-3a40e7cdb7e2.webp',
                        '06b15871-66d3-40be-8853-870e74df4dae.webp',
                        '300912f0-6103-4e18-9af9-a580c050f410.webp',
                ) ),
                'names'   => __( "BASF\nDow Chemical\nWanhua Chemical", 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'alt',
                'eyebrow' => __( 'KABEL HONORARY CHIEF SCIENTIST', 'kabel-blocks' ),
                'title'   => __( 'Collaborative Tribute to Nobel Laureate Professor James Fraser Stoddart', 'kabel-blocks' ),
                'body'    => __( 'We are honored to collaborate with Nobel Laureate Professor James Fraser Stoddart, appointing him as Kabel Honorary Chief Scientist. By deeply integrating cutting-edge chemical technology with innovative wall solutions, we jointly explore unprecedented possibilities in material performance and artistic aesthetics, continuously redefining higher standards for exceptional wall finishes.', 'kabel-blocks' ),
                'image'   => kbl_asset_id( 'nobel.webp' ),
        ) );

        $out .= kbl_b( 'features', array(
                'zone'    => 'b',
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
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'     => 'a',
                'title'    => __( 'Cultivating Wall Artisans, Delivering Masterpieces', 'kabel-blocks' ),
                'sub'      => __( 'With standardized construction, Kabel solemnly delivers a promise of quality and beautiful living.', 'kabel-blocks' ),
                'btnLabel' => __( 'CONSULT NOW', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * ECO Friendly — the formaldehyde-to-water story, certifications, data.
 */
function kabel_layout_eco_friendly(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'eyebrow' => __( 'KABEL', 'kabel-blocks' ),
                'title'   => __( 'eco friendly', 'kabel-blocks' ),
                'sub'     => __( 'Kabel is dedicated to protecting your health.', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'CORE TECHNOLOGY', 'kabel-blocks' ),
                'title'   => __( 'Principle of transforming formaldehyde into water', 'kabel-blocks' ),
                'body'    => __( 'KABEL has independently innovated a technology to degrade formaldehyde, enabling the paint film to effectively capture free formaldehyde and decompose it into harmless substances and water molecules. The formaldehyde purification rate is 94.1%, and the persistent purification rate is 82.1%. Residents can live in a worry-free environment with formaldehyde-free.', 'kabel-blocks' ),
                'image'   => kbl_asset_id( 'tech-formaldehyde-2.webp' ),
        ) );

        $out .= kbl_b( 'gallery', array(
                'zone'    => 'alt',
                'eyebrow' => __( 'INTERNATIONALLY CERTIFIED', 'kabel-blocks' ),
                'columns' => 4,
                'ids'     => kbl_asset_ids( array(
                        '1cc3bbaf-bc97-4071-b568-8199bebbf019.webp',
                        'd3fdda34-e3d5-4467-af2a-44c5f5d5d7c4.webp',
                        '5fdb666f-71bb-4aa0-89b3-982ce041d874.webp',
                        'd9ae674f-7b60-4688-a04c-4d981d926ba5.webp',
                ) ),
        ) );

        $out .= kbl_b( 'stats', array(
                'zone'  => 'a',
                'pad'   => 'compact',
                'title' => __( 'Formaldehyde degradation effect', 'kabel-blocks' ),
                'items' => array(
                        array( 'value' => '94.1%', 'text' => __( 'formaldehyde purification rate', 'kabel-blocks' ) ),
                        array( 'value' => '82.1%', 'text' => __( 'persistent purification rate', 'kabel-blocks' ) ),
                ),
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'     => 'a',
                'title'    => __( 'Create a healthier and more comfortable living environment', 'kabel-blocks' ),
                'sub'      => __( 'Kabel is dedicated to protecting your health.', 'kabel-blocks' ),
                'btnLabel' => __( 'CONSULT NOW', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * Designer — born-for-design positioning, brand attitude, three supports.
 */
function kabel_layout_designer(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'eyebrow' => __( 'DESIGNER', 'kabel-blocks' ),
                'title'   => __( 'Designer', 'kabel-blocks' ),
                'sub'     => __( 'Kabel is dedicated to protecting your health.', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'KABEL DESIGNER', 'kabel-blocks' ),
                'title'   => __( 'An artistic coating system born for design.', 'kabel-blocks' ),
                'body'    => __( 'Designers are looking for inspiration, freedom, expressive power, and professional recognition. The KABEL Designer page is built to answer these questions immediately — by positioning KABEL as a designer-friendly artistic coatings brand that expands, rather than restricts, creative vision.', 'kabel-blocks' ),
                'image'   => kbl_asset_id( 'image_20260401_085452.webp' ),
        ) );

        $out .= kbl_b( 'features', array(
                'zone'    => 'alt',
                'eyebrow' => __( 'EMOTION & BRAND ATTITUDE', 'kabel-blocks' ),
                'columns' => 4,
                'items'   => array(
                        array(
                                'icon'  => 'sparkle',
                                'title' => __( 'Core Message', 'kabel-blocks' ),
                                'text'  => __( 'KABEL is an artistic coatings brand designed to serve designers, not constrain them.', 'kabel-blocks' ),
                        ),
                        array(
                                'icon'  => 'search',
                                'title' => __( 'Visual Direction', 'kabel-blocks' ),
                                'text'  => __( 'Large-scale imagery of high-end residential projects, commercial interiors, and minimalist or artistic spaces. Surfaces should communicate depth, texture, and material emotion at first glance.', 'kabel-blocks' ),
                        ),
                        array(
                                'icon'  => 'medal',
                                'title' => __( 'Key Words to Highlight', 'kabel-blocks' ),
                                'text'  => __( 'Material expressiveness · Texture freedom · Color depth · Custom possibilities', 'kabel-blocks' ),
                        ),
                        array(
                                'icon'  => 'bolt',
                                'title' => __( 'Positioning Line', 'kabel-blocks' ),
                                'text'  => __( '“An artistic coating system born for design.”', 'kabel-blocks' ),
                        ),
                ),
        ) );

        $out .= kbl_b( 'process', array(
                'zone'  => 'b',
                'items' => array(
                        array(
                                'title' => __( 'Design Expressiveness', 'kabel-blocks' ),
                                'text'  => __( 'Designers need to know what a material can truly do. KABEL demonstrates a wide range of artistic effects through real application results: textures, layering, light reflection, matte variations, and surface depth. High-quality real project photography, detail shots, and macro images are essential — allowing designers to clearly understand material behavior beyond flat samples.', 'kabel-blocks' ),
                        ),
                        array(
                                'title' => __( 'Design Freedom', 'kabel-blocks' ),
                                'text'  => __( 'KABEL emphasizes flexibility as a core value. Designers are supported through custom color development, secondary creative effects based on original systems, and different application techniques achieving different visual outcomes. The same material can generate multiple design languages, giving designers full control over the final expression.', 'kabel-blocks' ),
                        ),
                        array(
                                'title' => __( 'Design Support', 'kabel-blocks' ),
                                'text'  => __( 'Creative ideas must be buildable. KABEL provides practical support to ensure smooth project realization, including material samples and effect mock-ups, technical consultation and system recommendations, and on-site or project-based coordination for complex designs. This service framework allows designers to focus on creativity, knowing that execution is professionally supported.', 'kabel-blocks' ),
                        ),
                ),
        ) );

        $out .= kbl_b( 'gallery', array(
                'zone'    => 'alt',
                'eyebrow' => __( 'DESIGN EXPRESSIVENESS', 'kabel-blocks' ),
                'columns' => 3,
                'ids'     => kbl_asset_ids( array(
                        'ltalian-stucco.webp',
                        'texture-detail-4.webp',
                        '12412412412.webp',
                ) ),
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'     => 'a',
                'title'    => __( 'CONTACT US FOR ANY QUESTIONS', 'kabel-blocks' ),
                'sub'      => __( 'Material expressiveness · Texture freedom · Color depth · Custom possibilities', 'kabel-blocks' ),
                'btnLabel' => __( 'CONSULT NOW', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * Products — the high-end artistic coating series page (grid is the live
 * product catalog; the six family entries are seeded with the theme).
 */
function kabel_layout_products(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'eyebrow' => __( 'PRODUCTS', 'kabel-blocks' ),
                'title'   => __( 'High-end Artistic Coating Series', 'kabel-blocks' ),
                'sub'     => __( 'Using texture, color, and craftsmanship, we endow every wall with a timeless artistic beauty.', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'INSPIRED BY ITALIAN AESTHETICS', 'kabel-blocks' ),
                'title'   => __( 'Inspired by Italian Aesthetics. Crafted for Timeless Spaces.', 'kabel-blocks' ),
                'body'    => __( 'Textures, colors and craftsmanship come together to create surfaces that embody elegance, depth and character. Inspired by Italian aesthetics and driven by material innovation, KABEL delivers premium decorative finishes with advanced formaldehyde-purifying technology — creating healthier, more beautiful spaces for homes, hospitality and commercial projects worldwide. Kabel — Italian design inspiration, a global perspective, and premium artistic coating solutions.', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'products', array(
                'zone'    => 'alt',
                'eyebrow' => __( 'PRODUCT FAMILIES', 'kabel-blocks' ),
                'count'   => 6,
                'columns' => 3,
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'     => 'a',
                'title'    => __( 'Transform every wall into a timeless work of art', 'kabel-blocks' ),
                'sub'      => __( '– KABEL Design Philosophy', 'kabel-blocks' ),
                'btnLabel' => __( 'CONSULT NOW', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * Morandi Colors — inspiration, palette, positioning.
 */
function kabel_layout_morandi_colors(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'eyebrow' => __( 'COLORS', 'kabel-blocks' ),
                'title'   => __( 'Morandi Colors', 'kabel-blocks' ),
                'sub'     => __( 'Giorgio Morandi', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'INSPIRATION', 'kabel-blocks' ),
                'title'   => __( 'Giorgio Morandi', 'kabel-blocks' ),
                'body'    => __( 'The color inspiration comes from the classic paintings of the Italian master Giorgio Morandi. The overall tone is restrained and warm, hailed as a “very safe color system” — soft, understated, yet timeless and enduring, possessing both high inclusivity and profound aesthetic connotations.', 'kabel-blocks' ),
                'image'   => kbl_asset_id( 'morandi-1-1.webp' ),
        ) );

        $out .= kbl_b( 'gallery', array(
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
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'COLOR POSITIONING', 'kabel-blocks' ),
                'title'   => __( 'Color Target', 'kabel-blocks' ),
                'body'    => __( 'Targeting individuals who value spatial quality, aesthetics, and long-term appreciation; suitable for users who prefer rational, balanced, and restrained beauty.', 'kabel-blocks' ),
                'bullets' => __( "Color Characteristics: Low saturation, with a combination of high and low brightness; delicate layering; natural harmony between colors; easy to coordinate.\nApplication Scenarios: Ideal for large-area applications, such as entire walls, public spaces, or main residential spaces, creating a quiet, stable, and sophisticated atmosphere.", 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'     => 'a',
                'title'    => __( 'Find colors that feel like home', 'kabel-blocks' ),
                'sub'      => __( 'Start your next project with expert guidance and a palette that’s tailored to your space, light and lifestyle, for both the inside and outside of your home.', 'kabel-blocks' ),
                'btnLabel' => __( 'CONTACT US', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * Van Gogh Color — inspiration, palette, positioning.
 */
function kabel_layout_van_gogh_color(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'eyebrow' => __( 'COLORS', 'kabel-blocks' ),
                'title'   => __( 'Van Gogh Color', 'kabel-blocks' ),
                'sub'     => __( 'Vincent Willem van Gogh', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'INSPIRATION', 'kabel-blocks' ),
                'title'   => __( 'Vincent Willem van Gogh', 'kabel-blocks' ),
                'body'    => __( 'Colors drawn from Dutch painter Vincent van Gogh’s works, featuring bold, dynamic hues that exude artistic beauty and serve as perfect accent tones.', 'kabel-blocks' ),
                'image'   => kbl_asset_id( 'van-gogh-1.webp' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'alt',
                'eyebrow' => __( 'COLOR POSITIONING', 'kabel-blocks' ),
                'title'   => __( 'Target Audience', 'kabel-blocks' ),
                'body'    => __( 'Highly discerning individuals. Characteristics: High saturation, medium/medium-low brightness.', 'kabel-blocks' ),
                'bullets' => __( "Application: Ideal for accent walls or as decorative accents in spaces.", 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'gallery', array(
                'zone'    => 'b',
                'columns' => 2,
                'ids'     => kbl_asset_ids( array(
                        'van-gogh-2.webp',
                        'van-gogh-3.webp',
                ) ),
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'     => 'a',
                'title'    => __( 'Find colors that feel like home', 'kabel-blocks' ),
                'sub'      => __( 'Start your next project with expert guidance and a palette that’s tailored to your space, light and lifestyle, for both the inside and outside of your home.', 'kabel-blocks' ),
                'btnLabel' => __( 'CONTACT US', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * Macaron colors — inspiration, palette, positioning.
 */
function kabel_layout_macaron_colors(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'eyebrow' => __( 'COLORS', 'kabel-blocks' ),
                'title'   => __( 'Macaron colors', 'kabel-blocks' ),
                'sub'     => __( 'Macaron', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'COLOR INSPIRATION', 'kabel-blocks' ),
                'title'   => __( 'Macaron', 'kabel-blocks' ),
                'body'    => __( 'Inspired by the classic French dessert – the macaron. As a symbol of French aristocratic desserts and a refined lifestyle, macarons are world-renowned for their vibrant colors and delicate texture. They are not only a delicious treat but also embody people’s aspirations and imaginations for a happy life and beautiful things.', 'kabel-blocks' ),
                'image'   => kbl_asset_id( '12.webp' ),
        ) );

        $out .= kbl_b( 'gallery', array(
                'zone'    => 'alt',
                'columns' => 4,
                'ids'     => kbl_asset_ids( array(
                        'macaron-1.webp',
                        'macaron-2.webp',
                        'macaron-3.webp',
                        '4.webp',
                        '5.webp',
                        'macaron-6.webp',
                        '7.webp',
                        '8.webp',
                        '9.webp',
                        '10.webp',
                        '11.webp',
                        '12.webp',
                        '13.webp',
                ) ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'COLOR POSITIONING', 'kabel-blocks' ),
                'title'   => __( 'Color Target', 'kabel-blocks' ),
                'body'    => __( 'Targeting young, energetic individuals who love life and express themselves. Color Characteristics: Medium saturation, high brightness; light and cheerful colors create a pleasant, warm, and approachable atmosphere.', 'kabel-blocks' ),
                'bullets' => __( "Application Scenarios: Suitable for young people’s rooms; can be used extensively as a base color or as an accent color for a partial background wall or space, easily creating a lively and soothing living space.", 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'     => 'a',
                'title'    => __( 'Find colors that feel like home', 'kabel-blocks' ),
                'sub'      => __( 'Start your next project with expert guidance and a palette that’s tailored to your space, light and lifestyle, for both the inside and outside of your home.', 'kabel-blocks' ),
                'btnLabel' => __( 'CONTACT US', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * Museum Collection Colors — inspiration, palette, positioning.
 */
function kabel_layout_museum_collection_colors(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'eyebrow' => __( 'COLORS', 'kabel-blocks' ),
                'title'   => __( 'Museum Collection Colors', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'INSPIRATION', 'kabel-blocks' ),
                'title'   => __( 'Museum Collection Colors', 'kabel-blocks' ),
                'body'    => __( 'The color scheme draws inspiration from classic colors found in historical artifacts, court art, and museum collections, reinterpreting Eastern and Western color aesthetics with modern design language. This color scheme achieves a balance between tradition and contemporary, blending historical weight with modern aesthetics to present a serene, elegant, and culturally profound spatial atmosphere.', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'gallery', array(
                'zone'    => 'alt',
                'columns' => 3,
                'ids'     => kbl_asset_ids( array(
                        '1-1.webp',
                        '2-1.webp',
                        '3-2.webp',
                        '4-2.webp',
                        '5-1.webp',
                        '6-1.webp',
                        '7-1.webp',
                        '8-1.webp',
                        '9-1.webp',
                ) ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'COLOR POSITIONING', 'kabel-blocks' ),
                'title'   => __( 'Target Audience', 'kabel-blocks' ),
                'body'    => __( 'Individuals who respect traditional culture, value cultural identity, and possess aesthetic confidence.', 'kabel-blocks' ),
                'bullets' => __( "Color Characteristics: High saturation, with both high and low brightness, strong color expressiveness, both dignified and rich in layering.\nApplication Scenarios: Suitable for large-scale application in overall spaces, feature walls, or public areas to create a spatial atmosphere with cultural depth and visual appeal.", 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'     => 'a',
                'title'    => __( 'Find colors that feel like home', 'kabel-blocks' ),
                'sub'      => __( 'Start your next project with expert guidance and a palette that’s tailored to your space, light and lifestyle, for both the inside and outside of your home.', 'kabel-blocks' ),
                'btnLabel' => __( 'CONTACT US', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * Projects — archive intro (the case cards themselves are the projects CPT).
 */
function kabel_layout_projects(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'eyebrow' => __( 'PROJECTS', 'kabel-blocks' ),
                'title'   => __( 'Residential & Commercial Projects', 'kabel-blocks' ),
                'sub'     => __( 'Home Renovation Projects · Engineering Projects', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'gallery', array(
                'zone'    => 'b',
                'eyebrow' => __( 'FIELD REFERENCES', 'kabel-blocks' ),
                'columns' => 2,
                'ids'     => kbl_asset_ids( array(
                        'image_20260327_171614-1.webp',
                        'chatgpt-image-2026-4-1-09_52_31.webp',
                ) ),
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'     => 'a',
                'title'    => __( 'Book Your Colour Service', 'kabel-blocks' ),
                'sub'      => __( 'Home Renovation Projects · Engineering Projects', 'kabel-blocks' ),
                'btnLabel' => __( 'CONSULT NOW', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * Project Procurement — the six-step service system and applications.
 */
function kabel_layout_project_procurement(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'eyebrow' => __( 'PROJECT PROCUREMENT', 'kabel-blocks' ),
                'title'   => __( 'Project Procurement', 'kabel-blocks' ),
                'sub'     => __( 'Global manufacturer of high-end decorative coating brands, providing one-stop wall solutions for large-scale commercial spaces and luxury residential projects.', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'PROFESSIONAL SERVICES', 'kabel-blocks' ),
                'title'   => __( 'Professional Services', 'kabel-blocks' ),
                'body'    => __( 'From early design support to product selection, sample confirmation, construction guidance, and delivery support, KABEL provides a complete service system for projects worldwide.', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'process', array(
                'zone'  => 'alt',
                'items' => array(
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
        ) );

        $out .= kbl_b( 'gallery', array(
                'zone'    => 'b',
                'eyebrow' => __( 'ARCHITECTURAL APPLICATIONS', 'kabel-blocks' ),
                'title'   => __( 'Architectural Applications', 'kabel-blocks' ),
                'sub'     => __( 'KABEL decorative coatings are widely used in:', 'kabel-blocks' ),
                'columns' => 3,
                'ids'     => kbl_asset_ids( array(
                        '103.webp',
                        'concrete-12-1.webp',
                        'sea-house-exterior-wall-paint.webp',
                        'como-velvet-2.webp',
                        '1-2.webp',
                ) ),
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'     => 'a',
                'title'    => __( 'Ready to Discuss Your Project?', 'kabel-blocks' ),
                'sub'      => __( 'From early design support to global delivery — one accountable partner.', 'kabel-blocks' ),
                'btnLabel' => __( 'CONSULT NOW', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * Customize — six customization dimensions and the process behind them.
 */
function kabel_layout_customize(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'eyebrow' => __( 'CUSTOMIZE', 'kabel-blocks' ),
                'title'   => __( 'Tailored Solutions for Your Market', 'kabel-blocks' ),
                'sub'     => __( 'Global manufacturer of high-end decorative coating brands, providing one-stop wall solutions for large-scale commercial spaces and luxury residential projects.', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'CUSTOMIZE', 'kabel-blocks' ),
                'title'   => __( 'Customized Services', 'kabel-blocks' ),
                'body'    => __( 'KABEL offers customized decorative coating solutions. From product formulation and color scheme to packaging design and logistics, we provide full-process support based on local climate, construction environment, regulations and standards, and market demand.', 'kabel-blocks' ),
                'image'   => kbl_asset_id( '12412412412-2.webp' ),
        ) );

        $out .= kbl_b( 'features', array(
                'zone'    => 'alt',
                'eyebrow' => __( 'CUSTOMIZED SERVICES', 'kabel-blocks' ),
                'columns' => 3,
                'items'   => array(
                        array(
                                'icon'  => 'gear',
                                'title' => __( 'Product performance customization', 'kabel-blocks' ),
                                'text'  => __( 'Optimize product performance based on local climate, construction environment, and application requirements.', 'kabel-blocks' ),
                        ),
                        array(
                                'icon'  => 'sparkle',
                                'title' => __( 'Color System Customization', 'kabel-blocks' ),
                                'text'  => __( 'Offers an extensive color palette to create color schemes that align with local aesthetic preferences.', 'kabel-blocks' ),
                        ),
                        array(
                                'icon'  => 'medal',
                                'title' => __( 'Customized Artistic Effects', 'kabel-blocks' ),
                                'text'  => __( 'KABEL boasts a rich system of artistic coating techniques, enabling the development of custom textures to meet design requirements.', 'kabel-blocks' ),
                        ),
                        array(
                                'icon'  => 'cube',
                                'title' => __( 'Custom Packaging', 'kabel-blocks' ),
                                'text'  => __( 'Customized packaging solutions featuring localized labels, branding, and compliance markings tailored to your market.', 'kabel-blocks' ),
                        ),
                        array(
                                'icon'  => 'globe',
                                'title' => __( 'Global Supply Chain Services', 'kabel-blocks' ),
                                'text'  => __( 'From order placement to global delivery — a one-stop supply chain service that is stable, efficient, and worry-free.', 'kabel-blocks' ),
                        ),
                        array(
                                'icon'  => 'wrench',
                                'title' => __( 'Technical & Service Support', 'kabel-blocks' ),
                                'text'  => __( 'Providing technical documentation, product data, and remote training to facilitate the efficient delivery of global projects.', 'kabel-blocks' ),
                        ),
                ),
        ) );

        $out .= kbl_b( 'process', array(
                'zone'    => 'b',
                'eyebrow' => __( 'CUSTOMIZATION PROCESS', 'kabel-blocks' ),
                'items'   => array(
                        array( 'title' => __( 'Submit Project Requirements', 'kabel-blocks' ), 'text' => '' ),
                        array( 'title' => __( 'Project Evaluation', 'kabel-blocks' ), 'text' => '' ),
                        array( 'title' => __( 'Confirmation of Proposal and Samples', 'kabel-blocks' ), 'text' => '' ),
                        array( 'title' => __( 'Quotation and Order Confirmation', 'kabel-blocks' ), 'text' => '' ),
                        array( 'title' => __( 'Project Order & Payment Process', 'kabel-blocks' ), 'text' => '' ),
                        array( 'title' => __( 'Production and Delivery', 'kabel-blocks' ), 'text' => '' ),
                ),
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'     => 'a',
                'title'    => __( 'Ready to Discuss Your Project?', 'kabel-blocks' ),
                'sub'      => __( 'From product formulation to packaging design and logistics — full-process support.', 'kabel-blocks' ),
                'btnLabel' => __( 'Request Custom Solution', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * Distributor — the dealer program.
 */
function kabel_layout_distributor(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'eyebrow' => __( 'DISTRIBUTOR', 'kabel-blocks' ),
                'title'   => __( 'Franchisees', 'kabel-blocks' ),
                'sub'     => __( 'Born in the heart of Italy’s decorative coating capital. Over a century of heritage, blending Italian aesthetics with modern innovation.', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'DISTRIBUTOR', 'kabel-blocks' ),
                'title'   => __( 'Bring Premium Decorative Coatings to Your Market', 'kabel-blocks' ),
                'body'    => __( 'KABEL supports international importers, paint distributors, building-material suppliers, design studios, and project partners with differentiated decorative coating products, showroom solutions, application training, localized marketing resources, and continuous business support.', 'kabel-blocks' ),
                'image'   => kbl_asset_id( 'training-1.webp' ),
        ) );

        $out .= kbl_b( 'features', array(
                'zone'    => 'alt',
                'eyebrow' => __( 'DEALER SUPPORT', 'kabel-blocks' ),
                'columns' => 3,
                'items'   => array(
                        array( 'icon' => 'cube',   'title' => __( 'Premium Products', 'kabel-blocks' ),     'text' => '' ),
                        array( 'icon' => 'globe',  'title' => __( 'Market Localization', 'kabel-blocks' ),  'text' => '' ),
                        array( 'icon' => 'wrench', 'title' => __( 'Technical Training', 'kabel-blocks' ),  'text' => '' ),
                        array( 'icon' => 'medal',  'title' => __( 'Showroom Support', 'kabel-blocks' ),     'text' => '' ),
                        array( 'icon' => 'truck',  'title' => __( 'Reliable Supply', 'kabel-blocks' ),      'text' => '' ),
                ),
        ) );

        $out .= kbl_b( 'cta-quote', array(
                'text'    => __( 'Dealer Application', 'kabel-blocks' ),
                'product' => __( 'Dealer Application', 'kabel-blocks' ),
                'align'   => 'center',
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'reverse' => true,
                'eyebrow' => __( 'MORE THAN PAINT', 'kabel-blocks' ),
                'title'   => __( 'More Than Paint—A Higher-Value Surface Solution', 'kabel-blocks' ),
                'body'    => __( 'Traditional paints mainly address basic protection and color requirements. Decorative coatings create greater design value through texture, touch, color, and customized application techniques. For distributors, this creates opportunities beyond material sales, including:', 'kabel-blocks' ),
                'image'   => kbl_asset_id( '12412412412-2.webp' ),
        ) );

        $out .= kbl_b( 'features', array(
                'zone'    => 'alt',
                'eyebrow' => __( 'DISTRIBUTOR OPPORTUNITIES', 'kabel-blocks' ),
                'columns' => 3,
                'items'   => array(
                        array( 'icon' => 'cube',   'title' => __( 'Product Differentiation', 'kabel-blocks' ), 'text' => __( 'Move beyond price-based competition in conventional wall paints.', 'kabel-blocks' ) ),
                        array( 'icon' => 'wrench', 'title' => __( 'Service-Based Revenue', 'kabel-blocks' ),   'text' => __( 'Extend revenue through samples, design, application, and project services.', 'kabel-blocks' ) ),
                        array( 'icon' => 'globe',  'title' => __( 'Channel Expansion', 'kabel-blocks' ),       'text' => '' ),
                ),
        ) );

        $out .= kbl_b( 'process', array(
                'zone'    => 'b',
                'eyebrow' => __( 'WHAT KABEL BRINGS TO YOUR MARKET', 'kabel-blocks' ),
                'items'   => array(
                        array(
                                'title' => __( 'Multi-Level Product Portfolio', 'kabel-blocks' ),
                                'text'  => __( 'From accessible decorative finishes to microcement, velvet, metallic, and specialty textures, KABEL helps partners serve different price levels and project requirements.', 'kabel-blocks' ),
                        ),
                        array(
                                'title' => __( 'Market-Specific Product Selection', 'kabel-blocks' ),
                                'text'  => __( 'We help partners plan an appropriate launch portfolio based on local climate, substrates, application practices, design preferences, and project demand — instead of simply offering every product.', 'kabel-blocks' ),
                        ),
                        array(
                                'title' => __( 'Replicable Sample & Application System', 'kabel-blocks' ),
                                'text'  => __( 'We provide standard effect samples, construction steps, suggestions for supporting tools, and process training to help partners more consistently replicate wall effects.', 'kabel-blocks' ),
                        ),
                        array(
                                'title' => __( 'Showroom and terminal support', 'kabel-blocks' ),
                                'text'  => __( 'Based on store size and budget, we provide recommendations for product displays, sample walls, color zones, consultation areas, and installation demonstration zones, helping dealers transform their showrooms into sales tools.', 'kabel-blocks' ),
                        ),
                ),
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'       => 'a',
                'title'      => __( 'Ready to Get Started?', 'kabel-blocks' ),
                'sub'        => __( 'Differentiated products, showroom solutions, application training, and continuous business support.', 'kabel-blocks' ),
                'btnLabel'   => __( 'Dealer Application', 'kabel-blocks' ),
                'btnProduct' => __( 'Dealer Application', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * Blog — news & inspiration opener (the listing itself is the posts archive).
 */
function kabel_layout_blog(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'eyebrow' => __( 'NEWS', 'kabel-blocks' ),
                'title'   => __( 'Blog', 'kabel-blocks' ),
                'sub'     => __( 'Want to add a unique style to your walls? Unsure which colors suit your home? Visit our blog for inspiration, ideas, and trends.', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'features', array(
                'zone'    => 'b',
                'eyebrow' => __( 'CATEGORIES', 'kabel-blocks' ),
                'columns' => 3,
                'items'   => array(
                        array( 'icon' => 'medal',   'title' => __( 'Company News', 'kabel-blocks' ),  'text' => __( 'Kabel milestones and showroom openings.', 'kabel-blocks' ) ),
                        array( 'icon' => 'globe',   'title' => __( 'Industry News', 'kabel-blocks' ), 'text' => __( 'Trends shaping decorative coatings.', 'kabel-blocks' ) ),
                        array( 'icon' => 'search',  'title' => __( 'Knowledge', 'kabel-blocks' ),     'text' => __( 'Practical guidance for walls and color.', 'kabel-blocks' ) ),
                ),
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'     => 'a',
                'title'    => __( 'CONTACT US FOR ANY QUESTIONS', 'kabel-blocks' ),
                'sub'      => __( 'Global Service · Local Response', 'kabel-blocks' ),
                'btnLabel' => __( 'CONSULT NOW', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * Contact — cards, inquiry form, social links.
 */
function kabel_layout_contact(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'pad'     => 'compact',
                'eyebrow' => __( 'CONTACT', 'kabel-blocks' ),
                'title'   => __( 'Contact Us', 'kabel-blocks' ),
                'sub'     => __( 'Global Service · Local Response', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'contact', array(
                'zone'    => 'b',
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
        ) );

        $out .= kbl_b( 'inquiry', array(
                'zone'    => 'alt',
                'eyebrow' => __( 'CONTACT', 'kabel-blocks' ),
                'title'   => __( 'CONTACT US FOR ANY QUESTIONS', 'kabel-blocks' ),
                'sub'     => __( 'Global Service · Local Response', 'kabel-blocks' ),
                'message' => __( 'Hello Kabel, I have a question about:', 'kabel-blocks' ),
        ) );

        /* Social link group — our universal section container + quote buttons. */
        $socials  = kbl_b( 'cta-quote', array(
                'text'  => __( 'Facebook', 'kabel-blocks' ),
                'url'   => 'https://www.facebook.com/profile.php?id=61575873133832',
                'style' => 'outline',
        ) );
        $socials .= kbl_b( 'cta-quote', array(
                'text'  => __( 'Instagram', 'kabel-blocks' ),
                'url'   => 'https://www.instagram.com/kabelpaints/',
                'style' => 'outline',
        ) );
        $socials .= kbl_b( 'cta-quote', array(
                'text'  => __( 'YouTube', 'kabel-blocks' ),
                'url'   => 'https://www.youtube.com/@kabelpaint/videos',
                'style' => 'outline',
        ) );
        $socials .= kbl_b( 'cta-quote', array(
                'text'  => __( 'TikTok', 'kabel-blocks' ),
                'url'   => 'https://www.tiktok.com/@kabel.paint',
                'style' => 'outline',
        ) );

        $out .= kbl_b( 'section', array(
                'zone' => 'b',
                'pad'  => 'compact',
        ), $socials );

        return $out;
}

/**
 * FAQ — fifteen Kabel questions with straight answers.
 */
function kabel_layout_faq(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'pad'     => 'compact',
                'eyebrow' => __( 'FAQ', 'kabel-blocks' ),
                'title'   => __( 'FAQ', 'kabel-blocks' ),
                'sub'     => __( 'Clear Answers to Help You Make Confident Decisions.', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'faq', array(
                'zone'  => 'b',
                'items' => array(
                        array(
                                'q' => __( 'What is the most popular wall painting trend for 2026?', 'kabel-blocks' ),
                                'a' => __( 'The core trend for 2026 is “decorative artistic borders.” Instead of full-wall coverage, hand-painted patterned borders are used to highlight architectural details, adding personality and playfulness to the space.', 'kabel-blocks' ),
                        ),
                        array(
                                'q' => __( 'What is artistic paint, and how does it differ from regular emulsion paint?', 'kabel-blocks' ),
                                'a' => __( 'While regular paint offers color and flatness, Kabel Artistic Paint uses specialized formulas and application techniques to create 3D textures, metallic sheens, or velvet-like visual effects, transforming walls into works of art.', 'kabel-blocks' ),
                        ),
                        array(
                                'q' => __( 'What is “Sensory Aesthetics,” and how is it reflected in your products?', 'kabel-blocks' ),
                                'a' => __( 'It refers to the fusion of sight and touch. Kabel products offer not just beautiful colors, but also tactile sensations like grit, silk, and leather, satisfying a full range of sensory experiences.', 'kabel-blocks' ),
                        ),
                        array(
                                'q' => __( 'Why choose Kabel instead of other brands?', 'kabel-blocks' ),
                                'a' => __( 'Because Kabel perfectly merges “top-tier formaldehyde-purifying tech” with “Italian artistic aesthetics.” We don’t just sell paint; we provide a one-stop solution for spatial aesthetics and wellness.', 'kabel-blocks' ),
                        ),
                        array(
                                'q' => __( 'How many colors are in the Kabel artistic paint library?', 'kabel-blocks' ),
                                'a' => __( 'We have thousands of digitally formulated colors and support personalized customization based on 2026 fashion trends.', 'kabel-blocks' ),
                        ),
                        array(
                                'q' => __( 'Can I apply artistic paint directly over old emulsion paint?', 'kabel-blocks' ),
                                'a' => __( 'If the old wall is firm, flat, and free of hollows, it can be applied after using a Kabel-specific primer. However, we recommend a wall assessment by a professional technician.', 'kabel-blocks' ),
                        ),
                        array(
                                'q' => __( 'Is artistic paint suitable for modern minimalist styles?', 'kabel-blocks' ),
                                'a' => __( 'Absolutely. Our Micro-cement series and premium matte textures are the perfect companions for minimalist styles, adding depth and a sense of luxury to the space.', 'kabel-blocks' ),
                        ),
                        array(
                                'q' => __( 'How does artistic paint look in rooms with poor natural light?', 'kabel-blocks' ),
                                'a' => __( 'Artistic paints with metallic reflective effects or silk textures can bounce light around, effectively enhancing the visual brightness of darker rooms.', 'kabel-blocks' ),
                        ),
                        array(
                                'q' => __( 'Is “core-shell copolymer” harmful to the human body?', 'kabel-blocks' ),
                                'a' => __( 'Not at all. It is a high-tech molecular structure designed to act as a formaldehyde trap; its physical and chemical properties are extremely stable and eco-friendly.', 'kabel-blocks' ),
                        ),
                        array(
                                'q' => __( 'Will artistic paint fade over time?', 'kabel-blocks' ),
                                'a' => __( 'No. Kabel uses high-quality weather-resistant pigments with excellent UV resistance, ensuring colors stay vibrant and fresh for years.', 'kabel-blocks' ),
                        ),
                        array(
                                'q' => __( 'Can Kabel’s formaldehyde-purifying artistic paint actually purify the air?', 'kabel-blocks' ),
                                'a' => __( 'Yes. We utilize advanced “core-shell copolymer” technology that actively adsorbs and decomposes free formaldehyde molecules in the air, converting them into harmless substances to continuously protect home wellness.', 'kabel-blocks' ),
                        ),
                        array(
                                'q' => __( 'Is artistic paint suitable for humid environments, such as the rainy season?', 'kabel-blocks' ),
                                'a' => __( 'Absolutely. Kabel artistic paint features excellent anti-mold and moisture-resistant properties; its breathability effectively prevents peeling or mold growth on walls.', 'kabel-blocks' ),
                        ),
                        array(
                                'q' => __( 'Is this paint scrub-resistant? What if children stain the walls?', 'kabel-blocks' ),
                                'a' => __( 'Our artistic paints are highly wear-resistant. Most stains can be removed by simply wiping with a damp cloth without damaging the artistic texture of the wall.', 'kabel-blocks' ),
                        ),
                        array(
                                'q' => __( 'How long does the application process typically take?', 'kabel-blocks' ),
                                'a' => __( 'It depends on the complexity. Basic textures usually take 3–5 days, involving multiple stages — including primer, mid-coat, and artistic topcoat — to ensure a perfect finish.', 'kabel-blocks' ),
                        ),
                        array(
                                'q' => __( 'Are Kabel’s artistic paints water-based or oil-based?', 'kabel-blocks' ),
                                'a' => __( 'Kabel’s artistic paints are water-based and low in VOC, aligned with our eco-friendly formulation and formaldehyde-purifying technology.', 'kabel-blocks' ),
                        ),
                ),
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'     => 'a',
                'title'    => __( 'CONTACT US FOR ANY QUESTIONS', 'kabel-blocks' ),
                'sub'      => __( 'Clear answers to help you make confident decisions.', 'kabel-blocks' ),
                'btnLabel' => __( 'CONSULT NOW', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * Landing — a Kabel campaign page (generic but real brand copy).
 */
function kabel_layout_landing(): string {
        $out  = kbl_b( 'hero', array(
                'zone'         => 'a',
                'layout'       => 'center',
                'pad'          => 'spacious',
                'eyebrow'      => __( 'Global high-end decorative paint brand', 'kabel-blocks' ),
                'title'        => __( 'Transform every wall into a timeless work of art', 'kabel-blocks' ),
                'sub'          => __( 'Kabel Art Paint — from Druento', 'kabel-blocks' ),
                'primaryLabel' => __( 'CONSULT NOW', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'features', array(
                'zone'    => 'b',
                'eyebrow' => __( 'PRODUCT FEATURES', 'kabel-blocks' ),
                'columns' => 3,
                'items'   => array(
                        array(
                                'icon'  => 'sparkle',
                                'title' => __( 'Craftsmanship effect', 'kabel-blocks' ),
                                'text'  => __( 'Refined through six artisanal techniques, the surface reveals a soft, cashmere-like touch — subtle, smooth, and quietly luxurious.', 'kabel-blocks' ),
                        ),
                        array(
                                'icon'  => 'cube',
                                'title' => __( 'Wall and floor integration', 'kabel-blocks' ),
                                'text'  => __( 'The original cement texture, with the wall surface molded as a single piece, presents a simple yet profound sense of space.', 'kabel-blocks' ),
                        ),
                        array(
                                'icon'  => 'drop',
                                'title' => __( 'Waterproof performance', 'kabel-blocks' ),
                                'text'  => __( 'Exceptional resistance to moisture and stains, preserving the wall’s pristine elegance over time.', 'kabel-blocks' ),
                        ),
                ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'alt',
                'eyebrow' => __( 'FORMALDEHYDE → WATER', 'kabel-blocks' ),
                'title'   => __( 'Create a healthier and more comfortable living environment', 'kabel-blocks' ),
                'body'    => __( 'KABEL has developed an innovative formaldehyde-purifying technology. Independently tested, it achieves a formaldehyde purification rate of up to 94.1% and a long-lasting purification efficiency of 82.1%. Beyond enhancing interior aesthetics, it helps improve indoor air quality and supports healthier, more comfortable living environments.', 'kabel-blocks' ),
                'image'   => kbl_asset_id( 'eco-friendly-1920850-5.5-.webp' ),
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'     => 'a',
                'title'    => __( 'CONTACT US FOR ANY QUESTIONS', 'kabel-blocks' ),
                'sub'      => __( 'Global Service · Local Response', 'kabel-blocks' ),
                'btnLabel' => __( 'CONSULT NOW', 'kabel-blocks' ),
        ) );

        return $out;
}

/**
 * Starter — a clean Kabel-flavoured beginning for any new page.
 */
function kabel_layout_starter(): string {
        $out  = kbl_b( 'hero', array(
                'zone'    => 'a',
                'layout'  => 'center',
                'pad'     => 'compact',
                'eyebrow' => __( 'PAGE EYEBROW', 'kabel-blocks' ),
                'title'   => __( 'Your page title', 'kabel-blocks' ),
                'sub'     => __( 'One line that says what this page promises the visitor.', 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'split', array(
                'zone'    => 'b',
                'eyebrow' => __( 'SECTION EYEBROW', 'kabel-blocks' ),
                'title'   => __( 'A two-column opener', 'kabel-blocks' ),
                'body'    => __( 'Replace this text with your story, spec or argument — then use the sidebar to swap the image, flip the columns or restyle the background.', 'kabel-blocks' ),
                'bullets' => __( "First checkable point\nSecond point\nThird point", 'kabel-blocks' ),
        ) );

        $out .= kbl_b( 'features', array(
                'zone'    => 'alt',
                'eyebrow' => __( 'HIGHLIGHTS', 'kabel-blocks' ),
                'title'   => __( 'Three things worth knowing', 'kabel-blocks' ),
                'columns' => 3,
                'items'   => array(
                        array( 'icon' => 'cube',   'title' => __( 'Point one', 'kabel-blocks' ),   'text' => __( 'Keep each card to two short lines — scannability wins.', 'kabel-blocks' ) ),
                        array( 'icon' => 'sparkle','title' => __( 'Point two', 'kabel-blocks' ),   'text' => __( 'Pick a different icon per card from the sidebar.', 'kabel-blocks' ) ),
                        array( 'icon' => 'medal',  'title' => __( 'Point three', 'kabel-blocks' ), 'text' => __( 'Add or remove cards with the repeater control.', 'kabel-blocks' ) ),
                ),
        ) );

        $out .= kbl_b( 'cta', array(
                'zone'  => 'a',
                'title' => __( 'Close with one clear action', 'kabel-blocks' ),
                'sub'   => __( 'The primary button opens the Kabel inquiry popup by default.', 'kabel-blocks' ),
        ) );

        return $out;
}

/* ---------------------------------------------------------------------
 * Registry + matching (consumed by the seeder, patterns and bootstrap)
 * ------------------------------------------------------------------- */

/**
 * The layout registry.
 *
 * @return array<string, array{label:string, desc:string}>
 */
function kabel_block_layouts(): array {
        return array(
                'home'                      => array(
                        'label' => __( 'Home — Kabel dark-luxury homepage', 'kabel-blocks' ),
                        'desc'  => __( 'Hero, Nobel-tribute story, formaldehyde-to-water technology, live product grid, features and inquiry form.', 'kabel-blocks' ),
                ),
                'about'                     => array(
                        'label' => __( 'About — heritage, technology, partners', 'kabel-blocks' ),
                        'desc'  => __( 'Druento heritage, Formaldehyde → Water core technology, BASF / Dow / Wanhua partners, Nobel tribute and architect reasons.', 'kabel-blocks' ),
                ),
                'eco-friendly'              => array(
                        'label' => __( 'ECO Friendly — the health story', 'kabel-blocks' ),
                        'desc'  => __( 'Purifying technology, international certifications and the 94.1% / 82.1% degradation data.', 'kabel-blocks' ),
                ),
                'designer'                  => array(
                        'label' => __( 'Designer — born for design', 'kabel-blocks' ),
                        'desc'  => __( 'Brand attitude cards, the three design supports and an expressiveness gallery.', 'kabel-blocks' ),
                ),
                'products'                  => array(
                        'label' => __( 'Products — artistic coating series', 'kabel-blocks' ),
                        'desc'  => __( 'Series intro plus the live six-family product catalog grid.', 'kabel-blocks' ),
                ),
                'morandi-colors'            => array(
                        'label' => __( 'Morandi Colors — palette page', 'kabel-blocks' ),
                        'desc'  => __( 'Giorgio Morandi inspiration, a six-swatch palette and the colour positioning.', 'kabel-blocks' ),
                ),
                'van-gogh-color'            => array(
                        'label' => __( 'Van Gogh Color — palette page', 'kabel-blocks' ),
                        'desc'  => __( 'Van Gogh inspiration, accent-tone gallery and the colour positioning.', 'kabel-blocks' ),
                ),
                'macaron-colors'            => array(
                        'label' => __( 'Macaron colors — palette page', 'kabel-blocks' ),
                        'desc'  => __( 'French-dessert inspiration with a thirteen-swatch palette and the colour positioning.', 'kabel-blocks' ),
                ),
                'museum-collection-colors'  => array(
                        'label' => __( 'Museum Collection Colors — palette page', 'kabel-blocks' ),
                        'desc'  => __( 'Museum-collections inspiration, a nine-swatch palette and the colour positioning.', 'kabel-blocks' ),
                ),
                'projects'                  => array(
                        'label' => __( 'Projects — residential & commercial', 'kabel-blocks' ),
                        'desc'  => __( 'Archive opener with field references and the colour-service CTA.', 'kabel-blocks' ),
                ),
                'project-procurement'       => array(
                        'label' => __( 'Project Procurement — six-step service', 'kabel-blocks' ),
                        'desc'  => __( 'Professional services, the six project steps and architectural application gallery.', 'kabel-blocks' ),
                ),
                'customize'                 => array(
                        'label' => __( 'Customize — tailored solutions', 'kabel-blocks' ),
                        'desc'  => __( 'Six customization dimensions and the six-step customization process.', 'kabel-blocks' ),
                ),
                'distributor'               => array(
                        'label' => __( 'Distributor — dealer program', 'kabel-blocks' ),
                        'desc'  => __( 'Dealer support, the dealer-application button, higher-value opportunities and what Kabel brings.', 'kabel-blocks' ),
                ),
                'blog'                      => array(
                        'label' => __( 'Blog — news & inspiration', 'kabel-blocks' ),
                        'desc'  => __( 'Blog opener with the three news categories and a contact CTA.', 'kabel-blocks' ),
                ),
                'contact'                   => array(
                        'label' => __( 'Contact — cards, form, socials', 'kabel-blocks' ),
                        'desc'  => __( 'Headquarters / branch / WhatsApp / email cards, the inquiry form and social links.', 'kabel-blocks' ),
                ),
                'faq'                       => array(
                        'label' => __( 'FAQ — fifteen answers', 'kabel-blocks' ),
                        'desc'  => __( 'The full Kabel question set as accordions plus a contact CTA.', 'kabel-blocks' ),
                ),
                'landing'                   => array(
                        'label' => __( 'Landing — campaign page', 'kabel-blocks' ),
                        'desc'  => __( 'A generic Kabel campaign page: hero, features, technology split and CTA.', 'kabel-blocks' ),
                ),
                'starter'                   => array(
                        'label' => __( 'Starter — clean generic page', 'kabel-blocks' ),
                        'desc'  => __( 'Hero, split, three cards and a CTA to begin any page.', 'kabel-blocks' ),
                ),
        );
}

/**
 * Get one layout's block markup (or null).
 */
function kabel_get_block_layout( string $slug ): ?string {
        switch ( sanitize_key( $slug ) ) {
                case 'home':
                        return kabel_layout_home();
                case 'about':
                        return kabel_layout_about();
                case 'eco-friendly':
                case 'ecofriendly':
                        return kabel_layout_eco_friendly();
                case 'designer':
                        return kabel_layout_designer();
                case 'products':
                        return kabel_layout_products();
                case 'morandi-colors':
                case 'morandi':
                        return kabel_layout_morandi_colors();
                case 'van-gogh-color':
                case 'van-gogh':
                        return kabel_layout_van_gogh_color();
                case 'macaron-colors':
                case 'macaron':
                        return kabel_layout_macaron_colors();
                case 'museum-collection-colors':
                case 'museum':
                        return kabel_layout_museum_collection_colors();
                case 'projects':
                        return kabel_layout_projects();
                case 'project-procurement':
                case 'procurement':
                case 'services': // Legacy bootstrap key → nearest Kabel page.
                        return kabel_layout_project_procurement();
                case 'customize':
                case 'solutions': // Legacy bootstrap key → nearest Kabel page.
                        return kabel_layout_customize();
                case 'distributor':
                        return kabel_layout_distributor();
                case 'blog':
                        return kabel_layout_blog();
                case 'contact':
                        return kabel_layout_contact();
                case 'faq':
                        return kabel_layout_faq();
                case 'landing':
                        return kabel_layout_landing();
                case 'starter':
                case 'generic':
                        return kabel_layout_starter();
        }
        return null;
}

/**
 * Guess the best layout for a page (front page / template / slug).
 */
function kabel_match_page_layout( $post ): string {
        $post = get_post( $post );
        if ( ! $post instanceof WP_Post ) {
                return 'starter';
        }

        if ( (int) get_option( 'page_on_front' ) === $post->ID ) {
                return 'home';
        }

        $template = (string) get_post_meta( $post->ID, '_wp_page_template', true );
        if ( 'default' !== $template && '' !== $template ) {
                return ''; // Custom template — leave the page alone.
        }

        switch ( $post->post_name ) {
                case 'home':
                        return 'home';
                case 'about':
                case 'about-us':
                        return 'about';
                case 'eco-friendly':
                        return 'eco-friendly';
                case 'designer':
                        return 'designer';
                case 'products':
                        return 'products';
                case 'morandi-colors':
                        return 'morandi-colors';
                case 'van-gogh-color':
                        return 'van-gogh-color';
                case 'macaron-colors':
                        return 'macaron-colors';
                case 'museum-collection-colors':
                        return 'museum-collection-colors';
                case 'projects':
                        return 'projects';
                case 'project-procurement':
                        return 'project-procurement';
                case 'customize':
                        return 'customize';
                case 'distributor':
                        return 'distributor';
                case 'blog':
                        return 'blog';
                case 'contact':
                case 'contact-us':
                        return 'contact';
                case 'faq':
                        return 'faq';
                case 'landing':
                        return 'landing';
        }

        return 'starter';
}
