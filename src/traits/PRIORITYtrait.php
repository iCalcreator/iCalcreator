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
 */
  public function setPriority( $value, $params=null ) {
    if( empty( $value ) && ! is_numeric( $value ))    {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        $value = util::$EMPTYPROPERTY;
      else
        return false;
    }
    $this->priority = [util::$LCvalue  => $value,
                       util::$LCparams => util::setParams( $params )];
    return true;
  }
}
