<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * link      http://kigkonsult.se/iCalcreator/index.php
 * package   iCalcreator
 * version   2.23.18
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
 * COMPLETED property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-19
 */
trait COMPLETEDtrait {
/**
 * @var array component property COMPLETED value
 * @access protected
 */
  protected $completed = null;
/**
 * Return formatted output for calendar component property completed
 *
 * @return string
 */
  public function createCompleted( ) {
    if( empty( $this->completed ))
      return null;
    if( util::hasNodate( $this->completed ))
      return ( $this->getConfig( util::$ALLOWEMPTY )) ? util::createElement( util::$COMPLETED ) : null;
    return util::createElement( util::$COMPLETED,
                                util::createParams( $this->completed[util::$LCparams] ),
                                util::date2strdate( $this->completed[util::$LCvalue], 7 ));
  }
/**
 * Set calendar component property completed
 *
 * @param mixed $year
 * @param mixed $month
 * @param int   $day
 * @param int   $hour
 * @param int   $min
 * @param int   $sec
 * @param array $params
 * @return bool
 */
  public function setCompleted( $year, $month=null, $day=null, $hour=null, $min=null, $sec=null, $params=null ) {
    if( empty( $year )) {
      if( $this->getConfig( util::$ALLOWEMPTY )) {
        $this->completed = [util::$LCvalue  => util::$EMPTYPROPERTY,
                            util::$LCparams => util::setParams( $params )];
        return true;
      }
      else
        return false;
    }
    $this->completed = util::setDate2( $year, $month, $day, $hour, $min, $sec, $params );
    return true;
  }
}
