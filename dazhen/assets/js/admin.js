/**
 * Dazhen theme — admin field engine JS (repeater + media multi picker).
 */

( function () {
	'use strict';

	/* ---------- Repeater: add / remove rows ---------- */
	document.addEventListener( 'click', function ( event ) {
		var addBtn = event.target.closest( '[data-dz-repeater-add]' );
		if ( addBtn ) {
			var wrap = document.querySelector( '[data-dz-repeater="' + addBtn.dataset.dzRepeaterAdd + '"]' );
			var tpl = document.getElementById( addBtn.dataset.dzRepeaterTpl );
			if ( wrap && tpl ) {
				var rows = wrap.querySelector( '.dazhen-repeater__rows' );
				var clone = tpl.content.firstElementChild.cloneNode( true );
				clone.innerHTML = clone.innerHTML.replace( /__INDEX__/g, String( Date.now() ) );
				rows.appendChild( clone );
			}
			return;
		}

		var removeBtn = event.target.closest( '[data-dz-repeater-remove]' );
		if ( removeBtn ) {
			removeBtn.closest( '.dazhen-repeater__row' ).remove();
		}
	} );

	/* ---------- Media multi picker (WP media modal) ---------- */
	document.addEventListener( 'click', function ( event ) {
		var openBtn = event.target.closest( '[data-dz-media-open]' );
		if ( ! openBtn || ! window.wp || ! window.wp.media ) {
			return;
		}

		var wrap = openBtn.closest( '[data-dz-media-multi]' );
		var hidden = wrap.querySelector( 'input[type="hidden"]' );
		var thumbs = wrap.querySelector( '.dazhen-media-multi__thumbs' );

		var frame = window.wp.media( {
			title: 'Select images',
			button: { text: 'Use selected images' },
			library: { type: 'image' },
			multiple: 'add'
		} );

		frame.on( 'select', function () {
			var selection = frame.state().get( 'selection' );
			var ids = [];
			thumbs.innerHTML = '';
			selection.each( function ( attachment ) {
				ids.push( attachment.get( 'id' ) );
				var thumb = attachment.get( 'sizes' ) && attachment.get( 'sizes' ).thumbnail
					? attachment.get( 'sizes' ).thumbnail.url
					: attachment.get( 'url' );
				var img = document.createElement( 'img' );
				img.src = thumb;
				img.className = 'dazhen-media-multi__thumb';
				thumbs.appendChild( img );
			} );
			hidden.value = ids.join( ',' );
		} );

		frame.open();
	} );
} )();
