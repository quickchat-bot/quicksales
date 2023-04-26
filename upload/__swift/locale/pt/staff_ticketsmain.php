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
    'tickets'                   => 'Pedidos de suporte',
    'overdue'                   => 'Vencido',
    'unassigned'                => '-- Não atribuído --',
    'unassigned2'               => 'Não atribuído',
    'menuviews'                 => 'Vista: %s',
    'merge'                     => 'Unir',
    'trash'                     => 'Lixo',
    'spam'                      => 'Spam',
    'watch'                     => 'Monitorização',
    /*
    * BUG FIX - Saloni Dhall
    *
    * SWIFT-3091 Staff CP: laguage display issue (only button reply)
    *
    * Comments: None
    */
    'reply'                     => 'Responder',
    'qticketpost'               => 'Citar esta publicação',

    // Tree
    'treeinbox'                 => 'Caixa de entrada',
    'treetrash'                 => 'Lixo',
    'treewatched'               => 'Monitorizado',
    'treeflagged'               => 'Sinalizada',
    'treemytickets'             => 'Os meus pedidos de suporte',
    'treeunassigned'            => 'Não atribuído',

    // Grid Titles
    'f_ticketid'                => 'ID do pedido de suporte',
    'f_subject'                 => 'Assunto',
    'f_queue'                   => 'Fila de email',
    'f_department'              => 'Departamento',
    'f_ticketstatus'            => 'Estado',
    'f_duedate'                 => 'Resposta devida',
    'f_lastactivity'            => 'Última atividade',
    'f_date'                    => 'Data',
    'f_owner'                   => 'Proprietário',
    'f_priority'                => 'Prioridade',
    'f_lastreplier'             => 'Última pessoa a responder',
    'f_fullname'                => 'Nome',
    'f_timeworked'              => 'Tempo trabalhado',
    'f_email'                   => 'Email',
    'f_totalreplies'            => 'Respostas',
    'f_assignstatus'            => 'Atribuído',
    'f_flagtype'                => 'Sinalizar',
    'f_laststaffreply'          => 'Atualização de pessoal',
    'f_lastuserreply'           => 'Atualização de utilizador',
    'f_tgroup'                  => 'Grupos de modelos',
    'f_slaplan'                 => 'Plano de SLA',
    'f_usergroup'               => 'Grupo de utilizadores',
    'f_userorganization'        => 'Organização',
    'f_escalationrule'          => 'Regra de escalonamento',
    'f_escalatedtime'           => 'Hora de escalonamento',
    'f_resolutiondue'           => 'Resolução devida',
    'f_type'                    => 'Tipo',
    'f_typeicon'                => 'Tipo (ícone)',

    // Creation Modes
    'cm_api'                    => 'API',
    'cm_email'                  => 'Email',
    'cm_sitebadge'              => 'Destaque do site',
    'cm_staffcp'                => 'CP do pessoal',
    'cm_supportcenter'          => 'Centro de suporte',

    // Ticket listing icon description tags
    'alt_hasattachments'        => 'Tem anexos',
    'alt_isescalated'           => 'Este pedido de suporte está em atraso e foi escalonado',
    'alt_raisedbyemail'         => 'Elevado por email',
    'alt_linkedticket'          => 'Este pedido de suporte está associado a outro',
    'alt_followupset'           => 'Tem de ser definido um item de seguimento para este pedido de suporte',
    'alt_assignedotyou'         => 'Atribuído a si',
    'alt_watchingticket'        => 'Está a monitorizar este pedido de suporte',
    'alt_ticketlocked'          => 'Este pedido de suporte está a ser visto por outro funcionário',
    'alt_ticketphonetype'       => 'Este pedido de suporte foi marcado como um pedido de suporte do tipo telefónico',
    'alt_ticketunread'          => 'Este pedido de suporte foi atualizado desde a sua última visita',
    'alt_tickethastimetracking' => 'Este pedido de suporte tem uma entrada de controlo de tempo',
    'alt_tickethasnote'         => 'Este pedido de suporte tem uma notas de pedido de suporte ou mais',
    'alt_tickethasbilling'      => 'Este pedido de suporte tem um controlo de tempo e uma entrada de faturação',

    // Potentialy unused phrases in staff_ticketsmain.php
    'filtertickets'             => 'Filter Tickets',

    // Ticket Copy/Split
    'closeold'                  => 'Close old ticket',
    'closeold_d'                => 'Close the old ticket (split from) after splitting this ticket.',
    'splitat'                   => 'Split at reply',
    'splitat_d'                 => 'This reply, and all replies made after it, will belong to the new ticket.',
    'split_into'                => 'Split into ticket',
    'split_from'                => 'Split from ticket',
    'duplicateat'               => 'Duplicate at reply',
    'duplicateat_d'             => 'This reply, and all replies made after it, will be duplicated to the new ticket.',
    'duplicate_into'            => 'Duplicate into ticket',
    'duplicate_from'            => 'Duplicate from ticket',
    'ticketsplitter'            => 'Ticket Splitter',
    'no_such_post'              => 'Couldn\'t identify the post at which to split.',
    'oldticket'                 => 'Old Ticket',
    'newticket'                 => 'New Ticket',
    'split'                     => 'Split',
    'duplicate'                 => 'Duplicate',
    'splitticket'               => 'Split Ticket',
    'duplicateticket'           => 'Duplicate Ticket'
);


return $__LANG;
