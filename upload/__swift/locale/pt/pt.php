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

// Initial pass done
// Usage script run

$_SWIFT = SWIFT::GetInstance();

$__LANG = array(
    'charset'                      => 'UTF-8',
    'html_encoding'                => '8 bits',
    'text_encoding'                => '8 bits',
    'html_charset'                 => 'UTF-8',
    'text_charset'                 => 'UTF-8',
    'base_charset'                 => '',
    'yes'                          => 'Sim',
    'no'                           => 'Não',
    'menusupportcenter'            => 'Centro de suporte',
    'menustaffcp'                  => 'CP do pessoal',
    'menuadmincp'                  => 'CP do administrador',
    'app_notreg'                   => 'A aplicação %s não está registada',
    'event_notreg'                 => 'O evento %s não está registado',
    'unable_to_execute'            => 'Não é possível executar %s',
    'action_notreg'                => 'A ação %s não está registada',
    'username'                     => 'Nome de utilizador',
    'password'                     => 'Palavra-passe',
    'rememberme'                   => 'Lembrar-me',
    'defaulttitle'                 => '%s - Com tecnologia da QuickSupport',
    'login'                        => 'Iniciar sessão',
    'global'                       => 'Global',
    'first'                        => 'Primeiro',
    'last'                         => 'Último',
    'pagination'                   => 'Página %s de %s',
    'submit'                       => 'Submeter',
    'reset'                        => 'Reiniciar',
    'poweredby'                    => 'Helpdesk Software com tecnologia da QuickSupport',
    'copyright'                    => 'Copyright &copy; 2001-%s QuickSupport',
    'notifycsrfhash'               => 'Ocorreu um problema aquando da validação deste pedido por parte da QuickSupport',
    'titlecsrfhash'                => 'Ocorreu um problema aquando da validação deste pedido por parte da QuickSupport',
    'msgcsrfhash'                  => 'A QuickSupport valida os pedidos para se proteger contra falsificações de pedidos em vários sites e não conseguiu validar este pedido. Tente novamente.',
    'invaliduser'                  => 'Nome de utilizador ou palavra-passe incorreto',
    'invaliduseripres'             => 'Endereço IP não autorizado (tentativa: %d de %d)',
    'invaliduserdisabled'          => 'Esta conta está desativada (tentativa: %d de %d)',
    'invalid_sessionid'            => 'A sua sessão expirou. Inicie sessão',
    'staff_not_admin'              => 'O utilizador não possui privilégios de administrador',
    'sort_desc'                    => 'Ordenar descendente',
    'sort_asc'                     => 'Ordenar ascendente',
    'options'                      => 'Opções',
    'delete'                       => 'Eliminar',
    'settings'                     => 'Definições',
    'search'                       => 'Pesquisar',
    'searchbutton'                 => 'Pesquisar',
    'actionconfirm'                => 'Tem a certeza de que pretende continuar?',
    'loggedout'                    => 'Sessão terminada com sucesso',
    'view'                         => 'Ver',
    'dashboard'                    => 'Página inicial',
    'help'                         => 'Ajuda',
    'size'                         => 'Tamanho',
    'home'                         => 'Página inicial',
    'logout'                       => 'Terminar sessão',
    'staffcp'                      => 'CP do pessoal',
    'admincp'                      => 'CP do administrador',
    'winapp'                       => 'QuickSupport Desktop',
    'staffapi'                     => 'Staff API',
    'bytes'                        => 'Bytes',
    'kb'                           => 'KB',
    'mb'                           => 'MB',
    'gb'                           => 'GB',
    'noitemstodisplay'             => 'Nenhum item para apresentar',
    'manage'                       => 'Gerir',
    'title'                        => 'Título',
    'disable'                      => 'Desactivar',
    'enable'                       => 'Ativar',
    'edit'                         => 'Editar',
    'back'                         => 'Anterior',
    'forward'                      => 'Reencaminhar',
    'insert'                       => 'Inserir',
    'edit'                         => 'Editar',
    'update'                       => 'Atualizar',
    'public'                       => 'Público',
    'private'                      => 'Privado',
    'requiredfieldempty'           => 'Um dos campos obrigatórios está vazio',
    'clifatalerror'                => 'ERRO FATAL',
    'clienterchoice'               => 'Introduza a sua escolha: ',
    'clinotvalidchoice'            => '"%s" não é uma escolha válida. Tente novamente. ',
    'description'                  => 'Descrição',
    'success'                      => 'Sucesso',
    'failure'                      => 'Falha',
    'status'                       => 'Estado',
    'date'                         => 'Data',
    'seconds'                      => 'Segundos',
    'order'                        => 'Ordem',
    'email'                        => 'Email',
    'subject'                      => 'Assunto',
    'contents'                     => 'Conteúdos',
    'sunday'                       => 'Domingo',
    'monday'                       => 'Segunda-feira',
    'tuesday'                      => 'Terça-feira',
    'wednesday'                    => 'Quarta-feira',
    'thursday'                     => 'Quinta-feira',
    'friday'                       => 'Sexta-feira',
    'saturday'                     => 'Sábado',
    'am'                           => 'AM',
    'pm'                           => 'PM',
    'pfieldreveal'                 => '[Revelar]',
    'pfieldhide'                   => '[Ocultar]',
    'loadingwindow'                => 'A carregar...',
    'customfields'                 => 'Campos personalizados',
    'nopermission'                 => 'Infelizmente, não tem permissão para efetuar este pedido.',
    'nopermissiontext'             => 'Infelizmente, não tem permissão para efetuar este pedido.',
    'generationdate'               => 'XML gerado: %s',
    'approve'                      => 'Aprovar',
    'paginall'                     => 'Tudo',
    'fullname'                     => 'Nome completo',
    'onlineusers'                  => 'Pessoal online',
    'vardate1'                     => '%dd %dh %dm',
    'vardate2'                     => '%dh %dm %ds',
    'vardate3'                     => '%dm %ds',
    'vardate4'                     => '%ds',
    'reports'                      => 'Relatórios',
    'demomode'                     => 'Esta ação não pode ser realizada no modo de demonstração',
    'unmodifiedreport'             => 'Running the report unmodified as user does not have permission to modify report.',
    'titledemomode'                => 'Não é possível continuar',
    'msgdemomode'                  => 'Esta ação não pode ser realizada no modo de demonstração',
    'filter'                       => 'Filtrar',
    'editor'                       => 'Editor',
    'images'                       => 'Imagens',
    'tabedit'                      => 'Editar',
    'notifyfieldempty'             => 'Um dos campos obrigatórios está vazio',
    'notifykqlfieldempty'          => 'A consulta do KQL está vazia',
    'titlefieldempty'              => 'Campos em falta',
    'msgfieldempty'                => 'Um dos campos obrigatórios está vazio ou contém dados inválidos; verifique os dados introduzidos.',
    'msgpastdate'                   => 'A data não pode estar no passado',
    'titlefieldinvalid'            => 'Valor inválido',
    'msgfieldinvalid'              => 'Um dos campos contém dados inválidos; por favor, verifique sua entrada.',
    'titleinvalidemail'            => 'Invalid Email',
    'msginvalidemail'              => 'The email you entered is same as your customer email; please check your input.',
    'msginvalidadditionalemail'    => 'The email address entered is already used in the desk; Please enter the valid email address.',
    'save'                         => 'Guardar',
    'viewall'                      => 'Ver tudo',
    'cancel'                       => 'Cancelar',
    'save'                         => 'Guardar',
    'tabgeneral'                   => 'Geral',
    'language'                     => 'Idioma',
    'loginshare'                   => 'LoginShare',
    'licenselimit_unabletocreate'  => 'Não foi possível criar um novo funcionário, pois foi atingido o seu limite de licenças',
    'help'                         => 'Ajuda',
    'name'                         => 'Nome',
    'value'                        => 'Valor',
    'engagevisitor'                => 'Contactar visitante',
    'inlinechat'                   => 'Conversação embutida',
    'url'                          => 'URL',
    'hexcode'                      => 'Código hexadecimal',
    'vactionvariables'             => 'Ação: variáveis',
    'vactionvexp'                  => 'Ação: experiência do visitante',
    'vactionsalerts'               => 'Ação: notificação do pessoal',
    'vactionsetdepartment'         => 'Ação: definir departamento',
    'vactionsetskill'              => 'Ação: definir competência',
    'vactionsetgroup'              => 'Ação: definir grupo de visitantes',
    'vactionsetcolor'              => 'Ação: definir cor',
    'vactionbanvisitor'            => 'Ação: banir visitante',
    'customengagevisitor'          => 'Contacto personalizado de visitante',
    'managerules'                  => 'Gerir regras',
    'open'                         => 'Abrir',
    'close'                        => 'Fechar',
    'titleupdatedswiftsettings'    => '%s definições atualizadas',
    'updatedsettingspartially'     => 'As seguintes configurações não foram atualizadas',
    'msgupdatedswiftsettings'      => 'As definições %s foram atualizadas com sucesso.',
    'geoipprocessrunning'          => 'A compilação da base de dados de GeoIP já está em curso',
    'continueprocessquestion'      => 'Ainda está a ser executada uma tarefa. Se sair desta página, será cancelada. Pretende continuar?',
    'titleupdsettings'             => '%s definições atualizadas',
    'type'                         => 'Tipo',
    'banip'                        => 'IP (255.255.255.255)',
    'banclassc'                    => 'Classe C (255.255.255.*)',
    'banclassb'                    => 'Classe B (255.255.*.*)',
    'banclassa'                    => 'Classe A (255.*.*.*)',
    'if'                           => 'Se',
    'loginlogerror'                => 'Início de sessão bloqueado durante %d minutos (tentativa: %d de %d)',
    'loginlogerrorsecs'            => 'Início de sessão bloqueado durante %d segundos (tentativa: %d de %d)',
    'loginlogwarning'              => 'Nome de utilizador ou palavra-passe inválido (tentativa: %d de %d)',
    'na'                           => '- ND -',
    'redirectloading'              => 'A carregar...',
    'noinfoinview'                 => 'Não há nada a apresentar aqui',
    'nochange'                     => '-- Sem alterações --',
    'activestaff'                  => '-- Pessoal ativo --',
    'notificationuser'             => 'Utilizador',
    'notificationuserorganization' => 'Organização do utilizador',
    'notificationstaff'            => 'Pessoal (proprietário)',
    'notificationteam'             => 'Equipa',
    'notificationdepartment'       => 'Departamento',
    'notificationsubject'          => 'Assunto: ',
    'lastupdate'                   => 'Última atualização',
    'interface_admin'              => 'CP do administrador',
    'interface_staff'              => 'CP do pessoal',
    'interface_intranet'           => 'Intranet',
    'interface_api'                => 'API',
    'interface_winapp'             => 'QuickSupport Desktop/API do Pessoal',
    'interface_syncworks'          => 'SyncWorks',
    'interface_instaalert'         => 'InstaAlert',
    'interface_pda'                => 'PDA',
    'interface_rss'                => 'RSS',
    'error_database'               => 'Base de dados',
    'error_php'                    => 'PHP',
    'error_exception'              => 'Exceção',
    'error_mail'                   => 'Email',
    'error_general'                => 'Geral',
    'error_loginshare'             => 'LoginShare',
    'loading'                      => 'A carregar...',
    'pwtooshort'                   => 'Demasiado curto',
    'pwveryweak'                   => 'Muito fraca',
    'pwweak'                       => 'Fraca',
    'pwmedium'                     => 'Média',
    'pwstrong'                     => 'Forte',
    'pwverystrong'                 => 'Muito forte',
    'pwunsafeword'                 => 'Palavra-passe potencialmente não segura',
    'staffpasswordpolicy'          => '<strong>Requisitos da palavra-passe:</strong> Comprimento mínimo: %d carateres, Dígitos mínimos: %d, Mínimo de símbolos: %d, Mínimo de maiúsculas: %d',
    'userpasswordpolicy'           => '<strong>Requisitos da palavra-passe:</strong> Comprimento mínimo: %d carateres, Dígitos mínimos: %d, Mínimo de símbolos: %d, Mínimo de maiúsculas: %d',
    'titlepwpolicy'                => 'A palavra-passe não cumpre os requisitos',
    'passwordexpired'              => 'A palavra-passe expirou',
    'newpassword'                  => 'Nova palavra-passe',
    'passwordagain'                => 'Palavra-passe (repita)',
    'passworddontmatch'            => 'As palavras-passe não correspondem',
    'defaulttimezone'              => '-- Fuso horário padrão --',
    'tagcloud'                     => 'Nuvem de etiquetas',
    'searchmodeactive'             => 'Os resultados estão filtrados - clique para redefinir',
    'notifysearchfailed'           => '"0" resultados encontrados',
    'titlesearchfailed'            => '"0" resultados encontrados',
    'msgsearchfailed'              => 'A QuickSupport não conseguiu encontrar quaisquer registos correspondentes aos critérios especificados.',
    'quickfilter'                  => 'Filtro rápido',
    'fuenterurl'                   => 'Introduzir URL:',
    'fuorupload'                   => 'Ou carregar:',
    'errorsmtpconnect'             => 'Não é possível estabelecer ligação com o servidor SMTP',
    'starttypingtags'              => 'Comece a escrever para inserir tags...',
    'unsupportedtagchars'          => 'One or more unsupported characters were stripped from the tag.',
    'titleinvalidfileext'          => 'Tipo de imagem não suportado',
    'msginvalidfileext'            => 'Os tipos de imagem suportados são: gif, jpeg, jpg e png.',
    'notset'                       => '-- Não definido --',
    'ratings'                      => 'Classificações',
    'system'                       => 'Sistema',
    'schatid'                      => 'ID da conversação',
    'supportcenterfield'           => 'Centro de suporte:',
    'smessagesurvey'               => 'Mensagens/inquéritos',
    'nosubject'                    => '(sem assunto)',
    'nolocale'                     => '(sem região)',
    'markdefault'                   => 'Marcar como padrão',
    'policyurlupdatetitle'           => 'URL da política atualizado',
    'policyurlupdatemessage'       => 'O URL da política foi atualizado com sucesso.',

    // Easy Dates
    'edoneyear'                    => 'um ano',
    'edxyear'                      => '%d anos',
    'edonemonth'                   => 'um mês',
    'edxmonth'                     => '%d meses',
    'edoneday'                     => 'um dia',
    'edxday'                       => '%d dias',
    'edonehour'                    => 'uma hora',
    'edxhour'                      => '%d horas',
    'edoneminute'                  => 'um minuto',
    'edxminute'                    => '%d minutos',
    'edjustnow'                    => 'Só agora',
    'edxseconds'                   => '%d segundos',
    'ago'                          => 'há',

    // Operators
    'opcontains'                   => 'Contém',
    'opnotcontains'                => 'Não contém',
    'opequal'                      => 'Igual a',
    'opnotequal'                   => 'Não é igual a',
    'opgreater'                    => 'Maior que',
    'opless'                       => 'Menor que',
    'opregexp'                     => 'Expressão regular',
    'opchanged'                    => 'Alterado',
    'opnotchanged'                 => 'Não alterado',
    'opchangedfrom'                => 'Alterado de',
    'opchangedto'                  => 'Alterado para',
    'opnotchangedfrom'             => 'Não alterado de',
    'opnotchangedto'               => 'Não alterado para',
    'matchand'                     => 'AND',
    'matchor'                      => 'OR',
    'strue'                        => 'Verdadeiro',
    'sfalse'                       => 'Falso',
    'notifynoperm'                 => 'Infelizmente, não tem permissão para efetuar este pedido.',
    'titlenoperm'                  => 'Permissões insuficientes',
    'msgnoperm'                    => 'Infelizmente, não tem permissão para efetuar este pedido.',
    'msgnoperm1'                   => 'The ticket has been created but you do not have the permission to carry out other operations.',
    'cyesterday'                   => 'Ontem',
    'ctoday'                       => 'Hoje',
    'ccurrentwtd'                  => 'Semana atual até à data',
    'ccurrentmtd'                  => 'Mês atual até à data',
    'ccurrentytd'                  => 'Ano atual até à data',
    'cl7days'                      => 'Últimos 7 dias',
    'cl30days'                     => 'Últimos 30 dias',
    'cl90days'                     => 'Últimos 90 dias',
    'cl180days'                    => 'Últimos 180 dias',
    'cl365days'                    => 'Últimos 365 dias',
    'ctomorrow'                    => 'Amanhã',
    'cnextwfd'                     => 'Semana atual a partir da data',
    'cnextmfd'                     => 'Mês atual a partir da data',
    'cnextyfd'                     => 'Ano atual a partir da data',
    'cn7days'                      => 'Próximos 7 dias',
    'cn30days'                     => 'Próximos 30 dias',
    'cn90days'                     => 'Próximos 90 dias',
    'cn180days'                    => 'Próximos 180 dias',
    'cn365days'                    => 'Próximos 365 dias',
    'new'                          => 'Novo',
    'phoneext'                     => 'Telefone: %s',
    'snewtickets'                  => 'Novos pedidos de suporte',
    'sadvancedsearch'              => 'Pesquisa avançada',
    'squicksearch'                 => 'Pesquisa rápida:',
    'sticketidlookup'              => 'Pesquisa por ID de pedido de suporte:',
    'screatorreplier'              => 'Criador/quem responde:',
    'smanage'                      => 'Gerir',
    'clear'                        => 'Limpar',
    'never'                        => 'Nunca',
    'seuser'                       => 'Utilizadores',
    'seuserorg'                    => 'Organizações do utilizador',
    'manage'                       => 'Gerir',
    'import'                       => 'Importar',
    'export'                       => 'Exportar',
    'comments'                     => 'Comentários',
    'commentdata'                  => 'Comentários:',
    'postnewcomment'               => 'Publicar um novo comentário',
    'replytocomment'               => 'Responder ao comentário',
    'buttonsubmit'                 => 'Submeter',
    'reply'                        => 'Responder',

    // Flags
    'purpleflag'                   => 'Sinalizador roxo',
    'redflag'                      => 'Sinalizador vermelho',
    'orangeflag'                   => 'Sinalizador laranja',
    'yellowflag'                   => 'Sinalizador amarelo',
    'blueflag'                     => 'Sinalizador azul',
    'greenflag'                    => 'Sinalizador verde',

    'calendar'                     => 'Calendário',
    'cal_january'                  => 'Janeiro',
    'cal_february'                 => 'Fevereiro',
    'cal_march'                    => 'Março',
    'cal_april'                    => 'Abril',
    'cal_may'                      => 'Maio',
    'cal_june'                     => 'Junho',
    'cal_july'                     => 'Julho',
    'cal_august'                   => 'Agosto',
    'cal_september'                => 'Setembro',
    'cal_october'                  => 'Outubro',
    'cal_november'                 => 'Novembro',
    'cal_december'                 => 'Dezembro',

    /**
     * ###############################################
     * APP LIST
     * ###############################################
     */
    'app_base'                     => 'Base',
    'app_tickets'                  => 'Pedidos de suporte',
    'app_knowledgebase'            => 'Base de dados de conhecimento',
    'app_parser'                   => 'Analisador de email',
    'app_livechat'                 => 'Suporte em tempo real',
    'app_troubleshooter'           => 'Resolução de problemas',
    'app_news'                     => 'Notícias',
    'app_core'                     => 'Core',
    'app_backend'                  => 'Back-end',
    'app_reports'                  => 'Relatórios',

    // Potentialy unused phrases in en-us.php
    'defaultloginapi'              => 'QuickSupport Login Routine',
    'redirect_login'               => 'Processing Login...',
    'redirect_dashboard'           => 'Redirecting to Home...',
    'no_wait'                      => 'Please click here if your browser does not automatically redirect you',
    'select_un_all'                => 'Select/Unselect All Items',
    'quicksearch'                  => 'Quick Search',
    'mass_action'                  => 'Mass Action',
    'massfieldaction'              => 'Action: ',
    'advanced_search'              => 'Advanced Search',
    'searchfieldquery'             => 'Query: ',
    'searchfieldfield'             => 'Field: ',
    'settingsfieldresultsperpage'  => 'Results Per Page: ',
    'clidefault'                   => '%s v%s\\nCopyright (c) 2001-%s QuickSupport\\n',
    'firstselect'                  => '- Select -',
    'exportasxml'                  => 'XML',
    'exportascsv'                  => 'CSV',
    'exportassql'                  => 'SQL',
    'exportaspdf'                  => 'PDF',
    'clientarea'                   => 'Support Center',
    'pdainterface'                 => 'PDA Interface',
    'kayakomobile'                 => 'QuickSupport Mobile',
    'thousandsseperator'           => ',',
    'clierror'                     => '[ERROR]: ',
    'cliwarning'                   => '[WARNING]: ',
    'cliok'                        => '[OK]: ',
    'cliinfo'                      => '[INFO]: ',
    'sections'                     => 'Sections',
    'twodesc'                      => '%s (%s)',
    'hourrenderus'                 => '%s:%s %s',
    'hourrendereu'                 => '%s:%s',
    'jump'                         => 'Jump',
    'newprvmsgconfirm'             => 'You have a new private message\\nClick OK to open the private message list in a new window.',
    'commentdelconfirm'            => 'Comment deleted successfully',
    'commentstatusconfirm'         => 'Comment status change completed successfully',
    'commentupdconfirm'            => 'Comment by "%s" updated successfully',
    'unapprove'                    => 'Unapprove',
    'approvedcomments'             => 'Approved Comments',
    'unapprovedcomments'           => 'Unapproved Comments',
    'editcomment'                  => 'Edit Comment',
    'quickjump'                    => 'Quick Jump',
    'choiceadd'                    => 'Add >',
    'choicerem'                    => '< Remove',
    'choicemup'                    => 'Move Up',
    'choicemdn'                    => 'Move Down',
    'ticketsubjectformat'          => '[%s#%s]: %s',
    'forwardticketsubjectformat'   => '[%s~%s]: %s',
    'loggedinas'                   => 'Logged In: ',
    'tcustomize'                   => 'Customize...',
    'notifydemomode'               => 'Permission denied. Product is in demo mode.',
    'uploadfile'                   => 'Upload File: ',
    'uploadedimages'               => 'Uploaded Images',
    'tabinsert'                    => 'Insert',
    'allpages'                     => 'All Pages',
    'maddimage'                    => 'Image',
    'maddlinktoimage'              => 'Link to Image',
    'maddthumbnail'                => 'Thumbnail',
    'maddthumbnailwithlink'        => 'Thumbnail with Link',
    'checkuncheckall'              => 'Check/Uncheck All',
    'defaultloginshare'            => 'QuickSupport LoginShare',
    'invalidusernoapiaccess'       => 'Invalid Staff. This staff does not have API access, please configure under Settings > General.',
    'msgupdsettings'               => 'Successfully updated all settings for "%s"',
    'msgpwpolicy'                  => 'The password specified does not match the requirements of the Password Policy.',
    'passwordpolicymismatch'       => 'The password specified does not match the requirements of the Password Policy.',
    'short_all_tickets'            => 'All',
    'iprestrictdenial'             => 'Access Denied (%s): IP not allowed (%s), please add the IP in the allowed list under /config/config.php',
    'cal_clear'                    => 'Clear',
    'cal_close'                    => 'Close',
    'cal_prev'                     => 'Prev',
    'cal_next'                     => 'Next',
    'cal_today'                    => 'Today',
    'cal_sunday'                   => 'Su',
    'cal_monday'                   => 'Mo',
    'cal_tuesday'                  => 'Tu',
    'cal_wednesday'                => 'We',
    'cal_thursday'                 => 'Th',
    'cal_friday'                   => 'Fr',
    'cal_saturday'                 => 'Sa',
    'app_bugs'                     => 'Bugs',
    'wrong_profile_image'          => 'A imagem do perfil não foi atualizada. Formato incorreto.',
    'wrong_image_size'             => 'O tamanho da imagem é maior que o tamanho de upload permitido.',
);


/*
 * ###############################################
 * BEGIN INTERFACE RELATED CODE
 * ###############################################
 */


if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_ADMIN) {
    /**
     * Admin Area Navigation Bar
     */

    $_adminBarContainer = array(

        14 => array('Definições', 'bar_settings.gif', APP_CORE, '/Base/Settings/Index'),
        26 => array('REST API', 'bar_restapi.gif', APP_BASE),
        27 => array('Gerador de marcas', 'bar_tag.gif', APP_LIVECHAT, '/Base/TagGenerator/Index'),
        0  => array('Modelos', 'bar_templates.gif', APP_BASE),
        1  => array('Idiomas', 'bar_languages.gif', APP_CORE),
        2  => array('Campos personalizados', 'bar_customfields.gif', APP_CORE),
        25 => array('GeoIP', 'bar_geoip.gif', APP_CORE, '/Base/GeoIP/Manage'),
        13 => array('Suporte em tempo real', 'bar_livesupport.gif', APP_LIVECHAT),
        4  => array('Analisador de email', 'bar_mailparser.gif', APP_PARSER),
        5  => array('Pedidos de suporte', 'bar_tickets.gif', APP_TICKETS),
        35 => array ('Consentimento do usuário', 'bar_maintenance.gif', APP_BASE, '/Base/Consent/Index'),
        29 => array('Fluxo de trabalho', 'bar_workflow.gif', APP_TICKETS, '/Tickets/Workflow/Manage'),
        30 => array('Classificações', 'bar_ratings.gif', APP_TICKETS, '/Base/Rating/Manage'),
        6  => array('SLA', 'bar_sla.gif', APP_TICKETS),
        7  => array('Escalonamentos', 'bar_escalations.gif', APP_TICKETS, '/Tickets/Escalation/Manage'),
        20 => array('Bayesiana', 'bar_bayesian.gif', APP_TICKETS),
        21 => array('Base de dados de conhecimento', 'bar_knowledgebase.gif', APP_KNOWLEDGEBASE),
        23 => array('Notícias', 'bar_news.gif', APP_NEWS),
        24 => array('Resolução de problemas', 'bar_troubleshooter.gif', APP_TROUBLESHOOTER),
        31 => array('Miniaplicações', 'bar_widgets.gif', APP_BASE, '/Base/Widget/Manage'),
        32 => array('Aplicações', 'bar_apps.gif', APP_BASE, '/Base/App/Manage'),
        9  => array('Registos', 'bar_logs.gif', APP_BASE),
        10 => array('Tarefas agendadas', 'bar_cron.gif', APP_BASE),
        11 => array('Base de dados', 'bar_database.gif', APP_BASE),
        33 => array('Importar', 'bar_import.gif', APP_BASE),
        12 => array('Diagnóstico', 'bar_diagnostics.gif', APP_BASE),
        34 => array('Manutenção', 'bar_maintenance.gif', APP_BASE),
    );

    SWIFT::Set('adminbar', $_adminBarContainer);

    $_adminBarItemContainer = array(
        0  => array(
            0 => array('Grupos', '/Base/TemplateGroup/Manage'),
            1 => array('Modelos', '/Base/Template/Manage'),
            2 => array('Pesquisar', '/Base/TemplateSearch/Index'),
            3 => array('Importar/Exportar', '/Base/TemplateManager/ImpEx'),
            4 => array('Restaurar', '/Base/TemplateRestore/Index'),
            5 => array('Diagnóstico', '/Base/TemplateDiagnostics/Index'),
            6 => array('Logótipos do cabeçalho', '/Base/TemplateManager/Personalize'),
        ),

        1  => array(
            0 => array('Idiomas', '/Base/Language/Manage'),
            1 => array('Frases', '/Base/LanguagePhrase/Manage'),
            2 => array('Pesquisar', '/Base/LanguagePhrase/Search'),
            3 => array('Importar/Exportar', '/Base/LanguageManager/ImpEx'),
            4 => array('Restaurar', '/Base/LanguageManager/Restore'),
            5 => array('Diagnóstico', '/Base/LanguageManager/Diagnostics'),
        ),

        2  => array(
            0 => array('Grupos', '/Base/CustomFieldGroup/Manage'),
            1 => array('Campos', '/Base/CustomField/Manage'),
        ),

        4  => array(
            0 => array('Definições', '/Parser/SettingsManager/Index'),
            1 => array('Fila de email', '/Parser/EmailQueue/Manage'),
            2 => array('Regras', '/Parser/Rule/Manage'),
            3 => array('Breaklines', '/Parser/Breakline/Manage'),
            4 => array('Bans', '/Parser/Ban/Manage'),
            5 => array('Regras catch-all', '/Parser/CatchAll/Manage'),
            6 => array('Bloqueios de ciclo', '/Parser/LoopBlock/Manage'),
            7 => array('Regras do bloqueador de ciclo', '/Parser/LoopRule/Manage'),
            9 => array('Registo do analisador', '/Parser/ParserLog/Manage'),
        ),

        5  => array(
            0 => array('Definições', '/Tickets/SettingsManager/Index'),
            1 => array('Tipos', '/Tickets/Type/Manage'),
            2 => array('Estados', '/Tickets/Status/Manage'),
            3 => array('Prioridades', '/Tickets/Priority/Manage'),
            4 => array('Tipos de ficheiro', '/Tickets/FileType/Manage'),
            5 => array('Ligações', '/Tickets/Link/Manage'),
            8 => array('Fecho automático', '/Tickets/AutoClose/Manage'),
            7 => array('Maintenance', '/Tickets/Maintenance/Index'),
        ),

        6  => array(
            0 => array('Definições', '/Tickets/SettingsManager/SLA'),
            1 => array('Planos', '/Tickets/SLA/Manage'),
            2 => array('Agendamentos', '/Tickets/Schedule/Manage'),
            3 => array('Feriados', '/Tickets/Holiday/Manage'),
            4 => array('Import/Export', '/Tickets/HolidayManager/Index'),
        ),

        20 => array(
            0 => array('Definições', '/Tickets/SettingsManager/Bayesian'),
            1 => array('Categorias', '/Tickets/BayesianCategory/Manage'),
            2 => array('Diagnostics', '/Tickets/BayesianDiagnostics/Index'),
        ),

        9  => array(
            0 => array('Registo de erros', '/Base/ErrorLog/Manage'),
            1 => array('Registo de tarefas', '/Base/ScheduledTasks/TaskLog'),
            3 => array('Registo de atividades', '/Base/ActivityLog/Manage'),
            4 => array('Registo de início de sessão', '/Base/LoginLog/Manage'),
        ),

        10 => array(
            0 => array('Gerir', '/Base/ScheduledTasks/Manage'),
            1 => array('Registo de tarefas', '/Base/ScheduledTasks/TaskLog'),
        ),

        11 => array(
            0 => array('Informações da tabela', '/Base/Database/TableInfo'),
        ),

        12 => array(
            0 => array('Sessões ativas', '/Base/Diagnostics/ActiveSessions'),
            1 => array('Informações de cache', '/Base/Diagnostics/CacheInformation'),
            2 => array('Reconstruir cache', '/Base/Diagnostics/RebuildCache'),
            3 => array('Informações do PHP', '/Base/Diagnostics/PHPInfo'),
            4 => array('Reportar problema', '/Base/Diagnostics/ReportBug'),
            5 => array('Informações sobre a licença', '/Base/Diagnostics/LicenseInformation'),
        ),

        13 => array(
            0 => array('Settings', '/LiveChat/SettingsManager/Index'),
            1 => array('Regras do visitante', '/LiveChat/Rule/Manage'),
            2 => array('Grupos de visitantes', '/LiveChat/Group/Manage'),
            3 => array('Competências do pessoal', '/LiveChat/Skill/Manage'),
            4 => array('Banimentos de visitantes', '/LiveChat/Ban/Manage'),
            5 => array('Encaminhamento de mensagens', '/LiveChat/MessageRouting/Index'),
            6 => array('Estado do pessoal', '/LiveChat/OnlineStatus/Index'),
        ),

        19 => array(
            0 => array('Definições', '/Manuals/SettingsManager/Index'),
        ),

        21 => array(
            0 => array('Definições', '/KnowledgeBase/SettingsManager/Index'),
            1 => array('Maintenance', '/KnowledgeBase/Maintenance/Index'),
        ),

        22 => array(
            0 => array('Definições', '/Downloads/SettingsManager/Index'),
        ),

        23 => array(
            0 => array('Definições', '/News/SettingsManager/Index'),
            1 => array('Importar/Exportar', '/News/ImpEx/Manage'),
        ),

        24 => array(
            0 => array('Definições', '/Troubleshooter/SettingsManager/Index'),
        ),

        25 => array(
            0 => array('Manutenção', '/Base/GeoIP/Manage'),
        ),

        26 => array(
            0 => array('Definições', '/Base/Settings/RESTAPI'),
            1 => array('Informações de API', '/Base/RESTAPI/Index'),
        ),

        33 => array(
            0 => array('Manage', '/Base/Import/Manage'),
            1 => array('Registo de importações', '/Base/ImportLog/Manage'),
        ),

        34 => array(
            0 => array('Remover anexos', '/Base/PurgeAttachments/Index'),
            1 => array('Mover anexos', '/Base/MoveAttachments/Index'),
        ),

    );

    // Log stuff
    if (SWIFT_PRODUCT == 'Fusion' || SWIFT_PRODUCT == 'Resolve' || SWIFT_PRODUCT == 'Case') {
        $_adminBarItemContainer[9][2] = array('Registo do analisador', '/Parser/ParserLog/Manage');
    }

    if (SWIFT_PRODUCT == 'Fusion' || SWIFT_PRODUCT == 'Engage') {
        unset($_adminBarContainer[27]);
    }

    SWIFT::Set('adminbaritems', $_adminBarItemContainer);


    /**
     * Admin Area Menu Links
     * Translate the Highlighted Text: 0 => array (>>>'Home'<<<, 100, APP_NAME),
     * ! IMPORTANT ! The following array does NOT have a Zero based index
     */

    $_adminMenuContainer = array(

        1 => array('Página inicial', 80, APP_CORE),
        2 => array('Pessoal', 100, APP_BASE),
        3 => array('Departamentos', 120, APP_BASE),
        4 => array('Utilizadores', 100, APP_BASE),
    );

    SWIFT::Set('adminmenu', $_adminMenuContainer);

    /**
     * Item Format Example: 0 => array ('Name Of Item to Display', 'www.link-to-item.com', PREFIX_SPACING, SUFFIX_BAR_AND_SPACING),
     * The PREFIX_SPACING and SUFFIX_BAR_AND_SPACING are required for the theme to display the separator items for the menu items
     */
    $_adminLinkContainer = array(

        1 => array(
            0 => array('Página inicial', '/Base/Home/Index'),
            1 => array('Definições', '/Base/Settings/Index'),
        ),

        2 => array(
            0 => array('Gerir pessoal', '/Base/Staff/Manage'),
            1 => array('Gerir equipas', '/Base/StaffGroup/Manage'),
            2 => array('Inserir pessoal', '/Base/Staff/Insert'),
            3 => array('Inserir equipas', '/Base/StaffGroup/Insert'),
            4 => array('LoginShare', '/Base/Settings/StaffLoginShare'),
            5 => array('Definições', '/Base/Settings/Staff'),
        ),

        3 => array(
            0 => array('Gerir departamentos', '/Base/Department/Manage'),
            1 => array('Inserir departamento', '/Base/Department/Insert'),
            2 => array('Descrição geral de acesso', '/Base/Department/AccessOverview'),
        ),

        4 => array(
            0 => array('Gerir grupos de utilizadores', '/Base/UserGroup/Manage'),
            1 => array('Inserir grupos de utilizadores', '/Base/UserGroup/Insert'),
            2 => array('LoginShare', '/Base/Settings/UserLoginShare'),
            3 => array('Definições', '/Base/Settings/User'),
        ),
    );

    SWIFT::Set('adminlinks', $_adminLinkContainer);
} else if ($_SWIFT->Interface->GetInterface() == SWIFT_Interface::INTERFACE_STAFF) {
    /**
     * Staff Area Menu Links
     * Translate the Highlighted Text: 0 => array (>>>'Home'<<<, 100),
     * ! IMPORTANT ! The following array does NOT have a Zero based index
     */

    $_staffMenuContainer = array(
        1 => array('Página inicial', 80, APP_CORE),
        2 => array('Gerir pedidos de suporte', 100, APP_TICKETS, 't_entab'),
        3 => array('Suporte em tempo real', 120, APP_LIVECHAT, 'ls_entab'),
        4 => array('Base de dados de conhecimento', 140, APP_KNOWLEDGEBASE, 'kb_entab'),
        6 => array('Resolução de problemas', 140, APP_TROUBLESHOOTER, 'tr_entab'),
        7 => array('Notícias', 90, APP_NEWS, 'nw_entab'),
        8 => array('Utilizadores', 90, APP_CORE, 'cu_entab'),
        9 => array('Relatórios', 90, APP_REPORTS, 'rp_entab'),
    );

    SWIFT::Set('staffmenu', $_staffMenuContainer);

    /**
     * Item Format Example: 0 => array ('Name Of Item to Display', 'www.link-to-item.com', PREFIX_SPACING, SUFFIX_BAR_AND_SPACING),
     * The PREFIX_SPACING and SUFFIX_BAR_AND_SPACING are required for the theme to display the separator items for the menu items
     */
    $_staffLinkContainer = array(

        1 => array(
            0 => array('Página inicial', '/Base/Home/Index'),
            1 => array('As minhas preferências', '/Base/Preferences/ViewPreferences'),
            2 => array('Notificações', '/Base/Notification/Manage', 'staff_canviewnotifications'),
            3 => array('Comentários', '/Base/Comment/Manage', 'staff_canviewcomments'),
        ),

        2 => array(
            0 => array('Gerir pedidos de suporte', '/Tickets/Manage/Index', 'staff_tcanviewtickets'),
            1 => array('Pesquisar', ':UIDropDown(\'ticketsearchmenu\', event, \'linkmenu2_1\', \'linksdiv\'); LinkTicketSearchForms();'),
            2 => array(
                'Novo pedido de suporte', ':UICreateWindow(\'/Tickets/Ticket/NewTicket/\', \'newticket\', \'New Ticket\', \'Loading..\', 500, 350, true);', 'staff_tcaninsertticket'
            ),
            3 => array('Macros', '/Tickets/MacroCategory/Manage', 'staff_tcanviewmacro'),
            4 => array('Vistas', '/Tickets/View/Manage', 'staff_tcanview_views'),
            5 => array('Filtros', ':UIDropDown(\'ticketfiltermenu\', event, \'linkmenu2_5\', \'linksdiv\');'),
        ),

        3 => array(
            0 => array('Histórico de conversação', '/LiveChat/ChatHistory/Manage', 'staff_lscanviewchat'),
            1 => array('Mensagens/inquéritos', '/LiveChat/Message/Manage', 'staff_lscanviewmessages'),
            2 => array('Registos de chamadas', '/LiveChat/Call/Manage', 'staff_lscanviewcalls'),
            3 => array('Respostas pré-configuradas', '/LiveChat/CannedCategory/Manage', 'admin_lscanviewcanned'),
            4 => array('Pesquisar', ':UIDropDown(\'chatsearchmenu\', event, \'linkmenu3_4\', \'linksdiv\'); LinkChatSearchForms();'),
        ),

        4 => array(
            0 => array('Ver base de dados de conhecimento', '/Knowledgebase/ViewKnowledgebase/Index'),
            1 => array('Gerir base de dados de conhecimento', '/Knowledgebase/Article/Manage'),
            2 => array('Categorias', '/Knowledgebase/Category/Manage'),
            3 => array('Novo artigo', '/Knowledgebase/Article/Insert'),
        ),

        5 => array(
            0 => array('View Downloads', '/Downloads/Downloads/Manage'),
            1 => array('Manage Downloads', '/Downloads/Downloads/Manage'),
            2 => array('Categories', '/Downloads/Category/Insert'),
            3 => array('New File', '/Downloads/File/Insert'),
        ),

        6 => array(
            0 => array('Ver resolução de problemas', '/Troubleshooter/Category/ViewAll'),
            1 => array('Gerir resolução de problemas', '/Troubleshooter/Step/Manage'),
            2 => array('Categorias', '/Troubleshooter/Category/Manage'),
            3 => array('Novo passo', ':UICreateWindow(\'/Troubleshooter/Step/InsertDialog/\', \'newstep\', \'Insert Step\', \'Loading..\', 400, 200, true);'),
        ),

        7 => array(
            0 => array('Ver notícias', '/News/NewsItem/ViewAll', 'staff_nwcanviewitems'),
            1 => array('Gerir notícias', '/News/NewsItem/Manage', 'staff_nwcanmanageitems'),
            2 => array('Categorias', '/News/Category/Manage', 'staff_nwcanviewcategories'),
            3 => array('Subscritores', '/News/Subscriber/Manage', 'staff_nwcanviewsubscribers'),
            4 => array('Inserir notícias', ':UICreateWindow(\'/News/NewsItem/InsertDialog/\', \'newnews\', \'Insert News\', \'Loading..\', 600, 420, true);'),
        ),

        8 => array(
            0 => array('Gerir utilizadores', '/Base/User/Manage', 'staff_canviewusers'),
            1 => array('Gerir organizações', '/Base/UserOrganization/Manage', 'staff_canviewuserorganizations'),
            2 => array('Pesquisar', ':UIDropDown(\'usersearchmenu\', event, \'linkmenu8_2\', \'linksdiv\'); LinkUserSearchForms();'),
            3 => array('Inserir utilizador', '/Base/User/Insert', 'staff_caninsertuser'),
            4 => array('Inserir organizações', '/Base/UserOrganization/Insert', 'staff_caninsertuserorganization'),
            5 => array ('Import Users', '/Base/User/ImportCSV', 'staff_caninsertuser'),
        ),

        9 => array(
            0 => array('Gerir relatórios', '/Reports/Report/Manage'),
            1 => array('Categorias', '/Reports/Category/Manage'),
            2 => array('Novo relatório', ':UICreateWindow(\'/Reports/Report/InsertDialog/\', \'newreport\', \'New Report\', \'Loading..\', 400, 280, true);'),
        ),
    );

    $_staffLinkContainer[2][1][15] = true;
    $_staffLinkContainer[2][5][15] = true;
    $_staffLinkContainer[8][2][15] = true;
    $_staffLinkContainer[3][4][15] = true;

    SWIFT::Set('stafflinks', $_staffLinkContainer);
}




return $__LANG;
