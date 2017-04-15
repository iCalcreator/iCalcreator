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
 * PRIORITY property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-02
 */
trait PRIORITYtrait {
/**
 * @var array component property PRIORITY value
 * @access protected
 */
  protected $priority = null;
/**
 * Return formatted output for calendar component property priority
 *
 * @return string
 * @uses calendarComponent::getConfig()
 * @uses util::createElement()
 * @uses util::createParams()
 */
  public function createPriority() {
    if( ! isset( $this->priority ) ||
        ( empty( $this->priority ) && ! is_numeric( $this->priority )))
      return null;
    if( ! isset( $this->priority[util::$LCvalue] ) ||
       ( empty( $this->priority[util::$LCvalue] ) && !is_numeric( $this->priority[util::$LCvalue] )))
      return ( $this->getConfig( util::$ALLOWEMPTY )) ? util::createElement( util::$PRIORITY ) : null;
    return util::createElement( util::$PRIORITY,
                                util::createParams( $this->priority[util::$LCparams] ),
                                $this->priority[util::$LCvalue] );
  }
/**
 * Set calendar component property priority
 *
 * @param int    $value
 * @param array  $params
 * @return bool
 * @uses calendarComponent::getConfig()
 * @uses util::setParams()
 */
  public function setPriority( $value, $params=null ) {
    if( empty( $value ) && ! is_numeric( $value ))    {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        $value = util::$EMPTYPROPERTY;
      else
        return false;
    }
    $this->priority = array( util::$LCvalue  => $value,
                             util::$LCparams => util::setParams( $params ));
    return true;
  }
}
