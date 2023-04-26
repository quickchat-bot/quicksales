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
    const DEFAULT_LINK = 'https://go.opencart.com.vn/?pageid=GFIHelpDeskHelp';

    static protected $_linkContainer = [
        'settings' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskHelpsettings',
        'staffpreferences' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskHelpstaffpreferences',
        'usersearch' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskusersearch',
        'userorganization' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskuserorganization',
        'insertorganization' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskinsertorganization',
        'manageorganization' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskmanageorganization',
        'user' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskuser',
        'insertuser' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskinsertuser',
        'manageuser' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskmanageuser',
        'notifications' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesknotifications',
        'templaterestore' => 'hhttps://go.opencart.com.vn/?pageid=GFIHelpDesktemplaterestore',
        'staff' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskstaff',
        'insertstaff' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskinsertstaff',
        'restapi' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskrestapi',
        'customfieldgroup' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskcustomfieldgroup',
        'usergroup' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskusergroup',
        'language' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesklanguage',
        'ratings' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskratings',
        'moveattachments' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskmoveattachments',
        'templatediagnostics' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesktemplatediagnostics',
        'managedepartments' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskmanagedepartments',
        'insertdepartments' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskinsertdepartments',
        'accessoverview' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskaccessoverview',
        'languagephrase' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesklanguagephrase',
        'languagesearchphrase' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesklanguagesearchphrase',
        'geoip' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskgeoip',
        'templategroup' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesktemplategroup',
        'taggenerator' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesktaggenerator',
        'languageimpex' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesklanguageimpex',
        'languagediagnostics' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesklanguagediagnostics',
        'languagerestore' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesklanguagerestore',
        'staffgroup' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskstaffgroup',
        'insertstaffgroup' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskinsertstaffgroup',
        'purgeattachments' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskpurgeattachments',
        'templateimpex' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesktemplateimpex',
        'template' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesktemplate',
        'templateheaderlogos' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesktemplateheaderlogos',
        'customfield' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskcustomfield',
        'templatesearch' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesktemplatesearch',
        'import' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskimport',
        'widget' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskwidget',
        'database' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskdatabase',
        'diagnostics' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskdiagnostics',
        'app' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskapp',
        'parserban' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskparserban',
        'parserlooprule' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskparserlooprule',
        'parsercatchall' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskparsercatchall',
        'parserbreakline' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskparserbreakline',
        'parseremailqueue' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskparseremailqueue',
        'parserlog' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskparserlog',
        'parserrule' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskparserrule',
        'reportimpex' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskreportimpex',
        'newsimpex' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesknewsimpex',
        'newscategory' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesknewscategory',
        'newssubscriber' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesknewssubscriber',
        'newsitem' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesknewsitem',
        'insertnews' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskinsertnews',
        'knowledgebaseview' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskknowledgebaseview',
        'knowledgebaseview' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskknowledgebaseview',
        'kbarticle' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskkbarticle',
        'kbcategory' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskkbcategory',
        'livechatsearch' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesklivechatsearch',
        'chathistory' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskchathistory',
        'chatmessage' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskchatmessage',
        'chatcanned' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskchatcanned',
        'call' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskcall',
        'chatskill' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskchatskill',
        'chatrule' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskchatrule',
        'chatban' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskchatban',
        'chatgroup' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskchatgroup',
        'messagerouting' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskmessagerouting',
        'ticketview' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskticketview',
        'ticketview' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskticketview',
        'ticketlink' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskticketlink',
        'ticketpriority' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskticketpriority',
        'slaholidayimpex' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskslaholidayimpex',
        'escalation' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskescalation',
        'workflow' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskworkflow',
        'ticketsmaintenance' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskticketsmaintenance',
        'ticketfiletype' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskticketfiletype',
        'report' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskreport',
        'ticketstatus' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskticketstatus',
        'bayesiandiagnostics' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskbayesiandiagnostics',
        'tickettype' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesktickettype',
        'slaholiday' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskslaholiday',
        'bayes' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskbayes',
        'sla' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesksla',
        'ticketviews' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskticketviews',
        'ticketmain' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskticketmain',
        'ticketmacro' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskticketmacro',
        'ticketsearch' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskticketsearch',
        'troubleshootercategory' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesktroubleshootercategory',
        'Troubleshootermanage' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskTroubleshootermanage',
        'troubleshooterstep' => 'https://go.opencart.com.vn/?pageid=GFIHelpDesktroubleshooterstep',
        'autoclose' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskautoclose',
        'customurl' => 'https://go.opencart.com.vn/?pageid=GFIHelpDeskcustomurl',
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
