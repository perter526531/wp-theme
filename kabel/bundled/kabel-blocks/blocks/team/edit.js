/**
 * kbl/team — a grid of team member cards. Photos sit 4:5 and wake up from
 * grayscale to color on hover. Names, roles and photos are editable per card.
 *
 * v4 (Stackable-style responsive layer): the name font size, the grid column
 * count and the top / bottom margins are per-device attributes (fontSize /
 * fontSizeT / fontSizeM, columns / columnsT / columnsM, mt / mb with T / M
 * variants) riding the per-instance <style> tag. The font size keeps its
 * --kbl3-fs chain (the shared CSS consumes it at every breakpoint); the
 * column count keeps --kbl3-team-cols on the root — with a uid the inline
 * variable is dropped and re-emitted from the sheet, and when columnsT /
 * columnsM are set a per-instance grid-template-columns rule (reading the
 * same variable) beats the shared tablet cap and the hardcoded mobile
 * single-column rules. Margins are real properties (no --kbl-mt consumer
 * exists for .kbl3-team). The Advanced tab gains the per-instance custom
 * CSS box and the HTML anchor (AdvancedPanelV2). Legacy content without a
 * uid keeps the exact v3 output. The member items are untouched repeater
 * data.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, MediaUpload } from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';
import { sanitizeClass, cx, toStyle, iconSvg, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs, typographyPairs } from '../../src/util.js';
import {
        KblInspector,
        RangeRow,
        TextRow,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
        Resp,
        useUid,
        uidClass,
        respStyleEl,
        v4Strip,
} from '../../src/kit.js';

export const DEFAULT_ITEMS = [
        { name: 'Elena Marsh', role: 'Lead Color Consultant', photoId: 0, photoURL: '', linkURL: '' },
        { name: 'Victor Hale', role: 'Coatings Engineer', photoId: 0, photoURL: '', linkURL: '' },
        { name: 'Ines Rocha', role: 'Project Manager', photoId: 0, photoURL: '', linkURL: '' },
];

/* Which attributes ride the per-instance <style> tag (respCss spec).
 * fontSize keeps the --kbl3-fs chain (consumed by .kbl3-team__name at every
 * breakpoint). columns re-emits --kbl3-team-cols from the sheet (the inline
 * variable is dropped once a uid exists); the grid-template-columns entries
 * fire only when columnsT / columnsM are set and override the shared
 * tablet cap / hardcoded mobile rules by specificity. Margins are real
 * properties. PHP twin: the $spec in kbl3_team(). */
export const RESP_SPEC = [
        { a: 'fontSize', p: '--kbl3-fs', u: 'px' },
        { a: 'columns', p: '--kbl3-team-cols' },
        { a: 'columns', p: 'grid-template-columns', raw: 'repeat(var(--kbl3-team-cols,2),minmax(0,1fr))', sel: '.kbl3-team', only: 't' },
        { a: 'columns', p: 'grid-template-columns', raw: 'repeat(var(--kbl3-team-cols,1),minmax(0,1fr))', sel: '.kbl3-team', only: 'm' },
        { a: 'mt', p: 'margin-top', u: 'px' },
        { a: 'mb', p: 'margin-bottom', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists (the inline
 * --kbl3-fs / --kbl3-team-cols / --kbl3-m* variables would beat every
 * media-query override). PHP twin: kbl4_strip() inside kbl3_team(). */
export const STRIP_KEYS = [ 'fontSize', 'fontSizeT', 'fontSizeM', 'columns', 'columnsT', 'columnsM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

/** Shared by edit + save (imported by save.js). PHP twin: the class list in
 * kbl3_team(). Takes the v4-stripped copy — the uid survives the strip. */
export function teamClass( a, items ) {
        return cx(
                'kbl3-team',
                items.length >= 3 && 'kbl3-team--2up',
                uidClass( a ),
                ...applyAnim( a ),
                ...applyVisibility( a ),
                ...applySpacing( a ),
                sanitizeClass( a.customClass )
        );
}

/** Shared by edit + save (imported by save.js). PHP twin: kbl3_team_style().
 * With a uid the --kbl3-team-cols variable rides the per-instance sheet —
 * the inline slot must stay empty or it would beat every media-query
 * override. */
export function teamStyle( a ) {
        return toStyle( [
                [ '--kbl3-team-cols', a.uid ? null : String( a.columns ?? 3 ) ],
                ...typographyPairs( a, { fw: false, lh: false, ls: false, tt: false } ),
                ...animDelayPairs( a ),
                ...spacingPairs( a ),
        ] );
}

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const a = attributes;
                const items = a.items && a.items.length ? a.items : DEFAULT_ITEMS;

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = teamClass( legacy, items );
                const style = teamStyle( legacy );

                const updateItem = ( i, patch ) => {
                        setAttributes( {
                                items: items.map( ( item, j ) => ( j === i ? { ...item, ...patch } : item ) ),
                        } );
                };
                const removeItem = ( i ) => {
                        setAttributes( { items: items.filter( ( _, j ) => j !== i ) } );
                };
                const moveItem = ( i, dir ) => {
                        const j = i + dir;
                        if ( j < 0 || j >= items.length ) {
                                return;
                        }
                        const next = items.slice();
                        const [ moved ] = next.splice( i, 1 );
                        next.splice( j, 0, moved );
                        setAttributes( { items: next } );
                };

                const itemsEditor = el(
                        PanelBody,
                        { title: __( 'Members', 'kabel-blocks' ), initialOpen: true },
                        items.map( ( item, i ) =>
                                el(
                                        'div',
                                        { className: 'kbl3-item-row', key: i },
                                        el( TextRow, {
                                                label: __( 'Member', 'kabel-blocks' ) + ' ' + ( i + 1 ) + ' — ' + __( 'name', 'kabel-blocks' ),
                                                value: item.name,
                                                onChange: ( v ) => updateItem( i, { name: v } ),
                                        } ),
                                        el( TextRow, {
                                                label: __( 'Role', 'kabel-blocks' ),
                                                value: item.role,
                                                onChange: ( v ) => updateItem( i, { role: v } ),
                                        } ),
                                        el( TextRow, {
                                                label: __( 'Profile link (optional)', 'kabel-blocks' ),
                                                value: item.linkURL,
                                                onChange: ( v ) => updateItem( i, { linkURL: v } ),
                                                placeholder: '/team/',
                                        } ),
                                        el(
                                                'div',
                                                { className: 'kbl3-item-row__btns' },
                                                el( Button, {
                                                        size: 'small',
                                                        variant: 'secondary',
                                                        disabled: i === 0,
                                                        onClick: () => moveItem( i, -1 ),
                                                        label: __( 'Move up', 'kabel-blocks' ),
                                                        icon: 'arrow-up',
                                                } ),
                                                el( Button, {
                                                        size: 'small',
                                                        variant: 'secondary',
                                                        disabled: i === items.length - 1,
                                                        onClick: () => moveItem( i, 1 ),
                                                        label: __( 'Move down', 'kabel-blocks' ),
                                                        icon: 'arrow-down',
                                                } ),
                                                el( Button, {
                                                        size: 'small',
                                                        variant: 'secondary',
                                                        isDestructive: true,
                                                        onClick: () => removeItem( i ),
                                                        label: __( 'Remove member', 'kabel-blocks' ),
                                                        icon: 'no',
                                                } )
                                        )
                                )
                        ),
                        el(
                                Button,
                                {
                                        variant: 'secondary',
                                        size: 'small',
                                        onClick: () =>
                                                setAttributes( {
                                                        items: [
                                                                ...items,
                                                                { name: '', role: '', photoId: 0, photoURL: '', linkURL: '' },
                                                        ],
                                                } ),
                                },
                                __( 'Add member', 'kabel-blocks' )
                        )
                );

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
                                                'div',
                                                null,
                                                el( Resp, { prop: 'columns', attrs: attributes, setAttrs: setAttributes },
                                                        el( RangeRow, {
                                                                label: __( 'Columns', 'kabel-blocks' ),
                                                                min: 2,
                                                                max: 4,
                                                        } ) ),
                                                itemsEditor
                                        ),
                                        style: el(
                                                'div',
                                                null,
                                                el( TypographyPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                        include: { fw: false, lh: false, ls: false, tt: false },
                                                        v4: true,
                                                } ),
                                                el( AnimPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                        advanced: el( 'div', null,
                                                el( SpacingPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                        v4: true,
                                                } ),
                                                el( VisibilityPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                }
                        ),
                        el(
                                'div',
                                useBlockProps( { className, style, id: a.anchor || undefined } ),
                                items.map( ( item, i ) =>
                                        el(
                                                'div',
                                                { className: 'kbl3-team__card', key: i },
                                                el(
                                                        'div',
                                                        { className: 'kbl3-team__photo' },
                                                        el( MediaUpload, {
                                                                onSelect: ( media ) =>
                                                                        updateItem( i, { photoId: media.id, photoURL: media.url } ),
                                                                allowedTypes: [ 'image' ],
                                                                value: item.photoId || undefined,
                                                                render: ( { open } ) =>
                                                                        item.photoURL
                                                                                ? el( 'img', {
                                                                                                className: 'kbl3-team__img',
                                                                                                src: item.photoURL,
                                                                                                alt: item.name || '',
                                                                                                onClick: open,
                                                                                  } )
                                                                                : el(
                                                                                                'span',
                                                                                                {
                                                                                                                className: 'kbl3-icon kbl3-team__phicon',
                                                                                                                style: toStyle( [ [ '--kbl3-icon-size', '44px' ] ] ),
                                                                                                                onClick: open,
                                                                                                },
                                                                                                iconSvg( 'user' )
                                                                                  ),
                                                        } )
                                                ),
                                                item.linkURL
                                                        ? el(
                                                                        'a',
                                                                        { className: 'kbl3-team__link', href: item.linkURL },
                                                                        el( RichText, {
                                                                                tagName: 'h3',
                                                                                className: 'kbl3-team__name',
                                                                                value: item.name,
                                                                                onChange: ( v ) => updateItem( i, { name: v } ),
                                                                                placeholder: __( 'Member name…', 'kabel-blocks' ),
                                                                                allowedFormats: [ 'core/bold', 'core/italic' ],
                                                                                identifier: 'name-' + i,
                                                                  } )
                                                          )
                                                        : el( RichText, {
                                                                        tagName: 'h3',
                                                                        className: 'kbl3-team__name',
                                                                        value: item.name,
                                                                        onChange: ( v ) => updateItem( i, { name: v } ),
                                                                        placeholder: __( 'Member name…', 'kabel-blocks' ),
                                                                        allowedFormats: [ 'core/bold', 'core/italic' ],
                                                                        identifier: 'name-' + i,
                                                          } ),
                                                el( RichText, {
                                                        tagName: 'p',
                                                        className: 'kbl3-team__role',
                                                        value: item.role,
                                                        onChange: ( v ) => updateItem( i, { role: v } ),
                                                        placeholder: __( 'Role or title…', 'kabel-blocks' ),
                                                        allowedFormats: [ 'core/bold', 'core/italic' ],
                                                        identifier: 'role-' + i,
                                                } )
                                        )
                                )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
