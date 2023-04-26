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
 * The API Management Class
 *
 * @author Varun Shoor
 */
abstract class SWIFT_API extends SWIFT_Controller
{
    protected $_dataStore = array();
    private $_dataLoaded = false;

    // Core Constants
    const ERROR_LOGINFAILURE = 100;
    const ERROR_INVALIDSESSION = 101;
    const ERROR_INVALIDDATA = 102;
    const ERROR_DBFAILURE = 103;
    const ERROR_INVALIDUSER = 104;

    /*
     * @var SWIFT_XML
     */
    public $XML;

    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');

        if (!$this->LoadData($_POST['xml']))
        {
            $this->_dataLoaded = false;

            return;
        }

        $this->_dataLoaded = true;
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
     * Checks to see if we recieved an incoming XML, if not, displays an error and stops further processing..
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function CheckDataLoaded()
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->SetIsClassLoaded(false);

        $this->Error(self::ERROR_INVALIDDATA, $this->Language->Get('invaliddatareceived'));

        return true;
    }

    /**
     * Dispatch an API Error
     *
     * @author Varun Shoor
     * @param int $_errorNumber The Error Number
     * @param string $_errorText The Error Text Describing what happened
     * @return bool "true" on Success, "false" otherwise
     */
    public function Error($_errorNumber, $_errorText)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $this->XML->AddParentTag('api');
            $this->XML->AddTag('status', '0');
            $this->XML->AddTag('errorno', $_errorNumber);
            $this->XML->AddTag('errortxt', $_errorText);

        $this->XML->EndParentTag('api');
        $this->XML->EchoXML();

        log_error_and_exit();
    }

    /**
     * Loads the API Data from an XML String
     *
     * @author Varun Shoor
     * @param string $_xmlString The XML Data
     * @return bool "true" on Success, "false" otherwise
     */
    public function LoadData($_xmlString)
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }

        $_apiParentContainer = $this->XML->XMLToTree($_xmlString);
        if (!isset($_apiParentContainer['api']) || !isset($_apiParentContainer['api'][0]['children']))
        {
            return false;
        }

        $_apiContainer = $_apiParentContainer['api'][0]['children'];

        if (!isset($_apiContainer['status']) || !isset($_apiContainer['status'][0]['values']))
        {
            return false;
        }

        $_returnData = array();

        if ($_apiContainer['status'][0]['values'][0] == '1')
        {
            if (isset($_apiContainer['data']) && isset($_apiContainer['data'][0]['children']))
            {
                $_apiDataContainer = $_apiContainer['data'][0]['children'];
                foreach ($_apiDataContainer as $_key=>$_val)
                {
                    if (_is_array($_apiDataContainer[$_key]))
                    {
                        foreach ($_apiDataContainer[$_key] as $_subKey=>$_subVal)
                        {
                            $_returnData[$_key][] = $_subVal['values'][0];
                        }
                    } else {
                        $_returnData[$_key] = $_apiDataContainer[$_key][0]['values'][0];
                    }
                }

                $this->_dataStore = $_returnData;

                return true;
            }

            return true;
        }

        return false;
    }

    /**
     * Dispatch the API Packet
     *
     * @author Varun Shoor
     * @param bool $_status The API Packet Status
     * @param array $_dataContainer (OPTIONAL) The Data Container to Dispatch
     * @return bool "true" on Success, "false" otherwise
     */
    public function Send($_status = true, $_dataContainer = array())
    {
        if (!$this->GetIsClassLoaded())
        {
            return false;
        }


        $this->XML->BuildXML();
        $this->XML->AddParentTag('api');
            $this->XML->AddTag('status', (int) ($_status));
            $this->XML->AddParentTag('data');

                if (_is_array($_dataContainer))
                {
                    foreach ($_dataContainer as $_key=>$_val)
                    {
                        if (_is_array($_val))
                        {
                            foreach ($_val as $_subKey => $_subVal)
                            {
                                $this->XML->AddTag($_key, $_subVal);
                            }
                        } else {
                            $this->XML->AddTag($_key, $_val);
                        }
                    }
                }

            $this->XML->EndParentTag('data');
        $this->XML->EndParentTag('api');

        $this->XML->EchoXML();

        return true;
    }
}
