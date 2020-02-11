<?php

namespace Twofed\SearchInFile;

use RecursiveDirectoryIterator, RecursiveIteratorIterator;

include 'File.php';
include 'SearchInFile.php';

date_default_timezone_set('Etc/GMT-3');

if (php_sapi_name() !== 'cli') throw new \Exception("Only CLI PHP available");

$needle = $argv[1];
if ($needle == "") throw new \Exception("No argument");

$searcher = new SearchInFile($needle);
$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(realpath(''), RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
$totalCount = 0;

$buffer = array();

foreach ($objects as $name) {
    $currentFile = new File($name);
    $result = $searcher->searchInFile($currentFile);

    if ($result) {
        echo sprintf("Строка была найдена в файле %s\n" , $currentFile->getFileName());
        echo sprintf("Число попаданий в файле: %s\n", count($result));
        $totalCount += count($result);
        echo "\n";

        array_push($buffer, sprintf("name: %s\n", $currentFile->getFileName()));

        array_walk($result, function($line, $key) {
            array_push($GLOBALS['buffer'], sprintf("line: %s\ncontent: %s", $key, $line));
        });

        array_push($buffer, sprintf("info: %s, %s, %s\n\n", $currentFile->getFileSize(), $currentFile->getFileCreated(), $currentFile->getFileModified()));
    }
}

foreach ($buffer as $logLine) file_put_contents('./log_' . date("d.m.Y") . '.log', $logLine, FILE_APPEND);

echo sprintf("Общее число строк: %s\n", $totalCount);
echo sprintf("Данные записаны в файл %s\n", 'log_' . date("d.m.Y") . '.log');
