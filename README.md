# pdb

PHP Database Abstraction Layer


## Goals

- Easy to use
- Very simple interface
- No need of metadata (column names) support


## Simple workflow

~~~php
require_once "./pdb/__autoload.php";

$connection = array(
	"connection_string" => "sqlite:database.sqlite3"
);

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
- Decorator\MultiDecorator - adapter for connection to read only replicas

## PFC Decorators

There required additional clases from PFC library

- Decorator\CacheDecorator - cache adapter
- Decorator\ProfilerDecorator - prints profiling information

# Examples

## PDO\PDO

~~~php
$connection = array(
	// PDO connection string
	"connection_string"	=> "mysql:unix_socket=$socket;dbname=test",
	"user"			=> "user",
	"password"		=> "secret",
	// Optional SQL command
	"init_command"		=> "set names utf8"
);

$db = new \pdb\PDO\PDO($connection);
~~~

## PDO\PDO

~~~php
$connection = array(
	"host"			=> "localhost",
	"port"			=> 3306,
	"database"		=> "test",
	"user"			=> "user",
	"password"		=> "secret",

	// socket to connect (remove host and port in this case)
	"socket"		=> null,

	// Optional SQL command
	"init_command"		=> "set names utf8"
);

$db = new \pdb\MySQLi\MySQLi($connection);
~~~

## CQL\CQL

Please note this adapter is experimental.

Currently supported data types:

- AsciiType
- UTF8Type
- DecimalType - Might give unexpected results, if number size is bigger than 64bit.
- Int32Type
- LongType
- FloatType
- BooleanType - return it as 'true' / 'false' strings
- UUIDType

All 64bit types are supported and works OK on 32bit machines.

~~~php
// you need to open this file and set $THRIFT_PATH accordingly.
require_once __DIR__ . "/../pdb/__cassandra_autoload.php";

$connection = array(
	// Cassandra hosts
	"hosts"		=> array(
				"office-server.cosm:9160"	,
				"office-server.com:9160"	,
			),

	// Cassandra keyspace (equivalent to database in RDBMS world)
	"keyspace"	=> "niki"				,
);

$db = new \pdb\CQL\CQL($connection);
~~~

# [eof]

