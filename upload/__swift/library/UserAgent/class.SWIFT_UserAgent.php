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

// +----------------------------------------------------------------------+
// | PHP version 4.2                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2007 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Dan Allen <dan@mojavelinux.com>                             |
// |          Jason Rust <jrust@php.net>                                  |
// +----------------------------------------------------------------------+

/**
 * The Net_UserAgent_Detect object does a number of tests on an HTTP user
 * agent string.  The results of these tests are available via methods of
 * the object.  Note that all methods in this class can be called
 * statically.  The constructor and singleton methods are only retained
 * for BC.
 *
 * This module is based upon the JavaScript browser detection code
 * available at http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html.
 * This module had many influences from the lib/Browser.php code in
 * version 1.3 of Horde.
 *
 * @author   Jason Rust <jrust@php.net>
 * @author   Dan Allen <dan@mojavelinux.com>
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @author   Jon Parise <jon@horde.org>
 * @package  Net_UserAgent
 */
class SWIFT_UserAgent extends SWIFT_Library
{

    const DETECT_BROWSER = 'browser';

    const DETECT_OS = 'os';

    const DETECT_FEATURES = 'features';

    const DETECT_QUIRKS = 'quirks';

    const DETECT_ACCEPT = 'accept';

    const DETECT_ALL = 'all';

    protected $_userAgent = '';

    protected $_leadingIdentifier = '';

    protected $_version = 0;

    protected $_majorVersion = 0;

    protected $_subVersion = 0;

    protected $_options = array();

    protected $_browser = array();

    protected $_os = array();

    protected $_quirks = array();

    protected $_features = array();

    protected $_acceptTypes = array();

    /**
     * User Agent Constructor
     *
     * @author Varun Shoor
     *
     * @param string   $_userAgentString (OPTIONAL)
     * @param mixed $_incomingOption  (OPTIONAL)
     */
    public function __construct($_userAgentString = '', $_incomingOption = false)
    {
        parent::__construct();

        $this->Detect($_userAgentString, $_incomingOption);
    }

    /**
     * Detect the User Agent
     *
     * @author Varun Shoor
     *
     * @param string   $_userAgentString
     * @param mixed $_incomingOption
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function Detect($_userAgentString = '', $_incomingOption = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        // User agent string that is being analyzed
        $_userAgent = &$this->_userAgent;

        // Array that stores all of the flags for the vendor and version
        // of the different browsers
        $_browser = &$this->_browser;
        $_browser = array_flip(array(
                                    'ns', 'ns2', 'ns3', 'ns4', 'ns4up', 'nav', 'ns6', 'belowns6', 'ns6up', 'firefox',
                                    'firefox0.x', 'firefox1.x', 'firefox1.5', 'firefox2.x', 'firefox3.x', 'gecko', 'ie',
                                    'ie3', 'ie4', 'ie4up', 'ie5', 'ie5_5', 'ie5up', 'ie6', 'belowie6', 'ie6up', 'ie7',
                                    'ie7up', 'ie8', 'ie8tr', 'ie8up', 'ie9', 'ie9up', 'opera', 'opera2', 'opera3',
                                    'opera4', 'opera5', 'opera6', 'opera7', 'opera8', 'opera9', 'opera5up', 'opera6up',
                                    'opera7up', 'belowopera8', 'opera8up', 'opera9up', 'aol', 'aol3', 'aol4', 'aol5',
                                    'aol6', 'aol7', 'aol8', 'webtv', 'aoltv', 'tvnavigator', 'hotjava', 'hotjava3',
                                    'hotjava3up', 'konq', 'safari', 'safari_mobile', 'chrome', 'netgem', 'webdav',
                                    'icab'
                               ));

        // Array that stores all of the flags for the operating systems,
        // and in some cases the versions of those operating systems (windows)
        $_os = &$this->_os;
        $_os = array_flip(array(
                               'win', 'win95', 'win16', 'win31', 'win9x', 'win98', 'wince', 'winme', 'win2k', 'winxp',
                               'winnt', 'win2003', 'vista', 'win7', 'os2', 'mac', 'mactiger', 'macleopard',
                               'macsnowleopard', 'mac68k', 'macppc', 'iphone', 'linux', 'unix', 'vms', 'sun', 'sun4',
                               'sun5', 'suni86', 'irix', 'irix5', 'irix6', 'hpux', 'hpux9', 'hpux10', 'aix', 'aix1',
                               'aix2', 'aix3', 'aix4', 'sco', 'unixware', 'mpras', 'reliant', 'dec', 'sinix', 'freebsd',
                               'bsd'
                          ));

        // Array which stores known issues with the given client that can
        // be used for on the fly tweaking so that the client may recieve
        // the proper handling of this quirk.
        $_quirks = &$this->_quirks;
        $_quirks = array(
            'must_cache_forms'         => false,
            'popups_disabled'          => false,
            'empty_file_input_value'   => false,
            'cache_ssl_downloads'      => false,
            'scrollbar_in_way'         => false,
            'break_disposition_header' => false,
            'nested_table_render_bug'  => false
        );

        // Array that stores credentials for each of the browser/os
        // combinations.  These allow quick access to determine if the
        // current client has a feature that is going to be implemented
        // in the script.
        $_features = &$this->_features;
        $_features = array(
            'javascript' => false,
            'dhtml'      => false,
            'dom'        => false,
            'sidebar'    => false,
            'gecko'      => false,
            'svg'        => false,
            'css2'       => false,
            'ajax'       => false
        );

        // The leading identifier is the very first term in the user
        // agent string, which is used to identify clients which are not
        // Mosaic-based browsers.
        $_leadingIdentifier = &$this->_leadingIdentifier;

        // The full version of the client as supplied by the very first
        // numbers in the user agent
        $_version = &$this->_version;
        $_version = 0;

        // The major part of the client version, which is the integer
        // value of the version.
        $_majorVersion = &$this->_majorVersion;
        $_majorVersion = 0;

        // The minor part of the client version, which is the decimal
        // parts of the version
        $_subVersion = &$this->_subVersion;
        $_subVersion = 0;

        // detemine what user agent we are using
        $_userAgent = $_userAgentString;
        if (empty($_userAgentString)) {
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $_userAgent = $_SERVER['HTTP_USER_AGENT'];
            } else {
                if (isset($GLOBALS['HTTP_SERVER_VARS']['HTTP_USER_AGENT'])) {
                    $_userAgent = $GLOBALS['HTTP_SERVER_VARS']['HTTP_USER_AGENT'];
                } else {
                    $_userAgent = '';
                }
            }
        }

        // get the lowercase version for case-insensitive searching
        $_agent = strtolower($_userAgent);

        // figure out what we need to look for
        $_detectOptions = array(
            self::DETECT_BROWSER,
            self::DETECT_OS, self::DETECT_FEATURES,
            self::DETECT_QUIRKS, self::DETECT_ACCEPT,
            self::DETECT_ALL
        );

        $_detectOption = empty($_incomingOption) ? self::DETECT_ALL : $_incomingOption;

        settype($_detectOption, 'array');
        $_detectFlags = array();

        foreach ($_detectOptions as $_option) {
            if (in_array($_option, $_detectOption)) {
                $_detectFlags[$_option] = true;
            } else {
                $_detectFlags[$_option] = false;
            }
        }

        // initialize the arrays of browsers and operating systems
        // Get the type and version of the client
        $_matches = array();
        if (preg_match(";^([[:alnum:]]+)[ /\(]*[[:alpha:]]*([\d]*)(\.[\d\.]*);", $_agent, $_matches)) {
            list(, $_leadingIdentifier, $_majorVersion, $_subVersion) = $_matches;
        }

        if (empty($_leadingIdentifier)) {
            $_leadingIdentifier = 'Unknown';
        }

        $_version = $_majorVersion . $_subVersion;

        // Browser type
        if ($_detectFlags[self::DETECT_ALL] || $_detectFlags[self::DETECT_BROWSER]) {
            $_browser['webdav']        = ($_agent == 'microsoft data access internet publishing provider dav' || $_agent == 'microsoft data access internet publishing provider protocol discovery');
            $_browser['konq']          = (strpos($_agent, 'konqueror') !== false || strpos($_agent, 'safari') !== false);
            $_browser['safari']        = (strpos($_agent, 'safari') !== false);
            $_browser['chrome']        = (strpos($_agent, 'chrome') !== false);
            $_browser['safari_mobile'] = (strpos($_agent, 'safari') !== false && strpos($_agent, 'mobile') !== false);
            $_browser['text']          = strpos($_agent, 'links') !== false || strpos($_agent, 'lynx') !== false || strpos($_agent, 'w3m') !== false;
            $_browser['ns']            = strpos($_agent, 'mozilla') !== false && !(strpos($_agent, 'spoofer') !== false) && !(strpos($_agent, 'compatible') !== false) && !(strpos($_agent, 'hotjava') !== false) && !(strpos($_agent, 'opera') !== false) && !(strpos($_agent, 'webtv') !== false) ? 1 : 0;
            $_browser['netgem']        = strpos($_agent, 'netgem') !== false;
            $_browser['icab']          = strpos($_agent, 'icab') !== false;
            $_browser['ns2']           = $_browser['ns'] && $_majorVersion == 2;
            $_browser['ns3']           = $_browser['ns'] && $_majorVersion == 3;
            $_browser['ns4']           = $_browser['ns'] && $_majorVersion == 4;
            $_browser['ns4up']         = $_browser['ns'] && $_majorVersion >= 4;
            // determine if this is a Netscape Navigator
            $_browser['nav']         = $_browser['belowns6'] = $_browser['ns'] && $_majorVersion < 5;
            $_browser['ns6']         = !$_browser['konq'] && $_browser['ns'] && $_majorVersion == 5;
            $_browser['ns6up']       = $_browser['ns6'] && $_majorVersion >= 5;
            $_browser['gecko']       = strpos($_agent, 'gecko') !== false && !$_browser['konq'];
            $_browser['firefox']     = $_browser['gecko'] && strpos($_agent, 'firefox') !== false;
            $_browser['firefox0.x']  = $_browser['firefox'] && strpos($_agent, 'firefox/0.') !== false;
            $_browser['firefox1.x']  = $_browser['firefox'] && strpos($_agent, 'firefox/1.') !== false;
            $_browser['firefox1.5']  = $_browser['firefox'] && strpos($_agent, 'firefox/1.5') !== false;
            $_browser['firefox2.x']  = $_browser['firefox'] && strpos($_agent, 'firefox/2.') !== false;
            $_browser['firefox3.x']  = $_browser['firefox'] && strpos($_agent, 'firefox/3.') !== false;
            $_browser['ie']          = strpos($_agent, 'msie') !== false && !(strpos($_agent, 'opera') !== false);
            $_browser['ie3']         = $_browser['ie'] && $_majorVersion < 4;
            $_browser['ie4']         = $_browser['ie'] && $_majorVersion == 4 && (strpos($_agent, 'msie 4') !== false);
            $_browser['ie4up']       = $_browser['ie'] && !$_browser['ie3'];
            $_browser['ie5']         = $_browser['ie4up'] && (strpos($_agent, 'msie 5') !== false);
            $_browser['ie5_5']       = $_browser['ie4up'] && (strpos($_agent, 'msie 5.5') !== false);
            $_browser['ie5up']       = $_browser['ie4up'] && !$_browser['ie3'] && !$_browser['ie4'];
            $_browser['ie5_5up']     = $_browser['ie5up'] && !$_browser['ie5'];
            $_browser['ie6']         = strpos($_agent, 'msie 6') !== false;
            $_browser['ie6up']       = $_browser['ie5up'] && !$_browser['ie5'] && !$_browser['ie5_5'];
            $_browser['ie7']         = strpos($_agent, 'msie 7') && !strpos($_agent, 'trident/4');
            $_browser['ie7up']       = $_browser['ie6up'] && (!$_browser['ie6'] || $_browser['ie7']);
            $_browser['ie8tr']       = strpos($_agent, 'msie 7') && strpos($_agent, 'trident/4') !== false;
            $_browser['ie8']         = strpos($_agent, 'msie 8') !== false;
            $_browser['ie8up']       = $_browser['ie7up'] && (!$_browser['ie7'] || $_browser['ie8']);
            $_browser['ie9']         = strpos($_agent, 'msie 9') !== false;
            $_browser['ie9up']       = $_browser['ie8up'] && (!$_browser['ie8'] || $_browser['ie9']);
            $_browser['ie10']        = strpos($_agent, 'msie 10') !== false;
            $_browser['ie10up']      = $_browser['ie9up'] && (!$_browser['ie9'] || $_browser['ie10']);
            $_browser['belowie6']    = $_browser['ie'] && !$_browser['ie6up'];
            $_browser['opera']       = strpos($_agent, 'opera') !== false;
            $_browser['opera2']      = strpos($_agent, 'opera 2') !== false || strpos($_agent, 'opera/2') !== false;
            $_browser['opera3']      = strpos($_agent, 'opera 3') !== false || strpos($_agent, 'opera/3') !== false;
            $_browser['opera4']      = strpos($_agent, 'opera 4') !== false || strpos($_agent, 'opera/4') !== false;
            $_browser['opera5']      = strpos($_agent, 'opera 5') !== false || strpos($_agent, 'opera/5') !== false;
            $_browser['opera6']      = strpos($_agent, 'opera 6') !== false || strpos($_agent, 'opera/6') !== false;
            $_browser['opera7']      = strpos($_agent, 'opera 7') !== false || strpos($_agent, 'opera/7') !== false;
            $_browser['opera8']      = strpos($_agent, 'opera 8') !== false || strpos($_agent, 'opera/8') !== false;
            $_browser['opera9']      = strpos($_agent, 'opera 9') !== false || strpos($_agent, 'opera/9') !== false;
            $_browser['opera5up']    = $_browser['opera'] && !$_browser['opera2'] && !$_browser['opera3'] && !$_browser['opera4'];
            $_browser['opera6up']    = $_browser['opera'] && !$_browser['opera2'] && !$_browser['opera3'] && !$_browser['opera4'] && !$_browser['opera5'];
            $_browser['opera7up']    = $_browser['opera'] && !$_browser['opera2'] && !$_browser['opera3'] && !$_browser['opera4'] && !$_browser['opera5'] && !$_browser['opera6'];
            $_browser['opera8up']    = $_browser['opera'] && !$_browser['opera2'] && !$_browser['opera3'] && !$_browser['opera4'] && !$_browser['opera5'] && !$_browser['opera6'] && !$_browser['opera7'];
            $_browser['opera9up']    = $_browser['opera'] && !$_browser['opera2'] && !$_browser['opera3'] && !$_browser['opera4'] && !$_browser['opera5'] && !$_browser['opera6'] && !$_browser['opera7'] && !$_browser['opera8'];
            $_browser['belowopera8'] = $_browser['opera'] && !$_browser['opera8up'];
            $_browser['aol']         = strpos($_agent, 'aol') !== false;
            $_browser['aol3']        = $_browser['aol'] && $_browser['ie3'];
            $_browser['aol4']        = $_browser['aol'] && $_browser['ie4'];
            $_browser['aol5']        = strpos($_agent, 'aol 5') !== false;
            $_browser['aol6']        = strpos($_agent, 'aol 6') !== false;
            $_browser['aol7']        = strpos($_agent, 'aol 7') !== false || strpos($_agent, 'aol7') !== false;
            $_browser['aol8']        = strpos($_agent, 'aol 8') !== false || strpos($_agent, 'aol8') !== false;
            $_browser['webtv']       = strpos($_agent, 'webtv') !== false;
            $_browser['aoltv']       = $_browser['tvnavigator'] = strpos($_agent, 'navio') !== false || strpos($_agent, 'navio_aoltv') !== false;
            $_browser['hotjava']     = strpos($_agent, 'hotjava') !== false;
            $_browser['hotjava3']    = $_browser['hotjava'] && $_majorVersion == 3;
            $_browser['hotjava3up']  = $_browser['hotjava'] && $_majorVersion >= 3;
            $_browser['iemobile']    = strpos($_agent, 'iemobile') !== false || strpos($_agent, 'windows ce') !== false && (strpos($_agent, 'ppc') !== false || strpos($_agent, 'smartphone') !== false);
        }

        if ($_detectFlags[self::DETECT_ALL] || ($_detectFlags[self::DETECT_BROWSER] && $_detectFlags[self::DETECT_FEATURES])) {
            // Javascript Check
            if ($_browser['ns2'] || $_browser['ie3']) {
                $this->SetFeature('javascript', 1.0);
            } else {
                if ($_browser['iemobile']) {
                    // no javascript
                } else {
                    if ($_browser['opera5up']) {
                        $this->SetFeature('javascript', 1.3);
                    } else {
                        if ($_browser['opera'] || $_browser['ns3']) {
                            $this->SetFeature('javascript', 1.1);
                        } else {
                            if (($_browser['ns4'] && ($_version <= 4.05)) || $_browser['ie4']) {
                                $this->SetFeature('javascript', 1.2);
                            } else {
                                if (($_browser['ie5up'] && strpos($_agent, 'mac') !== false) || $_browser['konq']) {
                                    $this->SetFeature('javascript', 1.4);
                                } else {
                                    if (($_browser['ns4'] && ($_version > 4.05)) || $_browser['ie5up'] || $_browser['hotjava3up']) {
                                        $this->SetFeature('javascript', 1.3);
                                    } else {
                                        if ($_browser['ns6up'] || $_browser['gecko'] || $_browser['netgem']) {
                                            $this->SetFeature('javascript', 1.5);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        /** OS Check * */
        if ($_detectFlags[self::DETECT_ALL] || $_detectFlags[self::DETECT_OS]) {
            $_os['win']            = strpos($_agent, 'win') !== false || strpos($_agent, '16bit') !== false;
            $_os['win95']          = strpos($_agent, 'win95') !== false || strpos($_agent, 'windows 95') !== false;
            $_os['win16']          = strpos($_agent, 'win16') !== false || strpos($_agent, '16bit') !== false || strpos($_agent, 'windows 3.1') !== false || strpos($_agent, 'windows 16-bit') !== false;
            $_os['win31']          = strpos($_agent, 'windows 3.1') !== false || strpos($_agent, 'win16') !== false || strpos($_agent, 'windows 16-bit') !== false;
            $_os['winme']          = strpos($_agent, 'win 9x 4.90') !== false;
            $_os['wince']          = strpos($_agent, 'windows ce') !== false;
            $_os['win2k']          = strpos($_agent, 'windows nt 5.0') !== false;
            $_os['winxp']          = strpos($_agent, 'windows nt 5.1') !== false;
            $_os['win2003']        = strpos($_agent, 'windows nt 5.2') !== false;
            $_os['win98']          = strpos($_agent, 'win98') !== false || strpos($_agent, 'windows 98') !== false;
            $_os['win9x']          = $_os['win95'] || $_os['win98'];
            $_os['winnt']          = (strpos($_agent, 'winnt') !== false || strpos($_agent, 'windows nt') !== false) && strpos($_agent, 'windows nt 5') === false;
            $_os['win32']          = $_os['win95'] || $_os['winnt'] || $_os['win98'] || $_majorVersion >= 4 && strpos($_agent, 'win32') !== false || strpos($_agent, '32bit') !== false;
            $_os['vista']          = strpos($_agent, 'windows nt 6.0') !== false;
            $_os['win7']           = strpos($_agent, 'windows nt 6.1') !== false;
            $_os['os2']            = strpos($_agent, 'os/2') !== false || strpos($_agent, 'ibm-webexplorer') !== false;
            $_os['mac']            = strpos($_agent, 'mac') !== false;
            $_os['mactiger']       = $_os['mac'] && (strpos($_agent, '10.4') !== false || strpos($_agent, '10_4') !== false);
            $_os['macleopard']     = $_os['mac'] && (strpos($_agent, '10.5') !== false || strpos($_agent, '10_5') !== false);
            $_os['macsnowleopard'] = $_os['mac'] && (strpos($_agent, '10.6') !== false || strpos($_agent, '10_6') !== false);
            $_os['mac68k']         = $_os['mac'] && (strpos($_agent, '68k') !== false || strpos($_agent, '68000') !== false);
            $_os['macppc']         = $_os['mac'] && (strpos($_agent, 'ppc') !== false || strpos($_agent, 'powerpc') !== false);
            $_os['iphone']         = strpos($_agent, 'iphone') !== false;
            $_os['sun']            = strpos($_agent, 'sunos') !== false;
            $_os['sun4']           = strpos($_agent, 'sunos 4') !== false;
            $_os['sun5']           = strpos($_agent, 'sunos 5') !== false;
            $_os['suni86']         = $_os['sun'] && strpos($_agent, 'i86') !== false;
            $_os['irix']           = strpos($_agent, 'irix') !== false;
            $_os['irix5']          = strpos($_agent, 'irix 5') !== false;
            $_os['irix6']          = strpos($_agent, 'irix 6') !== false || strpos($_agent, 'irix6') !== false;
            $_os['hpux']           = strpos($_agent, 'hp-ux') !== false;
            $_os['hpux9']          = $_os['hpux'] && strpos($_agent, '09.') !== false;
            $_os['hpux10']         = $_os['hpux'] && strpos($_agent, '10.') !== false;
            $_os['aix']            = strpos($_agent, 'aix') !== false;
            $_os['aix1']           = strpos($_agent, 'aix 1') !== false;
            $_os['aix2']           = strpos($_agent, 'aix 2') !== false;
            $_os['aix3']           = strpos($_agent, 'aix 3') !== false;
            $_os['aix4']           = strpos($_agent, 'aix 4') !== false;
            $_os['linux']          = strpos($_agent, 'inux') !== false;
            $_os['sco']            = strpos($_agent, 'sco') !== false || strpos($_agent, 'unix_sv') !== false;
            $_os['unixware']       = strpos($_agent, 'unix_system_v') !== false;
            $_os['mpras']          = strpos($_agent, 'ncr') !== false;
            $_os['reliant']        = strpos($_agent, 'reliant') !== false;
            $_os['dec']            = strpos($_agent, 'dec') !== false || strpos($_agent, 'osf1') !== false || strpos($_agent, 'dec_alpha') !== false || strpos($_agent, 'alphaserver') !== false || strpos($_agent, 'ultrix') !== false || strpos($_agent, 'alphastation') !== false;
            $_os['sinix']          = strpos($_agent, 'sinix') !== false;
            $_os['freebsd']        = strpos($_agent, 'freebsd') !== false;
            $_os['bsd']            = strpos($_agent, 'bsd') !== false;
            $_os['unix']           = strpos($_agent, 'x11') !== false || strpos($_agent, 'unix') !== false || $_os['sun'] || $_os['irix'] || $_os['hpux'] || $_os['sco'] || $_os['unixware'] || $_os['mpras'] || $_os['reliant'] || $_os['dec'] || $_os['sinix'] || $_os['aix'] || $_os['linux'] || $_os['bsd'] || $_os['freebsd'];
            $_os['vms']            = strpos($_agent, 'vax') !== false || strpos($_agent, 'openvms') !== false;
        }

        // Setup the quirks
        if ($_detectFlags[self::DETECT_ALL] || ($_detectFlags[self::DETECT_BROWSER] && $_detectFlags[self::DETECT_QUIRKS])) {
            if ($_browser['konq']) {
                $this->SetQuirk('empty_file_input_value');
            }

            if ($_browser['ie']) {
                $this->SetQuirk('cache_ssl_downloads');
            }

            if ($_browser['ie6']) {
                $this->SetQuirk('scrollbar_in_way');
            }

            if ($_browser['ie5']) {
                $this->SetQuirk('break_disposition_header');
            }

            if ($_browser['ie7']) {
                $this->SetQuirk('popups_disabled');
            }

            if ($_browser['ns6']) {
                $this->SetQuirk('popups_disabled');
                $this->SetQuirk('must_cache_forms');
            }

            if ($_browser['nav'] && $_subVersion < .79) {
                $this->SetQuirk('nested_table_render_bug');
            }
        }

        // Set features
        if ($_detectFlags[self::DETECT_ALL] || ($_detectFlags[self::DETECT_BROWSER] && $_detectFlags[self::DETECT_FEATURES])) {
            if ($_browser['gecko'] && preg_match(';gecko/([\d]+)\b;i', $_agent, $_matches)) {
                $this->SetFeature('gecko', $_matches[1]);
            }

            if ($_browser['gecko'] || ($_browser['ie5up'] && !$_browser['iemobile']) || $_browser['konq'] || $_browser['opera8up'] && !$_os['wince']) {
                $this->SetFeature('ajax');
            }

            if ($_browser['ns6up'] || $_browser['opera5up'] || $_browser['konq'] || $_browser['netgem']) {
                $this->SetFeature('dom');
            }

            if ($_browser['ie4up'] || $_browser['ns4up'] || $_browser['opera5up'] || $_browser['konq'] || $_browser['netgem']) {
                $this->SetFeature('dhtml');
            }

            if ($_browser['firefox1.5'] || $_browser['firefox2.x'] || $_browser['opera9up']) {
                $this->SetFeature('svg');
            }

            if ($_browser['gecko'] || $_browser['ns6up'] || $_browser['ie5up'] || $_browser['konq'] || $_browser['opera7up']) {
                $this->SetFeature('css2');
            }
        }

        if ($_detectFlags[self::DETECT_ALL] || $_detectFlags[self::DETECT_ACCEPT]) {
            $_mimeTypes = preg_split(';[\s,]+;', substr(getenv('HTTP_ACCEPT'), 0, strpos(getenv('HTTP_ACCEPT') . ';', ';')), -1, PREG_SPLIT_NO_EMPTY);
            $this->SetAcceptType((array) $_mimeTypes, 'mimetype');

            $_languages = preg_split(';[\s,]+;', substr(getenv('HTTP_ACCEPT_LANGUAGE'), 0, strpos(getenv('HTTP_ACCEPT_LANGUAGE') . ';', ';')), -1, PREG_SPLIT_NO_EMPTY);
            if (empty($_languages)) {
                $_languages = 'en';
            }

            $this->SetAcceptType((array) $_languages, 'language');

            $_encodings = preg_split(';[\s,]+;', substr(getenv('HTTP_ACCEPT_ENCODING'), 0, strpos(getenv('HTTP_ACCEPT_ENCODING') . ';', ';')), -1, PREG_SPLIT_NO_EMPTY);
            $this->SetAcceptType((array) $_encodings, 'encoding');

            $_charsets = preg_split(';[\s,]+;', substr(getenv('HTTP_ACCEPT_CHARSET'), 0, strpos(getenv('HTTP_ACCEPT_CHARSET') . ';', ';')), -1, PREG_SPLIT_NO_EMPTY);
            $this->SetAcceptType((array) $_charsets, 'charset');
        }

        return true;
    }

    /**
     * Sets a class option.  The available settings are:
     * o 'userAgent' => The user agent string to detect (useful for
     * checking a string manually).
     * o 'detectOptions' => The level of checking to do.  A single level
     * or an array of options.  Default is NET_USERAGENT_DETECT_ALL.
     *
     * @author Varun Shoor
     *
     * @param string $_optionKey
     * @param string $_optionValue
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetOption($_optionKey, $_optionValue)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $this->_options[$_optionKey] = $_optionValue;

        return true;
    }

    /**
     * Look up the provide browser flag and return a boolean value
     *
     * Given one of the flags listed in the properties, this function will return
     * the value associated with that flag.
     *
     * @author Varun Shoor
     *
     * @param string $_match flag to lookup
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IsBrowser($_match)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_browser = $this->_browser;

        return isset($_browser[strtolower($_match)]) ? $_browser[strtolower($_match)] : false;
    }

    /**
     * Since simply returning the "browser" is somewhat ambiguous since there
     * are different ways to classify the browser, this function works by taking
     * an expect list and returning the string of the first match, so put the important
     * ones first in the array.
     *
     * @author Varun Shoor
     *
     * @param  mixed $_expectList the browser flags to search for
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetBrowser($_expectList)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_browser = $this->_browser;
        foreach ((array) $_expectList as $_brwsr) {
            if (isset($_browser[strtolower($_brwsr)])) {
                return $_brwsr;
            }
        }

        return true;
    }

    /**
     * This function returns the vendor string corresponding to the flag.
     *
     * Either use the default matches or pass in an associative array of
     * flags and corresponding vendor strings.  This function will find
     * the highest version flag and return the vendor string corresponding
     * to the appropriate flag.  Be sure to pass in the flags in ascending order
     * if you want a basic matches first, followed by more detailed matches.
     *
     * @author Varun Shoor
     *
     * @param  mixed $_vendorStrings (optional) array of flags matched with vendor strings
     *
     * @return string vendor string matches appropriate flag
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetBrowserString($_vendorStrings = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        if (is_null($_vendorStrings)) {
            $_vendorStrings = array(
                'ie'            => 'Microsoft Internet Explorer',
                'ie4up'         => 'Microsoft Internet Explorer 4.x',
                'ie5up'         => 'Microsoft Internet Explorer 5.x',
                'ie6up'         => 'Microsoft Internet Explorer 6.x',
                'ie7up'         => 'Microsoft Internet Explorer 7.x',
                'ie8up'         => 'Microsoft Internet Explorer 8.x',
                'ie8tr'         => 'Microsoft Internet Explorer 8.x (Compatibility View)',
                'ie9up'         => 'Microsoft Internet Explorer 9.x',
                'opera4'        => 'Opera 4.x',
                'opera5up'      => 'Opera 5.x',
                'nav'           => 'Netscape Navigator',
                'ns4'           => 'Netscape 4.x',
                'ns6up'         => 'Mozilla/Netscape 6.x',
                'firefox0.x'    => 'Firefox 0.x',
                'firefox1.x'    => 'Firefox 1.x',
                'firefox1.5'    => 'Firefox 1.5',
                'firefox2.x'    => 'Firefox 2.x',
                'firefox3.x'    => 'Firefox 3.x',
                'konq'          => 'Konqueror',
                'safari'        => 'Safari',
                'safari_mobile' => 'Safari Mobile',
                'chrome'        => 'Google Chrome',
                'netgem'        => 'Netgem/iPlayer'
            );
        }

        $_browser = $this->_browser;
        foreach ((array) $_vendorStrings as $_flag => $_string) {
            if (isset($_browser[$_flag])) {
                $_vendorString = $_string;
            }
        }

        // if there are no matches just use the user agent leading idendifier (usually Mozilla)
        if (!isset($_vendorString)) {
            $_leadingIdentifier = $this->_leadingIdentifier;
            $_vendorString      = $_leadingIdentifier;
        }

        return $_vendorString;
    }

    /**
     * Look up the provide OS flag and return a boolean value
     *
     * Given one of the flags listed in the properties, this function will return
     * the value associated with that flag for the operating system.
     *
     * @author Varun Shoor
     *
     * @param  string $_match flag to lookup
     *
     * @return boolean whether or not the OS satisfies this flag
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function IsOS($_match)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_os = $this->_os;

        return isset($_os[strtolower($_match)]) ? $_os[strtolower($_match)] : false;
    }

    /**
     * Since simply returning the "os" is somewhat ambiguous since there
     * are different ways to classify the browser, this function works by taking
     * an expect list and returning the string of the first match, so put the important
     * ones first in the array.
     *
     * @author Varun Shoor
     *
     * @param mixed $_expectList
     *
     * @return string|false first flag that matches
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOS($_expectList)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_os = $this->_os;
        foreach ((array) $_expectList as $_expectOs) {
            if (!empty($_os[strtolower($_expectOs)])) {
                return $_expectOs;
            }
        }

        return false;
    }

    /**
     * This function returns the os string corresponding to the flag.
     *
     * Either use the default matches or pass in an associative array of
     * flags and corresponding os strings.  This function will find
     * the highest version flag and return the os string corresponding
     * to the appropriate flag.  Be sure to pass in the flags in ascending order
     * if you want a basic matches first, followed by more detailed matches.
     *
     * @author Varun Shoor
     *
     * @param  mixed $_osStrings (optional) array of flags matched with os strings
     *
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOSString($_osStrings = null)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        if (is_null($_osStrings)) {
            $_osStrings = array(
                'win'            => 'Microsoft Windows',
                'wince'          => 'Microsoft Windows CE',
                'win9x'          => 'Microsoft Windows 9x',
                'winme'          => 'Microsoft Windows Millenium',
                'win2k'          => 'Microsoft Windows 2000',
                'winnt'          => 'Microsoft Windows NT',
                'winxp'          => 'Microsoft Windows XP',
                'win2003'        => 'Microsoft Windows 2003',
                'vista'          => 'Microsoft Windows Vista',
                'win7'           => 'Microsoft Windows 7',
                'mac'            => 'Macintosh',
                'mactiger'       => 'OS X Tiger (10.4)',
                'macleopard'     => 'OS X Leopard (10.5)',
                'macsnowleopard' => 'OS X Snow Leopard (10.6)',
                'iphone'         => 'iPhone',
                'os2'            => 'OS/2',
                'unix'           => 'Linux/Unix'
            );
        }

        $_osString = 'Unknown';

        $_os = $this->_os;
        foreach ((array) $_osStrings as $_flag => $_string) {
            if (isset($_os[$_flag])) {
                $_osString = $_string;
                break;
            }
        }

        return $_osString;
    }

    /**
     * Set a unique behavior for the current browser.
     *
     * Many client browsers do some really funky things, and this
     * mechanism allows the coder to determine if an excepetion must
     * be made with the current client.
     *
     * @author Varun Shoor
     *
     * @param string $_quirkKey
     * @param bool   $_hasQuirk
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetQuirk($_quirkKey, $_hasQuirk = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $this->_quirks[$_quirkKey] = $_hasQuirk;

        return true;
    }

    /**
     * Check a unique behavior for the current browser.
     *
     * Many client browsers do some really funky things, and this
     * mechanism allows the coder to determine if an excepetion must
     * be made with the current client.
     *
     * @author Varun Shoor
     *
     * @param string $_quirkKey
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function HasQuirk($_quirkKey)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        if (isset($this->_quirks[$_quirkKey])) {
            return true;
        }

        return false;
    }

    /**
     * Get the unique behavior for the current browser.
     *
     * Many client browsers do some really funky things, and this
     * mechanism allows the coder to determine if an excepetion must
     * be made with the current client.
     *
     * @author Varun Shoor
     *
     * @param string $_quirkKey
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetQuirk($_quirkKey)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        } else {
            if (!isset($this->_quirks[$_quirkKey])) {
                return false;
            }
        }

        return $this->_quirks[$_quirkKey];
    }

    /**
     * Set capabilities for the current browser.
     *
     * Since the capabilities of client browsers vary widly, this interface
     * helps keep track of the core features of a client, such as if the client
     * supports dhtml, dom, javascript, etc.
     *
     * @author Varun Shoor
     *
     * @param string $_featureName
     * @param bool $_featureValue
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetFeature($_featureName, $_featureValue = true)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $this->_features[$_featureName] = $_featureValue;

        return true;
    }

    /**
     * Check the capabilities for the current browser.
     *
     * Since the capabilities of client browsers vary widly, this interface
     * helps keep track of the core features of a client, such as if the client
     * supports dhtml, dom, javascript, etc.
     *
     * @author Varun Shoor
     *
     * @param string $_featureKey
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function HasFeature($_featureKey)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        if (isset($this->_features[$_featureKey])) {
            return true;
        }

        return false;
    }

    /**
     * Get the capabilities for the current browser.
     *
     * Since the capabilities of client browsers vary widly, this interface
     * helps keep track of the core features of a client, such as if the client
     * supports dhtml, dom, javascript, etc.
     *
     * @author Varun Shoor
     *
     * @param string $_featureKey
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFeature($_featureKey)
    {
        $_featureKey = strtolower($_featureKey);

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        } else {
            if (!isset($this->_features[$_featureKey])) {
                return false;
            }
        }

        return $this->_features[$_featureKey];
    }

    /**
     * Retrive the accept type for the current browser.
     *
     * To keep track of the mime-types, languages, charsets and encodings
     * that each browser accepts we use associative arrays for each type.
     * This function works like getBrowser() as it takes an expect list
     * and returns the first match.  For instance, to find the language
     * you would pass in your allowed languages and see if any of the
     * languages set in the browser match.
     *
     * @param  string $_expectList values to check
     * @param  string $_type       type of accept
     *
     * @access public
     * @return string|null the first matched value
     *
     * @throws SWIFT_Exception If the class is not loaded
     */
    public function GetAcceptType($_expectList, $_type)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_type = strtolower($_type);

        if ($_type == 'mimetype' || $_type == 'language' || $_type == 'charset' || $_type == 'encoding') {
            $_typeArray = array();
            if (isset($this->_acceptTypes[$_type])) {
                $_typeArray = $this->_acceptTypes[$_type];
            }

            foreach ((array) $_expectList as $_match) {
                if (!empty($_typeArray[$_match])) {
                    return $_match;
                }
            }
        }

        return null;
    }

    /**
     * Set the accept types for the current browser.
     *
     * To keep track of the mime-types, languages, charsets and encodings
     * that each browser accepts we use associative arrays for each type.
     * This function takes and array of accepted values for the type and
     * records them for retrieval.
     *
     * @author Varun Shoor
     *
     * @param  mixed  $_values values of the accept type
     * @param  string $_type   type of accept
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function SetAcceptType($_values, $_type)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_type = strtolower($_type);

        if ($_type == 'mimetype' || $_type == 'language' || $_type == 'charset' || $_type == 'encoding') {
            $_typeArray = array();
            if (isset($this->_acceptTypes[$_type])) {
                $_typeArray = $this->_acceptTypes[$_type];
            }

            foreach ((array) $_values as $_value) {
                $_typeArray[$_value] = true;
            }

            $this->_acceptTypes[$_type] = $_typeArray;
        }

        return true;
    }

    /**
     * Check the accept types for the current browser.
     *
     * To keep track of the mime-types, languages, charsets and encodings
     * that each browser accepts we use associative arrays for each type.
     * This function checks the array for the given type and determines if
     * the browser accepts it.
     *
     * @author Varun Shoor
     *
     * @param string $_value Values to Check
     * @param string $_type  Type of Accept
     *
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function HasAcceptType($_value, $_type)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        return $this->GetAcceptType((array) $_value, $_type);
    }

    /**
     * Retrieves the User Agent
     *
     * @author Varun Shoor
     * @return string The User Agent
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetUserAgent()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_userAgent = $this->_userAgent;

        return $_userAgent;
    }

    /**
     * Returns the browser list
     *
     * @author Varun Shoor
     * @return array The Browser List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetBrowserList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_finalBrowserList = array();
        foreach ($this->_browser as $_browserKey => $_browserVal) {
            if ($_browserVal == true) {
                $_finalBrowserList[$_browserKey] = $_browserVal;
            }
        }

        return $_finalBrowserList;
    }

    /**
     * Retrieves the OS List
     *
     * @author Varun Shoor
     * @return array The OS List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOSList()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);
        }

        $_finalOSList = array();
        foreach ($this->_os as $_osKey => $_osVal) {
            if ($_osVal == true) {
                $_finalOSList[$_osKey] = $_osVal;
            }
        }

        return $_finalOSList;
    }
}

?>
