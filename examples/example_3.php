<?php
	include("/path/to/class.php-csv-parser.php");
	$csv_parser = new PhpCSV\PhpCSV_Parser();
	if($csv_parser){
		$csv_parser->from_string('#Welcome\n"1","2","3","4"\n"a","b","c","d"',array("rowDelimiter"=>'\n'));
		$csv_parser->on(PhpCSV\PhpCSV_Parser::EVENT_ON_RECORD, "on_record")
					->on(PhpCSV\PhpCSV_Parser::EVENT_ON_ERROR, "on_error")
					->on(PhpCSV\PhpCSV_Parser::EVENT_ON_CLOSE, "on_close")
					->on(PhpCSV\PhpCSV_Parser::EVENT_ON_END, "on_end")
					->on(PhpCSV\PhpCSV_Parser::EVENT_ON_DATA, "on_data")
					->parse()
					->transform("transform_callback");
		var_dump($csv_parser->to("array"));
	}

	function on_record(array $row, $index){
		echo "On Record fired: ".implode(" . ", $row)." @ index $index<br />";
	}
	function on_error(\PhpCSV\PhpCSV_Parser_Exception $exception){
		echo "On Error fired: ".$exception->getFullMessage()."<br />";
	}
	function on_close($count){
		echo "On Close fired: Successfully wrote $count records to the file<br />";
	}
	function on_end($count){
		echo "On End fired: Done processing $count records :)<br />";
	}
	function on_data($row, $index){
		echo "On Data fired: ".implode(" . ", $row)." @ index $index<br />";
	}
	function to_callback(array $rows){
		foreach($rows as $row){
			echo implode(" | ", $row)."<br />";
		}
	}
	function transform_callback($parsed_row, $index){
		$parsed_row[] = array_sum($parsed_row);
		return $parsed_row;
	}
?>