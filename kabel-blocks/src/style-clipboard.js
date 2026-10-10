/**
 * Kabel Blocks v3 — style copy/paste clipboard (the Stackable move).
 *
 * A tiny wp.data store ('kbl3/clip') holds the last copied STYLE attributes
 * (whitelist below — content keys like text, urls or icons are never
 * touched). registerPlugin mounts two items into every block's ⋮ settings
 * menu: "Copy styles" grabs the current block's whitelisted attributes,
 * "Paste styles" intersects them with the TARGET block's declared
 * attributes and pushes them through updateBlockAttributes — unknown keys
 * can never enter a block comment. Content stays content, style travels.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { createReduxStore, register, useSelect, useDispatch } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';
import { BlockSettingsMenuControls } from '@wordpress/block-editor';
import { MenuItem } from '@wordpress/components';
import { getBlockType } from '@wordpress/blocks';

/* ------------------------------------------------------------------ */
/* Clipboard store                                                     */
/* ------------------------------------------------------------------ */

const CLIP_STORE = 'kbl3/clip';

const clipStore = createReduxStore( CLIP_STORE, {
        reducer( state = { copied: null, sourceName: '' }, action ) {
                switch ( action.type ) {
                        case 'KBL3_CLIP_SET':
                                return { copied: action.copied, sourceName: action.sourceName };
                        default:
                                return state;
                }
        },
        actions: {
                setCopied( copied, sourceName ) {
                        return { type: 'KBL3_CLIP_SET', copied, sourceName };
                },
        },
        selectors: {
                getCopied( state ) {
                        return state.copied;
                },
                getSourceName( state ) {
                        return state.sourceName;
                },
        },
} );

/* Double-load insurance: registering an already-registered store throws. */
try {
        register( clipStore );
} catch ( e ) {
        /* The first registration keeps serving — nothing to do. */
}

/* ------------------------------------------------------------------ */
/* Style whitelist + helpers                                           */
/* ------------------------------------------------------------------ */

/**
 * Style-only attribute keys eligible for copy/paste. Content keys (text,
 * urls, icons, images, level, marker, variant…) are deliberately absent:
 * pasting is about looks, never about content.
 */
export const STYLE_KEYS = [
        'tone', 'color', 'textColor', 'bgColor', 'hoverBg', 'hoverText',
        'align', 'fontSize', 'fontSizeT', 'fontSizeM', 'fontWeight',
        'lineHeight', 'letterSpacing', 'textTransform', 'blockAlign',
        'contentWidth', 'mt', 'mb', 'ml', 'mr', 'mtM', 'mbM',
        'pt', 'pr', 'pb', 'pl', 'padM', 'radius', 'shadow',
        'borderWidth', 'borderStyle', 'borderColor',
        'shadowX', 'shadowY', 'shadowB', 'shadowS', 'shadowColor',
        'gFrom', 'gTo', 'gAngle', 'gType', 'anim', 'animDelay',
        'rot', 'scale', 'tx', 'ty', 'zIndex', 'overflow',
];

/** Extract the whitelisted, actually-set keys from an attributes object. */
export function copyStyles( attributes ) {
        const out = {};
        if ( ! attributes ) {
                return out;
        }
        for ( const key of STYLE_KEYS ) {
                const value = attributes[ key ];
                if ( value !== null && value !== undefined && value !== '' ) {
                        out[ key ] = value;
                }
        }
        return out;
}

/** Keep only the keys the target block type actually declares. */
export function filterForBlock( copied, blockTypeAttributes ) {
        const out = {};
        if ( ! copied || ! blockTypeAttributes ) {
                return out;
        }
        for ( const key of Object.keys( copied ) ) {
                if ( Object.prototype.hasOwnProperty.call( blockTypeAttributes, key ) ) {
                        out[ key ] = copied[ key ];
                }
        }
        return out;
}

/* ------------------------------------------------------------------ */
/* Block settings (⋮) menu items                                       */
/* ------------------------------------------------------------------ */

/**
 * The fill children function cannot host hooks, so it renders this inner
 * component instead. BlockSettingsMenuControls' slot provides the selected
 * client ids / names and onClose through fillProps.
 */
function StyleClipboardItems( { fillProps } ) {
        const ids = ( fillProps && fillProps.selectedClientIds ) || [];
        const names = ( fillProps && fillProps.selectedBlocks ) || [];
        const single = ids.length === 1 && names.length === 1;
        const clientId = single ? ids[ 0 ] : null;
        const blockName = single ? names[ 0 ] : null;
        const block = useSelect(
                ( select ) => ( clientId ? select( 'core/block-editor' ).getBlock( clientId ) : null ),
                [ clientId ]
        );
        const copied = useSelect( ( select ) => select( CLIP_STORE ).getCopied(), [] );
        const { setCopied } = useDispatch( CLIP_STORE );
        const { updateBlockAttributes } = useDispatch( 'core/block-editor' );

        if ( ! block || ! blockName ) {
                return null;
        }

        const closeMenu = () => {
                if ( fillProps && typeof fillProps.onClose === 'function' ) {
                        fillProps.onClose();
                }
        };

        const doCopy = () => {
                const type = getBlockType( blockName );
                setCopied( copyStyles( block.attributes ), type && type.title ? type.title : blockName );
                closeMenu();
        };

        const doPaste = () => {
                if ( ! copied ) {
                        return;
                }
                const type = getBlockType( blockName );
                const filtered = filterForBlock( copied, type ? type.attributes : null );
                if ( Object.keys( filtered ).length ) {
                        updateBlockAttributes( clientId, filtered );
                }
                closeMenu();
        };

        return el(
                Fr,
                null,
                el(
                        MenuItem,
                        { icon: 'admin-appearance', onClick: doCopy },
                        __( 'Copy styles', 'kabel-blocks' )
                ),
                el(
                        MenuItem,
                        {
                                icon: 'editor-paste-text',
                                onClick: doPaste,
                                disabled: ! copied,
                        },
                        copied ?
                                __( 'Paste styles', 'kabel-blocks' ) :
                                __( 'Paste styles (nothing copied yet)', 'kabel-blocks' )
                )
        );
}

/**
 * Rendered by registerPlugin from src/index.js. Returns the
 * BlockSettingsMenuControls fill — present in every block's ⋮ menu.
 */
export function StyleClipboardMenu() {
        return el(
                BlockSettingsMenuControls,
                null,
                ( fillProps ) => el( StyleClipboardItems, { fillProps } )
        );
}
