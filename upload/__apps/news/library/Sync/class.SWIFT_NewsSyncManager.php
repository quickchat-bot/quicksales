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

namespace News\Library\Sync;

use News\Models\Category\SWIFT_NewsCategory;
use News\Models\NewsItem\SWIFT_NewsItem;
use SimpleXMLElement;
use SWIFT;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * The News Synchronization Manager
 *
 * @author Varun Shoor
 */
class SWIFT_NewsSyncManager extends SWIFT_Library
{
    /**
     * Sync with the given feed URL
     *
     * @author Varun Shoor
     * @param string $_feedURL The Feed URL
     * @param mixed $_newsType The News Type
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Sync($_feedURL, $_newsType)
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_context = stream_context_create(array(
            'http' => array(
                'header' => 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:6.0.2) Gecko/20100101 Firefox/6.0.2',
            ),
        ));

        $_feedContents = file_get_contents($_feedURL, false, $_context);

        if (empty($_feedContents))
        {
            return false;
        }

        if(mb_detect_encoding($_feedContents) != 'UTF-8')
        {
            $_feedContents = utf8_encode($_feedContents);
        }

        $_XMLObject = simplexml_load_string($_feedContents);

        if ($_XMLObject === false || !isset($_XMLObject->channel, $_XMLObject->channel->item))
        {
            return false;
        }

        $_FeedObject = $this->ParseXMLFeed($_XMLObject);
        if (!isset($_FeedObject->items))
        {
            return false;
        }

        $_guidHashList = array();
        foreach ($_FeedObject->items as $_ItemObject)
        {
            $_guidHashList[] = md5((string) $_ItemObject->options->guid);
        }

        // Get list of sync'ed guids
        $_dbGuidHashList = $_dbGuidHashMap = array();
        $this->Database->Query("SELECT newsitemid, syncguidhash, contentshash, subjecthash, descriptionhash FROM " . TABLE_PREFIX . "newsitems WHERE issynced = '1' AND syncguidhash IN (" . BuildIN($_guidHashList) . ")");
        while ($this->Database->NextRecord())
        {
            $_dbGuidHashList[] = $this->Database->Record['syncguidhash'];
            $_dbGuidHashMap[$this->Database->Record['syncguidhash']] = array();
            $_dbGuidHashMap[$this->Database->Record['syncguidhash']]['newsitemid'] = $this->Database->Record['newsitemid'];
            $_dbGuidHashMap[$this->Database->Record['syncguidhash']]['contentshash'] = $this->Database->Record['contentshash'];
            $_dbGuidHashMap[$this->Database->Record['syncguidhash']]['subjecthash'] = $this->Database->Record['subjecthash'];
            $_dbGuidHashMap[$this->Database->Record['syncguidhash']]['descriptionhash'] = $this->Database->Record['descriptionhash'];
        }

        foreach ($_FeedObject->items as $_ItemObject)
        {
            $_guidHash = md5((string) $_ItemObject->options->guid);

            // Item is already in database
            if (array_key_exists($_guidHash, $_dbGuidHashMap))
            {
                $_newsItemID = array_key_exists('newsitemid', $_dbGuidHashMap[$_guidHash])? $_dbGuidHashMap[$_guidHash]['newsitemid'] : 0;
                $_contentsHash = array_key_exists('contentshash', $_dbGuidHashMap[$_guidHash])? $_dbGuidHashMap[$_guidHash]['contentshash'] : 0;
                $_subjectHash = array_key_exists('subjecthash', $_dbGuidHashMap[$_guidHash])? $_dbGuidHashMap[$_guidHash]['subjecthash'] : 0;
                $_descriptionHash = array_key_exists('descriptionhash', $_dbGuidHashMap[$_guidHash])? $_dbGuidHashMap[$_guidHash]['descriptionhash'] : 0;

                if (md5($_ItemObject->contents) != $_contentsHash || md5($_ItemObject->title) != $_subjectHash || md5($_ItemObject->description) != $_descriptionHash)
                {
                    $_SWIFT_NewsItemObject = new SWIFT_NewsItem((int)$_newsItemID);
                    if ($_SWIFT_NewsItemObject instanceof SWIFT_NewsItem && $_SWIFT_NewsItemObject->GetIsClassLoaded())
                    {
                        $_SWIFT_NewsItemObject->UpdateContents($_ItemObject->title, $_ItemObject->description, $_ItemObject->contents);
                    }
                }
            } else {
                $_newsItemID = SWIFT_NewsItem::Create($_newsType, SWIFT_NewsItem::STATUS_PUBLISHED, $_ItemObject->options->author, '', $_ItemObject->title, $_ItemObject->description,
                        $_ItemObject->contents, 0, 0, true, false, array(), false, array(), true, $_ItemObject->options->guid, $_ItemObject->title, $_ItemObject->options->timestamp);

                $_categoryList = array();
                if (isset($_ItemObject->options->tags))
                {
                    foreach ($_ItemObject->options->tags as $_TagObject)
                    {
                        $_categoryList[] = (string) $_TagObject;
                    }
                }

                if (count($_categoryList))
                {
                    $_visibilityType = SWIFT_PUBLIC;
                    if ($_newsType == SWIFT_NewsItem::TYPE_PRIVATE)
                    {
                        $_visibilityType = SWIFT_PRIVATE;
                    }

                    SWIFT_NewsCategory::CreateOrUpdateFromSync($_categoryList, $_newsItemID, $_visibilityType);
                }
            }
        }

        return true;
    }


    /**
     * Parse the XML Feed
     *
     * @author http://drupal.org/node/327508
     * @param SimpleXMLElement $feed_XML The Feed XML
     * @return object
     */
    protected function ParseXMLFeed($feed_XML)
    {
        $parsed_source = new \stdClass();
        // Detect the title.
        $parsed_source->title = '';
        if (isset($feed_XML->channel->title)) {
            $parsed_source->title = (string)$feed_XML->channel->title;
        }

        // Detect the description.
        $parsed_source->description = isset($feed_XML->channel->description) ? "{$feed_XML->channel->description}" : "";
        $parsed_source->options = new \stdClass();
        // Detect the link.
        $parsed_source->options->link = isset($feed_XML->channel->link) ? "{$feed_XML->channel->link}" : "";
        $parsed_source->items = [];
        $nslist = $feed_XML->getDocNamespaces(true); // Armando

        /** @var array $elements */
        $elements = $feed_XML->xpath('//item');
        foreach ($elements as $news) {
            $category = $news->xpath('category');
            $content = [];
            $dc = [];

            // Get children for current namespace.
            if (PHP_VERSION_ID > 50102) {
                $content = (array)$news->children('http://purl.org/rss/1.0/modules/content/');
                $dc = (array)$news->children('http://purl.org/dc/elements/1.1/');
            }
            ///// Armando
            $nsinfo = [];
            foreach ($nslist as $nsname => $uri) {
                $nsinfo[$nsname] = (array)$news->children($uri);
            }
            //////

            $news = (array)$news;
            $news['category'] = $category;

            $guid = $news['link'];
            if (isset($news['guid'])) {
                $guid = $news['guid'];
            }

            $title = '';
            if (isset($news['title'])) {
                $title = "{$news['title']}";
            }

            $body = $contents = '';
            if (isset($news['description'])) {
                $body = (string)$news['description'];

            } elseif (isset($news['encoded'])) {  // content:encoded for PHP < 5.1.2.
                $body = (string)$news['encoded'];
            } elseif (isset($content['encoded'])) { // content:encoded for PHP >= 5.1.2.
                $body = (string)$content['encoded'];
            } else {
                $body = (string)$news['title'];
            }

            if (isset($news['encoded'])) {  // content:encoded for PHP < 5.1.2.
                $contents = (string)$news['encoded'];
            } elseif (isset($content['encoded'])) { // content:encoded for PHP >= 5.1.2.
                $contents = (string)$content['encoded'];
            } else {
                $contents = $body;
            }

            $author = '';
            if (isset($dc['creator'])) {
                $author = $dc['creator'];
            } else {
                if (isset($news['author'])) {
                    $author = (string)$news['author'];
                }
            }

            $original_author = $original_url = '';
            if (!empty($feed_XML->channel->title)) {
                $original_author = (string)$feed_XML->channel->title;
            }

            if (!empty($news['link'])) {
                $original_url = (string)$news['link'];
            }

            $additional_taxonomies = [];
            $additional_taxonomies['RSS Categories'] = [];
            $additional_taxonomies['RSS Domains'] = [];
            if (isset($news['category'])) {
                foreach ($news['category'] as $category) {
                    $additional_taxonomies['RSS Categories'][] = $category;
                    if (isset($category['domain'])) {
                        $domain = (string)$category['domain'];
                        if (!empty($domain)) {
                            if (!isset($additional_taxonomies['RSS Domains'][$domain])) {
                                $additional_taxonomies['RSS Domains'][$domain] = [];
                            }
                            $additional_taxonomies['RSS Domains'][$domain][] = count($additional_taxonomies['RSS Categories']) - 1;
                        }
                    }
                }
            }

            $_newsDate = '';
            if (isset($news['pubDate'])) {
                $_newsDate = $news['pubDate'];
            } else {
                if (isset($dc['date'])) {
                    $_newsDate = $dc['date'];
                }
            }

            $item = new \stdClass();
            $item->title = $title;
            $item->description = $body;
            $item->contents = $contents;
            $item->options = new \stdClass();
            $item->options->author = $author;
            $item->options->original_author = $original_author;
            $item->options->timestamp = strtotime($_newsDate);
            $item->options->original_url = $original_url;
            $item->options->guid = $guid;
            $item->options->domains = $additional_taxonomies['RSS Domains'];
            $item->options->tags = $additional_taxonomies['RSS Categories'];
            $item->options->namespaces = $nsinfo; // Armando
            $parsed_source->items[] = $item;
        }
        return $parsed_source;
    }
}
