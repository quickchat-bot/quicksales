<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Varun Shoor
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, Kayako
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

/**
 * The Default Console Controller
 *
 * @property SWIFT_XML $XML
 * @method _DispatchError($_msg = '')
 * @method _DispatchConfirmation()
 * @method _LoadTemplateGroup($_templateGroupName = '')
 * @method RebuildCache()
 * @method GetInfo()
 * @method bool _ProcessNews()
 * @method bool _ProcessKnowledgebaseCategories()
 * @author Varun Shoor
 */
class Controller_Default extends Controller_console
{
     /**
     * The Default Console Controller
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);

            return false;
        }

        if (SWIFT_App::IsInstalled(APP_PARSER)) {
            $this->Load->Controller('Parse', APP_PARSER)->Load->Index();

            return true;
        }

        $this->Console->WriteLine('====================', false, SWIFT_Console::COLOR_GREEN);
        $this->Console->WriteLine('SWIFT Framework', false, SWIFT_Console::COLOR_YELLOW);
        $this->Console->WriteLine('====================', false, SWIFT_Console::COLOR_GREEN);
        $this->Console->WriteLine();

    }
}
