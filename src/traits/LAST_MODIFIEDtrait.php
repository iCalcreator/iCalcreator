<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * link      http://kigkonsult.se/iCalcreator/index.php
 * package   iCalcreator
 * version   2.23.10
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
 * LAST-MODIFIED property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-02
 */
trait LAST_MODIFIEDtrait {
/**
 * @var array component property LAST-MODIFIED value
 * @access protected
 */
  protected $lastmodified = null;
/**
 * Return formatted output for calendar component property last-modified
 *
 * @return string
 * @uses util::createParams()
 * @uses util::date2strdate()
 * @uses util::createElement()
 */
  public function createLastModified() {
    if( empty( $this->lastmodified ))
      return null;
    return util::createElement( util::$LAST_MODIFIED,
                                util::createParams( $this->lastmodified[util::$LCparams] ),
                                util::date2strdate( $this->lastmodified[util::$LCvalue], 7 ));
  }
/**
 * Set calendar component property completed
 *
 * @param mixed $year optional
 * @param mixed $month optional
 * @param int $day optional
 * @param int $hour optional
 * @param int $min optional
 * @param int $sec optional
 * @param array $params optional
 * @return bool
 * @uses util::setDate2()
 */
  public function setLastModified( $year=null, $month=null, $day=null, $hour=null, $min=null, $sec=null, $params=null ) {
    static $TMDTHIS = 'Ymd\THis';
    if( empty( $year ))
      $year = gmdate( $TMDTHIS );
    $this->lastmodified = util::setDate2( $year, $month, $day, $hour, $min, $sec, $params );
    return true;
  }
}
