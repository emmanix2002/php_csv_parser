<?php
	include("/path/to/class.php-csv-parser.php");
	$csv_parser = new PhpCSV\PhpCSV_Parser();
	if($csv_parser){
		var_dump($csv_parser->from_path('/path/to/examples/csv_1.csv')
					->parse()
					->to("/path/to/examples/csv_created_by_to_path.csv"));

	}
?>