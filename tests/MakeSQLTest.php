<?php

namespace Tests;

abstract class MakeSQLTest extends SQLTest
{
	public function setUp()
	{
		parent::setUp();
		$db = $this->getConnection();

		$db->pdo->exec('create table foo(id INTEGER PRIMARY KEY AUTOINCREMENT, mytext varchar, mydate datetime)');
		$db->pdo->exec("insert into foo (mytext, mydate) values ('Foo', '2020-01-01')");
	}

	public function connect()
	{
		$db = new \Simpl\SQL('sqlite', ':memory:');
		return $db;
	}

	public function tearDown()
	{
		parent::tearDown();
		$db = $this->getConnection();
		$db->pdo->exec('drop table foo');
	}
}