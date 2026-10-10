/**
 * Shim: @wordpress/plugins -> window.wp.plugins.
 *
 * Unlike the other shims this one reads the global lazily at CALL time: the
 * kabel-blocks-v3 script handle does not hard-depend on wp-plugins (see
 * inc/assets.php), so an absent package must degrade to a silent no-op
 * instead of crashing — the edit-smoke stub ships no wp.plugins at all and
 * registerPlugin() there must simply do nothing.
 */
export const registerPlugin = ( name, settings ) => {
        const api = typeof window !== 'undefined' && window.wp && window.wp.plugins;
        return api && api.registerPlugin ? api.registerPlugin( name, settings ) : undefined;
};
