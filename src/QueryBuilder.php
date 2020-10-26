<?php

namespace Simpl;

/*
QueryBuilder::select('*')->from('table')->where('1=1')->orderBy()->limit();
QueryBuilder::update()->data()->limit();
QueryBuilder::delete(
*/
class QueryBuilder
{
	public static $instance;

	public $table;
	public $cols;
	public $update;
	public $where;
	public $values;

	/**
	 * @return QueryBuilder
	 */
	public static function getInstance()
	{
		if (empty(self::$instance)){
			self::$instance = new self;
		}

		return self::$instance;
	}

	public static function select($cols = '*')
	{
		if (is_array($cols)){
			$cols = join(',', array_values($cols));
		}
	}

	public static function update($table)
	{

	}
}