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
    'autoclose'                    => 'Fecho automático',
    'manage'                       => 'Gerir',
    'targetstatus'                 => 'Estado para o qual alterar',
    'tabgeneral'                   => 'Geral',

    'insertrule'                   => 'Inserir regra',
    'editrule'                     => 'Editar regra',

    'ruletitle'                    => 'Título da regra',
    'desc_ruletitle'               => 'Introduza um título para esta regra de fecho automático.',
    'targetticketstatus'           => 'Alterar estado para',
    'desc_targetticketstatus'      => 'Decorrido o tempo de inatividade (consulte abaixo), os pedidos de suporte correspondentes a esta regra serão definidos com este estado (este deve ser um estado <strong>resolvido</strong>).',
    'inactivitythreshold'          => 'Limiar de inatividade',
    'desc_inactivitythreshold'     => 'Se não foi efetuada qualquer atualização a um pedido de suporte neste número de horas, o pedido de suporte será considerado como inativo. Este é o primeiro passo para o fecho automático de um pedido de suporte.',
    'closurethreshold'             => 'Limiar de encerramento',
    'desc_closurethreshold'        => 'Se um pedido de suporte tiver sido marcado como inativo e não tiver sido recebida qualquer atualização neste número de horas, o pedido de suporte será automaticamente definido com o estado especificado acima.',
    'isenabled'                    => 'Regra ativada',
    'desc_isenabled'               => 'Alterne entre regra ativada ou desativada.',
    'sortorder'                    => 'Ordem de execução',
    'desc_sortorder'               => 'É possível criar várias regras de fecho automático. A ordem de execução determina que regras são executadas primeiro, desde a menor para a maior.',
    'sendpendingnotification'      => 'Enviar email de notificação de inatividade',
    'desc_sendpendingnotification' => 'O suporte técnico pode notificar o utilizador sobre o facto de o seu pedido de suporte ter sido marcado como inativo e, caso não seja recebida qualquer resposta, será fechado.',
    'sendfinalnotification'        => 'Enviar email final de notificação de pedido de suporte fechado',
    'desc_sendfinalnotification'   => 'O suporte técnico também pode notificar o utilizador sobre o facto de o seu pedido de suporte ter sido fechado.',
    'suppresssurveyemail'          => 'Suprimir email de inquérito ao consumidor',
    'desc_suppresssurveyemail'     => 'Se tiver ativado convites de inquérito de satisfação ao cliente quando um pedido de suporte é definido com o estado especificado acima, pode pretender impedir que o suporte técnico envie um convite de inquérito para pedidos de suporte automaticamente fechados.',

    'insertcriteria'               => 'Inserir critérios',


    'titleautocloseruledel'        => 'Regras de fecho automático eliminadas (%d)',
    'msgautocloseruledel'          => 'Foram eliminadas as seguintes regras de fecho automático:',
    'titleautocloseruleenable'     => 'Regras de fecho automático ativadas (%d)',
    'msgautocloseruleenable'       => 'Foram ativadas as seguintes regras de fecho automático:',
    'titleautocloseruledisable'    => 'Regras de fecho automático desativadas (%d)',
    'msgautocloseruledisable'      => 'Foram desativadas as seguintes regras de fecho automático:',
    'titleautocloseruleinsert'     => 'Regra de fecho automático criada',
    'msgautocloseruleinsert'       => 'A regra de fecho automático (%s) foi criada com sucesso.',
    'titleautocloseruleupdate'     => 'Regra de fecho automático atualizada',
    'msgautocloseruleupdate'       => 'A regra de fecho automático (%s) foi atualizada com sucesso.',

    'titlenocriteriaadded'         => 'Nenhum critério especificado',
    'msgnocriteriaadded'           => 'Necessita de especificar pelo menos um critério para criar uma regra de fecho automático (caso contrário, o suporte técnico não saberá quais os pedidos de suporte a fechar automaticamente).',

    /**
     * ---------------------------------------------
     * Rule Criterias
     * ---------------------------------------------
     */
    'notapplicable'                => '-- ND --',
    'articketstatusid'             => 'Estado',
    'desc_articketstatusid'        => '',
    'arpriorityid'                 => 'Prioridade',
    'desc_arpriorityid'            => '',
    'ardepartmentid'               => 'Departamento',
    'desc_ardepartmentid'          => '',
    'arownerstaffid'               => 'Proprietário',
    'desc_arownerstaffid'          => '',
    'aremailqueueid'               => 'Fila de email',
    'arusergroupid'                => 'Grupo de utilizadores',
    'desc_arusergroupid'           => '',
    'desc_aremailqueueid'          => '',
    'arflagtype'                   => 'Sinalizar',
    'desc_arflagtype'              => '',
    'arbayescategoryid'            => 'Categoria Bayesiana',
    'desc_arbayescategoryid'       => 'Pedidos de suporte que foram associados a uma categoria Bayesiana específica.',
    'arcreator'                    => 'Criador',
    'desc_arcreator'               => '',
    'creatorstaff'                 => 'Pessoal',
    'creatorclient'                => 'Utilizador',
    'arunassigned'                 => '-- Não atribuído --',
    'artemplategroupid'            => 'Grupo de modelos',
    'desc_artemplategroupid'       => '',
    'artickettypeid'               => 'Tipo',
    'desc_rtickettypeid'           => '',
);

return $__LANG;
