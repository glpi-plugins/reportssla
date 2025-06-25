<?php
include("../../../inc/includes.php");
Session::checkLoginUser();
use Glpi\Http\Response;
$config = new PluginReportsslaConfig();
if($_GET['action'] == 'add')
{
  echo json_encode(['status'=>true,'result'=>$config->generateFields()]);
}
elseif($_GET['action'] == 'delete')
{
  if($config->deleteFields())
  {
    echo json_encode(['status'=>true,'result'=>0]);
  }
  else
  {
    echo json_encode(['status'=>false,'result'=>'error']);
  }

}
else{
  http_response_code(400);
  die();
}
