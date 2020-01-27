<?php

require_once 'vendor/autoload.php';

try {
    unset($argv[0]);

    $className = '\\src\\' . ucfirst(array_shift($argv));

    if (!class_exists($className)) {
        throw new \Exception('Command "' . $className . '" not found. Please write "php cli.php help" for check all commands.');
    }

    $params = [];
    foreach ($argv as $argument) {
        preg_match('/^-(.+)=(.+)$/', $argument, $matches);
        if (!empty($matches)) {
            $paramName = $matches[1];
            $paramValue = $matches[2];

            $params[$paramName] = $paramValue;
        }
    }

    $class = new $className($params);
    $class->execute();

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}