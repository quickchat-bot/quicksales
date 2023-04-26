<?php

class FunctionsTest extends SWIFT_TestCase
{
	public function ConvertTextUrlsToLinksProvider()
	{
		return [
            ['Email@domain.com', ['mail'], [], '<a href="mailto:Email@domain.com">Email@domain.com</a>'],
		    ['http://www.google.com', ['mail'], [], 'http://www.google.com'],
			['http://www.google.com', ['http'], [], '<a href="http://www.google.com">http://www.google.com</a>'],
			['https://www.google.com', ['http'], [], '<a href="https://www.google.com">https://www.google.com</a>'],
			['www.google.com', ['http'], [], '<a href="http://www.google.com">www.google.com</a>'],
			['<a href="https://www.google.com">Google</a>', ['http'], [], '<a href="https://www.google.com">Google</a>'],
			['Some text with a link www.google.com', ['http'], [], 'Some text with a link <a href="http://www.google.com">www.google.com</a>'],
			['<img src="https://www.kayako.com/image.jpg" />', ['http'], [], '<img src="https://www.kayako.com/image.jpg" />'],
			['<img src="www.kayako.com/image.jpg" />', ['http'], [], '<img src="www.kayako.com/image.jpg" />'],
			['some.mail@domain.com', ['http'], [], 'some.mail@domain.com'],
			['some.mail@domain.com', ['mail'], [], '<a href="mailto:some.mail@domain.com">some.mail@domain.com</a>'],
            		['<p><span>some.mail@domain.com</span></p>', ['mail'], [], '<p><span><a href="mailto:some.mail@domain.com">some.mail@domain.com</a></span></p>'],
			['@handler', ['twitter'], [], '<a  href="https://twitter.com/handler">@handler</a>'],
			['http://www.google.com', ['http'], ['data-test' => 'Test'], '<a  data-test="Test" href="http://www.google.com">http://www.google.com</a>'],
			['http://www.google.com', ['http'], ['data-test' => 'Test', 'data-second' => 'Second'], '<a  data-test="Test" data-second="Second" href="http://www.google.com">http://www.google.com</a>'],
		];
	}

	/**
	 * @dataProvider ConvertTextUrlsToLinksProvider
	 *
	 * @param $value
	 * @param $protocols
	 * @param $attributes
	 * @param $expected
	 */
	public function testConvertTextUrlsToLinks($value, $protocols, $attributes, $expected)
	{
		$actual = ConvertTextUrlsToLinks($value, $protocols, $attributes);
		$this->assertEquals($expected, $actual);
	}

    public function StripScriptTagsProvider()
    {
        return [
            ['<body onload=blabla style="background-color:blue">', '<body  style="background-color:blue">'],
            ['<div onclick="...." style="background-color:blue" onmouseover="....">', '<div  style="background-color:blue" >'],
['<img onclick="...." style="background-color:blue" src="whatever" onmouseover="...." />', '<img  style="background-color:blue" src="whatever"  />'],
['<img src="data:image/bmp;base64,Qk22AAAAAAAAADYAAAAoAAAACgAAAAQAAAABABgAAAAAAIAAAAAAAAAAAAAAAAAAAAAAAAAA////////////////////////////////////////AAD///+WkvYkHO3///+p2pVMsSL////np6PMSD////8AAP///7Sy+ZaS9v///8LktKnalf///+7Bveeno////wAA////////////////////////////////////////AAA="
 onclick="....."
 onhover="...."
 onload="...."
 alt="rgb" />', '<img src="data:image/bmp;base64,Qk22AAAAAAAAADYAAAAoAAAACgAAAAQAAAABABgAAAAAAIAAAAAAAAAAAAAAAAAAAAAAAAAA////////////////////////////////////////AAD///+WkvYkHO3///+p2pVMsSL////np6PMSD////8AAP///7Sy+ZaS9v///8LktKnalf///+7Bveeno////wAA////////////////////////////////////////AAA="
 
 
 
 alt="rgb" />'],
            ['< p title="<img src=x onerror=prompt(document.domain)>">xss</p>', '< p title="<img src=x >">xss</p>'],
            ['< p title="<img src=x onerror = prompt(document.domain)>">xss</p>', '< p title="<img src=x >">xss</p>'],
            ['&lt; p title=&#34;&lt;img src=x onerror=prompt(document.domain)&gt;&#34;&gt;', '&lt; p title=&#34;&lt;img src=x &gt;&#34;&gt;'],
            ['<p>This is a paragraph with onerror&#61;something </p>', '<p>This is a paragraph with onerror&#61;something </p>'],
        ];
    }

    public function TextToHtmlEntitiesProvider()
    {
        return [
            ["&lt;A href=\"https://wwww.w3schools.com\"&gt; Visit W3school", false, "<a href=\"https://wwww.w3schools.com\"> Visit W3school</a>"],
            ["&amp;lt;A href=\"https://wwww.w3schools.com\"&amp;gt; Visit W3school", false, "<a href=\"https://wwww.w3schools.com\"> Visit W3school</a>"],
	        ["<A href=\"https://wwww.w3schools.com\"> Visit W3school", false, "<a href=\"https://wwww.w3schools.com\"> Visit W3school</a>"],
            ["<IMG SRC=javascript:alert(&quot;XSS&quot;)>", false, ""],
            ["<IMG SRC=javascript:alert(&amp;quot;XSS&amp;quot;)>", false, ""],

            ["&lt;A href=\"https://wwww.w3schools.com\"&gt; Visit W3school", "<a>", " Visit W3school"],
            ["&amp;lt;A href=\"https://wwww.w3schools.com\"&amp;gt; Visit W3school", "<a>", " Visit W3school"],
        ];
    }

    /**
     * @dataProvider StripScriptTagsProvider
     *
     * @param $html
     * @param $expected
     */
    public function testStripScriptTagsReturnsCleanHtml($html, $expected)
    {
        $actual = StripScriptTags($html);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider TextToHtmlEntitiesProvider
     *
     * @param $input
     * @param $expected
     */
    public function testTextToHtmlEntities($input, $strip, $expected)
    {
        $actual = text_to_html_entities($input, $strip);
        $this->assertEquals($expected, $actual);
    }

    public function testTextToHtmlEntitiesWithQuotes()
    {
        $input = '"autofocus onx=() onfocus="(prompt)()';
        $expected = "'autofocus onx=() onfocus='(prompt)()";
        $actual = text_to_html_entities($input, 1, true, true);
        $this->assertEquals($expected, $actual);
    }
}
