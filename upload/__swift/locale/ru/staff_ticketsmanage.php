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

// Initial pass done
// Usage script run

$__LANG = array(
    'manage'                          => 'Управлять',

    'tabgeneral'                      => 'Общие',
    'tabmassaction'                   => 'Массовое действие',
    'tabreply'                        => 'Ответить',
    'tabforward'                      => 'Вперед',

    /**
     * ---------------------------------------------
     * Mass actions
     * ---------------------------------------------
     */
    'manochange'                      => '-- Без изменений --',
    'maticketstatus'                  => 'Изменить статус',
    'maticketpriority'                => 'Изменить приоритет',
    'matickettype'                    => 'Изменить тип заявки',
    'madepartment'                    => 'Перейти в отдел',
    'maowner'                         => 'Назначить',
    'maticketlink'                    => 'Ссылка',
    'maaddtags'                       => 'Добавить метки',
    'maremovetags'                    => 'Удалить метки',
    'maticketflag'                    => 'Вид флага',
    'manoflag'                        => '--Нет флага--',
    'mabayescategory'                 => 'Задать тип по Байесу',
    'sactivestaff'                    => '-- Активные сотрудники --',
    'tnavnextticket'                  => 'Следующая заявка',
    'tnavprevticket'                  => 'Предыдущая заявка',
    'tagaddtorecp'                    => 'Добавить получателей',
    'massreply'                       => 'Массовый ответ',

    /**
     * ---------------------------------------------
     * Trash view
     * ---------------------------------------------
     */
    'emptytrash'                      => 'Очистить корзину',
    'emptytrashconfirm'               => 'Заявки находящиеся в корзине будут окончательно удалены. Вы хотите продолжить?',
    'putback'                         => 'Положить обратно',

    /**
     * ---------------------------------------------
     * Viewing a Ticket
     * ---------------------------------------------
     */
    'mergeoptions'                    => 'Объединить заявку',
    'mergeparentticket'               => 'ID заявки для объединения',
    'desc_mergeparentticket'          => 'ID заявки для объединения.',
    'titleeditmergefailed'            => 'Невозможно объединить заявки',
    'msgeditmergefailed'              => 'Заявки не могут быть объединены так как невозможно найти указанный ID заявки. Проверьте ID заявки и повторите попытку.',
    'titleeditmergesuccess'           => 'Заявки объединены',
    'msgeditmergesuccess'             => 'Заявки были успешно объединены.',
    'tabcalls'                        => 'Звонки',
    'dispatchasuser'                  => 'Ответить как пользователь',
    'viewticket'                      => 'Просмотреть заявку',
    'viewticketext'                   => '[#%s]: %s',
    'proptitledepartment'             => 'ДЕПАРТАМЕНТ',
    'proptitleowner'                  => 'ВЛАДЕЛЕЦ',
    'proptitlestatus'                 => 'СТАТУС',
    'proptitlepriority'               => 'ПРИОРИТЕТ',
    'proptitletype'                   => 'ТИП',
    'tinforesolutiondue'              => 'Время решения: ',
    'tinfosla'                        => 'План SLA: ',
    'tinfoticketid'                   => 'ID: ',
    'tinfodue'                        => 'Ответить до : ',
    'tinfocreated'                    => 'Создано: ',
    'tinfoupdated'                    => 'Обновлено: ',
    'ticketlinkinfo'                  => 'Департамент: %s, Состояние: %s',
    'ticketunlink'                    => '-отсоединить',
    'tppostedon'                      => 'Опубликовано: %s',
    'tppostedonsurvey'                => 'Обзор комментария размещеного : %s',
    'tpemail'                         => 'Email: %s',
    'tpipaddress'                     => 'IP-адрес: %s',
    'participantbox'                  => 'Участники',
    'badgethirdparty'                 => 'Третья сторона',
    'badgeuser'                       => 'Пользователь',
    'badgestaff'                      => 'Персонал',
    'badgecc'                         => 'Получатель',
    'badgebcc'                        => 'СКРЫТАЯ КОПИЯ',
    'wineditticketpost'               => 'Редактировать сообщение заявки',
    'editpost'                        => 'Редактировать сообщение',
    'lastedited'                      => 'Последний раз редактировалось: %s  %s',
    'dialogduetimeline'               => 'Ответить до',
    'dialogresolutionduetimeline'     => 'Время решения',
    'stopwatching'                    => 'Остановить просмотр',
    'surrender'                       => 'Отказаться',
    'print'                           => 'Печать',
    'print_with_notes'                => 'Печать + Заметки',
    'take'                            => 'Взять',
    'tabauditlog'                     => 'Журнал регистрации выполняемых действий',
    'aldescription'                   => 'Описание',
    'alentrytype'                     => 'Тип ввода',
    'alstaff'                         => ' (Сотрудники)',
    'aluser'                          => ' (Пользователь)',
    'alsystem'                        => ' (Система)',
    'alparser'                        => ' (Парсер)',
    'editnote'                        => 'Редактировать заметку',
    'notetitle'                       => '%s  %s',
    'noteeditedtitle'                 => ' <em>(Редактировано %s  %s)</em>',
    'ticketnotedelconfirm'            => 'Вы уверены, что вы хотите удалить это примечание?',
    'tabaddnote'                      => 'Добавить заметку',
    'tabeditnote'                     => 'Редактировать заметку',
    'addnotes'                        => 'Заметки',
    'notetype'                        => 'Тип примечания',
    'addnote'                         => 'Добавить заметку',
    'notes_ticket'                    => 'Заявка',
    'notes_user'                      => 'Пользователь',
    'notes_userorganization'          => 'Организация пользователей',
    'tabrelease'                      => 'Релиз',
    'tabedit'                         => 'Редактировать',
    'tabhistory'                      => 'История',
    'tabchats'                        => 'Чаты',
    'notesvisibleall'                 => '--Все--',
    'notevisibleto'                   => 'Доступ',
    'desc_notevisibleto'              => 'Ограничьте видимость данного примечания.',
    'edit_subject'                    => 'Тема',
    'edit_fullname'                   => 'Имя автора',
    'edit_email'                      => 'Адрес электронной почты автора',
    'edit_overridesla'                => 'Переопределить SLA',
    'desc_edit_overridesla'           => 'Укажите план SLA для присоединения к этой заявке, переопределяя любое автоматическое назначение  планов SLA.',
    'editslausedef'                   => '--Использование SLA по умолчанию --',
    'edittproperties'                 => 'Свойства заявки',
    'edittrecipients'                 => 'Получатели заявки',
    'editrecipientsdesc'              => 'Additional recipients can be included in a ticket. Recipients will receive email copies of ticket replies made by staff, but will not have access to view the ticket online.<br /><br />The option to add ticket recipients is available when CCing, BCCing, or forwarding a ticket. There are three types of recipients:<br /><br /><strong>Third party:</strong> Copied (CCd) in on all ticket replies made by staff. They are also able to reply to these by email, and these replies will be added to the ticket.<br /><br /><strong>CC:</strong> Copied (CCd) in on all ticket replies made by staff. Can contribute to a ticket via email only.<br /><br /><strong>BCC:</strong> Blind copied (BCCd) in on all ticket replies made by staff. Can contribute to a ticket via email only.',
    'editthirdparty'                  => 'Получатели третьей стороны',
    'editcc'                          => 'Получатели копии',
    'editbcc'                         => 'Получатели скрытой копии',
    'history_ticketid'                => 'Идентификатор телефона',
    'history_subject'                 => 'Тема',
    'history_date'                    => 'Дата',
    'history_department'              => 'Департамент',
    'history_type'                    => 'Вид заявки',
    'history_status'                  => 'Статус',
    'history_priority'                => 'Приоритет заявки',
    'workflowbox'                     => 'Последовательность действий',
    'informationbox'                  => 'Информация',
    'tinfobticketid'                  => 'ID ЗАЯВКИ',
    'tinfobuser'                      => 'ПОЛЬЗОВАТЕЛЬ',
    'tinfobuserorganization'          => 'ОРГАНИЗАЦИЯ',
    'tinfobusergroup'                 => 'ГРУППА',
    'dispatchfrom'                    => 'От',
    'dispatchto'                      => 'Кому',
    'dispatchcc'                      => 'КОПИЯ',
    'dispatchbcc'                     => 'СКРЫТАЯ КОПИЯ',
    'dispatchcontents'                => 'Содержание ответа',
    'dispatchaddmacro'                => 'Макрос',
    'dispatchaddkb'                   => 'База знаний',
    'dispatchsendmail'                => 'Отправить письмо',
    'dispatchrawhtmlxml'              => 'Отправить как Raw HTML / XML',
    'dispatchprivate'                 => 'Частный ответ',
    'dispatchwatch'                   => 'Просмотреть заявку',
    'dispatchaddfile'                 => 'Добавьте еще один файл',
    'dispatchsend'                    => 'Отправить',
    'dispatchattachfile'              => 'Прикрепить файл',
    'dispatchsaveasdraft'             => 'Сохранить как черновик',
    'dispatchto'                      => 'Кому',
    'dispatchsubject'                 => 'Тема',
    'dispatchsend'                    => 'Отправить',
    'dispatchsendar'                  => 'Отправить автоответ',
    'dispatchuser'                    => 'Пользователь',
    'dispatchnewuser'                 => 'Создать новую пользовательскую учетной запись',
    'dispatchisphone'                 => 'Телефонная заявка',
    'winuserquickinsert'              => 'Создать новую пользовательскую учетной запись',
    'dispatch'                        => 'Назначить',
    'tabdispatch'                     => 'Назначить',
    'dispatchticket'                  => 'Назначить заявку',
    'assign'                          => 'Назначить',
    'newticket'                       => 'Новая заявка',
    'newticket_department'            => 'Создать заявку в Департаменте',
    'desc_newticket_department'       => '',
    'nt_sendmail'                     => 'Отправить письмо',
    'nt_asuser'                       => 'Как пользователь',
    'newticket_type'                  => 'Создайте тип заявки',
    'desc_newticket_type'             => '<strong>Отправить по электронной почте</strong> Заявка будет создана вами. <br /><strong></strong> Создать заявку от имени пользователя. Идеально подходит для регистрации телефонных звонков.',
    'nt_next'                         => 'Далее',
    'dispatchcreate'                  => 'Создать',
    'tabnewticket2'                   => 'Новая заявка: %s',
    'tpemailto'                       => 'Отправить Email: %s',
    'tpemailforwardedto'              => 'Направлено: %s',
    'tlockinfo'                       => '%s просмотр заявки (Последнее обновление: %s)',
    'tpostlockinfo'                   => '%s отвечает на эту заявку(Последнее обновление: %s)',
    'tabfollowup'                     => 'Последующая деятельность',
    'notes'                           => 'Заметки',

    'tabbilling'                      => 'Биллинг',
    'billworker'                      => 'Работник',
    'billdate'                        => 'Дата выставления счетов',
    'billworkdate'                    => 'Дата выполнения',
    'billtimespent'                   => 'Потраченное время (ч: м)',
    'billworked'                      => 'Отработанное время:',
    'billtotalworked'                 => 'Общее отработанное время:',
    'billbillable'                    => 'Оплачиваемое время:',
    'billtotalbillable'               => 'Общее оплачиваемое время:',
    'editbilling'                     => 'Редактировать запись выставления счетов',
    'tabeditbilling'                  => 'Редактирование счетов',
    'billingtitle'                    => 'Оплата входа для: %s в %s',
    'billingtitlework'                => ' (работал над: %s)',
    'billingeditedtitle'              => ' <em>(Редактировано %s  %s)</em>',
    'billingeditedtitle2'             => ' <em>(В редакции на %s)</em>',
    'editbilling'                     => 'Редактировать запись выставления счетов',
    'ticketbillingdelconfirm'         => 'Вы уверены, что хотите удалить платежную запись?',
    'titleinvalidbilldate'            => 'Существует проблема с потраченным временем',
    'msginvalidbilldate'              => 'Просьба представьте допустимые значения (часы: минуты) за отработанное время и оплачиваемое время.',

    'fugeneral'                       => 'Общие',
    'fuaddnote'                       => 'Добавить заметку',
    'fupostreply'                     => 'Ответ на сообщение',
    'fuforward'                       => 'Вперед',

    'followupmins'                    => 'В минутах...',
    'followuphours'                   => 'В часах...',
    'followupdays'                    => 'В днях...',
    'followupweeks'                   => 'В неделях...',
    'followupmonths'                  => 'В месяцах...',
    'followupcustom'                  => 'Настраевымие',
    'followup'                        => 'Последующая деятельность',
    'followup_willrunattime'          => 'Продолжение будет запущено в %s создано %s (%s)',
    'followup_willchangeownerto'      => 'Заявка будет назначена %s',
    'followup_willchangedepartmentto' => 'Заявка будет перемещена в  %s',
    'followup_willchangestatusto'     => 'Статус заявки будет изменен на %s',
    'followup_willchangepriorityto'   => 'Приоритет заявки будет изменен на %s',
    'followup_willchangetypeto'       => 'Тип заявки будет изменен на %s',
    'followup_willaddstaffnotes'      => 'В заявку будет добавлено примечание',
    'followup_willaddusernotes'       => 'Добавить пользовательскую заметку',
    'followup_willaddareply'          => 'Добавит шаблонный ответ на эту заявку',
    'followup_willforwardto'          => 'Направит эту заявку к %s',
    'followup_removeowner'            => 'Очистит поле владельца этой заявки',

    'flag'                            => 'Вид флага',
    'tescalationhistory'              => 'Просмотр истории эскалации (%d)',
    'tepdate'                         => 'Дата Эскалации : ',
    'tepslaplan'                      => 'План SLA: ',
    'tepescalationrule'               => 'Выполняется правило Эскалации: ',
    'ntchatid'                        => 'ID чата: ',
    'ntchatuserfullname'              => 'Пользователь: ',
    'ntchatuseremail'                 => 'Email:',
    'ntchatstafffullname'             => 'Сотрудник: ',
    'ntchatdepartment'                => 'Департамент: ',
    'titleticketdeleted'              => 'Заявкане может быть загружена',
    'msgticketdeleted'                => 'Эта заявка была удалена и не может быть загружена.',

    /**
     * ---------------------------------------------
     * MACRO
     * ---------------------------------------------
     */
    'macro'                           => 'Макрос',
    'macros'                          => 'Макрос',
    'insertmacro'                     => 'Вставить макрос',
    'editmacro'                       => 'Редактировать макрос',
    'tabcategories'                   => 'Категории',
    'tabmacros'                       => 'Макрос',
    'macrotitle'                      => 'Название макроса',
    'insertcategory'                  => 'Добавить категорию',
    'parentcategoryitem'              => '- Родительская категория -',
    'macrocategorytitle'              => 'Название категории',
    'desc_macrocategorytitle'         => 'Например <em>биллинговые ответы</em> или <em>поддержка сортировки</em>.',
    'parentcategory'                  => 'Родительская категория',
    'desc_parentcategory'             => 'Категория для размещения в этой категории.',
    'categorytype'                    => 'Доступ',
    'desc_categorytype'               => '<strong>Публичный</strong> Макрос категории доступны для всех сотрудников (только если конкретные команды, которые находятся ниже, выбраны). <br /><strong>Приватный</strong> Макрос категории доступны только для того кто их создал. Почему бы не поделиться богатством?',
    'titlemacrocategoryinsert'        => 'Категория макроса (%s) создана',
    'msgmacrocategoryinsert'          => 'Категория макроса (%s)создана успешно.',
    'titlemacrocategoryupdate'        => 'Категория макроса (%s) обновлена',
    'msgmacrocategoryupdate'          => 'Категория макроса (%s) обновлена успешно.',
    'titleinvalidparentcat'           => 'Проблема с родительской категорией',
    'msginvalidparentcat'             => 'Указанная родительская категория, не работает. Пожалуйста, проверьте дерево категорий макроса повторно, родительская категория должна быть фактическим родителем, а не ребенком.',
    'titledelmacrocat'                => 'Категории макроса удалены (%d)',
    'msgdelmacrocat'                  => 'Были удалены следующие категории макроса:',
    'filterreplies'                   => 'Фильтр ответов',
    'rootcategory'                    => 'Корневая категория',
    'macroreplytitle'                 => 'Название макроса',
    'desc_macroreplytitle'            => 'Например <em>первое сообщение</em> или <em>перевести заявку на 2й уровень</em>.',
    'parentcategoryreply'             => 'Категория',
    'desc_parentcategoryreply'        => 'Категория для создания этого макроса.',
    'reststaffgroupall'               => '-- Все команды сотрудников --',
    'restrictstaffgroup'              => 'Ограничить определенные команды',
    'desc_restrictstaffgroup'         => 'Сделать эту категорию доступной для команды (Категория доступности должна быть <em>общественной</em>,см. выше).',
    'macroreplycontents'              => 'Содержание ответа',
    'tabproperties'                   => 'Параметры',
    'ticketfields'                    => 'Поля заявки',
    'macrodepartment'                 => 'Задать департамент',
    'desc_macrodepartment'            => '',
    'macroticketstatus'               => 'Задать статус заявки',
    'desc_macroticketstatus'          => '',
    'macrotickettype'                 => 'Задать статус заявки',
    'desc_macrotickettype'            => '',
    'macroticketpriority'             => 'Задать приоритет заявки',
    'desc_macroticketpriority'        => '',
    'desc_macroaddtags'               => '',
    'macroaddtags'                    => 'Добавить теги в заявку',
    'macroownerstaff'                 => 'Задать владельца заявки',
    'desc_macroownerstaff'            => '',
    'insertmacro'                     => 'Вставить макрос',
    'editmacro'                       => 'Редактировать макрос',
    'titlemacroreplyinsert'           => 'Создан новый макрос (%s)',
    'msgmacroreplyinsert'             => 'Был успешно создан  (%s) новый макрос заявки.',
    'titlemacroreplyupdate'           => 'Макрос (%s) обновлен',
    'msgmacroreplyupdate'             => 'Был успешно создан  (%s) новый макрос заявки.',
    'titledelmacroreply'              => 'Удален макрос (%d)',
    'msgdelmacroreply'                => 'Были удалены следующие макросы:',
    'quickinsert'                     => 'Быстрое добавление',
    'qimacro'                         => 'Макрос',
    'qiknowledgebase'                 => 'База знаний',
    'replytotalhits'                  => 'Срабатывания',
    'replylastused'                   => 'Последнее использование',
    'invalidattachments'              => 'К сожалению, мы не смогли принять ваши вложения (слишком большие или из-за ограничений типа файлов): %s',


    /**
     * ---------------------------------------------
     * SEARCH
     * ---------------------------------------------
     */
    'search'                          => 'Поиск',
    'tabsearch'                       => 'Расширенный поиск',
    'matchtype'                       => 'Опции критериев',
    'desc_matchtype'                  => 'Выберите метод группирования критериев.',
    'smatchall'                       => 'Совпадения по всем (и)',
    'smatchany'                       => 'Совпадения по любому (или)',
    'insertcriteria'                  => 'Добавить критерии',

    /**
     * ---------------------------------------------
     * NEW TICKET
     * ---------------------------------------------
     */
    'tabrecurrence'                   => 'Повторение',
    'recurrence_none'                 => 'Не повторялись',
    'recurrence_daily'                => 'Каждый день',
    'recurrence_weekly'               => 'Каждую неделю',
    'recurrence_monthly'              => 'Каждый месяц',
    'recurrence_yearly'               => 'Каждый год',
    'rec_every'                       => 'Каждый',
    'rec_days'                        => 'дни',
    'rec_everyweekday'                => 'Каждый будний день',
    'rec_weeks'                       => 'неделю',
    'rec_monday'                      => 'Понедельник',
    'rec_tuesday'                     => 'Вторник',
    'rec_wednesday'                   => 'Среда',
    'rec_thursday'                    => 'Четверг',
    'rec_friday'                      => 'Пятница',
    'rec_saturday'                    => 'Суббота',
    'rec_sunday'                      => 'Воскресенье',
    'rec_day'                         => 'День',
    'rec_ofevery'                     => 'из каждого',
    'rec_months'                      => 'Месяцы',
    'rec_the'                         => 'В',
    'rec_first'                       => 'Первая',
    'rec_second'                      => 'Второй',
    'rec_third'                       => 'Третий',
    'rec_fourth'                      => 'Четвертый',
    'rec_fifth'                       => 'Пятый',
    'rec_of'                          => 'из',
    'recurnotactivated'               => 'Эта заявка не повторится.',
    'recurrencerange'                 => 'Повторяются до',
    'recur_starts'                    => 'Начинается повторение ',
    'recur_utc'                       => '<i>Date is in UTC</i>',
    'recur_ends'                      => 'Повторение заканчивается',
    'rec_noeenddate'                  => 'Нет даты окончания - продолжить повторение',
    'rec_endafter'                    => 'Заканчивается после:',
    'rec_endby'                       => 'Заканчивается:',
    'rec_occurrences'                 => 'проишествия',
    'pause'                           => 'Пауза',
    'resume'                          => 'Возобновить',
    'stop'                            => 'Остановить',

    // Potentialy unused phrases in staff_ticketsmanage.php
    'proptitleticketid'               => 'ID ЗАЯВКИ',
    'wineditdue'                      => 'Редактировать до и Решить до определенного срока',
    'aldate'                          => 'Дата',
    'altimeline'                      => 'График',
    'tabuser'                         => 'Пользователь',
    'desc_newticketdepartment'        => 'Выберите департамент, под которым вы хотите создать вашу заявку.',
    'tabnewticket'                    => 'Новая заявка',
    'currentfollowups'                => 'Дополнительные данные об этой заявке известные на данный момент:',
    'tabreplies'                      => 'Ответы',
    'replytitle'                      => 'Заголовок ответа',
    'insertreply'                     => 'Добавить ответ',
    'wininsertmacrocat'               => 'Вставить категорию макроса',
);



return $__LANG;
