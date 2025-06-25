<?php
namespace GlpiPlugin\Reportssla;
use CommonDBTM;
class Config extends CommonDBTM {
  /**
   * Tell DBTM to keep history
   * @var    bool     - $dohistory
   */
  public $dohistory = true;

  /**
   * Tell CommonGLPI to use config (Setup->Setup in UI) rights.
   * @var    string   - $rightname
   */
  public static $rightname = 'config';

  public static function canView(): bool
  {
    return true;
  }

  public static function getTypeName($nb = 0): string
  {
    return __('Отчет SLA', PLUGIN_NAME);
  }

  /**
  * Returns class icon to use in menus and tabs
  *
  * @return string   - returns Font Awesom icon classname.
  * @see             - https://fontawesome.com/search
  */
  public static function getIcon(): string
  {
    return 'fa-fw ti ti-tool';
  }
}
