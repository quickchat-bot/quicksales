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

namespace Tickets\Staff;

use SWIFT;
use SWIFT_Date;
use SWIFT_Exception;
use Base\Library\Help\SWIFT_Help;
use Tickets\Models\Ticket\SWIFT_Ticket;
use Tickets\Models\Note\SWIFT_TicketNote;
use Tickets\Models\Note\SWIFT_TicketNoteManager;
use Base\Models\User\SWIFT_User;
use Base\Library\UserInterface\SWIFT_UserInterface;
use Base\Library\UserInterface\SWIFT_UserInterfaceToolbar;
use Base\Models\User\SWIFT_UserNote;
use Base\Models\User\SWIFT_UserNoteManager;

trait View_TicketNoteTrait {
    /**
     * Render the Notes for the given Ticket (including his organization notes)
     *
     * @author Varun Shoor
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object
     * @param SWIFT_User $_SWIFT_UserObject (OPTIONAL) The SWIFT_User Object
     * @return mixed "_renderedHTML" (STRING) on Success, "false" otherwise
     * @throws SWIFT_Exception If the Class is not Loaded or If Invalid Data is Provided
     */
    public function RenderNotes(SWIFT_Ticket $_SWIFT_TicketObject, $_SWIFT_UserObject = null) {
        $_SWIFT = SWIFT::GetInstance();

        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        if (!$_SWIFT_TicketObject instanceof SWIFT_Ticket || !$_SWIFT_TicketObject->GetIsClassLoaded()) {
            throw new SWIFT_Exception(SWIFT_INVALIDDATA);
        }

        // Retrieve notes
        $_noteContainer = array();

        $_userNoteContainer = array();
        $_renderedHTML = '';

        // Fetch user notes
        if ($_SWIFT->Staff->GetPermission('staff_canviewusernotes') != '0' && $_SWIFT_UserObject instanceof SWIFT_User &&
            $_SWIFT_UserObject->GetIsClassLoaded()) {
            $this->Database->Query("SELECT usernotes.*, usernotedata.* FROM " . TABLE_PREFIX . "usernotes AS usernotes LEFT JOIN " . TABLE_PREFIX .
                "usernotedata AS usernotedata ON (usernotes.usernoteid = usernotedata.usernoteid) WHERE (usernotes.linktype = '" .
                SWIFT_UserNote::LINKTYPE_USER . "' AND usernotes.linktypeid = '" .  ($_SWIFT_UserObject->GetUserID()) .
                "') OR (usernotes.linktype = '" . SWIFT_UserNote::LINKTYPE_ORGANIZATION . "' AND usernotes.linktypeid = '" .
                (int) ($_SWIFT_UserObject->GetProperty('userorganizationid')) . "') ORDER BY usernotes.dateline DESC");
            while ($this->Database->NextRecord()) {
                $_noteKey = $this->Database->Record['dateline'] . '.' . $this->Database->Record['usernoteid'] . '.1';

                $_noteContainer[$_noteKey] = $this->Database->Record;

                $_noteContainer[$_noteKey][':customtype'] = 'user';
            }
        }

        // Fetch ticket notes
        if ($_SWIFT->Staff->GetPermission('staff_tcanviewticketnotes') != '0') {
            $this->Database->Query("SELECT * FROM " . TABLE_PREFIX . "ticketnotes WHERE linktype = '" . SWIFT_TicketNoteManager::LINKTYPE_TICKET .
                "' AND linktypeid = '" . (int) ($_SWIFT_TicketObject->GetTicketID()) . "'");
            while ($this->Database->NextRecord()) {
                if ($this->Database->Record['forstaffid'] != '0' && $this->Database->Record['forstaffid'] != $_SWIFT->Staff->GetStaffID() && $this->Database->Record['staffid'] != $_SWIFT->Staff->GetStaffID()) {
                    continue;
                }

                $_noteKey = $this->Database->Record['dateline'] . '.' . $this->Database->Record['ticketnoteid'] . '.2';

                $_noteContainer[$_noteKey] = $this->Database->Record;

                $_noteContainer[$_noteKey][':customtype'] = 'ticket';
            }
        }

        // Sort the data
        ksort($_noteContainer);

        foreach ($_noteContainer as $_note) {
            $_icon = $_noteColor = $_noteContents = '';

            if ($_note[':customtype'] === 'user' && $_note['linktype'] == SWIFT_UserNote::LINKTYPE_USER) {
                $_icon = 'fa-user';
                $_noteColor = SWIFT_UserNote::GetSanitizedNoteColor($_note['notecolor']);

                $_noteContents = $_note['notecontents'];
            } else if ($_note[':customtype'] === 'user' && $_note['linktype'] == SWIFT_UserNoteManager::LINKTYPE_ORGANIZATION) {
                $_icon = 'fa-institution';
                $_noteColor = SWIFT_UserNote::GetSanitizedNoteColor($_note['notecolor']);

                $_noteContents = $_note['notecontents'];
            } else if ($_note[':customtype'] === 'ticket' && $_note['linktype'] == SWIFT_TicketNote::LINKTYPE_TICKET) {
                $_icon = 'fa-ticket';
                $_noteColor = SWIFT_TicketNote::GetSanitizedNoteColor($_note['notecolor']);

                $_noteContents = $_note['note'];
            }

            $_renderedHTML .= '<div id="note' . (int) ($_noteColor) . '" class="bubble"><div class="notebubble"><cite class="tip"><strong><i class="fa ' . $_icon . '" aria-hidden="true"></i> ' . sprintf($this->Language->Get('notetitle'),
                    '<b>'.htmlspecialchars($_note['staffname']).'</b>', SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_note['dateline']))
                . IIF(!empty($_note['editedstaffid']) && !empty($_note['editedstaffname']), sprintf($this->Language->Get('noteeditedtitle'),
                    htmlspecialchars($_note['editedstaffname']), SWIFT_Date::Get(SWIFT_Date::TYPE_DATETIME, $_note['editedtimeline'])))
                . '</strong><div class="ticketnotesactions">';

            if ($_note[':customtype'] === 'ticket' && $_SWIFT->Staff->GetPermission('staff_tcanupdateticketnote') != '0') {
                $_renderedHTML .= '<a href="javascript: void(0);" onclick="javascript: UICreateWindow(\'' . SWIFT::Get('basename') . '/Tickets/Ticket/EditNote/' . $_SWIFT_TicketObject->GetTicketID() . '/' . (int) ($_note['ticketnoteid']) . "', 'editnote', '". $this->Language->Get('editnote') ."', '". $this->Language->Get('loadingwindow') . '\', 650, 350, true, this);"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a> ';
            }

            if ($_note[':customtype'] === 'ticket' && $_SWIFT->Staff->GetPermission('staff_tcandeleteticketnote') != '0') {
                $_renderedHTML .= '<a href="javascript: void(0);" onclick="javascript: TicketDeleteNote(\'' . addslashes($this->Language->Get('ticketnotedelconfirm')) . '\', \'' . (int) ($_SWIFT_TicketObject->GetTicketID()) . '/' . (int) ($_note['ticketnoteid']) . '\');"><i class="fa fa-trash" aria-hidden="true"></i></a>';
            }

            $_renderedHTML .= '</div></cite><blockquote><p>' . AutoLink(nl2br(htmlspecialchars($_noteContents))) . '</p></blockquote></div></div>';
        }

        return $_renderedHTML;
    }

    /**
     * Render the Add Note Dialog
     *
     * @author Varun Shoor
     * @param int $_mode The Render Mode
     * @param SWIFT_Ticket $_SWIFT_TicketObject The SWIFT_Ticket Object Pointer
     * @param SWIFT_TicketNote $_SWIFT_TicketNoteObject The SWIFT_TicketNote Object Poitner
     * @param SWIFT_User $_SWIFT_UserObject The SWIFT_User Object Pointer
     * @return bool "true" on Success, "false" otherwise
     * @throws SWIFT_Exception
     * @throws \Tickets\Library\Ticket\SWIFT_Ticket_Exception
     * @throws \Tickets\Models\Note\SWIFT_Note_Exception
     */
    public function RenderNoteForm($_mode, SWIFT_Ticket $_SWIFT_TicketObject, $_SWIFT_TicketNoteObject = null,
        $_SWIFT_UserObject = null) {
        $_SWIFT = SWIFT::GetInstance();

        $_staffCache = (array) $this->Cache->Get('staffcache');

        if (!$this->GetIsClassLoaded()) {
            return false;
        }

        /**
         * BUG FIX - Ravi Sharma <ravi.sharma@kayako.com>
         *
         * SWIFT-3465 Tickets should be refreshed while adding ticket notes.
         */
        // Calculate the URL
        if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
            $this->UserInterface->Start('ticketaddnotes', '/Tickets/Ticket/AddNoteSubmit/' . $_SWIFT_TicketObject->GetTicketID() . '/' . SWIFT::Get('ticketurlsuffix'), SWIFT_UserInterface::MODE_EDIT, true, false, false, false);
        } else {
            $this->UserInterface->Start('ticketaddnotes', '/Tickets/Ticket/EditNoteSubmit/' . $_SWIFT_TicketObject->GetTicketID() . '/' . $_SWIFT_TicketNoteObject->GetTicketNoteID(), SWIFT_UserInterface::MODE_EDIT, true, false, false, false, 'ticketnotescontainerdiv');
        }

        $this->UserInterface->Toolbar->AddButton($this->Language->Get('help'), 'fa-question-circle', SWIFT_Help::RetrieveHelpLink('ticketmain'), SWIFT_UserInterfaceToolbar::LINK_NEWWINDOW);

        $_defaultNoteContents = '';
        $_defaultNoteColor = 1;
        if ($_SWIFT_TicketNoteObject instanceof SWIFT_TicketNote && $_SWIFT_TicketNoteObject->GetIsClassLoaded()) {
            $_defaultNoteContents = $_SWIFT_TicketNoteObject->GetProperty('note');
            $_defaultNoteColor = (int) ($_SWIFT_TicketNoteObject->GetProperty('notecolor'));
        }

        /*
         * ###############################################
         * BEGIN ADD NOTES TAB
         * ###############################################
        */

        $_AddNoteTabObject = $this->UserInterface->AddTab(IIF($_mode == SWIFT_UserInterface::MODE_INSERT, $this->Language->Get('tabaddnote'), $this->Language->Get('tabeditnote')), 'icon_note.png', 'addnote', true);

        $_AddNoteTabObject->Notes('ticketnotes', $this->Language->Get('addnotes'), $_defaultNoteContents, $_defaultNoteColor);

        if ($_mode == SWIFT_UserInterface::MODE_INSERT) {
            $_radioContainer = array();
            $_radioContainer[0]['title'] = $this->Language->Get('notes_ticket');
            $_radioContainer[0]['value'] = 'ticket';
            $_radioContainer[0]['checked'] = true;

            if ($_SWIFT->Staff->GetPermission('staff_caninsertusernote') != '0' && $_SWIFT_UserObject instanceof SWIFT_User && $_SWIFT_UserObject->GetIsClassLoaded()) {
                $_radioContainer[1]['title'] = $this->Language->Get('notes_user');
                $_radioContainer[1]['value'] = 'user';

                $_radioContainer[2]['title'] = $this->Language->Get('notes_userorganization');
                $_radioContainer[2]['value'] = 'userorganization';
            }

            $_AddNoteTabObject->Radio('notetype', $this->Language->Get('notetype'), '', $_radioContainer, false, 'HandleTicketNoteRestriction();');

            $_optionsContainer = array();
            $_optionsContainer[0]['title'] = $this->Language->Get('notesvisibleall');
            $_optionsContainer[0]['value'] = '0';
            $_optionsContainer[0]['selected'] = true;

            $_index = 1;
            foreach ($_staffCache as $_staffID => $_staffContainer) {
                $_optionsContainer[$_index]['title'] = text_to_html_entities($_staffContainer['fullname']);
                $_optionsContainer[$_index]['value'] = $_staffID;

                $_index++;
            }

            $_AddNoteTabObject->Select('forstaffid', $this->Language->Get('notevisibleto'), $this->Language->Get('desc_notevisibleto'), $_optionsContainer);
        }

        /*
         * ###############################################
         * END ADD NOTES TAB
         * ###############################################
        */

        $this->UserInterface->End();

        return true;
    }
}
