#php_db

PHP Database Abstraction Layer


## Goals

- Easy to use
- Very simple interface
- No need of metadata (column names) support


## Workflow

~~~php
require_once "./pdb/__autoload.php";

$db = new \pdb\PDO\PDO($connection);

$result = $db->query("
	select
		*
	from
		users
	where
		id = %d
", array(55) );

if (!$result){
	echo "No result\n";
	exit;
}

foreach($results as $row){
	printf("%3d %-100s\n", $row["id"], $row["name"]);
}
~~~

## Classes

- PDO\PDO - PDO adapter
- MySQLi\MySQLi - MySQLi adapter
- CQL\CQL - Experimental Cassandra CQL adapter

## Decorators

- Decorator\ExceptionDecorator - make any adapter throw exceptions instead of return true / false.

## PFC Decorators

- Decorator\CacheDecorator - cache adapter
- Decorator\ProfilerDecorator - prints profiling information

# [eof]

