<?php

namespace Local\RegisterOfMembers;

use DateTime;
use DateTimeZone;

/**
 * Logger class (singleton pattern)
 */
class Logger
{
    private static $instance = null;

    private static $arLog = [];

    /**
     * Private constructor, use getInstance()
     */
    private function __construct()
    {
    }

    /**
     * Method returns or creates logger instance
     *
     * @return Logger|null
     */
    public static function getInstance(): ?Logger
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Method echoes log to user
     */
    public function echoLogs(): void
    {
        $log = '';
        if (count(self::$arLog)) {
            foreach (self::$arLog as $arLogItem) {
                if ('main' === $arLogItem['type']) {
                    $log .= '<p style="color: green;">' . $arLogItem['record'] . '</p>';
                } else {
                    $log .= '<p style="color: red;">' . $arLogItem['record'] . '</p>';
                }
            }
        }
        echo $log;
    }

    /**
     * Method writes log to file
     */
    public function writeLogs(): void
    {
        if (count(self::$arLog)) {
            $oDate = new DateTime();
            $date = $oDate->setTimezone(new DateTimeZone('Europe/Moscow'))->format('d.m.Y H:i:s') . "\n";
            $fp = fopen(REGISTRY_OF_MEMBERS_PLG_PATH . 'logs/log.txt', 'a+');
            $log = '';
            foreach (self::$arLog as $arLogItem) {
                if ('main' === $arLogItem['type']) {
                    $log .= $arLogItem['record'] . "\n";
                } else {
                    $log .= 'Ошибка: ' . $arLogItem['record'] . "\n";
                }
            }
            fwrite($fp, $date . $log . "\n\n");
            fclose($fp);
        }
    }

    /**
     * Method write string to log file
     *
     * @param string $record Record to write
     * @param string $logType Log type ['main', 'error']
     */
    public function log(string $record, string $logType = 'main'): void
    {
        self::$arLog[] = ['type' => $logType, 'record' => $record];
    }

    /**
     * Private clone, use getInstance()
     */
    private function __clone()
    {
    }
}