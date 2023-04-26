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
 * @copyright      Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The Shared Amazon Library
 *
 * @author Varun Shoor
 */
class SWIFT_Amazon extends SWIFT_Library
{
    /**
     * Convert the SimpleXML Object to Array
     *
     * @author Varun Shoor
     *
     * @param SimpleXMLElement $_SimpleXMLElementObject The SimpleXMLElement Object Pointer
     *
     * @return array The Converted Array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function ConvertXMLObjectToArray(SimpleXMLElement $_SimpleXMLElementObject)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_resultsContainer = array();
        $_childrenCount    = $_SimpleXMLElementObject->count();
        $_elementName      = mb_strtolower($_SimpleXMLElementObject->getName());
        if ($_childrenCount == 0) {

            $_resultsContainer[$_elementName] = (string) $_SimpleXMLElementObject;
        } else {
            foreach ($_SimpleXMLElementObject->children(null, true) as $_ChildrenObject) {
                $_childrenSubCount    = $_ChildrenObject->count();
                $_childrenElementName = mb_strtolower($_ChildrenObject->getName());

                if ($_childrenSubCount == 0) {
                    $_resultsContainer[$_childrenElementName] = (string) $_ChildrenObject;
                } else {
                    if ($_childrenElementName == 'item') {
                        $_resultsContainer[$_childrenElementName][] = $this->ConvertXMLObjectToArray($_ChildrenObject);
                    } else {
                        $_resultsContainer[$_childrenElementName] = $this->ConvertXMLObjectToArray($_ChildrenObject);
                    }
                }
            }
        }

        return $_resultsContainer;
    }
}
