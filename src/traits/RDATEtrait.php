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
use kigkonsult\iCalcreator\util\utilRexdate;
/**
 * RDATE property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-26
 */
trait RDATEtrait {
/**
 * @var array component property RDATE value
 * @access protected
 */
  protected $rdate = null;
/**
 * Return formatted output for calendar component property rdate
 *
 * @return string
 * @uses calendarComponent::getConfig()
 * @uses utilRexdate::formatRdate()
 */
  public function createRdate() {
    if( empty( $this->rdate ))
      return null;
    return utilRexdate::formatRdate( $this->rdate,
                                     $this->getConfig( util::$ALLOWEMPTY ),
                                     $this->objName );
  }
/**
 * Set calendar component property rdate
 *
 * @param array   $rdates
 * @param array   $params
 * @param integer $index
 * @return bool
 * @uses calendarComponent::getConfig()
 * @uses util::setMval()
 * @uses utilRexdate::prepInputRdate()
 */
  public function setRdate( $rdates, $params=null, $index=null ) {
    if( empty( $rdates )) {
      if( $this->getConfig( util::$ALLOWEMPTY )) {
        util::setMval( $this->rdate,
                       util::$EMPTYPROPERTY,
                       $params,
                       false,
                       $index );
        return true;
      }
      else
        return false;
    }
    $input = utilRexdate::prepInputRdate( $rdates,
                                          $params,
                                          $this->objName );
    util::setMval( $this->rdate,
                   $input[util::$LCvalue],
                   $input[util::$LCparams],
                   false,
                   $index );
    return true;
  }
}
