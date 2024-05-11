=== Advanced Forms for ACF ===
Contributors: philkurth, fabianlindfors
Tags: acf, advanced custom fields, acf form, form builder, contact form, frontend editing
Stable tag: 1.9.3.4
Requires at least: 5.4.0
Tested up to: 6.5.3
Requires PHP: 7.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Flexible and developer-friendly forms using the power of Advanced Custom Fields

== Description ==

[Documentation](https://advancedforms.github.io) | [Purchase Pro](https://hookturn.io/downloads/advanced-forms-pro)

Advanced Forms is a WordPress plugin for creating front-end forms using [Advanced Custom Fields](https://advancedcustomfields.com). It supports all ACF field types, including repeaters and flexible content fields, and provides the same field editing interface you are already familiar with. *Advanced Forms requires ACF PRO v5.7 or later*.

- **Email notifications**: Configure an unlimited number of email notifications, including support for dynamic recipients and field includes.
- **AJAX submissions**: Use AJAX for a better user experience with faster submissions and no page reloads.
- **Entries**: Save form submissions as entries with all fields.
- **Spam protection**: Every form is protected against spam using a honeypot. If you need more sophisticated spam protection, Advanced Forms Pro includes support for reCAPTCHA.
- **Restrictions**: Place limits on your form using the built-in restrictions or [create your own](https://advancedforms.github.io/guides/advanced/adding-custom-restrictions/):
    + Limit the total number of submissions
    + Limit your form to only logged-in users
    + Limit the time when your form can be used
- **User-friendly UI**: Create forms either through the admin panel or programmatically for easy integration.
- **Gutenberg support**: Add forms to your site using Gutenberg blocks.
- **Developer-friendly**: Designed for developers with a large variety of hooks and helper functions and [comprehensive documentation](https://advancedforms.github.io).

= Pro =

On top of that, **Advanced Forms Pro** offers even more features for advanced use cases. You can purchase a license through [Hookturn](https://hookturn.io/downloads/advanced-forms-pro/) which can be used on an unlimited number of sites.

- **Priority support**: Get direct support with an average response time of 1-2 days.
- **Post editing**: Set up forms to create and edit posts. Configure the post title, content and status and automatically map your existing ACF fields.
- **User editing**: Register new users or let people edit their user profile with automatic mapping of your user fields.
- **Calculated fields**: Give your users immediate feedback as they fill out your form. Calculated fields update live with the values from other fields. Calculated fields are also [fully programmable](https://advancedforms.github.io/pro/configuration/using-calculated-fields/) for more complex calculations.
- **Slack**: Get a message in [Slack](https://slack.com) for each form submission, including all form data.
- **Mailchimp**: Create a form to sign users up for your [Mailchimp](https://mailchimp.com) mailing list.
- **Zapier**: Connect your form to thousands of third-party services using [Zapier](https://zapier.com).
- **Google reCAPTCHA**: Protect your forms against spam using an invisible captcha.

= Support =

If you need help, have a feature request, or think you've found a bug, don't hesitate to reach out. Either create a ticket on the [WordPress Support Forums](https://wordpress.org/support/plugin/advanced-forms/) or an issue on [Github](http://github.com/advancedforms/advanced-forms/issues).

For Pro users, please send an email to [support@hookturn.io](mailto:support@hookturn.io?subject=Advanced%20Forms) and we'll respond as fast as we can, most often within 1-2 days.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/advanced-forms` directory, or install the plugin through the WordPress plugins screen directly.
2. Make sure ACF PRO v5 is installed and activated.
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Read the [documentation](https://advancedforms.github.io) for instructions on how to create, configure, and display forms.

== Frequently Asked Questions ==

= Q: Does this plugin only work with ACF PRO v5.7 or later? =

Yes. Versions 5.6 or lower of ACF are not supported.

== Screenshots ==

1. Form edit page with form settings and a list of form fields
2. Example of entry automatically generated after form submission including all form fields
3. Example of location rules on an ACF field group

== Changelog ==

= [1.9.3.4] =

* Added the `af/form/submission/value` filter to allow modification of submission values before they are processed.

= [1.9.3.3] =

* Added capability and nonce checks to prevent unauthorized export of form JSON.

= [1.9.3.2] =

* Added the `af/field_group/before_field_group` action hook to support custom markup before field groups.
* Added the `af/field_group/after_field_group` action hook to support custom markup after field groups.

= [1.9.3] =

* Added the `af_render_content` private field type for internal use in form settings UI.
* Added a hotfix for ACF validation bug when using multiple forms on the one page.
* Added the `af/settings/enqueue_validation_hotfix` filter for disabling the validation hotfix.
* Fix deprecation notices related to admin page registration that occur in PHP 8.1.
* Fix deprecation notices related to dynamic property usage in PHP 8.2.

= [1.9.2] =

* Added condition to stop ACF file nonces being treated as fields on submission.
* Added condition to prevent email sending if recipient is false. `af/form/email/recipient` filter can now be used to prevent email sending.
* Added support for file fields with return type "URL" in merge tags.
* Fixed issue which could cause duplicate submissions.
* Added filter `af/form/entry/should_create` to enable dynamically stopping entries from being created.
* Added hidden label for honeypot field for accessibility compliance.
* Added `af/field/before_field_wrapper` action.
* Added `af/field/after_field_wrapper` action.
* Fixed issue where filter mode wouldn't work.

= 1.9.1 =

*Pro*

* Added filter `af/form/editing/query_param` to change the query parameter used for post editing. The default is `post`.
* Fixed warnings about undefined `$post` which could show up in the form settings.

= 1.9.0 =

* Added support for Gutenberg with the "Advanced Form" block.
* Added support for flexible fields in merge tags.
* Added "af/form/after" action triggered after a form has been rendered.
* Fixed issue where form couldn't be submitted multiple times when using AJAX and filter mode.

*Pro*

* Added support for configuring a form to create/edit posts and user at the same time.
* Added option to specify post to edit through a `post` query parameter. 

= 1.8.2 =

* Added support for excluding multiple fields by comma-separating field names in the `exclude_fields` argument on shortcodes.
* Added automatic hiding of form pages if the page has no fields.
* Added filter to remove the default HTML email template (`af/form/email/use_template`).
* Added filter to change the content type of en email (`af/form/email/content_type`).
* Improved support for duplicating forms with post duplication plugins.

*Pro*

* Fixed issue where users could accidentally submit a reCAPTCHA protected form with large uploads multiple times.
* Fixed issue where reCAPTCHA protected forms could conflict with other reCAPTCHA instances on the same page.

= 1.8.1 =

* Added optimization to only enqueue stylesheet when a form is displayed.
* Fixed issue where multi-page forms would briefly show unstyled elements during loading.

*Pro*

* Added support for merge tags in Slack messages.
* Fixed issue where reCAPTCHA wouldn't work with AJAX submissions.
* Fixed issue where email could be reset for a user when "Custom format" was selected for the email mapping.

= 1.8.0 =

This version drops support for ACF 5.6 and earlier. Make sure you're running ACF 5.7 or later before installing the update.

* Added support for submissions using AJAX. This enables forms to be submitted without a page reload. Activate it using the `ajax` argument: `[advanced_form form="KEY" ajax="1"]`.
* Fixed warning which would appear when having multiple versions of Advanced Forms activated at the same time.

*Pro*

* Added new restriction to only let users edit their own posts.

= 1.7.2 =

* Added support for nested group fields in emails and success messages.
* Added ability to remove fields by returning `false` from the `af/field/before_field` filter.
* Added filter to control view counter. Use `add_filter( 'af/form/view_counter_enabled', '__return_false' )` to disable the view counter.
* Fixed incorrect variations for the `af/field/before_render` filter.
* Fixed incorrect callback order for the `af/form/page_changed` action. Fields were temporarily removed from the form when the action was triggered.
* Fixed warning when including a non-existent field with a merge tag.
* Fixed sidebar formatting on the forms page in the admin panel.

*Pro*

* Fixed issue where post title and content could be cleared if their mapped fields were excluded from the form.
* Added support for custom format for email field in Mailchimp integration.

= 1.7.1 =

* Improved validation handling for multi-page forms. Validation and error messages should now work the same as for regular forms.
* Fixed issue where comma-separated email addresses wouldn't work.

*Pro*

* Fixed warning which sometimes was triggered by user editing forms.

= 1.7.0 =

*Pro*

* Added support for Google reCAPTCHA. Use an invisible captcha to protect your forms against spam without bothering users.
* Changed editing to default to create users if no `user` argument is set. `user="new"` is no longer needed.
* Changed "Map all fields" to never save fields that are used for user passwords. 

= 1.6.9 =

* Added filter `af/settings/cookie_name` to change cookie name used for submissions.
* Added server-side handling of file validation errors. Should prevent submission with invalid uploads from being processed.
* Fixed security issue where arguments could be altered client-side.
* Fixed issue where submissions sometimes wouldn't be processed after a redirection.
* Fixed issue with button styles in WordPress 5.4.

*Pro*

* Added automatic validation of email and username when registering users.
* Fixed issue where some form post editing settings would get lost when importing a form.

= 1.6.8 =

*Pro*

* Fixed warning which could appear for newly created forms which edit posts.
* Fixed fatal error which could occur during activation under some PHP versions.

= 1.6.7 =

* Added actions triggered before and after rendering each field called `af/field/before_field` and `af/field/after_field`.
* Added JS action triggered when the page is changed in a multi-page form called `af/form/page_changed`.
* Added ARIA attributes for success messages to improve accessibility.
* Added action after form assets have been enqueued called `af/form/enqueue`. Should be used if any assets need to be dequeued.
* Fixed warning which could appear when including subfields through merge tags.
* Fixed warning which could appear when values were retrieved from an empty repeater.
* Fixed warning when displaying an empty gallery field with `{all_fields}`.

*Pro*

* Added new setting to configure the post status used when creating new posts.
* Added support for using calculated fields inside group fields.
* Improved UX when setting up field mappings for post and user editing. New forms will now default to map all fields.
* Added filter for Slack webhooks called `af/form/slack/webhook`.

= 1.6.6 =

* Removed dependency on PHP sessions for submission handling. Should improve compatibility with caching solutions.

*Pro*

* Added support for calculated fields in emails and success messages.
* Added JS hooks for extending calculated fields.
* Fixed bug where only 10 lists would show up when configuring Mailchimp integration.

= 1.6.5 =

* Added support for exporting and importing forms as JSON files.
* Added form shortcode to form settings for reference.
* Fixed session issue which could cause deadlock.
* Fixed issue where some ACF front end translations were missing.
* Fixed issue where form pagination in the admin panel was missing.

= 1.6.4 =

* Added honeypot to prevent spam submissions. The honeypot is enabled by default.
* Added support for gallery fields in success messages and emails.
* Fixed issue where start and end time restrictions wouldn't account for timezone.
* Fixed issue where entry date would be displayed without timezone adjustment.
* Fixed issue where field includes could cause infinite loops with some field type combinations.

*Pro*

* Fixed issue where some post types weren't available for post editing.
* Fixed issue where post title and content couldn't be cleared during editing.
* Fixed issue where some Slack settings would be hidden.

= 1.6.3 =

* Added support for having multiple forms with the same key on a single page. Forms are now differentiated both on key and arguments.
* Fixed issue where restriction message wouldn't display correctly.

*Pro*

* Fixed issue where custom calculations using the `af/field/calculate_value` filter wouldn't work.

= 1.6.2 =

* Added instruction placement argument to match ACF. Check out the [documentation](https://advancedforms.github.io/guides/basic/displaying-a-form/) for information on how to use it.
* Improved ACF 5.6 compatibility.
* Fixed issue where "Hide from admin" would also hide fields from previews.
* Fixed issue where multiple forms on single page could conflict.
* Fixed issue where page fields would display weirdly in the admin panel.
* Fixed style which could interfere with ACF styling.

*Pro*

* Changed editing to default to creating posts and users if no argument is passed. `"post"="new"` and `"user"="new"` is no longer required.
* Fixed issue where post ID and post URL couldn't be included in email notifications.

= 1.6.1 =

*Pro*

* Fixed issue where merge tags wouldn't work when used inside custom format fields, such as post titles.

= 1.6.0 =

* Added `af/field/before_render` filter allowing fields to be modified before they are rendered.
* Added `af/form/before_submission` action allowing code to be run before the submission is processed. Errors can be added using `af_add_submission_error( $message )`.

*Pro*

* Added calculated field which updates in real-time as form is filled in. Mix static content with field values or provide your own calculated value using the `af/field/calculated_value` hook. Perfect for previews, calculators, and payment flows.
* Added option to edit current post and user by passing `current` as the post/user argument.
* Added merge tags for post ID and permalink as well as user ID.
* Fixed issue where some ACF hooks would have a missing `$post_id` argument whilst editing a post.

= 1.5.6 =

*Pro*

* Added edited post and user IDs to submission object for access in notifications and success messages.
* Fixed issue where post title could be cleared during edit.

= 1.5.5 =

* Added support for label_placement argument.
* Added support for sub fields in field inserter.
* Fixed compatibility issue with ACF 5.7.5 where paged forms would get stuck.
* Fixed issue where entry ID couldn't be included in emails.
* Fixed issue where field insert button would overlap input.
* Fixed issue where file uploads with the basic uploader would fail.

*Pro*

* Fixed issue where custom username fields wouldn't work.
* Fixed issue where username was changed during user edit.

= 1.5.4 =

* Added `af/form/success_message` filter for success messages.
* Fixed issue where field includes wouldn't work in email subject lines.

= 1.5.3 =

* Changed to a redirect after submission to avoid duplicate submissions.
* Added support for sending separate emails to multiple recipients.
* Fixed compatibility issue with ACF 5.7 where select2 fields wouldn't validate.
* Fixed issue where view counter might be incremented twice.

*Pro*

* Fixed issue where password and role could be overwritten when editing users.

= 1.5.2 =

* Fixed compatibility issue with ACF 5.7 where form validation wouldn't work.
* Fixed issue where file uploads were not saved to image/field fields.

*Pro*

* Fixed issue with excerpts on form generated posts.
* Fixed potential naming conflict with other plugins.

= 1.5.1 =

*Pro*

* Fixed issue where custom fields wouldn't work with user editing.
* Fixed issue where Mailchimp wouldn't work with some PHP installations.

= 1.5.0 =

* Added support for multi-page forms. Use the "Page" field to split your form over multiple pages.
* Added code generation for registering forms programmatically.
* Added support for custom validation logic on forms using the new `af/form/validate` filter.
* Added helpful sidebar with links to documentation and support.

*Pro*

* Added filters for intercepting API requests to Mailchimp, Slack, and Zapier.
* Fixed issue where first and last name wouldn't be sent to Mailchimp.

= 1.4.2 =

*Pro*

* Fixed issue where user editing didn't work.
* Fixed issue where custom fields weren't saved to users.

= 1.4.1 =

* Fixed issue where a warning would be thrown in `acf_esc_atts`.

= 1.4.0 =

It's finally here: Advanced Forms Pro! The Pro version makes it ease to create forms which create and edit posts and users. It also includes integrations with Mailchimp, Slack, and thousands of other services through Zapier. Of course, a Pro license grants direct, priority support. Available from [hookturn.io](https://hookturn.io/downloads/advanced-forms-pro/).

* Added ability to preview forms through the admin panel.
* Added compatibility with the upcoming ACF 5.7
* Added Swedish translation
* Added French translation (courtesy of @valentin-pellegrin)
* Removed IP tracking to comply with GDPR.
* Added filters for modifying submit buttons.
* Added actions surrounding email notifications.
* Fixed bug where `af_get_field` wouldn't work with group fields.
* Fixed bug where clone and group fields couldn't be nested in field includes.

= 1.3.5 =

* Added support for sub fields in field includes (syntax: `{field:field_name[sub_field_name]}`).
* Added support for using shortcodes in emails.
* Improved support for group fields in field includes.
* Improved documentation and error messages to clarify ACF Pro requirement.
* Fixed bug where post types would disappear when hiding admin with `af/settings/show_admin`.

= 1.3.4 =

* Added filters for easier inclusion in themes and plugins.
* Added field include support for image and file fields.
* Added action after entry has been created (`af/form/entry_created`).
* Improved output sanitation for field includes.
* Fixed issue which sometimes caused "Invalid argument" warnings.

= 1.3.3 =

* Added Polish language translations (thanks @Triloworld).
* Fixed undefined index "post_type" warnings.

= 1.3.2 =

* Fixed bug where form data would be lost on forms without file uploaders.

= 1.3.1 =

* Added automatic upload of files for the basic uploader. A simple `af_save_field` will now suffice for file fields with the basic uploader.
* Added global field setting "Hide from admin?". Perfect for shared field groups used both for a form and admin, where some fields might only be relevant in the form.
* Fixed issue where the plugin didn't work with ACF included through a theme.

= 1.3.0 =

* Added new filter mode. When active a form will not show a success message after submission but instead display all fields again with their submitted values, effectively working as a filter. Set argument `filter_mode` as true to activate.
* Improved field inclusion with better dropdowns and contextual options.
* Improved admin interface with icons and better settings naming.
* Improved field include support for fields with posts, users, and terms.
* Added new helper function `af_save_all_fields` for saving all fields from a form to a post.
* Changed name of `af_save_field_to_post` to `af_save_field`. `af_save_field_to_post` still works but will be removed in a future version.
* Added support for default field values.
* Added filters for pre-filling field values.
* Added asterix for required fields.
* Fixed bug where select2 in fields wouldn't work.
* + many minor improvements and bug fixes.

= 1.2.0 =

* Revised field parsing behind the scenes to more closely match ACF and ensure compatibility. This also improves support for clone fields which should now work as expected in combination with entries and `af_get_field`. _Note: clone fields will no longer be expanded in the global fields array. `af_get_field` will return values of cloned fields if referenced by name._
* Added option to include all fields in emails/success messages. Fields will be rendered in a simple and consistent table structure with minimal styling (easily overriden!).
* Added support for displaying repeaters in emails/success messages.
* Fixed bug where entry info wasn't displayed.
* Fixed bug where filtering entries by form didn't work.

= 1.1.1 =

* Added helper function `af_save_field_to_post( $field_key_or_name, $post_id )` for saving submitted fields directly to posts. No need to mess with `update_field`!
* Optimized scripts and styles to only be enqueued when a form is displayed.
* Added ability to display field values in success messages similar to how they can be used in emails.

= 1.1 =
First major update to Advanced Forms!

* Added global plugin object containing info about the latest submission, accessible from anywhere.
* Revised submission handling. Submission data is now saved to the global object which simplifies the API (`af_get_field` no longer requires the field parameter and can be used anywhere). As a side effect the `af_success` URL parameter is no longer necessary!
* Added `target` attribute and helper function `af_has_submission`. The new target attribute lets you specify a custom URL to point the form to. Combined with the `af_has_submission` function you can now use your submitted form data anywhere and display it however you like.

Check out the updated documentation for more info: [Displaying a form](https://advancedforms.github.io/guides/basic/displaying-a-form/) and [Processing form submissions](https://advancedforms.github.io/guides/basic/processing-form-submissions/).

= 1.0.4 =

* Added argument to specify uploader type (media library or basic file field). Similar to `acf_form`.
* Added argument for excluding certain fields from form by field key or name (thanks [David](https://github.com/daviddarke)!)
* Fixed issue which generated 'undefined index' warnings (again, thanks [David](https://github.com/daviddarke))

= 1.0.3.3 =

* Added class for required fields and added field instructions to form output (big thanks to [David](https://github.com/daviddarke)!)
* Fixed issue where form wasn't returned when echo = false
* Fixed issue where arguments after submission where loaded as object instead of array

= 1.0.3.2 =

* Set the default content type of emails to HTML
* Updated styling to keep submit button on its own row
* Fixed issue with shortcode output being echoed instead of returned
* Fixed undefined index notice on some admin pages

= 1.0.3.1 =

* Quick-fix of an issue with field value includes in "From" headers

= 1.0.3 =

* Added option to only display form for logged in users
* Added option to only display form during certain times
* Added filters for creating own form restrictions. Check out the documentation for more info.

= 1.0.2 =

* Added option to restrict number of entries
* Added filters to modify form before rendering
* Simplified form admin interface

= 1.0.1 =

* Added ability to include form fields in email recipient, subject, and content
* Added the option to set "From" header in emails
* Added filters for email headers and attachments
* Fixed a bug where rows couldn't be added to repeaters/flexible content fields
* Fixed a bug where emails wouldn't be sent after form submission

= 1.0.0 =
First version of Advanced Forms!

* The ability to create forms using an intuitive UI or programmatically
* Full support for all ACF fields
* Automatically save form data to entries
* Define emails to be sent after form submissions
* Plenty of actions and filters to customize your forms and their functionality

== Upgrade Notice ==

None

== Documentation ==

The documentation has been moved to a new site, check it out: [advancedforms.github.io](https://advancedforms.github.io)