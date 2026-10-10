/**
 * Kabel Blocks — editor bundle build.
 *
 * Source is authored in JSX under src/. The bundle must run in the WordPress
 * editor WITHOUT any bundler runtime on the site: every @wordpress/* import
 * is aliased to a tiny shim that reads the wp.* globals, so the output is a
 * self-contained IIFE with zero imports.
 *
 * Usage: bun run build   (or: bunx esbuild via node build.mjs)
 */
import esbuild from 'esbuild';
import { resolve, dirname, relative } from 'node:path';
import { fileURLToPath } from 'node:url';
import { existsSync, readdirSync, readFileSync, statSync, writeFileSync } from 'node:fs';
import { ZH, ZH_SAVE_EXEMPT } from './src/shims/zh.js';

const root = dirname( fileURLToPath( import.meta.url ) );
const watch = process.argv.includes( '--watch' );

/** @wordpress/<pkg> -> src/shims/<pkg>.js (dashes preserved). */
const wpAlias = {
        name: 'wp-globals',
        setup( build ) {
                build.onResolve( { filter: /^@wordpress\// }, ( args ) => {
                        const pkg = args.path.replace( /^@wordpress\//, '' );
                        const shim = resolve( root, 'src/shims', `${ pkg }.js` );
                        if ( ! existsSync( shim ) ) {
                                return {
                                        errors: [ {
                                                text: `No shim for ${ args.path } — create src/shims/${ pkg }.js`,
                                        } ],
                                };
                        }
                        return { path: shim };
                } );
        },
};

const ctx = await esbuild.context( {
        entryPoints: {
                blocks: resolve( root, 'src/index.js' ),
                front: resolve( root, 'src/front.js' ),
        },
        bundle: true,
        format: 'iife',
        outdir: resolve( root, 'assets/js' ),
        target: [ 'es2019' ],
        jsx: 'transform',
        jsxFactory: 'el',
        jsxFragment: 'Fr',
        loader: { '.js': 'jsx' },
        /* Production bundles are minified. The zh-gate + save()-serialization
         * contract are unaffected: esbuild never rewrites string literals, so
         * the editor's save() output stays byte-identical to the emitter. */
        minify: true,
        sourcemap: false,
        logLevel: 'info',
        plugins: [ wpAlias ],
} );

/**
 * Dictionary gate: every __( '…', 'kabel-blocks' ) string in src/ must have
 * a Chinese entry in src/shims/zh.js, otherwise the build fails — a new
 * control can never ship with an English label by accident.
 */
function zhGate() {
        const files = [];
        const walk = ( dir ) => {
                for ( const name of readdirSync( dir ) ) {
                        const p = resolve( dir, name );
                        if ( statSync( p ).isDirectory() ) {
                                if ( name === 'shims' ) { continue; }
                                walk( p );
                        } else if ( name.endsWith( '.js' ) ) {
                                files.push( p );
                        }
                }
        };
        walk( resolve( root, 'src' ) );

        const re = /__\(\s*(['"])((?:\\.|(?!\1).)*)\1\s*,\s*['"]kabel-blocks['"]\s*\)/g;
        const missing = [];
        for ( const file of files ) {
                const src = readFileSync( file, 'utf8' );
                let m;
                while ( ( m = re.exec( src ) ) !== null ) {
                        const text = m[ 2 ]
                                .replace( /\\'/g, "'" )
                                .replace( /\\"/g, '"' )
                                .replace( /\\\\/g, '\\' );
                        if ( ! Object.prototype.hasOwnProperty.call( ZH, text ) && ! ZH_SAVE_EXEMPT.includes( text ) ) {
                                missing.push( `${ relative( root, file ) }: ${ JSON.stringify( text ) }` );
                        }
                }
        }
        if ( missing.length ) {
                console.error( `\n[kabel-blocks] zh dictionary is missing ${ missing.length } string(s):` );
                for ( const line of missing ) {
                        console.error( '  - ' + line );
                }
                process.exit( 1 );
        }
        console.log( `[kabel-blocks] zh dictionary covers all strings in src/.` );
}

if ( watch ) {
        await ctx.watch();
        console.log( '[kabel-blocks] watching src/…' );
} else {
        await ctx.rebuild();
        await ctx.dispose();
        zhGate();
        await minifyHandAuthored();
}

/**
 * Minify the hand-authored assets (the editor bundle above is built from
 * src/, these files are written directly and shipped as-is).
 *
 * For each input a `<name>.min.<ext>` sibling is produced; inc/assets.php
 * prefers the .min copy when it exists (same version query string), so a
 * missing .min file simply falls back to the readable original.
 *
 *   assets/css/kabel-blocks.css   → kabel-blocks.min.css
 *   assets/css/kabel-editor.css   → kabel-editor.min.css
 *   assets/css/kabel-legacy.css   → kabel-legacy.min.css
 *   assets/js/kabel-form.js       → kabel-form.min.js
 */
async function minifyHandAuthored() {
        const files = [
                'assets/css/kabel-blocks.css',
                'assets/css/kabel-editor.css',
                'assets/css/kabel-legacy.css',
                'assets/js/kabel-form.js',
        ];
        let saved = 0;
        for ( const rel of files ) {
                const src = resolve( root, rel );
                if ( ! existsSync( src ) ) {
                        continue;
                }
                const before = statSync( src ).size;
                const isCss = rel.endsWith( '.css' );
                const out = await esbuild.transform( readFileSync( src, 'utf8' ), {
                        minify: true,
                        legalComments: 'none',
                        loader: isCss ? 'css' : 'js',
                } );
                const dest = resolve( root, rel.replace( /\.(css|js)$/, '.min.$1' ) );
                writeFileSync( dest, out.code );
                const after = statSync( dest ).size;
                saved += Math.max( 0, before - after );
                console.log( `[kabel-blocks] ${ rel } → ${ rel.replace( /\.(css|js)$/, '.min.$1' ) } (${ before } → ${ after } bytes)` );
        }
        if ( saved > 0 ) {
                console.log( `[kabel-blocks] minification saved ~${ Math.round( saved / 1024 ) } KB` );
        }
}
