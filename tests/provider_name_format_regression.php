<?php

    $failures = [];
    $patientInc = file_get_contents(__DIR__ . '/../library/patient.inc');
    $functionsToLoad = [
        'formatProviderNameFromRow',
        'formatProviderNameParts',
        'providerNameFormatLegacyName',
        'providerNameJoinParts',
        'providerNamePartsFromRow',
        'providerNameLastNameParts',
    ];

    foreach ($functionsToLoad as $functionName) {
        eval(extractFunctionSource($patientInc, $functionName));
    }

    function extractFunctionSource($source, $functionName)
    {
        $pattern = '/function\s+' . preg_quote($functionName, '/') . '\s*\(/';
        if (!preg_match($pattern, $source, $match, PREG_OFFSET_CAPTURE)) {
            throw new RuntimeException('Function not found: ' . $functionName);
        }

        $start = $match[0][1];
        $braceStart = strpos($source, '{', $start);
        if ($braceStart === false) {
            throw new RuntimeException('Function body not found: ' . $functionName);
        }

        $depth = 0;
        $length = strlen($source);
        for ($i = $braceStart; $i < $length; $i++) {
            if ($source[$i] === '{') {
                $depth++;
            } elseif ($source[$i] === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $start, $i - $start + 1);
                }
            }
        }

        throw new RuntimeException('Function body is incomplete: ' . $functionName);
    }

    function assertSameValue($label, $expected, $actual)
    {
        global $failures;
        if ($expected !== $actual) {
            $failures[] = $label . ': expected [' . $expected . '] got [' . $actual . ']';
        }
    }

    function assertNoDoubleSpaces($label, $actual)
    {
        global $failures;
        if (strpos($actual, '  ') !== false) {
            $failures[] = $label . ': contains double spaces [' . $actual . ']';
        }
    }

    function providerParts($row)
    {
        return providerNamePartsFromRow($row);
    }

    $cases = [
        'fname + lname' => [
            ['fname' => 'John', 'mname' => '', 'lname' => 'Doe'],
            'clinical' => 'John Doe',
            'legacy_name' => 'John Doe',
            'legacy_concat' => 'John Doe',
            'apellido_1' => 'Doe',
            'apellido_2' => ''
        ],
        'fname + lname + lname2' => [
            ['fname' => 'John', 'mname' => '', 'lname' => 'Doe', 'lname2' => 'Smith'],
            'clinical' => 'John Doe Smith',
            'legacy_name' => 'John Doe Smith',
            'legacy_concat' => 'John Doe Smith',
            'apellido_1' => 'Doe',
            'apellido_2' => 'Smith'
        ],
        'fname + mname + lname + lname2' => [
            ['fname' => 'John', 'mname' => 'Paul', 'lname' => 'Doe', 'lname2' => 'Smith'],
            'clinical' => 'John Paul Doe Smith',
            'legacy_name' => 'John Doe Smith',
            'legacy_concat' => 'John Paul Doe Smith',
            'apellido_1' => 'Doe',
            'apellido_2' => 'Smith'
        ],
        'lname2 NULL' => [
            ['fname' => 'John', 'mname' => '', 'lname' => 'Doe', 'lname2' => null],
            'clinical' => 'John Doe',
            'legacy_name' => 'John Doe',
            'legacy_concat' => 'John Doe',
            'apellido_1' => 'Doe',
            'apellido_2' => ''
        ],
        'lname2 empty' => [
            ['fname' => 'John', 'mname' => '', 'lname' => 'Doe', 'lname2' => ''],
            'clinical' => 'John Doe',
            'legacy_name' => 'John Doe',
            'legacy_concat' => 'John Doe',
            'apellido_1' => 'Doe',
            'apellido_2' => ''
        ],
        'additional spaces' => [
            ['fname' => ' John ', 'mname' => ' Paul ', 'lname' => ' Doe ', 'lname2' => ' Smith '],
            'clinical' => 'John Paul Doe Smith',
            'legacy_name' => 'John Doe Smith',
            'legacy_concat' => 'John Paul Doe Smith',
            'apellido_1' => 'Doe',
            'apellido_2' => 'Smith'
        ],
        'historical compound lname' => [
            ['fname' => 'Juan', 'mname' => '', 'lname' => 'de la Cruz Perez', 'lname2' => ''],
            'clinical' => 'Juan de la Cruz Perez',
            'legacy_name' => 'Juan de la Cruz Perez',
            'legacy_concat' => 'Juan de la Cruz Perez',
            'apellido_1' => 'de la Cruz',
            'apellido_2' => 'Perez'
        ],
        'legacy suffix handling' => [
            ['fname' => 'John', 'mname' => 'Paul', 'lname' => 'Doe', 'lname2' => 'Smith', 'suffix' => 'Dr.'],
            'clinical' => 'John Paul Doe Smith',
            'legacy_name' => 'Dr. John Doe Smith',
            'legacy_concat' => 'John Paul Doe Smith',
            'apellido_1' => 'Doe',
            'apellido_2' => 'Smith'
        ],
    ];

    foreach ($cases as $label => $case) {
        $parts = providerParts($case[0]);

        assertSameValue($label . ' apellido_1', $case['apellido_1'], $parts['apellido_1']);
        assertSameValue($label . ' apellido_2', $case['apellido_2'], $parts['apellido_2']);
        assertSameValue($label . ' clinical', $case['clinical'], formatProviderNameParts($parts));
        assertSameValue($label . ' legacy_name', $case['legacy_name'], formatProviderNameFromRow($case[0], 'legacy_name'));
        assertSameValue($label . ' legacy_concat', $case['legacy_concat'], formatProviderNameFromRow($case[0], 'legacy_concat'));

        assertNoDoubleSpaces($label . ' clinical', formatProviderNameParts($parts));
        assertNoDoubleSpaces($label . ' legacy_name', formatProviderNameFromRow($case[0], 'legacy_name'));
        assertNoDoubleSpaces($label . ' legacy_concat', formatProviderNameFromRow($case[0], 'legacy_concat'));
    }

    if ($failures) {
        fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
        exit(1);
    }

    echo "Provider name format regression tests passed." . PHP_EOL;
