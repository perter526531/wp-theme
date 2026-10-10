/**
 * Dazhen theme — OEM journey interactive timeline (module #4).
 *
 * Tablist behaviour: click / ArrowLeft / ArrowRight / Home / End.
 * The progress fill is measured against the node button centres by scanning
 * the base path for the closest x — robust across container widths.
 * Vertical layout on small screens hides the SVG path entirely.
 */

( function () {
	'use strict';

	var wrap = document.querySelector( '[data-dz-oem-journey]' );
	if ( ! wrap ) {
		return;
	}

	var tabs = Array.prototype.slice.call( wrap.querySelectorAll( '[role="tab"]' ) );
	var panels = Array.prototype.slice.call( wrap.querySelectorAll( '[role="tabpanel"]' ) );
	var basePath = wrap.querySelector( '.oem-journey__path-base' );
	var fillPath = wrap.querySelector( '.oem-journey__path-fill' );
	var svg = basePath ? basePath.ownerSVGElement : null;
	var current = 0;

	function isVertical() {
		return window.matchMedia( '( max-width: 900px )' ).matches;
	}

	function pathLengthAtX( targetX ) {
		if ( ! basePath || ! basePath.getTotalLength ) {
			return 0;
		}
		var total = basePath.getTotalLength();
		var best = 0;
		var bestDx = Infinity;
		for ( var l = 0; l <= total; l += 4 ) {
			var pt = basePath.getPointAtLength( l );
			var dx = Math.abs( pt.x - targetX );
			if ( dx < bestDx ) {
				bestDx = dx;
				best = l;
			}
		}
		return best;
	}

	function updateFill( index ) {
		if ( ! fillPath || ! svg || isVertical() ) {
			return;
		}
		var svgRect = svg.getBoundingClientRect();
		var btnRect = tabs[ index ].getBoundingClientRect();
		if ( ! svgRect.width || ! btnRect.width ) {
			return;
		}
		/* Map the button centre from screen space into the 700-unit viewBox. */
		var centerX = ( ( btnRect.left + btnRect.width / 2 - svgRect.left ) / svgRect.width ) * 700;
		var length = pathLengthAtX( centerX );
		var total = basePath.getTotalLength();
		fillPath.style.strokeDasharray = total + ' ' + total;
		fillPath.style.strokeDashoffset = String( total - length );
	}

	function activate( index, focus ) {
		index = Math.max( 0, Math.min( tabs.length - 1, index ) );
		current = index;

		tabs.forEach( function ( tab, i ) {
			var on = i === index;
			tab.setAttribute( 'aria-selected', on ? 'true' : 'false' );
			tab.tabIndex = on ? 0 : -1;
			tab.classList.toggle( 'is-active', on );
			tab.classList.toggle( 'is-done', i < index );
			var li = tab.closest( '.oem-journey__node' );
			if ( li ) {
				li.classList.toggle( 'is-active', on );
				li.classList.toggle( 'is-done', i < index );
			}
			var panel = panels[ i ];
			if ( panel ) {
				if ( on ) {
					panel.removeAttribute( 'hidden' );
					panel.classList.add( 'is-active' );
				} else {
					panel.setAttribute( 'hidden', '' );
					panel.classList.remove( 'is-active' );
				}
			}
		} );

		updateFill( index );
		if ( focus ) {
			tabs[ index ].focus();
		}
	}

	tabs.forEach( function ( tab, i ) {
		tab.addEventListener( 'click', function () {
			activate( i, false );
		} );
		tab.addEventListener( 'keydown', function ( event ) {
			var key = event.key;
			if ( 'ArrowRight' === key || 'ArrowDown' === key ) {
				event.preventDefault();
				activate( i + 1, true );
			} else if ( 'ArrowLeft' === key || 'ArrowUp' === key ) {
				event.preventDefault();
				activate( i - 1, true );
			} else if ( 'Home' === key ) {
				event.preventDefault();
				activate( 0, true );
			} else if ( 'End' === key ) {
				event.preventDefault();
				activate( tabs.length - 1, true );
			}
		} );
	} );

	var resizeTimer;
	window.addEventListener(
		'resize',
		function () {
			window.clearTimeout( resizeTimer );
			resizeTimer = window.setTimeout( function () {
				updateFill( current );
			}, 120 );
		}
	);

	activate( 0, false );
} )();
