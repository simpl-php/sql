<?php
namespace Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use Simpl\SQL;

class ConnectionTest extends TestCase
{
	public function connectionArrayProvider()
	{
		return [
			[
				'mysql:host=mariadb;port=3306;dbname=test;charset=utf8mb4',
				[
					'prefix' => 'mysql',
					'host' => 'mariadb',
					'port' => 3306,
					'dbname' => 'test',
					'charset' => 'utf8mb4'
				]
			],
			[
				'mysql:host=mariadb;port=3306;dbname=test',
				[
					'prefix' => 'mysql',
					'host' => 'mariadb',
					'port' => 3306,
					'dbname' => 'test',
					'charset' => ''
				]
			],
			[
				'mysql:host=mariadb;port=3306',
				[
					'prefix' => 'mysql',
					'host' => 'mariadb',
					'port' => 3306,
					'dbname' => '',
					'charset' => ''
				]
			],
			[
				'mysql:host=mariadb',
				[
					'prefix' => 'mysql',
					'host' => 'mariadb',
					'port' => '',
					'dbname' => '',
					'charset' => ''
				]
			],
			[
				'mysql:host=mariadb;dbname=test',
				[
					'prefix' => 'mysql',
					'host' => 'mariadb',
					'dbname' => 'test',
				]
			],
			[
				'sqlite:/path/to/database.sqlite',
				[
					'prefix' => 'sqlite',
					'path' => '/path/to/database.sqlite'
				]
			],
			[
				'sqlite::memory:',
				[
					'prefix' => 'sqlite',
					'path' => ':memory:'
				]
			]
		];
	}

	/**
	 * @param $expected
	 * @param $options
	 * @dataProvider connectionArrayProvider
	 */
	public function testCanParseConnectionArray($expected, $options)
	{
		$actual = SQL::buildDsn($options);

		$this->assertEquals($expected, $actual);
	}


	public function connectionStringProvider()
	{
		return [
			[
				'sqlite::memory:',
				['sqlite', ':memory:']
			],
			[
				'sqlite:/path/to/database.sqlite',
				['sqlite', '/path/to/database.sqlite']
			],
			[
				'mysql:host=localhost;dbname=testdbname;charset=utf8mb4',
				['localhost', 'testdbname']
			]
		];
	}

	/**
	 * @param $expected
	 * @param $input
	 * @dataProvider connectionStringProvider
	 */
	public function testCanParseConnectionString($expected, $input)
	{
		$actual = SQL::buildDsn($input[0], $input[1]);
		$this->assertEquals($expected, $actual);
	}

	public function testCanGetUsernamePasswordFromConfigurationArray()
	{
		$config = [
			'prefix' => 'mysql',
			'host' => 'mariadb',
			'dbname' => 'test',
			'username' => 'testuser',
			'password' => 'testpassword'
		];

		$connection = new SQL($config, null, null, null, null, false);

		$this->assertEquals('testuser', $connection->username);
		$this->assertEquals('testpassword', $connection->password);
	}

	public function testCanOverrideConnectionOptions()
	{
		$config = [
			'prefix' => 'sqlite',
			'path' => ':memory:',
			'options' => [
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
				PDO::ATTR_EMULATE_PREPARES   => false,
			],
		];

		$db = new SQL($config);
		$db->pdo->exec('create table foo(id INTEGER PRIMARY KEY AUTOINCREMENT, mytext varchar, mydate datetime)');
		$db->pdo->exec("insert into foo (mytext, mydate) values ('bar', '2020-01-01')");
		$result = $db->query("select * from foo")->fetch();
		$this->assertTrue(is_object($result), 'Result should be an object.');
		$this->assertObjectHasAttribute('mytext', $result);
		$this->assertEquals('bar', $result->mytext);
	}
}
