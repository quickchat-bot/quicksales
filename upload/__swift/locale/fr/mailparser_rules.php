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
    // ======= BEGIN v4 LOCALES =======
    'isenabled'                 => 'Règle d\'analyseur d\'email activée',
    'desc_isenabled'            => 'Les règles d\'email seront ignorées si désactivé.',
    'insertcriteria'            => 'Ajouter un critère',
    'tabactions'                => 'Actions',
    'titlenoaction'             => 'Aucune action spécifiée',
    'msgnoaction'               => 'Vous devez spécifier au moins une action que la règle d\'email déclenchera (s\'il y a correspondance).',
    'if'                        => 'Si',
    'titleinsertrule'           => 'Règle d\'email (%s) créée',
    'msginsertrule'             => 'Règle d\'email (%s) créée avec succès.',
    'titleupdaterule'           => 'Règle d\'email (%s) mise à jour',
    'msgupdaterule'             => 'Règle d\'analyseur d\'email « %s » mise à jour avec succès.',
    'titledelprules'            => 'Règles d\'email supprimées (%d)',
    'msgdelprules'              => 'Les règles d\'email suivantes ont été supprimées :',
    'titleenableprules'         => 'Règles d\'email activées (%d)',
    'msgenableprules'           => 'Les règles d\'email suivantes ont été activées :',
    'titledisableprules'        => 'Règles d\'email désactivées (%d)',
    'msgdisableprules'          => 'Les règles d\'email suivantes ont été désactivées :',
    // ======= END v4 LOCALES =======

    'mailparser'                => 'Analyseur d\'email',

    'desc_parserrules'          => '',

    // Operators
    'opcontains'                => 'Contient',
    'opnotcontains'             => 'Ne contient pas',
    'opequal'                   => 'Égal',
    'opnotequal'                => 'Non égal à',
    'opgreater'                 => 'Supérieur à',
    'opless'                    => 'Inférieur à',
    'opregexp'                  => 'Expression régulière',
    'strue'                     => 'Vrai',
    'sfalse'                    => 'Faux',

    // Criteria
    'psendername'               => 'Nom de l\'expéditeur',
    'desc_psendername'          => 'Le nom de l\'expéditeur est capturé par l\'analyseur depuis l\'en-tête « de : » de l\'email.',

    'psenderemail'              => 'Email de l\'expéditeur',
    'desc_psenderemail'         => 'L\'email de l\'expéditeur est capturé par l\'analyseur depuis l\'en-tête « de : » de l\'email. Exemple :  <em>de : <span class="tabletitle">Nom de l\'expéditeur</span> <nomdel\'expéditeur@domaine.com></em>',

    'pdestinationname'          => 'Nom de destination (destinataire)',
    'desc_pdestinationname'     => 'Le nom de destination est capturé à partir de l\'en-tête « à : » de l\'e-mail. Le nom de destination est généralement le nom de l\'utilisateur du personnel qui a répondu au ticket (sauf si autrement configuré).',

    'pdestinationemail'         => 'Adresse email de destination (destinataire)',
    'desc_pdestinationemail'    => 'L\'adresse email de destination est capturée à partir de l\'en-tête « à : » de l\'email. C\'est généralement l\'adresse de la file d\'attente à laquelle est envoyée la réponse.',

    'preplytoname'              => 'Répondre à nom',
    'desc_preplytoname'         => 'Le  nom de réponse est capturé par l\'analyseur depuis l\'en-tête « Répondre à : » de l\'email.',

    'preplytoemail'             => 'Adresse email de réponse',
    'desc_preplytoemail'        => 'L\'adresse email de réponse est capturée à partir de l\'en-tête « Répondre à : » de l\'email.',

    'psubject'                  => 'Objet',
    'desc_psubject'             => 'Recherche une correspondance pour l\'objet de l\'email.',

    'precipients'               => 'Destinataires',
    'desc_precipients'          => 'Recherche des correspondances pour les destinataires de l\'email. L\'email entrant peut avoir été envoyé à plusieurs destinataires, inclus dans les en-têtes  « à : » et « CC : » de l\'email.',

    'pbody'                     => 'Corps',
    'desc_pbody'                => 'Recherche des correspondances pour ce qui reste du corps de l\'email après que l\'HTML  et les lignes de ruptures aient été supprimés (sauf si configuré autrement).',

    'pbodysize'                 => 'Taille du corps',
    'desc_pbodysize'            => 'La taille totale du contenu du corps de l\'email en octets.',

    'ptextbody'                 => 'Corps en texte brut',
    'desc_ptextbody'            => 'Un email peut avoir deux types de contenu ; <em>texte brut</em>, <em>HTML</em> ou <em>les deux</em>. Cette règle recherche dans la version en <em>texte brut</em> du corps seulement. Notez que tous les e-mails ne sont pas envoyés dans <em>les deux</em> formats.',

    'phtmlbody'                 => 'Corps HTML',
    'desc_phtmlbody'            => 'Un email peut avoir deux types de contenu ; <em>texte brut</em>, <em>HTML</em> ou <em>les deux</em>. Cette règle recherche dans la version en  <em>HTML</em> du corps seulement. Notez que tous les e-mails ne sont pas envoyés dans <em>les deux</em> formats.',

    'ptextbodysize'             => 'Taille du corps de texte',
    'desc_ptextbodysize'        => 'Un email peut avoir deux types de contenu ; <em>texte brut</em>, <em>HTML</em> ou <em>les deux</em>. Cette règle compare la taille totale du contenu du corps en <em>texte brut</em> de l\'email en octets.',

    'phtmlbodysize'             => 'Taille du corps HTML',
    'desc_phtmlbodysize'        => 'Un email peut avoir deux types de contenu ; <em>texte brut</em>, <em>HTML</em> ou <em>les deux</em>. Cette règle compare la taille totale du contenu du corps en <em>HTML</em> de l\'email en octets.',

    'pattachmentname'           => 'Noms des pièces jointes',
    'desc_pattachmentname'      => 'Recherche les correspondances pour les pièces jointes qui ont été acceptées dans le helpdesk (tel que défini dans le tableau de bord admin).',

    'pattachmentsize'           => 'Taille des pièces jointes',
    'desc_pattachmentsize'      => 'Recherche des correspondances pour la taille des pièces jointes qui ont été acceptées dans le helpdesk (tel que défini dans le tableau de bord admin).',

    'ptotalattachmentsize'      => 'Taille totale de la pièces jointe',
    'desc_ptotalattachmentsize' => 'Recherche des correspondances pour la <em>taille totale</em> (en octets) des pièces jointes qui ont été acceptés dans le helpdesk (tel que défini dans le tableau de bord admin).',

    'pisreply'                  => 'Est une réponse de l\'utilisateur',
    'desc_pisreply'             => 'Si le courrier entrant est une réponse à un un ticket existant provenant d\'un utilisateur final, il correspond à cette règle.',

    'pisthirdparty'             => 'Est une réponse d\'un destinataire tiers',
    'desc_pisthirdparty'        => 'Si le courrier entrant est une réponse à un ticket existant provenant d\'une adresse qui a été ajoutée au ticket comme destinataire tiers,  il correspond à cette règle.',

    'pfloodprotection'          => 'Protection contre les inondations déclenchée',
    'desc_pfloodprotection'     => 'Cette règle correspond si la protection contre l\'inondation du helpdesk a été déclenchée.',

    'pisstaffreply'             => 'Est une réponse du personnel',
    'desc_pisstaffreply'        => 'Si le courrier entrant est une réponse à un ticket existant provenant de l\'adresse email d\'un utilisateur du personnel, il correspond à cette règle.',

    'pticketstatus'             => 'État du ticket (post analyse)',
    'desc_pticketstatus'        => 'Si le courrier entrant est une réponse à un ticket existant, l\'état du ticket va correspondre à cette règle.',

    'pticketemailqueue'         => 'File d\'attente',
    'desc_pticketemailqueue'    => 'Si le courrier entrant est récupéré depuis la file d\'attente spécifiée, cela correspond à cette règle.',

    'ptickettype'               => 'Type de ticket (après analyse)',
    'desc_ptickettype'          => 'Si le courrier entrant est une réponse à un ticket existant, le type du ticket correspond à cette règle.',

    'pticketpriority'           => 'Priorité de ticket (après analyse)',
    'desc_pticketpriority'      => 'Si le courrier entrant est une réponse à un ticket existant, le priorité du ticket correspond à cette règle.',

    'pticketusergroup'          => 'Groupe d\'utilisateurs de tickets (après analyse)',
    'desc_pticketusergroup'     => 'Si le courrier entrant est une réponse à un ticket existant, le groupe d\'utilisateurs de ticket correspond à cette règle.',

    'pticketdepartment'         => 'Service de tickets (après analyse)',
    'desc_pticketdepartment'    => 'Si le courrier entrant est une réponse à un ticket existant, le service du ticket correspond à cette règle.',

    'pticketowner'              => 'Propriétaire du ticket (après analyse)',
    'desc_pticketowner'         => 'Si le courrier entrant est une réponse à un ticket existant, le propriétaire du ticket correspond à cette règle.',
    'prunassigned'              => '-- Non assigné --',

    'pticketflagtype'           => 'Indicateur de ticket (après analyse)',
    'desc_pticketflagtype'      => 'Si le courrier entrant est une réponse à un ticket existant, le drapeau du ticket correspond à cette règle.',

    'pbayescategory'            => 'Catégorie Bayesienne',
    'desc_pbayescategory'       => 'Le helpdesk fait passer tous les emails à travers le filtre bayésien et assigne assigne à chaque ticket la catégorie bayésienne correspondant au score le plus élevé. Vous pouvez utiliser cette fonctionnalité et les catégories bayésiennes pour filtrer les pourriels ou pour enseigner au système à router automatiquement les tickets sur la base de leur contenu.',

    // Insert Rule
    'insertrule'                => 'Ajouter une règle',
    'iruletype'                 => 'Type de règle d\'email : ',
    'ipreparse'                 => 'Avant que l\'email ne soit traité (analyse préalable)',
    'ipostparse'                => 'Après traitement de l\'email (post analyse)',

    'paignore'                  => 'Ignorer l\'email',
    'desc_paignore'             => 'L\'email sera ignoré par le helpdesk et ne sera pas traité pour produire un ticket ou une réponse.',
    'panoautoresp'              => 'Ne pas envoyer de réponse automatique',
    'desc_panoautoresp'         => 'Une réponse automatique confirmant la réception d\'un nouvel email (en tant que nouveau ticket ou réponse) ne sera pas envoyé.',
    'panoalerts'                => 'Ne pas appliquer les règles de notification',
    'desc_panoalerts'           => 'Les règles de notification de helpdesk ne seront pas traitées, si activé.',
    'pnochange'                 => '-- Aucun changement --',
    'pcstaff'                   => 'Affecter un ticket',
    'desc_pcstaff'              => 'Le ticket (qu\'il s\'agisse d\'un nouveau ticket ou d\'une réponse à un ticket existant) sera attribué à cet utilisateur du personnel.',
    'pcstatus'                  => 'Modifier l\'état du ticket',
    'desc_pcstatus'             => 'Ce nouvel état sera attribué au ticket.',
    'pcpriority'                => 'Modifier la priorité du ticket',
    'desc_pcpriority'           => 'Cette nouvelle priorité sera attribuée au ticket.',
    'pcdepartment'              => 'Déplacer le ticket vers le service',
    'desc_pcdepartment'         => 'Le ticket sera déplacé vers ce service.',
    'pcslaplan'                 => 'Modifier le plan de SLA du ticket',
    'desc_pcslaplan'            => 'Ce plan de SLA sera assigné au ticket.',
    'pcmovetotrash'             => 'Déplacer vers la corbeille',
    'desc_pcmovetotrash'        => 'Le ticket sera déplacé vers la corbeille',
    'pcflag'                    => 'Signaler le ticket',
    'desc_pcflag'               => 'Le ticket sera signalé avec cette couleur.',
    'paddnotes'                 => 'Ajouter une remarque au ticket',
    'desc_paddnotes'            => 'Une remarque sera ajoutée au ticket.',
    'pcforward'                 => 'Transférer l\'email',
    'desc_pcforward'            => 'L\'email sera transféré vers cette adresse.',
    'preply'                    => 'Répondre à l\'email',
    'desc_preply'               => 'L\'email recevra automatique ce message en réponse.',
    'panoticket'                => 'Ne pas traiter comme une réponse de ticket',
    'desc_panoticket'           => 'Même si l\'email est une réponse à un ticket existant, l\'email ne sera pas ajouté au ticket existant et un nouveau ticket sera créé.',
    'pctickettype'              => 'Modifier le type de ticket',
    'desc_pctickettype'         => 'Le type du ticket changera à celui spécifié ici.',
    'pcaddtags'                 => 'Ajouter des balises au ticket',
    'desc_pcaddtags'            => 'Ces balises seront ajoutées au ticket.',
    'pcremovetags'              => 'Supprimer les balises du ticket',
    'desc_pcremovetags'         => 'Si ces balises sont associées au ticket, elles seront supprimées.',
    'pcprivate'                 => 'Faire que la réponse au ticket soit privée',
    'desc_pcprivate'            => 'La réponse à ce ticket sera marquée comme privé et uniquement visible aux autres utilisateurs du personnel (ceci s\'applique aux réponses du personnel au ticket uniquement).',

    // Edit Rules
    'editrule'                  => 'Modifier la règle',
    'ptitle'                    => 'Titre de règle d\'email',
    'desc_ptitle'               => 'Par exemple, <em>Reçus de PayPal</em>.',
    'pstop'                     => 'Arrêter le traitement d\'autres règles',
    'desc_pstop'                => 'Si cette règle correspond en premier, aucune autre règle d\'email ne sera traitée après elle.',

    // Manage Rules
    'managerules'               => 'Règles',
    'ruletitle'                 => 'Titre de la règle',
    'sortorder'                 => 'Ordre d\'exécution',
    'desc_sortorder'            => 'Il est possible que des correspondances existent pour plusieurs règles d\'email pour un seul email. Le helpdesk traitera les règles selon leur numéro d\'ordre d\'exécution, du premier au dernier.',
    'ruletype'                  => 'Type de règle',
    'creationdate'              => 'Créé',

    // Potentialy unused phrases in mailparser_rules.php
    'smatchtype'                => 'Match Type',
    'matchtype'                 => 'Criteria Options',
    'desc_matchtype'            => 'Select the grouping method for the criteria fields.',
    'smatchall'                 => 'Match <b>All</b> Criteria (AND)',
    'smatchany'                 => 'Match <b>Any</b> Criteria (OR)',
    'criteria'                  => 'Criteria',
    'newcriteria'               => 'New Criteria',
    'ruleinsertconfirm'         => 'Parser rule "%s" inserted successfully',
    'ruleupdateconfirm'         => 'Parser rule "%s" updated successfully',
    'invalidrule'               => 'ERROR: Invalid email parser rule',
    'ruledelconfirm'            => 'Parser rule deleted successfully',
    'rulelist'                  => 'Rule List',
    'notapplicable'             => '-- NA --',

);

return $__LANG;
