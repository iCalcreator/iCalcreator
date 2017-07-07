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
namespace kigkonsult\iCalcreator\util;
/**
 * iCalcreator geo support class
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-02
 */
class utilGeo {
/**
 *  @var string  GEO vars: output format for geo latitude and longitude (before rtrim) etc
 *  @access public
 *  @static
 */
  public static $geoLatFmt  = '%09.6f';
  public static $geoLongFmt = '%8.6f';
  public static $LATITUDE   = 'latitude';
  public static $LONGITUDE  = 'longitude';
/**
 * Return formatted geo output
 *
 * @param float $ll
 * @param string $format
 * @return string
 * @access public
 * @static
 */
  public static function geo2str2( $ll, $format ) {
    if( 0.0 < $ll )
      $sign   = util::$PLUS;
    else
      $sign   = ( 0.0 > $ll ) ? util::$MINUS : null;
    return rtrim( rtrim( $sign . sprintf( $format, abs( $ll )),
                                                   util::$ZERO ),
                                                   util::$DOT );
  }
}
