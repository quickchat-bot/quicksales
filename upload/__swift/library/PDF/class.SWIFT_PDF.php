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

if (!defined('K_TCPDF_EXTERNAL_CONFIG'))
{
    define('K_TCPDF_EXTERNAL_CONFIG', '1');
    define ('K_PATH_MAIN', './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_THIRDPARTY_DIRECTORY . '/tcpdf/');
    define ('K_PATH_URL', SWIFT::Get('swiftpath') . SWIFT_BASE_DIRECTORY . '/' . SWIFT_THIRDPARTY_DIRECTORY . '/tcpdf/');
    define ('K_PATH_FONTS', K_PATH_MAIN . 'fonts/');
    define ('K_PATH_CACHE', './' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/');
    define ('K_PATH_URL_CACHE', SWIFT::Get('swiftpath') . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY . '/');
    define ('K_PATH_IMAGES', K_PATH_MAIN . 'images/');
    define ('K_BLANK_IMAGE', K_PATH_IMAGES . '_blank.png');
    define ('PDF_PAGE_FORMAT', 'A4');
    define ('PDF_PAGE_ORIENTATION', 'P');
    define ('PDF_CREATOR', 'QuickSupport ' . SWIFT_PRODUCT);
    define ('PDF_AUTHOR', 'QuickSupport ' . SWIFT_PRODUCT);
    define ('PDF_HEADER_TITLE', 'QuickSupport');
    define ('PDF_HEADER_STRING', '');
    define ('PDF_HEADER_LOGO', '');
    define ('PDF_HEADER_LOGO_WIDTH', 30);
    define ('PDF_UNIT', 'mm');
    define ('PDF_MARGIN_HEADER', 0);
    define ('PDF_MARGIN_FOOTER', 0);
    define ('PDF_MARGIN_TOP', 10);
    define ('PDF_MARGIN_BOTTOM', 2);
    define ('PDF_MARGIN_LEFT', 8);
    define ('PDF_MARGIN_RIGHT', 7);
    define ('PDF_FONT_NAME_MAIN', 'droidsans');
    define ('PDF_FONT_SIZE_MAIN', 10);
    define ('PDF_FONT_NAME_DATA', 'droidsans');
    define ('PDF_FONT_SIZE_DATA', 8);
    define ('PDF_FONT_MONOSPACED', 'courier');
    define ('PDF_IMAGE_SCALE_RATIO', 1.25);
    define ('HEAD_MAGNIFICATION', 1.1);
    define ('K_CELL_HEIGHT_RATIO', 1.25);
    define ('K_TITLE_MAGNIFICATION', 1.3);
    define ('K_SMALL_RATIO', 2/3);
    define ('K_THAI_TOPCHARS', true);
    define ('K_TCPDF_CALLS_IN_HTML', false);
}

/**
 * The PDF Handling Library
 *
 * @author Varun Shoor
 */
class SWIFT_PDF extends SWIFT_Library
{
    protected $_TCPDFObject = false;

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @param string $_pageTitle The Page Title
     * @param string $_subject The Page Subject
     * @param string $_keywords (OPTIONAL) The Keywords that describe this documente
     */
    public function __construct($_pageTitle, $_subject, $_keywords = '')
    {
        parent::__construct();

        global $l;

        chdir(SWIFT_BASEPATH);

        $_TCPDFObject = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $_TCPDFObject->SetCreator(PDF_CREATOR);
        $_TCPDFObject->SetAuthor($this->Settings->Get('general_companyname'));

        $_TCPDFObject->SetTitle($_pageTitle);
        $_TCPDFObject->SetSubject($_subject);

        if (empty($_keywords))
        {
            $_keywords = $_subject;
        }
        $_TCPDFObject->SetKeywords($_keywords);

        $_TCPDFObject->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $_TCPDFObject->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        $_TCPDFObject->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $_TCPDFObject->setPrintHeader(false);
        $_TCPDFObject->setPrintFooter(false);
        $_TCPDFObject->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $_TCPDFObject->SetHeaderMargin(PDF_MARGIN_HEADER);
        $_TCPDFObject->SetFooterMargin(PDF_MARGIN_FOOTER);
        $_TCPDFObject->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $_TCPDFObject->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $_TCPDFObject->setLanguageArray($l);
        $_TCPDFObject->setFontSubsetting(true);
        $_TCPDFObject->SetFont('droidsans', '', 14, '', true);
        //$_TCPDFObject->AddPage();

        $this->_TCPDFObject = $_TCPDFObject;
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
     * Retrieve the TCPDF Object
     *
     * @author Varun Shoor
     * @return TCPDF The TCPDF Object
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPDFObject()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_TCPDFObject;
    }

    /**
     * Inline downloading of PDF through browser
     *
     * @author Varun Shoor
     * @param string $_fileName (OPTIONAL) The Document Filename
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function InlineDocument($_fileName = 'document.pdf')
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_TCPDFObject->Output($_fileName, 'I');

        return true;
    }

    /**
     * Force the downloading of PDF through browser
     *
     * @author Varun Shoor
     * @param string $_fileName (OPTIONAL) The Document Filename
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function DownloadDocument($_fileName = 'document.pdf')
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_TCPDFObject->Output($_fileName, 'D');

        return true;
    }

    /**
     * Retrieve the Document as String
     *
     * @author Varun Shoor
     * @return string The PDF Document Data
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetDocument()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_TCPDFObject->Output('document.pdf', 'S');
    }
}
?>