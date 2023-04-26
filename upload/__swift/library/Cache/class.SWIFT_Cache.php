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
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

/**
 * Base Cache Layer Class
 *
 * @author Abhishek Mittal
 */
abstract class SWIFT_Cache extends SWIFT_Library
{
    const KEY_SEPARATOR  = '_';
    const DEFAULT_EXPIRY = 0;

    protected $_hash;

    /**
     * Constructor
     *
     * @author Abhishek Mittal
     *
     * @param string|bool $_customHash (OPTIONAL)
     *;
     * @throws SWIFT_Exception If Creation Fails
     */
    public function __construct($_customHash = false)
    {
        parent::__construct();

        $_hash = sprintf('%u', crc32(md5(DB_NAME . DB_USERNAME . DB_PASSWORD . TABLE_PREFIX)));
        if (!empty($_customHash)) {
            $_hash = $_customHash;
        }

        if (!empty($_hash)) {
            $this->SetHash($_hash);
        }
    }

    /**
     * Set Unique Hash Code
     *
     * @author Abhishek Mittal
     *
     * @param string $_hash
     *
     * @return SWIFT_Cache
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function SetHash($_hash)
    {
        if (empty($_hash)) {
            throw new SWIFT_Exception(__CLASS__ . ': ' . SWIFT_INVALIDDATA);
        }

        $this->_hash = $_hash;

        return $this;
    }

    /**
     * Get Unique Hash Code
     *
     * @author Abhishek Mittal
     *
     * @return string
     */
    public function GetHash()
    {
        return $this->_hash;
    }

    /**
     * Retrieve the Memcache object if available
     *
     * @author Abhishek Mittal
     * @return false
     */
    public static function GetObject()
    {
//        $_memCacheHost = $_memCachePort = '';
//
//        if (defined('MEMCACHE_PORT')) {
//            $_memCachePort = constant('MEMCACHE_PORT');
//        }
//
//        if (defined('MEMCACHE_HOST')) {
//            $_memCacheHost = constant('MEMCACHE_HOST');
//
//            if (empty($_memCachePort)) {
//                $_memCachePort = SWIFT_CacheMemcache::DEFAULT_PORT;
//            }
//        }
//
//        if (!empty($_memCacheHost) && !empty($_memCachePort) && class_exists('memcached')) {
//            return new SWIFT_CacheMemcache(array(array('hostname' => $_memCacheHost, 'port' => $_memCachePort)));
//        }

        return false;
    }

    /**
     * Prepend hash code with key
     *
     * @author Abhishek Mittal
     *
     * @param string|array $_keyName
     *
     * @return string|array
     * @throws SWIFT_Exception If Invalid Data Provided
     */
    public function GetProcessedKeyName($_keyName)
    {
        if (empty($_keyName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (_is_array($_keyName)) {

            $_keyNameList = array();

            foreach ($_keyName as $_key) {
                $_keyNameList[] = $this->GetHash() . self::KEY_SEPARATOR . $_key;
            }

            return $_keyNameList;
        }

        return $this->GetHash() . self::KEY_SEPARATOR . $_keyName;
    }
}