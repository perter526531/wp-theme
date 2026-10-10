<?php
/**
 * GTranslate bridge for kbl/language-switcher (source = "gtranslate").
 *
 * Architecture (v3.8): GTranslate's own settings are the SINGLE source of
 * truth for which languages exist. The owner picks languages in
 * Settings → GTranslate; this bridge re-renders the switcher row from those
 * settings on every request, so the header always matches the plugin — no
 * second list to keep in sync inside the block.
 *
 *   render_block_kbl/language-switcher →
 *     source is "gtranslate" AND the GTranslate plugin is active →
 *       read GTranslate's configured languages (fincl_langs / incl_langs,
 *       honouring its native-names preference) →
 *         re-render the styled anchor row from those languages →
 *           render one DISCARDED [gt-link] shortcode →
 *             GTranslate enqueues its own base.js + window.gtranslateSettings
 *             (via its native wp_enqueue_script + script_loader_tag pipeline) →
 *               base.js binds click handlers on every a[data-gt-lang].
 *
 * Why through the shortcode instead of enqueuing base.js ourselves: GTranslate
 * 5.x initializes base.js from the script tag's data-gt-widget-id attribute,
 * which its script_loader_tag filter only adds to handles IT registered
 * (gt_widget_script_*). Reusing [gt-link] keeps the whole lifecycle — the
 * settings subset, the CDN/local file choice, the defer + attribute rewrite —
 * in GTranslate's hands, so plugin updates cannot break the bridge.
 *
 * The discarded anchor never reaches the output; the trigger uses constant
 * safe values ("en"/"en") because the enqueued settings are global.
 *
 * A REST twin (kbl3/v1/gtranslate/languages) feeds the block editor the same
 * list, so the sidebar shows the plugin's languages read-only instead of a
 * second editable copy.
 *
 * Without GTranslate active the block degrades gracefully: it still renders
 * its saved links, and the editor shows an install hint.
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is the GTranslate plugin loaded?
 *
 * @return bool
 */
function kbl3_gtranslate_active(): bool {
        return class_exists( 'GTranslate' );
}

/**
 * The languages GTranslate is configured to expose.
 *
 * Mirrors the plugin's own load_defaults()/load_settings() resolution:
 * widget looks that use the dropdown list (dropdown / globe / lang_names /
 * lang_codes) read incl_langs, everything else reads fincl_langs; the
 * default language is always kept and first. Labels follow the plugin's
 * "native language names" preference.
 *
 * @return array{default:string, languages:array<int, array{code:string, label:string}>}
 */
function kbl3_gtranslate_languages(): array {
        static $cache = null;
        if ( null !== $cache ) {
                return $cache;
        }

        $out = array(
                'default'    => 'en',
                'languages'  => array(
                        array( 'code' => 'en', 'label' => 'English' ),
                ),
        );
        if ( ! kbl3_gtranslate_active() ) {
                return $cache = $out;
        }

        $data = get_option( 'GTranslate' );
        if ( ! is_array( $data ) ) {
                $data = array();
        }

        $widget_look = isset( $data['widget_look'] ) ? (string) $data['widget_look'] : 'float';
        $fincl       = isset( $data['fincl_langs'] ) && is_array( $data['fincl_langs'] ) ? array_values( $data['fincl_langs'] ) : array();
        $incl        = isset( $data['incl_langs'] ) && is_array( $data['incl_langs'] ) ? array_values( $data['incl_langs'] ) : array();
        $default     = isset( $data['default_language'] ) ? (string) $data['default_language'] : 'en';
        if ( '' === $default ) {
                $default = 'en';
        }

        /* GTranslate defaults when nothing is saved yet (load_defaults). */
        if ( array() === $fincl ) {
                $fincl = array( 'en', 'es', 'it', 'pt', 'de', 'fr', 'ru', 'nl', 'ar', 'zh-CN' );
        }
        if ( array() === $incl ) {
                $incl = $fincl;
        }

        /* load_settings(): dropdown-driven looks read the dropdown list. */
        $codes = in_array( $widget_look, array( 'dropdown', 'globe', 'lang_names', 'lang_codes' ), true ) ? $incl : $fincl;

        /* The default language always participates and leads the row. */
        $codes = array_values( array_unique( array_merge( array( $default ), $codes ) ) );

        /* Labels: native names when the plugin prefers them, English ones
         * otherwise; the raw code as the last resort. */
        $native = ! empty( $data['native_language_names'] );
        $map    = array();
        if ( $native ) {
                $decoded = json_decode( (string) GTranslate::$lang_array_native_json, true );
                if ( is_array( $decoded ) ) {
                        $map = $decoded;
                }
        }
        $english = isset( GTranslate::$lang_array ) && is_array( GTranslate::$lang_array ) ? GTranslate::$lang_array : array();

        $languages = array();
        foreach ( $codes as $code ) {
                $code  = (string) $code;
                $label = isset( $map[ $code ] ) && '' !== (string) $map[ $code ]
                        ? (string) $map[ $code ]
                        : ( isset( $english[ $code ] ) && '' !== (string) $english[ $code ] ? (string) $english[ $code ] : strtoupper( $code ) );
                $languages[] = array(
                        'code'  => $code,
                        'label' => $label,
                );
        }

        $out['default']   = $default;
        $out['languages'] = $languages;
        return $cache = $out;
}

/**
 * Build the block item rows from the plugin's language settings.
 *
 * The "current" flag follows the live request language when GTranslate is in
 * its URL-parameter mode (?lang=xx) — after a switch the page reloads and the
 * dropdown's toggle label must read the active language, not the site default.
 *
 * @return array<int, array{code:string, label:string, url:string, current:bool}>
 */
function kbl3_gtranslate_items(): array {
        $gt    = kbl3_gtranslate_languages();
        $items = array();

        /* GTranslate URL-parameter mode: the active language rides on ?lang=.
         * Matching against the plugin's own code list doubles as the
         * sanitization (sanitize_key() would lowercase zh-CN and miss). */
        $current = $gt['default'];
        if ( isset( $_GET['lang'] ) && is_string( $_GET['lang'] ) && '' !== $_GET['lang'] ) {
                $requested = (string) wp_unslash( $_GET['lang'] );
                foreach ( $gt['languages'] as $lang ) {
                        if ( (string) $lang['code'] === $requested ) {
                                $current = $requested;
                                break;
                        }
                }
        }

        foreach ( $gt['languages'] as $lang ) {
                $items[] = array(
                        'code'    => (string) $lang['code'],
                        'label'   => (string) $lang['label'],
                        'url'     => '',
                        'current' => (string) $lang['code'] === $current,
                );
        }
        return $items;
}

/**
 * Bridge render filter: re-render the row from GTranslate's settings and fire
 * its script machinery once.
 *
 * @param string $content Rendered block markup (replaced on success).
 * @param array  $block   Parsed block: blockName, attrs.
 * @return string
 */
function kbl3_gtranslate_bridge( string $content, array $block ): string {
        if ( ( $block['blockName'] ?? '' ) !== 'kbl/language-switcher' ) {
                return $content;
        }
        $attrs = (array) ( $block['attrs'] ?? array() );

        /*
         * Seed-upgrade path: a header seeded BEFORE GTranslate was installed
         * carries neither "source" nor "items" (the seed writes them only when
         * the plugin is already active — exactly the fallback list the emitter
         * serializes without attributes). Once GTranslate exists, such an
         * untouched block upgrades to the bridge automatically. Anything the
         * owner curated stays theirs: an explicit "custom", or any saved items
         * list, is respected.
         */
        $source = isset( $attrs['source'] ) ? (string) $attrs['source'] : '';
        if ( 'custom' === $source ) {
                return $content;
        }
        if ( 'gtranslate' !== $source && array_key_exists( 'items', $attrs ) ) {
                return $content;
        }

        if ( ! kbl3_gtranslate_active() || ! shortcode_exists( 'gt-link' ) ) {
                return $content;
        }

        /* Single source of truth: the anchor row is rebuilt from the
         * GTranslate settings on every render — a language added in the
         * plugin appears here immediately, a removed one disappears. */
        $items = kbl3_gtranslate_items();
        if ( array() !== $items ) {
                /* kbl3_lang_markup() keys the anchor form (data-gt-lang +
                 * notranslate + href="#") off attrs.source — the upgrade path
                 * reaches this point without a serialized source, so state it. */
                $attrs['source'] = 'gtranslate';
                $content = kbl3_lang_markup( $attrs, $items );
        }

        /* Discarded render: triggers the base.js enqueue + settings only.
         * Constant values — the anchor output is never used. */
        do_shortcode( '[gt-link lang="en" label="en"]' );

        return $content;
}
add_filter( 'render_block_kbl/language-switcher', 'kbl3_gtranslate_bridge', 10, 2 );

/**
 * REST twin of kbl3_gtranslate_languages() for the editor sidebar.
 *
 * GET /kbl3/v1/gtranslate/languages → { active, default, languages[] } —
 * the block's "Resync" button reads it after the owner edits the plugin
 * settings in another tab.
 */
function kbl3_gtranslate_rest(): void {
        register_rest_route( 'kbl3/v1', '/gtranslate/languages', array(
                'methods'             => 'GET',
                'permission_callback' => static function (): bool {
                        return current_user_can( 'edit_posts' );
                },
                'callback'            => static function () {
                        $gt = kbl3_gtranslate_languages();
                        return rest_ensure_response( array(
                                'active'     => kbl3_gtranslate_active(),
                                'default'    => $gt['default'],
                                'languages'  => $gt['languages'],
                        ) );
                },
        ) );
}
add_action( 'rest_api_init', 'kbl3_gtranslate_rest' );
