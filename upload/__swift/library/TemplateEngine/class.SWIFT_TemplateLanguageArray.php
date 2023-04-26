<?php

/**
 * Converts template language array to object to set default phrases language options
 *
 * @author Abdulrahman Suleiman <abdulrahman.suleiman@crossover.com>
 */
class SWIFT_TemplateLanguageArray implements ArrayAccess
{
    protected $languageArray;
    protected $languageArrayEN;

    function __construct($languageArray)
    {
        $this->languageArray = $languageArray;
        $this->loadLanguageEN();
    }

    public function loadLanguageEN()
    {
        $obj = new SWIFT_LanguageEngine(SWIFT_LanguageEngine::TYPE_DB, 'en-us', false, false);
        $this->languageArrayEN = is_array($obj->_phraseCache) ? $obj->_phraseCache : [];
    }

    public function offsetExists($offset)
    {
        return true;
    }

    public function offsetGet($offset)
    {
        return isset($this->languageArray[$offset])   ? $this->languageArray[$offset]   :
              (isset($this->languageArrayEN[$offset]) ? $this->languageArrayEN[$offset] : $offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->languageArray[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        if (isset($this->languageArray[$offset]))
            unset($this->languageArray[$offset]);
    }

}
