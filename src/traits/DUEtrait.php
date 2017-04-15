<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * @copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      http://kigkonsult.se/iCalcreator/index.php
 * @package   iCalcreator
 * @version   2.23.7
 * @license   Part 1. This software is for
 *                    individual evaluation use and evaluation result use only;
 *                    non assignable, non-transferable, non-distributable,
 *                    non-commercial and non-public rights, use and result use.
 *            Part 2. Creative Commons
 *                    Attribution-NonCommercial-NoDerivatives 4.0 International License
 *                    (http://creativecommons.org/licenses/by-nc-nd/4.0/)
 *            In case of conflict, Part 1 supercede Part 2.
 *
 * This file is a part of iCalcreator.
 */
namespace kigkonsult\iCalcreator\traits;
use kigkonsult\iCalcreator\util\util;
/**
 * DUE property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-02
 */
trait DUEtrait {
/**
 * @var array component property DUE value
 * @access protected
 */
  protected $due = null;
/**
 * Return formatted output for calendar component property due
 *
 * @return string
 * @uses util::isParamsValueSet()
 * @uses calendarComponent::getConfig()
 * @uses util::createElement()
 * @uses util::date2strdate()
 * @uses util::createParams()
 */
  public function createDue() {
    if( empty( $this->due ))
      return null;
    if( util::hasNodate( $this->due ))
      return ( $this->getConfig( util::$ALLOWEMPTY )) ? util::createElement( util::$DUE ) : null;
    return util::createElement( util::$DUE,
                                util::createParams( $this->due[util::$LCparams] ),
                                util::date2strdate( $this->due[util::$LCvalue],
                                                    util::isParamsValueSet( $this->due, util::$DATE ) ? 3 : null ));
  }
/**
 * Set calendar component property due
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
  public function setDue( $year, $month=null, $day=null, $hour=null, $min=null, $sec=null, $tz=null, $params=null ) {
    if( empty( $year )) {
      if( $this->getConfig( util::$ALLOWEMPTY )) {
        $this->due = array( util::$LCvalue  => util::$EMPTYPROPERTY,
                            util::$LCparams => util::setParams( $params ));
        return true;
      }
      else
        return false;
    }
    if( false === ( $tzid = $this->getConfig( util::$TZID )))
      $tzid = null;
    $this->due = util::setDate( $year, $month, $day, $hour, $min, $sec, $tz,
                                $params, null, null, $tzid );
    return true;
  }
}
