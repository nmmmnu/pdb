#!/usr/local/bin/php
<?
namespace tests;
require_once __DIR__ . "/../pdb/__autoload.php";


require "db.php";


// prepare data, don't throw exceptions
$create_statement = "
	create table ppl(
		id int primary key,
		name varchar(20),
		age int
	)
";

$truncate_statement = "
	delete from ppl
";

$insert_statement = "insert into ppl(id, name, age)values(%d, '%s', %d)";


$db->query($create_statement, array());

$db->query($truncate_statement, array());


$db->query($insert_statement, array(1, 'Ivan',   22) );
$db->query($insert_statement, array(2, 'Stoyan', 25) );
$db->query($insert_statement, array(3, 'Dragan', 33) );
$db->query($insert_statement, array(4, 'James',  42) );


// sql
$sql = "select * from ppl";


$rows = $db->query($sql, array() );

print_r($rows->fetchArray());

$rows = $db->query($sql, array());
print_r($rows->fetchArray("name"));


// close
$db->close();

