PHP CSV Parser
===============================
## Requirements

+ PHP 5.3 (it uses some PHP 5.3 specific functions like str_getcsv(...))

## Examples

```php
    include("/path/to/class.php-csv-parser.php");
    $csv_parser = new PhpCSV\PhpCSV_Parser();
    if($csv_parser){
        var_dump($csv_parser->from_path('/path/to/examples/csv_1.csv')
                            ->to("/path/to/examples/csv_created_by_to_path.csv"));
    }
```
```php

```