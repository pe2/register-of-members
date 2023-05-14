<?php

namespace Local\RegisterOfMembers;

/**
 * Class for working with plugin settings
 */
class RegisterOfMembersSettings extends RegisterOfMembersPlugin
{
    /** @var string Path to plugin settings page, 'options.php' is WP default*/
    private const SETTINGS_FILE_NAME = 'options.php';

    /**
     * Method handles plugin settings
     */
    public function handle()
    {
        // Create plugin settings menu
        add_action('admin_menu', [$this, 'registerOfMembersCreateMenu']);
        // Create plugin settings
        add_action('admin_init', [$this, 'registerOfMembersPluginSettings']);
        // Create ajax request to force file processing
        add_action('admin_print_footer_scripts', [$this, 'registerOfMembersProcessFilesAjaxRequest']);
        // Create method to handle ajax request
        add_action('wp_ajax_process_register_files', [$this, 'registerOfMembersProcessFilesAjaxHandler']);
    }

    /**
     * Method creates plugin menu link
     */
    public function registerOfMembersCreateMenu(): void
    {
        add_menu_page('Настройки плагина реестр членов СРО',
            'Реестр членов СРО',
            'administrator',
            'register-of-members/' . self::SETTINGS_FILE_NAME,
            [$this, 'registerOfMembersPluginOptionsMarkup'],
            REGISTRY_OF_MEMBERS_PLG_URL . 'img/register-icon-small.png'
        );
    }

    /**
     * Method registers plugin settings
     */
    public function registerOfMembersPluginSettings(): void
    {
        register_setting('register-of-members', 'itemsPerPage');
        register_setting('register-of-members', 'registerFilePath');
        register_setting('register-of-members', 'registerDetailFilePath');
        register_setting('register-of-members', 'registerBasePage');
        register_setting('register-of-members', 'registerDefaultUser');
        register_setting('register-of-members', 'registerTimeUpdate', [
                'sanitize_callback' => [$this, 'removeCronHook']
        ]);
    }

    /**
     * Method removes cron hook thus time can change
     *
     * @param $value
     *
     * @return mixed
     */
    public function removeCronHook($value)
    {
        wp_clear_scheduled_hook('register_of_members_cron_event');

        return $value;
    }

    /**
     * Method shows plugin options markup
     */
    public function registerOfMembersPluginOptionsMarkup(): void
    { ?>
        <div class="wrap">
            <h2>Настройки реестра членов саморегулируемой организации</h2>
            <form method="post" action="<?= self::SETTINGS_FILE_NAME; ?>">
                <?php
                settings_fields('register-of-members'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label
                                    title="Число организаций, которые будут выводиться на странице по умолчанию в постраничной навигации (20, 30, 50, 100, 250, 500). Например - 30"
                                    for="itemsPerPage">Количество компаний на странице</label></th>
                        <td><input type="text" name="itemsPerPage" id="itemsPerPage"
                                   value="<?= get_option('itemsPerPage'); ?>"/></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="registerFilePath"
                                               title="Путь от корня сайта для файла с общей информацией об организациях. Например - /wp-content/plugins/sro-registry/xml/reestr.xml">Путь
                                к файлу с общей информацией о компаниях</label>
                        </th>
                        <td><input type="text" name="registerFilePath" id="registerFilePath"
                                   value="<?= get_option('registerFilePath'); ?>"/></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="registerDetailFilePath"
                                               title="Путь от корня сайта для файла с детальной информацией об организациях. Например - /wp-content/plugins/sro-registry/xml/detail.xml">Путь
                                к файлу с детальной информацией о
                                компаниях</label></th>
                        <td><input type="text" name="registerDetailFilePath" id="registerDetailFilePath"
                                   value="<?= get_option('registerDetailFilePath'); ?>"/></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="registerBasePage"
                                               title="ID страницы с реестром для добавления дочерних статей с детальной информацией об организациях. Например - 12">ID
                                страницы с реестром</label></th>
                        <td><input type="text" name="registerBasePage" id="registerBasePage"
                                   value="<?= get_option('registerBasePage'); ?>"/></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="registerDefaultUser"
                                               title="ID пользователя по умолчанию для привязки при добавлении/редактировании статей. Например - 4">ID
                                пользователя по умолчанию</label></th>
                        <td><input type="text" name="registerDefaultUser" id="registerDefaultUser"
                                   value="<?= get_option('registerDefaultUser'); ?>"/></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="registerTimeUpdate"
                                               title="Время, в которое будут обновляться файлы реестра. Если часы или минуты меньше 9, необходим ведущий 0. Например - 14:09">Время обновления реестра</label></th>
                        <td><input type="text" name="registerTimeUpdate" id="registerTimeUpdate"
                                   value="<?= get_option('registerTimeUpdate'); ?>"/></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Сохранить изменения'); ?>"/>
                </p>
                <hr>
                <br>
                <p class="process">
                    <input type="button" class="button-secondary process-register-files"
                           value="<?php _e('Принудительная обработка файлов'); ?>"/>
                </p>
                <div class="process-files-log"></div>
            </form>
        </div>
        <?php
    }

    /**
     * Method performs ajax request to process register files
     */
    public function registerOfMembersProcessFilesAjaxRequest(): void
    {
        ?>
        <script>
            jQuery('.process-register-files').click(function () {
                jQuery('div.process-files-log').html('');
                jQuery.post(ajaxurl, {action: 'process_register_files'}, function (response) {
                    jQuery('div.process-files-log').html(response);
                });
            });
        </script>
        <?php
    }

    /**
     * Method handles process files ajax request
     */
    public function registerOfMembersProcessFilesAjaxHandler(): void
    {
        $oRegisterOfMembersPlugin = new RegisterOfMembersPlugin();
        $oRegisterOfMembersPlugin->processRegisterOfMembersFiles();
    }
}