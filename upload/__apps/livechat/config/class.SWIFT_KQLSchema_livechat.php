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

namespace LiveChat;

use Base\Library\KQL\SWIFT_KQLSchema;
use LiveChat\Models\Call\SWIFT_Call;
use LiveChat\Models\Chat\SWIFT_Chat;
use SWIFT_Exception;
use LiveChat\Models\Message\SWIFT_Message;

/**
 * The Live Chat KQL Schema Class
 *
 * @author Varun Shoor
 */
class SWIFT_KQLSchema_livechat extends SWIFT_KQLSchema
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->LoadKQLLabels('kql_livechat', APP_LIVECHAT);
    }

    /**
     * Retrieve the Live Chat Schema
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
         * Calls
         * ---------------------------------------------
         */
        $_schemaContainer['calls'] = array(
            self::SCHEMA_ISVISIBLE => true,
            self::SCHEMA_PRIMARYKEY => 'callid',
            self::SCHEMA_TABLELABEL => 'calls',
            self::SCHEMA_POSTCOMPILER => 'Schema_PostCompileLiveChat',
            self::SCHEMA_AUTOJOIN => array('users', 'staff', 'departments', 'chatobjects'),
            self::SCHEMA_RELATEDTABLES => array('users' => 'calls.userid = users.userid',
                'staff' => 'calls.staffid = staff.staffid',
                'departments' => 'calls.departmentid = departments.departmentid',
                'chatobjects' => 'calls.chatobjectid = chatobjects.chatobjectid'),

            self::SCHEMA_FIELDS => array(
                'callid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                ),

                'phonenumber' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 200,
                    self::FIELD_ALIGN => 'left',
                ),

                'userid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('users.userid', 'users.fullname'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'userfullname' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 250,
                    self::FIELD_ALIGN => 'left',
                ),

                'useremail' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 250,
                    self::FIELD_ALIGN => 'left',
                ),

                'staffid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('staff.staffid', 'staff.fullname'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'chatobjectid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('chatobjects.chatobjectid', 'chatobjects.chatobjectmaskid'),
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'departmentid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('departments.departmentid', 'departments.title'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'dateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'enddateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'lastactivity' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'duration' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_SECONDS,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'isclicktocall' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 100,
                    self::FIELD_ALIGN => 'center',
                ),

                'callstatus' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_Call::STATUS_ACCEPTED => 'custom_caccepted', SWIFT_Call::STATUS_ENDED => 'custom_cended', SWIFT_Call::STATUS_PENDING => 'custom_cpending', SWIFT_Call::STATUS_REJECTED => 'custom_crejected', SWIFT_Call::STATUS_UNANSWERED => 'custom_cunanswered'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'calltype' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_Call::TYPE_INBOUND => 'custom_cinbound', SWIFT_Call::TYPE_OUTBOUND => 'custom_coutbound'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

            ),
        );


        /**
         * ---------------------------------------------
         * ChatHits
         * ---------------------------------------------
         */
        $_schemaContainer['chathits'] = array(
            self::SCHEMA_ISVISIBLE => true,
            self::SCHEMA_PRIMARYKEY => 'chathitid',
            self::SCHEMA_TABLELABEL => 'chathits',
            self::SCHEMA_AUTOJOIN => array('staff', 'chatobjects'),
            self::SCHEMA_RELATEDTABLES => array('staff' => 'chathits.staffid = staff.staffid',
                'chatobjects' => 'chathits.chatobjectid = chatobjects.chatobjectid'),

            self::SCHEMA_FIELDS => array(
                'chathitid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                ),

                'staffid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('staff.staffid', 'staff.fullname'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'chatobjectid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('chatobjects.chatobjectid', 'chatobjects.chatobjectmaskid'),
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'dateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'isaccepted' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 100,
                    self::FIELD_ALIGN => 'center',
                ),

            ),
        );


        /**
         * ---------------------------------------------
         * ChatObjects
         * ---------------------------------------------
         */
        $_schemaContainer['chatobjects'] = array(
            self::SCHEMA_ISVISIBLE => true,
            self::SCHEMA_PRIMARYKEY => 'chatobjectid',
            self::SCHEMA_TABLELABEL => 'chatobjects',
            self::SCHEMA_POSTCOMPILER => 'Schema_PostCompileChatObjects',
            self::SCHEMA_AUTOJOIN => array('staff', 'users', 'departments'),
            self::SCHEMA_RELATEDTABLES => array('staff' => 'chatobjects.staffid = staff.staffid',
                'users' => 'chatobjects.userid = users.userid',
                'departments' => 'chatobjects.departmentid = departments.departmentid'),

            self::SCHEMA_FIELDS => array(
                'chatobjectid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_WRITER => 'Field_WriteChatID',
                ),

                'chatobjectmaskid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 150,
                    self::FIELD_AUXILIARY => array('chatobjectid' => 'chatobjects.chatobjectid'),
                    self::FIELD_WRITER => 'Field_WriteChatMaskID',
                ),

                'dateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'userid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('users.userid', 'users.fullname'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'userfullname' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 250,
                    self::FIELD_ALIGN => 'center',
                ),

                'useremail' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 250,
                    self::FIELD_ALIGN => 'center',
                ),

                'subject' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 400,
                    self::FIELD_ALIGN => 'center',
                ),

                'staffid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('staff.staffid', 'staff.fullname'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'chatstatus' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_Chat::CHAT_INCOMING => 'custom_chincoming', SWIFT_Chat::CHAT_INCHAT => 'custom_chinchat', SWIFT_Chat::CHAT_ENDED => 'custom_chended', SWIFT_Chat::CHAT_NOANSWER => 'custom_chnoanswer', SWIFT_Chat::CHAT_TIMEOUT => 'custom_chtimeout'),
                    self::FIELD_WIDTH => 230,
                    self::FIELD_ALIGN => 'center',
                ),

                'transferstatus' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array('0' => 'custom_chtransfernone', SWIFT_Chat::TRANSFER_ACCEPTED => 'custom_chtransferaccepted', SWIFT_Chat::TRANSFER_PENDING => 'custom_chtransferpending', SWIFT_Chat::TRANSFER_REJECTED => 'custom_chtransferrejected'),
                    self::FIELD_WIDTH => 230,
                    self::FIELD_ALIGN => 'center',
                ),

                'transfertimeline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'roundrobinhits' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'departmentid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('departments.departmentid', 'departments.title'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'chattype' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_Chat::CHATTYPE_CLIENT => 'custom_chuser', SWIFT_Chat::CHATTYPE_STAFF => 'custom_chstaff'),
                    self::FIELD_WIDTH => 230,
                    self::FIELD_ALIGN => 'center',
                ),

                'ipaddress' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'waittime' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_SECONDS,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'isproactive' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'hasgeoip' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_BOOL,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_ALIGN => 'center',
                ),

                'geoipcountry' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 300,
                    self::FIELD_ALIGN => 'center',
                ),

                'geoipcity' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 250,
                    self::FIELD_ALIGN => 'center',
                ),

                'lastpostactivity' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

            ),
        );


        /**
         * ---------------------------------------------
         * Messages
         * ---------------------------------------------
         */
        $_schemaContainer['messages'] = array(
            self::SCHEMA_ISVISIBLE => true,
            self::SCHEMA_PRIMARYKEY => 'messageid',
            self::SCHEMA_TABLELABEL => 'messages',
            self::SCHEMA_POSTCOMPILER => 'Schema_PostCompileLiveChat',
            self::SCHEMA_AUTOJOIN => array('departments', 'chatobjects'),
            self::SCHEMA_RELATEDTABLES => array('departments' => 'messages.departmentid = departments.departmentid',
                'chatobjects' => 'messages.chatobjectid = chatobjects.chatobjectid',
                'messagedata' => 'messages.messageid = messagedata.messageid'),

            self::SCHEMA_FIELDS => array(
                'messageid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                    self::FIELD_WRITER => 'Field_WriteMessageID',
                ),

                'messagemaskid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 150,
                    self::FIELD_AUXILIARY => array('messageid' => 'messages.messageid'),
                    self::FIELD_WRITER => 'Field_WriteMessageMaskID',
                ),

                'dateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'replydateline' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_UNIXTIME,
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

                'fullname' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 220,
                ),

                'email' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 250,
                ),

                'subject' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 300,
                ),

                'departmentid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('departments.departmentid', 'departments.title'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'messagestatus' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_Message::STATUS_NEW => 'custom_mnew', SWIFT_Message::STATUS_READ => 'custom_mread', SWIFT_Message::STATUS_REPLIED => 'custom_mreplied'),
                    self::FIELD_WIDTH => 230,
                    self::FIELD_ALIGN => 'center',
                ),

                'messagetype' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_CUSTOM,
                    self::FIELD_CUSTOMVALUES => array(SWIFT_Message::MESSAGE_CLIENT => 'custom_mclient', SWIFT_Message::MESSAGE_CLIENTSURVEY => 'custom_mclientsurvey', SWIFT_Message::MESSAGE_STAFF => 'custom_mstaff'),
                    self::FIELD_WIDTH => 230,
                    self::FIELD_ALIGN => 'center',
                ),

                'messagerating' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_FLOAT,
                    self::FIELD_WIDTH => 80,
                ),

                'staffid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('staff.staffid', 'staff.fullname'),
                    self::FIELD_WIDTH => 220,
                    self::FIELD_ALIGN => 'center',
                ),

                'chatobjectid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_LINKED,
                    self::FIELD_LINKEDTO => array('chatobjects.chatobjectid', 'chatobjects.chatobjectmaskid'),
                    self::FIELD_WIDTH => 180,
                    self::FIELD_ALIGN => 'center',
                ),

            ),
        );


        /**
         * ---------------------------------------------
         * Message Data
         * ---------------------------------------------
         */
        $_schemaContainer['messagedata'] = array(
            self::SCHEMA_ISVISIBLE => true,
            self::SCHEMA_PRIMARYKEY => 'messagedataid',
            self::SCHEMA_TABLELABEL => 'messagedata',

            self::SCHEMA_FIELDS => array(
                'messagedataid' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_INT,
                    self::FIELD_WIDTH => 80,
                ),

                'contents' => array(
                    self::FIELD_TYPE => self::FIELDTYPE_STRING,
                    self::FIELD_WIDTH => 300,
                    self::FIELD_ALIGN => 'center',
                ),

            ),
        );

        return $_schemaContainer;
    }

}
