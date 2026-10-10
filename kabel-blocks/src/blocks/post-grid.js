/**
 * kbl/post-grid — server-rendered grid of products / projects / posts.
 *
 * Dynamic block: save() returns null; PHP runs the query (see
 * inc/dynamic.php). The editor previews live records from the REST store.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { Placeholder, Spinner } from '@wordpress/components';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../util.js';
import { KblInspector, SegButtons, ToggleRow, TextRow, RangeRow, SpacingPanel, VisibilityPanel } from '../inspector.js';

const TYPE_OPTIONS = [
        { value: 'product', label: __( 'Products', 'kabel-blocks' ) },
        { value: 'projects', label: __( 'Projects', 'kabel-blocks' ) },
        { value: 'post', label: __( 'Posts', 'kabel-blocks' ) },
];

register( 'kbl/post-grid', {
        apiVersion: 3,
        title: __( 'Post grid (Kabel)', 'kabel-blocks' ),
        description: __(
                'Latest products, projects or posts in a card grid — updates itself.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'grid', 'products', 'posts', 'query', '产品', '文章网格', '文章列表', '内容列表'],
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
                postType: { type: 'string', default: 'product' },
                columns: { type: 'number', default: 3 },
                count: { type: 'number', default: 6 },
                showExcerpt: { type: 'boolean', default: true },
                btnLabel: { type: 'string', default: 'View All' },
                btnURL: { type: 'string' },
                hideD: { type: 'boolean' },
                hideT: { type: 'boolean' },
                hideM: { type: 'boolean' },
                mt: { type: 'number' },
                mb: { type: 'number' },
                mtM: { type: 'number' },
                mbM: { type: 'number' },
        },

        edit( { attributes, setAttributes } ) {
                const { postType, columns, count, showExcerpt, btnLabel, btnURL } = attributes;

                const records = useSelect(
                        ( select ) => {
                                const hasType = select( 'core' ).getPostType
                                        ? !! select( 'core' ).getPostType( postType )
                                        : true;
                                if ( ! hasType ) {
                                        return [];
                                }
                                return select( 'core' ).getEntityRecords( 'postType', postType, {
                                        per_page: count,
                                        orderby: 'date',
                                        order: 'desc',
                                        status: 'publish',
                                } ) || [];
                        },
                        [ postType, count ]
                );
                const isLoading = records === null;
                // Featured images are resolved per-record through the media store.
                const mediaIds = ( records || [] ).map( ( r ) => r.featured_media ).filter( Boolean );
                const featured = useSelect(
                        ( select ) => {
                                const out = {};
                                const coreSelect = select( 'core' );
                                mediaIds.forEach( ( id ) => {
                                        const media = coreSelect.getMedia ? coreSelect.getMedia( id ) : null;
                                        if ( media ) {
                                                out[ id ] = media.source_url;
                                        }
                                } );
                                return out;
                        },
                        [ mediaIds.join( ',' ) ]
                );

                const gridStyle = toStyle( [
                        [ '--kbl3-grid-cols', String( columns ) ],
                        ...spacingPairs( attributes ),
                ] );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        layout: el( 'div', null,
                                                el( SegButtons, {
                                                        label: __( 'Content type', 'kabel-blocks' ),
                                                        value: postType,
                                                        onChange: ( v ) => setAttributes( { postType: v } ),
                                                        options: TYPE_OPTIONS,
                                                } ),
                                                el( SegButtons, {
                                                        label: __( 'Columns', 'kabel-blocks' ),
                                                        value: columns,
                                                        onChange: ( v ) => setAttributes( { columns: Number( v ) } ),
                                                        options: [ 2, 3, 4 ].map( ( n ) => ( { value: n, label: String( n ) } ) ),
                                                } ),
                                                el( RangeRow, {
                                                        label: __( 'How many items', 'kabel-blocks' ),
                                                        value: count,
                                                        onChange: ( v ) => setAttributes( { count: v ?? 6 } ),
                                                        min: 2,
                                                        max: 12,
                                                } ),
                                                el( ToggleRow, {
                                                        label: __( 'Show excerpt', 'kabel-blocks' ),
                                                        checked: !! showExcerpt,
                                                        onChange: ( v ) => setAttributes( { showExcerpt: v } ),
                                                } )
                                        ),
                                        advanced: el( 'div', null,
                                                el( TextRow, {
                                                        label: __( 'Button label', 'kabel-blocks' ),
                                                        value: btnLabel,
                                                        onChange: ( v ) => setAttributes( { btnLabel: v } ),
                                                } ),
                                                el( TextRow, {
                                                        label: __( 'Button link', 'kabel-blocks' ),
                                                        value: btnURL,
                                                        onChange: ( v ) => setAttributes( { btnURL: v } ),
                                                        placeholder: '/products/',
                                                } ),
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
                                'div',
                                useBlockProps( { className: cx( 'kbl3-pgrid', ...applyVisibility( attributes ), ...applySpacing( attributes ) ), style: gridStyle } ),
                                isLoading ?
                                        el( Placeholder, null, el( Spinner, null ) ) :
                                        ( records || [] ).length === 0 ?
                                                el(
                                                        Placeholder,
                                                        { label: __( 'Post grid (Kabel)', 'kabel-blocks' ) },
                                                        __( 'No published items of this type yet — it will fill in automatically.', 'kabel-blocks' )
                                                ) :
                                                el(
                                                        'div',
                                                        { className: 'kbl3-pgrid__row' },
                                                        records.map( ( record ) =>
                                                                el(
                                                                        'article',
                                                                        { className: 'kbl3-pcard', key: record.id },
                                                                        featured[ record.featured_media ] ?
                                                                                el( 'img', {
                                                                                        className: 'kbl3-pcard__img',
                                                                                        src: featured[ record.featured_media ],
                                                                                        alt: record.title?.rendered || '',
                                                                                } ) :
                                                                                el( 'div', { className: 'kbl3-pcard__ph' } ),
                                                                        el(
                                                                                'div',
                                                                                { className: 'kbl3-pcard__body' },
                                                                                el(
                                                                                        'h4',
                                                                                        { className: 'kbl3-pcard__title' },
                                                                                        el( 'span', { dangerouslySetInnerHTML: { __html: record.title?.rendered || '' } } )
                                                                                ),
                                                                                showExcerpt ?
                                                                                        el( 'p', {
                                                                                                className: 'kbl3-pcard__text',
                                                                                                dangerouslySetInnerHTML: { __html: record.excerpt?.rendered || '' },
                                                                                        } ) :
                                                                                        null
                                                                        )
                                                                )
                                                        )
                                                ),
                                btnLabel ?
                                        el(
                                                'div',
                                                { className: 'kbl3-btnwrap is-center' },
                                                el( 'a', { className: 'kbl3-btn kbl3-btn--outline kbl3-btn--m', href: btnURL || '#' }, btnLabel )
                                        ) :
                                        null
                        )
                );
        },

        save() {
                return null;
        },
} );
