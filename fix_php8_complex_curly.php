<?php

$files = array_slice($argv, 1);

foreach ($files as $file) {
    $code = file_get_contents($file);

    $original = $code;

    // Convierte:
    // $array{$x}
    // a:
    // $array[$x]
    $code = preg_replace(
        '/(\$[A-Za-z_][A-Za-z0-9_]*)\{([^{}]+)\}/',
        '$1[$2]',
        $code
    );

    // Convierte:
    // }{"x"}
    // a:
    // ]["x"]
    $code = preg_replace(
        '/\}\{"([^"]+)"\}/',
        ']["$1"]',
        $code
    );

    // Convierte:
    // }{\'x\'}
    // a:
    // ][\'x\']
    $code = preg_replace(
        '/\}\{\'([^\']+)\'\}/',
        '][\'$1\']',
        $code
    );

    if ($code !== $original) {
        file_put_contents($file, $code);
        echo "fixed: $file\n";
    }
}
