/**
 * kbl/section — the outer container every Kabel page is built from.
 *
 * A full-width band with tone (dark/light), background image + overlay,
 * padding rhythm and content width. Anything can be nested inside.
 * Optional background gradient, shape dividers and entrance animation.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
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
} from '../util.js';
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
} from '../inspector.js';

const TONE_OPTIONS = [
        { value: 'dark', label: __( 'Dark (near-black)', 'kabel-blocks' ) },
        { value: 'navy', label: __( 'Navy black', 'kabel-blocks' ) },
        { value: 'white', label: __( 'White (light)', 'kabel-blocks' ) },
];

const PAD_OPTIONS = [
        { value: 'compact', label: __( 'Compact', 'kabel-blocks' ) },
        { value: 'normal', label: __( 'Normal', 'kabel-blocks' ) },
        { value: 'spacious', label: __( 'Spacious', 'kabel-blocks' ) },
];

const WIDTH_OPTIONS = [
        { value: 'normal', label: __( 'Normal (1290px)', 'kabel-blocks' ) },
        { value: 'narrow', label: __( 'Narrow (820px)', 'kabel-blocks' ) },
        { value: 'full', label: __( 'Full bleed', 'kabel-blocks' ) },
];

const DIVIDER_OPTIONS = [
        { value: '', label: __( 'None', 'kabel-blocks' ) },
        { value: 'wave', label: __( 'Wave', 'kabel-blocks' ) },
        { value: 'tilt', label: __( 'Tilt', 'kabel-blocks' ) },
        { value: 'arc', label: __( 'Arc', 'kabel-blocks' ) },
];

register( 'kbl/section', {
        apiVersion: 3,
        title: __( 'Section (Kabel)', 'kabel-blocks' ),
        description: __(
                'A full-width band with dark/light tone, background image and padding. Nest anything inside.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'section', 'container', 'band', 'zone', '分区', '横幅', '区块容器' ],
        supports: {
                anchor: true,
                customClassName: true,
        },
        attributes: {
                anchor: {
                        type: 'string',
                        source: 'attribute',
                        attribute: 'id',
                        selector: '*',
                },
                tone: { type: 'string', default: 'dark' },
                pad: { type: 'string', default: 'normal' },
                width: { type: 'string', default: 'normal' },
                widthPx: { type: 'number' },
                bgColor: { type: 'string' },
                bgURL: { type: 'string' },
                bgId: { type: 'number' },
                overlay: { type: 'number', default: 45 },
                textColor: { type: 'string' },
                padTop: { type: 'number' },
                padBottom: { type: 'number' },
                minH: { type: 'number' },
                vAlign: { type: 'string' },
                gFrom: { type: 'string' },
                gTo: { type: 'string' },
                gAngle: { type: 'number', default: 135 },
                divTop: { type: 'string' },
                divBottom: { type: 'string' },
                divColor: { type: 'string' },
                anim: { type: 'string' },
                animDelay: { type: 'number' },
                hideD: { type: 'boolean' },
                hideT: { type: 'boolean' },
                hideM: { type: 'boolean' },
                mt: { type: 'number' },
                mb: { type: 'number' },
                mtM: { type: 'number' },
                mbM: { type: 'number' },
        },

        edit( { attributes, setAttributes, clientId } ) {
                const {
                        anchor, tone, pad, width, widthPx, bgColor, bgURL, bgId, overlay, textColor,
                        padTop, padBottom, minH, vAlign, divTop, divBottom, divColor,
                } = attributes;

                const className = sectionClass( attributes );
                const style = sectionStyle( attributes );

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
                                        layout: el(
                                                PanelBody,
                                                { initialOpen: true },
                                                el( SelectRow, {
                                                        label: __( 'Tone', 'kabel-blocks' ),
                                                        value: tone,
                                                        options: TONE_OPTIONS,
                                                        onChange: ( v ) => setAttributes( { tone: v } ),
                                                } ),
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
                                                el( SelectRow, {
                                                        label: __( 'Vertical padding', 'kabel-blocks' ),
                                                        value: pad,
                                                        options: PAD_OPTIONS,
                                                        onChange: ( v ) => setAttributes( { pad: v } ),
                                                } ),
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
                                                el( RangeRow, {
                                                        label: __( 'Padding top (px)', 'kabel-blocks' ),
                                                        value: padTop,
                                                        onChange: ( v ) => setAttributes( { padTop: v } ),
                                                        min: 0,
                                                        max: 200,
                                                } ),
                                                el( RangeRow, {
                                                        label: __( 'Padding bottom (px)', 'kabel-blocks' ),
                                                        value: padBottom,
                                                        onChange: ( v ) => setAttributes( { padBottom: v } ),
                                                        min: 0,
                                                        max: 200,
                                                } ),
                                                el( RangeRow, {
                                                        label: __( 'Minimum height', 'kabel-blocks' ),
                                                        value: minH,
                                                        onChange: ( v ) => setAttributes( { minH: v } ),
                                                        min: 240,
                                                        max: 1080,
                                                } ),
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
                        )
                );
        },

        save( { attributes } ) {
                const { anchor, width, widthPx, divTop, divBottom, divColor } = attributes;

                return el(
                        'section',
                        useBlockProps.save( { className: sectionClass( attributes ), style: sectionStyle( attributes ), id: anchor || undefined } ),
                        divTop ? dividerSvg( 'top', divTop, divColor ) : null,
                        el(
                                'div',
                                {
                                        className: containerClass( width, widthPx ),
                                        style: widthPx != null ? toStyle( [ [ '--kbl3-cw', `${ widthPx }px` ] ] ) : undefined,
                                },
                                el( InnerBlocks.Content )
                        ),
                        divBottom ? dividerSvg( 'bottom', divBottom, divColor ) : null
                );
        },
} );

/** Shared by edit + save. PHP twin: kbl3_section(). */
function containerClass( width, widthPx ) {
        return cx(
                'kbl3-container',
                widthPx != null && 'kbl3-container--custom',
                width === 'narrow' && 'kbl3-container--narrow',
                width === 'full' && 'kbl3-container--full'
        );
}

/** Shared by edit + save. PHP twin: kbl3_section(). */
function sectionClass( a ) {
        return cx(
                'kbl3-section',
                a.tone === 'white' ? 'kbl3-tone--white' : a.tone === 'navy' ? 'kbl3-tone--navy' : 'kbl3-tone--dark',
                `kbl3-pad--${ a.pad }`,
                a.vAlign === 'center' && 'kbl3-mid',
                a.bgURL && 'has-bgimg',
                ...applyAnim( a ),
                ...applyVisibility( a ),
                ...applySpacing( a )
        );
}

/** Shared by edit + save. PHP twin: kbl3_section(). */
function sectionStyle( a ) {
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
