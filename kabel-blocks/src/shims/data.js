/** Shim: @wordpress/data -> window.wp.data. */
const wd = window.wp.data;

export const dispatch = wd.dispatch;
export const select = wd.select;
export const useSelect = wd.useSelect;
export const useDispatch = wd.useDispatch;
export const withSelect = wd.withSelect;
export const createReduxStore = wd.createReduxStore;
export const register = wd.register;
