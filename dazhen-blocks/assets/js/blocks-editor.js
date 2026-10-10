/**
 * Dazhen Blocks — the GENERIC editor runtime (schema-driven).
 *
 * This file contains ZERO knowledge about any specific block. It reads the
 * spec registry injected by inc/assets.php (window.DazhenBlocksSpecs) and
 * registers every block with an auto-built editing UI:
 *
 *   spec.panels[] → InspectorControls panels
 *   field.type    → text / textarea / range / select / toggle / media /
 *                   media-array / repeater controls
 *   spec.inner    → InnerBlocks container (the "section" block)
 *   otherwise     → ServerSideRender live preview (what you edit is what
 *                   visitors see)
 *
 * Adding a block on the PHP side never touches this file again.
 *
 * Also registers the "Dazhen layouts" sidebar for pages (one-click fill).
 *
 * Plain wp.element on purpose: no build step, nothing to compile.
 */

( function ( wp ) {
        'use strict';

        if ( ! wp || ! wp.blocks || ! wp.element || ! window.DazhenBlocksSpecs ) {
                return;
        }

        var el            = wp.element.createElement;
        var Fragment      = wp.element.Fragment;
        var useState      = wp.element.useState;
        var useEffect     = wp.element.useEffect;
        var __            = wp.i18n.__;
        var sprintf       = wp.i18n.sprintf;
        var registerBlockType = wp.blocks.registerBlockType;

        var blockEditor   = wp.blockEditor || wp.editor;
        var InspectorControls = blockEditor.InspectorControls;
        var MediaUpload       = blockEditor.MediaUpload;
        var MediaUploadCheck  = blockEditor.MediaUploadCheck;
        var InnerBlocks       = blockEditor.InnerBlocks;
        var useBlockProps     = blockEditor.useBlockProps;

        var PanelBody      = wp.components.PanelBody;
        var PanelRow       = wp.components.PanelRow;
        var RangeControl   = wp.components.RangeControl;
        var SelectControl  = wp.components.SelectControl;
        var TextControl    = wp.components.TextControl;
        var TextareaControl = wp.components.TextareaControl;
        var ToggleControl  = wp.components.ToggleControl;
        var Button         = wp.components.Button;
        var Spinner        = wp.components.Spinner;
        var ExternalLink   = wp.components.ExternalLink;

        var ServerSideRender = ( wp.serverSideRender && wp.serverSideRender.ServerSideRender ) || null;

        /* ------------------------------------------------------------------ */
        /* Small shared helpers                                                */
        /* ------------------------------------------------------------------ */

        function trunc( str, n ) {
                str = String( str || '' );
                return str.length > n ? str.slice( 0, n ) + '…' : str;
        }

        function classNames() {
                return Array.prototype.filter.call( arguments, Boolean ).join( ' ' );
        }

        /* ------------------------------------------------------------------ */
        /* Media preview (single + array share the thumbnail loader)           */
        /* ------------------------------------------------------------------ */

        function MediaThumb( props ) {
                var id = props.id;
                var size = props.size || 'medium';

                var state = useState( { src: '', loading: true } );
                var data = state[ 0 ];
                var setData = state[ 1 ];

                useEffect( function () {
                        if ( ! id ) {
                                return;
                        }
                        wp.apiFetch( { path: '/wp/v2/media/' + parseInt( id, 10 ) } )
                                .then( function ( media ) {
                                        var src = '';
                                        try {
                                                src = media.media_details.sizes[ size ].source_url;
                                        } catch ( e ) {}
                                        if ( ! src ) {
                                                src = media.source_url || '';
                                        }
                                        setData( { src: src, loading: false } );
                                } )
                                .catch( function () {
                                        setData( { src: '', loading: false } );
                                } );
                }, [ id ] );

                if ( ! id ) {
                        return null;
                }
                if ( data.loading ) {
                        return el( Spinner );
                }
                if ( ! data.src ) {
                        return el( 'span', { className: 'dzb-media-missing' }, '#' + id );
                }
                return el( 'img', {
                        className: props.className || 'dzb-media-thumb',
                        src: data.src,
                        alt: ''
                } );
        }

        /* ------------------------------------------------------------------ */
        /* Field → control mapping                                             */
        /* ------------------------------------------------------------------ */

        function fieldControl( field, attributes, setAttributes, keyPrefix ) {
                var value = attributes[ field.attr ];
                var setValue = function ( v ) {
                        var patch = {};
                        patch[ field.attr ] = v;
                        setAttributes( patch );
                };
                var key = ( keyPrefix || '' ) + field.attr;

                switch ( field.type ) {
                        case 'text':
                                return el( TextControl, {
                                        key: key,
                                        label: field.label,
                                        value: value,
                                        placeholder: field.placeholder,
                                        help: field.help,
                                        onChange: setValue
                                } );

                        case 'textarea':
                                return el( TextareaControl, {
                                        key: key,
                                        label: field.label,
                                        value: value,
                                        placeholder: field.placeholder,
                                        help: field.help,
                                        rows: field.rows || 3,
                                        onChange: setValue
                                } );

                        case 'range':
                                return el( RangeControl, {
                                        key: key,
                                        label: field.label,
                                        value: typeof value === 'number' ? value : parseInt( value, 10 ) || field.min || 0,
                                        min: field.min,
                                        max: field.max,
                                        allowReset: true,
                                        onChange: setValue
                                } );

                        case 'select':
                                return el( SelectControl, {
                                        key: key,
                                        label: field.label,
                                        value: value,
                                        options: ( field.options || [] ).map( function ( o ) {
                                                return { value: String( o.value ), label: o.label };
                                        } ),
                                        help: field.help,
                                        onChange: setValue
                                } );

                        case 'toggle':
                                return el( ToggleControl, {
                                        key: key,
                                        label: field.label,
                                        help: field.help,
                                        checked: !! value,
                                        onChange: setValue
                                } );

                        case 'media':
                                return el(
                                        'div',
                                        { key: key, className: 'dzb-field dzb-field-media' },
                                        el( 'p', { className: 'dzb-field__label' }, field.label ),
                                        field.help ? el( 'p', { className: 'dzb-field__help' }, field.help ) : null,
                                        el(
                                                MediaUploadCheck,
                                                null,
                                                el(
                                                        'div',
                                                        { className: 'dzb-media-row' },
                                                        value ? el( MediaThumb, { id: value, size: 'medium' } ) : null,
                                                        el(
                                                                MediaUpload,
                                                                {
                                                                        onSelect: function ( media ) {
                                                                                setValue( media.id );
                                                                        },
                                                                        allowedTypes: [ 'image' ],
                                                                        render: function ( obj ) {
                                                                                return el(
                                                                                        'div',
                                                                                        { className: 'dzb-media-row__buttons' },
                                                                                        el( Button, { isSecondary: true, isSmall: true, onClick: obj.open },
                                                                                                value ? __( 'Replace', 'dazhen-blocks' ) : __( 'Select image', 'dazhen-blocks' ) ),
                                                                                        value ? el( Button, { isDestructive: true, isSmall: true, isLink: true, onClick: function () { setValue( 0 ); } },
                                                                                                __( 'Remove', 'dazhen-blocks' ) ) : null
                                                                                );
                                                                        }
                                                                }
                                                        )
                                                )
                                        )
                                );

                        case 'media-array':
                                return el(
                                        'div',
                                        { key: key, className: 'dzb-field dzb-field-media-array' },
                                        el( 'p', { className: 'dzb-field__label' }, field.label ),
                                        field.help ? el( 'p', { className: 'dzb-field__help' }, field.help ) : null,
                                        el(
                                                MediaUploadCheck,
                                                null,
                                                el(
                                                        'div',
                                                        { className: 'dzb-media-array' },
                                                        ( Array.isArray( value ) ? value : [] ).map( function ( id, i ) {
                                                                return el(
                                                                        'span',
                                                                        { key: 'img-' + i, className: 'dzb-media-array__item' },
                                                                        el( MediaThumb, { id: id, size: 'thumbnail' } ),
                                                                        el(
                                                                                Button,
                                                                                {
                                                                                        className: 'dzb-media-array__remove',
                                                                                        isSmall: true,
                                                                                        isDestructive: true,
                                                                                        icon: 'no-alt',
                                                                                        label: __( 'Remove image', 'dazhen-blocks' ),
                                                                                        onClick: function () {
                                                                                                var next = ( Array.isArray( value ) ? value : [] ).slice();
                                                                                                next.splice( i, 1 );
                                                                                                setValue( next );
                                                                                        }
                                                                                }
                                                                        )
                                                                );
                                                        } ),
                                                        el(
                                                                MediaUpload,
                                                                {
                                                                        gallery: true,
                                                                        multiple: true,
                                                                        allowedTypes: [ 'image' ],
                                                                        onSelect: function ( media ) {
                                                                                var next = ( Array.isArray( value ) ? value : [] ).slice();
                                                                                ( Array.isArray( media ) ? media : [ media ] ).forEach( function ( m ) {
                                                                                        if ( next.indexOf( m.id ) === -1 ) {
                                                                                                next.push( m.id );
                                                                                        }
                                                                                } );
                                                                                setValue( next );
                                                                        },
                                                                        render: function ( obj ) {
                                                                                return el( Button, { isSecondary: true, isSmall: true, onClick: obj.open },
                                                                                        __( 'Add images', 'dazhen-blocks' ) );
                                                                        }
                                                                }
                                                        ),
                                                        ( Array.isArray( value ) && value.length ) ? el( Button, {
                                                                isDestructive: true, isSmall: true, isLink: true,
                                                                onClick: function () { setValue( [] ); }
                                                        }, __( 'Clear all', 'dazhen-blocks' ) ) : null
                                                )
                                        )
                                );

                        case 'repeater':
                                return el( RepeaterControl, {
                                        key: key,
                                        field: field,
                                        value: value,
                                        onChange: setValue
                                } );
                }
                return null;
        }

        /* ------------------------------------------------------------------ */
        /* Repeater control                                                    */
        /* ------------------------------------------------------------------ */

        function RepeaterControl( props ) {
                var field = props.field;
                var items = Array.isArray( props.value ) ? props.value : [];
                var openState = useState( 0 );
                var open = openState[ 0 ];
                var setOpen = openState[ 1 ];

                function commit( next ) {
                        props.onChange( next.filter( function ( it ) { return it !== null; } ) );
                }

                function addItem() {
                        var next = items.slice();
                        next.push( Object.assign( {}, field.defaults ) );
                        commit( next );
                        setOpen( next.length - 1 );
                }

                function updateItem( i, patch ) {
                        var next = items.slice();
                        next[ i ] = Object.assign( {}, next[ i ], patch );
                        commit( next );
                }

                function removeItem( i ) {
                        var next = items.slice();
                        next.splice( i, 1 );
                        commit( next );
                        if ( open >= next.length ) {
                                setOpen( Math.max( 0, next.length - 1 ) );
                        }
                }

                function moveItem( i, dir ) {
                        var j = i + dir;
                        if ( j < 0 || j >= items.length ) {
                                return;
                        }
                        var next = items.slice();
                        var tmp = next[ i ];
                        next[ i ] = next[ j ];
                        next[ j ] = tmp;
                        commit( next );
                        setOpen( j );
                }

                var firstTextKey = null;
                ( field.fields || [] ).some( function ( f ) {
                        if ( f.type === 'text' || f.type === 'textarea' ) {
                                firstTextKey = f.attr;
                                return true;
                        }
                        return false;
                } );

                return el(
                        'div',
                        { className: 'dzb-repeater' },
                        el( 'p', { className: 'dzb-field__label' }, field.label ),
                        items.length === 0 ? el( 'p', { className: 'dzb-repeater__empty' },
                                __( 'Nothing here yet — add the first one below.', 'dazhen-blocks' ) ) : null,
                        items.map( function ( item, i ) {
                                var isOpen = open === i;
                                var summary = firstTextKey ? trunc( item[ firstTextKey ], 24 ) : '';
                                return el(
                                        'div',
                                        { key: 'rep-' + i, className: classNames( 'dzb-repeater__item', isOpen && 'is-open' ) },
                                        el(
                                                'div',
                                                { className: 'dzb-repeater__head' },
                                                el(
                                                        Button,
                                                        {
                                                                className: 'dzb-repeater__toggle',
                                                                isTertiary: true,
                                                                onClick: function () { setOpen( isOpen ? -1 : i ); }
                                                        },
                                                        el( 'strong', null,
                                                                sprintf( __( '%s #%d', 'dazhen-blocks' ), field.itemLabel || __( 'Item', 'dazhen-blocks' ), i + 1 ) ),
                                                        summary ? el( 'span', { className: 'dzb-repeater__summary' }, summary ) : null
                                                ),
                                                el(
                                                        'span',
                                                        { className: 'dzb-repeater__ops' },
                                                        el( Button, { isSmall: true, isTertiary: true, disabled: i === 0, icon: 'arrow-up-alt', label: __( 'Move up', 'dazhen-blocks' ), onClick: function () { moveItem( i, -1 ); } } ),
                                                        el( Button, { isSmall: true, isTertiary: true, disabled: i === items.length - 1, icon: 'arrow-down-alt', label: __( 'Move down', 'dazhen-blocks' ), onClick: function () { moveItem( i, 1 ); } } ),
                                                        el( Button, { isSmall: true, isTertiary: true, isDestructive: true, icon: 'no-alt', label: __( 'Remove', 'dazhen-blocks' ), onClick: function () { removeItem( i ); } } )
                                                )
                                        ),
                                        isOpen ? el(
                                                'div',
                                                { className: 'dzb-repeater__body' },
                                                ( field.fields || [] ).map( function ( f ) {
                                                        return fieldControl( f, item, function ( patch ) { updateItem( i, patch ); }, 'i' + i + '-' );
                                                } )
                                        ) : null
                                );
                        } ),
                        el( Button, { isPrimary: true, isSmall: true, onClick: addItem },
                                field.addLabel || __( 'Add item', 'dazhen-blocks' ) )
                );
        }

        /* ------------------------------------------------------------------ */
        /* The generic Edit component                                          */
        /* ------------------------------------------------------------------ */

        function makeEdit( spec ) {
                return function ( props ) {
                        var blockProps = useBlockProps ? useBlockProps( { className: 'dzb-wrap' } ) : { className: 'dzb-wrap' };

                        var inspector = el(
                                InspectorControls,
                                null,
                                ( spec.panels || [] ).map( function ( panel, pi ) {
                                        return el(
                                                PanelBody,
                                                {
                                                        key: 'panel-' + pi,
                                                        title: panel.title,
                                                        initialOpen: !! panel.initialOpen
                                                },
                                                ( panel.fields || [] ).map( function ( field, fi ) {
                                                        return fieldControl( field, props.attributes, props.setAttributes, 'p' + pi + 'f' + fi + '-' );
                                                } )
                                        );
                                } )
                        );

                        if ( spec.inner ) {
                                return el(
                                        Fragment,
                                        null,
                                        inspector,
                                        el( 'div', blockProps, el( InnerBlocks ) )
                                );
                        }

                        return el(
                                Fragment,
                                null,
                                inspector,
                                el(
                                        'div',
                                        Object.assign( {}, blockProps, { className: classNames( blockProps.className, 'dzb-block-preview' ) } ),
                                        ServerSideRender
                                                ? el( ServerSideRender, { block: spec.name, attributes: props.attributes } )
                                                : el( 'p', null, spec.title )
                                )
                        );
                };
        }

        /* ------------------------------------------------------------------ */
        /* Register every spec                                                 */
        /* ------------------------------------------------------------------ */

        ( window.DazhenBlocksSpecs.blocks || [] ).forEach( function ( spec ) {
                registerBlockType( spec.name, {
                        apiVersion: 3,
                        title: spec.title,
                        description: spec.description,
                        icon: spec.icon,
                        keywords: spec.keywords || [],
                        category: spec.category,
                        supports: spec.supports || { html: false },
                        attributes: spec.attributes,
                        edit: makeEdit( spec ),
                        save: spec.inner
                                ? function () { return el( InnerBlocks.Content ); }
                                : function () { return null; }
                } );
        } );

        if ( wp.blocks.registerBlockCollection ) {
                wp.blocks.registerBlockCollection( 'dazhen-blocks', {
                        title: __( 'Dazhen Blocks · 大振内容块', 'dazhen-blocks' ),
                        icon: 'screenoptions'
                } );
        }

        /* ------------------------------------------------------------------ */
        /* "Dazhen layouts" sidebar — one-click fill for pages                 */
        /* ------------------------------------------------------------------ */

        ( function layoutsSidebar() {
                var layouts = window.DazhenBlocksLayouts;
                if ( ! layouts || ! layouts.isPage || ! wp.plugins || ! wp.plugins.registerPlugin ) {
                        return;
                }
                /*
                 * WP 6.6+ unified editor: the live slots live in wp.editor; the
                 * wp.editPost copies are deprecated shims. Prefer the modern ones.
                 */
                var sidebarLib = ( wp.editor && wp.editor.PluginSidebar ) ? wp.editor : ( wp.editPost || blockEditor );
                var PluginSidebar = sidebarLib.PluginSidebar || blockEditor.PluginSidebar;
                var PluginSidebarMoreMenuItem = sidebarLib.PluginSidebarMoreMenuItem || blockEditor.PluginSidebarMoreMenuItem;
                if ( ! PluginSidebar ) {
                        return;
                }

                function LayoutsPanel() {
                        var state = useState( ( layouts.options && layouts.options.length ) ? layouts.options[ 0 ].value : 'starter' );
                        var layout = state[ 0 ];
                        var setLayout = state[ 1 ];

                        var postId = parseInt( layouts.postId, 10 ) || 0;
                        var url = layouts.fillUrl +
                                '?action=dazhen_fill_page&page_ID=' + postId +
                                '&layout=' + encodeURIComponent( layout ) +
                                '&_wpnonce=' + encodeURIComponent( layouts.nonce );

                        var hasContent = !!( wp.data && wp.data.select && wp.data.select( 'core/editor' ) &&
                                wp.data.select( 'core/editor' ).getEditedPostAttribute &&
                                String( wp.data.select( 'core/editor' ).getEditedPostAttribute( 'content' ) || '' ).trim() );

                        return el(
                                Fragment,
                                null,
                                el( PluginSidebarMoreMenuItem, {
                                        name: 'dazhen-layouts',
                                        icon: 'screenoptions'
                                }, __( 'Dazhen layouts · 大振版式', 'dazhen-blocks' ) ),
                                el(
                                        PluginSidebar,
                                        {
                                                name: 'dazhen-layouts',
                                                title: __( 'Dazhen layouts · 大振版式', 'dazhen-blocks' ),
                                                icon: 'screenoptions'
                                        },
                                        el(
                                                PanelBody,
                                                { initialOpen: true },
                                                el( 'p', null, __( 'Pick a ready-made page made of Dazhen blocks, then fill this page with it. Everything stays editable afterwards.', 'dazhen-blocks' ) ),
                                                el( SelectControl, {
                                                        label: __( 'Layout', 'dazhen-blocks' ),
                                                        value: layout,
                                                        options: layouts.options || [],
                                                        onChange: setLayout
                                                } ),
                                                el( ExternalLink, {
                                                        className: 'components-button is-primary dzb-fill-link',
                                                        href: '#',
                                                        onClick: function ( e ) {
                                                                if ( hasContent && ! window.confirm( __( 'This page already has content. Filling a layout will REPLACE it. Continue?', 'dazhen-blocks' ) ) ) {
                                                                        e.preventDefault();
                                                                        return;
                                                                }
                                                                /* Unsaved editor changes would be lost — warn when dirty. */
                                                                if ( wp.data.select( 'core/editor' ).isEditedPostDirty && wp.data.select( 'core/editor' ).isEditedPostDirty() ) {
                                                                        if ( ! window.confirm( __( 'You have unsaved changes. They will be lost when the page is filled. Continue?', 'dazhen-blocks' ) ) ) {
                                                                                e.preventDefault();
                                                                                return;
                                                                        }
                                                                }
                                                                window.location.href = url;
                                                                e.preventDefault();
                                                        }
                                                }, hasContent
                                                        ? __( 'Replace this page with the layout', 'dazhen-blocks' )
                                                        : __( 'Fill this page with the layout', 'dazhen-blocks' ) ),
                                                el( 'p', { className: 'dzb-fill-note' },
                                                        __( 'You will come back to this page in the editor, now full of blocks.', 'dazhen-blocks' ) )
                                        )
                                )
                        );
                }

                wp.plugins.registerPlugin( 'dazhen-layouts', {
                        icon: 'screenoptions',
                        render: LayoutsPanel
                } );
        }() );

} )( window.wp );
