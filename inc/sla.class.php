<?php class PluginReportsslaSla //extends CommonDBTM
{

  /**
  * This function is called from GLPI to render the form when the user click
  *  on the menu item generated from getTabNameForItem()
  */
  static function displayForItem()
  {
  //  Session::checkRight('entity', READ);
    echo '<form action="" method="post">';

    //echo Html::hidden('_glpi_csrf_token', array('value' => Session::getNewCSRFToken()));
    echo '<div class="text-start">

    <div class="row">

    <div class="card">
    <div class="card-header">
    <h3>Отчет SLA</h3>
    </div>
    <div class="card-body">
    <div class="spaced" id="tabsbody">
    <div class="row">
    <div class="col-3"></div>
    <div class="form-field row col-3  mb-2">
    <label class="col-form-label col-xxl-2 text-xxl-end" for="date_2036942051">
    C
    </label>
    <div class="col-xxl-10  field-container">
    <div class="input-group flex-grow-1 flatpickr" id="date_2036942051">
    <input type="hidden" class="form-control rounded-start ps-2 flatpickr-input" data-input="" name="date_in" value="">
    <i class="input-group-text far fa-calendar-alt" data-toggle="" role="button"></i>
    </div>
    <script>
    $(function() {
      $("#date_2036942051").flatpickr({
        altInput: true,
        dateFormat:"Y-m-d H:i:S",
        altFormat: "d-m-Y H:i:S",
        enableTime: true,
        wrap: true,
        enableSeconds: true,
        weekNumbers: true,
        time_24hr: true,
        allowInput: true,
        clickOpens: true,
        defaultHour :"0",
        locale: getFlatPickerLocale("ru", "RU"),
        onClose(dates, currentdatestring, picker) {
          picker.setDate(picker.altInput.value, true, picker.config.altFormat)
        },
        plugins: [
          CustomFlatpickrButtons()
        ]
      });
    });
    </script>
    </div>
    </div>

    <div class="form-field row col-3  mb-2">
    <label class="col-form-label col-xxl-2 text-xxl-end" for="date_2036942052">
    По
    </label>
    <div class="col-xxl-10  field-container">
    <div class="input-group flex-grow-1 flatpickr" id="date_2036942052">
    <input type="hidden" class="form-control rounded-start ps-2 flatpickr-input" data-input="" name="date_out" value="">
    <i class="input-group-text far fa-calendar-alt" data-toggle="" role="button"></i>
    </div>
    <script>
    $(function() {
      $("#date_2036942052").flatpickr({
        altInput: true,
        dateFormat:"Y-m-d H:i:S",
        altFormat: "d-m-Y H:i:S",
        enableTime: true,
        wrap: true,
        enableSeconds: true,
        weekNumbers: true,
        time_24hr: true,
        allowInput: true,
        clickOpens: true,
        defaultHour :"23",
        defaultMinute : "59",
        defaultSeconds : "59",
        locale: getFlatPickerLocale("ru", "RU"),
        onClose(dates, currentdatestring, picker) {
          picker.setDate(picker.altInput.value, true, picker.config.altFormat)
        },
        plugins: [
          CustomFlatpickrButtons()
        ]
      });
    });
    </script>
    </div>
    </div> <div class="col-3"></div>
    </div>
    <div class="row mt-3">
    <div class="col text-center">
    <a href="" id="get_report" class="d-none" target="_blank"></a>
    <button id="download_reports" type="button" class="btn btn-icon btn-sm btn-secondary me-1 pe-2">Скачать</button>
    </div>
    </div>
    </div>
    </form>
    </div>
    </div>

    </div>';
    echo "<script>
    $(function(){
      $('#download_reports').click(function(){
        var date_in = $('input[name=date_in]').val();
        var date_out = $('input[name=date_out]').val();
        const CSV_DISPLAY_ALL = 3;
        $('#get_report').attr('href','');
        $('#get_report').attr('href', '/plugins/reportssla/front/report.dynamic.php?item_type=Ticket&sort[0]=1&order[0]=ASC&start=0&criteria[0][link]=AND&criteria[0][field]=15&criteria[0][searchtype]=morethan&_select_criteria[0][value]=0&criteria[0][value]='+date_in+'&criteria[1][link]=AND&criteria[1][field]=15&criteria[1][searchtype]=lessthan&_select_criteria[1][value]=0&criteria[1][value]='+date_out+'&display_type='+CSV_DISPLAY_ALL);
        console.log('sss');
        $('#get_report')[0].click();
      });

    });
    </script>";
if(Session::haveRight('entity', READ))
{
  echo "<script>
      $('#download_reports').after('<br><br><a class=\"btn btn-icon btn-sm btn-secondary\" href=\"/plugins/reportssla/front/report.dynamic.php?item_type=Ticket&history=2;&name=ВДД из АНО в ОЦО\" target=\"_blank\">Скачать историю ВДД из АНО в ОЦО</a>')
      $('#download_reports').after('<br><br><a class=\"btn btn-icon btn-sm btn-secondary\" href=\"/plugins/reportssla/front/report.dynamic.php?item_type=Ticket&history=24;25;26;27;28;29;34;35;36;37&name=ВДД из ОЦО в АНО\" target=\"_blank\">Скачать историю ВДД из ОЦО в АНО</a>')
      $('#download_reports').after('<br><br><a class=\"btn btn-icon btn-sm btn-secondary\" href=\"/plugins/reportssla/front/report.dynamic.php?item_type=Ticket&history=14;&name=ПД из АНО в ОЦО\" target=\"_blank\">Скачать историю ПД из АНО в ОЦО</a>')
      $('#download_reports').after('<br><br><a class=\"btn btn-icon btn-sm btn-secondary\" href=\"/plugins/reportssla/front/report.dynamic.php?item_type=Ticket&history=16;17;18;19;20;22;30;31;32;33&name=ПД из ОЦО в АНО\" target=\"_blank\">Скачать историю ПД из ОЦО в АНО</a>')
  </script>";
}
  }
}
