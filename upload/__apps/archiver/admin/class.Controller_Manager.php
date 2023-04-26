<?php
/**
 * ###############################################
 *
 * Archiver App for QuickSupport
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @package       archiver
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       https://github.com/trilogy-group/kayako-classic-archiver/blob/master/LICENSE
 * @link          https://github.com/trilogy-group/kayako-classic-archiver
 *
 * ###############################################
 */

namespace Archiver\Admin;

use Controller_admin;
use DateTime;
use PDO;
use SWIFT;
use SWIFT_App;
use SWIFT_Exception;
use SWIFT_Loader;
use Tickets\Library\Ticket\SWIFT_TicketManager;

/**
 * Class Controller_Manager
 * @property View_Manager $View
 */
class Controller_Manager extends Controller_admin
{

    use AjaxSearchTrait;
    use StaticFunctionsTrait;

    const MENU_ID = 444;
    const NAV_ID = 1;

    /**
     * @param string $where
     *
     * @param array $tables
     * @return int|float
     */
    private function CountRows($where, array $tables)
    {
        $total = 0;
        self::GetConn()->exec("SET NAMES 'utf8'");

        foreach ($tables as $table => $join) {
            try {
                $query = ($table === TABLE_PREFIX . 'tickets') ?
                    'SELECT count(*) FROM ' . $table . ' B WHERE ' . $where :
                    'SELECT count(*) FROM ' . $table . ' ' . $join . ' WHERE ' . $where;
                $result = self::GetConn()->query($query);
                $total += $result->fetchColumn();
            } catch (\Exception $e) {
                // ignore non existant tables
            }
        }

        return $total;
    }

    /**
     * Controller_Manager constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Render manage form
     *
     * @author Werner Garcia
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Index()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header(
            $this->Language->Get('archive_manager'),
            self::MENU_ID,
            self::NAV_ID
        );

        $this->View->RenderSearchForm();

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Render empty trash form
     *
     * @author Werner Garcia
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Trash()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $this->UserInterface->Header(
            $this->Language->Get('empty_trash'),
            self::MENU_ID,
            self::NAV_ID
        );

        $this->View->RenderTrashForm();

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Handle search submit
     *
     * @author Werner Garcia
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     */
    public function Search()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (isset($_REQUEST['ar_start_date']) && !empty($_REQUEST['ar_start_date'])) {
            $_start_date = self::GetDBDate($_REQUEST['ar_start_date']);
            if ($_start_date === false) {
                return $this->ErrorMessage('Start Date', 'archiver_date_valid', self::GetSwiftDateFormat(false));
            }
            if (strtotime((string) $_start_date) > time()) {
                return $this->ErrorMessage('Start Date', 'archiver_date_future');
            }
        } else {
            return $this->ErrorMessage('Start Date', 'archiver_noempty');
        }

        if (isset($_REQUEST['ar_end_date']) && !empty($_REQUEST['ar_end_date'])) {
            $_end_date = self::GetDBDate($_REQUEST['ar_end_date']);
            if ($_end_date === false) {
                return $this->ErrorMessage('End Date', 'archiver_date_valid', self::GetSwiftDateFormat(false));
            }
        } else {
            return $this->ErrorMessage('End Date', 'archiver_noempty');
        }

        if (strtotime((string) $_end_date) < strtotime((string) $_start_date)) {
            return $this->ErrorMessage('End Date', 'archiver_date_greater');
        }

        $_email = (isset($_REQUEST['ar_email']) && !empty($_REQUEST['ar_email']))
            ? trim($_REQUEST['ar_email']) : null;

        if ($_email) {
            $_email = filter_var($_email, FILTER_VALIDATE_EMAIL);
            if ($_email === false) {
                return $this->ErrorMessage('Email', 'archiver_valid');
            }
        }

        $_page_size = isset($_REQUEST['ar_page_size']) ? (int)$_REQUEST['ar_page_size'] : 20;

        if ($_page_size <= 0) {
            return $this->ErrorMessage('Page Size', 'archiver_valid');
        }

        $_is_trash = isset($_REQUEST['ar_is_trash']) ? (bool)$_REQUEST['ar_is_trash'] : false;

        $_where = self::GetWhere($_email, $_start_date, $_end_date, $_is_trash);

        $_row_count = $this->CountRows($_where, [TABLE_PREFIX . 'tickets' => '']);

        $this->UserInterface->Header(
            $this->Language->Get($_is_trash ? 'empty_trash' : 'archive_manager') . ' - ' . $this->Language->Get('archive_results'),
            self::MENU_ID,
            self::NAV_ID
        );

        $this->View->RenderSearchGrid(
            $_where,
            $_REQUEST['ar_start_date'],
            $_REQUEST['ar_end_date'],
            $_email,
            $_page_size,
            $_row_count,
            $_is_trash
        );

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * Export the Tickets
     *
     * @author Werner Garcia
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ExportAll()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        SWIFT::GetInstance()->Log->Log('ExportAll REQUEST VALUES: ' . print_r($_REQUEST, true), 0,
            strftime('%Y-%m-%d'));

        $_start_date = isset($_REQUEST['ar_start_date']) ?
            self::GetDBDate($_REQUEST['ar_start_date']) :
            strftime('%Y-%m-%d', 0);

        $_end_date = isset($_REQUEST['ar_end_date']) ?
            self::GetDBDate($_REQUEST['ar_end_date']) :
            strftime('%Y-%m-%d',
                (new DateTime('now'))->getTimestamp());

        $_email = isset($_REQUEST['ar_email']) ?
            filter_var(trim($_REQUEST['ar_email']), FILTER_VALIDATE_EMAIL) : false;

        if (!$_email) {
            $_email = '-';
        }

        $_ids = isset($_REQUEST['itemid']) ? $_REQUEST['itemid'] : [];
        $_idlist = implode(',', $_ids);
        $where = self::GetWhere($_email, $_start_date, $_end_date, false, $_idlist);

        $server_info = self::GetConn()->getAttribute(PDO::ATTR_SERVER_VERSION);
        self::GetConn()->exec("SET NAMES 'utf8'");

        $this->Template->Assign('server_info', $server_info);
        $this->Template->Assign('dbname', DB_NAME);
        $this->Template->Assign('host', DB_HOSTNAME);

        $content = $this->Template->Get('my_header');

        $content .= self::DumpTables($this->Template, $where);

        $date = gmdate('Y/m/j H:i:s', time());
        $this->Template->Assign('date', $date);
        $content .= $this->Template->Get('my_footer');

        self::DownloadFile($content);

        return true;
    }

    /**
     * Exports the selected tickets in the search grid
     * @param array $_ids
     * @return bool
     * @throws SWIFT_Exception
     */
    public static function ExportList(array $_ids)
    {
        // Generate request
        $params = array_filter($_REQUEST, function ($p) {
            return !is_array($p) && 0 === strpos($p, 'ar_');
        });

        $url = SWIFT::Get('basename') . '/archiver/Manager/ExportAll&';
        $url .= http_build_query(array_merge($params, ['itemid' => $_ids]));

        SWIFT::Set('export_ready', $url);

        SWIFT::GetInstance()->Log->Log('ExportList redirect url: ' . $url, 0, strftime('%Y-%m-%d'));

        return true;
    }

    /**
     * Deletes all the tickets
     *
     * @author Werner Garcia
     * @return int
     * @throws SWIFT_Exception
     */
    public function DeleteAll()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        return self::DeleteList([]);
    }

    /**
     * Deletes the selected tickets in the search grid
     * @param array $_ids
     * @return int
     * @throws SWIFT_Exception
     */
    public static function DeleteList(array $_ids)
    {
        SWIFT::GetInstance()->Log->Log('DeleteList REQUEST VALUES: ' . print_r($_REQUEST, true), 0,
            strftime('%Y-%m-%d'));

        $_start_date = isset($_REQUEST['ar_start_date']) ?
            self::GetDBDate($_REQUEST['ar_start_date']) :
            strftime('%Y-%m-%d', 0);

        $_end_date = isset($_REQUEST['ar_end_date']) ?
            self::GetDBDate($_REQUEST['ar_end_date']) :
            strftime('%Y-%m-%d',
                (new DateTime('now'))->getTimestamp());

        $_email = isset($_REQUEST['ar_email']) ?
            filter_var(trim($_REQUEST['ar_email']), FILTER_VALIDATE_EMAIL) : false;

        if (!$_email) {
            $_email = '-';
        }

        $is_trash = (bool)$_REQUEST['ar_is_trash'];

        $_idlist = implode(',', $_ids);
        $where = self::GetWhere($_email, $_start_date, $_end_date, $is_trash, $_idlist);

        $_totalRows = self::ProcessDelete($where);

        // rebuild cache when finished
        if (SWIFT_App::IsInstalled(APP_TICKETS)) {
            SWIFT_Loader::LoadLibrary('Ticket:TicketManager', APP_TICKETS);
            SWIFT_TicketManager::RebuildCache();
        }

        return (int) $_totalRows;
    }

    /**
     * @param string $field
     * @param string $langid
     * @param string $params
     * @return bool
     * @throws SWIFT_Exception
     */
    private function ErrorMessage($field, $langid, $params = null)
    {
        if ($params) {
            $msg = sprintf($this->Language->Get($langid), $params);
        } else {
            $msg = $this->Language->Get($langid);
        }
        $this->UserInterface->Error('Error', sprintf('%s: %s', $field, $msg));
        $this->Load->Index();

        return false;
    }
}
