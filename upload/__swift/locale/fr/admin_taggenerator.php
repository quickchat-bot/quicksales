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
 * @copyright      Copyright (c) 2001-2014, Kayako
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'chattagtype'                    => 'Type de balise',
    'chatprompttype'                 => 'Canal d\'assistance en direct',
    'desc_chatprompttype'            => '<strong>Live chat:</strong> Lorsqu\'un visiteur clique sur cette balise, une fenêtre de chat en direct s\'affiche.<br /><strong>Call (click to call):</strong> Le visiteur peut alors fournir son numéro de téléphone pour recevoir un rappel immédiat.',
    'chat'                           => 'Chat',
    'callrequest'                    => 'Appel (Cliquer pour appeler)',
    'taggenerator'                   => 'Generateur de balise',
    'manage'                         => 'Gérer',
    'next'                           => 'Suivante',
    'livechat'                       => 'Chat en direct',
    'tabchats'                       => 'Chat en direct',
    'tag_htmlbutton'                 => 'Bouton',
    'desc_tag_htmlbutton'            => 'Cette balise affiche un bouton de statut du chat en direct partout où elle est placée sur vos pages web. L\'état correspond à la disponibilité de votre équipe - en ligne, hors ligne, occupés ou absent. <br/> <br/>Lorsque quelqu\'un clique sur la balise, une fenêtre s\'affiche et demande les informations du visiteur. Celui-ci est alors mis en contact avec un membre disponible de votre équipe et le cas échéant il lui est demandé de laisser un message. <br/> <br/>Le traçage du visiteur est inclus dans cette balise.',
    'tag_sitebadge'                  => 'Badge flottant',
    'desc_tag_sitebadge'             => 'Le badge flottant fonctionne comme un bouton, mais plutôt que de rester fixé au lieu ou la balise est située sur la page, ce badge se place sur le côté gauche de la fenêtre du navigateur et se déplace avec le visiteur, quand il fait défiler la page de haut en bas. <br/> <br/>Le traçage du visiteur est inclus dans cette balise.',
    'tag_textlink'                   => 'Lien texte',
    'desc_tag_textlink'              => 'Cette balise fonctionne aussi comme un bouton, mais un lien de texte s\'affiche plutôt qu\'un graphique. Ce lien de texte est pensé pour les endroits où JavaScript peut ne pas être disponible. <br /><br /> Notez que la balise de lien texte n\'inclut pas le traçage des visiteurs et <strong>ne suivra pas vos visiteurs</strong>.',
    'tag_monitoring'                 => 'Balise de surveillance de visiteur',
    'desc_tag_monitoring'            => 'Cette balise est utilisée pour suivre vos visiteurs sur votre site web (à l\'arrière-plan, de la même façon que le code de suivi de Google Analytics). Elle n\'affiche pas un bouton comme les options précédentes mais le visiteur suivi peut-être invité à un chat par votre équipe ou par une règle de visiteur.',
    'generate'                       => 'Générer la balise',
    'tabchatsextended'               => 'Chst en direct &raquo; %s',
    'filteroptions'                  => 'Options de routage',
    'filterbydepartment'             => 'Restreindre à certains services',
    'desc_filterbydepartment'        => 'Si les services sont sélectionnés ici, les états de chat en direct et les choix de services offerts au visiteur seront valides uniquement pour ces services. .<BR /><BR /><em>Notez que cela signifie que les état de l\'assistance en direct (en ligne, hors ligne, occupé, absent) seront  valides uniquement pour le personnel assigné à ces services.</em>',
    'routetochatskill'               => 'Router vers une compétence spécifique',
    'desc_routetochatskill'          => 'Kayako essaiera de router par le biais de cette balise, les chat ou les demandes d\'appel formulés vers quelqu\'un ayant les compétences spécifiées ici. Le cas échéant, la demande est ajoutée à file d\'attente générale.',
    'generaloptions'                 => 'Options Générales',
    'usesecurelinks'                 => 'Utiliser des URL sécurisée (https://)',
    'desc_usesecurelinks'            => 'Si votre helpdesk est disponible sur HTTPs (SSL), vous pouvez activer ce paramètre pour appliquer cette balise aux visiteurs via HTTPs aussi.',
    'notselected'                    => '- Non sélectionné -',
    'usedefault'                     => '-Utiliser la valeur par défaut-',
    'tagtemplategroup'               => 'Groupe de gabarits',
    'desc_tagtemplategroup'          => 'Le groupe de gabarits qu\'utilisera ce bouton.',
    'skipuserdetails'                => 'Sauter le formulaire d\'informations d\'utilisateur',
    'desc_skipuserdetails'           => 'Si activé, l\'utilisateur sera immédiatement ajouté à la file d\'attente du chat sans que son nom ou son adresse email lui soient demandés. Autrement dit, ils apparaîtront comme « Visiteur » à votre équipe.',
    'nojavascript'                   => 'Désactiver JavaScript',
    'desc_nojavascript'              => 'Activez ce paramètre pour créer une balise qui n\'utilise pas JavaScript. Notez que cela désactive toutes les fonctions de traçage des visiteurs et que ce paramètre est prévu pour être utilisé dans le cadre de gabarits et ou de signatures d\'email où JavaScript ne peut pas s\'exécuter.',
    'defaulttextcontents'            => 'Cliquez ici pour discuter avec un membre de notre équipe',
    'defaulttextcontentscall'        => 'Cliquez ici pour demander un rappel immédiat',
    'textcontents'                   => 'Lien texte',
    'desc_textcontents'              => 'Par exemple, <em>Cliquez ici pour discuter avec un membre de notre équipe</em>.',
    'insertvariable'                 => 'Insérer la variable',
    'insertalert'                    => 'Insérer une alerte du personnel',
    'tabcustomize'                   => 'Personnaliser',
    'customonlineimage'              => 'Bouton d\'état <strong>en ligne</strong> personnalisé',
    'desc_customonlineimage'         => 'Remplacer le bouton d\'état en ligne par défaut en téléchargeant ou en créant un lien vers un autre bouton ici.',
    'customofflineimage'             => 'Bouton d\'état <strong>déconnecté</strong> personnalisé',
    'desc_customofflineimage'        => '',
    'customawayimage'                => 'Bouton d\'état <strong>absent</strong>personnalisé',
    'desc_customawayimage'           => '',
    'custombackshortlyimage'         => 'Bouton d\'état <strong>retour sous peu</strong> personnalisé',
    'desc_custombackshortlyimage'    => '',
    'badgewhite'                     => 'Blanc',
    'badgeblack'                     => 'Noir',
    'sitebadgecolor'                 => 'Couleur du texte',
    'desc_sitebadgecolor'            => 'Sélectionnez la couleur du texte du badge flottant.',
    'sitebadgelanguage'              => 'Langue du badge',
    'desc_sitebadgelanguage'         => 'La langue que le badge flottant utilise pour le texte.',
    'title_tagcode'                  => 'Code de balise généré',
    'desc_tagcode'                   => 'Copiez le contenu de la balise ci-dessous et collez-le dans le code source de votre page web.',
    'tagcode'                        => 'Code de la balise',
    'tagcodeheader'                  => 'DEBUT DU CODE DE LA BALISE - NE PAS MODIFIER PAS !',
    'tagcodefooter'                  => 'FIN DU CODE DE LA BALISE  - NE PAS MODIFIER !',
    'jswarning'                      => 'JAVASCRIPT N\'EST PAS ACTIVÉ',
    'tagpoweredlivechat'             => 'Logiciel de Chat en direct',
    'tagpoweredby'                   => 'par',
    'badgelivechat'                  => 'chat en direct',
    'badgelivehelp'                  => 'assistance en direct',
    'badgecallus'                    => 'appelez-nous',
    'badgecallme'                    => 'appelle-moi',
    'sitebadgetext'                  => 'Texte du badge',
    'desc_sitebadgetext'             => 'L\'étiquette utilisée pour le badge flottant.',
    'sitebadgeonlinecolor'           => 'Couleur d\'arrière-plan : en ligne',
    'desc_sitebadgeonlinecolor'      => 'Cette couleur d\'arrière-plan s\'affiche lorsque votre équipe est disponible.',
    'sitebadgeofflinecolor'          => 'Couleur d\'arrière-plan : hors ligne',
    'desc_sitebadgeofflinecolor'     => '',
    'sitebadgeawaycolor'             => 'Couleur d\'arrière-plan : absent',
    'desc_sitebadgeawaycolor'        => '',
    'sitebadgebackshortlycolor'      => 'Couleur d\'arrière-plan : bientôt de retour',
    'desc_sitebadgebackshortlycolor' => '',
    'titleinvalidfileext'            => 'Type d\'image non pris en charge',
    'msginvalidfileext'              => 'Les types de fichiers image pris en charge sont : gif, jpeg, jpg, png.',

    /*
     * ###############################################
     * BADGE LANGUAGE LOCALES
     * ###############################################
     */
    'badgeenglish'                   => 'Anglais',
);

return $__LANG;
