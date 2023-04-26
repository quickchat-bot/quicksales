<?php
namespace Base\Library\UserInterface;

use Base\Models\User\SWIFT_UserOrganization;

class SWIFT_UserInterfaceTest extends \SWIFT_TestCase
{
    public function GetMultipleInputValuesProvider()
    {
        $dataset = [
            ['test', false, false, [], false],
            ['test', false, false, ['taginput_test' => 'test_value'], ['test_value']],
        ];

        for ($i=0 ; $i<strlen(SWIFT_UserOrganization::ALLOWED_CHARACTERS) ; $i++) {
            $tests = [
                ['test', false, false,
                    ['taginput_test' => sprintf("first with%s char", SWIFT_UserOrganization::ALLOWED_CHARACTERS[$i])],
                    [sprintf("first with%s char", SWIFT_UserOrganization::ALLOWED_CHARACTERS[$i])]
                ],
                ['test', true, false,
                    ['taginput_test' => sprintf("second with%s char", SWIFT_UserOrganization::ALLOWED_CHARACTERS[$i])],
                    [sprintf("second with%s char", SWIFT_UserOrganization::ALLOWED_CHARACTERS[$i])]
                ],
                ['test', true, false,
                    ['taginput_test' => sprintf("third with%c char", SWIFT_UserOrganization::ALLOWED_CHARACTERS[$i])],
                    ["third with char"]
                ],
            ];
            $dataset = array_merge($dataset, $tests);
        }
        return $dataset;
    }

    /**
     * @dataProvider GetMultipleInputValuesProvider
     * @param $fieldname
     * @param $isCheckBox
     * @param $isEmail
     * @param $postField
     * @param $expected
     */
    public function testGetMultipleInputValues($fieldname, $isCheckBox, $isEmail, $postField, $expected)
    {
        $_POST = $postField;
        $actual = SWIFT_UserInterface::GetMultipleInputValues($fieldname, $isCheckBox, $isEmail);
        $this->assertEquals($expected, $actual);
    }
}
