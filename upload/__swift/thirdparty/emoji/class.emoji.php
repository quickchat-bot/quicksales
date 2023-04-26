<?php
/**
 * @author         Ravi Sharma <ravi.sharma@kayako.com>
 *
 * @package        SWIFT
 * @copyright      2001-2015 Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 */

namespace Emoji;

/**
 * The Emoji class
 *
 * @author Ravi Sharma <ravi.sharma@kayako.com>
 */
class Emoji
{
    /**
     * @var array
     */
    protected $unifiedToVariables = null;

    /**
     * @var array
     */
    protected $unifiedToHtml = null;

    /**
     * @var array
     */
    protected $variablesToUnified = null;

    /**
     * @var array
     */
    protected $variablesToHtml = null;

    /**
     * Emoji encode to #1f601#
     *
     * @param  string $text
     *
     * @return string
     */
    public function encode($text)
    {
        $unifiedToVariables = $this->getUnifiedToVariables();

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-4992 \X strips off everything which follows it.
         */
        $text = preg_replace_callback("/(\\\\x..)/isU", function ($m) {
            if (ctype_xdigit($m[0])) {
                return chr(hexdec(substr($m[0], 2)));
            } else {
                return $m[0];
            }
        }, $text);

        return str_replace(array_keys($unifiedToVariables), $unifiedToVariables, $text);
    }

    /**
     * Emoji decode to \xF0\x9F\x98\x81
     *
     * @param  string $text
     *
     * @return string
     */
    public function decode($text)
    {
        $variablesToUnified = $this->getVariablesToUnified();

        return str_replace(array_keys($variablesToUnified), $variablesToUnified, $text);
    }

    /**
     * Unified to html
     * "\xF0\x9F\x98\x81" to "<span class="emoji emoji1f601"></span>"
     *
     * @param  string $text
     *
     * @return string
     */
    public function unifiedToHtml($text)
    {
        $unifiedToHtml = $this->getUnifiedToHtml();

        return str_replace(array_keys($unifiedToHtml), $unifiedToHtml, $text);
    }

    /**
     * Variables convert to html
     * #1f601# to "<span class="emoji emoji1f601"></span>"
     *
     * @param  string $text
     *
     * @return text
     */
    public function variablesToHtml($text)
    {
        $variablesToHtml = $this->getVariablesToHtml();

        return str_replace(array_keys($variablesToHtml), $variablesToHtml, $text);
    }

    /**
     * Get unified to variables
     *
     * @return array
     */
    public function getUnifiedToVariables()
    {
        if ($this->unifiedToVariables === null) {
            $this->setUnifiedToVariables(include 'emoji.maps.php');
        }

        return $this->unifiedToVariables;
    }

    /**
     * Set unified to variables
     *
     * @param  array $unifiedToVariables
     *
     * @return Emoji
     */
    public function setUnifiedToVariables(array $unifiedToVariables)
    {
        $this->unifiedToVariables = $unifiedToVariables;

        return $this;
    }

    /**
     * Get unified to html
     *
     * @return array|null
     */
    public function getUnifiedToHtml()
    {
        if ($this->unifiedToHtml === null) {
            $unifiedToHtml = array();
            foreach ($this->getUnifiedToVariables() as $unified => $variable) {
                if ($html = $this->variableToHtml($variable)) {
                    $unifiedToHtml[$unified] = $html;
                }
            }
            $this->setUnifiedToHtml($unifiedToHtml);
        }

        return $this->unifiedToHtml;
    }

    /**
     * Set unified to html
     *
     * @param  array $unifiedToHtml
     *
     * @return Emoji
     */
    public function setUnifiedToHtml(array $unifiedToHtml)
    {
        $this->unifiedToHtml = $unifiedToHtml;

        return $this;
    }

    /**
     * Get variable to html
     *
     * @return array
     */
    public function getVariablesToHtml()
    {
        if ($this->variablesToHtml === null) {
            $variablesToHtml = array();
            foreach ($this->getUnifiedToVariables() as $variable) {
                if ($html = $this->variableToHtml($variable)) {
                    $variablesToHtml[$variable] = $html;
                }
            }
            $this->setVariablesToHtml($variablesToHtml);
        }

        return $this->variablesToHtml;
    }

    /**
     * Set variables to html
     *
     * @param  array $variablesToHtml
     *
     * @return Emoji
     */
    public function setVariablesToHtml(array $variablesToHtml)
    {
        $this->variablesToHtml = $variablesToHtml;

        return $this;
    }

    /**
     * A variable to html
     *
     * @param  string $variable
     *
     * @return string|false
     */
    protected function variableToHtml($variable)
    {
        if ($match = preg_match('/^#(.+?)#$/', $variable, $matches)) {
            return sprintf('<span class="emoji emoji%s"></span>', $matches[1]);
        }

        return false;
    }

    /**
     * Get variables to unified
     *
     * @return array
     */
    public function getVariablesToUnified()
    {
        if ($this->variablesToUnified === null) {
            $this->setVariablesToUnified(array_flip($this->getUnifiedToVariables()));
        }

        return $this->variablesToUnified;
    }

    /**
     * Set variables to unified
     *
     * @param  array $variablesToUnified
     *
     * @return Emoji
     */
    public function setVariablesToUnified(array $variablesToUnified)
    {
        $this->variablesToUnified = $variablesToUnified;

        return $this;
    }
}
