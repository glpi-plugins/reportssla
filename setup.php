<?php

/**
 * -------------------------------------------------------------------------
 * ReportsSla plugin for GLPI
 * Copyright (C) 2024 by the ReportsSla Development Team.
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * --------------------------------------------------------------------------
 */
use Glpi\Plugin\Hooks;
define('PLUGIN_REPORTSSLA_VERSION', '0.0.2');

// Minimal GLPI version, inclusive
define("PLUGIN_REPORTSSLA_MIN_GLPI_VERSION", "10.0.0");
// Maximum GLPI version, exclusive
define("PLUGIN_REPORTSSLA_MAX_GLPI_VERSION", "10.0.99");

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_reportssla()
{
    global $PLUGIN_HOOKS;
    $profiles = Profile_User::getUserProfiles(Session::getLoginUserID());//массив профилей пользователя
    $PLUGIN_HOOKS['csrf_compliant']['reportssla'] = true;
    // Регистрируем хук для изменения значений полей перед выводом
  //  $PLUGIN_HOOKS[Hooks::POST_INIT]['reportssla'] = 'plugin_reportssla_pre_item_form';
    $PLUGIN_HOOKS['csrf_compliant']['reportsslacfg'] = true;

    // add entry to configuration menu
    if (Session::haveRight('entity', READ))
     {
    $PLUGIN_HOOKS['menu_toadd']['reportssla']['config'] = ['PluginReportsslaMenucfg'];
    $PLUGIN_HOOKS["menu_toadd"]['reportssla']['helpdesk'] = ['PluginReportsslaMenu'];
    }
    else
    {
      if(!in_array($profiles,array(['5'=>'5'],['10'=>'10'],['12'=>'12'],['13'=>'13'],['36'=>'36'])))return;
      $PLUGIN_HOOKS['redefine_menus']['reportssla'] = 'plugin_reportssla_redefine_menus';
    }

}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_reportssla()
{
    return [
        'name'           => 'Отчет SLA',
        'version'        => PLUGIN_REPORTSSLA_VERSION,
        'author'         => 'Roman Yahin',
        'license'        => '',
        'homepage'       => '',
        'requirements'   => [
            'glpi' => [
                'min' => PLUGIN_REPORTSSLA_MIN_GLPI_VERSION,
                'max' => PLUGIN_REPORTSSLA_MAX_GLPI_VERSION,
            ]
        ]
    ];
}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_reportssla_check_prerequisites()
{
    return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_reportssla_check_config($verbose = false)
{
    if (true) { // Your configuration check
        return true;
    }

    if ($verbose) {
        echo __('Installed / not configured', 'reportssla');
    }
    return false;
}
