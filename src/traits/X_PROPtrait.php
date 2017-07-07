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
 * X-property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-02
 */
trait X_PROPtrait {
/**
 *  @var array component property X-property value
 *  @access protected
 */
  protected $xprop = null;
/**
 * Return formatted output for calendar/component property x-prop
 *
 * @return string
 */
  public function createXprop() {
    if( empty( $this->xprop ) || !is_array( $this->xprop ))
      return null;
    $output        = null;
    $lang          = $this->getConfig( util::$LANGUAGE );
    foreach( $this->xprop as $label => $xpropPart ) {
      if( ! isset( $xpropPart[util::$LCvalue]) ||
          ( empty( $xpropPart[util::$LCvalue] ) && ! is_numeric( $xpropPart[util::$LCvalue] ))) {
        if( $this->getConfig( util::$ALLOWEMPTY ))
          $output .= util::createElement( $label );
        continue;
      }
      if( is_array( $xpropPart[util::$LCvalue] )) {
        foreach( $xpropPart[util::$LCvalue] as $pix => $theXpart )
          $xpropPart[util::$LCvalue][$pix] = util::strrep( $theXpart );
        $xpropPart[util::$LCvalue]  = implode( util::$COMMA, $xpropPart[util::$LCvalue] );
      }
      else
        $xpropPart[util::$LCvalue] = util::strrep( $xpropPart[util::$LCvalue] );
      $output     .= util::createElement( $label,
                                          util::createParams( $xpropPart[util::$LCparams],
                                                              [util::$LANGUAGE],
                                                              $lang ),
                                          util::trimTrailNL( $xpropPart[util::$LCvalue] ));
    }
    return $output;
  }
/**
 * Set calendar property x-prop
 *
 * @param string $label
 * @param string $value
 * @param array $params optional
 * @return bool
 */
  public function setXprop( $label, $value, $params=false ) {
    if( empty( $label ) || ! util::isXprefixed( $label ))
      return false;
    if( empty( $value ) && ! is_numeric( $value )) {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        $value     = util::$EMPTYPROPERTY;
      else
        return false;
    }
    $xprop         = [util::$LCvalue => $value];
    $xprop[util::$LCparams] = util::setParams( $params );
    if( ! is_array( $this->xprop ))
      $this->xprop = [];
    $this->xprop[strtoupper( $label )] = $xprop;
    return true;
  }
/**
 * Delete component property X-prop value
 *
 * @param string $propName
 * @param array  $xProp     component X-property
 * @param int    $propix    removal counter
 * @param array  $propdelix
 * @access protected
 * @static
 */
  protected static function deleteXproperty( $propName=null, & $xProp, & $propix, & $propdelix ) {
    $reduced = [];
    if( $propName != util::$X_PROP ) {
      if( ! isset( $xProp[$propName] )) {
        unset( $propdelix[$propName] );
        return false;
      }
      foreach( $xProp as $k => $xValue ) {
        if(( $k != $propName ) && ! empty( $xValue ))
          $reduced[$k] = $xValue;
      }
    }
    else {
      if( count( $xProp ) <= $propix ) {
        unset( $propdelix[$propName] );
        return false;
      }
      $xpropno = 0;
      foreach( $xProp as $xPropKey => $xPropValue ) {
        if( $propix != $xpropno )
          $reduced[$xPropKey] = $xPropValue;
        $xpropno++;
      }
    }
    $xProp = $reduced;
    if( empty( $xProp )) {
      $xProp = null;
      unset( $propdelix[$propName] );
      return false;
    }
    return true;
  }
}
