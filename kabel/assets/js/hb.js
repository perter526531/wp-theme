/**
 * Kabel Header Builder — front-end behaviors (native ES module, no jQuery,
 * no dependencies; data-kbhb-* attribute driven).
 *
 * 1. Off-canvas drawer (burger → drawer + scrim, ESC, focus management)
 * 2. Drawer submenu accordion ([data-kbhb-sub] toggles, max-height animation)
 * 3. Search modal ([data-kbhb-search-open] → [data-kbhb-search])
 * 4. Sticky shadow (.kbhb--sticky gains .is-stuck past 4px of scroll)
 *
 * Progressive enhancement: the drawer, scrim and search modal all render with
 * the [hidden] attribute and only ever lose it through this script — without
 * JS the desktop header stays fully usable and the overlays never block.
 *
 * Re-initialization: the Customizer preview bridge swaps the whole header
 * element, so everything is reachable again through window.kabelHBInit()
 * (idempotent — re-running on an already-bound root is a no-op). The only
 * document-level listener (ESC) is registered once and dispatches to the
 * current header; the scroll listener is swapped along with the root.
 */

( function () {
	'use strict';

	var escState = null; /* { searchIsOpen, closeSearch, drawerIsOpen, closeDrawer } */
	var scrollHandler = null;

	var init = function () {
		var root = document.querySelector( '[data-kbhb]' );
		if ( ! root ) {
			return;
		}
		if ( '1' === root.getAttribute( 'data-kbhb-ready' ) ) {
			return; /* Already bound (double init on the same DOM). */
		}
		root.setAttribute( 'data-kbhb-ready', '1' );

		var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		var DURATION = 280; /* keep in sync with the hb.css drawer transition. */

		/* ---------- 1. Off-canvas drawer ---------- */
		var trigger = root.querySelector( '[data-kbhb-trigger]' );
		var drawer = root.querySelector( '[data-kbhb-drawer]' );
		var scrim = root.querySelector( '[data-kbhb-scrim]' );
		var drawerClose = drawer ? drawer.querySelector( '[data-kbhb-drawer-close]' ) : null;
		var drawerLastFocus = null;
		var drawerTimer = null;

		var drawerIsOpen = function () {
			return !! drawer && drawer.classList.contains( 'is-open' );
		};

		var openDrawer = function () {
			if ( ! drawer || ! trigger ) {
				return;
			}
			drawerLastFocus = document.activeElement;
			if ( drawerTimer ) {
				window.clearTimeout( drawerTimer );
				drawerTimer = null;
			}
			drawer.removeAttribute( 'hidden' );
			if ( scrim ) {
				scrim.removeAttribute( 'hidden' );
			}
			/* Force a reflow so the transition starts from the closed state. */
			void drawer.offsetHeight;
			drawer.classList.add( 'is-open' );
			if ( scrim ) {
				scrim.classList.add( 'is-open' );
			}
			document.documentElement.classList.add( 'kbhb-lock' );
			trigger.setAttribute( 'aria-expanded', 'true' );
			if ( drawerClose ) {
				drawerClose.focus();
			}
		};

		var closeDrawer = function () {
			if ( ! drawer || ! drawerIsOpen() ) {
				return;
			}
			drawer.classList.remove( 'is-open' );
			if ( scrim ) {
				scrim.classList.remove( 'is-open' );
			}
			document.documentElement.classList.remove( 'kbhb-lock' );
			if ( trigger ) {
				trigger.setAttribute( 'aria-expanded', 'false' );
			}
			drawerTimer = window.setTimeout( function () {
				drawer.setAttribute( 'hidden', '' );
				if ( scrim ) {
					scrim.setAttribute( 'hidden', '' );
				}
				drawerTimer = null;
			}, reduced ? 0 : DURATION );
			if ( drawerLastFocus && drawerLastFocus.focus ) {
				drawerLastFocus.focus();
				drawerLastFocus = null;
			}
		};

		if ( trigger && drawer ) {
			trigger.addEventListener( 'click', function () {
				if ( drawerIsOpen() ) {
					closeDrawer();
				} else {
					openDrawer();
				}
			} );
		}
		if ( drawerClose ) {
			drawerClose.addEventListener( 'click', closeDrawer );
		}
		if ( scrim ) {
			scrim.addEventListener( 'click', closeDrawer );
		}
		if ( drawer ) {
			/* Following a link navigates — close the overlay cleanly. */
			drawer.addEventListener( 'click', function ( event ) {
				if ( event.target.closest( 'a' ) ) {
					drawerLastFocus = null;
					closeDrawer();
				}
			} );
		}

		/* ---------- 2. Drawer submenu accordion ---------- */
		if ( drawer ) {
			var subToggles = drawer.querySelectorAll( '[data-kbhb-sub]' );
			Array.prototype.forEach.call( subToggles, function ( btn ) {
				btn.addEventListener( 'click', function () {
					var open = 'true' === btn.getAttribute( 'aria-expanded' );
					var panel = document.getElementById( btn.getAttribute( 'aria-controls' ) );
					btn.setAttribute( 'aria-expanded', open ? 'false' : 'true' );
					if ( ! panel ) {
						return;
					}
					if ( open ) {
						panel.classList.remove( 'is-open' );
						panel.style.maxHeight = '0px';
					} else {
						panel.classList.add( 'is-open' );
						panel.style.maxHeight = panel.scrollHeight + 'px';
					}
				} );
			} );
		}

		/* ---------- 3. Search modal ---------- */
		var search = root.querySelector( '[data-kbhb-search]' );
		var searchInput = search ? search.querySelector( 'input[name="s"]' ) : null;
		var searchClose = search ? search.querySelector( '[data-kbhb-search-close]' ) : null;
		var searchLastFocus = null;

		var searchIsOpen = function () {
			return !! search && search.classList.contains( 'is-open' );
		};

		var openSearch = function () {
			if ( ! search ) {
				return;
			}
			search.removeAttribute( 'hidden' );
			void search.offsetHeight;
			search.classList.add( 'is-open' );
			document.documentElement.classList.add( 'kbhb-lock' );
			if ( searchInput ) {
				searchInput.focus();
			}
		};

		var closeSearch = function () {
			if ( ! search || ! searchIsOpen() ) {
				return;
			}
			search.classList.remove( 'is-open' );
			document.documentElement.classList.remove( 'kbhb-lock' );
			window.setTimeout( function () {
				if ( ! search.classList.contains( 'is-open' ) ) {
					search.setAttribute( 'hidden', '' );
				}
			}, reduced ? 0 : 220 );
			if ( searchLastFocus && searchLastFocus.focus ) {
				searchLastFocus.focus();
				searchLastFocus = null;
			}
		};

		var searchOpeners = root.querySelectorAll( '[data-kbhb-search-open]' );
		Array.prototype.forEach.call( searchOpeners, function ( btn ) {
			btn.addEventListener( 'click', function () {
				searchLastFocus = btn;
				if ( searchIsOpen() ) {
					closeSearch();
				} else {
					openSearch();
				}
			} );
		} );
		if ( searchClose ) {
			searchClose.addEventListener( 'click', closeSearch );
		}
		if ( search ) {
			/* Click on the backdrop (the modal itself, not its children). */
			search.addEventListener( 'click', function ( event ) {
				if ( event.target === search ) {
					closeSearch();
				}
			} );
		}

		/* Hand the ESC dispatcher the live closers for this root. */
		escState = {
			searchIsOpen: searchIsOpen,
			closeSearch: closeSearch,
			drawerIsOpen: drawerIsOpen,
			closeDrawer: closeDrawer
		};

		/* ---------- 4. Sticky shadow ---------- */
		if ( scrollHandler ) {
			window.removeEventListener( 'scroll', scrollHandler );
			scrollHandler = null;
		}
		if ( root.classList.contains( 'kbhb--sticky' ) ) {
			scrollHandler = function () {
				root.classList.toggle( 'is-stuck', window.scrollY > 4 );
			};
			window.addEventListener( 'scroll', scrollHandler, { passive: true } );
			scrollHandler();
		}
	};

	/* Registered once — dispatches to whichever header is currently live. */
	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' !== event.key || ! escState ) {
			return;
		}
		if ( escState.searchIsOpen() ) {
			escState.closeSearch();
		} else if ( escState.drawerIsOpen() ) {
			escState.closeDrawer();
		}
	} );

	/* Public re-init hook for the Customizer preview bridge. */
	window.kabelHBInit = init;

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
