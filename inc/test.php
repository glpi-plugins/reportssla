{//ini_set("memory_limit", -1);
  //set_time_limit(60);
  $calendars_id = self::getSlaCalendarId($slaName);
  $holidays = self::getHolidays($calendars_id);
  $dateFinishSla = new DateTime($dateFinishSla);
  $getSlaCalendarsSegments = self::getSlaCalendarsSegments($calendars_id);
  $data = [];
  $second = round(($hours*60)*60,0);
  //$minutes = $hours*60;
  for ($i=0; $i < $second; )
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
       foreach ($getSlaCalendarsSegments as $segment)
       {

           if($segment['day'] == $dateFinishSla->format('w'))
           {
             $beginTime = strtotime($segment['begin']);
             $endTime = strtotime($segment['end']);
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
              //   die($dateFinishSla->format("Y-m-d H:i"));
              }
              else
              {
                $i += $second;
                //$min = $second/60;
               $dateFinishSla->add(new DateInterval("PT".$second."S"));
              }


            }
            if ($currentTime < $beginTime)
            {
              $time = $beginTime - $currentTime;
          //    $second -= $time;
          //    $i += ($time/60);
            //  $min = ($time/60);
              $dateFinishSla->add(new DateInterval("PT".$time."S"));
            }
            if ($currentTime > $endTime)
            {
              $time = $currentTime - $endTime;
            //  $second -= $time;
          //    $i += ($time/60);
            //  $min = ($time/60);
              $dateFinishSla->sub(new DateInterval("PT".$time."S"));
              $dateFinishSla->add(new DateInterval("PT15H"));
            }
          }
       }

  }


  return $dateFinishSla->format("Y-m-d H:i");
}
