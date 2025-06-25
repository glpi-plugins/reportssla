<?php
include_once ("../../../inc/includes.php");
// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isInstalled('reportssla') || !$plugin->isActivated('reportssla')) {
   Html::displayNotFoundError();
}

  Html::header(
      __("Отчет SLA", "reportssla"),
      $_SERVER['PHP_SELF'],
    "config",
    "pluginreportsslamenucfg",
    "reportssla"
  );


Html::footer();
