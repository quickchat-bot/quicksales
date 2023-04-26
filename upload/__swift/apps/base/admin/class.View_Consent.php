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

namespace Base\Admin;

use Base\Models\Language\SWIFT_Language;
use Base\Models\PolicyLink\SWIFT_PolicyLink;
use Base\Library\UserInterface\SWIFT_UserInterface;
use SWIFT_View;

/**
 * The Consent View
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @author Arotimi Busayo
 */
class View_Consent extends SWIFT_View
{
    /**
     * Constructor
     *
     * @author Arotimi Busayo
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Render the Policy URL
     *
     * @author Arotimi Busayo
     * @return bool
     */
    public function Render()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_availableLanguageList = SWIFT_Language::GetAvailableLanguageList();
        $_policyLinks = SWIFT_PolicyLink::RetrievePolicyLinks();

        $this->UserInterface->Start(get_short_class($this), '/Base/Consent/Update', SWIFT_UserInterface::MODE_INSERT, false);
        $this->UserInterface->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle');
        $_GeneralTabObject = $this->UserInterface->AddTab($this->Language->Get('policyurl'), 'icon_form.gif', null, true);

        $_availableLanguageListSize = count($_availableLanguageList);

        for ($i = 0; $i < $_availableLanguageListSize; $i++) {
            $languageID = $_availableLanguageList[$i]['languageid'];
            $_GeneralTabObject->Text(
                'policy_' . $languageID,
                " (" . $_availableLanguageList[$i]['title'] . ")",
                '',
                (isset($_policyLinks[$languageID]) ? $_policyLinks[$languageID]['url'] : "")
            );
            $_GeneralTabObject->Radio('isdefault',
                $this->Language->Get('markdefault'),
                "",
                [$i => [
                    'checked' => (isset($_policyLinks[$languageID]) && $_policyLinks[$languageID]['isdefault'] > 0),
                    'value' => 'policy_' . $languageID, 'title' => ''
                ]]
            );
        }
        $this->UserInterface->End();
    }
}

?>
