(function($) {
	
	/**
	 * Insert text in input at cursor position
	 *
	 * Reference: http://stackoverflow.com/questions/1064089/inserting-a-text-where-cursor-is-using-javascript-jquery
	 *
	 */
	function insert_at_caret(input, text) {
		var txtarea = input;
		if (!txtarea) { return; }
		
		var scrollPos = txtarea.scrollTop;
		var strPos = 0;
		var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
			"ff" : (document.selection ? "ie" : false ) );
		if (br == "ie") {
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart ('character', -txtarea.value.length);
			strPos = range.text.length;
		} else if (br == "ff") {
			strPos = txtarea.selectionStart;
		}
		
		var front = (txtarea.value).substring(0, strPos);
		var back = (txtarea.value).substring(strPos, txtarea.value.length);
		txtarea.value = front + text + back;
		strPos = strPos + text.length;
		if (br == "ie") {
			txtarea.focus();
			var ieRange = document.selection.createRange();
			ieRange.moveStart ('character', -txtarea.value.length);
			ieRange.moveStart ('character', strPos);
			ieRange.moveEnd ('character', 0);
			ieRange.select();
		} else if (br == "ff") {
			txtarea.selectionStart = strPos;
			txtarea.selectionEnd = strPos;
			txtarea.focus();
		}
		
		txtarea.scrollTop = scrollPos;
	}
	
	
	$(document).ready(function() {

		/**
		 * Add has-field-inserter class to all fields which has a floating inserter button
		 */
		$( '.af-field-dropdown.floating' )
			.parents( '.acf-input' )
			.find( 'input' )
			.addClass( 'has-field-inserter' );
		
		/**
		 * Handles the field insert drop downs, supports both regular text fields and WYSIWYG fields
		 */
		$(document).on( 'click', '.af-dropdown .field-option', function(e) {
			
			e.stopPropagation();
			
			var $option = $(this);
			
			var value = $option.data('insert-value');
			
			var $editor = $option.parents('.acf-field').first().find('.wp-editor-area');
			
			// Check if we should insert into WYSIWYG field or a regular field
			if ( $editor.length > 0 ) {
				
				// WYSIWYG field
				var editor = tinymce.editors[ $editor.attr('id') ];
				editor.editorCommands.execCommand( 'mceInsertContent', false, value );
				
			} else {
				
				// Regular field
				var $input = $option.parents('.acf-input').first().find('input[type=text]');
				insert_at_caret( $input.get(0), value );
				
			}

			// Close dropdown after selection
			$( '.af-field-dropdown' ).removeClass( 'open' );
			
		});
		
		// Close dropdowns when clicking anywhere
		$(document).on( 'click', function() {
			$( '.af-field-dropdown' ).removeClass( 'open' );
		});
		
		// Toggle dropdown
		$(document).on( 'click', '.af-field-dropdown', function(e) {
			
			// Stop propagation to not trigger click event on document (which closes all dropdowns)
			e.stopPropagation();
			
			var $this = $( this );
			
			if ( $this.hasClass( 'open' ) ) {
				$this.removeClass( 'open' );
			} else {
				$( '.af-field-dropdown' ).removeClass( 'open' );
				$this.addClass( 'open' );
			}
			
		});
		
		
		/**
		 * Field picker
		 */
		$( '.acf-field[data-type="field_picker"]' ).each(function() {
			var $field = $( this );
			
			var update_field = function() {
				var $input = $field.find( '.format-input' );
				
				if ( $field.find( 'select' ).val() == 'custom' ) {
					$input.show();
				} else {
					$input.hide();
				}
			};
			
			$field.find( 'select' ).on( 'change', update_field );
			
			update_field();
				
		});


		/**
		 * Handles the Zapier test submission button.
		 * This is part of the pro plugin but included here to avoid another JS file to load.
		 */
		$( '.zapier-test-button' ).click(function() {
			var $button = $( this );
			var $input = $button.parent().find( 'input' );
			var form_key = $button.data( 'form-key' );

			$button.attr( 'disabled', 'disabled' );

			// Fix width before inserting spinner
			var width = $button.outerWidth();
			$button.css( 'width', width + 'px' );

			// Remove text and insert spinner
			var $spinner = $( '<span class="spinner is-active">' );
			$button.empty().append( $spinner );

			// Perform AJAX request
			var data = {
				action: 'test_zapier_submission',
				form_key: form_key,
				webhook_url: $input.val()
			};
			$.post(ajaxurl, data, function() {
				// Display sent message for a few seconds before reseting button
				$button.html( 'Sent!' );

				setTimeout(function() {
					$button.removeAttr( 'disabled' );
					$button.css( 'width', '' );
					$button.html( $button.data( 'text' ) );
				}, 3000)
			});
		});
		

		/**
		 * Inserts the sidebar next to the forms list
		 */
		var $list = $( '.post-type-af_form #posts-filter' );
		$list.wrap('<div class="acf-columns-2" />');
		$list.addClass( 'acf-column-1' );
		$list.after( $('#af-sidebar-template').html() );


		/**
		 * Form export with code copying
		 */
		var $form_export = $( '.af-form-export' );
		$form_export.find( '.copy-button' ).click(function() {
			var $button = $( this );
			var code = $form_export.find( '.export-code' ).text();
			
			// Copy text to clipboard with a temporary textarea
			var $temp = $( '<textarea>' );
  		$( 'body' ).append( $temp );
  		$temp.val( code ).select();
  		document.execCommand( 'copy' );
  		$temp.remove();

  		// Show confirmation message in button
  		var previous_text = $button.text();
  		$button.text( $button.data( 'copied-text' ) );
  		$button.attr( 'disabled', true );

  		// Reset button after 2 seconds
  		setTimeout(function() {
  			$button.text( previous_text );
  			$button.attr( 'disabled', null );
  		}, 1000);
		});

	});
	
	
	
})(jQuery);