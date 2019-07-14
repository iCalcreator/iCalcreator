<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.28
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
 *
 *           iCalcreator is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is a part of iCalcreator.
 */
/**
 * autoload.php
 *
 * iCalcreator package test autoloader
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.27.21 - 2019-06-06
 */
/**
 *         Do NOT alter or remove the constant!!
 */
if( ! defined( 'ICALCREATOR_VERSION' )) {
    define( 'ICALCREATOR_VERSION', 'iCalcreator 2.27.21' );
}
/**
 * load iCalcreator src and support classes and Traits
 */
spl_autoload_register(
  function( $class ) {
      static $PREFIX   = 'Kigkonsult\\Icalcreator\\';
      static $BS       = '\\';
      static $PATHSRC  = null;
      static $PATHTEST = null;
      static $DD       = '..';
      static $SRC      = 'src';
      static $TEST     = 'test';
      static $FMT      = '%1$s%2$s.php';
      if( empty( $PATHSRC ) ) {
          $PATHSRC  =
              realpath( __DIR__ . DIRECTORY_SEPARATOR . $DD . DIRECTORY_SEPARATOR . $SRC ) . DIRECTORY_SEPARATOR;
          $PATHTEST =
              realpath( __DIR__ . DIRECTORY_SEPARATOR . $DD . DIRECTORY_SEPARATOR . $TEST ) . DIRECTORY_SEPARATOR;
      }
      if( 0 != strncmp( $PREFIX, $class, 23 ) ) {
          return false;
      }
      $class = substr( $class, 23 );
      if( false !== strpos( $class, $BS ) ) {
          $class = str_replace( $BS, DIRECTORY_SEPARATOR, $class );
      }
      $file = sprintf( $FMT, $PATHSRC, $class );
      if( file_exists( $file ) ) {
          include $file;
      }
      else {
          $file = sprintf( $FMT, $PATHTEST, $class );
          if( file_exists( $file ) ) {
              include $file;
          }
      }
  }
);
/**
 * iCalcreator timezones add-on functionality functions, IF required?
 */
// include __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'iCal.tz.inc.php';
