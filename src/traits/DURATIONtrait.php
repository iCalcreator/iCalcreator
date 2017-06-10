<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * link      http://kigkonsult.se/iCalcreator/index.php
 * package   iCalcreator
 * version   2.23.16
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
 * DURATION property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-05
 */
trait DURATIONtrait {
/**
 * @var array component property DURATION value
 * @access protected
 */
  protected $duration = null;
/**
 * Return formatted output for calendar component property duration
 *
 * @return string
 * @uses calendarComponent::getConfig()
 * @uses util::createElement()
 * @uses util::createParams()
 * @uses util::duration2str()
 */
  public function createDuration() {
    if( empty( $this->duration ))
      return null;
    if( ! isset( $this->duration[util::$LCvalue][util::$LCWEEK] ) &&
        ! isset( $this->duration[util::$LCvalue][util::$LCDAY] )  &&
        ! isset( $this->duration[util::$LCvalue][util::$LCHOUR] ) &&
        ! isset( $this->duration[util::$LCvalue][util::$LCMIN] )  &&
        ! isset( $this->duration[util::$LCvalue][util::$LCSEC] )) {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        return util::createElement( util::$DURATION );
      else
        return null;
    }
    return util::createElement( util::$DURATION,
                                util::createParams( $this->duration[util::$LCparams] ),
                                util::duration2str( $this->duration[util::$LCvalue] ));
  }
/**
 * Set calendar component property duration
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.23.8 - 2017-04-17
 * @param mixed $week
 * @param mixed $day
 * @param int   $hour
 * @param int   $min
 * @param int   $sec
 * @param array $params
 * @return bool
 * @uses calendarComponent::getConfig()
 * @uses util::duration2arr()
 * @uses util::durationStr2arr()
 * @uses util::setParams()
 * @uses util::trimTrailNL()
 * @uses util::duration2arr()
 */
  public function setDuration( $week, $day=null, $hour=null, $min=null, $sec=null, $params=null ) {
    static $PLUSMINUSARR = ['+', '-'];
    if( empty( $week ) && empty( $day ) && empty( $hour ) && empty( $min ) && empty( $sec )) {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        $week = $day = null;
      else
        return false;
    }
    if( is_array( $week ) && ( 1 <= count( $week )))
      $this->duration = [util::$LCvalue  => util::duration2arr( $week ),
                         util::$LCparams => util::setParams( $day )];
    elseif( is_string( $week ) && ( 3 <= strlen( trim( $week )))) {
      $week = util::trimTrailNL( trim( $week ));
      if( in_array( $week[0], $PLUSMINUSARR ))
        $week = substr( $week, 1 );
      $this->duration = [util::$LCvalue  => util::durationStr2arr( $week ),
                         util::$LCparams => util::setParams( $day )];
    }
    else
      $this->duration = [util::$LCvalue  => util::duration2arr( [util::$LCWEEK => $week,
                                                                 util::$LCDAY  => $day,
                                                                 util::$LCHOUR => $hour,
                                                                 util::$LCMIN  => $min,
                                                                 util::$LCSEC  => $sec]),
                         util::$LCparams => util::setParams( $params )];
    return true;
  }
}
