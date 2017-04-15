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
use kigkonsult\iCalcreator\util\utilAttendee;
/**
 * ORGANIZER property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-04-03
 */
trait ORGANIZERtrait {
/**
 * @var array component property ORGANIZER value
 * @access protected
 */
  protected $organizer = null;
/**
 * Return formatted output for calendar component property organizer
 *
 * @return string
 * @uses calendarComponent::getConfig()
 * @uses util::createElement()
 * @uses util::createParams()
 */
  public function createOrganizer() {
    if( empty( $this->organizer ))
      return null;
    if( empty( $this->organizer[util::$LCvalue] ))
      return ( $this->getConfig( util::$ALLOWEMPTY )) ? util::createElement( util::$ORGANIZER ) : null;
    return util::createElement( util::$ORGANIZER,
                                util::createParams( $this->organizer[util::$LCparams],
                                                    array( util::$CN,
                                                           util::$DIR,
                                                           util::$SENT_BY,
                                                           util::$LANGUAGE ),
                                                    $this->getConfig( util::$LANGUAGE )),
                                $this->organizer[util::$LCvalue] );
  }
/**
 * Set calendar component property organizer
 *
 * @param string  $value
 * @param array   $params
 * @return bool
 * @uses calendarComponent::getConfig()
 * @uses utilAttendee::calAddressCheck()
 * @uses util::setParams()
 */
  public function setOrganizer( $value, $params=null ) {
    if( empty( $value )) {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        $value = util::$EMPTYPROPERTY;
      else
        return false;
    }
    $value = utilAttendee::calAddressCheck( $value, false );
    $this->organizer = array( util::$LCvalue  => $value,
                              util::$LCparams => util::setParams( $params ));
    if( isset( $this->organizer[util::$LCparams][util::$SENT_BY] ))
      $this->organizer[util::$LCparams][util::$SENT_BY] =
        utilAttendee::calAddressCheck( $this->organizer[util::$LCparams][util::$SENT_BY], false );
    return true;
  }
}
