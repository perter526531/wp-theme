/** Shim: @wordpress/block-editor -> window.wp.blockEditor. */
const be = window.wp.blockEditor;

export const RichText = be.RichText;
export const MediaUpload = be.MediaUpload;
export const MediaUploadCheck = be.MediaUploadCheck;
export const MediaPlaceholder = be.MediaPlaceholder;
export const InnerBlocks = be.InnerBlocks;
export const useBlockProps = be.useBlockProps;
export const useInnerBlocksProps = be.useInnerBlocksProps;
export const InspectorControls = be.InspectorControls;
export const BlockControls = be.BlockControls;
export const AlignmentToolbar = be.AlignmentToolbar;
export const BlockVerticalAlignmentToolbar = be.BlockVerticalAlignmentToolbar;
export const PanelColorSettings = be.PanelColorSettings;
export const ContrastChecker = be.ContrastChecker;
export const useSetting = be.useSetting;
export const blockEditorStore = be.store;
export const withColors = be.withColors;
export const URLInput = be.URLInput;
