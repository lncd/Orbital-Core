<?php

/**
 * @group System
 */

class PHPTest extends CIUnit_TestCase
{
	function setUp()
	{
		// Setup
	}

	public function testFunctions()
	{
		$this->assertTrue(function_exists('json_encode'));
		$this->assertTrue(function_exists('json_decode'));
	}

	public function testPhpVersion()
	{
		$this->assertTrue(phpversion() > 5.1);
	}
}
