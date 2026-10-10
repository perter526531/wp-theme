<?php
/**
 * Site bootstrap — turn a fresh WordPress into the complete Kabel site.
 *
 * Why this exists: the seeder only fills pages that ALREADY exist. A brand-new
 * WordPress has no About / Colors / Contact pages at all, so the nav links
 * 404 and the owner sees "an empty site". This module:
 *
 *   1. Auto-runs once on a truly fresh site (no Kabel pages, hardly any
 *      content): creates the fourteen blueprint pages pre-filled with their
 *      block layouts, the six paint families, three demo articles, two demo
 *      projects, the taxonomy terms, the static front page + posts page, and
 *      the navigation menus.
 *   2. On sites that already have content, a standing admin notice offers
 *      the same in one click — it only ever creates MISSING content and
 *      never overwrites anything.
 *
 * Images: every media reference is resolved through kabel_bootstrap_media_id().
 * It first re-uses an attachment that already exists in the library (exact
 * original upload path, then file name — the "migrated from the old site"
 * case) and only then side-loads the compressed webp copy the theme ships
 * in sample-assets/. Layouts reference images with "kbl-media:<path>" string
 * tokens inside image attributes; kbl_resolve_seed_media() swaps those
 * tokens for real attachment IDs right after seeding.
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * The page blueprint: what a complete Kabel site contains.
 *
 * The full site map is nineteen URLs: these fourteen pages plus the
 * /products/ and /projects/ archives and the three news categories
 * (company-news / industry-news / knowledge), which the terms + CPT
 * registrations below provide.
 *
 * @return array<string, array{title:string, layout:?string, template:string}>
 */
function kbl_bootstrap_blueprint(): array {
        return array(
                'home'                       => array(
                        'title'    => __( 'Home', 'kabel-blocks' ),
                        'layout'   => 'home',
                        'template' => 'default',
                ),
                'about'                      => array(
                        'title'    => __( 'About Us', 'kabel-blocks' ),
                        'layout'   => 'about',
                        'template' => 'default',
                ),
                'eco-friendly'               => array(
                        'title'    => __( 'ECO Friendly', 'kabel-blocks' ),
                        'layout'   => 'eco-friendly',
                        'template' => 'default',
                ),
                'designer'                   => array(
                        'title'    => __( 'Designer', 'kabel-blocks' ),
                        'layout'   => 'designer',
                        'template' => 'default',
                ),
                'morandi-colors'             => array(
                        'title'    => __( 'Morandi Colors', 'kabel-blocks' ),
                        'layout'   => 'morandi-colors',
                        'template' => 'default',
                ),
                'van-gogh-color'             => array(
                        'title'    => __( 'Van Gogh Color', 'kabel-blocks' ),
                        'layout'   => 'van-gogh-color',
                        'template' => 'default',
                ),
                'macaron-colors'             => array(
                        'title'    => __( 'Macaron colors', 'kabel-blocks' ),
                        'layout'   => 'macaron-colors',
                        'template' => 'default',
                ),
                'museum-collection-colors'   => array(
                        'title'    => __( 'Museum Collection Colors', 'kabel-blocks' ),
                        'layout'   => 'museum-collection-colors',
                        'template' => 'default',
                ),
                'project-procurement'        => array(
                        'title'    => __( 'Project Procurement', 'kabel-blocks' ),
                        'layout'   => 'project-procurement',
                        'template' => 'default',
                ),
                'customize'                  => array(
                        'title'    => __( 'Customize', 'kabel-blocks' ),
                        'layout'   => 'customize',
                        'template' => 'default',
                ),
                'distributor'                => array(
                        'title'    => __( 'DISTRIBUTOR', 'kabel-blocks' ),
                        'layout'   => 'distributor',
                        'template' => 'default',
                ),
                'blog'                       => array(
                        'title'    => __( 'Blog', 'kabel-blocks' ),
                        'layout'   => null, // Posts page — WordPress ignores its content.
                        'template' => 'default',
                ),
                'faq'                        => array(
                        'title'    => __( 'Faq', 'kabel-blocks' ),
                        'layout'   => 'faq',
                        'template' => 'default',
                ),
                'contact'                    => array(
                        'title'    => __( 'CONTACT', 'kabel-blocks' ),
                        'layout'   => 'contact',
                        'template' => 'default',
                ),
        );
}

/**
 * Does a published page with this slug exist?
 */
function kbl_page_exists( string $slug ): bool {
        $page = get_page_by_path( $slug );
        return $page instanceof WP_Post && 'publish' === $page->post_status;
}

/**
 * Which blueprint pages are missing?
 *
 * @return array<string, string> slug => title
 */
function kbl_bootstrap_missing(): array {
        $missing = array();
        foreach ( kbl_bootstrap_blueprint() as $slug => $page ) {
                if ( ! kbl_page_exists( $slug ) ) {
                        $missing[ $slug ] = $page['title'];
                }
        }
        return $missing;
}

/**
 * Site state: 'none' (no Kabel pages at all), 'partial' or 'complete'.
 */
function kbl_bootstrap_state(): string {
        $missing = kbl_bootstrap_missing();
        if ( array() === $missing ) {
                return 'complete';
        }
        return count( $missing ) === count( kbl_bootstrap_blueprint() ) ? 'none' : 'partial';
}

/**
 * Is this a brand-new site where auto-bootstrap is safe?
 *
 * No Kabel pages at all, at most two published pages (the WordPress
 * "Sample Page" era), and we have never bootstrapped here.
 */
function kbl_site_is_fresh(): bool {
        if ( get_option( 'kabel_bootstrapped' ) ) {
                return false;
        }
        if ( 'none' !== kbl_bootstrap_state() ) {
                return false;
        }
        $pages = get_pages( array(
                'post_status' => 'publish',
                'number'      => 3,
        ) );
        return count( $pages ) <= 2;
}

/* ---------------------------------------------------------------------
 * Media — re-use the existing library, else side-load the shipped asset
 * ------------------------------------------------------------------- */

/**
 * The sample-assets manifest: original upload path => local webp file name.
 *
 * Lives in the Kabel theme (sample-assets/manifest.json); absent when the
 * plugin runs standalone, in which case media resolution simply yields 0.
 *
 * @return array<string, string>
 */
function kbl_sample_manifest(): array {
        static $map = null;
        if ( null !== $map ) {
                return $map;
        }

        $map  = array();
        $base = defined( 'KABEL_DIR' ) ? KABEL_DIR : get_template_directory();
        $file = $base . '/sample-assets/manifest.json';

        if ( is_readable( $file ) ) {
                // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- checked below.
                $decoded = json_decode( (string) @file_get_contents( $file ), true );
                if ( is_array( $decoded ) ) {
                        foreach ( $decoded as $entry ) {
                                if ( ! empty( $entry['orig'] ) && ! empty( $entry['file'] ) ) {
                                        $map[ ltrim( (string) $entry['orig'], '/' ) ] = (string) $entry['file'];
                                }
                        }
                }
        }

        return $map;
}

/**
 * Resolve an original-site image reference to a library attachment ID.
 *
 * Order: (1) an asset this bootstrap already side-loaded, (2) an attachment
 * with the exact original `_wp_attached_file` path (site migrated from the
 * previous host), (3) an attachment with the same file name anywhere in the
 * uploads tree, (4) side-load the compressed webp shipped in the theme's
 * sample-assets/ folder. Returns 0 when nothing can be resolved.
 *
 * @param string $orig Original upload path, e.g. "2026/08/Velvet-Texture-1-1.webp".
 * @return int Attachment ID (0 = not available).
 */
function kabel_bootstrap_media_id( string $orig ): int {
        static $cache = array();

        $orig = ltrim( str_replace( '\\/', '/', $orig ), '/' );
        if ( '' === $orig ) {
                return 0;
        }
        if ( isset( $cache[ $orig ] ) ) {
                return $cache[ $orig ];
        }

        global $wpdb;

        /* 1. Previously side-loaded by a bootstrap run. */
        $id = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT p.ID FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
                 WHERE p.post_type = 'attachment' AND m.meta_key = '_kbl_sample_orig' AND m.meta_value = %s
                 ORDER BY p.ID ASC LIMIT 1",
                $orig
        ) );

        /* 2. Migrated site: the attachment kept its original upload path. */
        if ( ! $id ) {
                $id = (int) $wpdb->get_var( $wpdb->prepare(
                        "SELECT p.ID FROM {$wpdb->posts} p
                         INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
                         WHERE p.post_type = 'attachment' AND m.meta_key = '_wp_attached_file' AND m.meta_value = %s
                         ORDER BY p.ID ASC LIMIT 1",
                        $orig
                ) );
        }

        /* 3. Same file name anywhere in the library (upload folder may differ). */
        if ( ! $id ) {
                $id = (int) $wpdb->get_var( $wpdb->prepare(
                        "SELECT p.ID FROM {$wpdb->posts} p
                         INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
                         WHERE p.post_type = 'attachment' AND m.meta_key = '_wp_attached_file' AND m.meta_value LIKE %s
                         ORDER BY p.ID ASC LIMIT 1",
                        '%' . $wpdb->esc_like( '/' . wp_basename( $orig ) )
                ) );
        }

        /* 4. Nothing in the library — side-load the theme's compressed copy. */
        if ( ! $id ) {
                $id = kbl_sideload_sample_asset( $orig );
        }

        $cache[ $orig ] = $id;
        return $id;
}

/**
 * Side-load one file from the theme's sample-assets folder.
 *
 * @param string $orig Original upload path (manifest key).
 * @return int Attachment ID (0 on failure).
 */
function kbl_sideload_sample_asset( string $orig ): int {
        $manifest = kbl_sample_manifest();
        $file     = $manifest[ $orig ] ?? '';
        if ( '' === $file ) {
                return 0;
        }

        $base = defined( 'KABEL_DIR' ) ? KABEL_DIR : get_template_directory();
        $path = $base . '/sample-assets/' . $file;
        if ( ! is_readable( $path ) ) {
                return 0;
        }

        if ( ! function_exists( 'media_handle_sideload' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                require_once ABSPATH . 'wp-admin/includes/media.php';
                require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        /* Copy first — media_handle_sideload MOVES the temp file. */
        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- checked below.
        $temp = wp_tempnam( $file );
        if ( ! $temp || false === @copy( $path, $temp ) ) {
                if ( $temp ) {
                        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- best effort.
                        @unlink( $temp );
                }
                return 0;
        }

        $title = ucwords( str_replace( array( '-', '_' ), ' ', (string) pathinfo( $file, PATHINFO_FILENAME ) ) );

        /*
         * Speed: the sample assets ship pre-compressed webp — generating every
         * intermediate size for 76+ files is what blew the 30s budget on shared
         * hosting. Keep only the admin-grid thumbnail; front-end falls back to
         * the original file when a size is missing (they are already web-optimised).
         */
        $kbl_trim_sizes = static function ( $sizes ) {
                if ( is_array( $sizes ) && isset( $sizes['thumbnail'] ) ) {
                        return array( 'thumbnail' => $sizes['thumbnail'] );
                }
                return $sizes;
        };
        add_filter( 'intermediate_image_sizes_advanced', $kbl_trim_sizes );
        $attach_id = media_handle_sideload(
                array(
                        'name'     => $file,
                        'tmp_name' => $temp,
                ),
                0,
                $title
        );
        remove_filter( 'intermediate_image_sizes_advanced', $kbl_trim_sizes );

        if ( is_wp_error( $attach_id ) ) {
                return 0;
        }

        update_post_meta( (int) $attach_id, '_kbl_sample_orig', $orig );
        return (int) $attach_id;
}

/**
 * Replace the "kbl-media:<path>" tokens a seeded layout carries with real
 * attachment IDs.
 *
 * Layouts express images as string tokens inside numeric image attributes
 * (image, bg, images[]...). After this pass the stored markup carries plain
 * integers, so the editor sees clean values. Unresolvable tokens become 0
 * (blocks fall back to their no-image arrangement).
 *
 * @param int $post_id Freshly seeded page.
 * @return int Number of tokens replaced.
 */
function kbl_resolve_seed_media( int $post_id ): int {
        $post = get_post( $post_id );
        if ( ! $post instanceof WP_Post || '' === trim( (string) $post->post_content ) ) {
                return 0;
        }
        if ( false === strpos( (string) $post->post_content, 'kbl-media:' ) ) {
                return 0;
        }

        $count   = 0;
        $content = preg_replace_callback(
                '/"kbl-media:([^"]+)"/',
                static function ( array $m ) use ( &$count ): string {
                        $count++;
                        return (string) kabel_bootstrap_media_id( $m[1] );
                },
                (string) $post->post_content
        );

        if ( null === $content || 0 === $count ) {
                return 0;
        }

        /* Same importer-style write as kabel_seed_page() — skip content filters. */
        global $wpdb;
        $wpdb->update(
                $wpdb->posts,
                array(
                        'post_content'      => $content,
                        'post_modified'     => current_time( 'mysql' ),
                        'post_modified_gmt' => current_time( 'mysql', true ),
                ),
                array( 'ID' => $post_id )
        );
        clean_post_cache( $post_id );

        return $count;
}

/* ---------------------------------------------------------------------
 * Taxonomy terms
 * ------------------------------------------------------------------- */

/**
 * The six paint families + the five content categories.
 *
 * @return array<string, string> map of "taxonomy:slug" => term ID (existing or fresh).
 */
function kbl_bootstrap_terms(): array {
        $map = array();

        $taxonomies = array(
                'product_series' => array(
                        'velvet-texture' => array( __( 'Velvet Texture', 'kabel-blocks' ), __( 'Luxury velvet matte finish.', 'kabel-blocks' ) ),
                        'lime-plaster'   => array( __( 'Lime Plaster', 'kabel-blocks' ), __( 'Breathable mineral plaster.', 'kabel-blocks' ) ),
                        'stone-texture'  => array( __( 'Stone Texture', 'kabel-blocks' ), __( 'Natural stone character, hand-troweled.', 'kabel-blocks' ) ),
                        'solid-color'    => array( __( 'Solid Color', 'kabel-blocks' ), __( 'Skin-feeling solid colour with formaldehyde-purifying technology.', 'kabel-blocks' ) ),
                        'microcement'    => array( __( 'Microcement', 'kabel-blocks' ), __( 'Seamless wall & floor integration.', 'kabel-blocks' ) ),
                        'exterior-paint' => array( __( 'Exterior Paint', 'kabel-blocks' ), __( 'Weatherproof colour for exterior walls.', 'kabel-blocks' ) ),
                ),
                'category'       => array(
                        'company-news'             => array( __( 'Company News', 'kabel-blocks' ), __( 'Showroom openings, brand milestones and company announcements.', 'kabel-blocks' ) ),
                        'industry-news'            => array( __( 'Industry News', 'kabel-blocks' ), __( 'Design trends and updates from the decorative-coatings world.', 'kabel-blocks' ) ),
                        'knowledge'                => array( __( 'Knowledge', 'kabel-blocks' ), __( 'Plain-language paint knowledge and application guidance.', 'kabel-blocks' ) ),
                        'home-renovation-projects' => array( __( 'Home Renovation Projects', 'kabel-blocks' ), __( 'Residential references finished with Kabel coatings.', 'kabel-blocks' ) ),
                        'engineering-projects'     => array( __( 'Engineering Projects', 'kabel-blocks' ), __( 'Commercial and engineering references finished with Kabel coatings.', 'kabel-blocks' ) ),
                ),
        );

        foreach ( $taxonomies as $taxonomy => $terms ) {
                if ( ! taxonomy_exists( $taxonomy ) ) {
                        continue;
                }
                foreach ( $terms as $slug => $data ) {
                        $existing = term_exists( $slug, $taxonomy );
                        if ( is_array( $existing ) && ! empty( $existing['term_id'] ) ) {
                                $map[ $taxonomy . ':' . $slug ] = (int) $existing['term_id'];
                                continue;
                        }

                        $created = wp_insert_term( $data[0], $taxonomy, array(
                                'slug'        => $slug,
                                'description' => $data[1],
                        ) );
                        if ( is_wp_error( $created ) ) {
                                continue;
                        }
                        $map[ $taxonomy . ':' . $slug ] = (int) $created['term_id'];
                }
        }

        return $map;
}

/* ---------------------------------------------------------------------
 * Demo content — the six paint families (product CPT)
 * ------------------------------------------------------------------- */

/**
 * The six Kabel paint families, one product entry each.
 *
 * Image references are original upload paths (kabel_bootstrap_media_id
 * resolves them). Spec values are typical decorative-coating parameters;
 * no certification numbers are claimed.
 *
 * @return array<int, array<string, mixed>>
 */
function kbl_bootstrap_products(): array {
        return array(
                array(
                        'slug'     => 'velvet-texture',
                        'title'    => 'Velvet Texture',
                        'series'   => 'velvet-texture',
                        'order'    => 1,
                        'excerpt'  => 'Luxury velvet matte finish with a soft, light-absorbing depth.',
                        'tagline'  => 'Luxury velvet matte finish',
                        'content'  => "<p>Velvet Texture is Kabel's signature luxury matte finish. Built up through six craftsmanship passes, the film forms a fine velvet nap that absorbs light instead of reflecting it — walls take on the quiet depth of draped cloth rather than painted plaster.</p><p>The finish pairs naturally with the Morandi and Museum Collection colour cards, and its scrub-resistant film stands up to daily life in living rooms, bedrooms, boutique hotels and showrooms.</p>",
                        'bullets'  => "Velvet-soft matte touch\nLight-absorbing colour depth\nScrub-resistant, washable film\nLow-odour water-based formula",
                        'specs'    => array(
                                array( 'group' => 'Base', 'name' => 'Binder', 'value' => 'Pure acrylic emulsion', 'unit' => '' ),
                                array( 'group' => 'Base', 'name' => 'Dilution', 'value' => 'Clean water, up to 10', 'unit' => '%' ),
                                array( 'group' => 'Application', 'name' => 'Application temperature', 'value' => '10–35', 'unit' => '°C' ),
                                array( 'group' => 'Application', 'name' => 'Recoat interval', 'value' => '4–6', 'unit' => 'h' ),
                                array( 'group' => 'Application', 'name' => 'Theoretical coverage', 'value' => '8–10', 'unit' => 'm²/kg' ),
                                array( 'group' => 'Application', 'name' => 'Recommended coats', 'value' => '2–3', 'unit' => '' ),
                                array( 'group' => 'Finish', 'name' => 'Sheen', 'value' => 'Velvet matte', 'unit' => '' ),
                        ),
                        'featured' => '2026/08/Velvet-Texture-1-1.webp',
                        'gallery'  => array( '2026/08/Velvet-Texture-1-1.webp', '2026/08/都灵雅绒1-scaled.jpg' ),
                ),
                array(
                        'slug'     => 'lime-plaster',
                        'title'    => 'Lime Plaster',
                        'series'   => 'lime-plaster',
                        'order'    => 2,
                        'excerpt'  => 'Breathable mineral plaster with the character of Italian stucco.',
                        'tagline'  => 'Breathable mineral plaster',
                        'content'  => "<p>Lime Plaster carries the mineral character of traditional Italian stucco into modern interiors. Air-slaked lime and natural aggregates create a breathable film that lets walls exchange moisture — the quiet mechanics behind healthy, comfortable rooms.</p><p>Its naturally alkaline surface discourages mould, while soft cloud-like tonal shifts follow the daylight across the wall. A natural fit for feature walls, living spaces and hospitality projects.</p>",
                        'bullets'  => "Naturally breathable mineral film\nSoft, cloud-like tonal depth\nAlkaline surface discourages mould\nSuited to feature walls & hospitality",
                        'specs'    => array(
                                array( 'group' => 'Base', 'name' => 'Binder', 'value' => 'Air-slaked lime with mineral aggregates', 'unit' => '' ),
                                array( 'group' => 'Base', 'name' => 'Dilution', 'value' => 'Ready to use', 'unit' => '' ),
                                array( 'group' => 'Application', 'name' => 'Application temperature', 'value' => '10–35', 'unit' => '°C' ),
                                array( 'group' => 'Application', 'name' => 'Recoat interval', 'value' => '6–12', 'unit' => 'h' ),
                                array( 'group' => 'Application', 'name' => 'Theoretical coverage', 'value' => '0.8–1.2', 'unit' => 'kg/m²' ),
                                array( 'group' => 'Environment', 'name' => 'VOC', 'value' => '≤ 20', 'unit' => 'g/L' ),
                        ),
                        'featured' => '2026/08/Lime-Plaster-1-1.webp',
                        'gallery'  => array( '2026/08/Lime-Plaster-1-1.webp', '2026/08/ltalian-stucco-scaled.jpg', 'slider/cache/7a295eafd0c7a37fbaf2c3f35aca1d3c/ltalian-stucco-scaled.jpg' ),
                ),
                array(
                        'slug'     => 'stone-texture',
                        'title'    => 'Stone Texture',
                        'series'   => 'stone-texture',
                        'order'    => 3,
                        'excerpt'  => 'Hand-troweled texture with the character of natural stone.',
                        'tagline'  => 'Natural stone character, hand-troweled',
                        'content'  => "<p>Stone Texture brings the weight and grain of natural stone to the wall. Mineral fillers are worked by trowel, so every surface keeps the honest variation of the craftsman's pass — no two walls read exactly alike.</p><p>The matte mineral surface sits comfortably beside timber, brass and raw concrete, making it a favourite for entrance walls, villa living rooms and boutique commercial spaces.</p>",
                        'bullets'  => "Natural stone grain & depth\nHand-troweled, one-of-a-kind surface\nMineral matte finish\nPairs with timber, brass & concrete",
                        'specs'    => array(
                                array( 'group' => 'Base', 'name' => 'Binder', 'value' => 'Acrylic copolymer with natural mineral fillers', 'unit' => '' ),
                                array( 'group' => 'Base', 'name' => 'Dilution', 'value' => 'Clean water, up to 5', 'unit' => '%' ),
                                array( 'group' => 'Application', 'name' => 'Application temperature', 'value' => '10–35', 'unit' => '°C' ),
                                array( 'group' => 'Application', 'name' => 'Recoat interval', 'value' => '4–6', 'unit' => 'h' ),
                                array( 'group' => 'Application', 'name' => 'Theoretical coverage', 'value' => '6–8', 'unit' => 'm²/kg' ),
                                array( 'group' => 'Environment', 'name' => 'VOC', 'value' => '≤ 50', 'unit' => 'g/L' ),
                        ),
                        'featured' => '2026/08/Stone-texture-1-1.webp',
                        'gallery'  => array( '2026/08/Stone-texture-1-1.webp', '2026/08/Stone-texture-1.webp' ),
                ),
                array(
                        'slug'     => 'solid-color',
                        'title'    => 'Solid Color',
                        'series'   => 'solid-color',
                        'order'    => 4,
                        'excerpt'  => 'Skin-feeling solid colour with formaldehyde-purifying technology.',
                        'tagline'  => 'Skin-feeling solid colour with formaldehyde-purifying technology',
                        'content'  => "<p>Solid Color is the skin-feeling face of Kabel's formaldehyde-purifying line. The aqueous film feels smooth and warm to the touch — the reason it is often described as a \"skin-feeling\" paint — while covering walls in clean, full colour.</p><p>Underneath the comfort sits Kabel's independently developed formaldehyde-degradation technology: Formaldehyde → Water. It doesn't just cover — it actively purifies, achieving a formaldehyde purification rate of 94.1% and a long-term purification rate of 82.1%.</p>",
                        'bullets'  => "Skin-soft, full-colour film\nFormaldehyde → Water purification technology\nFormaldehyde purification rate 94.1%\nLong-term purification rate 82.1%",
                        'specs'    => array(
                                array( 'group' => 'Base', 'name' => 'Binder', 'value' => 'Aqueous acrylic emulsion', 'unit' => '' ),
                                array( 'group' => 'Base', 'name' => 'Dilution', 'value' => 'Clean water, up to 20', 'unit' => '%' ),
                                array( 'group' => 'Application', 'name' => 'Application temperature', 'value' => '5–35', 'unit' => '°C' ),
                                array( 'group' => 'Application', 'name' => 'Recoat interval', 'value' => '2–4', 'unit' => 'h' ),
                                array( 'group' => 'Application', 'name' => 'Theoretical coverage', 'value' => '10–12', 'unit' => 'm²/kg' ),
                                array( 'group' => 'Environment', 'name' => 'VOC', 'value' => '≤ 30', 'unit' => 'g/L' ),
                                array( 'group' => 'Purification', 'name' => 'Formaldehyde purification rate', 'value' => '94.1', 'unit' => '%' ),
                                array( 'group' => 'Purification', 'name' => 'Long-term purification rate', 'value' => '82.1', 'unit' => '%' ),
                        ),
                        'featured' => '2026/08/Skin-feeling-paint-1-1.webp',
                        'gallery'  => array( '2026/08/Skin-feeling-paint-1-1.webp', '2026/08/净醛科莫雅绒1-scaled.jpg', '2026/08/净醛科莫雅绒2-scaled.jpg' ),
                ),
                array(
                        'slug'     => 'microcement',
                        'title'    => 'Microcement',
                        'series'   => 'microcement',
                        'order'    => 5,
                        'excerpt'  => 'Seamless wall & floor integration with waterproof performance.',
                        'tagline'  => 'Seamless wall & floor integration',
                        'content'  => "<p>Microcement is Kabel's answer to the seamless interior: a cementitious finish that flows from wall to floor without joints or transitions. The look is raw and contemporary; the performance is quietly practical.</p><p>Its waterproof decorative layer resists the splashes and humidity of kitchens, bathrooms and entrance walls, keeping the elegant concrete-inspired texture for years with simple maintenance.</p>",
                        'bullets'  => "Seamless walls and floors in one finish\nMoisture-resistant decorative layer\nCement-inspired handcrafted texture\nIdeal for kitchens, bathrooms & feature walls",
                        'specs'    => array(
                                array( 'group' => 'Base', 'name' => 'Binder', 'value' => 'Cementitious mineral binder with polymer modifiers', 'unit' => '' ),
                                array( 'group' => 'Base', 'name' => 'Dilution', 'value' => 'Ready to use', 'unit' => '' ),
                                array( 'group' => 'Application', 'name' => 'Application temperature', 'value' => '10–35', 'unit' => '°C' ),
                                array( 'group' => 'Application', 'name' => 'Recoat interval', 'value' => '6–8', 'unit' => 'h' ),
                                array( 'group' => 'Application', 'name' => 'Theoretical coverage', 'value' => '1.2–1.6', 'unit' => 'kg/m²' ),
                                array( 'group' => 'Environment', 'name' => 'VOC', 'value' => '≤ 20', 'unit' => 'g/L' ),
                        ),
                        'featured' => '2026/08/Microcement-1-1.webp',
                        'gallery'  => array( '2026/08/Microcement-1-1.webp', '2026/08/concrete-12-1-scaled.jpg', '2026/08/Microcement-02.jpg' ),
                ),
                array(
                        'slug'     => 'exterior-paint',
                        'title'    => 'Exterior Paint',
                        'series'   => 'exterior-paint',
                        'order'    => 6,
                        'excerpt'  => 'Weatherproof colour for exterior walls.',
                        'tagline'  => 'Weatherproof colour for exterior walls',
                        'content'  => "<p>Exterior Paint takes Kabel colour outside. A weather-resistant acrylic–silicone film holds its hue under strong sun and shrugs off rain, while staying breathable so moisture never gets trapped in the wall.</p><p>From villa façades to coastal homes and garden architecture, the finish keeps facades clean and vivid with minimal repainting.</p>",
                        'bullets'  => "UV-stable colour retention\nBreathable, rain-resistant film\nResists alkali efflorescence\nFor villas, façades & coastal homes",
                        'specs'    => array(
                                array( 'group' => 'Base', 'name' => 'Binder', 'value' => 'Weather-resistant acrylic–silicone emulsion', 'unit' => '' ),
                                array( 'group' => 'Base', 'name' => 'Dilution', 'value' => 'Clean water, up to 10', 'unit' => '%' ),
                                array( 'group' => 'Application', 'name' => 'Application temperature', 'value' => '5–35', 'unit' => '°C' ),
                                array( 'group' => 'Application', 'name' => 'Recoat interval', 'value' => '4–6', 'unit' => 'h' ),
                                array( 'group' => 'Application', 'name' => 'Theoretical coverage', 'value' => '8–10', 'unit' => 'm²/kg' ),
                                array( 'group' => 'Environment', 'name' => 'VOC', 'value' => '≤ 50', 'unit' => 'g/L' ),
                        ),
                        'featured' => '2026/08/布艺感1-1-1.webp',
                        'gallery'  => array( '2026/08/布艺感1-1-1.webp', '2026/08/sea-house-exterior-wall-paint.jpg' ),
                ),
        );
}

/* ---------------------------------------------------------------------
 * Demo content — articles + project references
 * ------------------------------------------------------------------- */

/**
 * The three live articles, verbatim from the Kabel newsroom
 * (titles, slugs, categories and body text as published).
 *
 * @return array<int, array<string, mixed>>
 */
function kbl_bootstrap_posts(): array {
/**
                 * The three live articles, verbatim from the Kabel newsroom
                 * (titles, slugs, categories and body text as published).
                 */
                $posts = array(
                        array(
                                'title'    => 'KABEL Redefines the Fifth Wall: Bringing Color and Materiality Beyond the Walls',
                                'slug'     => 'kabel-redefines-the-fifth-wall-bringing-color-and-materiality-beyond-the-walls',
                                'category' => 'industry-news',
                                'date'     => '2026-09-01 09:00:00',
                                'excerpt'  => 'For decades, interior design has focused heavily on walls as the primary decorative surface.',
                                'featured' => '2026/09/a002f786-bb99-47e9-b012-1b45031127f9.webp',
                                'blocks'   => array(
                                        array( 'img', '2026/09/a002f786-bb99-47e9-b012-1b45031127f9.webp' ),
                                        array( 'p', 'For decades, interior design has focused heavily on walls as the primary decorative surface.' ),
                                        array( 'p', 'But a new approach is emerging: designers are increasingly treating the ceiling as the “fifth wall”, using color, texture and decorative finishes to create more immersive environments. Recent design coverage highlights color-drenching, decorative ceilings and material treatments as ways to create a more cohesive architectural experience.' ),
                                        array( 'p', 'KABEL believes this concept opens new possibilities for decorative coatings.' ),
                                        array( 'h3', 'Creating a Continuous Visual Experience' ),
                                        array( 'p', 'Instead of treating walls, ceilings and architectural details as separate elements, designers can use coordinated colors and textures to visually connect different surfaces.' ),
                                        array( 'p', 'A warm mineral tone can extend from the wall onto the ceiling.' ),
                                        array( 'p', 'A subtle texture can continue through an architectural niche.' ),
                                        array( 'p', 'A matte finish can soften the transition between different surfaces.' ),
                                        array( 'p', 'This creates a more immersive and unified environment.' ),
                                        array( 'h3', 'Designed for Different Architectural Spaces' ),
                                        array( 'p', 'This approach can be particularly effective in:' ),
                                        array( 'ul',
                                                array( 'li', 'Luxury Villas', 'Creating calm, enveloping residential environments.' ),
                                                array( 'li', 'Boutique Hotels', 'Building memorable guest experiences through color and texture.' ),
                                                array( 'li', 'Designer Showrooms', 'Using walls and ceilings as part of the brand experience.' ),
                                                array( 'li', 'Restaurants &amp; Hospitality Spaces', 'Creating distinctive atmospheres through material and lighting.' ),
                                                array( 'li', 'Premium Commercial Interiors', 'Developing cohesive architectural identities.' ),
                                        ),
                                        array( 'h3', 'The Surface Becomes the Architecture' ),
                                        array( 'p', 'For KABEL, decorative coating is no longer simply the final step of interior construction.' ),
                                        array( 'p', 'It can become part of the architectural design itself.' ),
                                        array( 'p', 'Through color, texture, light interaction and material expression, KABEL aims to give architects and designers more freedom to create spaces that feel complete from every angle.' ),
                                        array( 'p', 'KABEL — Beyond the Wall. Into the Space.' ),
                                ),
                        ),
                        array(
                                'title'    => 'Kabel Decorative Coatings: First Singapore Showroom Officially Unveiled',
                                'slug'     => 'kabel-decorative-coatings-first-singapore-showroom-officially-unveiled',
                                'category' => 'company-news',
                                'date'     => '2026-09-01 09:00:00',
                                'excerpt'  => 'Singapore, a vibrant land where Eastern and Western cultures converge, holds the colourful heritage of Nanyang shophouses alongside the modern skyline of Marina Bay.',
                                'featured' => '2026/09/212ff1d1-ce45-4eab-bad3-355c55011453.webp',
                                'blocks'   => array(
                                        array( 'img', '2026/09/212ff1d1-ce45-4eab-bad3-355c55011453.webp' ),
                                        array( 'p', 'Singapore, a vibrant land where Eastern and Western cultures converge, holds the colourful heritage of Nanyang shophouses alongside the modern skyline of Marina Bay. It embraces profound traditions of the Chinese community while resonating with cutting-edge global trends. As Kabel Decorative Coatings sets foot in this culturally diverse territory for the first time, a dialogue spanning space, culture and art begins.' ),
                                        array( 'p', 'July 2026 — Multicultural Singapore gains a new artistic landmark. Kabel Decorative Coatings opens its first premium custom showroom in Singapore.' ),
                                        array( 'p', 'After Hong Kong, China and Taiwan, China, this is a key milestone for Kabel’s overseas development, accelerating the brand’s global expansion.' ),
                                        array( 'p', 'Local designers, partners and guests came together for the grand opening. Festive lion &amp; dragon dances, lively drum performances and the ribbon-cutting ceremony filled the day with joy and good fortune.' ),
                                        array( 'p', 'Trendy mascot interactions and refreshments united modern style and Nanyang culture, offering guests a comfortable space for communication.' ),
                                        array( 'p', 'Kabel Premium Custom Showroom in Singapore showcases Kabel’s full product portfolio. Through full-scale real sample feature walls, it intuitively demonstrates the outstanding performance of its products in colour, texture and tactility.' ),
                                        array( 'p', 'During the tour and exchanges, emerging local designers noted that the rich textures and eco-friendly properties of Kabel Decorative Coatings align perfectly with the demand for personalised and sustainable design in today’s high-end residential and commercial spaces, bringing brand-new creative inspiration to the local market.' ),
                                        array( 'p', 'Anchor in the Lion City: Kabel’s Strategic Pivot for Global Expansion' ),
                                        array( 'p', 'Why Singapore? As the commercial and design heart of Southeast Asia, the city-state is home to high-end residences, international hotels and boutique commercial spaces, sustaining robust demand for premium decorative coatings with superior quality and aesthetic appeal.' ),
                                        array( 'p', 'Meanwhile, Singapore’s stringent environmental regulations and strict market access requirements make it a natural litmus test. Only brands with comprehensive strength can gain a foothold here. By establishing its first flagship showroom in Singapore for global deployment, Kabel precisely targets the nation’s strategic value — acting as a hub covering Southeast Asia and paving the way for future expansion into European and American markets.' ),
                                        array( 'p', 'From its strategic hub in Singapore, Kabel aims to share its vision of “Art and Life” with more global consumers, enabling the aesthetic expression of a Chinese brand to reach the world.' ),
                                        array( 'p', 'Solid Foundation: Dual Endorsement of Technology and Aesthetics' ),
                                        array( 'quote', '“The essence of going global is not merely shipping containers of goods, but the transplantation of brand value and operational systems,”', 'Wang Hui, President of Kabel' ),
                                        array( 'p', 'Guided by this philosophy, Kabel adopts systematic output for overseas expansion — fully rolling out its brand philosophy, service standards and operational methodologies.' ),
                                        array( 'p', 'At present, three independent overseas departments covering business, marketing and customer service are in full operation, delivering end-to-end support ranging from product supply to after-sales service for international markets.' ),
                                        array( 'p', 'Hard Power Backing Kabel’s Overseas Operation System: Technology and Aesthetics' ),
                                        array( 'p', 'Kabel’s overseas operation system is underpinned by robust strength in both technology and aesthetics.' ),
                                        array( 'p', 'In terms of environmental performance, Kabel’s self-developed patented “Formaldehyde-to-Water” technology achieves a formaldehyde removal rate of 94.1% and a long-term purification rate of 82.1%. The brand also conducts joint R&amp;D of high-efficiency formaldehyde-purifying formulas together with a Nobel Laureate in Chemistry and Fortune Global 500 enterprises including BASF and Dow. Its products have obtained authoritative international certifications such as French A+, GREENGUARD Gold, Finnish M1 and EU REACH.' ),
                                        array( 'p', 'On the aesthetic front, Kabel has collaborated with Qiu Qingnian, inheritor of the intangible cultural heritage of traditional Chinese painting pigments, to launch the Heritage Collection. The line extracts classic Chinese mineral pigments including malachite green and azurite blue from A Thousand Li of Rivers and Mountains and Dunhuang Murals. In partnership with pattern specialist Chen Zimo, Kabel transforms traditional ethnic patterns into contemporary wall motifs. Furthermore, its products have earned prestigious international awards including the MUSE Design Awards Gold, French Design Awards Gold and London Design Awards.' ),
                                        array( 'p', 'Such formidable capabilities form the solid foundation for a Chinese decorative coatings brand to march onto the global stage.' ),
                                        array( 'p', 'From Hong Kong, China and Taiwan, China to the newly opened showroom in Singapore, Kabel is also advancing market cooperation in Indonesia, Thailand, Malaysia and other Southeast Asian nations. Kabel Decorative Coatings is steadily expanding its global footprint.' ),
                                        array( 'p', 'This achievement not only demonstrates the brand’s comprehensive strength, but also marks a new chapter of its in-depth development within the international high-end coatings market.' ),
                                        array( 'p', 'Moving forward, Kabel will keep driving brand growth through innovative craftsmanship and design aesthetics, delivering living space solutions enriched with artistic value to global customers. It strives to make every wall a carrier of urban culture.' ),
                                ),
                        ),
                        array(
                                'title'    => 'Waterproof Microcement Wall Paint: Create Stylish, Waterproof & Textured Walls for Modern Home Interiors',
                                'slug'     => 'waterproof-microcement-wall-paint-create-stylish-waterproof-textured-walls-for-modern-home-interiors',
                                'category' => 'knowledge',
                                'date'     => '2026-09-01 09:00:00',
                                'excerpt'  => 'Combining the natural beauty of microcement with advanced waterproof technology, this artistic wall paint creates sophisticated spaces that are both stylish and practical.',
                                'featured' => '2026/09/76bb6f44-2a71-4dd4-a553-f5185eb4cd24.webp',
                                'blocks'   => array(
                                        array( 'img', '2026/09/76bb6f44-2a71-4dd4-a553-f5185eb4cd24.webp' ),
                                        array( 'h2', 'Elevate Modern Living with Waterproof Microcement Wall Paint' ),
                                        array( 'p', 'When homeowners want a seamless, elegant, and durable interior finish, waterproof microcement wall paint has become one of the most sought-after decorative wall coatings. Combining the natural beauty of microcement with advanced waterproof technology, this artistic wall paint creates sophisticated spaces that are both stylish and practical.' ),
                                        array( 'p', 'As a Kabel Italian Top 10 Decorative Paint Brand, Kabel delivers premium decorative coatings designed for modern home decoration. Our waterproof microcement wall paint offers a luxurious cement-inspired texture while providing excellent moisture resistance, making it ideal for living rooms, bedrooms, kitchens, bathrooms, and feature walls.' ),
                                        array( 'p', 'Whether you are designing a minimalist apartment, a luxury villa, or a contemporary townhouse, waterproof microcement wall paint creates timeless interiors that combine aesthetics with long-lasting protection.' ),
                                        array( 'h2', 'Why Choose Waterproof Microcement Wall Paint?' ),
                                        array( 'p', 'Traditional wall finishes often struggle with moisture, stains, or aging surfaces. Waterproof microcement wall paintsolves these common challenges by offering a decorative solution that is both beautiful and highly functional.' ),
                                        array( 'p', 'Key advantages include:' ),
                                        array( 'p', 'Unlike ordinary paint, waterproof microcement wall paint creates an artistic surface with subtle texture variations, giving walls a premium handcrafted appearance without complicated construction.' ),
                                        array( 'h2', 'Premium Decorative Finish for Contemporary Homes' ),
                                        array( 'p', 'Today’s homeowners appreciate clean lines, soft textures, and natural materials. Waterproof microcement wall paintperfectly matches popular interior styles including:' ),
                                        array( 'p', 'As a Kabel Italian Top 10 Decorative Paint Brand, every decorative coating is carefully developed to balance texture, color depth, and durability, allowing designers to create sophisticated living environments with confidence.' ),
                                        array( 'h2', 'Superior Waterproof Protection for Interior Walls' ),
                                        array( 'p', 'One of the biggest concerns in home decoration is wall damage caused by humidity and everyday living.' ),
                                        array( 'p', 'Our waterproof microcement wall paint forms a protective decorative layer that helps resist:' ),
                                        array( 'p', 'This makes waterproof microcement wall paint especially suitable for kitchens, dining areas, entrance walls, laundry rooms, powder rooms, and other moisture-prone residential spaces while maintaining its elegant artistic appearance.' ),
                                        array( 'h2', 'Rich Texture Creates Artistic Living Spaces' ),
                                        array( 'p', 'Texture has become an essential element in luxury interior design.' ),
                                        array( 'p', 'Unlike flat latex paint, waterproof microcement wall paint delivers natural depth through subtle handcrafted effects. Every wall becomes a decorative feature that reflects light softly and creates visual warmth.' ),
                                        array( 'p', 'The cement-inspired finish pairs beautifully with:' ),
                                        array( 'p', 'The result is a comfortable, modern home with distinctive personality and premium visual appeal.' ),
                                        array( 'h2', 'Solve Common Home Decoration Pain Points' ),
                                        array( 'p', 'Many homeowners face similar problems after traditional wall painting:' ),
                                        array( 'p', 'Waterproof microcement wall paint provides an ideal solution by combining decorative beauty with long-term performance.' ),
                                        array( 'p', 'Instead of repainting frequently, homeowners enjoy durable walls that remain attractive for years with simple maintenance.' ),
                                        array( 'h2', 'Real Home Application Example' ),
                                        array( 'p', 'A homeowner renovating a modern open-plan apartment wanted an elegant concrete-inspired living room without using heavy cement panels.' ),
                                        array( 'p', 'By choosing Kabel Italian Top 10 Decorative Paint Brand and applying waterproof microcement wall paint, the designer achieved:' ),
                                        array( 'p', 'The completed project created a refined atmosphere while reducing future maintenance costs, making the home both beautiful and practical.' ),
                                        array( 'h2', 'Why Designers Prefer Kabel Decorative Paint' ),
                                        array( 'p', 'Interior designers increasingly recommend Kabel Italian Top 10 Decorative Paint Brand because our decorative coatings combine aesthetics with reliable performance.' ),
                                        array( 'p', 'Our waterproof microcement wall paint offers:' ),
                                        array( 'p', 'From luxury apartments to custom villas, Kabel products help designers create memorable interiors that satisfy homeowners seeking elegance and durability.' ),
                                        array( 'h2', 'Choose Waterproof Microcement Wall Paint for Lasting Beauty' ),
                                        array( 'p', 'Modern home decoration requires more than ordinary wall paint. Homeowners want artistic finishes that are attractive, durable, and easy to maintain.' ),
                                        array( 'p', 'Waterproof microcement wall paint from Kabel Italian Top 10 Decorative Paint Brand combines contemporary design, decorative texture, and dependable waterproof performance in one premium coating solution. Whether renovating a single feature wall or decorating an entire home, this innovative artistic wall paint transforms ordinary spaces into sophisticated living environments with lasting value.' ),
                                        array( 'p', 'Choose waterproof microcement wall paint from Kabel Italian Top 10 Decorative Paint Brand and experience decorative wall coatings that deliver exceptional texture, reliable protection, and timeless elegance for every modern home.' ),
                                ),
                        ),
                );
                return $posts;

}

/**
 * The two live project references (slugs, titles and classes as published).
 *
 * @return array<int, array<string, mixed>>
 */
function kbl_bootstrap_projects(): array {
        return array(
                array(
                        'title'    => 'PROJECTS post test 1',
                        'slug'     => 'projects-test-1',
                        'category' => 'engineering-projects',
                        'date'     => '2026-09-01 09:00:00',
                        'excerpt'  => '',
                        'content'  => '<p>test</p>',
                        'featured' => '2026/08/Image_20260327_171614-1-scaled.jpg',
                ),
                array(
                        'title'    => 'PROJECTS post test 2',
                        'slug'     => 'projects-post-test-2',
                        'category' => 'home-renovation-projects',
                        'date'     => '2026-09-01 09:00:00',
                        'excerpt'  => '',
                        'content'  => '<p>test</p>',
                        'featured' => '2026/08/ChatGPT-Image-2026年4月1日-09_52_31.png',
                ),
        );
}

/**
 * Does a post of this type with this slug exist (any status)?
 */
function kbl_post_exists( string $slug, string $post_type ): bool {
        $post = get_page_by_path( $slug, OBJECT, $post_type );
        return $post instanceof WP_Post;
}

/**
 * Render the article block list as HTML.
 *
 * Supported blocks: h2 / h3 / h4 / p / img (cover) / ul (label+text rows) /
 * quote (text + cite).
 */
function kbl_blocks_to_html( array $blocks ): string {
        $html = '';
        foreach ( $blocks as $block ) {
                switch ( $block[0] ) {
                        case 'img':
                                $id = kabel_bootstrap_media_id( $block[1] );
                                if ( $id ) {
                                        $html .= sprintf(
                                                '<figure class="kb-article-cover">%s</figure>',
                                                wp_get_attachment_image( $id, 'kabel-large', false, array( 'class' => 'kb-article-cover__img' ) )
                                        );
                                }
                                break;

                        case 'quote':
                                $html .= '<blockquote class="prose-quote"><p>' . esc_html( $block[1] ) . '</p><cite>— ' . esc_html( $block[2] ) . '</cite></blockquote>';
                                break;

                        case 'ul':
                                $html .= '<ul>';
                                foreach ( $block[1] as $li ) {
                                        if ( ! is_array( $li ) || count( $li ) < 2 ) {
                                                continue;
                                        }
                                        $html .= '<li><strong>' . esc_html( $li[1] ) . '</strong>' . ( isset( $li[2] ) && '' !== $li[2] ? ' — ' . esc_html( $li[2] ) : '' ) . '</li>';
                                }
                                $html .= '</ul>';
                                break;

                        default:
                                $tag = isset( $block[0] ) ? strtolower( preg_replace( '/[^a-z0-9]/i', '', (string) $block[0] ) ) : '';
                                if ( in_array( $tag, array( 'h2', 'h3', 'h4', 'p' ), true ) && isset( $block[1] ) && is_string( $block[1] ) ) {
                                        $html .= '<' . $tag . '>' . esc_html( $block[1] ) . '</' . $tag . '>';
                                }
                }
        }
        return $html;
}

/**
 * Create the six paint families (never overwrites — existing slugs are skipped).
 *
 * @param array<string, int> $term_ids Map of "taxonomy:slug" => term ID.
 * @return string[] Created series titles.
 */
function kbl_seed_products( array $term_ids ): array {
        if ( ! post_type_exists( 'product' ) ) {
                return array();
        }

        $created = array();
        foreach ( kbl_bootstrap_products() as $data ) {
                if ( kbl_post_exists( $data['slug'], 'product' ) ) {
                        continue;
                }

                $post_id = wp_insert_post( array(
                        'post_type'    => 'product',
                        'post_status'  => 'publish',
                        'post_title'   => $data['title'],
                        'post_name'    => $data['slug'],
                        'post_content' => $data['content'],
                        'post_excerpt' => $data['excerpt'],
                        'menu_order'   => (int) $data['order'],
                ) );
                if ( ! is_int( $post_id ) || $post_id <= 0 ) {
                        continue;
                }

                update_post_meta( $post_id, '_kb_product_tagline', $data['tagline'] );
                update_post_meta( $post_id, '_kb_product_bullets', $data['bullets'] );
                update_post_meta( $post_id, '_kb_product_specs', $data['specs'] );

                $featured = kabel_bootstrap_media_id( $data['featured'] );
                if ( $featured ) {
                        set_post_thumbnail( $post_id, $featured );
                }

                $gallery = array();
                foreach ( (array) $data['gallery'] as $orig ) {
                        $id = kabel_bootstrap_media_id( $orig );
                        if ( $id ) {
                                $gallery[] = $id;
                        }
                }
                if ( $gallery ) {
                        update_post_meta( $post_id, '_kb_product_gallery', implode( ',', $gallery ) );
                }

                $series_id = $term_ids[ 'product_series:' . $data['series'] ] ?? 0;
                if ( $series_id && taxonomy_exists( 'product_series' ) ) {
                        wp_set_object_terms( $post_id, (int) $series_id, 'product_series' );
                }

                $created[] = $data['title'];
        }

        return $created;
}

/**
 * Create the three demo articles.
 *
 * @return string[] Created article titles.
 */
function kbl_seed_articles(): array {
        $created = array();
        foreach ( kbl_bootstrap_posts() as $data ) {
                if ( kbl_post_exists( $data['slug'], 'post' ) ) {
                        continue;
                }

                $post_id = wp_insert_post( array(
                        'post_type'    => 'post',
                        'post_status'  => 'publish',
                        'post_title'   => $data['title'],
                        'post_name'    => $data['slug'],
                        'post_content' => kbl_blocks_to_html( $data['blocks'] ),
                        'post_excerpt' => $data['excerpt'],
                        'post_date'    => $data['date'],
                        'post_date_gmt' => $data['date'],
                ) );
                if ( ! is_int( $post_id ) || $post_id <= 0 ) {
                        continue;
                }

                $featured = kabel_bootstrap_media_id( $data['featured'] );
                if ( $featured ) {
                        set_post_thumbnail( $post_id, $featured );
                }

                $category = get_category_by_slug( $data['category'] );
                if ( $category ) {
                        wp_set_post_categories( $post_id, array( (int) $category->term_id ) );
                }

                $created[] = $data['title'];
        }

        return $created;
}

/**
 * Create the two project references.
 *
 * @return string[] Created project titles.
 */
function kbl_seed_projects(): array {
        if ( ! post_type_exists( 'projects' ) ) {
                return array();
        }

        $created = array();
        foreach ( kbl_bootstrap_projects() as $data ) {
                if ( kbl_post_exists( $data['slug'], 'projects' ) ) {
                        continue;
                }

                $post_id = wp_insert_post( array(
                        'post_type'    => 'projects',
                        'post_status'  => 'publish',
                        'post_title'   => $data['title'],
                        'post_name'    => $data['slug'],
                        'post_content' => $data['content'],
                        'post_excerpt' => $data['excerpt'],
                        'post_date'    => $data['date'],
                        'post_date_gmt' => $data['date'],
                ) );
                if ( ! is_int( $post_id ) || $post_id <= 0 ) {
                        continue;
                }

                $featured = kabel_bootstrap_media_id( $data['featured'] );
                if ( $featured ) {
                        set_post_thumbnail( $post_id, $featured );
                }

                $category = get_category_by_slug( $data['category'] );
                if ( $category ) {
                        wp_set_object_terms( $post_id, array( (int) $category->term_id ), 'category' );
                }

                $created[] = $data['title'];
        }

        return $created;
}

/* ---------------------------------------------------------------------
 * The run
 * ------------------------------------------------------------------- */

/**
 * Run the bootstrap. Creates only MISSING content — never overwrites.
 *
 * @return array{pages:string[],front:?int,blog:?int,terms:int,products:string[],articles:string[],projects:string[],menus:string[]}
 */
function kbl_run_bootstrap(): array {
        /*
         * The media import (76+ attachments) can exceed a host's 30s PHP cap —
         * the #1 cause of "first login shows a 500" on shared hosting.
         * Raise the budget when the host allows; if it cannot be raised, the
         * resume flag below lets the next admin visit finish the job.
         */
        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- hosts may forbid raising.
        @set_time_limit( 300 );
        update_option( 'kabel_bootstrap_incomplete', time(), false );

        $report = array(
                'pages'    => array(),
                'front'    => null,
                'blog'     => null,
                'terms'    => 0,
                'products' => array(),
                'articles' => array(),
                'projects' => array(),
                'menus'    => array(),
        );
        $ids     = array();

        /* Captured BEFORE any page exists — after step 1 the site is no longer "fresh". */
        $was_fresh = kbl_site_is_fresh();

        /* 1. Pages. */
        foreach ( kbl_bootstrap_blueprint() as $slug => $page ) {
                $existing = get_page_by_path( $slug );
                if ( $existing instanceof WP_Post && 'publish' === $existing->post_status ) {
                        $ids[ $slug ] = (int) $existing->ID;
                        continue;
                }

                /*
                 * Insert with EMPTY content (so nothing can strip the block markup
                 * on the way in), then write the layout through the importer-style
                 * $wpdb path used by kabel_seed_page().
                 */
                $post_id = wp_insert_post( array(
                        'post_type'    => 'page',
                        'post_status'  => 'publish',
                        'post_title'   => $page['title'],
                        'post_name'    => $slug,
                        'post_content' => '',
                ) );

                if ( ! is_int( $post_id ) || $post_id <= 0 ) {
                        continue;
                }

                if ( null !== $page['layout'] ) {
                        $seeded = false;

                        /* Preferred: the blueprint's own layout key. */
                        if ( function_exists( 'kabel_get_block_layout' ) && null !== kabel_get_block_layout( sanitize_key( $page['layout'] ) ) ) {
                                $seeded = ! is_wp_error( kabel_seed_page( $post_id, $page['layout'] ) );
                        }

                        /* Fallback: let the layout matcher read the page. */
                        if ( ! $seeded ) {
                                $seeded = ! is_wp_error( kabel_seed_page( $post_id ) );
                        }

                        /* Swap "kbl-media:" tokens for real attachment IDs. */
                        if ( $seeded ) {
                                kbl_resolve_seed_media( $post_id );
                        }
                }

                if ( 'default' !== $page['template'] ) {
                        update_post_meta( $post_id, '_wp_page_template', $page['template'] );
                }

                $ids[ $slug ]        = $post_id;
                $report['pages'][]   = $page['title'];
        }

        /*
         * Keep the resume flag alive when any blueprint page ended up
         * empty (capability hiccup, interrupted write): the next admin
         * visit retries instead of leaving a half-built site behind.
         * The flag is only cleared at the end when nothing is empty —
         * deleting it unconditionally here would defeat this very check
         * (observed in the wild: a CLI run without unfiltered_html left
         * 14 empty pages with no resume path).
         */
        $incomplete = false;
        foreach ( $ids as $slug => $pid ) {
                $page_def = kbl_bootstrap_blueprint()[ $slug ] ?? null;
                if ( null === $page_def || null === $page_def['layout'] ) {
                        continue;
                }
                if ( '' === trim( (string) get_post_field( 'post_content', $pid ) ) ) {
                        $incomplete = true;
                        update_option( 'kabel_bootstrap_incomplete', time(), false );
                        break;
                }
        }

        /* 2. Reading + writing settings. */

        /*
         * Fresh install nicety: factory-default URLs (plain "?p=123" or the
         * installer's date-based structure) become clean "/slug/" ones.
         * Any custom permalink structure is never touched.
         */
        $fresh_urls_set = false;
        $factory_structures = array(
                '',
                '/index.php/%year%/%monthnum%/%day%/%postname%/',
                '/%year%/%monthnum%/%day%/%postname%/',
        );
        if ( $was_fresh && in_array( (string) get_option( 'permalink_structure' ), $factory_structures, true ) ) {
                /*
                 * set_permalink_structure() updates the option AND the in-memory
                 * $wp_rewrite — a plain update_option() would leave the current
                 * request flushing rules for the OLD structure.
                 */
                if ( isset( $GLOBALS['wp_rewrite'] ) ) {
                        $GLOBALS['wp_rewrite']->set_permalink_structure( '/%postname%/' );
                } else {
                        update_option( 'permalink_structure', '/%postname%/' );
                }
                $fresh_urls_set = true;
        }

        if ( isset( $ids['blog'] ) ) {
                $was_front = (int) get_option( 'page_on_front' );
                $front_ok  = 0 === $was_front;

                if ( ! $front_ok ) {
                        $front_page = get_post( $was_front );
                        $front_ok   = ! $front_page instanceof WP_Post
                                || in_array( $front_page->post_name, array( 'sample-page', 'home' ), true );
                }

                if ( $front_ok && isset( $ids['home'] ) ) {
                        update_option( 'show_on_front', 'page' );
                        update_option( 'page_on_front', $ids['home'] );
                        update_option( 'page_for_posts', $ids['blog'] );
                        $report['front'] = $ids['home'];
                        $report['blog']  = $ids['blog'];
                        flush_rewrite_rules();
                }
        }

        /* 3. Terms (paint families + content categories). */
        $term_ids = kbl_bootstrap_terms();
        if ( $term_ids ) {
                $report['terms'] = count( $term_ids );
        }

        /* 4. Demo content. */
        $report['products'] = kbl_seed_products( $term_ids );
        $report['articles'] = kbl_seed_articles();
        $report['projects'] = kbl_seed_projects();

        /* 5. Navigation menus (theme locations when registered; the seed
         * "Primary Menu" by name on location-less block themes). */
        $menus = kbl_build_nav_menus( $ids, $term_ids );
        if ( $menus ) {
                $report['menus'] = $menus;
        }

        /* 6. Rewrite rules: new CPTs, new pages and (maybe) fresh URLs. */
        flush_rewrite_rules();

        update_option( 'kabel_bootstrapped', (string) time(), false );
        if ( ! $incomplete ) {
                /* Everything landed — retire the resume flag. An incomplete
                 * run keeps it so kbl_maybe_autorun() retries on the next
                 * admin visit (every step is idempotent). */
                delete_option( 'kabel_bootstrap_incomplete' );
        }

        /* 7. Site chrome (plugin mode): the editable Site Header / Site
         * Footer part pages, seeded with the classic layout. Runs only
         * when the Kabel theme is absent — its own kabel_ensure_site_parts
         * covers theme mode. */
        if ( function_exists( 'kbl3_ensure_site_parts' ) ) {
                kbl3_ensure_site_parts();
        }

        /**
         * Fires after a successful bootstrap run.
         *
         * @param array $report Summary: created page titles, front/blog ids, term count, demo content, menus.
         */
        do_action( 'kabel_blocks_bootstrapped', $report );

        return $report;
}

/* ---------------------------------------------------------------------
 * Navigation menus
 * ------------------------------------------------------------------- */

/**
 * Create + assign the primary and footer menus (safe: only fills fresh,
 * unassigned menus — a user-curated menu is never touched).
 *
 * @param array<string, int> $ids      Map of blueprint slug => page ID.
 * @param array<string, int> $term_ids Map of "taxonomy:slug" => term ID.
 * @return string[] Names of the menus that were created/filled.
 */
function kbl_build_nav_menus( array $ids, array $term_ids = array() ): array {
        $made             = array();
        $assigned         = false;
        $menus_registered = get_registered_nav_menus(); // Standalone themes may not have these locations.

        /* Location-less themes (block themes like Twenty Twenty-Five
         * register no classic locations): the kbl/nav-menu block resolves
         * its menu BY NAME, so a fresh site still needs the seed "Primary
         * Menu" to exist — without it the theme's page-list fallback took
         * over and no menu edit could ever change the header. Created only
         * when the site has NO menus at all; a user-curated menu is never
         * touched, and it stays unassigned (there is nowhere to assign
         * it). */
        $locationless = ! isset( $menus_registered['primary'] ) && isset( $ids['home'] );

        if ( ( isset( $menus_registered['primary'] ) || $locationless ) && ( ! isset( $menus_registered['primary'] ) || ! has_nav_menu( 'primary' ) ) ) {
                /* For location-less creation: only on a menu-free site. */
                $create = true;
                if ( $locationless ) {
                        $existing = wp_get_nav_menus( array( 'orderby' => 'name' ) );
                        $create   = empty( $existing ) || is_wp_error( $existing );
                }

                $menu_id = $create ? kbl_menu_fresh( 'Primary Menu' ) : 0;
                if ( $menu_id ) {
                        $products = (string) ( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) );
                        $projects = (string) ( get_post_type_archive_link( 'projects' ) ?: home_url( '/projects/' ) );

                        $items = array(
                                'home'     => array( 'type' => 'custom', 'url' => home_url( '/' ), 'title' => __( 'HOME', 'kabel-blocks' ) ),
                                'about'    => array( 'type' => 'page', 'id' => $ids['about'] ?? 0, 'title' => __( 'ABOUT', 'kabel-blocks' ) ),

                                'about-self'  => array( 'type' => 'page', 'id' => $ids['about'] ?? 0, 'title' => __( 'About Us', 'kabel-blocks' ), 'parent' => 'about' ),
                                'eco'         => array( 'type' => 'page', 'id' => $ids['eco-friendly'] ?? 0, 'title' => __( 'ECO Friendly', 'kabel-blocks' ), 'parent' => 'about' ),
                                'designer'    => array( 'type' => 'page', 'id' => $ids['designer'] ?? 0, 'title' => __( 'Designer', 'kabel-blocks' ), 'parent' => 'about' ),
                                'distributor' => array( 'type' => 'page', 'id' => $ids['distributor'] ?? 0, 'title' => __( 'Distributor', 'kabel-blocks' ), 'parent' => 'about' ),

                                'products' => array( 'type' => 'custom', 'url' => $products, 'title' => __( 'PRODUCTS', 'kabel-blocks' ) ),

                                'colors'   => array( 'type' => 'custom', 'url' => '#', 'title' => __( 'COLORS', 'kabel-blocks' ) ),
                                'morandi'  => array( 'type' => 'page', 'id' => $ids['morandi-colors'] ?? 0, 'title' => __( 'Morandi Colors', 'kabel-blocks' ), 'parent' => 'colors' ),
                                'vangogh'  => array( 'type' => 'page', 'id' => $ids['van-gogh-color'] ?? 0, 'title' => __( 'Van Gogh Color', 'kabel-blocks' ), 'parent' => 'colors' ),
                                'macaron'  => array( 'type' => 'page', 'id' => $ids['macaron-colors'] ?? 0, 'title' => __( 'Macaron colors', 'kabel-blocks' ), 'parent' => 'colors' ),
                                'museum'   => array( 'type' => 'page', 'id' => $ids['museum-collection-colors'] ?? 0, 'title' => __( 'Museum Collection Colors', 'kabel-blocks' ), 'parent' => 'colors' ),

                                /* Seven top-level items fit one header row at
                                 * 15px; deeper pages nest — a 9-item top level
                                 * (procurement + distributor on their own
                                 * rows) wrapped into two lines and broke the
                                 * header layout. */
                                'projects'     => array( 'type' => 'custom', 'url' => $projects, 'title' => __( 'PROJECTS', 'kabel-blocks' ) ),
                                'renovation'   => array( 'type' => 'term', 'id' => $term_ids['category:home-renovation-projects'] ?? 0, 'taxonomy' => 'category', 'title' => __( 'Home Renovation Projects', 'kabel-blocks' ), 'parent' => 'projects' ),
                                'engineering'  => array( 'type' => 'term', 'id' => $term_ids['category:engineering-projects'] ?? 0, 'taxonomy' => 'category', 'title' => __( 'Engineering Projects', 'kabel-blocks' ), 'parent' => 'projects' ),
                                'procurement-pg' => array( 'type' => 'page', 'id' => $ids['project-procurement'] ?? 0, 'title' => __( 'Project Procurement', 'kabel-blocks' ), 'parent' => 'projects' ),
                                'customize'      => array( 'type' => 'page', 'id' => $ids['customize'] ?? 0, 'title' => __( 'Customize', 'kabel-blocks' ), 'parent' => 'projects' ),

                                'news'          => array( 'type' => 'custom', 'url' => isset( $ids['blog'] ) ? (string) get_permalink( $ids['blog'] ) : home_url( '/blog/' ), 'title' => __( 'NEWS', 'kabel-blocks' ) ),
                                'company-news'  => array( 'type' => 'term', 'id' => $term_ids['category:company-news'] ?? 0, 'taxonomy' => 'category', 'title' => __( 'Company News', 'kabel-blocks' ), 'parent' => 'news' ),
                                'industry-news' => array( 'type' => 'term', 'id' => $term_ids['category:industry-news'] ?? 0, 'taxonomy' => 'category', 'title' => __( 'Industry News', 'kabel-blocks' ), 'parent' => 'news' ),
                                'knowledge'     => array( 'type' => 'term', 'id' => $term_ids['category:knowledge'] ?? 0, 'taxonomy' => 'category', 'title' => __( 'Knowledge', 'kabel-blocks' ), 'parent' => 'news' ),
                                'faq'           => array( 'type' => 'page', 'id' => $ids['faq'] ?? 0, 'title' => __( 'Faq', 'kabel-blocks' ), 'parent' => 'news' ),

                                'contact' => array( 'type' => 'page', 'id' => $ids['contact'] ?? 0, 'title' => __( 'CONTACT', 'kabel-blocks' ) ),
                        );

                        kbl_fill_menu( $menu_id, $items );

                        $made[] = 'Primary Menu';
                        if ( isset( $menus_registered['primary'] ) ) {
                                $locations            = get_theme_mod( 'nav_menu_locations', array() );
                                $locations['primary'] = $menu_id;
                                set_theme_mod( 'nav_menu_locations', $locations );
                                $assigned = true;
                        }
                }
        }

        if ( isset( $menus_registered['footer_company'] ) && ! has_nav_menu( 'footer_company' ) ) {
                $menu_id = kbl_menu_fresh( 'Footer — Company' );
                if ( $menu_id ) {
                        $items = array(
                                'about'        => array( 'type' => 'page', 'id' => $ids['about'] ?? 0, 'title' => __( 'ABOUT', 'kabel-blocks' ) ),
                                'colors'       => array( 'type' => 'page', 'id' => $ids['morandi-colors'] ?? 0, 'title' => __( 'Colors', 'kabel-blocks' ) ),
                                'projects'     => array( 'type' => 'custom', 'url' => (string) ( get_post_type_archive_link( 'projects' ) ?: home_url( '/projects/' ) ), 'title' => __( 'PROJECTS', 'kabel-blocks' ) ),
                                'procurement'  => array( 'type' => 'page', 'id' => $ids['project-procurement'] ?? 0, 'title' => __( 'Procurement', 'kabel-blocks' ) ),
                                'customize'    => array( 'type' => 'page', 'id' => $ids['customize'] ?? 0, 'title' => __( 'customize', 'kabel-blocks' ) ),
                                'distributor'  => array( 'type' => 'page', 'id' => $ids['distributor'] ?? 0, 'title' => __( 'DISTRIBUTOR', 'kabel-blocks' ) ),
                                'news'         => array( 'type' => 'page', 'id' => $ids['blog'] ?? 0, 'title' => __( 'NEWS', 'kabel-blocks' ) ),
                                'contact'      => array( 'type' => 'page', 'id' => $ids['contact'] ?? 0, 'title' => __( 'CONTACT', 'kabel-blocks' ) ),
                        );
                        kbl_fill_menu( $menu_id, $items );

                        $made[]                    = 'Footer — Company';
                        $locations                 = get_theme_mod( 'nav_menu_locations', array() );
                        $locations['footer_company'] = $menu_id;
                        set_theme_mod( 'nav_menu_locations', $locations );
                        $assigned = true;
                }
        }

        /*
         * Footer paint-family menu: only when series terms already exist
         * (the theme fallback renders the list dynamically otherwise).
         */
        if ( isset( $menus_registered['footer_products'] ) && ! has_nav_menu( 'footer_products' ) && taxonomy_exists( 'product_series' ) ) {
                /* Series terms in the family order of the product blueprint. */
                $series_ids = array();
                foreach ( array_column( kbl_bootstrap_products(), 'series' ) as $series_slug ) {
                        $series_id = $term_ids[ 'product_series:' . $series_slug ] ?? 0;
                        if ( $series_id ) {
                                $series_ids[] = (int) $series_id;
                        }
                }

                if ( $series_ids ) {
                        $menu_id = kbl_menu_fresh( 'Footer — Product Series' );
                        if ( $menu_id ) {
                                $items = array();
                                foreach ( $series_ids as $index => $series_id ) {
                                        $items[ 'series-' . $index ] = array(
                                                'type'     => 'term',
                                                'id'       => $series_id,
                                                'taxonomy' => 'product_series',
                                        );
                                }
                                kbl_fill_menu( $menu_id, $items );

                                $made[]                     = 'Footer — Product Series';
                                $locations                  = get_theme_mod( 'nav_menu_locations', array() );
                                $locations['footer_products'] = $menu_id;
                                set_theme_mod( 'nav_menu_locations', $locations );
                                $assigned = true;
                        }
                }
        }

        if ( $assigned ) {
                // Menu changes need a clean slate for front-end menu fallbacks.
                wp_cache_delete( 'nav_menu_locations', 'options' );
        }

        return $made;
}

/**
 * Get a menu by name that is safe to fill: reuse an existing EMPTY one, or
 * create a fresh one. Returns the term ID (or 0).
 */
function kbl_menu_fresh( string $name ): int {
        $menu = wp_get_nav_menu_object( $name );
        if ( $menu instanceof WP_Term ) {
                $items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );
                return ( array() === $items ) ? (int) $menu->term_id : 0;
        }
        $created = wp_create_nav_menu( $name );
        return is_wp_error( $created ) ? 0 : (int) $created;
}

/**
 * Add items to a (fresh, empty) menu.
 *
 * Item shape: array{type:'page'|'term'|'custom', id?:int, taxonomy?:string,
 * url?:string, title?:string, parent?:string}. Array keys double as handles
 * for the 'parent' references, so dropdowns nest correctly. Items whose
 * page/term ID is 0 are skipped (their page was not created this run).
 *
 * @param array<string, array> $items
 */
function kbl_fill_menu( int $menu_id, array $items ): void {
        $inserted = array();
        foreach ( $items as $key => $item ) {
                $type = $item['type'] ?? 'custom';
                $args = array( 'menu-item-status' => 'publish' );

                if ( 'page' === $type ) {
                        if ( empty( $item['id'] ) ) {
                                continue;
                        }
                        $args['menu-item-object-id'] = (int) $item['id'];
                        $args['menu-item-object']    = 'page';
                        $args['menu-item-type']      = 'post_type';
                        if ( ! empty( $item['title'] ) ) {
                                $args['menu-item-title'] = $item['title'];
                        }
                } elseif ( 'term' === $type ) {
                        if ( empty( $item['id'] ) ) {
                                continue;
                        }
                        $args['menu-item-object-id'] = (int) $item['id'];
                        $args['menu-item-object']    = $item['taxonomy'] ?? 'category';
                        $args['menu-item-type']      = 'taxonomy';
                        if ( ! empty( $item['title'] ) ) {
                                $args['menu-item-title'] = $item['title'];
                        }
                } else {
                        $args['menu-item-url']   = $item['url'] ?? '#';
                        $args['menu-item-title'] = $item['title'] ?? '';
                        $args['menu-item-type']  = 'custom';
                }

                if ( ! empty( $item['parent'] ) && isset( $inserted[ $item['parent'] ] ) ) {
                        $args['menu-item-parent-id'] = $inserted[ $item['parent'] ];
                }

                $item_id = wp_update_nav_menu_item( $menu_id, 0, $args );
                if ( is_int( $item_id ) && $item_id > 0 ) {
                        $inserted[ $key ] = $item_id;
                }
        }
}

/* ---------------------------------------------------------------------
 * Auto-run + notices
 * ------------------------------------------------------------------- */

/**
 * Auto-bootstrap a fresh site on the first admin visit (activation landing),
 * and RESUME an interrupted run (the 30s cap on shared hosting can cut the
 * media import short — the resume flag picks up where it died; every step is
 * idempotent, only missing pieces are created).
 */
function kbl_maybe_autorun(): void {
        if ( wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
                return;
        }
        if ( ! current_user_can( 'edit_pages' ) || ! current_user_can( 'unfiltered_html' ) ) {
                return;
        }
        $resuming = (int) get_option( 'kabel_bootstrap_incomplete' );
        if ( $resuming && ( time() - $resuming ) < 60 ) {
                return; /* Give the previous (possibly still-running) request a minute. */
        }
        if ( ! kbl_site_is_fresh() && ! $resuming ) {
                return;
        }

        $GLOBALS['kbl_bootstrap_report'] = kbl_run_bootstrap();
}
add_action( 'admin_init', 'kbl_maybe_autorun', 1 );

/**
 * Success notice for the auto-run (same request) and the manual handler
 * (redirect flag), plus the standing "pages missing" helper.
 */
function kbl_bootstrap_notices(): void {
        if ( ! current_user_can( 'edit_pages' ) ) {
                return;
        }

        /* Manual-run capability failure (redirect back from admin-post.php). */
        if ( isset( $_GET['kabel_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
                printf(
                        '<div class="notice notice-error is-dismissible"><p><strong>%s</strong> %s</p></div>',
                        esc_html__( 'The Kabel pages could not be created.', 'kabel-blocks' ),
                        esc_html__( 'Your account needs the Administrator role (page creation + unfiltered HTML). Log in as an administrator and try again.', 'kabel-blocks' )
                );
                return;
        }

        /* Auto-run result (same request). */
        if ( isset( $GLOBALS['kbl_bootstrap_report'] ) ) {
                kbl_render_success_notice( (array) $GLOBALS['kbl_bootstrap_report'] );
                return;
        }

        /* Manual-run result (redirect back from admin-post.php). */
        if ( isset( $_GET['kabel_bootstrapped'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
                $report = json_decode( (string) get_transient( 'kbl_bootstrap_report' ), true );
                delete_transient( 'kbl_bootstrap_report' );
                if ( is_array( $report ) ) {
                        kbl_render_success_notice( $report );
                }
                return;
        }

        /* Standing helper while pages are missing. */
        if ( '1' === (string) get_option( 'kabel_bootstrap_notice_off', '' ) ) {
                return;
        }
        $missing = kbl_bootstrap_missing();
        if ( array() === $missing ) {
                return;
        }

        $url = wp_nonce_url(
                add_query_arg( 'action', 'kabel_bootstrap', admin_url( 'admin-post.php' ) ),
                'kabel_bootstrap'
        );
        $dismiss = wp_nonce_url(
                add_query_arg( 'action', 'kabel_dismiss_bootstrap_notice', admin_url( 'admin-post.php' ) ),
                'kabel_dismiss_bootstrap_notice'
        );
        ?>
        <div class="notice notice-info kabel-bootstrap-notice">
                <p>
                        <strong><?php echo esc_html( sprintf( _n( '%d Kabel page is missing.', '%d Kabel pages are missing.', count( $missing ), 'kabel-blocks' ), count( $missing ) ) ); ?></strong>
                        <?php
                        echo esc_html( sprintf(
                                /* translators: %s: list of page titles, e.g. "Home, About Us". */
                                __( 'Missing: %s — create them pre-filled with their ready-made layouts?', 'kabel-blocks' ),
                                implode( ', ', $missing )
                        ) );
                        ?>
                </p>
                <p>
                        <a class="button button-primary" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Create the pages · 一键初始化整站', 'kabel-blocks' ); ?></a>
                        <a class="button" href="<?php echo esc_url( $dismiss ); ?>"><?php esc_html_e( 'Dismiss', 'kabel-blocks' ); ?></a>
                </p>
        </div>
        <?php
}
add_action( 'admin_notices', 'kbl_bootstrap_notices' );

/**
 * Render the "site ready" success notice.
 */
function kbl_render_success_notice( array $report ): void {
        $pages    = isset( $report['pages'] ) ? (array) $report['pages'] : array();
        $products = isset( $report['products'] ) ? (array) $report['products'] : array();
        $articles = isset( $report['articles'] ) ? (array) $report['articles'] : array();
        $projects = isset( $report['projects'] ) ? (array) $report['projects'] : array();
        $menus    = isset( $report['menus'] ) ? (array) $report['menus'] : array();

        $demo = array();
        if ( $products ) {
                $demo[] = sprintf( _n( '%d paint family', '%d paint families', count( $products ), 'kabel-blocks' ), count( $products ) );
        }
        if ( $articles ) {
                $demo[] = sprintf( _n( '%d article', '%d articles', count( $articles ), 'kabel-blocks' ), count( $articles ) );
        }
        if ( $projects ) {
                $demo[] = sprintf( _n( '%d project', '%d projects', count( $projects ), 'kabel-blocks' ), count( $projects ) );
        }
        ?>
        <div class="notice notice-success is-dismissible">
                <p>
                        <strong><?php esc_html_e( 'Your Kabel site is ready.', 'kabel-blocks' ); ?></strong>
                        <?php
                        if ( $pages ) {
                                echo ' ' . esc_html( sprintf(
                                        /* translators: %s: list of page titles. */
                                        __( 'Created: %s.', 'kabel-blocks' ),
                                        implode( ', ', $pages )
                                ) );
                        }
                        if ( $demo ) {
                                echo ' ' . esc_html( sprintf(
                                        /* translators: %s: list such as "6 paint families, 3 articles". */
                                        __( 'Demo content: %s.', 'kabel-blocks' ),
                                        implode( ', ', $demo )
                                ) );
                        }
                        if ( $menus ) {
                                echo ' ' . esc_html( sprintf(
                                        /* translators: %s: list of menu names. */
                                        __( 'Navigation menus built: %s.', 'kabel-blocks' ),
                                        implode( ', ', $menus )
                                ) );
                        }
                        ?>
                </p>
                <p>
                        <a class="button button-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Visit the site', 'kabel-blocks' ); ?></a>
                        <a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>"><?php esc_html_e( 'Edit the pages', 'kabel-blocks' ); ?></a>
                </p>
        </div>
        <?php
}

/**
 * Manual trigger: create the missing Kabel content now.
 */
function kbl_handle_bootstrap(): void {
        if ( ! current_user_can( 'edit_pages' ) || ! current_user_can( 'unfiltered_html' ) ) {
                wp_safe_redirect( add_query_arg( 'kabel_error', '1', admin_url( 'edit.php?post_type=page' ) ) );
                exit;
        }
        check_admin_referer( 'kabel_bootstrap' );

        $report  = kbl_run_bootstrap();
        set_transient( 'kbl_bootstrap_report', wp_json_encode( $report ), 60 );

        $back = wp_get_referer() ?: admin_url( 'edit.php?post_type=page' );
        wp_safe_redirect( add_query_arg( 'kabel_bootstrapped', '1', $back ) );
        exit;
}
add_action( 'admin_post_kabel_bootstrap', 'kbl_handle_bootstrap' );

/**
 * Logged-out click on "Create the pages": admin-post.php does NOT redirect
 * visitors to the login screen — without this, WordPress would render its
 * empty "Error" box (wp_die('', 400) for an unregistered nopriv action).
 * Send the visitor to login instead; after signing in they land on the Pages
 * list where the standing notice offers a fresh button.
 */
function kbl_handle_bootstrap_nopriv(): void {
        wp_safe_redirect( wp_login_url( admin_url( 'edit.php?post_type=page' ) ) );
        exit;
}
add_action( 'admin_post_nopriv_kabel_bootstrap', 'kbl_handle_bootstrap_nopriv' );

/**
 * Dismiss the standing notice.
 */
function kbl_handle_dismiss_bootstrap_notice(): void {
        if ( ! current_user_can( 'edit_pages' ) ) {
                wp_safe_redirect( admin_url( 'edit.php?post_type=page' ) );
                exit;
        }
        check_admin_referer( 'kabel_dismiss_bootstrap_notice' );

        update_option( 'kabel_bootstrap_notice_off', '1', false );

        $back = wp_get_referer() ?: admin_url( 'edit.php?post_type=page' );
        wp_safe_redirect( remove_query_arg( 'kabel_bootstrapped', $back ) );
        exit;
}
add_action( 'admin_post_kabel_dismiss_bootstrap_notice', 'kbl_handle_dismiss_bootstrap_notice' );
add_action( 'admin_post_nopriv_kabel_dismiss_bootstrap_notice', 'kbl_handle_bootstrap_nopriv' );
