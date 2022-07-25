<?php

namespace AdvancedFormsTests\Core;

use stdClass;
use WP_UnitTestCase;

class TestAfArrGetUtilFunction extends WP_UnitTestCase {

	const TEST_ARR = [
		'l1' => [
			'l2' => 'value'
		]
	];

	public function test_af_arr_get_defaults_to_null() {
		$this->assertNull( af_arr_get( self::TEST_ARR, 'non-existent-key' ) );
	}

	public function test_af_arr_get_default_can_be_altered() {
		$this->assertSame( 'new default', af_arr_get( self::TEST_ARR, 'non-existent-key', 'new default' ) );
	}

	public function test_af_arr_get_resolves_top_level_values() {
		$this->assertSame( self::TEST_ARR['l1'], af_arr_get( self::TEST_ARR, 'l1' ) );
	}

	public function test_af_arr_get_resolves_nested_values() {
		$this->assertSame( 'value', af_arr_get( self::TEST_ARR, 'l1.l2' ) );
	}

	public function test_af_arr_get_returns_default_when_non_array_passed() {
		$data = 'string';
		$this->assertNull( af_arr_get( $data, 'l1.l2' ) );
		$this->assertSame( 'default value', af_arr_get( $data, 'l1.l2', 'default value' ) );
	}

	public function test_af_arr_get_returns_default_when_object_passed() {
		$data = new stdClass();
		$data->l1 = [ 'l2' => 'value' ];
		$this->assertNull( af_arr_get( $data, 'l1.l2' ) );
		$this->assertSame( 'default value', af_arr_get( $data, 'l1.l2', 'default value' ) );
	}

}