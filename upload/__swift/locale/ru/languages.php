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
    // ======= BEGIN V4 LOCALES =======
    'languageisenabled'          => 'Активировано?',
    'desc_languageisenabled'     => 'Если эта опция активирована, язык будет отображаться в центре поддержки и в настройках профиля пользователя.',
    'titlemissingphrases'        => 'Обнаружены недостающие фразы',
    'msgmissingphrases'          => 'QuickSupport обнаружил "%d" недостающих фраз, рекомендуется обновить выбранный язык и добавить все недостающие фразы.',
    'tabimport'                  => 'Импорт',
    'tabexport'                  => 'Экспорт',
    'languagemen'                => 'Язык',
    'export'                     => 'Экспорт',
    'import'                     => 'Импорт',
    'squicksearch'               => 'Быстрый поиск',
    'titleupdatedlangphrases'    => 'Обновлены языковые фразы',
    'msgupdatedlangphrases'      => 'Фразу для "%s" языка успешно обновлены',
    'titlevcfailed'              => 'Не удалось проверить версию',
    'msgvcfailed'                => 'Загруженный языковой пакет был создан с использованием старой версии QuickSupport. Вы можете начать импорт/слияние, указав "Да" в настройке "Игнорировать версию".',
    'titlelangimpfailed'         => 'Не удалось произвести импорт/слияние',
    'msglangimpfailed'           => 'QuickSupport не смог произвести импорт/слияние Вашего языкового файла. Пожалуйста, проверьте Ваш XML файл на наличие всех искажённых тегов или данных.',
    'tabsearch'                  => 'Поиск',
    'tabgeneral'                 => 'Общие',
    'flagicon'                   => 'Значок флага',
    'desc_flagicon'              => 'Укажите URL изображения для значка флага. Стандартный пакет значков находится в каталоге themes/__global/flags. Вы можете использовать следующие переменные:<br><b>{$themepath}</b> - указатель URL для каталога изображений<br> - Пример: {$themepath}us.gif',
    'wineditlanguage'            => 'Редактировать язык: %s',
    'phrases'                    => 'Фразы',
    'tabphrases'                 => 'Фразы',
    'insertphrase'               => 'Добавить фразу',
    'desc_insertphrase'          => '',
    'tabgeneral'                 => 'Общие',
    'phraselanguage'             => 'Фразовый язык',
    'desc_phraselanguage'        => 'Выберите язык, в который нужно добавить эту фразу',
    'titlemergelang'             => 'Успешное слияние с языком',
    'msgmergelang'               => 'Произошло успешное слияние XML с языком "%s".',
    'titleimportlang'            => 'Язык успешно импортирован',
    'msgimportlang'              => 'XML языка успешно импортирован, информация о новом языке находится ниже:',
    'titlephraseinsert'          => 'Добавлена языковая фраза "%s"',
    'msgphraseinsert'            => 'Фраза "%s" успешно добавлена в языковую таблицу.<br /><b>Язык: </b>%s<br /><b>Код фразы:</b> %s<br /><b>Секция фразы:</b> %s<br /><b>Значение фразы:</b> %s',
    'titlephrasedel'             => 'Удалена языковая фраза',
    'msgphrasedel'               => 'Языковая фраза "%s" успешно удалена с языковой таблицы',
    'titleinsertlang'            => 'Добавлен язык: <b>%s</b>',
    'msginsertlang'              => 'Язык <b>"%s"</b> успешно добавлен, информация о новом языке находится ниже:',
    'titleupdatelang'            => 'Язык обновлен: <b>%s</b>',
    'msgupdatelang'              => 'Язык <b>"%s"</b> успешно обновлен, информация об обновленном языке находится ниже:',
    'titledellang'               => 'Удалено "%d" языков',
    'msgdellang'                 => 'Следующие языки были успешно удалены из базы данных.<br>',
    'compare'                    => 'Сравнить',
    'restorelanguage'            => 'Восстановить язык',
    'restoreconfirm'             => 'Вы уверены, что хотите восстановить фразы для этого языка по умолчанию?\\nВосстановление уничтожает весь перевод фраз выбранного языка.',
    'titlerestorephrase'         => 'Восстановлены языковые фразы',
    'msgrestorephrase'           => 'Все фразы "%s" языка успешно восстановлены по умолчанию.',
    'diagnostics'                => 'Диагностика',
    'tabmissingphrases'          => 'Недостающие фразы',
    'diagnosticslang1'           => 'Язык',
    'desc_diagnosticslang1'      => 'Пожалуйста, выберите основной язык для сравнения.',
    'diagnosticslang2'           => 'Сравнить с',
    'desc_diagnosticslang2'      => 'Пожалуйста, выберите язык, с которым нужно сравнить недостающие фразы. QuickSupport создаст список всех фраз, которых нет в основном языке и которые есть в языке сравнения.',
    'restorephrases'             => 'Восстановить фразы',
    'desc_restorephrases'        => '',
    'tabrestorephrases'          => 'Восстановить фразы',
    'lookup'                     => 'Поиск',
    'modified'                   => 'Изменено',
    'upgraderevert'              => 'Необходимо обновить',
    'notmodified'                => 'Не изменено',
    'titlenooptsel'              => 'Указан неверный тип фраз',
    'msgnooptsel'                => 'Необходимо выбрать как минимум один тип фраз для того, чтобы отфильтровать результаты.',
    'restore'                    => 'Восстановить',
    'titleunabledelmasterlang'   => 'Невозможно удалить',
    'msgunabledelmasterlang'     => 'QuickSupport не может удалить этот основной язык. Вы можете выбрать другой язык в группу языков по умолчанию, но Вы не можете удалить основной язык.',
    'phrasesection'              => 'Раздел',
    'desc_phrasesection'         => 'Укажите языковой раздел, в котором необходимо сохранить новую фразу.  Рекомендуется сохранить эти настройки в общей секции "по умолчанию", если Вы не хотите задать особый шаблон.',
    'phrasestatus'               => 'Статус фразы',
    'restorelanguage2'           => 'Восстановить языковые фразы: %s',
    'restorelanguage3'           => 'Язык: %s',
    'titlerestorephrases'        => 'Восстановлено"%d" фраз',
    'msgrestorephrases'          => 'Следующие языковые фразы были успешно восстановлены:',
    'phrasemissing'              => '-- НЕДОСТАЮЩИЕ --',
    // ======= END V4 LOCALES =======

    // ======= BEGIN v3 IMPORT =======
    'section'                    => 'Раздел',
    // ======= END v3 IMPORT =======

    'languages'                  => 'Языки',
    'languagedetails'            => 'Информация о языке',
    'desc_languages'             => '',
    'languagelist'               => 'Список языков',
    'languagetitle'              => 'Название языка',
    'desc_languagetitle'         => 'Введите название языка. <i>Пример: "Английский (США)"</i>',
    'authorname'                 => 'Автор',
    'desc_authorname'            => 'Укажите имя автора.',
    'isdefault'                  => 'По умолчанию',
    'desc_isdefault'             => 'Если эта опция включена, этот язык будет установлен как язык по умолчанию',
    'textdirection'              => 'Направление текста',
    'desc_textdirection'         => 'Укажите направление текста для этого языка.',
    'isocode'                    => 'ISO код',
    'desc_isocode'               => 'Введите  ISO код для языка. <a href="http://www.iso.org/iso/english_country_names_and_code_elements" target="_blank" rel="noopener noreferrer"> нажмите сюда</a> для получения списка  ISO кодов. Например: <em>en-США: Английский (США)</em>.',
    'languagecharset'            => 'Кодировка',
    'desc_languagecharset'       => 'Укажите HTML кодировку по умолчанию. Используйте <i>UTF-8</i> для языков, которым нужен Unicode.',
    'rtl'                        => 'Справа налево',
    'ltr'                        => 'Слева направо',
    'displayorder'               => 'Порядок отображения',
    'desc_displayorder'          => 'Если отображено несколько языков, они отображаются в порядке, указанном здесь (по возрастанию).',
    'insertlanguage'             => 'Добавить язык',
    'phrases'                    => 'Фразы',
    'invalidlanguageid'          => 'Неверный ID языка!',
    'languagedeleteconfirmation' => 'Язык "%s" успешно удален',
    'languageinsertconfirmation' => 'Язык "%s" успешно добавлен',
    'languageupdateconfirmation' => 'Язык "%s" успешно обновлен',
    'importexport'               => 'Импорт/экспорт',
    'exportlanguage'             => 'Экспортировать язык',
    'explanguage'                => 'Язык, который нужно экспортировать',
    'desc_explanguage'           => 'Выберите язык, который Вы хотите экспортировать.',
    'exportxml'                  => 'Экспорт',
    'importlanguage'             => 'Импортировать язык',
    'importxml'                  => 'Импорт',
    'languagefile'               => 'XML файл, который нужно импортировать',
    'desc_languagefile'          => 'Найдите языковой пакет, который Вы хотите импортировать.',
    'mergewith'                  => 'Способ импортирования',
    'desc_mergewith'             => '<i>Создайте новый язык:</i> Создайте полностью новый языковой пакет, с использованием XML файла.<br><br><i>Выберите язык:</i> Объедините фразы в XML файле с выбранным языком.',
    'ignoreversion'              => 'Пропустить проверку версии',
    'desc_ignoreversion'         => 'Выберите, хотите ли Вы, чтобы QuickSupport игнорировал информацию о версии в файле языкового пакета.  Рекомендуется не импортировать файлы языкового пакета, созданные для предыдущей версии QuickSupport.',
    'createnewlanguage'          => '-- Создайте новый язык --',
    'languageimportconfirmation' => 'Язык "%s" успешно импортирован',
    'managephrases'              => 'Управление фразами',
    'code'                       => 'Идентификатор',
    'value'                      => 'Текст',
    'phraseupdateconfirm'        => 'Фразы успешно обновлены',
    'managephrases'              => 'Управление фразами',
    'searchphrases'              => 'Поиск фраз',
    'languagesearching'          => 'Выполняется поиск...',
    'search'                     => 'Поиск',
    'codetext'                   => 'Идентификатор и текст',
    'query'                      => 'Поисковой запрос',
    'desc_query'                 => 'Укажите ваш поисковой запрос.',
    'searchtype'                 => 'Тип поиска',
    'desc_searchtype'            => '<i>Идентификатор и текст:</i> Искать в тексте фраз и идентификаторе.<br><br><i>Идентификатор:</i> Искать только в идентификаторе фр.',
    'searchlanguage'             => 'Язык поиска',
    'desc_searchlanguage'        => 'Выберите язык, в котором нужно осуществить поиск.',
    'versioncheckfailed'         => 'ОШИБКА: Не удалось проверить версию. Языковой пакет был создан для лдной из предыдущих версий QuickSupport',
    'addphrase'                  => 'Добавить фразу',
    'changelanguage'             => 'Перейти к языку',
    'desc_phrasecode'            => 'Пожалуйста, укажите уникальный идентификатор для новой фразы. <i>Пример: "mynewphrase"</i>.',
    'desc_phrasevalue'           => 'Укажите содержание фразы.  Здесь можно использовать HTML.',
    'phraseinsertconfirm'        => 'Фраза "%s" успешно добавлена',
    'languagejump'               => 'Перейти к языку',
    'language'                   => 'Язык',
    'deletephrase'               => 'Удалить фразу',
    'phrasedeleteconfirm'        => 'Фраза "%s" успешно удалена',
    'phrasedeletepopup'          => 'Вы уверены, что хотите удалить эту фразу? В результате удаления фразы может измениться способ отображения шаблонов!',
    'phrasedelfailure'           => 'ОШИБКА: невозможно удалить фразу',
    'invalidlanguagecode'        => 'Invalid language code',
    'invalidlanguagecodedesc'    => 'The language code is already in use',
    'novalue'                    => '[Нет основных значений]',
);

return $__LANG;
