=== Advanced Forms ===
Contributors: fabianlindfors
Tags: af, advanced, forms, form, acf, advanced, custom, fields, flexible, developer, developer-friendly
Requires at least: 3.6.0
Tested up to: 4.7
Stable tag: 1.0.3.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Flexible and developer-friendly forms using the power of Advanced Custom Fields

== Description ==

*Requires ACF v5*

Documentation: [advancedforms.github.io](https://advancedforms.github.io)

Advanced Forms lets you build flexible forms using the power of Advanced Custom Fields. The plugin has been built with developers in mind and offers a large variety of helper functions and customization hooks.

* Use all the fields provided by ACF, including repeaters and flexible content fields
* Define forms and fields fully programmatically for easy integration with your theme/plugin, or use the intuitive UI
* Either use the provided hooks to process form submissions as you wish or let the plugin automatically save them as entries
* Optionally set up emails to be sent automatically with form submissions
* Set a maximum number of entries created, limit a form to only logged in users, or schedule a form to only display during certain times. Custom restrictions can be applied by hooking in to a simple filter.

= Developers =

Advanced Forms is first and foremost built for developers and allows for simple integration with themes/plugins. Check out the [documentation](https://advancedforms.github.io) for guides and details about functions/hooks.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/advanced-forms` directory, or install the plugin through the WordPress plugins screen directly.
2. Make sure ACF v5 is installed and activated.
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Read the [documentation](https://advancedforms.github.io) for instructions on how to create, configure, and display forms.


== Frequently Asked Questions ==

= Q: Does this plugin only work with ACF v5? =

Yes. Versions 4 or lower of ACF are not supported.

== Screenshots ==

1. Form edit page with form settings and a list of form fields
2. Example of entry automatically generated after form submission including all form fields
3. Example of location rules on an ACF field group

== Changelog ==

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