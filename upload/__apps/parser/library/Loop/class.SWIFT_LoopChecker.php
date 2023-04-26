<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

namespace Parser\Library\Loop;
use SWIFT_Exception;
use SWIFT_Library;
use Parser\Models\Loop\SWIFT_LoopBlock;
use Parser\Models\Loop\SWIFT_LoopHit;
use Parser\Models\Loop\SWIFT_LoopRule;

/**
 * Checks the Data for an active loop
 *
 * @author Varun Shoor
 */
class SWIFT_LoopChecker extends SWIFT_Library
{
    /**
     * Constructor
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check the email address against loop rules
     *
     * @author Varun Shoor
     *
     * @param string $_emailAddress The Email Address
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Check($_emailAddress)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        // Remove obsolete hits
        SWIFT_LoopHit::DeleteOnTime(SWIFT_LoopRule::GetMaxContactAge());

        // Remove obsolete blocks
        SWIFT_LoopBlock::DeleteObsolete();

        // Insert a new loop cutter hit
        SWIFT_LoopHit::Create($_emailAddress);

        if (SWIFT_LoopBlock::CheckIfAddressIsBlocked($_emailAddress)) {
            return true;
        } else {
            if (($_blockLength = $this->CheckEmailForLoop($_emailAddress)) !== false) {
                // @codeCoverageIgnoreStart
                SWIFT_LoopBlock::Create($_emailAddress, $_blockLength);
                return true;
                // @codeCoverageIgnoreEnd
            }
        }

        return false;
    }

    /**
     *  Call wrapper to check if an incoming email address is in loop state, and to signal response halt if so.
     *
     *  Checks an incoming mail for loop conditions, particularly to cut loops with autoresponders.  This prevents one known
     *  resource shortage condition.  Indirectly SQL dependant.  This is basically a data prefetching call wrapper for
     *  TestForEmailLoopWorker, to compartmentalize dataset changes, to keep the dataset out of the algorithm itself, and
     *  to facilitate straightforward testing.
     *
     * @author Varun Shoor
     *
     * @param string $_emailAddress The Email Address
     *
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function CheckEmailForLoop($_emailAddress)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }


        $_datelineHitList = SWIFT_LoopHit::RetrieveOnAddress($_emailAddress);
        $_loopRuleContainer = SWIFT_LoopRule::GetFrequencyThreshholds();

        return $this->CheckForEmailLoopWorker(DATENOW, $_datelineHitList, $_loopRuleContainer);
    }

    /**
     *
     *  Email loop cutter algorithm implementation.  Externally testable.  SQL independant.
     *
     *  Tests an incoming dataset representing email for loop conditions, particularly to cut loops with autoresponders.  This
     *  prevents one known resource shortage condition.  This is just the actual testing algorithm; the data to be tested is
     *  fetched upstream.  SQL independant.
     *
     * @author    John Haugeland
     * @since     0.1
     * @version   1
     * @access    public
     *
     * @param     int $_compareTime Timestamp to be used as comparison
     * @param     array $_datelineHitList      array(mktime(),...) Key-ignorant array of mktime() timestamps
     * @param     array $_loopRuleContainer    array(thresh,...) Array of arrays, internal arrays are k=>v maps of threshhold data, see GetEmailLoopFrequencyThreshholds()
     *
     * @return    boolean
     *
     * @todo      Speed may be improvable by sorting the contact times and frequency threshholds, then aborting inner loops to
     *             skip repeated unnessecary comparisons.  It remains unclear whether such optimization is worth the effort;
     *             this is low demand code.
     *
     * @todo      Set marker() profile
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    protected function CheckForEmailLoopWorker($_compareTime, &$_datelineHitList, &$_loopRuleContainer)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        foreach ($_datelineHitList as $_hitTimestamp) {                         // iterate over stored hit times

            $_timeDiff = $_compareTime - $_hitTimestamp;                        // cache difference between comparison and current stored time
            foreach ($_loopRuleContainer as &$_loopRule) {                          // iterate over threshholds (as reference so that internal member may be updated)

                if ($_timeDiff < $_loopRule['length']) {                          // if the timestamp difference is smaller than the length marker (ie, within the threshhold)
                    isset ($_loopRule['hit']) ? ++$_loopRule['hit'] : $_loopRule['hit'] = 1;        // if there's a hit counter, increment it; otherwise create it at value 1
                    if ($_loopRule['hit'] >= $_loopRule['maxhits']) {                       // if the hit counter is over tolerance,
                        return $_loopRule['restoreafter'];                               // return the length of the expected cut
                    }
                }

            }                                                           // otherwise try next threshhold
        }                                                           // otherwise try next timestamp

        return false;                                             // otherwise exit, returning false (that no cut was discovered)

    }
}

?>
