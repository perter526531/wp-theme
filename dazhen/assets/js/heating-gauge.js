/**
 * Dazhen theme — "3-second instant heat" gauge (homepage hero module #1).
 *
 * Animates an SVG progress ring from 25°C to 95°C over 3 seconds,
 * holds for 1.2 seconds, then loops. Honors prefers-reduced-motion
 * by rendering the final (95°C) state statically.
 */

( function () {
	'use strict';

	var RADIUS = 34;
	var CIRCUMFERENCE = 2 * Math.PI * RADIUS;
	var TEMP_MIN = 25;
	var TEMP_MAX = 95;
	var HEAT_MS = 3000;
	var HOLD_MS = 1200;

	var gauges = document.querySelectorAll( '[data-dz-gauge]' );

	gauges.forEach( function ( gauge ) {
		var bar = gauge.querySelector( '.hero-gauge__bar' );
		var readout = gauge.querySelector( '[data-dz-gauge-temp]' );
		if ( ! bar || ! readout ) {
			return;
		}

		bar.style.strokeDasharray = String( CIRCUMFERENCE );

		var render = function ( progress ) {
			// progress 0..1
			bar.style.strokeDashoffset = String( CIRCUMFERENCE * ( 1 - progress ) );
			var temp = Math.round( TEMP_MIN + ( TEMP_MAX - TEMP_MIN ) * progress );
			readout.textContent = temp + '°';
		};

		var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		if ( reduced ) {
			render( 1 );
			return;
		}

		var cycle = HEAT_MS + HOLD_MS;
		var start = null;

		var frame = function ( now ) {
			if ( start === null ) {
				start = now;
			}
			var t = ( now - start ) % cycle;
			render( Math.min( 1, t / HEAT_MS ) );
			window.requestAnimationFrame( frame );
		};
		window.requestAnimationFrame( frame );
	} );
} )();
