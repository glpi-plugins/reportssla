<?php

include_once ("../../../inc/includes.php");

if (!Session::haveRight('entity', READ)) {
  Html::helpHeader(
      __("Отчет SLA", "reportssla"),
    //  $_SERVER['PHP_SELF'],
      "reportssla",
      "reportssla"
  );
}
else
{

  Html::header(
      __("Отчет SLA", "reportssla"),
      $_SERVER['PHP_SELF'],
      "helpdesk",
      "pluginreportsslamenu",
      "reportsslareportform"
  );

}

PluginReportsslaSla::displayForItem();
Html::footer();
