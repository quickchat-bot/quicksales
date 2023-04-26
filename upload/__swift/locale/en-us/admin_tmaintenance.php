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

$__LANG = array (
    // ======= BEGIN v4 LOCALES =======
    'tabpostindex' => 'Rebuild Search Index',
    'tabmoveattachments' => 'Move Attachments',
    'attachmentsperpass' => 'Number of attachments to process in a batch',
    'desc_attachmentsperpass' => 'QuickSupport will work through the attachments one batch at a time. A higher number requires a more powerful server.',
    'move' => 'Move',
    'dbtofiles' => 'From database (%d) into file system',
    'filestodb' => 'From file system (%d) into database',
    'movetype' => 'Move attachments from',
    'desc_movetype' => 'Select where you wish to move your attachments. If you are a QuickSupport Download customer and manage your own web server, you may need to increase your max_packet_size if moving files from the file system to the database.',
    'attachmentsprocessed' => 'Attachments moved',
    'totalattachments' => 'Total attachments',
    'tabproperties'=> 'Rebuild Properties',
    'ticketsperpass' => 'Batch size',
    'desc_ticketsperpass' => 'Enter the number of tickets to process in a single batch (default = 100). You may wish to increase this number to complete the reindexing quicker, or you may wish to lower it to reduce the demand on your server.',
    // ======= END v4 LOCALES =======
    'tickets' => 'Tickets',
    'maintenance' => 'Maintenance',
    'rebuildpindex' => 'Rebuild Post Index',
    'indexseterror' => 'Note: The default search mode is set to full text. To use the inbuilt search engine, change the <strong>Search Type</strong> setting under <em>Settings > Tickets</em>.',
    'rebuild' => 'Rebuild',
    'postperpass' => 'Ticket replies to process in a batch',
    'desc_postperpass' => 'Enter the number of ticket replies to process in a single batch (default = 100). You may wish to increase this number to complete the reindexing quicker, or you may wish to lower it to reduce the demand on your server.',
    'reindexheader' => '<strong>Ticket post indexing progress</strong>',
    'totalposts' => 'Total ticket replies:',
    'totaltickets' => 'Total tickets:',
    'postsprocesed' => 'Replies processed:',
    'ticketsprocessed' => 'Tickets processed:',
    'timeelapsed' => 'Time elapsed:',
    'timeremaining' => 'Estimated time remaining:',
);

return $__LANG;
