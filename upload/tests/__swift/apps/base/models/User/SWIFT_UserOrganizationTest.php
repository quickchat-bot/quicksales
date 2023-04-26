<?php

namespace Base\Models\User;

use SWIFT_TestCase;

class SWIFT_UserOrganizationTest extends SWIFT_TestCase
{
  public function mergeListProvider()
  {
    return [
      [1, [1, 2]],
      [2, [1, 2]],
    ];
  }

  /**
   * Merge User Organizations
   *
   * @dataProvider mergeListProvider
   * 
   * @param int $_primaryOrganizationID The Primary Organization ID to Preserve
   * @param array $_userOrganizationIDList The User Organization ID List
   * @return bool "true" on Success, "false" otherwise
   * @throws SWIFT_Exception If Invalid Data is Provided
   */
  public function testMergeList($_primaryOrganizationID, $_userOrganizationIDList)
  {
    $cache = $this->createMock(\SWIFT_CacheStore::class);
    $cache->method('Get')
      ->will(self::returnValueMap([
        ['languagecache', [['languagecode' => 1, 'isdefault' => 1]]],
        ['templategroupcache', [['regusergroupid' => 1, 'tgroupid' => 1]]]
      ]));

    $db = $this->createMock(\SWIFT_Database::class);
    $db->method('Query')
      ->willReturn([[
        'userorganizationid' => '1',
        'userid' => '1',
      ]]);
    $db->method('NextRecord')
      ->will($this->onConsecutiveCalls(
        [[
          'userorganizationid' => '1',
          'userid' => '1'
        ]],
        false
      ));
    $db->method('AutoExecute')->willReturn(true);
    
    $_SWIFT = \SWIFT::GetInstance();
    $_SWIFT->Cache = $cache;
    $_SWIFT->Database = $db;

    $actual = SWIFT_UserOrganization::MergeList(
      $_primaryOrganizationID,
      $_userOrganizationIDList
    );

    $this->assertTrue($actual);
  }
}
