/**
 * kbl/post-grid — server-rendered grid of products / projects / posts.
 *
 * Dynamic block: save() returns null; PHP runs the query (see render.php
 * in this folder). The editor previews live records from the REST store.
 *
 * v4 (Stackable-style responsive layer): the grid column count and the top /
 * bottom margins are per-device attributes (columns / columnsT / columnsM,
 * mt / mb with T / M variants) riding the per-instance <style> tag. The
 * column count keeps --kbl3-grid-cols on the root — with a uid the inline
 * variable is dropped and re-emitted from the sheet, and when columnsT /
 * columnsM are set a per-instance grid-template-columns rule (reading the
 * same variable) beats the shared tablet cap and the hardcoded mobile
 * single-column rules (team-block pattern). Margins are real properties (no
 * --kbl-mt consumer exists for .kbl3-pgrid). The Advanced tab gains the
 * per-instance custom CSS box and the HTML anchor (AdvancedPanelV2). Legacy
 * content without a uid keeps the exact v3 output.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { Placeholder, Spinner } from '@wordpress/components';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../../src/util.js';
import { KblInspector, SegButtons, ToggleRow, TextRow, RangeRow, SpacingPanel, VisibilityPanel, Resp, useUid, uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

export const TYPE_OPTIONS = [
        { value: 'product', label: __( 'Products', 'kabel-blocks' ) },
        { value: 'projects', label: __( 'Projects', 'kabel-blocks' ) },
        { value: 'post', label: __( 'Posts', 'kabel-blocks' ) },
];

/* Which attributes ride the per-instance <style> tag (respCss spec).
 * columns re-emits --kbl3-grid-cols from the sheet (the inline variable is
 * dropped once a uid exists); the grid-template-columns entries fire only
 * when columnsT / columnsM are set and override the shared tablet cap /
 * hardcoded mobile rules by specificity. Margins are real properties. PHP
 * twin: the $spec in blocks/post-grid/render.php. */
export const RESP_SPEC = [
        { a: 'columns', p: '--kbl3-grid-cols' },
        { a: 'columns', p: 'grid-template-columns', raw: 'repeat(var(--kbl3-grid-cols,2),minmax(0,1fr))', sel: ' .kbl3-pgrid__row', only: 't' },
        { a: 'columns', p: 'grid-template-columns', raw: 'repeat(var(--kbl3-grid-cols,1),minmax(0,1fr))', sel: ' .kbl3-pgrid__row', only: 'm' },
        { a: 'mt', p: 'margin-top', u: 'px' },
        { a: 'mb', p: 'margin-bottom', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists (the inline
 * --kbl3-grid-cols variable and the margin utilities would beat every
 * media-query override). PHP twin: kbl4_strip() in render.php. */
export const STRIP_KEYS = [ 'columns', 'columnsT', 'columnsM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const { postType, count, showExcerpt, btnLabel, btnURL } = attributes;

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

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

                /* v3 keeps the inline --kbl3-grid-cols on the root (byte-parity with
                 * the saved markup); with a uid the variable is stripped and
                 * re-emitted from the per-instance sheet instead. */
                const gridStyle = toStyle( [
                        ...( legacy.columns != null ? [ [ '--kbl3-grid-cols', String( legacy.columns ) ] ] : [] ),
                        ...spacingPairs( legacy ),
                ] );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        attrs: attributes,
                                        setAttrs: setAttributes,
                                        v4: true,
                                        layout: el( 'div', null,
                                                el( SegButtons, {
                                                        label: __( 'Content type', 'kabel-blocks' ),
                                                        value: postType,
                                                        onChange: ( v ) => setAttributes( { postType: v } ),
                                                        options: TYPE_OPTIONS,
                                                } ),
                                                el( Resp, { prop: 'columns', attrs: attributes, setAttrs: setAttributes },
                                                        el( SegButtons, {
                                                                label: __( 'Columns', 'kabel-blocks' ),
                                                                options: [ 2, 3, 4 ].map( ( n ) => ( { value: n, label: String( n ) } ) ),
                                                        } ) ),
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
                                                } ),
                                                el( SpacingPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                        v4: true,
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
                                useBlockProps( { className: cx( 'kbl3-pgrid', uidClass( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ) ), style: gridStyle, id: attributes.anchor || undefined } ),
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
                        ),
                        /* v4 preview: the per-instance <style> rides as a SIBLING so the
                         * editor replica shows the responsive columns / margins live. */
                        respStyleEl( attributes, RESP_SPEC )
                );
}
