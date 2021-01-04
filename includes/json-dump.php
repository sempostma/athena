<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class JSON_Dump
{
    public static function dump($value) {
        echo '<pre><code>';
        JSON_Dump::json_dump_recursive($value);
        echo '</code></pre>';
    }

    private static function json_dump_recursive($value) {
        if (!is_array($value)) return;
        echo '{<br>';
        foreach ($value as $key => $value) {
            echo htmlspecialchars($key);
            echo ": ";
            if (is_string($value)) {
                if(filter_var('http://example.com', FILTER_VALIDATE_URL)) {
                    echo htmlspecialchars(json_encode($value));
                } else {
                    echo htmlspecialchars(json_encode($value));
                }
            } else if (is_numeric($value)) {
                echo $value;
            } else if (is_null($value)) {
                echo 'null';
            } else if (is_bool($value)) {
                echo $value ? 'true' : 'false';
            } else {
                JSON_Dump::json_dump_recursive($value);
            }
            echo '<br>';
        }
        echo '}';
    }
}
