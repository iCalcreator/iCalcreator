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
/**
 * UID property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.23.20 - 2017-02-17
 */
trait UIDtrait {
/**
 * @var array component property UID value
 * @access protected
 */
  protected $uid = null;
/**
 * Return formatted output for calendar component property uid
 *
 * If uid is missing, uid is created
 *
 * @return string
 * @uses util::makeUid();
 * @uses calendarComponent::getConfig()
 * @uses util::createParams()
 * @uses util::createElement()
 */
  public function createUid() {
    if( empty( $this->uid ))
      $this->uid = util::makeUid( $this->getConfig( util::$UNIQUE_ID ));
    return util::createElement( util::$UID,
                                util::createParams( $this->uid[util::$LCparams] ),
                                $this->uid[util::$LCvalue] );
  }
/**
 * Set calendar component property uid
 *
 * @param string  $value
 * @param array   $params
 * @return bool
 * @uses util::trimTrailNL()
 * @uses util::setParams()
 */
  public function setUid( $value, $params=null ) {
    if( empty( $value ) && ( util::$ZERO != $value ))
      return false; // no allowEmpty check here !!!!
    $this->uid = array( util::$LCvalue  => util::trimTrailNL( $value ),
                        util::$LCparams => util::setParams( $params ));
    return true;
  }
}
