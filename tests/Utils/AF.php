<?php

namespace AdvancedFormsTests\Utils;

use AF_Core_Forms_Submissions;

class AF {

	public static function make_submission( $submission ) {
		// This is the basic structure of a submission.
		$structure = [
			'form' => [],
			'args' => [],
			'fields' => [],
			'errors' => [],
			'origin_url' => '',
		];

		return array_merge( $structure, $submission );
	}

	public static function process_submission( $submission ) {
		AF()->submission = $submission;
		/** @var AF_Core_Forms_Submissions $submissions */
		$submissions = AF()->classes['core_forms_submissions'];
		$submissions->process_submission( $submission );
	}

}