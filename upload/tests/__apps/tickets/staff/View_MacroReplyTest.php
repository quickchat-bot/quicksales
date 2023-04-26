<?php
/**
 * ###############################################
 *
 * QuickSupport Classic
 * _______________________________________________
 *
 * @author        Werner Garcia <werner.garcia@crossover.com>
 *
 * @package       swift
 * @copyright     Copyright (c) 2001-2018, Trilogy
 * @license       http://opencart.com.vn/license
 * @link          http://opencart.com.vn
 *
 * ###############################################
 */

namespace Tickets\Staff;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;

/**
 * Class View_MacroReplyTest
 * @group tickets
 * @group tickets-staff
 */
class View_MacroReplyTest extends \SWIFT_TestCase
{
    public static $prop = [];

    public function testRenderThrowsException()
    {
        $obj = $this->getMocked();

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
        ]);

        $_POST['tredir_ticketid'] = 1;
        $this->assertInvalidData($obj, 'Render', 2, null, 1, null, 1);
    }

    /**
     * @throws SWIFT_Exception
     */
    public function testRenderReturnsTrue()
    {
        $mockCache = $this->getMockBuilder('SWIFT_CacheStore')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $mockCache->method('Get')->willReturn([
            1 => [
                1 => [1],
                'departmentid' => 1,
                'tickettypeid' => 1,
                'staffvisibilitycustom' => 0,
                'priorityid' => 1,
                'ticketstatusid' => 1,
            ],
            2 => [
                'departmentid' => 2,
                'tickettypeid' => 2,
                'staffvisibilitycustom' => 1,
            ],
        ]);

        $obj = $this->getMocked([
            'Cache' => $mockCache,
        ]);

        $reply = $this->getMockBuilder('Tickets\Models\Macro\SWIFT_MacroReply')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $reply->method('GetIsClassLoaded')->willReturn(true);
        $reply->method('GetProperty')->willReturnCallback(function ($x) {
            if (!isset(static::$prop[$x])) {
                if (strtolower(substr($x, -2)) === 'id') {
                    static::$prop[$x] = 1;
                } else {
                    static::$prop[$x] = $x;
                }
            }

            return static::$prop[$x];
        });
        $post = $this->getMockBuilder('Tickets\Models\Ticket\SWIFT_TicketPost')
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        $post->method('GetIsClassLoaded')->willReturnOnConsecutiveCalls(false, true);
        $post->method('GetProperty')->willReturnCallback(function ($x) {
            if (!isset(static::$prop[$x])) {
                if (strtolower(substr($x, -2)) === 'id') {
                    static::$prop[$x] = 1;
                } else {
                    static::$prop[$x] = $x;
                }
            }

            return static::$prop[$x];
        });

        \SWIFT::GetInstance()->Staff->method('GetAssignedDepartments')->willReturn([1]);

        \SWIFT::GetInstance()->Database->method('QueryFetch')->willReturn([
            'ticketid' => 1,
            'iswatched' => 0,
            'lastpostid' => 0,
            'departmentid' => 1,
        ]);

        $settings = $this->getMockBuilder('SWIFT_Settings')
            ->disableOriginalConstructor()
            ->getMock();

        $settings->method('Get')->willReturnCallback(function ($x) {
            if (!isset(static::$prop[$x])) {
                if (strtolower(substr($x, -2)) === 'id') {
                    static::$prop[$x] = 1;
                } else {
                    if (false !== strpos($x, 'mail')) {
                        static::$prop[$x] = 'me@mail.com';
                    } else {
                        static::$prop[$x] = $x;
                    }
                }
            }

            return static::$prop[$x];
        });

        \SWIFT::GetInstance()->Settings = $settings;

        $_POST['_isDialog'] = 1;
        $_POST['tredir_ticketid'] = 1;

        $this->assertTrue($obj->Render(1, $reply, 1, $post, 1, 'inbox', 1, 1, 1, true));

        static::$prop['ownerstaffid'] = 0;
        $this->assertTrue($obj->Render(1, $reply, 1, $post, 1, 'inbox', 1, 1, 1, true));

        static::$prop['ownerstaffid'] = -2;
        $this->assertTrue($obj->Render(1, $reply, 1, $post, 1, 'inbox', 1, 1, 1, true));

        static::$prop['t_tinymceeditor'] = 0;
        $this->assertTrue($obj->Render(2));

        $obj->SetIsClassLoaded(false);
        $this->assertFalse($obj->Render(1));
    }

    /**
     * @param array $services
     * @return \PHPUnit_Framework_MockObject_MockObject|View_MacroReplyMock
     */
    private function getMocked(array $services = [])
    {
        return $this->getMockObject('Tickets\Staff\View_MacroReplyMock', $services);
    }
}

class View_MacroReplyMock extends View_MacroReply
{
    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct();
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

