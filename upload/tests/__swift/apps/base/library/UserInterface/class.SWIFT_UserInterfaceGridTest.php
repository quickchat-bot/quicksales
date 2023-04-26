<?php
namespace Base\Library\UserInterface;

class SWIFT_UserInterfaceGridTest extends \SWIFT_TestCase
{
    private function getUserInterfaceGridInstance()
    {
        $grid = new SWIFT_UserInterfaceGrid("test");
        $this->assertInstanceOf(SWIFT_UserInterfaceGrid::class, $grid);
        $grid->SetSearchQueryString("API");
        return $grid;
    }

    public function testSkipInlineImagesFromPostContentsSearchQuery()
    {
        $queryExpected = "postContents NOT REGEXP concat(char(60),'img[',char(94),char(62),']*API[',char(94),char(62),']*')";
        $grid = $this->getUserInterfaceGridInstance();
        $_SWIFT = \SWIFT::GetInstance();
        $_SWIFT->Database->method('Escape')->willReturnCallback(function ($x){ return $x;});
        $queryOutput = $grid->skipInlineImagesFromPostContentsSearchQuery("postContents");
        $this->assertEquals($queryExpected, $queryOutput);
    }
}
