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

$_SWIFT = SWIFT::GetInstance();

$__LANG = array(
    'charset'                      => 'UTF-8',
    'html_encoding'                => '8bit',
    'text_encoding'                => '8bit',
    'html_charset'                 => 'UTF-8',
    'text_charset'                 => 'UTF-8',
    'base_charset'                 => '',
    'yes'                          => 'Да',
    'no'                           => 'Нет',
    'menusupportcenter'            => 'Центр поддержки',
    'menustaffcp'                  => 'ПУ Персонала',
    'menuadmincp'                  => 'ПУ Администраторов',
    'app_notreg'                   => '%s модуль не зарегистрирован',
    'event_notreg'                 => '%s событие не зарегистрировано',
    'unable_to_execute'            => 'Невозможно выполнить %s',
    'action_notreg'                => '%s действие не зарегистрировано',
    'username'                     => 'Имя пользователя',
    'password'                     => 'Пароль',
    'rememberme'                   => 'Запомнить меня',
    'defaulttitle'                 => '%s - Powered by Kayako %s',
    'login'                        => 'Войти',
    'global'                       => 'Общий',
    'first'                        => 'Первая',
    'last'                         => 'Последняя',
    'pagination'                   => 'Страница %s из %s',
    'submit'                       => 'Отправить',
    'reset'                        => 'Сброс',
    'poweredby'                    => 'Helpdesk Software Powered by Kayako %s',
    'copyright'                    => 'Copyright &copy; 2001-%s Kayako',
    'notifycsrfhash'               => 'Обнаружена попытка CSRF, невозможно выполнить необходимое действие.',
    'titlecsrfhash'                => 'Неверный CSRF Hash',
    'msgcsrfhash'                  => 'Kayako обнаружил попытку CSRF (межсайтовой подделки запроса) и не может выполнить необходимое действие.',
    'invaliduser'                  => 'Неверное имя пользователя или пароль',
    'invaliduseripres'             => 'Неавторизованный IP (Попытка: %d/%d)',
    'invaliduserdisabled'          => 'Учетная запись заблокирована (Попытка: %d/%d)',
    'invalid_sessionid'            => 'Сессия окончена из-за отсутствия активности',
    'staff_not_admin'              => 'У пользователя нет прав администратора',
    'sort_desc'                    => 'Сортировать по убыванию',
    'sort_asc'                     => 'Сортировать по возрастанию',
    'options'                      => 'Опции',
    'delete'                       => 'Удалить',
    'settings'                     => 'Настройки',
    'search'                       => 'Поиск',
    'searchbutton'                 => 'Поиск',
    'actionconfirm'                => 'Вы уверены, что хотите продолжить?',
    'loggedout'                    => 'Успешный выход из системы',
    'view'                         => 'Просмотр',
    'dashboard'                    => 'Главная',
    'help'                         => 'Помощь',
    'size'                         => 'Размер',
    'home'                         => 'Главная',
    'logout'                       => 'Выход',
    'staffcp'                      => 'ПУ Персонала',
    'admincp'                      => 'ПУ Администраторов',
    'winapp'                       => 'Kayako Desktop',
    'staffapi'                     => 'Staff API',
    'bytes'                        => 'Байты',
    'kb'                           => 'KB',
    'mb'                           => 'МБ',
    'gb'                           => 'ГБ',
    'noitemstodisplay'             => 'Нет элементов для отображения',
    'manage'                       => 'Управление',
    'title'                        => 'Название',
    'disable'                      => 'Отключить',
    'enable'                       => 'Включить',
    'edit'                         => 'Редактировать',
    'back'                         => 'Назад',
    'forward'                      => 'Вперед',
    'insert'                       => 'Добавить',
    'edit'                         => 'Редактировать',
    'update'                       => 'Обновить',
    'public'                       => 'Публичный',
    'private'                      => 'Приватный',
    'requiredfieldempty'           => 'Одно из обязательных для заполнения полей пустое',
    'clifatalerror'                => 'КРИТИЧЕСКАЯ ОШИБКА',
    'clienterchoice'               => 'Пожалуйста, укажите Ваш выбор: ',
    'clinotvalidchoice'            => '"%s" невозможный выбор; повторите попытку ',
    'description'                  => 'Описание',
    'success'                      => 'Успешно',
    'failure'                      => 'Ошибка',
    'status'                       => 'Статус',
    'date'                         => 'Дата',
    'seconds'                      => 'Секунды',
    'order'                        => 'Порядок',
    'email'                        => 'Email',
    'subject'                      => 'Тема',
    'contents'                     => 'Содержание',
    'sunday'                       => 'Воскресенье',
    'monday'                       => 'Понедельник',
    'tuesday'                      => 'Вторник',
    'wednesday'                    => 'Среда',
    'thursday'                     => 'Четверг',
    'friday'                       => 'Пятница',
    'saturday'                     => 'Суббота',
    'am'                           => 'Дня',
    'pm'                           => 'Вечера',
    'pfieldreveal'                 => '[Показать]',
    'pfieldhide'                   => '[Скрыть]',
    'loadingwindow'                => 'Загрузка...',
    'customfields'                 => 'Настраиваемые поля',
    'nopermission'                 => 'К сожалению, у Вас нет необходимых прав доступа для выполнения этого действия',
    'nopermissiontext'             => 'К сожалению, у Вас нет необходимых прав доступа для выполнения этого действия',
    'generationdate'               => 'XML сгенерирован на: %s',
    'approve'                      => 'Подтвердить',
    'paginall'                     => 'Все',
    'fullname'                     => 'Имя и фамилия',
    'onlineusers'                  => 'Персонал онлайн',
    'vardate1'                     => '%dд %dч %dм',
    'vardate2'                     => '%dч %dм %dс',
    'vardate3'                     => '%dм %dс',
    'vardate4'                     => '%dс',
    'reports'                      => 'Отчеты',
    'demomode'                     => 'Невозможно выполнить действие в демо-режиме',
    'titledemomode'                => 'Невозможно продолжить',
    'unmodifiedreport'             => 'Running the report unmodified as user does not have permission to modify report.',
    'msgdemomode'                  => 'Невозможно выполнить необходимо действие в демо-режиме',
    'filter'                       => 'Фильтр',
    'editor'                       => 'Редактор',
    'images'                       => 'Изображения',
    'tabedit'                      => 'Редактировать',
    'notifyfieldempty'             => 'Одно из обязательных для заполнения полей не заполнено',
    'notifykqlfieldempty'          => 'KQL Query пуст',
    'titlefieldempty'              => 'Поля не заполнены.',
    'msgfieldempty'                => 'Одно из обязательных для заполнения полей не заполнено или содержит неверные данные. Пожалуйста, убедитесь, что Вы ввели всю необходимую информацию в допустимом формате в поля ниже.',
    'msgpastdate'                   => 'Дата не может быть в прошлом',
    'titlefieldinvalid'            => 'Недопустимое значение',
    'msgfieldinvalid'              => 'В одном из полей содержатся недопустимые данные; проверьте свои данные.',
    'titleinvalidemail'            => 'Invalid Email',
    'msginvalidemail'              => 'The email you entered is same as your customer email; please check your input.',
    'msginvalidadditionalemail'    => 'The email address entered is already used in the desk; Please enter the valid email address.',
    'save'                         => 'Сохранить',
    'viewall'                      => 'Просмотреть все',
    'cancel'                       => 'Закрыть',
    'tabgeneral'                   => 'Общие',
    'language'                     => 'Язык',
    'loginshare'                   => 'LoginShare',
    'licenselimit_unabletocreate'  => 'Невозможно создать новую учётную запись сотрудника, так как истек срок действия Вашей лицензии',
    'help'                         => 'Помощь',
    'name'                         => 'Имя',
    'value'                        => 'Значение',
    'engagevisitor'                => 'Привлечь посетителя',
    'inlinechat'                   => 'Встроенный чат',
    'url'                          => 'Ссылка',
    'hexcode'                      => 'Hex код',
    'vactionvariables'             => 'Действие: Переменные',
    'vactionvexp'                  => 'Действие: Качество обслуживания посетителей',
    'vactionsalerts'               => 'Действие: Уведомления персонала',
    'vactionsetdepartment'         => 'Действие: Указать департамент',
    'vactionsetskill'              => 'Действие: Указать навык',
    'vactionsetgroup'              => 'Действие: Указать группу',
    'vactionsetcolor'              => 'Действие: Указать группу',
    'vactionbanvisitor'            => 'Действие: Заблокировать посетителя',
    'customengagevisitor'          => 'Индивидуально привлекать посетителей',
    'managerules'                  => 'Управление правилами',
    'open'                         => 'Открыть',
    'close'                        => 'Закрыть',
    'titleupdatedswiftsettings'    => 'Обновлены "%s" настройки',
    'updatedsettingspartially'     => 'Следующие настройки не были обновлены',
    'msgupdatedswiftsettings'      => 'Все настройки для "%s" категории успешно обновлены.',
    'geoipprocessrunning'          => 'Невозможно вернуть процесс восстановления. Выполняется процесс востановления GeoIP.',
    'continueprocessquestion'      => 'Существующее задание все еще выполняется. Вы хотите его прервать, чтобы продолжить?',
    'titleupdsettings'             => 'Обновлены "%s" настройки',
    'type'                         => 'Тип',
    'banip'                        => 'IP (255.255.255.255)',
    'banclassc'                    => 'Класс C (255.255.255.*)',
    'banclassb'                    => 'Класс B (255.255.*.*)',
    'banclassa'                    => 'Класс A (255.*.*.*)',
    'if'                           => 'Если',
    'loginlogerror'                => 'Вход заблокирован на %d минут (попытка: %d/%d)',
    'loginlogerrorsecs'            => 'Логин заблокирован на %d секунд (попытка: %d/%d)',
    'loginlogwarning'              => 'Неверное имя пользователя или пароль (попытка: %d/%d)',
    'na'                           => '- Нет данных -',
    'redirectloading'              => 'Загрузка...',
    'noinfoinview'                 => 'Информация недоступна в этом виде',
    'nochange'                     => '-- Без изменений --',
    'activestaff'                  => '-- Активный персонал --',
    'notificationuser'             => 'Пользователь',
    'notificationuserorganization' => 'Организация пользователей',
    'notificationstaff'            => 'Персонал (Владелец)',
    'notificationteam'             => 'Команда сотрудников',
    'notificationdepartment'       => 'Департамент',
    'notificationsubject'          => 'Тема: ',
    'lastupdate'                   => 'Последнее обновление',
    'interface_admin'              => 'ПУ Администратора',
    'interface_staff'              => 'ПУ Персонала',
    'interface_intranet'           => 'Интранет',
    'interface_api'                => 'API',
    'interface_winapp'             => 'Kayako Desktop',
    'interface_syncworks'          => 'SyncWorks',
    'interface_instaalert'         => 'InstaAlert',
    'interface_pda'                => 'PDA',
    'interface_rss'                => 'RSS',
    'error_database'               => 'База данных',
    'error_php'                    => 'PHP',
    'error_exception'              => 'Исключение',
    'error_mail'                   => 'Email',
    'error_general'                => 'Общие',
    'error_loginshare'             => 'LoginShare',
    'loading'                      => 'Загрузка...',
    'pwtooshort'                   => 'Слишком короткий',
    'pwveryweak'                   => 'Очень слабый',
    'pwweak'                       => 'Слабый',
    'pwmedium'                     => 'Средний',
    'pwstrong'                     => 'Надежный',
    'pwverystrong'                 => 'Очень надежный',
    'pwunsafeword'                 => 'Ненадежное слово в пароле!',
    'staffpasswordpolicy'          => '<span class="tabletitle">Политика паролей:</span> Минимальная длинна пароля: %d, Минимальное количество цифр: %d, Минимальное количество символов: %d, Минимальное количество символов верхнем регистре: %d',
    'userpasswordpolicy'           => '<span class="tabletitle">Политика паролей:</span> Минимальная длинна пароля: %d, Минимальное количество цифр: %d, Минимальное количество символов: %d, Минимальное количество символов верхнем регистре: %d',
    'titlepwpolicy'                => 'Несовместимость с политикой паролей',
    'passwordexpired'              => 'Срок действия пароля истек',
    'newpassword'                  => 'Новый пароль',
    'passwordagain'                => 'Пароль (повторите)',
    'passworddontmatch'            => 'Новый пароль не походит или не указан',
    'defaulttimezone'              => '-- Часовой пояс по умолчанию --',
    'tagcloud'                     => 'Облаго тегов',
    'searchmodeactive'             => 'Результаты отфильтрованы',
    'notifysearchfailed'           => 'Найдено "0" результатов',
    'titlesearchfailed'            => 'Найдено "0" результатов',
    'msgsearchfailed'              => 'Kayako не смог обнаружить какие-либо записи, соответствующие указанным критериям.',
    'quickfilter'                  => 'Быстрый фильтр',
    'fuenterurl'                   => 'Введите URL:',
    'fuorupload'                   => 'или загрузите:',
    'errorsmtpconnect'             => 'Невозможно соединиться с SMTP сервером',
    'starttypingtags'              => 'Начните печатать, чтобы добавить теги...',
    'unsupportedtagchars'          => 'One or more unsupported characters were stripped from the tag.',
    'titleinvalidfileext'          => 'Недопустимое расширение файла изображения',
    'msginvalidfileext'            => 'Недопустимое расширение файла изображения. Допустимые расширения: gif, jpeg, jpg, png',
    'notset'                       => '-- Не указано --',
    'ratings'                      => 'Рейтинги',
    'system'                       => 'Система',
    'schatid'                      => 'ID чата',
    'supportcenterfield'           => 'Центр поддержки:',
    'smessagesurvey'               => 'Сообщения/опросы',
    'nosubject'                    => '(без темы)',
    'nolocale'                     => '(нет перевода)',
    'markdefault'                   => 'Пометить по умолчанию',
    'policyurlupdatetitle'           => 'Обновлен URL-адрес политики',
    'policyurlupdatemessage'       => 'URL-адрес политики был успешно обновлен.',

    // Easy Dates
    'edoneyear'                    => 'один год',
    'edxyear'                      => '%d лет',
    'edonemonth'                   => 'один месяц',
    'edxmonth'                     => '%d месяцев',
    'edoneday'                     => 'один день',
    'edxday'                       => '%d дней',
    'edonehour'                    => 'один час',
    'edxhour'                      => '%d часов',
    'edoneminute'                  => 'одна минута',
    'edxminute'                    => '%d минут',
    'edjustnow'                    => 'Сейчас',
    'edxseconds'                   => '%d секунд',
    'ago'                          => 'ранее',

    // Operators
    'opcontains'                   => 'Включает',
    'opnotcontains'                => 'Не включает',
    'opequal'                      => 'Равный',
    'opnotequal'                   => 'Не равный',
    'opgreater'                    => 'Больше чем',
    'opless'                       => 'Меньше чем',
    'opregexp'                     => 'Регулярное выражение',
    'opchanged'                    => 'Изменено',
    'opnotchanged'                 => 'Не изменено',
    'opchangedfrom'                => 'Изменено с',
    'opchangedto'                  => 'Изменено на',
    'opnotchangedfrom'             => 'Не изменено с',
    'opnotchangedto'               => 'Не изменено на',
    'matchand'                     => 'И',
    'matchor'                      => 'ИЛИ',
    'strue'                        => 'Правильно',
    'sfalse'                       => 'Неправильно',
    'notifynoperm'                 => 'Действие запрещено, отказано в разрешении.',
    'titlenoperm'                  => 'Действие запрещено',
    'msgnoperm'                    => 'Kayako не может продолжить, так как сотрудник, который в данный момент находится в системе, не имеет достаточно прав доступа для выполнения этого действия.',
    'msgnoperm1'                   => 'The ticket has been created but you do not have the permission to carry out other operations.',
    'cyesterday'                   => 'Вчера',
    'ctoday'                       => 'Сегодня',
    'ccurrentwtd'                  => 'Текущая неделя на сегодняшний день',
    'ccurrentmtd'                  => 'Текущий месяц на сегодняшний день',
    'ccurrentytd'                  => 'Текущий год на сегодняшний день',
    'cl7days'                      => 'Последние 7 дней',
    'cl30days'                     => 'Последние 30 дней',
    'cl90days'                     => 'Последние 90 дней',
    'cl180days'                    => 'Последние 180 дней',
    'cl365days'                    => 'Последние 365 дней',
    'ctomorrow'                    => 'Завтра',
    'cnextwfd'                     => 'Текущая неделя с сегодняшнего дня',
    'cnextmfd'                     => 'Текущий месяц с сегодняшнего дня',
    'cnextyfd'                     => 'Текущий год с сегодняшнего дня',
    'cn7days'                      => 'Следующие 7 дней',
    'cn30days'                     => 'Следующие 30 дней',
    'cn90days'                     => 'Следующие 90 дней',
    'cn180days'                    => 'Следующие 180 дней',
    'cn365days'                    => 'Следующие 365 дней',
    'new'                          => 'Новый',
    'phoneext'                     => 'Телефон: %s',
    'snewtickets'                  => 'Новые заявки',
    'sadvancedsearch'              => 'Расширенный поиск',
    'squicksearch'                 => 'Быстрый поиск:',
    'sticketidlookup'              => 'Поиск ID заявки:',
    'screatorreplier'              => 'Создатель/отвечающий:',
    'smanage'                      => 'Управление',
    'clear'                        => 'Очистить',
    'never'                        => 'Никогда',
    'seuser'                       => 'Пользователи',
    'seuserorg'                    => 'Организации пользователей',
    'manage'                       => 'Управление',
    'import'                       => 'Импорт',
    'export'                       => 'Экспорт',
    'comments'                     => 'Комментарии',
    'commentdata'                  => 'Комментарии:',
    'postnewcomment'               => 'Отправить новый комментарий',
    'replytocomment'               => 'Ответить на комментарий',
    'buttonsubmit'                 => 'Отправить',
    'reply'                        => 'Ответить',

    // Flags
    'purpleflag'                   => 'Фиолетовый флаг',
    'redflag'                      => 'Красный флаг',
    'orangeflag'                   => 'Оранжевый флаг',
    'yellowflag'                   => 'Желтый флаг',
    'blueflag'                     => 'Голубой флаг',
    'greenflag'                    => 'Зеленый флаг',

    'calendar'                     => 'Календарь',
    'cal_january'                  => 'Январь',
    'cal_february'                 => 'Февраль',
    'cal_march'                    => 'Март',
    'cal_april'                    => 'Апрель',
    'cal_may'                      => 'Май',
    'cal_june'                     => 'Июнь',
    'cal_july'                     => 'Июль',
    'cal_august'                   => 'Август',
    'cal_september'                => 'Сентябрь',
    'cal_october'                  => 'Октябрь',
    'cal_november'                 => 'Ноябрь',
    'cal_december'                 => 'Декабрь',

    /**
     * ###############################################
     * APP LIST
     * ###############################################
     */
    'app_base'                     => 'База',
    'app_tickets'                  => 'Заявки',
    'app_knowledgebase'            => 'База знаний',
    'app_parser'                   => 'Парсер почты',
    'app_livechat'                 => 'Онлайн поддержка',
    'app_troubleshooter'           => 'Устранение неполадок',
    'app_news'                     => 'Новости',
    'app_core'                     => 'Ядро',
    'app_backend'                  => 'Бэкенд',
    'app_reports'                  => 'Отчеты',

    // Potentialy unused phrases in en-us.php
    'defaultloginapi'              => 'Kayako Login Routine',
    'redirect_login'               => 'Processing Login...',
    'redirect_dashboard'           => 'Redirecting to Home...',
    'no_wait'                      => 'Please click here if your browser does not automatically redirect you',
    'select_un_all'                => 'Select/Unselect All Items',
    'quicksearch'                  => 'Quick Поиск',
    'mass_action'                  => 'Mass Action',
    'massfieldaction'              => 'Action: ',
    'advanced_search'              => 'Advanced Поиск',
    'searchfieldquery'             => 'Query: ',
    'searchfieldfield'             => 'Field: ',
    'settingsfieldresultsperpage'  => 'Results Per Page: ',
    'clidefault'                   => '%s v%s\\nCopyright (c) 2001-%s Kayako\\n',
    'firstselect'                  => '- Select -',
    'exportasxml'                  => 'XML',
    'exportascsv'                  => 'CSV',
    'exportassql'                  => 'SQL',
    'exportaspdf'                  => 'PDF',
    'clientarea'                   => 'Support Center',
    'pdainterface'                 => 'PDA Interface',
    'kayakomobile'                 => 'Kayako Mobile',
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
    'defaultloginshare'            => 'Kayako LoginShare',
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
    'wrong_profile_image'          => 'Изображение профиля не обновлялось. Неверный формат.',
    'wrong_image_size'             => 'Размер изображения больше разрешенного размера загрузки.',
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

        14 => array('Настройки', 'bar_settings.gif', APP_CORE, '/Base/Settings/Index'),
        26 => array('REST API', 'bar_restapi.gif', APP_BASE),
        27 => array('Генератор ярлыков', 'bar_tag.gif', APP_LIVECHAT, '/Base/TagGenerator/Index'),
        0  => array('Шаблоны', 'bar_templates.gif', APP_BASE),
        1  => array('Языки', 'bar_languages.gif', APP_CORE),
        2  => array('Настраиваемые поля', 'bar_customfields.gif', APP_CORE),
        25 => array('GeoIP', 'bar_geoip.gif', APP_CORE, '/Base/GeoIP/Manage'),
        13 => array('Онлайн чат', 'bar_livesupport.gif', APP_LIVECHAT),
        4  => array('Парсер почты', 'bar_mailparser.gif', APP_PARSER),
        5  => array('Заявки', 'bar_tickets.gif', APP_TICKETS),
        35 => array ('Пользовательское согласие', 'bar_maintenance.gif', APP_BASE, '/Base/Consent/Index'),
        29 => array('Последовательность действий', 'bar_workflow.gif', APP_TICKETS, '/Tickets/Workflow/Manage'),
        30 => array('Рейтинги', 'bar_ratings.gif', APP_TICKETS, '/Base/Rating/Manage'),
        6  => array('SLA', 'bar_sla.gif', APP_TICKETS),
        7  => array('Эскалации', 'bar_escalations.gif', APP_TICKETS, '/Tickets/Escalation/Manage'),
        20 => array('Байесовы', 'bar_bayesian.gif', APP_TICKETS),
        21 => array('База знаний', 'bar_knowledgebase.gif', APP_KNOWLEDGEBASE),
        23 => array('Новости', 'bar_news.gif', APP_NEWS),
        24 => array('Устранение неполадок', 'bar_troubleshooter.gif', APP_TROUBLESHOOTER),
        31 => array('Виджеты', 'bar_widgets.gif', APP_BASE, '/Base/Widget/Manage'),
        32 => array('Модули', 'bar_apps.gif', APP_BASE, '/Base/App/Manage'),
        9  => array('Журналы регистрации', 'bar_logs.gif', APP_BASE),
        10 => array('Запланированные задания', 'bar_cron.gif', APP_BASE),
        11 => array('База данных', 'bar_database.gif', APP_BASE),
        33 => array('Импорт', 'bar_import.gif', APP_BASE),
        12 => array('Диагностика', 'bar_diagnostics.gif', APP_BASE),
        34 => array('Управление', 'bar_maintenance.gif', APP_BASE),
    );

    SWIFT::Set('adminbar', $_adminBarContainer);

    $_adminBarItemContainer = array(
        0  => array(
            0 => array('Группы', '/Base/TemplateGroup/Manage'),
            1 => array('Шаблоны', '/Base/Template/Manage'),
            2 => array('Поиск', '/Base/TemplateSearch/Index'),
            3 => array('Импорт/Экспорт', '/Base/TemplateManager/ImpEx'),
            4 => array('Восстановить', '/Base/TemplateRestore/Index'),
            5 => array('Диагностика', '/Base/TemplateDiagnostics/Index'),
            6 => array('Логотипы заглавий', '/Base/TemplateManager/Personalize'),
        ),

        1  => array(
            0 => array('Языки', '/Base/Language/Manage'),
            1 => array('Фразы', '/Base/LanguagePhrase/Manage'),
            2 => array('Поиск', '/Base/LanguagePhrase/Search'),
            3 => array('Импорт/Экспорт', '/Base/LanguageManager/ImpEx'),
            4 => array('Восстановить', '/Base/LanguageManager/Restore'),
            5 => array('Диагностика', '/Base/LanguageManager/Diagnostics'),
        ),

        2  => array(
            0 => array('Группы', '/Base/CustomFieldGroup/Manage'),
            1 => array('Поля', '/Base/CustomField/Manage'),
        ),

        4  => array(
            0 => array('Настройки', '/Parser/SettingsManager/Index'),
            1 => array('Очереди Email', '/Parser/EmailQueue/Manage'),
            2 => array('Правила', '/Parser/Rule/Manage'),
            3 => array('Breaklines', '/Parser/Breakline/Manage'),
            4 => array('Bans', '/Parser/Ban/Manage'),
            5 => array('Общие правила', '/Parser/CatchAll/Manage'),
            6 => array('Циклические запреты', '/Parser/LoopBlock/Manage'),
            7 => array('Циклические правила', '/Parser/LoopRule/Manage'),
            9 => array('Журнал парсера', '/Parser/ParserLog/Manage'),
        ),

        5  => array(
            0 => array('Настройки', '/Tickets/SettingsManager/Index'),
            1 => array('Типы', '/Tickets/Type/Manage'),
            2 => array('Статусы', '/Tickets/Status/Manage'),
            3 => array('Приоритеты', '/Tickets/Priority/Manage'),
            4 => array('Типы файлов', '/Tickets/FileType/Manage'),
            5 => array('Ссылки', '/Tickets/Link/Manage'),
            8 => array('Автоматическое закрытие', '/Tickets/AutoClose/Manage'),
            7 => array('Управление', '/Tickets/Maintenance/Index'),
        ),

        6  => array(
            0 => array('Настройки', '/Tickets/SettingsManager/SLA'),
            1 => array('Планы', '/Tickets/SLA/Manage'),
            2 => array('Графики', '/Tickets/Schedule/Manage'),
            3 => array('Выходные дни', '/Tickets/Holiday/Manage'),
            4 => array('Импорт/Экспорт', '/Tickets/HolidayManager/Index'),
        ),

        20 => array(
            0 => array('Настройки', '/Tickets/SettingsManager/Bayesian'),
            1 => array('Категории', '/Tickets/BayesianCategory/Manage'),
            2 => array('Диагностика', '/Tickets/BayesianDiagnostics/Index'),
        ),

        9  => array(
            0 => array('Лог ошибок', '/Base/ErrorLog/Manage'),
            1 => array('Журнал заданий', '/Base/ScheduledTasks/TaskLog'),
            3 => array('Журнал регистрации операций', '/Base/ActivityLog/Manage'),
            4 => array('Журнал регистрации доступа', '/Base/LoginLog/Manage'),
        ),

        10 => array(
            0 => array('Управлять', '/Base/ScheduledTasks/Manage'),
            1 => array('Журнал заданий', '/Base/ScheduledTasks/TaskLog'),
        ),

        11 => array(
            0 => array('Информация о таблице', '/Base/Database/TableInfo'),
        ),

        12 => array(
            0 => array('Активные сессии', '/Base/Diagnostics/ActiveSessions'),
            1 => array('Информация кэша', '/Base/Diagnostics/CacheInformation'),
            2 => array('Перестроить кэш', '/Base/Diagnostics/RebuildCache'),
            3 => array('PHP информация', '/Base/Diagnostics/PHPInfo'),
            4 => array('Отчет об ошибке', '/Base/Diagnostics/ReportBug'),
            5 => array('Информация о лицензии', '/Base/Diagnostics/LicenseInformation'),
        ),

        13 => array(
            0 => array('Настройки', '/LiveChat/SettingsManager/Index'),
            1 => array('Правила посетителей', '/LiveChat/Rule/Manage'),
            2 => array('Группы пользователей', '/LiveChat/Group/Manage'),
            3 => array('Навыки сотрудников', '/LiveChat/Skill/Manage'),
            4 => array('Запреты посетителей', '/LiveChat/Ban/Manage'),
            5 => array('Маршрутизация сообщений', '/LiveChat/MessageRouting/Index'),
            6 => array('Статус персонала', '/LiveChat/OnlineStatus/Index'),
        ),

        19 => array(
            0 => array('Настройки', '/Manuals/SettingsManager/Index'),
        ),

        21 => array(
            0 => array('Настройки', '/KnowledgeBase/SettingsManager/Index'),
            1 => array('Управление', '/KnowledgeBase/Maintenance/Index'),
        ),

        22 => array(
            0 => array('Настройки', '/Downloads/SettingsManager/Index'),
        ),

        23 => array(
            0 => array('Настройки', '/News/SettingsManager/Index'),
            1 => array('Импорт/Экспорт', '/News/ImpEx/Manage'),
        ),

        24 => array(
            0 => array('Настройки', '/Troubleshooter/SettingsManager/Index'),
        ),

        25 => array(
            0 => array('Управление', '/Base/GeoIP/Manage'),
        ),

        26 => array(
            0 => array('Настройки', '/Base/Settings/RESTAPI'),
            1 => array('API инфрмация', '/Base/RESTAPI/Index'),
        ),

        33 => array(
            0 => array('Управлять', '/Base/Import/Manage'),
            1 => array('Журнал импорта', '/Base/ImportLog/Manage'),
        ),

        34 => array(
            0 => array('Очистка вложений', '/Base/PurgeAttachments/Index'),
            1 => array('Переместить приложения', '/Base/MoveAttachments/Index'),
        ),

    );

    // Log stuff
    if (SWIFT_PRODUCT == 'Fusion' || SWIFT_PRODUCT == 'Resolve' || SWIFT_PRODUCT == 'Case') {
        $_adminBarItemContainer[9][2] = array('Parser Log', '/Parser/ParserLog/Manage');
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

        1 => array('Главная', 80, APP_CORE),
        2 => array('Персонал', 100, APP_BASE),
        3 => array('Департаменты', 120, APP_BASE),
        4 => array('Пользователи', 100, APP_BASE),
    );

    SWIFT::Set('adminmenu', $_adminMenuContainer);

    /**
     * Item Format Example: 0 => array ('Name Of Item to Display', 'www.link-to-item.com', PREFIX_SPACING, SUFFIX_BAR_AND_SPACING),
     * The PREFIX_SPACING and SUFFIX_BAR_AND_SPACING are required for the theme to display the separator items for the menu items
     */
    $_adminLinkContainer = array(

        1 => array(
            0 => array('Главная', '/Base/Home/Index'),
            1 => array('Настройки', '/Base/Settings/Index'),
        ),

        2 => array(
            0 => array('Управление персоналом', '/Base/Staff/Manage'),
            1 => array('Управление командами', '/Base/StaffGroup/Manage'),
            2 => array('Добавить сотрудника', '/Base/Staff/Insert'),
            3 => array('Добавить команду', '/Base/StaffGroup/Insert'),
            4 => array('LoginShare', '/Base/Settings/StaffLoginShare'),
            5 => array('Настройки', '/Base/Settings/Staff'),
        ),

        3 => array(
            0 => array('Управление департаментамиs', '/Base/Department/Manage'),
            1 => array('Добавить департамент', '/Base/Department/Insert'),
            2 => array('Доступ', '/Base/Department/AccessOverview'),
        ),

        4 => array(
            0 => array('Управление группами пользователей', '/Base/UserGroup/Manage'),
            1 => array('Добавить группу пользователей', '/Base/UserGroup/Insert'),
            2 => array('LoginShare', '/Base/Settings/UserLoginShare'),
            3 => array('Настройки', '/Base/Settings/User'),
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
        1 => array('Главная', 80, APP_CORE),
        2 => array('Заявки', 100, APP_TICKETS, 't_entab'),
        3 => array('Онлайн чат', 120, APP_LIVECHAT, 'ls_entab'),
        4 => array('База знаний', 140, APP_KNOWLEDGEBASE, 'kb_entab'),
        6 => array('Устранение неполадок', 140, APP_TROUBLESHOOTER, 'tr_entab'),
        7 => array('Новости', 90, APP_NEWS, 'nw_entab'),
        8 => array('Пользователи', 90, APP_CORE, 'cu_entab'),
        9 => array('Отчеты', 90, APP_REPORTS, 'rp_entab'),
    );

    SWIFT::Set('staffmenu', $_staffMenuContainer);

    /**
     * Item Format Example: 0 => array ('Name Of Item to Display', 'www.link-to-item.com', PREFIX_SPACING, SUFFIX_BAR_AND_SPACING),
     * The PREFIX_SPACING and SUFFIX_BAR_AND_SPACING are required for the theme to display the separator items for the menu items
     */
    $_staffLinkContainer = array(

        1 => array(
            0 => array('Главная', '/Base/Home/Index'),
            1 => array('Мои предпочтения', '/Base/Preferences/ViewPreferences'),
            2 => array('Уведомления', '/Base/Notification/Manage', 'staff_canviewnotifications'),
            3 => array('Комментарии', '/Base/Comment/Manage', 'staff_canviewcomments'),
        ),

        2 => array(
            0 => array('Управление заявками', '/Tickets/Manage/Index', 'staff_tcanviewtickets'),
            1 => array('Поиск', ':UIDropDown(\'ticketsearchmenu\', event, \'linkmenu2_1\', \'linksdiv\'); LinkTicketSearchForms();'),
            2 => array('Новая заявка', ':UICreateWindow(\'/Tickets/Ticket/NewTicket/\', \'newticket\', \'New Ticket\', \'Loading..\', 500, 350, true);', 'staff_tcaninsertticket'),
            3 => array('Макрос', '/Tickets/MacroCategory/Manage', 'staff_tcanviewmacro'),
            4 => array('Режимы отображения', '/Tickets/View/Manage', 'staff_tcanview_views'),
            5 => array('Фильтры', ':UIDropDown(\'ticketfiltermenu\', event, \'linkmenu2_5\', \'linksdiv\');'),
        ),

        3 => array(
            0 => array('История чата', '/LiveChat/ChatHistory/Manage', 'staff_lscanviewchat'),
            1 => array('Сообщения/опросы', '/LiveChat/Message/Manage', 'staff_lscanviewmessages'),
            2 => array('Журнал звонков', '/LiveChat/Call/Manage', 'staff_lscanviewcalls'),
            3 => array('Шаблонные ответы', '/LiveChat/CannedCategory/Manage', 'admin_lscanviewcanned'),
            4 => array('Поиск', ':UIDropDown(\'chatsearchmenu\', event, \'linkmenu3_4\', \'linksdiv\'); LinkChatSearchForms();'),
        ),

        4 => array(
            0 => array('База знаний', '/Knowledgebase/ViewKnowledgebase/Index'),
            1 => array('Управление базой знаний', '/Knowledgebase/Article/Manage'),
            2 => array('Категории', '/Knowledgebase/Category/Manage'),
            3 => array('Новая статья', '/Knowledgebase/Article/Insert'),
        ),

        5 => array(
            0 => array('View Downloads', '/Downloads/Downloads/Manage'),
            1 => array('Manage Downloads', '/Downloads/Downloads/Manage'),
            2 => array('Категории', '/Downloads/Category/Insert'),
            3 => array('New File', '/Downloads/File/Insert'),
        ),

        6 => array(
            0 => array('Устранение неполадок', '/Troubleshooter/Category/ViewAll'),
            1 => array('Управление устранением неполадок', '/Troubleshooter/Step/Manage'),
            2 => array('Категории', '/Troubleshooter/Category/Manage'),
            3 => array('Новый шаг', ':UICreateWindow(\'/Troubleshooter/Step/InsertDialog/\', \'newstep\', \'Insert Step\', \'Loading..\', 400, 200, true);'),
        ),

        7 => array(
            0 => array('Новости', '/News/NewsItem/ViewAll', 'staff_nwcanviewitems'),
            1 => array('Управление новостями', '/News/NewsItem/Manage', 'staff_nwcanmanageitems'),
            2 => array('Категории', '/News/Category/Manage', 'staff_nwcanviewcategories'),
            3 => array('Подписчики', '/News/Subscriber/Manage', 'staff_nwcanviewsubscribers'),
            4 => array('Добавить новость', ':UICreateWindow(\'/News/NewsItem/InsertDialog/\', \'newnews\', \'Insert News\', \'Loading..\', 600, 420, true);'),
        ),

        8 => array(
            0 => array('Управление пользователями', '/Base/User/Manage', 'staff_canviewusers'),
            1 => array('Управление организациями', '/Base/UserOrganization/Manage', 'staff_canviewuserorganizations'),
            2 => array('Поиск', ':UIDropDown(\'usersearchmenu\', event, \'linkmenu8_2\', \'linksdiv\'); LinkUserSearchForms();'),
            3 => array('Добавить пользователя', '/Base/User/Insert', 'staff_caninsertuser'),
            4 => array('Добавить организацию', '/Base/UserOrganization/Insert', 'staff_caninsertuserorganization'),
            5 => array ('Import Users', '/Base/User/ImportCSV', 'staff_caninsertuser'),
        ),

        9 => array(
            0 => array('Управление отчетами', '/Reports/Report/Manage'),
            1 => array('Категории', '/Reports/Category/Manage'),
            2 => array('Новый отчет', ':UICreateWindow(\'/Reports/Report/InsertDialog/\', \'newreport\', \'New Report\', \'Loading..\', 400, 280, true);'),
        ),
    );

    $_staffLinkContainer[2][1][15] = true;
    $_staffLinkContainer[2][5][15] = true;
    $_staffLinkContainer[8][2][15] = true;
    $_staffLinkContainer[3][4][15] = true;

    SWIFT::Set('stafflinks', $_staffLinkContainer);
}




return $__LANG;
