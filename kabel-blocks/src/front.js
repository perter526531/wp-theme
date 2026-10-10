/**
 * Kabel Blocks v3 — front-end runtime (no dependencies).
 *
 * 1. Flags <html> with .kb-js so animation styles activate. The Kabel
 *    theme's main.js does the same when present — classList.add is
 *    idempotent, so the two never conflict. Without JavaScript the
 *    entrance-animation CSS never applies (it is scoped to html.kb-js),
 *    so every block stays visible.
 * 2. Reveals .kbl3-anim elements with an IntersectionObserver as they
 *    enter the viewport; prefers-reduced-motion skips the effect.
 * 3. Tab switching for [data-kbl-tabs]: click + arrow keys. Without
 *    JavaScript every panel stays visible (CSS only hides inactive
 *    panels under html.kb-js).
 * 4. Lazy video for [data-kbl-video]: the cover is replaced by the
 *    embedded player on first click. Without JavaScript the cover
 *    simply stays in place.
 * 5. Mobile nav toggle for .kbl3-nav: delegated clicks on
 *    .kbl3-nav__toggle flip is-open + aria-expanded; link clicks and Esc
 *    collapse it again. Without JavaScript the menu stays fully expanded
 *    (the collapse CSS is scoped to html.kb-js).
 */
( function () {
        var doc = document.documentElement;
        if ( doc && doc.classList ) {
                doc.classList.add( 'kb-js' );
        }

        function revealAll( nodes ) {
                for ( var i = 0; i < nodes.length; i++ ) {
                        nodes[ i ].classList.add( 'is-in' );
                }
        }

        function closestEl( node, selector ) {
                if ( node && node.closest ) {
                        try {
                                return node.closest( selector );
                        } catch ( e ) {
                                return null;
                        }
                }
                return null;
        }

        function init() {
                var nodes = document.querySelectorAll( '.kbl3-anim' );

                if ( nodes.length ) {
                        var reduce = window.matchMedia
                                && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

                        if ( reduce || ! ( 'IntersectionObserver' in window ) ) {
                                revealAll( nodes );
                        } else {
                                var io = new IntersectionObserver(
                                        function ( entries ) {
                                                for ( var i = 0; i < entries.length; i++ ) {
                                                        if ( entries[ i ].isIntersecting ) {
                                                                var el = entries[ i ].target;
                                                                el.classList.add( 'is-in' );
                                                                io.unobserve( el );
                                                        }
                                                }
                                        },
                                        { threshold: 0.12 }
                                );

                                for ( var i = 0; i < nodes.length; i++ ) {
                                        io.observe( nodes[ i ] );
                                }
                        }
                }
        }

        /* ---------------- Tabs ---------------- */

        function activateTab( root, tab ) {
                var tabs = root.querySelectorAll( '.kbl3-tabs__tab' );
                var panels = root.querySelectorAll( '.kbl3-tabs__panel' );
                var idx = Array.prototype.indexOf.call( tabs, tab );
                var i;
                for ( i = 0; i < tabs.length; i++ ) {
                        tabs[ i ].classList.toggle( 'is-active', i === idx );
                        tabs[ i ].setAttribute( 'aria-selected', i === idx ? 'true' : 'false' );
                }
                for ( i = 0; i < panels.length; i++ ) {
                        panels[ i ].classList.toggle( 'is-active', i === idx );
                }
        }

        function initTabs() {
                document.addEventListener( 'click', function ( e ) {
                        var tab = closestEl( e.target, '.kbl3-tabs__tab' );
                        if ( ! tab ) {
                                return;
                        }
                        var root = closestEl( tab, '[data-kbl-tabs]' );
                        if ( root ) {
                                activateTab( root, tab );
                        }
                } );

                document.addEventListener( 'keydown', function ( e ) {
                        if ( 'ArrowLeft' !== e.key && 'ArrowRight' !== e.key ) {
                                return;
                        }
                        var tab = closestEl( e.target, '.kbl3-tabs__tab' );
                        if ( ! tab ) {
                                return;
                        }
                        var root = closestEl( tab, '[data-kbl-tabs]' );
                        if ( ! root ) {
                                return;
                        }
                        var tabs = Array.prototype.slice.call( root.querySelectorAll( '.kbl3-tabs__tab' ) );
                        var i = tabs.indexOf( tab );
                        var next = 'ArrowRight' === e.key ? i + 1 : i - 1;
                        if ( next < 0 ) {
                                next = tabs.length - 1;
                        }
                        if ( next >= tabs.length ) {
                                next = 0;
                        }
                        if ( tabs[ next ] ) {
                                tabs[ next ].focus();
                                activateTab( root, tabs[ next ] );
                        }
                } );
        }

        /* ---------------- Video ---------------- */

        function youtubeId( url ) {
                var m = url.match( /(?:youtube\.com\/(?:watch\?[^#]*v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{6,})/ );
                return m ? m[ 1 ] : '';
        }

        function vimeoId( url ) {
                var m = url.match( /vimeo\.com\/(?:video\/)?(\d+)/ );
                return m ? m[ 1 ] : '';
        }

        function videoEmbed( data ) {
                var src = data && data.src ? String( data.src ) : '';
                var provider = data && data.provider ? String( data.provider ) : 'file';
                var id;
                if ( ! src ) {
                        return '';
                }
                if ( 'youtube' === provider ) {
                        id = youtubeId( src );
                        if ( id ) {
                                return '<iframe src="https://www.youtube-nocookie.com/embed/' + id
                                        + '?autoplay=1&rel=0" title="Video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                        }
                        return '';
                }
                if ( 'vimeo' === provider ) {
                        id = vimeoId( src );
                        if ( id ) {
                                return '<iframe src="https://player.vimeo.com/video/' + id
                                        + '?autoplay=1" title="Video" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
                        }
                        return '';
                }
                return '<video controls autoplay playsinline src="' + src.replace( /"/g, '%22' ) + '"></video>';
        }

        function initVideos() {
                document.addEventListener( 'click', function ( e ) {
                        var cover = closestEl( e.target, '.kbl3-video__cover' );
                        if ( ! cover ) {
                                return;
                        }
                        var wrap = closestEl( cover, '[data-kbl-video]' );
                        if ( ! wrap ) {
                                return;
                        }
                        var data = null;
                        try {
                                data = JSON.parse( wrap.getAttribute( 'data-kbl-video' ) );
                        } catch ( err ) {
                                data = null;
                        }
                        var embed = videoEmbed( data );
                        if ( embed ) {
                                wrap.innerHTML = embed;
                        }
                } );
        }

        /* ---------------- Nav ---------------- */

        function closeNav( root ) {
                root.classList.remove( 'is-open' );
                var toggle = root.querySelector( '.kbl3-nav__toggle' );
                if ( toggle ) {
                        toggle.setAttribute( 'aria-expanded', 'false' );
                }
        }

        function initNavs() {
                document.addEventListener( 'click', function ( e ) {
                        var toggle = closestEl( e.target, '.kbl3-nav__toggle' );
                        if ( toggle ) {
                                var root = closestEl( toggle, '.kbl3-nav' );
                                if ( root ) {
                                        var open = root.classList.toggle( 'is-open' );
                                        toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
                                }
                                return;
                        }
                        var link = closestEl( e.target, '.kbl3-nav__link' );
                        if ( link ) {
                                var nav = closestEl( link, '.kbl3-nav' );
                                if ( nav ) {
                                        closeNav( nav );
                                }
                        }
                } );

                document.addEventListener( 'keydown', function ( e ) {
                        if ( 'Escape' !== e.key ) {
                                return;
                        }
                        var open = document.querySelectorAll( '.kbl3-nav.is-open' );
                        for ( var i = 0; i < open.length; i++ ) {
                                closeNav( open[ i ] );
                        }
                } );
        }

        /* Language switcher: dropdown open/close + keep the .is-current
         * marker in sync with GTranslate's .gt-current-lang (it re-marks on
         * every switch), and move it optimistically on click for immediate
         * feedback — the dropdown's toggle label follows the same source. */
        function initLangs() {
                function applyCurrent( list, link ) {
                        var links = list.querySelectorAll( 'a.kbl3-lang__link' );
                        for ( var j = 0; j < links.length; j++ ) {
                                links[ j ].classList.toggle( 'is-current', links[ j ] === link );
                        }
                        /* Dropdown variant: mirror the picked label onto the
                         * toggle button (server-side renders the flagged item,
                         * this keeps it live after GTranslate switches). */
                        var current = list.querySelector( '.kbl3-lang__current' );
                        if ( current && link ) {
                                current.textContent = link.textContent;
                        }
                }

                function sync() {
                        var lists = document.querySelectorAll( '.kbl3-lang' );
                        for ( var i = 0; i < lists.length; i++ ) {
                                var live = lists[ i ].querySelector( 'a.gt-current-lang' );
                                if ( ! live ) {
                                        continue;
                                }
                                applyCurrent( lists[ i ], live );
                        }
                }

                function closeLang( root ) {
                        root.classList.remove( 'is-open' );
                        var toggle = root.querySelector( '.kbl3-lang__toggle' );
                        if ( toggle ) {
                                toggle.setAttribute( 'aria-expanded', 'false' );
                        }
                }

                document.addEventListener( 'click', function ( e ) {
                        /* Dropdown toggle: open/close the panel. */
                        var toggle = closestEl( e.target, '.kbl3-lang__toggle' );
                        if ( toggle ) {
                                var root = closestEl( toggle, '.kbl3-lang--dropdown' );
                                if ( root ) {
                                        var open = root.classList.toggle( 'is-open' );
                                        toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
                                }
                                return;
                        }

                        /* Click outside an open panel closes it. */
                        var openRoots = document.querySelectorAll( '.kbl3-lang--dropdown.is-open' );
                        for ( var i = 0; i < openRoots.length; i++ ) {
                                if ( ! closestEl( e.target, '.kbl3-lang' ) || ! openRoots[ i ].contains( e.target ) ) {
                                        closeLang( openRoots[ i ] );
                                }
                        }

                        var link = closestEl( e.target, '.kbl3-lang a[data-gt-lang]' );
                        if ( ! link ) {
                                return;
                        }
                        var list = closestEl( link, '.kbl3-lang' );
                        if ( ! list ) {
                                return;
                        }
                        applyCurrent( list, link );

                        /* Picking a language closes the panel. */
                        var dd = closestEl( link, '.kbl3-lang--dropdown' );
                        if ( dd ) {
                                closeLang( dd );
                        }
                } );

                document.addEventListener( 'keydown', function ( e ) {
                        if ( 'Escape' !== e.key ) {
                                return;
                        }
                        var open = document.querySelectorAll( '.kbl3-lang--dropdown.is-open' );
                        for ( var i = 0; i < open.length; i++ ) {
                                closeLang( open[ i ] );
                        }
                } );

                /* GTranslate marks the live language once its widget boots;
                 * observe so the marker follows even without a click. Debounced
                 * — a translation pass rewrites hundreds of nodes at once. */
                if ( window.MutationObserver ) {
                        var t = 0;
                        var mo = new MutationObserver( function () {
                                clearTimeout( t );
                                t = setTimeout( sync, 250 );
                        } );
                        mo.observe( document.documentElement, {
                                childList: true,
                                subtree: true,
                                attributes: true,
                                attributeFilter: [ 'class' ],
                        } );
                }
        }

        function boot() {
                init();
                initTabs();
                initVideos();
                initNavs();
                initLangs();
        }

        if ( document.readyState === 'loading' ) {
                document.addEventListener( 'DOMContentLoaded', boot );
        } else {
                boot();
        }
} )();
