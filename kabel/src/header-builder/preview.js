/**
 * Kabel Header Builder — Customizer preview bridge (bundle hb-preview.js).
 *
 * Runs inside the Customizer preview iframe (deps: customize-preview).
 * Listens to the `kabel_header_builder` setting (postMessage transport),
 * debounces ~400ms, POSTs the model to the kabel/v1/header-builder/render
 * endpoint (X-WP-Nonce, edit_theme_options) and swaps the returned header
 * element over header[data-kb-header], then re-initializes the front-end
 * behaviors via window.kabelHBInit() (drawer / accordion / search modal /
 * sticky). The response carries a model hash + the dynamic breakpoint CSS:
 *
 *  - hash matches the DOM  → no-op (skips flicker on preview load, when the
 *    server already rendered the very model the pane is holding)
 *  - model has no rows     → the site falls back to the classic header, which
 *    only the server can render → request a full preview refresh
 *  - fetch fails           → console.warn and skip; the next change retries
 */
( function () {
	'use strict';

	var DATA = window.KabelHBPreview || {};
	var DELAY_MS = 400;
	var timer = null;
	var lastRaw = null;

	function parseModel( raw ) {
		if ( typeof raw !== 'string' || '' === raw ) {
			return null;
		}
		try {
			var parsed = JSON.parse( raw );
			return parsed && typeof parsed === 'object' ? parsed : null;
		} catch ( err ) {
			return null;
		}
	}

	function hasRows( model ) {
		return !!( model && Array.isArray( model.rows ) && model.rows.length );
	}

	function currentDomHash() {
		var marker = document.querySelector( 'header[data-kb-header] [data-kbhb-hash]' );
		return marker ? marker.getAttribute( 'data-kbhb-hash' ) : '';
	}

	/* Keep the dynamic breakpoint media queries in sync with unsaved models. */
	function applyInlineCss( css ) {
		if ( ! css ) {
			return;
		}
		var style = document.getElementById( 'kabel-hb-inline-css' );
		if ( ! style ) {
			style = document.createElement( 'style' );
			style.id = 'kabel-hb-inline-css';
			var link = document.getElementById( 'kabel-hb-css' );
			if ( link && link.parentNode ) {
				link.parentNode.insertBefore( style, link.nextSibling );
			} else {
				document.head.appendChild( style );
			}
		}
		style.textContent = css;
	}

	/* Empty model → classic header, which only the server renders. The pane
	 * refresh re-renders with the changeset-applied setting value. */
	function requestRefresh() {
		if ( window.wp && window.wp.customize && window.wp.customize.preview ) {
			window.wp.customize.preview.send( 'refresh' );
		} else {
			window.location.reload();
		}
	}

	function renderModel( model ) {
		window
			.fetch( DATA.rest, {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': DATA.restNonce || '',
				},
				body: JSON.stringify( { model: model } ),
			} )
			.then( function ( res ) {
				if ( ! res.ok ) {
					throw new Error( 'HTTP ' + res.status );
				}
				return res.json();
			} )
			.then( function ( data ) {
				if ( ! data || data.ok !== true || ! data.html ) {
					return;
				}
				if ( data.hash && data.hash === currentDomHash() ) {
					applyInlineCss( data.inlineCss );
					return;
				}
				var headerEl = document.querySelector( 'header[data-kb-header]' );
				if ( ! headerEl ) {
					return;
				}
				headerEl.outerHTML = data.html;
				applyInlineCss( data.inlineCss );
				if ( window.kabelHBInit ) {
					window.kabelHBInit();
				}
			} )
			.catch( function ( err ) {
				/* Silent skip — the next change retries. */
				if ( window.console && window.console.warn ) {
					window.console.warn( '[kabel-hb] preview render failed:', err );
				}
			} );
	}

	function apply( raw ) {
		lastRaw = raw;
		var model = parseModel( raw );
		var live = !! document.querySelector( 'header[data-kb-header] [data-kbhb]' );
		if ( ! hasRows( model ) ) {
			if ( live ) {
				requestRefresh();
			}
			return;
		}
		renderModel( model );
	}

	if ( ! ( window.wp && window.wp.customize ) ) {
		return;
	}

	window.wp.customize( 'kabel_header_builder', function ( value ) {
		value.bind( function ( raw ) {
			if ( raw === lastRaw ) {
				return;
			}
			if ( timer ) {
				window.clearTimeout( timer );
			}
			timer = window.setTimeout( function () {
				apply( raw );
			}, DELAY_MS );
		} );
	} );
} )();
