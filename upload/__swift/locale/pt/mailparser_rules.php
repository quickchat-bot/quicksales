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
    'isenabled'                 => 'Regra de analisador de email ativada',
    'desc_isenabled'            => 'Se desativado, as regras de email serão ignoradas.',
    'insertcriteria'            => 'Inserir critérios',
    'tabactions'                => 'Ações',
    'titlenoaction'             => 'Nenhuma ação especificada',
    'msgnoaction'               => 'É necessário especificar pelo menos uma ação a ser executada pela regra de email (se corresponder).',
    'if'                        => 'Se',
    'titleinsertrule'           => 'Regra de email (%s) criada',
    'msginsertrule'             => 'Regra de email (%s) criada com sucesso.',
    'titleupdaterule'           => 'Regra de email (%s) atualizada',
    'msgupdaterule'             => 'Regra de analisador de email "%s" atualizada com sucesso.',
    'titledelprules'            => 'Regras de email eliminadas (%d)',
    'msgdelprules'              => 'As seguintes regras de email foram eliminadas:',
    'titleenableprules'         => 'Regras de email ativadas (%d)',
    'msgenableprules'           => 'As seguintes regras de email foram ativadas:',
    'titledisableprules'        => 'Regras de email desativadas (%d)',
    'msgdisableprules'          => 'As seguintes regras de email foram desativadas:',
    // ======= END v4 LOCALES =======

    'mailparser'                => 'Analisador de email',

    'desc_parserrules'          => '',

    // Operators
    'opcontains'                => 'Contém',
    'opnotcontains'             => 'Não contém',
    'opequal'                   => 'Igual a',
    'opnotequal'                => 'Não é igual a',
    'opgreater'                 => 'Maior que',
    'opless'                    => 'Menor que',
    'opregexp'                  => 'Expressão regular',
    'strue'                     => 'Verdadeiro',
    'sfalse'                    => 'Falso',

    // Criteria
    'psendername'               => 'Nome do remetente',
    'desc_psendername'          => 'O nome do remetente é analisado no cabeçalho "De:" da mensagem de email.',

    'psenderemail'              => 'Endereço de email do remetente',
    'desc_psenderemail'         => 'O endereço de email do remetente é analisado no cabeçalho "De:" da mensagem de email. Exemplo: <em>De: <span class="tabletitle">Nome do remetente</span> <senderemail@domain.com></em>',

    'pdestinationname'          => 'Nome de destino (destinatário)',
    'desc_pdestinationname'     => 'O nome do destinatário é analisado no cabeçalho "Para:" do email. O nome de destino corresponde normalmente ao nome do funcionário que respondeu ao pedido de suporte (exceto se estiver configurado de outra forma).',

    'pdestinationemail'         => 'Endereço de email de destino (destinatário)',
    'desc_pdestinationemail'    => 'O endereço de email de destino é analisado no cabeçalho "Para:" da mensagem de email. Normalmente, é o endereço que pertence à fila de email para a qual a resposta está a ser enviada.',

    'preplytoname'              => 'Nome Responder a',
    'desc_preplytoname'         => 'O nome Responder a é analisado no cabeçalho "Responder a:" da mensagem de email.',

    'preplytoemail'             => 'Endereço de email Responder a',
    'desc_preplytoemail'        => 'O endereço de email Responder a é analisado no cabeçalho "Responder a:" da mensagem de email.',

    'psubject'                  => 'Assunto',
    'desc_psubject'             => 'Corresponde ao assunto do email.',

    'precipients'               => 'Destinatários',
    'desc_precipients'          => 'Corresponde aos destinatários do email. Pode haver vários destinatários para os quais uma mensagem recebida foi enviada, incluindo os cabeçalhos "Para:" e "CC:" da mensagem de email.',

    'pbody'                     => 'Corpo',
    'desc_pbody'                => 'Corresponde ao restante conteúdo do corpo do email após o HTML (exceto se estiver configurado de outra forma) e as quebras de linha serem removidas.',

    'pbodysize'                 => 'Tamanho do corpo',
    'desc_pbodysize'            => 'O tamanho total do conteúdo do corpo do email em bytes.',

    'ptextbody'                 => 'Corpo de texto simples',
    'desc_ptextbody'            => 'Um email pode ter dois tipos de conteúdo: <em>texto simples</em>, <em>HTML</em> ou <em>ambos</em>. Esta regra procura apenas a versão <em>texto simples</em> do corpo. Note que nem todos os emails são enviados no formato <em>ambos</em>.',

    'phtmlbody'                 => 'Corpo HTML',
    'desc_phtmlbody'            => 'Um email pode ter dois tipos de conteúdo: <em>texto simples</em>, <em>HTML</em> ou <em>ambos</em>. Esta regra procura apenas a versão <em>HTML</em> do corpo. Note que nem todos os emails são enviados no formato <em>ambos</em>.',

    'ptextbodysize'             => 'Tamanho do corpo do texto',
    'desc_ptextbodysize'        => 'Um email pode ter dois tipos de conteúdo: <em>texto simples</em>, <em>HTML</em> ou <em>ambos</em>. Esta regra corresponde ao tamanho total do conteúdo do corpo <em>texto simples</em> do email em bytes.',

    'phtmlbodysize'             => 'Tamanho do corpo HTML',
    'desc_phtmlbodysize'        => 'Um email pode ter dois tipos de conteúdo: <em>texto simples</em>, <em>HTML</em> ou <em>ambos</em>. Esta regra corresponde ao tamanho total do conteúdo do corpo <em>HTML</em> do email em bytes.',

    'pattachmentname'           => 'Nomes de anexos',
    'desc_pattachmentname'      => 'Faz corresponder os nomes de anexos dos anexos que foram aceites pelo suporte técnico (conforme definido no painel de controlo do administrador).',

    'pattachmentsize'           => 'Tamanho do anexo',
    'desc_pattachmentsize'      => 'Faz corresponder os tamanhos de anexos dos anexos que foram aceites pelo suporte técnico (conforme definido no painel de controlo do administrador).',

    'ptotalattachmentsize'      => 'Tamanho total dos anexos',
    'desc_ptotalattachmentsize' => 'Faz corresponder o <em>tamanho total</em> (em bytes) dos anexos que foram aceites pelo suporte técnico (conforme definido no painel de controlo do administrador).',

    'pisreply'                  => 'É uma resposta de utilizador',
    'desc_pisreply'             => 'Se o email recebido for uma resposta a um pedido de suporte existente de um utilizador final, esta regra terá correspondência.',

    'pisthirdparty'             => 'É a resposta de terceiros',
    'desc_pisthirdparty'        => 'Se o email recebido for uma resposta a um pedido de suporte existente de um endereço adicionado ao pedido de suporte como um destinatário de terceiros, esta regra terá correspondência.',

    'pfloodprotection'          => 'Proteção contra flood acionada',
    'desc_pfloodprotection'     => 'Esta regra terá correspondência caso a proteção contra flood do suporte técnico tenha sido acionada.',

    'pisstaffreply'             => 'É uma resposta do pessoal',
    'desc_pisstaffreply'        => 'Se o email recebido for uma resposta a um pedido de suporte existente do endereço de email de um funcionário, esta regra terá correspondência.',

    'pticketstatus'             => 'Estado do pedido de suporte (pós-análise)',
    'desc_pticketstatus'        => 'Se o email recebido for uma resposta a um pedido de suporte existente, o estado do pedido de suporte corresponderá a esta regra.',

    'pticketemailqueue'         => 'Fila de email',
    'desc_pticketemailqueue'    => 'Se o email recebido for obtido a partir da fila especificada, esta regra terá correspondência.',

    'ptickettype'               => 'Tipo de pedido de suporte (pós-análise)',
    'desc_ptickettype'          => 'Se o email recebido for uma resposta a um pedido de suporte existente, o tipo do pedido de suporte corresponderá a esta regra.',

    'pticketpriority'           => 'Prioridade do pedido de suporte (pós-análise)',
    'desc_pticketpriority'      => 'Se o email recebido for uma resposta a um pedido de suporte existente, a prioridade do pedido de suporte corresponderá a esta regra.',

    'pticketusergroup'          => 'Grupo de utilizadores do pedido de suporte (pós-análise)',
    'desc_pticketusergroup'     => 'Se o email recebido for uma resposta a um pedido de suporte existente, o grupo de utilizadores do pedido de suporte corresponderá a esta regra.',

    'pticketdepartment'         => 'Departamento do pedido de suporte (pós-análise)',
    'desc_pticketdepartment'    => 'Se o email recebido for uma resposta a um pedido de suporte existente, o departamento do pedido de suporte corresponderá a esta regra.',

    'pticketowner'              => 'Proprietário do pedido de suporte (pós-análise)',
    'desc_pticketowner'         => 'Se o email recebido for uma resposta a um pedido de suporte existente, o proprietário do pedido de suporte corresponderá a esta regra.',
    'prunassigned'              => '-- Não atribuído --',

    'pticketflagtype'           => 'Ticket flag (post-parse)',
    'desc_pticketflagtype'      => 'Se o email recebido for uma resposta a um pedido de suporte existente, o sinalizador do pedido de suporte corresponderá a esta regra.',

    'pbayescategory'            => 'Categoria Bayesiana',
    'desc_pbayescategory'       => 'O suporte técnico passa todo o email recebido pelo filtro Bayesiano e atribui o pedido de suporte a uma categoria Bayesiana com a pontuação mais elevada. Pode utilizar o mesmo para filtrar spam ou para preparar o sistema para encaminhar automaticamente pedidos de suporte com base no seu conteúdo, utilizando categorias Bayesianas personalizadas.',

    // Insert Rule
    'insertrule'                => 'Inserir regra',
    'iruletype'                 => 'Tipo de regra de email: ',
    'ipreparse'                 => 'Antes de o email ser processado (pré-análise)',
    'ipostparse'                => 'Após o email ser processado (pós-análise)',

    'paignore'                  => 'Ignorar email',
    'desc_paignore'             => 'O email será ignorado pelo suporte técnico e não será transformado num pedido de suporte nem numa resposta.',
    'panoautoresp'              => 'Não enviar resposta automática',
    'desc_panoautoresp'         => 'Não será enviada uma resposta automática a confirmar a receção do novo email (como um novo pedido de suporte ou resposta).',
    'panoalerts'                => 'Não processar regras de notificação',
    'desc_panoalerts'           => 'Se ativado, as regras de notificação do suporte técnico não serão processadas.',
    'pnochange'                 => '-- Sem alterações --',
    'pcstaff'                   => 'Atribuir o pedido de suporte',
    'desc_pcstaff'              => 'O pedido de suporte (independentemente de ser um novo ou uma resposta a um antigo) será atribuído a este funcionário.',
    'pcstatus'                  => 'Alterar estado do pedido de suporte',
    'desc_pcstatus'             => 'O pedido de suporte será alterado para este estado.',
    'pcpriority'                => 'Alterar prioridade do pedido de suporte',
    'desc_pcpriority'           => 'O pedido de suporte será alterado para esta prioridade.',
    'pcdepartment'              => 'Mover pedido de suporte para departamento',
    'desc_pcdepartment'         => 'O pedido de suporte será movido para este departamento.',
    'pcslaplan'                 => 'Alterar plano de SLA do pedido de suporte',
    'desc_pcslaplan'            => 'O pedido de suporte será atribuído a este plano de SLA.',
    'pcmovetotrash'             => 'Mover para o lixo',
    'desc_pcmovetotrash'        => 'O pedido de suporte será movido para o lixo',
    'pcflag'                    => 'Sinalizar pedido de suporte',
    'desc_pcflag'               => 'O pedido de suporte será sinalizado com esta cor.',
    'paddnotes'                 => 'Adicionar uma nota ao pedido de suporte',
    'desc_paddnotes'            => 'Será adicionada uma nota ao pedido de suporte.',
    'pcforward'                 => 'Reencaminhar o email',
    'desc_pcforward'            => 'O email será reencaminhado para este endereço.',
    'preply'                    => 'Responder para o email',
    'desc_preply'               => 'O email receberá uma resposta automática com esta mensagem.',
    'panoticket'                => 'Não processar como uma resposta a pedido de suporte',
    'desc_panoticket'           => 'Mesmo se o email for uma resposta a um pedido de suporte existente, o email não será adicionado ao mesmo, sendo criado um novo pedido de suporte.',
    'pctickettype'              => 'Alterar tipo de pedido de suporte',
    'desc_pctickettype'         => 'O tipo de pedido de suporte será alterado para o tipo aqui especificado.',
    'pcaddtags'                 => 'Adicionar tags ao pedido de suporte',
    'desc_pcaddtags'            => 'Estas tags serão adicionadas ao pedido de suporte.',
    'pcremovetags'              => 'Remover tags do pedido de suporte',
    'desc_pcremovetags'         => 'Se o pedido de suporte contiver alguma destas tags, serão removidas.',
    'pcprivate'                 => 'Tornar privada a resposta ao pedido de suporte',
    'desc_pcprivate'            => 'Esta resposta a pedido de suporte será marcada como privada, sendo visível apenas a outros funcionários (isto aplica-se apenas às respostas ao pedido de suporte do pessoal).',

    // Edit Rules
    'editrule'                  => 'Editar regra',
    'ptitle'                    => 'Título da regra de email',
    'desc_ptitle'               => 'Por exemplo, <em>Recibos de PayPal</em>.',
    'pstop'                     => 'Parar processamento de outras regras',
    'desc_pstop'                => 'Se esta regra tiver correspondência primeiro, não serão processadas quaisquer regras de email após a mesma.',

    // Manage Rules
    'managerules'               => 'Regras',
    'ruletitle'                 => 'Título da regra',
    'sortorder'                 => 'Ordem de execução',
    'desc_sortorder'            => 'A correspondência de várias regras de email ao mesmo email é possível. O suporte técnico processará as regras de acordo com o número da ordem de execução, do menor para o maior.',
    'ruletype'                  => 'Tipo de regra',
    'creationdate'              => 'Criado a',

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
