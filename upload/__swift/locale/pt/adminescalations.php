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
    // ======= BEGIN V4 LOCALES =======
    'escalations'                 => 'Escalonamentos',
    'insertrule'                  => 'Inserir regra',
    'ruletitle'                   => 'Título da regra',
    'desc_ruletitle'              => 'Introduza um título para a regra de escalonamento, por exemplo: <em>pedidos de suporte vencidos</em>.',
    'escalationruledetails'       => 'Detalhes da regra de escalonamento',
    'nochange'                    => '-- Sem alterações --',
    'escalationplan'              => 'Plano de SLA',
    'desc_escalationplan'         => 'Esta regra de escalonamento será acionada se um pedido de suporte for vencido de acordo com o plano de SLA aqui especificado.',
    'escalationaction'            => 'Ações',
    'escalationstaff'             => 'Atribuir pedido de suporte a um funcionário',
    'desc_escalationstaff'        => '',
    'escalationpriority'          => 'Alterar prioridade do pedido de suporte',
    'desc_escalationpriority'     => '',
    'escalationticketstatus'      => 'Alterar estado do pedido de suporte',
    'desc_escalationticketstatus' => '',
    'escalationdepartment'        => 'Mover pedido de suporte para um departamento',
    'desc_escalationdepartment'   => '',
    'escalationslaplan'           => 'Alterar o plano de SLA do pedido de suporte',
    'desc_escalationslaplan'      => '',
    'escalationflagtype'          => 'Sinalizar o pedido de suporte',
    'desc_escalationflagtype'     => '',
    'noplanavailable'             => '-- Indisponível --',
    'sendnotification'            => 'Enviar notificação de email ao utilizador',
    'desc_sendnotification'       => '',
    'titlenoplanavail'            => 'Nenhum plano de SLA encontrado',
    'msgnoplanavail'              => 'É necessário haver pelo menos um plano de SLA antes de poder criar uma regra de escalonamento, pois uma regra de escalonamento determina o que acontece a um pedido de suporte que viole um plano de SLA.',
    'creationdate'                => 'Data de criação',
    'wineditescalationrule'       => 'Editar regra de escalonamento: %s',
    'invalidescalationrule'       => 'Foi encontrado um problema (regra de escalonamento inválida - certifique-se de que a regra existe na base de dados)',
    'editrule'                    => 'Editar regra',
    'desc_editrule'               => '',
    'titledelescalationrule'      => 'Regras de escalonamento eliminadas (%d)',
    'msgdelescalationrule'        => 'As seguintes regras de escalonamento foram eliminadas:',
    'titleinsertescalationrule'   => 'Regra de escalonamento criada',
    'msginsertescalationrule'     => 'A regra de escalonamento (%s) foi criada com sucesso.',
    'titleupdateescalationrule'   => 'Regra de escalonamento atualizada',
    'msgupdateescalationrule'     => 'A regra de escalonamento (%s) foi atualizada com sucesso.',
    'manageescalations'           => 'Gerir',
    'desc_manageescalations'      => '',
    'tabnotifications'            => 'Notificações',
    'insertnotification'          => 'Inserir notificação',
    'escalationtickettype'        => 'Alterar tipo de pedido de suporte',
    'desc_escalationtickettype'   => '',
    'escalationaddtags'           => 'Assinalar o pedido de suporte',
    'desc_escalationaddtags'      => '',
    'escalationremovetags'        => 'Remover tags do pedido de suporte (se definido)',
    'desc_escalationremovetags'   => '',
    'eruletype'                   => 'Tipo de escalonamento',
    'desc_eruletype'              => 'Esta regra de escalonamento aciona quando um pedido de suporte fica em atraso de acordo com o plano de SLA especificado acima.<br /><br /><strong>Resposta em atraso</strong> Esta regra de escalonamento aciona quando termina o prazo de resposta do pedido de suporte.<br /><strong>Resolução em atraso</strong> Aciona quando o prazo de resolução termina.<br /><strong>Ambas</strong> Aciona quando o prazo de resposta ou de resolução termina.',
    'eruletype_due'               => 'Resposta em atraso',
    'eruletype_resolutiondue'     => 'Resolução em atraso',
    'eruletype_both'              => 'Resposta ou resolução em atraso',
    // ======= END V4 LOCALES =======
);

return $__LANG;
