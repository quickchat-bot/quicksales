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
 * @copyright      Copyright (c) 2001-2014, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'dbexport'            => 'Экспортировать XML',
    'desc_dbexport'       => '',
    'tabexportxml'        => 'Экспортировать XML',
    'export'              => 'Экспорт',
    'dbtables'            => 'Таблицы базы данных',
    'exportdata'          => 'Экспортировать данные',
    'desc_exportdata'     => 'Если этот параметр включен, Каяко будет экспортировать базы данных, а также схемы.',
    'dbdiagnostics'       => 'Диагностика',
    'desc_dbdiagnostics'  => '',
    'tabdiagnostics'      => 'Диагностика',
    'fieldname'           => 'Название поля',
    'maxlength'           => 'Максимальная длина',
    'fieldtype'           => 'Тип',
    'fieldprimary'        => 'Первичный ключ',
    'fieldautoincrement'  => 'Автоинкремент',

    // Potentialy unused phrases in database.php
    'backup'              => 'Backup',
    'database'            => 'Database',
    'desc_backup'         => 'Back up the database by generating and downloading a MySQL dump file. If your database is large, it is recommended that you use the <b>mysqldump</b> utility (via the MySQL command line interface). You can restore the dump file at a later time here using the MySQL CLI or third party database management applications such as phpMyAdmin.',
    'backupoptions'       => 'Backup Options',
    'genbackup'           => 'Generate Backup',
    'title_createtable'   => 'Add "CREATE TABLE" SQL statements',
    'desc_createtable'    => 'Enabling this option will add a "CREATE TABLE tablename;" SQL statement before the insert statements (enable this if you plan to restore the dump file to an empty database).',
    'title_compressdump'  => 'Compress SQL Dump',
    'desc_compressdump'   => 'Compresses the dump file using <b>gzip</b> compression.',
    'repairoptimize'      => 'Repair/Optimize',
    'desc_repairoptimize' => 'Optimizing a database results in the rewriting of indices and is a resource intensive process for large tables.',
    'repair'              => 'Repair',
    'optimize'            => 'Optimize',
    'repairall'           => 'Repair All',
    'optimizeall'         => 'Optimize All',
    'tablelist'           => 'Table List',
    'tablename'           => 'Table Name',
    'tabletype'           => 'Table Type',
    'rows'                => 'Rows',
    'indexsize'           => 'Index Size',
    'optimizedtable'      => 'Optimized table %s. MySQL status message: %s',
    'repairedtable'       => 'Repaired table %s. MySQL status message: %s',
    'optimizedalltables'  => 'Optimized %s tables',
    'repairedalltables'   => 'Repaired %s tables',
);


return $__LANG;
