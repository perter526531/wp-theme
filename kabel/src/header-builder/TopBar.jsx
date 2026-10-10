/**
 * Header Builder — top bar: title, source badge, undo/redo, device switch,
 * save, open-front-end and the overflow menu.
 */
import { el, Fr, useState, useEffect, useRef } from '@wordpress/element';
import { t } from './zh.js';
import { Icon } from './ui.jsx';
import { Segmented } from './controls.jsx';

export function TopBar( {
	liveSource,
	canUndo,
	canRedo,
	onUndo,
	onRedo,
	device,
	onDevice,
	dirty,
	saving,
	onSave,
	onClearRequest,
	home,
	headerBlocksEdit,
} ) {
	const [ open, setOpen ] = useState( false );
	const menuRef = useRef( null );

	useEffect( () => {
		if ( ! open ) {
			return;
		}
		const onKey = ( e ) => {
			if ( e.key === 'Escape' ) {
				setOpen( false );
			}
		};
		const onDown = ( e ) => {
			if ( menuRef.current && ! menuRef.current.contains( e.target ) ) {
				setOpen( false );
			}
		};
		document.addEventListener( 'keydown', onKey );
		document.addEventListener( 'mousedown', onDown );
		return () => {
			document.removeEventListener( 'keydown', onKey );
			document.removeEventListener( 'mousedown', onDown );
		};
	}, [ open ] );

	const badgeLabel =
		liveSource === 'builder'
			? t( 'Builder active' )
			: liveSource === 'blocks'
				? t( 'Blocks header active' )
				: t( 'Classic header active' );
	const badgeClass =
		liveSource === 'builder' ? 'khb-badge khb-badge--builder' : 'khb-badge khb-badge--plain';

	return el(
		'header',
		{ className: 'khb-topbar' },
		el( 'h1', { className: 'khb-topbar__title' }, t( 'Header Builder' ) ),
		el( 'span', { className: badgeClass, title: t( 'Which header source is live on the site' ) }, badgeLabel ),
		el(
			'div',
			{ className: 'khb-topbar__group', role: 'group', 'aria-label': t( 'History' ) },
			el( 'button', {
				type: 'button',
				className: 'khb-iconbtn',
				'aria-label': t( 'Undo' ),
				title: t( 'Undo' ),
				disabled: ! canUndo,
				onClick: onUndo,
			}, el( Icon, { name: 'undo' } ) ),
			el( 'button', {
				type: 'button',
				className: 'khb-iconbtn',
				'aria-label': t( 'Redo' ),
				title: t( 'Redo' ),
				disabled: ! canRedo,
				onClick: onRedo,
			}, el( Icon, { name: 'redo' } ) )
		),
		el( Segmented, {
			value: device,
			ariaLabel: t( 'Preview device' ),
			onChange: onDevice,
			options: [
				{ value: 'desktop', label: t( 'Desktop' ), icon: 'desktop' },
				{ value: 'mobile', label: t( 'Mobile' ), icon: 'mobile' },
			],
		} ),
		el( 'span', { className: 'khb-topbar__spacer' } ),
		home
			? el(
				'a',
				{
					className: 'khb-btn',
					href: home,
					target: '_blank',
					rel: 'noopener noreferrer',
				},
				el( Icon, { name: 'external', size: 15 } ),
				el( 'span', null, t( 'Open Front End' ) )
			)
			: null,
		el(
			'button',
			{
				type: 'button',
				className: 'khb-btn khb-btn--primary' + ( saving ? ' is-busy' : '' ),
				onClick: onSave,
				disabled: saving,
				'aria-label': t( 'Save' ),
			},
			saving
				? el( 'span', { className: 'khb-spin', 'aria-hidden': true } )
				: dirty
					? el( 'span', { className: 'khb-save-dot', 'aria-hidden': true } )
					: null,
			el( 'span', null, saving ? t( 'Saving…' ) : t( 'Save' ) )
		),
		el(
			'div',
			{ className: 'khb-menu', ref: menuRef },
			el(
				'button',
				{
					type: 'button',
					className: 'khb-iconbtn' + ( open ? ' is-open' : '' ),
					'aria-label': t( 'More actions' ),
					title: t( 'More actions' ),
					'aria-expanded': open,
					'aria-haspopup': 'menu',
					onClick: () => setOpen( ! open ),
				},
				el( Icon, { name: 'more' } )
			),
			open
				? el(
					'div',
					{ className: 'khb-menu__pop', role: 'menu' },
					el(
						'button',
						{
							type: 'button',
							className: 'khb-menu__item is-danger',
							role: 'menuitem',
							onClick: () => {
								setOpen( false );
								onClearRequest();
							},
						},
						el( Icon, { name: 'trash', size: 15 } ),
						el( 'span', null, t( 'Clear Builder Data' ) )
					),
					headerBlocksEdit
						? el(
							'a',
							{
								className: 'khb-menu__item',
								role: 'menuitem',
								href: headerBlocksEdit,
							},
							el( Icon, { name: 'code', size: 15 } ),
							el( 'span', null, t( 'Edit Header Blocks (Legacy)' ) )
						)
						: null
				)
				: null
		)
	);
}
