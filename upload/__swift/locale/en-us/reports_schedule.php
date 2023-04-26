<?php
/**
 * ###############################################
 *
 * SWIFT Framework
 * _______________________________________________
 *
 * @author        Andriy Lesyuk
 *
 * @package        SWIFT
 * @copyright    Copyright (c) 2001-2012, QuickSupport
 * @license        http://www.kayako.com/license
 * @link        http://www.kayako.com
 *
 * ###############################################
 */

$__LANG = array(
    'schedules' => 'Schedules',

    'exportformat' => 'Report format',
    'desc_exportformat' => 'The report will be generated (and emailed to you) in this format.',
    'recurrence' => 'Run report every',
    'desc_recurrence' => 'Specify how often the report should be run.',
    'firstexecutiondate' => 'First run date',
    'nextexecutiondate' => 'Next run date',
    'lastexecutiondate' => 'Last run date',
    'desc_executiondate' => 'Specify the first time this report should be run. Subsequent report runs will be calculated automatically.',
    'schedulecc' => 'Send to other email addresses',
    'desc_schedulecc' => 'The report will be sent to your email address. Optionally specify other email addresses.',

    'recurrence_once' => 'Once',
    'recurrence_hourly' => 'Hourly',
    'recurrence_daily' => 'Daily',
    'recurrence_weekly' => 'Weekly',
    'recurrence_bimonthly' => 'Every two weeks',
    'recurrence_monthly' => 'Monthly',
    'recurrence_quarterly' => 'Quarterly',
    'recurrence_yearly' => 'Yearly',

    'titleinvalidformat' => 'Invalid export format',
    'msginvalidformat' => 'Please select a valid export format.',
    'titleinvalidrecurrence' => 'Invalid recurrence',
    'msginvalidrecurrence' => 'Please select a valid recurrence type.',
    'titleinvalidexecutiondate' => 'Invalid first run date',
    'msginvalidexecutiondate' => 'Please specify valid value(s) for the date and time properties of the first run date.',
    'titlepastexecutiondate' => 'First run date is in the past',
    'msgpastexecutiondate' => 'Please select a date which is in the future for the first run.',
    'titleinvalidcc' => 'Invalid email address',
    'msginvalidcc' => 'Please check the syntax of the email addresses provided.',

    'notifyreportscheduleinsert' => 'Report scheduled successfully',
    'notifyreportscheduleupdate' => 'Report schedule updated',

    'ccemailaddresses' => 'Email addresses copied in',

    'reportsendoncesub' => 'Report: %s',
    'reportsendhourlysub' => 'Hourly report: %s',
    'reportsenddailysub' => 'Daily report: %s',
    'reportsendweeklysub' => 'Weekly report: %s',
    'reportsendbimonthlysub' => 'Two weeks report: %s',
    'reportsendmonthlysub' => 'Monthly report: %s',
    'reportsendquarterlysub' => 'Quarterly report: %s',
    'reportsendyearlysub' => 'Yearly report: %s',

    'reportnoattachment' => 'no attachment',
    'reportsendintro' => 'You received this email because you or someone on your behalf (%s) scheduled a helpdesk report to be run and delivered to your inbox automatically.',
    'reporterrorintro' => 'The report returned the following error',
    'reporterrorccnote' => 'Due to the above error this email was not sent to the other email addresses specified to receive copies of the report.',

    'noexportresultfound' => 'This report didn\'t produce any results, so there was nothing to export.',
);

return $__LANG;
