<?php

namespace Local\RegisterOfMembers;

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
            $this->enqueueStylesheets();
            return '';
        });
    }

    /**
     * Method shows register of members
     */
    public function showRegisterOfMembersPluginMainFileMarkup(): void
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('dataTablesScript', REGISTRY_OF_MEMBERS_PLG_URL . 'js/datatables.min.js');
        wp_enqueue_script('registerOfMembersScript', REGISTRY_OF_MEMBERS_PLG_URL . 'js/script.js');
        $this->enqueueStylesheets();

        $this->addJSConstants();
        include_once REGISTRY_OF_MEMBERS_PLG_PATH . self::REGISTER_MARKUP_PATH;
    }

    /**
     * Method enqueue plugin styles
     */
    public function enqueueStylesheets()
    {
        wp_enqueue_style('registerOfMembersStyle', REGISTRY_OF_MEMBERS_PLG_URL . 'css/style.css');
    }

    private function addJSConstants()
    {
        echo '<script>
            let defaultTableItems = "' . get_option('itemsPerPage') . '",
                langFilePath = "' . REGISTRY_OF_MEMBERS_PLG_URL . 'js/ru.json";
        </script>';
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