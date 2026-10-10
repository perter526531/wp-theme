/**
 * Kabel Header Builder — Customizer bundle entry.
 *
 * Mounts the React app on the #kabel-hb-root element rendered by the
 * `kabel_hb_builder` Customizer section control (inc/customizer-hb.php).
 * Runs against window.wp.element (loaded as a script dependency) and
 * window.KabelHBData; every model change is pushed into the
 * `kabel_header_builder` Customizer setting — Publish saves it.
 *
 * Mount timing: WordPress 6.5+ ships custom control markup inside the
 * `_wpCustomizeControlsSettings` preload and the customize-controls script
 * injects the control <li> into the pane only while booting — AFTER
 * DOMContentLoaded. The mount therefore waits for the root to exist instead
 * of assuming it parsed with the document (MutationObserver + immediate
 * check, disconnected after the first hit).
 */
import { el, render, createRoot } from '@wordpress/element';
import { App } from './App.jsx';
import { t } from './zh.js';
import './customizer.css';

function mount() {
        const root = document.getElementById( 'kabel-hb-root' );
        if ( ! root || root.dataset.khbMounted === '1' ) {
                return;
        }
        root.dataset.khbMounted = '1';
        if ( ! window.KabelHBData ) {
                const notice = document.createElement( 'div' );
                notice.className = 'khb-boot-error';
                notice.textContent = t( 'Setup error: KabelHBData is missing, the Header Builder cannot start.' );
                root.appendChild( notice );
                return;
        }
        const app = el( App, { data: window.KabelHBData } );
        if ( createRoot ) {
                createRoot( root ).render( app );
        } else if ( render ) {
                render( app, root );
        }
}

function boot() {
        mount();
        if ( document.getElementById( 'kabel-hb-root' ) ) {
                return;
        }
        const observer = new MutationObserver( () => {
                if ( document.getElementById( 'kabel-hb-root' ) ) {
                        observer.disconnect();
                        mount();
                }
        } );
        observer.observe( document.documentElement, { childList: true, subtree: true } );
        /* Safety net: never watch forever if the control never appears. */
        setTimeout( () => observer.disconnect(), 30000 );
}

if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
} else {
        boot();
}
