<?php
    include("../src/Emmanix2002/PhpCSVParser.php");

    use Emmanix2002\PhpCSV\PhpCSVParser;
    use Emmanix2002\PhpCSV\Enum\PhpCSVParserEventType;
    use Emmanix2002\PhpCSV\Exception\PhpCSVParserException;

    $csv_parser = new PhpCSVParser();
	if ($csv_parser)
    {
		$csv_parser->fromString('#Welcome\n"1","2","3","4"\n"a","b","c","d"',array("rowDelimiter"=>'\n'));
		$csv_parser->on(PhpCSVParserEventType::EVENT_ON_RECORD, "on_record")
					->on(PhpCSVParserEventType::EVENT_ON_ERROR, "on_error")
					->on(PhpCSVParserEventType::EVENT_ON_CLOSE, "on_close")
					->on(PhpCSVParserEventType::EVENT_ON_END, "on_end")
					->on(PhpCSVParserEventType::EVENT_ON_DATA, "on_data")
					->parse()
					->transform("transform_callback");
		var_dump($csv_parser->to("array"));
	}

	function on_record(array $row, $index){
		echo "On Record fired: ".implode(" . ", $row)." @ index $index<br />";
	}
	function on_error(PhpCSVParserException $exception){
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