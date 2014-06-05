#!/usr/local/bin/php
<?
require_once __DIR__ . "/../pdb/__autoload.php";
require_once __DIR__ . "/../../pfc/pfc/__autoload.php";

pdb\pdb_assert_setup();

pdb\Mock::test();

pdb\Decorator\ExceptionDecorator::test();
pdb\Decorator\MultiDecorator::test();
pdb\Decorator\ProfilerDecorator::test();
pdb\Decorator\CacheDecorator::test();

