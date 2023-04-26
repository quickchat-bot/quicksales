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
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base;

use SWIFT_Exception;
use Base\Library\KQL2\SWIFT_KQL2;
use Base\Library\KQL\SWIFT_KQLSchema;
use Base\Models\Rating\SWIFT_Rating;
use Base\Models\Tag\SWIFT_TagLink;
use Base\Models\User\SWIFT_User;
use Base\Models\User\SWIFT_UserEmail;
use Base\Models\User\SWIFT_UserGroup;
use Base\Models\User\SWIFT_UserOrganization;

/**
 * The Base KQL Schema Class
 *
 * @author Varun Shoor
 */
class SWIFT_KQLSchema_base extends SWIFT_KQLSchema
{
    /**
     * Retrieve the Tickets Schema
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
         * Department
         * ---------------------------------------------
         */
        $_schemaContainer['departments'] = array(
            self::SCHEMA_ISVISIBLE => false,
            self::SCHEMA_PRIMARYKEY => 'departmentid',
            self::SCHEMA_TABLELABEL => 'departments',

            self::SCHEMA_FIELDS => array(
                'departmentid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 20,
                ),

                'title' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 100,
                ),

                'departmentapp' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(APP_TICKETS => 'custom_tickets', APP_LIVECHAT => 'custom_livechat'),
                    self::FIELD_WIDTH => 100,
                ),

                'departmenttype' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array('public' => 'custom_public', 'private' => 'custom_private'),
                    self::FIELD_WIDTH => 60,
                ),

                'displayorder' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 20,
                ),

                'parentdepartmentid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('departments.departmentid', 'departments.title', "departments.parentdepartmentid = '0'"),
                ),
            ),
        );

        /**
         * ---------------------------------------------
         * Staff
         * ---------------------------------------------
         */
        $_schemaContainer['staff'] = array(
            self::SCHEMA_ISVISIBLE => false,
            self::SCHEMA_PRIMARYKEY => 'staffid',
            self::SCHEMA_TABLELABEL => 'staff',

            self::SCHEMA_RELATEDTABLES => array('staffgroup' => 'staff.staffgroupid = staffgroup.staffgroupid'),

            self::SCHEMA_FIELDS => array(
                'staffid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 20,
                ),

                'firstname' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 60,
                ),

                'lastname' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 60,
                ),

                'fullname' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 120,
                ),

                'username' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 120,
                ),

                'designation' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 120,
                ),

                'email' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 160,
                ),

                'lastvisit' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 120,
                ),

                'isenabled' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 60,
                ),

                'staffgroupid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('staffgroup.staffgroupid', 'staffgroup.title'),
                ),
            ),
        );

        /**
         * ---------------------------------------------
         * StaffGroup
         * ---------------------------------------------
         */
        $_schemaContainer['staffgroup'] = array(
            self::SCHEMA_ISVISIBLE => false,
            self::SCHEMA_PRIMARYKEY => 'staffgroupid',
            self::SCHEMA_TABLELABEL => 'teams',

            self::SCHEMA_FIELDS => array(
                'staffgroupid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 20,
                ),

                'title' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 100,
                ),

                'isadmin' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 60,
                ),
            ),
        );

        /**
         * ---------------------------------------------
         * UserGroup
         * ---------------------------------------------
         */
        $_schemaContainer['usergroups'] = array(
            self::SCHEMA_ISVISIBLE => false,
            self::SCHEMA_PRIMARYKEY => 'usergroupid',
            self::SCHEMA_TABLELABEL => 'usergroups',

            self::SCHEMA_FIELDS => array(
                'usergroupid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 20,
                ),

                'title' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 100,
                ),

                'grouptype' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_UserGroup::TYPE_GUEST => 'custom_guest', SWIFT_UserGroup::TYPE_REGISTERED => 'custom_registered'),
                    self::FIELD_WIDTH => 60,
                ),
            ),
        );

        /**
         * ---------------------------------------------
         * UserOrganization
         * ---------------------------------------------
         */
        $_schemaContainer['userorganizations'] = array(
            self::SCHEMA_ISVISIBLE => true,
            self::SCHEMA_PRIMARYKEY => 'userorganizationid',
            self::SCHEMA_TABLELABEL => 'userorganizations',

            self::SCHEMA_RELATEDTABLES => array('usergroups' => 'userorganizations.usergroupid = usergroups.usergroupid',
                'taglinks' => 'userorganizations.userorganizationid = taglinks.linkid AND taglinks.linktype = \'' . SWIFT_TagLink::TYPE_USERORGANIZATION . '\'',
                'slaplans' => 'userorganizations.slaplanid = slaplans.slaplanid'),

            self::SCHEMA_FIELDS => array(
                'userorganizationid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 20,
                ),

                'organizationname' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 120,
                ),

                'organizationtype' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_UserOrganization::TYPE_SHARED => 'custom_shared', SWIFT_UserOrganization::TYPE_RESTRICTED => 'custom_restricted'),
                    self::FIELD_WIDTH => 60,
                ),

                'address' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 160,
                ),

                'city' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 120,
                ),

                'state' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 120,
                ),

                'postalcode' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 60,
                ),

                'country' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 140,
                ),

                'phone' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 80,
                ),

                'fax' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 80,
                ),

                'website' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 150,
                ),

                'dateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 120,
                ),

                'lastupdate' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 120,
                ),

                'slaplanid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('slaplans.slaplanid', 'slaplans.title'),
                ),

                'slaexpirytimeline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 120,
                ),

                'usergroupid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('usergroups.usergroupid', 'usergroups.title'),
                ),
            ),
        );

        /**
         * ---------------------------------------------
         * User
         * ---------------------------------------------
         */
        $_schemaContainer['users'] = array(
            self::SCHEMA_ISVISIBLE => true,
            self::SCHEMA_PRIMARYKEY => 'userid',
            self::SCHEMA_AUTOJOIN => array('userorganizations'),
            self::SCHEMA_TABLELABEL => 'users',

            self::SCHEMA_RELATEDTABLES => array('usergroups' => 'users.usergroupid = usergroups.usergroupid',
                'userorganizations' => 'users.userorganizationid = userorganizations.userorganizationid',
                'taglinks' => 'users.userid = taglinks.linkid AND taglinks.linktype = \'' . SWIFT_TagLink::TYPE_USER . '\'',
                'slaplans' => 'users.slaplanid = slaplans.slaplanid'),

            self::SCHEMA_FIELDS => array(
                'userid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 20,
                ),

                'usergroupid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('usergroups.usergroupid', 'usergroups.title'),
                ),

                'userorganizationid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('userorganizations.userorganizationid', 'userorganizations.organizationname'),
                ),

                'userrole' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_User::ROLE_USER => 'custom_user', SWIFT_User::ROLE_MANAGER => 'custom_manager'),
                    self::FIELD_WIDTH => 60,
                ),

                'salutation' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_User::SALUTATION_MR => 'custom_mr', SWIFT_User::SALUTATION_MISS => 'custom_ms', SWIFT_User::SALUTATION_MRS => 'custom_mrs',
                        SWIFT_User::SALUTATION_DR => 'custom_dr', SWIFT_User::SALUTATION_NONE => 'custom_nosalutation'),
                    self::FIELD_WIDTH => 40,
                ),

                'fullname' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 150,
                ),

                'userdesignation' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 120,
                ),

                'phone' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 150,
                ),

                'dateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 120,
                ),

                'lastupdate' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 120,
                ),

                'lastvisit' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 120,
                ),

                'slaplanid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('slaplans.slaplanid', 'slaplans.title'),
                ),

                'slaexpirytimeline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 120,
                ),

                'userexpirytimeline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 120,
                ),

                'isvalidated' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 60,
                ),
            ),
        );

        /**
         * ---------------------------------------------
         * UserEmail
         * ---------------------------------------------
         */
        $_schemaContainer['useremails'] = array(
            self::SCHEMA_ISVISIBLE => true,
            self::SCHEMA_PRIMARYKEY => 'useremailid',
            self::SCHEMA_TABLELABEL => 'useremails',

            self::SCHEMA_RELATEDTABLES => array('users' => 'useremails.linktypeid = users.userid AND useremails.linktype = \'' . SWIFT_UserEmail::LINKTYPE_USER . '\''),

            self::SCHEMA_FIELDS => array(
                'useremailid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 20,
                ),

                'linktypeid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('users.userid', 'users.fullname', 'useremails.linktype = \'' . SWIFT_UserEmail::LINKTYPE_USER . '\''),
                ),

                'email' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 150,
                ),
            ),
        );

        /**
         * ---------------------------------------------
         * Ratings
         * ---------------------------------------------
         */
        $_schemaContainer['ratings'] = array(
            self::SCHEMA_ISVISIBLE => false,
            self::SCHEMA_PRIMARYKEY => 'ratingid',
            self::SCHEMA_TABLELABEL => 'ratings',

            self::SCHEMA_FIELDS => array(
                'ratingid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 20,
                ),

                'ratingtitle' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 200,
                ),

                'ratingtype' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_Rating::TYPE_TICKET => 'custom_benchtickets', SWIFT_Rating::TYPE_TICKETPOST => 'custom_benchticketposts', SWIFT_Rating::TYPE_CHATSURVEY => 'custom_benchchatsurvey', SWIFT_Rating::TYPE_CHATHISTORY => 'custom_benchchathistory'),
                    self::FIELD_WIDTH => 80,
                ),

                'ratingvisibility' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_PUBLIC => 'custom_public', SWIFT_PRIVATE => 'custom_private'),
                    self::FIELD_WIDTH => 80,
                ),
            ),
        );

        /**
         * ---------------------------------------------
         * RatingResults
         * ---------------------------------------------
         */
        $_schemaContainer['ratingresults'] = array(
            self::SCHEMA_ISVISIBLE => true,
            self::SCHEMA_PRIMARYKEY => 'ratingresultid',
            self::SCHEMA_TABLELABEL => 'ratingresults',

            self::SCHEMA_AUTOJOIN => array('ratings'),
            self::SCHEMA_RELATEDTABLES => array('ratings' => 'ratingresults.ratingid = ratings.ratingid',
                'tickets' => 'ratingresults.typeid = tickets.ticketid',
                'ticketposts' => 'ratingresults.typeid = ticketposts.ticketpostid',
                'chatobjects' => 'ratingresults.typeid = chatobjects.chatobjectid',
                'messages' => 'ratingresults.typeid = messages.chatobjectid'),

            self::SCHEMA_FIELDS => array(
                'ratingresultid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 20,
                ),

                'ratingid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('ratings.ratingid', 'ratings.ratingtitle'),
                ),

                'ratingresult' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                ),

                'dateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 120,
                ),
            ),
        );

        /**
         * ---------------------------------------------
         * Tags
         * ---------------------------------------------
         */
        $_schemaContainer['tags'] = array(
            self::SCHEMA_ISVISIBLE => false,
            self::SCHEMA_PRIMARYKEY => 'tagid',
            self::SCHEMA_TABLELABEL => 'tags',

            self::SCHEMA_FIELDS => array(
                'tagid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 20,
                ),

                'tagname' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 150,
                ),
            ),
        );

        /**
         * ---------------------------------------------
         * TagLinks
         * ---------------------------------------------
         */
        $_schemaContainer['taglinks'] = array(
            self::SCHEMA_ISVISIBLE => false,
            self::SCHEMA_PRIMARYKEY => 'taglinkid',
            self::SCHEMA_TABLELABEL => 'taglinks',

            self::SCHEMA_AUTOJOIN => array('tags'),
            self::SCHEMA_RELATEDTABLES => array('tags' => 'taglinks.tagid = tags.tagid'),

            self::SCHEMA_FIELDS => array(
                'taglinkid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 20,
                ),

                'tagid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('tags.tagid', 'tags.tagname'),
                ),
            ),
        );

        return $_schemaContainer;
    }

    /**
     * Retrieve basic KQL clauses
     *
     * @author Andriy Lesyuk
     * @return array The Clauses Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetClauses()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_clausesContainer = array(
            'SELECT' => array(
                self::CLAUSE_PRECOMPILER => 'Clause_PreCompileSelect',
                self::CLAUSE_COMPILER => 'Clause_CompileSelect'
            ),

            'FROM' => array(
                self::CLAUSE_PARSER => 'Clause_ParseFrom'
            ),

            'WHERE' => array(
                self::CLAUSE_PARSER => 'Clause_ParseWhere',
                self::CLAUSE_COMPILER => 'Clause_CompileWhere'
            ),

            'GROUP BY' => array(
                self::CLAUSE_COMPILER => 'Clause_CompileGroupBy'
            ),

            'MULTIGROUP BY' => array(
                self::CLAUSE_COMPILER => 'Clause_CompileMultiGroupBy'
            ),

            'ORDER BY' => array(
                self::CLAUSE_COMPILER => 'Clause_CompileOrderBy',
            ),

            'LIMIT' => array(
                self::CLAUSE_PARSER => 'Clause_ParseLimit',
                self::CLAUSE_COMPILER => 'Clause_CompileLimit'
            ),

            'TOTALIZE BY' => array(
                self::CLAUSE_MULTIPLE => true,
                self::CLAUSE_PARSER => 'Clause_ParseTotalizeBy',
                self::CLAUSE_COMPILER => 'Clause_CompileTotalizeBy',
            ),
        );

        return $_clausesContainer;
    }

    /**
     * Retrieve basic KQL operators
     *
     * @author Andriy Lesyuk
     * @return array The Operators Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetOperators()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_operatorsContainer = array(

            /**
             * Numerical operators
             */

            '+' => array(),

            '-' => array(),

            '*' => array(
                self::OPERATOR_LEFTTYPE => SWIFT_KQL2::DATA_NUMERIC,
                self::OPERATOR_RIGHTTYPE => SWIFT_KQL2::DATA_NUMERIC,
                self::OPERATOR_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            '/' => array(
                self::OPERATOR_LEFTTYPE => SWIFT_KQL2::DATA_NUMERIC,
                self::OPERATOR_RIGHTTYPE => SWIFT_KQL2::DATA_NUMERIC,
                self::OPERATOR_RETURNTYPE => SWIFT_KQL2::DATA_FLOAT
            ),

            '%' => array(
                self::OPERATOR_LEFTTYPE => SWIFT_KQL2::DATA_NUMERIC,
                self::OPERATOR_RIGHTTYPE => SWIFT_KQL2::DATA_NUMERIC,
                self::OPERATOR_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            /**
             * Comparison operators
             */

            '=' => array(
                self::OPERATOR_NEGATIVE => '!=',
                self::OPERATOR_LEFTTYPE => SWIFT_KQL2::DATA_SAME,
                self::OPERATOR_RIGHTTYPE => SWIFT_KQL2::DATA_SAME,
                self::OPERATOR_RETURNTYPE => SWIFT_KQL2::DATA_BOOLEAN,
                self::OPERATOR_COMPILER => 'Operator_CompileEqual'
            ),

            /**
             * Numerical comparison operators
             */

            /*
             * NOTE: E.g. < should come after <=, otherwise it will never get to <=
             */

            '<=' => array(
                self::OPERATOR_LEFTTYPE => SWIFT_KQL2::DATA_SAME,
                self::OPERATOR_RIGHTTYPE => SWIFT_KQL2::DATA_SAME,
                self::OPERATOR_RETURNTYPE => SWIFT_KQL2::DATA_BOOLEAN
            ),

            '<' => array(
                self::OPERATOR_LEFTTYPE => SWIFT_KQL2::DATA_SAME,
                self::OPERATOR_RIGHTTYPE => SWIFT_KQL2::DATA_SAME,
                self::OPERATOR_RETURNTYPE => SWIFT_KQL2::DATA_BOOLEAN
            ),

            '>=' => array(
                self::OPERATOR_LEFTTYPE => SWIFT_KQL2::DATA_SAME,
                self::OPERATOR_RIGHTTYPE => SWIFT_KQL2::DATA_SAME,
                self::OPERATOR_RETURNTYPE => SWIFT_KQL2::DATA_BOOLEAN
            ),

            '>' => array(
                self::OPERATOR_LEFTTYPE => SWIFT_KQL2::DATA_SAME,
                self::OPERATOR_RIGHTTYPE => SWIFT_KQL2::DATA_SAME,
                self::OPERATOR_RETURNTYPE => SWIFT_KQL2::DATA_BOOLEAN
            ),

            /**
             * String comparison operators
             */

            'LIKE' => array(
                self::OPERATOR_NEGATIVE => 'NOT LIKE',
                self::OPERATOR_LEFTTYPE => SWIFT_KQL2::DATA_STRING,
                self::OPERATOR_RIGHTTYPE => SWIFT_KQL2::DATA_STRING,
                self::OPERATOR_RETURNTYPE => SWIFT_KQL2::DATA_BOOLEAN
            ),

            /**
             * Other comparison operators
             */

            'IN' => array(
                self::OPERATOR_NEGATIVE => 'NOT IN',
                self::OPERATOR_LEFTTYPE => SWIFT_KQL2::DATA_SAME,
                self::OPERATOR_RIGHTTYPE => SWIFT_KQL2::DATA_ARRAY,
                self::OPERATOR_RETURNTYPE => SWIFT_KQL2::DATA_BOOLEAN,
                self::OPERATOR_COMPILER => 'Operator_CompileIn'
            ),

            /**
             * Boolean operators
             */

            'AND' => array(
                self::OPERATOR_LEFTTYPE => SWIFT_KQL2::DATA_BOOLEAN,
                self::OPERATOR_RIGHTTYPE => SWIFT_KQL2::DATA_BOOLEAN,
                self::OPERATOR_RETURNTYPE => SWIFT_KQL2::DATA_BOOLEAN
            ),

            'OR' => array(
                self::OPERATOR_LEFTTYPE => SWIFT_KQL2::DATA_BOOLEAN,
                self::OPERATOR_RIGHTTYPE => SWIFT_KQL2::DATA_BOOLEAN,
                self::OPERATOR_RETURNTYPE => SWIFT_KQL2::DATA_BOOLEAN
            ),
        );

        return $_operatorsContainer;
    }

    /**
     * Retrieve custom functions
     *
     * @author Andriy Lesyuk
     * @return array The Functions Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetFunctions()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_functionsContainer = array(

            /**
             * General functions
             */

            'COUNT' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_ANY),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'IF' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_BOOLEAN, SWIFT_KQL2::DATA_ANY, SWIFT_KQL2::DATA_ANY),
                self::FUNCTION_RETURNTYPE => array(2)
            ),

            'IFNULL' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_ANY, SWIFT_KQL2::DATA_ANY),
                self::FUNCTION_RETURNTYPE => array(2)
            ),

            'NULLIF' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_ANY, SWIFT_KQL2::DATA_ANY),
                self::FUNCTION_RETURNTYPE => array(1)
            ),

            /**
             * Mathematical functions
             */

            'SUM' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => array(1),
            ),

            'AVG' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => array(1),
            ),

            'MAX' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => array(1),
            ),

            'MIN' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => array(1),
            ),

            /**
             * MySQL mathematical functions
             */

            'ABS' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'ACOS' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'ASIN' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'ATAN2' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC, SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'ATAN' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC, SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'CEIL' => 'CEILING',

            'CEILING' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'CONV' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_STRING), SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'COS' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'COT' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'CRC32' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_ANY),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'DEGREES' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'EXP' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'FLOOR' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'FORMAT' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC, SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'HEX' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_STRING)),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'LN' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'LOG' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC, SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_OPTIONALARGUMENTS => 2,
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'LOG2' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'LOG10' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'MOD' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC, SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'OCT' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'PI' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_FLOAT
            ),

            'POW' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC, SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'POWER' => 'POW',

            'RADIANS' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'RAND' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_NUMERIC)),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_FLOAT
            ),

            'ROUND' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC, SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_OPTIONALARGUMENTS => 2,
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'SIGN' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'SIN' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'SQRT' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'TAN' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'TRUNCATE' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_NUMERIC, SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_OPTIONALARGUMENTS => 2,
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            /**
             * Date and time functions
             */

            'MKTIME' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_OPTIONALARGUMENTS => 1,
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE,
                self::FUNCTION_COMPILER => 'Function_CompileMakeTime'
            ),

            'DATENOW' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE,
                self::FUNCTION_COMPILER => 'Function_CompileDateNow'
            ),

            'YESTERDAY' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE,
                self::FUNCTION_PRECOMPILER => 'Function_PreCompileExtendedDateFunctions',
                self::FUNCTION_COMPILER => 'Function_CompileExtendedDateFunctions'
            ),

            'TODAY' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE,
                self::FUNCTION_PRECOMPILER => 'Function_PreCompileExtendedDateFunctions',
                self::FUNCTION_COMPILER => 'Function_CompileExtendedDateFunctions'
            ),

            'TOMORROW' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE,
                self::FUNCTION_PRECOMPILER => 'Function_PreCompileExtendedDateFunctions',
                self::FUNCTION_COMPILER => 'Function_CompileExtendedDateFunctions'
            ),

            'LAST7DAYS' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE,
                self::FUNCTION_PRECOMPILER => 'Function_PreCompileExtendedDateFunctions',
                self::FUNCTION_COMPILER => 'Function_CompileExtendedDateFunctions'
            ),

            'LAST30DAYS' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE,
                self::FUNCTION_PRECOMPILER => 'Function_PreCompileExtendedDateFunctions',
                self::FUNCTION_COMPILER => 'Function_CompileExtendedDateFunctions'
            ),

            'LASTWEEK' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE,
                self::FUNCTION_PRECOMPILER => 'Function_PreCompileExtendedDateFunctions',
                self::FUNCTION_COMPILER => 'Function_CompileExtendedDateFunctions'
            ),

            'THISWEEK' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE,
                self::FUNCTION_PRECOMPILER => 'Function_PreCompileExtendedDateFunctions',
                self::FUNCTION_COMPILER => 'Function_CompileExtendedDateFunctions'
            ),

            'NEXTWEEK' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE,
                self::FUNCTION_PRECOMPILER => 'Function_PreCompileExtendedDateFunctions',
                self::FUNCTION_COMPILER => 'Function_CompileExtendedDateFunctions'
            ),

            'LASTMONTH' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE,
                self::FUNCTION_PRECOMPILER => 'Function_PreCompileExtendedDateFunctions',
                self::FUNCTION_COMPILER => 'Function_CompileExtendedDateFunctions'
            ),

            'THISMONTH' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE,
                self::FUNCTION_PRECOMPILER => 'Function_PreCompileExtendedDateFunctions',
                self::FUNCTION_COMPILER => 'Function_CompileExtendedDateFunctions',
            ),

            'NEXTMONTH' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE,
                self::FUNCTION_PRECOMPILER => 'Function_PreCompileExtendedDateFunctions',
                self::FUNCTION_COMPILER => 'Function_CompileExtendedDateFunctions'
            ),

            'ENDOFWEEK' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE,
                self::FUNCTION_PRECOMPILER => 'Function_PreCompileExtendedDateFunctions',
                self::FUNCTION_COMPILER => 'Function_CompileExtendedDateFunctions'
            ),

            'MONTHRANGE' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE,
                self::FUNCTION_PARSER => 'Function_ParseMonthRange',
                self::FUNCTION_PRECOMPILER => 'Function_PreCompileExtendedDateFunctions',
                self::FUNCTION_COMPILER => 'Function_CompileExtendedDateFunctions'
            ),

            'MONTH' => array( // NOTE: Overrides MySQL's MONTH()
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_STRING)),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER,
                self::FUNCTION_PARSER => 'Function_ParseMonthRange',
                self::FUNCTION_PRECOMPILER => 'Function_PreCompileExtendedDateFunctions',
                self::FUNCTION_COMPILER => 'Function_CompileExtendedDateFunctions'
            ),

            /**
             * MySQL date and time functions
             */

            'FROM_UNIXTIME' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_UNIXDATE),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_DATETIME
            ),

            'LAST_DAY' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME)),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_DATE
            ),

            'DATEDIFF' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME), array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME)),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'ADDDATE' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME), array(SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_INTERVAL)),
                self::FUNCTION_RETURNTYPE => array(1)
            ),

            'ADDTIME' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_TIME, SWIFT_KQL2::DATA_DATETIME), SWIFT_KQL2::DATA_TIME),
                self::FUNCTION_RETURNTYPE => array(1)
            ),

            'CONVERT_TZ' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_DATETIME, SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_DATETIME
            ),

            'CURDATE' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_DATE
            ),

            'CURRENT_DATE' => 'CURDATE',

            'CURTIME' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_TIME
            ),

            'CURRENT_TIME' => 'CURTIME',

            'CURRENT_TIMESTAMP' => 'NOW',

            'DATE_ADD' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME), SWIFT_KQL2::DATA_INTERVAL),
                self::FUNCTION_RETURNTYPE => array(1)
            ),

            'DATE_FORMAT' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME), SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'DATE_SUB' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME), SWIFT_KQL2::DATA_INTERVAL),
                self::FUNCTION_RETURNTYPE => array(1)
            ),

            'DATE' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME)),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_DATE
            ),

            'DAY' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_DATE),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_NUMERIC
            ),

            'DAYNAME' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_DATE),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'DAYOFMONTH' => 'DAY',

            'DAYOFWEEK' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_DATE),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'DAYOFYEAR' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_DATE),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'FROM_DAYS' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_DATE
            ),

            'HOUR' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_TIME, SWIFT_KQL2::DATA_DATETIME)),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'LOCALTIME' => 'NOW',

            'LOCALTIMESTAMP' => 'NOW',

            'MAKEDATE' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_DATE
            ),

            'MAKETIME' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_TIME
            ),

            'MICROSECOND' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_TIME, SWIFT_KQL2::DATA_DATETIME)),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'MINUTE' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_TIME, SWIFT_KQL2::DATA_DATETIME)),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'MONTHNAME' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_DATE),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'NOW' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_DATETIME
            ),

            'PERIOD_ADD' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'PERIOD_DIFF' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'QUARTER' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_DATE),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'SEC_TO_TIME' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_TIME
            ),

            'SECOND' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_TIME),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'STR_TO_DATE' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_DATETIME // FIXME array(SWIFT_KQL2::DATA_DATETIME, SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_TIME)
            ),

            'SUBDATE' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME), array(SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_INTERVAL)),
                self::FUNCTION_RETURNTYPE => array(1)
            ),

            'SUBTIME' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_TIME, SWIFT_KQL2::DATA_DATETIME), SWIFT_KQL2::DATA_TIME),
                self::FUNCTION_RETURNTYPE => array(1)
            ),

            'SYSDATE' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_DATETIME
            ),

            'TIME_FORMAT' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_TIME, SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'TIME_TO_SEC' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_TIME),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'TIME' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_TIME, SWIFT_KQL2::DATA_DATETIME)),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_TIME
            ),

            'TIMEDIFF' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_TIME, SWIFT_KQL2::DATA_DATETIME), array(SWIFT_KQL2::DATA_TIME, SWIFT_KQL2::DATA_DATETIME)),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_TIME
            ),

            'TIMESTAMP' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME), SWIFT_KQL2::DATA_TIME),
                self::FUNCTION_OPTIONALARGUMENTS => 2,
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_DATETIME
            ),

            'TO_DAYS' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_DATE),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'UNIX_TIMESTAMP' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME)),
                self::FUNCTION_OPTIONALARGUMENTS => 1,
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_UNIXDATE
            ),

            'UTC_DATE' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_DATE
            ),

            'UTC_TIME' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_TIME
            ),

            'UTC_TIMESTAMP' => array(
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_DATETIME
            ),

            'WEEK' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME), SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_OPTIONALARGUMENTS => 2,
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'WEEKDAY' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME)),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'WEEKOFYEAR' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME)),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'YEAR' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME)),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),
            'YEARWEEK' => array(
                self::FUNCTION_ARGUMENTS => array(array(SWIFT_KQL2::DATA_DATE, SWIFT_KQL2::DATA_DATETIME), SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_OPTIONALARGUMENTS => 2,
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            /**
             * String functions
             */

            'ASCII' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'BIN' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'BIT_LENGTH' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'BITLENGTH' => 'BIT_LENGTH',

            'CHAR_LENGTH' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'CHARACTER_LENGTH' => 'CHAR_LENGTH',

            'CONCAT' => array(
//                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING), // FIXME: Specify types of arguments somehow...
                self::FUNCTION_PARSER => 'Function_ParseConcat',
                self::FUNCTION_OPTIONALARGUMENTS => 3,
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'CONCAT_WS' => array(
//                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING), // FIXME: Specify types of arguments somehow...
                self::FUNCTION_PARSER => 'Function_ParseConcat',
                self::FUNCTION_OPTIONALARGUMENTS => 4,
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'INSERT' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'INSTR' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'LOWER' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'LCASE' => 'LOWER',

            'LEFT' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'LENGTH' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'OCTET_LENGTH' => 'LENGTH',

            'LOCATE' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_OPTIONALARGUMENTS => 3,
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'POSITION' => 'LOCATE',

            'LPAD' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'LTRIM' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'ORD' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'REPEAT' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'REPLACE' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'REVERSE' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'RIGHT' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'RPAD' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'RTRIM' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'SOUNDEX' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'SPACE' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'STRCMP' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_INTEGER
            ),

            'SUBSTRING' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_INTEGER, SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_OPTIONALARGUMENTS => 3,
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'SUBSTR' => 'SUBSTRING',

            'MID' => 'SUBSTRING',

            'SUBSTRING_INDEX' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_STRING, SWIFT_KQL2::DATA_INTEGER),
                self::FUNCTION_OPTIONALARGUMENTS => 3,
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'TRIM' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'UPPER' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            'UCASE' => 'UPPER',

            'UNHEX' => array(
                self::FUNCTION_ARGUMENTS => array(SWIFT_KQL2::DATA_STRING),
                self::FUNCTION_RETURNTYPE => SWIFT_KQL2::DATA_STRING
            ),

            /**
             * Custom functions
             */

            'CUSTOMFIELD' => array(
                self::FUNCTION_PARSER => 'Function_ParseCustomField',
                self::FUNCTION_POSTPARSER => 'Function_PostParseCustomField'
            ),

            'X' => array(
                self::FUNCTION_POSTPARSER => 'Function_PostParseXY'
            ),

            'Y' => array(
                self::FUNCTION_POSTPARSER => 'Function_PostParseXY'
            ),
        );

        return $_functionsContainer;
    }

    /**
     * Retrieve custom selectors
     *
     * @author Andriy Lesyuk
     * @return array The Selectors Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetSelectors()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_selectorsContainer = array(
            'MINUTE' => array(),

            'HOUR' => array(),

            'DAY' => array(),

            'DAYNAME' => array(),

            'WEEK' => array(),

            'WEEKDAY' => array(),

            'MONTH' => array(),

            'MONTHNAME' => array(),

            'QUARTER' => array(),

            'YEAR' => array(),
        );

        return $_selectorsContainer;
    }

    /**
     * Retrieve custom pre-modifiers
     *
     * @author Andriy Lesyuk
     * @return array The Pre-Modifiers Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPreModifiers()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_preModifiersContainer = array(
            'DISTINCT' => array(
                self::PREMODIFIER_PARSER => 'Modifier_ParseDistinct',
                self::PREMODIFIER_COMPILER => 'Modifier_CompileDistinct'
            ),

            'INTERVAL' => array(
                self::PREMODIFIER_PARSER => 'Modifier_ParseInterval',
                self::PREMODIFIER_COMPILER => 'Modifier_CompileInterval'
            ),
        );

        return $_preModifiersContainer;
    }

    /**
     * Retrieve custom post-modifiers
     *
     * @author Andriy Lesyuk
     * @return array The Post-Modifiers Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetPostModifiers()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_postModifiersContainer = array(
            'AS' => array(
                self::POSTMODIFIER_CLAUSE => 'SELECT',
                self::POSTMODIFIER_PARSER => 'Modifier_ParseSelectModifiers'
            ),

            'FORMAT' => array( # TODO
                self::POSTMODIFIER_CLAUSE => 'SELECT',
            ),

            'X' => array(
                self::POSTMODIFIER_CLAUSE => 'GROUP BY',
                self::POSTMODIFIER_PARSER => 'Modifier_ParseGroupModifiers',
                self::POSTMODIFIER_COMPILER => 'Modifier_CompileXY'
            ),

            'Y' => array(
                self::POSTMODIFIER_CLAUSE => 'GROUP BY',
                self::POSTMODIFIER_PARSER => 'Modifier_ParseGroupModifiers',
                self::POSTMODIFIER_COMPILER => 'Modifier_CompileXY'
            ),

            'ASC' => array(
                self::POSTMODIFIER_CLAUSE => 'ORDER BY',
                self::POSTMODIFIER_PARSER => 'Modifier_ParseOrderModifiers'
            ),

            'DESC' => array(
                self::POSTMODIFIER_CLAUSE => 'ORDER BY',
                self::POSTMODIFIER_PARSER => 'Modifier_ParseOrderModifiers'
            ),
        );

        return $_postModifiersContainer;
    }

    /**
     * Retrieve custom identifiers
     *
     * @author Andriy Lesyuk
     * @return array The Identifiers Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetIdentifiers()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_identifiersContainer = array(
            'NULL' => array(
                self::IDENTIFIER_VALUE => null
            ),

            'TRUE' => array(
                self::IDENTIFIER_VALUE => 1,
                self::IDENTIFIER_TYPE => SWIFT_KQL2::DATA_BOOLEAN
            ),

            'FALSE' => array(
                self::IDENTIFIER_VALUE => 0,
                self::IDENTIFIER_TYPE => SWIFT_KQL2::DATA_BOOLEAN
            )
        );

        return $_identifiersContainer;
    }

    /**
     * Retrieve custom variables
     *
     * @author Andriy Lesyuk
     * @return array The Variables Container
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function GetVariables()
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_variablesContainer = array(
            '_STAFF' => array(
                self::VARIABLE_COMPILER => 'Variable_Compile_Staff'
            ),

            '_NOW' => array(
                self::VARIABLE_COMPILER => 'Variable_Compile_Now'
            ),
        );

        return $_variablesContainer;
    }

}
