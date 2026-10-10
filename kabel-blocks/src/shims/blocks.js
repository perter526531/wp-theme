/** Shim: @wordpress/blocks -> window.wp.blocks. */
const wpBlocks = window.wp.blocks;

export const registerBlockType = wpBlocks.registerBlockType;
export const unregisterBlockType = wpBlocks.unregisterBlockType;
export const getBlockType = wpBlocks.getBlockType;
export const getBlockTypes = wpBlocks.getBlockTypes;
export const createBlock = wpBlocks.createBlock;
export const registerBlockCollection = wpBlocks.registerBlockCollection;
export const unregisterBlockCollection = wpBlocks.unregisterBlockCollection;
/* v11 column layout picker — absent only in stripped-down stubs (edit-smoke). */
export const registerBlockVariation = wpBlocks.registerBlockVariation || ( () => {} );
export const unregisterBlockVariation = wpBlocks.unregisterBlockVariation || ( () => {} );
