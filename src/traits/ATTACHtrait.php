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
 * ATTACH property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-02
 */
trait ATTACHtrait {
/**
 * @var array component property ATTACH value
 * @access protected
 */
  protected $attach = null;
/**
 * Return formatted output for calendar component property attach
 *
 * @return string
 * @uses util::createParams()
 * @uses util::createElement()
 * @uses calendarComponent::getConfig()
 */
  public function createAttach() {
    if( empty( $this->attach ))
      return null;
    $output       = null;
    foreach( $this->attach as $aix => $attachPart ) {
      if( ! empty( $attachPart[util::$LCvalue] )) {
        $output  .= util::createElement( util::$ATTACH,
                                           util::createParams( $attachPart[util::$LCparams] ),
                                           $attachPart[util::$LCvalue] );
      }
      elseif( $this->getConfig( util::$ALLOWEMPTY ))
        $output  .= util::createElement( util::$ATTACH );
    }
    return $output;
  }
/**
 * Set calendar component property attach
 *
 * @param string  $value
 * @param array   $params
 * @param integer $index
 * @return bool
 * @uses calendarComponent::getConfig()
 * @uses util::setMval()
 */
  public function setAttach( $value, $params=null, $index=null ) {
    if( empty( $value )) {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        $value = util::$EMPTYPROPERTY;
      else
        return false;
    }
    util::setMval( $this->attach,
                    $value,
                    $params,
                    false,
                    $index );
    return true;
  }
}
