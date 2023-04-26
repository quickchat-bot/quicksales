<?php
namespace Base\Library\HTML;

class SWIFT_HTMLPurifierTest extends \SWIFT_TestCase
{
    /**
     *
     */
    private function getReflection()
    {
        $reflection = new \ReflectionClass(SWIFT_HTMLPurifier::class);

        $obj = $reflection->getProperty('HTMLPurifierObject');
        $obj->setAccessible(true);

        $config = $reflection->getProperty('HTMLPurifierConfig');
        $config->setAccessible(true);

        $method = $reflection->getMethod('getHtmlPurifierConfig');
        $method->setAccessible(true);

        return [$obj, $config, $method];
    }

    public function purifierConfigProvider()
    {
        return [
            [null, null, true, [['t_allowableadvtags', 'div'], ['t_allowableadvtagsattributes', 'class']], null],
            [false, null, true, [['t_allowableadvtags', 'div'], ['t_allowableadvtagsattributes', 'class']], null],
            ['div', null, true, [['t_allowableadvtags', 'div'], ['t_allowableadvtagsattributes', '']], 'div,div'],
            ['div', null, true, [['t_allowableadvtags', 'div'], ['t_allowableadvtagsattributes', 'class']], 'div,div,div[class],div[class]'],
            ['a', null, true, [['t_allowableadvtags', 'div'], ['t_allowableadvtagsattributes', 'class']], 'div,a,div[class],a[class]'],
            ['a', 'href', true, [['t_allowableadvtags', 'div'], ['t_allowableadvtagsattributes', 'class']], 'div,a,div[class],a[class|href]'],
            ['a', 'href', false, [['t_allowableadvtags', 'div'], ['t_allowableadvtagsattributes', 'class']], null],
        ];
    }

    public function purifierProvider()
    {
        return [
            // Test the default settings
            [false, false, '<div>Test</div>', '<div>Test</div>'],
            [false, false, "<div onclick=\"alert('hello');\">Test</div>", '<div>Test</div>'],
            [false, false, "<script>Test</script>", ''],
            [false, false, "<a href='https://www.google.com'>Test</a>", "<a href=\"https://www.google.com\">Test</a>"],
            [false, false, "<embed>Test</embed>", 'Test'],
            [false, false, "<SCRIPT SRC=http://xss.rocks/xss.js></SCRIPT>", ''],
            [false, false, "<IMG SRC=\"javascript:alert('XSS');\">", ''],
            [false, false, "<IMG SRC=javascript:alert(&quot;XSS&quot;)>", ''],
            [false, false, "<IMG SRC=`javascript:alert(\"RSnake says, 'XSS'\")`>", '<img src="%60javascript%3Aalert(" alt="`javascript:alert(&quot;RSnake" />'], // Ugly HTML, but still valid
            [false, false, "<IMG SRC=\"someimage.jpg\">", '<img src="someimage.jpg" alt="someimage.jpg" />'],
            [false, false, "<IMG SRC=# onmouseover=\"alert('xxs')\">", '<img src="#" alt="#" />'],
            [false, false, "<IMG SRC=&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#88;&#83;&#83;&#39;&#41;>", ''],
            [false, false, "<IMG SRC=&#0000106&#0000097&#0000118&#0000097&#0000115&#0000099&#0000114&#0000105&#0000112&#0000116&#0000058&#0000097&#0000108&#0000101&#0000114&#0000116&#0000040&#0000039&#0000088&#0000083&#0000083&#0000039&#0000041>", ''],
            [false, false, "<IMG SRC=&#x6A&#x61&#x76&#x61&#x73&#x63&#x72&#x69&#x70&#x74&#x3A&#x61&#x6C&#x65&#x72&#x74&#x28&#x27&#x58&#x53&#x53&#x27&#x29>", ''],
            [false, false, "<svg/onload=alert('XSS')>", ''],

            // Test overriding the allowed tags
            ['a', false, "<div>Test</div>", 'Test'],
            ['div', false, "<div>Test</div>", '<div>Test</div>'],
            ['div', false, "<div class='abc'>Test</div>", '<div>Test</div>'],
            ['div', 'class', "<div class='abc'>Test</div>", '<div class="abc">Test</div>'],
            ['embed', false, "<embed>Test</embed>", 'Test'], // We will never allow to embed
            ['iframe', 'src', "<iframe src=\"data:text/html;base64,PHNjcmlwdD5hbGVydCgiaGVlbCIpOzwvc2NyaXB0Pg==\"></iframe>", ''], // Non safe locations will be handled
            ['frame,frameset', 'src', "<FRAMESET><FRAME SRC=\"javascript:alert('XSS');\"></FRAMESET>", ''],
            ['iframe', 'src', "<iframe src=\"https://www.youtube.com/watch?v=WNOBl1mtsMY\"></iframe>", '<iframe src="https://www.youtube.com/watch?v=WNOBl1mtsMY"></iframe>'], // Youtube is a safe place
        ];
    }

    public function testConstructor()
    {
        list($purifierObj, $purifierConfig) = $this->getReflection();

        $purifier = new SWIFT_HTMLPurifier();
        $this->assertInstanceOf(SWIFT_HTMLPurifier::class, $purifier);
        $this->assertInstanceOf(\HTMLPurifier::class, $purifierObj->getValue($purifier));
        $this->assertInstanceOf(\HTMLPurifier_Config::class, $purifierConfig->getValue($purifier));
    }

    /**
     * @dataProvider purifierConfigProvider
     * @param $_overrideAllowableTags
     * @param $_overrideAllowableAttrs
     * @param $allowHtml
     * @param $tags
     * @param $expected
     * @throws \SWIFT_Exception
     */
    public function testPurifierConfig($_overrideAllowableTags , $_overrideAllowableAttrs, $allowHtml, $tags, $expected)
    {
        $settings = $this->createMock(\SWIFT_Settings::class);
        $settings->method('GetBool')
            ->willReturn($allowHtml);
        $settings->method('Get')
            ->will($this->returnValueMap($tags));
        \SWIFT::GetInstance()->Settings = $settings;

        list($purifierObj, $purifierConfig, $getHtmlPurifierConfigMethod) = $this->getReflection();
        $purifier = new SWIFT_HTMLPurifier();

        $this->assertInstanceOf(SWIFT_HTMLPurifier::class, $purifier);
        $this->assertInstanceOf(\HTMLPurifier::class, $purifierObj->getValue($purifier));
        $this->assertInstanceOf(\HTMLPurifier_Config::class, $purifierConfig->getValue($purifier));

        $config = $getHtmlPurifierConfigMethod->invoke($purifier, $_overrideAllowableTags , $_overrideAllowableAttrs);
        // This tests the overrides
        $this->assertEquals($expected, $config->get('HTML.Allowed'));
    }

    /**
     * @dataProvider purifierProvider
     * @param $input
     * @param $allowableTags
     * @param $allowableAttrs
     * @param $expected
     * @throws \SWIFT_Exception
     */
    public function testPurifier($allowableTags, $allowableAttrs, $input, $expected)
    {
        $settings = $this->createMock(\SWIFT_Settings::class);
        $settings->method('GetBool')
            ->willReturn(true);
        \SWIFT::GetInstance()->Settings = $settings;

        $purifierReflection = new \ReflectionProperty(SWIFT_HTMLPurifier::class, 'cache');
        $purifierReflection->setAccessible(true);
        $purifierReflection->setValue([]);

        $purifier = new SWIFT_HTMLPurifier();
        $actual = $purifier->purify($input, $allowableTags, $allowableAttrs);
        $this->assertEquals($expected, $actual);
    }
}
