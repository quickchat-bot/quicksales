<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Ravi Sharma <ravi.sharma@kayako.com>
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2015, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */
require_once('./' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_THIRDPARTY_DIRECTORY . '/emoji/class.emoji.php');

/**
 * Convert emoji characters to the string and vice versa
 *
 * @author Ravi Sharma <ravi.sharma@kayako.com>
 */
class SWIFT_Emoji extends SWIFT_Library
{

    protected $emoji = null;

    /**
     * Constructor
     *
     * @author Ravi Sharma <ravi.sharma@kayako.com>
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->emoji = new Emoji\Emoji();
    }

    /**
     * Encode the emoji characters to the string
     *
     * @author Ravi Sharma <ravi.sharma@kayako.com>
     *
     * @param string $_content
     *
     * @throws SWIFT_Exception
     * @return string The encoded string
     */
    public function encode($_content)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->emoji->encode($_content);
    }

    /**
     * Decode the the string to emoji characters
     *
     * @author Ravi Sharma <ravi.sharma@kayako.com>
     *
     * @param string $_content
     *
     * @throws SWIFT_Exception
     * @return string The encoded string
     */
    public function decode($_content)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->emoji->decode($_content);
    }
}