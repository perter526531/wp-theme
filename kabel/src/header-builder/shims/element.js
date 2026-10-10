/**
 * Shim: the WP element package -> window.wp.element globals.
 * `el` / `Fr` are the JSX factory + fragment used by the build.
 * The bundle therefore runs against the wp.element that WordPress
 * ships, with zero bundler runtime required on the site.
 */
const wpEl = window.wp.element;

export const el = wpEl.createElement;
export const Fr = wpEl.Fragment;
export const RawHTML = wpEl.RawHTML;
export const useState = wpEl.useState;
export const useEffect = wpEl.useEffect;
export const useMemo = wpEl.useMemo;
export const useCallback = wpEl.useCallback;
export const useRef = wpEl.useRef;
export const render = wpEl.render;
export const createRoot = wpEl.createRoot;
export const cloneElement = wpEl.cloneElement;
export const isValidElement = wpEl.isValidElement;
