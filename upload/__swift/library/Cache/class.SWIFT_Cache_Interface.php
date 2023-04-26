<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Abhishek Mittal
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2013, QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

interface SWIFT_Cache_Interface
{
    public function Set($_keyName, $_keyData, $_expiry);

    public function SetMultiple($_keyContainer);

    public function Get($_keyName);

    public function GetMultiple($_keyNameList);

    public function Flush();

    public function Delete($_keyName);

    public function DeleteMultiple($_keyNameList);
}