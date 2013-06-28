<?php
	include("C:/Users/Emmanuel/Documents/GitHub/php_csv_parser/class.php-csv-parser.php");
	$csv_parser = new PhpCSV\PhpCSV_Parser();
	if($csv_parser){
		var_dump($csv_parser->from_path('C:/Users/Emmanuel/Documents/GitHub/php_csv_parser/examples/csv_1.csv')
					->parse()
					->to("C:/Users/Emmanuel/Documents/GitHub/php_csv_parser/examples/csv_created_by_to_path.csv"));

	}
?>