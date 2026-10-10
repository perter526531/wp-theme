/**
 * Dazhen theme — front-end behaviors (no jQuery, no dependencies).
 *
 * 1. Header scroll state
 * 2. Mobile navigation toggle
 * 3. Reveal-on-scroll via IntersectionObserver
 * 4. Single-open accordion groups ([data-dz-accordion])
 * 5. RFQ popup modal (.dz-rfq-open → #dz-rfq, fetch submit, no page leave)
 * 6. Language dropdown (.lang-drop, outside click + Escape)
 * 7. Product gallery carousel ([data-dz-gallery])
 */

( function () {
        'use strict';

        var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

        /* ---------- 1. Header scroll state ---------- */
        var header = document.querySelector( '[data-dz-header]' );
        if ( header ) {
                var onScroll = function () {
                        header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
                };
                window.addEventListener( 'scroll', onScroll, { passive: true } );
                onScroll();
        }

        /* ---------- 2. Mobile navigation toggle ---------- */
        var toggle = document.querySelector( '[data-dz-nav-toggle]' );
        var nav = document.getElementById( 'primary-nav' );
        if ( toggle && nav ) {
                toggle.addEventListener( 'click', function () {
                        var open = nav.classList.toggle( 'is-open' );
                        header.classList.toggle( 'is-open', open );
                        toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
                } );

                nav.addEventListener( 'click', function ( event ) {
                        if ( event.target.closest( 'a' ) ) {
                                nav.classList.remove( 'is-open' );
                                header.classList.remove( 'is-open' );
                                toggle.setAttribute( 'aria-expanded', 'false' );
                        }
                } );

                document.addEventListener( 'keydown', function ( event ) {
                        if ( event.key === 'Escape' && nav.classList.contains( 'is-open' ) ) {
                                nav.classList.remove( 'is-open' );
                                header.classList.remove( 'is-open' );
                                toggle.setAttribute( 'aria-expanded', 'false' );
                                toggle.focus();
                        }
                } );
        }

        /* ---------- 3. Reveal on scroll ---------- */
        var items = document.querySelectorAll( '[data-dz-reveal]' );
        if ( items.length ) {
                if ( reduced || ! ( 'IntersectionObserver' in window ) ) {
                        items.forEach( function ( el ) {
                                el.classList.add( 'is-visible' );
                        } );
                } else {
                        var observer = new IntersectionObserver(
                                function ( entries ) {
                                        entries.forEach( function ( entry ) {
                                                if ( entry.isIntersecting ) {
                                                        entry.target.classList.add( 'is-visible' );
                                                        observer.unobserve( entry.target );
                                                }
                                        } );
                                },
                                { rootMargin: '0px 0px -8% 0px', threshold: 0.05 }
                        );
                        items.forEach( function ( el ) {
                                observer.observe( el );
                        } );
                }
        }

        /* ---------- 4. Single-open accordion ---------- */
        var accordion = document.querySelector( '[data-dz-accordion]' );
        if ( accordion ) {
                var accBtns = Array.prototype.slice.call( accordion.querySelectorAll( '[data-dz-acc-btn]' ) );
                accBtns.forEach( function ( btn ) {
                        btn.addEventListener( 'click', function () {
                                var wasOpen = 'true' === btn.getAttribute( 'aria-expanded' );
                                accBtns.forEach( function ( other ) {
                                        other.setAttribute( 'aria-expanded', 'false' );
                                        var item = other.closest( '.faq-item' );
                                        if ( item ) {
                                                item.classList.remove( 'is-open' );
                                        }
                                        var panel = document.getElementById( other.getAttribute( 'aria-controls' ) );
                                        if ( panel ) {
                                                panel.setAttribute( 'hidden', '' );
                                        }
                                } );
                                if ( ! wasOpen ) {
                                        btn.setAttribute( 'aria-expanded', 'true' );
                                        var item2 = btn.closest( '.faq-item' );
                                        if ( item2 ) {
                                                item2.classList.add( 'is-open' );
                                        }
                                        var panel2 = document.getElementById( btn.getAttribute( 'aria-controls' ) );
                                        if ( panel2 ) {
                                                panel2.removeAttribute( 'hidden' );
                                        }
                                }
                        } );
                } );
        }

        /* ---------- 5. RFQ popup modal ---------- */
        var rfq = document.getElementById( 'dz-rfq' );
        if ( rfq ) {
                var rfqDialog = rfq.querySelector( '.dz-rfq__dialog' );
                var rfqForm = rfq.querySelector( 'form' );
                var rfqSuccess = rfq.querySelector( '[data-dz-rfq-success]' );
                var rfqBody = rfq.querySelector( '[data-dz-rfq-body]' );
                var lastFocus = null;

                var rfqOpen = function ( product ) {
                        lastFocus = document.activeElement;
                        if ( product ) {
                                var field = rfq.querySelector( '#dz_product' );
                                if ( field && ! field.value ) {
                                        field.value = product;
                                }
                        }
                        rfq.removeAttribute( 'hidden' );
                        document.body.classList.add( 'dz-rfq-open' );
                        var first = rfq.querySelector( 'input:not([type=hidden]), textarea, button' );
                        if ( first ) {
                                                first.focus();
                        }
                };
                var rfqClose = function () {
                        rfq.setAttribute( 'hidden', '' );
                        document.body.classList.remove( 'dz-rfq-open' );
                        if ( rfqSuccess && rfqBody ) {
                                rfqSuccess.setAttribute( 'hidden', '' );
                                rfqBody.removeAttribute( 'hidden' );
                        }
                        if ( lastFocus && lastFocus.focus ) {
                                lastFocus.focus();
                        }
                };

                document.addEventListener( 'click', function ( event ) {
                        var opener = event.target.closest( '.dz-rfq-open' );
                        if ( opener ) {
                                event.preventDefault();
                                rfqOpen( opener.getAttribute( 'data-dz-product' ) || '' );
                                return;
                        }
                        if ( event.target.closest( '[data-dz-rfq-close]' ) ) {
                                rfqClose();
                        }
                } );

                document.addEventListener( 'keydown', function ( event ) {
                        if ( event.key === 'Escape' && ! rfq.hasAttribute( 'hidden' ) ) {
                                rfqClose();
                        }
                } );

                /* Submit via fetch: visitor stays on the page. */
                if ( rfqForm && rfqForm.addEventListener ) {
                        rfqForm.addEventListener( 'submit', function ( event ) {
                                event.preventDefault();
                                var btn = rfqForm.querySelector( 'button[type="submit"]' );
                                if ( btn ) {
                                        btn.disabled = true;
                                }
                                fetch( rfqForm.getAttribute( 'action' ) || window.location.href, {
                                        method: 'POST',
                                        body: new FormData( rfqForm ),
                                        credentials: 'same-origin',
                                        redirect: 'follow'
                                } ).then( function ( res ) {
                                        var ok = res.redirected && /inquiry=success/.test( res.url );
                                        if ( ok && rfqSuccess && rfqBody ) {
                                                rfqBody.setAttribute( 'hidden', '' );
                                                rfqSuccess.removeAttribute( 'hidden' );
                                                rfqSuccess.scrollIntoView( { block: 'nearest' } );
                                        } else {
                                                rfqForm.reset();
                                                window.alert( 'Sorry — something went wrong. Please use the contact page form instead.' );
                                        }
                                } ).catch( function () {
                                        window.alert( 'Sorry — the network failed. Please try again.' );
                                } ).then( function () {
                                        if ( btn ) {
                                                btn.disabled = false;
                                        }
                                } );
                        } );
                }
        }

        /* ---------- 6. Language dropdown ---------- */
        Array.prototype.forEach.call( document.querySelectorAll( '.lang-drop' ), function ( drop ) {
                var btn = drop.querySelector( '.lang-drop__btn' );
                var panel = drop.querySelector( '.lang-drop__panel' );
                if ( ! btn || ! panel ) {
                        return;
                }
                btn.addEventListener( 'click', function ( event ) {
                        event.stopPropagation();
                        var open = drop.classList.toggle( 'is-open' );
                        btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
                        panel.setAttribute( 'aria-hidden', open ? 'false' : 'true' );
                } );
                document.addEventListener( 'click', function ( event ) {
                        if ( drop.classList.contains( 'is-open' ) && ! drop.contains( event.target ) ) {
                                drop.classList.remove( 'is-open' );
                                btn.setAttribute( 'aria-expanded', 'false' );
                                panel.setAttribute( 'aria-hidden', 'true' );
                        }
                } );
                drop.addEventListener( 'keydown', function ( event ) {
                        if ( event.key === 'Escape' && drop.classList.contains( 'is-open' ) ) {
                                drop.classList.remove( 'is-open' );
                                btn.setAttribute( 'aria-expanded', 'false' );
                                panel.setAttribute( 'aria-hidden', 'true' );
                                btn.focus();
                        }
                } );
        } );

        /* ---------- 7. Product gallery carousel ---------- */
        Array.prototype.forEach.call( document.querySelectorAll( '[data-dz-gallery]' ), function ( gallery ) {
                var frames = gallery.querySelectorAll( '.pdp-gallery__frame' );
                var thumbs = gallery.querySelectorAll( '.pdp-gallery__thumb' );
                if ( frames.length < 2 ) {
                        return;
                }
                var index = 0;
                var show = function ( i ) {
                        index = ( i + frames.length ) % frames.length;
                        Array.prototype.forEach.call( frames, function ( frame, fi ) {
                                frame.classList.toggle( 'is-active', fi === index );
                                if ( fi === index ) {
                                        frame.removeAttribute( 'hidden' );
                                } else {
                                        frame.setAttribute( 'hidden', '' );
                                }
                        } );
                        Array.prototype.forEach.call( thumbs, function ( thumb, ti ) {
                                thumb.classList.toggle( 'is-active', ti === index );
                        } );
                };
                var prev = gallery.querySelector( '[data-dz-gallery-prev]' );
                var next = gallery.querySelector( '[data-dz-gallery-next]' );
                if ( prev ) {
                        prev.addEventListener( 'click', function () { show( index - 1 ); } );
                }
                if ( next ) {
                        next.addEventListener( 'click', function () { show( index + 1 ); } );
                }
                Array.prototype.forEach.call( thumbs, function ( thumb, ti ) {
                        thumb.addEventListener( 'click', function () { show( ti ); } );
                } );
        } );
} )();
