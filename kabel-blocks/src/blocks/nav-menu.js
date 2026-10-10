/**
 * kbl/nav-menu — header navigation from a WordPress menu.
 *
 * Dynamic block: save() returns null; PHP renders the live menu (see
 * inc/dynamic.php, kbl3_render_nav_menu). The editor previews the real
 * records from the core data store. One nesting level is rendered — deeper
 * menu items flatten into the second level. Without JavaScript the list
 * stays fully expanded (the collapse CSS is scoped to html.kb-js).
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { Placeholder, Spinner } from '@wordpress/components';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs, typographyPairs } from '../util.js';
import { KblInspector, SelectRow, RangeRow, ColorsPanel, TypographyPanel, SpacingPanel, VisibilityPanel } from '../inspector.js';

register( 'kbl/nav-menu', {
        apiVersion: 3,
        title: __( 'Nav menu (Kabel)', 'kabel-blocks' ),
        description: __(
                'Header navigation from a WordPress menu — auto-highlights the current page.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'nav', 'menu', 'navigation', 'header', '导航', '菜单', '站内导航'],
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
                menu: { type: 'number' },
                fontSize: { type: 'number' },
                fontSizeT: { type: 'number' },
                fontSizeM: { type: 'number' },
                fontWeight: { type: 'number' },
                lineHeight: { type: 'number' },
                letterSpacing: { type: 'number' },
                textTransform: { type: 'string' },
                color: { type: 'string' },
                hoverColor: { type: 'string' },
                bgColor: { type: 'string' },
                gap: { type: 'number', default: 28 },
                padX: { type: 'number', default: 4 },
                padY: { type: 'number', default: 8 },
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

                /* Menus list (guarded: the smoke stub has no getMenus). */
                const menus = useSelect(
                        ( select ) => {
                                const core = select( 'core' );
                                return core && core.getMenus ? ( core.getMenus() || [] ) : [];
                        },
                        []
                );

                /* Auto menu: primary location → any assigned location → first
                 * menu — the same resolution order the PHP renderer uses
                 * (kbl3_render_nav_menu), so the preview matches the front. */
                const menuId = useSelect(
                        ( select ) => {
                                if ( a.menu ) {
                                        return a.menu;
                                }
                                const core = select( 'core' );
                                if ( core && core.getMenuLocations ) {
                                        const locs = core.getMenuLocations();
                                        if ( Array.isArray( locs ) ) {
                                                const primary = locs.find( ( l ) => l && l.name === 'primary' && l.menu );
                                                const any = primary || locs.find( ( l ) => l && l.menu );
                                                if ( any ) {
                                                        return any.menu;
                                                }
                                        }
                                }
                                return menus.length ? menus[ 0 ].id : 0;
                        },
                        [ a.menu, menus ]
                );
                const items = useSelect(
                        ( select ) => {
                                if ( ! menuId ) {
                                        return null;
                                }
                                const core = select( 'core' );
                                return core && core.getMenuItems
                                        ? ( core.getMenuItems( { menus: menuId, per_page: -1 } ) || null )
                                        : null;
                        },
                        [ menuId ]
                );
                const isLoading = items === null;

                const className = cx( 'kbl3-nav', ...applyVisibility( a ), ...applySpacing( a ) );
                const style = navStyle( a );

                const inspector = el(
                        KblInspector,
                        {
                                layout: el( 'div', null,
                                        el( SelectRow, {
                                                label: __( 'Menu', 'kabel-blocks' ),
                                                value: String( a.menu || 0 ),
                                                onChange: ( v ) => setAttributes( { menu: Number( v ) || undefined } ),
                                                options: [
                                                        { value: '0', label: __( 'Auto (primary menu location)', 'kabel-blocks' ) },
                                                        ...menus.map( ( m ) => ( { value: String( m.id ), label: m.name } ) ),
                                                ],
                                        } ),
                                        el( RangeRow, {
                                                label: __( 'Menu item gap (px)', 'kabel-blocks' ),
                                                value: a.gap,
                                                initial: 28,
                                                onChange: ( v ) => setAttributes( { gap: v } ),
                                                min: 0,
                                                max: 64,
                                        } ),
                                        el( RangeRow, {
                                                label: __( 'Item padding X (px)', 'kabel-blocks' ),
                                                value: a.padX,
                                                initial: 4,
                                                onChange: ( v ) => setAttributes( { padX: v } ),
                                                min: 0,
                                                max: 48,
                                        } ),
                                        el( RangeRow, {
                                                label: __( 'Item padding Y (px)', 'kabel-blocks' ),
                                                value: a.padY,
                                                initial: 8,
                                                onChange: ( v ) => setAttributes( { padY: v } ),
                                                min: 0,
                                                max: 32,
                                        } )
                                ),
                                style: el( 'div', null,
                                        el( TypographyPanel, {
                                                attrs: a,
                                                setAttrs: setAttributes,
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
                                                                label: __( 'Hover color', 'kabel-blocks' ),
                                                                value: a.hoverColor,
                                                                onChange: ( v ) => setAttributes( { hoverColor: v } ),
                                                        },
                                                        {
                                                                label: __( 'Background color', 'kabel-blocks' ),
                                                                value: a.bgColor,
                                                                onChange: ( v ) => setAttributes( { bgColor: v } ),
                                                                isBackground: true,
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
                );

                if ( ! menus.length ) {
                        return el(
                                Fr,
                                null,
                                inspector,
                                el(
                                        'div',
                                        useBlockProps( { className } ),
                                        el(
                                                Placeholder,
                                                { icon: 'menu', label: __( 'Nav menu (Kabel)', 'kabel-blocks' ) },
                                                __( 'No menus yet — create one under Appearance → Menus.', 'kabel-blocks' )
                                        )
                                )
                        );
                }

                const tree = navTree( items );

                return el(
                        Fr,
                        null,
                        inspector,
                        el(
                                'nav',
                                useBlockProps( { className, style, id: a.anchor || undefined } ),
                                el(
                                        'button',
                                        { type: 'button', className: 'kbl3-nav__toggle', 'aria-expanded': 'false' },
                                        el( 'span', { className: 'screen-reader-text' }, __( 'Menu', 'kabel-blocks' ) ),
                                        el(
                                                'svg',
                                                {
                                                        viewBox: '0 0 24 24',
                                                        fill: 'none',
                                                        stroke: 'currentColor',
                                                        strokeWidth: 1.8,
                                                        width: 20,
                                                        height: 20,
                                                        'aria-hidden': true,
                                                },
                                                el( 'path', { key: 1, d: 'M4 7h16' } ),
                                                el( 'path', { key: 2, d: 'M4 12h16' } ),
                                                el( 'path', { key: 3, d: 'M4 17h16' } )
                                        )
                                ),
                                isLoading
                                        ? el( Placeholder, null, el( Spinner, null ) )
                                        : el(
                                                'ul',
                                                { className: 'kbl3-nav__list' },
                                                tree.map( ( top ) =>
                                                        el(
                                                                'li',
                                                                {
                                                                        className: cx( 'kbl3-nav__item', top.children.length ? 'has-sub' : '' ),
                                                                        key: top.id,
                                                                },
                                                                el( 'a', { className: 'kbl3-nav__link', href: top.url || '#', onClick: ( e ) => e.preventDefault() }, top.title ),
                                                                top.children.length
                                                                        ? el(
                                                                                'svg',
                                                                                {
                                                                                        className: 'kbl3-nav__chev',
                                                                                        viewBox: '0 0 24 24',
                                                                                        fill: 'none',
                                                                                        stroke: 'currentColor',
                                                                                        strokeWidth: 1.8,
                                                                                        'aria-hidden': true,
                                                                                },
                                                                                el( 'path', { key: 1, d: 'M6 9l6 6 6-6' } )
                                                                          )
                                                                        : null,
                                                                top.children.length
                                                                        ? el(
                                                                                'ul',
                                                                                { className: 'kbl3-nav__sub' },
                                                                                top.children.map( ( child ) =>
                                                                                        el(
                                                                                                'li',
                                                                                                { className: 'kbl3-nav__item', key: child.id },
                                                                                                el( 'a', { className: 'kbl3-nav__link', href: child.url || '#', onClick: ( e ) => e.preventDefault() }, child.title )
                                                                                        )
                                                                                )
                                                                          )
                                                                        : null
                                                        )
                                                )
                                        )
                        )
                );
        },

        save() {
                return null;
        },
} );

/**
 * Flatten REST menu items into one nesting level (deeper levels join the
 * second level). Mirrors kbl3_nav_tree() in inc/dynamic.php. REST items
 * carry the parent as `parent` (unlike the PHP objects, which expose
 * `menu_item_parent`).
 */
function navTree( items ) {
        const list = ( items || [] ).map( ( it ) => ( {
                id: Number( it.id ) || 0,
                title: ( it.title && it.title.rendered ) || '',
                url: it.url || '',
                parent: Number( it.parent ) || 0,
                children: [],
        } ) );

        const byId = {};
        for ( const it of list ) {
                byId[ it.id ] = it;
        }

        const tops = [];
        const sub = {};
        for ( const it of list ) {
                if ( ! it.parent || ! byId[ it.parent ] || it.parent === it.id ) {
                        tops.push( it );
                        continue;
                }
                let anc = byId[ it.parent ];
                let guard = 0;
                while ( anc.parent && byId[ anc.parent ] && byId[ anc.parent ] !== anc && guard++ < 20 ) {
                        anc = byId[ anc.parent ];
                }
                if ( anc === it ) {
                        tops.push( it );
                        continue;
                }
                if ( ! sub[ anc.id ] ) {
                        sub[ anc.id ] = [];
                }
                sub[ anc.id ].push( it );
        }

        for ( const top of tops ) {
                top.children = sub[ top.id ] || [];
        }
        return tops;
}

/** Edit-side root style. PHP twin: kbl3_render_nav_menu() in inc/dynamic.php. */
function navStyle( a ) {
        return toStyle( [
                [ '--kbl3-nav-gap', ( a.gap ?? 28 ) + 'px' ],
                [ '--kbl3-nav-pad-x', ( a.padX ?? 4 ) + 'px' ],
                [ '--kbl3-nav-pad-y', ( a.padY ?? 8 ) + 'px' ],
                [ '--kbl3-nav-color', a.color ],
                [ '--kbl3-nav-hover', a.hoverColor ],
                [ 'color', a.color ],
                [ 'background-color', a.bgColor ],
                ...typographyPairs( a ),
                ...spacingPairs( a ),
        ] );
}
