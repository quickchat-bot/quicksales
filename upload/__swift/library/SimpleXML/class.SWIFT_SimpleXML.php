<?php
/**
 * @author         Varun Shoor <varun.shoor@opencart.com.vn>
 *
 * @package        SWIFT
 * @copyright      2001-2014 QuickSupport
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 */


/**
 * An extended version of SimpleXMLElement with support for chaining (in addAttribute() etc.), workaround for escaping &, CDATA implementation etc.
 *
 * This library is intended to eventually replace SWIFT_XML.
 *
 * @author Varun Shoor <varun.shoor@opencart.com.vn>
 */
class SWIFT_SimpleXML extends SimpleXMLElement
{
    /**
     * Add an attribute and return the element to support chaining of calls
     *
     * @author Varun Shoor <varun.shoor@opencart.com.vn>
     *
     * @param string $name      The name of the attribute to add.
     * @param string $value     (OPTIONAL) The value of the attribute.
     * @param string $namespace (OPTIONAL) If specified, the namespace to which the attribute belongs.
     *
     * @return SWIFT_SimpleXML
     */
    public function addAttribute($name, $value = null, $namespace = null)
    {
        parent::addAttribute($name, htmlspecialchars($value), $namespace);

        return $this;
    }

    /**
     * Adds CDATA text in a node
     *
     * @author Alexandre Feraud
     *
     * @param string $text
     *
     * @return SWIFT_SimpleXML
     */
    private function addCData($text)
    {
        $DOM  = dom_import_simplexml($this);
        $Node = $DOM->ownerDocument;
        $Node->appendChild($Node->createCDATASection($text));
    }

    /**
     * Adds a child element to the XML node..
     *
     * @author Varun Shoor <varun.shoor@opencart.com.vn>
     *
     * @param string $name      The name of the child element to add.
     * @param string $value     (OPTIONAL) If specified, the value of the child element.
     * @param string $namespace (OPTIONAL) If specified, the namespace to which the child element belongs.
     *
     * @return SWIFT_SimpleXML
     */
    public function addChild($name, $value = null, $namespace = null)
    {
        parent::addChild($name, htmlspecialchars($value), $namespace);

        return $this->$name;
    }

    /**
     * Creates a child element to the XML node and and adds the specified CDATA value to it.
     *
     * @author Alexandre Feraud
     *
     * @param string $name      The name of the child element to add.
     * @param string $text      The CDATA value of the child element.
     * @param string $namespace (OPTIONAL) If specified, the namespace to which the child element belongs.
     *
     * @return SWIFT_SimpleXML
     */
    public function addChildCData($name, $text, $namespace = null)
    {
        $ChildElement = $this->addChild($name, null, $namespace);
        $ChildElement->addCData($text);

        return $ChildElement;
    }
}