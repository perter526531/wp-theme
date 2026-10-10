/**
 * Header Builder — Customizer application shell (Blocksy-style sidebar app).
 *
 * Owns the global state (model + history, selection, device, library
 * collapse, toast, confirm) and lays out the vertical flow the Customizer
 * pane expects (~300px wide): header row (device switch + undo/redo) →
 * dark structure bar → collapsible element library → contextual settings.
 *
 * All mutations flow through the history hook so undo/redo stays consistent.
 * Every model change after mount is pushed into the
 * `kabel_header_builder` Customizer setting (postMessage transport) — the
 * Customizer's own Publish button saves, and the preview iframe re-renders
 * live through the REST render endpoint.
 */
import { el, useState, useEffect, useMemo, useRef, useCallback } from '@wordpress/element';
import { t } from './zh.js';
import { StructureBar } from './StructureBar.jsx';
import { Palette } from './Palette.jsx';
import { SettingsPanel } from './SettingsPanel.jsx';
import { Toast, ConfirmDialog, IconButton } from './ui.jsx';
import { Segmented } from './controls.jsx';
import { useHistory } from './history.js';
import {
	normalizeModel,
	uid,
	applyDrop,
	addElementWithId,
	deleteRow,
	removeOccurrence,
	pruneOrphans,
	isReferenced,
} from './model.js';

/**
 * Push the model into the Customizer setting as a JSON string — the exact
 * storage format the option has always used (k-15 back-compat).
 */
function pushToCustomizer( model ) {
	if ( window.wp && window.wp.customize && window.wp.customize.instance ) {
		const setting = window.wp.customize.instance( 'kabel_header_builder' );
		if ( setting ) {
			setting.set( JSON.stringify( model ) );
		}
	}
}

export function App( { data } ) {
	const initialModel = useMemo(
		() => normalizeModel( data.model, data ),
		[] // eslint-disable-line react-hooks/exhaustive-deps
	);
	const hist = useHistory( initialModel );
	const model = hist.model;

	const [ selectedId, setSelectedId ] = useState( null );
	const [ device, setDevice ] = useState( 'desktop' );
	const [ elementsOpen, setElementsOpen ] = useState( true );
	const [ toast, setToast ] = useState( null );
	const [ confirmState, setConfirmState ] = useState( null );
	const [ drag, setDrag ] = useState( null );

	const mountedRef = useRef( false );

	/* Model → Customizer setting. The first render only arms the hook so a
	 * default model (nothing stored yet) does not dirty the Customizer. */
	useEffect( () => {
		if ( ! mountedRef.current ) {
			mountedRef.current = true;
			return;
		}
		pushToCustomizer( model );
	}, [ model ] );

	const showToast = useCallback( ( message, tone ) => {
		setToast( { message, tone, key: Date.now() } );
	}, [] );

	useEffect( () => {
		if ( ! toast ) {
			return;
		}
		const timer = setTimeout( () => setToast( null ), 2600 );
		return () => clearTimeout( timer );
	}, [ toast ] );

	/* Undo / redo keyboard shortcuts */
	useEffect( () => {
		const onKey = ( e ) => {
			if ( ! ( e.ctrlKey || e.metaKey ) || e.key.toLowerCase() !== 'z' ) {
				return;
			}
			const target = e.target;
			const tag = target && target.tagName ? target.tagName : '';
			if (
				tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' ||
				( target && target.isContentEditable )
			) {
				return;
			}
			e.preventDefault();
			if ( e.shiftKey ) {
				hist.redo();
			} else {
				hist.undo();
			}
		};
		window.addEventListener( 'keydown', onKey );
		return () => window.removeEventListener( 'keydown', onKey );
	}, [ hist ] );

	/* Safety net: a drag that ends outside any zone still clears the state. */
	useEffect( () => {
		if ( ! drag ) {
			return;
		}
		const clear = () => setDrag( null );
		window.addEventListener( 'dragend', clear );
		window.addEventListener( 'drop', clear );
		return () => {
			window.removeEventListener( 'dragend', clear );
			window.removeEventListener( 'drop', clear );
		};
	}, [ drag ] );

	/* ---------------- actions ---------------- */

	/* Selecting something swaps the library for its options (Blocksy flow);
	 * clearing the selection brings the library back. */
	const select = useCallback( ( id ) => {
		setSelectedId( id );
		setElementsOpen( false );
	}, [] );

	useEffect( () => {
		if ( null == selectedId ) {
			setElementsOpen( true );
		}
	}, [ selectedId ] );

	const openElements = useCallback( () => setElementsOpen( true ), [] );

	const switchDevice = useCallback( ( d ) => {
		setDevice( d );
		setSelectedId( ( cur ) => ( cur == null && d === 'mobile' ? 'mobile' : cur ) );
	}, [] );

	const addFromPalette = useCallback(
		( type ) => {
			const id = uid( 'e' );
			hist.set( ( m ) => addElementWithId( m, type, id, device ) );
			setSelectedId( id );
			setElementsOpen( false );
		},
		[ hist, device ]
	);

	const onPaletteDragStart = useCallback( ( e, type ) => {
		e.dataTransfer.setData( 'application/x-kabel-hb', JSON.stringify( { kind: 'new', type } ) );
		e.dataTransfer.effectAllowed = 'copy';
		setDrag( { kind: 'new', type } );
	}, [] );

	const onDropPayload = useCallback(
		( payload, loc, beforeId ) => {
			const newId = payload && payload.kind === 'new' ? uid( 'e' ) : null;
			hist.set( ( m ) => applyDrop( m, payload, loc, beforeId, newId ) );
			if ( newId ) {
				setSelectedId( newId );
				setElementsOpen( false );
			}
		},
		[ hist ]
	);

	const removeChip = useCallback(
		( id, from ) => {
			const next = pruneOrphans( removeOccurrence( model, from, id ) );
			hist.set( next );
			setSelectedId( ( cur ) => ( cur === id && ! isReferenced( next, id ) ? null : cur ) );
		},
		[ hist, model ]
	);

	const requestRowDelete = useCallback(
		( rowId ) => {
			setConfirmState( {
				title: t( 'Delete this row?' ),
				message: t( 'The row and its element placements will be removed. Elements used elsewhere stay.' ),
				confirmLabel: t( 'Delete' ),
				danger: true,
				action: () => {
					hist.set( ( m ) => deleteRow( m, rowId ) );
					setSelectedId( ( cur ) => ( cur === 'row:' + rowId ? null : cur ) );
				},
			} );
		},
		[ hist ]
	);

	/* ---------------- render ---------------- */

	const settingsProps = {
		selectedId,
		model,
		setModel: hist.set,
		menus: data.menus || [],
		canUnfiltered: !! data.canUnfiltered,
		siteName: data.siteName || '',
		logoUrl: data.logoUrl || '',
		woocommerce: !! data.woocommerce,
		home: data.home || '',
		showToast,
	};

	return el(
		'div',
		{ className: 'khb-app' + ( drag ? ' is-dragging' : '' ) },
		el(
			'div',
			{ className: 'khb-head', role: 'group', 'aria-label': t( 'Header Builder' ) },
			el( 'h2', { className: 'khb-head__title' }, t( 'Header Builder' ) ),
			el(
				'div',
				{ className: 'khb-head__tools' },
				el( Segmented, {
					value: device,
					ariaLabel: t( 'Preview device' ),
					onChange: switchDevice,
					options: [
						{ value: 'desktop', label: t( 'Desktop' ), icon: 'desktop' },
						{ value: 'mobile', label: t( 'Mobile' ), icon: 'mobile' },
					],
				} ),
				el(
					'div',
					{ className: 'khb-head__group', role: 'group', 'aria-label': t( 'History' ) },
					el( IconButton, {
						icon: 'undo',
						label: t( 'Undo' ),
						disabled: ! hist.canUndo,
						onClick: hist.undo,
					} ),
					el( IconButton, {
						icon: 'redo',
						label: t( 'Redo' ),
						disabled: ! hist.canRedo,
						onClick: hist.redo,
					} )
				)
			)
		),
		el( StructureBar, {
			model,
			setModel: hist.set,
			device,
			selectedId,
			onSelect: select,
			onOpenElements: openElements,
			onDropPayload,
			onRequestRowDelete: requestRowDelete,
			onRemoveChip: removeChip,
			onMobileSettings: () => select( 'mobile' ),
			drag,
			setDrag,
		} ),
		el( Palette, {
			open: elementsOpen,
			onToggle: () => setElementsOpen( ( cur ) => ! cur ),
			onAdd: addFromPalette,
			onDragStart: onPaletteDragStart,
			onDragEnd: () => setDrag( null ),
			woocommerce: !! data.woocommerce,
		} ),
		el(
			'section',
			{ className: 'khb-block', 'aria-label': t( 'Settings' ) },
			el(
				'div',
				{ className: 'khb-block__head is-static' },
				el( 'span', { className: 'khb-block__title' }, t( 'Settings' ) )
			),
			el( 'div', { className: 'khb-block__body khb-block__body--settings' }, el( SettingsPanel, settingsProps ) )
		),
		toast
			? el( Toast, { key: toast.key, message: toast.message, tone: toast.tone } )
			: null,
		confirmState
			? el( ConfirmDialog, {
					key: 'confirm',
					title: confirmState.title,
					message: confirmState.message,
					confirmLabel: confirmState.confirmLabel,
					danger: confirmState.danger,
					onCancel: () => setConfirmState( null ),
					onConfirm: () => {
						const action = confirmState.action;
						setConfirmState( null );
						if ( action ) {
							action();
						}
					},
			} )
			: null
	);
}
