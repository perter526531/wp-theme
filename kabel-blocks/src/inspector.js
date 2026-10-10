/**
 * Kabel Blocks v3 — the shared inspector kit.
 *
 * Every block gets a Stackable-style 3-tab sidebar (Layout / Style /
 * Advanced) assembled from these controls, so the whole suite feels like one
 * product instead of 17 ad-hoc panels.
 */
import { el, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import {
        InspectorControls,
        PanelColorSettings,
        ContrastChecker,
        MediaUpload,
        MediaUploadCheck,
} from '@wordpress/block-editor';
import {
        PanelBody,
        RangeControl,
        SelectControl,
        TextControl,
        ToggleControl,
        TabPanel,
        Button,
        ButtonGroup,
        BaseControl,
        Spinner,
} from '@wordpress/components';
import { PALETTE, SHADOW_OPTIONS, ICON_NAMES, iconSvg, cx } from './util.js';

/* ------------------------------------------------------------------ */
/* Tabs shell                                                          */
/* ------------------------------------------------------------------ */

/**
 * <KblInspector layout={…} style={…} advanced={…} />
 * Renders InspectorControls + TabPanel. Pass null to hide a tab.
 */
export function KblInspector( { layout = null, style = null, advanced = null } ) {
        const tabs = [
                { name: 'layout', title: __( 'Layout', 'kabel-blocks' ) },
        ];
        if ( style ) {
                tabs.push( { name: 'style', title: __( 'Style', 'kabel-blocks' ) } );
        }
        tabs.push( { name: 'advanced', title: __( 'Advanced', 'kabel-blocks' ) } );

        return el(
                InspectorControls,
                null,
                el(
                        TabPanel,
                        {
                                className: 'kbl3-inspector-tabs',
                                activeClass: 'is-active',
                                tabs,
                        },
                        ( tab ) => {
                                if ( tab.name === 'layout' ) {
                                        return el( 'div', { className: 'kbl3-tab' }, layout );
                                }
                                if ( tab.name === 'style' ) {
                                        return el( 'div', { className: 'kbl3-tab' }, style );
                                }
                                return el( 'div', { className: 'kbl3-tab' }, advanced || null );
                        }
                )
        );
}

/* ------------------------------------------------------------------ */
/* Small controls                                                      */
/* ------------------------------------------------------------------ */

export function Row( { label, children } ) {
        return el( BaseControl, { label, className: 'kbl3-row' }, children );
}

/** Three-state horizontal alignment buttons (left / center / right). */
export function AlignButtons( { label = __( 'Alignment', 'kabel-blocks' ), value, onChange } ) {
        const opts = [
                { v: 'left', dash: 'dashicons-editor-alignleft' },
                { v: 'center', dash: 'dashicons-editor-aligncenter' },
                { v: 'right', dash: 'dashicons-editor-alignright' },
        ];
        return el(
                Row,
                { label },
                el(
                        ButtonGroup,
                        null,
                        opts.map( ( o ) =>
                                el(
                                        Button,
                                        {
                                                key: o.v,
                                                size: 'small',
                                                variant: value === o.v ? 'primary' : 'secondary',
                                                onClick: () => onChange( value === o.v ? undefined : o.v ),
                                                'aria-label': o.v,
                                        },
                                        el( 'span', { className: `dashicons ${ o.dash }` } )
                                )
                        )
                )
        );
}

/** Segmented buttons for a fixed option set. */
export function SegButtons( { label, value, onChange, options } ) {
        return el(
                Row,
                { label },
                el(
                        ButtonGroup,
                        null,
                        options.map( ( o ) =>
                                el(
                                        Button,
                                        {
                                                key: o.value,
                                                size: 'small',
                                                variant: value === o.value ? 'primary' : 'secondary',
                                                onClick: () => onChange( o.value ),
                                        },
                                        o.label
                                )
                        )
                )
        );
}

export function RangeRow( props ) {
        const { label, value, onChange, min = 0, max = 100, step = 1, unit = 'px', initial, help } = props;
        return el( RangeControl, {
                label,
                value: value ?? initial ?? min,
                onChange: ( v ) => onChange( v === null || v === undefined ? null : v ),
                min,
                max,
                step,
                unit,
                allowReset: true,
                help,
        } );
}

export function SelectRow( props ) {
        return el( SelectControl, props );
}

export function ToggleRow( props ) {
        return el( ToggleControl, props );
}

export function TextRow( props ) {
        return el( TextControl, props );
}

/* ------------------------------------------------------------------ */
/* Color panel                                                         */
/* ------------------------------------------------------------------ */

/**
 * The global-color slots (window.KBL3_ENV.globalColors — injected by
 * assets.php). Each carries `var` = "var(--kbl-gc-N)" so picking a swatch
 * stores the token, and every block that uses it re-skins when the slot
 * changes (Stackable Global Colors semantics).
 */
function kblGlobalColors() {
        const env = ( typeof window !== 'undefined' && window.KBL3_ENV ) || {};
        return Array.isArray( env.globalColors ) ? env.globalColors : [];
}

/**
 * The swatch list for every color dropdown: global slots first, then the
 * site palette. Falls back to the default editor palette when the store
 * is not reachable.
 */
function kblSwatchList() {
        const globals = kblGlobalColors().map( ( g ) => ( {
                name: ( g.name || '' ) + ' · ' + __( 'Global', 'kabel-blocks' ),
                color: g.var,
        } ) );
        let base = [];
        try {
                base = select( 'core/block-editor' ).getSettings().colors || [];
        } catch ( e ) {}
        return globals.concat( base );
}

/**
 * <ColorsPanel colors={ [ { label, value, onChange, panelLabel } ] } />
 * PanelColorSettings + ContrastChecker when a text/bg pair exists.
 */
export function ColorsPanel( { panelLabel = __( 'Colors', 'kabel-blocks' ), colors } ) {
        const textDef = colors.find( ( c ) => c.isText );
        const bgDef = colors.find( ( c ) => c.isBackground );
        const showChecker = textDef && bgDef && textDef.value && bgDef.value;
        const gcUrl = ( typeof window !== 'undefined' && window.KBL3_ENV && window.KBL3_ENV.gcSettingsUrl ) || '';
        const swatches = kblSwatchList();
        return el( PanelColorSettings, {
                title: panelLabel,
                colorSettings: colors.map( ( c ) => ( {
                        label: c.label,
                        value: c.value || '',
                        onChange: c.onChange,
                        enableAlpha: true,
                        colors: swatches,
                } ) ),
                children: [
                        showChecker ?
                                el( ContrastChecker, {
                                        textColor: textDef.value,
                                        backgroundColor: bgDef.value,
                                        fontSize: 16,
                                }) :
                                null,
                        gcUrl ?
                                el(
                                        'a',
                                        { key: 'kbl-gc', href: gcUrl, target: '_blank', rel: 'noopener', className: 'kbl3-gc-link' },
                                        __( 'Manage global colors', 'kabel-blocks' )
                                ) :
                                null,
                ],
        } );
}

/* ------------------------------------------------------------------ */
/* Border / radius / shadow panel                                      */
/* ------------------------------------------------------------------ */

export function BorderPanel( {
        radius, onRadius,
        shadow, onShadow,
        borderWidth, onBorderWidth,
        borderColor, onBorderColor,
        borderStyle, onBorderStyle,
} ) {
        const hasBorder = borderWidth !== null && borderWidth !== undefined && borderWidth > 0;
        return el(
                PanelBody,
                { title: __( 'Border & shadow', 'kabel-blocks' ), initialOpen: false },
                el( RangeRow, {
                        label: __( 'Corner radius', 'kabel-blocks' ),
                        value: radius,
                        onChange: onRadius,
                        min: 0,
                        max: 40,
                } ),
                onShadow ?
                        el( SelectRow, {
                                label: __( 'Box shadow', 'kabel-blocks' ),
                                value: shadow || '',
                                options: SHADOW_OPTIONS,
                                onChange: ( v ) => onShadow( v || undefined ),
                        } ) :
                        null,
                onBorderWidth ?
                        el( RangeRow, {
                                label: __( 'Border width', 'kabel-blocks' ),
                                value: borderWidth,
                                onChange: onBorderWidth,
                                min: 0,
                                max: 6,
                        } ) :
                        null,
                onBorderStyle ?
                        el( SelectRow, {
                                label: __( 'Border style', 'kabel-blocks' ),
                                value: borderStyle || 'solid',
                                options: [
                                        { value: 'solid', label: __( 'Solid', 'kabel-blocks' ) },
                                        { value: 'dashed', label: __( 'Dashed', 'kabel-blocks' ) },
                                        { value: 'dotted', label: __( 'Dotted', 'kabel-blocks' ) },
                                ],
                                onChange: ( v ) => onBorderStyle( v ),
                        } ) :
                        null,
                onBorderColor && hasBorder ?
                        el( ColorsPanel, {
                                panelLabel: __( 'Border color', 'kabel-blocks' ),
                                colors: [ { label: __( 'Border color', 'kabel-blocks' ), value: borderColor, onChange: onBorderColor } ],
                        } ) :
                        null
        );
}

/* ------------------------------------------------------------------ */
/* Icon picker                                                         */
/* ------------------------------------------------------------------ */

export function IconPicker( { label = __( 'Icon', 'kabel-blocks' ), value, onChange } ) {
        return el(
                Row,
                { label },
                el(
                        'div',
                        { className: 'kbl3-icon-picker' },
                        ICON_NAMES.map( ( name ) =>
                                el(
                                        Button,
                                        {
                                                key: name,
                                                size: 'small',
                                                variant: value === name ? 'primary' : 'secondary',
                                                className: 'kbl3-icon-picker__btn',
                                                onClick: () => onChange( name ),
                                                label: name,
                                        },
                                        iconSvg( name )
                                )
                        )
                )
        );
}

/* ------------------------------------------------------------------ */
/* Background image control                                            */
/* ------------------------------------------------------------------ */

export function BgImageControl( { url, id, onSelect, onRemove, overlay, onOverlay } ) {
        return el(
                PanelBody,
                { title: __( 'Background image', 'kabel-blocks' ), initialOpen: false },
                el(
                        MediaUploadCheck,
                        null,
                        el( MediaUpload, {
                                onSelect: ( media ) => onSelect( { url: media.url, id: media.id } ),
                                allowedTypes: [ 'image' ],
                                value: id,
                                render: ( { open } ) =>
                                        el(
                                                'div',
                                                { className: 'kbl3-bg-ctl' },
                                                url ?
                                                        el( 'img', { src: url, className: 'kbl3-bg-ctl__thumb', alt: '' } ) :
                                                        null,
                                                el(
                                                        'div',
                                                        { className: 'kbl3-bg-ctl__btns' },
                                                        el(
                                                                Button,
                                                                { variant: 'secondary', size: 'small', onClick: open, isPrimary: ! url },
                                                                url ? __( 'Replace image', 'kabel-blocks' ) : __( 'Choose image', 'kabel-blocks' )
                                                        ),
                                                        url && onRemove ?
                                                                el(
                                                                        Button,
                                                                        { variant: 'secondary', size: 'small', onClick: onRemove, isDestructive: true },
                                                                        __( 'Remove', 'kabel-blocks' )
                                                                ) :
                                                                null
                                                )
                                        ),
                        } )
                ),
                url && onOverlay ?
                        el( RangeRow, {
                                label: __( 'Dark overlay', 'kabel-blocks' ),
                                value: overlay === undefined || overlay === null ? 45 : overlay,
                                onChange: onOverlay,
                                min: 0,
                                max: 90,
                                unit: '%',
                        } ) :
                        null
        );
}

/* ------------------------------------------------------------------ */
/* Editor-only helper: media image picker row (used by kbl/image)      */
/* ------------------------------------------------------------------ */

export { MediaUpload, MediaUploadCheck, Spinner };

/* ------------------------------------------------------------------ */
/* Typography panel (device-aware font controls)                       */
/* ------------------------------------------------------------------ */

const TYPO_DEVICES = [
        { name: 'desktop', dash: 'dashicons-desktop', label: __( 'Desktop', 'kabel-blocks' ) },
        { name: 'tablet', dash: 'dashicons-tablet', label: __( 'Tablet', 'kabel-blocks' ) },
        { name: 'mobile', dash: 'dashicons-smartphone', label: __( 'Mobile', 'kabel-blocks' ) },
];

/**
 * <TypographyPanel attrs={attributes} setAttrs={setAttributes} include={{lh:true,ls:true,tt:true}}/>
 *
 * Font size is per-device (desktop value, tablet override, mobile
 * override — empty overrides follow the desktop size). Weight, line
 * height, letter spacing and letter case can be switched off per block
 * through `include` where they make little sense (buttons, stats…).
 */
export function TypographyPanel( { attrs, setAttrs, include = {} } ) {
        const { fw = true, lh = true, ls = true, tt = true } = include;
        const [ device, setDevice ] = useState( 'desktop' );
        const sizeKey = 'tablet' === device ? 'fontSizeT' : 'mobile' === device ? 'fontSizeM' : 'fontSize';
        const follows = 'desktop' !== device && attrs[ sizeKey ] == null;

        return el(
                PanelBody,
                { title: __( 'Typography', 'kabel-blocks' ), initialOpen: false },
                el(
                        'div',
                        { className: 'kbl3-device-switch' },
                        el(
                                ButtonGroup,
                                null,
                                TYPO_DEVICES.map( ( d ) =>
                                        el(
                                                Button,
                                                {
                                                        key: d.name,
                                                        size: 'small',
                                                        variant: device === d.name ? 'primary' : 'secondary',
                                                        label: d.label,
                                                        onClick: () => setDevice( d.name ),
                                                },
                                                el( 'span', { className: `dashicons ${ d.dash }` } )
                                        )
                                )
                        ),
                        follows ? el( 'span', { className: 'kbl3-device-follows' }, __( 'Follows desktop', 'kabel-blocks' ) ) : null
                ),
                el( RangeControl, {
                        label: __( 'Font size (px)', 'kabel-blocks' ),
                        value: attrs[ sizeKey ],
                        onChange: ( v ) => setAttrs( { [ sizeKey ]: v == null ? undefined : v } ),
                        min: 12,
                        max: 96,
                        allowReset: true,
                } ),
                fw ? el( SelectControl, {
                        label: __( 'Font weight', 'kabel-blocks' ),
                        value: attrs.fontWeight == null ? '' : String( attrs.fontWeight ),
                        options: [
                                { value: '', label: __( 'Default', 'kabel-blocks' ) },
                                ...[ 300, 400, 500, 600, 700 ].map( ( w ) => ( { value: String( w ), label: String( w ) } ) ),
                        ],
                        onChange: ( v ) => setAttrs( { fontWeight: v === '' ? undefined : Number( v ) } ),
                } ) : null,
                lh ? el( RangeControl, {
                        label: __( 'Line height', 'kabel-blocks' ),
                        value: attrs.lineHeight,
                        onChange: ( v ) => setAttrs( { lineHeight: v == null ? undefined : v } ),
                        min: 1,
                        max: 2,
                        step: 0.05,
                        allowReset: true,
                } ) : null,
                ls ? el( RangeControl, {
                        label: __( 'Letter spacing (px)', 'kabel-blocks' ),
                        value: attrs.letterSpacing,
                        onChange: ( v ) => setAttrs( { letterSpacing: v == null ? undefined : v } ),
                        min: -2,
                        max: 8,
                        step: 0.1,
                        allowReset: true,
                } ) : null,
                tt ? el( SegButtons, {
                        label: __( 'Letter case', 'kabel-blocks' ),
                        value: attrs.textTransform || '',
                        onChange: ( v ) => setAttrs( { textTransform: v || undefined } ),
                        options: [
                                { value: '', label: __( 'None', 'kabel-blocks' ) },
                                { value: 'uppercase', label: 'UPPERCASE' },
                                { value: 'capitalize', label: 'Capitalize' },
                        ],
                } ) : null
        );
}

/* ------------------------------------------------------------------ */
/* Margin spacing panel (Advanced tab of every block)                  */
/* ------------------------------------------------------------------ */

/**
 * <SpacingPanel attrs={attributes} setAttrs={setAttributes} />
 *
 * Desktop margins (mt/mb) plus optional mobile overrides (mtM/mbM) that
 * only apply below 781px. Mirrors applySpacing()/spacingPairs() in
 * util.js and kbl3_spacing_class()/kbl3_spacing_pairs() in the emitter.
 */
export function SpacingPanel( { attrs, setAttrs } ) {
        const mobileOpen = attrs.mtM != null || attrs.mbM != null;
        return el(
                PanelBody,
                { title: __( 'Spacing', 'kabel-blocks' ), initialOpen: false },
                el( RangeRow, {
                        label: __( 'Margin top', 'kabel-blocks' ),
                        value: attrs.mt,
                        onChange: ( v ) => setAttrs( { mt: v } ),
                        min: -80,
                        max: 240,
                } ),
                el( RangeRow, {
                        label: __( 'Margin bottom', 'kabel-blocks' ),
                        value: attrs.mb,
                        onChange: ( v ) => setAttrs( { mb: v } ),
                        min: -80,
                        max: 240,
                } ),
                el( ToggleRow, {
                        label: __( 'Adjust margins on mobile', 'kabel-blocks' ),
                        checked: mobileOpen,
                        onChange: ( v ) =>
                                setAttrs(
                                        v
                                                ? { mtM: attrs.mtM == null ? 16 : attrs.mtM, mbM: attrs.mbM == null ? 16 : attrs.mbM }
                                                : { mtM: undefined, mbM: undefined }
                                ),
                } ),
                mobileOpen ? el( RangeRow, {
                        label: __( 'Margin top (mobile)', 'kabel-blocks' ),
                        value: attrs.mtM,
                        onChange: ( v ) => setAttrs( { mtM: v } ),
                        min: -80,
                        max: 160,
                } ) : null,
                mobileOpen ? el( RangeRow, {
                        label: __( 'Margin bottom (mobile)', 'kabel-blocks' ),
                        value: attrs.mbM,
                        onChange: ( v ) => setAttrs( { mbM: v } ),
                        min: -80,
                        max: 160,
                } ) : null,
                el( 'p', { className: 'kbl3-panel-hint' }, __( 'Margins push this block up or down — works in every container.', 'kabel-blocks' ) )
        );
}

/* ------------------------------------------------------------------ */
/* Device visibility panel (Advanced tab of every block)               */
/* ------------------------------------------------------------------ */

export function VisibilityPanel( { attrs, setAttrs } ) {
        return el(
                PanelBody,
                { title: __( 'Visibility', 'kabel-blocks' ), initialOpen: false },
                el( ToggleRow, {
                        label: __( 'Hide on desktop', 'kabel-blocks' ),
                        checked: !! attrs.hideD,
                        onChange: ( v ) => setAttrs( { hideD: v || undefined } ),
                } ),
                el( ToggleRow, {
                        label: __( 'Hide on tablet', 'kabel-blocks' ),
                        checked: !! attrs.hideT,
                        onChange: ( v ) => setAttrs( { hideT: v || undefined } ),
                } ),
                el( ToggleRow, {
                        label: __( 'Hide on mobile', 'kabel-blocks' ),
                        checked: !! attrs.hideM,
                        onChange: ( v ) => setAttrs( { hideM: v || undefined } ),
                } )
        );
}

/* ------------------------------------------------------------------ */
/* Entrance animation panel                                            */
/* ------------------------------------------------------------------ */

const ANIM_OPTIONS = [
        { value: '', label: __( 'None', 'kabel-blocks' ) },
        { value: 'fade-up', label: __( 'Fade up', 'kabel-blocks' ) },
        { value: 'fade-in', label: __( 'Fade in', 'kabel-blocks' ) },
        { value: 'zoom-in', label: __( 'Zoom in', 'kabel-blocks' ) },
        { value: 'slide-left', label: __( 'Slide from right', 'kabel-blocks' ) },
        { value: 'slide-right', label: __( 'Slide from left', 'kabel-blocks' ) },
];

export function AnimPanel( { attrs, setAttrs } ) {
        return el(
                PanelBody,
                { title: __( 'Animation', 'kabel-blocks' ), initialOpen: false },
                el( SelectRow, {
                        label: __( 'Entrance animation', 'kabel-blocks' ),
                        value: attrs.anim || '',
                        options: ANIM_OPTIONS,
                        onChange: ( v ) => setAttrs( { anim: v || undefined } ),
                } ),
                attrs.anim ? el( RangeControl, {
                        label: __( 'Delay (ms)', 'kabel-blocks' ),
                        value: attrs.animDelay,
                        onChange: ( v ) => setAttrs( { animDelay: v == null ? 0 : v } ),
                        min: 0,
                        max: 600,
                        step: 50,
                        unit: 'ms',
                        allowReset: true,
                } ) : null
        );
}

/* ------------------------------------------------------------------ */
/* Background gradient panel (section / column / icon box)             */
/* ------------------------------------------------------------------ */

export function GradientPanel( { attrs, setAttrs } ) {
        return el(
                PanelBody,
                { title: __( 'Background gradient', 'kabel-blocks' ), initialOpen: false },
                el( ColorsPanel, {
                        panelLabel: __( 'Gradient colors', 'kabel-blocks' ),
                        colors: [
                                { label: __( 'From', 'kabel-blocks' ), value: attrs.gFrom, onChange: ( v ) => setAttrs( { gFrom: v } ) },
                                { label: __( 'To', 'kabel-blocks' ), value: attrs.gTo, onChange: ( v ) => setAttrs( { gTo: v } ) },
                        ],
                } ),
                el( RangeRow, {
                        label: __( 'Angle (degrees)', 'kabel-blocks' ),
                        value: attrs.gAngle == null ? 135 : attrs.gAngle,
                        onChange: ( v ) => setAttrs( { gAngle: v == null ? 135 : v } ),
                        min: 0,
                        max: 360,
                        unit: '°',
                } ),
                el( 'p', { className: 'kbl3-panel-hint' }, __( 'A gradient replaces the solid background color. A background image replaces the gradient.', 'kabel-blocks' ) )
        );
}

/* ------------------------------------------------------------------ */
/* Hover effect panel (button / icon box)                              */
/* ------------------------------------------------------------------ */

export function HoverPanel( { attrs, setAttrs, include = { colors: true, lift: true } } ) {
        return el(
                PanelBody,
                { title: __( 'Hover effect', 'kabel-blocks' ), initialOpen: false },
                include.colors ? el( ColorsPanel, {
                        panelLabel: __( 'Hover colors', 'kabel-blocks' ),
                        colors: [
                                { label: __( 'Background', 'kabel-blocks' ), value: attrs.hoverBg, onChange: ( v ) => setAttrs( { hoverBg: v } ), isBackground: true },
                                { label: __( 'Label color', 'kabel-blocks' ), value: attrs.hoverText, onChange: ( v ) => setAttrs( { hoverText: v } ), isText: true },
                        ],
                } ) : null,
                include.lift ? el( ToggleRow, {
                        label: __( 'Lift on hover', 'kabel-blocks' ),
                        checked: !! attrs.hoverLift,
                        onChange: ( v ) => setAttrs( { hoverLift: v || undefined } ),
                } ) : null
        );
}
