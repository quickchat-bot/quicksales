<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Varun Shoor
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2014, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'invaliddatareceived'           => 'Ongeldige of onjuist geformuleerde gegevens ontvangen; kan niet verder gaan.',
    'errordataprocess'              => 'The API sub system received mailformed data after function execution; unable to proceed.',
    'invalidemail'                  => 'Ongeldig emailadres. Controleer de schrijfwijze van het emailadres.',

    // ======= CORE =======
    'staffusernameexists'           => 'Kan medewerkergebruiker niet toevoegen of bijwerken. Er bestaat al een medewerkergebruiker met dezelfde naam.',
    'staffdoesnotexist'             => 'De medewerkergebruiker bestaat niet',
    'staffgroupdoesnotexist'        => 'De medewerkergroep bestaat niet',
    'settingdoesnotexist'           => 'Het opgegeven instellingsveld bestaat niet',
    'departmentmodmismatch'         => 'De opgegeven afdelingsapp voor de bovenliggende afdeling %s (%s) komt niet overeen met de opgegeven app (%s)',
    'departmentmultipleparenterror' => SWIFTPRODUCT . ' staat alleen onderafdelingen toe op één niveau. Kan niet toewijzen aan een bestaande onderafdeling',
    'errorinvaliddepartment'        => 'De afdeling bestaat niet',
    'errorinvalidparentdepartment'  => 'De bovenliggende afdeling bestaat niet',
    // ======= TICKETS =======
);

return $__LANG;
