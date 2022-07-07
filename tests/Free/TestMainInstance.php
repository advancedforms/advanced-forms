<?php

namespace AdvancedFormsTests\Free;

use WP_UnitTestCase;

class TestMainInstance extends WP_UnitTestCase {

	public function test_pro_flag_returns_false() {
		$this->assertFalse( AF()->pro );
	}

}