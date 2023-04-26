<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Varun Shoor
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The Dwoo Template File Abstraction Layer
 * 
 * @author Varun Shoor
 */
class SWIFT_TemplateEngineFile extends \Dwoo\Template\File
{
    /**
     * Constructor
     *
     * @param string $_fileName the path to the template file, make sure it exists
     */
    public function __construct($_fileName, $_cacheTime = null, $_cacheId = null, $_compileId = null, $_includePath = null)
    {
        parent::__construct($_fileName, $_cacheTime, $_cacheId, $_compileId, $_includePath);
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
    }

    /**
     * returns the full compiled file name and assigns a default value to it if
     * required
     *
     * @param \Dwoo\Core $dwoo the dwoo instance that requests the file name
     * @return string the full path to the compiled file
     */
    protected function getCompiledFilename(\Dwoo\Core $dwoo)
    {
        $_compiledFilename = parent::getCompiledFilename($dwoo);
        
        $_finalCompiledFilename = md5($_compiledFilename . SWIFT::Get('InstallationHash')) .'.php';

        return './'. SWIFT_BASEDIRECTORY .'/'. SWIFT_CACHEDIRECTORY . '/' . $_finalCompiledFilename;
    }
}
?>