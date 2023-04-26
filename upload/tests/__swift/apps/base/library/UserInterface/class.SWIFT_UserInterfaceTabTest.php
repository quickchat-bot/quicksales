<?php
namespace Base\Library\UserInterface;

use Base\Models\User\SWIFT_UserOrganization;

class SWIFT_UserInterfaceTabTest extends \SWIFT_TestCase
{
    private function getUserInterfaceTabInstance()
    {
        $userInterface = $this->createMock(SWIFT_UserInterfaceControlPanel::class);
        $userInterface->method('GetIsClassLoaded')
            ->willReturn(true);
        $userInterface->method('GetTabCount')
            ->willReturn(1);

        $tab = new SWIFT_UserInterfaceTab($userInterface,'Test', 'test', 1);
        $this->assertInstanceOf(SWIFT_UserInterfaceTab::class, $tab);
        return $tab;
    }

    public function TextMultipleAutoCompleteProvider()
    {
        $dataset = [
            [['taginput_test' => 'test_value'], 'tagid="test_value">test_value'],
        ];

        for ($i=0 ; $i<strlen(SWIFT_UserOrganization::ALLOWED_CHARACTERS) ; $i++) {
            $tests = [
                [
                    ['taginput_test' => sprintf("what%ss up", SWIFT_UserOrganization::ALLOWED_CHARACTERS[$i])],
                        sprintf("tagid=\"what%ss up\">what%ss up", SWIFT_UserOrganization::ALLOWED_CHARACTERS[$i], SWIFT_UserOrganization::ALLOWED_CHARACTERS[$i])
                ],
            ];
            $dataset = array_merge($dataset, $tests);
        }
        return $dataset;
    }

    /**
     * @dataProvider TextMultipleAutoCompleteProvider
     * @param $input
     * @param $expected
     * @throws \SWIFT_Exception
     */
    public function testTextMultipleAutoComplete($input, $expected)
    {
        $tab = $this->getUserInterfaceTabInstance();
        $text = $tab->TextMultipleAutoComplete('Test', 'Test', 'Test', '',$input);
        $this->assertTrue(false !== strstr($text, $expected), sprintf("Got %s instead of %s", $expected,$text));
    }
}
