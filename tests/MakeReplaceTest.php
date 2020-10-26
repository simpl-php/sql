<?php
namespace Tests;

use Simpl\SQL;

class MakeReplaceTest extends MakeSQLTest
{
	public function dataProvider()
	{
		$tests = [
			"mydate should be null" => [
				"replace into foo (id, mytext, mydate) values (1, 'Bar', NULL)",
				[
					'id' => 1,
					'mytext' => 'Bar',
					'mydate' => null
				]
			],
			"mydate should be empty string" => [
				"replace into foo (id, mytext, mydate) values (1, 'Bar', '')",
				[
					'id' => 1,
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
	public function testReplace($expected, $data)
	{
		$db = $this->getConnection();

		# Does the generated SQL match expected?
		$actual = $db->makeReplace('foo', $data);
		$this->assertEquals($expected, $actual);

		# Does executing the SQL return the expected result?
		$db->exec($actual);
		$row = $db->query("select * from foo")->fetch();

		foreach ($data as $key => $value) {
			$this->assertEquals($row[$key], $value);
		}
	}
}
