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

// Initial pass done
// Usage script run

$_SWIFT = SWIFT::GetInstance();

$__LANG = array(
    'charset'                      => 'UTF-8',
    'html_encoding'                => '8 bit',
    'text_encoding'                => '8 bit',
    'html_charset'                 => 'UTF-8',
    'text_charset'                 => 'UTF-8',
    'base_charset'                 => '',
    'yes'                          => 'Oui',
    'no'                           => 'Non',
    'menusupportcenter'            => 'Centre d\'assistance',
    'menustaffcp'                  => 'TB du personnel',
    'menuadmincp'                  => 'TB de l\'administration',
    'app_notreg'                   => 'L\'application %s n\'est pas enregistrée',
    'event_notreg'                 => 'L\'événement %s n\'est pas enregistré',
    'unable_to_execute'            => 'Impossible d\'exécuter %s',
    'action_notreg'                => 'L\'action de %s n\'est pas enregistrée',
    'username'                     => 'Nom d’utilisateur',
    'password'                     => 'Mot de Passe',
    'rememberme'                   => 'Se souvenir de moi',
    'defaulttitle'                 => '%s - propulsé par QuickSupport',
    'login'                        => 'Ouvrir une session',
    'global'                       => 'Global',
    'first'                        => 'Premièr',
    'last'                         => 'Dernièr',
    'pagination'                   => 'Page %s de %s',
    'submit'                       => 'Soumettre',
    'reset'                        => 'Remise à zéro',
    'poweredby'                    => 'Logiciel de helpdesk propulsé par QuickSupport',
    'copyright'                    => 'Copyright &copy; 2001-%s QuickSupport',
    'notifycsrfhash'               => 'QuickSupport a un problème lors de la validation de cette requête',
    'titlecsrfhash'                => 'QuickSupport a un problème lors de la validation de cette requête',
    'msgcsrfhash'                  => 'QuickSupport valide les requêtes pour se protéger contre la falsification de requêtes inter-site et n\'a pas pu valider cette requête. Veuillez essayer de nouveau.',
    'invaliduser'                  => 'Le nom d\'utilisateur ou le mot de passe sont incorrects',
    'invaliduseripres'             => 'Adresse IP non autorisée (tentative : %d de%d)',
    'invaliduserdisabled'          => 'Ce compte est désactivé (tentative : %d de %d)',
    'invalid_sessionid'            => 'Votre session a expiré, veuillez ouvrir une nouvelle session',
    'staff_not_admin'              => 'L\'utilisateur ne dispose pas de privilèges d\'administrateur',
    'sort_desc'                    => 'Tri décroissant',
    'sort_asc'                     => 'Tri croissant',
    'options'                      => 'Options',
    'delete'                       => 'Supprimer',
    'settings'                     => 'Paramètres',
    'search'                       => 'Recherche',
    'searchbutton'                 => 'Recherche',
    'actionconfirm'                => 'Êtes-vous sûr de vouloir continuer ?',
    'loggedout'                    => 'Déconnecté avec succès',
    'view'                         => 'Affichage',
    'dashboard'                    => 'Accueil',
    'help'                         => 'Aide',
    'size'                         => 'Taille',
    'home'                         => 'Accueil',
    'logout'                       => 'Déconnexion',
    'staffcp'                      => 'TB du personnel',
    'admincp'                      => 'TB de l\'administration',
    'winapp'                       => 'QuickSupport Desktop',
    'staffapi'                     => 'Staff API',
    'bytes'                        => 'Octets',
    'kb'                           => 'KO',
    'mb'                           => 'MB',
    'gb'                           => 'GO',
    'noitemstodisplay'             => 'Aucun élément à afficher',
    'manage'                       => 'Gérer',
    'title'                        => 'Titre',
    'disable'                      => 'Désactiver',
    'enable'                       => 'Activer',
    'edit'                         => 'Modifier',
    'back'                         => 'Précédent',
    'forward'                      => 'Suivante',
    'insert'                       => 'Ajouter',
    'edit'                         => 'Modifier',
    'update'                       => 'Mettre à jour',
    'public'                       => 'Publique',
    'private'                      => 'Privé',
    'requiredfieldempty'           => 'Un des champs requis est vide',
    'clifatalerror'                => 'ERREUR IRRÉCUPÉRABLE',
    'clienterchoice'               => 'Veuillez entrer votre choix : ',
    'clinotvalidchoice'            => '"%s"  n\'est pas sélectionnable ; Veuillez Réessayer.: ',
    'description'                  => 'Description',
    'success'                      => 'Succès',
    'failure'                      => 'Échec',
    'status'                       => 'État',
    'date'                         => 'Date',
    'seconds'                      => 'Secondes',
    'order'                        => 'Ordre',
    'email'                        => 'Email',
    'subject'                      => 'Objet',
    'contents'                     => 'Contenus',
    'sunday'                       => 'Dimanche',
    'monday'                       => 'Lundi',
    'tuesday'                      => 'Mardi',
    'wednesday'                    => 'Mercredi',
    'thursday'                     => 'Jeudi',
    'friday'                       => 'Vendredi',
    'saturday'                     => 'Samedi',
    'am'                           => 'AM',
    'pm'                           => 'PM',
    'pfieldreveal'                 => '[Revéler]',
    'pfieldhide'                   => '[Cacher]',
    'loadingwindow'                => 'Chargement en cours...',
    'customfields'                 => 'Champs personnalisés',
    'nopermission'                 => 'Malheureusement, vous ne disposez pas des autorisations nécessaires pour soumettre cette requête.',
    'nopermissiontext'             => 'Malheureusement, vous ne disposez pas des autorisations nécessaires pour soumettre cette requête.',
    'generationdate'               => 'Code XML généré : %s',
    'approve'                      => 'Approuver',
    'paginall'                     => 'Tous',
    'fullname'                     => 'Nom et prénom',
    'onlineusers'                  => 'Personnel en ligne',
    'vardate1'                     => '%dd %dh %dm',
    'vardate2'                     => '%dh %dm %ds',
    'vardate3'                     => '%dm %ds',
    'vardate4'                     => '%ds',
    'reports'                      => 'Rapports',
    'demomode'                     => 'Cette action ne peut être effectuée en mode démo',
    'unmodifiedreport'             => 'Running the report unmodified as user does not have permission to modify report.',
    'titledemomode'                => 'Impossible de continuer',
    'msgdemomode'                  => 'Cette action ne peut être effectuée en mode démo',
    'filter'                       => 'Filtre',
    'editor'                       => 'Éditeur de texte',
    'images'                       => 'Images',
    'tabedit'                      => 'Modifier',
    'notifyfieldempty'             => 'Un des champs requis est vide',
    'notifykqlfieldempty'          => 'KQL Query est vide',
    'titlefieldempty'              => 'Champs manquants',
    'msgfieldempty'                => 'Un des champ obligatoires est vide ou contient des données non valides ; veuillez vérifier votre saisie.',
    'msgpastdate'                   => 'La date ne peut pas être dans le passé',
    'titlefieldinvalid'            => 'Valeur invalide',
    'msgfieldinvalid'              => 'Un des champs contient des données invalides; veuillez vérifier votre saisie.',
    'titleinvalidemail'            => 'Invalid Email',
    'msginvalidemail'              => 'The email you entered is same as your customer email; please check your input.',
    'msginvalidadditionalemail'    => 'The email address entered is already used in the desk; Please enter the valid email address.',
    'save'                         => 'Enregistrer',
    'viewall'                      => 'Afficher tout',
    'cancel'                       => 'Annuler',
    'tabgeneral'                   => 'Généralités',
    'language'                     => 'Langue',
    'loginshare'                   => 'LoginShare',
    'licenselimit_unabletocreate'  => 'Un nouvel utilisateur du personnel n\'a pas pu être créé parce que la limite imposée par votre licence a été atteinte',
    'help'                         => 'Aide',
    'name'                         => 'Nom',
    'value'                        => 'Valeur',
    'engagevisitor'                => 'Interpeller le visiteur',
    'inlinechat'                   => 'Chat en ligne',
    'url'                          => 'URL',
    'hexcode'                      => 'Code hexadécimal',
    'vactionvariables'             => 'Action : variables',
    'vactionvexp'                  => 'Action : expérience du visiteur',
    'vactionsalerts'               => 'Action : notifications de personnel',
    'vactionsetdepartment'         => 'Action : définir les services',
    'vactionsetskill'              => 'Action : définir la compétence',
    'vactionsetgroup'              => 'Action: définir le groupe de visiteurs',
    'vactionsetcolor'              => 'Action : définir la couleur',
    'vactionbanvisitor'            => 'Action : interdire visiteur',
    'customengagevisitor'          => 'Interpellation personnalisé d\'un visiteur',
    'managerules'                  => 'Gérer les règles',
    'open'                         => 'Ouvrir',
    'close'                        => 'Fermer',
    'titleupdatedswiftsettings'    => 'Mise à jour des paramètres de %s',
    'updatedsettingspartially'     => 'Les paramètres suivants n\'ont pas été mis à jour',
    'msgupdatedswiftsettings'      => 'Les paramètres de %s ont été mis à jour avec succès.',
    'geoipprocessrunning'          => 'La construction de base de données GeoIP est déjà en cours',
    'continueprocessquestion'      => 'Une tâche est encore en cours d\'exécution. Si vous quittez cette page, elle sera annulée. Voulez-vous continuer ?',
    'titleupdsettings'             => 'Mise à jour des paramètres de %s',
    'type'                         => 'Type',
    'banip'                        => 'IP (255.255.255.255)',
    'banclassc'                    => 'Class C (255.255.255.*)',
    'banclassb'                    => 'Class B (255.255.*.*)',
    'banclassa'                    => 'Class A (255.*.*.*)',
    'if'                           => 'Si',
    'loginlogerror'                => 'Ouverture de session verrouillée pour %d minutes (tentatives : %d de %d)',
    'loginlogerrorsecs'            => 'Ouverture de session verrouillée pour %d secondes (tentatives : %d de %d)',
    'loginlogwarning'              => 'Nom d\'utilisateur ou mot de passe non valide (tentatives : %d de %d)',
    'na'                           => '- NA -',
    'redirectloading'              => 'Chargement en cours...',
    'noinfoinview'                 => 'Il n\'y a rien à afficher ici',
    'nochange'                     => '-- Aucun changement --',
    'activestaff'                  => '-- Personnel actif --',
    'notificationuser'             => 'Utilisateur',
    'notificationuserorganization' => 'Organisation de l\'utilisateur',
    'notificationstaff'            => 'Personnel (propriétaire)',
    'notificationteam'             => 'Équipes d\'opérateurs',
    'notificationdepartment'       => 'Service',
    'notificationsubject'          => 'Objet : ',
    'lastupdate'                   => 'Dernière mise à jour',
    'interface_admin'              => 'TB de l\'administration',
    'interface_staff'              => 'TB du personnel',
    'interface_intranet'           => 'Intranet',
    'interface_api'                => 'API',
    'interface_winapp'             => 'API de QuickSupport Desktop/Personnel',
    'interface_syncworks'          => 'SyncWorks',
    'interface_instaalert'         => 'InstaAlert',
    'interface_pda'                => 'PDA',
    'interface_rss'                => 'RSS',
    'error_database'               => 'Base de données',
    'error_php'                    => 'PHP',
    'error_exception'              => 'Exception',
    'error_mail'                   => 'Email',
    'error_general'                => 'Généralités',
    'error_loginshare'             => 'LoginShare',
    'loading'                      => 'Chargement en cours...',
    'pwtooshort'                   => 'Trop court',
    'pwveryweak'                   => 'Trop faible',
    'pwweak'                       => 'Faible',
    'pwmedium'                     => 'Moyen',
    'pwstrong'                     => 'Fort',
    'pwverystrong'                 => 'Très fort',
    'pwunsafeword'                 => 'Mot de passe potentiellement dangereux',
    'staffpasswordpolicy'          => '<strong>Exigences de mot de passe :</strong> Longueur minimale : %d caractères, minimum de chiffres : %d, minimum de symboles : %d,minimum de majuscules: %d',
    'userpasswordpolicy'           => '<strong>Exigences de mot de passe :</strong> Longueur minimale : %d caractères, minimum de chiffres : %d, minimum de symboles : %d,minimum de majuscules: %d',
    'titlepwpolicy'                => 'Le mot de passe ne respecte pas les exigences',
    'passwordexpired'              => 'le mot de passe a expiré',
    'newpassword'                  => 'Nouveau mot de passe',
    'passwordagain'                => 'Mot de passe (répétition)',
    'passworddontmatch'            => 'Les mots de passe ne correspondent pas',
    'defaulttimezone'              => '-- Fuseau horaire par défaut --',
    'tagcloud'                     => 'Nuage de Tags',
    'searchmodeactive'             => 'Les résultats sont filtrés - cliquez pour réinitialiser',
    'notifysearchfailed'           => '« 0 » Résultats trouvés',
    'titlesearchfailed'            => '« 0 » Résultats trouvés',
    'msgsearchfailed'              => 'QuickSupport n\'a pas pu localiser des enregistrements correspondant aux critères spécifiés.',
    'quickfilter'                  => 'Filtre rapide',
    'fuenterurl'                   => 'Entrez l\'URL :',
    'fuorupload'                   => 'ou télécharger :',
    'errorsmtpconnect'             => 'Impossible de se connecter au serveur SMTP',
    'starttypingtags'              => 'Commencez à taper pour insérer des balises...',
    'unsupportedtagchars'          => 'One or more unsupported characters were stripped from the tag.',
    'titleinvalidfileext'          => 'Type d\'image non pris en charge',
    'msginvalidfileext'            => 'Les types de fichiers image pris en charge sont : gif, jpeg, jpg, png.',
    'notset'                       => '-- Non défini --',
    'ratings'                      => 'Classements',
    'system'                       => 'Système',
    'schatid'                      => 'ID de chat',
    'supportcenterfield'           => 'Centre d\'assistance :',
    'smessagesurvey'               => 'Messages/sondages',
    'nosubject'                    => '(aucun objet)',
    'nolocale'                     => '(aucun paramètre régional)',
    'markdefault'                   => 'Marquer comme défaut',
    'policyurlupdatetitle'           => 'URL de stratégie mise à jour',
    'policyurlupdatemessage'       => 'L\'URL de la stratégie a été mise à jour avec succès.',

    // Easy Dates
    'edoneyear'                    => 'un an',
    'edxyear'                      => '%d ans',
    'edonemonth'                   => 'un mois',
    'edxmonth'                     => '%d mois',
    'edoneday'                     => 'un jour',
    'edxday'                       => '%d jours',
    'edonehour'                    => 'une heure',
    'edxhour'                      => '%d heures',
    'edoneminute'                  => 'Une minute',
    'edxminute'                    => '%d minutes',
    'edjustnow'                    => 'Bientôt',
    'edxseconds'                   => '%d secondes',
    'ago'                          => 'Il y a',

    // Operators
    'opcontains'                   => 'Contient',
    'opnotcontains'                => 'Ne contient pas',
    'opequal'                      => 'Égal à',
    'opnotequal'                   => 'N\'est pas égal à',
    'opgreater'                    => 'Supérieur à',
    'opless'                       => 'Inférieur à',
    'opregexp'                     => 'Expression rationelle',
    'opchanged'                    => 'Modifié',
    'opnotchanged'                 => 'Non modifié',
    'opchangedfrom'                => 'Passé de',
    'opchangedto'                  => 'Passé à',
    'opnotchangedfrom'             => 'Non changé de',
    'opnotchangedto'               => 'Non changé à',
    'matchand'                     => 'ET',
    'matchor'                      => 'OU',
    'strue'                        => 'Vrai',
    'sfalse'                       => 'Faux',
    'notifynoperm'                 => 'Malheureusement, vous ne disposez pas des autorisations nécessaires pour soumettre cette requête.',
    'titlenoperm'                  => 'Autorisations insuffisantes',
    'msgnoperm'                    => 'Malheureusement, vous ne disposez pas des autorisations nécessaires pour soumettre cette requête.',
    'msgnoperm1'                   => 'The ticket has been created but you do not have the permission to carry out other operations.',
    'cyesterday'                   => 'Hier',
    'ctoday'                       => 'Aujoud\'hui',
    'ccurrentwtd'                  => 'Semaine en cours à ce jour',
    'ccurrentmtd'                  => 'Mois en cours à ce jour',
    'ccurrentytd'                  => 'Année en cours à ce jour',
    'cl7days'                      => 'Les 7 derniers jours',
    'cl30days'                     => 'Les 30 derniers jours',
    'cl90days'                     => 'Les 90 derniers jours',
    'cl180days'                    => 'Les 180 derniers jours',
    'cl365days'                    => 'Les 365 derniers jours',
    'ctomorrow'                    => 'Demain',
    'cnextwfd'                     => 'Semaine en cours à compter de la date',
    'cnextmfd'                     => 'Mois en cours à compter de la date',
    'cnextyfd'                     => 'Année en cours à compter de la date',
    'cn7days'                      => '7 prochains jours',
    'cn30days'                     => '30 prochains jours',
    'cn90days'                     => '90 prochains jours',
    'cn180days'                    => '180 prochains jours',
    'cn365days'                    => '365 prochains jours',
    'new'                          => 'Nouveau',
    'phoneext'                     => 'Téléphone : %s',
    'snewtickets'                  => 'Nouveaux tickets',
    'sadvancedsearch'              => 'Recherche avancée',
    'squicksearch'                 => 'Recherche rapide :',
    'sticketidlookup'              => 'Recherche d\'ID de ticket :',
    'screatorreplier'              => 'Créateur/auteur de la réponse :',
    'smanage'                      => 'Gérer',
    'clear'                        => 'Effacer',
    'never'                        => 'Jamais',
    'seuser'                       => 'Utlisateurs',
    'seuserorg'                    => 'Organisations d\'utilisateurs',
    'manage'                       => 'Gérer',
    'import'                       => 'Importer',
    'export'                       => 'Exporter',
    'comments'                     => 'Commentaires',
    'commentdata'                  => 'Commentaires :',
    'postnewcomment'               => 'Poster un nouveau commentaire',
    'replytocomment'               => 'Répondre au commentaire',
    'buttonsubmit'                 => 'Soumettre',
    'reply'                        => 'Répondre',

    // Flags
    'purpleflag'                   => 'Indicateur violet',
    'redflag'                      => 'Indicateur rouge',
    'orangeflag'                   => 'Indicateur Orange',
    'yellowflag'                   => 'Indicateur jaune',
    'blueflag'                     => 'Indicateur bleu',
    'greenflag'                    => 'Indicateur vert',

    'calendar'                     => 'Calendrier',
    'cal_january'                  => 'Janvier',
    'cal_february'                 => 'Février',
    'cal_march'                    => 'Mars',
    'cal_april'                    => 'Avril',
    'cal_may'                      => 'Mai',
    'cal_june'                     => 'Juin',
    'cal_july'                     => 'Juillet',
    'cal_august'                   => 'Août',
    'cal_september'                => 'Septembre',
    'cal_october'                  => 'Octobre',
    'cal_november'                 => 'Novembre',
    'cal_december'                 => 'Décembre',

    /**
     * ###############################################
     * APP LIST
     * ###############################################
     */
    'app_base'                     => 'Base',
    'app_tickets'                  => 'Tickets',
    'app_knowledgebase'            => 'Base de connaissances',
    'app_parser'                   => 'Analyseur d\'email',
    'app_livechat'                 => 'Assistance en direct',
    'app_troubleshooter'           => 'Dépannage',
    'app_news'                     => 'Actualités',
    'app_core'                     => 'Noyau',
    'app_backend'                  => 'Système principal',
    'app_reports'                  => 'Rapports',

    // Potentialy unused phrases in en-us.php
    'defaultloginapi'              => 'QuickSupport Login Routine',
    'redirect_login'               => 'Processing Login...',
    'redirect_dashboard'           => 'Redirecting to Home...',
    'no_wait'                      => 'Please click here if your browser does not automatically redirect you',
    'select_un_all'                => 'Select/Unselect All Items',
    'quicksearch'                  => 'Quick Search',
    'mass_action'                  => 'Mass Action',
    'massfieldaction'              => 'Action: ',
    'advanced_search'              => 'Advanced Search',
    'searchfieldquery'             => 'Query: ',
    'searchfieldfield'             => 'Field: ',
    'settingsfieldresultsperpage'  => 'Results Per Page: ',
    'clidefault'                   => '%s v%s\\nCopyright (c) 2001-%s QuickSupport\\n',
    'firstselect'                  => '- Select -',
    'exportasxml'                  => 'XML',
    'exportascsv'                  => 'CSV',
    'exportassql'                  => 'SQL',
    'exportaspdf'                  => 'PDF',
    'clientarea'                   => 'Support Center',
    'pdainterface'                 => 'PDA Interface',
    'kayakomobile'                 => 'QuickSupport Mobile',
    'thousandsseperator'           => ',',
    'clierror'                     => '[ERREUR] : ',
    'cliwarning'                   => '[AVERTISSEMENT] : ',
    'cliok'                        => '[OK] : ',
    'cliinfo'                      => '[INFO] : ',
    'sections'                     => 'Sections',
    'twodesc'                      => '%s (%s)',
    'hourrenderus'                 => '%s:%s %s',
    'hourrendereu'                 => '%s:%s',
    'jump'                         => 'Saut',
    'newprvmsgconfirm'             => 'Vous avez un nouveau message privé\\nCliquez OK pour ouvrir le message privé dans une nouvelle fenêtre.',
    'commentdelconfirm'            => 'Commentaire supprimé avec succès',
    'commentstatusconfirm'         => 'Changement d\'état de commentaire terminé avec succès',
    'commentupdconfirm'            => 'Comment de "%s" mis à jour avec succès',
    'unapprove'                    => 'Retirer l\'approbation',
    'approvedcomments'             => 'Approved Comments',
    'unapprovedcomments'           => 'Unapproved Comments',
    'editcomment'                  => 'Edit Comment',
    'quickjump'                    => 'Quick Jump',
    'choiceadd'                    => 'Add >',
    'choicerem'                    => '< Remove',
    'choicemup'                    => 'Move Up',
    'choicemdn'                    => 'Move Down',
    'ticketsubjectformat'          => '[%s#%s]: %s',
    'forwardticketsubjectformat'   => '[%s~%s]: %s',
    'loggedinas'                   => 'Logged In: ',
    'tcustomize'                   => 'Customize...',
    'notifydemomode'               => 'Permission denied. Product is in demo mode.',
    'uploadfile'                   => 'Upload File: ',
    'uploadedimages'               => 'Uploaded Images',
    'tabinsert'                    => 'Insert',
    'allpages'                     => 'All Pages',
    'maddimage'                    => 'Image',
    'maddlinktoimage'              => 'Link to Image',
    'maddthumbnail'                => 'Thumbnail',
    'maddthumbnailwithlink'        => 'Thumbnail with Link',
    'checkuncheckall'              => 'Check/Uncheck All',
    'defaultloginshare'            => 'QuickSupport LoginShare',
    'invalidusernoapiaccess'       => 'Invalid Staff. This staff does not have API access, please configure under Settings > General.',
    'msgupdsettings'               => 'Successfully updated all settings for "%s"',
    'msgpwpolicy'                  => 'The password specified does not match the requirements of the Password Policy.',
    'passwordpolicymismatch'       => 'The password specified does not match the requirements of the Password Policy.',
    'short_all_tickets'            => 'All',
    'iprestrictdenial'             => 'Access Denied (%s): IP not allowed (%s), please add the IP in the allowed list under /config/config.php',
    'cal_clear'                    => 'Clear',
    'cal_close'                    => 'Close',
    'cal_prev'                     => 'Prev',
    'cal_next'                     => 'Next',
    'cal_today'                    => 'Today',
    'cal_sunday'                   => 'Su',
    'cal_monday'                   => 'Mo',
    'cal_tuesday'                  => 'Tu',
    'cal_wednesday'                => 'We',
    'cal_thursday'                 => 'Th',
    'cal_friday'                   => 'Fr',
    'cal_saturday'                 => 'Sa',
    'app_bugs'                     => 'Bugs',
    'wrong_profile_image'          => "L'image du profil n'a pas été mise à jour. Mauvais format.",
    'wrong_image_size'             => 'La taille de l\'image est supérieure à la taille de téléchargement autorisée.',
);


/*
 * ###############################################
 * BEGIN INTERFACE RELATED CODE
 * ###############################################
 */


if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_ADMIN) {
    /**
     * Admin Area Navigation Bar
     */

    $_adminBarContainer = array(

        14 => array('Paramètres', 'bar_settings.gif', APP_CORE, '/Base/Settings/Index'),
        26 => array('REST API', 'bar_restapi.gif', APP_BASE),
        27 => array('Generateur de balise', 'bar_tag.gif', APP_LIVECHAT, '/Base/TagGenerator/Index'),
        0  => array('Modèles', 'bar_templates.gif', APP_BASE),
        1  => array('Langues', 'bar_languages.gif', APP_CORE),
        2  => array('Champs personnalisés', 'bar_customfields.gif', APP_CORE),
        25 => array('GeoIP', 'bar_geoip.gif', APP_CORE, '/Base/GeoIP/Manage'),
        13 => array('Assistance en direct', 'bar_livesupport.gif', APP_LIVECHAT),
        4  => array('Analyseur d\'email', 'bar_mailparser.gif', APP_PARSER),
        5  => array('Tickets', 'bar_tickets.gif', APP_TICKETS),
        35 => array ('Consentement de l\'utilisateur', 'bar_maintenance.gif', APP_BASE, '/Base/Consent/Index'),
        29 => array('Flux de travail', 'bar_workflow.gif', APP_TICKETS, '/Tickets/Workflow/Manage'),
        30 => array('Classements', 'bar_ratings.gif', APP_TICKETS, '/Base/Rating/Manage'),
        6  => array('SLA', 'bar_sla.gif', APP_TICKETS),
        7  => array('Escalades', 'bar_escalations.gif', APP_TICKETS, '/Tickets/Escalation/Manage'),
        20 => array('Bayésien', 'bar_bayesian.gif', APP_TICKETS),
        21 => array('Base de connaissances', 'bar_knowledgebase.gif', APP_KNOWLEDGEBASE),
        23 => array('Actualités', 'bar_news.gif', APP_NEWS),
        24 => array('Dépannage', 'bar_troubleshooter.gif', APP_TROUBLESHOOTER),
        31 => array('Gadgets', 'bar_widgets.gif', APP_BASE, '/Base/Widget/Manage'),
        32 => array('Apps', 'bar_apps.gif', APP_BASE, '/Base/App/Manage'),
        9  => array('Journaux', 'bar_logs.gif', APP_BASE),
        10 => array('Tâches planifiées', 'bar_cron.gif', APP_BASE),
        11 => array('Base de données', 'bar_database.gif', APP_BASE),
        33 => array('Importer', 'bar_import.gif', APP_BASE),
        12 => array('Diagnostiques', 'bar_diagnostics.gif', APP_BASE),
        34 => array('Maintenance', 'bar_maintenance.gif', APP_BASE),
    );

    SWIFT::Set('adminbar', $_adminBarContainer);

    $_adminBarItemContainer = array(
        0  => array(
            0 => array('Groupes', '/Base/TemplateGroup/Manage'),
            1 => array('Modèles', '/Base/Template/Manage'),
            2 => array('Recherche', '/Base/TemplateSearch/Index'),
            3 => array('Importer/Exporter', '/Base/TemplateManager/ImpEx'),
            4 => array('Restaurer', '/Base/TemplateRestore/Index'),
            5 => array('Diagnostiques', '/Base/TemplateDiagnostics/Index'),
            6 => array('Logos de l\'en-tête', '/Base/TemplateManager/Personalize'),
        ),

        1  => array(
            0 => array('Langues', '/Base/Language/Manage'),
            1 => array('Phrases', '/Base/LanguagePhrase/Manage'),
            2 => array('Recherche', '/Base/LanguagePhrase/Search'),
            3 => array('Importer/Exporter', '/Base/LanguageManager/ImpEx'),
            4 => array('Restaurer', '/Base/LanguageManager/Restore'),
            5 => array('Diagnostiques', '/Base/LanguageManager/Diagnostics'),
        ),

        2  => array(
            0 => array('Groupes', '/Base/CustomFieldGroup/Manage'),
            1 => array('Champs', '/Base/CustomField/Manage'),
        ),

        4  => array(
            0 => array('Paramètres', '/Parser/SettingsManager/Index'),
            1 => array('Files d\'attent', '/Parser/EmailQueue/Manage'),
            2 => array('Règles', '/Parser/Rule/Manage'),
            3 => array('Breaklines', '/Parser/Breakline/Manage'),
            4 => array('Bans', '/Parser/Ban/Manage'),
            5 => array('Règles fourre-tout', '/Parser/CatchAll/Manage'),
            6 => array('Blocages de boucles', '/Parser/LoopBlock/Manage'),
            7 => array('Règles de bloqueur de boucles', '/Parser/LoopRule/Manage'),
            9 => array('Analyseur de journal', '/Parser/ParserLog/Manage'),
        ),

        5  => array(
            0 => array('Paramètres', '/Tickets/SettingsManager/Index'),
            1 => array('Types', '/Tickets/Type/Manage'),
            2 => array('État', '/Tickets/Status/Manage'),
            3 => array('Priorités', '/Tickets/Priority/Manage'),
            4 => array('Types de fichiers', '/Tickets/FileType/Manage'),
            5 => array('Liens', '/Tickets/Link/Manage'),
            8 => array('Auto Close', '/Tickets/AutoClose/Manage'),
            7 => array('Maintenance', '/Tickets/Maintenance/Index'),
        ),

        6  => array(
            0 => array('Paramètres', '/Tickets/SettingsManager/SLA'),
            1 => array('Plans', '/Tickets/SLA/Manage'),
            2 => array('Horaires', '/Tickets/Schedule/Manage'),
            3 => array('Vacances', '/Tickets/Holiday/Manage'),
            4 => array('Importer/Exporter', '/Tickets/HolidayManager/Index'),
        ),

        20 => array(
            0 => array('Paramètres', '/Tickets/SettingsManager/Bayesian'),
            1 => array('Catégories', '/Tickets/BayesianCategory/Manage'),
            2 => array('Diagnostiques', '/Tickets/BayesianDiagnostics/Index'),
        ),

        9  => array(
            0 => array('Journal des erreurs', '/Base/ErrorLog/Manage'),
            1 => array('Journal des tâches', '/Base/ScheduledTasks/TaskLog'),
            3 => array('Journal d\'activité', '/Base/ActivityLog/Manage'),
            4 => array('Journal de connexion', '/Base/LoginLog/Manage'),
        ),

        10 => array(
            0 => array('Gérer', '/Base/ScheduledTasks/Manage'),
            1 => array('Journal des tâches', '/Base/ScheduledTasks/TaskLog'),
        ),

        11 => array(
            0 => array('Informations de la table', '/Base/Database/TableInfo'),
        ),

        12 => array(
            0 => array('Sessions actives', '/Base/Diagnostics/ActiveSessions'),
            1 => array('Infos Cache', '/Base/Diagnostics/CacheInformation'),
            2 => array('Reconstruire le cache', '/Base/Diagnostics/RebuildCache'),
            3 => array('Informations PHP', '/Base/Diagnostics/PHPInfo'),
            4 => array('Rapporter le problème', '/Base/Diagnostics/ReportBug'),
            5 => array('Infos licence', '/Base/Diagnostics/LicenseInformation'),
        ),

        13 => array(
            0 => array('Paramètres', '/LiveChat/SettingsManager/Index'),
            1 => array('Règles visiteur', '/LiveChat/Rule/Manage'),
            2 => array('Groupes de visiteurs', '/LiveChat/Group/Manage'),
            3 => array('Compétences du personnel', '/LiveChat/Skill/Manage'),
            4 => array('Bannissements visiteurs', '/LiveChat/Ban/Manage'),
            5 => array('Routage des messages', '/LiveChat/MessageRouting/Index'),
            6 => array('Statut du personnel', '/LiveChat/OnlineStatus/Index'),
        ),

        19 => array(
            0 => array('Paramètres', '/Manuals/SettingsManager/Index'),
        ),

        21 => array(
            0 => array('Paramètres', '/KnowledgeBase/SettingsManager/Index'),
            1 => array('Maintenance', '/KnowledgeBase/Maintenance/Index'),
        ),

        22 => array(
            0 => array('Paramètres', '/Downloads/SettingsManager/Index'),
        ),

        23 => array(
            0 => array('Paramètres', '/News/SettingsManager/Index'),
            1 => array('Importer/Exporter', '/News/ImpEx/Manage'),
        ),

        24 => array(
            0 => array('Paramètres', '/Troubleshooter/SettingsManager/Index'),
        ),

        25 => array(
            0 => array('Maintenance', '/Base/GeoIP/Manage'),
        ),

        26 => array(
            0 => array('Paramètres', '/Base/Settings/RESTAPI'),
            1 => array('Informations API', '/Base/RESTAPI/Index'),
        ),

        33 => array(
            0 => array('Gérer', '/Base/Import/Manage'),
            1 => array('Journal d\'importation', '/Base/ImportLog/Manage'),
        ),

        34 => array(
            0 => array('Purger les pièces jointes', '/Base/PurgeAttachments/Index'),
            1 => array('Déplacer les pièces jointes', '/Base/MoveAttachments/Index'),
        ),

    );

    // Log stuff
    if (SWIFT_PRODUCT == 'Fusion' || SWIFT_PRODUCT == 'Resolve' || SWIFT_PRODUCT == 'Case') {
        $_adminBarItemContainer[9][2] = array('Parser Log', '/Parser/ParserLog/Manage');
    }

    if (SWIFT_PRODUCT == 'Fusion' || SWIFT_PRODUCT == 'Engage') {
        unset($_adminBarContainer[27]);
    }

    SWIFT::Set('adminbaritems', $_adminBarItemContainer);


    /**
     * Admin Area Menu Links
     * Translate the Highlighted Text: 0 => array (>>>'Home'<<<, 100, APP_NAME),
     * ! IMPORTANT ! The following array does NOT have a Zero based index
     */

    $_adminMenuContainer = array(

        1 => array('Accueil', 80, APP_CORE),
        2 => array('Personnel', 100, APP_BASE),
        3 => array('Services', 120, APP_BASE),
        4 => array('Utilisateurs', 100, APP_BASE),
    );

    SWIFT::Set('adminmenu', $_adminMenuContainer);

    /**
     * Item Format Example: 0 => array ('Name Of Item to Display', 'www.link-to-item.com', PREFIX_SPACING, SUFFIX_BAR_AND_SPACING),
     * The PREFIX_SPACING and SUFFIX_BAR_AND_SPACING are required for the theme to display the separator items for the menu items
     */
    $_adminLinkContainer = array(

        1 => array(
            0 => array('Accueil', '/Base/Home/Index'),
            1 => array('Paramètres', '/Base/Settings/Index'),
        ),

        2 => array(
            0 => array('Gérer le personnel', '/Base/Staff/Manage'),
            1 => array('Gérer les équipes', '/Base/StaffGroup/Manage'),
            2 => array('Ajouter des opérateurs', '/Base/Staff/Insert'),
            3 => array('Ajouter équipes', '/Base/StaffGroup/Insert'),
            4 => array('LoginShare', '/Base/Settings/StaffLoginShare'),
            5 => array('Settings', '/Base/Settings/Staff'),
        ),

        3 => array(
            0 => array('Gérer les départements', '/Base/Department/Manage'),
            1 => array('Ajouter le service', '/Base/Department/Insert'),
            2 => array('Vue d\'ensemble de l\'accès', '/Base/Department/AccessOverview'),
        ),

        4 => array(
            0 => array('Vue d\'ensemble de l\'accès', '/Base/UserGroup/Manage'),
            1 => array('Insérer groupes d\'utilisateurs', '/Base/UserGroup/Insert'),
            2 => array('LoginShare', '/Base/Settings/UserLoginShare'),
            3 => array('Paramètres', '/Base/Settings/User'),
        ),
    );

    SWIFT::Set('adminlinks', $_adminLinkContainer);
} else if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_STAFF) {
    /**
     * Staff Area Menu Links
     * Translate the Highlighted Text: 0 => array (>>>'Home'<<<, 100),
     * ! IMPORTANT ! The following array does NOT have a Zero based index
     */

    $_staffMenuContainer = array(
        1 => array('Accueil', 80, APP_CORE),
        2 => array('Tickets', 100, APP_TICKETS, 't_entab'),
        3 => array('Assistance en direct', 120, APP_LIVECHAT, 'ls_entab'),
        4 => array('Base de connaissances', 140, APP_KNOWLEDGEBASE, 'kb_entab'),
        6 => array('Dépannage', 140, APP_TROUBLESHOOTER, 'tr_entab'),
        7 => array('Actualités', 90, APP_NEWS, 'nw_entab'),
        8 => array('Utilisateurs', 90, APP_CORE, 'cu_entab'),
        9 => array('Rapports', 90, APP_REPORTS, 'rp_entab'),
    );

    SWIFT::Set('staffmenu', $_staffMenuContainer);

    /**
     * Item Format Example: 0 => array ('Name Of Item to Display', 'www.link-to-item.com', PREFIX_SPACING, SUFFIX_BAR_AND_SPACING),
     * The PREFIX_SPACING and SUFFIX_BAR_AND_SPACING are required for the theme to display the separator items for the menu items
     */
    $_staffLinkContainer = array(

        1 => array(
            0 => array('Accueil', '/Base/Home/Index'),
            1 => array('Mes préférences', '/Base/Preferences/ViewPreferences'),
            2 => array('Notifications', '/Base/Notification/Manage', 'staff_canviewnotifications'),
            3 => array('Commentaires', '/Base/Comment/Manage', 'staff_canviewcomments'),
        ),

        2 => array(
            0 => array('Gérer les Tickets', '/Tickets/Manage/Index', 'staff_tcanviewtickets'),
            1 => array('Recherche', ':UIDropDown(\'ticketsearchmenu\', event, \'linkmenu2_1\', \'linksdiv\'); LinkTicketSearchForms();'),
            2 => array(
                'Nouveau ticket', ':UICreateWindow(\'/Tickets/Ticket/NewTicket/\', \'newticket\', \'New Ticket\', \'Loading..\', 500, 350, true);', 'staff_tcaninsertticket'
            ),
            3 => array('Macros', '/Tickets/MacroCategory/Manage', 'staff_tcanviewmacro'),
            4 => array('Vues', '/Tickets/View/Manage', 'staff_tcanview_views'),
            5 => array('Filtres', ':UIDropDown(\'ticketfiltermenu\', event, \'linkmenu2_5\', \'linksdiv\');'),
        ),

        3 => array(
            0 => array('Historique du chat', '/LiveChat/ChatHistory/Manage', 'staff_lscanviewchat'),
            1 => array('Messages/sondages', '/LiveChat/Message/Manage', 'staff_lscanviewmessages'),
            2 => array('Journaux d\'appels', '/LiveChat/Call/Manage', 'staff_lscanviewcalls'),
            3 => array('Réponses préenregistrées', '/LiveChat/CannedCategory/Manage', 'admin_lscanviewcanned'),
            4 => array('Recherche', ':UIDropDown(\'chatsearchmenu\', event, \'linkmenu3_4\', \'linksdiv\'); LinkChatSearchForms();'),
        ),

        4 => array(
            0 => array('Afficher la base de connaissances', '/Knowledgebase/ViewKnowledgebase/Index'),
            1 => array('Gérer base de connaissances', '/Knowledgebase/Article/Manage'),
            2 => array('Catégories', '/Knowledgebase/Category/Manage'),
            3 => array('Nouvel Article', '/Knowledgebase/Article/Insert'),
        ),

        5 => array(
            0 => array('View Downloads', '/Downloads/Downloads/Manage'),
            1 => array('Manage Downloads', '/Downloads/Downloads/Manage'),
            2 => array('Catégories', '/Downloads/Category/Insert'),
            3 => array('New File', '/Downloads/File/Insert'),
        ),

        6 => array(
            0 => array('Voir Dépanneur', '/Troubleshooter/Category/ViewAll'),
            1 => array('Gérer Dépanneur', '/Troubleshooter/Step/Manage'),
            2 => array('Catégories', '/Troubleshooter/Category/Manage'),
            3 => array('Nouvelle étape', ':UICreateWindow(\'/Troubleshooter/Step/InsertDialog/\', \'newstep\', \'Insert Step\', \'Loading..\', 400, 200, true);'),
        ),

        7 => array(
            0 => array('Afficher les actualités', '/News/NewsItem/ViewAll', 'staff_nwcanviewitems'),
            1 => array('Gérer les actualités', '/News/NewsItem/Manage', 'staff_nwcanmanageitems'),
            2 => array('Catégories', '/News/Category/Manage', 'staff_nwcanviewcategories'),
            3 => array('Abonnés', '/News/Subscriber/Manage', 'staff_nwcanviewsubscribers'),
            4 => array('Ajouter les actualités', ':UICreateWindow(\'/News/NewsItem/InsertDialog/\', \'newnews\', \'Insert News\', \'Loading..\', 600, 420, true);'),
        ),

        8 => array(
            0 => array('Gérer les utilisateurs', '/Base/User/Manage', 'staff_canviewusers'),
            1 => array('Gérer les organisations', '/Base/UserOrganization/Manage', 'staff_canviewuserorganizations'),
            2 => array('Recherche', ':UIDropDown(\'usersearchmenu\', event, \'linkmenu8_2\', \'linksdiv\'); LinkUserSearchForms();'),
            3 => array('Insérer groupes d\'utilisateurs', '/Base/User/Insert', 'staff_caninsertuser'),
            4 => array('Ajouter organisations', '/Base/UserOrganization/Insert', 'staff_caninsertuserorganization'),
            5 => array ('Import Users', '/Base/User/ImportCSV', 'staff_caninsertuser'),
        ),

        9 => array(
            0 => array('Gérer rapports', '/Reports/Report/Manage'),
            1 => array('Catégories', '/Reports/Category/Manage'),
            2 => array('Nouveau rapport', ':UICreateWindow(\'/Reports/Report/InsertDialog/\', \'newreport\', \'New Report\', \'Loading..\', 400, 280, true);'),
        ),
    );

    $_staffLinkContainer[2][1][15] = true;
    $_staffLinkContainer[2][5][15] = true;
    $_staffLinkContainer[8][2][15] = true;
    $_staffLinkContainer[3][4][15] = true;

    SWIFT::Set('stafflinks', $_staffLinkContainer);
}




return $__LANG;
