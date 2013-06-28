PHP CSV Parser
===============================
## Requirements

+ PHP 5.3 (it uses some PHP 5.3 specific functions like str_getcsv(...))

## WARNING

This class has not been fully tested to ensure it works as it should -- (it's a work in progress) ;-)

## Examples

```php
    include("/path/to/class.php-csv-parser.php");
    $csv_parser = new PhpCSV\PhpCSV_Parser();
    if($csv_parser){
        var_dump($csv_parser->from_path('/path/to/examples/csv_1.csv')
                            ->parse()
                            ->to("/path/to/examples/csv_created_by_to_path.csv"));
    }
```
```php
    include("/path/to/class.php-csv-parser.php");
	$csv_parser = new PhpCSV\PhpCSV_Parser();
	$csv_parser_2 = new PhpCSV\PhpCSV_Parser();
	$csv_parser_3 = new PhpCSV\PhpCSV_Parser();
	if($csv_parser){
		var_dump($csv_parser->from_string('#Welcome\n"1","2","3","4"\n"a","b","c","d"',array("rowDelimiter"=>'\n'))
					->parse()
					->to("array"));
		var_dump($csv_parser_2->from_string('#Welcome\n"1","2","3","4"\n"a","b","c","d"',array("rowDelimiter"=>"\\n")) #note the \\
					->parse()
					->to("string"));
		var_dump($csv_parser_3->from_string('#Welcome\n"1","2","3","4"\n"a","b","c","d"',array("rowDelimiter"=>"\\n"))
					->parse()
					->to("to_callback"));

	}
```