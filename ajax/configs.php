<?php
include("../../../inc/includes.php");
Session::checkLoginUser();
use Glpi\Http\Response;
$config = new PluginReportsslaConfig();
if($_GET['active_sla'] == 'on')
{
  $config->setConfigs($_GET['active_sla']);
  echo json_encode(['status'=>true,'result'=>$_GET['active_sla']]);
}
elseif($_GET['active_sla'] == 'off')
{
  $config->setConfigs($_GET['active_sla']);
  echo json_encode(['status'=>true,'result'=>$_GET['active_sla']]);
}
else{
  http_response_code(400);
  die();
}
