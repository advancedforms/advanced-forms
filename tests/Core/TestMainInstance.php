<?php

namespace AdvancedFormsTests\Core;

use WP_UnitTestCase;

class TestMainInstance extends WP_UnitTestCase {

	public function test_AF_function_returns_main_instance() {
		$this->assertTrue( AF() instanceof \AF );
		$this->assertSame( AF(), AF(), 'Subsequent calls did not return the same instance.' );
	}

}