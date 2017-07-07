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
/**
 * autoload.php
 *
 * iCalcreator package autoloader
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.23.16 - 2017-05-27
 */
/**
 *         Do NOT alter or remove the constant!!
 */
define( 'ICALCREATOR_VERSION', 'iCalcreator 2.23.20' );
/**
 * load iCalcreator src and support classes and traits
 */
spl_autoload_register(
  function( $class ) {
    static $SRC      = 'src';
    static $BS       = '\\';
    static $PHP      = '.php';
    static $PREFIX   = 'kigkonsult\\iCalcreator\\';
    static $BASEDIR  = null;
    if( is_null( $BASEDIR ))
      $BASEDIR       = __DIR__ . DIRECTORY_SEPARATOR . $SRC . DIRECTORY_SEPARATOR;
    if( 0 != strncmp( $PREFIX, $class, 23 ))
      return false;
    $class   = substr( $class, 23 );
    if( false !== strpos( $class, $BS ))
      $class = str_replace( $BS, DIRECTORY_SEPARATOR, $class );
    $file    = $BASEDIR . $class . $PHP;
    if( file_exists( $file )) {
      require $file;
      return true;
    }
    return false;
  }
);
/**
 * iCalcreator timezones add-on functionality functions, IF required?
 */
// include __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'iCal.tz.inc.php';
