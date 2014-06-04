#!/usr/local/bin/php
<?
require_once __DIR__ . "/../pdb/__autoload.php";
require_once __DIR__ . "/../../pfc/__autoload.php";

pdb\Mock::test();

pdb\Decorator\ExceptionDecorator::test();
pdb\Decorator\ProfilerDecorator::test();
pdb\Decorator\CacheDecorator::test();

