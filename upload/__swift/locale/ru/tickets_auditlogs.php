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
    'duetimecleared'      => '- ОЧИЩЕНО -',

    // Activity Log
    'log_newreply'        => 'Ответили на заявку: #%s',
    'log_forward'         => 'Переслали заявку: #%s к: %s',
    'log_newticket'       => 'Создали заявку: #%s: %s',

    // Audit Log
    'al_newticket'        => 'Новая заявка, созданная %s (%s): тема: %s', // DONE
    'al_newticket_queue'  => 'Новая заявка, созданная %s (%s): тема: %s, очередь ожидания email: %s', // DONE
    'al_newreply'         => 'Новый ответ, созданный %s (%s)', // DONE
    'al_watch'            => 'Просмотр заявки активирован: #%s : %s', // DONE
    'al_owner'            => 'Владелец заявки изменен с: %s на: %s', // DONE
    'al_priority'         => 'Приоритет заявки изменен с: %s на: %s', // DONE
    'al_status'           => 'Статус заявки изменен с: %s на: %s', // DONE
    'al_statusautoclose'  => 'Статус заявки изменен с: %s на: %s в результате автоматического закрытия', // DONE
    'al_type'             => 'Тип заявки изменен с: %s на: %s', // DONE
    'al_department'       => 'Департамент заявки изменен с: %s на: %s', // DONE
    'al_due'              => 'Установка отведенного для решения времени: %s', // DONE
    'al_resolutiondue'    => 'Установка времени решения: %s', // DONE
    'al_sla'              => 'SLA план установлен на: %s', // DONE
    'al_slaclear'         => 'Ассоциированный SLA план удален', // DONE
    'al_flag'             => 'Заявка отмечена флажком: %s', // DONE
    'al_flagclear'        => 'Снята отметка с заявки', // DONE
    'al_duestaffoverdue'  => 'Заявка отмечена как просроченная', // DONE
    'al_duestaffclear'    => 'Очищено время, отведенное для решения заявки', // DONE
    'al_resduestaffclear' => 'Очищено время выполнения заявки', // DONE
    'al_ban'              => 'Создатель заявки запрещен: ID заявки: #%s, email адрес: %s', // DONE
    'al_merge'            => 'Заявка объединена: ID заявки: #%s, тема: %s, название: %s, email адрес: %s', // DONE
    'al_untrashticket'    => 'Заявка восстановлена из корзины: ID заявки: #%s, тема: %s, название: %s, email адрес: %s', // DONE
    'al_deleteticket'     => 'Удаленная заявка: ID заявки: #%s, тема: %s, название: %s, email адрес: %s', // DONE
    'al_trashticket'      => 'Заявка отправлена в корзину: ID заявки: #%s, тема: %s, название: %s, email адрес: %s', // DONE
    'al_updateticketpost' => 'Обновлено сообщение в заявке: создатель: %s (%s)', // DONE
    'al_deleteticketpost' => 'Удалено сообщение в заявке: создатель: %s (%s)', // DONE
    'al_ticketnote'       => 'Примечание добавлено в заявку', // DONE
    'al_usernote'         => 'К заявке добавлена заметка пользователя', // DONE
    'al_deletenote'       => 'Заметка пользователя удалена', // DONE
    'al_updatenote'       => 'Заметка пользователя обновлена', // DONE
    'al_delbilling'       => 'Запись о биллинге заявки удалена', // DONE
    'al_ticketbilling'    => 'Запись о биллинге добавлена к заявке ', // DONE
    'al_updticketbilling' => 'Запись о биллинге заявки обновлена', // DONE
    'al_delfollowup'      => 'Запланированное действие для заявки удалено', // DONE
    'al_createfollowup'   => 'Запланированное действие для заявки создано', // DONE
    'al_updateproperties' => 'Параметры заявки обновлены: тема: %s > %s, название: %s > %s, email адрес: %s > %s',

    'al_newforward'       => 'Создано новое сообщение переадресации %s (%s)',
    'al_escalated'        => 'Заявка передана на рассмотрение высшей инстанции в соответствии с правилом: %s',
    'al_print'            => 'Заявка напечатана: %s',
    'al_cleardraft'       => 'Черновик заявки удален: %s',
    'al_savedraft'        => 'Черновик заявки сохранен: %s',
    'al_forward'          => 'Заявка переадресована: %s : %s',
    'al_recipientdel'     => 'Получатель заявки: %s удален: %s',
    'al_timetrackdel'     => 'Запись отслеживания времени #%s удалена: %s',
    'al_timetrack'        => 'Запись отслеживания времени добавлена для: %s : %s (отработанное время: %s время подлежащее оплате: %s)',
    'al_prule'            => 'Выполнено в соответствии с правилом email: %s',
    'al_statusac'         => 'Статус заявки изменен с: %s на: %s в результате автоматического закрытия',
    'al_xmlexport'        => 'Заявка экспортирована в формате XML: %s',
    'al_pdfexport'        => 'Заявка экспортирована в формате PDF: %s',
);

return $__LANG;
