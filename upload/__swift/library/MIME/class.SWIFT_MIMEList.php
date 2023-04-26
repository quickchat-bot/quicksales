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
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

/**
 * The MIME List Management Class
 *
 * @author Varun Shoor
 */
class SWIFT_MIMEList extends SWIFT_Model
{
    private $_mimeList = array();

    /**
     * Constructor
     *
     * @author Varun Shoore
     * @throws SWIFT_MIME_Exception If the Class is not Loaded
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('mime');

        if (!$this->LoadData())
        {
            throw new SWIFT_MIME_Exception(SWIFT_CLASSNOTLOADED);
        }
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Get the Mime Data from the List
     *
     * @author Varun Shoor
     * @param string $_fileExtension The File Extension
     * @return mixed "_mimeList" (ARRAY) on Success, "false" (bool) otherwise
     * @throws SWIFT_MIME_Exception If the Class is not Loaded or If Invalid Data Provided
     */
    public function Get($_fileExtension)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_MIME_Exception(SWIFT_CLASSNOTLOADED);

            return false;

        } else if (empty($_fileExtension) || !isset($this->_mimeList[$_fileExtension])) {
            throw new SWIFT_MIME_Exception(SWIFT_INVALIDDATA);

            return false;
        }

        return $this->_mimeList[$_fileExtension];
     }

    /**
     * Load the Mime Data
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_MIME_Exception If the Class is not Loaded
     */
    public function LoadData($_ = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_MIME_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $this->_mimeList = array(

            'ace'     => array('application/x-ace', 'mimeico_compressed.gif', $this->Language->Get('ace')),
            'ani'     => array('application/graphicconverter', 'mimeico_pic.gif', $this->Language->Get('ani')),
            'pdf'     => array('application/pdf', 'mimeico_pdf.gif', $this->Language->Get('pdf')),
            /* Bug Fix : Saloni Dhall
             * SWIFT-3988 : Incorrect MIME type for PNG images creating issues in REST APIs
             */
            'png'     => array('image/png', 'mimeico_pic.gif', $this->Language->Get('png')),
            'htm'     => array('text/html', 'mimeico_html.gif', $this->Language->Get('htm')),
            'html'    => array('text/html', 'mimeico_html.gif', $this->Language->Get('html')),
            'rtf'     => array('text/richtext', 'mimeico_text.gif', $this->Language->Get('rtf')),
            'gz'      => array('application/x-gzip', 'mimeico_compressed.gif', $this->Language->Get('gz')),
            'tar'     => array('application/x-gzip', 'mimeico_compressed.gif', $this->Language->Get('tar')),
            'zip'     => array('application/zip', 'mimeico_compressed.gif', $this->Language->Get('zip')),
            'rar'     => array('application/rar', 'mimeico_compressed.gif', $this->Language->Get('rar')),
            'bz2'     => array('application/bz2', 'mimeico_compressed.gif', $this->Language->Get('bz2')),
            'bz'      => array('application/bz', 'mimeico_compressed.gif', $this->Language->Get('bz')),
            'jar'     => array('application/java-archive', 'mimeico_compressed.gif', $this->Language->Get('jar')),
            'doc'     => array('application/msword', 'mimeico_word.gif', $this->Language->Get('doc')),
            'docx'    => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'mimeico_word.gif', $this->Language->Get('docx')),
            'jpg'     => array('image/jpeg', 'mimeico_pic.gif', $this->Language->Get('jpg')),
            'jpeg'    => array('image/jpeg', 'mimeico_pic.gif', $this->Language->Get('jpeg')),
            'txt'     => array('text/plain', 'mimeico_text.gif', $this->Language->Get('txt')),
            'wav'     => array('audio/x-wav', 'mimeico_sound.gif', $this->Language->Get('wav')),
            'mov'     => array('video/quicktime', 'mimeico_flick.gif', $this->Language->Get('mov')),
            'ppt'     => array('application/powerpoint', 'mimeico_powerpoint.gif', $this->Language->Get('ppt')),
            'pptx'    => array('application/vnd.openxmlformats-officedocument.presentationml.presentation', 'mimeico_powerpoint.gif', $this->Language->Get('pptx')),
            'xls'     => array('application/vnd.ms-excel', 'mimeico_excel.gif', $this->Language->Get('xls')),
            'xlsx'    => array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'mimeico_excel.gif', $this->Language->Get('xlsx')),
            'ram'     => array('audio/x-realaudio', 'mimeico_flick.gif', $this->Language->Get('ram')),
            'ico'     => array('image/ico', 'mimeico_pic.gif', $this->Language->Get('ico')),
            'gif'     => array('image/gif', 'mimeico_pic.gif', $this->Language->Get('gif')),
            'mpg'     => array('video/mpeg', 'mimeico_flick.gif', $this->Language->Get('mpg')),
            'mpeg'    => array('video/mpeg', 'mimeico_flick.gif', $this->Language->Get('mpeg')),
            'mp3'     => array('audio/x-mpeg', 'mimeico_sound.gif', $this->Language->Get('mp3')),
            'php'     => array('text/plain', 'mimeico_script.gif', $this->Language->Get('php')),
            'swf'     => array('application/x-shockwave-flash', 'mimeico_flick.gif', $this->Language->Get('swf')),
            'exe'     => array('application/executableFile', 'mimeico_text.gif', $this->Language->Get('exe')),
            'wmv'     => array('application/windowMediaVedio', 'mimeico_flick.gif', $this->Language->Get('wmv')),
            'avi'     => array('video/audiovediointerleave', 'mimeico_flick.gif', $this->Language->Get('avi')),
            'tif'     => array('image/imagefileformat', 'mimeico_pic.gif', $this->Language->Get('tif')),
            'psd'     => array('image/photoshopdocument', 'mimeico_pic.gif', $this->Language->Get('psd')),
            'wma'     => array('audio/windowsaudio', 'mimeico_sound.gif', $this->Language->Get('wma')),
            'mp4'     => array('audio/x-mpeg', 'mimeico_sound.gif', $this->Language->Get('mp4')),
            'bmp'     => array('image/bitmap', 'mimeico_pic.gif', $this->Language->Get('bmp')),
            'aif'     => array('audio/interchangefile', 'mimeico_sound.gif', $this->Language->Get('aif')),
            'qbb'     => array('text/quickbooksbackupfile', 'mimeico_text.gif', $this->Language->Get('qbb')),
            'dat'     => array('text/data', 'mimeico_script.gif', $this->Language->Get('dat')),
            'rm'      => array('video/realmedia', 'mimeico_flick.gif', $this->Language->Get('rm')),
            'dmg'     => array('image/diskimage', 'mimeico_pic.gif', $this->Language->Get('dmg')),
            'iso'     => array('image/opticaldiskimage', 'mimeico_pic.gif', $this->Language->Get('iso')),
            'flv'     => array('video/flashvedio', 'mimeico_flick.gif', $this->Language->Get('flv')),
            'ttf'     => array('text/truetypefont', 'mimeico_script.gif', $this->Language->Get('ttf')),
            'bin'     => array('text/binary', 'mimeico_text.gif', $this->Language->Get('bin')),
            'log'     => array('text/log', 'mimeico_text.gif', $this->Language->Get('log')),
            'dll'     => array('application/linklibrary', 'mimeico_flick.gif', $this->Language->Get('dll')),
            'ss'      => array('image/bitmapgraphics', 'mimeico_pic.gif', $this->Language->Get('ss')),
            'torrent' => array('video/bittorrent', 'mimeico_flick.gif', $this->Language->Get('torrent')),
            'bat'     => array('text/batchfile', 'mimeico_text.gif', $this->Language->Get('bat')),
            'bup'     => array('text/backupfile', 'mimeico_text.gif', $this->Language->Get('bup')),
            'wps'     => array('text/textdocument', 'mimeico_script.gif', $this->Language->Get('wps')),
            'sql'     => array('text/querylanguage', 'mimeico_script.gif', $this->Language->Get('sql')),
            'rdl'     => array('text/xml', 'mimeico_script.gif', $this->Language->Get('rdl')),
            'xml'     => array('text/xml', 'mimeico_script.gif', $this->Language->Get('xml')),

        );

        return true;
    }
}
?>
