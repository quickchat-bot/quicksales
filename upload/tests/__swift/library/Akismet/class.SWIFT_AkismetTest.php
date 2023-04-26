<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */


/**
 * Class AkismetTest
 * @group akismet
 */
class SWIFT_AkismetTest extends SWIFT_TestCase
{

    public function testConstruct()
    {
        $_SWIFT = SWIFT::GetInstance();
        $_key = $_SWIFT->Settings->Get('security_akismetkey');
        $_is_akismet_enabled = (bool)$_SWIFT->Settings->Get('security_enableakismet') && !empty($_key);

        if (!$_is_akismet_enabled) {
            $this->setExpectedException('SWIFT_Exception');
        }
        $akismetObject = new SWIFT_Akismet();
        $this->assertInstanceOf('SWIFT_Akismet', $akismetObject);
    }

    public function testCheck()
    {
        $stub = $this->getMockBuilder('SWIFT_Akismet')
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock();

        $stub->method('Check')
            ->will($this->returnCallback(function ($name, $email, $content) {
                $result = false !== strpos($content, 'cialis');
                return $result;
            }));

        $this->assertTrue($stub->Check('Werner', 'werner@xo.com', 'buy cialis'));
        $this->assertFalse($stub->Check('Ivan', 'ivan@xo.com', 'normal comment'));
    }

    public function testMarkAsHam()
    {
        $stub = $this->getMockBuilder('SWIFT_Akismet')
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock();

        $stub->method('MarkAsHam')
            ->willReturn(true);

        $this->assertTrue($stub->MarkAsHam('Ivan', 'ivan@xo.com', 'normal comment',
            'Fusion/4.91.2 | Akismet/1.11',
            'http://localhost'));
    }

    public function testMarkAsSpam()
    {
        $stub = $this->getMockBuilder('SWIFT_Akismet')
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->getMock();

        $stub->method('MarkAsSpam')
            ->willReturn(true);

        $this->assertTrue($stub->MarkAsSpam('Werner', 'werner@xo.com', 'buy cialis',
            'Fusion/4.91.2 | Akismet/1.11',
            'http://localhost'));
    }
}
