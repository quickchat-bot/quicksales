<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * ANSI SQL dialect definition file
 *
 * PHP versions 5
 *
 * LICENSE: This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Database
 * @package   SQL_Parser
 * @author    Brent Cook <busterbcook@yahoo.com>
 * @copyright 2002-2004 Brent Cook
 * @license   http://www.gnu.org/licenses/lgpl.html GNU Lesser GPL 3
 * @version   CVS: $Id: ANSI.php 263413 2008-07-24 14:49:31Z cybot $
 * @link      http://pear.php.net/package/SQL_Parser
 * @since     File available since Release 0.1.0
 */

/**
 * define tokens accepted by the SQL dialect.
 */
$dialect = array(
    'commands' => array(
        'alter',
        'create',
        'drop',
        'select',
        'delete',
        'insert',
        'update',
    ),

    'operators' => array(
        '+',
        '-',
        '*',
        '/',
        '^',
        '=',
        '!=',
        '<>',
        '<',
        '<=',
        '>',
        '>=',
        'like',
        'clike',
        'slike',
        'not',
        'is',
        'in',
        'between',
        'and',
        'or',
    ),

    'types' => array(
        'character',
        'char',
        'varchar',
        'nchar',
        'bit',
        'numeric',
        'decimal',
        'dec',
        'integer',
        'int',
        'smallint',
        'float',
        'real',
        'double',
        'date',
        'time',
        'timestamp',
        'interval',
        'bool',
        'boolean',
        'set',
        'enum',
        'text',
    ),

    'conjunctions' => array(
        'by',
        'as',
        'on',
        'into',
        'from',
        'where',
        'with',
    ),

    'functions' => array(
        'avg',
        'count',
        'max',
        'min',
        'sum',
        'nextval',
        'currval',
    ),

    'reserved' => array(
        'absolute',
        'action',
        'add',
        'all',
        'allocate',
        'and',
        'any',
        'are',
        'asc',
        'ascending',
        'assertion',
        'at',
        'authorization',
        'auto_increment',
        'begin',
        'bit_length',
        'both',
        'cascade',
        'cascaded',
        'case',
        'cast',
        'catalog',
        'char_length',
        'character_length',
        'check',
        'close',
        'coalesce',
        'collate',
        'collation',
        'column',
        'commit',
        'connect',
        'connection',
        'constraint',
        'constraints',
        'continue',
        'convert',
        'corresponding',
        'cross',
        'current',
        'current_date',
        'current_time',
        'current_timestamp',
        'current_user',
        'cursor',
        'day',
        'deallocate',
        'declare',
        'default',
        'deferrable',
        'deferred',
        'desc',
        'descending',
        'describe',
        'descriptor',
        'diagnostics',
        'disconnect',
        'distinct',
        'domain',
        'else',
        'end',
        'end-exec',
        'escape',
        'except',
        'exception',
        'exec',
        'execute',
        'exists',
        'external',
        'extract',
        'false',
        'fetch',
        'first',
        'for',
        'foreign',
        'found',
        'full',
        'get',
        'global',
        'go',
        'goto',
        'grant',
        'group',
        'having',
        'hour',
        'identity',
        'immediate',
        'indicator',
        'initially',
        'inner',
        'input',
        'insensitive',
        'intersect',
        'isolation',
        'join',
        'key',
        'language',
        'last',
        'leading',
        'left',
        'level',
        'limit',
        'local',
        'lower',
        'match',
        'minute',
        'module',
        'month',
        'names',
        'national',
        'natural',
        'next',
        'no',
        'null',
        'nullif',
        'octet_length',
        'of',
        'only',
        'open',
        'option',
        'or',
        'order',
        'outer',
        'output',
        'overlaps',
        'pad',
        'partial',
        'position',
        'precision',
        'prepare',
        'preserve',
        'primary',
        'prior',
        'privileges',
        'procedure',
        'public',
        'read',
        'references',
        'relative',
        'restrict',
        'revoke',
        'right',
        'rollback',
        'rows',
        'schema',
        'scroll',
        'second',
        'section',
        'session',
        'session_user',
        'size',
        'some',
        'space',
        'sql',
        'sqlcode',
        'sqlerror',
        'sqlstate',
        'substring',
        'system_user',
        'table',
        'temporary',
        'then',
        'timezone_hour',
        'timezone_minute',
        'to',
        'trailing',
        'transaction',
        'translate',
        'translation',
        'trim',
        'true',
        'union',
        'unique',
        'unknown',
        'upper',
        'usage',
        'user',
        'using',
        'value',
        'values',
        'varying',
        'view',
        'when',
        'whenever',
        'work',
        'write',
        'year',
        'zone',
        'eoc',
    ),

    'synonyms' => array(
        'decimal' => 'numeric',
        'dec' => 'numeric',
        'numeric' => 'numeric',
        'float' => 'float',
        'real' => 'real',
        'double' => 'real',
        'int' => 'int',
        'integer' => 'int',
        'interval' => 'interval',
        'smallint' => 'smallint',
        'timestamp' => 'timestamp',
        'bool' => 'bool',
        'boolean' => 'bool',
        'set' => 'set',
        'enum' => 'enum',
        'text' => 'text',
        'char' => 'char',
        'character' => 'char',
        'varchar' => 'varchar',
        'ascending' => 'asc',
        'asc' => 'asc',
        'descending' => 'desc',
        'desc' => 'desc',
        'date' => 'date',
        'time' => 'time',
    ),

    'lexeropts' => array(
        'allowIdentFirstDigit' => false,
    ),

    'parseropts' => array(
    ),

    'comments' => array(
        '--' => "\n",
    ),

    'quotes' => array(
        "'" => 'string',
        '"' => 'ident',
    ),
);
