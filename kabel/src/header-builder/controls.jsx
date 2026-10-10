/**
 * Header Builder — form controls for the settings panel.
 *
 * Shared conventions:
 *  - every control renders its own label + help text;
 *  - range rows pair a slider with a number input (typed values are clamped);
 *  - color rows are swatch + read-only hex + a "Default" clear button;
 *  - toggles are styled switches (appearance-free checkbox under the hood).
 */
import { el, Fr, useState, useEffect } from '@wordpress/element';
import { t } from './zh.js';
import { Icon } from './ui.jsx';

function Field( { label, help, children } ) {
	return el(
		'div',
		{ className: 'khb-field' },
		label ? el( 'label', { className: 'khb-field__label' }, label ) : null,
		children,
		help ? el( 'p', { className: 'khb-help' }, help ) : null
	);
}

export function TextRow( { label, value, onChange, placeholder, type } ) {
	return el(
		Field,
		{ label },
		el( 'input', {
			className: 'khb-input',
			type: type || 'text',
			value: value == null ? '' : value,
			placeholder: placeholder || '',
			onChange: ( e ) => onChange( e.target.value ),
		} )
	);
}

export function UrlRow( props ) {
	return el( TextRow, Object.assign( { type: 'url' }, props ) );
}

export function TextareaRow( { label, value, onChange, placeholder, rows, mono } ) {
	return el(
		Field,
		{ label },
		el( 'textarea', {
			className: 'khb-textarea' + ( mono ? ' is-mono' : '' ),
			rows: rows || 4,
			value: value == null ? '' : value,
			placeholder: placeholder || '',
			spellCheck: false,
			onChange: ( e ) => onChange( e.target.value ),
		} )
	);
}

export function SelectRow( { label, help, value, options, onChange } ) {
	return el(
		Field,
		{ label, help },
		el(
			'select',
			{
				className: 'khb-select',
				value: String( value == null ? '' : value ),
				onChange: ( e ) => onChange( e.target.value ),
			},
			options.map( ( o ) =>
				el( 'option', { key: String( o.value ), value: String( o.value ) }, o.label )
			)
		)
	);
}

/** Segmented control — also used outside forms (device switch, dark bar). */
export function Segmented( { value, options, onChange, ariaLabel, small } ) {
	return el(
		'div',
		{ className: 'khb-seg' + ( small ? ' is-small' : '' ), role: 'group', 'aria-label': ariaLabel },
		options.map( ( o ) =>
			el(
				'button',
				{
					key: String( o.value ),
					type: 'button',
					className: o.value === value ? 'is-active' : '',
					'aria-pressed': o.value === value,
					onClick: () => onChange( o.value ),
				},
				o.icon ? el( Icon, { name: o.icon, size: 15 } ) : null,
				o.label ? el( 'span', { className: 'khb-seg__label' }, o.label ) : null
			)
		)
	);
}

export function SegRow( { label, help, value, options, onChange } ) {
	return el( Field, { label, help }, el( Segmented, { value, options, onChange, ariaLabel: label } ) );
}

/** Number input bound to a range; typed values commit live and clamp. */
export function NumberInput( { value, min, max, step, onCommit, unit, ariaLabel } ) {
	const [ text, setText ] = useState( String( value ) );
	const [ focused, setFocused ] = useState( false );
	useEffect( () => {
		if ( ! focused ) {
			setText( String( value ) );
		}
	}, [ value, focused ] );
	const commit = ( raw ) => {
		const n = parseFloat( raw );
		if ( Number.isNaN( n ) ) {
			return;
		}
		onCommit( Math.min( max, Math.max( min, n ) ) );
	};
	return el(
		Fr,
		null,
		el( 'input', {
			className: 'khb-num',
			type: 'number',
			value: focused ? text : String( value ),
			min,
			max,
			step: step || 1,
			'aria-label': ariaLabel,
			onFocus: () => {
				setFocused( true );
				setText( String( value ) );
			},
			onBlur: () => {
				setFocused( false );
				commit( text );
			},
			onChange: ( e ) => {
				setText( e.target.value );
				commit( e.target.value );
			},
		} ),
		unit ? el( 'span', { className: 'khb-unit' }, unit ) : null
	);
}

export function RangeRow( { label, help, value, min, max, step, unit, onChange } ) {
	const v = Number.isFinite( Number( value ) ) ? Number( value ) : min;
	return el(
		Field,
		{ label, help },
		el(
			'div',
			{ className: 'khb-rangerow' },
			el( 'input', {
				className: 'khb-range',
				type: 'range',
				min,
				max,
				step: step || 1,
				value: v,
				'aria-label': label,
				onChange: ( e ) => onChange( Number( e.target.value ) ),
			} ),
			el( NumberInput, {
				value: v,
				min,
				max,
				step,
				unit,
				ariaLabel: label,
				onCommit: onChange,
			} )
		)
	);
}

export function ColorRow( { label, value, onChange } ) {
	const hex = value || '';
	return el(
		Field,
		{ label },
		el(
			'div',
			{ className: 'khb-colorrow' },
			el( 'input', {
				className: 'khb-swatch',
				type: 'color',
				value: hex || '#B39A74',
				'aria-label': label,
				title: hex || t( 'Default' ),
				onChange: ( e ) => onChange( e.target.value ),
			} ),
			el( 'input', {
				className: 'khb-hex',
				type: 'text',
				readOnly: true,
				value: hex ? hex.toUpperCase() : '',
				placeholder: t( 'Default' ),
				'aria-hidden': true,
				tabIndex: -1,
			} ),
			el(
				'button',
				{
					type: 'button',
					className: 'khb-btn khb-btn--tiny' + ( hex ? '' : ' is-disabled' ),
					onClick: () => onChange( '' ),
					disabled: ! hex,
				},
				t( 'Default' )
			)
		)
	);
}

export function ToggleRow( { label, help, checked, onChange } ) {
	return el(
		'div',
		{ className: 'khb-field khb-toggle' },
		el(
			'label',
			{ className: 'khb-toggle__row' },
			el( 'span', { className: 'khb-toggle__label' }, label ),
			el( 'input', {
				type: 'checkbox',
				className: 'khb-switch',
				checked: !! checked,
				onChange: ( e ) => onChange( e.target.checked ),
			} )
		),
		help ? el( 'p', { className: 'khb-help' }, help ) : null
	);
}

/** Info notice (safe-mode HTML, WooCommerce requirement, …). */
export function Note( { children } ) {
	return el( 'div', { className: 'khb-note', role: 'note' }, children );
}
