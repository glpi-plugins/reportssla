<?php


include_once ("../../../inc/includes.php");

if (!isset($_GET['item_type']) || !is_string($_GET['item_type']) || !is_a($_GET['item_type'], CommonGLPI::class, true)) {
    return;
}
if(isset($_GET['history']))
{
  PluginReportsslaSearch::getLogs($_GET['history'],$_GET['name']);
  return;
}
$itemtype = $_GET['item_type'];
$_GET["export_all"]   = 1;
$params = Search::manageParams($itemtype, $_GET);
PluginReportsslaSearch::showList($itemtype, $params);
