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
    'notifications'                  => 'Notificações',
    'manage'                         => 'Gerir',
    'ruletitle'                      => 'Título da regra',
    'desc_ruletitle'                 => 'Introduza um título para a regra de notificação.',
    'isenabled'                      => 'Regra de notificação ativada',
    'desc_isenabled'                 => 'Alterne entre regra ativada ou desativada.',
    'ruletype'                       => 'Tipo',
    'desc_ruletype'                  => 'A regra de notificação pode ser associada a um dos seguintes itens.',
    'next'                           => 'Seguinte',
    'creator'                        => 'Criador',
    'wininsertnotification'          => 'Inserir notificação',

    'unassigned'                     => '-- Não atribuído --',

    'tabgeneral'                     => 'Geral',
    'tabactions'                     => 'Ações',
    'insertcriteria'                 => 'Inserir critérios',

    'na_email'                       => 'Email',
    'na_sms'                         => 'SMS',
    'na_pool'                        => 'Aplicação de ambiente de trabalho',
    'na_staff'                       => 'Conta própria do funcionário',
    'na_staffgroup'                  => 'Equipa',
    'na_department'                  => 'Departamento',
    'na_user'                        => 'Utilizador',
    'nactionemaildispatch'           => 'Enviar email ao proprietário do pedido de suporte...',
    'desc_nactionemaildispatch'      => '',
    'nadispatchemail'                => 'Enviar email para outros endereços de email',
    'desc_nadispatchemail'           => '',
    'nadispatchemailstaff'           => 'Enviar email para outro funcionário',
    'desc_nadispatchemailstaff'      => '',
    'nactionpooldispatch'            => 'Enviar notificação para ... do proprietário de pedido de suporte ativo',
    'desc_nactionpooldispatch'       => '',
    'nactionpoolcustomdispatch'      => 'Pessoal',
    'desc_nactionpoolcustomdispatch' => '',
    'notificationruledesc'           => 'Selecione um tipo de regra de notificação.',
    'titlenocriteria'                => 'Nenhum critério especificado',
    'msgnocriteria'                  => 'É necessário pelo menos um critério para criar uma regra de notificação.',
    'titlenotificationinsert'        => 'Regra de notificação (%s) criada',
    'msgnotificationinsert'          => 'A regra de notificação (%s) foi criada com sucesso.',
    'titlenotificationupdate'        => 'Regra de notificação (%s) atualizada',
    'msgnotificationupdate'          => 'A regra de notificação (%s) foi atualizada com sucesso.',
    'tabnotifyapp'                   => 'Notify App',
    'tabemail'                       => 'Email',
    'titleenablenotification'        => 'Regras de notificação ativadas (%d)',
    'msgenablenotification'          => 'As seguintes regras de notificação foram ativadas:<br />%s',
    'titledisablenotification'       => 'Regras de notificação desativadas (%d)',
    'msgdisablenotification'         => 'As seguintes regras de notificação foram desativadas:<br />%s',

    'emailprefix'                    => 'Prefixo do assunto de email',
    'desc_emailprefix'               => 'Especifique um prefixo de assunto de email para identificar facilmente os emails enviados por esta regra de notificação.',

    /**
     * ---------------------------------------------
     * RULE TYPES
     * ---------------------------------------------
     */
    'nrule_chat'                     => 'Conversação',
    'nrule_message'                  => 'Mensagem offline',
    'nrule_survey'                   => 'Inquérito da conversação',
    'nrule_ticket'                   => 'Pedido de suporte',
    'nrule_user'                     => 'Utilizador',

    /**
     * ---------------------------------------------
     * RULE CRITERIA: TICKETS
     * ---------------------------------------------
     */

    'ntickettype'                    => 'Tipo de pedido de suporte',
    'desc_ntickettype'               => '',
    'nbayesian'                      => 'Categoria Bayesiana',
    'desc_nbayesian'                 => 'Pedidos de suporte que foram associados a uma categoria Bayesiana específica.',
    'notapplicable'                  => '-- Indisponível --',
    'nticketstatus'                  => 'Estado do pedido de suporte',
    'desc_nticketstatus'             => '',
    'nticketpriority'                => 'Prioridade do pedido de suporte',
    'desc_nticketpriority'           => '',
    'nusergroup'                     => 'Grupo de utilizadores',
    'desc_nusergroup'                => 'Pesquisa pedidos de suporte cujo destinatário pertence a determinado grupo de utilizadores.',
    'ndepartment'                    => 'Departamento do pedido de suporte',
    'desc_ndepartment'               => 'Pedidos de suporte que pertencem a um departamento.',
    'nowner'                         => 'Proprietário do pedido de suporte',
    'desc_nowner'                    => 'Pedidos de suporte atribuídos a determinado funcionário.',
    'nunassigned'                    => '-- Não atribuído --',
    'nactivestaff'                   => '-- Pessoal ativo --',
    'nemailqueue'                    => 'Fila de email',
    'desc_nemailqueue'               => 'Pedidos de suporte que foram criados ou respondidos por email através de determinada fila de email.',
    'nflagtype'                      => 'Sinalizador de pedido de suporte',
    'desc_nflagtype'                 => '',
    'ncreator'                       => 'Pedido de suporte criado por',
    'desc_ncreator'                  => '',
    'creatorstaff'                   => 'Pessoal',
    'creatorclient'                  => 'Utilizador',
    'nslaplan'                       => 'Plano de SLA',
    'desc_nslaplan'                  => 'Pedidos de suporte que estão atualmente atribuídos a determinado plano de SLA.',
    'ndaterange'                     => 'Data de criação do pedido de suporte <range>',
    'desc_ndaterange'                => 'Pedidos de suporte que foram criados neste intervalo de tempo.',
    'nlastactivityrange'             => 'Última atualização do pedido de suporte <range>',
    'desc_nlastactivityrange'        => 'Pedidos de suporte que foram atualizados (isto é, obtiveram resposta ou qualquer outro evento de atualização) neste intervalo de tempo.',
    'nlaststaffreplyrange'           => 'Última resposta do pessoal <range>',
    'desc_nlaststaffreplyrange'      => 'Pedidos de suporte que receberam uma resposta de um funcionário neste intervalo de tempo.',
    'nlastuserreplyrange'            => 'Última resposta do utilizador <range>',
    'desc_nlastuserreplyrange'       => 'Pedidos de suporte que receberam uma resposta de um utilizador neste intervalo de tempo.',
    'nduerange'                      => 'Prazo de resposta <range>',
    'desc_nduerange'                 => 'Pedidos de suporte com um prazo de resposta neste intervalo de tempo.',
    'nisoverdue'                     => 'Em atraso: resposta em atraso',
    'desc_nisoverdue'                => 'Pedidos de suporte em atraso (não foram respondidos antes do prazo de resposta).',
    'nresolutionduerange'            => 'Prazo de resolução <range>',
    'desc_nresolutionduerange'       => 'Pedidos de suporte com um prazo de resolução neste intervalo de tempo.',
    'nisresolutionoverdue'           => 'Em atraso: resolução em atraso',
    'desc_nisresolutionoverdue'      => 'Pedidos de suporte em atraso (não foram resolvidos antes do prazo de resolução).',
    'ntemplategroup'                 => 'Grupo de modelos',
    'desc_ntemplategroup'            => 'Pedidos de suporte que pertencem a determinado grupos de modelos.',
    'ntimeworked'                    => 'Tempo trabalhado (em minutos)',
    'desc_ntimeworked'               => 'Pedidos de suporte que coincidem com este valor de <strong>tempo trabalhado</strong> (como parte das entradas de faturação e controlo de tempo do pedido de suporte).',
    'ntotalreplies'                  => 'Total de respostas',
    'desc_ntotalreplies'             => 'Pedidos de suporte com todas estas respostas.',
    'npendingfollowups'              => 'Seguimentos pendentes',
    'desc_npendingfollowups'         => 'Pedidos de suporte com seguimentos de pedido de suporte agendados.',
    'nipaddress'                     => 'Endereço IP',
    'desc_nipaddress'                => 'Se uma resposta ao pedido de suporte tiver sido dada pelo <strong>centro de suporte</strong>, o suporte técnico pode ter registado um endereço IP para o utilizador.',
    'nisemailed'                     => 'Criado por email',
    'desc_nisemailed'                => 'Pedidos de suporte que foram criados por email.',
    'nisedited'                      => 'Foi editado',
    'desc_nisedited'                 => 'Pedidos de suporte que foram editados.',
    'nhasnotes'                      => 'Tem notas',
    'desc_nhasnotes'                 => 'Pedidos de suporte com notas de pedido de suporte.',
    'nhasattachments'                => 'Tem anexos',
    'desc_nhasattachments'           => 'Pedidos de suporte com anexos de ficheiro.',
    'nisescalated'                   => 'Foi escalonado',
    'desc_nisescalated'              => 'Pedidos de suporte que foram escalonados por pelo menos uma regra de escalonamento.',
    'nhasdraft'                      => 'Tem um rascunho',
    'desc_nhasdraft'                 => 'Pedidos de suporte com um rascunho de resposta guardado.',
    'nhasbilling'                    => 'Tem entradas de faturação e controlo de tempo',
    'desc_nhasbilling'               => 'Pedidos de suporte com entradas de faturação e controlo de tempo.',
    'nisphonecall'                   => 'É um pedido de suporte telefónico',
    'desc_nisphonecall'              => 'Pedidos de suporte que foram marcados como pedido de suporte do tipo <em>chamada telefónica</em>.',
    'nislabeled'                     => 'Está marcado',
    'desc_nislabeled'                => 'Pedidos de suporte que foram marcados.',

    'nticketevent'                   => 'Evento',
    'desc_nticketevent'              => '',
    'tevent_newticket'               => 'Novo pedido de suporte criado',
    'tevent_newticketnotes'          => 'Nova nota de pedido de suporte adicionada',
    'tevent_newclientreply'          => 'Nova resposta do utilizador final',
    'tevent_newclientsurvey'         => 'Nova submissão de inquérito',
    'tevent_newstaffreply'           => 'Nova resposta do pessoal',
    'tevent_ticketassigned'          => 'Pedido de suporte atribuído',
    'tevent_newuser'                 => 'Novo registo de utilizador',

    'nuserevent'                     => 'Evento',
    'desc_nuserevent'                => 'Esta regra será acionada neste evento.',
);

return $__LANG;
