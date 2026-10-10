/**
 * Kabel Blocks — crash-proof registration wrapper.
 *
 * Every block's edit() renders inside a small React error boundary, so a
 * failure in ONE block (a WP-version API drift, a bad attribute, a plugin
 * conflict) can never take the whole editor down with "The editor has
 * encountered an unexpected error". The editor is the owner's workspace —
 * a single broken block must degrade to a placeholder, not a white modal.
 *
 * save() is NOT wrapped: published markup must stay byte-identical (the
 * emitter twins in inc/emitter.php mirror it exactly).
 *
 * Usage (drop-in for registerBlockType):
 *   import { register } from '../register.js';
 *   register( 'kbl/heading', { apiVersion: 3, … } );
 */
import { el, Component } from '@wordpress/element';
import { registerBlockType } from '@wordpress/blocks';

/** Neutral placeholder shown when a block's edit view throws. */
class KblEditBoundary extends Component {
	constructor( props ) {
		super( props );
		this.state = { failed: false };
	}

	static getDerivedStateFromError() {
		return { failed: true };
	}

	componentDidCatch( error ) {
		/* Surface the real error for debugging, keep the editor alive. */
		if ( typeof console !== 'undefined' && console.error ) {
			console.error( '[kabel-blocks] block edit failed:', error );
		}
	}

	render() {
		if ( this.state.failed ) {
			return el(
				'div',
				{ className: 'kbl3-edit-error' },
				el(
					'p',
					null,
					'⚠ ',
					'This block could not be displayed in the editor — the front end is unaffected. ' +
						'Updating the Kabel Blocks plugin usually fixes this.'
				)
			);
		}
		return this.props.children;
	}
}

/**
 * Wrap an edit component with the boundary. The wrapper itself holds no
 * hooks, and the original edit() runs as its own component instance, so
 * hook order inside it is untouched.
 *
 * @param {Function} Edit Original edit function.
 * @return {Function} Guarded edit function.
 */
export function safeEdit( Edit ) {
	if ( typeof Edit !== 'function' ) {
		return Edit;
	}
	return function KblSafeEdit( props ) {
		return el( KblEditBoundary, null, el( Edit, props ) );
	};
}

/**
 * registerBlockType with the edit() boundary pre-applied.
 *
 * @param {string} name     Block name, e.g. 'kbl/heading'.
 * @param {Object} settings Block settings (apiVersion, edit, save, …).
 * @return {Object} The registered block, per registerBlockType.
 */
export function register( name, settings ) {
	if ( settings && typeof settings.edit === 'function' ) {
		return registerBlockType( name, {
			...settings,
			edit: safeEdit( settings.edit ),
		} );
	}
	return registerBlockType( name, settings );
}
