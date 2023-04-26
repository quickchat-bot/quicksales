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
    'insertbreakline'                           => 'Добавить разделитель новой строки',
    'editbreakline'                             => 'Редактировать разделитель новой строки',
    'emailqueues'                               => 'Очереди ожидания email',
    'winviewparserlog'                          => 'Просмотреть журнал парсера',
    'close'                                     => 'Закрыть',
    'titleparserlogdel'                         => 'Удалено "%d" записей в журнале парсера',
    'msgparserlogdel'                           => 'Следующие записи в журнале парсера были успешно удалены из базы данных:',

    'breaklines'                                => 'Разделители новой строки',
    'desc_breaklines'                           => '',
    'sortorder'                                 => 'Порядок сортировки',
    'desc_sortorder'                            => 'Укажите порядок сортировки разделителя новой строки.',
    'wineditbreakline'                          => 'Редактировать разделитель новой строки',
    'breaklinetitle'                            => 'Текст разделителя новой строки',
    'desc_breaklinetitle'                       => 'Введите текст разделителя новой строки, все входящие электронные письма будут сокращены до линии, которая содержит текст разделителя новой строки.',
    'isregexp'                                  => 'Регулярное выражение?',
    'desc_isregexp'                             => 'Если эта опция включена, текст разделителя новой строки вверху воспринимается системой как регулярное выражение.',
    'titleinsertbreakline'                      => 'Добавлен разделитель новой строки',
    'msginsertbreakline'                        => 'Разделитель новой строки парсера был успешно добавлен в базу данных.',
    'titleupdatebreakline'                      => 'Обновлен разделитель новой строки',
    'msgupdatebreakline'                        => 'Разделитель новой строки парсера успешно обновлен.',
    'titledelbreakline'                         => 'Удалено "%d" разделителей новой строки',
    'msgdelbreakline'                           => 'Следующие разделители новой строки  были успешно удалены из базы данных:',
    'invalidbreakline'                          => 'Указан неверный разделитель новой строки. Пожалуйста, убедитесь, что такой разделитель новой строки существует в базе данных, и что база данных не повреждена.',

    'tabsettings'                               => 'Настройки',
    'forcequeue'                                => 'Создать очередь входящей почты',
    'desc_forcequeue'                           => 'Если эта опция включена, система будет отображать все сообщения в почтовом ящике в том же порядке, в котором они приходили. Если Вы хотете использовать эту очередность для сбора пересылаемых писем, рекомедуется отключить эту опцию.',
    'leavecopyonserver'                         => 'Сохранить копию на сервере?',
    'desc_leavecopyonserver'                    => 'Если эта опция включена, система сохранит копию сообщения на сервере электронной почте. По умолчанию система очищает почтовый ящик каждый раз, когда процесс выборки завершен.<br /><b><font color="red">ПРИМЕЧАНИЕ:</font></b> Эта опция применима только к почтовым ящикам типа IMAP.',
    'nonssl'                                    => 'Без SSL',
    'tls'                                       => 'TLS',
    'ssl'                                       => 'SSL',
    'sslv2'                                     => 'SSL v2',
    'sslv3'                                     => 'SSL v3',
    'usequeuesmtp'                              => 'Использовать SMTP-сервер',
    'desc_usequeuesmtp'                         => 'Если эта опция включена, для отправки всех писем, которые относятся к данной очереди, будет использоваться SMTP-сервер. Благодаря этому почта не воспринимается системой как спам.',
    'smtptype'                                  => 'Тип SMTP',
    'desc_smtptype'                             => 'Выберите тип SMTP-сервера.',
    'next'                                      => 'Далее',
    'back'                                      => 'Назад',
    'wineditemailqueue'                         => 'Редактировать очередь электронной почты',
    'tabticketsettings'                         => 'Параметры заявки',
    'tabpop3imap'                               => 'POP3/IMAP',
    'queueisenabled'                            => 'Включено?',
    'desc_queueisenabled'                       => 'Если эта опция включена, очередь будет применяться ко всем входящим сообщениям, которые отправляются по указанному email адресу. В результате отключения очереди произойдет рассогласование всех входящих сообщений типа pipe или pop3.',
    'titlequeueinsert'                          => 'Добавлена очередь Email',
    'msgqueueinsert'                            => 'Очередь email "%s" успешно добавлена в базу данных.',
    'titlequeueupdate'                          => 'Обновлена очередь Email',
    'msgqueueupdate'                            => 'Очередь email "%s" была успешно обновлена.',
    'titledelqueues'                            => 'Удалено "%d" очередей Email',
    'msgdelqueues'                              => 'Следующие очереди email были успешно удалены из базы данных:',
    'titlestaffemail'                           => 'Неверный адрес Email',
    'msgstaffemail'                             => 'QuickSupport не может добавить/обновить адрес очереди email, так как указанный email адрес также связан с "%s" сотрудником. Эта проверка была добавлена с целью избежать возникновения замкнутых петель email.',
    'verifyconnection'                          => 'Подтвердить связь',
    'vcvariablesanity'                          => 'Выполняется проверка информации об очереди.',
    'vcimapnotcompiled'                         => 'Ваш PHP не имеет поддержки IMAP. *nix пользователи должны перекомпилировать их PHP с помощью \'--with-imap\' флага; пользователи Windows могут лишь сделать комментарий в командной строке =\'php_imap.dll\' в их php.ini',
    'vcimapcompiled'                            => 'PHP имеет поддержку IMAP',
    'vcattemptconnection'                       => 'Устанавливается соединение с сервером POP3/IMAP',
    'vcconnectionsuccess'                       => 'Соединение установлено',
    'vctotalmessages'                           => 'Всего сообщений в почтовом ящике: %d',
    'vcconnectionfailed'                        => 'Не удалось установить соединение с почтовым ящиком:<BR />%s',
    'erremailqueuesame'                         => 'Email отклонен, так как главный email адрес добавлен как email очередь',

    'errloopcontrol'                            => 'Сообщение заблокировано правилом циклического запрета.',
    'errtoobig'                                 => 'The message exceeded the maximum message size of %s (the message was %s)',

    'mailparser'                                => 'Парсер почты',
    'desc_mailparser'                           => '',
    'queuelist'                                 => 'Список очередей Email',

    'insertemailqueue'                          => 'Добавить очередь Email',
    'insertqueue'                               => 'Добавить очередь',
    'queuenextstep'                             => 'Далее &raquo;',
    'emailgeneralfields'                        => 'Поля Email',
    'emailimapfields'                           => 'Информация доступа (необходимо, только если выбрано POP3/IMAP)',
    'emailaddress'                              => 'Адреса очереди Email',
    'desc_emailaddress'                         => 'Введите верный email адрес для создания очереди. Пример: support@domain.com',
    'emailtype'                                 => 'Тип очереди',
    'desc_emailtype'                            => '',
    'emailfetchtype'                            => 'Тип выборки',
    'desc_emailfetchtype'                       => 'Укажите, как парсер почты должен производить выборку новых писем. Для всех опций, кроме <i>Pipe</i>, необходимо указать имя хост-системы, имя пользователя и пароль.',
    'fetchpipe'                                 => 'Pipe',
    'fetchpop3'                                 => 'POP3',
    'fetchpop3ssl'                              => 'POP3 SSL',
    'fetchimap'                                 => 'IMAP',
    'fetchimapssl'                              => 'IMAP SSL',
    'emailhost'                                 => 'Хост-система',
    'desc_emailhost'                            => 'Введите название основного сервера. Это может быть IP адрес или название хостинга, такое как <i>mail.domain.com</i>.',
    'emailport'                                 => 'Порт',
    'desc_emailport'                            => 'Укажите порт почтового сервера. Рекомендуется не изменять эти данные, пока почтовый сервер работает на отдельном порту, в отличии от сервера по умолчанию.',
    'emailusername'                             => 'Имя пользователя',
    'desc_emailusername'                        => 'Укажите имя пользователя почтового сервера.',
    'emailpassword'                             => 'Пароль',
    'desc_emailpassword'                        => 'Введите пароль потового сервера.',

    'queueoverrides'                            => 'Отмена очереди(не обязательно)',
    'queuefromname'                             => 'От имени',
    'desc_queuefromname'                        => 'Если выбрана эта опция, у всех исходящих сообщения из этой очереди в строке <i>От имени</i> будет указано это имя.',
    'queuefromemail'                            => 'С адреса',
    'desc_queuefromemail'                       => 'Если выбрана эта опция, у всех исходящих сообщения из этой очереди в строке <i>С адреса</i> будет указан этот адрес.',

    'queuesettings'                             => 'Настройки очереди',
    'templategroup'                             => 'Группа шаблонов очереди',
    'desc_templategroup'                        => 'Выберите группу шаблонов очереди. Настройки и шаблоны с этой группы шаблонов используются для только что созданных очередей email.',
    'queuesignature'                            => 'Подпись очереди (не обязательно)',
    'desc_queuesignature'                       => 'Укажите подпись очереди. Эта подпись будет отображаться во всех исходящих письмах этой очереди.',
    'registrationrequired'                      => 'Необходима регистрация?',
    'desc_registrationrequired'                 => 'Если эта опция включена, только <b>зарегистрированные </b> пользователи смогут создавать новые заявки, отправляя сообщение на этот email и очередь. Незарегистрированные пользователи смогут отправлять заявки с центра поддержки клиентов.',
    'issueautoresponder'                        => 'Активировать автоответчик для новых проблем?',
    'desc_issueautoresponder'                   => 'Если эта опция включена, система будет отправлять автоматическое сообщение, подтверждающее регистрацию новой проблемы.',
    'replyautoresponder'                        => 'Активировать автоответчик для новых ответов?',
    'desc_replyautoresponder'                   => 'Если эта опция включена, система будет отправлять автоматическое сообщение, подтверждающее регистрацию нового ответа в уже существующей проблеме.',

    'ticketfields'                              => 'Поля заявки',
    'queuedepartment'                           => 'Департамент',
    'desc_queuedepartment'                      => 'Укажите департамент, в котором будет создана заявка.',
    'queuetickettype'                           => 'Тип заявки',
    'desc_queuetickettype'                      => 'Выберите тип заявки для этой очереди. Любая заявка, созданная в этой очереди ожидания, будет отнесена к указанному типу заявок.',
    'queueticketstatus'                         => 'Статус заявки',
    'desc_queueticketstatus'                    => 'Выберите статус заявки для этой очереди. Все заявки, созданные в этой очереди ожидания, будут иметь данный статус.',
    'queuepriority'                             => 'Приоритет заявки',
    'desc_queuepriority'                        => 'Выберите приоритет заявки для этой очереди. Это не относится к "приоритету" входящей почты. Все заявки, созданные в этой очереди ожидания, будут иметь указанный приоритет.',
    'editemailqueue'                            => 'Редактировать очередь ожидания email',
    'emailqueues'                               => 'Очереди ожидания email',


    'queueprefix'                               => 'Префикс темы очереди ожидания (не обязательно)',
    'desc_queueprefix'                          => 'Если эта опция включена, все темы писем в этой очереди ожидания будут иметь префикс с указанным значением. Например, если вы укажите <i>SUPPORT</i> префикс, в теме исходящего письма-ответа сотрудника будет: <i>[SUPPORT #ABC-12345]: Actual Subject</i>',
    'titleinvalidqueueprefix'                   => 'Неверный префикс очереди',
    'msginvalidqueueprefix'                     => 'Пожалуйста, укажите верный префикс очереди писем. Используйте только буквенно-цифровые символы.',

    'titleenablequeues'                         => 'Активировано "%d" очередей ожидания email',
    'msgenablequeues'                           => 'QuickSupport успешно активировал следующие очереди ожидания email:',
    'titledisablequeues'                        => 'Деактивировано "%d" очередей ожидания email',
    'msgdisablequeues'                          => 'QuickSupport успешно деактивировал следующие очереди ожидания email:',

    // View Parser Log
    'viewparserlog'                             => 'Журнал парсера',
    'generalinformation'                        => 'Общая информация',
    'mimedata'                                  => 'MIME данные',
    'ppticketid'                                => 'ID заявки: ',
    'ppticketmaskid'                            => 'ID маски заявки: ',
    'ppdate'                                    => 'Дата: ',
    'pptimeline'                                => 'График: ',
    'ppemailqueue'                              => 'Очередь ожидания email: ',
    'ppstatus'                                  => 'Статус процесса: ',
    'ppsubject'                                 => 'Тема: ',
    'ppfromemail'                               => 'С Email: ',
    'pptoemail'                                 => 'На Email: ',
    'ppsize'                                    => 'Размер: ',
    'pptimetaken'                               => 'Фактическое время: ',
    'ppdesc'                                    => 'Описание: ',
    'ppresult'                                  => 'Результат',
    'pparserlogid'                              => 'ID журнала парсера: ',
    'parserlog'                                 => 'Журнал парсера',
    'emailsubject'                              => 'Тема Email',
    'emailsubjectresult'                        => 'Тема и результат Email',
    'emailsubresultformat'                      => '%s<BR /><B>Результат:</B> %s',
    'emailparsetime'                            => 'Время парсинга',
    'emaillogtype'                                 => 'Log Type',
    'emailto'                                   => '"Кому:" Email',
    'nosubject'                                 => '(Без темы)',

    // Parser Errors
    'errnoqueues'                               => 'Назначенные очереди ожидания для получателей почты не найдены',
    'errfloodprotection'                        => 'Включена защита от флуда',
    'scccreatedticket'                          => 'Отправлена заявка #%s',
    'scccreatedreply'                           => 'Отправлен ответ #%s на заявку #%s',
    'sctcreatedreply'                           => 'Отправлен ответ от третьего лица #%s на заявку #%s',
    'scccreatedstaffreply'                      => 'Отправлен ответ сотрудника #%s на заявку #%s',
    'errusernotreg'                             => 'Пользователь %s не зарегистрирован. В соответствии с настройками очереди ожидания, пользователь должен быть зарегистрированным.',
    'erremailbanned'                            => 'Email адрес пользователя заблокирован',
    'actionrepprefix'                           => 'В ответ на: ',

    // Misc
    'reprocessemail'                            => 'Перепарсить Email',

    // Loop Cutter
    'pr_mangelooprules'                         => 'Циклические правила',
    'pr_manageloopblockages'                    => 'Циклические запреты',
    'pr_loopblockages'                          => 'Циклические запреты',
    'pr_insert_new_loopcutter_rule_title'       => 'Добавить правило управления циклом',
    'pr_edit_new_loopcutter_rule_title'         => 'Редактировать правило управления циклом',
    'pr_threshhold_grid_timeframe_title'        => 'Время',
    'pr_threshhold_grid_maxhits_title'          => 'Максимальное количество попыток',
    'pr_threshhold_grid_restoreafter_title'     => 'Восстановить после',
    'pr_threshhold_grid_address_title'          => 'Адрес',
    'pr_newloopcontrolwatchlength_title'        => 'Контрольное время',
    'pr_newloopcontrolwatchlength_desc'         => 'Это время действия циклического запрета, в секундах.',
    'pr_newloopcontrolmaxcontacts_title'        => 'Максимальное количество email',
    'pr_newloopcontrolmaxcontacts_desc'         => 'Это количество отправленных email, при достижении которого наступает циклический запрет.',
    'pr_newloopcontrolrestoreafter_title'       => 'Восстановить после',
    'pr_newloopcontrolrestoreafter_desc'        => 'Это время, по истечении которого действие циклического запрета автоматически отключается в секундах.',
    'wineditloopcutterrule'                     => 'Редактировать правило прекращения цикла',
    'pr_threshhold_grid_title'                  => 'Название правила',
    'thresholdruletitle'                        => 'Название циклического правила',
    'desc_thresholdruletitle'                   => 'Укажите название циклического правила',
    'titledelloopblock'                         => 'Удалено "%d" циклических запретов',
    'msgdelloopblock'                           => 'Следующие циклические запреты были успешно удалены из базы данных:',
    'titledellooprule'                          => 'Удалено "%d" циклических правил',
    'msgdellooprule'                            => 'Следующие правила были успешно удалены из базы данных:',
    'titleinsertlooprule'                       => 'Добавлено циклическое правило',
    'msginsertlooprule'                         => 'Циклическое правило было успешно добавлено в базу данных:',
    'titleupdatelooprule'                       => 'Циклическое правило обновлено',
    'msgupdatelooprule'                         => 'Правило блокирования цикла успешно обновлено:',
    'titlelooprulemasterdel'                    => 'Невозможно удалить основные циклические правила',
    'msglooprulemasterdel'                      => 'QuickSupport не может удалить следующие основные циклические правила:',

    // Potentialy unused phrases in emailparser.php
    'titlefailedtocreatequeue'                  => 'Failed to create queue',
    'msgfailedtocreatequeue'                    => 'Unable to create a queue. Please check your database configuration and connectivity settings.',
    'queue_id_label'                            => 'ID',
    'managemailqueue'                           => 'Manage Email Queues',
    'invalidqueueaddress'                       => 'You must enter a valid email address for the queue.',
    'fetchpop3tls'                              => 'POP3 TLS',
    'fetchimaptls'                              => 'IMAP TLS',
    'errorlockfilefound'                        => '[ERROR]: Lock file found! (./files/parser.lockfile). A lock file has been found preventing further execution. If it is from a stale instance of cron then please delete the file in order to continue. To remove the file manually <a href="%s">click here</a>',
    'lockfileexpired'                           => '[WARNING]: Expired lock file (./files/parser.lockfile) from previous instance of cron found; automatically deleting...',
    'autorespondernotsent'                      => ' (Autoresponder not sent due to settings in effect)',
    'queuedetails'                              => 'Queue Details',
    'failedtocreatequeue'                       => 'ERROR: Failed to create email queue',
    'redirect_queueinsert'                      => 'Email queue inserted successfully.',
    'queueinsertconfirm'                        => 'Email queue "%s" inserted successfully',
    'depdeleteconfirm'                          => 'Email queue "%s" deleted successfully',
    'invalidemailqueue'                         => 'Invalid email queue specified',
    'queueupdconfirm'                           => 'Email queue "%s" updated successfully',
    'invalidparserlog'                          => 'Invalid parser log entry. Make sure that the parser log entry exists in the database and has not been deleted.',
    'desc_parserlog'                            => 'The mail parser logs all incoming email under the log with useful information to help debug any problems. To disable the logging of email or change other settings, navigate to <i>Settings > Mail Parser</i>.',
    'invalidqueueprefix'                        => 'Invalid queue prefix; only alphanumeric characters (e.g. A-Z, 0-9) and spaces are allowed.',
    'parserlogentry'                            => 'Parser Log #%s',
    'parserlogs'                                => 'Email Parser Logs',
    'emailstatus'                               => 'Status',
    'emailfrom'                                 => '"From:" Email',
    'emaildescription'                          => 'Description',
    'emailticketid'                             => 'Ticket/Bug ID',
    'emailticketmaskid'                         => 'Ticket Mask ID',
    'emailcontents'                             => 'Email Contents',
    'breaklinelist'                             => 'Breakline List',
    'breakline'                                 => 'Breakline',
    'desc_breakline'                            => 'Enter the contents of the breakline you wish to insert.<BR /><BR />Note: To use regular expressions, prepend your breakline with "regex:"<BR />Example: <i>"regex:@[-=]{4,}[^-=]+[-=]{4,}@"</i>',
    'breaklinedelconfirm'                       => 'Breakline deleted successfully',
    'breaklineinsertconfirm'                    => 'Breakline inserted successfully',
    'errnorecipients'                           => 'No recipients found',
    'errnofromemail'                            => 'No from email specified',
    'clearedduelimit'                           => '-- MIME data cleared due to the size limit under Settings > Parser --',
    'pr_loopcutter_prevents_autoresponder_desc' => ' (Autoresponder not sent due to loop control)',
    'pr_loopcontrolrules'                       => 'Loop Control Rules',
    'pr_new_loop_control_header_text'           => 'New Loop Control',
    'pr_new_loop_control_rule_added_desc'       => 'New loop control rule added.',
    'pr_desc_loopblockages_filler'              => 'Below is a list of active loop blockages. The addresses listed here triggered one or more loop control rules (<i>Mail Parser >> Manage Loop Rules</i>).  Depending on the active settings (<i>Settings >> Mail Parser >> Loop Control Settings</i>), incoming email from these addresses may be completely ignored by QuickSupport and an autoresponder message may not be sent.  These settings will be in effect for each blockage until it expires.',
    'pr_desc_looprules_filler'                  => 'Below is a list of loop control rules.  These rules prevent autoresponder loops or other email flood situations and all incoming email messages are subjected to them.  If a rule is triggered, the settings (<i>Settings >> Mail Parser >> Loop Control Settings</i>) are applied.  This allows QuickSupport to completely ignore the message and/or not reply with an autoresponder message.  Blockages created by these rules are automatically removed after the specified timeframe.',
    'pr_loop_rule_deleted'                      => 'Rule deleted.',
    'pr_loop_block_deleted'                     => 'Blockage deleted.',
    'invalidlooprule'                           => 'Invalid Loop Cutter Rule. Please make sure the record exists in the database.',
    'notapplicable'                             => '-- NA --',

);


return $__LANG;
