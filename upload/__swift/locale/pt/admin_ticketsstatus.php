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
 * @license        http://www.opencart.com.vn/license
 * @link           http://www.opencart.com.vn
 *
 * ###############################################
 */

$__LANG = array(
    // ======= BEGIN v4 LOCALES =======
    'tickets'                               => 'Pedidos de suporte',
    'tabgeneral'                            => 'Geral',
    'tabpermissions'                        => 'Permissões',
    'staffvisibilitycustom'                 => 'Restringir alteração de pedidos de suporte para este estado',
    'desc_staffvisibilitycustom'            => 'Se ativado, apenas as equipas abaixo definirão pedidos de suporte para este estado.',
    'staffgroups'                           => 'Equipas',
    'triggersurvey'                         => 'Enviar email de inquérito de satisfação',
    'desc_triggersurvey'                    => 'Quando um pedido de suporte é alterado para este estado, é enviado um email ao utilizador solicitando a sua classificação e feedback.',
    'statustitle'                           => 'Nome do estado',
    'desc_statustitle'                      => 'Introduza um nome para este estado.',
    'statuscolor'                           => 'Cor do estado',
    'desc_statuscolor'                      => 'Recomendamos verde para estados abertos e cores escuras para estados resolvidos.',
    'statusbgcolor'                         => 'Cor de fundo do estado',
    'desc_statusbgcolor'                    => 'No painel de controlo do pessoal, toda a barra de propriedades do pedido de suporte está preenchida nesta cor (dependendo do estado do pedido de suporte). É impossível não o ver!',
    'displayorder'                          => 'Ordem de apresentação',
    'desc_displayorder'                     => 'Os estados do pedido de suporte são apresentados de acordo com a sua ordem de apresentação, do menor para o maior.',
    'displayiconstatus'                     => 'Ícone',
    'desc_displayiconstatus'                => 'Carregue ou associe um ícone a este estado. {$themepath} pode ser utilizado para indicar o diretório de imagens do suporte técnico, por exemplo: <em>{$themepath}icon_statusopen.gif</em>.',
    'statusdep'                             => 'Associar estado ao departamento',
    'desc_statusdep'                        => 'Associe este estado a um departamento específico. Caso não o faça, então, o estado será disponível para todos os departamentos de pedidos de suporte.',
    'statusalldep'                          => '-- Não associado --',
    'markasresolved'                        => 'Este é um estado de pedido de suporte <em>resolvido</em>?',
    'desc_markasresolved'                   => 'Se os pedidos de suporte definidos com este estado estiverem resolvidos ou fechados (isto é, corrigidos, resolvidos, concluídos), ative esta opção. Tal ocultá-los-á das listas de pedidos de suporte abertos.',
    'displayinmainlist'                     => 'Apresentar na listagem principal de pedidos de suporte',
    'desc_displayinmainlist'                => 'Os pedidos de suporte definidos com este estado serão listados na listagem principal de pedidos de suporte.',
    'displaycount'                          => 'Apresentar contagem de pedidos de suporte na árvore de navegação de pedidos de suporte',
    'desc_displaycount'                     => 'Se ativado, a contagem dos pedidos de suporte definidos com este estado será apresentada na árvore de navegação de pedidos de suporte do painel de controlo do pessoal. Recomendamos que desative esta definição para quaisquer estados resolvidos.',
    'statustype2'                           => 'Tipo de estado',
    'desc_statustype2'                      => '<strong>Público</strong> Os estados são visíveis em qualquer lugar e aos seus utilizadores finais.<br /><strong>Privado</strong> Os estados só são visíveis aos seus funcionários.',
    'resetduetime'                          => 'Apagar prazo de resposta ao pedido de suporte aquando da definição para este estado',
    'desc_resetduetime'                     => 'Ao alterar os pedidos de suporte para este estado, o tempo para conclusão do pedido de suporte será automaticamente apagado.',
    'dispatchnotification'                  => 'Emitir notificação para o utilizador',
    'desc_dispatchnotification'             => 'Se ativado, o sistema emite uma notificação ao utilizador/criador sempre que este estado ficar ativo para um pedido de suporte.',
    'insertstatus'                          => 'Inserir estado',
    'desc_insertstatus'                     => '',
    'invalidticketstatus'                   => 'Não foi possível carregar o estado do pedido de suporte.',
    'managestatus'                          => 'Estados',
    'desc_managestatus'                     => '',
    'wineditticketstatus'                   => 'Editar estado: %s',
    'editstatus'                            => 'Editar estado',
    'desc_editstatus'                       => '',
    'taboptions'                            => 'Opções',
    'titledelticketstatus'                  => 'Estados de pedido de suporte eliminados (%d)',
    'msgdelticketstatus'                    => 'Os seguintes estados de pedido de suporte foram eliminados:',
    'titleticketstatusinsert'               => 'Estado %s de pedido de suporte criado',
    'msgticketstatusinsert'                 => 'O estado %s de pedido de suporte foi criado com sucesso.',
    'titleticketstatusupdate'               => 'Estado %s de pedido de suporte atualizado',
    'msgticketstatusupdate'                 => 'O estado %s de pedido de suporte foi atualizado com sucesso.',
    'titlemasterstatusdelete'               => 'Não é possível eliminar estados de pedido de suporte (%d)',
    'msgmasterstatusdelete'                 => 'Os seguintes estados principais de pedido de suporte não puderam ser eliminados:',
    'tablanguages'                          => 'Idiomas: tradução',
    'title_statusinuse'                     => 'Estado em utilização',
    'message_statusinuse_editassociation'   => 'Existem pedidos de suporte definidos com este estado que pertencem ao departamento associado existente. Necessita de alterar o estado dos pedidos de suporte antes de poder atualizar o departamento associado.',
    'message_statusinuse_createassociation' => 'Existem pedidos de suporte definidos com este estado que pertencem ao departamento associado existente. Necessita de alterar o estado dos pedidos de suporte antes de poder atualizar o departamento associado.',
    // ======= END v4 LOCALES =======
);


return $__LANG;
