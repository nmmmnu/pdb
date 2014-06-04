<?
namespace pdb\UnitTests;

class MockTests{
	static function factory($array = false){
		if ($array == false){
			$array = array(
				array("id" => 1, "city" => "London"	),
				array("id" => 2, "city" => "Bonn"	),
				array("id" => 3, "city" => "Boston"	),
			);
		}
		
		return new \pdb\Mock($array);
	}

	static function test(\pdb\SQL $db){
		$result = $db->query("select * from bla", array(), "city");

		assert($result->affectedRows() == 3);

		$res = iterator_to_array($result);

		//echo "You should see SQL result as array:\n";
		//print_r($res);

		assert($res["Bonn"]["id"  ] == 2      );
		assert($res["Bonn"]["city"] == "Bonn" );


		// ====================


		$result = $db->query("select * from bla", array() );

		assert($result->affectedRows() == 3);

		$res = iterator_to_array($result);

		//echo "You should see SQL result as array:\n";
		//print_r($res);

		assert($res[1]["id"  ] == 2      );
		assert($res[1]["city"] == "Bonn" );
	}
}

