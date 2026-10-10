/**
 * Header Builder — undo/redo history hook.
 *
 * Keeps the whole model in one state object; every `set` pushes the previous
 * model onto the past stack (max 60 snapshots). Slider/text edits pass a
 * `group` key so rapid changes to the same field coalesce into one undo step.
 */
import { useState, useCallback } from '@wordpress/element';

const MAX_HISTORY = 60;
const COALESCE_MS = 900;

export function useHistory( initial ) {
	const [ hist, setHist ] = useState( {
		current: initial,
		past: [],
		future: [],
		last: null,
	} );

	/**
	 * set( updaterOrValue, group )
	 * `group` — optional coalesce key ('set:e-abc:fontSize'); consecutive sets
	 * with the same key inside COALESCE_MS collapse into one history entry.
	 */
	const set = useCallback( ( updater, group ) => {
		setHist( ( prev ) => {
			const next = typeof updater === 'function' ? updater( prev.current ) : updater;
			if ( next === prev.current ) {
				return prev;
			}
			const now = Date.now();
			const coalesce =
				!! group &&
				!! prev.last &&
				prev.last.group === group &&
				now - prev.last.at < COALESCE_MS;
			return {
				current: next,
				past: coalesce
					? prev.past
					: prev.past.concat( [ prev.current ] ).slice( -MAX_HISTORY ),
				future: [],
				last: { group: group || null, at: now },
			};
		} );
	}, [] );

	const undo = useCallback( () => {
		setHist( ( prev ) => {
			if ( ! prev.past.length ) {
				return prev;
			}
			return {
				current: prev.past[ prev.past.length - 1 ],
				past: prev.past.slice( 0, -1 ),
				future: [ prev.current ].concat( prev.future ).slice( 0, MAX_HISTORY ),
				last: null,
			};
		} );
	}, [] );

	const redo = useCallback( () => {
		setHist( ( prev ) => {
			if ( ! prev.future.length ) {
				return prev;
			}
			return {
				current: prev.future[ 0 ],
				past: prev.past.concat( [ prev.current ] ).slice( -MAX_HISTORY ),
				future: prev.future.slice( 1 ),
				last: null,
			};
		} );
	}, [] );

	/** Replace the model without touching history (used after save). */
	const replace = useCallback( ( next ) => {
		setHist( { current: next, past: [], future: [], last: null } );
	}, [] );

	return {
		model: hist.current,
		set,
		undo,
		redo,
		replace,
		canUndo: hist.past.length > 0,
		canRedo: hist.future.length > 0,
	};
}
