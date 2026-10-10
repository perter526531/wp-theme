/**
 * kbl/section — the outer container every Kabel page is built from.
 *
 * A full-width band with tone (dark/light), background image + overlay,
 * padding rhythm and content width. Anything can be nested inside.
 * Optional background gradient, shape dividers and entrance animation.
 *
 * v4 (Stackable-style responsive layer): the vertical paddings (pt / pb),
 * minimum height, margins (mt / mb) and the legacy mobile padding (padM)
 * are per-device attributes (desktop / tablet ≤1080px / mobile ≤781px)
 * riding the per-instance <style> tag (uid class + --kbl-* custom
 * properties + real properties for the non-inheriting ones), NOT the
 * legacy inline styles — see src/kit.js. The desktop pt / pb default
 * follows the pad preset (compact 44 / normal 72 / spacious 108) through
 * respCss's `d` map, so an inner v4 section never inherits an outer
 * section's padding. The legacy padTop / padBottom attributes migrate
 * into --kbl-pt / --kbl-pb once a uid exists (zero visual change on
 * upgrade). Real properties (margin-* / min-height / mobile padding) are
 * used for everything that must not inherit into nested blocks. The
 * Advanced tab gains the per-instance custom CSS box and the HTML anchor
 * (AdvancedPanelV2). Legacy content without a uid keeps the exact v3
 * output (v4Strip passes the attributes through untouched).
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
        useBlockProps,
        useInnerBlocksProps,
        InnerBlocks,
} from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import {
        cx,
        toStyle,
        bgUrl,
        applyVisibility,
        applySpacing,
        spacingPairs,
        applyAnim,
        animDelayPairs,
        gradientValue,
        dividerSvg,
        sanitizeClass,
} from '../../src/util.js';
import {
        KblInspector,
        SelectRow,
        RangeRow,
        SegButtons,
        ColorsPanel,
        BgImageControl,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
        GradientPanel,
        Resp,
        useUid,
        uidClass,
        respStyleEl,
        v4Strip,
} from '../../src/kit.js';

export const TONE_OPTIONS = [
        { value: 'dark', label: __( 'Dark (near-black)', 'kabel-blocks' ) },
        { value: 'navy', label: __( 'Navy black', 'kabel-blocks' ) },
        { value: 'white', label: __( 'White (light)', 'kabel-blocks' ) },
];

export const PAD_OPTIONS = [
        { value: 'compact', label: __( 'Compact', 'kabel-blocks' ) },
        { value: 'normal', label: __( 'Normal', 'kabel-blocks' ) },
        { value: 'spacious', label: __( 'Spacious', 'kabel-blocks' ) },
];

export const WIDTH_OPTIONS = [
        { value: 'normal', label: __( 'Normal (1290px)', 'kabel-blocks' ) },
        { value: 'narrow', label: __( 'Narrow (820px)', 'kabel-blocks' ) },
        { value: 'full', label: __( 'Full bleed', 'kabel-blocks' ) },
];

export const DIVIDER_OPTIONS = [
        { value: '', label: __( 'None', 'kabel-blocks' ) },
        { value: 'wave', label: __( 'Wave', 'kabel-blocks' ) },
        { value: 'tilt', label: __( 'Tilt', 'kabel-blocks' ) },
        { value: 'arc', label: __( 'Arc', 'kabel-blocks' ) },
];

/* Which attributes ride the per-instance <style> tag (respCss spec).
 * pt / pb are custom properties consumed by the .kbl3-pad--* preset rules
 * (desktop default = the pad preset, via the `d` map). mt / mb / minH /
 * padM are REAL properties: margins and min-height must not inherit into
 * nested sections (an outer --kbl-mt would leak into every legacy heading
 * and text inside), and padM keeps its all-four-sides mobile semantics. */
export const RESP_SPEC = [
        { a: 'pt', p: '--kbl-pt', u: 'px', d: { key: 'pad', map: { compact: '44px', normal: '72px', spacious: '108px' }, fallback: '72px' } },
        { a: 'pb', p: '--kbl-pb', u: 'px', d: { key: 'pad', map: { compact: '44px', normal: '72px', spacious: '108px' }, fallback: '72px' } },
        { a: 'minH', p: 'min-height', u: 'px' },
        { a: 'mt', p: 'margin-top', u: 'px' },
        { a: 'mb', p: 'margin-bottom', u: 'px' },
        { a: 'padM', p: 'padding', u: 'px', only: 'm' },
];
/* The keys the legacy pair helpers must stop emitting once a uid exists.
 * padTop / padBottom feed the responsive merge below instead of the old
 * inline paddings. PHP twin: kbl4_strip() inside kbl3_section(). */
export const STRIP_KEYS = [ 'pt', 'ptT', 'ptM', 'pb', 'pbT', 'pbM', 'minH', 'minHT', 'minHM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM', 'padM', 'padTop', 'padBottom' ];

/**
 * The responsive input: the legacy padTop / padBottom attributes migrate
 * into pt / pb once a uid exists (the v4 control reads and writes pt/pb,
 * so upgraded sections keep rendering their old values). Shared by edit +
 * save + the sidebar rows. PHP twin: the $resp merge in kbl3_section().
 */
export function sectionRespAttrs( a ) {
        return {
                ...a,
                pt: a.pt != null ? a.pt : a.padTop,
                pb: a.pb != null ? a.pb : a.padBottom,
        };
}

/** Shared by edit + save (imported by save.js). PHP twin: kbl3_section(). */
export function containerClass( width, widthPx ) {
        return cx(
                'kbl3-container',
                widthPx != null && 'kbl3-container--custom',
                width === 'narrow' && 'kbl3-container--narrow',
                width === 'full' && 'kbl3-container--full'
        );
}

/** Shared by edit + save (imported by save.js). PHP twin: kbl3_section().
 * Receives the v4-stripped copy so the responsive props stay silent. */
export function sectionClass( a ) {
        return cx(
                'kbl3-section',
                a.tone === 'white' ? 'kbl3-tone--white' : a.tone === 'navy' ? 'kbl3-tone--navy' : 'kbl3-tone--dark',
                `kbl3-pad--${ a.pad }`,
                a.vAlign === 'center' && 'kbl3-mid',
                a.bgURL && 'has-bgimg',
                uidClass( a ),
                ...applyAnim( a ),
                ...applyVisibility( a ),
                ...applySpacing( a ),
                sanitizeClass( a.customClass )
        );
}

/** Shared by edit + save (imported by save.js). PHP twin: kbl3_section().
 * Receives the v4-stripped copy so the responsive props stay silent. */
export function sectionStyle( a ) {
        return toStyle( [
                [ 'background-color', a.bgColor ],
                [ 'background-image', bgUrl( a.bgURL ) || gradientValue( a ) ],
                [ '--kbl3-ov', a.bgURL ? String( ( a.overlay ?? 45 ) / 100 ) : null ],
                [ 'color', a.textColor ],
                [ 'min-height', a.minH != null ? `${ a.minH }px` : null ],
                [ 'padding-top', a.padTop != null ? `${ a.padTop }px` : null ],
                [ 'padding-bottom', a.padBottom != null ? `${ a.padBottom }px` : null ],
                ...animDelayPairs( a ),
                ...spacingPairs( a ),
        ] );
}

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const {
                        anchor, tone, pad, width, widthPx, bgColor, bgURL, bgId, overlay, textColor,
                        vAlign, divTop, divBottom, divColor,
                } = attributes;

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );
                const resp = sectionRespAttrs( attributes );

                const className = sectionClass( legacy );
                const style = sectionStyle( legacy );

                const blockProps = useBlockProps( { className, style, id: anchor || undefined } );
                const innerProps = useInnerBlocksProps(
                        { className: containerClass( width, widthPx ), style: widthPx != null ? toStyle( [ [ '--kbl3-cw', `${ widthPx }px` ] ] ) : undefined },
                        { renderAppender: InnerBlocks.ButtonBlockAppender }
                );

                const setBg = ( { url, id } ) =>
                        setAttributes( { bgURL: url, bgId: id } );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        attrs: attributes,
                                        setAttrs: setAttributes,
                                        v4: true,
                                        layout: el(
                                                PanelBody,
                                                { initialOpen: true },
                                                el( SelectRow, {
                                                        label: __( 'Tone', 'kabel-blocks' ),
                                                        value: tone,
                                                        options: TONE_OPTIONS,
                                                        onChange: ( v ) => setAttributes( { tone: v } ),
                                                } ),
                                                el( SelectRow, {
                                                        label: __( 'Vertical padding', 'kabel-blocks' ),
                                                        value: pad,
                                                        options: PAD_OPTIONS,
                                                        onChange: ( v ) => setAttributes( { pad: v } ),
                                                } ),
                                                el( Resp, { prop: 'pt', attrs: resp, setAttrs: setAttributes },
                                                        el( RangeRow, {
                                                                label: __( 'Padding top (px)', 'kabel-blocks' ),
                                                                min: 0,
                                                                max: 200,
                                                        } ) ),
                                                el( Resp, { prop: 'pb', attrs: resp, setAttrs: setAttributes },
                                                        el( RangeRow, {
                                                                label: __( 'Padding bottom (px)', 'kabel-blocks' ),
                                                                min: 0,
                                                                max: 200,
                                                        } ) ),
                                                el( SelectRow, {
                                                        label: __( 'Content width', 'kabel-blocks' ),
                                                        value: width,
                                                        options: WIDTH_OPTIONS,
                                                        onChange: ( v ) => setAttributes( { width: v } ),
                                                } ),
                                                el( RangeRow, {
                                                        label: __( 'Custom content width (px)', 'kabel-blocks' ),
                                                        value: widthPx,
                                                        onChange: ( v ) => setAttributes( { widthPx: v } ),
                                                        min: 640,
                                                        max: 1680,
                                                        step: 10,
                                                        help: __( 'Custom width wins over the preset above.', 'kabel-blocks' ),
                                                } ),
                                                el( Resp, { prop: 'minH', attrs: attributes, setAttrs: setAttributes },
                                                        el( RangeRow, {
                                                                label: __( 'Minimum height', 'kabel-blocks' ),
                                                                min: 240,
                                                                max: 1080,
                                                        } ) ),
                                                el( SegButtons, {
                                                        label: __( 'Content vertical position', 'kabel-blocks' ),
                                                        value: vAlign || 'top',
                                                        onChange: ( v ) => setAttributes( { vAlign: v === 'top' ? undefined : v } ),
                                                        options: [
                                                                { value: 'top', label: __( 'Top', 'kabel-blocks' ) },
                                                                { value: 'center', label: __( 'Center', 'kabel-blocks' ) },
                                                        ],
                                                } ),
                                                el( SegButtons, {
                                                        label: __( 'Divider top', 'kabel-blocks' ),
                                                        value: divTop || '',
                                                        options: DIVIDER_OPTIONS,
                                                        onChange: ( v ) => setAttributes( { divTop: v || undefined } ),
                                                } ),
                                                el( SegButtons, {
                                                        label: __( 'Divider bottom', 'kabel-blocks' ),
                                                        value: divBottom || '',
                                                        options: DIVIDER_OPTIONS,
                                                        onChange: ( v ) => setAttributes( { divBottom: v || undefined } ),
                                                } ),
                                                ( divTop || divBottom ) ? el( ColorsPanel, {
                                                        panelLabel: __( 'Divider color', 'kabel-blocks' ),
                                                        colors: [
                                                                {
                                                                        label: __( 'Divider color', 'kabel-blocks' ),
                                                                        value: divColor,
                                                                        onChange: ( v ) => setAttributes( { divColor: v } ),
                                                                },
                                                        ],
                                                } ) : null,
                                                el( BgImageControl, {
                                                        url: bgURL,
                                                        id: bgId,
                                                        onSelect: setBg,
                                                        onRemove: () => setAttributes( { bgURL: undefined, bgId: undefined } ),
                                                        overlay,
                                                        onOverlay: ( v ) => setAttributes( { overlay: v ?? 45 } ),
                                                } )
                                        ),
                                        style: el( 'div', null,
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Background color', 'kabel-blocks' ),
                                                                        value: bgColor,
                                                                        onChange: ( v ) => setAttributes( { bgColor: v } ),
                                                                        isBackground: true,
                                                                },
                                                                {
                                                                        label: __( 'Text color', 'kabel-blocks' ),
                                                                        value: textColor,
                                                                        onChange: ( v ) => setAttributes( { textColor: v } ),
                                                                        isText: true,
                                                                },
                                                        ],
                                                } ),
                                                el( 'p', { className: 'kbl3-panel-hint' }, __( 'Custom background color overrides the tone preset. Pair it with the text color.', 'kabel-blocks' ) ),
                                                el( GradientPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                } ),
                                                el( AnimPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                        advanced: el( 'div', null,
                                                el( SpacingPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                        v4: true,
                                                        /* The Layout tab owns the section paddings (pad
                                                         * presets + the Resp pt / pb rows above). */
                                                        paddings: false,
                                                } ),
                                                el( VisibilityPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                }
                        ),
                        el(
                                'section',
                                blockProps,
                                divTop ? dividerSvg( 'top', divTop, divColor ) : null,
                                el( 'div', innerProps ),
                                divBottom ? dividerSvg( 'bottom', divBottom, divColor ) : null
                        ),
                        respStyleEl( resp, RESP_SPEC )
                );
        }
