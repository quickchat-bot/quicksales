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
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Library\Help;

use SWIFT_Exception;
use SWIFT_Library;

/**
 * The Help Button Handling Lib
 *
 * @author Varun Shoor
 */
class SWIFT_Help extends SWIFT_Library
{
    const DEFAULT_LINK = 'https://go.opencart.com.vn/?pageid=NBTHelpDeskHelp';

    static protected $_linkContainer = [
        'settings' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskHelpsettings',
        'staffpreferences' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskHelpstaffpreferences',
        'usersearch' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskusersearch',
        'userorganization' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskuserorganization',
        'insertorganization' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskinsertorganization',
        'manageorganization' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskmanageorganization',
        'user' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskuser',
        'insertuser' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskinsertuser',
        'manageuser' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskmanageuser',
        'notifications' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesknotifications',
        'templaterestore' => 'hhttps://go.opencart.com.vn/?pageid=NBTHelpDesktemplaterestore',
        'staff' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskstaff',
        'insertstaff' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskinsertstaff',
        'restapi' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskrestapi',
        'customfieldgroup' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskcustomfieldgroup',
        'usergroup' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskusergroup',
        'language' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesklanguage',
        'ratings' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskratings',
        'moveattachments' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskmoveattachments',
        'templatediagnostics' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesktemplatediagnostics',
        'managedepartments' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskmanagedepartments',
        'insertdepartments' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskinsertdepartments',
        'accessoverview' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskaccessoverview',
        'languagephrase' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesklanguagephrase',
        'languagesearchphrase' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesklanguagesearchphrase',
        'geoip' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskgeoip',
        'templategroup' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesktemplategroup',
        'taggenerator' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesktaggenerator',
        'languageimpex' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesklanguageimpex',
        'languagediagnostics' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesklanguagediagnostics',
        'languagerestore' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesklanguagerestore',
        'staffgroup' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskstaffgroup',
        'insertstaffgroup' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskinsertstaffgroup',
        'purgeattachments' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskpurgeattachments',
        'templateimpex' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesktemplateimpex',
        'template' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesktemplate',
        'templateheaderlogos' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesktemplateheaderlogos',
        'customfield' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskcustomfield',
        'templatesearch' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesktemplatesearch',
        'import' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskimport',
        'widget' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskwidget',
        'database' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskdatabase',
        'diagnostics' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskdiagnostics',
        'app' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskapp',
        'parserban' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskparserban',
        'parserlooprule' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskparserlooprule',
        'parsercatchall' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskparsercatchall',
        'parserbreakline' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskparserbreakline',
        'parseremailqueue' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskparseremailqueue',
        'parserlog' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskparserlog',
        'parserrule' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskparserrule',
        'reportimpex' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskreportimpex',
        'newsimpex' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesknewsimpex',
        'newscategory' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesknewscategory',
        'newssubscriber' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesknewssubscriber',
        'newsitem' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesknewsitem',
        'insertnews' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskinsertnews',
        'knowledgebaseview' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskknowledgebaseview',
        'knowledgebaseview' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskknowledgebaseview',
        'kbarticle' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskkbarticle',
        'kbcategory' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskkbcategory',
        'livechatsearch' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesklivechatsearch',
        'chathistory' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskchathistory',
        'chatmessage' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskchatmessage',
        'chatcanned' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskchatcanned',
        'call' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskcall',
        'chatskill' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskchatskill',
        'chatrule' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskchatrule',
        'chatban' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskchatban',
        'chatgroup' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskchatgroup',
        'messagerouting' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskmessagerouting',
        'ticketview' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskticketview',
        'ticketview' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskticketview',
        'ticketlink' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskticketlink',
        'ticketpriority' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskticketpriority',
        'slaholidayimpex' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskslaholidayimpex',
        'escalation' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskescalation',
        'workflow' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskworkflow',
        'ticketsmaintenance' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskticketsmaintenance',
        'ticketfiletype' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskticketfiletype',
        'report' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskreport',
        'ticketstatus' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskticketstatus',
        'bayesiandiagnostics' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskbayesiandiagnostics',
        'tickettype' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesktickettype',
        'slaholiday' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskslaholiday',
        'bayes' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskbayes',
        'sla' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesksla',
        'ticketviews' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskticketviews',
        'ticketmain' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskticketmain',
        'ticketmacro' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskticketmacro',
        'ticketsearch' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskticketsearch',
        'troubleshootercategory' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesktroubleshootercategory',
        'Troubleshootermanage' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskTroubleshootermanage',
        'troubleshooterstep' => 'https://go.opencart.com.vn/?pageid=NBTHelpDesktroubleshooterstep',
        'autoclose' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskautoclose',
        'customurl' => 'https://go.opencart.com.vn/?pageid=NBTHelpDeskcustomurl',
    ];

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
     * Retrieve the Help Link
     *
     * @author Varun Shoor
     *
     * @param string $_linkName The Link Name
     *
     * @return string The Documentation Link
     * @throws SWIFT_Exception If Invalid Data is Provided
     */
    public static function RetrieveHelpLink($_linkName)
    {
        if (empty($_linkName)) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        if (isset(self::$_linkContainer[$_linkName]) && !empty(self::$_linkContainer[$_linkName])) {
            return self::$_linkContainer[$_linkName];
        }

        return self::DEFAULT_LINK;
    }
}

?>
