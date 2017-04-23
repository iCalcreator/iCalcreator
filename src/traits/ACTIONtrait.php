<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * link      http://kigkonsult.se/iCalcreator/index.php
 * package   iCalcreator
 * version   2.23.10
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
 * ACTION property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-17
 */
trait ACTIONtrait {
/**
 * @var array component property ACTION value
 * @access protected
 */
  protected $action = null;
/**
 * Return formatted output for calendar component property action
 *
 * @uses calendarComponent::getConfig()
 * @uses util::createElement()
 * @uses util::createParams()
 * @return string
 */
  public function createAction() {
    if( empty( $this->action ))
      return null;
    if( empty( $this->action[util::$LCvalue] ))
      return ( $this->getConfig( util::$ALLOWEMPTY )) ? util::createElement( util::$ACTION ) : null;
    return util::createElement( util::$ACTION,
                                util::createParams( $this->action[util::$LCparams] ),
                                $this->action[util::$LCvalue] );
  }
/**
 * Set calendar component property action
 *
 * @param string $value  "AUDIO" / "DISPLAY" / "EMAIL" / "PROCEDURE"  / iana-token / x-name
 * @param mixed $params
 * @return bool
 * @uses calendarComponent::getConfig()
 * @uses util::trimTrailNL()
 * @uses util::setParams()
 */
  public function setAction( $value, $params=null ) {
    if( empty( $value )) {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        $value = util::$EMPTYPROPERTY;
      else
        return false;
    }
    $this->action = array( util::$LCvalue  => util::trimTrailNL( $value ),
                           util::$LCparams => util::setParams( $params ));
    return true;
  }
}
