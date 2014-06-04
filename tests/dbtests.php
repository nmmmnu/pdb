#!/usr/local/bin/php
<?
namespace tests;
require_once __DIR__ . "/../pdb/__autoload.php";


require "db.php";


// prepare data, don't throw exceptions
if ($conn == "cql"){
	$create_statement1 = "
		create table ppl(
			id int primary key,
			name text,
			age int,
			tt uuid,
			balance float,
			balance2 bigint,
			balance3 decimal,
			enabled boolean
		)
	";
	$create_statement = false;

	$truncate_statement = false;

	$insert_statement = "
		insert into ppl(
			id,
			name,
			age,
			tt,
			balance,
			balance2,
			balance3,
			enabled
		)values(
			%d,
			'%s',
			%d,
			uuid(),
			3.1416,
			1234,
			12.34567890,
			true
		)
	";
}else{
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

	$insert_statement = "insert into ppl(id, name, age)values(%s, '%s', %s)";
}


if ($create_statement)
	$db->query($create_statement, array());

if ($truncate_statement)
	$db->query($truncate_statement, array());


$db->query($insert_statement, array(1, 'Ivan',   22) );
$db->query($insert_statement, array(2, 'Stoyan', 25) );
$db->query($insert_statement, array(3, 'Dragan', 33) );
$db->query($insert_statement, array(4, 'James',  42) );


// sql
$sql = "select * from ppl";


$rows = $db->query($sql, array() );

print_r(iterator_to_array($rows));

$rows = $db->query($sql, array(), "name");
print_r(iterator_to_array($rows));


// close
$db->close();

