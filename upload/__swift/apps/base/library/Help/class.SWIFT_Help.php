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
    const DEFAULT_LINK = 'https://go.gfi.com/?pageid=GFIHelpDeskHelp';

    static protected $_linkContainer = [
        'settings' => 'https://go.gfi.com/?pageid=GFIHelpDeskHelpsettings',
        'staffpreferences' => 'https://go.gfi.com/?pageid=GFIHelpDeskHelpstaffpreferences',
        'usersearch' => 'https://go.gfi.com/?pageid=GFIHelpDeskusersearch',
        'userorganization' => 'https://go.gfi.com/?pageid=GFIHelpDeskuserorganization',
        'insertorganization' => 'https://go.gfi.com/?pageid=GFIHelpDeskinsertorganization',
        'manageorganization' => 'https://go.gfi.com/?pageid=GFIHelpDeskmanageorganization',
        'user' => 'https://go.gfi.com/?pageid=GFIHelpDeskuser',
        'insertuser' => 'https://go.gfi.com/?pageid=GFIHelpDeskinsertuser',
        'manageuser' => 'https://go.gfi.com/?pageid=GFIHelpDeskmanageuser',
        'notifications' => 'https://go.gfi.com/?pageid=GFIHelpDesknotifications',
        'templaterestore' => 'hhttps://go.gfi.com/?pageid=GFIHelpDesktemplaterestore',
        'staff' => 'https://go.gfi.com/?pageid=GFIHelpDeskstaff',
        'insertstaff' => 'https://go.gfi.com/?pageid=GFIHelpDeskinsertstaff',
        'restapi' => 'https://go.gfi.com/?pageid=GFIHelpDeskrestapi',
        'customfieldgroup' => 'https://go.gfi.com/?pageid=GFIHelpDeskcustomfieldgroup',
        'usergroup' => 'https://go.gfi.com/?pageid=GFIHelpDeskusergroup',
        'language' => 'https://go.gfi.com/?pageid=GFIHelpDesklanguage',
        'ratings' => 'https://go.gfi.com/?pageid=GFIHelpDeskratings',
        'moveattachments' => 'https://go.gfi.com/?pageid=GFIHelpDeskmoveattachments',
        'templatediagnostics' => 'https://go.gfi.com/?pageid=GFIHelpDesktemplatediagnostics',
        'managedepartments' => 'https://go.gfi.com/?pageid=GFIHelpDeskmanagedepartments',
        'insertdepartments' => 'https://go.gfi.com/?pageid=GFIHelpDeskinsertdepartments',
        'accessoverview' => 'https://go.gfi.com/?pageid=GFIHelpDeskaccessoverview',
        'languagephrase' => 'https://go.gfi.com/?pageid=GFIHelpDesklanguagephrase',
        'languagesearchphrase' => 'https://go.gfi.com/?pageid=GFIHelpDesklanguagesearchphrase',
        'geoip' => 'https://go.gfi.com/?pageid=GFIHelpDeskgeoip',
        'templategroup' => 'https://go.gfi.com/?pageid=GFIHelpDesktemplategroup',
        'taggenerator' => 'https://go.gfi.com/?pageid=GFIHelpDesktaggenerator',
        'languageimpex' => 'https://go.gfi.com/?pageid=GFIHelpDesklanguageimpex',
        'languagediagnostics' => 'https://go.gfi.com/?pageid=GFIHelpDesklanguagediagnostics',
        'languagerestore' => 'https://go.gfi.com/?pageid=GFIHelpDesklanguagerestore',
        'staffgroup' => 'https://go.gfi.com/?pageid=GFIHelpDeskstaffgroup',
        'insertstaffgroup' => 'https://go.gfi.com/?pageid=GFIHelpDeskinsertstaffgroup',
        'purgeattachments' => 'https://go.gfi.com/?pageid=GFIHelpDeskpurgeattachments',
        'templateimpex' => 'https://go.gfi.com/?pageid=GFIHelpDesktemplateimpex',
        'template' => 'https://go.gfi.com/?pageid=GFIHelpDesktemplate',
        'templateheaderlogos' => 'https://go.gfi.com/?pageid=GFIHelpDesktemplateheaderlogos',
        'customfield' => 'https://go.gfi.com/?pageid=GFIHelpDeskcustomfield',
        'templatesearch' => 'https://go.gfi.com/?pageid=GFIHelpDesktemplatesearch',
        'import' => 'https://go.gfi.com/?pageid=GFIHelpDeskimport',
        'widget' => 'https://go.gfi.com/?pageid=GFIHelpDeskwidget',
        'database' => 'https://go.gfi.com/?pageid=GFIHelpDeskdatabase',
        'diagnostics' => 'https://go.gfi.com/?pageid=GFIHelpDeskdiagnostics',
        'app' => 'https://go.gfi.com/?pageid=GFIHelpDeskapp',
        'parserban' => 'https://go.gfi.com/?pageid=GFIHelpDeskparserban',
        'parserlooprule' => 'https://go.gfi.com/?pageid=GFIHelpDeskparserlooprule',
        'parsercatchall' => 'https://go.gfi.com/?pageid=GFIHelpDeskparsercatchall',
        'parserbreakline' => 'https://go.gfi.com/?pageid=GFIHelpDeskparserbreakline',
        'parseremailqueue' => 'https://go.gfi.com/?pageid=GFIHelpDeskparseremailqueue',
        'parserlog' => 'https://go.gfi.com/?pageid=GFIHelpDeskparserlog',
        'parserrule' => 'https://go.gfi.com/?pageid=GFIHelpDeskparserrule',
        'reportimpex' => 'https://go.gfi.com/?pageid=GFIHelpDeskreportimpex',
        'newsimpex' => 'https://go.gfi.com/?pageid=GFIHelpDesknewsimpex',
        'newscategory' => 'https://go.gfi.com/?pageid=GFIHelpDesknewscategory',
        'newssubscriber' => 'https://go.gfi.com/?pageid=GFIHelpDesknewssubscriber',
        'newsitem' => 'https://go.gfi.com/?pageid=GFIHelpDesknewsitem',
        'insertnews' => 'https://go.gfi.com/?pageid=GFIHelpDeskinsertnews',
        'knowledgebaseview' => 'https://go.gfi.com/?pageid=GFIHelpDeskknowledgebaseview',
        'knowledgebaseview' => 'https://go.gfi.com/?pageid=GFIHelpDeskknowledgebaseview',
        'kbarticle' => 'https://go.gfi.com/?pageid=GFIHelpDeskkbarticle',
        'kbcategory' => 'https://go.gfi.com/?pageid=GFIHelpDeskkbcategory',
        'livechatsearch' => 'https://go.gfi.com/?pageid=GFIHelpDesklivechatsearch',
        'chathistory' => 'https://go.gfi.com/?pageid=GFIHelpDeskchathistory',
        'chatmessage' => 'https://go.gfi.com/?pageid=GFIHelpDeskchatmessage',
        'chatcanned' => 'https://go.gfi.com/?pageid=GFIHelpDeskchatcanned',
        'call' => 'https://go.gfi.com/?pageid=GFIHelpDeskcall',
        'chatskill' => 'https://go.gfi.com/?pageid=GFIHelpDeskchatskill',
        'chatrule' => 'https://go.gfi.com/?pageid=GFIHelpDeskchatrule',
        'chatban' => 'https://go.gfi.com/?pageid=GFIHelpDeskchatban',
        'chatgroup' => 'https://go.gfi.com/?pageid=GFIHelpDeskchatgroup',
        'messagerouting' => 'https://go.gfi.com/?pageid=GFIHelpDeskmessagerouting',
        'ticketview' => 'https://go.gfi.com/?pageid=GFIHelpDeskticketview',
        'ticketview' => 'https://go.gfi.com/?pageid=GFIHelpDeskticketview',
        'ticketlink' => 'https://go.gfi.com/?pageid=GFIHelpDeskticketlink',
        'ticketpriority' => 'https://go.gfi.com/?pageid=GFIHelpDeskticketpriority',
        'slaholidayimpex' => 'https://go.gfi.com/?pageid=GFIHelpDeskslaholidayimpex',
        'escalation' => 'https://go.gfi.com/?pageid=GFIHelpDeskescalation',
        'workflow' => 'https://go.gfi.com/?pageid=GFIHelpDeskworkflow',
        'ticketsmaintenance' => 'https://go.gfi.com/?pageid=GFIHelpDeskticketsmaintenance',
        'ticketfiletype' => 'https://go.gfi.com/?pageid=GFIHelpDeskticketfiletype',
        'report' => 'https://go.gfi.com/?pageid=GFIHelpDeskreport',
        'ticketstatus' => 'https://go.gfi.com/?pageid=GFIHelpDeskticketstatus',
        'bayesiandiagnostics' => 'https://go.gfi.com/?pageid=GFIHelpDeskbayesiandiagnostics',
        'tickettype' => 'https://go.gfi.com/?pageid=GFIHelpDesktickettype',
        'slaholiday' => 'https://go.gfi.com/?pageid=GFIHelpDeskslaholiday',
        'bayes' => 'https://go.gfi.com/?pageid=GFIHelpDeskbayes',
        'sla' => 'https://go.gfi.com/?pageid=GFIHelpDesksla',
        'ticketviews' => 'https://go.gfi.com/?pageid=GFIHelpDeskticketviews',
        'ticketmain' => 'https://go.gfi.com/?pageid=GFIHelpDeskticketmain',
        'ticketmacro' => 'https://go.gfi.com/?pageid=GFIHelpDeskticketmacro',
        'ticketsearch' => 'https://go.gfi.com/?pageid=GFIHelpDeskticketsearch',
        'troubleshootercategory' => 'https://go.gfi.com/?pageid=GFIHelpDesktroubleshootercategory',
        'Troubleshootermanage' => 'https://go.gfi.com/?pageid=GFIHelpDeskTroubleshootermanage',
        'troubleshooterstep' => 'https://go.gfi.com/?pageid=GFIHelpDesktroubleshooterstep',
        'autoclose' => 'https://go.gfi.com/?pageid=GFIHelpDeskautoclose',
        'customurl' => 'https://go.gfi.com/?pageid=GFIHelpDeskcustomurl',
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
