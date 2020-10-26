<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use Simpl\SQL;

abstract class SQLTest extends TestCase
{
	/**
	 * @var SQL
	 */
	protected $db;

	protected function connect()
	{
		$db = new \Simpl\SQL('sqlite', ':memory:');
		$db->pdo->exec('create table foo(id INTEGER PRIMARY KEY AUTOINCREMENT, mytext varchar, myint int, myfloat float, mydate datetime)');
		return $db;
	}

	protected function getConnection()
	{
		if (empty($this->db)){
			$this->db = $this->connect();
		}

		return $this->db;
	}
}