<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The Akismet Checking Library
 *
 * @author Varun Shoor
 */
class SWIFT_Akismet extends SWIFT_Library
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public function __construct()
    {
        parent::__construct();

        if ($this->Settings->Get('security_enableakismet') == '0' || $this->Settings->Get('security_akismetkey') == '') {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }
    }

    /**
     * Check against Akismet DB
     *
     * @author Varun Shoor
     *
     * @param string $_fullName The User Full Name
     * @param string $_email    The Email Address
     * @param string $_contents The Comment Contents
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Check($_fullName, $_email, $_contents)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_variableContainer = array(
            'blog'         => SWIFT::Get('swiftpath'), 'user_ip' => SWIFT::Get('IP'), 'user_agent' => $_SERVER['HTTP_USER_AGENT'], 'referrer' => $_SERVER['HTTP_REFERER'],
            'comment_type' => 'comment', 'comment_author' => $_fullName, 'comment_author_email' => $_email, 'comment_content' => $_contents
        );

        $_verifyResult = self::DispatchPost($this->Settings->Get('security_akismetkey') . '.rest.akismet.com', 80, '/1.1/comment-check', $_variableContainer);

        if ($_verifyResult == 'true') {
            return false;
        }

        return true;
    }

    /**
     * Mark as Spam in Akismet DB
     *
     * @author Varun Shoor
     *
     * @param string $_fullName  The User Full Name
     * @param string $_email     The Email Address
     * @param string $_contents  The Comment Contents
     * @param string $_userAgent The User Agent
     * @param string $_referrer  The Referrer
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MarkAsSpam($_fullName, $_email, $_contents, $_userAgent, $_referrer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_variableContainer = array(
            'blog'         => SWIFT::Get('swiftpath'), 'user_ip' => SWIFT::Get('IP'), 'user_agent' => $_userAgent, 'referrer' => $_referrer,
            'comment_type' => 'comment', 'comment_author' => $_fullName, 'comment_author_email' => $_email, 'comment_content' => $_contents
        );

        $_verifyResult = self::DispatchPost($this->Settings->Get('security_akismetkey') . '.rest.akismet.com', 80, '/1.1/submit-spam', $_variableContainer);

        if ($_verifyResult == 'true') {
            return false;
        }

        return true;
    }

    /**
     * Mark as Not Spam in Akismet DB
     *
     * @author Varun Shoor
     *
     * @param string $_fullName  The User Full Name
     * @param string $_email     The Email Address
     * @param string $_contents  The Comment Contents
     * @param string $_userAgent The User Agent
     * @param string $_referrer  The Referrer
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function MarkAsHam($_fullName, $_email, $_contents, $_userAgent, $_referrer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_variableContainer = array(
            'blog'         => SWIFT::Get('swiftpath'), 'user_ip' => SWIFT::Get('IP'), 'user_agent' => $_userAgent, 'referrer' => $_referrer,
            'comment_type' => 'comment', 'comment_author' => $_fullName, 'comment_author_email' => $_email, 'comment_content' => $_contents
        );

        $_verifyResult = self::DispatchPost($this->Settings->Get('security_akismetkey') . '.rest.akismet.com', 80, '/1.1/submit-ham', $_variableContainer);

        if ($_verifyResult == 'true') {
            return false;
        }

        return true;
    }

    /**
     * Call up a HTTP Post
     *
     * DispatchPost("www.fat.com", 80, "/weightloss.pl", array("name" => "obese bob", "age" => "20"));
     *
     * @author Varun Shoor
     *
     * @param string $_server    The Server
     * @param string $_port      The Port
     * @param string $_url       The URL to Execute
     * @param array  $_variables The Variables
     *
     * @return string "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    protected static function DispatchPost($_server, $_port, $_url, $_variables)
    {
        $_userAgent = SWIFT_PRODUCT . '/' . SWIFT_VERSION . ' | Akismet/1.11';

        $_urlEncoded = http_build_query($_variables);

        $_urlPath = "http://${_server}:${_port}${_url}";

        $_headers = [
            'User-Agent: ' . $_userAgent,
            'Cache-Control: no-cache, no-store, must-revalidate',
        ];

        $_SWIFT = SWIFT::GetInstance();
        $_SWIFT->Log->Log("POST $_urlPath ", 0, strftime('%H:%M:%S'));
        $_SWIFT->Log->Log($_urlEncoded, 0, strftime('%H:%M:%S'));

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $_urlPath);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $_urlEncoded);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $_headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $_result = curl_exec($ch);

        curl_close($ch);

        $_SWIFT->Log->Log("RESULT: $_result", 0, strftime('%H:%M:%S'));

        return trim($_result);
    }

}
