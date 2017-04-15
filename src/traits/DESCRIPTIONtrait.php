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
 * DESCRIPTION property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-02
 */
trait DESCRIPTIONtrait {
/**
 * @var array component property DESCRIPTION value
 * @access protected
 */
  protected $description = null;
/**
 * Return formatted output for calendar component property description
 *
 * @return string
 * @uses calendarComponent::getConfig()
 * @uses util::createParams()
 * @uses util::strrep()
 * @uses util::createElement()
 */
  public function createDescription() {
    if( empty( $this->description ))
      return null;
    $output      = null;
    $lang        = $this->getConfig( util::$LANGUAGE );
    foreach( $this->description as $dx => $description ) {
      if( ! empty( $description[util::$LCvalue] ))
        $output .= util::createElement( util::$DESCRIPTION,
                                        util::createParams( $description[util::$LCparams],
                                                            util::$ALTRPLANGARR,
                                                            $lang ),
                                        util::strrep( $description[util::$LCvalue] ));
      elseif( $this->getConfig( util::$ALLOWEMPTY ))
        $output .= util::createElement( util::$DESCRIPTION );
    }
    return $output;
  }
/**
 * Set calendar component property description
 *
 * @param string  $value
 * @param array   $params
 * @param integer $index
 * @return bool
 * @uses calendarComponent::getConfig()
 * @uses util::setMval()
 */
  public function setDescription( $value, $params=null, $index=null ) {
    if( empty( $value )) {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        $value = util::$EMPTYPROPERTY;
      else
        return false;
    }
    if( util::$LCVJOURNAL != $this->objName )
      $index = 1;
    util::setMval( $this->description,
                    $value,
                    $params,
                    false,
                    $index );
    return true;
  }
}
