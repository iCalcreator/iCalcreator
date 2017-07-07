<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * link      http://kigkonsult.se/iCalcreator/index.php
 * package   iCalcreator
 * version   2.23.20
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
 * PRODID property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-03-15
 */
trait PRODIDtrait {
/**
 * @var string calendar property PRODID
 * @access protected
 */
  protected $prodid = null;
/**
 * Return formatted output for calendar property prodid
 *
 * @return string
 */
  public function createProdid() {
    if( ! isset( $this->prodid ))
      $this->makeProdid();
    return util::createElement( util::$PRODID,
                                null,
                                $this->prodid );
  }
/**
 * Create default value for calendar prodid,
 * Do NOT alter or remove this method or the invoke of this method,
 * a licence violation.
 *
 * [rfc5545]
 * "Conformance: The property MUST be specified once in an iCalendar object.
 *  Description: The vendor of the implementation SHOULD assure that this
 *  is a globally unique identifier; using some technique such as an FPI
 *  value, as defined in [ISO 9070]."
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.20 - 2017-01-29
 */
  public function makeProdid() {
    static $FMT = '-//%s//NONSGML kigkonsult.se %s//%s';
    if( false !== ( $lang = $this->getConfig( util::$LANGUAGE )))
      $lang = strtoupper( $lang );
    else
      $lang = null;
    $this->prodid  = sprintf( $FMT, $this->getConfig( util::$UNIQUE_ID ),
                                    ICALCREATOR_VERSION,
                                    $lang );
  }
}
