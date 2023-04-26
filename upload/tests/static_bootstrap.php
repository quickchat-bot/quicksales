<?php

define('SWIFT_INTERFACE', 'phpstan');
define('SWIFT_INTERFACEFILE', __FILE__);
define('SWIFT_BASEPATH', realpath(__DIR__ . '/..'));
define('DWOO_DIRECTORY', SWIFT_BASEPATH . '/vendor/dwoo/dwoo/lib/Dwoo/');

define('IN_SWIFT', '1');
define('DATENOW', time()); // As of PHP5, time() is already GMT. There is no need to subtract date("Z"). -- RML
define('SWIFT_DATE', DATENOW); // Alias
define('SWIFT_CRLF', "\n");
define('SWIFT_BASEDIRECTORY', '__swift');
define('SWIFT_APPSDIRECTORY', '__apps');
define('SWIFT_CACHEDIRECTORY', 'cache');
define('SWIFT_FILESDIRECTORY', 'files');
define('SWIFT_LOGDIRECTORY', 'logs');
define('SWIFT_COREAPPSDIRECTORY', 'apps');
define('SWIFT_LIBRARYDIRECTORY', 'library');
define('SWIFT_MODELSDIRECTORY', 'models');
define('SWIFT_INCLUDESDIRECTORY', 'includes');
define('SWIFT_LOCALEDIRECTORY', 'locale');
define('SWIFT_CONFIGDIRECTORY', 'config');
define('SWIFT_THIRDPARTYDIRECTORY', 'thirdparty');
define('SWIFT_THEMESDIRECTORY', 'themes');
define('SWIFT_THEMEGLOBALDIRECTORY', '__global');
define('SWIFT_THEMECPDIRECTORY', '__cp');
define('SWIFT_JAVASCRIPTDIRECTORY', 'javascript');

define('SWIFT_BASE_DIRECTORY', SWIFT_BASEDIRECTORY);
define('SWIFT_APPS_DIRECTORY', SWIFT_APPSDIRECTORY);
define('SWIFT_CACHE_DIRECTORY', SWIFT_CACHEDIRECTORY);
define('SWIFT_FILES_DIRECTORY', SWIFT_FILESDIRECTORY);
define('SWIFT_LOG_DIRECTORY', SWIFT_LOGDIRECTORY);
define('SWIFT_COREAPPS_DIRECTORY', SWIFT_COREAPPSDIRECTORY);
define('SWIFT_LIBRARY_DIRECTORY', SWIFT_LIBRARYDIRECTORY);
define('SWIFT_MODELS_DIRECTORY', SWIFT_MODELSDIRECTORY);
define('SWIFT_INCLUDES_DIRECTORY', SWIFT_INCLUDESDIRECTORY);
define('SWIFT_LOCALE_DIRECTORY', SWIFT_LOCALEDIRECTORY);
define('SWIFT_CONFIG_DIRECTORY', SWIFT_CONFIGDIRECTORY);
define('SWIFT_THIRDPARTY_DIRECTORY', SWIFT_THIRDPARTYDIRECTORY);
define('SWIFT_THEMES_DIRECTORY', SWIFT_THEMESDIRECTORY);
define('SWIFT_THEMEGLOBAL_DIRECTORY', SWIFT_THEMEGLOBALDIRECTORY);
define('SWIFT_JAVASCRIPT_DIRECTORY', SWIFT_JAVASCRIPTDIRECTORY);

define('SWIFT_CLASSNOTLOADED', 'Class not loaded');
define('SWIFT_INVALIDDATA', 'Invalid data provided');
define('SWIFT_CREATEFAILED', 'Object could not be created');
define('SWIFT_UPDATEFAILED', 'Object could not be updated');
define('SWIFT_NOPERMISSION', 'Access denied');
define('SWIFT_DATABASEERROR', 'Database error');

define('APP_CORE', 'core');
define('APP_BASE', 'base');
define('APP_LIVECHAT', 'livechat');
define('APP_TICKETS', 'tickets');
define('APP_PARSER', 'parser');
define('APP_KNOWLEDGEBASE', 'knowledgebase');
define('APP_NEWS', 'news');
define('APP_TROUBLESHOOTER', 'troubleshooter');
define('APP_REPORTS', 'reports');
define('APP_INTRANET', 'intranet');
define('APP_BACKEND', 'backend');
define('APP_CLUSTER', 'cluster');
define('APP_CC', 'cc');
define('APP_PRIVATE', 'private');
define('APP_GEOIP', 'geoip');
define('APP_DASHBOARD', 'dashboard');
define('APP_GECKOBOARD', 'geckoboard');
define('APP_HRMS', 'hrms');
define('APP_METRICS', 'metrics');
define('APP_STATS', 'stats');
define('APP_BACKSYNC', 'backsync');

define('SWIFT_VIEW', 1);
define('SWIFT_INSERT', 2);
define('SWIFT_UPDATE', 3);
define('SWIFT_DELETE', 4);
define('SWIFT_MANAGE', 5);
define('SWIFT_IMPORT', 6);
define('SWIFT_EXPORT', 7);

define('SWIFT_PUBLIC', 'public');
define('SWIFT_PRIVATE', 'private');
define('SWIFT_PUBLICINT', '1');
define('SWIFT_PRIVATEINT', '0');

define('SWIFT_PRODUCT', 'Fusion');
define('SWIFT_VERSION', '4.92.4');
define('BUILD_TYPE', 'DEV');
define('BUILD_DATE', '- NA -');
define('SOURCE_TYPE', 'SOURCE');
define('SOURCE_BRANCH', 'TRUNK');
define('SWIFT_PACKAGE', 'fusion');

define('SWIFT_BASENAME', 'index.php?');
define('DB_HOSTNAME', 'mysql');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'OGYxYmI1OTUzZmM');
define('DB_NAME', 'swift_php56');
define('DB_PORT', '3306');
define('DB_LAYER', 'PDO');
define('DB_PDOPERSISTENT', false);
define('DB_TYPE', (PHP_VERSION >= 5.5 && extension_loaded('mysqli') ? 'mysqli' : 'mysql'));
define('SWIFT_ENVIRONMENT', 'PRODUCTION');
define('SWIFT_DEBUG', true);
define('DB_CHARSET', 'utf8');
define('SWIFT_LOCALE', 'en_EN');
define('LANGUAGE_ADMIN', 'en-us');
define('TABLE_PREFIX', 'sw');
define('USE_ICONV', false);
define('ENABLECHATGATEWAYBYPASS', true);
define('EXECUTESEGMENT', true);
define('EXECUTEGTM', false);

define('DB_DSN', DB_TYPE . '://' . urlencode(DB_USERNAME) . ':' . urlencode(DB_PASSWORD) . '@' . urlencode(DB_HOSTNAME . ':' . DB_PORT). '/' . urlencode(DB_NAME));
define('XMLS_SCHEMA_VERSION', '0.2');

require_once realpath(__DIR__ . '/../__swift/includes/data.geoipregions.php');
require_once realpath(__DIR__ . '/../__swift/includes/functions.php');
require_once realpath(__DIR__ . '/../__swift/includes/functions.settings.php');

if (false == class_exists('tidy')) {
    class tidy {

    }
}
