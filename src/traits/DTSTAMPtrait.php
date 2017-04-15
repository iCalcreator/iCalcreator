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
 * DTSTAMP property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-02
 */
trait DTSTAMPtrait {
/**
 * @var array component property DTSTAMP value
 * @access protected
 */
  protected $dtstamp = null;
/**
 * Return formatted output for calendar component property dtstamp
 *
 * @return string
 * @uses util::hasNodate()
 * @uses util::makeDtstamp()
 * @uses util::createParams()
 * @uses util::date2strdate()
 * @uses util::createElement()
 */
  public function createDtstamp() {
    if( util::hasNodate( $this->dtstamp ))
      $this->dtstamp = util::makeDtstamp();
    return util::createElement( util::$DTSTAMP,
                                util::createParams( $this->dtstamp[util::$LCparams] ),
                                util::date2strdate( $this->dtstamp[util::$LCvalue], 7 ));
  }
/**
 * Set calendar component property dtstamp
 *
 * @param mixed $year
 * @param mixed $month
 * @param int   $day
 * @param int   $hour
 * @param int   $min
 * @param int   $sec
 * @param array $params
 * @return bool
 * @uses util::makeDtstamp()
 * @uses util::setDate2()
 */
  public function setDtstamp( $year, $month=null, $day=null, $hour=null, $min=null, $sec=null, $params=null ) {
    $this->dtstamp = ( empty( $year )) ? util::makeDtstamp()
                                       : util::setDate2( $year, $month, $day, $hour, $min, $sec, $params );
    return true;
  }
}
