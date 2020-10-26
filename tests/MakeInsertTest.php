<?php
namespace Tests;

class MakeInsertTest extends SQLTest
{
	public function dataProvider()
	{
		$tests = [
			"mydate should be null" => [
				"insert into foo (mytext, mydate) values ('Bar', NULL)",
				[
					'mytext' => 'Bar',
					'mydate' => null
				],
			],
			"mydate should be empty string" => [
				"insert into foo (mytext, mydate) values ('Bar', '')",
				[
					'mytext' => 'Bar',
					'mydate' => ''
				],

			],
			"myfloat should be 3.1415" => [
				"insert into foo (myfloat) values (3.1415)",
				[
					'myfloat' => 3.1415
				],
			],
			"myint should be 3" => [
				"insert into foo (myint) values (3)",
				[
					'myint' => 3
				],
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
	public function testInsert($expected, $data)
	{
		$db = $this->connect();

		# Does the generated SQL match expected?
		$actual = $db->makeInsert('foo', $data);
		$this->assertEquals($expected, $actual);

		# Does executing the SQL return the expected result?
		$db->exec($actual);
		$row = $db->query("select * from foo")->fetch();

		foreach ($data as $key => $value) {
			$this->assertEquals($row[$key], $value);
		}
	}
}
