<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * link      http://kigkonsult.se/iCalcreator/index.php
 * package   iCalcreator
 * version   2.23.12
 * license   By obtaining and/or copying the Software, iCalcreator,
 *           you (the licensee) agree that you have read, understood,
 *           and will comply with the following terms and conditions.
 *           a. The above copyright, link, package and version notices,
 *              this licence notice and
 *              the [rfc5545] PRODID as implemented and invoked in the software
 *              shall be included in all copies or substantial portions of the Software.
 *           b. The Software, iCalcreator, is for
 *              individual evaluation use and evaluation result use only;
 *              non assignable, non-transferable, non-distributable,
 *              non-commercial and non-public rights, use and result use.
 *           c. Creative Commons
 *              Attribution-NonCommercial-NoDerivatives 4.0 International License
 *              (http://creativecommons.org/licenses/by-nc-nd/4.0/)
 *           In case of conflict, a and b supercede c.
 *
 * This file is a part of iCalcreator.
 */
namespace kigkonsult\iCalcreator\traits;
use kigkonsult\iCalcreator\util\util;
/**
 * CREATED property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-02
 */
trait CREATEDtrait {
/**
 * @var array component property CREATED value
 * @access protected
 */
  protected $created = null;
/**
 * Return formatted output for calendar component property created
 *
 * @return string
 * @uses util::date2strdate()
 * @uses util::createParams()
 * @uses util::createElement()
 */
  public function createCreated() {
    if( empty( $this->created ))
      return null;
    return util::createElement( util::$CREATED,
                                util::createParams( $this->created[util::$LCparams] ),
                                util::date2strdate( $this->created[util::$LCvalue], 7 ));
  }
/**
 * Set calendar component property created
 *
 * @param mixed $year
 * @param mixed $month
 * @param int   $day
 * @param int   $hour
 * @param int   $min
 * @param int   $sec
 * @param mixed $params
 * @return bool
 * @uses util::setDate2()
 */
  public function setCreated( $year=null, $month=null, $day=null, $hour=null, $min=null, $sec=null, $params=null ) {
    static $YMDTHIS = 'Ymd\THis';
    if( empty( $year ))
      $year = gmdate( $YMDTHIS );
    $this->created = util::setDate2( $year, $month, $day, $hour, $min, $sec, $params );
    return true;
  }
}
