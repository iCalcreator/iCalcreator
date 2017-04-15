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
/**
 * iCalcreator.php
 *
 * iCalcreator package autoloader
 *
 * @package icalcreator
 * @copyright Copyright (c) 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @since 2.23 - 2017-04-06
 */
/**
 *         Do NOT remove or change version!!
 */
define( 'ICALCREATOR_VERSION', 'iCalcreator 2.23.7' );
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
