<?php
namespace Tests;
use Tests\SQLTest;

class MakeUpdateTest extends SQLTest
{
	public function testCanUpdateNullValue()
	{
		$db = $this->connect();

		$data = [
			'mytext' => 'Bar',
			'mydate' => null
		];

		$expected = "update foo set mytext = 'Bar', mydate = NULL where 1=1";
		$actual = $db->makeUpdate('foo', $data, '1=1');

		$this->assertEquals($expected, $actual);
	}

	public function testCanUpdateEmptyString()
	{
		$db = $this->connect();

		$data = [
			'mytext' => 'Bar',
			'mydate' => ''
		];

		$expected = "update foo set mytext = 'Bar', mydate = '' where 1=1";
		$actual = $db->makeUpdate('foo', $data, '1=1');

		$this->assertEquals($expected, $actual);
	}
}