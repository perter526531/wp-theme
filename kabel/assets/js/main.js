/**
 * Kabel theme — front-end behaviors (no jQuery, no dependencies).
 *
 * 1. Header scroll state
 * 2. Mobile navigation toggle
 * 3. Reveal-on-scroll via IntersectionObserver
 * 4. Single-open accordion groups ([data-kb-accordion])
 * 5. RFQ popup modal (.kb-rfq-open → #kb-rfq, fetch submit, no page leave)
 * 6. Language dropdown (.lang-drop, outside click + Escape)
 * 7. Product gallery carousel ([data-kb-gallery])
 * 8. Back-to-top button
 * 9. Reading progress bar (single post)
 * 10. Product gallery lightbox
 * 8. Back-to-top button ([data-kb-to-top])
 * 9. Reading progress bar ([data-kb-progress], single post)
 * 10. Product gallery lightbox ([data-kb-lightbox])
 * 11. Inquiry list — B2B product shortlist ([data-kb-add-list] → floating
 *     pill [data-kb-listbar] → RFQ modal prefilled with the saved models)
 */

( function () {
        'use strict';

        var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

        /* Progressive enhancement flag: reveal animations only run with JS on. */
        document.documentElement.classList.add( 'kb-js' );

        /* ---------- 1. Header scroll state ---------- */
        var header = document.querySelector( '[data-kb-header]' );
        if ( header ) {
                var onScroll = function () {
                        header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
                };
                window.addEventListener( 'scroll', onScroll, { passive: true } );
                onScroll();
        }

        /* ---------- 2. Mobile navigation toggle ---------- */
        var toggle = document.querySelector( '[data-kb-nav-toggle]' );
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
        var items = document.querySelectorAll( '[data-kb-reveal]' );
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
        var accordion = document.querySelector( '[data-kb-accordion]' );
        if ( accordion ) {
                var accBtns = Array.prototype.slice.call( accordion.querySelectorAll( '[data-kb-acc-btn]' ) );
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
        var rfq = document.getElementById( 'kb-rfq' );
        if ( rfq ) {
                var rfqDialog = rfq.querySelector( '.kb-rfq__dialog' );
                var rfqForm = rfq.querySelector( 'form' );
                var rfqSuccess = rfq.querySelector( '[data-kb-rfq-success]' );
                var rfqBody = rfq.querySelector( '[data-kb-rfq-body]' );
                var lastFocus = null;

                var rfqOpen = function ( product ) {
                        lastFocus = document.activeElement;
                        if ( product ) {
                                var field = rfq.querySelector( '#kb_product' );
                                if ( field && ! field.value ) {
                                        field.value = product;
                                }
                        }
                        rfq.removeAttribute( 'hidden' );
                        document.body.classList.add( 'kb-rfq-open' );
                        var first = rfq.querySelector( 'input:not([type=hidden]), textarea, button' );
                        if ( first ) {
                                                first.focus();
                        }
                };
                var rfqClose = function () {
                        rfq.setAttribute( 'hidden', '' );
                        document.body.classList.remove( 'kb-rfq-open' );
                        if ( rfqSuccess && rfqBody ) {
                                rfqSuccess.setAttribute( 'hidden', '' );
                                rfqBody.removeAttribute( 'hidden' );
                        }
                        if ( lastFocus && lastFocus.focus ) {
                                lastFocus.focus();
                        }
                };

                document.addEventListener( 'click', function ( event ) {
                        var opener = event.target.closest( '.kb-rfq-open' );
                        if ( opener ) {
                                event.preventDefault();
                                rfqOpen( opener.getAttribute( 'data-kb-product' ) || '' );
                                return;
                        }
                        if ( event.target.closest( '[data-kb-rfq-close]' ) ) {
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
                                                try {
                                                        window.dispatchEvent( new CustomEvent( 'kb:inquiry-success' ) );
                                                } catch ( e ) {}
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
        Array.prototype.forEach.call( document.querySelectorAll( '[data-kb-gallery]' ), function ( gallery ) {
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
                var prev = gallery.querySelector( '[data-kb-gallery-prev]' );
                var next = gallery.querySelector( '[data-kb-gallery-next]' );
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

        /* ---------- 8. Back-to-top button ---------- */
        var toTop = document.querySelector( '[data-kb-to-top]' );
        if ( toTop ) {
                var onTopScroll = function () {
                        toTop.classList.toggle( 'is-show', window.scrollY > 640 );
                };
                window.addEventListener( 'scroll', onTopScroll, { passive: true } );
                onTopScroll();

                toTop.addEventListener( 'click', function () {
                        window.scrollTo( { top: 0, behavior: reduced ? 'auto' : 'smooth' } );
                } );
        }

        /* ---------- 9. Reading progress bar (single post) ---------- */
        var progress = document.querySelector( '[data-kb-progress]' );
        if ( progress ) {
                var progressBar = progress.querySelector( 'span' );
                if ( progressBar ) {
                        var onProgressScroll = function () {
                                var max = document.documentElement.scrollHeight - window.innerHeight;
                                var ratio = max > 0 ? window.scrollY / max : 0;
                                ratio = Math.min( 1, Math.max( 0, ratio ) );
                                progressBar.style.transform = 'scaleX(' + ratio + ')';
                        };
                        window.addEventListener( 'scroll', onProgressScroll, { passive: true } );
                        window.addEventListener( 'resize', onProgressScroll, { passive: true } );
                        onProgressScroll();
                }
        }

        /* ---------- 10. Product gallery lightbox ---------- */
        Array.prototype.forEach.call( document.querySelectorAll( '[data-kb-lightbox]' ), function ( stage ) {
                var frames = Array.prototype.slice.call( stage.querySelectorAll( 'img' ) );
                if ( ! frames.length ) {
                        return;
                }

                var overlay = null;
                var imgView = null;
                var counter = null;
                var index = 0;
                var lastFocus = null;

                var srcOf = function ( img ) {
                        return img.currentSrc || img.getAttribute( 'src' ) || '';
                };

                var render = function () {
                        var frame = frames[ index ];
                        imgView.setAttribute( 'src', srcOf( frame ) );
                        var alt = frame.getAttribute( 'alt' );
                        if ( alt ) {
                                imgView.setAttribute( 'alt', alt );
                        } else {
                                imgView.removeAttribute( 'alt' );
                        }
                        if ( counter ) {
                                counter.textContent = ( index + 1 ) + ' / ' + frames.length;
                        }
                        /* Restart the fade-in when the source changes. */
                        imgView.style.animation = 'none';
                        void imgView.offsetWidth;
                        imgView.style.animation = '';
                };

                var show = function ( i ) {
                        index = ( i + frames.length ) % frames.length;
                        render();
                };

                var closeLightbox = function () {
                        if ( ! overlay ) {
                                return;
                        }
                        overlay.remove();
                        overlay = null;
                        imgView = null;
                        counter = null;
                        document.body.classList.remove( 'kb-lightbox-open' );
                        document.removeEventListener( 'keydown', onLightboxKey );
                        if ( lastFocus && lastFocus.focus ) {
                                lastFocus.focus();
                        }
                };

                var onLightboxKey = function ( event ) {
                        if ( ! overlay ) {
                                return;
                        }
                        if ( event.key === 'Escape' ) {
                                closeLightbox();
                        } else if ( event.key === 'ArrowLeft' && frames.length > 1 ) {
                                show( index - 1 );
                        } else if ( event.key === 'ArrowRight' && frames.length > 1 ) {
                                show( index + 1 );
                        }
                };

                var makeButton = function ( className, label, glyph ) {
                        var btn = document.createElement( 'button' );
                        btn.type = 'button';
                        btn.className = className;
                        btn.setAttribute( 'aria-label', label );
                        btn.appendChild( document.createTextNode( glyph ) );
                        return btn;
                };

                var openLightbox = function ( start ) {
                        if ( overlay ) {
                                return;
                        }
                        lastFocus = document.activeElement;
                        index = Math.max( 0, Math.min( frames.length - 1, start ) );

                        overlay = document.createElement( 'div' );
                        overlay.className = 'kb-lightbox';
                        overlay.setAttribute( 'role', 'dialog' );
                        overlay.setAttribute( 'aria-modal', 'true' );
                        overlay.setAttribute( 'aria-label', 'Image viewer' );

                        var closeBtn = makeButton( 'kb-lightbox__close', 'Close image viewer', '\u00d7' );
                        closeBtn.addEventListener( 'click', closeLightbox );
                        overlay.appendChild( closeBtn );

                        var figure = document.createElement( 'figure' );
                        figure.className = 'kb-lightbox__figure';
                        imgView = document.createElement( 'img' );
                        imgView.className = 'kb-lightbox__img';
                        figure.appendChild( imgView );
                        overlay.appendChild( figure );

                        if ( frames.length > 1 ) {
                                var prevBtn = makeButton( 'kb-lightbox__nav kb-lightbox__nav--prev', 'Previous image', '\u2039' );
                                prevBtn.addEventListener( 'click', function () { show( index - 1 ); } );
                                overlay.appendChild( prevBtn );

                                var nextBtn = makeButton( 'kb-lightbox__nav kb-lightbox__nav--next', 'Next image', '\u203a' );
                                nextBtn.addEventListener( 'click', function () { show( index + 1 ); } );
                                overlay.appendChild( nextBtn );

                                counter = document.createElement( 'p' );
                                counter.className = 'kb-lightbox__count';
                                counter.setAttribute( 'aria-live', 'polite' );
                                overlay.appendChild( counter );
                        }

                        /* Click on the dark backdrop (not the image) closes. */
                        overlay.addEventListener( 'click', function ( event ) {
                                if ( event.target === overlay || event.target === figure ) {
                                        closeLightbox();
                                }
                        } );

                        document.body.appendChild( overlay );
                        document.body.classList.add( 'kb-lightbox-open' );
                        document.addEventListener( 'keydown', onLightboxKey );

                        render();
                        closeBtn.focus();
                };

                /* Clicking any image inside the stage opens the viewer. */
                stage.addEventListener( 'click', function ( event ) {
                        var img = event.target.closest ? event.target.closest( 'img' ) : null;
                        if ( ! img || ! stage.contains( img ) ) {
                                return;
                        }
                        var at = frames.indexOf( img );
                        openLightbox( at < 0 ? 0 : at );
                } );
        } );

        /* ---------- 11. Inquiry list (B2B shortlist) ---------- */
        ( function () {
                var KEY = 'kb-inquiry-list';
                var bar = document.querySelector( '[data-kb-listbar]' );
                if ( ! bar ) {
                        return;
                }
                var countEl = bar.querySelector( '[data-kb-list-count]' );
                var labelEl = bar.querySelector( '[data-kb-list-label]' );

                var read = function () {
                        try {
                                var raw = window.localStorage.getItem( KEY );
                                var val = raw ? JSON.parse( raw ) : [];
                                return Array.isArray( val ) ? val.filter( function ( v ) {
                                        return typeof v === 'string' && v;
                                } ) : [];
                        } catch ( e ) {
                                return [];
                        }
                };
                var write = function ( list ) {
                        try {
                                window.localStorage.setItem( KEY, JSON.stringify( list ) );
                        } catch ( e ) {}
                };
                var paint = function () {
                        var list = read();
                        if ( list.length > 0 ) {
                                bar.removeAttribute( 'hidden' );
                                bar.classList.add( 'is-on' );
                        } else {
                                bar.setAttribute( 'hidden', '' );
                                bar.classList.remove( 'is-on' );
                        }
                        if ( countEl ) {
                                countEl.textContent = String( list.length );
                        }
                        if ( labelEl ) {
                                labelEl.textContent = list.length === 1 ? '1 model' : list.length + ' models';
                        }
                };

                /* Add-to-list buttons live anywhere on the page. */
                document.addEventListener( 'click', function ( event ) {
                        var add = event.target.closest ? event.target.closest( '[data-kb-add-list]' ) : null;
                        if ( add ) {
                                event.preventDefault();
                                var name = add.getAttribute( 'data-kb-add-list' ) || '';
                                if ( ! name ) {
                                        return;
                                }
                                var list = read();
                                if ( list.indexOf( name ) === -1 ) {
                                        list.push( name );
                                        write( list );
                                }
                                paint();
                                bar.classList.remove( 'kb-pop' );
                                void bar.offsetWidth; /* restart the pop animation */
                                bar.classList.add( 'kb-pop' );
                                var orig = add.getAttribute( 'data-kb-add-label' ) || add.textContent;
                                add.setAttribute( 'data-kb-add-label', orig );
                                add.classList.add( 'is-added' );
                                add.textContent = 'Added \u2713';
                                window.setTimeout( function () {
                                        add.classList.remove( 'is-added' );
                                        add.textContent = orig;
                                }, 1400 );
                                return;
                        }

                        if ( event.target.closest( '[data-kb-list-clear]' ) ) {
                                write( [] );
                                paint();
                                return;
                        }

                        var open = event.target.closest( '[data-kb-list-open]' );
                        if ( open ) {
                                event.preventDefault();
                                var items = read();
                                if ( ! items.length ) {
                                        return;
                                }
                                var rfq = document.getElementById( 'kb-rfq' );
                                if ( ! rfq || rfq.hasAttribute( 'hidden' ) ) {
                                        /* Trigger the standard RFQ opener, then prefill. */
                                        var trigger = document.createElement( 'a' );
                                        trigger.className = 'kb-rfq-open';
                                        trigger.href = '#kb-rfq';
                                        trigger.setAttribute( 'data-kb-product', items.join( ', ' ) );
                                        document.body.appendChild( trigger );
                                        trigger.click();
                                        window.setTimeout( function () {
                                                document.body.removeChild( trigger );
                                        }, 50 );
                                }
                                /* Scope to the modal: pages with a second inline
                                   form reuse the same field ids. */
                                var msg = document.querySelector( '#kb-rfq #kb_message' );
                                if ( msg && ! msg.value ) {
                                        msg.value = items.map( function ( n ) {
                                                return '- ' + n;
                                        } ).join( '\n' ) + '\n\n';
                                }
                                var pill = document.querySelector( '.kb-listbar__main' );
                                if ( pill ) {
                                        pill.focus();
                                }
                        }
                } );

                /* Successful submit clears the list and hides the pill. */
                window.addEventListener( 'kb:inquiry-success', function () {
                        write( [] );
                        paint();
                } );

                paint();
        } )();
} )();
