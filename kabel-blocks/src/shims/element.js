/**
 * Shim: @wordpress/element -> window.wp.element.
 * `el` / `Fr` are the JSX factory + fragment used by the build.
 */
const wpEl = window.wp.element;

export const el = wpEl.createElement;
export const Fr = wpEl.Fragment;
export const Component = wpEl.Component;
export const RawHTML = wpEl.RawHTML;
export const useState = wpEl.useState;
export const useEffect = wpEl.useEffect;
export const useMemo = wpEl.useMemo;
export const useCallback = wpEl.useCallback;
export const useRef = wpEl.useRef;
export const render = wpEl.render;
export const cloneElement = wpEl.cloneElement;
export const isValidElement = wpEl.isValidElement;
