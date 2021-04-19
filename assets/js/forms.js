var af;

(function($) {
  // Ensure acf-input.js is available
  if (typeof acf === 'undefined') {
    console.error( 'acf-input.js not found. AF requires ACF to work.' );
    return;
  }

  af = {
    forms: {},

    setup_form: function( $form ) {
      var key = $form.attr( 'data-key' );
      var form = {
        $el: $form,
        key: key,
      }

      // Initialize pages if this is a multi-page form
      this.pages.initialize( form );

      this.ajax.initialize( form );

      this.forms[ key ] = form;

      acf.doAction( 'af/form/setup', form );
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
        self.changePage( form.current_page + 1, form );
      });
    },

    // Navigate to previous page
    previousPage: function( form ) {
      if ( this.isFirstPage( form ) ) return;

      this.changePage( form.current_page - 1, form );
    },

    // Navigate to specific page
    navigateToPage: function( page, form ) {
      if ( page < 0 || page > form.max_page ) return;

      var self = this;

      this.validatePage( form, form.current_page, function() {
        self.changePage( page, form );
      });
    },

    changePage: function( page, form ) {
      var previousPage = form.current_page;
      form.current_page = page;

      // Update max page if we have exceeded the previous max
      if ( form.max_page <= form.current_page ) {
        form.max_page = form.current_page
      }

      this.refresh( form );

      acf.doAction( 'af/form/page_changed', page, previousPage, form );
    },

    isFirstPage: function( form ) {
      return form.current_page == 0;
    },

    isLastPage: function( form ) {
      return form.current_page == form.pages.length - 1;
    },

    validatePage: function( form, page_index, callback ) {
      var page = form.pages[ page_index ];

      // Trigger browser validation manually.
      // This is normally triggered automatically when a form is submitted.
      page.$fields.find( 'input' ).each(function() {
        this.checkValidity();
      });

      // Helper function to apply a function on pages except the current one.
      var forEachOtherPage = function(f) {
        for (i = 0; i < form.pages.length; i++) {
          if ( i == page_index ) {
            continue;
          }

          var otherPage = form.pages[ i ];
          f(otherPage);
        }
      }

      // Temporarily remove all other fields outside the current page.
      // This way we can use the regular ACF validation on the entire form.
      forEachOtherPage(function(otherPage) {
        otherPage.$fields.detach();
      });

      // Put back the previously removed fields.
      var putFieldsBack = function() {
        forEachOtherPage(function(otherPage) {
          otherPage.$fields.insertAfter( otherPage.$field );
        });
      }

      acf.validation.fetch({
        form: form.$el,
        lock: false,
        reset: true,
        success: function() {
          putFieldsBack();
          callback();
        },
        failure: function() {
          // We can't use the "complete" callback to put fields back as it's triggered after "success".
          putFieldsBack();
        }
      })
    }
  };

  af.ajax = {
    initialize: function( form ) {
      var self = this;

      // Check if form has data-ajax attribute
      if ( ! form.$el.is( '[data-ajax]' ) ) {
        return;
      }

      form.$el.on('submit', function( e ) {
        e.preventDefault();
      
        // Validate form 
        acf.validation.fetch({
          form: form.$el,
          lock: false,
          reset: true,
          success: function() {
            self.sendSubmission( form );
          },
        });
      });
    },

    sendSubmission: function( form ) {
      acf.validation.lockForm( form.$el );

      var formData = new FormData( form.$el.get(0) );
      formData.append( 'action', 'af_submission' );

      // Send AJAX request with action "af_submission"
      $.ajax({
        url: acf.get( 'ajaxurl' ),
        data: formData,
        processData: false,
        contentType: false,
        type: 'post',
        success: this.onSuccess( form ),
        error: this.onError( form ),
        complete: function() {
          acf.validation.unlockForm( form.$el );
        }
      });
    },

    onSuccess: function( form ) {
      return function( resp ) {
        var data = resp.data;

        acf.doAction( 'af/form/ajax/submission', data, form );
      
        switch ( data.type ) {
          case 'success_message':
            // Replace form fields with the success message
            var $success_message = $( data.success_message );
            var $fields = form.$el.find( '.af-fields' );
            $fields.replaceWith( $success_message );
            break;
          case 'redirect':
            // Redirect user to another URL
            window.location.href = data.redirect_url;
            break;
        }
      };
    },

    onError: function( form ) {
      return function( resp ) {
        var validator = form.$el.data( 'acf' );
        var errors = resp.responseJSON.data.errors;
      
        // Add errors to form
        validator.addErrors( errors );
        validator.showErrors();
      }
    },
  };

  // Set up all forms on page
  $(document).ready(function() {
    $( '.af-form' ).each(function() {
      af.setup_form( $(this) );
    });
  });

})(jQuery);