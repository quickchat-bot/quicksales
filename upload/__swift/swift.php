<?php 
 require_once(dirname(__FILE__).'/gfiht.php');
                    /**
                    * ###############################################
                    *
                    * SWIFT Framework
                    * _______________________________________________
                    *
                    * @package        SWIFT
                    * @copyright    Copyright (c) 2001-2014, Kayako
                    * @license        http://www.kayako.com/license
                    * @link        http://www.kayako.com
                    *
                    * ###############################################
                    */

                       require_once __DIR__ . '/../vendor/autoload.php'; use Base\Models\Language\SWIFT_Language;   define('IN_SWIFT', '1'); define('DATENOW', time()); define('SWIFT_DATE', DATENOW); define('SWIFT_CRLF', "\n"); define('SWIFT_BASEDIRECTORY', '__swift'); define('SWIFT_APPSDIRECTORY', '__apps'); define('SWIFT_CACHEDIRECTORY', 'cache'); define('SWIFT_FILESDIRECTORY', 'files'); define('SWIFT_LOGDIRECTORY', 'logs'); define('SWIFT_COREAPPSDIRECTORY', 'apps'); define('SWIFT_LIBRARYDIRECTORY', 'library'); define('SWIFT_MODELSDIRECTORY', 'models'); define('SWIFT_INCLUDESDIRECTORY', 'includes'); define('SWIFT_LOCALEDIRECTORY', 'locale'); define('SWIFT_CONFIGDIRECTORY', 'config'); define('SWIFT_THIRDPARTYDIRECTORY', 'thirdparty'); define('SWIFT_THEMESDIRECTORY', 'themes'); define('SWIFT_THEMEGLOBALDIRECTORY', '__global'); define('SWIFT_THEMECPDIRECTORY', '__cp'); define('SWIFT_JAVASCRIPTDIRECTORY', 'javascript'); define('SWIFT_BASE_DIRECTORY', SWIFT_BASEDIRECTORY); define('SWIFT_APPS_DIRECTORY', SWIFT_APPSDIRECTORY); define('SWIFT_CACHE_DIRECTORY', SWIFT_CACHEDIRECTORY); define('SWIFT_FILES_DIRECTORY', SWIFT_FILESDIRECTORY); define('SWIFT_LOG_DIRECTORY', SWIFT_LOGDIRECTORY); define('SWIFT_COREAPPS_DIRECTORY', SWIFT_COREAPPSDIRECTORY); define('SWIFT_LIBRARY_DIRECTORY', SWIFT_LIBRARYDIRECTORY); define('SWIFT_MODELS_DIRECTORY', SWIFT_MODELSDIRECTORY); define('SWIFT_INCLUDES_DIRECTORY', SWIFT_INCLUDESDIRECTORY); define('SWIFT_LOCALE_DIRECTORY', SWIFT_LOCALEDIRECTORY); define('SWIFT_CONFIG_DIRECTORY', SWIFT_CONFIGDIRECTORY); define('SWIFT_THIRDPARTY_DIRECTORY', SWIFT_THIRDPARTYDIRECTORY); define('SWIFT_THEMES_DIRECTORY', SWIFT_THEMESDIRECTORY); define('SWIFT_THEMEGLOBAL_DIRECTORY', SWIFT_THEMEGLOBALDIRECTORY); define('SWIFT_JAVASCRIPT_DIRECTORY', SWIFT_JAVASCRIPTDIRECTORY); define('SWIFT_CLASSNOTLOADED', 'Class not loaded'); define('SWIFT_INVALIDDATA', 'Invalid data provided'); define('SWIFT_CREATEFAILED', 'Object could not be created'); define('SWIFT_UPDATEFAILED', 'Object could not be updated'); define('SWIFT_NOPERMISSION', 'Access denied'); define('SWIFT_DATABASEERROR', 'Database error'); define('APP_CORE', 'core'); define('APP_BASE', 'base'); define('APP_LIVECHAT', 'livechat'); define('APP_TICKETS', 'tickets'); define('APP_PARSER', 'parser'); define('APP_KNOWLEDGEBASE', 'knowledgebase'); define('APP_NEWS', 'news'); define('APP_TROUBLESHOOTER', 'troubleshooter'); define('APP_REPORTS', 'reports'); define('APP_INTRANET', 'intranet'); define('APP_BACKEND', 'backend'); define('APP_CLUSTER', 'cluster'); define('APP_CC', 'cc'); define('APP_PRIVATE', 'private'); define('APP_GEOIP', 'geoip'); define('APP_DASHBOARD', 'dashboard'); define('APP_GECKOBOARD', 'geckoboard'); define('APP_HRMS', 'hrms'); define('APP_METRICS', 'metrics'); define('APP_STATS', 'stats'); define('APP_BACKSYNC', 'backsync');  define('SWIFT_VIEW', 1); define('SWIFT_INSERT', 2); define('SWIFT_UPDATE', 3); define('SWIFT_DELETE', 4); define('SWIFT_MANAGE', 5); define('SWIFT_IMPORT', 6); define('SWIFT_EXPORT', 7); define('SWIFT_PUBLIC', 'public'); define('SWIFT_PRIVATE', 'private'); define('SWIFT_PUBLICINT', '1'); define('SWIFT_PRIVATEINT', '0'); define('BUILD_DATE', '23 Feb 2023 01:38:10 PM'); define('SWIFT_PRODUCT', 'fusion'); define('SWIFT_VERSION', '4.98.8'); define('BUILD_TYPE', 'STABLE'); define('SOURCE_TYPE', 'SOURCEOBF'); define('SOURCE_COMMIT', '78092746a296cb512807b5bba91959913d775b49'); define('SOURCE_BRANCH', 'ORIGIN/MAIN'); define('SWIFT_PACKAGE', 'FUSION'); @ini_set('magic_quotes_gpc', '0');  @ini_set('expose_php', '0'); @ini_set('assert.active', '0'); date_default_timezone_set('GMT'); mb_internal_encoding("UTF-8"); if (!defined('SWIFT_INTERFACE')) { log_error_and_exit(); } if ((!isset($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == "") && isset($_SERVER['SCRIPT_NAME']) && $_SERVER['SCRIPT_NAME'] != '') { $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME']; if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != "") { $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING']; } } if (defined('SWIFT_CUSTOMPATH')) { chdir(SWIFT_CUSTOMPATH); } else { chdir(dirname(__FILE__) . '/../'); } define('SWIFT_BASEPATH', getcwd()); define('DWOO_DIRECTORY', './vendor/dwoo/dwoo/lib/Dwoo/'); require_once('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_INCLUDESDIRECTORY . '/functions.php'); require_once('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_LIBRARYDIRECTORY . '/class.SWIFT_Base.php'); require_once('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_LIBRARYDIRECTORY . '/class.SWIFT.php'); require_once('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_LIBRARYDIRECTORY . '/Exception/class.SWIFT_Exception.php'); require_once('./' . SWIFT_BASEDIRECTORY . '/' . SWIFT_LIBRARYDIRECTORY . '/FirePHP/class.SWIFT_FirePHP.php'); if (!in_array(SWIFT_INTERFACE, ['tests', 'phpstan', 'console'])) { header('X-Frame-Options: SAMEORIGIN'); header('X-XSS-Protection: 1'); header('X-Content-Type-Options: nosniff'); } $_SWIFT = SWIFT::GetInstance(); if ($_SWIFT instanceof SWIFT && isset($_SWIFT->Language)) { if ($_SWIFT->Language instanceof SWIFT_Language) { mb_internal_encoding($_SWIFT->Language->Get('charset')); } elseif ($_SWIFT->Language instanceof SWIFT_LanguageEngine) { ini_set('intl.default_locale', $_SWIFT->Language->GetLanguageCode()); } } 