/**
 * kbl/team — a grid of team member cards. Photos sit 4:5 and wake up from
 * grayscale to color on hover. Names, roles and photos are editable per card.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps, RichText, MediaUpload } from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';
import { cx, toStyle, iconSvg, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs, typographyPairs } from '../util.js';
import {
        KblInspector,
        RangeRow,
        TextRow,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
} from '../inspector.js';

const DEFAULT_ITEMS = [
        { name: 'Elena Marsh', role: 'Lead Color Consultant', photoId: 0, photoURL: '', linkURL: '' },
        { name: 'Victor Hale', role: 'Coatings Engineer', photoId: 0, photoURL: '', linkURL: '' },
        { name: 'Ines Rocha', role: 'Project Manager', photoId: 0, photoURL: '', linkURL: '' },
];

register( 'kbl/team', {
        apiVersion: 3,
        title: __( 'Team (Kabel)', 'kabel-blocks' ),
        description: __(
                'Team member cards with grayscale-to-color portraits, names and roles.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'team', 'members', 'staff', '团队', '成员', '团队', '成员'],
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
                items: { type: 'array', default: DEFAULT_ITEMS },
                columns: { type: 'number', default: 3 },
                fontSize: { type: 'number' },
                fontSizeT: { type: 'number' },
                fontSizeM: { type: 'number' },
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

        edit( { attributes, setAttributes } ) {
                const a = attributes;
                const items = a.items && a.items.length ? a.items : DEFAULT_ITEMS;

                const className = teamClass( a, items );
                const style = teamStyle( a );

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
                                        layout: el(
                                                'div',
                                                null,
                                                el( RangeRow, {
                                                        label: __( 'Columns', 'kabel-blocks' ),
                                                        value: a.columns,
                                                        onChange: ( v ) => setAttributes( { columns: v ?? 3 } ),
                                                        min: 2,
                                                        max: 4,
                                                } ),
                                                itemsEditor
                                        ),
                                        style: el(
                                                'div',
                                                null,
                                                el( TypographyPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                        include: { fw: false, lh: false, ls: false, tt: false },
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
                        )
                );
        },

        save( { attributes } ) {
                const a = attributes;
                const items = a.items || [];

                return el(
                        'div',
                        useBlockProps.save( { className: teamClass( a, items ), style: teamStyle( a ), id: a.anchor || undefined } ),
                        items.map( ( item, i ) =>
                                el(
                                        'div',
                                        { className: 'kbl3-team__card', key: i },
                                        el(
                                                'div',
                                                { className: 'kbl3-team__photo' },
                                                item.photoURL
                                                        ? el( 'img', {
                                                                        className: 'kbl3-team__img',
                                                                        src: item.photoURL,
                                                                        alt: item.name || '',
                                                          } )
                                                        : el(
                                                                        'span',
                                                                        {
                                                                                        className: 'kbl3-icon kbl3-team__phicon',
                                                                                        style: toStyle( [ [ '--kbl3-icon-size', '44px' ] ] ),
                                                                        },
                                                                        iconSvg( 'user' )
                                                          )
                                        ),
                                        item.linkURL
                                                ? el(
                                                                'a',
                                                                { className: 'kbl3-team__link', href: item.linkURL },
                                                                el( RichText.Content, {
                                                                        tagName: 'h3',
                                                                        className: 'kbl3-team__name',
                                                                        value: item.name,
                                                                } )
                                                  )
                                                : el( RichText.Content, {
                                                                tagName: 'h3',
                                                                className: 'kbl3-team__name',
                                                                value: item.name,
                                                  } ),
                                        el( RichText.Content, {
                                                tagName: 'p',
                                                className: 'kbl3-team__role',
                                                value: item.role,
                                        } )
                                )
                        )
                );
        },
} );

/** Shared by edit + save. PHP twin: kbl3_team_class(). */
function teamClass( a, items ) {
        return cx(
                'kbl3-team',
                items.length >= 3 && 'kbl3-team--2up',
                ...applyAnim( a ),
                ...applyVisibility( a ),
                ...applySpacing( a )
        );
}

/** Shared by edit + save. PHP twin: kbl3_team_style(). */
function teamStyle( a ) {
        return toStyle( [
                [ '--kbl3-team-cols', String( a.columns ?? 3 ) ],
                ...typographyPairs( a, { fw: false, lh: false, ls: false, tt: false } ),
                ...animDelayPairs( a ),
                ...spacingPairs( a ),
        ] );
}
