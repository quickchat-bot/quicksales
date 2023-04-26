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
 * The Time Zone Container Class
 *
 * @author Varun Shoor
 */
class SWIFT_TimeZone extends SWIFT_Library
{
    /**
     * Constructor
     *
     * @author Varun Shoore
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Destructor
     *
     * @author Varun Shoore
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Get the Time Zone list
     *
     * @author Varun Shoor
     * @return array The Time Zone List
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_timeZoneContainer = array(
            array('title' => 'Europe/Amsterdam', 'value' => 'Europe/Amsterdam'),
            array('title' => 'Europe/Andorra', 'value' => 'Europe/Andorra'),
            array('title' => 'Europe/Athens', 'value' => 'Europe/Athens'),
            array('title' => 'Europe/Belgrade', 'value' => 'Europe/Belgrade'),
            array('title' => 'Europe/Berlin', 'value' => 'Europe/Berlin'),
            array('title' => 'Europe/Bratislava', 'value' => 'Europe/Bratislava'),
            array('title' => 'Europe/Brussels', 'value' => 'Europe/Brussels'),
            array('title' => 'Europe/Bucharest', 'value' => 'Europe/Bucharest'),
            array('title' => 'Europe/Budapest', 'value' => 'Europe/Budapest'),
            array('title' => 'Europe/Chisinau', 'value' => 'Europe/Chisinau'),
            array('title' => 'Europe/Copenhagen', 'value' => 'Europe/Copenhagen'),
            array('title' => 'Europe/Dublin', 'value' => 'Europe/Dublin'),
            array('title' => 'Europe/Gibraltar', 'value' => 'Europe/Gibraltar'),
            array('title' => 'Europe/Guernsey', 'value' => 'Europe/Guernsey'),
            array('title' => 'Europe/Helsinki', 'value' => 'Europe/Helsinki'),
            array('title' => 'Europe/Isle_of_Man', 'value' => 'Europe/Isle_of_Man'),
            array('title' => 'Europe/Istanbul', 'value' => 'Europe/Istanbul'),
            array('title' => 'Europe/Jersey', 'value' => 'Europe/Jersey'),
            array('title' => 'Europe/Kaliningrad', 'value' => 'Europe/Kaliningrad'),
            array('title' => 'Europe/Kiev', 'value' => 'Europe/Kiev'),
            array('title' => 'Europe/Lisbon', 'value' => 'Europe/Lisbon'),
            array('title' => 'Europe/Ljubljana', 'value' => 'Europe/Ljubljana'),
            array('title' => 'Europe/London', 'value' => 'Europe/London'),
            array('title' => 'Europe/Luxembourg', 'value' => 'Europe/Luxembourg'),
            array('title' => 'Europe/Madrid', 'value' => 'Europe/Madrid'),
            array('title' => 'Europe/Malta', 'value' => 'Europe/Malta'),
            array('title' => 'Europe/Mariehamn', 'value' => 'Europe/Mariehamn'),
            array('title' => 'Europe/Minsk', 'value' => 'Europe/Minsk'),
            array('title' => 'Europe/Monaco', 'value' => 'Europe/Monaco'),
            array('title' => 'Europe/Moscow', 'value' => 'Europe/Moscow'),
            array('title' => 'Europe/Oslo', 'value' => 'Europe/Oslo'),
            array('title' => 'Europe/Paris', 'value' => 'Europe/Paris'),
            array('title' => 'Europe/Podgorica', 'value' => 'Europe/Podgorica'),
            array('title' => 'Europe/Prague', 'value' => 'Europe/Prague'),
            array('title' => 'Europe/Riga', 'value' => 'Europe/Riga'),
            array('title' => 'Europe/Rome', 'value' => 'Europe/Rome'),
            array('title' => 'Europe/Samara', 'value' => 'Europe/Samara'),
            array('title' => 'Europe/San_Marino', 'value' => 'Europe/San_Marino'),
            array('title' => 'Europe/Sarajevo', 'value' => 'Europe/Sarajevo'),
            array('title' => 'Europe/Simferopol', 'value' => 'Europe/Simferopol'),
            array('title' => 'Europe/Skopje', 'value' => 'Europe/Skopje'),
            array('title' => 'Europe/Sofia', 'value' => 'Europe/Sofia'),
            array('title' => 'Europe/Stockholm', 'value' => 'Europe/Stockholm'),
            array('title' => 'Europe/Tallinn', 'value' => 'Europe/Tallinn'),
            array('title' => 'Europe/Tirane', 'value' => 'Europe/Tirane'),
            array('title' => 'Europe/Uzhgorod', 'value' => 'Europe/Uzhgorod'),
            array('title' => 'Europe/Vaduz', 'value' => 'Europe/Vaduz'),
            array('title' => 'Europe/Vatican', 'value' => 'Europe/Vatican'),
            array('title' => 'Europe/Vienna', 'value' => 'Europe/Vienna'),
            array('title' => 'Europe/Vilnius', 'value' => 'Europe/Vilnius'),
            array('title' => 'Europe/Volgograd', 'value' => 'Europe/Volgograd'),
            array('title' => 'Europe/Warsaw', 'value' => 'Europe/Warsaw'),
            array('title' => 'Europe/Zagreb', 'value' => 'Europe/Zagreb'),
            array('title' => 'Europe/Zaporozhye', 'value' => 'Europe/Zaporozhye'),
            array('title' => 'Europe/Zurich', 'value' => 'Europe/Zurich'),
            array('title' => 'Australia/Adelaide', 'value' => 'Australia/Adelaide'),
            array('title' => 'Australia/Brisbane', 'value' => 'Australia/Brisbane'),
            array('title' => 'Australia/Broken_Hill', 'value' => 'Australia/Broken_Hill'),
            array('title' => 'Australia/Currie', 'value' => 'Australia/Currie'),
            array('title' => 'Australia/Darwin', 'value' => 'Australia/Darwin'),
            array('title' => 'Australia/Eucla', 'value' => 'Australia/Eucla'),
            array('title' => 'Australia/Hobart', 'value' => 'Australia/Hobart'),
            array('title' => 'Australia/Lindeman', 'value' => 'Australia/Lindeman'),
            array('title' => 'Australia/Lord_Howe', 'value' => 'Australia/Lord_Howe'),
            array('title' => 'Australia/Melbourne', 'value' => 'Australia/Melbourne'),
            array('title' => 'Australia/Perth', 'value' => 'Australia/Perth'),
            array('title' => 'Australia/Sydney', 'value' => 'Australia/Sydney'),
            array('title' => 'Asia/Aden', 'value' => 'Asia/Aden'),
            array('title' => 'Asia/Almaty', 'value' => 'Asia/Almaty'),
            array('title' => 'Asia/Amman', 'value' => 'Asia/Amman'),
            array('title' => 'Asia/Anadyr', 'value' => 'Asia/Anadyr'),
            array('title' => 'Asia/Aqtau', 'value' => 'Asia/Aqtau'),
            array('title' => 'Asia/Aqtobe', 'value' => 'Asia/Aqtobe'),
            array('title' => 'Asia/Ashgabat', 'value' => 'Asia/Ashgabat'),
            array('title' => 'Asia/Baghdad', 'value' => 'Asia/Baghdad'),
            array('title' => 'Asia/Bahrain', 'value' => 'Asia/Bahrain'),
            array('title' => 'Asia/Baku', 'value' => 'Asia/Baku'),
            array('title' => 'Asia/Bangkok', 'value' => 'Asia/Bangkok'),
            array('title' => 'Asia/Beirut', 'value' => 'Asia/Beirut'),
            array('title' => 'Asia/Bishkek', 'value' => 'Asia/Bishkek'),
            array('title' => 'Asia/Brunei', 'value' => 'Asia/Brunei'),
            array('title' => 'Asia/Choibalsan', 'value' => 'Asia/Choibalsan'),
            array('title' => 'Asia/Colombo', 'value' => 'Asia/Colombo'),
            array('title' => 'Asia/Dacca', 'value' => 'Asia/Dacca'),
            array('title' => 'Asia/Damascus', 'value' => 'Asia/Damascus'),
            array('title' => 'Asia/Dhaka', 'value' => 'Asia/Dhaka'),
            array('title' => 'Asia/Dili', 'value' => 'Asia/Dili'),
            array('title' => 'Asia/Dubai', 'value' => 'Asia/Dubai'),
            array('title' => 'Asia/Dushanbe', 'value' => 'Asia/Dushanbe'),
            array('title' => 'Asia/Gaza', 'value' => 'Asia/Gaza'),
            array('title' => 'Asia/Hong_Kong', 'value' => 'Asia/Hong_Kong'),
            array('title' => 'Asia/Hovd', 'value' => 'Asia/Hovd'),
            array('title' => 'Asia/Irkutsk', 'value' => 'Asia/Irkutsk'),
            array('title' => 'Asia/Jakarta', 'value' => 'Asia/Jakarta'),
            array('title' => 'Asia/Jayapura', 'value' => 'Asia/Jayapura'),
            array('title' => 'Asia/Jerusalem', 'value' => 'Asia/Jerusalem'),
            array('title' => 'Asia/Kabul', 'value' => 'Asia/Kabul'),
            array('title' => 'Asia/Kamchatka', 'value' => 'Asia/Kamchatka'),
            array('title' => 'Asia/Karachi', 'value' => 'Asia/Karachi'),
            array('title' => 'Asia/Kashgar', 'value' => 'Asia/Kashgar'),
            array('title' => 'Asia/Krasnoyarsk', 'value' => 'Asia/Krasnoyarsk'),
            array('title' => 'Asia/Kuala_Lumpur', 'value' => 'Asia/Kuala_Lumpur'),
            array('title' => 'Asia/Kuching', 'value' => 'Asia/Kuching'),
            array('title' => 'Asia/Kuwait', 'value' => 'Asia/Kuwait'),
            array('title' => 'Asia/Macau', 'value' => 'Asia/Macau'),
            array('title' => 'Asia/Magadan', 'value' => 'Asia/Magadan'),
            array('title' => 'Asia/Makassar', 'value' => 'Asia/Makassar'),
            array('title' => 'Asia/Manila', 'value' => 'Asia/Manila'),
            array('title' => 'Asia/Muscat', 'value' => 'Asia/Muscat'),
            array('title' => 'Asia/Nicosia', 'value' => 'Asia/Nicosia'),
            array('title' => 'Asia/Novosibirsk', 'value' => 'Asia/Novosibirsk'),
            array('title' => 'Asia/Omsk', 'value' => 'Asia/Omsk'),
            array('title' => 'Asia/Oral', 'value' => 'Asia/Oral'),
            array('title' => 'Asia/Phnom_Penh', 'value' => 'Asia/Phnom_Penh'),
            array('title' => 'Asia/Pontianak', 'value' => 'Asia/Pontianak'),
            array('title' => 'Asia/Pyongyang', 'value' => 'Asia/Pyongyang'),
            array('title' => 'Asia/Qatar', 'value' => 'Asia/Qatar'),
            array('title' => 'Asia/Qyzylorda', 'value' => 'Asia/Qyzylorda'),
            array('title' => 'Asia/Rangoon', 'value' => 'Asia/Rangoon'),
            array('title' => 'Asia/Riyadh', 'value' => 'Asia/Riyadh'),
            array('title' => 'Asia/Sakhalin', 'value' => 'Asia/Sakhalin'),
            array('title' => 'Asia/Samarkand', 'value' => 'Asia/Samarkand'),
            array('title' => 'Asia/Seoul', 'value' => 'Asia/Seoul'),
            array('title' => 'Asia/Shanghai', 'value' => 'Asia/Shanghai'),
            array('title' => 'Asia/Singapore', 'value' => 'Asia/Singapore'),
            array('title' => 'Asia/Taipei', 'value' => 'Asia/Taipei'),
            array('title' => 'Asia/Tashkent', 'value' => 'Asia/Tashkent'),
            array('title' => 'Asia/Tbilisi', 'value' => 'Asia/Tbilisi'),
            array('title' => 'Asia/Tehran', 'value' => 'Asia/Tehran'),
            array('title' => 'Asia/Thimphu', 'value' => 'Asia/Thimphu'),
            array('title' => 'Asia/Tokyo', 'value' => 'Asia/Tokyo'),
            array('title' => 'Asia/Ulaanbaatar', 'value' => 'Asia/Ulaanbaatar'),
            array('title' => 'Asia/Urumqi', 'value' => 'Asia/Urumqi'),
            array('title' => 'Asia/Vientiane', 'value' => 'Asia/Vientiane'),
            array('title' => 'Asia/Vladivostok', 'value' => 'Asia/Vladivostok'),
            array('title' => 'Asia/Yakutsk', 'value' => 'Asia/Yakutsk'),
            array('title' => 'Asia/Yekaterinburg', 'value' => 'Asia/Yekaterinburg'),
            array('title' => 'Asia/Yerevan', 'value' => 'Asia/Yerevan'),
            array('title' => 'Asia/Chita', 'value' => 'Asia/Chita'),
            array('title' => 'Asia/Ho_Chi_Minh', 'value' => 'Asia/Ho_Chi_Minh'),
            array('title' => 'Asia/Kathmandu', 'value' => 'Asia/Kathmandu'),
            array('title' => 'Asia/Khandyga', 'value' => 'Asia/Khandyga'),
            array('title' => 'Asia/Kolkata', 'value' => 'Asia/Kolkata'),
            array('title' => 'Asia/Novokuznetsk', 'value' => 'Asia/Novokuznetsk'),
            array('title' => 'Asia/Srednekolymsk', 'value' => 'Asia/Srednekolymsk'),
            array('title' => 'Asia/Ust-Nera', 'value' => 'Asia/Ust-Nera'),
            array('title' => 'Asia/Hebron', 'value' => 'Asia/Hebron'),
            array('title' => 'America/Adak', 'value' => 'America/Adak'),
            array('title' => 'America/Anchorage', 'value' => 'America/Anchorage'),
            array('title' => 'America/Anguilla', 'value' => 'America/Anguilla'),
            array('title' => 'America/Antigua', 'value' => 'America/Antigua'),
            array('title' => 'America/Araguaina', 'value' => 'America/Araguaina'),
            array('title' => 'America/Argentina/Buenos_Aires', 'value' => 'America/Argentina/Buenos_Aires'),
            array('title' => 'America/Argentina/Catamarca', 'value' => 'America/Argentina/Catamarca'),
            array('title' => 'America/Argentina/ComodRivadavia', 'value' => 'America/Argentina/ComodRivadavia'),
            array('title' => 'America/Argentina/Cordoba', 'value' => 'America/Argentina/Cordoba'),
            array('title' => 'America/Argentina/Jujuy', 'value' => 'America/Argentina/Jujuy'),
            array('title' => 'America/Argentina/La_Rioja', 'value' => 'America/Argentina/La_Rioja'),
            array('title' => 'America/Argentina/Mendoza', 'value' => 'America/Argentina/Mendoza'),
            array('title' => 'America/Argentina/Rio_Gallegos', 'value' => 'America/Argentina/Rio_Gallegos'),
            array('title' => 'America/Argentina/San_Juan', 'value' => 'America/Argentina/San_Juan'),
            array('title' => 'America/Argentina/Tucuman', 'value' => 'America/Argentina/Tucuman'),
            array('title' => 'America/Argentina/Ushuaia', 'value' => 'America/Argentina/Ushuaia'),
            array('title' => 'America/Argentina/Salta', 'value' => 'America/Argentina/Salta'),
            array('title' => 'America/Argentina/San_Luis', 'value' => 'America/Argentina/San_Luis'),
            array('title' => 'America/Aruba', 'value' => 'America/Aruba'),
            array('title' => 'America/Asuncion', 'value' => 'America/Asuncion'),
            array('title' => 'America/Atikokan', 'value' => 'America/Atikokan'),
            array('title' => 'America/Bahia', 'value' => 'America/Bahia'),
            array('title' => 'America/Barbados', 'value' => 'America/Barbados'),
            array('title' => 'America/Belem', 'value' => 'America/Belem'),
            array('title' => 'America/Belize', 'value' => 'America/Belize'),
            array('title' => 'America/Blanc-Sablon', 'value' => 'America/Blanc-Sablon'),
            array('title' => 'America/Boa_Vista', 'value' => 'America/Boa_Vista'),
            array('title' => 'America/Bogota', 'value' => 'America/Bogota'),
            array('title' => 'America/Boise', 'value' => 'America/Boise'),
            array('title' => 'America/Cambridge_Bay', 'value' => 'America/Cambridge_Bay'),
            array('title' => 'America/Campo_Grande', 'value' => 'America/Campo_Grande'),
            array('title' => 'America/Cancun', 'value' => 'America/Cancun'),
            array('title' => 'America/Caracas', 'value' => 'America/Caracas'),
            array('title' => 'America/Cayenne', 'value' => 'America/Cayenne'),
            array('title' => 'America/Cayman', 'value' => 'America/Cayman'),
            array('title' => 'America/Chicago', 'value' => 'America/Chicago'),
            array('title' => 'America/Chihuahua', 'value' => 'America/Chihuahua'),
            array('title' => 'America/Costa_Rica', 'value' => 'America/Costa_Rica'),
            array('title' => 'America/Cuiaba', 'value' => 'America/Cuiaba'),
            array('title' => 'America/Curacao', 'value' => 'America/Curacao'),
            array('title' => 'America/Danmarkshavn', 'value' => 'America/Danmarkshavn'),
            array('title' => 'America/Dawson', 'value' => 'America/Dawson'),
            array('title' => 'America/Dawson_Creek', 'value' => 'America/Dawson_Creek'),
            array('title' => 'America/Denver', 'value' => 'America/Denver'),
            array('title' => 'America/Detroit', 'value' => 'America/Detroit'),
            array('title' => 'America/Dominica', 'value' => 'America/Dominica'),
            array('title' => 'America/Edmonton', 'value' => 'America/Edmonton'),
            array('title' => 'America/Eirunepe', 'value' => 'America/Eirunepe'),
            array('title' => 'America/El_Salvador', 'value' => 'America/El_Salvador'),
            array('title' => 'America/Fortaleza', 'value' => 'America/Fortaleza'),
            array('title' => 'America/Glace_Bay', 'value' => 'America/Glace_Bay'),
            array('title' => 'America/Godthab', 'value' => 'America/Godthab'),
            array('title' => 'America/Goose_Bay', 'value' => 'America/Goose_Bay'),
            array('title' => 'America/Grand_Turk', 'value' => 'America/Grand_Turk'),
            array('title' => 'America/Grenada', 'value' => 'America/Grenada'),
            array('title' => 'America/Guadeloupe', 'value' => 'America/Guadeloupe'),
            array('title' => 'America/Guatemala', 'value' => 'America/Guatemala'),
            array('title' => 'America/Guayaquil', 'value' => 'America/Guayaquil'),
            array('title' => 'America/Guyana', 'value' => 'America/Guyana'),
            array('title' => 'America/Halifax', 'value' => 'America/Halifax'),
            array('title' => 'America/Havana', 'value' => 'America/Havana'),
            array('title' => 'America/Hermosillo', 'value' => 'America/Hermosillo'),
            array('title' => 'America/Indiana/Indianapolis', 'value' => 'America/Indiana/Indianapolis'),
            array('title' => 'America/Indiana/Knox', 'value' => 'America/Indiana/Knox'),
            array('title' => 'America/Indiana/Marengo', 'value' => 'America/Indiana/Marengo'),
            array('title' => 'America/Indiana/Petersburg', 'value' => 'America/Indiana/Petersburg'),
            array('title' => 'America/Indiana/Tell_City', 'value' => 'America/Indiana/Tell_City'),
            array('title' => 'America/Indiana/Vevay', 'value' => 'America/Indiana/Vevay'),
            array('title' => 'America/Indiana/Vincennes', 'value' => 'America/Indiana/Vincennes'),
            array('title' => 'America/Indiana/Winamac', 'value' => 'America/Indiana/Winamac'),
            array('title' => 'America/Inuvik', 'value' => 'America/Inuvik'),
            array('title' => 'America/Iqaluit', 'value' => 'America/Iqaluit'),
            array('title' => 'America/Jamaica', 'value' => 'America/Jamaica'),
            array('title' => 'America/Juneau', 'value' => 'America/Juneau'),
            array('title' => 'America/Kentucky/Louisville', 'value' => 'America/Kentucky/Louisville'),
            array('title' => 'America/Kentucky/Monticello', 'value' => 'America/Kentucky/Monticello'),
            array('title' => 'America/La_Paz', 'value' => 'America/La_Paz'),
            array('title' => 'America/Lima', 'value' => 'America/Lima'),
            array('title' => 'America/Los_Angeles', 'value' => 'America/Los_Angeles'),
            array('title' => 'America/Maceio', 'value' => 'America/Maceio'),
            array('title' => 'America/Managua', 'value' => 'America/Managua'),
            array('title' => 'America/Manaus', 'value' => 'America/Manaus'),
            array('title' => 'America/Martinique', 'value' => 'America/Martinique'),
            array('title' => 'America/Mazatlan', 'value' => 'America/Mazatlan'),
            array('title' => 'America/Menominee', 'value' => 'America/Menominee'),
            array('title' => 'America/Merida', 'value' => 'America/Merida'),
            array('title' => 'America/Mexico_City', 'value' => 'America/Mexico_City'),
            array('title' => 'America/Miquelon', 'value' => 'America/Miquelon'),
            array('title' => 'America/Moncton', 'value' => 'America/Moncton'),
            array('title' => 'America/Monterrey', 'value' => 'America/Monterrey'),
            array('title' => 'America/Montevideo', 'value' => 'America/Montevideo'),
            array('title' => 'America/Montserrat', 'value' => 'America/Montserrat'),
            array('title' => 'America/Nassau', 'value' => 'America/Nassau'),
            array('title' => 'America/New_York', 'value' => 'America/New_York'),
            array('title' => 'America/Nipigon', 'value' => 'America/Nipigon'),
            array('title' => 'America/Nome', 'value' => 'America/Nome'),
            array('title' => 'America/Noronha', 'value' => 'America/Noronha'),
            array('title' => 'America/North_Dakota/Center', 'value' => 'America/North_Dakota/Center'),
            array('title' => 'America/North_Dakota/New_Salem', 'value' => 'America/North_Dakota/New_Salem'),
            array('title' => 'America/Panama', 'value' => 'America/Panama'),
            array('title' => 'America/Pangnirtung', 'value' => 'America/Pangnirtung'),
            array('title' => 'America/Paramaribo', 'value' => 'America/Paramaribo'),
            array('title' => 'America/Phoenix', 'value' => 'America/Phoenix'),
            array('title' => 'America/Port-au-Prince', 'value' => 'America/Port-au-Prince'),
            array('title' => 'America/Port_of_Spain', 'value' => 'America/Port_of_Spain'),
            array('title' => 'America/Porto_Velho', 'value' => 'America/Porto_Velho'),
            array('title' => 'America/Puerto_Rico', 'value' => 'America/Puerto_Rico'),
            array('title' => 'America/Rainy_River', 'value' => 'America/Rainy_River'),
            array('title' => 'America/Rankin_Inlet', 'value' => 'America/Rankin_Inlet'),
            array('title' => 'America/Recife', 'value' => 'America/Recife'),
            array('title' => 'America/Regina', 'value' => 'America/Regina'),
            array('title' => 'America/Resolute', 'value' => 'America/Resolute'),
            array('title' => 'America/Rio_Branco', 'value' => 'America/Rio_Branco'),
            array('title' => 'America/Santiago', 'value' => 'America/Santiago'),
            array('title' => 'America/Santo_Domingo', 'value' => 'America/Santo_Domingo'),
            array('title' => 'America/Sao_Paulo', 'value' => 'America/Sao_Paulo'),
            array('title' => 'America/Scoresbysund', 'value' => 'America/Scoresbysund'),
            array('title' => 'America/St_Johns', 'value' => 'America/St_Johns'),
            array('title' => 'America/St_Kitts', 'value' => 'America/St_Kitts'),
            array('title' => 'America/St_Lucia', 'value' => 'America/St_Lucia'),
            array('title' => 'America/St_Thomas', 'value' => 'America/St_Thomas'),
            array('title' => 'America/St_Vincent', 'value' => 'America/St_Vincent'),
            array('title' => 'America/Swift_Current', 'value' => 'America/Swift_Current'),
            array('title' => 'America/Tegucigalpa', 'value' => 'America/Tegucigalpa'),
            array('title' => 'America/Thule', 'value' => 'America/Thule'),
            array('title' => 'America/Thunder_Bay', 'value' => 'America/Thunder_Bay'),
            array('title' => 'America/Tijuana', 'value' => 'America/Tijuana'),
            array('title' => 'America/Toronto', 'value' => 'America/Toronto'),
            array('title' => 'America/Tortola', 'value' => 'America/Tortola'),
            array('title' => 'America/Vancouver', 'value' => 'America/Vancouver'),
            array('title' => 'America/Whitehorse', 'value' => 'America/Whitehorse'),
            array('title' => 'America/Winnipeg', 'value' => 'America/Winnipeg'),
            array('title' => 'America/Yakutat', 'value' => 'America/Yakutat'),
            array('title' => 'America/Yellowknife', 'value' => 'America/Yellowknife'),
            array('title' => 'America/Bahia_Banderas', 'value' => 'America/Bahia_Banderas'),
            array('title' => 'America/Kralendijk', 'value' => 'America/Kralendijk'),
            array('title' => 'America/Matamoros', 'value' => 'America/Matamoros'),
            array('title' => 'America/Santa_Isabel', 'value' => 'America/Santa_Isabel'),
            array('title' => 'America/North_Dakota/Beulah', 'value' => 'America/North_Dakota/Beulah'),
            array('title' => 'America/Ojinaga', 'value' => 'America/Ojinaga'),
            array('title' => 'America/Santarem', 'value' => 'America/Santarem'),
            array('title' => 'America/Sitka', 'value' => 'America/Sitka'),
            array('title' => 'America/St_Barthelemy', 'value' => 'America/St_Barthelemy'),
            array('title' => 'America/Creston', 'value' => 'America/Creston'),
            array('title' => 'America/Port_of_Spain', 'value' => 'America/Port_of_Spain'),
            array('title' => 'America/Metlakatla', 'value' => 'America/Metlakatla'),
            array('title' => 'Africa/Abidjan', 'value' => 'Africa/Abidjan'),
            array('title' => 'Africa/Accra', 'value' => 'Africa/Accra'),
            array('title' => 'Africa/Addis_Ababa', 'value' => 'Africa/Addis_Ababa'),
            array('title' => 'Africa/Algiers', 'value' => 'Africa/Algiers'),
            array('title' => 'Africa/Bamako', 'value' => 'Africa/Bamako'),
            array('title' => 'Africa/Bangui', 'value' => 'Africa/Bangui'),
            array('title' => 'Africa/Banjul', 'value' => 'Africa/Banjul'),
            array('title' => 'Africa/Bissau', 'value' => 'Africa/Bissau'),
            array('title' => 'Africa/Blantyre', 'value' => 'Africa/Blantyre'),
            array('title' => 'Africa/Brazzaville', 'value' => 'Africa/Brazzaville'),
            array('title' => 'Africa/Bujumbura', 'value' => 'Africa/Bujumbura'),
            array('title' => 'Africa/Cairo', 'value' => 'Africa/Cairo'),
            array('title' => 'Africa/Casablanca', 'value' => 'Africa/Casablanca'),
            array('title' => 'Africa/Ceuta', 'value' => 'Africa/Ceuta'),
            array('title' => 'Africa/Conakry', 'value' => 'Africa/Conakry'),
            array('title' => 'Africa/Dakar', 'value' => 'Africa/Dakar'),
            array('title' => 'Africa/Dar_es_Salaam', 'value' => 'Africa/Dar_es_Salaam'),
            array('title' => 'Africa/Djibouti', 'value' => 'Africa/Djibouti'),
            array('title' => 'Africa/Douala', 'value' => 'Africa/Douala'),
            array('title' => 'Africa/El_Aaiun', 'value' => 'Africa/El_Aaiun'),
            array('title' => 'Africa/Freetown', 'value' => 'Africa/Freetown'),
            array('title' => 'Africa/Gaborone', 'value' => 'Africa/Gaborone'),
            array('title' => 'Africa/Harare', 'value' => 'Africa/Harare'),
            array('title' => 'Africa/Johannesburg', 'value' => 'Africa/Johannesburg'),
            array('title' => 'Africa/Kampala', 'value' => 'Africa/Kampala'),
            array('title' => 'Africa/Khartoum', 'value' => 'Africa/Khartoum'),
            array('title' => 'Africa/Kigali', 'value' => 'Africa/Kigali'),
            array('title' => 'Africa/Kinshasa', 'value' => 'Africa/Kinshasa'),
            array('title' => 'Africa/Lagos', 'value' => 'Africa/Lagos'),
            array('title' => 'Africa/Libreville', 'value' => 'Africa/Libreville'),
            array('title' => 'Africa/Lome', 'value' => 'Africa/Lome'),
            array('title' => 'Africa/Luanda', 'value' => 'Africa/Luanda'),
            array('title' => 'Africa/Lubumbashi', 'value' => 'Africa/Lubumbashi'),
            array('title' => 'Africa/Lusaka', 'value' => 'Africa/Lusaka'),
            array('title' => 'Africa/Malabo', 'value' => 'Africa/Malabo'),
            array('title' => 'Africa/Maputo', 'value' => 'Africa/Maputo'),
            array('title' => 'Africa/Maseru', 'value' => 'Africa/Maseru'),
            array('title' => 'Africa/Mbabane', 'value' => 'Africa/Mbabane'),
            array('title' => 'Africa/Mogadishu', 'value' => 'Africa/Mogadishu'),
            array('title' => 'Africa/Monrovia', 'value' => 'Africa/Monrovia'),
            array('title' => 'Africa/Nairobi', 'value' => 'Africa/Nairobi'),
            array('title' => 'Africa/Ndjamena', 'value' => 'Africa/Ndjamena'),
            array('title' => 'Africa/Niamey', 'value' => 'Africa/Niamey'),
            array('title' => 'Africa/Nouakchott', 'value' => 'Africa/Nouakchott'),
            array('title' => 'Africa/Ouagadougou', 'value' => 'Africa/Ouagadougou'),
            array('title' => 'Africa/Porto-Novo', 'value' => 'Africa/Porto-Novo'),
            array('title' => 'Africa/Sao_Tome', 'value' => 'Africa/Sao_Tome'),
            array('title' => 'Africa/Timbuktu', 'value' => 'Africa/Timbuktu'),
            array('title' => 'Africa/Tripoli', 'value' => 'Africa/Tripoli'),
            array('title' => 'Africa/Tunis', 'value' => 'Africa/Tunis'),
            array('title' => 'Africa/Windhoek', 'value' => 'Africa/Windhoek'),
            array('title' => 'Africa/Juba', 'value' => 'Africa/Juba'),
            array('title' => 'Atlantic/Azores', 'value' => 'Atlantic/Azores'),
            array('title' => 'Atlantic/Bermuda', 'value' => 'Atlantic/Bermuda'),
            array('title' => 'Atlantic/Canary', 'value' => 'Atlantic/Canary'),
            array('title' => 'Atlantic/Cape_Verde', 'value' => 'Atlantic/Cape_Verde'),
            array('title' => 'Atlantic/Faroe', 'value' => 'Atlantic/Faroe'),
            array('title' => 'Atlantic/Madeira', 'value' => 'Atlantic/Madeira'),
            array('title' => 'Atlantic/Reykjavik', 'value' => 'Atlantic/Reykjavik'),
            array('title' => 'Atlantic/South_Georgia', 'value' => 'Atlantic/South_Georgia'),
            array('title' => 'Atlantic/St_Helena', 'value' => 'Atlantic/St_Helena'),
            array('title' => 'Atlantic/Stanley', 'value' => 'Atlantic/Stanley'),
            array('title' => 'Indian/Antananarivo', 'value' => 'Indian/Antananarivo'),
            array('title' => 'Indian/Chagos', 'value' => 'Indian/Chagos'),
            array('title' => 'Indian/Christmas', 'value' => 'Indian/Christmas'),
            array('title' => 'Indian/Cocos', 'value' => 'Indian/Cocos'),
            array('title' => 'Indian/Comoro', 'value' => 'Indian/Comoro'),
            array('title' => 'Indian/Kerguelen', 'value' => 'Indian/Kerguelen'),
            array('title' => 'Indian/Mahe', 'value' => 'Indian/Mahe'),
            array('title' => 'Indian/Maldives', 'value' => 'Indian/Maldives'),
            array('title' => 'Indian/Mauritius', 'value' => 'Indian/Mauritius'),
            array('title' => 'Indian/Mayotte', 'value' => 'Indian/Mayotte'),
            array('title' => 'Indian/Reunion', 'value' => 'Indian/Reunion'),
            array('title' => 'Pacific/Apia', 'value' => 'Pacific/Apia'),
            array('title' => 'Pacific/Auckland', 'value' => 'Pacific/Auckland'),
            array('title' => 'Pacific/Chatham', 'value' => 'Pacific/Chatham'),
            array('title' => 'Pacific/Easter', 'value' => 'Pacific/Easter'),
            array('title' => 'Pacific/Efate', 'value' => 'Pacific/Efate'),
            array('title' => 'Pacific/Enderbury', 'value' => 'Pacific/Enderbury'),
            array('title' => 'Pacific/Fakaofo', 'value' => 'Pacific/Fakaofo'),
            array('title' => 'Pacific/Fiji', 'value' => 'Pacific/Fiji'),
            array('title' => 'Pacific/Funafuti', 'value' => 'Pacific/Funafuti'),
            array('title' => 'Pacific/Galapagos', 'value' => 'Pacific/Galapagos'),
            array('title' => 'Pacific/Gambier', 'value' => 'Pacific/Gambier'),
            array('title' => 'Pacific/Guadalcanal', 'value' => 'Pacific/Guadalcanal'),
            array('title' => 'Pacific/Guam', 'value' => 'Pacific/Guam'),
            array('title' => 'Pacific/Honolulu', 'value' => 'Pacific/Honolulu'),
            array('title' => 'Pacific/Johnston', 'value' => 'Pacific/Johnston'),
            array('title' => 'Pacific/Kiritimati', 'value' => 'Pacific/Kiritimati'),
            array('title' => 'Pacific/Kosrae', 'value' => 'Pacific/Kosrae'),
            array('title' => 'Pacific/Kwajalein', 'value' => 'Pacific/Kwajalein'),
            array('title' => 'Pacific/Majuro', 'value' => 'Pacific/Majuro'),
            array('title' => 'Pacific/Marquesas', 'value' => 'Pacific/Marquesas'),
            array('title' => 'Pacific/Midway', 'value' => 'Pacific/Midway'),
            array('title' => 'Pacific/Nauru', 'value' => 'Pacific/Nauru'),
            array('title' => 'Pacific/Niue', 'value' => 'Pacific/Niue'),
            array('title' => 'Pacific/Norfolk', 'value' => 'Pacific/Norfolk'),
            array('title' => 'Pacific/Noumea', 'value' => 'Pacific/Noumea'),
            array('title' => 'Pacific/Pago_Pago', 'value' => 'Pacific/Pago_Pago'),
            array('title' => 'Pacific/Palau', 'value' => 'Pacific/Palau'),
            array('title' => 'Pacific/Pitcairn', 'value' => 'Pacific/Pitcairn'),
            array('title' => 'Pacific/Pohnpei', 'value' => 'Pacific/Pohnpei'),
            array('title' => 'Pacific/Port_Moresby', 'value' => 'Pacific/Port_Moresby'),
            array('title' => 'Pacific/Rarotonga', 'value' => 'Pacific/Rarotonga'),
            array('title' => 'Pacific/Saipan', 'value' => 'Pacific/Saipan'),
            array('title' => 'Pacific/Tahiti', 'value' => 'Pacific/Tahiti'),
            array('title' => 'Pacific/Tarawa', 'value' => 'Pacific/Tarawa'),
            array('title' => 'Pacific/Tongatapu', 'value' => 'Pacific/Tongatapu'),
            array('title' => 'Pacific/Truk', 'value' => 'Pacific/Truk'),
            array('title' => 'Pacific/Wake', 'value' => 'Pacific/Wake'),
            array('title' => 'Pacific/Wallis', 'value' => 'Pacific/Wallis'),
            array('title' => 'Pacific/Bougainville', 'value' => 'Pacific/Bougainville'),
            array('title' => 'Pacific/Chuuk', 'value' => 'Pacific/Chuuk'),
            array('title' => 'Antarctica/Casey', 'value' => 'Antarctica/Casey'),
            array('title' => 'Antarctica/Davis', 'value' => 'Antarctica/Davis'),
            array('title' => 'Antarctica/DumontDUrville', 'value' => 'Antarctica/DumontDUrville'),
            array('title' => 'Antarctica/Mawson', 'value' => 'Antarctica/Mawson'),
            array('title' => 'Antarctica/McMurdo', 'value' => 'Antarctica/McMurdo'),
            array('title' => 'Antarctica/Palmer', 'value' => 'Antarctica/Palmer'),
            array('title' => 'Antarctica/Rothera', 'value' => 'Antarctica/Rothera'),
            array('title' => 'Antarctica/Syowa', 'value' => 'Antarctica/Syowa'),
            array('title' => 'Antarctica/Vostok', 'value' => 'Antarctica/Vostok'),
            array('title' => 'Antarctica/Macquarie', 'value' => 'Antarctica/Macquarie'),
            array('title' => 'Antarctica/Troll', 'value' => 'Antarctica/Troll'),
            array('title' => 'UTC', 'value' => 'UTC'),
            array('title' => 'Arctic/Longyearbyen', 'value' => 'Arctic/Longyearbyen'));

        return $_timeZoneContainer;
    }
}
?>
