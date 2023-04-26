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
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

namespace Base\Library\HTML;

use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_URISchemeRegistry;
use SWIFT_Exception;
use SWIFT_Library;

/**
 * The HTML Purifier Wrapper Class
 *
 * @author Varun Shoor
 */
class SWIFT_HTMLPurifier extends SWIFT_Library
{
    const END_HTML_RE = '/\<\/body\>\s*\<\/html\>(\s*\S+)/m';
    const END_HTML = '/\<\/body\>\s*\<\/html\>/m';
    const BEGIN_HTML_RE = '/^\<html\>/';
    const MIDDLE_TEXT_OPEN_HTML = '/([\w\W]+)(\<html\>[\w\W]*\<body\>)/m';

    const AFTER_HTML_CONTENT_IDX = 1;
    const MIDDLE_HTML_FLAG_IDX = 2;

    protected $HTMLPurifierConfig = false;
    protected $HTMLPurifierObject = false;

    private static $cache = [];

    /**
     * Constructor
     *
     * @author Varun Shoor
     * @throws SWIFT_Exception
     */
    public function __construct()
    {
        parent::__construct();

        $this->HTMLPurifierConfig = $this->getHtmlPurifierConfig();
        $this->HTMLPurifierObject = new HTMLPurifier($this->HTMLPurifierConfig);
    }

    /**
     * @param string|bool $_overrideAllowableTags
     * @param string|bool $_overrideAllowableAttrs
     * @return HTMLPurifier_Config
     */
    private function getHtmlPurifierConfig($_overrideAllowableTags = false, $_overrideAllowableAttrs = false)
    {
        if (false === $_overrideAllowableTags && $this->HTMLPurifierConfig) {
            return $this->HTMLPurifierConfig;
        }

        $_HTMLPurifierConfig = HTMLPurifier_Config::createDefault();
        $_HTMLPurifierConfig->autoFinalize = false;
        $_HTMLPurifierConfig->set('Cache.SerializerPath', StripTrailingSlash(SWIFT_BASEPATH) . '/' . SWIFT_BASE_DIRECTORY . '/' . SWIFT_CACHE_DIRECTORY);
        $_HTMLPurifierConfig->set('HTML.SafeIframe', true);
        /**
         * Allow CSS display attributes (for rendering accuracy of emails)
         * @see http://htmlpurifier.org/live/configdoc/plain.html#CSS.AllowTricky
         */
        $_HTMLPurifierConfig->set('CSS.AllowTricky', true);
        $_HTMLPurifierConfig->set('URI.SafeIframeRegexp', '%^(https?:)?//(www.youtube.com|youtu.be|player.vimeo.com)%');
        $_HTMLPurifierConfig->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
            'mailto' => true,
            'ftp' => true,
            'nntp' => true,
            'news' => true,
            'tel' => true,
            'data' => true,
        ]);
        $_HTMLPurifierConfig->set('AutoFormat.RemoveEmpty', true);
        $_HTMLPurifierConfig->set('Attr.AllowedFrameTargets', ['_blank']);

        HTMLPurifier_URISchemeRegistry::instance()->register('cid', new HTMLPurifier_URIScheme_cid());

        if ($this->Settings->GetBool('t_allowhtml')) {
            $_preAllowableTags = $this->Settings->Get('t_allowableadvtags');
            $_allowableTagsAttributes = $this->Settings->Get('t_allowableadvtagsattributes');

            if (false !== $_overrideAllowableTags) {
                if (!empty($_preAllowableTags)) {
                    $_preAllowableTags .= ',';
                }
                $_preAllowableTags .= $_overrideAllowableTags;
            }

            if (false !== $_overrideAllowableAttrs) {
                if (!empty($_allowableTagsAttributes)) {
                    $_allowableTagsAttributes .= ',';
                }
                $_allowableTagsAttributes .= $_overrideAllowableAttrs;
            }

            $allowable = [$_preAllowableTags];
            $validDef = $_HTMLPurifierConfig->getHTMLDefinition();
            $attributes = explode(',', $_allowableTagsAttributes);

            foreach (explode(',', $_preAllowableTags) as $tag) {
                if (isset($validDef->info[$tag])) {
                    $valid = implode('|', array_filter(
                        $attributes,
                        function ($val) use ($tag, $validDef) {
                            return isset($validDef->info[$tag]->attr[$val]);
                        }
                    ));
                    if (!empty($valid))
                        $allowable[] = sprintf('%s[%s]', $tag, $valid);
                }
            }

            $_preAllowableTags = implode(',', $allowable);

            if (!empty($_overrideAllowableTags)) {
                $_HTMLPurifierConfig->set('HTML.Allowed', $_preAllowableTags);
                $_HTMLPurifierConfig->getHTMLDefinition();
            }

            $_HTMLPurifierConfig->finalize();
        }

        return $_HTMLPurifierConfig;
    }

    /**
     * Purify the HTML
     *
     * @author Varun Shoor
     * @param string $_stringContents
     * @param string|bool $_overrideAllowableTags
     * @param string|bool $_overrideAllowableAttrs
     * @return string
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Purify($_stringContents, $_overrideAllowableTags = false, $_overrideAllowableAttrs = false)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $openHtmlFlag = self::getOpenHtmlFlag($_stringContents);
        if ($openHtmlFlag) {
            if (self::hasBeginningHtml($_stringContents)) {
                $_stringContents = preg_replace(self::MIDDLE_TEXT_OPEN_HTML, "$1", $_stringContents);
            } else {
                $_stringContents = $openHtmlFlag.PHP_EOL.preg_replace(self::MIDDLE_TEXT_OPEN_HTML, "$1", $_stringContents);
            }
        }

        if (self::hasMultipleHTMLEndTags($_stringContents)) {
            $_stringContents = preg_replace(self::END_HTML, "", $_stringContents) . '</body></html>';
        }
        
        $hash = sha1($_stringContents);
        if (isset(self::$cache[$hash])) {
            return self::$cache[$hash];
        }

        if (false !== $_overrideAllowableTags || false !== $_overrideAllowableAttrs) {
            $_HTMLPurifierObject = new HTMLPurifier($this->getHtmlPurifierConfig($_overrideAllowableTags, $_overrideAllowableAttrs));

            return $_HTMLPurifierObject->purify($_stringContents);
        }

        return self::$cache[$hash] = $this->HTMLPurifierObject->purify($_stringContents);
    }

    protected static function hasBeginningHtml($html) 
    {
        preg_match(self::BEGIN_HTML_RE, $html, $matches);

        return isset($matches)
            && is_array($matches)
            && !empty($matches);
    }

    protected static function getOpenHtmlFlag($html) {
        preg_match(self::MIDDLE_TEXT_OPEN_HTML, $html, $matches);

        if (isset($matches) && is_array($matches) && count($matches) > self::MIDDLE_HTML_FLAG_IDX) {
            return $matches[self::MIDDLE_HTML_FLAG_IDX];
        }

        return '';
    }

    protected static function hasMultipleHTMLEndTags($html)
    {
        preg_match(self::END_HTML_RE, $html, $matches);

        return isset($matches)
            && is_array($matches)
            && count($matches) > self::AFTER_HTML_CONTENT_IDX
            && strlen($matches[self::AFTER_HTML_CONTENT_IDX]) > 0;
    }
}
