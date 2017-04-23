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
use kigkonsult\iCalcreator\util\utilRecur;
/**
 * EXRULE property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-04-03
 */
trait EXRULEtrait {
/**
 * @var array component property EXRULE value
 * @access protected
 */
  protected $exrule = null;
/**
 * Return formatted output for calendar component property exrule
 *
 * @return string
 * @uses utilRecur::formatRecur()
 */
  public function createExrule() {
    return utilRecur::formatRecur( util::$EXRULE,
                                   $this->exrule,
                                   $this->getConfig( util::$ALLOWEMPTY ));
  }
/**
 * Set calendar component property exdate
 *
 * @param array   $exruleset
 * @param array   $params
 * @param integer $index
 * @return bool
 * @uses calendarComponent::getConfig()
 * @uses util::setMval()
 * @uses utilRecur::setRexrule()
 */
  public function setExrule( $exruleset, $params=null, $index=null ) {
    if( empty( $exruleset )) {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        $exruleset = util::$EMPTYPROPERTY;
      else
        return false;
    }
    util::setMval( $this->exrule,
                   utilRecur::setRexrule( $exruleset ),
                   $params,
                   false,
                   $index );
    return true;
  }
}
