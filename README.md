PHP CSV Parser
===============================
## Requirements

+ PHP 5.3 (it uses some PHP 5.3 specific functions like str_getcsv(...))

## WARNING

This class has not been fully tested to ensure it works as it should -- (it's a work in progress) ;-)

### UPDATE

It has now been tested on small data sets...Still remains to be seen how it performs on large data sets :)

## Examples

```php
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
```
```php
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
```
```php
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
```