<?php
/**
 * ###############################################
 *
 * Kayako Classic
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

/**
 * This program outputs a coverage report from a coverage file
 */

require __DIR__ . '/../vendor/autoload.php';

$isMD = false;
$isCSV = false;
$isTab = false;
$isCol = true;
$isDir = true;
$sep = '';
$filter = '';

$shortopts = 'hf:ncmtrs:d:';
$options = getopt($shortopts, []);

if (isset($options['h'])) {
    echo "\nUsage ${argv[0]} [-h] [-n] [-c|-t|-r|-m] [-s STRING] [-d NUM_DIRS] -f COVERAGE_FILE\n\n";
    echo "  -f COVERAGE_FILE  Coverage file generated by 'phpunit --coverage-php'\n";
    echo "  -h                Show this help message\n";
    echo "  -n                Show coverage by files instead of directories\n";
    echo "  -c                Show comma separated report\n";
    echo "  -m                Show markdown formatted report\n";
    echo "  -t                Show TAB separated report\n";
    echo "  -r                Show report in even sized columns. This is the default\n";
    echo "  -s STRING         Filter report by lines containing STRING\n";
    echo "  -d NUM_DIRS       Group report by number of subdirectories. Default is 3\n";
    echo "\n";
    exit(0);
}

$fileName = __DIR__ . '/logs/php54/coverage.php';

if (isset($options['f'])) {
    $fileName = $options['f'];
}

if (!file_exists($fileName)) {
    echo sprintf("ERROR. File not found: %s\n", $fileName);
    exit(1);
}

/** @var PHP_CodeCoverage $coverage */
$coverage = include $fileName;

if (isset($options['c'])) {
    $isCSV = true;
    $isCol = false;
    $isMD = false;
    $isTab = false;
    $sep = ',';
}

if (isset($options['m'])) {
    $isMD = true;
    $isCSV = false;
    $isCol = false;
    $isTab = false;
    $sep = ' | ';
}

if (isset($options['t'])) {
    $isTab = true;
    $isCSV = false;
    $isMD = false;
    $isCol = false;
    $sep = "\t";
}

if (isset($options['n'])) {
    $isDir = false;
}

$numDirs = (isset($options['d']) && (int)$options['d'] > 0) ? (int)$options['d'] : 3;

$data = $coverage->getData();
$dirs = [];
$maxCol = [0, 0, 0];

try {
    $class = new \ReflectionClass('PHP_CodeCoverage');
    $prop = $class->getProperty('ignoredLines');
    $prop->setAccessible(true);
    $ignoredLines = $prop->getValue($coverage);
} catch (ReflectionException $e) {
    $ignoredLines = [];
}

foreach ($data as $file => $cover) {
    if (isset($options['s']) && (false === strpos($file, $options['s']))) {
        continue;
    }

    if (array_key_exists($file, $ignoredLines)) {
        continue;
    }

    if ($isDir) {
        $dirname = dirname($file);
        while (substr_count($dirname, '/') > $numDirs) {
            $dirname = dirname($dirname);
        }
    } else {
        $dirname = $file;
    }

    if (!isset($dirs[$dirname])) {
        $dirs[$dirname] = [0, 0];
    }

    foreach ($cover as $ln => $lines) {
        if ($lines !== null) {
            $dirs[$dirname][1]++;
            if (count($lines) > 0) { // is line covered by tests?
                $dirs[$dirname][0]++;
            }
        }
    }
}

ksort($dirs);

if ($isMD) {
    \Colors::errln('| Class | LOC | Total | %');
    \Colors::errln('| --- | --- | --- | ---');
}

foreach ($dirs as $dir => $counters) {
    if ($isCol) {
        if (strlen($dir) > $maxCol[0]) {
            $maxCol[0] = strlen($dir);
        }
        if (strlen($counters[0]) > $maxCol[1]) {
            $maxCol[1] = strlen($counters[0]);
        }
        if (strlen($counters[1]) > $maxCol[2]) {
            $maxCol[2] = strlen($counters[1]);
        }
    } else {
        \Colors::err(sprintf('%s%s%s', $isMD ? '| ' : '', $dir, $sep), \Colors::FG_LIGHT_GRAY);
        \Colors::err(sprintf('%d%s', $counters[0], $sep), \Colors::FG_LIGHT_GREEN);
        \Colors::err(sprintf('%d%s', $counters[1], $sep), \Colors::FG_LIGHT_PURPLE);
        \Colors::errln(sprintf('%.2f%%', round(($counters[1] === 0)? 0: ($counters[0] * 100 / $counters[1]), 2)), \Colors::FG_LIGHT_CYAN);
    }
}

if ($isCol) {
    foreach ($dirs as $dir => $counters) {
        \Colors::err(sprintf('%s', str_pad($dir, $maxCol[0] + 1, ' ')), \Colors::FG_LIGHT_GRAY);
        \Colors::err(sprintf('%s', str_pad($counters[0], $maxCol[1] + 2, ' ', STR_PAD_LEFT)), \Colors::FG_LIGHT_GREEN);
        \Colors::err(sprintf('%s', str_pad($counters[1], $maxCol[2] + 2, ' ', STR_PAD_LEFT)),
            \Colors::FG_LIGHT_PURPLE);
        \Colors::errln(sprintf('%s%%',
            str_pad(sprintf('%.2f', round($counters[0] * 100 / $counters[1], 2)), 8, ' ', STR_PAD_LEFT)),
            \Colors::FG_LIGHT_CYAN);
    }
}