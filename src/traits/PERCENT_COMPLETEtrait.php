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
 * PERCENT-COMPLETE property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-05
 */
trait PERCENT_COMPLETEtrait {
/**
 * @var array component property PERCENT_COMPLETE value
 * @access protected
 */
  protected $percentcomplete = null;
/**
 * Return formatted output for calendar component property percent-complete
 *
 * @return string
 * @uses calendarComponent::getConfig()
 * @uses util::createElement()
 * @uses util::createParams()
 */
  public function createPercentComplete() {
    if( ! isset( $this->percentcomplete ) ||
        ( empty( $this->percentcomplete ) && ! is_numeric( $this->percentcomplete )))
      return null;
    if(     ! isset( $this->percentcomplete[util::$LCvalue] ) ||
            ( empty( $this->percentcomplete[util::$LCvalue] ) &&
       ! is_numeric( $this->percentcomplete[util::$LCvalue] )))
      return ( $this->getConfig( util::$ALLOWEMPTY )) ? util::createElement( util::$PERCENT_COMPLETE ) : null;
    return util::createElement( util::$PERCENT_COMPLETE,
                                util::createParams( $this->percentcomplete[util::$LCparams] ),
                                $this->percentcomplete[util::$LCvalue] );
  }
/**
 * Set calendar component property percent-complete
 *
 * @param int    $value
 * @param array  $params
 * @return bool
 * @uses calendarComponent::getConfig()
 * @uses util::setParams()
 */
  public function setPercentComplete( $value, $params=null ) {
    if( empty( $value ) && ! is_numeric( $value )) {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        $value = util::$EMPTYPROPERTY;
      else
        return false;
    }
    $this->percentcomplete = [util::$LCvalue  => $value,
                              util::$LCparams => util::setParams( $params )];
    return true;
  }
}
