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

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_reportssla_install()
{
  global $DB;
  $version = plugin_version_reportssla();

  // Параметры автоматического действия
   $actionTitle = "generateFields SLA";
   $actionClass = "PluginReportsslaConfig"; // Имя класса
   $actionMethod = "generateFields"; // Метод класса

   // Регистрация действия
   CronTask::Register(
       $actionClass, // Объект (класс)
       $actionMethod, // Метод
       60, // Интервал (в минутах)
       [
           'comment'   => $actionTitle,
           'mode'      => 2, // Запуск по расписанию
           'parameter' => null,
       ]
   );


  //создать экземпляр миграции с версией
  $migration = new Migration($version['version']);
  //Create table only if it does not exists yet!
if (!$DB->tableExists('glpi_plugin_reportssla_fields')) {
   //table creation query
   $query = 'CREATE TABLE glpi_plugin_reportssla_fields (
               id INT(11) NOT NULL AUTO_INCREMENT,
               item_id INT(11) NOT NULL,
               itemtype VARCHAR(255) NOT NULL,
               slaremainsfield VARCHAR(10) NOT NULL,
               fishingforwaiting timestamp NULL DEFAULT NULL,
               slanold VARCHAR(10) NOT NULL,
               slaisviolated VARCHAR(10) NOT NULL,
               PRIMARY KEY  (id),
               UNIQUE KEY glpi_plugin_reportssla_fields_unique_item_id (item_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC';

   $DB->queryOrDie($query, $DB->error());
}
//Create table only if it does not exists yet!
if (!$DB->tableExists('glpi_plugin_reportssla_configs')) {
 //table creation query
 $query = 'CREATE TABLE glpi_plugin_reportssla_configs (
             id INT(11) NOT NULL AUTO_INCREMENT,
             active_sla INT(11) NOT NULL,
             date_creation TIMESTAMP NULL DEFAULT current_timestamp(),
	           date_mod TIMESTAMP NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
             PRIMARY KEY  (id)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC';

 $DB->queryOrDie($query, $DB->error());
}


//execute the whole migration
$migration->executeMigration();
    return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_reportssla_uninstall()
{
  global $DB;
  CronTask::Unregister('PluginReportsslaConfig','generateFields');
    $tables = [
        'fields',
        'configs'
      ];

      foreach ($tables as $table) {
        $tablename = 'glpi_plugin_reportssla_' . $table;
        //Create table only if it does not exists yet!
      if ($DB->tableExists($tablename)) {
        $DB->queryOrDie(
          "DROP TABLE `$tablename`",
          $DB->error()
        );
      }
    }

    return true;
}
function plugin_reportssla_redefine_menus($menu)
{
   if (empty($menu)) {
      return $menu;
   }

   $front_fields = Plugin::getPhpDir('reportssla', false) . "/front";
   if (array_key_exists('reportssla', $menu) === false) {
           $menu['reportssla'] = [
               'default'   => "$front_fields/report.form.php",
               'title'     => __('Отчет SLA', 'reportssla'),
               'content'   => [true],
               'icon'  =>"fa-fw ti ti-report",
               'page'  =>  "$front_fields/report.form.php",
           ];
       }

   return $menu;
}
function plugin_reportssla_init()
{
   error_log("PLUGIN_REPORTSSLA_INIT CALLED");

   $loader = \Glpi\Application\View\TemplateRenderer::getInstance()->getEnvironment()->getLoader();
   if ($loader instanceof \Twig\Loader\FilesystemLoader) {
       error_log("LOADER IS FilesystemLoader");
       $loader->prependPath(GLPI_ROOT . '/plugins/reportssla/templates');
       error_log("prependPath done");
   } else {
       error_log("LOADER IS NOT FilesystemLoader");
   }
   $paths = $loader->getPaths(\Twig\Loader\FilesystemLoader::MAIN_NAMESPACE);
    foreach ($paths as $p) {
        error_log("TWIG PATH: $p");
    }
}
