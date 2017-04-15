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
 * CATEGORIES property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-02
 */
trait CATEGORIEStrait {
/**
 * @var array component property CATEGORIES value
 * @access protected
 */
  protected $categories = null;
/**
 * Return formatted output for calendar component property categories
 *
 * @return string
 * @uses calendarComponent::getConfig()
 * @uses util::createElement()
 * @uses util::createParams()
 * @uses util::strrep()
 */
  public function createCategories() {
    if( empty( $this->categories ))
      return null;
    $output = null;
    $lang   = $this->getConfig( util::$LANGUAGE );
    foreach( $this->categories as $cx => $category ) {
      if( empty( $category[util::$LCvalue] )) {
        if ( $this->getConfig( util::$ALLOWEMPTY ))
          $output .= util::createElement( util::$CATEGORIES );
        continue;
      }
      if( is_array( $category[util::$LCvalue] )) {
        foreach( $category[util::$LCvalue] as $cix => $cValue )
          $category[util::$LCvalue][$cix] = util::strrep( $cValue );
        $content  = implode( util::$COMMA, $category[util::$LCvalue] );
      }
      else
        $content  = util::strrep( $category[util::$LCvalue] );
      $output    .= util::createElement( util::$CATEGORIES,
                                         util::createParams( $category[util::$LCparams],
                                                             array( util::$LANGUAGE ),
                                                             $lang ),
                                         $content );
    }
    return $output;
  }
/**
 * Set calendar component property categories
 *
 * @param mixed   $value
 * @param array   $params
 * @param integer $index
 * @return bool
 * @uses calendarComponent::getConfig()
 * @uses util::setMval()
 */
  public function setCategories( $value, $params=null, $index=null ) {
    if( empty( $value )) {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        $value = util::$EMPTYPROPERTY;
      else
        return false;
    }
    util::setMval( $this->categories,
                    $value,
                    $params,
                    false,
                    $index );
    return true;
  }
}
