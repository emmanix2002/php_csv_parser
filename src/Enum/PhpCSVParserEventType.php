<?php
    namespace Emmanix2002\PhpCSV\Enum;

    class PhpCSVParserEventType
    {
        /**
         * The record event -- fired while parsing each row of the CSV data
         */
        const EVENT_ON_RECORD = 'record';
        /**
         * The data event -- fired on each line(row) once the data has been transformed and stringified
         */
        const EVENT_ON_DATA = 'data';
        /**
         * The close event -- fired when using the to_stream() after the file written to has been closed
         */
        const EVENT_ON_CLOSE = 'close';
        /**
         * The end event --  fired after the CSV file has been completely parsed
         */
        const EVENT_ON_END = 'end';
        /**
         * The error event -- fired whenever an error occurs
         */
        const EVENT_ON_ERROR = 'error';
    }