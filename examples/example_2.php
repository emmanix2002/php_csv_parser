<?php
    include("../src/Emmanix2002/PhpCSVParser.php");

    use Emmanix2002\PhpCSV\PhpCSVParser;

    $csv_parser = new PhpCSVParser();
	$csv_parser_2 = new PhpCSVParser();
	$csv_parser_3 = new PhpCSVParser();
	if ($csv_parser)
    {
		var_dump(
            $csv_parser->fromString(
                            '#Welcome\n"1","2","3","4"\n"a","b","c","d"',
                            array("rowDelimiter"=>'\n')
                        )
					->parse()
					->to('array')
        );
    }
    if ($csv_parser_2)
    {
		var_dump($csv_parser_2->fromString('#Welcome\n"1","2","3","4"\n"a","b","c","d"',array("rowDelimiter"=>"\\n"))
					->parse()
					->to("string"));
    }
    if ($csv_parser_3)
    {
		var_dump($csv_parser_3->fromString('#Welcome\n"1","2","3","4"\n"a","b","c","d"',array("rowDelimiter"=>"\\n"))
					->parse()
					->to("to_callback"));
	}
	function to_callback(array $rows){
		foreach($rows as $row){
			echo implode(" | ", $row)."<br />";
		}
	}