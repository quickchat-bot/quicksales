<?php

namespace Archiver\Admin;

use DateTime;
use PDO;
use PDOStatement;
use SWIFT;
use SWIFT_Exception;
use SWIFT_TemplateEngine;

trait StaticFunctionsTrait
{

    /** @var PDO|null $conn */
    private static $_conn;

    /**
     * @return mixed|PDO
     * @throws SWIFT_Exception
     */
    private static function GetConn()
    {
        if (self::$_conn === null) {
            $_swift = SWIFT::GetInstance();
            $db = $_swift->Database;
            self::$_conn = $db->GetPDOObject();
            self::$_conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        }

        return self::$_conn;
    }

    /**
     * @param PDO $conn
     */
    public static function SetConn($conn)
    {
        self::$_conn = $conn;
    }

    /**
     * @return array
     */
    private static function GetTableNames()
    {
        return [
            TABLE_PREFIX . 'tickets' => '',
            TABLE_PREFIX . 'ticketpostlocks' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'ticketlocks' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'ticketlinkchains' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'ticketfollowups' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'ticketdrafts' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'attachments' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'escalationpaths' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'ticketnotes' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.linktypeid = B.ticketid',
            TABLE_PREFIX . 'ticketauditlogs' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'ticketlinkedtables' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'ticketmergelog' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'ticketmessageids' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'ticketposts' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'ticketrecipients' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'ticketwatchers' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'customfieldvalues' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.typeid = B.ticketid',
            TABLE_PREFIX . 'tickettimetracks' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'ticketrecurrences' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.ticketid = B.ticketid',
            TABLE_PREFIX . 'searchindex' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.objid = B.ticketid AND A.type = 1',
            TABLE_PREFIX . 'taglinks' => 'A JOIN ' . TABLE_PREFIX
                . 'tickets B ON A.linkid = B.ticketid AND A.linktype = 1',
            TABLE_PREFIX . 'tickettimetracknotes' => 'A JOIN (' . TABLE_PREFIX
                . 'tickettimetracks C, ' . TABLE_PREFIX
                . 'tickets B) ON A.tickettimetrackid = C.tickettimetrackid AND C.ticketid = B.ticketid',
        ];
    }

    /**
     * @param SWIFT_TemplateEngine $template
     * @param mixed $where
     * @return string
     * @throws SWIFT_Exception
     */
    private static function DumpTables($template, $where)
    {
        $content = '';
        foreach (self::GetTableNames() as $table => $join) {
            $query = ($table === TABLE_PREFIX . 'tickets') ?
                'SELECT SQL_CALC_FOUND_ROWS B.* FROM ' . $table . ' B WHERE ' . $where :
                'SELECT SQL_CALC_FOUND_ROWS A.* FROM ' . $table . ' ' . $join . ' WHERE ' . $where;
            $result = self::GetConn()->query($query, PDO::FETCH_NUM);
            $row_count = (int)self::GetConn()->query('SELECT FOUND_ROWS()')->fetchColumn();
            $field_count = $result->columnCount();

            // hidrate column meta
            $meta = [];
            foreach (range(0, $field_count - 1) as $column_index) {
                $meta[] = $result->getColumnMeta($column_index);
            }

            if ($row_count > 0) {
                $template->Assign('table', (string) $table);
                $content .= $template->Get('my_table_header');
                $content .= self::DumpTable($field_count, $result, $table, $meta, $row_count);
                $content .= $template->Get('my_table_footer');
            }
        }
        return $content;
    }

    /**
     * @param mixed $content
     */
    private static function DownloadFile($content)
    {
        // Prepare file name
        $backup_name = DB_NAME . '_' . TABLE_PREFIX . 'tickets.sql.zip';
        $content_file = substr($backup_name, 0, -4);
        $file = tempnam(sys_get_temp_dir(), 'zip');

        if ($file === false) {
            return;
        }

        try {
            // create zip
            $zip = new \splitbrain\PHPArchive\Zip();
            $data = new \splitbrain\PHPArchive\FileInfo($content_file);
            $zip->create($file);
            $zip->addData($data, $content);
            $zip->close();

            // @codeCoverageIgnoreStart
            if (SWIFT_INTERFACE !== 'tests') {
                header('Content-Type: application/force-download');
                header('Content-Length: ' . filesize($file));
                header('Content-Disposition: attachment; filename="' . $backup_name . '"');
                // Download as zip
                readfile($file);
                unlink($file);
            }
            // @codeCoverageIgnoreEnd
        } catch (\Exception $e) {
            // @codeCoverageIgnoreStart
            if (SWIFT_INTERFACE !== 'tests') {
                header('Content-Type: application/octet-stream');
                header('Content-Transfer-Encoding: Binary');
                header('Content-disposition: attachment; filename="' . $backup_name . '"');
                // Download as text
                echo $content;
            }

            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @param mixed $field_count
     * @param PDOStatement $result
     * @param mixed $table
     * @param array $meta
     * @param mixed $row_count
     * @return string
     */
    private static function DumpTable($field_count, PDOStatement $result, $table, array $meta, $row_count)
    {
        $content = '';
        for ($i = 0, $st_counter = 0; $i < $field_count; $i++, $st_counter = 0) {
            while ($row = $result->fetch()) {
                //when started (and every after 100 command cycle):
                if ($st_counter % 100 === 0 || $st_counter === 0) {
                    $content .= "\nINSERT INTO " . $table . ' VALUES';
                }
                $content .= "\n(" .
                    self::DumpRow($row, $meta, $field_count) .
                    ')';
                //every after 100 command cycle [or at last line] ....
                //p.s. but should be inserted 1 cycle eariler
                if ((($st_counter + 1) % 100 === 0 && $st_counter !== 0)
                    || $st_counter + 1 === $row_count) {
                    $content .= ';';
                } else {
                    $content .= ',';
                }
                ++$st_counter;
            }
        }
        return $content;
    }

    /**
     * @param array $row
     * @param array $meta
     * @param mixed $field_count
     * @return string
     */
    private static function DumpRow(array $row, array $meta, $field_count)
    {
        $content = '';
        foreach ($row as $j => $jValue) {
            if (isset($row[$j])) {
                switch ($meta[$j]['native_type']) {
                    case 'SHORT':
                    case 'LONG':
                        $content .= $jValue;
                        break;
                    case 'VAR_STRING':
                    default:
                        $jValue = str_replace("\n", "\\n", addslashes($jValue));
                        $content .= "'" . $jValue . "'";
                        break;
                }
            } else {
                $content .= 'NULL';
            }
            if ($j < ($field_count - 1)) {
                $content .= ',';
            }
        }
        return $content;
    }

    /**
     * @param mixed $email
     * @param mixed $start_date
     * @param mixed $end_date
     * @param bool $is_trash
     * @param string $itemid_list
     * @return string
     */
    private static function GetWhere($email, $start_date, $end_date, $is_trash = false, $itemid_list = '')
    {
        $_swift = SWIFT::GetInstance();
        $db = $_swift->Database;

        if ($is_trash) {
            $result = 'B.departmentid = 0';
        } else {
            $email_clean = $db->Escape($email ?: '-');
            $result = "('" . $email_clean
                . "' = '-' OR B.email = '" . $email_clean
                . "') AND date(from_unixtime(B.dateline)) BETWEEN '"
                . $db->Escape($start_date) . " 00:00:00' AND '"
                . $db->Escape($end_date) . " 23:59:59' AND B.departmentid > 0";
        }

        if (!empty($itemid_list)) {
            $result .= ' AND B.ticketid in (' . $itemid_list . ')';
        }

        return $result;
    }

    /**
     * @param bool $php_format
     * @return string
     */
    public static function GetSwiftDateFormat($php_format = true)
    {
        $_SWIFT = SWIFT::GetInstance();
        $_dmy = $_SWIFT->Settings->Get('dt_caltype') === 'eu';
        $_format = $_dmy ? 'd/m/Y' : 'm/d/Y';
        $_sformat = $_dmy ? 'dd/mm/yyyy' : 'mm/dd/yyyy';

        return $php_format ? $_format : $_sformat;
    }

    /**
     * @param mixed $date_str
     * @return bool|string
     */
    private static function GetDBDate($date_str)
    {
        $_format = self::GetSwiftDateFormat();
        // validate date
        $d = DateTime::createFromFormat($_format, $date_str);
        $is_valid = $d && $d->format($_format) === $date_str;
        if (!$is_valid) {
            return false;
        }

        return strftime('%Y-%m-%d', GetCalendarDateline($date_str));
    }

    /**
     * @param mixed $where
     * @return float|int
     * @throws SWIFT_Exception
     */
    private static function ProcessDelete($where)
    {
        self::GetConn()->exec("SET NAMES 'utf8'");

        // delete in reverse order
        $_totalRows = 0;
        foreach (array_reverse(static::GetTableNames()) as $table => $join) {
            // delete  tickets
            $query = ($table === TABLE_PREFIX . 'tickets') ?
                'DELETE B FROM ' . $table . ' B WHERE ' . $where :
                'DELETE A FROM ' . $table . ' ' . $join . ' WHERE ' . $where;
            try {
                if ($table === TABLE_PREFIX . 'taglinks') {
                    // update tag cloud count before deleting tickets
                    $statement = 'UPDATE ' . TABLE_PREFIX . 'tags C '
                        . 'SET linkcount = linkcount - (SELECT COUNT(*) '
                        . 'FROM ' . TABLE_PREFIX . 'taglinks A '
                        . 'JOIN ' . TABLE_PREFIX . 'tickets B ON A . linkid = B . ticketid '
                        . "WHERE A.linktype = 1 AND C.tagid = A.tagid AND $where)";
                    self::GetConn()->exec($statement);
                }
                $_totalRows += self::GetConn()->exec($query);
            } catch (\Exception $e) {
                // ignore non existent tables
            }
        }

        return $_totalRows;
    }

}
