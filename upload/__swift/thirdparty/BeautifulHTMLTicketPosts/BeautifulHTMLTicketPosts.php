<?php
/**
 *
 * Beautiful HTML Ticket Posts
 *
 * @author         Marvin Herbold <mherbold@ainterpreting.com>
 * @link           http://forge.kayako.com/projects/beautiful-html-ticket-posts
 */

/**
 * Beautiful HTML Ticket Posts
 *
 */
class BeautifulHTMLTicketPosts {

    // marvin: process the html so we have a nice clean valid unbroken html for display in the staff cp and for history in emails
    public static function Beautify($_contents, $_isContentHTML = false) {

        if ($_isContentHTML)
        {
            $_contents = preg_replace('/^<!DOCTYPE.+?>/i', '', $_contents);

            /**
             * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
             *
             * SWIFT-4778 New line break is not working even after allowing HTML rendering in tickets.
             *
             * Comments: System should convert \n spaces to <br> if content is not containing <br> and <body> tags(not proper html)
             */
            if (strpos($_contents, '<br') === false && strpos($_contents, '<body') === false) {
                $_contents = nl2br($_contents);
            }

            // convert newlines to spaces unless it is inside a <pre> tag in which case we convert it to a <br> instead
            $_contents = preg_replace('/\r?\n/', '<newline-was-here>', $_contents);
            $_contents = preg_replace_callback('/<pre.*?>(.*?)<\/pre>/imsu', function($_matches) { return str_replace('<newline-was-here>', '<br>', $_matches[0]); }, $_contents);
            $_contents = str_replace('<newline-was-here>', ' ', $_contents);

            // parse the (potentially broken) original html into a tree
            $_length = strlen($_contents);
            $_offset = 0;
            $_rootTag = new HtmlTag(null, 'root');

            self::ParseHtml($_contents, $_length, $_offset, $_rootTag);

            // find the body tag (or just pick the root tag if there is no body tag)
            $_firstTag = self::FindTag($_rootTag, 'body');

            if ($_firstTag === null)
            {
                $_firstTag = $_rootTag;
            }

            // do html breaklines
            if (strpos($_contents, '<!-- breakline was here -->') !== false)
            {
                $_breaklines = array(
                    // unknown mail client
                    'p blockquote #^on\s#i a /a #\swrote:$#i br /br /blockquote /p',
                    // unknown mail client
                    'hr /hr font p b #from:#i /b #.*# br /br b #sent:#i /b #.*# br /br b #to:#i /b #.*# br /br /p /font',
                    // mail from blackberry.net
                    'hr /hr div b #from:#i /b /div div b #date:#i /b /div div b #to:#i /b /div',
                    // mail from gmail.com
                    'div #on\s.*\sat\s.*#i span /span #wrote:#i br /br blockquote /blockquote',
                    'div #^on\s.*$#i a /a #^.*wrote:$#i br /br blockquote /blockquote',
                    'br /br #^on\s.*\swrote:$#i br /br blockquote /blockquote',
                    // mail from hotmail.com
                    'hr /hr #^date:\s#i br /br #^subject:\s#i br /br #^from:\s#i br /br #^to:\s#i br /br',
                    // blackberry wireless (using exchange?)
                    'br /br div font b #^from$#i /b #^:\s.*$# br /br b #^sent$#i /b #^:\s.*$# br /br b #^to$#i /b #^:\s.*$# br /br /font /div',
                    // verizon wireless
                    'br /br div #-+\sreply\smessage\s-+#i br /br #^from:#i br /br #^to:#i /div',
                    // X-Mailer: Apple Mail (2.1082)
                    'br /br div div #^on\s.*\sat\s.*\swrote:$#iU /div br /br blockquote /blockquote /div',
                    // X-Mailer: Microsoft Windows Live Mail 15.4.3538.513
                    'div b #from:#i /b /div div b #sent:#i /b /div div b #to:#i /b /div div b #subject:#i /b /div',
                    // X-Mailer: Microsoft Office Outlook 12.0
                    'div p ?a b span #from:#i /span /b span #.*# br /br b #sent:#i /b #.*# br /br b #to:#i /b #.*# br /br /span /p /div',
                    // X-Mailer: Microsoft Windows Mail 6.0.6002.18197
                    'blockquote div #-+\soriginal\smessage\s-+#i /div div b #from:#i /b /div div b #to:#i /b /div div b #sent:#i /b /div /blockquote',
                    // X-Mailer: Lotus Notes Release 8.5.1 September 28, 2009
                    'br /br table tr td td table tr td font b /b /font /td /tr /table br /br table tr td font /font td font #to:#i /font /td /td /tr /table br /br table tr td font b #please\srespond\sto\s#i /b /font /td /tr /table',
                    // X-Mailer: Microsoft Outlook 14.0
                    'p b span #from:#i /span /b span #.*# br /br b #sent:#i /b #.*# br /br b #to:#i /b #.*# br /br b #subject:#i /b /span /p',
                    // X-Mailer: iPhone Mail (9A405)
                    'div br /br #on\s.*\sat\s.*#i a /a #.*wrote:#i /div div /div blockquote /blockquote',
                    // X-Mailer: iPhone Mail (8C148)
                    'div br /br br /br #on\s.*\sat\s.*#i a /a #.*wrote:#i /div div /div blockquote /blockquote',
                    // X-Mailer: Verizon Webmail
                    'span #^\s?on\s.*$#i span /span #^\s?wrote:$#i /span div /div',
                    // X-Mailer: YahooMailWebService/0.8.116.338427
                    'div font hr /hr b span #from:#i /span /b #.*# br /br b span #to:#i /span /b #.*# br /br b span #sent:#i /span /b #.*# br /br b span #subject:#i /span /b #.*# br /br /font /div',
                    // X-Mailer: Motorola android mail 1.0
                    'br /br #^\s*-+\s?original\smessage\s?-+\s*$#im br /br blockquote /blockquote',
                    // X-Mailer: Lotus Domino Web Server Release 8.5.3 September 15, 2011
                    'br /br font #^-+.*\swrote:\s-+$#im /font div div #to:\s#i br /br #from:\s#i br /br #date:\s#i br /br /div /div',
                    // X-MimeOLE: Produced By Microsoft Exchange V6.5
                    'p #^\s*-+\s?original\smessage\s?-+\s*$#im br /br #from:\s.*#i br /br #sent:\s.*#i br /br #to:\s.*#i br /br /p',
                    // X-Mailer: YahooMailClassic/15.0.4 YahooMailWebService/0.8.116.338427
                    'div /div b span #from:#i /span /b !br /br b span #to:#i /span /b !br /br b span #sent:#i /span /b !br /br b span #subject:#i /span /b',
                );

                foreach ($_breaklines as $_breakline)
                {
                    $_breakline = explode(' ', $_breakline);

                    self::ProcessBreakline($_firstTag, $_breakline);
                }
            }

            // get rid of the trailing html whitespace (tags at the end of the html that display nothing but space)
            //self::TrimTrailingWhitespace($_firstTag);

            /**
             * Bug Fix : Saloni Dhall <saloni.dhall@kayako.com>
             * SWIFT-4333 : An extra space is added to the contents of ticket created from Outlook
             * Comments : $_firstTag fetches data from <body> tag, style and other tags cant be skipped from contents.
             */
            // reconstruct document tree into valid html
            $_contents = self::ReconstructHtml($_rootTag);

            // more than 2 line breaks in a row is annoying
            do
            {
                $_contents = preg_replace('#<br>\s*<br>\s*<br>#iU', '<br><br>', $_contents, -1, $count);
            }
            while ($count > 0);
        }
        else
        {
            // do text breaklines
            $_breaklinePos = strpos($_contents, '<!-- breakline was here -->');

            if ($_breaklinePos !== false)
            {
                $_contents = substr($_contents, 0, $_breaklinePos);

                $_regexBreaklines = array(
                    '#^\s*-+\s?original\smessage\s?-+\s*$#im',
                    '#^from:.*\r?\nsent:.*\r?\nto:.*\r?\nsubject:.*$#imU',
                    '#^from:.*\r?\nto:.*\r?\ncc:.*\r?\ndate:.*\r?\nsubject:.*$#imU',
                    '#^on\s.*(\r?\n.*)?\swrote:(\r?\n)+>\s#im',
                    '#^on\s.*(\r?\n.*)?\swrote:(\r?\n)+\Z#im',
                );

                foreach ($_regexBreaklines as $_regexBreakline)
                {
                    $_splitContainer = preg_split($_regexBreakline, $_contents);

                    if (count($_splitContainer) > 1)
                    {
                        $_contents = $_splitContainer[0];
                    }
                }
            }

            // trim off whitespace
            $_contents = trim($_contents);
        }

        return $_contents;
    }

    // marvin: parse (potentially invalid) html into a tree
    private static function ParseHtml(& $_contents, $_length, & $_offset, HtmlTag $_currentTag)
    {
        $_singletonTags = array('area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source');

        while ($_offset < $_length)
        {
            if ($_contents[$_offset] == '<')
            {
                // discard <!-- ... --> tags
                if (substr_compare($_contents, '<!--', $_offset, 4) == 0)
                {
                    if (!preg_match('#<!--.*-->#U', $_contents, $_matches, 0, $_offset))
                    {
                        $_offset = $_length;
                        return;
                    }

                    $_offset += strlen($_matches[0]);
                }

                // discard <![ ... ]> tags
                else if (substr_compare($_contents, '<![', $_offset, 3) == 0)
                {
                    if (!preg_match('#<!\[.*\]>#U', $_contents, $_matches, 0, $_offset))
                    {
                        $_offset = $_length;
                        return;
                    }

                    $_offset += strlen($_matches[0]);
                }

                // process closing tags
                else if (substr_compare($_contents, '</', $_offset, 2) == 0)
                {
                    if (!preg_match('#</([a-z0-9:]+)( .*)?>#iU', $_contents, $_matches, 0, $_offset))
                    {
                        $_offset = $_length;
                        return;
                    }

                    $_tagName = strtolower($_matches[1]);

                    if ($_tagName == $_currentTag->_name)
                    {
                        // tag nicely closed - we are good to go
                        $_offset += strlen($_matches[0]);
                        $_currentTag->_hasCloseTag = true;
                        return;
                    }
                    else
                    {
                        // does not match the currently open tag so look for one up the tree
                        $_parentTag = $_currentTag->_parentTag;

                        while ($_parentTag !== null)
                        {
                            if ($_parentTag->_name == $_tagName)
                            {
                                // found a matching tag up the tree
                                return;
                            }

                            $_parentTag = $_parentTag->_parentTag;
                        }

                        // no matching opening tag up the tree so discard this invalid closing tag
                        $_offset += strlen($_matches[0]);
                    }
                }

                /**
                 * IMPROVEMENT - Ankit Saini <ankit.saini@kayako.com>
                 *
                 * SWIFT-5169 Kayako should allow the rendering of mathematical representations including "<" or ">" symbols.
                 */

                // If a start tag is followed immediately by a number consider it as a mathematical expression
                else if(preg_match('#(^<=? *[+-]?\d+\.?\d*)#', substr($_contents, $_offset), $_matches, 0, 0)){
                    $_offset += strlen($_matches[0]);
                    $_currentTag->_data[] = $_matches[0];
                }

                // process non closing tags as expressions and add as data in the current tag.
                else if(preg_match('#(^<[^>]*)<#', substr($_contents, $_offset), $_matches, 0, 0)){
                    $_offset += strlen($_matches[1]);
                    $_currentTag->_data[] = $_matches[1];
                }

                // process opening tags
                else
                {
                    if (!preg_match('#<([a-z0-9:]+)( .*)?(/)?>#iU', $_contents, $_matches, 0, $_offset))
                    {
                        $_offset = $_length;
                        return;
                    }

                    $_offset += strlen($_matches[0]);

                    $_newTag = new HtmlTag($_currentTag, strtolower($_matches[1]));

                    $_currentTag->_data[] = $_newTag;

                    $_newTag->_hasOpenTag = true;

                    if (isset($_matches[2]))
                    {
                        $_newTag->_parameters = trim($_matches[2]);
                    }

                    if (isset($_matches[3]) && ($_matches[3] == '/'))
                    {
                        $_newTag->_isSelfClosing = true;
                    }
                    else
                    {
                        if (in_array($_newTag->_name, $_singletonTags))
                        {
                            $_newTag->_isSingletonTag = true;
                        }
                        else
                        {
                            // process tag contents
                            self::ParseHtml($_contents, $_length, $_offset, $_newTag);
                        }
                    }
                }
            }
            else
            {
                // process data between tags
                if (!preg_match('#(.*)<#U', $_contents, $_matches, 0, $_offset))
                {
                    /**
                     * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
                     *
                     * SWIFT-4963 User receives blank emails when staff replies from mobile apps
                     */
                    $_currentTag->_data[] = substr($_contents, $_offset);
                    $_offset = $_length;
                    return;
                }

                $_offset += strlen($_matches[1]);

                $_currentTag->_data[] = $_matches[1];
            }
        }
    }

    // marvin: search for a specific tag (like 'body')
    private static function FindTag(HtmlTag $_currentTag, $_tagName)
    {
        if ($_currentTag->_name == $_tagName)
        {
            return $_currentTag;
        }

        foreach ($_currentTag->_data as $_data)
        {
            if ($_data instanceof HtmlTag)
            {
                $_matchingTag = self::FindTag($_data, $_tagName);

                if ($_matchingTag !== null)
                {
                    return $_matchingTag;
                }
            }
        }

        return null;
    }

    // marvin: remove a tag from the tree
    private static function RemoveTag(HtmlTag $_currentTag)
    {
        $_parentTag = $_currentTag->_parentTag;

        if ($_parentTag instanceof HtmlTag) {
            foreach ($_parentTag->_data as $_key => $_data)
            {
                if ($_data === $_currentTag)
                {
                    unset($_parentTag->_data[ $_key ]);

                    break;
                }
            }
        }
    }

    // marvin: figure out of this breakline matches this tag
    private static function BreaklineMatch(HtmlTag $_currentTag, array & $_breakline, & $_index)
    {
        $_data = reset($_currentTag->_data);

        while ( $_index < count( $_breakline ) )
        {
            // ignore whitespace
            while (($_data !== false) && !($_data instanceof HtmlTag) && (trim($_data) == ''))
            {
                $_data = next($_currentTag->_data);
            }

            // get the next part of our breakline code
            $_part = $_breakline[ $_index ];

            $_index++;

            // regex match
            if ($_part[0] == '#')
            {
                if ($_data === false || ($_data instanceof HtmlTag))
                {
                    return false;
                }

                if (!preg_match($_part, $_data))
                {
                    return false;
                }

                $_data = next($_currentTag->_data);
            }

            // skip optional tag
            else if ($_part[0] == '?')
            {
                if ($_data instanceof HtmlTag)
                {
                    if (strcmp(substr($_part, 1), $_data->_name) == 0)
                    {
                        $_data = next($_currentTag->_data);
                    }
                }
            }

            // skip ahead to tag (and move into it)
            else if ($_part[0] == '!')
            {
                while ($_data !== false)
                {
                    if ($_data instanceof HtmlTag)
                    {
                        if (strcmp(substr($_part, 1), $_data->_name) == 0)
                        {
                            break;
                        }
                    }

                    $_data = next($_currentTag->_data);
                }

                if ($_data === false)
                {
                    return false;
                }

                $_currentTag = $_data;

                $_data = reset($_currentTag->_data);
            }

            // move out of tag
            else if ($_part[0] == '/')
            {
                if ($_currentTag->_parentTag === null)
                {
                    return false;
                }

                if (strcmp(substr($_part, 1), $_currentTag->_name) != 0)
                {
                    return false;
                }

                $_data = reset($_currentTag->_parentTag->_data);

                for (;;)
                {
                    if ($_data === $_currentTag)
                    {
                        break;
                    }

                    $_data = next($_currentTag->_parentTag->_data);
                }

                $_currentTag = $_currentTag->_parentTag;

                $_data = next($_currentTag->_data);
            }

            // move into tag
            else
            {
                if ($_data === false || !($_data instanceof HtmlTag))
                {
                    return false;
                }

                if ($_data->_name != $_part)
                {
                    return false;
                }

                $_currentTag = $_data;

                $_data = reset($_currentTag->_data);
            }
        }

        return true;
    }

    // marvin: process breaklines
    private static function ProcessBreakline(HtmlTag $_currentTag, array & $_breakline)
    {
        $_index = 0;

        $_testTag = new HtmlTag(null, 'test');

        $_testTag->_data[] = $_currentTag;

        if (self::BreaklineMatch($_testTag, $_breakline, $_index))
        {
            self::RemoveTag($_currentTag);

            return true;
        }

        $_breaklineFound = false;

        foreach ($_currentTag->_data as $_key => $_data)
        {
            if ($_breaklineFound)
            {
                unset($_currentTag->_data[ $_key ]);
            }
            else if ($_data instanceof HtmlTag)
            {
                if (self::ProcessBreakline($_data, $_breakline))
                {
                    $_breaklineFound = true;
                }
            }
        }

        return $_breaklineFound;
    }

    // marvin: remove embedded images (but keep external images)
    private static function RemoveEmbeddedImages(HtmlTag $_currentTag)
    {
        foreach ($_currentTag->_data as $_data)
        {
            if ($_data instanceof HtmlTag)
            {
                self::RemoveEmbeddedImages($_data);
            }
        }

        if ($_currentTag->_name == 'img')
        {
            if (preg_match('#src\s*=\s*[\'"](.*)[\'"]#U', $_currentTag->_parameters, $_matches))
            {
                if (filter_var($_matches[1], FILTER_VALIDATE_URL) !== false)
                {
                    return;
                }
            }

            self::RemoveTag($_currentTag);
        }
    }

    // marvin: find the last trailing whitespace tag or return null if no more trailing whitespace tags found
    private static function FindLastWhitespaceTag(HtmlTag $_currentTag)
    {
        if (!empty($_currentTag->_data))
        {
            $_data = end($_currentTag->_data);

            while ($_data !== false)
            {
                if ($_data instanceof HtmlTag)
                {
                    return self::FindLastWhitespaceTag($_data);
                }
                else
                {
                    if (ctype_space($_data) || (trim($_data) == '&nbsp;'))
                    {
                        $_data = prev($_currentTag->_data);
                    }
                    else
                    {
                        return null;
                    }
                }
            }
        }

        return $_currentTag;
    }

    // marvin: nuke trailing whitespace tags
    private static function TrimTrailingWhitespace(HtmlTag $_currentTag)
    {
        $_lastTag = self::FindLastWhitespaceTag($_currentTag);

        while (($_lastTag instanceof HtmlTag) && ($_lastTag->_name != 'img'))
        {
            self::RemoveTag($_lastTag);

            if ($_lastTag->_parentTag instanceof HtmlTag) {
                $_lastTag = self::FindLastWhitespaceTag($_lastTag->_parentTag);
            }

            if ($_lastTag === $_currentTag)
            {
                break;
            }
        }
    }

    // marvin: rebuild the html string from the tree
    private static function ReconstructHtml(HtmlTag $_currentTag)
    {
        $_contents = '<' . $_currentTag->_name;

        if (!empty($_currentTag->_parameters))
        {
            $_contents .= ' ' . $_currentTag->_parameters;
        }

        if ($_currentTag->_isSelfClosing)
        {
            $_contents .= ' />';
        }
        else
        {
            $_contents .= '>';

            if (!$_currentTag->_isSingletonTag)
            {
                foreach ($_currentTag->_data as $_data)
                {
                    if ($_data instanceof HtmlTag)
                    {
                        $_contents .= self::ReconstructHtml($_data);
                    }
                    else
                    {
                        $_contents .= $_data;
                    }
                }

                $_contents .= '</' . $_currentTag->_name . '>';
            }
        }

        return $_contents;
    }

}

//  marvin: a little class for our mini html parser
class HtmlTag
{
    public $_parentTag;
    public $_name;
    public $_parameters;
    public $_hasOpenTag;
    public $_hasCloseTag;
    public $_isSelfClosing;
    public $_isSingletonTag;
    public $_data;

    public function __construct(HtmlTag $parentTag = null, $name)
    {
        $this->_parentTag = $parentTag;
        $this->_name = $name;
        $this->_parameters = '';
        $this->_hasOpenTag = false;
        $this->_hasCloseTag = false;
        $this->_isSelfClosing = false;
        $this->_isSingletonTag = false;
        $this->_data = array();
    }
}

