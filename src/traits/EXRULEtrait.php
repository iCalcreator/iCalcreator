<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * @copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      http://kigkonsult.se/iCalcreator/index.php
 * @package   iCalcreator
 * @version   2.23.7
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
