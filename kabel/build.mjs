/**
 * Kabel theme — Header Builder bundles (Customizer integration, v1.7).
 *
 * Source lives in src/header-builder/ (JSX authored against window.wp.element
 * through the shim, exactly like the kabel-blocks editor bundle). Two
 * self-contained IIFE outputs with zero imports:
 *
 *   assets/js/hb-customizer.js   (pane app; deps customize-controls/wp-element/wp-i18n)
 *   assets/js/hb-preview.js      (preview bridge; deps customize-preview)
 *   assets/css/hb-customizer.css (pane app stylesheet, relocated from assets/js)
 *
 * esbuild is borrowed from the plugin's node_modules (the theme ships no
 * package.json by design):  cd wordpress-theme/kabel && node build.mjs
 *
 * A dictionary gate at the end fails the build if any t('…') UI string (or
 * registry label) has no Chinese entry in src/header-builder/zh.js.
 */
import esbuild from '../kabel-blocks/node_modules/esbuild/lib/main.js';
import { resolve, dirname, relative } from 'node:path';
import { fileURLToPath } from 'node:url';
import { existsSync, mkdirSync, readdirSync, readFileSync, renameSync, statSync } from 'node:fs';
import { ZH } from './src/header-builder/zh.js';

const root = dirname( fileURLToPath( import.meta.url ) );
const SRC = resolve( root, 'src/header-builder' );

/** The pane app may only ever touch @wordpress/element (the preview bridge
 *  is plain JS with no imports at all). */
const wpAlias = {
        name: 'wp-globals',
        setup( build ) {
                build.onResolve( { filter: /^@wordpress\// }, ( args ) => {
                        if ( args.path !== '@wordpress/element' ) {
                                return {
                                        errors: [
                                                {
                                                        text:
                                                                'Header builder only allows @wordpress/element, got ' +
                                                                args.path,
                                                },
                                        ],
                                };
                        }
                        return { path: resolve( SRC, 'shims/element.js' ) };
                } );
        },
};

await esbuild.build( {
        entryPoints: {
                'hb-customizer': resolve( SRC, 'index.jsx' ),
                'hb-preview': resolve( SRC, 'preview.js' ),
        },
        bundle: true,
        format: 'iife',
        outdir: resolve( root, 'assets/js' ),
        target: [ 'es2019' ],
        jsx: 'transform',
        jsxFactory: 'el',
        jsxFragment: 'Fr',
        loader: { '.js': 'jsx' },
        minify: false,
        sourcemap: false,
        logLevel: 'info',
        plugins: [ wpAlias ],
} );

/*
 * esbuild emits the pane app stylesheet next to its JS entry
 * (assets/js/hb-customizer.css); the theme enqueues it from
 * assets/css/hb-customizer.css — relocate it. rename(2) replaces any previous
 * build output, so repeated runs stay idempotent.
 */
const cssFrom = resolve( root, 'assets/js/hb-customizer.css' );
const cssTo = resolve( root, 'assets/css/hb-customizer.css' );
if ( existsSync( cssFrom ) ) {
        mkdirSync( dirname( cssTo ), { recursive: true } );
        renameSync( cssFrom, cssTo );
}

zhGate();

/**
 * Dictionary gate — every UI string must have a Chinese entry.
 * Collected sources:
 *   t( '…' )            — direct calls (any .js/.jsx under src/header-builder)
 *   label: '…'          — element registry names (model.js)
 *   SOCIAL_ICONS pairs  — icon display names (model.js)
 *   ZONE_LABELS array   — zone names (StructureBar.jsx)
 */
function zhGate() {
        const files = [];
        const walk = ( dir ) => {
                for ( const name of readdirSync( dir ) ) {
                        const p = resolve( dir, name );
                        if ( statSync( p ).isDirectory() ) {
                                if ( name === 'shims' ) {
                                        continue;
                                }
                                walk( p );
                        } else if ( ( name.endsWith( '.js' ) || name.endsWith( '.jsx' ) ) && name !== 'zh.js' ) {
                                files.push( p );
                        }
                }
        };
        walk( SRC );

        const used = new Set();
        const collect = ( src, re ) => {
                let m;
                while ( ( m = re.exec( src ) ) !== null ) {
                        used.add(
                                m[ 1 ].replace( /\\'/g, "'" ).replace( /\\"/g, '"' ).replace( /\\\\/g, '\\' )
                        );
                }
        };

        for ( const file of files ) {
                const src = readFileSync( file, 'utf8' );
                collect( src, /\bt\(\s*'((?:\\.|[^'\\])*)'\s*[,)]/g );
                collect( src, /\blabel:\s*'((?:\\.|[^'\\])*)'/g );
        }

        const modelSrc = readFileSync( resolve( SRC, 'model.js' ), 'utf8' );
        collect( modelSrc, /\[\s*'[a-z0-9]+',\s*'((?:\\.|[^'\\])*)'\s*\]/g );

        const structureSrc = readFileSync( resolve( SRC, 'StructureBar.jsx' ), 'utf8' );
        const zoneMatch = structureSrc.match( /ZONE_LABELS\s*=\s*\[([^\]]*)\]/ );
        if ( zoneMatch ) {
                collect( zoneMatch[ 1 ], /'([^']+)'/g );
        }

        const missing = [];
        for ( const key of used ) {
                if ( ! Object.prototype.hasOwnProperty.call( ZH, key ) ) {
                        missing.push( key );
                }
        }
        if ( missing.length ) {
                console.error(
                        '\n[kabel-hb] zh dictionary is missing ' + missing.length + ' string(s):'
                );
                for ( const line of missing ) {
                        console.error( '  - ' + JSON.stringify( line ) );
                }
                process.exit( 1 );
        }
        console.log(
                '[kabel-hb] zh dictionary covers all ' +
                        used.size +
                        ' UI strings (' +
                        Object.keys( ZH ).length +
                        ' keys).'
        );
}
