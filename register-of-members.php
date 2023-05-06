<?php
/**
 * Plugin Name: Реестр членов организации
 * Plugin URI: http://github.com/pe2/register-of-members
 * Description: Вывод информации о членах СРО из выгрузки Lotus
 * Version: 1.0.0
 * Date released: 01/05/2023
 * Author: Alex M. Telezhkin
 * Author URI: http://github.com/pe2
 */

/*  Copyright 2023 Alex M. Telezhkin (email: telezhkin@gmail.com)

    Redistribution and use in source and binary forms, with or without modification, are
    permitted provided that the following conditions are met:

    1. Redistributions of source code must retain the above copyright notice, this list of
    conditions and the following disclaimer.

    2. Redistributions in binary form must reproduce the above copyright notice, this list
    of conditions and the following disclaimer in the documentation and/or other
    materials provided with the distribution.

    3. Neither the name of the copyright holder nor the names of its contributors may be
    used to endorse or promote products derived from this software without specific
    prior written permission.

    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND
    CONTRIBUTORS “AS IS” AND ANY EXPRESS OR IMPLIED WARRANTIES,
    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
    MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
    DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS
    BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
    CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT
    OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR
    BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
    LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
    NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
    SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

use Local\RegisterOfMembers\RegisterOfMembersPlugin;

define('REGISTRY_OF_MEMBERS_PLG_PATH', plugin_dir_path(__FILE__));
define('REGISTRY_OF_MEMBERS_PLG_URL', plugin_dir_url(__FILE__));
const REGISTRY_OF_MEMBERS_PLG_NAME = 'Реестр членов организации';

try {
    /**
     * Register of members plugin classes autoloader
     *
     * @param string $class Class name
     */
    function registerOfMembersAutoload(string $class): void
    {
        $filePath = plugin_dir_path(__FILE__) . 'classes/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($filePath)) {
            include_once $filePath;
        }
    }

    spl_autoload_register('registerOfMembersAutoload');
} catch (Exception $e) {
    echo 'Plugin "' . REGISTRY_OF_MEMBERS_PLG_NAME . '" autoload classes error: ' . $e->getMessage();
}

try {
    $registerOfMembersPlugin = new RegisterOfMembersPlugin();
    $registerOfMembersPlugin->run();
} catch (Exception $e) {
    echo 'Error in plugin: "' . REGISTRY_OF_MEMBERS_PLG_NAME . '"' . $e->getMessage();
}
