=== Advanced Forms ===
Contributors: fabianlindfors
Tags: af, advanced, forms, form, acf, advanced, custom, fields, flexible, developer, developer-friendly
Requires at least: 3.6.0
Tested up to: 4.9.4
Stable tag: 1.5.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Flexible and developer-friendly forms using the power of Advanced Custom Fields

== Description ==

*Requires ACF PRO v5*

Documentation: [advancedforms.github.io](https://advancedforms.github.io)

Advanced Forms lets you build flexible forms using the power of Advanced Custom Fields. The plugin has been built with developers in mind and offers a large variety of helper functions and customization hooks.

* Use all the fields provided by ACF, including repeaters and flexible content fields
* Define forms and fields fully programmatically for easy integration with your theme/plugin, or use the intuitive UI
* Either use the provided hooks to process form submissions as you wish or let the plugin automatically save them as entries
* Optionally set up emails to be sent automatically with form submissions
* Set a maximum number of entries created, limit a form to only logged in users, or schedule a form to only display during certain times. Custom restrictions can be applied by hooking in to a simple filter.

= Advanced Forms Pro =

* Create/edit posts and users with ease
* Integrate with Slack, Mailchimp, and Zapier
* Get direct, priority support

Available from [hookturn.io](https://hookturn.io/downloads/advanced-forms)

= Developers =

Advanced Forms is first and foremost built for developers and allows for simple integration with themes/plugins. Check out the [documentation](https://advancedforms.github.io) for guides and details about functions/hooks.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/advanced-forms` directory, or install the plugin through the WordPress plugins screen directly.
2. Make sure ACF PRO v5 is installed and activated.
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Read the [documentation](https://advancedforms.github.io) for instructions on how to create, configure, and display forms.


== Frequently Asked Questions ==

= Q: Does this plugin only work with ACF PRO v5? =

Yes. Versions 4 or lower of ACF are not supported.

== Screenshots ==

1. Form edit page with form settings and a list of form fields
2. Example of entry automatically generated after form submission including all form fields
3. Example of location rules on an ACF field group

== Changelog ==

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