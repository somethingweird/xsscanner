<?php

if (! isset($argv[1]) || ! file_exists($argv[1])) {
    exit;
}
$lookup_variable = array();
$toks = token_get_all(file_get_contents($argv[1]));
$build_tag = array();
for ($i = 0; $i < count($toks); $i++) {
    $token = $toks[$i];
    if ($token == ';' || 
        (is_array($token) && token_name($token[0]) == 'T_CLOSE_TAG')) {
        // echo '**NEW COMMAND',"\n";
        analyzeMe($build_tag);
        $build_tag = array();
    } else if (is_array($token) && token_name($token[0]) == 'T_INLINE_HTML') {
        // we can ignore inline html for now
    } else if (is_array($token) && token_name($token[0]) == 'T_WHITESPACE') {
        // and whitespaces
    } else if (is_array($token) && token_name($token[0]) == 'T_OPEN_TAG') {
        // ignore open tags;
    } else if (is_array($token) && token_name($token[0]) == 'T_OPEN_TAG_WITH_ECHO') {
        $build_tag[] = array(
            'line_number' => $token[2],
            'type' => token_name($token[0]),
            'value' => 'echo'
        );    
    } else {
        if (is_array($token)) {
            $build_tag[] = array(
                'line_number' => $token[2],
                'type' => token_name($token[0]),
                'value' => $token[1]
            );    
        } else {
            $build_tag[] = array(
                'line_number' => '',
                'type' => $token,
                'value' => $token
            );
        }
    }
};

function analyzeMe($array) {
    $clean = true;
    $ln = array_filter(array_unique(array_column($array, 'line_number')));
    $types = array_column($array, 'type');
    $values = array_column($array, 'value');
    // does it have a $_GET / $_POST / $_REQUEST
    $method_hit = array('$_GET', '$_POST', '$_REQUEST');
    foreach ($method_hit as $k => $v) {
        if (in_array($v, $values)) {
            if (! in_array('htmlspecialchars', $values) && in_array('echo', $values)) {
                echo 'lines: '. join(',', $ln);
                echo " ( ";
                echo join (' ',$values);
                echo " ) \n";
                $clean = false;
            } else
            if (! in_array('htmlspecialchars', $values) && in_array('printf', $values)) {
                echo 'lines: '. join(',', $ln);
                echo " ( ";
                echo join (' ',$values);
                echo " ) \n";
                $clean = false;
            }
        }        
    }
    // it's clean
    return $clean;
}
