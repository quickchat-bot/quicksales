<?php

namespace Reports\Library\Render;

use Base\Library\KQL\SWIFT_KQLParserResult;
use Base\Library\KQL2\SWIFT_KQL2;
use Base\Library\KQL2\SWIFT_KQL2Compiler;
use SWIFT_Report;
use SWIFT_ReportRenderMatrix;
use SWIFT_TestCase;

class SWIFT_ReportRenderMatrixTest extends SWIFT_TestCase
{
	public function testCustomFieldAsGroupXName()
	{
		// Given
		$splitSQL = [
			"_cf_3:a:2:{i:0;s:1:\"1\";i:1;a:1:{i:1;s:1:\"2\";}}" => [
				"SELECT COUNT(*) AS 'No. of Tickets', SUM(customfield3.fieldvalue) AS 'Value of Tickets', departments.title AS 'departments_title', customfield3.fieldvalue AS '_cf_3', customfield3.isserialized AS '_cf_3_isserialized', customfield3.isencrypted AS '_cf_3_isencrypted' FROM swtickets AS tickets LEFT JOIN swusers AS users ON tickets.userid = users.userid LEFT JOIN swticketstatus AS ticketstatus ON tickets.ticketstatusid = ticketstatus.ticketstatusid LEFT JOIN swdepartments AS departments ON tickets.departmentid = departments.departmentid LEFT JOIN swcustomfieldvalues AS customfield3 ON customfield3.customfieldid = '3' AND customfield3.fieldvalue != '' AND customfield3.typeid = tickets.ticketid WHERE (ticketstatus.title != 'Closed' AND customfield3.fieldvalue = 'a:2:{i:0;s:1:\\\"1\\\";i:1;a:1:{i:1;s:1:\\\"2\\\";}}') AND tickets.departmentid IN ('2','4','5') GROUP BY `departments_title`",
				"SELECT COUNT(*) AS 'No. of Tickets', NULL AS 'Value of Tickets', '%grandtotalrowgroupbyexpression[0]%' AS 'departments_title', NULL AS '_cf_3', NULL AS '_cf_3_isserialized', NULL AS '_cf_3_isencrypted' FROM swtickets AS tickets LEFT JOIN swusers AS users ON tickets.userid = users.userid LEFT JOIN swticketstatus AS ticketstatus ON tickets.ticketstatusid = ticketstatus.ticketstatusid LEFT JOIN swdepartments AS departments ON tickets.departmentid = departments.departmentid LEFT JOIN swcustomfieldvalues AS customfield3 ON customfield3.customfieldid = '3' AND customfield3.fieldvalue != '' AND customfield3.typeid = tickets.ticketid WHERE (ticketstatus.title != 'Closed' AND customfield3.fieldvalue = 'a:2:{i:0;s:1:\\\"1\\\";i:1;a:1:{i:1;s:1:\\\"2\\\";}}') AND tickets.departmentid IN ('2','4','5')"
			]
		];
		$sqlResult = [
			[
				"No. of Tickets" => "2193",
				"Value of Tickets" => "0",
				"departments_title" => "General",
				"_cf_3" => "Test 1 \u00bb Test 1.1",
				"_cf_3_isserialized" => "1",
				"_cf_3_isencrypted" => "0"
			], [
				"No. of Tickets" => "2224",
				"Value of Tickets" => "0",
				"departments_title" => "Second Department",
				"_cf_3" => "Test 1 \u00bb Test 1.1",
				"_cf_3_isserialized" => "1",
				"_cf_3_isencrypted" => "0"
			], [
				"No. of Tickets" => "2172",
				"Value of Tickets" => "0",
				"departments_title" => "Third Department",
				"_cf_3" => "Test 1 \u00bb Test 1.1",
				"_cf_3_isserialized" => "1",
				"_cf_3_isencrypted" => "0"
			]
		];
		$groupByFields = [["departments.title", "departments_title"]];
		$sqlGroupByXFields = [["cf.3", "_cf_3", "customfield3.fieldvalue"]];
		$sqlDistinctValueContainer = [
			"_cf_3" => [
				"a:2:{i:0;s:1:\"1\";i:1;a:1:{i:1;s:1:\"2\";}}",
				"a:2:{i:0;s:1:\"1\";i:1;a:1:{i:1;s:1:\"3\";}}",
				"a:2:{i:0;s:1:\"1\";i:1;a:1:{i:1;s:1:\"4\";}}",
			]
		];
		$customFields = [
			"3" => [
				"id" => "3",
				"name" => "ktyscw0wy51w",
				"type" => "9",
				"title" => "Test Linked",
				"encrypt" => "0",
				"group_id" => "1",
				"group_title" => "Test Field Group",
				"table" => "tickets",
				"options" => [
					"1" => [
						"value" => "Test 1",
						"suboptions" => [
							"2" => ["value" => "Test 1.1"],
							"3" => ["value" => "Test 1.2"],
							"4" => ["value" => "Test 1.3"]
						]
					]
				]
			]
		];
		$dataContainer = [
			'_cf_3:a:2:{i:0;s:1:"1";i:1;a:1:{i:1;s:1:"2";}}' => [
				"title" => [
					"No. of Tickets" => [
						"No. of Tickets",
						false,
						false,
						false
					],
					"Value of Tickets" => [
						"Value of Tickets",
						false,
						false,
						false
					],
					"departments_title" => [
						"Department",
						3,
						[
							"1" => 3,
							"4" => 100
						],
						false
					],
					"_cf_3" => [
						"Test Linked",
						3,
						[
							"1" => 3
						],
						false
					],
					"_cf_3_isserialized" => [
						"isserialized",
						4,
						[
							"1" => 4
						],
						false
					],
					"_cf_3_isencrypted" => [
						"isencrypted",
						4,
						[
							"1" => 4
						],
						false
					]
				],
				"results" => [
					[
						"No. of Tickets" => "2193",
						"Value of Tickets" => "0",
						"departments_title" => "General",
						"_cf_3" => "a:2:[i:0;s:1:\"1\";i:1;a:1:[i:1;s:1:\"2\";]]",
						"_cf_3_isserialized" => "1",
						"_cf_3_isencrypted" => "0"
					],
					[
						"No. of Tickets" => "2224",
						"Value of Tickets" => "0",
						"departments_title" => "Second Department",
						"_cf_3" => "a:2:[i:0;s:1:\"1\";i:1;a:1:[i:1;s:1:\"2\";]]",
						"_cf_3_isserialized" => "1",
						"_cf_3_isencrypted" => "0"
					],
					[
						"No. of Tickets" => "2172",
						"Value of Tickets" => "0",
						"departments_title" => "Third Department",
						"_cf_3" => "a:2:[i:0;s:1:\"1\";i:1;a:1:[i:1;s:1:\"2\";]]",
						"_cf_3_isserialized" => "1",
						"_cf_3_isencrypted" => "0"
					],
					[
						"No. of Tickets" => "6589",
						"Value of Tickets" => null,
						"departments_title" => "%grandtotalrowgroupbyexpression[0]%",
						"_cf_3" => null,
						"_cf_3_isserialized" => null,
						"_cf_3_isencrypted" => null
					]
				]
			]
		];
		$baseUserFieldCount = 2;
		$baseUserFieldList = ["No. of Tickets", "Value of Tickets"];

		// When
		$kql2 = $this->getMockBuilder(SWIFT_KQL2::class)
			->disableOriginalConstructor()
			->getMock();
		$kql2->Compiler = $this->getMockBuilder(SWIFT_KQL2Compiler::class)
			->disableOriginalConstructor()
			->getMock();
		$kql2->Compiler->method('GetExpressionByColumnName')->willReturnOnConsecutiveCalls(
			["0" => 3, "1" => ["COUNT", [[1, [false, "*"]]]], "2" => 2, "4" => []],
			["0" => 3, "1" => ["SUM", [[2, ["tickets", false, "3"], 12]]], "2" => false, "4" => []],
			["0" => 3, "1" => ["COUNT", [[1, [false, "*"]]]], "2" => 2, "4" => []],
			["0" => 3, "1" => ["SUM", [[2, ["tickets", false, "3"], 12]]], "2" => false, "4" => []],
			["0" => 3, "1" => ["COUNT", [[1, [false, "*"]]]], "2" => 2, "4" => []],
			["0" => 3, "1" => ["SUM", [[2, ["tickets", false, "3"], 12]]], "2" => false, "4" => []],
			["0" => 3, "1" => ["COUNT", [[1, [false, "*"]]]], "2" => 2, "4" => []],
		);

		$report = $this->getMockBuilder(SWIFT_Report::class)
			->disableOriginalConstructor()
			->getMock();
		$report->method('GetIsClassLoaded')->willReturn(true);

		$kqlParser = $this->getMockBuilder(SWIFT_KQLParserResult::class)
			->disableOriginalConstructor()
			->getMock();
		$kqlParser->method('GetIsClassLoaded')->willReturn(true);
		$kqlParser->method('GetSQL')->willReturn($splitSQL);

		$obj = new SWIFT_ReportRenderMatrixProxy($kql2, $report, $kqlParser);
		$obj->setSqlResult($sqlResult);
		$obj->setGroupByFields($groupByFields);
		$obj->setSqlGroupByXFields($sqlGroupByXFields);
		$obj->setSqlDistinctValueContainer($sqlDistinctValueContainer);
		$obj->setCustomFields($customFields);
		$obj->setDataContainer($dataContainer);
		$obj->setBaseUserFieldCount($baseUserFieldCount);
		$obj->setBaseUserFieldList($baseUserFieldList);
		$actual = $obj->Render();

		// Then
		$this->assertTrue($actual);
		$this->assertStringContainsString('Test 1 » Test 1.1', $obj->getOutput());
	}
}

class SWIFT_ReportRenderMatrixProxy extends SWIFT_ReportRenderMatrix
{
	public function __construct($_SWIFT_KQL2Object, SWIFT_Report $_SWIFT_ReportObject, SWIFT_KQLParserResult $_SWIFT_KQLParserResultObject)
	{
		parent::__construct($_SWIFT_KQL2Object, $_SWIFT_ReportObject, $_SWIFT_KQLParserResultObject);
	}

	public function setSqlResult($r)
	{
		$this->_sqlResult = $r;
	}

	public function setGroupByFields($r)
	{
		$this->_sqlGroupByFields = $r;
	}

	public function setSqlGroupByXFields($r)
	{
		$this->_sqlGroupByXFields = $r;
	}

	public function setGroupMap($r)
	{
		$this->_groupMap = $r;
	}

	public function setSqlDistinctValueContainer($r)
	{
		$this->_sqlDistinctValueContainer = $r;
	}

	public function setCustomFields($r)
	{
		$this->_customFields = $r;
	}

	public function setDataContainer($r)
	{
		$this->_dataContainer = $r;
	}

	public function setBaseUserFieldCount($r)
	{
		$this->_baseUserFieldCount = $r;
	}

	public function setBaseUserFieldList($r)
	{
		$this->_baseUserFieldList = $r;
	}

	public function ProcessFieldValues()
	{
		return true;
	}

	public function getOutput()
	{
		return $this->_renderedOutput;
	}
}

