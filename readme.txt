=== Advanced Forms ===
Contributors: fabianlindfors
Tags: af, advanced, forms, form, acf, advanced, custom, fields, flexible, developer, developer-friendly
Requires at least: 3.6.0
Tested up to: 4.7
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Flexible and developer-friendly forms using the power of Advanced Custom Fields

== Description ==

*Requires ACF v5*

Advanced Forms lets you build flexible forms using the power of Advanced Custom Fields. The plugin has been built with developers in mind and offers a large variety of helper functions and customization hooks.

* Use all the fields provided by ACF, including repeaters and flexible content fields
* Define forms and fields fully programmatically for easy integration with your theme/plugin, or use the intuitive UI
* Either use the provided hooks to process form submissions as you wish or let the plugin automatically save them as entries
* Optionally set up emails to be sent automatically with form submissions

= Developers =

Advanced Forms is first and foremost built for developers and allows for simple integration with themes/plugins. Check out the [documentation](https://wordpress.org/plugins/advanced-forms/other_notes/) for details.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/advanced-forms` directory, or install the plugin through the WordPress plugins screen directly.
2. Make sure ACF v5 is installed and activated.
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Read the documentation for instructions on how to create, configure, and display forms.


== Frequently Asked Questions ==

= Q: Does this plugin only work with ACF v5? =

Yes. Versions 4 or lower of ACF are not supported.

== Screenshots ==

1. Form edit page with form settings and a list of form fields
2. Example of entry automatically generated after form submission including all form fields
3. Example of location rules on an ACF field group

== Changelog ==

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

= Creating a form =

Forms can be created either using the UI provided or programmatically.

To create a form using the UI navigate to the "Forms" admin page and create a new form. Use the form settings to set up entries, emails and display options. At the bottom you will find a list of all fields which are connected to your form.

To create a form programmatically the function `af_register_form( $form )` is provided. The `$form` parameter should be an array matching the following structure:

`
array(
	'title' 		=> '',
	'key'			=> '',
	'display' 		=> array(
		'display_title' 			=> false,
		'display_description' 		=> false,
		'description' 				=> '',
		'success_message' 			=> '',
	),
	'create_entries' => false,
)
`

The only required attribute is `key` which should be a unique identifier for your form. Setting the title attribute is recommended.

= Adding fields to a form =

The fields connected to a form are fully defined by Advanced Custom Fields allowing you to use the full range of field types offered by ACF. To connect a field group to your form set its location rule to match. This can be done in the ACF field group UI by adding a location rule and setting it to "Form" -> "is equal to" -> your form title.

If your ACF field group is registered programmatically using `acf_add_local_field_group` your location rule can be defined as:

`
array (
	'param' => 'af_form',
	'operator' => '==',
	'value' => YOUR_FORM_KEY,
),
`

= Displaying a form =

Once a form has been added and fields have been assigned you can display the form either using a shortcode or with a function call.

To display a form using a shortcode use the structure below.

`[advanced_form form="FORM_ID_OR_KEY"]`

The form can be specified either by its post ID or its form key but it's recommended to always use the form key. The form key can be found right below the title on the form edit page.

A form can also be displayed using a function call which specifies the form key or ID. The function call is shown below.

`advanced_form( $form_id_or_key, $args );`

The `$args` parameter allows you to tweak how the form is displayed. These settings can also be passed to the shortcode. The available settings and their defaults are as follows.

`
array(
	'display_title' 			=> defaults to form setting,			// Wether the title should be displayed or not (true/false)
	'display_description'		=> defaults to form setting,			// Wether the description should be displayed or not (true/false)
	'submit_text'				=> 'Submit',						  // Text used for the submit button
	'redirect'				=> current url with ?af_success,		// The URL to redirect to after a successful submission. Defaults to the current URL displaying the success message set in the form settings
	'echo'					=> true,								// Wether the form output should be echoed or returned
	'values'					=> array(),							// Field values to pre-fill. Should be an array with format: $field_name_or_key => $field_prefill_value
)
`

= Processing form submissions =

After a form has been submitted the field values need to be processed. The plugin comes with the ability to automatically save form data to entries and to send custom emails. Emails and entries can be configured in the form settings but are not enabled by default.

If you need to process the form data further the handy action hook `af/form/submission` should be used. The hook can be used in three different ways.

`
add_action( 'af/form/submission', 'your_callback_function' );
add_action( 'af/form/submission/id=FORM_ID', 'your_callback_function' );
add_action( 'af/form/submission/key=FORM_KEY', 'your_callback_function' );
`

The first hook is invoked for all form submissions while the two last ones allow you to specify a form using either the form post ID or form key. It's recommended to use the form key.

The action passes three different parameters:

`
$form - The form object
$fields - Array of the submitted fields and their processed values
$args - Array of arguments used to display the form
`


To simplify the retrieval of field values a helper function `af_get_field( $field_name_or_key, $fields )` is provided which takes the field name/key to find and the array of fields. The function returns a processed value.


The following is an example of processing a form submission and extracting the value entered into the field with name "email".

`
function handle_form_submission( $form, $fields, $args ) {
	
	$email = af_get_field( 'email', $fields );
	
}
add_action( 'af/form/submission', 'handle_form_submission' );
`

= Customizing validations =

Form validation is fully handled by ACF and if customization is needed the filters provided by ACF can be used, such as `acf/validate_value`. Refer to the [ACF documentation](https://www.advancedcustomfields.com/resources/) for more info.

= Actions =

= af/form/before_title =

Triggered at the beginning of a form, before the title.

`
function before_title( $form, $args ) {
	echo 'Before title';
}
add_action( 'af/form/before_title', 'before_title' );
add_action( 'af/form/before_title/id=FORM_ID', 'before_title' );
add_action( 'af/form/before_title/key=FORM_KEY', 'before_title' );
`

= af/form/before_fields =

Triggered right before the fields and after the description.

`
function before_fields( $form, $args ) {
	echo 'Before fields and after description';
}
add_action( 'af/form/before_fields', 'before_fields' );
add_action( 'af/form/before_fields/id=FORM_ID', 'before_fields' );
add_action( 'af/form/before_fields/key=FORM_KEY', 'before_fields' );
`

= af/form/hidden_fields =

Use to add hidden fields to a form.

`
function hidden_field( $form, $args ) {
	echo '<input type="hidden" name="some_hidden_field">';
}
add_action( 'af/form/hidden_fields', 'hidden_field' );
add_action( 'af/form/hidden_fields/id=FORM_ID', 'hidden_field' );
add_action( 'af/form/hidden_fields/key=FORM_KEY', 'hidden_field' );
`

= af/form/after_fields =

Triggered after the submit button.

`
function after_fields( $form, $args ) {
	echo 'After fields';
}
add_action( 'af/form/after_fields', 'after_fields' );
add_action( 'af/form/after_fields/id=FORM_ID', 'after_fields' );
add_action( 'af/form/after_fields/key=FORM_KEY', 'after_fields' );
`

= Filters =

= af/form/args =

Alter the arguments used to display a form. The arguments are either passed to the function call or defined as attributes on a shortcode.

`
function filter_args( $args, $form ) {
	$args['submit_text'] = 'Send';
	
	return $args;
}
add_action( 'af/form/args', 'filter_args' );
add_action( 'af/form/args/id=FORM_ID', 'filter_args' );
add_action( 'af/form/args/key=FORM_KEY', 'filter_args' );
`

= af/form/title =

Change the title displayed above form.

`
function filter_title( $title, $form ) {
	return 'New title';
}
add_action( 'af/form/title', 'filter_title' );
add_action( 'af/form/title/id=FORM_ID', 'filter_title' );
add_action( 'af/form/title/key=FORM_KEY', 'filter_title' );
`

= af/form/description =

Change the description displayed before the fields.

`
function filter_description( $description, $form ) {
	return 'New description';
}
add_action( 'af/form/description', 'filter_description' );
add_action( 'af/form/description/id=FORM_ID', 'filter_description' );
add_action( 'af/form/description/key=FORM_KEY', 'filter_description' );
`

= af/form/field_attributes =

Filter the attributes on field wrappers. Use to add classes, set an ID, or add new attributes.
$attributes is an array of HTML attributes and their values.

`
function filter_field_attributes( $attributes, $field, $form, $args ) {
	$attributes['id'] = 'form-id';
	
	return $attributes;
}
add_action( 'af/form/field_attributes', 'filter_field_attributes' );
add_action( 'af/form/field_attributes/id=FORM_ID', 'filter_field_attributes' );
add_action( 'af/form/field_attributes/key=FORM_KEY', 'filter_field_attributes' );
`