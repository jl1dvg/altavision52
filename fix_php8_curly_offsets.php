<?php

$files = array_slice($argv, 1);

foreach ($files as $file) {
    $code = file_get_contents($file);
    $tokens = token_get_all($code);
    $out = '';

    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {
        $token = $tokens[$i];

        if (
            is_array($token)
            && $token[0] === T_VARIABLE
            && isset($tokens[$i + 1], $tokens[$i + 2], $tokens[$i + 3])
            && $tokens[$i + 1] === '{'
            && is_array($tokens[$i + 2])
            && in_array($tokens[$i + 2][0], [T_CONSTANT_ENCAPSED_STRING, T_LNUMBER, T_STRING], true)
            && $tokens[$i + 3] === '}'
        ) {
            $out .= $token[1] . '[' . $tokens[$i + 2][1] . ']';
            $i += 3;
            continue;
        }

        $out .= is_array($token) ? $token[1] : $token;
    }

    if ($out !== $code) {
        file_put_contents($file, $out);
        echo "fixed: $file\n";
    }
}
