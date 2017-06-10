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
namespace kigkonsult\iCalcreator\util;
use kigkonsult\iCalcreator\vcalendar;
/**
 * iCalcreator redirect support class
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.23.6 - 2017-04-13
 */
class utilRedirect {
/**
 * HTTP headers
 *
 * @var array $headers
 * @access private
 * @static
 */
  private static $headers = ['Content-Encoding: gzip',
                                   'Vary: *',
                                   'Content-Length: %s',
                                   'Content-Type: text/calendar; charset=utf-8',
                                   'Content-Disposition: attachment; filename="%s"',
                                   'Content-Disposition: inline; filename="%s"',
                                   'Cache-Control: max-age=10',
  ];
/**
 * Return created, updated and/or parsed calendar, sending a HTTP redirect header.
 *
 * @param vcalendar $calendar
 * @param bool   $utf8Encode
 * @param bool   $gzip
 * @param bool   $cdType       true : Content-Disposition: attachment... (default), false : ...inline...
 * @uses vcalendar::getConfig()
 * @uses vcalendar::createCalendar()
 * @uses utilRedirect::$headers
 * @return bool true on success, false on error
 * @static
 */
  public static function returnCalendar( vcalendar $calendar,
                                         $utf8Encode=false,
                                         $gzip=false,
                                         $cdType=true ) {
    static $ICR = 'iCr';
    $filename = $calendar->getConfig( util::$FILENAME );
    $output   = $calendar->createCalendar();
    if( $utf8Encode )
      $output = utf8_encode( $output );
    $fsize    = null;
    if( $gzip ) {
      $output = gzencode( $output, 9 );
      $fsize  = strlen( $output );
      header( self::$headers[0] );
      header( self::$headers[1] );
    }
    else {
      if( false !== ( $temp = tempnam( sys_get_temp_dir(), $ICR ))) {
        if( false !== file_put_contents( $temp, $output ))
          $fsize = @filesize( $temp );
        unlink( $temp );
      }
    }
    if( ! empty( $fsize ))
      header( sprintf( self::$headers[2], $fsize ));
    header( self::$headers[3] );
    $cdType = ( $cdType ) ? 4 : 5;
    header( sprintf( self::$headers[$cdType], $filename ));
    header( self::$headers[6] );
    echo $output;
    return true;
  }
/**
 * If recent version of calendar file exists (default one hour), an HTTP redirect header is sent
 *
 * @param vcalendar $calendar
 * @param int    $timeout  default 3600 sec
 * @param bool   $cdType   true : Content-Disposition: attachment... (default), false : ...inline...
 * @return bool true on success, false on error
 * @uses vcalendar::getConfig()
 * @uses vcalendar::$headers
 * @static
 */
  public static function useCachedCalendar( vcalendar $calendar,
                                            $timeout=3600,
                                            $cdType=true ) {
    static $R = 'r';
    if( false === ( $dirfile = $calendar->getConfig( util::$URL )))
      $dirfile = $calendar->getConfig( util::$DIRFILE );
    if( ! is_file( $dirfile ) || ! is_readable( $dirfile ))
      return false;
    if( time() - filemtime( $dirfile ) > $timeout )
      return false;
    clearstatcache();
    $fsize     = @filesize( $dirfile );
    $filename  = $calendar->getConfig( util::$FILENAME );
    header( self::$headers[3] );
    if( ! empty( $fsize ))
      header( sprintf( self::$headers[2], $fsize ));
    $cdType    = ( $cdType ) ? 4 : 5;
    header( sprintf( self::$headers[$cdType], $filename ));
    header( self::$headers[6] );
    if( false === ( $fp = @fopen( $dirfile, $R )))
      return false;
    fpassthru( $fp );
    fclose( $fp );
    return true;
  }
}
