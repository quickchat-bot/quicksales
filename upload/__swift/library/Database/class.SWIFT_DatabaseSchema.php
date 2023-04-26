<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Varun Shoor
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

/**
 * Database Schema Generation Class
 *
 * @author Varun Shoor
 */
class SWIFT_DatabaseSchema extends SWIFT_Model
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Destructor
     *
     * @author Varun Shoor
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Generate the XML Schema
     *
     * @author Varun Shoor
     * @param array $_tableList The Table List
     * @param bool $_generateData Whether to Generate Data in XML
     * @return mixed "schema" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Generate($_tableList, $_generateData = false)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        $old_mode = $this->Database->GetADODBObject()->SetFetchMode( ADODB_FETCH_NUM );

        $schema = '<?xml version="1.0"?>' . "\n"
                . '<schema version="' . XMLS_SCHEMA_VERSION . '">' . "\n";

        if(_is_array( $tables = $_tableList ) ) {
            foreach( $tables as $table ) {
                $schema .= '    <table name="' . $table . '">' . "\n";

                // grab details from database
                $rs = $this->Database->GetADODBObject()->Execute( 'SELECT * FROM ' . $table . ' WHERE 1=1' );
                $fields = $this->Database->GetADODBObject()->MetaColumns( $table );
                $indexes = $this->Database->GetADODBObject()->MetaIndexes( $table );

                if( is_array( $fields ) ) {
                    foreach( $fields as $details ) {
                        $extra = '';
                        $content = array();

                        if( $details->max_length > 0 ) {
                            $extra .= ' size="' . $details->max_length . '"';
                        }

                        if( $details->primary_key ) {
                            $content[] = '<KEY/>';
                        } elseif( $details->not_null ) {
                            $content[] = '<NOTNULL/>';
                        }

                        if( isset($details->has_default) && $details->has_default != '' ) {
                            $_default_value = isset($details->default_value) ? $details->default_value : '';
                            $content[] = '<DEFAULT value="' . $_default_value . '"/>';
                        }

                        if( $details->auto_increment ) {
                            $content[] = '<AUTOINCREMENT/>';
                        }

                        // this stops the creation of 'R' columns,
                        // AUTOINCREMENT is used to create auto columns
                        $details->primary_key = 0;
                        $type = $rs->MetaType( $details );

                        $schema .= '        <field name="' . $details->name . '" type="' . $type . '"' . $extra . '>';

                        if( !empty( $content ) ) {
                            $schema .= "\n            " . implode( "\n            ", $content ) . "\n        ";
                        }

                        $schema .= '</field>' . "\n";
                    }
                }

                if( is_array( $indexes ) ) {
                    foreach( $indexes as $index => $details ) {
                        $schema .= '        <index name="' . $index . '">' . "\n";

                        if( $details['unique'] ) {
                            $schema .= '            <UNIQUE/>' . "\n";
                        }

                        foreach( $details['columns'] as $column ) {
                            $schema .= '            <col>' . $column . '</col>' . "\n";
                        }

                        $schema .= '        </index>' . "\n";
                    }
                }

                if( $_generateData ) {
                    $rs = $this->Database->GetADODBObject()->Execute( 'SELECT * FROM ' . $table );

                    if( $rs instanceof ADORecordSet ) {
                        $schema .= '        <data>' . "\n";

                        while( $row = $rs->FetchRow() ) {
                            foreach( $row as $key => $val ) {
                                $row[$key] = htmlentities($val);
                            }

                            $schema .= '            <row><f>' . implode( '</f><f>', $row ) . '</f></row>' . "\n";
                        }

                        $schema .= '        </data>' . "\n";
                    }
                }

                $schema .= '    </table>' . "\n";
            }
        }

        $this->Database->GetADODBObject()->SetFetchMode( $old_mode );

        $schema .= '</schema>';

        return $schema;
    }
}
?>