<?php

return [
    /**
     *  Cast mapping
     */
    'casts' => [
        'array' => 'json',
        'bool' => 'boolean',
        'float' => 'number_decimal',
        'int' => 'number_integer',
        'object' => 'json',
        'string' => 'single_line_text_field',
    ],
];
