<?php
	include("/path/to/class.php-csv-parser.php");
	$csv_parser = new PhpCSV\PhpCSV_Parser();
	$csv_parser_2 = new PhpCSV\PhpCSV_Parser();
	$csv_parser_3 = new PhpCSV\PhpCSV_Parser();
	if($csv_parser){
		var_dump($csv_parser->from_string('#Welcome\n"1","2","3","4"\n"a","b","c","d"',array("rowDelimiter"=>'\n'))
					->parse()
					->to("array"));
		var_dump($csv_parser_2->from_string('#Welcome\n"1","2","3","4"\n"a","b","c","d"',array("rowDelimiter"=>"\\n"))
					->parse()
					->to("string"));
		var_dump($csv_parser_3->from_string('#Welcome\n"1","2","3","4"\n"a","b","c","d"',array("rowDelimiter"=>"\\n"))
					->parse()
					->to("to_callback"));
	}
	function to_callback(array $rows){
		foreach($rows as $row){
			echo implode(" | ", $row)."<br />";
		}
	}
?>