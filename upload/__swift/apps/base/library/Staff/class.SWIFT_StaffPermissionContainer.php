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

namespace Base\Library\Staff;

use SWIFT_App;
use SWIFT_Library;

/**
 * The Permission Container Class
 *
 * @author Varun Shoor
 */
class SWIFT_StaffPermissionContainer extends SWIFT_Library
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
    }

    /**
     * Retrieve the Admin Permissions
     *
     * @author Varun Shoor
     * @return array The Admin Permissions
     */
    public static function GetAdmin()
    {
        $_adminPermissionContainer = array(
            APP_CORE => array(
                // Staff
                array('corestaff', array(SWIFT_VIEW => 'admin_canviewstaff', SWIFT_INSERT => 'admin_caninsertstaff',
                    SWIFT_UPDATE => 'admin_caneditstaff', SWIFT_DELETE => 'admin_candeletestaff')),
                array('coreteams', array(SWIFT_VIEW => 'admin_canviewstaffgroup', SWIFT_INSERT => 'admin_caninsertstaffgroup',
                    SWIFT_UPDATE => 'admin_caneditstaffgroup', SWIFT_DELETE => 'admin_candeletestaffgroup')),

                // Departments
                array('coredepartments', array(SWIFT_VIEW => 'admin_canviewdepartments', SWIFT_INSERT => 'admin_caninsertdepartment',
                    SWIFT_UPDATE => 'admin_caneditdepartment', SWIFT_DELETE => 'admin_candeletedepartment')),

                // Users
                array('coreusergroups', array(SWIFT_VIEW => 'admin_canviewusergroups', SWIFT_INSERT => 'admin_caninsertusergroup',
                    SWIFT_UPDATE => 'admin_canupdateusergroup', SWIFT_DELETE => 'admin_candeleteusergroups')),

                // Settings
                'admin_canupdatesettings', // DONE

                // Rest API
                'admin_canmanagerestapi', // DONE

                // Tag Generator
                'admin_canmanagetaggenerator', // DONE

                // Templates
                array('coretemplategroups', array(SWIFT_VIEW => 'admin_tmpcanviewgroups', SWIFT_INSERT => 'admin_tmpcaninsertgroup',
                    SWIFT_UPDATE => 'admin_tmpcanupdategroup', SWIFT_DELETE => 'admin_tmpcandeletegroup')),
                array('coretemplates', array(SWIFT_VIEW => 'admin_tmpcanviewtemplates', SWIFT_INSERT => 'admin_tmpcaninserttemplate',
                    SWIFT_UPDATE => 'admin_tmpcanupdatetemplate')),

                'admin_tmpcanrestoretemplates', // DONE
                'admin_tmpcanrundiagnostics', // DONE
                'admin_tmpcanrunimportexport', // DONE
                'admin_tmpcansearchtemplates', // DONE
                'admin_tmpcanpersonalize', // DONE

                // Languages
                array('corelanguages', array(SWIFT_VIEW => 'admin_canviewlanguages', SWIFT_INSERT => 'admin_caninsertlanguage',
                    SWIFT_UPDATE => 'admin_canupdatelanguage', SWIFT_DELETE => 'admin_candeletelanguage')),
                array('corelanguagephrases', array(SWIFT_VIEW => 'admin_canviewphrases', SWIFT_INSERT => 'admin_caninsertphrase',
                    SWIFT_UPDATE => 'admin_canupdatephrase', SWIFT_DELETE => 'admin_candeletephrase')),

                // GeoIP
                'admin_canrebuildgeoip', // DONE

                // Custom Fields
                array('corecustomfieldgroups', array(SWIFT_VIEW => 'admin_canviewcfgroups', SWIFT_INSERT => 'admin_caninsertcfgroup',
                    SWIFT_UPDATE => 'admin_canupdatecfgroup', SWIFT_DELETE => 'admin_candeletecfgroup')),
                array('corecustomfields', array(SWIFT_VIEW => 'admin_canviewcfields', SWIFT_INSERT => 'admin_caninsertcustomfield',
                    SWIFT_UPDATE => 'admin_canupdatecustomfield', SWIFT_DELETE => 'admin_candeletecustomfield')),

                // Ratings
                array('coreratings', array(SWIFT_VIEW => 'admin_canviewratings', SWIFT_INSERT => 'admin_caninsertrating',
                    SWIFT_UPDATE => 'admin_canupdaterating', SWIFT_DELETE => 'admin_candeleterating')),

                // Scheduled Tasks
                array('corescheduledtasks', array(SWIFT_VIEW => 'admin_canviewscheduledtasks', SWIFT_UPDATE => 'admin_canupdatescheduledtasks',
                    SWIFT_DELETE => 'admin_candeletescheduledtasklogs')),

                // Widgets
                array('corewidgets', array(SWIFT_VIEW => 'admin_canviewwidgets', SWIFT_INSERT => 'admin_caninsertwidget',
                    SWIFT_UPDATE => 'admin_canupdatewidget', SWIFT_DELETE => 'admin_candeletewidgets')),

                'admin_canrestorelanguage', // DONE
                'admin_canimportphrases', // DONE
                'admin_canexportphrases', // DONE
                'admin_canrestorephrases', // DONE
                'admin_canrunlanguagediagnostics', // DONE
                'admin_canviewloginlog',
                'admin_canviewactivitylog',
                'admin_canviewerrorlog',
                'admin_canrundiagnostics', // DONE
                'admin_canviewdatabase', // DONE
                'admin_canrunimport',
                'admin_tcanpurgeattachments', // DONE
                'admin_tcanrunmoveattachments', // DONE
                'admin_canmanageapps',
            ),

            APP_LIVECHAT => array(
                array('lrskills', array(SWIFT_VIEW => 'admin_lrcanviewskills', SWIFT_INSERT => 'admin_lrcaninsertskill',
                    SWIFT_UPDATE => 'admin_lrcanupdateskill', SWIFT_DELETE => 'admin_lrcandeleteskill')),
                array('lrrules', array(SWIFT_VIEW => 'admin_lrcanviewrules', SWIFT_INSERT => 'admin_lrcaninsertrule',
                    SWIFT_UPDATE => 'admin_lrcanupdaterule', SWIFT_DELETE => 'admin_lrcandeleterule')),
                array('lrgroups', array(SWIFT_VIEW => 'admin_lrcanviewvisitorgroups', SWIFT_INSERT => 'admin_lrcaninsertvisitorgroup',
                    SWIFT_UPDATE => 'admin_lrcanupdatevisitorgroup', SWIFT_DELETE => 'admin_lrcandeletevisitorgroup')),
                array('lrbans', array(SWIFT_VIEW => 'admin_lrcanviewbans', SWIFT_INSERT => 'admin_lrcaninsertban',
                    SWIFT_UPDATE => 'admin_lrcanupdateban', SWIFT_DELETE => 'admin_lrcandeleteban')),
                array('lrmessagerouting', array(SWIFT_VIEW => 'admin_lrcanviewrouting', SWIFT_UPDATE => 'admin_lrcanupdaterouting')),
                'admin_lrcanviewonlinestaff', // DONE
                'admin_lrcandisconnectstaff', // DONE
            ),

            APP_PARSER => array(
                array('parserqueues', array(SWIFT_VIEW => 'admin_mpcanviewqueues', SWIFT_INSERT => 'admin_mpcaninsertqueue',
                    SWIFT_UPDATE => 'admin_mpcanupdatequeue', SWIFT_DELETE => 'admin_mpcandeletequeue')),
                array('parserlogs', array(SWIFT_VIEW => 'admin_mpcanviewparserlogs', SWIFT_DELETE => 'admin_mpcandeleteparserlogs')),
                array('parserrules', array(SWIFT_VIEW => 'admin_mpcanviewrules', SWIFT_INSERT => 'admin_mpcaninsertrule',
                    SWIFT_UPDATE => 'admin_mpcanupdaterule', SWIFT_DELETE => 'admin_mpcandeleterule')),
                array('parserbreaklines', array(SWIFT_VIEW => 'admin_mpcanviewbreaklines', SWIFT_INSERT => 'admin_mpcaninsertbreakline',
                    SWIFT_UPDATE => 'admin_mpcanupdatebreakline', SWIFT_DELETE => 'admin_mpcandeletebreaklines')),
                array('parserbans', array(SWIFT_VIEW => 'admin_mpcanviewbans', SWIFT_INSERT => 'admin_mpcaninsertban',
                    SWIFT_UPDATE => 'admin_mpcanupdateban', SWIFT_DELETE => 'admin_mpcandeletebans')),
                array('parsercatchall', array(SWIFT_VIEW => 'admin_canviewcatchall', SWIFT_INSERT => 'admin_mpcaninsertcatchall',
                    SWIFT_UPDATE => 'admin_mpcanupdatecatchall', SWIFT_DELETE => 'admin_candeletecatchall')),
                array('parserblockages', array(SWIFT_VIEW => 'admin_mpcanviewloopblockages', SWIFT_DELETE => 'admin_mpcandeleteloopblockages')),
                array('parserlooprule', array(SWIFT_VIEW => 'admin_mpcanviewlooprules', SWIFT_INSERT => 'admin_mpcaninsertlooprule',
                    SWIFT_UPDATE => 'admin_mpcanupdatelooprule', SWIFT_DELETE => 'admin_mpcandeletelooprule')),
            ),

            APP_TICKETS => array(
                array('ticketworkflows', array(SWIFT_VIEW => 'admin_tcanviewworkflows', SWIFT_INSERT => 'admin_tcaninsertworkflow',
                    SWIFT_UPDATE => 'admin_tcanupdateworkflow', SWIFT_DELETE => 'admin_tcandeleteworkflows')),
                array('ticketstatuses', array(SWIFT_VIEW => 'admin_tcanviewstatus', SWIFT_INSERT => 'admin_tcaninsertstatus',
                    SWIFT_UPDATE => 'admin_tcanupdatestatus', SWIFT_DELETE => 'admin_tcandeletestatus')),
                array('tickettypes', array(SWIFT_VIEW => 'admin_tcanviewtypes', SWIFT_INSERT => 'admin_tcaninserttype',
                    SWIFT_UPDATE => 'admin_tcanupdatetype', SWIFT_DELETE => 'admin_tcandeletetypes')),
                array('ticketpriorities', array(SWIFT_VIEW => 'admin_tcanviewpriorities', SWIFT_INSERT => 'admin_tcaninsertpriority',
                    SWIFT_UPDATE => 'admin_tcanupdatepriority', SWIFT_DELETE => 'admin_tcandeletepriority')),
                array('ticketlinks', array(SWIFT_VIEW => 'admin_tcanviewlinks', SWIFT_INSERT => 'admin_tcaninsertlink',
                    SWIFT_UPDATE => 'admin_tcanupdatelink', SWIFT_DELETE => 'admin_tcandeletelinks')),
                array('ticketfiletypes', array(SWIFT_VIEW => 'admin_tcanviewfiletypes', SWIFT_INSERT => 'admin_tcaninsertfiletype',
                    SWIFT_UPDATE => 'admin_tcanupdatefiletype', SWIFT_DELETE => 'admin_tcandeletefiletypes')),
                array('ticketbayes', array(SWIFT_VIEW => 'admin_tcanviewbayescategories', SWIFT_INSERT => 'admin_tcaninsertbayescategory',
                    SWIFT_UPDATE => 'admin_tcanupdatebayescategory', SWIFT_DELETE => 'admin_tcandeletebayescategories')),
                array('ticketautocloserules', array(SWIFT_VIEW => 'admin_tcanviewautoclose', SWIFT_INSERT => 'admin_tcaninsertautoclose',
                    SWIFT_UPDATE => 'admin_tcanupdateautoclose', SWIFT_DELETE => 'admin_tcandeleteautoclose')),
                array('ticketslaplans', array(SWIFT_VIEW => 'admin_tcanviewslaplans', SWIFT_INSERT => 'admin_tcaninsertslaplan',
                    SWIFT_UPDATE => 'admin_tcanupdateslaplan', SWIFT_DELETE => 'admin_tcandeleteslaplans')),
                array('ticketslaschedules', array(SWIFT_VIEW => 'admin_tcanviewslaschedules', SWIFT_INSERT => 'admin_tcaninsertslaschedules',
                    SWIFT_UPDATE => 'admin_tcanupdateslaschedules', SWIFT_DELETE => 'admin_tcandeleteslaschedules')),
                array('ticketslaholidays', array(SWIFT_VIEW => 'admin_tcanviewslaholidays', SWIFT_INSERT => 'admin_tcaninsertslaholidays',
                    SWIFT_UPDATE => 'admin_tcanupdateslaholidays', SWIFT_DELETE => 'admin_tcandeleteslaholidays')),
                array('ticketescalations', array(SWIFT_VIEW => 'admin_tcanviewescalations', SWIFT_INSERT => 'admin_tcaninsertescalations',
                    SWIFT_UPDATE => 'admin_tcanupdateescalations', SWIFT_DELETE => 'admin_tcandeleteescalations')),

                'admin_tcanrunmaintenance', // DONE
                'admin_tcanrunbayesdiagnostics', // DONE
                'admin_tcanimpexslaholidays', // DONE
            ),

            APP_NEWS => array(
                array('staff_newssubscribers', array(SWIFT_UPDATE => 'admin_nwcanupdatesubscriber')),
            ),
        );

        $_permissionContainer = array_merge($_adminPermissionContainer, SWIFT_App::GetPermissionContainer('admin'));

        return $_permissionContainer;
    }

    /**
     * Retrieve the Staff Permission Container
     *
     * @author Varun Shoor
     * @return array The Staff Permission Container
     */
    public static function GetStaff()
    {
        /*
         * Improvement - Bishwanath Jha
         *
         * SWIFT-1339 Restrict staff to be able to view assigned tickets ONLY
         *
         * Comments : Adding staff_tcanviewunassign and staff_tcanviewall to Check if restriction for Group (Can View unassigned Tickets, Can View All Tickets) is set?
         */
        $_staffPermissionContainer = array(
            APP_TICKETS => array(
                't_entab',
                'staff_tcanviewunassign',
                'staff_tcanviewall',
                array('staff_tickets', array(SWIFT_VIEW => 'staff_tcanviewtickets', SWIFT_INSERT => 'staff_tcaninsertticket',
                    SWIFT_UPDATE => 'staff_tcanupdateticket', SWIFT_DELETE => 'staff_tcandeleteticket')),
                array('staff_ticketposts', array(SWIFT_UPDATE => 'staff_tcanupaticketpost', SWIFT_DELETE => 'staff_tcandeleteticketpost')),
                array('staff_ticketbilling', array(SWIFT_VIEW => 'staff_tcanviewbilling', SWIFT_INSERT => 'staff_tcaninsertbilling',
                    SWIFT_UPDATE => 'staff_tcanupdatebilling', SWIFT_DELETE => 'staff_tcandeletebilling')),
                array('staff_ticketfollowup', array(SWIFT_VIEW => 'staff_tcanfollowup', SWIFT_DELETE => 'staff_tcandeletefollowup')),
                array('staff_ticketnotes', array(SWIFT_VIEW => 'staff_tcanviewticketnotes', SWIFT_INSERT => 'staff_tcaninsertticketnote',
                    SWIFT_UPDATE => 'staff_tcanupdateticketnote', SWIFT_DELETE => 'staff_tcandeleteticketnote')),
                array('staff_ticketviews', array(SWIFT_VIEW => 'staff_tcanview_views', SWIFT_INSERT => 'staff_tcaninsertview',
                    SWIFT_UPDATE => 'staff_tcanupdateview', SWIFT_DELETE => 'staff_tcandeleteview')),
                array('staff_ticketfilters', array(SWIFT_VIEW => 'staff_tcanviewfilters', SWIFT_INSERT => 'staff_tcaninsertfilter',
                    SWIFT_UPDATE => 'staff_tcanupdatefilter', SWIFT_DELETE => 'staff_tcandeletefilters')),
                array('staff_ticketmacros', array(SWIFT_VIEW => 'staff_tcanviewmacro', SWIFT_INSERT => 'staff_tcaninsertmacro',
                    SWIFT_UPDATE => 'staff_tcanupdatemacro', SWIFT_DELETE => 'staff_tcandeletemacro')),
                array('staff_recurrence', array(SWIFT_VIEW => 'staff_tcanviewrecurrence', SWIFT_INSERT => 'staff_tcaninsertrecurrence',
                    SWIFT_UPDATE => 'staff_tcanupdaterecurrence', SWIFT_DELETE => 'staff_tcandeleterecurrence')),
                'staff_tcanforward',
                'staff_tcanreply',
                'staff_tcanrelease',
                'staff_tcanworkflow',
                'staff_tcanviewauditlog',
                'staff_tcansaveasdraft',
                'staff_tcanmarkasspam',
                'staff_tcantrashticket',
                'staff_tcansearch',
                'staff_tcanchangeunassigneddepartment',
                'staff_tcanaddfromaddress',
            ),

            APP_LIVECHAT => array(
                'ls_entab',
                array('livechathistory', array(SWIFT_VIEW => 'staff_lscanviewchat', SWIFT_UPDATE => 'staff_lscanupdatechat',
                    SWIFT_DELETE => 'staff_lscandeletechat')),
                array('livechatnotes', array(SWIFT_INSERT => 'admin_lscaninsertchatnote',
                    SWIFT_UPDATE => 'staff_lscanupdatechatnote', SWIFT_DELETE => 'staff_lscandeletechatnote')),
                array('livechatmessages', array(SWIFT_VIEW => 'staff_lscanviewmessages', SWIFT_UPDATE => 'staff_lscanupdatemessages',
                    SWIFT_DELETE => 'staff_lscandeletemessages')),
                array('livechatcalls', array(SWIFT_VIEW => 'staff_lscanviewcalls', SWIFT_DELETE => 'staff_lscandeletecalls')),
                array('livechatcanned', array(SWIFT_VIEW => 'admin_lscanviewcanned', SWIFT_INSERT => 'admin_lscaninsertcanned',
                    SWIFT_UPDATE => 'admin_lscanupdatecanned', SWIFT_DELETE => 'admin_lscandeletecanned')),
                array('winapplrbans', array(SWIFT_INSERT => 'winapp_lrcaninsertban')),
                'ls_canobserve',
            ),

            /*
             * BUG FIX - Saloni Dhall
             *
             * SWIFT-2005: Reports Tab Staff Permissions.
             *
             * Comments:
             */
            APP_REPORTS => array(
                'rp_entab',
                'staff_rrestrict',
                array('reportcategories', array(SWIFT_VIEW => 'staff_rcanviewcategories', SWIFT_INSERT => 'staff_rcaninsertcategory',
                    SWIFT_UPDATE => 'staff_rcanupdatecategory', SWIFT_DELETE => 'staff_rcandeletecategory')),
                array('reports', array(SWIFT_VIEW => 'staff_rcanviewreports', SWIFT_INSERT => 'staff_rcaninsertreport',
                    SWIFT_UPDATE => 'staff_rcanupdatereport', SWIFT_DELETE => 'staff_rcandeletereport')),
                array('reportschedules', array(SWIFT_VIEW => 'staff_rcanviewschedules', SWIFT_INSERT => 'staff_rcaninsertschedule',
                    SWIFT_UPDATE => 'staff_rcanupdateschedule', SWIFT_DELETE => 'staff_rcandeleteschedule')),
            ),

            APP_TROUBLESHOOTER => array(
                'tr_entab',
                array('staff_troubleshootercategories', array(SWIFT_VIEW => 'staff_trcanviewcategories', SWIFT_INSERT => 'staff_trcaninsertcategory',
                    SWIFT_UPDATE => 'staff_trcanupdatecategory', SWIFT_DELETE => 'staff_trcandeletecategory')),
                array('staff_troubleshootersteps', array(SWIFT_VIEW => 'staff_trcanviewsteps', SWIFT_MANAGE => 'staff_trcanmanagesteps', SWIFT_INSERT => 'staff_trcaninsertstep',
                    SWIFT_UPDATE => 'staff_trcanupdatestep', SWIFT_DELETE => 'staff_trcandeletestep')),
                'staff_trcaninsertpublishedsteps',

            ),

            APP_NEWS => array(
                'nw_entab',
                'staff_newscanpublicinsert',
                array('staff_newsitems', array(SWIFT_VIEW => 'staff_nwcanviewitems', SWIFT_MANAGE => 'staff_nwcanmanageitems', SWIFT_INSERT => 'staff_nwcaninsertitem',
                    SWIFT_UPDATE => 'staff_nwcanupdateitem', SWIFT_DELETE => 'staff_nwcandeleteitem')),
                array('staff_newssubscribers', array(SWIFT_VIEW => 'staff_nwcanviewsubscribers', SWIFT_INSERT => 'staff_nwcaninsertsubscriber',
                    SWIFT_UPDATE => 'staff_nwcanupdatesubscriber', SWIFT_DELETE => 'staff_nwcandeletesubscriber')),
                array('staff_newscategories', array(SWIFT_VIEW => 'staff_nwcanviewcategories', SWIFT_INSERT => 'staff_nwcaninsertcategory',
                    SWIFT_UPDATE => 'staff_nwcanupdatecategory', SWIFT_DELETE => 'staff_nwcandeletecategory')),
            ),

            APP_KNOWLEDGEBASE => array(
                'kb_entab',
                array('staff_kbarticles', array(SWIFT_VIEW => 'staff_kbcanviewarticles', SWIFT_MANAGE => 'staff_kbcanmanagearticles', SWIFT_INSERT => 'staff_kbcaninsertarticle',
                    SWIFT_UPDATE => 'staff_kbcanupdatearticle', SWIFT_DELETE => 'staff_kbcandeletearticle')),
                'staff_kbcaninsertpublishedarticles',
                array('staff_kbcategories', array(SWIFT_VIEW => 'staff_kbcanviewcategories', SWIFT_INSERT => 'staff_kbcaninsertcategory',
                    SWIFT_UPDATE => 'staff_kbcanupdatecategory', SWIFT_DELETE => 'staff_kbcandeletecategory')),
            ),


            APP_BASE => array(
                'cu_entab',
                'staff_profile',
                'staff_changepassword',
                'staff_loginasuser',
                array('staff_users', array(SWIFT_VIEW => 'staff_canviewusers', SWIFT_INSERT => 'staff_caninsertuser',
                    SWIFT_UPDATE => 'staff_canupdateuser', SWIFT_DELETE => 'staff_candeleteuser')),
                array('staff_usersorganizations', array(SWIFT_VIEW => 'staff_canviewuserorganizations', SWIFT_INSERT => 'staff_caninsertuserorganization',
                    SWIFT_UPDATE => 'staff_canupdateuserorganization', SWIFT_DELETE => 'staff_candeleteuserorganization')),
                array('staff_usernotes', array(SWIFT_VIEW => 'staff_canviewusernotes', SWIFT_INSERT => 'staff_caninsertusernote',
                    SWIFT_UPDATE => 'staff_canupdateusernote', SWIFT_DELETE => 'staff_candeleteusernote')),
                array('staff_ratings', array(SWIFT_VIEW => 'staff_canviewratings', SWIFT_UPDATE => 'staff_canupdateratings')),
                array('staff_tags', array(SWIFT_UPDATE => 'staff_canupdatetags')),
                array('staff_notifications', array(SWIFT_VIEW => 'staff_canviewnotifications', SWIFT_INSERT => 'staff_caninsertnotification',
                    SWIFT_UPDATE => 'staff_canupdatenotification', SWIFT_DELETE => 'staff_candeletenotification')),
                array('staff_comments', array(SWIFT_VIEW => 'staff_canviewcomments', SWIFT_UPDATE => 'staff_canupdatecomments', SWIFT_DELETE => 'staff_candeletecomments')),
            ),
        );

        $_permissionContainer = array_merge($_staffPermissionContainer, SWIFT_App::GetPermissionContainer('staff'));

        return $_permissionContainer;
    }

    /**
     * Retrieve the Department Permission Container
     *
     * @author Varun Shoor
     * @return array The Department Permission Container
     */
    public static function GetDepartment()
    {
        $_departmentPermissionContainer = array(
            APP_TICKETS => array(
                'd_t_canreply',
                'd_t_canforward',
                'd_t_canfollowup',
                'd_t_canbilling',
            ),

        );

        return $_departmentPermissionContainer;
    }
}

?>
