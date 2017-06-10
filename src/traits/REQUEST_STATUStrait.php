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
 * REQUEST-STATUS property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-19
 */
trait REQUEST_STATUStrait {
/**
 * @var array component property REQUEST-STATUS value
 * @access protected
 */
  protected $requeststatus = null;
/**
 * Return formatted output for calendar component property request-status
 *
 * @return string
 * @uses calendarComponent::getConfig()
 * @uses util::createElement()
 * @uses util::createParams()
 * @uses util::strrep()
 */
  public function createRequestStatus() {
    static $STATCODE = 'statcode';
    static $TEXT     = 'text';
    static $EXTDATA  = 'extdata';
    if( empty( $this->requeststatus ))
      return null;
    $output = null;
    $lang   = $this->getConfig( util::$LANGUAGE );
    foreach( $this->requeststatus as $rx => $rStat ) {
      if( empty( $rStat[util::$LCvalue][$STATCODE] )) {
        if( $this->getConfig( util::$ALLOWEMPTY ))
          $output .= util::createElement( util::$REQUEST_STATUS );
        continue;
      }
      $content     = number_format( (float) $rStat[util::$LCvalue][$STATCODE], 2, util::$DOT, null );
      $content    .= util::$SEMIC . util::strrep( $rStat[util::$LCvalue][$TEXT] );
      if( isset( $rStat[util::$LCvalue][$EXTDATA] ))
        $content  .= util::$SEMIC . util::strrep( $rStat[util::$LCvalue][$EXTDATA] );
      $output     .= util::createElement( util::$REQUEST_STATUS,
                                          util::createParams( $rStat[util::$LCparams],
                                                              [util::$LANGUAGE],
                                                              $lang ),
                                          $content );
    }
    return $output;
  }
/**
 * Set calendar component property request-status
 *
 * @param float    $statcode
 * @param string   $text
 * @param string   $extdata
 * @param array    $params
 * @param integer  $index
 * @return bool
 * @uses calendarComponent::getConfig()
 * @uses util::trimTrailNL( )
 * @uses util::setMval()
 */
  public function setRequestStatus( $statcode, $text, $extdata=null, $params=null, $index=null ) {
    static $STATCODE = 'statcode';
    static $TEXT     = 'text';
    static $EXTDATA  = 'extdata';
    if( empty( $statcode ) || empty( $text )) {
      if( $this->getConfig( util::$ALLOWEMPTY ))
        $statcode = $text = util::$EMPTYPROPERTY;
      else
        return false;
    }
    $input = [$STATCODE => $statcode,
              $TEXT     => util::trimTrailNL( $text )];
    if( $extdata )
      $input[$EXTDATA] = util::trimTrailNL( $extdata );
    util::setMval( $this->requeststatus,
                   $input,
                   $params,
                   false,
                   $index );
    return true;
  }
}
