/**
 * Header Builder — contextual settings panel.
 *
 * Renders the form matching the current selection:
 *   element id  -> one of the 11 element forms (contract settings, all fields)
 *   'row:<id>'  -> row settings (colors / padding / border / sticky / transparent)
 *   'mobile'    -> mobile controls + drawer list management
 */
import { el } from '@wordpress/element';
import { t } from './zh.js';
import { Icon, IconButton } from './ui.jsx';
import {
        ELEMENTS,
        SOCIAL_ICONS,
        updateElementSettings,
        updateRowSettings,
        updateMobile,
        removeOccurrence,
        pruneOrphans,
        moveInList,
} from './model.js';
import {
        TextRow,
        UrlRow,
        TextareaRow,
        SelectRow,
        SegRow,
        RangeRow,
        ColorRow,
        ToggleRow,
        Note,
} from './controls.jsx';

export function SettingsPanel( props ) {
        const { selectedId, model } = props;

        if ( selectedId === 'mobile' ) {
                return el( MobileForm, props );
        }
        if ( typeof selectedId === 'string' && selectedId.indexOf( 'row:' ) === 0 ) {
                const rowId = selectedId.slice( 4 );
                const row = model.rows.find( ( r ) => r.id === rowId );
                if ( row ) {
                        return el( RowForm, Object.assign( { row, rowId }, props ) );
                }
        }
        if ( selectedId && model.elements[ selectedId ] ) {
                return el( ElementForm, props );
        }
        return el(
                'div',
                { className: 'khb-empty' },
                el( Icon, { name: 'logo', size: 26 } ),
                el( 'p', null, t( 'Select an element, a row, or mobile settings to edit.' ) )
        );
}

function PanelHeader( { icon, label, meta } ) {
        return el(
                'div',
                { className: 'khb-panelhead' },
                el( Icon, { name: icon, size: 18 } ),
                el( 'span', { className: 'khb-panelhead__label' }, label ),
                meta ? el( 'span', { className: 'khb-panelhead__meta' }, meta ) : null
        );
}

/* ------------------------------------------------------------------ */
/* Element forms                                                       */
/* ------------------------------------------------------------------ */

function ElementForm( props ) {
        const { selectedId, model, setModel } = props;
        const def = model.elements[ selectedId ];
        const meta = ELEMENTS[ def.type ];
        const id = selectedId;

        const update = ( patch, group ) =>
                setModel( ( m ) => updateElementSettings( m, id, patch ), group );

        /** upd('fontSize')(value) — history-coalesced per field. */
        const upd = ( field ) => ( value ) =>
                update( { [ field ]: value }, 'set:' + id + ':' + field );

        const shared = { def, id, settings: def.settings, upd, update, props };

        return el(
                'div',
                null,
                el( PanelHeader, { icon: meta.icon, label: t( meta.label ) } ),
                el(
                        'div',
                        { className: 'khb-form' },
                        def.type === 'logo' ? el( LogoForm, shared ) :
                        def.type === 'menu' ? el( MenuForm, shared ) :
                        def.type === 'button' ? el( ButtonForm, shared ) :
                        def.type === 'search' ? el( SearchForm, shared ) :
                        def.type === 'social' ? el( SocialForm, shared ) :
                        def.type === 'html' ? el( HtmlForm, shared ) :
                        def.type === 'contact' ? el( ContactForm, shared ) :
                        def.type === 'text' ? el( TextForm, shared ) :
                        def.type === 'lang' ? el( LangForm, shared ) :
                        def.type === 'account' ? el( AccountForm, shared ) :
                        def.type === 'cart' ? el( CartForm, shared ) :
                        null
                )
        );
}

function LogoForm( { settings, upd, props } ) {
        const { siteName, logoUrl } = props;
        const openMedia = () => {
                if ( ! ( window.wp && window.wp.media ) ) {
                        props.showToast( t( 'Media library is not available.' ), 'error' );
                        return;
                }
                const frame = window.wp.media( {
                        title: t( 'Choose Image' ),
                        multiple: false,
                        library: { type: 'image' },
                } );
                frame.on( 'select', () => {
                        const att = frame.state().get( 'selection' ).first().toJSON();
                        upd( 'imgUrl' )( att.url );
                        upd( 'imgId' )( att.id || 0 );
                } );
                frame.open();
        };
        const preview =
                settings.source === 'image' && settings.imgUrl
                        ? settings.imgUrl
                        : settings.source === 'site'
                                ? logoUrl || ''
                                : '';
        return el(
                'div',
                null,
                el( SegRow, {
                        label: t( 'Source' ),
                        value: settings.source,
                        onChange: upd( 'source' ),
                        options: [
                                { value: 'site', label: t( 'Site identity' ) },
                                { value: 'text', label: t( 'Text' ) },
                                { value: 'image', label: t( 'Image' ) },
                        ],
                } ),
                settings.source === 'text'
                        ? el(
                                'div',
                                null,
                                el( TextRow, {
                                        label: t( 'Logo text' ),
                                        value: settings.text,
                                        placeholder: siteName || '',
                                        onChange: upd( 'text' ),
                                } ),
                                el( TextRow, {
                                        label: t( 'Tagline' ),
                                        value: settings.tagline,
                                        onChange: upd( 'tagline' ),
                                } ),
                                el( ToggleRow, {
                                        label: t( 'Show tagline' ),
                                        checked: settings.taglineShow,
                                        onChange: upd( 'taglineShow' ),
                                } )
                        )
                        : null,
                settings.source === 'image'
                        ? el(
                                'div',
                                null,
                                el(
                                        'div',
                                        { className: 'khb-media' },
                                        preview
                                                ? el( 'img', {
                                                        className: 'khb-media__thumb',
                                                        src: preview,
                                                        alt: '',
                                                } )
                                                : null,
                                        el(
                                                'div',
                                                { className: 'khb-media__actions' },
                                                el(
                                                        'button',
                                                        { type: 'button', className: 'khb-btn', onClick: openMedia },
                                                        el( Icon, { name: 'image', size: 15 } ),
                                                        el( 'span', null, preview ? t( 'Replace Image' ) : t( 'Choose Image' ) )
                                                ),
                                                settings.imgUrl
                                                        ? el(
                                                                'button',
                                                                {
                                                                        type: 'button',
                                                                        className: 'khb-btn',
                                                                        onClick: () => {
                                                                                upd( 'imgUrl' )( '' );
                                                                                upd( 'imgId' )( 0 );
                                                                        },
                                                                },
                                                                t( 'Remove Image' )
                                                        )
                                                        : null
                                        )
                                ),
                                el( RangeRow, {
                                        label: t( 'Logo width' ),
                                        value: settings.imgWidth,
                                        min: 40,
                                        max: 400,
                                        step: 2,
                                        unit: 'px',
                                        onChange: upd( 'imgWidth' ),
                                } )
                        )
                        : null,
                settings.source === 'site'
                        ? el(
                                Note,
                                null,
                                t( 'Uses the site logo from Appearance → Settings.' ) + ( siteName ? ' ' + t( 'Site name' ) + ': ' + siteName : '' )
                        )
                        : null
        );
}

function MenuForm( { settings, upd, props } ) {
        const menuOptions = [ { value: '0', label: t( 'Auto (first menu)' ) } ].concat(
                ( props.menus || [] ).map( ( m ) => ( { value: String( m.id ), label: m.name } ) )
        );
        return el(
                'div',
                null,
                el( SelectRow, {
                        label: t( 'Menu' ),
                        value: settings.menuId,
                        options: menuOptions,
                        onChange: ( v ) => upd( 'menuId' )( Number( v ) || 0 ),
                } ),
                el( RangeRow, {
                        label: t( 'Font size' ),
                        value: settings.fontSize,
                        min: 10,
                        max: 32,
                        unit: 'px',
                        onChange: upd( 'fontSize' ),
                } ),
                el( RangeRow, {
                        label: t( 'Gap' ),
                        value: settings.gap,
                        min: 0,
                        max: 96,
                        unit: 'px',
                        onChange: upd( 'gap' ),
                } ),
                el( SegRow, {
                        label: t( 'Alignment' ),
                        value: settings.align,
                        onChange: upd( 'align' ),
                        options: [
                                { value: 'left', label: t( 'Left' ) },
                                { value: 'center', label: t( 'Center' ) },
                                { value: 'right', label: t( 'Right' ) },
                        ],
                } ),
                el( ColorRow, { label: t( 'Link color' ), value: settings.color, onChange: upd( 'color' ) } ),
                el( ColorRow, {
                        label: t( 'Hover color' ),
                        value: settings.hoverColor,
                        onChange: upd( 'hoverColor' ),
                } ),
                el( SegRow, {
                        label: t( 'Menu style' ),
                        value: settings.style,
                        onChange: upd( 'style' ),
                        options: [
                                { value: 'plain', label: t( 'Plain' ) },
                                { value: 'underline', label: t( 'Underline' ) },
                                { value: 'pill', label: t( 'Pill' ) },
                        ],
                } ),
                el( ToggleRow, {
                        label: t( 'Submenu indicator' ),
                        checked: settings.subIndicator,
                        onChange: upd( 'subIndicator' ),
                } )
        );
}

function ButtonForm( { settings, upd } ) {
        return el(
                'div',
                null,
                el( TextRow, { label: t( 'Button text' ), value: settings.text, onChange: upd( 'text' ) } ),
                el( UrlRow, { label: t( 'URL' ), value: settings.url, onChange: upd( 'url' ) } ),
                el( SegRow, {
                        label: t( 'Variant' ),
                        value: settings.variant,
                        onChange: upd( 'variant' ),
                        options: [
                                { value: 'gold', label: t( 'Gold' ) },
                                { value: 'outline', label: t( 'Outline' ) },
                                { value: 'ghost', label: t( 'Ghost' ) },
                                { value: 'dark', label: t( 'Dark' ) },
                                { value: 'light', label: t( 'Light' ) },
                        ],
                } ),
                el( SegRow, {
                        label: t( 'Size' ),
                        value: settings.size,
                        onChange: upd( 'size' ),
                        options: [
                                { value: 's', label: t( 'Small' ) },
                                { value: 'm', label: t( 'Medium' ) },
                        ],
                } ),
                el( ToggleRow, {
                        label: t( 'Open in new tab' ),
                        checked: settings.target,
                        onChange: upd( 'target' ),
                } )
        );
}

function SearchForm( { settings, upd } ) {
        return el(
                'div',
                null,
                el( SegRow, {
                        label: t( 'Search style' ),
                        value: settings.style,
                        onChange: upd( 'style' ),
                        options: [
                                { value: 'icon', label: t( 'Icon' ) },
                                { value: 'field', label: t( 'Field' ) },
                        ],
                } ),
                el( TextRow, {
                        label: t( 'Placeholder' ),
                        value: settings.placeholder,
                        onChange: upd( 'placeholder' ),
                } ),
                el( RangeRow, {
                        label: t( 'Field width' ),
                        help: settings.style === 'icon' ? t( 'Applies to the field style.' ) : undefined,
                        value: settings.fieldWidth,
                        min: 120,
                        max: 420,
                        step: 2,
                        unit: 'px',
                        onChange: upd( 'fieldWidth' ),
                } )
        );
}

function SocialForm( { settings, update, id } ) {
        const items = Array.isArray( settings.items ) ? settings.items : [];
        const setItems = ( next, group ) => update( { items: next }, group );
        const iconOptions = SOCIAL_ICONS.map( ( [ value, label ] ) => ( {
                value,
                label: t( label ),
        } ) );
        const moveAt = ( i, dir ) => {
                const to = i + dir;
                if ( to < 0 || to >= items.length ) {
                        return;
                }
                const next = items.slice();
                const tmp = next[ i ];
                next[ i ] = next[ to ];
                next[ to ] = tmp;
                setItems( next );
        };
        return el(
                'div',
                null,
                el( 'div', { className: 'khb-field' }, el( 'label', { className: 'khb-field__label' }, t( 'Social links' ) ) ),
                items.length === 0
                        ? el( 'p', { className: 'khb-help' }, t( 'No icons yet — click “Add Icon” to create one.' ) )
                        : null,
                items.map( ( item, i ) =>
                        el(
                                'div',
                                { className: 'khb-item', key: i },
                                el(
                                        'div',
                                        { className: 'khb-item__tools' },
                                        el( IconButton, {
                                                icon: 'chevronUp',
                                                label: t( 'Move up' ),
                                                disabled: i === 0,
                                                onClick: () => moveAt( i, -1 ),
                                        } ),
                                        el( IconButton, {
                                                icon: 'chevronDown',
                                                label: t( 'Move down' ),
                                                disabled: i === items.length - 1,
                                                onClick: () => moveAt( i, 1 ),
                                        } ),
                                        el( IconButton, {
                                                icon: 'close',
                                                label: t( 'Remove item' ),
                                                tone: 'danger',
                                                onClick: () => setItems( items.filter( ( x, j ) => j !== i ) ),
                                        } )
                                ),
                                el(
                                        'select',
                                        {
                                                className: 'khb-select',
                                                value: item.icon,
                                                'aria-label': t( 'Icon' ),
                                                onChange: ( e ) =>
                                                        setItems(
                                                                items.map( ( x, j ) => ( j === i ? Object.assign( {}, x, { icon: e.target.value } ) : x ) )
                                                        ),
                                        },
                                        iconOptions.map( ( o ) =>
                                                el( 'option', { key: o.value, value: o.value }, o.label )
                                        )
                                ),
                                el( 'input', {
                                        className: 'khb-input',
                                        type: 'url',
                                        value: item.url || '',
                                        placeholder: 'https://',
                                        'aria-label': t( 'URL' ),
                                        onChange: ( e ) =>
                                                setItems(
                                                        items.map( ( x, j ) => ( j === i ? Object.assign( {}, x, { url: e.target.value } ) : x ) ),
                                                        'set:' + id + ':items:' + i + ':url'
                                                ),
                                } )
                        )
                ),
                el(
                        'button',
                        {
                                type: 'button',
                                className: 'khb-btn khb-btn--block',
                                onClick: () => setItems( items.concat( [ { icon: 'whatsapp', url: '' } ] ) ),
                        },
                        el( Icon, { name: 'plus', size: 15 } ),
                        el( 'span', null, t( 'Add Icon' ) )
                ),
                el( RangeRow, {
                        label: t( 'Icon size' ),
                        value: settings.size,
                        min: 12,
                        max: 32,
                        unit: 'px',
                        onChange: ( v ) => update( { size: v }, 'set:' + id + ':size' ),
                } ),
                el( RangeRow, {
                        label: t( 'Icon gap' ),
                        value: settings.gap,
                        min: 4,
                        max: 40,
                        unit: 'px',
                        onChange: ( v ) => update( { gap: v }, 'set:' + id + ':gap' ),
                } )
        );
}

function HtmlForm( { settings, upd, props } ) {
        return el(
                'div',
                null,
                el( TextareaRow, {
                        label: t( 'HTML content' ),
                        value: settings.content,
                        rows: 8,
                        mono: true,
                        placeholder: '<span>…</span>',
                        onChange: upd( 'content' ),
                } ),
                props.canUnfiltered
                        ? null
                        : el( Note, null, t( 'Will be filtered in safe mode because your account lacks the unfiltered_html capability.' ) )
        );
}

function ContactForm( { settings, upd } ) {
        return el(
                'div',
                null,
                el( TextRow, { label: t( 'Phone number' ), value: settings.phone, onChange: upd( 'phone' ) } ),
                el( TextRow, { label: t( 'Email address' ), value: settings.email, onChange: upd( 'email' ) } ),
                el( TextRow, { label: t( 'Address' ), value: settings.address, onChange: upd( 'address' ) } ),
                el( ToggleRow, { label: t( 'Show phone' ), checked: settings.showPhone, onChange: upd( 'showPhone' ) } ),
                el( ToggleRow, { label: t( 'Show email' ), checked: settings.showEmail, onChange: upd( 'showEmail' ) } ),
                el( ToggleRow, { label: t( 'Show address' ), checked: settings.showAddress, onChange: upd( 'showAddress' ) } )
        );
}

function TextForm( { settings, upd } ) {
        return el(
                'div',
                null,
                el( TextareaRow, {
                        label: t( 'Content' ),
                        value: settings.content,
                        onChange: upd( 'content' ),
                } ),
                el( RangeRow, {
                        label: t( 'Font size' ),
                        value: settings.size,
                        min: 10,
                        max: 40,
                        unit: 'px',
                        onChange: upd( 'size' ),
                } ),
                el( ColorRow, { label: t( 'Color' ), value: settings.color, onChange: upd( 'color' ) } )
        );
}

function LangForm( { settings, upd } ) {
        return el( SegRow, {
                label: t( 'Language style' ),
                value: settings.style,
                onChange: upd( 'style' ),
                options: [
                        { value: 'code', label: t( 'Code' ) },
                        { value: 'name', label: t( 'Name' ) },
                ],
        } );
}

function AccountForm( { settings, upd } ) {
        return el(
                'div',
                null,
                el( TextRow, { label: t( 'Login text' ), value: settings.loginText, onChange: upd( 'loginText' ) } ),
                el( UrlRow, { label: t( 'Login URL' ), value: settings.url, onChange: upd( 'url' ) } )
        );
}

function CartForm( { settings, upd, props } ) {
        return el(
                'div',
                null,
                props.woocommerce
                        ? null
                        : el( Note, null, t( 'Requires WooCommerce' ) ),
                el( ToggleRow, {
                        label: t( 'Show item count' ),
                        checked: settings.showCount,
                        onChange: upd( 'showCount' ),
                } )
        );
}

/* ------------------------------------------------------------------ */
/* Row form                                                            */
/* ------------------------------------------------------------------ */

function RowForm( { row, rowId, setModel } ) {
        const s = row.settings;
        const upd = ( field ) => ( value ) =>
                setModel(
                        ( m ) => updateRowSettings( m, rowId, { [ field ]: value } ),
                        'set:row:' + rowId + ':' + field
                );
        return el(
                'div',
                null,
                el( PanelHeader, { icon: 'menu', label: t( 'Row settings' ) } ),
                el(
                        'div',
                        { className: 'khb-form' },
                        el( ColorRow, { label: t( 'Background color' ), value: s.bg, onChange: upd( 'bg' ) } ),
                        el( ColorRow, { label: t( 'Text color' ), value: s.textColor, onChange: upd( 'textColor' ) } ),
                        el( RangeRow, {
                                label: t( 'Vertical padding' ),
                                value: s.padY,
                                min: 0,
                                max: 96,
                                unit: 'px',
                                onChange: upd( 'padY' ),
                        } ),
                        el( ToggleRow, {
                                label: t( 'Bottom border' ),
                                checked: s.borderBottom,
                                onChange: upd( 'borderBottom' ),
                        } ),
                        el( ToggleRow, {
                                label: t( 'Sticky' ),
                                help: t( 'When any row is sticky, the whole header stays pinned while scrolling.' ),
                                checked: s.sticky,
                                onChange: upd( 'sticky' ),
                        } ),
                        el( ToggleRow, {
                                label: t( 'Transparent' ),
                                help: t( 'Transparent rows overlay the page content — great with hero sections.' ),
                                checked: s.transparent,
                                onChange: upd( 'transparent' ),
                        } )
                )
        );
}

/* ------------------------------------------------------------------ */
/* Mobile form                                                         */
/* ------------------------------------------------------------------ */

function MobileForm( { model, setModel } ) {
        const m = model.mobile;
        const upd = ( field ) => ( value ) =>
                setModel( ( mm ) => updateMobile( mm, { [ field ]: value } ), 'set:mobile:' + field );
        const drawerRemove = ( id ) =>
                setModel( ( mm ) => pruneOrphans( removeOccurrence( mm, { area: 'drawer' }, id ) ) );
        const drawerMove = ( id, dir ) =>
                setModel( ( mm ) => updateMobile( mm, { offcanvas: moveInList( mm.mobile.offcanvas, id, dir ) } ) );
        return el(
                'div',
                null,
                el( PanelHeader, { icon: 'mobile', label: t( 'Mobile settings' ) } ),
                el(
                        'div',
                        { className: 'khb-form' },
                        el( SegRow, {
                                label: t( 'Hamburger side' ),
                                value: m.trigger,
                                onChange: upd( 'trigger' ),
                                options: [
                                        { value: 'left', label: t( 'Left' ) },
                                        { value: 'right', label: t( 'Right' ) },
                                ],
                        } ),
                        el( SegRow, {
                                label: t( 'Drawer side' ),
                                value: m.offcanvasDir,
                                onChange: upd( 'offcanvasDir' ),
                                options: [
                                        { value: 'left', label: t( 'Left' ) },
                                        { value: 'right', label: t( 'Right' ) },
                                ],
                        } ),
                        el( RangeRow, {
                                label: t( 'Breakpoint' ),
                                help: t( 'Below this viewport width the mobile header is shown.' ),
                                value: m.breakpoint,
                                min: 480,
                                max: 1280,
                                step: 10,
                                unit: 'px',
                                onChange: upd( 'breakpoint' ),
                        } ),
                        el( 'div', { className: 'khb-field' }, el( 'label', { className: 'khb-field__label' }, t( 'Drawer elements' ) ) ),
                        m.offcanvas.length === 0
                                ? el( 'p', { className: 'khb-help' }, t( 'Drawer is empty. Drag elements here.' ) )
                                : null,
                        el(
                                'ul',
                                { className: 'khb-drawerlist', role: 'list' },
                                m.offcanvas.map( ( id, i ) => {
                                        const def = model.elements[ id ];
                                        const meta = def ? ELEMENTS[ def.type ] : null;
                                        return el(
                                                'li',
                                                { className: 'khb-drawerlist__item', key: id },
                                                el( Icon, { name: meta ? meta.icon : 'alert', size: 16 } ),
                                                el( 'span', { className: 'khb-drawerlist__name' }, meta ? t( meta.label ) : t( 'Unknown element' ) ),
                                                el(
                                                        'span',
                                                        { className: 'khb-drawerlist__tools' },
                                                        el( IconButton, {
                                                                icon: 'chevronUp',
                                                                label: t( 'Move up' ),
                                                                disabled: i === 0,
                                                                onClick: () => drawerMove( id, -1 ),
                                                        } ),
                                                        el( IconButton, {
                                                                icon: 'chevronDown',
                                                                label: t( 'Move down' ),
                                                                disabled: i === m.offcanvas.length - 1,
                                                                onClick: () => drawerMove( id, 1 ),
                                                        } ),
                                                        el( IconButton, {
                                                                icon: 'close',
                                                                label: t( 'Remove item' ),
                                                                tone: 'danger',
                                                                onClick: () => drawerRemove( id ),
                                                        } )
                                                )
                                        );
                                } )
                        )
                )
        );
}
