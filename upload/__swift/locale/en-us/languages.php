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
 * @license        http://www.opencart.com.vn/license
 * @link        http://www.opencart.com.vn
 *
 * ###############################################
 */

$__LANG = [
    // ======= BEGIN V4 LOCALES =======
    'languageisenabled'        => 'Language enabled',
    'desc_languageisenabled'   => 'If enable, your end users will be able to select this language in the support center.',
    'titlemissingphrases'      => 'Missing phrases found',
    'msgmissingphrases'        => 'QuickSupport has found %d missing phrases. It is recommended that you update the selected language and insert all the missing phrases.',
    'tabimport'                => 'Import',
    'tabexport'                => 'Export',
    'languagemen'              => 'Language',
    'export'                   => 'Export',
    'import'                   => 'Import',
    'squicksearch'             => 'Quick Search',
    'titleupdatedlangphrases'  => 'Phrases updated',
    'msgupdatedlangphrases'    => 'Phrases for %s have been updated.',
    'titlevcfailed'            => 'Language pack out of date',
    'msgvcfailed'              => 'The language pack uploaded was created for an older version of QuickSupport. You can skip this check by enabling <em>Ignore version check</em> below.',
    'titlelangimpfailed'       => 'Language import/merge failed',
    'msglangimpfailed'         => 'QuickSupport had problems importing this language pack. It may not be in the correct format or may contain bad data.',
    'tabsearch'                => 'Search',
    'tabgeneral'               => 'General',
    'flagicon'                 => 'Flag icon',
    'desc_flagicon'            => 'Upload or link to an icon for this flag. {$themepath} can be used to point to the flags directory, for example: <em>{$themepath}us.gif</em>',
    'wineditlanguage'          => 'Edit Language: %s',
    'phrases'                  => 'Phrases',
    'tabphrases'               => 'Phrases',
    'insertphrase'             => 'Insert Phrase',
    'desc_insertphrase'        => '',
    'tabgeneral'               => 'General',
    'phraselanguage'           => 'Language',
    'desc_phraselanguage'      => 'Which language will this phrase belong to?',
    'titlemergelang'           => 'Language merge successful',
    'msgmergelang'             => 'Successfully merged the language pack with language "%s".',
    'titleimportlang'          => 'Language import successful',
    'msgimportlang'            => 'The language pack was successfully imported. QuickSupport has created the following language:',
    'titlephraseinsert'        => 'Phrase (%s) created',
    'msgphraseinsert'          => 'The phrase (%s) was successfully created. <strong>Language: </strong>%s <strong>Phrase code:</strong> %s <strong>Phrase section:</strong> %s <strong>Phrase:</strong> %s',
    'titlephrasedel'           => 'Phrase deleted',
    'msgphrasedel'             => 'The phrase (%s) was deleted.',
    'titleinsertlang'          => 'Language %s created',
    'msginsertlang'            => 'The language %s was created successfully:',
    'titleupdatelang'          => 'Language %s updated',
    'msgupdatelang'            => 'The language %s was updated successfully:',
    'titledellang'             => 'Languages deleted (%d)',
    'msgdellang'               => 'The following languages were deleted:<br>',
    'compare'                  => 'Compare',
    'restorelanguage'          => 'Restore Language',
    'restoreconfirm'           => 'Are you sure you wish to restore the phrases for this language to the originals? All of your phrase customizations will be lost.',
    'titlerestorephrase'       => 'Phrases restored',
    'msgrestorephrase'         => 'All of the phrases in %s were restored to the original versions.',
    'diagnostics'              => 'Diagnostics',
    'tabmissingphrases'        => 'Missing Phrases',
    'diagnosticslang1'         => 'Language',
    'desc_diagnosticslang1'    => 'The language to search the phrases of.',
    'diagnosticslang2'         => 'Compare with',
    'desc_diagnosticslang2'    => 'Please select the language to compare the missing phrases with. QuickSupport will list all phrases that are missing from the main language.',
    'restorephrases'           => 'Restore Phrases',
    'desc_restorephrases'      => '',
    'tabrestorephrases'        => 'Restore Phrases',
    'lookup'                   => 'Lookup',
    'modified'                 => 'Modified',
    'upgraderevert'            => 'Out of date',
    'notmodified'              => 'Original',
    'titlenooptsel'            => 'Invalid phrase type',
    'msgnooptsel'              => 'You need to select at least one phrase type to filter the results.',
    'restore'                  => 'Restore',
    'titleunabledelmasterlang' => 'Unable to delete master language',
    'msgunabledelmasterlang'   => 'It is not possible to delete the master language.',
    'phrasesection'            => 'Section',
    'desc_phrasesection'       => 'Which section will this phrase belong to? It is recommended that this be kept to the global "default" section unless you want to target a specific area.',
    'phrasestatus'             => 'Phrase Status',
    'restorelanguage2'         => 'Restore phrases to latest original versions: %s',
    'restorelanguage3'         => 'Language: %s',
    'titlerestorephrases'      => 'Restored phrases (%d)',
    'msgrestorephrases'        => 'The following phrases were restored to their original versions:',
    'phrasemissing'            => '-- MISSING --',
    // ======= END V4 LOCALES =======

    // ======= BEGIN v3 IMPORT =======
    'section'                  => 'Section',
    // ======= END v3 IMPORT =======

    'languages'                  => 'Languages',
    'languagedetails'            => 'Language Details',
    'desc_languages'             => '',
    'languagelist'               => 'Language List',
    'languagetitle'              => 'Language',
    'desc_languagetitle'         => 'Enter a title for the language, for example: <em>English (U.S.)</em>.',
    'authorname'                 => 'Author',
    'desc_authorname'            => 'Enter the language pack author\'s name.',
    'isdefault'                  => 'Is the helpdesk default language',
    'desc_isdefault'             => 'Enable this setting to make it the helpdesk\'s default language.',
    'textdirection'              => 'Orientation',
    'desc_textdirection'         => 'Select the orientation of the text in the language.',
    'isocode'                    => 'ISO code',
    'desc_isocode'               => 'Enter the ISO code for the language. <a href="http://www.iso.org/iso/english_country_names_and_code_elements" target="_blank" rel="noopener noreferrer">Click here</a> for a list of ISO codes. For example: <em>en-us: English (U.S.)</em>.',
    'languagecharset'            => 'Character set',
    'desc_languagecharset'       => 'Enter the HTML encoding for this language. This should usually be <em>UTF-8</em>.',
    'rtl'                        => 'Right to left',
    'ltr'                        => 'Left to right',
    'displayorder'               => 'Display order',
    'desc_displayorder'          => 'If you have multiple languages they will be displayed according to their display order, smallest to largest.',
    'insertlanguage'             => 'Insert Language',
    'phrases'                    => 'Phrases',
    'invalidlanguageid'          => 'Invalid language ID',
    'languagedeleteconfirmation' => 'Deleted language %s',
    'languageinsertconfirmation' => 'Created language %s',
    'languageupdateconfirmation' => 'Updated language %s',
    'importexport'               => 'Import/Export',
    'exportlanguage'             => 'Export Language',
    'explanguage'                => 'Language to export',
    'desc_explanguage'           => 'Select the language you wish to export as a language pack.',
    'exportxml'                  => 'Export',
    'importlanguage'             => 'Import Language',
    'importxml'                  => 'Import',
    'languagefile'               => 'XML language pack file to import',
    'desc_languagefile'          => 'Select the language pack you wish to import.',
    'mergewith'                  => 'Import method',
    'desc_mergewith'             => '<strong>Create new language</strong> Create an entirely new language using the data in the language pack.<br /><strong>Select language</strong> The translated phrases in the language pack will be merged with the existing language.',
    'ignoreversion'              => 'Bypass version check',
    'desc_ignoreversion'         => 'Enable this to force QuickSupport to accept language packs created using older versions of QuickSupport. We recommend using up-to-date translations only.',
    'createnewlanguage'          => '-- Create New Language --',
    'languageimportconfirmation' => 'Imported language %s successfully',
    'managephrases'              => 'Manage Phrases',
    'code'                       => 'Identifier',
    'value'                      => 'Text',
    'phraseupdateconfirm'        => 'Phrases updated successfully',
    'managephrases'              => 'Manage Phrases',
    'searchphrases'              => 'Search Phrases',
    'languagesearching'          => 'Search in progress...',
    'search'                     => 'Search',
    'codetext'                   => 'Identifier and text',
    'query'                      => 'Search query',
    'desc_query'                 => 'What do you want to search for?',
    'searchtype'                 => 'Search type',
    'desc_searchtype'            => '<strong>Identifier and text</strong> Will search in phrase texts and phrase identifiers.<br /><strong>Identifier</strong> Will only search the phrase identifiers.',
    'searchlanguage'             => 'Search within language',
    'desc_searchlanguage'        => 'Select the language to search.',
    'versioncheckfailed'         => 'ERROR: This language pack was created using an older version of QuickSupport, and is out of date',
    'addphrase'                  => 'Insert Phrase',
    'changelanguage'             => 'Language Jump',
    'desc_phrasecode'            => 'Please enter a unique identifier for the new phrase. For example: <em>ticket_myphrase</em>.',
    'desc_phrasevalue'           => 'Enter the contents of the phrase. HTML is accepted here.',
    'phraseinsertconfirm'        => 'Phrase (%s) created',
    'languagejump'               => 'Language Jump',
    'language'                   => 'Target Language',
    'deletephrase'               => 'Delete Phrase',
    'phrasedeleteconfirm'        => 'Phrase (%s) has been deleted',
    'phrasedeletepopup'          => 'Are you sure you wish to delete this phrase? If you delete a phrase which is in use, this may change your helpdesk and confuse your users.',
    'phrasedelfailure'           => 'ERROR: Unable to delete phrase',
    'invalidlanguagecode'        => 'Invalid language code',
    'invalidlanguagecodedesc'    => 'The language code is already in use',
    'novalue'                    => '[No Master Value Available]',
];

return $__LANG;
