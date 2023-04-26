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
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The Dwoo Template String Abstraction Layer
 * 
 * @author Varun Shoor
 */
class SWIFT_TemplateEngineString extends \Dwoo\Template\Str
{

    /**
     * Constructor
     *
     * @param string $_templateName The Template Name
     * @param string $_templateString The Template String
     */
    public function __construct($_templateName, $_templateString)
    {
        $_templateHash = md5($_templateName);

        parent::__construct($_templateString, null, null, $_templateHash);

        $this->name = $_templateHash;
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