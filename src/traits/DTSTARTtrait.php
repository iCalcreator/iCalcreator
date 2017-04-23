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
 * DTSTART property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-02
 */
trait DTSTARTtrait {
/**
 * @var array component property DTSTART value
 * @access protected
 */
  protected $dtstart = null;
/**
 * Return formatted output for calendar component property dtstart
 *
 * @return string
 * @uses util::hasNodate()
 * @uses calendarComponent::getConfig()
 * @uses util::createElement()
 * @uses util::createParams()
 * @uses util::date2strdate()
 */
  public function createDtstart() {
    if( empty( $this->dtstart ))
      return null;
    if( util::hasNodate( $this->dtstart ))
      return ( $this->getConfig( util::$ALLOWEMPTY )) ? util::createElement( util::$DTSTART ): null;
    if( in_array( $this->objName, util::$TZCOMPS ))
      unset( $this->dtstart[util::$LCvalue][util::$LCtz], $this->dtstart[util::$LCparams][util::$TZID] );
    return util::createElement( util::$DTSTART,
                                util::createParams( $this->dtstart[util::$LCparams] ),
                                util::date2strdate( $this->dtstart[util::$LCvalue],
                                                    util::isParamsValueSet( $this->dtstart, util::$DATE ) ? 3 : null ));
  }
/**
 * Set calendar component property dtstart
 *
 * @param mixed  $year
 * @param mixed  $month
 * @param int    $day
 * @param int    $hour
 * @param int    $min
 * @param int    $sec
 * @param string $tz
 * @param array  $params
 * @return bool
 * @uses calendarComponent::getConfig()
 * @uses util::setParams()
 * @uses util::setDate()
 */
  public function setDtstart( $year, $month=null, $day=null, $hour=null, $min=null, $sec=null, $tz=null, $params=null ) {
    if( empty( $year )) {
      if( $this->getConfig( util::$ALLOWEMPTY )) {
        $this->dtstart = array( util::$LCvalue  => util::$EMPTYPROPERTY,
                                util::$LCparams => util::setParams( $params ));
        return true;
      }
      else
        return false;
    }
    if( false === ( $tzid = $this->getConfig( util::$TZID )))
      $tzid = null;
    $this->dtstart = util::setDate( $year, $month, $day, $hour, $min, $sec, $tz,
                                    $params, util::$DTSTART, $this->objName, $tzid);
    return true;
  }
}
