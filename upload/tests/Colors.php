<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
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
 * Class Colors
 *
 * This class provides some helper methods to write strings to STDOUT and STDERR
 * using standard Unix terminal color codes.
 */
class Colors
{
    // foreground colors
    const FG_BLACK = '0;30';
    const FG_DARK_GRAY = '1;30';
    const FG_BLUE = '0;34';
    const FG_LIGHT_BLUE = '1;34';
    const FG_GREEN = '0;32';
    const FG_LIGHT_GREEN = '1;32';
    const FG_CYAN = '0;36';
    const FG_LIGHT_CYAN = '1;36';
    const FG_RED = '0;31';
    const FG_LIGHT_RED = '1;31';
    const FG_PURPLE = '0;35';
    const FG_LIGHT_PURPLE = '1;35';
    const FG_BROWN = '0;33';
    const FG_YELLOW = '1;33';
    const FG_LIGHT_GRAY = '0;37';
    const FG_WHITE = '1;37';

    // background colors
    const BG_BLACK = '40';
    const BG_RED = '41';
    const BG_GREEN = '42';
    const BG_YELLOW = '43';
    const BG_BLUE = '44';
    const BG_MAGENTA = '45';
    const BG_CYAN = '46';
    const BG_LIGHT_GRAY = '47';

    /**
     * @var Colors singleton variable
     */
    private static $_instance;

    /**
     * Returns singleton instance
     *
     * @return Colors
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * Returns a colored string
     *
     * @param $string
     * @param null $fg
     * @param null $bg
     * @return string
     */
    public static function get($string, $fg = null, $bg = null)
    {
        return sprintf('%s', self::getInstance()->getColoredString($string, $fg, $bg));
    }

    /**
     * Outputs a string to STDERR
     *
     * @param $string
     * @param null $fg
     * @param null $bg
     */
    public static function err($string, $fg = null, $bg = null)
    {
        fprintf(STDERR, '%s', self::getInstance()->getColoredString($string, $fg, $bg));
    }

    /**
     * Outputs a string to STDERR and adds an END OF LINE character at the end
     *
     * @param $string
     * @param null $fg
     * @param null $bg
     */
    public static function errln($string, $fg = null, $bg = null)
    {
        self::err($string . PHP_EOL, $fg, $bg);
    }

    /**
     * Outputs a green colored string to STDERR and adds an EOL character at the end
     *
     * @param $string
     * @param null $fg
     * @param null $bg
     */
    public static function errlng($string, $fg = null, $bg = null)
    {
        self::err($string . PHP_EOL, $fg ?: self::FG_LIGHT_GREEN, $bg);
    }

    /**
     * Outputs a yellow colored string to STDERR and adds an EOL character at the end
     *
     * @param $string
     * @param null $fg
     * @param null $bg
     */
    public static function errlny($string, $fg = null, $bg = null)
    {
        self::err($string . PHP_EOL, $fg ?: self::FG_YELLOW, $bg);
    }

    /**
     * Outputs a red colored string to STDERR and adds an EOL character at the end
     *
     * @param $string
     * @param null $fg
     * @param null $bg
     */
    public static function errlnr($string, $fg = null, $bg = null)
    {
        self::err($string . PHP_EOL, $fg ?: self::FG_LIGHT_RED, $bg);
    }

    /**
     * Outputs a blue colored string to STDERR and adds an EOL character at the end
     *
     * @param $string
     * @param null $fg
     * @param null $bg
     */
    public static function errlnb($string, $fg = null, $bg = null)
    {
        self::err($string . PHP_EOL, $fg ?: self::FG_LIGHT_BLUE, $bg);
    }

    /**
     * Outputs a purple colored string to STDERR and adds an EOL character at the end
     *
     * @param $string
     * @param null $fg
     * @param null $bg
     */
    public static function errlnp($string, $fg = null, $bg = null)
    {
        self::err($string . PHP_EOL, $fg ?: self::FG_LIGHT_PURPLE, $bg);
    }

    /**
     * Outputs a cyan colored string to STDERR and adds an EOL character at the end
     *
     * @param $string
     * @param null $fg
     * @param null $bg
     */
    public static function errlnc($string, $fg = null, $bg = null)
    {
        self::err($string . PHP_EOL, $fg ?: self::FG_LIGHT_CYAN, $bg);
    }

    /**
     * Outputs a string to STDOUT
     *
     * @param $string
     * @param null $fg
     * @param null $bg
     */
    public static function out($string, $fg = null, $bg = null)
    {
        printf('%s', self::getInstance()->getColoredString($string, $fg, $bg));
    }

    /**
     * Outputs a string to STDOUT and adds an END OF LINE character at the end.
     *
     * @param $string
     * @param null $fg
     * @param null $bg
     */
    public static function outln($string, $fg = null, $bg = null)
    {
        self::out($string . PHP_EOL, $fg, $bg);
    }

    /**
     * Returns colored string
     *
     * @param $string
     * @param null $fg_color
     * @param null $bg_color
     * @return string
     */
    public function getColoredString($string, $fg_color = null, $bg_color = null)
    {
        $colored_string = '';

        // Check if given foreground color found
        if ($fg_color) {
            $colored_string .= "\033[${fg_color}m";
        }
        // Check if given background color found
        if ($bg_color) {
            $colored_string .= "\033[${bg_color}m";
        }

        // Add string and end coloring
        $colored_string .= $string . "\033[0m";

        return $colored_string;
    }
}
