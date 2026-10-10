/** Shim: @wordpress/i18n -> window.wp.i18n (+ zh-CN editor UI for kabel-blocks). */
import { ZH } from './zh.js';

const wi18n = ( window.wp && window.wp.i18n ) || {
	__: ( s ) => s,
	_x: ( s ) => s,
	_n: ( s ) => s,
	sprintf: ( f, ...args ) => f.replace( /%[sd]/g, () => String( args.shift() ) ),
};
const raw__ = wi18n.__;

/**
 * Editor strings for the kabel-blocks domain are swapped for Chinese via
 * the dictionary in zh.js. save() never calls __(), so published markup is
 * unaffected — this localizes the editing experience only.
 */
export const __ = ( text, domain ) => {
	if ( domain === 'kabel-blocks' && Object.prototype.hasOwnProperty.call( ZH, text ) ) {
		return ZH[ text ];
	}
	return raw__( text, domain );
};
export const _x = wi18n._x;
export const _n = wi18n._n;
export const sprintf = wi18n.sprintf;
