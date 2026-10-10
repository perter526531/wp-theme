/**
 * kbl/navigation — the site header menu, READ FROM THE BACKEND.
 *
 * v14 rework: the block no longer stores its own item tree. It renders
 * whatever menu lives in 外观 → 菜单 (Appearance → Menus) — the "Menu
 * source" control picks which one, with Auto following the theme's
 * Primary Navigation location. Menu items are managed on the admin Menus
 * screen; this block only decides presentation: alignment, gap, collapse
 * breakpoint, mega dropdowns, colors and typography.
 *
 * Server-side: dynamic block (save() returns null) — the render callback
 * resolves the menu at render time (inc/dynamic.php → kbl3_navigation_html)
 * so editing the menu updates every page instantly. The editor canvas gets
 * the same tree through window.KblMenus (injected by inc/assets.php).
 *
 * PHP twins: kbl3_navigation_html() (markup), kbl3_navigation_attrs()
 * (attribute assembly) in inc/emitter.php.
 */
import { el, Fr } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { PanelBody, ExternalLink, Notice } from '@wordpress/components';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs, typographyPairs } from '../util.js';
import {
        KblInspector,
        RangeRow,
        AlignButtons,
        SegButtons,
        SelectRow,
        ToggleRow,
        ColorsPanel,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
} from '../inspector.js';

const BREAK_OPTIONS = [
        { value: 'always', label: __( 'Always', 'kabel-blocks' ) },
        { value: '1280', label: '1280px' },
        { value: '1024', label: '1024px' },
        { value: '768', label: '768px' },
        { value: 'never', label: __( 'Never', 'kabel-blocks' ) },
];

/* Fallback preview when the site has no menus yet (matches
 * kbl3_navigation_defaults() in the emitter). */
const FALLBACK_ITEMS = [
        { label: 'Home', url: '/', newTab: false, mega: false, sub: [] },
        { label: 'About', url: '/about/', newTab: false, mega: false, sub: [] },
        { label: 'Contact', url: '/contact/', newTab: false, mega: false, sub: [] },
];

/** Menus injected by PHP (window.KblMenus = { adminUrl, menus: [...] }). */
function menuData() {
        return ( typeof window !== 'undefined' && window.KblMenus ) || { adminUrl: '', menus: [] };
}

/** Resolve the items the canvas should preview for the current source. */
function resolveItems( source ) {
        const { menus } = menuData();
        if ( ! menus.length ) {
                return { items: FALLBACK_ITEMS, menu: null };
        }
        const menu = '' === source
                ? menus[ 0 ]
                : menus.find( ( m ) => String( m.id ) === String( source ) ) || menus[ 0 ];
        return { items: menu.items || [], menu };
}

/* Shared style pairs — the attribute order is part of the twin contract. */
function navPairs( a ) {
        return [
                [ '--kbl3-nav-gap', `${ a.gap ?? 4 }px` ],
                [ '--kbl3-nav-link', a.textColor ],
                [ '--kbl3-nav-hover', a.hoverColor ],
                [ '--kbl3-nav-accent', a.accentColor ],
                [ '--kbl3-nav-sub-bg', a.subBgColor ],
                [ '--kbl3-nav-sub-link', a.subTextColor ],
        ];
}

registerBlockType( 'kbl/navigation', {
        apiVersion: 2,
        title: __( 'Navigation (Kabel)', 'kabel-blocks' ),
        description: __(
                'The site header menu — reads a menu from Appearance → Menus; this block only styles it.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'navigation', 'menu', 'nav', 'links', 'header', '导航', '菜单', '导航菜单', '页眉' ],
        supports: {
                anchor: true,
                customClassName: true,
        },
        attributes: {
                anchor: {
                        type: 'string',
                        source: 'attribute',
                        attribute: 'id',
                        selector: '*',
                },
                /* Legacy v13 item tree — still parsed from old content, never
                 * written again (the block is menu-driven now). */
                items: { type: 'array' },
                source: { type: 'string', default: '' },
                align: { type: 'string' },
                gap: { type: 'number', default: 4 },
                mobileBreak: { type: 'string', default: '1024' },
                megaAuto: { type: 'boolean', default: true },
                textColor: { type: 'string' },
                hoverColor: { type: 'string' },
                accentColor: { type: 'string' },
                subBgColor: { type: 'string' },
                subTextColor: { type: 'string' },
                fontSize: { type: 'number' },
                fontSizeT: { type: 'number' },
                fontSizeM: { type: 'number' },
                fontWeight: { type: 'number' },
                lineHeight: { type: 'number' },
                letterSpacing: { type: 'number' },
                textTransform: { type: 'string' },
                hideD: { type: 'boolean' },
                hideT: { type: 'boolean' },
                hideM: { type: 'boolean' },
                mt: { type: 'number' },
                mb: { type: 'number' },
                mtM: { type: 'number' },
                mbM: { type: 'number' },
        },

        edit( { attributes, setAttributes } ) {
                const a = attributes;
                const { adminUrl, menus } = menuData();
                const { items, menu } = resolveItems( a.source );

                const className = cx(
                        'kbl3-nav',
                        a.align === 'center' && 'is-center',
                        a.align === 'right' && 'is-right',
                        `kbl3-nav--mbp-${ a.mobileBreak ?? '1024' }`,
                        ...applyVisibility( a ),
                        ...applySpacing( a ),
                );
                const style = toStyle( [
                        ...navPairs( a ),
                        ...typographyPairs( a, { lh: false } ),
                        ...spacingPairs( a ),
                ] );

                const isMega = ( item ) =>
                        !! item.mega || ( ( a.megaAuto ?? true ) && ( item.sub || [] ).length >= 4 );

                /* ---------------- Inspector ---------------- */
                const sourceOptions = [
                        { value: '', label: __( 'Auto — Primary Navigation location', 'kabel-blocks' ) },
                        ...menus.map( ( m ) => ( { value: String( m.id ), label: m.name } ) ),
                ];

                const layoutPanel = el(
                        PanelBody,
                        { title: __( 'Menu source', 'kabel-blocks' ), initialOpen: true },
                        el( SelectRow, {
                                label: __( 'Menu', 'kabel-blocks' ),
                                value: a.source ?? '',
                                options: sourceOptions,
                                onChange: ( v ) => setAttributes( { source: v } ),
                        } ),
                        el( 'p', { className: 'kbl3-nav__meta' },
                                menu
                                        ? sprintf( __( '%d items · managed in Appearance → Menus', 'kabel-blocks' ), items.length )
                                        : __( 'No menus yet — items below are a placeholder.', 'kabel-blocks' )
                        ),
                        adminUrl
                                ? el(
                                        ExternalLink,
                                        { href: adminUrl + 'nav-menus.php', target: '_blank', rel: 'noreferrer' },
                                        __( 'Manage menu items →', 'kabel-blocks' )
                                )
                                : null
                );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        layout: el(
                                                'div',
                                                null,
                                                layoutPanel,
                                                el( AlignButtons, {
                                                        value: a.align,
                                                        onChange: ( v ) => setAttributes( { align: v } ),
                                                } ),
                                                el( RangeRow, {
                                                        label: __( 'Gap', 'kabel-blocks' ),
                                                        value: a.gap,
                                                        onChange: ( v ) => setAttributes( { gap: v ?? 4 } ),
                                                        min: 0,
                                                        max: 48,
                                                } ),
                                                el( SegButtons, {
                                                        label: __( 'Collapse below', 'kabel-blocks' ),
                                                        value: a.mobileBreak ?? '1024',
                                                        onChange: ( v ) => setAttributes( { mobileBreak: v } ),
                                                        options: BREAK_OPTIONS,
                                                } ),
                                                el( ToggleRow, {
                                                        label: __( 'Wide dropdowns', 'kabel-blocks' ),
                                                        checked: a.megaAuto ?? true,
                                                        onChange: ( v ) => setAttributes( { megaAuto: v } ),
                                                        help: __( 'Parents with 4+ items (or the mega class) use the wide two-column dropdown.', 'kabel-blocks' ),
                                                } )
                                        ),
                                        style: el(
                                                'div',
                                                null,
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Link color', 'kabel-blocks' ),
                                                                        value: a.textColor,
                                                                        onChange: ( v ) => setAttributes( { textColor: v } ),
                                                                        isText: true,
                                                                },
                                                                {
                                                                        label: __( 'Hover color', 'kabel-blocks' ),
                                                                        value: a.hoverColor,
                                                                        onChange: ( v ) => setAttributes( { hoverColor: v } ),
                                                                },
                                                                {
                                                                        label: __( 'Underline color', 'kabel-blocks' ),
                                                                        value: a.accentColor,
                                                                        onChange: ( v ) => setAttributes( { accentColor: v } ),
                                                                },
                                                                {
                                                                        label: __( 'Dropdown background', 'kabel-blocks' ),
                                                                        value: a.subBgColor,
                                                                        onChange: ( v ) => setAttributes( { subBgColor: v } ),
                                                                        isBackground: true,
                                                                },
                                                                {
                                                                        label: __( 'Dropdown link color', 'kabel-blocks' ),
                                                                        value: a.subTextColor,
                                                                        onChange: ( v ) => setAttributes( { subTextColor: v } ),
                                                                },
                                                        ],
                                                } ),
                                                el( TypographyPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                        include: { lh: false },
                                                } )
                                        ),
                                        advanced: el( 'div', null,
                                                el( SpacingPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                } ),
                                                el( VisibilityPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                }
                        ),
                        el(
                                'nav',
                                {
                                        ...useBlockProps( { className, style, id: a.anchor || undefined } ),
                                        'data-kbl-nav': '',
                                },
                                el(
                                        'button',
                                        { className: 'kbl3-nav__toggle', type: 'button', 'aria-expanded': 'false' },
                                        el( 'span', { className: 'kbl3-nav__bars', 'aria-hidden': true },
                                                el( 'span' ), el( 'span' ), el( 'span' ) ),
                                        el( 'span', { className: 'screen-reader-text' }, 'Menu' )
                                ),
                                el(
                                        'ul',
                                        { className: 'kbl3-nav__list' },
                                        items.map( ( item, i ) => {
                                                const subs = Array.isArray( item.sub ) ? item.sub : [];
                                                return el(
                                                        'li',
                                                        {
                                                                key: i,
                                                                className: cx( 'kbl3-nav__item', subs.length ? 'has-sub' : '', isMega( item ) ? 'is-mega' : '' ),
                                                        },
                                                        /* span, not a: the tree is read-only (it lives in
                                                         * 外观 → 菜单) and links must not navigate the canvas. */
                                                        el(
                                                                'span',
                                                                { className: 'kbl3-nav__link' },
                                                                item.label
                                                        ),
                                                        subs.length
                                                                ? el(
                                                                        'ul',
                                                                        { className: cx( 'kbl3-nav__sub', isMega( item ) ? 'kbl3-nav__sub--mega' : '' ) },
                                                                        subs.map( ( s, j ) =>
                                                                                el(
                                                                                        'li',
                                                                                        { key: j, className: 'kbl3-nav__subitem' },
                                                                                        el( 'span', { className: 'kbl3-nav__sublink' }, s.label )
                                                                                )
                                                                        )
                                                                )
                                                                : null
                                                );
                                        } )
                                ),
                                ! menus.length
                                        ? el( Notice, {
                                                status: 'warning',
                                                isDismissible: false,
                                                className: 'kbl3-nav__hint',
                                        },
                                        __( 'This menu is read from Appearance → Menus. Create a menu there and it will appear here.', 'kabel-blocks' )
                                        )
                                        : null
                        )
                );
        },

        save() {
                /* Dynamic block — the server renders the resolved menu tree. */
                return null;
        },

        deprecated: [
                {
                        /*
                         * v13 — the nav stored its own item tree as static
                         * markup (save() rendered the full <ul>). Sites
                         * upgraded from v13 carry that markup everywhere
                         * (the site-part header plus any page it was dropped
                         * on), and the v14 dynamic form (save() = null)
                         * flags it as "Block contains unexpected or invalid
                         * content". Declaring the v13 save here lets
                         * WordPress match the stored markup, show it
                         * warning-free and migrate the block to the
                         * menu-driven form the next time it is saved.
                         * Kept verbatim from 3.5.0 — do not hand-edit.
                         */
                        attributes: {
                                anchor: {
                                        type: 'string',
                                        source: 'attribute',
                                        attribute: 'id',
                                        selector: '*',
                                },
                                items: { type: 'array' },
                                align: { type: 'string' },
                                gap: { type: 'number', default: 4 },
                                mobileBreak: { type: 'string', default: '1024' },
                                textColor: { type: 'string' },
                                hoverColor: { type: 'string' },
                                accentColor: { type: 'string' },
                                subBgColor: { type: 'string' },
                                subTextColor: { type: 'string' },
                                fontSize: { type: 'number' },
                                fontSizeT: { type: 'number' },
                                fontSizeM: { type: 'number' },
                                fontWeight: { type: 'number' },
                                lineHeight: { type: 'number' },
                                letterSpacing: { type: 'number' },
                                textTransform: { type: 'string' },
                                hideD: { type: 'boolean' },
                                hideT: { type: 'boolean' },
                                hideM: { type: 'boolean' },
                                mt: { type: 'number' },
                                mb: { type: 'number' },
                                mtM: { type: 'number' },
                                mbM: { type: 'number' },
                        },
                        supports: {
                                anchor: true,
                                customClassName: true,
                        },
                        isEligible( attributes ) {
                                /* Only stored-trees take this path; a v14
                                 * block never carries items. */
                                return Array.isArray( attributes.items ) && attributes.items.length > 0;
                        },
                        save( { attributes } ) {
                                const a = attributes;
                                const items = Array.isArray( a.items ) ? a.items : [];

                                const className = cx(
                                        'kbl3-nav',
                                        a.align === 'center' && 'is-center',
                                        a.align === 'right' && 'is-right',
                                        `kbl3-nav--mbp-${ a.mobileBreak ?? '1024' }`,
                                        ...applyVisibility( a ),
                                        ...applySpacing( a ),
                                );
                                const style = toStyle( [
                                        ...navPairs( a ),
                                        ...typographyPairs( a, { lh: false } ),
                                        ...spacingPairs( a ),
                                ] );

                                return el(
                                        'nav',
                                        {
                                                ...useBlockProps.save( { className, style, id: a.anchor || undefined } ),
                                                'data-kbl-nav': '',
                                        },
                                        el(
                                                'button',
                                                { className: 'kbl3-nav__toggle', type: 'button', 'aria-expanded': 'false' },
                                                el( 'span', { className: 'kbl3-nav__bars', 'aria-hidden': true },
                                                        el( 'span' ), el( 'span' ), el( 'span' ) ),
                                                el( 'span', { className: 'screen-reader-text' }, 'Menu' )
                                        ),
                                        el(
                                                'ul',
                                                { className: 'kbl3-nav__list' },
                                                items.map( ( item, i ) => {
                                                        const subs = Array.isArray( item.sub ) ? item.sub : [];
                                                        return el(
                                                                'li',
                                                                {
                                                                        key: i,
                                                                        className: cx( 'kbl3-nav__item', subs.length ? 'has-sub' : '', item.mega ? 'is-mega' : '' ),
                                                                },
                                                                el(
                                                                        'a',
                                                                        {
                                                                                className: 'kbl3-nav__link',
                                                                                href: item.url || '#',
                                                                                ...( item.newTab ? { target: '_blank', rel: 'noopener noreferrer' } : {} ),
                                                                        },
                                                                        el( RichText.Content, { value: item.label } )
                                                                ),
                                                                subs.length
                                                                        ? el(
                                                                                'ul',
                                                                                { className: cx( 'kbl3-nav__sub', item.mega ? 'kbl3-nav__sub--mega' : '' ) },
                                                                                subs.map( ( s, j ) =>
                                                                                        el(
                                                                                                'li',
                                                                                                { key: j, className: 'kbl3-nav__subitem' },
                                                                                                el(
                                                                                                        'a',
                                                                                                        {
                                                                                                                className: 'kbl3-nav__sublink',
                                                                                                                href: s.url || '#',
                                                                                                                ...( s.newTab ? { target: '_blank', rel: 'noopener noreferrer' } : {} ),
                                                                                                        },
                                                                                                        el( RichText.Content, { value: s.label } )
                                                                                                )
                                                                                        )
                                                                                )
                                                                        )
                                                                        : null
                                                        );
                                                } )
                                        )
                                );
                        },
                },
        ],
} );
