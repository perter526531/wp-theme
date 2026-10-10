/** Shim: @wordpress/hooks -> window.wp.hooks. */
const wh = window.wp.hooks;

export const addFilter = wh.addFilter;
export const removeFilter = wh.removeFilter;
export const applyFilters = wh.applyFilters;
