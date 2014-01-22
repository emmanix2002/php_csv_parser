<?php
	include("../src/Emmanix2002/PhpCSVParser.php");

    use Emmanix2002\PhpCSV\PhpCSVParser;

	$csv_parser = new PhpCSVParser();
	if($csv_parser){
		var_dump(
            $csv_parser->fromPath(__DIR__.'/csv_1.csv')
					->parse()
					->to(__DIR__.'/csv_created_by_to_path.csv')
        );
	}