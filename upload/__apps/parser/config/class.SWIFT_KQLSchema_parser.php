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
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser;

use Base\Library\KQL\SWIFT_KQLSchema;
use SWIFT_Exception;
use SWIFT_Loader;

/**
 * The Parser KQL Schema Class
 *
 * @author Varun Shoor
 */
class SWIFT_KQLSchema_parser extends SWIFT_KQLSchema
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();

        SWIFT_Loader::LoadModel('EmailQueue:EmailQueue', APP_PARSER, false);

        $this->LoadKQLLabels('kql_parser', APP_PARSER);
    }

    /**
     * Retrieve the Parser Schema
     *
     * @author Varun Shoor
     * @return array The Schema Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSchema()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_schemaContainer = array();

        /**
         * ---------------------------------------------
         * EmailQueues
         * ---------------------------------------------
         */
        $_schemaContainer['emailqueues'] = array(
            self::SCHEMA_ISVISIBLE => false,
            self::SCHEMA_PRIMARYKEY => 'emailqueueid',
            self::SCHEMA_TABLELABEL => 'emailqueues',

            self::SCHEMA_FIELDS => array(
                'emailqueueid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                ),

                'email' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 250,
                    self::FIELD_ALIGN => 'center',
                ),

            ),
        );


        return $_schemaContainer;
    }

}

?>
