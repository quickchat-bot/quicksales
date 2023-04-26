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

/**
 * The Extended Country Container
 *
 * @author Varun Shoor
 */
class SWIFT_ExtendedCountryContainer extends SWIFT_Library
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
     * Retrieve the Extended Country Container
     *
     * @author Varun Shoor
     * @return array
     * @throws SWIFT_Exception If the Class is not Loaded
     */
    public function Get()
    {
        if (!$this->GetIsClassLoaded())
        {
            throw new SWIFT_Exception(SWIFT_CLASSNOTLOADED);
        }

        $_countryContainer = array(
            0 => array('Afghanistan', 'Islamic State of Afghanistan', 'Kabul', 'AFN', 'Afghani', '+93', 'AF', 'AFG', '004', '.af'),
            1 => array('Albania', 'Republic of Albania', 'Tirana', 'ALL', 'Lek', '+355', 'AL', 'ALB', '008', '.al'),
            2 => array('Algeria', 'People\'s Democratic Republic of Algeria', 'Algiers', 'DZD', 'Dinar', '+213', 'DZ', 'DZA', '012', '.dz'),
            3 => array('Andorra', 'Principality of Andorra', 'Andorra la Vella', 'EUR', 'Euro', '+376', 'AD', 'AND', '020', '.ad'),
            4 => array('Angola', 'Republic of Angola', 'Luanda', 'AOA', 'Kwanza', '+244', 'AO', 'AGO', '024', '.ao'),
            5 => array('Antigua and Barbuda', '', 'Saint John\'s', 'XCD', 'Dollar', '+1-268', 'AG', 'ATG', '028', '.ag'),
            6 => array('Argentina', 'Argentine Republic', 'Buenos Aires', 'ARS', 'Peso', '+54', 'AR', 'ARG', '032', '.ar'),
            7 => array('Armenia', 'Republic of Armenia', 'Yerevan', 'AMD', 'Dram', '+374', 'AM', 'ARM', '051', '.am'),
            8 => array('Australia', 'Commonwealth of Australia', 'Canberra', 'AUD', 'Dollar', '+61', 'AU', 'AUS', '036', '.au'),
            9 => array('Austria', 'Republic of Austria', 'Vienna', 'EUR', 'Euro', '+43', 'AT', 'AUT', '040', '.at'),
            10 => array('Azerbaijan', 'Republic of Azerbaijan', 'Baku', 'AZN', 'Manat', '+994', 'AZ', 'AZE', '031', '.az'),
            11 => array('Bahamas, The', 'Commonwealth of The Bahamas', 'Nassau', 'BSD', 'Dollar', '+1-242', 'BS', 'BHS', '044', '.bs'),
            12 => array('Bahrain', 'Kingdom of Bahrain', 'Manama', 'BHD', 'Dinar', '+973', 'BH', 'BHR', '048', '.bh'),
            13 => array('Bangladesh', 'People\'s Republic of Bangladesh', 'Dhaka', 'BDT', 'Taka', '+880', 'BD', 'BGD', '050', '.bd'),
            14 => array('Barbados', '', 'Bridgetown', 'BBD', 'Dollar', '+1-246', 'BB', 'BRB', '052', '.bb'),
            15 => array('Belarus', 'Republic of Belarus', 'Minsk', 'BYR', 'Ruble', '+375', 'BY', 'BLR', '112', '.by'),
            16 => array('Belgium', 'Kingdom of Belgium', 'Brussels', 'EUR', 'Euro', '+32', 'BE', 'BEL', '056', '.be'),
            17 => array('Belize', '', 'Belmopan', 'BZD', 'Dollar', '+501', 'BZ', 'BLZ', '084', '.bz'),
            18 => array('Benin', 'Republic of Benin', 'Porto-Novo', 'XOF', 'Franc', '+229', 'BJ', 'BEN', '204', '.bj'),
            19 => array('Bhutan', 'Kingdom of Bhutan', 'Thimphu', 'BTN', 'Ngultrum', '+975', 'BT', 'BTN', '064', '.bt'),
            20 => array('Bolivia', 'Republic of Bolivia', 'La Paz (administrative/legislative) and Sucre (judical)', 'BOB', 'Boliviano', '+591', 'BO', 'BOL', '068', '.bo'),
            21 => array('Bosnia and Herzegovina', '', 'Sarajevo', 'BAM', 'Marka', '+387', 'BA', 'BIH', '070', '.ba'),
            22 => array('Botswana', 'Republic of Botswana', 'Gaborone', 'BWP', 'Pula', '+267', 'BW', 'BWA', '072', '.bw'),
            23 => array('Brazil', 'Federative Republic of Brazil', 'Brasilia', 'BRL', 'Real', '+55', 'BR', 'BRA', '076', '.br'),
            24 => array('Brunei', 'Negara Brunei Darussalam', 'Bandar Seri Begawan', 'BND', 'Dollar', '+673', 'BN', 'BRN', '096', '.bn'),
            25 => array('Bulgaria', 'Republic of Bulgaria', 'Sofia', 'BGN', 'Lev', '+359', 'BG', 'BGR', '100', '.bg'),
            26 => array('Burkina Faso', '', 'Ouagadougou', 'XOF', 'Franc', '+226', 'BF', 'BFA', '854', '.bf'),
            27 => array('Burundi', 'Republic of Burundi', 'Bujumbura', 'BIF', 'Franc', '+257', 'BI', 'BDI', '108', '.bi'),
            28 => array('Cambodia', 'Kingdom of Cambodia', 'Phnom Penh', 'KHR', 'Riels', '+855', 'KH', 'KHM', '116', '.kh'),
            29 => array('Cameroon', 'Republic of Cameroon', 'Yaounde', 'XAF', 'Franc', '+237', 'CM', 'CMR', '120', '.cm'),
            30 => array('Canada', '', 'Ottawa', 'CAD', 'Dollar', '+1', 'CA', 'CAN', '124', '.ca'),
            31 => array('Cape Verde', 'Republic of Cape Verde', 'Praia', 'CVE', 'Escudo', '+238', 'CV', 'CPV', '132', '.cv'),
            32 => array('Central African Republic', '', 'Bangui', 'XAF', 'Franc', '+236', 'CF', 'CAF', '140', '.cf'),
            33 => array('Chad', 'Republic of Chad', 'N\'Djamena', 'XAF', 'Franc', '+235', 'TD', 'TCD', '148', '.td'),
            34 => array('Chile', 'Republic of Chile', 'Santiago (administrative/judical) and Valparaiso (legislative)', 'CLP', 'Peso', '+56', 'CL', 'CHL', '152', '.cl'),
            35 => array('China, People\'s Republic of', 'People\'s Republic of China', 'Beijing', 'CNY', 'Yuan Renminbi', '+86', 'CN', 'CHN', '156', '.cn'),
            36 => array('Colombia', 'Republic of Colombia', 'Bogota', 'COP', 'Peso', '+57', 'CO', 'COL', '170', '.co'),
            37 => array('Comoros', 'Union of Comoros', 'Moroni', 'KMF', 'Franc', '+269', 'KM', 'COM', '174', '.km'),
            38 => array('Congo, Democratic Republic of the (Congo – Kinshasa)', 'Democratic Republic of the Congo', 'Kinshasa', 'CDF', 'Franc', '+243', 'CD', 'COD', '180', '.cd'),
            39 => array('Congo, Republic of the (Congo – Brazzaville)', 'Republic of the Congo', 'Brazzaville', 'XAF', 'Franc', '+242', 'CG', 'COG', '178', '.cg'),
            40 => array('Costa Rica', 'Republic of Costa Rica', 'San Jose', 'CRC', 'Colon', '+506', 'CR', 'CRI', '188', '.cr'),
            41 => array('Cote d\'Ivoire (Ivory Coast)', 'Republic of Cote d\'Ivoire', 'Yamoussoukro', 'XOF', 'Franc', '+225', 'CI', 'CIV', '384', '.ci'),
            42 => array('Croatia', 'Republic of Croatia', 'Zagreb', 'HRK', 'Kuna', '+385', 'HR', 'HRV', '191', '.hr'),
            43 => array('Cuba', 'Republic of Cuba', 'Havana', 'CUP', 'Peso', '+53', 'CU', 'CUB', '192', '.cu'),
            44 => array('Cyprus', 'Republic of Cyprus', 'Nicosia', 'CYP', 'Pound', '+357', 'CY', 'CYP', '196', '.cy'),
            45 => array('Czech Republic', '', 'Prague', 'CZK', 'Koruna', '+420', 'CZ', 'CZE', '203', '.cz'),
            46 => array('Denmark', 'Kingdom of Denmark', 'Copenhagen', 'DKK', 'Krone', '+45', 'DK', 'DNK', '208', '.dk'),
            47 => array('Djibouti', 'Republic of Djibouti', 'Djibouti', 'DJF', 'Franc', '+253', 'DJ', 'DJI', '262', '.dj'),
            48 => array('Dominica', 'Commonwealth of Dominica', 'Roseau', 'XCD', 'Dollar', '+1-767', 'DM', 'DMA', '212', '.dm'),
            49 => array('Dominican Republic', '', 'Santo Domingo', 'DOP', 'Peso', '+1-809 and 1-829', 'DO', 'DOM', '214', '.do'),
            50 => array('Ecuador', 'Republic of Ecuador', 'Quito', 'USD', 'Dollar', '+593', 'EC', 'ECU', '218', '.ec'),
            51 => array('Egypt', 'Arab Republic of Egypt', 'Cairo', 'EGP', 'Pound', '+20', 'EG', 'EGY', '818', '.eg'),
            52 => array('El Salvador', 'Republic of El Salvador', 'San Salvador', 'USD', 'Dollar', '+503', 'SV', 'SLV', '222', '.sv'),
            53 => array('Equatorial Guinea', 'Republic of Equatorial Guinea', 'Malabo', 'XAF', 'Franc', '+240', 'GQ', 'GNQ', '226', '.gq'),
            54 => array('Eritrea', 'State of Eritrea', 'Asmara', 'ERN', 'Nakfa', '+291', 'ER', 'ERI', '232', '.er'),
            55 => array('Estonia', 'Republic of Estonia', 'Tallinn', 'EEK', 'Kroon', '+372', 'EE', 'EST', '233', '.ee'),
            56 => array('Ethiopia', 'Federal Democratic Republic of Ethiopia', 'Addis Ababa', 'ETB', 'Birr', '+251', 'ET', 'ETH', '231', '.et'),
            57 => array('Fiji', 'Republic of the Fiji Islands', 'Suva', 'FJD', 'Dollar', '+679', 'FJ', 'FJI', '242', '.fj'),
            58 => array('Finland', 'Republic of Finland', 'Helsinki', 'EUR', 'Euro', '+358', 'FI', 'FIN', '246', '.fi'),
            59 => array('France', 'French Republic', 'Paris', 'EUR', 'Euro', '+33', 'FR', 'FRA', '250', '.fr'),
            60 => array('Gabon', 'Gabonese Republic', 'Libreville', 'XAF', 'Franc', '+241', 'GA', 'GAB', '266', '.ga'),
            61 => array('Gambia, The', 'Republic of The Gambia', 'Banjul', 'GMD', 'Dalasi', '+220', 'GM', 'GMB', '270', '.gm'),
            62 => array('Georgia', 'Republic of Georgia', 'Tbilisi', 'GEL', 'Lari', '+995', 'GE', 'GEO', '268', '.ge'),
            63 => array('Germany', 'Federal Republic of Germany', 'Berlin', 'EUR', 'Euro', '+49', 'DE', 'DEU', '276', '.de'),
            64 => array('Ghana', 'Republic of Ghana', 'Accra', 'GHC', 'Cedi', '+233', 'GH', 'GHA', '288', '.gh'),
            65 => array('Greece', 'Hellenic Republic', 'Athens', 'EUR', 'Euro', '+30', 'GR', 'GRC', '300', '.gr'),
            66 => array('Grenada', '', 'Saint George\'s', 'XCD', 'Dollar', '+1-473', 'GD', 'GRD', '308', '.gd'),
            67 => array('Guatemala', 'Republic of Guatemala', 'Guatemala', 'GTQ', 'Quetzal', '+502', 'GT', 'GTM', '320', '.gt'),
            68 => array('Guinea', 'Republic of Guinea', 'Conakry', 'GNF', 'Franc', '+224', 'GN', 'GIN', '324', '.gn'),
            69 => array('Guinea-Bissau', 'Republic of Guinea-Bissau', 'Bissau', 'XOF', 'Franc', '+245', 'GW', 'GNB', '624', '.gw'),
            70 => array('Guyana', 'Co-operative Republic of Guyana', 'Georgetown', 'GYD', 'Dollar', '+592', 'GY', 'GUY', '328', '.gy'),
            71 => array('Haiti', 'Republic of Haiti', 'Port-au-Prince', 'HTG', 'Gourde', '+509', 'HT', 'HTI', '332', '.ht'),
            72 => array('Honduras', 'Republic of Honduras', 'Tegucigalpa', 'HNL', 'Lempira', '+504', 'HN', 'HND', '340', '.hn'),
            73 => array('Hungary', 'Republic of Hungary', 'Budapest', 'HUF', 'Forint', '+36', 'HU', 'HUN', '348', '.hu'),
            74 => array('Iceland', 'Republic of Iceland', 'Reykjavik', 'ISK', 'Krona', '+354', 'IS', 'ISL', '352', '.is'),
            75 => array('India', 'Republic of India', 'New Delhi', 'INR', 'Rupee', '+91', 'IN', 'IND', '356', '.in'),
            76 => array('Indonesia', 'Republic of Indonesia', 'Jakarta', 'IDR', 'Rupiah', '+62', 'ID', 'IDN', '360', '.id'),
            77 => array('Iran', 'Islamic Republic of Iran', 'Tehran', 'IRR', 'Rial', '+98', 'IR', 'IRN', '364', '.ir'),
            78 => array('Iraq', 'Republic of Iraq', 'Baghdad', 'IQD', 'Dinar', '+964', 'IQ', 'IRQ', '368', '.iq'),
            79 => array('Ireland', '', 'Dublin', 'EUR', 'Euro', '+353', 'IE', 'IRL', '372', '.ie'),
            80 => array('Israel', 'State of Israel', 'Jerusalem', 'ILS', 'Shekel', '+972', 'IL', 'ISR', '376', '.il'),
            81 => array('Italy', 'Italian Republic', 'Rome', 'EUR', 'Euro', '+39', 'IT', 'ITA', '380', '.it'),
            82 => array('Jamaica', '', 'Kingston', 'JMD', 'Dollar', '+1-876', 'JM', 'JAM', '388', '.jm'),
            83 => array('Japan', '', 'Tokyo', 'JPY', 'Yen', '+81', 'JP', 'JPN', '392', '.jp'),
            84 => array('Jordan', 'Hashemite Kingdom of Jordan', 'Amman', 'JOD', 'Dinar', '+962', 'JO', 'JOR', '400', '.jo'),
            85 => array('Kazakhstan', 'Republic of Kazakhstan', 'Astana', 'KZT', 'Tenge', '+7', 'KZ', 'KAZ', '398', '.kz'),
            86 => array('Kenya', 'Republic of Kenya', 'Nairobi', 'KES', 'Shilling', '+254', 'KE', 'KEN', '404', '.ke'),
            87 => array('Kiribati', 'Republic of Kiribati', 'Tarawa', 'AUD', 'Dollar', '+686', 'KI', 'KIR', '296', '.ki'),
            88 => array('Korea, Democratic People\'s Republic of (North Korea)', 'Democratic People\'s Republic of Korea', 'Pyongyang', 'KPW', 'Won', '+850', 'KP', 'PRK', '408', '.kp'),
            89 => array('Korea, Republic of  (South Korea)', 'Republic of Korea', 'Seoul', 'KRW', 'Won', '+82', 'KR', 'KOR', '410', '.kr'),
            90 => array('Kuwait', 'State of Kuwait', 'Kuwait', 'KWD', 'Dinar', '+965', 'KW', 'KWT', '414', '.kw'),
            91 => array('Kyrgyzstan', 'Kyrgyz Republic', 'Bishkek', 'KGS', 'Som', '+996', 'KG', 'KGZ', '417', '.kg'),
            92 => array('Laos', 'Lao People\'s Democratic Republic', 'Vientiane', 'LAK', 'Kip', '+856', 'LA', 'LAO', '418', '.la'),
            93 => array('Latvia', 'Republic of Latvia', 'Riga', 'LVL', 'Lat', '+371', 'LV', 'LVA', '428', '.lv'),
            94 => array('Lebanon', 'Lebanese Republic', 'Beirut', 'LBP', 'Pound', '+961', 'LB', 'LBN', '422', '.lb'),
            95 => array('Lesotho', 'Kingdom of Lesotho', 'Maseru', 'LSL', 'Loti', '+266', 'LS', 'LSO', '426', '.ls'),
            96 => array('Liberia', 'Republic of Liberia', 'Monrovia', 'LRD', 'Dollar', '+231', 'LR', 'LBR', '430', '.lr'),
            97 => array('Libya', 'State of Libya', 'Tripoli', 'LYD', 'Dinar', '+218', 'LY', 'LBY', '434', '.ly'),
            98 => array('Liechtenstein', 'Principality of Liechtenstein', 'Vaduz', 'CHF', 'Franc', '+423', 'LI', 'LIE', '438', '.li'),
            99 => array('Lithuania', 'Republic of Lithuania', 'Vilnius', 'LTL', 'Litas', '+370', 'LT', 'LTU', '440', '.lt'),
            100 => array('Luxembourg', 'Grand Duchy of Luxembourg', 'Luxembourg', 'EUR', 'Euro', '+352', 'LU', 'LUX', '442', '.lu'),
            101 => array('Macedonia', 'Republic of Macedonia', 'Skopje', 'MKD', 'Denar', '+389', 'MK', 'MKD', '807', '.mk'),
            102 => array('Madagascar', 'Republic of Madagascar', 'Antananarivo', 'MGA', 'Ariary', '+261', 'MG', 'MDG', '450', '.mg'),
            103 => array('Malawi', 'Republic of Malawi', 'Lilongwe', 'MWK', 'Kwacha', '+265', 'MW', 'MWI', '454', '.mw'),
            104 => array('Malaysia', '', 'Kuala Lumpur (legislative/judical) and Putrajaya (administrative)', 'MYR', 'Ringgit', '+60', 'MY', 'MYS', '458', '.my'),
            105 => array('Maldives', 'Republic of Maldives', 'Male', 'MVR', 'Rufiyaa', '+960', 'MV', 'MDV', '462', '.mv'),
            106 => array('Mali', 'Republic of Mali', 'Bamako', 'XOF', 'Franc', '+223', 'ML', 'MLI', '466', '.ml'),
            107 => array('Malta', 'Republic of Malta', 'Valletta', 'MTL', 'Lira', '+356', 'MT', 'MLT', '470', '.mt'),
            108 => array('Marshall Islands', 'Republic of the Marshall Islands', 'Majuro', 'USD', 'Dollar', '+692', 'MH', 'MHL', '584', '.mh'),
            109 => array('Mauritania', 'Islamic Republic of Mauritania', 'Nouakchott', 'MRO', 'Ouguiya', '+222', 'MR', 'MRT', '478', '.mr'),
            110 => array('Mauritius', 'Republic of Mauritius', 'Port Louis', 'MUR', 'Rupee', '+230', 'MU', 'MUS', '480', '.mu'),
            111 => array('Mexico', 'United Mexican States', 'Mexico', 'MXN', 'Peso', '+52', 'MX', 'MEX', '484', '.mx'),
            112 => array('Micronesia', 'Federated States of Micronesia', 'Palikir', 'USD', 'Dollar', '+691', 'FM', 'FSM', '583', '.fm'),
            113 => array('Moldova', 'Republic of Moldova', 'Chisinau', 'MDL', 'Leu', '+373', 'MD', 'MDA', '498', '.md'),
            114 => array('Monaco', 'Principality of Monaco', 'Monaco', 'EUR', 'Euro', '+377', 'MC', 'MCO', '492', '.mc'),
            115 => array('Mongolia', '', 'Ulaanbaatar', 'MNT', 'Tugrik', '+976', 'MN', 'MNG', '496', '.mn'),
            116 => array('Montenegro', 'Republic of Montenegro', 'Podgorica', 'EUR', 'Euro', '+382', 'ME', 'MNE', '499', '.me and .yu'),
            117 => array('Morocco', 'Kingdom of Morocco', 'Rabat', 'MAD', 'Dirham', '+212', 'MA', 'MAR', '504', '.ma'),
            118 => array('Mozambique', 'Republic of Mozambique', 'Maputo', 'MZM', 'Meticail', '+258', 'MZ', 'MOZ', '508', '.mz'),
            119 => array('Myanmar (Burma)', 'Union of Myanmar', 'Naypyidaw', 'MMK', 'Kyat', '+95', 'MM', 'MMR', '104', '.mm'),
            120 => array('Namibia', 'Republic of Namibia', 'Windhoek', 'NAD', 'Dollar', '+264', 'NA', 'NAM', '516', '.na'),
            121 => array('Nauru', 'Republic of Nauru', 'Yaren', 'AUD', 'Dollar', '+674', 'NR', 'NRU', '520', '.nr'),
            122 => array('Nepal', '', 'Kathmandu', 'NPR', 'Rupee', '+977', 'NP', 'NPL', '524', '.np'),
            123 => array('Netherlands', 'Kingdom of the Netherlands', 'Amsterdam (administrative) and The Hague (legislative/judical)', 'EUR', 'Euro', '+31', 'NL', 'NLD', '528', '.nl'),
            124 => array('New Zealand', '', 'Wellington', 'NZD', 'Dollar', '+64', 'NZ', 'NZL', '554', '.nz'),
            125 => array('Nicaragua', 'Republic of Nicaragua', 'Managua', 'NIO', 'Cordoba', '+505', 'NI', 'NIC', '558', '.ni'),
            126 => array('Niger', 'Republic of Niger', 'Niamey', 'XOF', 'Franc', '+227', 'NE', 'NER', '562', '.ne'),
            127 => array('Nigeria', 'Federal Republic of Nigeria', 'Abuja', 'NGN', 'Naira', '+234', 'NG', 'NGA', '566', '.ng'),
            128 => array('Norway', 'Kingdom of Norway', 'Oslo', 'NOK', 'Krone', '+47', 'NO', 'NOR', '578', '.no'),
            129 => array('Oman', 'Sultanate of Oman', 'Muscat', 'OMR', 'Rial', '+968', 'OM', 'OMN', '512', '.om'),
            130 => array('Pakistan', 'Islamic Republic of Pakistan', 'Islamabad', 'PKR', 'Rupee', '+92', 'PK', 'PAK', '586', '.pk'),
            131 => array('Palau', 'Republic of Palau', 'Melekeok', 'USD', 'Dollar', '+680', 'PW', 'PLW', '585', '.pw'),
            132 => array('Panama', 'Republic of Panama', 'Panama', 'PAB', 'Balboa', '+507', 'PA', 'PAN', '591', '.pa'),
            133 => array('Papua New Guinea', 'Independent State of Papua New Guinea', 'Port Moresby', 'PGK', 'Kina', '+675', 'PG', 'PNG', '598', '.pg'),
            134 => array('Paraguay', 'Republic of Paraguay', 'Asuncion', 'PYG', 'Guarani', '+595', 'PY', 'PRY', '600', '.py'),
            135 => array('Peru', 'Republic of Peru', 'Lima', 'PEN', 'Sol', '+51', 'PE', 'PER', '604', '.pe'),
            136 => array('Philippines', 'Republic of the Philippines', 'Manila', 'PHP', 'Peso', '+63', 'PH', 'PHL', '608', '.ph'),
            137 => array('Poland', 'Republic of Poland', 'Warsaw', 'PLN', 'Zloty', '+48', 'PL', 'POL', '616', '.pl'),
            138 => array('Portugal', 'Portuguese Republic', 'Lisbon', 'EUR', 'Euro', '+351', 'PT', 'PRT', '620', '.pt'),
            139 => array('Qatar', 'State of Qatar', 'Doha', 'QAR', 'Rial', '+974', 'QA', 'QAT', '634', '.qa'),
            140 => array('Romania', '', 'Bucharest', 'RON', 'Leu', '+40', 'RO', 'ROU', '642', '.ro'),
            141 => array('Russia', 'Russian Federation', 'Moscow', 'RUB', 'Ruble', '+7', 'RU', 'RUS', '643', '.ru and .su'),
            142 => array('Rwanda', 'Republic of Rwanda', 'Kigali', 'RWF', 'Franc', '+250', 'RW', 'RWA', '646', '.rw'),
            143 => array('Saint Kitts and Nevis', 'Federation of Saint Kitts and Nevis', 'Basseterre', 'XCD', 'Dollar', '+1-869', 'KN', 'KNA', '659', '.kn'),
            144 => array('Saint Lucia', '', 'Castries', 'XCD', 'Dollar', '+1-758', 'LC', 'LCA', '662', '.lc'),
            145 => array('Saint Vincent and the Grenadines', '', 'Kingstown', 'XCD', 'Dollar', '+1-784', 'VC', 'VCT', '670', '.vc'),
            146 => array('Samoa', 'Independent State of Samoa', 'Apia', 'WST', 'Tala', '+685', 'WS', 'WSM', '882', '.ws'),
            147 => array('San Marino', 'Republic of San Marino', 'San Marino', 'EUR', 'Euro', '+378', 'SM', 'SMR', '674', '.sm'),
            148 => array('Sao Tome and Principe', 'Democratic Republic of Sao Tome and Principe', 'Sao Tome', 'STD', 'Dobra', '+239', 'ST', 'STP', '678', '.st'),
            149 => array('Saudi Arabia', 'Kingdom of Saudi Arabia', 'Riyadh', 'SAR', 'Rial', '+966', 'SA', 'SAU', '682', '.sa'),
            150 => array('Senegal', 'Republic of Senegal', 'Dakar', 'XOF', 'Franc', '+221', 'SN', 'SEN', '686', '.sn'),
            151 => array('Serbia', 'Republic of Serbia', 'Belgrade', 'RSD', 'Dinar', '+381', 'RS', 'SRB', '688', '.rs and .yu'),
            152 => array('Seychelles', 'Republic of Seychelles', 'Victoria', 'SCR', 'Rupee', '+248', 'SC', 'SYC', '690', '.sc'),
            153 => array('Sierra Leone', 'Republic of Sierra Leone', 'Freetown', 'SLL', 'Leone', '+232', 'SL', 'SLE', '694', '.sl'),
            154 => array('Singapore', 'Republic of Singapore', 'Singapore', 'SGD', 'Dollar', '+65', 'SG', 'SGP', '702', '.sg'),
            155 => array('Slovakia', 'Slovak Republic', 'Bratislava', 'SKK', 'Koruna', '+421', 'SK', 'SVK', '703', '.sk'),
            156 => array('Slovenia', 'Republic of Slovenia', 'Ljubljana', 'EUR', 'Euro', '+386', 'SI', 'SVN', '705', '.si'),
            157 => array('Solomon Islands', '', 'Honiara', 'SBD', 'Dollar', '+677', 'SB', 'SLB', '090', '.sb'),
            158 => array('Somalia', '', 'Mogadishu', 'SOS', 'Shilling', '+252', 'SO', 'SOM', '706', '.so'),
            159 => array('South Africa', 'Republic of South Africa', 'Pretoria (administrative), Cape Town (legislative), and Bloemfontein (judical)', 'ZAR', 'Rand', '+27', 'ZA', 'ZAF', '710', '.za'),
            160 => array('Spain', 'Kingdom of Spain', 'Madrid', 'EUR', 'Euro', '+34', 'ES', 'ESP', '724', '.es'),
            161 => array('Sri Lanka', 'Democratic Socialist Republic of Sri Lanka', 'Colombo (administrative/judical) and Sri Jayewardenepura Kotte (legislative)', 'LKR', 'Rupee', '+94', 'LK', 'LKA', '144', '.lk'),
            162 => array('Sudan', 'Republic of the Sudan', 'Khartoum', 'SDD', 'Dinar', '+249', 'SD', 'SDN', '736', '.sd'),
            163 => array('Suriname', 'Republic of Suriname', 'Paramaribo', 'SRD', 'Dollar', '+597', 'SR', 'SUR', '740', '.sr'),
            164 => array('Swaziland', 'Kingdom of Swaziland', 'Mbabane (administrative) and Lobamba (legislative)', 'SZL', 'Lilangeni', '+268', 'SZ', 'SWZ', '748', '.sz'),
            165 => array('Sweden', 'Kingdom of Sweden', 'Stockholm', 'SEK', 'Kronoa', '+46', 'SE', 'SWE', '752', '.se'),
            166 => array('Switzerland', 'Swiss Confederation', 'Bern', 'CHF', 'Franc', '+41', 'CH', 'CHE', '756', '.ch'),
            167 => array('Syria', 'Syrian Arab Republic', 'Damascus', 'SYP', 'Pound', '+963', 'SY', 'SYR', '760', '.sy'),
            168 => array('Tajikistan', 'Republic of Tajikistan', 'Dushanbe', 'TJS', 'Somoni', '+992', 'TJ', 'TJK', '762', '.tj'),
            169 => array('Tanzania', 'United Republic of Tanzania', 'Dar es Salaam (administrative/judical) and Dodoma (legislative)', 'TZS', 'Shilling', '+255', 'TZ', 'TZA', '834', '.tz'),
            170 => array('Thailand', 'Kingdom of Thailand', 'Bangkok', 'THB', 'Baht', '+66', 'TH', 'THA', '764', '.th'),
            171 => array('Timor-Leste (East Timor)', 'Democratic Republic of Timor-Leste', 'Dili', 'USD', 'Dollar', '+670', 'TL', 'TLS', '626', '.tp and .tl'),
            172 => array('Togo', 'Togolese Republic', 'Lome', 'XOF', 'Franc', '+228', 'TG', 'TGO', '768', '.tg'),
            173 => array('Tonga', 'Kingdom of Tonga', 'Nuku\'alofa', 'TOP', 'Pa\'anga', '+676', 'TO', 'TON', '776', '.to'),
            174 => array('Trinidad and Tobago', 'Republic of Trinidad and Tobago', 'Port-of-Spain', 'TTD', 'Dollar', '+1-868', 'TT', 'TTO', '780', '.tt'),
            175 => array('Tunisia', 'Tunisian Republic', 'Tunis', 'TND', 'Dinar', '+216', 'TN', 'TUN', '788', '.tn'),
            176 => array('Turkey', 'Republic of Turkey', 'Ankara', 'TRY', 'Lira', '+90', 'TR', 'TUR', '792', '.tr'),
            177 => array('Turkmenistan', '', 'Ashgabat', 'TMM', 'Manat', '+993', 'TM', 'TKM', '795', '.tm'),
            178 => array('Tuvalu', '', 'Funafuti', 'AUD', 'Dollar', '+688', 'TV', 'TUV', '798', '.tv'),
            179 => array('Uganda', 'Republic of Uganda', 'Kampala', 'UGX', 'Shilling', '+256', 'UG', 'UGA', '800', '.ug'),
            180 => array('Ukraine', '', 'Kiev', 'UAH', 'Hryvnia', '+380', 'UA', 'UKR', '804', '.ua'),
            181 => array('United Arab Emirates', 'United Arab Emirates', 'Abu Dhabi', 'AED', 'Dirham', '+971', 'AE', 'ARE', '784', '.ae'),
            182 => array('United Kingdom', 'United Kingdom of Great Britain and Northern Ireland', 'London', 'GBP', 'Pound', '+44', 'GB', 'GBR', '826', '.uk'),
            183 => array('United States', 'United States of America', 'Washington', 'USD', 'Dollar', '+1', 'US', 'USA', '840', '.us'),
            184 => array('Uruguay', 'Oriental Republic of Uruguay', 'Montevideo', 'UYU', 'Peso', '+598', 'UY', 'URY', '858', '.uy'),
            185 => array('Uzbekistan', 'Republic of Uzbekistan', 'Tashkent', 'UZS', 'Som', '+998', 'UZ', 'UZB', '860', '.uz'),
            186 => array('Vanuatu', 'Republic of Vanuatu', 'Port-Vila', 'VUV', 'Vatu', '+678', 'VU', 'VUT', '548', '.vu'),
            187 => array('Vatican City', 'State of the Vatican City', 'Vatican City', 'EUR', 'Euro', '+379', 'VA', 'VAT', '336', '.va'),
            188 => array('Venezuela', 'Bolivarian Republic of Venezuela', 'Caracas', 'VEB', 'Bolivar', '+58', 'VE', 'VEN', '862', '.ve'),
            189 => array('Vietnam', 'Socialist Republic of Vietnam', 'Hanoi', 'VND', 'Dong', '+84', 'VN', 'VNM', '704', '.vn'),
            190 => array('Yemen', 'Republic of Yemen', 'Sanaa', 'YER', 'Rial', '+967', 'YE', 'YEM', '887', '.ye'),
            191 => array('Zambia', 'Republic of Zambia', 'Lusaka', 'ZMK', 'Kwacha', '+260', 'ZM', 'ZMB', '894', '.zm'),
            192 => array('Zimbabwe', 'Republic of Zimbabwe', 'Harare', 'ZWD', 'Dollar', '+263', 'ZW', 'ZWE', '716', '.zw'),
            193 => array('Abkhazia', 'Republic of Abkhazia', 'Sokhumi', 'RUB', 'Ruble', '+995', 'GE', 'GEO', '268', '.ge'),
            194 => array('China, Republic of (Taiwan)', 'Republic of China', 'Taipei', 'TWD', 'Dollar', '+886', 'TW', 'TWN', '158', '.tw'),
            195 => array('Nagorno-Karabakh', 'Nagorno-Karabakh Republic', 'Stepanakert', 'AMD', 'Dram', '+374-97', 'AZ', 'AZE', '031', '.az'),
            196 => array('Northern Cyprus', 'Turkish Republic of Northern Cyprus', 'Nicosia', 'TRY', 'Lira', '+90-392', 'CY', 'CYP', '196', '.nc.tr'),
            197 => array('Pridnestrovie (Transnistria)', 'Pridnestrovian Moldavian Republic', 'Tiraspol', '', 'Ruple', '+373-533', 'MD', 'MDA', '498', '.md'),
            198 => array('Somaliland', 'Republic of Somaliland', 'Hargeisa', '', 'Shilling', '+252', 'SO', 'SOM', '706', '.so'),
            199 => array('South Ossetia', 'Republic of South Ossetia', 'Tskhinvali', 'RUB and GEL', 'Ruble and Lari', '+995', 'GE', 'GEO', '268', '.ge'),
            201 => array('Christmas Island', 'Territory of Christmas Island', 'The Settlement (Flying Fish Cove)', 'AUD', 'Dollar', '+61', 'CX', 'CXR', '162', '.cx'),
            202 => array('Cocos (Keeling) Islands', 'Territory of Cocos (Keeling) Islands', 'West Island', 'AUD', 'Dollar', '+61', 'CC', 'CCK', '166', '.cc'),
            204 => array('Heard Island and McDonald Islands', 'Territory of Heard Island and McDonald Islands', '', '', '', '', 'HM', 'HMD', '334', '.hm'),
            205 => array('Norfolk Island', 'Territory of Norfolk Island', 'Kingston', 'AUD', 'Dollar', '+672', 'NF', 'NFK', '574', '.nf'),
            206 => array('New Caledonia', '', 'Noumea', 'XPF', 'Franc', '+687', 'NC', 'NCL', '540', '.nc'),
            207 => array('French Polynesia', 'Overseas Country of French Polynesia', 'Papeete', 'XPF', 'Franc', '+689', 'PF', 'PYF', '258', '.pf'),
            208 => array('Mayotte', 'Departmental Collectivity of Mayotte', 'Mamoudzou', 'EUR', 'Euro', '+269', 'YT', 'MYT', '175', '.yt'),
            209 => array('Saint Barthelemy', 'Collectivity of Saint Barthelemy', 'Gustavia', 'EUR', 'Euro', '+590', 'GP', 'GLP', '312', '.gp'),
            210 => array('Saint Martin', 'Collectivity of Saint Martin', 'Marigot', 'EUR', 'Euro', '+590', 'GP', 'GLP', '312', '.gp'),
            211 => array('Saint Pierre and Miquelon', 'Territorial Collectivity of Saint Pierre and Miquelon', 'Saint-Pierre', 'EUR', 'Euro', '+508', 'PM', 'SPM', '666', '.pm'),
            212 => array('Wallis and Futuna', 'Collectivity of the Wallis and Futuna Islands', 'Mata\'utu', 'XPF', 'Franc', '+681', 'WF', 'WLF', '876', '.wf'),
            213 => array('French Southern and Antarctic Lands', 'Territory of the French Southern and Antarctic Lands', 'Martin-de-Viviès', '', '', '', 'TF', 'ATF', '260', '.tf'),
            214 => array('Clipperton Island', '', '', '', '', '', 'PF', 'PYF', '258', '.pf'),
            215 => array('Bouvet Island', '', '', '', '', '', 'BV', 'BVT', '074', '.bv'),
            216 => array('Cook Islands', '', 'Avarua', 'NZD', 'Dollar', '+682', 'CK', 'COK', '184', '.ck'),
            217 => array('Niue', '', 'Alofi', 'NZD', 'Dollar', '+683', 'NU', 'NIU', '570', '.nu'),
            218 => array('Tokelau', '', '', 'NZD', 'Dollar', '+690', 'TK', 'TKL', '772', '.tk'),
            219 => array('Guernsey', 'Bailiwick of Guernsey', 'Saint Peter Port', 'GGP', 'Pound', '+44', 'GG', 'GGY', '831', '.gg'),
            220 => array('Isle of Man', '', 'Douglas', 'IMP', 'Pound', '+44', 'IM', 'IMN', '833', '.im'),
            221 => array('Jersey', 'Bailiwick of Jersey', 'Saint Helier', 'JEP', 'Pound', '+44', 'JE', 'JEY', '832', '.je'),
            222 => array('Anguilla', '', 'The Valley', 'XCD', 'Dollar', '+1-264', 'AI', 'AIA', '660', '.ai'),
            223 => array('Bermuda', '', 'Hamilton', 'BMD', 'Dollar', '+1-441', 'BM', 'BMU', '060', '.bm'),
            224 => array('British Indian Ocean Territory', '', '', '', '', '+246', 'IO', 'IOT', '086', '.io'),
            225 => array('British Sovereign Base Areas', '', 'Episkopi', 'CYP', 'Pound', '+357', '', '', '', ''),
            226 => array('British Virgin Islands', '', 'Road Town', 'USD', 'Dollar', '+1-284', 'VG', 'VGB', '092', '.vg'),
            227 => array('Cayman Islands', '', 'George Town', 'KYD', 'Dollar', '+1-345', 'KY', 'CYM', '136', '.ky'),
            228 => array('Falkland Islands (Islas Malvinas)', '', 'Stanley', 'FKP', 'Pound', '+500', 'FK', 'FLK', '238', '.fk'),
            229 => array('Gibraltar', '', 'Gibraltar', 'GIP', 'Pound', '+350', 'GI', 'GIB', '292', '.gi'),
            230 => array('Montserrat', '', 'Plymouth', 'XCD', 'Dollar', '+1-664', 'MS', 'MSR', '500', '.ms'),
            231 => array('Pitcairn Islands', '', 'Adamstown', 'NZD', 'Dollar', '', 'PN', 'PCN', '612', '.pn'),
            232 => array('Saint Helena', '', 'Jamestown', 'SHP', 'Pound', '+290', 'SH', 'SHN', '654', '.sh'),
            233 => array('South Georgia and the South Sandwich Islands', '', '', '', '', '', 'GS', 'SGS', '239', '.gs'),
            234 => array('Turks and Caicos Islands', '', 'Grand Turk', 'USD', 'Dollar', '+1-649', 'TC', 'TCA', '796', '.tc'),
            235 => array('Northern Mariana Islands', 'Commonwealth of The Northern Mariana Islands', 'Saipan', 'USD', 'Dollar', '+1-670', 'MP', 'MNP', '580', '.mp'),
            236 => array('Puerto Rico', 'Commonwealth of Puerto Rico', 'San Juan', 'USD', 'Dollar', '+1-787 and 1-939', 'PR', 'PRI', '630', '.pr'),
            237 => array('American Samoa', 'Territory of American Samoa', 'Pago Pago', 'USD', 'Dollar', '+1-684', 'AS', 'ASM', '016', '.as'),
            238 => array('Baker Island', '', '', '', '', '', 'UM', 'UMI', '581', ''),
            239 => array('Guam', 'Territory of Guam', 'Hagatna', 'USD', 'Dollar', '+1-671', 'GU', 'GUM', '316', '.gu'),
            240 => array('Howland Island', '', '', '', '', '', 'UM', 'UMI', '581', ''),
            241 => array('Jarvis Island', '', '', '', '', '', 'UM', 'UMI', '581', ''),
            242 => array('Johnston Atoll', '', '', '', '', '', 'UM', 'UMI', '581', ''),
            243 => array('Kingman Reef', '', '', '', '', '', 'UM', 'UMI', '581', ''),
            244 => array('Midway Islands', '', '', '', '', '', 'UM', 'UMI', '581', ''),
            245 => array('Navassa Island', '', '', '', '', '', 'UM', 'UMI', '581', ''),
            246 => array('Palmyra Atoll', '', '', '', '', '', 'UM', 'UMI', '581', ''),
            247 => array('U.S. Virgin Islands', 'United States Virgin Islands', 'Charlotte Amalie', 'USD', 'Dollar', '+1-340', 'VI', 'VIR', '850', '.vi'),
            248 => array('Wake Island', '', '', '', '', '', 'UM', 'UMI', '850', ''),
            249 => array('Hong Kong', 'Hong Kong Special Administrative Region', '', 'HKD', 'Dollar', '+852', 'HK', 'HKG', '344', '.hk'),
            250 => array('Macau', 'Macau Special Administrative Region', 'Macau', 'MOP', 'Pataca', '+853', 'MO', 'MAC', '446', '.mo'),
            251 => array('Faroe Islands', '', 'Torshavn', 'DKK', 'Krone', '+298', 'FO', 'FRO', '234', '.fo'),
            252 => array('Greenland', '', 'Nuuk (Godthab)', 'DKK', 'Krone', '+299', 'GL', 'GRL', '304', '.gl'),
            253 => array('French Guiana', 'Overseas Region of Guiana', 'Cayenne', 'EUR', 'Euro', '+594', 'GF', 'GUF', '254', '.gf'),
            254 => array('Guadeloupe', 'Overseas Region of Guadeloupe', 'Basse-Terre', 'EUR', 'Euro', '+590', 'GP', 'GLP', '312', '.gp'),
            255 => array('Martinique', 'Overseas Region of Martinique', 'Fort-de-France', 'EUR', 'Euro', '+596', 'MQ', 'MTQ', '474', '.mq'),
            256 => array('Reunion', 'Overseas Region of Reunion', 'Saint-Denis', 'EUR', 'Euro', '+262', 'RE', 'REU', '638', '.re'),
            257 => array('Aland', '', 'Mariehamn', 'EUR', 'Euro', '+358-18', 'AX', 'ALA', '248', '.ax'),
            258 => array('Aruba', '', 'Oranjestad', 'AWG', 'Guilder', '+297', 'AW', 'ABW', '533', '.aw'),
            259 => array('Netherlands Antilles', '', 'Willemstad', 'ANG', 'Guilder', '+599', 'AN', 'ANT', '530', '.an'),
            260 => array('Svalbard', '', 'Longyearbyen', 'NOK', 'Krone', '+47', 'SJ', 'SJM', '744', '.sj'),
            261 => array('Ascension', '', 'Georgetown', 'SHP', 'Pound', '+247', 'AC', 'ASC', '', '.ac'),
            262 => array('Tristan da Cunha', '', 'Edinburgh', 'SHP', 'Pound', '+290', 'TA', 'TAA', '', ''),
            263 => array('Antarctica', '', '', '', '', '', 'AQ', 'ATA', '010', '.aq'),
            264 => array('Kosovo', '', 'Pristina', 'CSD and EUR', 'Dinar and Euro', '+381', 'CS', 'SCG', '891', '.cs and .yu'),
            265 => array('Palestinian Territories (Gaza Strip and West Bank)', '', 'Gaza City (Gaza Strip) and Ramallah (West Bank)', 'ILS', 'Shekel', '+970', 'PS', 'PSE', '275', '.ps'),
            266 => array('Western Sahara', '', 'El-Aaiun', 'MAD', 'Dirham', '+212', 'EH', 'ESH', '732', '.eh'),
            267 => array('Australian Antarctic Territory', '', '', '', '', '', 'AQ', 'ATA', '010', '.aq'),
            268 => array('Ross Dependency', '', '', '', '', '', 'AQ', 'ATA', '010', '.aq'),
            269 => array('Peter I Island', '', '', '', '', '', 'AQ', 'ATA', '010', '.aq'),
            270 => array('Queen Maud Land', '', '', '', '', '', 'AQ', 'ATA', '010', '.aq'),
            271 => array('British Antarctic Territory', '', '', '', '', '', 'AQ', 'ATA', '010', '.aq'),
            272 => array('Bonaire', '', 'Kralendijk', 'USD', 'US dollar', '+599-7', 'BQ', 'BES', '535', '.bq'),
            273 => array('Curaçao', 'Country of Curaçao', 'Willemstad', 'ANG', 'Netherlands Antillean guilder', '+599-9', 'CW', 'CUW', '531', '.cw'),
            274 => array('Saba', '', 'The Bottom', 'USD', 'U.S. dollar', '+599-4', 'BQ', 'BES', '535', '.an'),
            275 => array('Sint Eustatius', '', 'Oranjestad', 'USD', 'U.S. dollar', '+599-3', 'BQ', 'BES', '535', '.an'),
            276 => array('Sint Maarten', '', 'Philipsburg', 'ANG', 'Netherlands Antillean guilder', '+599-3', 'SX', 'SXM', '534', '.sx'),
        );

        /*
        * Removed: 203 => array('Coral Sea Islands', 'Coral Sea Islands Territory', '', '', '', '', 'AU', 'AUS', '036', '.au'),
        * Removed: 200 => array('Ashmore and Cartier Islands', 'Territory of Ashmore and Cartier Islands', '', '', '', '', 'AU', 'AUS', '036', '.au'),
        * Reason: Seems erronous (AU).
        */

        return $_countryContainer;
    }


    /**
    * Get Country Name
    *
    * @author Mahesh Salaria
    * @param string $_countryCode Country Code
    * @return bool "true" on Success, "false" otherwise
    * @throws SWIFT_Exception If the Class is not Loaded
    */
    public function GetCoutryName($_countryCode)
    {
        if (!$this->GetIsClassLoaded()) {
            throw new SWIFT_Exception(__CLASS__ . ':  ' . SWIFT_CLASSNOTLOADED);

            return false;
        }

        foreach ($this->Get() as $_country) {
            if ($_country[6] === $_countryCode)
            {
                return $_country[0];
            }
        }

        return false;
    }
}
?>