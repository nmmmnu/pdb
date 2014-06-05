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


# Adapters and Decorators


## Adapters

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


## MySQLi\MySQLi

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


## Decorator\ExceptionDecorator

This enable the adapter to throw exceptions instead of give error codes.

~~~php
// Now we create normal PDO\PDO or different SQL adapter
$connection = array(
	"connection_string" => "sqlite:database.sqlite3"
);

$realdb = new \pdb\PDO\PDO($connection);

// Then attach $realdb to Decorator\ExceptionDecorator
$db = new \pdb\Decorator\ExceptionDecorator($realdb);
~~~


## Decorator\MultiDecorator

This decorator is used when you have master / slave set up.

It can be used in CQL too, but CQL have own implementation.

Suppose we have a master database and 3 replicas:

- 192.168.0.100 - master
- 192.168.0.101 - slave
- 192.168.0.102 - slave
- 192.168.0.103 - slave

~~~php
$connection = array(
	"port"			=> 3306,
	"database"		=> "test",
	"user"			=> "user",
	"password"		=> "secret",
);


$connection["host"] = "192.168.0.100";
$master = new \pdb\MySQLi\MySQLi($connection);

$replicaDB = array();
$connection["host"] = "192.168.0.101";
$replicas[] = new \pdb\MySQLi\MySQLi($connection);

$connection["host"] = "192.168.0.102";
$replicas[] = new \pdb\MySQLi\MySQLi($connection);

$connection["host"] = "192.168.0.103";
$replicas[] = new \pdb\MySQLi\MySQLi($connection);

// 3 objects are created, but there are no open connection yet.
$db = new \pdb\Decorator\ExceptionDecorator($replicaDB);

// then send insert / update / delete to the master
$master->quesy("update users set logged = now() where id = %d", array(1234) );

// and select from slaves
$result = $db->query("select * from users where id = %d", array(1234) );
~~~


## Decorator\CacheDecorator

This enable caching the results using PFC library.

~~~php
// First you need to create CacheAdapter.
// following adapter will cache in /dev/shm (e.g. RAM Disk)
$cacheAdapter = new \pfc\CacheAdapter\Shm("sqlcache_");
$cacheAdapter->setTTL(24 * 3600); // cache for 24h

// Then you need to create Serializer.
// following class will serialize in JSON format
$serializer = new \pfc\Serializer\JSON();

// For debug purposes, you can create a Logger.
// we will skip this step
$logger = null;

// Now we create normal PDO\PDO or different SQL adapter
$connection = array(
	"connection_string" => "sqlite:database.sqlite3"
);

$realdb = new \pdb\PDO\PDO($connection);

// Then attach $realdb to Decorator\CacheDecorator
$db = new \pdb\Decorator\CacheDecorator($realdb, $cacheAdapter, $serializer, $logger);

// using $db will lead to cached results
// using $realdb will query the same connection, without cache.
~~~


## Decorator\ProfilerDecorator

This enable profiling the SQL statements using PFC library.

~~~php
// First you need to create Profiler.
$profiler = new \pfc\Profiler();

// Then you need a Logger
$logger = new \pfc\Logger();
$logger->addOutput(new \pfc\OutputAdapter\Console());

// Now we create normal PDO\PDO or different SQL adapter
$connection = array(
	"connection_string" => "sqlite:database.sqlite3"
);

$realdb = new \pdb\PDO\PDO($connection);

// Then attach $realdb to Decorator\ProfilerDecorator
$db = new \pdb\Decorator\CacheDecorator($realdb, $cacheAdapter, $serializer, $logger);

// Use $db here. Execution times will be written to the log.

// Finally, if you want, you can print all profiling results
print_r($profiler->getData());
~~~


## Combine all together :)

Following example:

- create Decorator\MultiDecorator with 3 replicas
- attach Decorator\CacheDecorator
- attach Decorator\ExceptionDecorator

~~~php
$connection = array(
	"port"			=> 3306,
	"database"		=> "test",
	"user"			=> "user",
	"password"		=> "secret",
);


// ================================
// STEP 1
// ================================

$replicaDB = array();
$connection["host"] = "192.168.0.101";
$replicas[] = new \pdb\MySQLi\MySQLi($connection);

$connection["host"] = "192.168.0.102";
$replicas[] = new \pdb\MySQLi\MySQLi($connection);

$connection["host"] = "192.168.0.103";
$replicas[] = new \pdb\MySQLi\MySQLi($connection);

// 3 objects are created, but there are no open connection yet.
$multidb = new \pdb\Decorator\ExceptionDecorator($replicaDB);


// ================================
// STEP 2
// ================================

// First you need to create CacheAdapter.
// following adapter will cache in /dev/shm (e.g. RAM Disk)
$cacheAdapter = new \pfc\CacheAdapter\Shm("sqlcache_");
$cacheAdapter->setTTL(24 * 3600); // cache for 24h

// Then you need to create Serializer.
// following class will serialize in JSON format
$serializer = new \pfc\Serializer\JSON();

// For debug purposes, you can create a Logger.
// we will skip this step
$logger = null;

// Now we create normal PDO\PDO or different SQL adapter
$connection = array(
	"connection_string" => "sqlite:database.sqlite3"
);

// Then attach $multidb to Decorator\CacheDecorator
$cachedb = new \pdb\Decorator\CacheDecorator($multidb, $cacheAdapter, $serializer, $logger);


// ================================
// STEP 3
// ================================

// Then attach $realdb to Decorator\ExceptionDecorator
$db = new \pdb\Decorator\ExceptionDecorator($cachedb);


// ================================
// STEP 4
// ================================

// Now you can use $db just in "normal" way:

$result = $db->query("select * from users", array() );

if (!$result){
	echo "No results\n";
	exit;
}

foreach($results as $row){
	printf("%3d %-100s\n", $row["id"], $row["name"]);
}
~~~

# [eof]

