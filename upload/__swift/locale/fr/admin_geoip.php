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
    'geoip'                      => 'GeoIP',
    'completed'                  => 'Complété',
    'maintenance'                => 'Maintenance',
    'desc_maintenance'           => '',
    'tabcitylocations'           => 'Ville (emplacements)',
    'tabcityblocks'              => 'Ville (blocs)',
    'generalinformation'         => 'Informations générales',
    'indexsettings'              => 'Paramètres d\'indexation',
    'rebuild'                    => 'Reconstruire',
    'taborganization'            => 'Organization',
    'tabisp'                     => 'FAI',
    'tabnetspeed'                => 'Netspeed',
    'geoiploadingalert'          => 'L\'indexation est en cours. Éviter de fermer ou de quitter la page avant que l\'indexation ne soit terminée.',
    'entriesperpass'             => 'Entrées pour le traitement par lot',
    'desc_entriesperpass'        => 'Les entrées GeoIP sont indexées par lots. Vous pouvez augmenter le nombre d\'entrées pour accélérer l\'indexation, ou le réduire pour diminuer la demande sur votre serveur.',
    'passnumber'                 => 'Nombre de lots à traiter lors de l\'actualisation d\'une page',
    'desc_passnumber'            => 'Au cours de l\'indexation, la page est actualisée plusieurs fois. Avec chaque actualisation ce nombre de lots est indexé. Soyez prudent lors de l\'augmentation de ce nombre - cela peut imposer une lourde charge à votre serveur. Si vous rencontrez des problèmes, essayez de réduire ce nombre et offrez-vous une pause café.',
    'sizeprocessed'              => 'Données traitées (kilo-octets)',
    'totalsize'                  => 'Total des données (en kilo-octets)',
    'timeelapsed'                => 'Temps écoulé',
    'timeremaining'              => 'Temps restant',
    'databaseinformation'        => 'Information de la bases de données',
    'rebuildoptions'             => 'Options de reconstruction',
    'licensetype'                => 'Type de licences :',
    'premium'                    => 'Premium',
    'lite'                       => 'Lite',
    'lastupdate'                 => 'Dernière mise à jour :',
    'notavailable'               => '- Non disponible -',
    'titlegeoipremote'           => 'Le service de recherche de QuickSupport GeoIP est actif',
    'msggeoipremote'             => 'Votre service client utilise le service de recherche de QuickSupport GeoIP. Aucune action additionnelle n\'est requise.',
    'titlenofilecitylocation'    => 'La base de données de ville (emplacement) n\'a pas pu être trouvé',
    'msgnofilecitylocation'      => 'QuickSupport ne parvient pas à localiser une base de données GeoIP valide pour la ville (emplacement). Vous pouvez télécharger la dernière base de données dans  <a href="https://go.gfi.com/?pageid=GFIHelpDeskGeoIP">ici</a>.',
    'titlenofilecityblocks'      => 'Base de données de ville (blocs) introuvable',
    'msgnofilecityblocks'        => 'QuickSupport ne parvient pas à localiser une base de données GeoIP valide pour la ville (blocs). Vous pouvez télécharger la dernière base de données dans la zone <strong>Mes commandes</strong> sur    <a href="https://go.gfi.com/?pageid=GFIHelpDeskGeoIP">ici</a>..',
    'titlenofilenetspeed'        => 'Base de données Netspeed introuvable',
    'msgnofilenetspeed'          => 'QuickSupport ne parvient pas à localiser une base de données GeoIP valide pour Netspeeds. Vous pouvez télécharger la dernière base de données dans la zone <strong>Mes commandes</strong> sur  <a href="https://go.gfi.com/?pageid=GFIHelpDeskGeoIP">ici</a>..',
    'titlenofileisp'             => 'Base de données ISP introuvable',
    'msgnofileisp'               => 'QuickSupport ne parvient pas à localiser une base de données GeoIP valide pour Netspeeds. Vous pouvez télécharger la dernière base de données dans  <a href="https://go.gfi.com/?pageid=GFIHelpDeskGeoIP">ici</a>.',
    'titlenofileorganization'    => 'Base de données de l\'organisation introuvable',
    'msgnofileorganization'      => 'QuickSupport ne parvient pas à localiser une base de données GeoIP valide pour les organisations. Vous pouvez télécharger la dernière base de données dans  <a href="https://go.gfi.com/?pageid=GFIHelpDeskGeoIP">ici</a>.',
    'titlenotbuiltcitylocations' => 'La base de données de la ville (emplacement) n\'a pas été indexée',
    'msgnotbuiltcitylocations'   => 'Les informations de cette base de données GeoIP ne peuvent pas être utilisées par QuickSupport jusqu\'à ce qu\'elle aient été indexées.',
    'titlenotbuiltcityblocks'    => 'La base de données de la ville (blocs) n\'a pas été indexée',
    'msgnotbuiltcityblocks'      => 'Les informations de cette base de données GeoIP ne peuvent pas être utilisées par QuickSupport jusqu\'à ce qu\'elles aient été indexées.',
    'titlenotbuiltisp'           => 'La base de données de la FAI n\'a pas été indexée',
    'msgnotbuiltisp'             => 'Les informations de cette base de données GeoIP ne peuvent pas être utilisées par QuickSupport jusqu\'à ce qu\'elles aient été indexées.',
    'titlenotbuiltnetspeed'      => 'La base de données Netspeed n\'a pas été indexée',
    'msgnotbuiltnetspeed'        => 'Les informations de cette base de données GeoIP ne peuvent pas être utilisées par QuickSupport jusqu\'à ce qu\'elles aient été indexées.',
    'titlenotbuiltorganization'  => 'La base de données de l\'organisation n\'a pas été indexée',
    'msgnotbuiltorganization'    => 'Les informations de cette base de données GeoIP ne peuvent pas être utilisées par QuickSupport jusqu\'à ce qu\'elles aient été indexées.',
    'titlenotbuiltgeoip'         => 'La base de données GeoIP n\'a pas été indexée',
    'msgnotbuiltgeoip'           => 'Les informations de cette base de données GeoIP ne peuvent pas être utilisées par QuickSupport jusqu\'à ce qu\'elles aient été indexées.',
    'titlenotuptodategeoip'      => 'La base de données GeoIP est périmée',
    'msgnotuptodategeoip'        => 'Votre base de données GeoIP est plus récente que celle indexée par QuickSupport. Nous vous recommandons de réindexer votre base de données GeoIP pour disposer des informations de localisation les plus récentes.',
);

return $__LANG;
