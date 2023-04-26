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
 * @license       http://kayako.com/license
 * @link          http://kayako.com
 *
 * ###############################################
 */

namespace Tickets\Library\UnifiedSearch;

use Knowledgebase\Admin\LoaderMock;
use SWIFT_Exception;
use SWIFT_Interface;

/**
 * Class UnifiedSearch_ticketsTest
 * @group tickets
 */
class UnifiedSearch_ticketsTest extends \SWIFT_TestCase
{
    /**
     * @throws SWIFT_Exception
     */
    public function testConstructorReturnsClassInstance()
    {
        $obj = $this->getMockObject('Tickets\Library\UnifiedSearch\SWIFT_UnifiedSearch_ticketsMock');
        $this->assertInstanceOf('Tickets\Library\UnifiedSearch\SWIFT_UnifiedSearch_tickets', $obj);
    }
}

class SWIFT_UnifiedSearch_ticketsMock extends SWIFT_UnifiedSearch_tickets
{

    public function __construct($services = [])
    {
        $this->Load = new LoaderMock();

        foreach ($services as $key => $service) {
            $this->$key = $service;
        }

        $this->SetIsClassLoaded(true);

        parent::__construct('query', SWIFT_Interface::INTERFACE_TESTS, \SWIFT::GetInstance()->Staff, 1);
    }

    public function Initialize()
    {
        // override
        return true;
    }
}

