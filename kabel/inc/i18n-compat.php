<?php
/**
 * Multilingual compatibility layer.
 *
 * User-selected plugin (Phase 4, corrected 2026-09-29): GTranslate —
 * automatic machine translation; activating it instantly adds 100+ languages.
 *
 * This layer:
 *  - Detects GTranslate and renders its switcher in the header automatically.
 *  - Keeps a Polylang upgrade path (human-managed translations) working
 *    should a strategic market need it later.
 *  - Hides the switcher entirely on single-language sites.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is the GTranslate plugin active (its shortcode registered)?
 */
function kabel_gtranslate_active(): bool {
        return function_exists( 'shortcode_exists' ) && shortcode_exists( 'gtranslate' );
}

/**
 * Is Polylang active? (Future upgrade path.)
 */
function kabel_polylang_active(): bool {
        return function_exists( 'pll_current_language' );
}

/**
 * Does the site expose more than one language via any supported plugin?
 */
function kabel_is_multilingual(): bool {
        return kabel_gtranslate_active() || kabel_polylang_active();
}

/**
 * Render the header language switcher — styled as an A×B dropdown.
 *
 * Priority: Polylang (human translations, full dropdown with flags) →
 * GTranslate (machine translation; its select is restyled to the same pill
 * button via CSS) → nothing (single-language sites stay clean).
 */
function kabel_language_switcher(): void {
        if ( kabel_polylang_active() ) {
                $languages = pll_the_languages( array( 'raw' => true ) );
                if ( ! empty( $languages ) && is_array( $languages ) ) {
                        $current = null;
                        foreach ( $languages as $lang ) {
                                if ( ! empty( $lang['current_lang'] ) ) {
                                        $current = $lang;
                                        break;
                                }
                        }
                        if ( ! $current ) {
                                $current = reset( $languages );
                        }

                        $id = wp_unique_id( 'lang-drop-' );
                        printf(
                                '<div class="lang-drop" id="%1$s">' .
                                        '<button class="lang-drop__btn" type="button" aria-expanded="false" aria-controls="%1$s-panel" aria-haspopup="true">' .
                                                '<svg class="lang-drop__globe" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.5 3.8 5.6 3.8 9s-1.3 6.5-3.8 9c-2.5-2.5-3.8-5.6-3.8-9S9.5 5.5 12 3z"/></svg>' .
                                                '<span class="lang-drop__code">%2$s</span>' .
                                                '<svg class="lang-drop__chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>' .
                                                '<span class="screen-reader-text">%3$s</span>' .
                                        '</button>' .
                                        '<div class="lang-drop__panel" id="%1$s-panel" role="menu" aria-hidden="true">' .
                                                '<ul class="lang-drop__list">%4$s</ul>' .
                                        '</div>' .
                                '</div>',
                                esc_attr( $id ),
                                esc_html( strtoupper( (string) ( $current['slug'] ?? '' ) ) ),
                                esc_html__( 'Switch language', 'kabel' ),
                                kabel_lang_drop_items( $languages ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper.
                        );
                        return;
                }
        }

        if ( kabel_gtranslate_active() ) {
                // GTranslate renders a <select>; main.css §21 restyles it to the same pill.
                echo do_shortcode( '[gtranslate]' );
                return;
        }

        // Single-language site: the header slot stays empty by design.
}

/**
 * The dropdown menu items (Polylang languages, escaped).
 *
 * @param array $languages Raw pll_the_languages() output.
 */
function kabel_lang_drop_items( array $languages ): string {
        $html = '';
        foreach ( $languages as $lang ) {
                $classes = 'lang-drop__item' . ( ! empty( $lang['current_lang'] ) ? ' is-current' : '' );
                $flag    = ! empty( $lang['flag'] ) ? '<span class="lang-drop__flag" aria-hidden="true">' . $lang['flag'] . '</span>' : '';
                $html   .= sprintf(
                        '<li class="%1$s" role="none"><a role="menuitem" hreflang="%2$s" href="%3$s">%4$s<span class="lang-drop__name">%5$s</span><span class="lang-drop__code2">%6$s</span></a></li>',
                        esc_attr( $classes ),
                        esc_attr( (string) $lang['slug'] ),
                        esc_url( (string) $lang['url'] ),
                        wp_kses_post( $flag ),
                        esc_html( (string) $lang['name'] ),
                        esc_html( strtoupper( (string) $lang['slug'] ) )
                );
        }
        return $html;
}

/* ---------------------------------------------------------------
 * Polylang helpers (no-ops when the plugin is not active).
 * ------------------------------------------------------------- */

/**
 * Current language slug ('' when single-language).
 */
function kabel_pll_current_language(): string {
        return kabel_polylang_active() ? (string) pll_current_language( 'slug' ) : '';
}

/**
 * Translate a post ID if a translation exists; otherwise return as-is.
 */
function kabel_pll_post_id( int $post_id ): int {
        if ( function_exists( 'pll_get_post' ) ) {
                $translated = pll_get_post( $post_id );
                if ( $translated ) {
                        return (int) $translated;
                }
        }
        return $post_id;
}

/**
 * Register theme strings with Polylang for manual translation when active.
 * (Called on init; harmless no-op otherwise.)
 */
function kabel_pll_register_strings(): void {
        if ( ! function_exists( 'pll_register_string' ) ) {
                return;
        }
        $strings = array(
                'cta'  => kabel_get_option( 'kabel_cta_text', 'Consult Now' ),
                'hero' => kabel_get_option( 'kabel_hero_eyebrow', 'GLOBAL HIGH-END DECORATIVE PAINT BRAND' ),
        );
        foreach ( $strings as $key => $value ) {
                pll_register_string( 'kabel_' . $key, $value, 'Kabel Theme' );
        }
}
add_action( 'init', 'kabel_pll_register_strings', 30 );
