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
/**
 * VERSION property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-18
 */
trait VERSIONtrait {
/**
 * Property Name: VERSION
 *
 * Description: A value of "2.0" corresponds to this memo.
 *
 * @var string calendar property VERSION
 * @access protected
 */
  protected $version = '2.0';
/**
 * Return formatted output for calendar property version
 *
 * If version is missing, version is set
 *
 * @return string
 */
  public function createVersion() {
    return sprintf( self::$FMTICAL, util::$VERSION,
                                    $this->version );
  }
/**
 * Set (another?) calendar version
 *
 * @param string $value
 */
  public function setVersion( $value ) {
    if( empty( $value ))
      return false;
    $this->version = $value;
    return true;
  }
}
