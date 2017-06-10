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
 * SEQUENCE property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-24
 */
trait SEQUENCEtrait {
/**
 * @var array component property SEQUENCE value
 * @access protected
 */
  protected $sequence = null;
/**
 * Return formatted output for calendar component property sequence
 *
 * @return string
 * @uses calendarComponent::getConfig()
 * @uses util::createElement()
 * @uses util::createParams()
 */
  public function createSequence() {
    if( ! isset( $this->sequence ) ||
        ( empty( $this->sequence ) && ! is_numeric( $this->sequence )))
      return null;
    if((    ! isset( $this->sequence[util::$LCvalue] ) ||
            ( empty( $this->sequence[util::$LCvalue] ) && ! is_numeric( $this->sequence[util::$LCvalue] ))) &&
    ( util::$ZERO != $this->sequence[util::$LCvalue] ))
      return ( $this->getConfig( util::$ALLOWEMPTY )) ? util::createElement( util::$SEQUENCE ) : null;
    return util::createElement( util::$SEQUENCE,
                                util::createParams( $this->sequence[util::$LCparams] ),
                                $this->sequence[util::$LCvalue] );
  }
/**
 * Set calendar component property sequence
 *
 * @param int    $value
 * @param array  $params
 * @return bool
 * @uses util::setParams();
 */
  public function setSequence( $value=null, $params=null ) {
    if(( empty( $value ) && ! is_numeric( $value )) && ( util::$ZERO != $value ))
      $value = ( isset( $this->sequence[util::$LCvalue] ) &&
                 ( -1 < $this->sequence[util::$LCvalue] ))
             ? $this->sequence[util::$LCvalue] + 1
             : util::$ZERO;
    $this->sequence = [util::$LCvalue  => $value,
                       util::$LCparams => util::setParams( $params )];
    return true;
  }
}
