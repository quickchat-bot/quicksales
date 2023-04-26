<?php
$__LANG = array(
    'archiver' => 'Archiver',
    'error' => 'Error',
    'success' => 'Success',
    'warning' => 'Warning',
    'insert' => 'Insert',
    'update' => 'Update',
    'delete' => 'Delete',
    'help' => 'Help',
    'yes' => 'Yes',
    'cancel' => 'Cancel',
    'close' => 'Close',

    //manager controller
    'archive_manager' => 'Purge Old Data',
    'empty_trash' => 'Empty Trash Can',
    'archive_results' => 'Search Results',
    'archiver_tab_general' => 'General',
    'archiver_save_button' => 'Save',
    'archiver_search_button' => 'Search',
    'archiver_search_trash' => 'Search Trash',
    'archiver_export_button' => 'Export Selected',
    'archiver_export_button_all' => 'Export All',
    'archiver_delete_button' => 'Delete Selected',
    'archiver_delete_button_all' => 'Delete All',
    'archiver_trash_button' => 'Empty Trash Can',
    'archiver_error_title' => 'Error',
    'archiver_noempty' => ' can not be empty',
    'archiver_valid' => ' please enter a valid value',
    'archiver_date_valid' => ' please enter a valid date in the format "%s"',
    'archiver_date_greater' => ' the value should be greater that the start date',
    'archiver_date_future' => ' the value cannot be in the future',

    // manage form
    'ar_email' => 'Email',
    'ar_desc_email' => 'Provide an email to narrow search only to tickets belonging to a single user (leave blank to fetch all tickets)',
    'ar_page_size' => 'Page Size',
    'ar_desc_page_size' => 'Number of items per page in search results screen',
    'ar_start_date' => 'Start Date',
    'ar_desc_start_date' => 'Start of date period to search for old data (in the format "%s")',
    'ar_end_date' => 'End Date',
    'ar_desc_end_date' => 'End of date period to search for old data. It defaults to 3 months ago (format "%s")',

    // grid
    'lcid' => 'Ticket ID',
    'lcdept' => 'Department',
    'lcstatus' => 'Status',
    'lcstaff' => 'Staff Owner',
    'lcuser' => 'User Name',
    'lcsubject' => 'Subject',
    'lcdate' => 'Last Activity',
    'ar_titlesearchresult' => '"%d" Ticket%s Found',
    'ar_msgsearchresult' => 'Search found %d ticket%s based on the criteria provided',
    'ar_msgsearchnoresult' => 'Search was unable to locate any tickets matching the specified criteria.',
    'ar_msgsearchnotrash' => 'There are no tickets in the trash can.',
    'ar_msgsearchtrash' => 'There %s %d ticket%s in the trash can',

    // delete
    'rowsprocessed' => 'Rows Processed',
    'totalrows' => 'Total Rows',
    'ticketsprocessed' => 'Tables Processed',
    'totaltickets' => 'Total Tables',
    'timeelapsed' => 'Time Elapsed',
    'timeremaining' => 'Time Remaining',
    'generalinformation' => 'Processing Table',
    'ar_confirmdelete' => 'Are you sure you want to continue?',
    'ar_confirmdelete_all' => 'Are you sure you want to permanently delete ALL the tickets found?',
    'ar_confirmtrash' => 'Are you sure you want to delete the tickets in the trash for all staff users?',
);

/*
 * ###############################################
 * BEGIN INTERFACE RELATED CODE
 * ###############################################
 */

$_SWIFT = SWIFT::GetInstance();

if ($_SWIFT->Interface->GetInterface() === SWIFT_Interface::INTERFACE_ADMIN) {
    /**
     * Admin Area Navigation Bar
     */

    $_adminBarItemContainer = SWIFT::Get('adminbaritems');
    // Insert new item on Maintenance
    $_adminBarItemContainer[34][] = array('Purge Old Data', '/archiver/Manager/Index');
    $_adminBarItemContainer[34][] = array('Empty Trash Can', '/archiver/Manager/Trash');

    SWIFT::Set('adminbaritems', $_adminBarItemContainer);
}

return $__LANG;