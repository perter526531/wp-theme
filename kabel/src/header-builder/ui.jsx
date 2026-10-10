/**
 * Header Builder — shared UI primitives: icon set, toast, confirm dialog.
 */
import { el, Fr, useEffect, useRef } from '@wordpress/element';
import { t } from './zh.js';

/* Minimal stroke icon set (24x24, currentColor). */
const ICONS = {
        logo: [ 'M12 3l7 4v6c0 4-3 6.6-7 8-4-1.4-7-4-7-8V7z' ],
        menu: [ 'M4 7h16', 'M4 12h16', 'M4 17h16' ],
        button: [ 'M4 9h16v6H4z', 'M9.5 12h5' ],
        search: [ 'M18 11a7 7 0 11-14 0 7 7 0 0114 0z', 'M21 21l-4.3-4.3' ],
        social: [
                'M21 5a3 3 0 11-6 0 3 3 0 016 0z',
                'M9 12a3 3 0 11-6 0 3 3 0 016 0z',
                'M21 19a3 3 0 11-6 0 3 3 0 016 0z',
                'M8.6 13.5l6.8 4',
                'M15.4 6.5l-6.8 4',
        ],
        code: [ 'M8 6l-5 6 5 6', 'M16 6l5 6-5 6' ],
        contact: [
                'M5 4h4l2 5-2.5 1.5a12 12 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z',
        ],
        text: [ 'M4 7V4h16v3', 'M9 20h6', 'M12 4v16' ],
        globe: [
                'M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'M3 12h18',
                'M12 3c2.6 2.6 2.6 15.4 0 18',
                'M12 3c-2.6 2.6-2.6 15.4 0 18',
        ],
        user: [ 'M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2', 'M16 7a4 4 0 11-8 0 4 4 0 018 0z' ],
        cart: [
                'M9 22a1 1 0 11-2 0 1 1 0 012 0z',
                'M20 22a1 1 0 11-2 0 1 1 0 012 0z',
                'M1 1h4l2.7 13.4a2 2 0 002 1.6h9.7a2 2 0 002-1.6L23 6H6',
        ],
        desktop: [ 'M3 5h18v11H3z', 'M8 20h8', 'M12 16v4' ],
        mobile: [ 'M7 3h10v18H7z', 'M10.5 18.5h3' ],
        undo: [ 'M3 4v6h6', 'M21 12a9 9 0 11-17.5-2.2' ],
        redo: [ 'M21 4v6h-6', 'M3 12a9 9 0 1017.5-2.2' ],
        chevronUp: [ 'M6 14l6-6 6 6' ],
        chevronDown: [ 'M6 10l6 6 6-6' ],
        close: [ 'M6 6l12 12', 'M18 6L6 18' ],
        plus: [ 'M12 5v14', 'M5 12h14' ],
        more: [
                'M6.6 12a1.6 1.6 0 11-3.2 0 1.6 1.6 0 013.2 0z',
                'M13.6 12a1.6 1.6 0 11-3.2 0 1.6 1.6 0 013.2 0z',
                'M20.6 12a1.6 1.6 0 11-3.2 0 1.6 1.6 0 013.2 0z',
        ],
        external: [ 'M14 4h6v6', 'M20 4L11 13', 'M20 14v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5a1 1 0 011-1h5' ],
        gear: [
                'M15 12a3 3 0 11-6 0 3 3 0 016 0z',
                'M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09a1.65 1.65 0 00-1.08-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 110-4h.09a1.65 1.65 0 001.51-1.08 1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33h.08a1.65 1.65 0 001-1.51V3a2 2 0 114 0v.09a1.65 1.65 0 001 1.51h.08a1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82v.08a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z',
        ],
        trash: [ 'M4 7h16', 'M9 7V4h6v3', 'M6 7l1 14h10l1-14', 'M10 11v6', 'M14 11v6' ],
        image: [
                'M3 5h18v14H3z',
                'M3 16l5-5 4 4 3-3 6 6',
                'M8.6 9a1.3 1.3 0 11-2.6 0 1.3 1.3 0 012.6 0z',
        ],
        alert: [ 'M12 4L2.5 20h19z', 'M12 10v4', 'M12 17v.5' ],
};

export function Icon( { name, size } ) {
        const paths = ICONS[ name ];
        if ( ! paths ) {
                return null;
        }
        const s = size || 20;
        return el(
                'svg',
                {
                        className: 'khb-icon',
                        width: s,
                        height: s,
                        viewBox: '0 0 24 24',
                        fill: 'none',
                        stroke: 'currentColor',
                        strokeWidth: 1.8,
                        strokeLinecap: 'round',
                        strokeLinejoin: 'round',
                        'aria-hidden': true,
                        focusable: 'false',
                },
                paths.map( ( d, i ) => el( 'path', { key: i, d } ) )
        );
}

export function Toast( { message, tone } ) {
        if ( ! message ) {
                return null;
        }
        return el(
                'div',
                {
                        className: 'khb-toast' + ( tone === 'error' ? ' is-error' : '' ),
                        role: 'status',
                        'aria-live': 'polite',
                },
                message
        );
}

export function ConfirmDialog( {
        title,
        message,
        confirmLabel,
        danger,
        onConfirm,
        onCancel,
} ) {
        const ref = useRef( null );
        useEffect( () => {
                const onKey = ( e ) => {
                        if ( e.key === 'Escape' ) {
                                onCancel();
                        }
                };
                document.addEventListener( 'keydown', onKey );
                if ( ref.current ) {
                        ref.current.focus();
                }
                return () => document.removeEventListener( 'keydown', onKey );
        }, [ onCancel ] );
        return el(
                'div',
                {
                        className: 'khb-overlay',
                        onMouseDown: ( e ) => {
                                if ( e.target === e.currentTarget ) {
                                        onCancel();
                                }
                        },
                },
                el(
                        'div',
                        { className: 'khb-dialog', role: 'dialog', 'aria-modal': 'true', 'aria-label': title },
                        el( 'h3', { className: 'khb-dialog__title' }, title ),
                        el( 'p', { className: 'khb-dialog__msg' }, message ),
                        el(
                                'div',
                                { className: 'khb-dialog__actions' },
                                el( 'button', { type: 'button', className: 'khb-btn', onClick: onCancel }, t( 'Cancel' ) ),
                                el(
                                        'button',
                                        {
                                                type: 'button',
                                                ref,
                                                className: 'khb-btn ' + ( danger ? 'khb-btn--danger' : 'khb-btn--primary' ),
                                                onClick: onConfirm,
                                        },
                                        confirmLabel || t( 'Confirm' )
                                )
                        )
                )
        );
}

/** Small helper for icon-only buttons. */
export function IconButton( { icon, label, onClick, disabled, tone } ) {
        return el(
                'button',
                {
                        type: 'button',
                        className:
                                'khb-iconbtn' +
                                ( tone === 'dark' ? ' khb-iconbtn--dark' : '' ) +
                                ( tone === 'danger' ? ' is-danger' : '' ),
                        'aria-label': label,
                        title: label,
                        onClick,
                        disabled: !! disabled,
                },
                el( Icon, { name: icon } )
        );
}

/** Renders nothing — placeholder to keep imports tidy. */
export function Noop() {
        return el( Fr, null );
}
