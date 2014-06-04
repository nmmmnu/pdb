<?
namespace pdb;


spl_autoload_register(function($class){
	$parts = explode("\\", $class);

	if ($parts[0] != __NAMESPACE__)
		return;

	//unset($parts[0]);

	$file = implode("/", $parts) . ".php";

	$file = dirname(__FILE__) . "/" . $file;

	//echo "Loading $file...\n";

	include_once $file;
});


function pdb_assert_setup(){
	assert_options(ASSERT_ACTIVE,	true);
	assert_options(ASSERT_BAIL,	true);
	assert_options(ASSERT_WARNING,  false);
	assert_options(ASSERT_CALLBACK, __NAMESPACE__ . "\pdb_assert_callback");
}


function pdb_assert_callback($script, $line, $message){
	echo "Condition failed in $script, Line: $line\n";

	if ($message)
		echo "Message: $message\n";

	echo "\n";
}




