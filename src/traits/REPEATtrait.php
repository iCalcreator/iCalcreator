<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * @copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      http://kigkonsult.se/iCalcreator/index.php
 * @package   iCalcreator.7
 * @version   2.23
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
 * REPEAT property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-05
 */
trait REPEATtrait {
/**
 * @var array component property REPEAT value
 * @access protected
 */
  protected $repeat = null;
/**
 * Return formatted output for calendar component property repeat
 *
 * @return string
 * @uses calendarComponent::getConfig()
 * @uses util::createElement()
 * @uses util::createParams()
 */
  public function createRepeat() {
    if( ! isset( $this->repeat ) ||
        ( empty( $this->repeat ) && ! is_numeric( $this->repeat )))
      return null;
    if( ! isset( $this->repeat[util::$LCvalue]) ||
        ( empty( $this->repeat[util::$LCvalue] ) && ! is_numeric( $this->repeat[util::$LCvalue] )))
      return ( $this->getConfig( util::$ALLOWEMPTY )) ? util::createElement( util::$REPEAT ) : null;
    return util::createElement( util::$REPEAT,
                                util::createParams( $this->repeat[util::$LCparams] ),
                                $this->repeat[util::$LCvalue] );
  }
/**
 * Set calendar component property repeat
 *
 * @param string  $value
 * @param array   $params
 * @uses calendarComponent::getConfig()
 * @uses util::setParams()
 */
  public function setRepeat( $value, $params=null ) {
    if( empty( $value ) && !is_numeric( $value )) {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        $value = util::$EMPTYPROPERTY;
      else
        return false;
    }
    $this->repeat = array( util::$LCvalue  => $value,
                           util::$LCparams => util::setParams( $params ));
    return true;
  }
}
