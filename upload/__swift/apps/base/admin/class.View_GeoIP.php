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

use SWIFT;
use SWIFT_Date;
use Base\Models\GeoIP\SWIFT_GeoIP;
use Base\Models\GeoIP\SWIFT_GeoIPCity;
use Base\Models\GeoIP\SWIFT_GeoIPInternetServiceProvider;
use Base\Models\GeoIP\SWIFT_GeoIPNetSpeed;
use Base\Models\GeoIP\SWIFT_GeoIPOrganization;
use Base\Library\Help\SWIFT_Help;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use SWIFT_View;

/**
 * The GeoIP View Management Class
 *
 * @property \Base\Library\UserInterface\SWIFT_UserInterfaceControlPanel $UserInterface
 * @property mixed $_geoIPCityReturn
 * @property mixed $_geoIPCityBlocksReturn
 * @property mixed $_geoIPNetSpeedReturn
 * @property mixed $_geoIPISPReturn
 * @property mixed $_geoIPOrganizationReturn
 * @author Varun Shoor
 */
class View_GeoIP extends SWIFT_View
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
     * Render the GeoIP Tabs
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    public function Render()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        // Calculate the URL
        $this->UserInterface->Start(get_short_class($this), '/Base/GeoIP/Process', SWIFT_UserInterface::MODE_INSERT, false, false, 'javascript: return false;');

        $this->RenderCityLocations();

        $this->RenderCityBlocks();

        $this->RenderNetSpeed();

        $this->RenderISP();

        $this->RenderOrganization();

        $this->UserInterface->End();

        return true;
    }

    /**
     * Render the GeoIP Result
     *
     * @author Varun Shoor
     * @param string $_geoIPType The GeoIP Type
     * @param string $_redirectURL The Redirect URL
     * @param string $_javascriptFunctionName The Javascript Function Name to Execute
     * @param int $_percent The Percentage Completed
     * @param array $_result The Result Container
     * @param int $_refreshCount The Refresh Count
     * @param int $_fileSize The File Size
     * @param int $_offset The Offset
     * @param int $_startTime THe Start Time
     * @param int $_uniqueID The Unique ID for this Instance
     * @return bool "true" on Success, "false" otherwise
     */
    public function RenderResult($_geoIPType, $_redirectURL, $_javascriptFunctionName, $_percent, $_result, $_refreshCount, $_fileSize, $_offset, $_startTime, $_uniqueID)
    {
        echo '<table cellpadding="0" cellspacing="0" border="0" width="100%" class="gridlayoutborder">' . SWIFT_CRLF;
        echo '<tbody><tr><td class="gridcontentborder">';
        echo '<table width="100%" border="0" cellspacing="1" cellpadding="4">';

        echo '<tr><td align="center" valign="top" class="rowhighlight" colspan="4" nowrap>';
        echo '<span class="tabletitle">' . number_format($_percent, 2) . '%</span>' . IIF($_redirectURL, '<br /><img src="' . SWIFT::Get('themepath') . 'images/barloadingdark.gif" align="absmiddle" border="0" />') . '<BR />';

        if ($_percent >= 100) {
            echo $this->Language->Get('completed');
        } else {
            echo $this->Language->Get('geoiploadingalert');
        }
        echo '</td></tr>';

        $_totalRefreshCount = 0;
        $_refreshCountRemaining = 0;

        if ($_result[0] != -1) {
            $_bytesPerRefresh = $_result[0] / $_refreshCount;

            if ($_bytesPerRefresh) {
                $_totalRefreshCount = $_fileSize / $_bytesPerRefresh;
                $_refreshCountRemaining = $_totalRefreshCount - $_refreshCount;
            }
        }

        $_sizeRemaining = $_fileSize - $_result[0];
        if ($_offset) {
            $_averageTime = (DATENOW - $_startTime) / $_refreshCount;
        } else {
            $_averageTime = 0;
        }
        $_timeRemaining = $_refreshCountRemaining * $_averageTime;

        if ($_result[0] == -1) {
            $_processedSize = $_fileSize;
        } else {
            $_processedSize = (int)($_result[0]);
        }

        echo '<tr><td colspan="4" align="left" valign="top" class="settabletitlerowmain2">' . $this->Language->Get('generalinformation') . '</td></tr>';

        echo '<tr><td align="left" valign="top" class="gridrow1">' . $this->Language->Get('sizeprocessed') . '</td><td align="left" valign="top" class="gridrow2">' . FormattedSize($_processedSize, false, 2) . '</td><td align="left" valign="top" class="gridrow1">' . $this->Language->Get('totalsize') . '</td><td align="left" valign="top" class="gridrow2">' . FormattedSize($_fileSize, false, 2) . '</td></tr>';

        echo '<tr><td align="left" valign="top" class="gridrow1">' . $this->Language->Get('timeelapsed') . '</td><td align="left" valign="top" class="gridrow2">' . SWIFT_Date::ColorTime(DATENOW - $_startTime) . '</td><td align="left" valign="top" class="gridrow1">' . $this->Language->Get('timeremaining') . '</td><td align="left" valign="top" class="gridrow2">' . SWIFT_Date::ColorTime($_timeRemaining, true) . '</td></tr>';

        echo '</table></td></tr></tbody></table>';

        if ($_redirectURL) {
            echo '<script type="text/javascript">$("#geoipparent_' . $_geoIPType . '_' . $_uniqueID . '").load("' . $_redirectURL . '");</script>';
        } else {
            echo '<script type="text/javascript">$("#dialoggeoip' . $_geoIPType . '").slideUp("medium"); $("#dialoggeoip' . $_geoIPType . '").hide(); RemoveActiveSWIFTAction("geoip' . $_geoIPType . '"); ChangeTabLoading(\'View_GeoIP\', \'tab' . $_geoIPType . '\', \'icon_form.gif\');</script>';
        }

        return true;
    }

    /**
     * Render the Organization Tab
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function RenderOrganization()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_geoIPOrganizationReturn = SWIFT_GeoIPOrganization::GetFile();
        $_organizationUniqueHash = substr(buildHash(), 0, 10);

        $this->_geoIPOrganizationReturn = $_geoIPOrganizationReturn;

        $_OrganizationTabObject = $this->UserInterface->AddTab($this->Language->Get('taborganization'), 'icon_form.gif', 'taborganization');

        $_OrganizationTabObject->LoadToolbar();

        if (!defined('SWIFT_GEOIP_SERVER')) {
            $_OrganizationTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle', 'startGeoIPMaintenance(\'organization\', \'' . $_organizationUniqueHash . '\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        }

        $_OrganizationTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('geoip'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        if (defined('SWIFT_GEOIP_SERVER')) {
            $_OrganizationTabObject->Info($this->Language->Get('titlegeoipremote'), $this->Language->Get('msggeoipremote'));
        } elseif ($_geoIPOrganizationReturn) {
            if (!$this->Settings->GetKey('geoip', 'organization')) {
                $_OrganizationTabObject->Alert($this->Language->Get('titlenotbuiltorganization'), $this->Language->Get('msgnotbuiltorganization'), 'dialoggeoiporganization');
            }

            $_OrganizationTabObject->Title($this->Language->Get('databaseinformation'), 'doublearrows.gif');

            $_licenseIcon = '';
            if (isset($this->_geoIPCityReturn[1]) && isset($this->_geoIPCityBlocksReturn[1]) && $this->_geoIPCityReturn[1] == SWIFT_GeoIP::GEOIP_PREMIUM && $this->_geoIPCityBlocksReturn[1] == SWIFT_GeoIP::GEOIP_PREMIUM) {
                $_licenseIcon = 'icon_geoippremium.png';
                $_organizationType = SWIFT_GeoIP::GEOIP_PREMIUM;
            } else {
                $_licenseIcon = 'icon_geoiplite.png';
                $_organizationType = SWIFT_GeoIP::GEOIP_LITE;
            }

            $_OrganizationTabObject->DefaultDescriptionRow($this->Language->Get('licensetype'), '', '<img src="' . SWIFT::Get('themepathimages') . $_licenseIcon . '" align="absmiddle" border="0" /> ' . IIF($_organizationType == SWIFT_GeoIP::GEOIP_PREMIUM, $this->Language->Get('premium'), '<i>' . $this->Language->Get('lite') . '</i>'));
            $_OrganizationTabObject->DefaultDescriptionRow($this->Language->Get('lastupdate'), '', IIF(!$this->Settings->GetKey('geoip', 'organization'), $this->Language->Get('notavailable'), '<img src="' . SWIFT::Get('themepath') . 'images/icon_check.gif' . '" align="absmiddle" border="0" /> ' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Settings->GetKey('geoip', 'organization'))));

            $_OrganizationTabObject->Title($this->Language->Get('indexsettings'), 'doublearrows.gif');

            if (isset($_POST['entriesperpassorganization']) && !empty($_POST['entriesperpassorganization'])) {
                $_entriesPerPassOrganization = $_POST['entriesperpassorganization'];
            } else {
                $_entriesPerPassOrganization = SWIFT_GeoIP::LINE_LIMIT;
            }

            if (isset($_POST['passnumberorganization']) && !empty($_POST['passnumberorganization'])) {
                $_passNumberOrganization = $_POST['passnumberorganization'];
            } else {
                $_passNumberOrganization = SWIFT_GeoIP::PASS_LIMIT;
            }

            $_OrganizationTabObject->Number('entriesperpassorganization', $this->Language->Get('entriesperpass'), $this->Language->Get('desc_entriesperpass'), $_entriesPerPassOrganization);
            $_OrganizationTabObject->Number('passnumberorganization', $this->Language->Get('passnumber'), $this->Language->Get('desc_passnumber'), $_passNumberOrganization);
            $_OrganizationTabObject->AppendHTML('<tr class="gridrow2"><td align="left" colspan="2" class="smalltext"><div id="geoipparent_organization_' . $_organizationUniqueHash . '"></div></td></tr>');

        } else {
            $_OrganizationTabObject->Error($this->Language->Get('titlenofileorganization'), $this->Language->Get('msgnofileorganization'));
        }

        return true;
    }

    /**
     * Render the ISP Tab
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function RenderISP()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_geoIPISPReturn = SWIFT_GeoIPInternetServiceProvider::GetFile();
        $this->_geoIPISPReturn = $_geoIPISPReturn;

        $_ispUniqueHash = substr(buildHash(), 0, 10);

        $_ISPTabObject = $this->UserInterface->AddTab($this->Language->Get('tabisp'), 'icon_form.gif', 'tabisp');

        $_ISPTabObject->LoadToolbar();

        if (!defined('SWIFT_GEOIP_SERVER')) {
            $_ISPTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle', 'startGeoIPMaintenance(\'isp\', \'' . $_ispUniqueHash . '\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        }

        $_ISPTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('geoip'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        if (defined('SWIFT_GEOIP_SERVER')) {
            $_ISPTabObject->Info($this->Language->Get('titlegeoipremote'), $this->Language->Get('msggeoipremote'));
        } elseif ($_geoIPISPReturn) {
            if (!$this->Settings->GetKey('geoip', 'isp')) {
                $_ISPTabObject->Alert($this->Language->Get('titlenotbuiltisp'), $this->Language->Get('msgnotbuiltisp'), 'dialoggeoipisp');
            }

            $_ISPTabObject->Title($this->Language->Get('databaseinformation'), 'doublearrows.gif');

            $_licenseIcon = '';
            if (isset($this->_geoIPCityReturn[1]) && isset($this->_geoIPCityBlocksReturn[1]) && $this->_geoIPCityReturn[1] == SWIFT_GeoIP::GEOIP_PREMIUM && $this->_geoIPCityBlocksReturn[1] == SWIFT_GeoIP::GEOIP_PREMIUM) {
                $_licenseIcon = 'icon_geoippremium.png';
                $_ispType = SWIFT_GeoIP::GEOIP_PREMIUM;
            } else {
                $_licenseIcon = 'icon_geoiplite.png';
                $_ispType = SWIFT_GeoIP::GEOIP_LITE;
            }

            $_ISPTabObject->DefaultDescriptionRow($this->Language->Get('licensetype'), '', '<img src="' . SWIFT::Get('themepathimages') . $_licenseIcon . '" align="absmiddle" border="0" /> ' . IIF($_ispType == SWIFT_GeoIP::GEOIP_PREMIUM, $this->Language->Get('premium'), '<i>' . $this->Language->Get('lite') . '</i>'));
            $_ISPTabObject->DefaultDescriptionRow($this->Language->Get('lastupdate'), '', IIF(!$this->Settings->GetKey('geoip', 'isp'), $this->Language->Get('notavailable'), '<img src="' . SWIFT::Get('themepath') . 'images/icon_check.gif' . '" align="absmiddle" border="0" /> ' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Settings->GetKey('geoip', 'isp'))));

            $_ISPTabObject->Title($this->Language->Get('indexsettings'), 'doublearrows.gif');

            if (isset($_POST['entriesperpassisp']) && !empty($_POST['entriesperpassisp'])) {
                $_entriesPerPassISP = $_POST['entriesperpassisp'];
            } else {
                $_entriesPerPassISP = SWIFT_GeoIP::LINE_LIMIT;
            }

            if (isset($_POST['passnumberisp']) && !empty($_POST['passnumberisp'])) {
                $_passNumberISP = $_POST['passnumberisp'];
            } else {
                $_passNumberISP = SWIFT_GeoIP::PASS_LIMIT;
            }

            $_ISPTabObject->Number('entriesperpassisp', $this->Language->Get('entriesperpass'), $this->Language->Get('desc_entriesperpass'), $_entriesPerPassISP);
            $_ISPTabObject->Number('passnumberisp', $this->Language->Get('passnumber'), $this->Language->Get('desc_passnumber'), $_passNumberISP);
            $_ISPTabObject->AppendHTML('<tr class="gridrow2"><td align="left" colspan="2" class="smalltext"><div id="geoipparent_isp_' . $_ispUniqueHash . '"></div></td></tr>');

        } else {
            $_ISPTabObject->Error($this->Language->Get('titlenofileisp'), $this->Language->Get('msgnofileisp'));
        }

        return true;
    }

    /**
     * Render the Net Speed Tab
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function RenderNetSpeed()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_geoIPNetSpeedReturn = SWIFT_GeoIPNetSpeed::GetFile();
        $_netSpeedUniqueHash = substr(buildHash(), 0, 10);
        $this->_geoIPNetSpeedReturn = $_geoIPNetSpeedReturn;

        $_licenseIcon = '';
        if ($_geoIPNetSpeedReturn[1] == SWIFT_GeoIP::GEOIP_PREMIUM) {
            $_licenseIcon = 'icon_geoippremium.png';
        } else {
            $_licenseIcon = 'icon_geoiplite.png';
        }

        $_NetSpeedTabObject = $this->UserInterface->AddTab($this->Language->Get('tabnetspeed'), 'icon_form.gif', 'tabnetspeed');

        $_NetSpeedTabObject->LoadToolbar();

        if (!defined('SWIFT_GEOIP_SERVER')) {
            $_NetSpeedTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle', 'startGeoIPMaintenance(\'netspeed\', \'' . $_netSpeedUniqueHash . '\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        }

        $_NetSpeedTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('geoip'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        if (defined('SWIFT_GEOIP_SERVER')) {
            $_NetSpeedTabObject->Info($this->Language->Get('titlegeoipremote'), $this->Language->Get('msggeoipremote'));
        } elseif ($_geoIPNetSpeedReturn) {
            if (!$this->Settings->GetKey('geoip', 'netspeed')) {
                $_NetSpeedTabObject->Alert($this->Language->Get('titlenotbuiltnetspeed'), $this->Language->Get('msgnotbuiltnetspeed'), 'dialoggeoipnetspeed');
            }

            $_NetSpeedTabObject->Title($this->Language->Get('databaseinformation'), 'doublearrows.gif');
            $_NetSpeedTabObject->DefaultDescriptionRow($this->Language->Get('licensetype'), '', '<img src="' . SWIFT::Get('themepathimages') . $_licenseIcon . '" align="absmiddle" border="0" /> ' . IIF($_geoIPNetSpeedReturn[1] == SWIFT_GeoIP::GEOIP_PREMIUM, $this->Language->Get('premium'), '<i>' . $this->Language->Get('lite') . '</i>'));
            $_NetSpeedTabObject->DefaultDescriptionRow($this->Language->Get('lastupdate'), '', IIF(!$this->Settings->GetKey('geoip', 'netspeed'), $this->Language->Get('notavailable'), '<img src="' . SWIFT::Get('themepath') . 'images/icon_check.gif' . '" align="absmiddle" border="0" /> ' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Settings->GetKey('geoip', 'netspeed'))));

            $_NetSpeedTabObject->Title($this->Language->Get('indexsettings'), 'doublearrows.gif');

            if (isset($_POST['entriesperpassnetspeed']) && !empty($_POST['entriesperpassnetspeed'])) {
                $_entriesPerPassNetSpeed = $_POST['entriesperpassnetspeed'];
            } else {
                $_entriesPerPassNetSpeed = SWIFT_GeoIP::LINE_LIMIT;
            }

            if (isset($_POST['passnumbernetspeed']) && !empty($_POST['passnumbernetspeed'])) {
                $_passNumberNetSpeed = $_POST['passnumbernetspeed'];
            } else {
                $_passNumberNetSpeed = SWIFT_GeoIP::PASS_LIMIT;
            }

            $_NetSpeedTabObject->Number('entriesperpassnetspeed', $this->Language->Get('entriesperpass'), $this->Language->Get('desc_entriesperpass'), $_entriesPerPassNetSpeed);
            $_NetSpeedTabObject->Number('passnumbernetspeed', $this->Language->Get('passnumber'), $this->Language->Get('desc_passnumber'), $_passNumberNetSpeed);
            $_NetSpeedTabObject->AppendHTML('<tr class="gridrow2"><td align="left" colspan="2" class="smalltext"><div id="geoipparent_netspeed_' . $_netSpeedUniqueHash . '"></div></td></tr>');

        } else {
            $_NetSpeedTabObject->Error($this->Language->Get('titlenofilenetspeed'), $this->Language->Get('msgnofilenetspeed'));
        }

        return true;
    }

    /**
     * Render the City Blocks
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function RenderCityBlocks()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_geoIPCityBlocksReturn = SWIFT_GeoIPCity::GetFileCityBlocks();
        $_cityBlocksUniqueHash = substr(BuildHash(), 0, 10);

        $this->_geoIPCityBlocksReturn = $_geoIPCityBlocksReturn;
        $_licenseIcon = '';
        if ($_geoIPCityBlocksReturn[1] == SWIFT_GeoIP::GEOIP_PREMIUM) {
            $_licenseIcon = 'icon_geoippremium.png';
        } else {
            $_licenseIcon = 'icon_geoiplite.png';
        }

        $_CityBlocksTabObject = $this->UserInterface->AddTab($this->Language->Get('tabcityblocks'), 'icon_form.gif', 'tabcityblocks');

        $_CityBlocksTabObject->LoadToolbar();

        if (!defined('SWIFT_GEOIP_SERVER')) {
            $_CityBlocksTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle', 'startGeoIPMaintenance(\'cityblocks\', \'' . $_cityBlocksUniqueHash . '\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        }

        $_CityBlocksTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('geoip'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        if (defined('SWIFT_GEOIP_SERVER')) {
            $_CityBlocksTabObject->Info($this->Language->Get('titlegeoipremote'), $this->Language->Get('msggeoipremote'));
        } elseif ($_geoIPCityBlocksReturn) {
            if (!$this->Settings->GetKey('geoip', 'cityblocks')) {
                $_CityBlocksTabObject->Alert($this->Language->Get('titlenotbuiltcityblocks'), $this->Language->Get('msgnotbuiltcityblocks'), 'dialoggeoipcityblocks');
            }

            $_CityBlocksTabObject->Title($this->Language->Get('databaseinformation'), 'doublearrows.gif');
            $_CityBlocksTabObject->DefaultDescriptionRow($this->Language->Get('licensetype'), '', '<img src="' . SWIFT::Get('themepathimages') . $_licenseIcon . '" align="absmiddle" border="0" /> ' . IIF($_geoIPCityBlocksReturn[1] == SWIFT_GeoIP::GEOIP_PREMIUM, $this->Language->Get('premium'), '<i>' . $this->Language->Get('lite') . '</i>'));
            $_CityBlocksTabObject->DefaultDescriptionRow($this->Language->Get('lastupdate'), '', IIF(!$this->Settings->GetKey('geoip', 'cityblocks'), $this->Language->Get('notavailable'), '<img src="' . SWIFT::Get('themepath') . 'images/icon_check.gif' . '" align="absmiddle" border="0" /> ' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Settings->GetKey('geoip', 'cityblocks'))));

            $_CityBlocksTabObject->Title($this->Language->Get('indexsettings'), 'doublearrows.gif');

            if (isset($_POST['entriesperpasscityblocks']) && !empty($_POST['entriesperpasscityblocks'])) {
                $_entriesPerPassCityBlocks = $_POST['entriesperpasscityblocks'];
            } else {
                $_entriesPerPassCityBlocks = SWIFT_GeoIP::LINE_LIMIT;
            }

            if (isset($_POST['passnumbercityblocks']) && !empty($_POST['passnumbercityblocks'])) {
                $_passNumberCityBlocks = $_POST['passnumbercityblocks'];
            } else {
                $_passNumberCityBlocks = SWIFT_GeoIP::PASS_LIMIT;
            }

            $_CityBlocksTabObject->Number('entriesperpasscityblocks', $this->Language->Get('entriesperpass'), $this->Language->Get('desc_entriesperpass'), $_entriesPerPassCityBlocks);
            $_CityBlocksTabObject->Number('passnumbercityblocks', $this->Language->Get('passnumber'), $this->Language->Get('desc_passnumber'), $_passNumberCityBlocks);
            $_CityBlocksTabObject->AppendHTML('<tr class="gridrow2"><td align="left" colspan="2" class="smalltext"><div id="geoipparent_cityblocks_' . $_cityBlocksUniqueHash . '"></div></td></tr>');

        } else {
            $_CityBlocksTabObject->Error($this->Language->Get('titlenofilecityblocks'), $this->Language->Get('msgnofilecityblocks'));
        }

        return true;
    }

    /**
     * Render the City Locations
     *
     * @author Varun Shoor
     * @return bool "true" on Success, "false" otherwise
     */
    protected function RenderCityLocations()
    {
        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        $_CityLocationsTabObject = $this->UserInterface->AddTab($this->Language->Get('tabcitylocations'), 'icon_form.gif', 'tabcitylocations', true);
        $_geoIPCityReturn = SWIFT_GeoIPCity::GetFileCityLocation();
        $_cityLocationUniqueHash = substr(BuildHash(), 0, 10);

        $this->_geoIPCityReturn = $_geoIPCityReturn;
        $_licenseIcon = '';
        if ($_geoIPCityReturn[1] == SWIFT_GeoIP::GEOIP_PREMIUM) {
            $_licenseIcon = 'icon_geoippremium.png';
        } else {
            $_licenseIcon = 'icon_geoiplite.png';
        }

        $_CityLocationsTabObject->LoadToolbar();

        if (!defined('SWIFT_GEOIP_SERVER')) {
            $_CityLocationsTabObject->Toolbar->AddButton($this->Language->Get('update'), 'fa-check-circle', 'startGeoIPMaintenance(\'citylocations\', \'' . $_cityLocationUniqueHash . '\');', SWIFT_UserInterfaceToolbar::LINK_JAVASCRIPT, '', '', false);
        }

        $_CityLocationsTabObject->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('geoip'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        if (defined('SWIFT_GEOIP_SERVER')) {
            $_CityLocationsTabObject->Info($this->Language->Get('titlegeoipremote'), $this->Language->Get('msggeoipremote'));
        } elseif ($_geoIPCityReturn) {
            if (!$this->Settings->GetKey('geoip', 'citylocations')) {
                $_CityLocationsTabObject->Alert($this->Language->Get('titlenotbuiltcitylocations'), $this->Language->Get('msgnotbuiltcitylocations'), 'dialoggeoipcitylocations');
            }

            $_CityLocationsTabObject->Title($this->Language->Get('databaseinformation'), 'doublearrows.gif');
            $_CityLocationsTabObject->DefaultDescriptionRow($this->Language->Get('licensetype'), '', '<img src="' . SWIFT::Get('themepathimages') . $_licenseIcon . '" align="absmiddle" border="0" /> ' . IIF($_geoIPCityReturn[1] == SWIFT_GeoIP::GEOIP_PREMIUM, $this->Language->Get('premium'), '<i>' . $this->Language->Get('lite') . '</i>'));
            $_CityLocationsTabObject->DefaultDescriptionRow($this->Language->Get('lastupdate'), '', IIF(!$this->Settings->GetKey('geoip', 'citylocations'), $this->Language->Get('notavailable'), '<img src="' . SWIFT::Get('themepath') . 'images/icon_check.gif' . '" align="absmiddle" border="0" /> ' . SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $this->Settings->GetKey('geoip', 'citylocations'))));

            $_CityLocationsTabObject->Title($this->Language->Get('indexsettings'), 'doublearrows.gif');

            if (isset($_POST['entriesperpasscitylocations']) && !empty($_POST['entriesperpasscitylocations'])) {
                $_entriesPerPassCityLocations = $_POST['entriesperpasscitylocations'];
            } else {
                $_entriesPerPassCityLocations = SWIFT_GeoIP::LINE_LIMIT;
            }

            if (isset($_POST['passnumbercitylocations']) && !empty($_POST['passnumbercitylocations'])) {
                $_passNumberCityLocations = $_POST['passnumbercitylocations'];
            } else {
                $_passNumberCityLocations = SWIFT_GeoIP::PASS_LIMIT;
            }

            $_CityLocationsTabObject->Number('entriesperpasscitylocations', $this->Language->Get('entriesperpass'), $this->Language->Get('desc_entriesperpass'), $_entriesPerPassCityLocations);
            $_CityLocationsTabObject->Number('passnumbercitylocations', $this->Language->Get('passnumber'), $this->Language->Get('desc_passnumber'), $_passNumberCityLocations);
            $_CityLocationsTabObject->AppendHTML('<tr class="gridrow2"><td align="left" colspan="2" class="smalltext"><div id="geoipparent_citylocations_' . $_cityLocationUniqueHash . '"></div></td></tr>');

        } else {
            $_CityLocationsTabObject->Error($this->Language->Get('titlenofilecitylocation'), $this->Language->Get('msgnofilecitylocation'));
        }

        return true;
    }
}

?>
