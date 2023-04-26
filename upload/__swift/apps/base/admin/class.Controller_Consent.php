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
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

namespace Base\Admin;

use Controller_admin;
use SWIFT_Exception;
use Base\Models\Language\SWIFT_Language;
use Base\Models\PolicyLink\SWIFT_PolicyLink;

/**
 * The User Consent Controller
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property View_Consent $View
 * @author Arotimi Busayo
 */
class Controller_Consent extends Controller_admin
{
    // Core Constants
    const MENU_ID = 1;
    const NAVIGATION_ID = 0;

    /**
     * Constructor
     *
     * @author Arotimi Busayo
     */
    public function __construct()
    {
        parent::__construct();

        $this->Language->Load('admin_consent');
        $this->Language->Load('settings');
    }

    /**
     * Destructor
     *
     * @author Arotimi Busayo
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Displays the Policy URL forms
     *
     * @author Arotimi Busayo
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Index()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $this->UserInterface->Header($this->Language->Get('policyurl'), self::MENU_ID, self::NAVIGATION_ID);
        $this->View->Render();

        $this->UserInterface->Footer();

        return true;
    }

    /**
     * @author Arotimi Busayo
     */
    public function update()
    {
        $_availableLanguageIDs = SWIFT_Language::GetAvailableLanguageList(true);

        $this->UserInterface->Header($this->Language->Get('policyurl'), self::MENU_ID, self::NAVIGATION_ID);

        $validationError = false;
        $validatedData = [];

        foreach ($_availableLanguageIDs as $id) {
            if (isset($_POST['policy_' . $id])) {
                $_isDefault = isset($_POST['isdefault']) && ($_POST['isdefault'] == 'policy_' . $id);
                if (empty($_POST['policy_' . $id]) && $_isDefault) {

                    $this->UserInterface->DisplayError($this->Language->Get('defaultpolicyempty'), $this->Language->Get('desc_defaultpolicyempty'));
                    $validationError = true;
                    break;

                } elseif (!empty($_POST['policy_' . $id]) && !preg_match("@^(?:https?)://(?:[-.\w]+(?:\.[\w]{2,6})?|[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3})(?::[\d]{1,5})?(?:[/\\\\][-=\w_?&%+$.!*'()/\\\\]+)?@i", $_POST['policy_' . $id])) {

                    $this->UserInterface->DisplayError($this->Language->Get('titleurlinvaliddata'), $this->Language->Get('desc_titleurlinvaliddata') . ' ' . $_POST['policy_' . $id]);
                    $validationError = true;
                    break;
                }
                $validatedData[] = [
                    'languageid' => $id,
                    'url' => $_POST['policy_' . $id],
                    'isDefault' => (int)$_isDefault
                ];
            }
        }

        if (!$validationError) {
            foreach ($validatedData as $data) {
                if (SWIFT_PolicyLink::CheckPolicyExists($data['languageid'])) {
                    SWIFT_PolicyLink::UpdatePolicyLink($data['languageid'], $data['url'], $data['isDefault']);
                } else {
                    SWIFT_PolicyLink::Create($data['languageid'], $data['url'], $data['isDefault']);
                }
            }
            $this->UserInterface->DisplayInfo($this->Language->Get('policyurlupdatetitle'), $this->Language->Get('policyurlupdatemessage'));
        }

        $this->View->Render();

        $this->UserInterface->Footer();
    }
}

?>
