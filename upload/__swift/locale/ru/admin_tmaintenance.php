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
    'tabpostindex'            => 'Восстановить индекс поиска',
    'tabmoveattachments'      => 'Переместить вложения',
    'attachmentsperpass'      => 'Допустимое количество вложений для одной страницы',
    'desc_attachmentsperpass' => 'Укажите количество вложений, которые можно прикрепить к одной странице. Kayako поочередно выполнит перебор всех вложений каждой страницы, затем перейдет на загруженные вложения. Чем больше вложений, тем мощнее должен быть сервер. Если система выдает ошибку во время процесса, используйте меньшее количество вложений.',
    'move'                    => 'Переместить',
    'dbtofiles'               => 'База данных (%d) => Файлы',
    'filestodb'               => 'Файлы (%d) => База данных',
    'movetype'                => 'Переместить вложения из',
    'desc_movetype'           => 'Выберите способ перемещения вложений. Если Вы хотите переместить вложения из файлов в базу данных, рекомендуется вначале проверить настройки максимального размера пакетов в вашей базе данных, чтобы избежать повреждений или потери данных.',
    'attachmentsprocessed'    => 'Перемещенные вложения',
    'totalattachments'        => 'Все вложения',
    'tabproperties'           => 'Восстановить параметры',
    'ticketsperpass'          => 'Размер пакета',
    'desc_ticketsperpass'     => 'Укажите количество заявок для обработки в одном пакете <i>(по умолчанию = 100).</i><br/><br/>В связи с ограничениями использования памяти и процессора не рекомендуется указывать значение больше чем сто (100).<br/><br/><font color="cc3300">Примечание: Во время обработки пакета, счетчик затраченного времени <i>не</i> будет обновляться, но процесс будет все равно происходить!',
    // ======= END v4 LOCALES =======
    'tickets'                 => 'Заявки',
    'maintenance'             => 'Управление',
    'rebuildpindex'           => 'Восстановить индекс',
    'indexseterror'           => 'Примечание: режим поиска по умолчанию применен ко всему тексту. Для того, чтобы использовать встроенный механизм поиска, измените настройки <b>типа поиска</b>: <i>Настройки> Заявки</i>.',
    'rebuild'                 => 'Восстановить',
    'postperpass'             => 'Размер пакета',
    'desc_postperpass'        => 'Введите количество ответов на заявки для обработки в одном пакете <i>(по умолчанию = 100).</i><br/><br/>В связи с ограничениями использования памяти и процессора, не рекомендуется указывать значение больше чем сто (100).<br/><br/><font color="cc3300">Примечание: Во время обработки пакета, счетчик затраченного времени <i>не</i> будет обновляться, но процесс будет все равно происходить!</font>',
    'reindexheader'           => '<b>Процесс переиндексации</b>',
    'totalposts'              => 'Общее # ответов в базе данных:',
    'totaltickets'            => 'Общее # заявок в базе данных:',
    'postsprocesed'           => '# обработанных ответов:',
    'ticketsprocessed'        => '# обработанных заявок:',
    'timeelapsed'             => 'Затраченное время:',
    'timeremaining'           => 'Оставшееся время (приблизительно):',
);

return $__LANG;