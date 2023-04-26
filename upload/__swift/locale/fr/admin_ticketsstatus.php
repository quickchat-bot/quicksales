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
    // ======= BEGIN v4 LOCALES =======
    'tickets'                               => 'Tickets',
    'tabgeneral'                            => 'Généralités',
    'tabpermissions'                        => 'Autorisations',
    'staffvisibilitycustom'                 => 'Limiter la modification des tickets à cet état',
    'desc_staffvisibilitycustom'            => 'Si activé, seules les équipes ci-dessous auront des tickets définis sur cet état.',
    'staffgroups'                           => 'Équipes d\'opérateurs',
    'triggersurvey'                         => 'Envoyer email de sondage de satisfaction',
    'desc_triggersurvey'                    => 'Quand un billet est changé à cet état, un email est envoyé à l\'utilisateur lui demandant de noter et de commenter le service.',
    'statustitle'                           => 'Nom de l\'État',
    'desc_statustitle'                      => 'Entrez un nom pour cet état.',
    'statuscolor'                           => 'Couleur de l\'état',
    'desc_statuscolor'                      => 'Nous vous recommandons le vert pour les états ouverts et des couleurs sombres pour les états résolus.',
    'statusbgcolor'                         => 'Couleur d\'arrière-plan d\'état',
    'desc_statusbgcolor'                    => 'Dans le tableau de bord du personnel, l\'ensemble de la barre de propriétés du ticket est de cette couleur (selon l\'état du ticket). Il est Impossible de ne pas le remarquer !',
    'displayorder'                          => 'Ordre d\'affichage',
    'desc_displayorder'                     => 'Les états des tickets sont affichés en fonction de leur ordre d\'affichage, du premier au dernier.',
    'displayiconstatus'                     => 'Icône',
    'desc_displayiconstatus'                => 'Télécharger ou créer un lien vers une icône pour cet état. {$themepath} peut être utilisé pour pointer vers le répertoire d\'images du helpdesk, par exemple : <em>{$themepath}icon_statusopen.gif</em>.',
    'statusdep'                             => 'Associer l\'état avec le service',
    'desc_statusdep'                        => 'Associez cet état avec un service spécifique. A défaut, l\'état sera disponible pour tous les services qui utilisent des tickets.',
    'statusalldep'                          => '--Sans lien--',
    'markasresolved'                        => 'Est-ce un état <em>résolu</em> de ticket ?',
    'desc_markasresolved'                   => 'Si les tickets correspondant à cet état sont résolus ou fermés (c\'est-à-dire réglés, résolus, finis), activez cette option, cela les exclu de la liste des tickets ouverts.',
    'displayinmainlist'                     => 'Afficher dans la liste principale des tickets',
    'desc_displayinmainlist'                => 'Les tickets correspondant à cet état sont répertoriés dans la liste principale de tickets.',
    'displaycount'                          => 'Afficher le compte de tickets dans l\'arborescence de navigation des tickets',
    'desc_displaycount'                     => 'Lorsqu\'activé, le décompte de tickets correspondant à cet état s\'affiche dans l\'arborescence de navigation de tickets du tableau de bord du personnel. Nous recommandons de désactiver ce paramètre les états résolus.',
    'statustype2'                           => 'Type d\'État',
    'desc_statustype2'                      => '<strong>Public</strong> Les états sont visibles partout et à vos utilisateurs finaux..<br /><strong>Private</strong> Les états sont uniquement visibles aux utilisateurs membres du personnel.',
    'resetduetime'                          => 'Supprimer la date limite de réponse des ticket quand ils passent à cet état',
    'desc_resetduetime'                     => 'Lorsque le ticket passe à cet état, la date limite de réponse est automatiquement supprimée.',
    'dispatchnotification'                  => 'Envoyer une notification à l\'utilisateur',
    'desc_dispatchnotification'             => 'Si activé, le système enverra une notification à l\'utilisateur/créateur chaque fois que qu\'un ticket passe à cet état.',
    'insertstatus'                          => 'Ajouter un état',
    'desc_insertstatus'                     => '',
    'invalidticketstatus'                   => 'Impossible de charger l\'état du ticket.',
    'managestatus'                          => 'État',
    'desc_managestatus'                     => '',
    'wineditticketstatus'                   => 'Modifier l\'état : %s',
    'editstatus'                            => 'Modifier l\'état',
    'desc_editstatus'                       => '',
    'taboptions'                            => 'Options',
    'titledelticketstatus'                  => 'État du ticket suppriméd (%d)',
    'msgdelticketstatus'                    => 'Les états suivants du ticket ont été supprimés :',
    'titleticketstatusinsert'               => 'L\'état de ticket %s a été créé',
    'msgticketstatusinsert'                 => 'L\'état du ticket %s a été correctement créé.',
    'titleticketstatusupdate'               => 'État du ticket %s mis à jour',
    'msgticketstatusupdate'                 => 'L\'état du ticket %s a été correctement mis à jour.',
    'titlemasterstatusdelete'               => 'Impossible de supprimer les états (%d) des tickets',
    'msgmasterstatusdelete'                 => 'Les statuts de ticket principal suivants n\'ont pas pu être supprimés :',
    'tablanguages'                          => 'Langues : traduction',
    'title_statusinuse'                     => 'État en cours d\'utilisation',
    'message_statusinuse_editassociation'   => 'Certains tickets correspondant à cet état appartiennent à des services liés. Vous devez modifier l\'état des tickets avant de mettre à jour le service lié.',
    'message_statusinuse_createassociation' => 'Certains tickets correspondant à cet état appartiennent à des services liés. Vous devez modifier l\'état des tickets avant de mettre à jour le service lié.',
    // ======= END v4 LOCALES =======
);


return $__LANG;
