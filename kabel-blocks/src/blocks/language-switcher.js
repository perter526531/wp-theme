/**
 * kbl/language-switcher — multi-language switcher for the header.
 *
 * display = 'dropdown' (default): globe icon + current language + chevron
 * button opening a panel with every language — the shape every modern
 * switcher (GTranslate, Blocksy, Elementor) uses. display = 'inline': the
 * flat link row.
 *
 * Source = 'custom': links go wherever the owner points them (a Polylang
 * URL, a separate microsite, …) — the full item editor lives in the sidebar.
 *
 * Source = 'gtranslate': the GTranslate PLUGIN owns the language list
 * (single source of truth). The sidebar shows the plugin's languages
 * read-only with a resync button + a settings link, and a render_block
 * bridge (inc/gtranslate.php) rebuilds the front-end switcher from the live
 * plugin settings on every request — languages added in GTranslate appear
 * in the header immediately, removed ones disappear.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps } from '@wordpress/block-editor';
import { PanelBody, Button, Notice } from '@wordpress/components';
import { cx, toStyle, iconSvg, applyVisibility, applySpacing, spacingPairs } from '../util.js';
import {
        KblInspector,
        SegButtons,
        RangeRow,
        SelectRow,
        TextRow,
        ToggleRow,
        ColorsPanel,
        SpacingPanel,
        VisibilityPanel,
} from '../inspector.js';

const DEFAULT_ITEMS = [
        { code: 'EN', label: 'English', url: '', current: true },
        { code: '中文', label: '中文', url: '', current: false },
        { code: 'IT', label: 'Italiano', url: '', current: false },
];

/** Remap the seed codes to GTranslate codes when the source switches and
 * no live plugin data is available (plugin missing/not active). */
const GT_CODE_MAP = { EN: 'en', '中文': 'zh-CN', IT: 'it' };

/** GTranslate presence, injected server-side (inc/assets.php). */
function gtActive() {
        return !! ( window.KBL3_ENV && window.KBL3_ENV.gtranslate );
}

/** The plugin's current language settings, injected server-side:
 * { default: 'en', languages: [ { code, label } ] } — null when absent. */
function gtData() {
        const d = window.KBL3_ENV && window.KBL3_ENV.gtLanguages;
        return d && Array.isArray( d.languages ) && d.languages.length ? d : null;
}

/** Item rows for the block attrs from a gtData() payload. */
function gtItems( data ) {
        return data.languages.map( ( l ) => ( {
                code: l.code,
                label: l.label,
                url: '',
                current: l.code === data.default,
        } ) );
}

/** Refresh the injected plugin data through the REST twin
 * (kbl3/v1/gtranslate/languages) and apply it to the block items. */
function gtResync( setAttributes, onDone ) {
        const api = window.wp && window.wp.apiFetch;
        if ( ! api ) {
                if ( onDone ) {
                        onDone( false );
                }
                return;
        }
        api( { path: '/kbl3/v1/gtranslate/languages' } )
                .then( ( res ) => {
                        if ( res && Array.isArray( res.languages ) && res.languages.length ) {
                                setAttributes( {
                                        items: res.languages.map( ( l ) => ( {
                                                code: l.code,
                                                label: l.label,
                                                url: '',
                                                current: l.code === res.default,
                                        } ) ),
                                } );
                                if ( window.KBL3_ENV ) {
                                        window.KBL3_ENV.gtLanguages = { default: res.default, languages: res.languages };
                                }
                                if ( onDone ) {
                                        onDone( true );
                                }
                                return;
                        }
                        if ( onDone ) {
                                onDone( false );
                        }
                } )
                .catch( () => {
                        if ( onDone ) {
                                onDone( false );
                        }
                } );
}

register( 'kbl/language-switcher', {
        apiVersion: 3,
        title: __( 'Language switcher (Kabel)', 'kabel-blocks' ),
        description: __(
                'Multi-language switcher for the header — dropdown button or inline links.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'language', 'multilingual', 'switcher', 'gtranslate', 'dropdown', '语言', '多语言', '切换'],
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
                source: { type: 'string', default: 'custom' },
                items: { type: 'array', default: DEFAULT_ITEMS },
                display: { type: 'string', default: 'dropdown' },
                color: { type: 'string' },
                activeColor: { type: 'string' },
                fontSize: { type: 'number', default: 13 },
                align: { type: 'string', default: 'left' },
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
                const items = a.items == null ? DEFAULT_ITEMS : a.items;
                const isGT = ( a.source || 'custom' ) === 'gtranslate';
                const isDropdown = ( a.display || 'dropdown' ) !== 'inline';

                const className = langClass( a );
                const style = langStyle( a );

                const updateItem = ( i, patch ) => {
                        setAttributes( {
                                items: items.map( ( item, j ) => ( j === i ? { ...item, ...patch } : item ) ),
                        } );
                };
                const removeItem = ( i ) => {
                        setAttributes( {
                                items: items.filter( ( _, j ) => j !== i ),
                        } );
                };
                const moveItem = ( i, dir ) => {
                        const j = i + dir;
                        if ( j < 0 || j >= items.length ) {
                                return;
                        }
                        const next = items.slice();
                        const [ moved ] = next.splice( i, 1 );
                        next.splice( j, 0, moved );
                        setAttributes( { items: next } );
                };

                /* Entering GTranslate mode: adopt the plugin's language list
                 * (single source of truth); the seed-code remap is only the
                 * no-plugin fallback. */
                const switchSource = ( v ) => {
                        if ( v === 'gtranslate' && ! isGT ) {
                                const data = gtData();
                                if ( data ) {
                                        setAttributes( {
                                                source: 'gtranslate',
                                                items: gtItems( data ),
                                        } );
                                        return;
                                }
                                setAttributes( {
                                        source: 'gtranslate',
                                        items: items.map( ( item ) => ( {
                                                ...item,
                                                code: GT_CODE_MAP[ item.code ] || item.code,
                                        } ) ),
                                } );
                                return;
                        }
                        setAttributes( { source: v === 'gtranslate' ? 'gtranslate' : 'custom' } );
                };

                const itemsEditor = el(
                        PanelBody,
                        { title: __( 'Languages', 'kabel-blocks' ), initialOpen: true },
                        items.map( ( item, i ) =>
                                el(
                                        'div',
                                        { className: 'kbl3-item-row', key: i },
                                        el( TextRow, {
                                                className: 'kbl3-lang-code',
                                                label: __( 'Language', 'kabel-blocks' ) + ' ' + ( i + 1 ) + ' — ' + ( isGT ? __( 'GTranslate code', 'kabel-blocks' ) : __( 'Code', 'kabel-blocks' ) ),
                                                value: item.code,
                                                onChange: ( v ) => updateItem( i, { code: v } ),
                                        } ),
                                        el( TextRow, {
                                                label: __( 'Name', 'kabel-blocks' ),
                                                value: item.label,
                                                onChange: ( v ) => updateItem( i, { label: v } ),
                                        } ),
                                        isGT
                                                ? el( 'p', { className: 'kbl3-panel-hint' },
                                                        __( 'GTranslate switches the language — no link needed. Use its codes, e.g. en, zh-CN, it.', 'kabel-blocks' ) )
                                                : el( TextRow, {
                                                        label: __( 'Link', 'kabel-blocks' ),
                                                        value: item.url,
                                                        onChange: ( v ) => updateItem( i, { url: v } ),
                                                        placeholder: '/en/',
                                                } ),
                                        el( ToggleRow, {
                                                label: __( 'Current language', 'kabel-blocks' ),
                                                checked: !! item.current,
                                                onChange: ( v ) => updateItem( i, { current: v } ),
                                        } ),
                                        el(
                                                'div',
                                                { className: 'kbl3-item-row__btns' },
                                                el( Button, {
                                                        size: 'small',
                                                        variant: 'secondary',
                                                        disabled: i === 0,
                                                        onClick: () => moveItem( i, -1 ),
                                                        label: __( 'Move up', 'kabel-blocks' ),
                                                        icon: 'arrow-up',
                                                } ),
                                                el( Button, {
                                                        size: 'small',
                                                        variant: 'secondary',
                                                        disabled: i === items.length - 1,
                                                        onClick: () => moveItem( i, 1 ),
                                                        label: __( 'Move down', 'kabel-blocks' ),
                                                        icon: 'arrow-down',
                                                } ),
                                                el( Button, {
                                                        size: 'small',
                                                        variant: 'secondary',
                                                        isDestructive: true,
                                                        onClick: () => removeItem( i ),
                                                        label: __( 'Remove language', 'kabel-blocks' ),
                                                        icon: 'no',
                                                } )
                                        )
                                )
                        ),
                        el(
                                Button,
                                {
                                        variant: 'secondary',
                                        size: 'small',
                                        onClick: () =>
                                                setAttributes( {
                                                        items: [
                                                                ...items,
                                                                { code: isGT ? 'es' : 'NEW', label: '', url: '', current: false },
                                                        ],
                                                } ),
                                },
                                __( 'Add language', 'kabel-blocks' )
                        )
                );

                /* GTranslate mode: the plugin's settings own the list — show
                 * it read-only with a resync button and a settings link, never
                 * a second editable copy. */
                const gtPanel = el(
                        PanelBody,
                        { title: __( 'Languages', 'kabel-blocks' ), initialOpen: true },
                        el(
                                Notice,
                                {
                                        status: gtActive() ? 'info' : 'warning',
                                        isDismissible: false,
                                        className: 'kbl3-gt-notice',
                                },
                                gtActive()
                                        ? __( 'The language list follows the GTranslate plugin — pick languages under Settings → GTranslate and the header updates automatically.', 'kabel-blocks' )
                                        : __( 'GTranslate plugin not detected. Install & activate it (Plugins → Add New → "GTranslate"), then the languages it exposes appear here automatically.', 'kabel-blocks' )
                        ),
                        gtActive()
                                ? el(
                                        'div',
                                        { className: 'kbl3-gt-list' },
                                        items.map( ( item, i ) =>
                                                el(
                                                        'div',
                                                        { className: 'kbl3-gt-row', key: i },
                                                        el( 'span', { className: 'kbl3-gt-row__label' }, item.label || item.code ),
                                                        el( 'span', { className: 'kbl3-gt-row__code' }, item.code ),
                                                        item.current
                                                                ? el( 'span', { className: 'kbl3-gt-row__badge' }, __( 'Default', 'kabel-blocks' ) )
                                                                : null
                                                )
                                        ),
                                        el(
                                                'div',
                                                { className: 'kbl3-gt-actions' },
                                                el(
                                                        Button,
                                                        {
                                                                variant: 'secondary',
                                                                size: 'small',
                                                                onClick: () => gtResync( setAttributes ),
                                                        },
                                                        __( 'Resync from GTranslate', 'kabel-blocks' )
                                                ),
                                                window.KBL3_ENV && window.KBL3_ENV.gtSettingsUrl
                                                        ? el(
                                                                        'a',
                                                                        {
                                                                                href: window.KBL3_ENV.gtSettingsUrl,
                                                                                target: '_blank',
                                                                                rel: 'noopener noreferrer',
                                                                                className: 'kbl3-gt-settings-link',
                                                                        },
                                                                        __( 'Open GTranslate settings ↗', 'kabel-blocks' )
                                                          )
                                                        : null
                                        ),
                                        el(
                                                'p',
                                                { className: 'kbl3-panel-hint' },
                                                __( 'Saved for the no-plugin fallback — the front end always rebuilds this row from the live GTranslate settings.', 'kabel-blocks' )
                                        )
                                )
                                : null
                );

                const gtStatus = el(
                        Notice,
                        {
                                status: gtActive() ? 'info' : 'warning',
                                isDismissible: false,
                                className: 'kbl3-gt-notice',
                        },
                        gtActive()
                                ? __( 'GTranslate is active — these links switch the language live on the front end.', 'kabel-blocks' )
                                : __( 'GTranslate plugin not detected. Install & activate it (Plugins → Add New → "GTranslate"), then these links switch languages automatically.', 'kabel-blocks' )
                );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        layout: el( 'div', null,
                                                el( SelectRow, {
                                                        label: __( 'Language source', 'kabel-blocks' ),
                                                        value: a.source || 'custom',
                                                        onChange: switchSource,
                                                        options: [
                                                                { value: 'custom', label: __( 'Custom links', 'kabel-blocks' ) },
                                                                { value: 'gtranslate', label: __( 'GTranslate (auto translate)', 'kabel-blocks' ) },
                                                        ],
                                                } ),
                                                isGT ? gtStatus : null,
                                                el( SegButtons, {
                                                        label: __( 'Layout', 'kabel-blocks' ),
                                                        value: isDropdown ? 'dropdown' : 'inline',
                                                        onChange: ( v ) => setAttributes( { display: v } ),
                                                        options: [
                                                                { value: 'dropdown', label: __( 'Dropdown button', 'kabel-blocks' ) },
                                                                { value: 'inline', label: __( 'Inline links', 'kabel-blocks' ) },
                                                        ],
                                                } ),
                                                el( SegButtons, {
                                                        label: __( 'Alignment', 'kabel-blocks' ),
                                                        value: a.align || 'left',
                                                        onChange: ( v ) => setAttributes( { align: v } ),
                                                        options: [
                                                                { value: 'left', label: __( 'Left', 'kabel-blocks' ) },
                                                                { value: 'center', label: __( 'Center', 'kabel-blocks' ) },
                                                                { value: 'right', label: __( 'Right', 'kabel-blocks' ) },
                                                        ],
                                                } ),
                                                isGT ? gtPanel : itemsEditor
                                        ),
                                        style: el( 'div', null,
                                                el( RangeRow, {
                                                        label: __( 'Font size (px)', 'kabel-blocks' ),
                                                        value: a.fontSize,
                                                        initial: 13,
                                                        onChange: ( v ) => setAttributes( { fontSize: v } ),
                                                        min: 10,
                                                        max: 24,
                                                } ),
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Text color', 'kabel-blocks' ),
                                                                        value: a.color,
                                                                        onChange: ( v ) => setAttributes( { color: v } ),
                                                                        isText: true,
                                                                },
                                                                {
                                                                        label: __( 'Active language color', 'kabel-blocks' ),
                                                                        value: a.activeColor,
                                                                        onChange: ( v ) => setAttributes( { activeColor: v } ),
                                                                },
                                                        ],
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
                        /* NOTE: langRoot() already returns a React element —
                         * do NOT wrap it in el(); createElement(reactElement)
                         * treats the element as a component type and dies
                         * with React #130 (bubbles into the parent column). */
                        langRoot( a, items, isGT, {
                                className,
                                style,
                                id: a.anchor || undefined,
                        }, useBlockProps )
                );
        },

        save( { attributes } ) {
                const a = attributes;
                const items = a.items == null ? DEFAULT_ITEMS : a.items;
                const isGT = ( a.source || 'custom' ) === 'gtranslate';

                return langRoot( a, items, isGT, {
                        className: langClass( a ),
                        style: langStyle( a ),
                        id: a.anchor || undefined,
                }, useBlockProps.save );
        },
} );

/** Shared root element for edit + save — the byte-twin of PHP
 * kbl3_lang_markup(): dropdown wraps a toggle button + panel <ul> in a
 * <div>, inline stays the flat <ul> (v3.9 markup). useProps is the
 * block-props hook of the calling side (edit: useBlockProps, save:
 * useBlockProps.save). */
function langRoot( a, items, isGT, props, useProps ) {
        const links = items.map( ( item, i ) =>
                el(
                        'li',
                        { className: 'kbl3-lang__item', key: i },
                        el( 'a', {
                                className: cx( 'kbl3-lang__link', item.current && 'is-current', isGT && 'notranslate' ),
                                href: isGT ? '#' : ( item.url || '#' ),
                                'data-gt-lang': isGT ? item.code : undefined,
                        }, item.label )
                )
        );

        if ( ( a.display || 'dropdown' ) === 'inline' ) {
                return el( 'ul', useProps( { ...props } ), links );
        }

        let currentLabel = '';
        for ( const item of items ) {
                if ( item.current ) {
                        currentLabel = item.label;
                        break;
                }
        }
        if ( ! currentLabel ) {
                currentLabel = items.length ? items[ 0 ].label : '—';
        }

        return el(
                'div',
                useProps( { ...props } ),
                el(
                        'button',
                        {
                                className: 'kbl3-lang__toggle',
                                type: 'button',
                                'aria-expanded': 'false',
                                'aria-haspopup': 'true',
                                'aria-label': __( 'Switch language', 'kabel-blocks' ),
                        },
                        el( 'span', { className: 'kbl3-lang__globe', 'aria-hidden': 'true' }, iconSvg( 'globe' ) ),
                        el( 'span', { className: 'kbl3-lang__current' }, currentLabel ),
                        el( 'span', { className: 'kbl3-lang__chevron', 'aria-hidden': 'true' }, iconSvg( 'chevron-down' ) )
                ),
                el( 'ul', { className: 'kbl3-lang__menu' }, links )
        );
}

/** Shared by edit + save. PHP twin: kbl3_language_switcher(). */
function langClass( a ) {
        const align = a.align || 'left';
        const dropdown = ( a.display || 'dropdown' ) !== 'inline';
        return cx(
                'kbl3-lang',
                dropdown && 'kbl3-lang--dropdown',
                align === 'center' && 'kbl3-lang--center',
                align === 'right' && 'kbl3-lang--right',
                ...applyVisibility( a ),
                ...applySpacing( a )
        );
}

/** Shared by edit + save. PHP twin: kbl3_language_switcher(). */
function langStyle( a ) {
        return toStyle( [
                [ '--kbl3-lang-fg', a.color ],
                [ '--kbl3-lang-active', a.activeColor ],
                [ '--kbl3-fs', ( a.fontSize ?? 13 ) + 'px' ],
                ...spacingPairs( a ),
        ] );
}
