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

use Composer\Script\Event;
use Composer\Package\Package;

/**
 * Class Setup runs commands in composer
 *
 * @author Werner Garcia <werner.garcia@crossover.com>
 */
class Setup
{
    /**
     * Patches CodeCoverage.php file so phpunit with code coverage can run
     *
     * @param Event $event
     * @throws \RuntimeException
     */
    public static function patchCodeCoverage(Event $event)
    {
        if (!$event->isDevMode()) {
            // Ignore if it is in prod environment
            return;
        }

        // get DIR from autoload file
        $file = __DIR__ . '/../vendor/phpunit/php-code-coverage/src/CodeCoverage.php';
        $text = file_get_contents($file);

        if ($text === false) {
            echo 'CodeCoverage does not exist: ' . $file . PHP_EOL;
            return;
        }

        if (false !== strpos($text, 'CODE COVERAGE PATCH BEGINS')) {
            echo 'CodeCoverage is already patched!' . PHP_EOL;
            return;
        }

        $_PATCH = self::getPatchCode('uncoveredFile');

        $f = fopen($file, 'r+');
        $specificLine = 'private function processUncoveredFileFromWhitelist';
        $ok = false;
        while (($buffer = fgets($f)) !== false) {
            if (strpos($buffer, $specificLine) !== false) {
                // advance 1 line
                fgets($f);
                $pos = ftell($f);
                $newstr = substr_replace($text, $_PATCH, $pos, 0);
                $ok = file_put_contents($file, $newstr);
                break;
            }
        }

        if (false === $ok) {
            // try with newer codecoverage version

            $_PATCH = self::getPatchCode('file', 'continue');

            $specificLine = 'private function initializeData(): void';
            fseek($f, 0);
            while (($buffer = fgets($f)) !== false) {
                if (strpos($buffer, $specificLine) !== false) {
                    \Colors::errlny('Detected new codecoverage version');
                    break;
                }
            }
            $specificLine = 'if ($this->filter->isFile($file)) {';
            while (($buffer = fgets($f)) !== false) {
                if (strpos($buffer, $specificLine) !== false) {
                    $pos = ftell($f);
                    $newstr = substr_replace($text, $_PATCH, $pos, 0);
                    $ok = file_put_contents($file, $newstr);
                }
            }
        }

        fclose($f);

        if (false === $ok) {
            echo 'ERROR: I was unable to patch ' . $file . PHP_EOL;
        } else {
            echo 'CodeCoverage was succesfully patched!' . PHP_EOL;
        }
    }

    /**
     * Patches TestCase.php file so phpunit does not complain about risky tests
     *
     * @param Event $event
     * @throws \RuntimeException
     */
    public static function patchTestCase(Event $event)
    {
        if (!$event->isDevMode()) {
            // Ignore if it is in prod environment
            return;
        }

        // get DIR from autoload file
        $file = __DIR__ . '/../vendor/phpunit/phpunit/src/Framework/TestCase.php';
        $text = file_get_contents($file);

        if ($text === false) {
            echo 'TestCase does not exist: ' . $file . PHP_EOL;
            return;
        }

        if (false !== strpos($text, 'TESTCASE PATCH BEGINS')) {
            echo 'TestCase is already patched!' . PHP_EOL;
            return;
        }

        $newstr = preg_replace('/(private function stopOutputBuffering\(\): void\s+)\{(\s+)/',
            '\1{\2// TESTCASE PATCH BEGINS\2while(\\ob_get_level() < \$this->outputBufferingLevel){\\ob_start();}\2// TESTCASE PATCH ENDS\2', $text);

        $ok = file_put_contents($file, $newstr);

        if (false === $ok) {
            echo 'ERROR: I was unable to patch ' . $file . PHP_EOL;
        } else {
            echo 'TestCase was succesfully patched!' . PHP_EOL;
        }
    }

    /**
     * @param $_fileVar
     * @param $_continueStmt
     * @return string
     */
    protected static function getPatchCode($_fileVar, $_continueStmt = 'return')
    {
        $_PATCH = "
                    // CODE COVERAGE PATCH BEGINS
                    \$contents=file_get_contents(\$${_fileVar});\$namespace=\$class='';\$getting_namespace=\$getting_class=false;foreach(token_get_all(\$contents)as \$token){if(is_array(\$token)&&\$token[0]==T_NAMESPACE){\$getting_namespace=true;}
                        if(is_array(\$token)&&\$token[0]==T_CLASS){\$getting_class=true;}
                        if(\$getting_namespace===true){if(is_array(\$token)&&in_array(\$token[0],[T_STRING,T_NS_SEPARATOR])){\$namespace.=\$token[1];}
                        else if(\$token===';'){\$getting_namespace=false;}}
                        if(\$getting_class===true){if(is_array(\$token)&&\$token[0]==T_STRING){\$class=\$token[1];break;}}}
                    \$class_from_file = \$namespace?\$namespace.\"\\\\\".\$class:\$class;
                    \$classes = get_declared_classes();
                    if (in_array(\$class_from_file, \$classes)) {
                        // the class is already declared, skip it
                        ${_continueStmt};
                    }
                    // CODE COVERAGE PATCH ENDS
        \n";
        return $_PATCH;
    }
}
