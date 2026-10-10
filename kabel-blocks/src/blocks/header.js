/**
 * kbl/header — the site header container.
 *
 * v14: the header itself only decides BEHAVIOR and CHROME — position
 * (sticky / hide-on-scroll-up / transparent overlay over the hero), colors
 * (background with alpha, text, accent, border), blur and height. The
 * CONTENT (logo, navigation, search, contact info, language switcher,
 * buttons…) is ordinary nested blocks — the Stackable/Blocksy element
 * model, block-native: drop kbl/contact-info into the header and the top
 * contact bar exists.
 *
 * Dynamic container: save() stores only the inner blocks (no wrapper
 * markup) — the render callback (inc/dynamic.php kbl3_render_header)
 * wraps them in the live <header> element. PHP twin:
 * kbl3_header_comment() in inc/emitter.php.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import {
        useBlockProps,
        useInnerBlocksProps,
        InnerBlocks,
} from '@wordpress/block-editor';
import { cx, toStyle } from '../util.js';
import {
        KblInspector,
        SegButtons,
        RangeRow,
        ToggleRow,
        ColorControl,
} from '../inspector.js';

/* Elements that make sense inside a header. */
const ALLOWED_BLOCKS = [
        'kbl/columns',
        'kbl/column',
        'kbl/heading',
        'kbl/text',
        'kbl/image',
        'kbl/icon',
        'kbl/button',
        'kbl/button-group',
        'kbl/navigation',
        'kbl/contact-info',
        'kbl/search-button',
        'kbl/language-switcher',
        'kbl/separator',
        'kbl/spacer',
];

/* Fresh headers start as the classic row: brand left, menu right. */
const TEMPLATE = [
        [
                'kbl/columns',
                {},
                [
                        [ 'kbl/column', { width: 30 }, [ [ 'kbl/heading', { level: 4, eyebrow: true, text: 'KABEL PAINTS' } ] ] ],
                        [ 'kbl/column', { width: 70 }, [ [ 'kbl/navigation', { align: 'right' } ] ] ],
                ],
        ],
];

registerBlockType( 'kbl/header', {
        apiVersion: 2,
        title: __( 'Header (Kabel)', 'kabel-blocks' ),
        description: __(
                'Site header container — sticky / transparent / colors. Fill it with the logo, navigation, search, contact info and language blocks.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'header', 'site header', 'sticky', '页眉', '网站页眉', '固定页眉', '透明页眉' ],
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
                sticky: { type: 'string', default: '' },
                overlay: { type: 'boolean' },
                bgColor: { type: 'string' },
                textColor: { type: 'string' },
                accentColor: { type: 'string' },
                borderColor: { type: 'string' },
                padY: { type: 'number', default: 16 },
                blur: { type: 'boolean' },
        },

        edit( { attributes, setAttributes } ) {
                const a = attributes;

                const className = cx(
                        'kbl3-header',
                        a.sticky === 'on' && 'kbl3-header--sticky',
                        a.sticky === 'up' && 'kbl3-header--sticky kbl3-header--hide-up',
                        a.overlay && 'kbl3-header--overlay',
                        a.blur && 'has-blur',
                );
                const style = toStyle( [
                        [ '--kbl3-header-bg', a.bgColor ],
                        [ 'color', a.textColor ],
                        [ '--kbl3-header-accent', a.accentColor ],
                        [ '--kbl3-header-border', a.borderColor ],
                        [ '--kbl3-header-py', `${ a.padY ?? 16 }px` ],
                ] );

                const blockProps = useBlockProps( { className, style, id: a.anchor || undefined } );
                const innerProps = useInnerBlocksProps(
                        { className: 'kbl3-header__inner' },
                        {
                                allowedBlocks: ALLOWED_BLOCKS,
                                template: TEMPLATE,
                                renderAppender: InnerBlocks.ButtonBlockAppender,
                        }
                );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        layout: el(
                                                'div',
                                                null,
                                                el( SegButtons, {
                                                        label: __( 'Position', 'kabel-blocks' ),
                                                        value: a.sticky ?? '',
                                                        onChange: ( v ) => setAttributes( { sticky: v } ),
                                                        options: [
                                                                { value: '', label: __( 'Static', 'kabel-blocks' ) },
                                                                { value: 'on', label: __( 'Sticky', 'kabel-blocks' ) },
                                                                { value: 'up', label: __( 'Hide on scroll', 'kabel-blocks' ) },
                                                        ],
                                                } ),
                                                el( ToggleRow, {
                                                        label: __( 'Transparent over hero', 'kabel-blocks' ),
                                                        checked: !! a.overlay,
                                                        onChange: ( v ) => setAttributes( { overlay: v } ),
                                                        help: __( 'Floats above the first section with no background until the page scrolls.', 'kabel-blocks' ),
                                                } ),
                                                el( RangeRow, {
                                                        label: __( 'Height padding', 'kabel-blocks' ),
                                                        value: a.padY,
                                                        onChange: ( v ) => setAttributes( { padY: v ?? 16 } ),
                                                        min: 4,
                                                        max: 40,
                                                } )
                                        ),
                                        style: el(
                                                'div',
                                                null,
                                                el( ColorControl, {
                                                        label: __( 'Background color', 'kabel-blocks' ),
                                                        value: a.bgColor,
                                                        onChange: ( v ) => setAttributes( { bgColor: v } ),
                                                        help: __( 'Pick a color and drag the alpha slider for a frosted look.', 'kabel-blocks' ),
                                                } ),
                                                el( ColorControl, {
                                                        label: __( 'Text color', 'kabel-blocks' ),
                                                        value: a.textColor,
                                                        onChange: ( v ) => setAttributes( { textColor: v } ),
                                                } ),
                                                el( ColorControl, {
                                                        label: __( 'Accent color', 'kabel-blocks' ),
                                                        value: a.accentColor,
                                                        onChange: ( v ) => setAttributes( { accentColor: v } ),
                                                } ),
                                                el( ColorControl, {
                                                        label: __( 'Border color', 'kabel-blocks' ),
                                                        value: a.borderColor,
                                                        onChange: ( v ) => setAttributes( { borderColor: v } ),
                                                } ),
                                                el( ToggleRow, {
                                                        label: __( 'Backdrop blur', 'kabel-blocks' ),
                                                        checked: !! a.blur,
                                                        onChange: ( v ) => setAttributes( { blur: v } ),
                                                        help: __( 'Frosted-glass effect over scrolling content.', 'kabel-blocks' ),
                                                } )
                                        ),
                                }
                        ),
                        el(
                                'header',
                                { ...blockProps, 'data-kbl-header': '' },
                                el( 'div', innerProps )
                        )
                );
        },

        save() {
                /* Dynamic container — the render callback wraps the inner
                 * blocks in the live <header> element. */
                return el( InnerBlocks.Content );
        },
} );
