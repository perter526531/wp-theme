/**
 * Header Builder — REST client for kabel/v1/header-builder.
 * Endpoint URL + nonce come from window.KabelHBData (localized by PHP).
 */

const DATA = ( typeof window !== 'undefined' && window.KabelHBData ) || {};

async function request( method, body ) {
	if ( ! DATA.rest ) {
		throw new Error( 'REST endpoint is not configured.' );
	}
	const res = await fetch( DATA.rest, {
		method,
		credentials: 'same-origin',
		headers: {
			'Content-Type': 'application/json',
			'X-WP-Nonce': DATA.restNonce || '',
		},
		body: body ? JSON.stringify( body ) : undefined,
	} );
	if ( ! res.ok ) {
		throw new Error( 'HTTP ' + res.status );
	}
	const data = await res.json().catch( () => null );
	if ( ! data || data.ok !== true ) {
		throw new Error( 'Unexpected response.' );
	}
	return data;
}

/** POST {model, draft:false} -> {ok, model, liveSource} */
export function saveModel( model ) {
	return request( 'POST', { model, draft: false } );
}

/** POST {model, draft:true} -> {ok, model, liveSource} (user-meta preview write) */
export function saveDraft( model ) {
	return request( 'POST', { model, draft: true } );
}

/** DELETE -> {ok, liveSource} (clear option + preview meta) */
export function clearModel() {
	return request( 'DELETE' );
}
