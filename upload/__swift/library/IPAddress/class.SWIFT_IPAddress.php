<?php
//=======================================
//###################################
// Kayako Singapore Pte. Ltd. - SWIFT Framework
//
// Source Copyright 2001Kayako Singapore Pte. Ltd.h Ltd.
// Unauthorized reproduction is not allowed
// License Number: $%LICENSE%$
//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
//                   www.kayako.com
//###################################
//=======================================

/**
 * IPAddress class
 *
 * @author Varun Shoor
 */
class SWIFT_IPAddress extends SWIFT_Library
{
    private $_ipAddress;
    private $_netMask;
    private $_gateway;
    private $_type;
    private $_macAddress;
    private $_device;
    private $_onBoot;

    /**
     * Constructor
     *
     * @author Varun Shoore
     * @throws SWIFT_IPAddress_Exception If Class could not be loaded or If Invalid Data is Provided
     */
    public function __construct($_ipAddress, $_netMask, $_gateway, $_type, $_macAddress, $_device, $_onBoot = false)
    {
        parent::__construct();

        if (empty($_ipAddress) || empty($_netMask) || empty($_gateway) || empty($_type) || empty($_macAddress) || empty($_device))
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_INVALIDDATA);
    }

        if (!$this->SetIPAddress($_ipAddress) || !$this->SetNetMask($_netMask) || !$this->SetGateway($_gateway) || !$this->SetType($_type) || !$this->SetMacAddress($_macAddress) || !$this->SetDevice($_device) || !$this->SetOnBoot($_onBoot))
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_CLASSNOTLOADED);

            $this->SetIsClassLoaded(false);
        }
    }

    /**
     * Sets the IP Address
     *
     * @param string $_ipAddress The IP Address
     * @return bool True on success, false otherwise
     * @throws SWIFT_IPAddress_Exception If Class could not be loaded or If Invalid Data is Provided
     */
    public function SetIPAddress($_ipAddress)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_ipAddress)) {
            throw new SWIFT_IPAddress_Exception(SWIFT_INVALIDDATA);
        }

        $this->_ipAddress = $_ipAddress;

        return true;
    }

    /**
     * Gets the IP Address
     *
     * @return string The IP Address
     * @throws SWIFT_IPAddress_Exception If Class could not be loaded
     */
    public function GetIPAddress()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_ipAddress;
    }

    /**
     * Sets the Netmask
     *
     * @param string $_netMask The Net Mask
     * @return bool True on success, false otherwise
     * @throws SWIFT_IPAddress_Exception If Class could not be loaded or If Invalid Data is Provided
     */
    public function SetNetMask($_netMask)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_netMask)) {
            throw new SWIFT_IPAddress_Exception(SWIFT_INVALIDDATA);
        }

        $this->_netMask = $_netMask;

        return true;
    }

    /**
     * Gets the Netmask
     *
     * @return string The Net Mask
     * @throws SWIFT_IPAddress_Exception If Class could not be loaded
     */
    public function GetNetMask()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_netMask;
    }

    /**
     * Sets the Gateway IP
     *
     * @param string $_gateway The Gateway IP Address
     * @return bool True on success, false otherwise
     * @throws SWIFT_IPAddress_Exception If Class could not be loaded or If Invalid Data is Provided
     */
    public function SetGateway($_gateway)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_gateway)) {
            throw new SWIFT_IPAddress_Exception(SWIFT_INVALIDDATA);
        }

        $this->_gateway = $_gateway;

        return true;
    }

    /**
     * Gets the Gateway IP
     *
     * @return string The Gateway IP Address
     * @throws SWIFT_IPAddress_Exception If Class could not be loaded
     */
    public function GetGateway()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_gateway;
    }

    /**
     * Sets the Network Type
     *
     * @param string $_type The Network Type
     * @return bool True on success, false otherwise
     * @throws SWIFT_IPAddress_Exception If Class could not be loaded or If Invalid Data is Provided
     */
    public function SetType($_type)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_type)) {
            throw new SWIFT_IPAddress_Exception(SWIFT_INVALIDDATA);
        }

        $this->_type = $_type;

        return true;
    }

    /**
     * Gets the Network Type
     *
     * @return string The Network Type
     * @throws SWIFT_IPAddress_Exception If Class could not be loaded
     */
    public function GetType()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_type;
    }

    /**
     * Sets the Mac Address
     *
     * @param string $_macAddress The Mac Address
     * @return bool True on success, false otherwise
     * @throws SWIFT_IPAddress_Exception If Class could not be loaded or If Invalid Data is Provided
     */
    public function SetMacAddress($_macAddress)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_macAddress)) {
            throw new SWIFT_IPAddress_Exception(SWIFT_INVALIDDATA);
        }

        $this->_macAddress = $_macAddress;

        return true;
    }

    /**
     * Gets the Mac Address
     *
     * @return string The Mac Address
     * @throws SWIFT_IPAddress_Exception If Class could not be loaded
     */
    public function GetMacAddress()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_macAddress;
    }

    /**
     * Sets the device address
     *
     * @param string $_device The Device Address
     * @return bool True on success, false otherwise
     * @throws SWIFT_IPAddress_Exception If Class could not be loaded or If Invalid Data is Provided
     */
    public function SetDevice($_device)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_CLASSNOTLOADED);
        } else if (empty($_device)) {
            throw new SWIFT_IPAddress_Exception(SWIFT_INVALIDDATA);
        }

        $this->_device = $_device;

        return true;
    }

    /**
     * Gets the device address
     *
     * @return string The Device Address
     * @throws SWIFT_IPAddress_Exception If Class could not be loaded
     */
    public function GetDevice()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_device;
    }

    /**
     * Sets the boolean value on whether the card gets initiated on boot
     *
     * @param bool $_onBoot On Boot startup value
     * @return bool True on success, false otherwise
     * @throws SWIFT_IPAddress_Exception If Class could not be loaded
     */
    public function SetOnBoot($_onBoot)
    {
        $_onBoot = (int) ($_onBoot);

        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->_onBoot = $_onBoot;

        return true;
    }

    /**
     * Gets the boolean value on whether the card gets initiated on boot
     *
     * @return bool On Boot startup value
     * @throws SWIFT_IPAddress_Exception If Class could not be loaded
     */
    public function GetOnBoot()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_IPAddress_Exception(SWIFT_CLASSNOTLOADED);
        }

        return $this->_onBoot;
    }
}