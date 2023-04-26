<?php

namespace LiveChat\Library\Canned;

use Base\Models\Staff\SWIFT_Staff;
use LiveChat\Models\Canned\SWIFT_CannedCategory;
use PHPUnit\Framework\TestCase;

class SWIFT_CannedManagerTest extends TestCase
{
	public function providerDispatchXML()
	{
		$withCategories = <<<XML
<canned>
<category id="0" title="root">
<category id="1" title="Testing Canned">
</category>
<response id="1" title="Private response">
<message><![CDATA[This is the private response]]></message>
</response>
</category>
</canned>

XML;

		$empty = <<<XML
<canned>
<category id="0" title="root">
</category>
</canned>

XML;

		return [
			[2, SWIFT_CannedCategory::TYPE_PUBLIC, $withCategories],
			[1, SWIFT_CannedCategory::TYPE_PRIVATE, $withCategories],
			[2, SWIFT_CannedCategory::TYPE_PRIVATE, $empty],
		];
	}

	/**
	 * @dataProvider providerDispatchXML
	 * @param $staffId
	 * @param $public
	 * @param $expected
	 * @throws SWIFT_Canned_Exception
	 */
	public function testDispatchXML($staffId, $public, $expected)
	{
		$xmlMock = $this->getMockBuilder(\SWIFT_XML::class)
			->setMethodsExcept(['ReturnXML', 'AddParentTag', 'EndParentTag', 'AddTag', 'EndTag'])
			->getMock();

		$_SWIFT = \SWIFT::GetInstance();
		$_SWIFT->Staff = $this->getMockBuilder(SWIFT_Staff::class)
			->disableOriginalConstructor()
			->getMock();

		$_SWIFT->Staff
			->method('GetID')
			->willReturn(1);

		$reflection = new \ReflectionClass(SWIFT_CannedManager::class);
		$_cannedCategoryCache = $reflection->getProperty('_cannedCategoryCache');
		$_cannedCategoryCache->setAccessible(true);
		$_cannedCategoryCache->setValue([
			'_cannedCategoryContainer' => [
				[
					'cannedcategoryid' => '1',
					'parentcategoryid' => '0',
					'categorytype' => $public,
					'staffid' => $staffId,
					'title' => 'Testing Canned',
				],
			],
			'_cannedParentMap' =>
				[
					[
						[
							'cannedcategoryid' => '1',
							'parentcategoryid' => '0',
							'categorytype' => $public,
							'staffid' => $staffId,
							'title' => 'Testing Canned',
						],
					],
				],
		]);

		$_cannedResponseCache = $reflection->getProperty('_cannedResponseCache');
		$_cannedResponseCache->setAccessible(true);
		$_cannedResponseCache->setValue([
			'_cannedResponsesContainer' =>
				[
					[
						'cannedresponseid' => '1',
						'cannedcategoryid' => '1',
						'staffid' => $staffId,
						'title' => 'Private response',
						'urldata' => '',
						'imagedata' => '',
						'responsetype' => '3',
						'dateline' => '0',
						'contents' => 'This is the private response',
					],
				],
			'_responseParentMap' =>
				[
					[
						[
							'cannedresponseid' => '1',
							'cannedcategoryid' => '1',
							'staffid' => $staffId,
							'title' => 'Private response',
							'urldata' => '',
							'imagedata' => '',
							'responsetype' => '3',
							'dateline' => '0',
							'contents' => 'This is the private response',
						],
					],
				],
		]);

		$xmlMock->method('GetIsClassLoaded')
			->willReturn(true);

		$s = SWIFT_CannedManager::DispatchXML($xmlMock);

		$this->assertTrue($s);
		$this->assertEquals($expected, $xmlMock->ReturnXML());
	}
}
