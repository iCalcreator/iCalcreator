<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * link      http://kigkonsult.se/iCalcreator/index.php
 * package   iCalcreator
 * version   2.23.20
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
 * RECURRENCE-ID property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-05
 */
trait RECURRENCE_IDtrait {
/**
 * @var array component property RECURRENCE_ID value
 * @access protected
 */
  protected $recurrenceid = null;
/**
 * Return formatted output for calendar component property recurrence-id
 *
 * @return string
 */
  public function createRecurrenceid() {
    if( empty( $this->recurrenceid ))
      return null;
    if( empty( $this->recurrenceid[util::$LCvalue] ))
      return ( $this->getConfig( util::$ALLOWEMPTY )) ? util::createElement( util::$RECURRENCE_ID ) : null;
    return util::createElement( util::$RECURRENCE_ID,
                                util::createParams( $this->recurrenceid[util::$LCparams] ),
                                util::date2strdate( $this->recurrenceid[util::$LCvalue],
                                                    util::isParamsValueSet( $this->recurrenceid, util::$DATE ) ? 3 : null ));
  }
/**
 * Set calendar component property recurrence-id
 *
 * @param mixed   $year
 * @param mixed   $month
 * @param int     $day
 * @param int     $hour
 * @param int     $min
 * @param int     $sec
 * @param string  $tz
 * @param array   $params
 * @return bool
 */
  public function setRecurrenceid( $year, $month=null, $day=null,
                                   $hour=null, $min=null, $sec=null,
                                   $tz=null, $params=null ) {
    if( empty( $year )) {
      if( $this->getConfig( util::$ALLOWEMPTY )) {
        $this->recurrenceid = [util::$LCvalue  => util::$EMPTYPROPERTY,
                               util::$LCparams => null];
        return true;
      }
      else
        return false;
    }
    $this->recurrenceid = util::setDate( $year, $month, $day, $hour, $min, $sec, $tz,
                                         $params,
                                         null,
                                         null,
                                         $this->getConfig( util::$TZID ));
    return true;
  }
}
