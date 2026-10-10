/**
 * Header Builder — data model helpers.
 *
 * Pure, immutable helpers over the JSON model described in the k15 contract:
 *   { version, rows[{id, settings, zones[3]}], mobile{zones[3], offcanvas,
 *     offcanvasDir, trigger, breakpoint}, elements{ id: {type, settings} } }
 *
 * The same module owns the element registry (11 types + default settings)
 * and the default model used when the stored option is empty.
 */

export const ELEMENT_TYPES = [
        'logo',
        'menu',
        'button',
        'search',
        'social',
        'html',
        'contact',
        'text',
        'lang',
        'account',
        'cart',
];

export const ELEMENTS = {
        logo: { label: 'Site Logo', icon: 'logo' },
        menu: { label: 'Menu', icon: 'menu' },
        button: { label: 'Button', icon: 'button' },
        search: { label: 'Search', icon: 'search' },
        social: { label: 'Social Icons', icon: 'social' },
        html: { label: 'Custom HTML', icon: 'code' },
        contact: { label: 'Contact Info', icon: 'contact' },
        text: { label: 'Text', icon: 'text' },
        lang: { label: 'Language Switcher', icon: 'globe' },
        account: { label: 'Account', icon: 'user' },
        cart: { label: 'Cart', icon: 'cart' },
};

export const SOCIAL_ICONS = [
        [ 'whatsapp', 'WhatsApp' ],
        [ 'facebook', 'Facebook' ],
        [ 'instagram', 'Instagram' ],
        [ 'linkedin', 'LinkedIn' ],
        [ 'youtube', 'YouTube' ],
        [ 'x', 'X (Twitter)' ],
        [ 'tiktok', 'TikTok' ],
        [ 'email', 'Email' ],
        [ 'phone', 'Phone' ],
];

export const MAX_ROWS = 3;

let uidCounter = 0;

/** Short, contract-safe id (^[a-z0-9-]{2,40}$). */
export function uid( prefix ) {
        uidCounter += 1;
        const rand = Math.random().toString( 36 ).slice( 2, 8 );
        return prefix + '-' + rand + uidCounter.toString( 36 );
}

function clampNum( value, min, max, fallback ) {
        const n = Number( value );
        if ( ! Number.isFinite( n ) ) {
                return fallback;
        }
        return Math.min( max, Math.max( min, n ) );
}

function colorValue( value ) {
        return typeof value === 'string' && /^#[0-9a-fA-F]{3,8}$/.test( value ) ? value : '';
}

function textValue( value, fallback ) {
        return typeof value === 'string' ? value : fallback;
}

function boolValue( value, fallback ) {
        return typeof value === 'boolean' ? value : fallback;
}

function idList( value ) {
        if ( ! Array.isArray( value ) ) {
                return [];
        }
        const out = [];
        for ( const item of value ) {
                if ( typeof item === 'string' && item && out.indexOf( item ) === -1 ) {
                        out.push( item );
                }
        }
        return out;
}

/** Default settings per element type — mirrors the k15 contract exactly. */
export function defaultsFor( type, home ) {
        const base = ( ( ( home || '' ).replace( /\/+$/, '' ) ) || '' );
        switch ( type ) {
                case 'logo':
                        return { source: 'site', text: '', tagline: '', taglineShow: true, imgId: 0, imgUrl: '', imgWidth: 140 };
                case 'menu':
                        return { menuId: 0, fontSize: 15, gap: 28, align: 'left', color: '', hoverColor: '', style: 'plain', subIndicator: true };
                case 'button':
                        return { text: 'Contact', url: base ? base + '/contact/' : '', variant: 'gold', size: 's', target: false };
                case 'search':
                        return { style: 'icon', placeholder: 'Search…', fieldWidth: 180 };
                case 'social':
                        return { items: [], size: 18, gap: 10 };
                case 'html':
                        return { content: '' };
                case 'contact':
                        return { phone: '', email: '', address: '', showPhone: true, showEmail: true, showAddress: false };
                case 'text':
                        return { content: '', size: 14, color: '' };
                case 'lang':
                        return { style: 'code' };
                case 'account':
                        return { loginText: 'Login', url: '' };
                case 'cart':
                        return { showCount: true };
                default:
                        return {};
        }
}

function rowSettingsDefaults() {
        return { bg: '', textColor: '', padY: 16, borderBottom: false, sticky: false, transparent: false };
}

/** Numeric clamp ranges per element type — mirrors the PHP sanitizer. */
const NUMERIC_FIELDS = {
        logo: { imgWidth: [ 40, 400 ] },
        menu: { fontSize: [ 10, 32 ], gap: [ 0, 96 ] },
        search: { fieldWidth: [ 120, 420 ] },
        social: { size: [ 12, 32 ], gap: [ 4, 40 ] },
        text: { size: [ 10, 40 ] },
};

function clampSettings( type, settings ) {
        const ranges = NUMERIC_FIELDS[ type ];
        if ( ! ranges ) {
                return settings;
        }
        const out = Object.assign( {}, settings );
        for ( const field of Object.keys( ranges ) ) {
                const n = Number( out[ field ] );
                if ( ! Number.isFinite( n ) ) {
                        out[ field ] = defaultsFor( type )[ field ];
                } else {
                        out[ field ] = Math.min( ranges[ field ][ 1 ], Math.max( ranges[ field ][ 0 ], n ) );
                }
        }
        return out;
}

function rowSettings( input ) {
        const s = input && typeof input === 'object' ? input : {};
        return {
                bg: colorValue( s.bg ),
                textColor: colorValue( s.textColor ),
                padY: clampNum( s.padY, 0, 96, 16 ),
                borderBottom: boolValue( s.borderBottom, false ),
                sticky: boolValue( s.sticky, false ),
                transparent: boolValue( s.transparent, false ),
        };
}

function zonesOf( input ) {
        const z = Array.isArray( input ) ? input : [];
        return [ 0, 1, 2 ].map( ( i ) => idList( z[ i ] ) );
}

/** Default model shown when nothing is stored yet (contract default). */
export function defaultModel( data ) {
        const home = ( ( data && data.home ) || '' ).replace( /\/+$/, '' );
        return {
                version: 1,
                rows: [
                        {
                                id: 'r-1',
                                settings: rowSettingsDefaults(),
                                zones: [ [ 'e-logo' ], [], [ 'e-menu', 'e-cta' ] ],
                        },
                ],
                mobile: {
                        zones: [ [ 'e-logo' ], [], [] ],
                        offcanvas: [ 'e-menu', 'e-cta' ],
                        offcanvasDir: 'right',
                        trigger: 'right',
                        breakpoint: 780,
                },
                elements: {
                        'e-logo': { type: 'logo', settings: defaultsFor( 'logo', home ) },
                        'e-menu': { type: 'menu', settings: defaultsFor( 'menu', home ) },
                        'e-cta': {
                                type: 'button',
                                settings: Object.assign( defaultsFor( 'button', home ), {
                                        text: 'Contact',
                                        url: home ? home + '/contact/' : '',
                                        variant: 'gold',
                                } ),
                        },
                },
        };
}

/**
 * Normalize any incoming model into the canonical shape with a stable key
 * order (so JSON.stringify comparisons for the dirty flag stay reliable).
 */
export function normalizeModel( input, data ) {
        if ( ! input || typeof input !== 'object' ) {
                return defaultModel( data );
        }
        const home = ( ( data && data.home ) || '' ).replace( /\/+$/, '' );
        const rows = [];
        const srcRows = Array.isArray( input.rows ) ? input.rows.slice( 0, MAX_ROWS ) : [];
        for ( const row of srcRows ) {
                if ( ! row || typeof row !== 'object' ) {
                        continue;
                }
                rows.push( {
                        id: typeof row.id === 'string' && row.id ? row.id : uid( 'r' ),
                        settings: rowSettings( row.settings ),
                        zones: zonesOf( row.zones ),
                } );
        }
        const m = input.mobile && typeof input.mobile === 'object' ? input.mobile : {};
        const elements = {};
        const srcElements = input.elements && typeof input.elements === 'object' ? input.elements : {};
        for ( const id of Object.keys( srcElements ) ) {
                const def = srcElements[ id ];
                if ( ! def || ! ELEMENTS[ def.type ] ) {
                        continue;
                }
                const settings = clampSettings(
                        def.type,
                        Object.assign(
                                defaultsFor( def.type, home ),
                                def.settings && typeof def.settings === 'object' ? def.settings : {}
                        )
                );
                elements[ id ] = { type: def.type, settings };
        }
        return {
                version: 1,
                rows,
                mobile: {
                        zones: zonesOf( m.zones ),
                        offcanvas: idList( m.offcanvas ),
                        offcanvasDir: m.offcanvasDir === 'left' ? 'left' : 'right',
                        trigger: m.trigger === 'left' ? 'left' : 'right',
                        breakpoint: clampNum( m.breakpoint, 480, 1280, 780 ),
                },
                elements,
        };
}

/* ------------------------------------------------------------------ */
/* Structural mutations (all immutable)                                */
/* ------------------------------------------------------------------ */

function mapZone( zones, index, fn ) {
        return zones.map( ( list, i ) => ( i === index ? fn( list ) : list ) );
}

function insertInList( list, id, beforeId ) {
        const next = list.filter( ( x ) => x !== id );
        let at = beforeId != null ? next.indexOf( beforeId ) : -1;
        if ( at < 0 ) {
                at = next.length;
        }
        next.splice( at, 0, id );
        return next;
}

/**
 * `loc` describes a drop target:
 *   { area: 'desktop', rowId, zone } | { area: 'mobile', zone } | { area: 'drawer' }
 */
export function insertAt( model, loc, id, beforeId ) {
        if ( ! loc ) {
                return model;
        }
        if ( loc.area === 'drawer' ) {
                return Object.assign( {}, model, {
                        mobile: Object.assign( {}, model.mobile, {
                                offcanvas: insertInList( model.mobile.offcanvas, id, beforeId ),
                        } ),
                } );
        }
        if ( loc.area === 'mobile' ) {
                return Object.assign( {}, model, {
                        mobile: Object.assign( {}, model.mobile, {
                                zones: mapZone( model.mobile.zones, loc.zone, ( list ) => insertInList( list, id, beforeId ) ),
                        } ),
                } );
        }
        return Object.assign( {}, model, {
                rows: model.rows.map( ( row ) =>
                        row.id === loc.rowId
                                ? Object.assign( {}, row, {
                                        zones: mapZone( row.zones, loc.zone, ( list ) => insertInList( list, id, beforeId ) ),
                                } )
                                : row
                ),
        } );
}

/** Remove one occurrence of `id` at `from` (same shape as `loc`). */
export function removeOccurrence( model, from, id ) {
        if ( ! from ) {
                return model;
        }
        if ( from.area === 'drawer' ) {
                return Object.assign( {}, model, {
                        mobile: Object.assign( {}, model.mobile, {
                                offcanvas: model.mobile.offcanvas.filter( ( x ) => x !== id ),
                        } ),
                } );
        }
        if ( from.area === 'mobile' ) {
                return Object.assign( {}, model, {
                        mobile: Object.assign( {}, model.mobile, {
                                zones: mapZone( model.mobile.zones, from.zone, ( list ) => list.filter( ( x ) => x !== id ) ),
                        } ),
                } );
        }
        return Object.assign( {}, model, {
                rows: model.rows.map( ( row ) =>
                        row.id === from.rowId
                                ? Object.assign( {}, row, {
                                        zones: mapZone( row.zones, from.zone, ( list ) => list.filter( ( x ) => x !== id ) ),
                                } )
                                : row
                ),
        } );
}

/** Is this element id still placed anywhere (desktop zones, mobile zones, drawer)? */
export function isReferenced( model, id ) {
        for ( const row of model.rows ) {
                for ( const zone of row.zones ) {
                        if ( zone.indexOf( id ) > -1 ) {
                                return true;
                        }
                }
        }
        for ( const zone of model.mobile.zones ) {
                if ( zone.indexOf( id ) > -1 ) {
                        return true;
                }
        }
        return model.mobile.offcanvas.indexOf( id ) > -1;
}

/** Drop every element that is not referenced by any placement. */
export function pruneOrphans( model ) {
        const used = {};
        for ( const row of model.rows ) {
                for ( const zone of row.zones ) {
                        for ( const id of zone ) {
                                used[ id ] = true;
                        }
                }
        }
        for ( const zone of model.mobile.zones ) {
                for ( const id of zone ) {
                        used[ id ] = true;
                }
        }
        for ( const id of model.mobile.offcanvas ) {
                used[ id ] = true;
        }
        const elements = {};
        for ( const id of Object.keys( model.elements ) ) {
                if ( used[ id ] ) {
                        elements[ id ] = model.elements[ id ];
                }
        }
        return Object.assign( {}, model, { elements } );
}

/** Handle a palette drop ({kind:'new',type}) or a chip move ({kind:'move',id,from}). */
export function applyDrop( model, payload, loc, beforeId, presetId ) {
        if ( ! payload ) {
                return model;
        }
        let next = model;
        let id;
        if ( payload.kind === 'new' ) {
                if ( ! ELEMENTS[ payload.type ] ) {
                        return model;
                }
                id = presetId || uid( 'e' );
                next = Object.assign( {}, next, {
                        elements: Object.assign( {}, next.elements, {
                                [ id ]: { type: payload.type, settings: defaultsFor( payload.type ) },
                        } ),
                } );
        } else if ( payload.kind === 'move' ) {
                id = payload.id;
                if ( ! id || ! next.elements[ id ] ) {
                        return model;
                }
                next = removeOccurrence( next, payload.from, id );
        } else {
                return model;
        }
        next = insertAt( next, loc, id, beforeId );
        return pruneOrphans( next );
}

/** Add an element from the palette by click (desktop -> first row right zone, mobile -> drawer). */
export function addElementWithId( model, type, id, device ) {
        if ( ! ELEMENTS[ type ] ) {
                return model;
        }
        let next = Object.assign( {}, model, {
                elements: Object.assign( {}, model.elements, {
                        [ id ]: { type, settings: defaultsFor( type ) },
                } ),
        } );
        if ( device === 'mobile' ) {
                next = insertAt( next, { area: 'drawer' }, id, null );
        } else {
                if ( ! next.rows.length ) {
                        next = addRow( next );
                }
                next = insertAt( next, { area: 'desktop', rowId: next.rows[ 0 ].id, zone: 2 }, id, null );
        }
        return pruneOrphans( next );
}

export function addRow( model ) {
        if ( model.rows.length >= MAX_ROWS ) {
                return model;
        }
        return Object.assign( {}, model, {
                rows: model.rows.concat( [
                        { id: uid( 'r' ), settings: rowSettingsDefaults(), zones: [ [], [], [] ] },
                ] ),
        } );
}

export function deleteRow( model, rowId ) {
        return pruneOrphans(
                Object.assign( {}, model, {
                        rows: model.rows.filter( ( row ) => row.id !== rowId ),
                } )
        );
}

export function moveRow( model, rowId, dir ) {
        const rows = model.rows.slice();
        const at = rows.findIndex( ( row ) => row.id === rowId );
        const to = at + dir;
        if ( at < 0 || to < 0 || to >= rows.length ) {
                return model;
        }
        const tmp = rows[ at ];
        rows[ at ] = rows[ to ];
        rows[ to ] = tmp;
        return Object.assign( {}, model, { rows } );
}

export function updateElementSettings( model, id, patch ) {
        const def = model.elements[ id ];
        if ( ! def ) {
                return model;
        }
        return Object.assign( {}, model, {
                elements: Object.assign( {}, model.elements, {
                        [ id ]: Object.assign( {}, def, {
                                settings: Object.assign( {}, def.settings, patch ),
                        } ),
                } ),
        } );
}

export function updateRowSettings( model, rowId, patch ) {
        return Object.assign( {}, model, {
                rows: model.rows.map( ( row ) =>
                        row.id === rowId
                                ? Object.assign( {}, row, { settings: Object.assign( {}, row.settings, patch ) } )
                                : row
                ),
        } );
}

export function updateMobile( model, patch ) {
        return Object.assign( {}, model, {
                mobile: Object.assign( {}, model.mobile, patch ),
        } );
}

/** Reorder an id inside a single list by -1/+1 (used by the drawer manager). */
export function moveInList( list, id, dir ) {
        const at = list.indexOf( id );
        const to = at + dir;
        if ( at < 0 || to < 0 || to >= list.length ) {
                return list;
        }
        const next = list.slice();
        const tmp = next[ at ];
        next[ at ] = next[ to ];
        next[ to ] = tmp;
        return next;
}
