<?php

namespace AdvancedFormsTests\Utils;

use AF_Core_Forms_Submissions;

class AF {

	/**
	 * Make a submission array for processing.
	 */
	public static function make_submission( $submission ): array {
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

	/**
	 * Takes a submission array and runs the submission processor.
	 */
	public static function process_submission( $submission ) {
		AF()->submission = $submission;
		/** @var AF_Core_Forms_Submissions $submissions */
		$submissions = AF()->classes['core_forms_submissions'];
		$submissions->process_submission( $submission );
	}

}