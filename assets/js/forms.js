var af;

(function($) {

  // Ensure acf-input.js is available
  if (typeof acf === 'undefined') {
    console.error( 'acf-input.js not found. AF requires ACF to work.' );
    return;
  }

  af = {

    setup_form: function( $form ) {
      var form = {
        $el: $form,
      }

      this.pages.initialize( form );
    },

  };

  af.pages = {

    initialize: function( form ) {
      var self = this;
      $page_fields = form.$el.find( '.acf-field-page' );

      if ( $page_fields.exists() ) {
        form.pages = [];
        form.current_page = 0;
        form.max_page = 0;
        form.show_numbering = true;

        // Add wrapper for page links
        form.$page_wrap = $( '<div class="af-page-wrap">' );
        form.$page_wrap.insertBefore( $page_fields.first() );

        // Create next/previous buttons
        form.$previous_button = $page_fields.first().find( '.af-previous-button' );
        form.$next_button = $page_fields.first().find( '.af-next-button' );

        // Read show numbering setting
        form.show_numbering = 'true' === $page_fields.first().find( '.af-page-button' ).attr( 'data-show-numbering' );

        form.$previous_button.click(function(e) {
          e.preventDefault();
          self.previousPage( form );
        });

        form.$next_button.click(function(e) {
          e.preventDefault();
          self.nextPage( form );
        });

        var submit_wrap = form.$el.find( '.af-submit' );
        submit_wrap.prepend( form.$next_button );
        submit_wrap.prepend( form.$previous_button );
        form.$submit_button = submit_wrap.find( '.af-submit-button' );

        $page_fields.each(function(i, el) {
          var $page_field = $( el );

          // Create navigation button
          var $page_button = $page_field.find( '.af-page-button' ).attr( 'data-index', i );
          $page_button.click(function(e) {
            e.preventDefault();
            af.pages.navigateToPage( i, form );
          });

          // Add index indicator
          if ( form.show_numbering ) {
            $index = $( '<span class="index">' ).html( i + 1 );
            $page_button.prepend( $index );
          }

          form.$page_wrap.append( $page_button );

          $page_field.hide();

          var $fields = $page_field.nextUntil( '.acf-field-page', '.acf-field' );

          form.pages.push({
            $field: $page_field,
            $fields: $fields,
            $button: $page_button,
          });
        })

        this.refresh( form );
      }
    },

    refresh: function( form ) {
      // Refresh navigation and hide/show fields
      $.each(form.pages, function(i, page) {
        var isCurrentPage = (i == form.current_page);

        page.$button.toggleClass( 'enabled', i <= form.max_page );
        page.$button.toggleClass( 'current', isCurrentPage );

        // Hide/show fields
        page.$fields.each(function() {
          $(this).toggle(isCurrentPage)
        });
      });

      var isFirst = this.isFirstPage( form );
      var isLast = this.isLastPage( form );

      // Refresh next and previous buttons
      form.$previous_button.attr( 'disabled', isFirst ? true : null );
      form.$next_button.toggle( !isLast );

      // Show submit button on last step
      form.$submit_button.toggle(isLast);
    },

    // Navigate to next page
    nextPage: function( form ) {
      if ( this.isLastPage( form ) ) return;
      var self = this;

      this.validatePage( form, form.current_page, function() {
        form.current_page++;
        if ( form.max_page <= form.current_page ) {
          form.max_page = form.current_page
        }

        self.refresh( form );
      });
    },

    // Navigate to previous page
    previousPage: function( form ) {
      if ( this.isFirstPage( form ) ) return;

      form.current_page--;
      this.refresh( form );
    },

    // Navigate to specific page
    navigateToPage: function( page, form ) {
      if ( page < 0 || page > form.max_page ) return;

      var self = this;

      this.validatePage( form, form.current_page, function() {
        form.current_page = page;
        self.refresh( form );
      });
    },

    isFirstPage: function( form ) {
      return form.current_page == 0;
    },

    isLastPage: function( form ) {
      return form.current_page == form.pages.length - 1;
    },

    validatePage: function( form, page_index, callback ) {
      var page = form.pages[ page_index ];

      // Create temporary div to hold fake form (for serialization)
      var $temp = $( '<div>' );
      $temp.append( form.$el.find( '#acf-form-data' ).clone() );
      $temp.append( form.$el.find( '.acf-hidden' ).clone() );
      
      // Detach page fields and insert into temporary div.
      // Detach is necessary as a regular clone won't work with select2.
      var $fields = page.$fields.detach();
      $temp.append( $fields );

      // Serialize data from ephemeral form
      var data = acf.serialize( $temp );

      // Put page fields back into the DOM
      $fields.detach().insertAfter( page.$field );

      
      data.action = 'acf/validate_save_post';
      data = acf.prepare_for_ajax( data );

      /**
       * With ACF 5.7 large parts of the JS code base was redone.
       * With these changes the custom page validation became a lot simpler to implement.
       * The old implementation remains for compatibility.
       */

      // Check if ACF 5.7 or later (the lockForm function was introduced then)
      if ( acf.validation.lockForm !== undefined ) {
        var dataFilter = function() { return data; };

        // Temporary override of prepare_for_ajax to inject custom data
        acf.addFilter( 'prepare_for_ajax', dataFilter );

        acf.validation.fetch({
          form: form.$el,
          lock: false,
          reset: true,
          success: function() {
            callback();
          },
        })

        acf.removeFilter( 'prepare_for_ajax', dataFilter )
      } else {
        // Lock form and show spinner
        acf.validation.toggle( form.$el, 'lock' );

        // vars
        var data = acf.serialize( page.$fields.clone() );
          
        // append AJAX action   
        data.action = 'acf/validate_save_post';
        
        // prepare
        data = acf.prepare_for_ajax(data);

        // ajax
        $.ajax({
          url: acf.get('ajaxurl'),
          data: data,
          type: 'post',
          dataType: 'json',
          success: function( json ){

            // Unlock form, hiding spinner
            acf.validation.toggle( form.$el, 'unlock' );
            
            // bail early if not json success
            if( !acf.is_ajax_success(json) ) {
              return;
            }

            acf.validation.fetch_success( form.$el, json.data );            
          },
          complete: function(){
            if ( acf.validation.valid ) {
              // remove previous error message
              acf.remove_el( form.$el.children('.acf-error-message') );

              // Run callback (which will proceed to the next page)
              callback()
            }
          }
        });
      }
    }
  };

  // Set up all forms on page
  $( '.af-form' ).each(function() {
    af.setup_form( $(this) );
  });

})(jQuery);