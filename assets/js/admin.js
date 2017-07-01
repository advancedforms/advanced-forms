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
		
	});
	
	
	
})(jQuery);