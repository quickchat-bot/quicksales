<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author         Andriy Lesyuk
 *
 * @package        SWIFT
 * @copyright      Copyright (c) 2001-2014, QuickSupport
 * @license        http://www.kayako.com/license
 * @link           http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'schedules'                  => 'Agendamentos',

    'exportformat'               => 'Formato do relatório',
    'desc_exportformat'          => 'O relatório será gerado (e enviado por email para si) neste formato.',
    'recurrence'                 => 'Executar relatório a cada',
    'desc_recurrence'            => 'Especifique a frequência de execução do relatório.',
    'firstexecutiondate'         => 'Data da primeira execução',
    'nextexecutiondate'          => 'Próxima data de execução',
    'lastexecutiondate'          => 'Data da última execução',
    'desc_executiondate'         => 'Especifique a primeira vez que este relatório deve ser executado. As execuções de relatório subsequentes serão calculadas automaticamente.',
    'schedulecc'                 => 'Enviar para outros endereços de email',
    'desc_schedulecc'            => 'O relatório será enviado para o seu endereço de email. Como opção, especifique outros endereços de email.',

    'recurrence_once'            => 'Uma vez',
    'recurrence_hourly'          => 'De hora a hora',
    'recurrence_daily'           => 'Diariamente',
    'recurrence_weekly'          => 'Semanalmente',
    'recurrence_bimonthly'       => 'A cada duas semanas',
    'recurrence_monthly'         => 'Mensal',
    'recurrence_quarterly'       => 'Trimestral',
    'recurrence_yearly'          => 'Anual',

    'titleinvalidformat'         => 'Formato de exportação inválido',
    'msginvalidformat'           => 'Selecione um formato de exportação válido.',
    'titleinvalidrecurrence'     => 'Periodicidade inválida',
    'msginvalidrecurrence'       => 'Selecione um tipo de periodicidade válido.',
    'titleinvalidexecutiondate'  => 'Data da primeira execução inválida',
    'msginvalidexecutiondate'    => 'Especifique valor(es) válido(s) para as propriedades de data e hora da data da primeira execução.',
    'titlepastexecutiondate'     => 'A data da primeira execução está no passado',
    'msgpastexecutiondate'       => 'Selecione uma data futura para a primeira execução.',
    'titleinvalidcc'             => 'Endereço de email inválido',
    'msginvalidcc'               => 'Verifique a sintaxe dos endereços de email fornecidos.',

    'notifyreportscheduleinsert' => 'Relatório agendado com sucesso',
    'notifyreportscheduleupdate' => 'Agendamento de relatório atualizado',

    'ccemailaddresses'           => 'Endereços de email copiados',

    'reportsendoncesub'          => 'Relatório: %s',
    'reportsendhourlysub'        => 'Relatório horário: %s',
    'reportsenddailysub'         => 'Relatório diário: %s',
    'reportsendweeklysub'        => 'Relatório semanal: %s',
    'reportsendbimonthlysub'     => 'Relatório quinzenal: %s',
    'reportsendmonthlysub'       => 'Relatório mensal: %s',
    'reportsendquarterlysub'     => 'Relatório trimestral: %s',
    'reportsendyearlysub'        => 'Relatório anual: %s',

    'reportnoattachment'         => 'nenhum anexo',
    'reportsendintro'            => 'Recebeu este email, pois você ou alguém em seu nome (%s) agendou a execução de um relatório de suporte técnico e a sua entrega automática na sua caixa de entrada.',
    'reporterrorintro'           => 'O relatório devolveu o seguinte erro',
    'reporterrorccnote'          => 'Devido ao erro acima, este email não foi enviado para os outros endereços de email especificados para receber cópias do relatório.',

    'noexportresultfound'        => 'Este relatório não produziu quaisquer resultados, pelo que não há nada a exportar.',
);

return $__LANG;
