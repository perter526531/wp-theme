/**
 * Dazhen theme — Solution Finder (3-question product wizard).
 *
 * Reads product data from the JSON script tag rendered by
 * template-parts/selector-wizard.php, drives the step flow and scores
 * matches: scenario ×2 + install ×1 + water ×1, top 3 shown.
 *
 * No jQuery, no dependencies. Progressive enhancement only — the catalog
 * stays fully usable without JS.
 */

( function () {
        'use strict';

        var root = document.querySelector( '[data-dz-finder]' );
        if ( ! root ) {
                return;
        }

        var dataEl = root.querySelector( '[data-dz-finder-data]' );
        if ( ! dataEl ) {
                return;
        }

        var products;
        try {
                products = JSON.parse( dataEl.textContent || '[]' );
        } catch ( e ) {
                return;
        }

        var KEYS = [ 'scenario', 'install', 'water' ];
        var SCORE = { scenario: 2, install: 1, water: 1 };
        var MAX_RESULTS = 3;

        var answers = { scenario: null, install: null, water: null };
        var step = 0; // 0..2 = questions, 3 = results.

        var panels = {
                scenario: root.querySelector( '[data-dz-finder-panel="scenario"]' ),
                install: root.querySelector( '[data-dz-finder-panel="install"]' ),
                water: root.querySelector( '[data-dz-finder-panel="water"]' ),
                results: root.querySelector( '[data-dz-finder-panel="results"]' ),
        };
        var stepBtns = root.querySelectorAll( '[data-dz-finder-goto]' );
        var matchesEl = root.querySelector( '[data-dz-finder-matches]' );
        var resultsTitle = root.querySelector( '[data-dz-finder-results-title]' );

        /* ---------- helpers ---------- */

        function showPanel() {
                KEYS.forEach( function ( key ) {
                        panels[ key ].hidden = ( KEYS[ step ] !== key );
                } );
                panels.results.hidden = ( step !== 3 );

                /* Step indicators. */
                [].forEach.call( stepBtns, function ( btn, i ) {
                        var li = btn.closest( '.finder__step' );
                        if ( ! li ) {
                                return;
                        }
                        li.classList.toggle( 'is-active', i === step );
                        li.classList.toggle( 'is-done', i < step );
                        btn.disabled = i >= step; // Only completed steps are clickable.
                } );

                /* Mark selected options when navigating back. */
                if ( step >= 0 && step < 3 ) {
                        var key = KEYS[ step ];
                        var opts = panels[ key ].querySelectorAll( '[data-dz-finder-answer]' );
                        [].forEach.call( opts, function ( opt ) {
                                opt.classList.toggle( 'is-selected', opt.getAttribute( 'data-dz-finder-answer' ) === answers[ key ] );
                        } );
                }
        }

        function score( product ) {
                var total = 0;
                KEYS.forEach( function ( key ) {
                        if ( answers[ key ] && product.tags && product.tags[ key ] &&
                                product.tags[ key ].indexOf( answers[ key ] ) !== -1 ) {
                                total += SCORE[ key ];
                        }
                } );
                return total;
        }

        function buildMatchCard( product ) {
                var card = document.createElement( 'a' );
                card.className = 'finder__match';
                card.href = product.url;

                var media = document.createElement( 'span' );
                media.className = 'finder__match-media';
                if ( product.img ) {
                        var img = document.createElement( 'img' );
                        img.src = product.img;
                        img.alt = '';
                        img.loading = 'lazy';
                        media.appendChild( img );
                }
                card.appendChild( media );

                var info = document.createElement( 'span' );
                info.className = 'finder__match-info';

                var model = document.createElement( 'strong' );
                model.className = 'finder__match-model';
                model.textContent = product.model;
                info.appendChild( model );

                var series = document.createElement( 'span' );
                series.className = 'finder__match-series';
                series.textContent = product.series;
                info.appendChild( series );

                var more = document.createElement( 'span' );
                more.className = 'finder__match-more';
                more.textContent = 'View details →';
                info.appendChild( more );

                card.appendChild( info );
                return card;
        }

        function renderResults() {
                matchesEl.innerHTML = '';

                var ranked = products
                        .map( function ( p ) {
                                return { p: p, s: score( p ) };
                        } )
                        .filter( function ( item ) {
                                return item.s > 0;
                        } )
                        .sort( function ( a, b ) {
                                return b.s - a.s;
                        } )
                        .slice( 0, MAX_RESULTS );

                if ( ranked.length ) {
                        ranked.forEach( function ( item ) {
                                matchesEl.appendChild( buildMatchCard( item.p ) );
                        } );
                        if ( resultsTitle ) {
                                resultsTitle.textContent = 'Your matches';
                        }
                } else {
                        var empty = document.createElement( 'p' );
                        empty.className = 'finder__no-match';
                        empty.textContent = 'No catalog match — our specialty platforms are built to order:';
                        matchesEl.appendChild( empty );
                        if ( resultsTitle ) {
                                resultsTitle.textContent = 'Built to order';
                        }
                }
        }

        /* ---------- events ---------- */

        root.addEventListener( 'click', function ( event ) {
                var option = event.target.closest( '[data-dz-finder-answer]' );

                if ( option && step < 3 ) {
                        var key = KEYS[ step ];
                        answers[ key ] = option.getAttribute( 'data-dz-finder-answer' );
                        step += 1;
                        if ( step === 3 ) {
                                renderResults();
                        }
                        showPanel();
                        return;
                }

                var gotoBtn = event.target.closest( '[data-dz-finder-goto]' );
                if ( gotoBtn ) {
                        var target = parseInt( gotoBtn.getAttribute( 'data-dz-finder-goto' ), 10 );
                        if ( ! isNaN( target ) && target < step ) {
                                step = target;
                                showPanel();
                        }
                        return;
                }

                if ( event.target.closest( '[data-dz-finder-back]' ) ) {
                        if ( step > 0 ) {
                                step -= 1;
                                showPanel();
                        }
                        return;
                }

                if ( event.target.closest( '[data-dz-finder-restart]' ) ) {
                        answers = { scenario: null, install: null, water: null };
                        step = 0;
                        showPanel();
                }
        } );

        showPanel();
} )();
