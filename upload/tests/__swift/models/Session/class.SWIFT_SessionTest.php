<?php

class SWIFT_SessionTest extends SWIFT_TestCase
{
    /**
     * @throws Exception
     */
    public function testUpdateActivityCombined()
    {
        $mockObj = $this->getMocked();
        $updated = $mockObj->UpdateActivityCombined();
        $this->assertTrue($updated);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Controller_BreaklineMock
     */
    private function getMocked()
    {
        $mockRouter = $this->getMockBuilder('SWIFT_Router')
            ->disableOriginalConstructor()
            ->getMock();

        $mockRouter->method('GetAction')->willReturn('onlinestaff');
        SWIFT::GetInstance()->Router = $mockRouter;

        return $this->getMockObject('SWIFT_SessionMock');
    }
}

class SWIFT_SessionMock extends SWIFT_Session
{
    public function __construct()
    {
        $this->SetIsClassLoaded(true);
        $sessionData = ['sessionid' => 1, 'sessiontype' => SWIFT_Interface::INTERFACE_VISITOR];
        parent::__construct($sessionData);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}