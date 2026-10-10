/**
 * Header Builder — live preview iframe.
 *
 * Every model change is POSTed as a draft (user meta) after a 700ms debounce;
 * once the server confirms, the iframe is reloaded in place with a fresh
 * cache-busting stamp so the front end renders the draft for real.
 */
import { el, useState, useEffect, useRef } from '@wordpress/element';
import { t } from './zh.js';
import { saveDraft } from './api.js';

function buildSrc( home, previewNonce ) {
	const base = home || '/';
	const join = base.indexOf( '?' ) > -1 ? '&' : '?';
	return (
		base +
		join +
		'kabel-hb-preview=' +
		encodeURIComponent( previewNonce || '' ) +
		'&t=' +
		Date.now()
	);
}

export function PreviewFrame( { model, home, previewNonce, device } ) {
	const [ src, setSrc ] = useState( null );
	const [ error, setError ] = useState( '' );
	const [ flash, setFlash ] = useState( 0 );
	const frameRef = useRef( null );
	const jobRef = useRef( 0 );
	const srcRef = useRef( null );
	const firstRef = useRef( true );

	const navigate = ( url ) => {
		if ( ! srcRef.current ) {
			srcRef.current = url;
			setSrc( url );
			return;
		}
		const frame = frameRef.current;
		if ( frame && frame.contentWindow ) {
			try {
				frame.contentWindow.location.replace( url );
				return;
			} catch ( err ) {
				/* cross-origin guard — fall through to a src swap */
			}
		}
		srcRef.current = url;
		setSrc( url );
	};

	useEffect( () => {
		const run = () => {
			const job = ++jobRef.current;
			saveDraft( model )
				.then( () => {
					if ( job !== jobRef.current ) {
						return;
					}
					setError( '' );
					navigate( buildSrc( home, previewNonce ) );
					setFlash( Date.now() );
				} )
				.catch( () => {
					if ( job !== jobRef.current ) {
						return;
					}
					setError( t( 'Preview update failed.' ) );
					if ( ! srcRef.current ) {
						navigate( buildSrc( home, previewNonce ) );
					}
				} );
		};
		const delay = firstRef.current ? 60 : 700;
		firstRef.current = false;
		const timer = setTimeout( run, delay );
		return () => clearTimeout( timer );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ model ] );

	useEffect( () => {
		if ( ! flash ) {
			return;
		}
		const timer = setTimeout( () => setFlash( 0 ), 1600 );
		return () => clearTimeout( timer );
	}, [ flash ] );

	return el(
		'div',
		{
			className: 'khb-preview' + ( device === 'mobile' ? ' is-mobile' : '' ),
		},
		error
			? el( 'div', { className: 'khb-pverror', role: 'alert' }, error )
			: null,
		flash
			? el( 'div', { className: 'khb-flash', 'aria-live': 'polite' }, t( 'Preview updated' ) )
			: null,
		src
			? el(
				'div',
				{ className: 'khb-preview__holder' },
				el( 'iframe', {
					ref: frameRef,
					className: 'khb-preview__frame',
					src,
					title: t( 'Live preview' ),
				} )
			)
			: el(
				'div',
				{ className: 'khb-preview__loading' },
				el( 'span', { className: 'khb-spin is-dark', 'aria-hidden': true } ),
				el( 'span', null, t( 'Loading preview…' ) )
			)
	);
}
