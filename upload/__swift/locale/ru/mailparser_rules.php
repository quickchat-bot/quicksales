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
    'isenabled'                 => 'Включено?',
    'desc_isenabled'            => 'Переключите выполнение этого правила посредством включения/выключения этой опции.',
    'insertcriteria'            => 'Добавить критерий',
    'tabactions'                => 'Действия',
    'titlenoaction'             => 'Действие не выбрано',
    'msgnoaction'               => 'Необходимо выбрать как минимум одно действие для выполнения, если этотот критерий совпадает с правилом.',
    'if'                        => 'Если',
    'titleinsertrule'           => 'Добавлено правило парсера "%s"',
    'msginsertrule'             => ' Правило парсера "%s" успешно добавлено в базу данных.',
    'titleupdaterule'           => 'Обновлено правило парсера "%s"',
    'msgupdaterule'             => 'Правило парсера "%s" успешно обновлено.',
    'titledelprules'            => 'Правила электронной почты удалены "%d"',
    'msgdelprules'              => 'Следующие правила парсера успешно удалены.',
    'titleenableprules'         => 'Активировано "%d" правил парсера',
    'msgenableprules'           => 'Kayako успешно активировал следующие правила парсера:',
    'titledisableprules'        => 'Деактивировано "%d" правил парсера',
    'msgdisableprules'          => 'Kayako успешно деактивировал следующие правила парсера:',
    // ======= END v4 LOCALES =======

    'mailparser'                => 'Парсер почты',

    'desc_parserrules'          => '',

    // Operators
    'opcontains'                => 'Содержит',
    'opnotcontains'             => 'Не содержит',
    'opequal'                   => 'Равно',
    'opnotequal'                => 'Не равно',
    'opgreater'                 => 'Больше чем',
    'opless'                    => 'Меньше чем',
    'opregexp'                  => 'Регулярное выражение',
    'strue'                     => 'Правильно',
    'sfalse'                    => 'Неправильно',

    // Criteria
    'psendername'               => 'Имя отправителя',
    'desc_psendername'          => 'Имя отправителя парсится с заголовка сообщения email "От кого:". Пример: <i>От кого: <span class="tabletitle">Имя отправителя</span> <senderemail@domain.com></i>',

    'psenderemail'              => 'Email адрес отправителя',
    'desc_psenderemail'         => 'Email адрес отправителя парсится с заголовка сообщения email "От кого:". Пример: <i>От кого: <span class="tabletitle">Имя отправителя</span> <senderemail@domain.com></i>',

    'pdestinationname'          => 'Имя получателя',
    'desc_pdestinationname'     => 'Имя получателя парсится с заголовка сообщения email "Кому:". Именем получателя чаще всего является имя сотрудника, который ответил на заявку (если другое не предусмотрено настройками). Пример: <i>Кому: <span class="tabletitle">Имя получателя</span> <destinationemail@domain.com></i>',

    'pdestinationemail'         => 'Email адрес получателя',
    'desc_pdestinationemail'    => 'Email адрес получателя парсится с заголовка сообщения email "Кому:". Именем получателя чаще всего является адрес, который относится к той очереди ожидания email, с которой был отправлен ответ (если другое не предусмотрено настройками). Пример: <i>Кому: <span class="tabletitle">Имя получателя</span> <destinationemail@domain.com></i>',

    'preplytoname'              => 'Имя Reply-To',
    'desc_preplytoname'         => 'Имя Reply-To парсится с заголовка сообщения email "Reply-To:". Пример: <i>Reply-To: <span class="tabletitle">Имя Reply-To</span> <replytoemail@domain.com></i>',

    'preplytoemail'             => 'Reply-To Email адрес',
    'desc_preplytoemail'        => 'Reply-To Email адрес парсится с заголовка сообщения email "Reply-To:". Пример: <i>Reply-To: <span class="tabletitle">Имя Reply-To</span> <replytoemail@domain.com></i>',

    'psubject'                  => 'Тема',
    'desc_psubject'             => 'Соответствует теме email.',

    'precipients'               => 'Получатели',
    'desc_precipients'          => 'Соответствует получателям email. Может быть несколько получателей, которым было отправлено входящее сообщение email. Это правило также ищет получателей, указанных в заголовках "Кому:" и "CC:" .',

    'pbody'                     => 'Текст',
    'desc_pbody'                => 'Соответствует содержимому текста email после HTML (если другое не предусмотрено настройками) и разделителей новой строки.',

    'pbodysize'                 => 'Размер текста',
    'desc_pbodysize'            => 'Общий размер содержимого сообщения в байтах.',

    'ptextbody'                 => 'Обычный текст',
    'desc_ptextbody'            => 'Содержание email сообщения может двух типов; <i>обычный текст</i>, <i>HTML</i> или <i>оба</i>. Это правило ищет только <i>обычный текст</i> в тексте сообщения. Обратите внимание на то, что не все email сообщения отправляются в <b>двух форматах</b>.',

    'phtmlbody'                 => 'HTML текст',
    'desc_phtmlbody'            => 'Содержание email сообщения может двух типов; <i>обычный текст</i>, <i>HTML</i> или <i>оба</i>. Это правило ищет только <i>обычный текст</i> в тексте сообщения. Обратите внимание на то, что не все email сообщения отправляются в <b>двух форматах</b>.',

    'ptextbodysize'             => 'Размер обычной текстовой части',
    'desc_ptextbodysize'        => 'Содержание email сообщения может двух типов; <i>обычный текст</i>, <i>HTML</i> или <i>оба</i>. Это правило соответствует общему размеру <i>обычного текста</i> в тексте сообщения email в байтах.',

    'phtmlbodysize'             => 'Размер HTML части',
    'desc_phtmlbodysize'        => 'Содержание email сообщения может двух типов; <i>обычный текст</i>, <i>HTML</i> или <i>оба</i>. Это правило соответствует общему размеру <i>HTML</i> в тесте сообщения email в байтах.',

    'pattachmentname'           => 'Название вложения',
    'desc_pattachmentname'      => 'Соответствует названиям вложений среди <i>допустимых</i> вложений. Типы допустимых вложений определены в административной панели управления в разделе <i>Парсер почты</i>.',

    'pattachmentsize'           => 'Размер вложения',
    'desc_pattachmentsize'      => 'Соответствует размеру (в байтах) любого <i>допустимого</i> вложения во входящем сообщении email. Типы допустимых вложений определены в административной панели управления в разделе <i>Парсер почты</ii>.',

    'ptotalattachmentsize'      => 'Размер всех вложений',
    'desc_ptotalattachmentsize' => 'Соответствует <b>общему</b> размеру (в байтах) любого <i>допустимого</i> вложения во входящем сообщении email. Типы допустимых вложений определены в административной панели управления в разделе <i>Парсер почты</i>.',

    'pisreply'                  => 'Ответ',
    'desc_pisreply'             => 'Если входящее сообщение отмечено как ответ на <b>существующую заявку</b>, в силу вступает это правило (становится правильным).',

    'pisthirdparty'             => 'Третье лицо',
    'desc_pisthirdparty'        => 'Если входящее сообщение отмечено как ответ на <b>существующую заявку</b> от адреса, который был добавлен как сторонний получатель, в силу вступает это правило (становится правильным).',

    'pfloodprotection'          => 'Включена защита от флуда',
    'desc_pfloodprotection'     => 'Этот критерий вступает в действие, когда на входящее сообщение Email распространяется защита от флуда.',

    'pisstaffreply'             => 'Ответ сотрудника',
    'desc_pisstaffreply'        => 'Если входящее сообщение отмечено как ответ на <b>существующую заявку</b> от адреса сотрудника, в силу вступает это правило (становится правильным).',

    'pticketstatus'             => 'Статус заявки (после парсинга)',
    'desc_pticketstatus'        => 'Если входящее сообщение парсится как ответ на существующую заявку, статус заявки будет совпадать с этим правилом.',

    'pticketemailqueue'         => 'Очередь ожидания Email',
    'desc_pticketemailqueue'    => 'Если при получении входящее сообщение определяется в определенную очередь ожидания, в силу вступает это правило.',

    'ptickettype'               => 'Тип заявки (после парсинга)',
    'desc_ptickettype'          => 'Если входящее сообщение парсится как ответ на существующую заявку, тип заявки будет совпадать с этим правилом.',

    'pticketpriority'           => 'Приоритет заявки (после парсинга)',
    'desc_pticketpriority'      => 'Если входящее сообщение парсится как ответ на существующую заявку, приоритет заявки будет совпадать с этим правилом.',

    'pticketusergroup'          => 'Группа пользователей заявки (после парсинга)',
    'desc_pticketusergroup'     => 'Если входящее сообщение парсится как ответ на существующую заявку, группа пользователей заявки будет совпадать с этим правилом.',

    'pticketdepartment'         => 'Департамент заявки (после парсинга)',
    'desc_pticketdepartment'    => 'Если входящее сообщение парсится как ответ на существующую заявку, департамент заявки будет совпадать с этим правилом.',

    'pticketowner'              => 'Владелец заявки (после парсинга)',
    'desc_pticketowner'         => 'Если входящее сообщение парсится как ответ на существующую заявку, владелец заявки будет совпадать с этим правилом.',
    'prunassigned'              => '-- Не назначенный --',

    'pticketflagtype'           => 'Флаг заявки',
    'desc_pticketflagtype'      => 'Если входящее сообщение парсится как ответ на существующую заявку, тип флага заявки будет совпадать с этим правилом.',

    'pbayescategory'            => 'Категория по Байесу',
    'desc_pbayescategory'       => 'Категория по Байесу с самой высоким  ранжированием по параметру вероятности. Вы можете использовать эту опцию как фильтр от спама или настроить систему автоматически маршрутизировать заявки с подобным содержанием.',

    // Insert Rule
    'insertrule'                => 'Добавить правило',
    'iruletype'                 => 'Тип правила: ',
    'ipreparse'                 => 'До парсинга',
    'ipostparse'                => 'После парсинга',

    'paignore'                  => 'Игнорировать сообщение Email',
    'desc_paignore'             => 'Система полностью проигнорирует сообщение email и не будет парсить его как заявку или ответ.',
    'panoautoresp'              => 'Не отправлять сообщение автоответчика',
    'desc_panoautoresp'         => 'Заявка или ответ на заявку не будет отправлен.',
    'panoalerts'                => 'Не применять правила уведомления заявок',
    'desc_panoalerts'           => 'Правила сообщений email сотрудников или SMS уведомлений не будут применены.',
    'pnochange'                 => '-- Без изменений --',
    'pcstaff'                   => 'Назначить заявку',
    'desc_pcstaff'              => 'Заявка (новая или ответ на существующую) будет назначена указанному здесь сотруднику.',
    'pcstatus'                  => 'Изменить статус заявки',
    'desc_pcstatus'             => 'Статус заявки будет изменен на указанное здесь значение.',
    'pcpriority'                => 'Изменить приоритет заявки',
    'desc_pcpriority'           => 'Приоритет заявки будет изменен на указанное здесь значение.',
    'pcdepartment'              => 'Изменить департамент заявки',
    'desc_pcdepartment'         => 'Департамент заявки будет изменен на указанное здесь значение.',
    'pcslaplan'                 => 'Изменить SLA план заявки',
    'desc_pcslaplan'            => 'Заявка будет назначен указанный здесь SLA план.',
    'pcmovetotrash'             => 'Отправить в корзину',
    'desc_pcmovetotrash'        => 'Заявка будет отправлена в корзину',
    'pcflag'                    => 'Отметить заявку',
    'desc_pcflag'               => 'Заявка будет отмечена этим цветом.',
    'paddnotes'                 => 'Добавит заметку',
    'desc_paddnotes'            => 'К заявке будет добавлена заметка.',
    'pcforward'                 => 'Переслать',
    'desc_pcforward'            => 'Email будет переслан на указанный здесь адрес.',
    'preply'                    => 'Ответить на Email',
    'desc_preply'               => 'Указанное здесь сообщение будет автоматически отправлено на этот email.',
    'panoticket'                => 'Не парсить как ответ на заявку',
    'desc_panoticket'           => 'Система отобразит этот email как новую заявку, вместо того, чтобы прикрепить к существующей.',
    'pctickettype'              => 'Изменить тип заявки',
    'desc_pctickettype'         => 'Тип заявки будет изменен на указанное здесь значение.',
    'pcaddtags'                 => 'Добавить теги',
    'desc_pcaddtags'            => 'К заявке будут добавлены указанные здесь теги.',
    'pcremovetags'              => 'Удалить теги',
    'desc_pcremovetags'         => 'С заявки будут удалены  указанные здесь теги.',
    'pcprivate'                 => 'Отметить ответ на заявку как приватный.',
    'desc_pcprivate'            => 'Ответ на заявку (только ответ сотрудника) будет отмечен как приватный.',

    // Edit Rules
    'editrule'                  => 'Редактировать правило',
    'ptitle'                    => 'Название правила',
    'desc_ptitle'               => 'Укажите название для этого правила.',
    'pstop'                     => 'Остановить применение правил',
    'desc_pstop'                => 'Если эта опция активирована, и если это правило парсинга почты встречается впервые, никакие другие правила не будут вступать в действие (даже если они совпадают с входищей почтой).',

    // Manage Rules
    'managerules'               => 'Правила',
    'ruletitle'                 => 'Название правила',
    'sortorder'                 => 'Правило выполнения',
    'desc_sortorder'            => 'Парсер почты будут выполнять правила парсинга почты в соответствии с этим значением. (Например, <i>правило "1" будет выполняться перед правилом "2" и т.п.</i>).',
    'ruletype'                  => 'Тип правила',
    'creationdate'              => 'Дата создания',

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