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
    'tickets'                   => 'Pedidos de suporte',
    'insertpriority'            => 'Inserir prioridade',
    'tabgeneral'                => 'Geral',
    'tabpermissions'            => 'Permissões',
    'tablanguages'              => 'Idiomas',
    'prioritytitle'             => 'Título da prioridade',
    'desc_prioritytitle'        => 'Por exemplo, <em>alta</em> ou <em>nível 1</em>.',
    'prioritytype'              => 'Tipo de prioridade',
    'desc_prioritytype'         => '<strong>Privado</strong> As prioridades não são visíveis a utilizadores finais, mas apenas a funcionários.<br /><strong>Público</strong> As prioridade são visíveis a ambos.',
    'forecolor'                 => 'Cor do primeiro plano',
    'desc_forecolor'            => 'Como opção, especifique uma cor do primeiro plano (texto) para a prioridade.',
    'bgcolor'                   => 'Cor de fundo',
    'desc_bgcolor'              => 'Como opção, especifique uma cor de fundo (sombreado) para a prioridade (certifique-se de que a cor do primeiro plano acima estará visível sobre a cor de fundo).',
    'displayorder'              => 'Ordem de apresentação',
    'desc_displayorder'         => 'As prioridades são listadas de acordo com a sua ordem de apresentação, da menor para a maior.',
    'displayiconpriority'       => 'Ícone',
    'desc_displayiconpriority'  => 'Carregue ou associe um ícone a esta prioridade. {$themepath} pode ser utilizado para indicar o diretório de imagens do suporte técnico, por exemplo: <em>{$themepath}icon_prioritymedium.gif</em>.',
    'uservisibilitycustom'      => 'Restringir prioridade a grupos de utilizadores específicos',
    'desc_uservisibilitycustom' => 'Se ativado, apenas os grupos de utilizadores abaixo poderão visualizar (e, se aplicável, selecionar) esta prioridade.',
    'usergroups'                => 'Grupos de utilizadores',
    'invalidticketpriority'     => 'Foi encontrado um problema (prioridade de pedido de suporte inválida - certifique-se de que a prioridade existe na base de dados)',
    'editpriority'              => 'Editar prioridade',
    'desc_editpriority'         => '',
    'managepriorities'          => 'Prioridades',
    'desc_managepriorities'     => '',
    'wineditticketpriority'     => 'Editar prioridade do pedido de suporte: %s',
    'titledelticketpriority'    => 'Prioridades do pedido de suporte eliminadas (%d)',
    'msgdelticketpriority'      => 'As seguintes prioridades de pedido de suporte foram eliminadas:',
    'titleticketpriorityinsert' => 'Prioridade de pedido de suporte %s criada',
    'msgticketpriorityinsert'   => 'A prioridade (%s) foi criada com sucesso.',
    'titleticketpriorityupdate' => 'Prioridade de pedido de suporte %s atualizada',
    'msgticketpriorityupdate'   => 'A prioridade (%s) foi atualizada com sucesso.',
    'titlemasterprioritydelete' => 'Não foi possível eliminar prioridades de pedido de suporte',
    'msgmasterprioritydelete'   => 'Não é possível eliminar as seguintes prioridades de pedido de suporte principal:',

    // Potentialy unused phrases in admin_ticketspriority.php
    'desc_insertpriority'       => '',
    'prioritylist'              => 'Priority List',
    'prioritydetails'           => 'Priority Details',
    'registrationrequired'      => 'Registration Required?',
    'desc_registrationrequired' => 'If enabled, only registered clients will be able to create Tickets with this Priority.',
);

return $__LANG;
