<?php
/**
 *  *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Varun Shoor
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2014, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 *  */

/**
 * Convert Accented Characters to base characters.
 *
 * @author Mansi Wason
 */
class SWIFT_StringConverter extends SWIFT_Library
{
    private $URLify;

    /**
     * @author Mansi Wason
     */
    public function __construct()
    {
        parent::__construct();

        $this->URLify = new URLify();
    }

    /**
     * Converts accented characters within the specified text to utf-8 compatible characters
     *
     * @author Mansi Wason
     */
    public function ConvertAccented($text)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->URLify->downcode($text);
    }
}