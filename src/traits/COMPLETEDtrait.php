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
 * @uses util::hasNodate()
 * @uses calendarComponent::getConfig()
 * @uses util::createElement()
 * @uses util::createParams()
 * @uses util::date2strdate();
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
 * @uses calendarComponent::getConfig()
 * @uses util::setParams()
 * @uses calendarComponent::$completed
 * @uses util::setDate2()
 */
  public function setCompleted( $year, $month=null, $day=null, $hour=null, $min=null, $sec=null, $params=null ) {
    if( empty( $year )) {
      if( $this->getConfig( util::$ALLOWEMPTY )) {
        $this->completed = array( util::$LCvalue  => util::$EMPTYPROPERTY,
                                  util::$LCparams => util::setParams( $params ));
        return true;
      }
      else
        return false;
    }
    $this->completed = util::setDate2( $year, $month, $day, $hour, $min, $sec, $params );
    return true;
  }
}
