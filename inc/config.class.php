<?php
use Glpi\Application\View\TemplateRenderer;
class PluginReportsslaConfig extends CommonDBTM
{
  static function displayForItem()
  {
    $random = rand();
    $tvpL = [
      'count_fields'=>count(self::getFields()),
      'random'=>$random,
      'config'=>self::getConfigs()
    ];
    TemplateRenderer::getInstance()->display('@reportssla/config.form.html.twig', $tvpL);

    echo <<< SCRIPT
    <script type="text/javascript">
    $('#generate_{$random}').click(function (){
      let generate_{$random} = $(this);
      let delete_{$random}   = $('#delete_{$random}');
      $.ajax({
        url: '../ajax/generateFields.php',
        method: 'get',
        dataType: 'json',
        data: {action: 'add'},
        beforeSend: function () { // Before we send the request, remove the .hidden class from the spinner and default to inline-block.
          $('#count_fields_{$random}').hide();
          $('#loader_{$random}').show();
          generate_{$random}.prop("disabled",true);
        },
        success: function(data){
          if(data.status)
          {
            generate_{$random}.hide();
            delete_{$random}.prop("disabled",false);
            delete_{$random}.show();
            $('#loader_{$random}').hide();
            $('#count_fields_{$random}').html('('+data.result+')');
            $('#count_fields_{$random}').show();
          }
          console.log(data)
        }
      });
    });

    $('#delete_{$random}').click(function (){
      let delete_{$random} = $(this);
      let generate_{$random}   = $('#generate_{$random}');
      $.ajax({
        url: '../ajax/generateFields.php',
        method: 'get',
        dataType: 'json',
        data: {action: 'delete'},
        beforeSend: function () { // Before we send the request, remove the .hidden class from the spinner and default to inline-block.
          $('#count_fields_{$random}').hide();
          $('#loader_{$random}').show();
          delete_{$random}.prop("disabled",true);
        },
        success: function(data){
          if(data.status)
          {
            delete_{$random}.hide();
            generate_{$random}.prop("disabled",false);
            generate_{$random}.show();
            $('#loader_{$random}').hide();
            $('#count_fields_{$random}').html('('+data.result+')');
            $('#count_fields_{$random}').show();
          }
          console.log(data)
        }
      });
    });

    $('#active_sla_{$random}').click(function (){
      var active_sla_{$random} = $(this);
      var active = '';
      if (active_sla_{$random}.is(':checked')){
        active = 'on';
      } else {
        active = 'off';
      }
      $.ajax({
        url: '../ajax/configs.php',
        method: 'get',
        dataType: 'json',
        data: {active_sla: active},
        beforeSend: function () { // Before we send the request, remove the .hidden class from the spinner and default to inline-block.

        },
        success: function(data){
          if(data.status)
          {
            if(data.result == 'on')
            {
              active_sla_{$random}.prop('checked', true);
            }
            else
            {
              active_sla_{$random}.prop('checked', false);
            }
            console.log(data);
          }
        },
        error: function(e) {
          $('#active_sla_{$random}').prop('checked', false);
        }
      });

    });
    </script>
    SCRIPT;
  }

  static function getFields()
  {
    global $DB;

    $query = $DB->request([
      'FROM' => 'glpi_plugin_reportssla_fields'
    ]);
    return $query;
  }
  static function getConfigs()
  {
    global $DB;

    $query = $DB->request([
      'FROM' => 'glpi_plugin_reportssla_configs'
    ]);
    return $query->current();
  }

  static function setConfigs($param)
  {
    global $DB;
    $param = $param == 'on'?1:0;
    if(self::getConfigs())
    {
      $DB->update(
        'glpi_plugin_reportssla_configs',
        [
          'active_sla' => $param
        ],
        [
          'id' => self::getConfigs()['id']
        ]
      );
    }
    else
    {
      $DB->insert(
        'glpi_plugin_reportssla_configs',
        [
          'active_sla' => 1
        ]
      );
    }

  }
  static function crongenerateFields()
  {
    self::generateFields();
    return true;
  }
  static function generateFields()
  {
    global $DB;
    $tickets = $DB->request([
      'FROM' => 'glpi_tickets',
      'WHERE'=> ['is_deleted'=>0]
    ]);
    $i = 0;
    $rows = $DB->request([
      'FROM'=> 'glpi_plugin_reportssla_fields'
    ]);
    if(count($rows))
    {
      $DB->delete('glpi_plugin_reportssla_fields',[1]);
      // Сбрасываем AUTO_INCREMENT
      $DB->query("ALTER TABLE glpi_plugin_reportssla_fields AUTO_INCREMENT = 1");
    }
    foreach ($tickets as $ticket) {
      $sla = $DB->request([
        'FROM'  => 'glpi_slas',
        'WHERE' => ['id'=>$ticket['slas_id_ttr']]
        ])->current();
        if(!isset($sla['id']))
        {
          $glpi_plugin_fields_tickettickets = $DB->request([
            'FROM'  => 'glpi_plugin_fields_tickettickets',
            'WHERE' => [
              'items_id'=>$ticket['id'],
              'itemtype'=>'Ticket'
            ]
            ])->current();
          if(isset($glpi_plugin_fields_tickettickets['id']))
          {
            $DB->update(
              'glpi_plugin_fields_tickettickets',
              [
                'slaremainsfieldfield'   => NULL,
                'fishingforwaitingfield' => NULL,
                'slanoldfield'           => NULL,
                'slaisviolatedfield'     => NULL
              ],
              [
                'id' => $glpi_plugin_fields_tickettickets['id']
              ]
            );
          }


          continue;
        }
        //Общее время решения обращения
        if($ticket['solvedate'])
        {

          $totalTimeSolved =  PluginReportsslaSearch::getHoursForCalendar($ticket['date_creation'],$ticket['solvedate'],$sla['name']);
        }
        else
        {
          $totalTimeSolved =  PluginReportsslaSearch::getHoursForCalendar($ticket['date_creation'],date("Y-m-d H:i:s"),$sla['name']);
        }
        //

        $expiredSla = PluginReportsslaSearch::getDateSlaFinishForWaitng($ticket['date_creation'], $sla['number_time'],$sla['name']);//истечение SLA без приостановки
        $timeInWaiting = 0;
        foreach (PluginReportsslaSearch::getWaitingCountTime($ticket['id']) as $key => $v) {
          if(isset($v['end']))
          {
            if(!isset($v['start']))
            {
              continue;
            }
            $timeInWaiting += PluginReportsslaSearch::getHoursForCalendar($v['start'],$v['end'],$sla['name']);
          }
          if(!isset($v['end']))
          {
            $timeInWaiting += PluginReportsslaSearch::getHoursForCalendar($v['start'],date("Y-m-d H:i:s"),$sla['name']);
          }
        }
        ////Время решения с вычетом приостановки

        $totalTimeSolvedInWaiting = round($totalTimeSolved - $timeInWaiting,1);
        //
        //Оставшееся время от SLA
        $remainsHoursSla = round($sla['number_time']) - $totalTimeSolvedInWaiting;
        //

        if($sla['number_time'] == 0)
        {
          $remainsHoursSla = 0;
        }
        //SLA более 80%
        if($remainsHoursSla > 0)
        {
          $sla_nold  = round(100-($remainsHoursSla / round($sla['number_time'])*100)) > 80 ? 'Да':'Нет' ;

          $sla_violated = 'Нет';

          //Конвертация Оставшееся время от SLA в формат H:i
          $remainsHoursSla = explode(".",$remainsHoursSla);

          $H = $remainsHoursSla[0];
          if(isset($remainsHoursSla[1]))
          {
            $I = $remainsHoursSla[1]/10*60;
            if($I < 10)
            {
              $I = '0'.$I;
            }
          }
          else
          {
            $I = '00';
          }

          $remainsHoursSla = $H.':'.$I;
          //
        }
        else
        {
          $sla_nold = "Да";
          $remainsHoursSla = gmdate("H:i",0);
          $sla_violated = 'Да';
        }
        //
        if($sla['number_time'] == 0)
        {
          $sla_nold = "Нет";
            $sla_violated = 'Нет';
        }
        $expiredSlaForWaiting = PluginReportsslaSearch::getDateSlaFinishForWaitng($expiredSla, $timeInWaiting,$sla['name']);//истечение SLA с учетом приостановки

        $DB->insert(
          'glpi_plugin_reportssla_fields',
          [
            'item_id'           => $ticket['id'],
            'itemtype'          => 'Ticket',
            'slaremainsfield'   => $remainsHoursSla,
            'fishingforwaiting' => $expiredSlaForWaiting,
            'slanold'           => $sla_nold,
            'slaisviolated'     => $sla_violated
          ]
        );
        $glpi_plugin_fields_tickettickets = $DB->request([
          'FROM'  => 'glpi_plugin_fields_tickettickets',
          'WHERE' => [
            'items_id'=>$ticket['id'],
            'itemtype'=>'Ticket'
          ]
          ])->current();
          $expiredSlaForWaiting = $ticket['status']==4?'':$expiredSlaForWaiting;
          if(isset($glpi_plugin_fields_tickettickets['id']))
          {
            $DB->update(
              'glpi_plugin_fields_tickettickets',
              [
                'slaremainsfieldfield'   => $remainsHoursSla,
                'fishingforwaitingfield' => $expiredSlaForWaiting,
                'slanoldfield'           => $sla_nold,
                'slaisviolatedfield'     => $sla_violated
              ],
              [
                'id' => $glpi_plugin_fields_tickettickets['id']
              ]
            );
          }
          else
          {
            $DB->insert(
              'glpi_plugin_fields_tickettickets',
              [
                'items_id'           => $ticket['id'],
                'itemtype'          => 'Ticket',
                'slaremainsfieldfield'   => $remainsHoursSla,
                'fishingforwaitingfield' => $expiredSlaForWaiting,
                'slanoldfield'           => $sla_nold,
                'slaisviolatedfield'     => $sla_violated
              ]
            );
          }
          $i++;
        }
        return $i;
      }

      static function deleteFields()
      {
        global $DB;

        $DB->delete('glpi_plugin_reportssla_fields',[1]);
        // Сбрасываем AUTO_INCREMENT
        $DB->query("ALTER TABLE glpi_plugin_reportssla_fields AUTO_INCREMENT = 1");
        return true;
      }

    }
