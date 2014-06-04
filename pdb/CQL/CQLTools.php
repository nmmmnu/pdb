<?
namespace pdb\CQL;


class CQLTools{
	static function fixType($type){
		$ns = "org.apache.cassandra.db.marshal.";
		if (substr($type, 0, strlen($ns)) == $ns)
			$type = substr($type, strlen($ns));

		return $type;
	}


	private static function decodeInteger($data){
		$base = 0;
		$sign = false;
		for($i = 0; $i < strlen($data); $i++){
			$byte = ord($data[$i]);

			// Check the sign
			if ($i == 0 && $byte  &  0b10000000)
				$sign = true;

			if ($sign)
				$byte = (~ $byte) & 0b11111111;

			//printf("%d %9b\n", $i, $byte);

			$base = $base * 256 + $byte;
		}

		if ($sign)
			$base = - ( $base + 1 );

		return $base;
	}

	static function decode($type, $data){
		switch(self::fixType($type)){
			case "Int32Type" :
				return current(unpack('l', strrev($data)));

			case "DecimalType" :
				$power = unpack('N', substr($data,  0,4));
				$power = $power[1];

				$base = self::decodeInteger(substr($data,  4));

				return $base * pow(10, - $power);

			case "LongType" :
				return self::decodeInteger($data);

			case "UUIDType" :
				return
					bin2hex(substr($data,  0,4)) . "-" .
					bin2hex(substr($data,  4,2)) . "-" .
					bin2hex(substr($data,  6,2)) . "-" .
					bin2hex(substr($data,  8,2)) . "-" .
					bin2hex(substr($data, 10,6));

			case "BooleanType" :
				return current(unpack('C', $data)) === 1 ? "true" : "false";

			case "FloatType" :
				return current(unpack("f", strrev($data)));

			case "AsciiType" :
			case "UTF8Type" :
				return $data;
		}

		return $data;
	}


	static function transformCassandraResults($data){
		$array = array();
print_r($data);
		foreach($data->rows as $r){
			$row = array();

			// add the columns
			foreach($r->columns as $column)
				if ($column->name){
					$type = $data->schema->value_types[$column->name];
					$value = self::decode($type, $column->value);

					if ($value)
						$row[$column->name] = $value;
				}

			$array[] = $row;
		}

		return $array;
	}

}

