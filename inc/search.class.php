<?php
require '../vendor/autoload.php';
/*
  Метод смещает дату на любое количество часов с учет производственного графика
  $dateFinishSla = Входящая дата от которой будет производиться отчет в формате "Y-m-d H:i:s"
  $hours = Количество часов на которые будет смещаться дата тип integer или float
  $slaName = наименование Стандарта SLA тип string
  getDateSlaFinishForWaitng($dateFinishSla = null,$hours = null,$slaName = "")

  Метод возвращает количество часов в приостановке
  $id = id обращения
  getWaitingCountTime($id)
*/
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Glpi\Csv\LogCsvExport;
class PluginReportsslaSearch extends Search
{
  /**
  * Display result table for search engine for an type
  *
  * @param class-string<CommonDBTM> $itemtype Item type to manage
  * @param array  $params       Search params passed to
  *                             prepareDatasForSearch function
  * @param array  $forcedisplay Array of columns to display (default empty
  *                             = use display pref and search criteria)
  *
  * @return void
  **/

  public static function showList(
    $itemtype,
    $params,
    array $forcedisplay = []
  ) {
    $data = self::getDatas($itemtype, $params, $forcedisplay);
    unset($_SESSION['glpisearch'][$itemtype]['criteria']);
    $_SESSION['glpisearch'][$itemtype]['criteria'][] = [
      'field' => '12',
      'searchtype' => 'equals',
      'value' => 'notold'
    ];
  //  file_put_contents(GLPI_ROOT.'/logtmp/tmp.log',PHP_EOL.PHP_EOL."[".date("Y-m-d H:i:s")."] ". json_encode($data,JSON_UNESCAPED_UNICODE), FILE_APPEND);
    switch ($data['display_type']) {
      case self::CSV_OUTPUT:
      case self::PDF_OUTPUT_LANDSCAPE:
      case self::PDF_OUTPUT_PORTRAIT:
      case self::SYLK_OUTPUT:
      case self::NAMES_OUTPUT:

    //file_put_contents(GLPI_ROOT.'/tmp/buffer.txt',PHP_EOL.PHP_EOL. json_encode($data,JSON_UNESCAPED_UNICODE), FILE_APPEND);
      //  die(json_encode($data['toview'],JSON_UNESCAPED_UNICODE));
        self::outputData($data['data']);



      break;
      case self::GLOBAL_SEARCH:
      case self::HTML_OUTPUT:
      default:
      //  self::displayData($data);
      break;
    }
  }
  public static function getLogs($itilcategories,$name)
  {
    include('../../../inc/includes.php');
    global $DB;
    // Read params

  //  file_put_contents('../../../tmp/buffer.txt',PHP_EOL.PHP_EOL. json_encode($getloghistory->getFileContent(),JSON_UNESCAPED_UNICODE), FILE_APPEND);
$tickets = $DB->request([
        'FROM' => 'glpi_tickets',
        'WHERE'=> [
          'status'=>[1,2,3,4],
          'itilcategories_id'=>explode(';',$itilcategories)
        ]
    ]);
  //  file_put_contents('../../../tmp/buffer.txt',PHP_EOL.PHP_EOL. json_encode($tickets,JSON_UNESCAPED_UNICODE), FILE_APPEND);
    \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder( new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder() );
      $spreadsheet = new Spreadsheet();
    $i=1;
    foreach ($tickets as $key => $value) {
        $activeWorksheet = "activeWorksheet$i";
        $ticket_id = $value['id'];
      if($i==1)
      {
         $$activeWorksheet = $spreadsheet->getActiveSheet()->setTitle("$ticket_id");
      }
      else
      {
        $$activeWorksheet = $spreadsheet->createSheet()->setTitle("$ticket_id");
      }

       $$activeWorksheet->setCellValue('A1', 'id');
       $$activeWorksheet->setCellValue('B1', 'дата');
       $$activeWorksheet->setCellValue('C1', 'Пользователь');
       $$activeWorksheet->setCellValue('D1', 'Поле');
       $$activeWorksheet->setCellValue('E1', 'Обновление');
       $row=2;
       $itemtype = 'Ticket';
       $id       = $ticket_id ;
       $filter   = [];
       $item = $itemtype::getById($id);
       $getloghistory = new LogCsvExport($item, $filter);
       foreach ($getloghistory->getFileContent() as $log)
       {
           $$activeWorksheet->setCellValue('A'.$row, $log['id']);
           $$activeWorksheet->setCellValue('B'.$row, $log['date_mod']);
           $$activeWorksheet->setCellValue('C'.$row, $log['user_name']);
           $$activeWorksheet->setCellValue('D'.$row, $log['field']);
           $$activeWorksheet->setCellValue('E'.$row, $log['change']);
           $row++;
       }
       $i++;
    }


             $writer = new Xlsx($spreadsheet);

             header('Content-type: application/ms-excel');
             header("Content-Disposition: attachment; filename=Отчет Истории $name.xlsx");
             header('Cache-Control: max-age=0');
             $writer->save('php://output');

  }
  public static function outputData($data = [])
  {
      global $DB;
    //  die(json_encode(self::getWaitingCountTime(1050)));
    \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder( new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder() );
            $spreadsheet = new Spreadsheet();
             $activeWorksheet = $spreadsheet->getActiveSheet()->setTitle("Выгрузка обращений");
             $activeWorksheet->setCellValue('B1', 'Прошло более 80% времени от SLA');
             $activeWorksheet->setCellValue('B2', 'SLA Нарушен');
             $activeWorksheet->getStyle("B1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F4B084');
             $activeWorksheet->getStyle("B2")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FC9696');
             $spreadsheet->getActiveSheet()->getColumnDimension("B")->setWidth(50);
             $activeWorksheet->setCellValue('A4', 'ID');
             $activeWorksheet->setCellValue('B4', 'Краткое описание');
             $activeWorksheet->setCellValue('C4', 'Статус');
             $activeWorksheet->setCellValue('D4', 'Приоритет');
             $activeWorksheet->setCellValue('E4', 'Дата открытия');
             $activeWorksheet->setCellValue('F4', 'Инициатор обращения');
             $activeWorksheet->setCellValue('G4', 'Исполнитель обращения');
             $activeWorksheet->setCellValue('H4', 'Категория');
             $activeWorksheet->setCellValue('I4', 'Предприятие инициатора');
             $activeWorksheet->setCellValue('J4', 'Дата решения');
             $activeWorksheet->setCellValue('K4', 'Общее время решения обращения');
             $activeWorksheet->setCellValue('L4', 'Время решения с вычетом приостановки');
             $activeWorksheet->setCellValue('M4', 'Время назначения исполнителя');
             $activeWorksheet->setCellValue('N4', 'Время в приостановке');
             $activeWorksheet->setCellValue('O4', 'Стандарт SLA');
             $activeWorksheet->setCellValue('P4', 'Остаток SLA, час');
             $activeWorksheet->setCellValue('Q4', 'Просрочено, час');
             $activeWorksheet->setCellValue('R4', 'Истечение SLA');
             $activeWorksheet->setCellValue('S4', 'Истечение SLA с учетом приостановки');
             $activeWorksheet->setCellValue('T4', 'SLA более 80%');
             $activeWorksheet->setCellValue('U4', 'Нарушение SLA');
             $activeWorksheet->getStyle('A4:U4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('305496');
             $activeWorksheet->getStyle('A4:U4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
             for ($i = 'C'; $i !=  $spreadsheet->getActiveSheet()->getHighestColumn(); $i++) {
                  if($i == 'F' || $i == 'G')
                  {
                    $spreadsheet->getActiveSheet()->getColumnDimension("F")->setWidth(38);
                    $spreadsheet->getActiveSheet()->getColumnDimension("G")->setWidth(38);
                  }
                  else
                  {
                    $spreadsheet->getActiveSheet()->getColumnDimension($i)->setAutoSize(TRUE);
                  }

             }
             $row = 5;
             $spreadsheet->getActiveSheet()->freezePane('A'.$row);
             $status = ['1'=>'Новое','2'=>'Назначено','3'=>'В работе','4'=>'Приостановка','5'=>'Решено'];
              foreach ($data['rows'] as $key => $value)
              {
                $ticket_descr = $value['Ticket_21']['displayname']; //полное описание заявки
              //  file_put_contents(GLPI_ROOT.'/logtmp/tmp.log',PHP_EOL.PHP_EOL."[".date("Y-m-d H:i:s")."] ". json_encode($value['Ticket_21']['displayname'],JSON_UNESCAPED_UNICODE), FILE_APPEND);
                //проверка , если поле инициатор предприятие в заявке не заполнен , то проверяем его в профиле пользователя который создал обращение, и от него уже подставляем значение предприятие инициатора
                if(!$value['raw']['ITEM_Ticket_76667'])
                {
                  $field = $DB->request([
                          'FROM' => 'glpi_plugin_fields_userusers',
                          'WHERE'=> [
                            'itemtype'=>'User',
                            'items_id'=>$value['raw']['ITEM_Ticket_4'],
                          ]
                      ]);
                   if ($field->numrows() > 0) {
                     $orginiciatorfielddropdowns_id = end(iterator_to_array($field))['plugin_fields_orginiciatorfielddropdowns_id'];
                     $orginiciatorName = $DB->request([
                             'FROM' => 'glpi_plugin_fields_orginiciatorfielddropdowns',
                             'WHERE'=> [
                               'id' => $orginiciatorfielddropdowns_id
                             ]
                         ]);

                     if($orginiciatorfielddropdowns_id)
                     {
                       $DB->update(
                               'glpi_plugin_fields_tickettickets', // имя таблицы
                               [
                                   'plugin_fields_orginiciatorfielddropdowns_id' => $orginiciatorfielddropdowns_id  // какие поля обновлять
                               ],
                               [
                                   'itemtype' => 'Ticket',
                                   'items_id' => $value['raw']['ITEM_Ticket_2']  // условие обновления
                               ]
                           );
                        $value['Ticket_76667']['displayname'] = end(iterator_to_array($orginiciatorName))['name'];
                     }

                  } else {
                      die(json_encode([ 'error' => 'Предприятие инициатора не заполнено у пользователя и в заявке #'.$value['raw']['ITEM_Ticket_2'] ], JSON_UNESCAPED_UNICODE));
                  }
                }
                //Общее время решения обращения
                if($value['raw']['ITEM_Ticket_30']){
                  if(!empty($value['raw']['ITEM_Ticket_17']))
                  {

                     $value['Ticket_45']['displayname'] =  self::getHoursForCalendar($value['raw']['ITEM_Ticket_15'],$value['raw']['ITEM_Ticket_17'],$value['raw']['ITEM_Ticket_30']);
                 }
                 else
                 {

                    $value['Ticket_45']['displayname'] =  self::getHoursForCalendar($value['raw']['ITEM_Ticket_15'],date("Y-m-d H:i:s"),$value['raw']['ITEM_Ticket_30']);
                  }
                }
                else
                {
                  $value['Ticket_45']['displayname'] = 0;
                }
                //Время в приостановке
                $value['raw']['ITEM_Ticket_153']   = 0;


                  foreach (self::getWaitingCountTime($value['id']) as $key => $v) {
                    if(isset($v['end']) && !empty($value['raw']['ITEM_Ticket_30']))
                    {
                      if(!isset($v['start']))
                      {
                        continue;
                      }
                      $value['raw']['ITEM_Ticket_153'] += self::getHoursForCalendar($v['start'],$v['end'],$value['raw']['ITEM_Ticket_30']);
                    }
                    if(!isset($v['end']) && !empty($value['raw']['ITEM_Ticket_30']))
                    {
                      $value['raw']['ITEM_Ticket_153'] += self::getHoursForCalendar($v['start'],date("Y-m-d H:i:s"),$value['raw']['ITEM_Ticket_30']);
                    }
                  }


                  //Время назначения исполнителя
                  $value['raw']['ITEM_Ticket_150']   = 0;
                 if(self::getAssignedTime($value['id']) && !empty($value['raw']['ITEM_Ticket_30']))
                 {
                   $value['raw']['ITEM_Ticket_150'] += self::getHoursForCalendar($value['raw']['ITEM_Ticket_15'],self::getAssignedTime($value['id']),$value['raw']['ITEM_Ticket_30']);
                 }
                 ////Время решения с вычетом приостановки

                 $value['raw']['ITEM_Ticket_154'] = round($value['Ticket_45']['displayname'] - $value['raw']['ITEM_Ticket_153'],1);

                //Получаем время SLA
                $sla = 0;

                if($value['raw']['ITEM_Ticket_30'])
                {

                  if(strpos($value['raw']['ITEM_Ticket_30'], "SLA-") !== FALSE)
                  {
                    $sla = intval( explode("-",$value['raw']['ITEM_Ticket_30'])[1]);
                  }
                }
                //Оставшееся время от SLA
                if($value['raw']['ITEM_Ticket_154'] && $sla)
                {
                  $value['sla']['remains'] = $sla - $value['raw']['ITEM_Ticket_154'];
                  if($value['sla']['remains'] < 0)
                    {
                      $value['sla']['overdue'] = -$value['sla']['remains'];
                      $value['sla']['remains']=0;
                    }
                    else
                    {
                      $value['sla']['overdue'] = 0;
                    }

                }else{

                  $value['sla']['remains'] = $value['sla']['overdue'] = 0;
                }
                //SLA более 80%
                $value['sla_nold'] = "Нет";
                if($value['sla']['remains'] && $sla)
                {
                  $value['sla_nold']  = ($value['sla']['remains'] / $sla) <= 0.2?"Да":"Нет";
                }
                //Истечение sla
                if($sla)
                {
                  $value['raw']['ITEM_Ticket_18'] = self::getDateSlaFinishForWaitng($value['raw']['ITEM_Ticket_15'],$sla,$value['raw']['ITEM_Ticket_30']);
                }
                else
                {
                  $value['raw']['ITEM_Ticket_18'] = 0;
                }
                //Истечение SLA с учетом приостановки
                if($value['raw']['ITEM_Ticket_18'] && $value['raw']['ITEM_Ticket_30'])
                {
                  $value['sla']['fishing_for_waiting'] = self::getDateSlaFinishForWaitng($value['raw']['ITEM_Ticket_18'], $value['raw']['ITEM_Ticket_153'],$value['raw']['ITEM_Ticket_30']);
                }
                else
                {
                  $value['sla']['fishing_for_waiting'] = "";
                }
                //Истечение sla
              //  $value['raw']['ITEM_Ticket_18'] = $value['raw']['ITEM_Ticket_1_status']==4?'Не применяется, обращение приостановлено':$value['raw']['ITEM_Ticket_18'];
                $value['sla']['fishing_for_waiting'] = $value['raw']['ITEM_Ticket_1_status']==4?'Не применяется, обращение приостановлено':$value['sla']['fishing_for_waiting'];
              //Нарушение sla
              if($value['sla']['overdue'])
              {
                $value['Ticket_82']['displayname'] = $value['sla_nold'] = "Да";
              }
              else
              {
                $value['Ticket_82']['displayname'] = "Нет";
              }


                $users_assign_name = [];
                if(is_array($value['Ticket_5']))
                {
                  foreach ($value['Ticket_5'] as $uid) {
                    if(is_array($uid))
                    {
                      $user_tmp = new User();
                      if ($user_tmp->getFromDB($uid['name'])) {
                        $users_assign_name[] = $user_tmp->getName();
                      }
                    }
                  }
                  if(count($users_assign_name) > 0)
                  {
                    $users_assign_name = implode("; ".PHP_EOL,$users_assign_name);
                  }
                  else
                  {
                    $users_assign_name = "";
                  }
                }

                $ITEM_Ticket_3 = [
                  1 => 'Очень низкий',
                  2 => 'Низкий',
                  3 => 'Средний',
                  4 => 'Высокий',
                  5 => 'Очень высокий',
                  6 => 'Наивысший',
                ];


                $activeWorksheet->setCellValue('A'.$row, $value['raw']['id']);
                $activeWorksheet->setCellValue('B'.$row, trim($ticket_descr));//Полное описание
                $activeWorksheet->setCellValue('C'.$row, $status[$value['raw']['ITEM_Ticket_1_status']]);//Статус
                  $activeWorksheet->setCellValue('D'.$row, $ITEM_Ticket_3[$value['raw']['ITEM_Ticket_3']]);//Приоритет
                $activeWorksheet->setCellValue('E'.$row, $value['raw']['ITEM_Ticket_15']);//Дата открытия
                $spreadsheet->getActiveSheet()->getStyle('E'.$row)
                ->getNumberFormat()
                ->setFormatCode('dd/mm/yyyy hh:mm');
                $activeWorksheet->setCellValue('F'.$row, explode("<a",$value['Ticket_4']['displayname'])[0]);//Инициатор обращения
                $activeWorksheet->setCellValue('G'.$row, $users_assign_name);//Исполнитель обращения
                $activeWorksheet->setCellValue('H'.$row, $value['raw']['ITEM_Ticket_7']);//Категория
                $activeWorksheet->setCellValue('I'.$row, $value['Ticket_76667']['displayname']);//Предприятие инициатора
                $activeWorksheet->setCellValue('J'.$row, $value['raw']['ITEM_Ticket_17']);//Дата решения
                $spreadsheet->getActiveSheet()->getStyle('J'.$row)
                ->getNumberFormat()
                ->setFormatCode('dd/mm/yyyy hh:mm');
                $activeWorksheet->setCellValue('K'.$row, $value['Ticket_45']['displayname']);//Общее время решения обращения
                $activeWorksheet->setCellValue('L'.$row, $value['raw']['ITEM_Ticket_154']);//Время решения с вычетом приостановки
                $activeWorksheet->setCellValue('M'.$row, $value['raw']['ITEM_Ticket_150']);//Время назначения исполнителя
                $activeWorksheet->setCellValue('N'.$row, $value['raw']['ITEM_Ticket_153']);//Время в приостановке
                $activeWorksheet->setCellValue('O'.$row, $value['Ticket_30']['displayname']);//Стандарт SLA
                $activeWorksheet->setCellValue('P'.$row, $value['sla']['remains']);//Остаток SLA, час.
                $activeWorksheet->setCellValue('Q'.$row, $value['sla']['overdue']);//Просрочено, час
                $activeWorksheet->setCellValue('R'.$row, $value['raw']['ITEM_Ticket_18']);//Истечение SLA
                $spreadsheet->getActiveSheet()->getStyle('R'.$row)
                ->getNumberFormat()
                ->setFormatCode('dd/mm/yyyy hh:mm');
                $activeWorksheet->setCellValue('S'.$row, $value['sla']['fishing_for_waiting']);//Истечение SLA с учетом приостановки
                $spreadsheet->getActiveSheet()->getStyle('S'.$row)
                ->getNumberFormat()
                ->setFormatCode('dd/mm/yyyy hh:mm');
                $activeWorksheet->setCellValue('T'.$row, $value['sla_nold']);//SLA более 80%
                $activeWorksheet->setCellValue('U'.$row, $value['Ticket_82']['displayname']);//Нарушение SLA
                if($value['Ticket_82']['displayname'] == "Да")
                {
                  $activeWorksheet->getStyle("A$row:U$row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FC9696');
                }
                if($value['sla_nold'] == "Да" && $value['Ticket_82']['displayname'] == "Нет")
                {
                  $activeWorksheet->getStyle("A$row:U$row")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F4B084');
                }
                $row++;

    }
  $spreadsheet->getActiveSheet()->setAutoFilter('A4:U'.$row-4);
      $writer = new Xlsx($spreadsheet);

      header('Content-type: application/ms-excel');
      header("Content-Disposition: attachment; filename=Отчет SLA от ".date("Y-m-d h:i").".xlsx");
      header('Cache-Control: max-age=0');
      $writer->save('php://output');

  }
  public static function getDateSlaFinishForWaitng($dateFinishSla = null,$hours = null,$slaName = "")
  {ini_set("memory_limit", -1);
    //set_time_limit(60);
    $calendars_id = self::getSlaCalendarId($slaName);
    $holidays = self::getHolidays($calendars_id);
    $dateFinishSla = new DateTime($dateFinishSla);
    $getSlaCalendarsSegments = self::getSlaCalendarsSegments($calendars_id);
    $data = [];
    $second = $count = round(($hours*60)*60,0);
    //$minutes = $hours*60;
    $weeks = [];
    foreach ($getSlaCalendarsSegments as $key => $value) {
      $weeks[$value["day"]] = $value;
    }
    for ( $i=0; $i < $count; )
    {

       if(
         in_array($dateFinishSla->format("Y-m-d"),$holidays)
         || $dateFinishSla->format("w")=='6'
         || $dateFinishSla->format("w")=='0'
         )
         {
           if(strtotime($dateFinishSla->format("H:i:s"))>strtotime("18:00:00"))
           {
             $time = strtotime($dateFinishSla->format("H:i:s")) - strtotime("18:00:00");


          //   $min = ($time/60);
             $dateFinishSla->sub(new DateInterval("PT".$time."S"));
             $dateFinishSla->add(new DateInterval("PT15H"));
           }
           elseif(strtotime($dateFinishSla->format("H:i:s"))<strtotime("09:00:00"))
           {
             $time = strtotime("09:00:00") - strtotime($dateFinishSla->format("H:i:s"));


            // $min = ($time/60);
             $dateFinishSla->add(new DateInterval("PT".$time."S"));
             $dateFinishSla->add(new DateInterval('P1D'));
           }
           elseif(!$i)
           {
              $time = strtotime($dateFinishSla->format("H:i:s")) - strtotime("09:00:00");
            //  $min = ($time/60);

              $dateFinishSla->sub(new DateInterval("PT".$time."S"));
              $dateFinishSla->add(new DateInterval('P1D'));
           }
           else
           {
             $dateFinishSla->add(new DateInterval('P1D'));
           }


           continue;
         }

             if(isset($weeks[$dateFinishSla->format('w')]))
             {
               $beginTime = strtotime($weeks[$dateFinishSla->format('w')]['begin']);
               $endTime = strtotime($weeks[$dateFinishSla->format('w')]['end']);
               $currentTime = strtotime($dateFinishSla->format("H:i:s"));
              if(
                $currentTime >= $beginTime
                && $currentTime <= $endTime
              )
              {
                $time = $endTime - $currentTime;
                if($time <= $second)
                {
                  $second -= $time;
                  $i += $time;
                  //$min = ($time/60);

                   $dateFinishSla->add(new DateInterval("PT".$time."S"));
                   $dateFinishSla->add(new DateInterval("PT15H"));


                }
                else
                {
                  $i += $second;
                  //$min = $second/60;

                 $dateFinishSla->add(new DateInterval("PT".$second."S"));
                }
                continue;

              }
              if ($currentTime < $beginTime)
              {
                $time = $beginTime - $currentTime;
            //    $second -= $time;
            //    $i += ($time/60);
              //  $min = ($time/60);
                $dateFinishSla->add(new DateInterval("PT".$time."S"));
                continue;
              }
              if ($currentTime > $endTime)
              {
                $time = $currentTime - $endTime;
              //  $second -= $time;
            //    $i += ($time/60);
              //  $min = ($time/60);
                $dateFinishSla->sub(new DateInterval("PT".$time."S"));
                $dateFinishSla->add(new DateInterval("PT15H"));
                continue;
              }


            }


    }

    return $dateFinishSla->format("Y-m-d H:i:s");
  }

  public static function getWaitingCountTime($id)
  {
    global $DB;
    $ticket = $DB->request([
            'FROM' => 'glpi_logs',
            'WHERE'=> [
              'itemtype'=>'Ticket',
              'items_id'=>$id,
              'id_search_option'=>12
            ]
        ]);
        $data = [];
        $i = 0;
        foreach ($ticket as $key => $value) {
          if($value['new_value']=='4')
          {
            $data[$i]['start'] = $value['date_mod'];
          }
          if($value['old_value']=='4')
          {
            $I = $i-1;
            $data[$I]['end'] = $value['date_mod'];
          }
          $i++;
        }
    return $data;

  }
  public static function getAssignedTime($id)
  {
    global $DB;
    $ticket = $DB->request([
            'FROM' => 'glpi_logs',
            'WHERE'=> [
              'itemtype'=>'Ticket',
              'items_id'=>$id,
              'id_search_option'=>12
            ]
        ]);
        $data = 0;
        foreach ($ticket as $key => $value) {
          if($value['new_value']=='2')
          {
            $data = $value['date_mod'];
            break;
          }
        }
    return $data;
  }
  public static function getHoursForCalendar($date1 = null,$date2 =null,$slaName = "")
  {
    $calendars_id = self::getSlaCalendarId($slaName);
    $getSlaCalendarsSegments = self::getSlaCalendarsSegments($calendars_id);
    $period = new DatePeriod(
     new DateTime($date1),
     new DateInterval('P1D'),
     new DateTime($date2)
);
    $date1 = new DateTime($date1);
    $date2 = new DateTime($date2);
    if($date1 == $date2)
    {
      return '0';
    }
    $weekWork = [];
    foreach ($getSlaCalendarsSegments as $val) {
      $weekWork[$val['day']] = $val;
    }
    $startWeekDay = 0;
    $endWeekDay   = 0;
    $data = [];
    $data['holidays'] = self::getHolidays($calendars_id);
    if($date1->format("Y-m-d") == $date2->format("Y-m-d"))
    {
      $data['currents'][] = ['timeStart'=>$period->getStartDate()->format('Y-m-d H:i:s'),'w'=>$period->getStartDate()->format('w')];
      $startWeekDay = $date1->format("Y-m-d");
    }

    $i = 0;

    foreach ($period as $key => $value)
    {

      foreach ($data['holidays'] as  $holiday) {
        if($holiday == $value->format('Y-m-d'))
        {
          continue 2;
        }
      }
      if(!self::isWeekDayWork($calendars_id,$value->format('w')))
      {

        continue;
      }
      if(!$i)
      {
        $data['currents'][] = ['timeStart'=>$value->format('Y-m-d H:i:s'),'w'=>$value->format('w')];
        $startWeekDay = $value->format("Y-m-d");

      }
      else
      {
        $data['currents'][] = ['time'=>$value->format('Y-m-d H:i:s'),'w'=>$value->format('w')];
      }
      $i++;

    }
    if(self::isWeekDayWork($calendars_id,$period->getEndDate()->format('w')))
    {
      if(strtotime($date1->format('H:i:s')) > strtotime($date2->format('H:i:s')))
        {
          $data['currents'][] = ['timeEnd'=>$date2->format('Y-m-d H:i:s'),'w'=>$date2->format('w')];
            $endWeekDay   = $date2->format('Y-m-d');
        }
      elseif(strtotime($date1->format('H:i:s')) <= strtotime($date2->format('H:i:s')))
        {

           $last = end($data['currents']);
           if(explode(" ",reset($last))[0] == $date2->format("Y-m-d"))
           {
             array_pop($data['currents']);
           }
          $time = $date2->format('H:i:s');
          $data['currents'][] = ['timeEnd'=>$date2->format("Y-m-d $time"),'w'=>$date2->format('w')];
          $endWeekDay   = $date2->format('Y-m-d');
        }
      }
     else
      {
        //$d = explode(" ",end($data['currents'])['d'])[0];
        $w = end($data['currents'])['w'];
        if(count($data['currents']) > 1)
         {
            $lastDate =  array_pop($data['currents']);

            $lastDate = explode(" ",$lastDate['time'])[0];
         }
       else
        {
            $lastDate = $date1->format("Y-m-d");
        }

        $data['currents'][] =  ['timeEnd'=>"$lastDate 18:00:00","w"=>$w];
        $endWeekDay   = $lastDate;
     }



     if(!isset($data['currents'][0]['timeStart']))
     {
       array_unshift($data['currents'],['timeStart'=>$date1->format("Y-m-d H:i:s"),'w'=>$date1->format('w')]);
       $startWeekDay = $date1->format("Y-m-d");
     }
    $hours = 0;
    foreach ($data['currents'] as $key => $value) {
      $timeWork = [];
  /*    foreach (self::getSlaCalendarsSegments($calendars_id) as $segment)
      {
          if($segment['day'] == $value['w'])
          {
            $timeWork = ['begin'=>$segment['begin'],'end'=>$segment['end']];
          }

      }*/
      if(isset($weekWork[$value['w']]))
      {
        $timeBegin = strtotime($weekWork[$value['w']]['begin']);
        $timeEnd = strtotime($weekWork[$value['w']]['end']);
      }
      else
      {
        continue;
      }


        if(isset($value['timeStart']))
        {
            $time = strtotime(explode(" ",$value['timeStart'])[1]);
           if(($time >= $timeBegin && $time <= $timeEnd) && $startWeekDay == $endWeekDay)
           {
            $hours += $time;

           }
           elseif(($time >= $timeBegin && $time <= $timeEnd) && $startWeekDay != $endWeekDay)
           {
             $hours += ($timeEnd - $time);

           }
           elseif($time < $timeBegin && $startWeekDay == $endWeekDay)
           {
             $hours = $timeBegin;
           }
           elseif($time < $timeBegin && $startWeekDay != $endWeekDay)
           {
             $hours = ($timeEnd - $timeBegin);
           }

        }

        if(isset($value['time']))
        {
          $time = strtotime(explode(" ",$value['time'])[1]);

          $hours += ($timeEnd - $timeBegin);

        }
        if(isset($value['timeEnd']))
        {//die($startWeekDay."_".$endWeekDay." ".$hours/60/60);
          $time = strtotime(explode(" ",$value['timeEnd'])[1]);
         if(($time >= $timeBegin && $time <= $timeEnd) && $startWeekDay == $endWeekDay)
         {
          $hours = ($time - $hours);
         }
         elseif(($time >= $timeBegin && $time <= $timeEnd) && $startWeekDay != $endWeekDay)
         {

           if($hours)
           {
             $hours += ($time - $timeBegin);
           }
           else
           {
             $hours = ($time - $timeBegin);
           }

         }
         elseif($time > $timeEnd && $startWeekDay == $endWeekDay)
         {
           if($hours)
           {
             $hours = ($timeEnd - $hours);
           }

         }
         elseif($time > $timeEnd && $startWeekDay != $endWeekDay)
         {
           $hours += ($timeEnd - $timeBegin);
         }
         elseif($time < $timeBegin && $startWeekDay == $endWeekDay)
         {
           $hours = 0;
         }
        }
    }
    if($hours)$hours = round($hours/60/60,1);
    return "$hours";//$date2->format('w');
  }
  public static function getSlaCalendarId($slaName)
  {
    global $DB;
    $request = $DB->request([
      'SELECT'=>['calendars_id'],
      'FROM' => 'glpi_slas',
      'WHERE'=>['name'=>$slaName]
    ]);
    return $request->current()['calendars_id'];
  }
  public static function getSlaCalendarsSegments($calendars_id)
  {
    global $DB;
    $request = $DB->request([
      'SELECT'=>['day','begin','end'],
      'FROM' => 'glpi_calendarsegments',
      'WHERE'=>['calendars_id'=>$calendars_id]
    ]);
    $data = [];
    foreach ($request as $row)
    {
      $data[] = $row;
    }
    return $data;
  }
  public static function isWeekDayWork($calendars_id,$week_id)
  {
    $weeks = self::getSlaCalendarsSegments($calendars_id);
    foreach ($weeks as $row)
    {
      if($row['day']==$week_id)
      {
        return true;
      }
    }
    return false;
  }
  public static function getHolidays($calendars_id)
  {
    global $DB;
    $holidays_ids = $DB->request([
      'SELECT'=>['holidays_id'],
      'FROM' => 'glpi_calendars_holidays',
      'WHERE'=>['calendars_id'=>$calendars_id]
    ]);
    $data = [];
    foreach ($holidays_ids as $row)
    {
      $data[] = $row['holidays_id'];
    }
    $holidays = $DB->request([
      //'SELECT'=>['holidays_id'],
      'FROM' => 'glpi_holidays',
      'WHERE'=>['id'=>$data]
    ]);
    $data = [];
    foreach ($holidays as $key => $value)
    {
      $period_holidays = new DatePeriod(
       new DateTime($value['begin_date']),
       new DateInterval('P1D'),
       new DateTime($value['end_date'])
        );
      foreach ($period_holidays as $value) {

        $data[] = $value->format('Y-m-d');
      }
      $data[] = $period_holidays->getEndDate()->format('Y-m-d');
    }

    return $data;
  }
  /**
  * Get data based on search parameters
  *
  * @since 0.85
  *
  * @param class-string<CommonDBTM> $itemtype Item type to manage
  * @param array  $params        Search params passed to prepareDatasForSearch function
  * @param array  $forcedisplay  Array of columns to display (default empty = empty use display pref and search criteria)
  *
  * @return array The data
  **/
  public static function getDatas($itemtype, $params, array $forcedisplay = [])
  {

    $data = self::prepareDatasForSearch($itemtype, $params, $forcedisplay);

    self::constructSQL($data);
    self::constructData($data);


    return $data;
  }

  /**
  * Prepare search criteria to be used for a search
  *
  * @since 0.85
  *
  * @param class-string<CommonDBTM> $itemtype Item type
  * @param array  $params        Array of parameters
  *                               may include sort, order, start, list_limit, deleted, criteria, metacriteria
  * @param array  $forcedisplay  Array of columns to display (default empty = empty use display pref and search criterias)
  *
  * @return array prepare to be used for a search (include criteria and others needed information)
  **/
  public static function prepareDatasForSearch($itemtype, array $params, array $forcedisplay = [])
  {
    /** @var array $CFG_GLPI */
    global $CFG_GLPI;

    // Default values of parameters
    $p['criteria']            = [];
    $p['metacriteria']        = [];
    $p['sort']                = ['1'];
    $p['order']               = ['ASC'];
    $p['start']               = 0;//
    $p['is_deleted']          = 0;
    $p['export_all']          = 0;
    if (class_exists($itemtype)) {
      $p['target']       = $itemtype::getSearchURL();
    } else {
      $p['target']       = Toolbox::getItemTypeSearchURL($itemtype);
    }
    $p['display_type']        = self::HTML_OUTPUT;
    $p['showmassiveactions']  = true;
    $p['dont_flush']          = false;
    $p['show_pager']          = true;
    $p['show_footer']         = true;
    $p['no_sort']             = false;
    $p['list_limit']          = $_SESSION['glpilist_limit'];
    $p['massiveactionparams'] = [];

    foreach ($params as $key => $val) {
      switch ($key) {
        case 'order':
        if (!is_array($val)) {
          // Backward compatibility with GLPI < 10.0 links
          if (in_array($val, ['ASC', 'DESC'])) {
            $p[$key] = [$val];
          }
          break;
        }
        $p[$key] = $val;
        break;
        case 'sort':
        if (!is_array($val)) {
          // Backward compatibility with GLPI < 10.0 links
          $val = (int) $val;
          if ($val >= 0) {
            $p[$key] = [$val];
          }
          break;
        }
        $p[$key] = $val;
        break;
        case 'is_deleted':
        if ($val == 1) {
          $p[$key] = '1';
        }
        break;
        default:
        $p[$key] = $val;
        break;
      }
    }

    // Set display type for export if define
    if (isset($p['display_type'])) {
      // Limit to 10 element
      if ($p['display_type'] == self::GLOBAL_SEARCH) {
        $p['list_limit'] = self::GLOBAL_DISPLAY_COUNT;
      }
    }

    if ($p['export_all']) {
      $p['start'] = 0;
    }

    $p = self::cleanParams($p);

    $data             = [];
    $data['search']   = $p;
    $data['itemtype'] = $itemtype;

    // Instanciate an object to access method
    $data['item'] = null;

    if ($itemtype != AllAssets::getType()) {
      $data['item'] = getItemForItemtype($itemtype);
    }

    $data['display_type'] = $data['search']['display_type'];

    if (!$CFG_GLPI['allow_search_all']) {
      foreach ($p['criteria'] as $val) {
        if (isset($val['field']) && $val['field'] == 'all') {
          Html::displayRightError();
        }
      }
    }
    if (!$CFG_GLPI['allow_search_view'] && !array_key_exists('globalsearch', $p)) {
      foreach ($p['criteria'] as $val) {
        if (isset($val['field']) && $val['field'] == 'view') {
          Html::displayRightError();
        }
      }
    }

    /// Get the items to display
    // Add searched items

    $forcetoview = false;
    if (is_array($forcedisplay) && count($forcedisplay)) {
      $forcetoview = true;
    }
    $data['search']['all_search']  = false;
    $data['search']['view_search'] = false;
    // If no research limit research to display item and compute number of item using simple request
    $data['search']['no_search']   = true;

    $data['toview'] = self::addDefaultToView($itemtype, $params);

    $data['meta_toview'] = [];
    if (!$forcetoview) {
      // Add items to display depending of personal prefs
      $displaypref = DisplayPreference::getForTypeUser($itemtype, Session::getLoginUserID());
      if (count($displaypref)) {
        foreach ($displaypref as $val) {
          array_push($data['toview'], $val);
        }
      }
    } else {
      $data['toview'] = array_merge($data['toview'], $forcedisplay);
    }
    $data['toview'] = [1,3,12,15,4,5,7,45,76667,17,30,64,19,18,82,150,153,154,21];
    if (count($p['criteria']) > 0) {
      // use a recursive closure to push searchoption when using nested criteria
      $parse_criteria = function ($criteria) use (&$parse_criteria, &$data) {
        foreach ($criteria as $criterion) {
          // recursive call
          if (isset($criterion['criteria'])) {
            $parse_criteria($criterion['criteria']);
          } else {
            // normal behavior
            if (
              isset($criterion['field'])
              && !in_array($criterion['field'], $data['toview'])
            ) {
              if (
                $criterion['field'] != 'all'
                && $criterion['field'] != 'view'
                && (!isset($criterion['meta'])
                || !$criterion['meta'])
              ) {
                array_push($data['toview'], $criterion['field']);
              } else if ($criterion['field'] == 'all') {
                $data['search']['all_search'] = true;
              } else if ($criterion['field'] == 'view') {
                $data['search']['view_search'] = true;
              }
            }

            if (
              isset($criterion['value'])
              && (strlen($criterion['value']) > 0)
            ) {
              $data['search']['no_search'] = false;
            }
          }
        }
      };

      // call the closure
      $parse_criteria($p['criteria']);
    }

    if (count($p['metacriteria'])) {
      $data['search']['no_search'] = false;
    }

    // Add order item
    $to_add_view = array_diff($p['sort'], $data['toview']);
    array_push($data['toview'], ...$to_add_view);

    // Special case for CommonITILObjects : put ID in front
    if (is_a($itemtype, CommonITILObject::class, true)) {
      array_unshift($data['toview'], 2);
    }

    $limitsearchopt   = self::getCleanedOptions($itemtype);
    // Clean and reorder toview
    $tmpview = [];
    foreach ($data['toview'] as $val) {
      if (isset($limitsearchopt[$val]) && !in_array($val, $tmpview)) {
        $tmpview[] = $val;
      }
    }
    $data['toview']    = $tmpview;
    $data['tocompute'] = $data['toview'];

    // Force item to display
    if ($forcetoview) {
      foreach ($data['toview'] as $val) {
        if (!in_array($val, $data['tocompute'])) {
          array_push($data['tocompute'], $val);
        }
      }
    }
    $data['search']['no_search'] = false;
    return $data;
  }

  /**
   * Construct SQL request depending of search parameters
   *
   * Add to data array a field sql containing an array of requests :
   *      search : request to get items limited to wanted ones
   *      count : to count all items based on search criterias
   *                    may be an array a request : need to add counts
   *                    maybe empty : use search one to count
   *
   * @since 0.85
   *
   * @param array $data  Array of search datas prepared to generate SQL
   *
   * @return void|false May return false if the search request data is invalid
   **/
  public static function constructSQL(array &$data)
  {
      /**
       * @var array $CFG_GLPI
       * @var \DBmysql $DB
       */
      global $CFG_GLPI, $DB;

      if (!isset($data['itemtype'])) {
          return false;
      }

      $data['sql']['count']  = [];
      $data['sql']['search'] = '';
      $data['sql']['raw']    = [];

      $searchopt        = self::getOptions($data['itemtype']);

      $blacklist_tables = [];
      $orig_table = self::getOrigTableName($data['itemtype']);
      if (isset($CFG_GLPI['union_search_type'][$data['itemtype']])) {
          $itemtable          = $CFG_GLPI['union_search_type'][$data['itemtype']];
          $blacklist_tables[] = $orig_table;
      } else {
          $itemtable = $orig_table;
      }

     // hack for AllAssets and ReservationItem
      if (isset($CFG_GLPI['union_search_type'][$data['itemtype']])) {
          $entity_restrict = true;
      } else {
          $entity_restrict = $data['item']->isEntityAssign() && $data['item']->isField('entities_id');
      }

     // Construct the request

     //// 1 - SELECT
     // request currentuser for SQL supervision, not displayed
      $SELECT = "SELECT DISTINCT `$itemtable`.`id` AS id, '" . Toolbox::addslashes_deep($_SESSION['glpiname']) . "' AS currentuser,
                      " . self::addDefaultSelect($data['itemtype']);

     // Add select for all toview item
      foreach ($data['toview'] as $val) {
          $SELECT .= self::addSelect($data['itemtype'], $val);
      }

      if (isset($data['search']['as_map']) && $data['search']['as_map'] == 1 && $data['itemtype'] != 'Entity') {
          $SELECT .= ' `glpi_locations`.`id` AS loc_id, ';
      }

     //// 2 - FROM AND LEFT JOIN
     // Set reference table
      $FROM = " FROM `$itemtable`";

     // Init already linked tables array in order not to link a table several times
      $already_link_tables = [];
     // Put reference table
      array_push($already_link_tables, $itemtable);

     // Add default join
      $COMMONLEFTJOIN = self::addDefaultJoin($data['itemtype'], $itemtable, $already_link_tables);
      $FROM          .= $COMMONLEFTJOIN;

     // Add all table for toview items
      foreach ($data['tocompute'] as $val) {
          if (!in_array($searchopt[$val]["table"], $blacklist_tables)) {
              $FROM .= self::addLeftJoin(
                  $data['itemtype'],
                  $itemtable,
                  $already_link_tables,
                  $searchopt[$val]["table"],
                  $searchopt[$val]["linkfield"],
                  0,
                  0,
                  $searchopt[$val]["joinparams"],
                  $searchopt[$val]["field"]
              );
          }
      }

     // Search all case :
      if ($data['search']['all_search']) {
          foreach ($searchopt as $key => $val) {
             // Do not search on Group Name
              if (is_array($val) && isset($val['table'])) {
                  if (!in_array($searchopt[$key]["table"], $blacklist_tables)) {
                      $FROM .= self::addLeftJoin(
                          $data['itemtype'],
                          $itemtable,
                          $already_link_tables,
                          $searchopt[$key]["table"],
                          $searchopt[$key]["linkfield"],
                          0,
                          0,
                          $searchopt[$key]["joinparams"],
                          $searchopt[$key]["field"]
                      );
                  }
              }
          }
      }

     //// 3 - WHERE

     // default string
      $COMMONWHERE = "";
      $first       = empty($COMMONWHERE);

     // Add deleted if item have it
      if ($data['item'] && $data['item']->maybeDeleted()) {
          $LINK = " AND ";
          if ($first) {
              $LINK  = " ";
              $first = false;
          }
          $COMMONWHERE .= $LINK . "`$itemtable`.`is_deleted` = " . (int)$data['search']['is_deleted'] . " ";
      }

     // Remove template items
      if ($data['item'] && $data['item']->maybeTemplate()) {
          $LINK = " AND ";
          if ($first) {
              $LINK  = " ";
              $first = false;
          }
          $COMMONWHERE .= $LINK . "`$itemtable`.`is_template` = 0 ";
      }

     // Add Restrict to current entities
      if ($entity_restrict) {
          $LINK = " AND ";
          if ($first) {
              $LINK  = " ";
              $first = false;
          }

          if ($data['itemtype'] == 'Entity') {
              $COMMONWHERE .= getEntitiesRestrictRequest($LINK, $itemtable);
          } else if (isset($CFG_GLPI["union_search_type"][$data['itemtype']])) {
             // Will be replace below in Union/Recursivity Hack
              $COMMONWHERE .= $LINK . " ENTITYRESTRICT ";
          } else {
              $COMMONWHERE .= getEntitiesRestrictRequest(
                  $LINK,
                  $itemtable,
                  '',
                  '',
                  $data['item']->maybeRecursive() && $data['item']->isField('is_recursive')
              );
          }
      }
      $WHERE  = "";
      $HAVING = "";

     // Add search conditions
     // If there is search items
      if (count($data['search']['criteria'])) {
          $WHERE  = self::constructCriteriaSQL($data['search']['criteria'], $data, $searchopt);
          $HAVING = self::constructCriteriaSQL($data['search']['criteria'], $data, $searchopt, true);

         // if criteria (with meta flag) need additional join/from sql
          self::constructAdditionalSqlForMetacriteria($data['search']['criteria'], $SELECT, $FROM, $already_link_tables, $data);
      }

     //// 4 - ORDER
      $ORDER = " ORDER BY `id` ";
      $sort_fields = [];
      $sort_count = count($data['search']['sort']);
      for ($i = 0; $i < $sort_count; $i++) {
          foreach ($data['tocompute'] as $val) {
              if ($data['search']['sort'][$i] == $val) {
                  $sort_fields[] = [
                      'searchopt_id' => $data['search']['sort'][$i],
                      'order'        => $data['search']['order'][$i] ?? null
                  ];
              }
          }
      }
      if (count($sort_fields)) {
          $ORDER = self::addOrderBy($data['itemtype'], $sort_fields);
      }

      $SELECT = rtrim(trim($SELECT), ',');

     //// 7 - Manage GROUP BY
      $GROUPBY = "";
     // Meta Search / Search All / Count tickets
      $criteria_with_meta = array_filter($data['search']['criteria'], function ($criterion) {
          return isset($criterion['meta'])
              && $criterion['meta'];
      });
      if (
          (count($data['search']['metacriteria']))
          || count($criteria_with_meta)
          || !empty($HAVING)
          || $data['search']['all_search']
      ) {
          $GROUPBY = " GROUP BY `$itemtable`.`id`";
      }

      if (empty($GROUPBY)) {
          foreach ($data['toview'] as $val2) {
              if (!empty($GROUPBY)) {
                  break;
              }
              if (isset($searchopt[$val2]["forcegroupby"])) {
                  $GROUPBY = " GROUP BY `$itemtable`.`id`";
              }
          }
      }

      $LIMIT   = "";
      $numrows = 0;
     //No search : count number of items using a simple count(ID) request and LIMIT search
      if ($data['search']['no_search']) {
          $LIMIT = " LIMIT " . (int)$data['search']['start'] . ", " . (int)$data['search']['list_limit'];

          $count = "count(DISTINCT `$itemtable`.`id`)";
         // request currentuser for SQL supervision, not displayed
          $query_num = "SELECT $count,
                            '" . Toolbox::addslashes_deep($_SESSION['glpiname']) . "' AS currentuser
                     FROM `$itemtable`" .
                     $COMMONLEFTJOIN;

          $first     = true;

          if (!empty($COMMONWHERE)) {
              $LINK = " AND ";
              if ($first) {
                  $LINK  = " WHERE ";
                  $first = false;
              }
              $query_num .= $LINK . $COMMONWHERE;
          }
         // Union Search :
          if (isset($CFG_GLPI["union_search_type"][$data['itemtype']])) {
              $tmpquery = $query_num;

              foreach ($CFG_GLPI[$CFG_GLPI["union_search_type"][$data['itemtype']]] as $ctype) {
                  $ctable = $ctype::getTable();
                  if (
                      ($citem = getItemForItemtype($ctype))
                      && $citem->canView()
                  ) {
                      // State case
                      if ($data['itemtype'] == AllAssets::getType()) {
                          $query_num  = str_replace(
                              $CFG_GLPI["union_search_type"][$data['itemtype']],
                              $ctable,
                              $tmpquery
                          );
                          $query_num  = str_replace($data['itemtype'], $ctype, $query_num);
                          $query_num .= " AND `$ctable`.`id` IS NOT NULL ";

                       // Add deleted if item have it
                          if ($citem && $citem->maybeDeleted()) {
                                $query_num .= " AND `$ctable`.`is_deleted` = 0 ";
                          }

                       // Remove template items
                          if ($citem && $citem->maybeTemplate()) {
                              $query_num .= " AND `$ctable`.`is_template` = 0 ";
                          }
                      } else {// Ref table case
                          $reftable = $data['itemtype']::getTable();
                          if ($data['item'] && $data['item']->maybeDeleted()) {
                              $tmpquery = str_replace(
                                  "`" . $CFG_GLPI["union_search_type"][$data['itemtype']] . "`.
                                                 `is_deleted`",
                                  "`$reftable`.`is_deleted`",
                                  $tmpquery
                              );
                          }
                          $replace  = "FROM `$reftable`
                                INNER JOIN `$ctable`
                                     ON (`$reftable`.`items_id` =`$ctable`.`id`
                                         AND `$reftable`.`itemtype` = '$ctype')";

                          $query_num = str_replace(
                              "FROM `" .
                                      $CFG_GLPI["union_search_type"][$data['itemtype']] . "`",
                              $replace,
                              $tmpquery
                          );
                          $query_num = str_replace(
                              $CFG_GLPI["union_search_type"][$data['itemtype']],
                              $ctable,
                              $query_num
                          );
                      }
                      $query_num = str_replace(
                          "ENTITYRESTRICT",
                          getEntitiesRestrictRequest(
                              '',
                              $ctable,
                              '',
                              '',
                              $citem->maybeRecursive()
                          ),
                          $query_num
                      );
                       $data['sql']['count'][] = $query_num;
                  }
              }
          } else {
              $data['sql']['count'][] = $query_num;
          }
      }

     // If export_all reset LIMIT condition
      if ($data['search']['export_all']) {
          $LIMIT = "";
      }

      if (!empty($WHERE) || !empty($COMMONWHERE)) {
          if (!empty($COMMONWHERE)) {
              $WHERE = ' WHERE ' . $COMMONWHERE . (!empty($WHERE) ? ' AND ( ' . $WHERE . ' )' : '');
          } else {
              $WHERE = ' WHERE ' . $WHERE . ' ';
          }
          $first = false;
      }

      if (!empty($HAVING)) {
          $HAVING = ' HAVING ' . $HAVING;
      }

     // Create QUERY
      if (isset($CFG_GLPI["union_search_type"][$data['itemtype']])) {
          $first = true;
          $QUERY = "";
          foreach ($CFG_GLPI[$CFG_GLPI["union_search_type"][$data['itemtype']]] as $ctype) {
              $ctable = $ctype::getTable();
              if (
                  ($citem = getItemForItemtype($ctype))
                  && $citem->canView()
              ) {
                  if ($first) {
                      $first = false;
                  } else {
                      $QUERY .= " UNION ALL ";
                  }
                  $tmpquery = "";
                 // AllAssets case
                  if ($data['itemtype'] == AllAssets::getType()) {
                       $tmpquery = $SELECT . ", '$ctype' AS TYPE " .
                           $FROM .
                           $WHERE;

                       $tmpquery .= " AND `$ctable`.`id` IS NOT NULL ";

                       // Add deleted if item have it
                      if ($citem && $citem->maybeDeleted()) {
                          $tmpquery .= " AND `$ctable`.`is_deleted` = 0 ";
                      }

                     // Remove template items
                      if ($citem && $citem->maybeTemplate()) {
                          $tmpquery .= " AND `$ctable`.`is_template` = 0 ";
                      }

                      $tmpquery .= $GROUPBY .
                           $HAVING;

                    // Replace 'asset_types' by itemtype table name
                      $tmpquery = str_replace(
                          $CFG_GLPI["union_search_type"][$data['itemtype']],
                          $ctable,
                          $tmpquery
                      );
                      // Replace 'AllAssets' by itemtype
                      // Use quoted value to prevent replacement of AllAssets in column identifiers
                      $tmpquery = str_replace(
                          $DB->quoteValue(AllAssets::getType()),
                          $DB->quoteValue($ctype),
                          $tmpquery
                      );
                  } else {// Ref table case
                      $reftable = $data['itemtype']::getTable();

                      $tmpquery = $SELECT . ", '$ctype' AS TYPE,
                                    `$reftable`.`id` AS refID, " . "
                                    `$ctable`.`entities_id` AS ENTITY " .
                      $FROM .
                      $WHERE;
                      if ($data['item']->maybeDeleted()) {
                          $tmpquery = str_replace(
                              "`" . $CFG_GLPI["union_search_type"][$data['itemtype']] . "`.
                                              `is_deleted`",
                              "`$reftable`.`is_deleted`",
                              $tmpquery
                          );
                      }

                      $replace = "FROM `$reftable`" . "
                            INNER JOIN `$ctable`" . "
                               ON (`$reftable`.`items_id`=`$ctable`.`id`" . "
                                   AND `$reftable`.`itemtype` = '$ctype')";
                      $tmpquery = str_replace(
                          "FROM `" .
                               $CFG_GLPI["union_search_type"][$data['itemtype']] . "`",
                          $replace,
                          $tmpquery
                      );
                      $tmpquery = str_replace(
                          $CFG_GLPI["union_search_type"][$data['itemtype']],
                          $ctable,
                          $tmpquery
                      );
                      $name_field = $ctype::getNameField();
                      $tmpquery = str_replace("`$ctable`.`name`", "`$ctable`.`$name_field`", $tmpquery);
                  }
                  $tmpquery = str_replace(
                      "ENTITYRESTRICT",
                      getEntitiesRestrictRequest(
                          '',
                          $ctable,
                          '',
                          '',
                          $citem->maybeRecursive()
                      ),
                      $tmpquery
                  );

                   // SOFTWARE HACK
                  if ($ctype == 'Software') {
                      $tmpquery = str_replace("`glpi_softwares`.`serial`", "''", $tmpquery);
                      $tmpquery = str_replace("`glpi_softwares`.`otherserial`", "''", $tmpquery);
                  }
                  $QUERY .= $tmpquery;
              }
          }
          if (empty($QUERY)) {
              echo self::showError($data['display_type']);
              return;
          }
          $QUERY .= str_replace($CFG_GLPI["union_search_type"][$data['itemtype']] . ".", "", $ORDER) .
                 $LIMIT;
      } else {
          $data['sql']['raw'] = [
              'SELECT' => $SELECT,
              'FROM' => $FROM,
              'WHERE' => $WHERE,
              'GROUPBY' => $GROUPBY,
              'HAVING' => $HAVING,
              'ORDER' => $ORDER,
              'LIMIT' => $LIMIT
          ];
          $QUERY = $SELECT .
                $FROM .
                $WHERE .
                $GROUPBY .
                $HAVING .
                $ORDER .
                $LIMIT;
      }
      $data['sql']['search'] = $QUERY;
  }



}
