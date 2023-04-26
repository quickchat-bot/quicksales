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

namespace News\Library\Rss;

use News\Models\NewsItem\SWIFT_NewsItem;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;
use SWIFT_XML;

/**
 * The News RSS Manager
 *
 * @property SWIFT_XML $XML
 * @author Varun Shoor
 */
class SWIFT_NewsRSSManager extends SWIFT_Library
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     */
    public function __construct()
    {
        parent::__construct();

        $this->Load->Library('XML:XML');

        $this->Language->Load('news');
    }

    /**
     * Dispatch the RSS feed to the user
     *
     * @author Varun Shoor
     * @param int $_newsCategoryID The News Category ID
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Dispatch($_newsCategoryID = 0)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_newsContainer = SWIFT_NewsItem::Retrieve($this->Settings->Get('nw_maxrss'), 0, array(SWIFT_NewsItem::TYPE_GLOBAL, SWIFT_NewsItem::TYPE_PUBLIC), SWIFT::Get('usergroupid'), 0, $_newsCategoryID);

        @header("Content-Type: text/xml");

        $this->XML->AddParentTag('rss', array('xmlns:content' => 'http://purl.org/rss/1.0/modules/content/', 'xmlns:dc' => 'http://purl.org/dc/elements/1.1/', 'version' => '2.0'));
            $this->XML->AddParentTag('channel');

                $this->XML->AddTag('title', SWIFT::Get('companyname'));
                $this->XML->AddTag('link', SWIFT::Get('swiftpath'));
                $this->XML->AddTag('description', '');
                $this->XML->AddTag('generator', 'Kayako ' . SWIFT_PRODUCT . ' v' . SWIFT_VERSION);

                foreach ($_newsContainer as $_newsItem)
                {
                    $this->XML->AddParentTag('item');

                    $this->XML->AddTag('title', $_newsItem['subject']);
                    $this->XML->AddTag('link', SWIFT::Get('swiftpath') . 'index.php?' . $this->Template->GetTemplateGroupPrefix() . '/News/NewsItem/View/' . $_newsItem['newsitemid']);
                    $this->XML->AddTag('guid', md5($_newsItem['newsitemid']), array('isPermaLink'=>'false'));
                    $this->XML->AddTag('pubDate', date('D, d M Y H:i:s O', $_newsItem['dateline']));
                    $this->XML->AddTag('dc:creator', $_newsItem['author']);
                    $this->XML->AddTag('description', StripName(strip_tags_attributes($_newsItem['contents']), 255));
                    $this->XML->AddTag('content:encoded', $_newsItem['contents']);

                    $this->XML->EndParentTag('item');
                }

            $this->XML->EndParentTag('channel');
        $this->XML->EndParentTag('rss');

        $this->XML->EchoXML();

        return true;
    }
}
