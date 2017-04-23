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
 * RESOURCES property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-17
 */
trait RESOURCEStrait {
/**
 * @var array component property RESOURCES value
 * @access protected
 */
  protected $resources = null;
/**
 * Return formatted output for calendar component property resources
 *
 * @return string
 * @uses calendarComponent::getConfig()
 * @uses util::createElement()
 * @uses util::createParams()
 * @uses util::strrep()
 */
  public function createResources() {
    if( empty( $this->resources ))
      return null;
    $output      = null;
    $lang        = $this->getConfig( util::$LANGUAGE );
    foreach( $this->resources as $rx => $resource ) {
      if( empty( $resource[util::$LCvalue] )) {
        if( $this->getConfig( util::$ALLOWEMPTY ))
          $output .= util::createElement( util::$RESOURCES );
        continue;
      }
      if( is_array( $resource[util::$LCvalue] )) {
        foreach( $resource[util::$LCvalue] as $rix => $rValue )
          $resource[util::$LCvalue][$rix] = util::strrep( $rValue );
        $content = implode( util::$COMMA, $resource[util::$LCvalue] );
      }
      else
        $content = util::strrep( $resource[util::$LCvalue] );
      $output   .= util::createElement( util::$RESOURCES,
                                        util::createParams( $resource[util::$LCparams],
                                                            util::$ALTRPLANGARR,
                                                            $lang ),
                                        $content );
    }
    return $output;
  }
/**
 * Set calendar component property recources
 *
 * @param mixed    $value
 * @param array    $params
 * @param integer  $index
 * @return bool
 * @uses calendarComponent::getConfig()
 * @uses util::setMval()
 * @uses util::trimTrailNL()
 */
  public function setResources( $value, $params=null, $index=null ) {
    if( empty( $value )) {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        $value = util::$EMPTYPROPERTY;
      else
        return false;
    }
    if( is_array( $value ))
      foreach( $value as & $valuePart )
        $valuePart = util::trimTrailNL( $valuePart );
    else
      $value = util::trimTrailNL( $value );
    util::setMval( $this->resources,
                   $value,
                   $params,
                   false,
                   $index );
    return true;
  }
}
