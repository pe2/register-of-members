<?php

namespace Local\RegisterOfMembers;

use DateTime;
use DateTimeZone;
use Exception;

/**
 * Base plugin class
 */
class RegisterOfMembersPlugin
{
    /** @var string Path to generated register of members markup file */
    public const REGISTER_MARKUP_PATH = 'markup/registerOfMembersMarkup.html';

    /**
     * Method init plugin
     */
    public function run()
    {
        $oRegisterOfMembersSettings = new RegisterOfMembersSettings();
        $oRegisterOfMembersSettings->handle();

        add_shortcode('registerOfMembersMarkupTag', array($this, 'showRegisterOfMembersPluginMainFileMarkup'));
        add_shortcode('registerOfMembersDetailEmptyTagForStyles', function () {
            $this->enqueueStylesheetsAndScripts();
            wp_enqueue_style('registerOfMembersStylePrint', REGISTRY_OF_MEMBERS_PLG_URL . 'css/print.css', [], false, 'print');
            wp_enqueue_script('registerOfMembersScript', REGISTRY_OF_MEMBERS_PLG_URL . 'js/detail.js');
            return '';
        });

        $this->handleCronPluginTask();
    }

    /**
     * Method enqueue plugin styles
     */
    public function enqueueStylesheetsAndScripts()
    {
        $this->addJSConstants();
        wp_enqueue_script('jquery');
        wp_enqueue_style('registerOfMembersStyle', REGISTRY_OF_MEMBERS_PLG_URL . 'css/style.css');
    }

    /**
     * Method echos some constants as js variables
     */
    private function addJSConstants()
    {
        echo '<script>
            let defaultTableItems = "' . get_option('itemsPerPage') . '",
                pluginPath = "' . REGISTRY_OF_MEMBERS_PLG_URL . '",
                langFilePath = "' . REGISTRY_OF_MEMBERS_PLG_URL . 'js/ru.json";
        </script>';
    }

    /**
     * Method appends hook for register update by WP cron
     */
    private function handleCronPluginTask()
    {
        add_action('wp', [$this, 'addCronEvent']);
        add_action('register_of_members_cron_event', [$this, 'processRegisterOfMembersFiles']);
    }

    /**
     * Method appends cron event for register update
     *
     * @throws Exception
     */
    function addCronEvent()
    {
        $oDate = new DateTime(get_option('registerTimeUpdate'), new DateTimeZone('Europe/Moscow'));
        // WP cron in UTC only
        $oDate->setTimezone(new DateTimeZone('UTC'));
        if (!wp_next_scheduled('register_of_members_cron_event')) {
            wp_schedule_event($oDate->format('U'), 'daily', 'register_of_members_cron_event');
        }
    }

    /**
     * Method shows register of members
     */
    public function showRegisterOfMembersPluginMainFileMarkup(): void
    {
        $this->enqueueStylesheetsAndScripts();
        wp_enqueue_script('dataTablesScript', REGISTRY_OF_MEMBERS_PLG_URL . 'js/datatables.min.js');
        wp_enqueue_script('registerOfMembersScript', REGISTRY_OF_MEMBERS_PLG_URL . 'js/datatable.js');

        $registerOfMembersMainMarkupFilePath = REGISTRY_OF_MEMBERS_PLG_PATH . self::REGISTER_MARKUP_PATH;
        if (file_exists($registerOfMembersMainMarkupFilePath)) {
            include_once $registerOfMembersMainMarkupFilePath;
        } else {
            echo '<p>Возникла проблема при отображении реестра.</p>';
        }
    }

    /**
     * Method calls files processing class
     */
    public function processRegisterOfMembersFiles(): void
    {
        $oRegisterOfMembersHandler = new RegisterOfMembersHandler();
        $oRegisterOfMembersHandler->processFiles();
        $oRegisterOfMembersHandler->preserveLogs();
        // File output only
        wp_die();
    }

    /**
     * Method handles log data
     */
    private function preserveLogs(): void
    {
        Logger::getInstance()->echoLogs();
        Logger::getInstance()->writeLogs();
    }

    /**
     * Method write string to log file
     *
     * @param string $record Record to write
     * @param string $logType Log type ['main', 'error']
     */
    public function log(string $record, string $logType = 'main'): void
    {
        Logger::getInstance()->log($record, $logType);
    }
}