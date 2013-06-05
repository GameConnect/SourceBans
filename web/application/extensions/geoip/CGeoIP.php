<?php

/**
 * +----------------------------------------------------------------------+
 * | PHP version 5                                                        |
 * +----------------------------------------------------------------------+
 * | Copyright (C) 2010 Dinis Lage                                        |
 * +----------------------------------------------------------------------+
 * | This library is free software; you can redistribute it and/or        |
 * | modify it under the terms of the GNU Lesser General Public           |
 * | License as published by the Free Software Foundation; either         |
 * | version 2.1 of the License, or (at your option) any later version.   |
 * |                                                                      |
 * | This library is distributed in the hope that it will be useful,      |
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU    |
 * | Lesser General Public License for more details.                      |
 * |                                                                      |
 * | You should have received a copy of the GNU Lesser General Public     |
 * | License along with this library; if not, write to the Free Software  |
 * | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 |
 * | USA, or view it online at http://www.gnu.org/licenses/lgpl.txt.      |
 * +----------------------------------------------------------------------+
 * | Authors: Dinis Lage <dinislage@gmail.com>                            |
 * +----------------------------------------------------------------------+
 *
 * @category Net
 * @package  GeoIP
 * @author Dinis Lage <dinislage@gmail.com>
 * @license  LGPL http://www.gnu.org/licenses/lgpl.txt
 * $Id: GeoIP.php 296763 2010-03-25 00:53:44Z clockwerx $
 */
/**
 * CGeoip class file.
 *
 * @author Dinis Lage <dinislage@gmail.com>
 * @link http://www.yiiframework.com/
 * @version 0.1
 */
Yii::import('application.extensions.geoip.GeoIP');

class CGeoIP extends CApplicationComponent {

  public $filename = '/usr/local/share/GeoIP/GeoLiteCity.dat';
  public $mode;
  protected static $flags = GeoIP::STANDARD;
  protected static $geoip;

  public function init() {
    switch($this->mode) {
      case 'MEMORY_CACHE':
        self::$flags = GeoIP::MEMORY_CACHE;
        break;
      default:
        self::$flags = GeoIP::STANDARD;
        break;
    }
    self::$geoip = GeoIP::getInstance($this->filename, self::$flags);
    // Run parent
    parent::init();
  }

  public function lookupLocation($ip=null) {
    $ip = $this->_getIP($ip);
    return self::$geoip->lookupLocation($ip);
  }

  public function lookupCountryCode($ip=null) {
    $ip = $this->_getIP($ip);
    return self::$geoip->lookupCountryCode($ip);
  }

  public function lookupCountryName($ip=null) {
    $ip = $this->_getIP($ip);
    return self::$geoip->lookupCountryName($ip);
  }

  public function lookupOrg($ip=null) {
    $ip = $this->_getIP($ip);
    return self::$geoip->lookupOrg($ip);
  }

  public function lookupRegion($ip=null) {
    $ip = $this->_getIP($ip);
    return self::$geoip->lookupRegion($ip);
  }

  protected function _getIP($ip=null) {
    if ($ip === null) {
      $ip = Yii::app()->getRequest()->getUserHostAddress();
    }
    return $ip;
  }

}
?>
