/**
 * Dazhen Blocks — front-end form handler (standalone mode, no theme).
 * Binds the compact lead form to the plugin's REST endpoint.
 */
( function () {
	'use strict';

	function on( el, ev, fn ) {
		if ( el.addEventListener ) {
			el.addEventListener( ev, fn, false );
		}
	}

	function ready( fn ) {
		if ( document.readyState !== 'loading' ) {
			fn();
		} else {
			on( document, 'DOMContentLoaded', fn );
		}
	}

	var MSG = {
		sending: ( window.DazhenBlocksI18n && window.DazhenBlocksI18n.sending ) || 'Sending…',
		done: ( window.DazhenBlocksI18n && window.DazhenBlocksI18n.done ) || 'Thank you — we received your inquiry and will reply within one working day.',
		fail: ( window.DazhenBlocksI18n && window.DazhenBlocksI18n.fail ) || 'Something went wrong. Please email us directly.'
	};

	ready( function () {
		var forms = document.querySelectorAll( 'form.dzb-form' );
		Array.prototype.forEach.call( forms, function ( form ) {
			on( form, 'submit', function ( e ) {
				e.preventDefault();

				var note = form.querySelector( '[data-dzb-form-note]' );
				var btn = form.querySelector( 'button[type="submit"]' );
				if ( btn ) {
					btn.disabled = true;
				}
				if ( note ) {
					note.textContent = MSG.sending;
				}

				var payload = {};
				Array.prototype.forEach.call( form.elements, function ( el ) {
					if ( el.name ) {
						payload[ el.name ] = el.value;
					}
				} );

				var cfg = window.DazhenBlocksForm || {};
				fetch( cfg.rest, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': cfg.nonce || ''
					},
					credentials: 'same-origin',
					body: JSON.stringify( payload )
				} )
					.then( function ( res ) {
						return res.json().then( function ( json ) {
							return { ok: res.ok, json: json };
						} );
					} )
					.then( function ( out ) {
						if ( out.ok && out.json && out.json.ok ) {
							form.reset();
							if ( note ) {
								note.textContent = MSG.done;
								note.setAttribute( 'data-dzb-ok', '1' );
							}
						} else {
							throw out.json;
						}
					} )
					.catch( function () {
						if ( btn ) {
							btn.disabled = false;
						}
						if ( note ) {
							note.textContent = ( arguments && arguments[ 0 ] && arguments[ 0 ].message ) || MSG.fail;
						}
					} );
			} );
		} );
	} );
}() );
