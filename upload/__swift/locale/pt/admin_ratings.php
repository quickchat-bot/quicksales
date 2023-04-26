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
    'ratings'                     => 'Classificações',
    'tabgeneral'                  => 'Geral',
    'tabpermissionsstaff'         => 'Permissões (pessoal)',
    'tabpermissionsuser'          => 'Permissões (utilizador)',
    'staffgroups'                 => 'Equipas',
    'usergroups'                  => 'Grupos de utilizadores',
    'ratingticket'                => 'Pedido de suporte',
    'ratingticketpost'            => 'Publicações de pedido de suporte',
    'ratingchatsurvey'            => 'Inquérito da conversação',
    'ratingchathistory'           => 'Histórico de conversação',

    'staffvisibilitycustom'       => 'Restringir modificação de classificação a determinadas equipas',
    'desc_staffvisibilitycustom'  => 'Se ativado, apenas as equipas abaixo poderão modificar e definir valores para esta classificação.',

    'buservisibilitycustom'       => 'Restringir classificação a grupos de utilizadores específicos',
    'desc_buservisibilitycustom'  => 'Se ativado, apenas os grupos de utilizadores abaixo poderão modificar e definir valores para esta classificação.',

    'bstaffvisibilitycustom'      => 'Restringir classificação a equipas específicas',
    'desc_bstaffvisibilitycustom' => 'Se ativado, apenas as equipas abaixo poderão visualizar esta classificação.',

    'isclientonly'                => 'Impedir que o pessoal defina valores para esta classificação',
    'desc_isclientonly'           => 'Apenas os utilizadores finais poderão definir valores para esta classificação, se ativado.',

    'ratingscale'                 => 'Escala de classificação',
    'desc_ratingscale'            => 'A classificação máxima que pode ser atribuída. Por exemplo, se <em>5</em> for selecionado, a classificação mais baixa possível será 1 e a mais elevada 10.',

    'ratingtype'                  => 'Área do suporte técnico',
    'desc_ratingtype'             => 'A área do suporte técnico à qual se aplicará esta classificação. <br /><br /><strong>Pedido de suporte:</strong> utilizado para classificar pedidos de suporte completos.<br /><strong>Publicação de pedido de suporte:</strong> utilizado para classificar respostas a pedidos de suporte individuais.<br /><strong>Inquérito de conversação:</strong> as classificações serão adicionadas ao inquérito pós-conversação.<br /><strong>Histórico de conversação:</strong> utilizado para classificar registos de conversação no painel de controlo do pessoal (destinado a classificação interna e auditoria).',

    'ratingtitle'                 => 'Código de classificação',
    'desc_ratingtitle'            => 'Por exemplo, <em>Satisfação global</em> ou <em>Velocidade de resolução</em>.',

    'displayorder'                => 'Ordem de apresentação',
    'desc_displayorder'           => 'Podem aparecer várias classificações na mesma localização. Serão apresentadas de acordo com o número da sua ordem de apresentação, do menor para o maior.',

    'ratingdep'                   => 'Restringir classificação a um departamento',
    'desc_ratingdep'              => 'Selecione um departamento para restringir esta classificação a pedidos de suporte ou conversações num departamento específico. Se não for selecionado qualquer departamento, a classificação será apresentada em todos os departamentos.',
    'ratingalldep'                => '-- Todos os departamentos --',

    'iseditable'                  => 'Permitir modificação após primeira submissão',
    'desc_iseditable'             => 'O valor definido para esta classificação pode ser modificado, se ativado.',

    'ratingvisibility'            => 'Quem pode submeter uma classificação?',
    'desc_ratingvisibility'       => '<strong>Público</strong> As classificações estão disponíveis para todos os utilizadores. Selecione esta opção se pretender obter feedback dos seus utilizadores finais e tornar as classificações visíveis ao pessoal.<br /><strong>Privado</strong> As classificações só estão disponíveis aos funcionários, para fins de classificação interna.',

    'ratingvis'                   => 'Visibilidade',
    'insertrating'                => 'Inserir classificação',
    'desc_insertrating'           => '',
    'manageratings'               => 'Gerir',
    'desc_manageratings'          => '',
    'wineditrating'               => 'Editar classificação: %s',
    'editrating'                  => 'Editar classificação',
    'desc_editrating'             => '',
    'titledelrating'              => 'Classificações eliminadas (%d)',
    'msgdelrating'                => 'As classificações seguintes e respetivos dados foram eliminados:',
    'titleratinginsert'           => 'Classificação criada',
    'msgratinginsert'             => 'A classificação (%s) foi criada com sucesso.',
    'titleratingupdate'           => 'Classificação atualizada',
    'msgratingupdate'             => 'A classificação (%s) foi atualizada com sucesso.',

    // Potentialy unused phrases in admin_ratings.php
    'ratingknowledgebase'         => 'Base de dados de conhecimento',
    'ratingtroubleshooter'        => 'Resolução de problemas',
    'ratingnews'                  => 'Notícias',
);


return $__LANG;
