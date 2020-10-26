<?php
namespace Tests;

use Simpl\SQL;

class MakeUpdateTest extends MakeSQLTest
{
	public function dataProvider()
	{
		$tests = [
			"test can update with a null value" => [
				"update foo set mytext = 'Bar', mydate = NULL where id = 1",
				[
					'mytext' => 'Bar',
					'mydate' => null
				]
			],
			"test can update with an empty string" => [
				"update foo set mytext = 'Bar', mydate = '' where id = 1",
				[
					'mytext' => 'Bar',
					'mydate' => ''
				]
			]
		];

		return $tests;
	}

	/**
	 * Ensure generated SQL matches expected and that we can exec the generated SQL and
	 * retrieve expected values from the database.
	 * @param $expected
	 * @param $data
	 * @dataProvider dataProvider
	 */
	public function testUpdate($expected, $data)
	{
		$db = $this->getConnection();

		# Does the generated SQL match expected?
		$actual = $db->makeUpdate('foo', $data, 'id = 1');
		$this->assertEquals($expected, $actual);

		# Does executing the SQL return the expected result?
		$db->exec($actual);
		$row = $db->query("select * from foo")->fetch();

		foreach ($data as $key => $value) {
			$this->assertEquals($row[$key], $value);
		}
	}
}
