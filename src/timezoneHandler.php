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
namespace kigkonsult\iCalcreator;
use DateTime;
use DateTimeZone;
use kigkonsult\iCalcreator\util\util;
/**
 * iCalcreator timezone management class
 *
 * Manages loosely coupled iCalcreator vcalendar (timezone) functions
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-04-07
 */
class timezoneHandler {
  private static $FMTTIMESTAMP = '@%s';
  private static $OFFSET       = 'offset';
  private static $TIME         = 'time';
/**
 * Create a calendar timezone and standard/daylight components
 *
 * Result when 'Europe/Stockholm' and no from/to arguments is used as timezone:
 * BEGIN:VTIMEZONE
 * TZID:Europe/Stockholm
 * BEGIN:STANDARD
 * DTSTART:20101031T020000
 * TZOFFSETFROM:+0200
 * TZOFFSETTO:+0100
 * TZNAME:CET
 * END:STANDARD
 * BEGIN:DAYLIGHT
 * DTSTART:20100328T030000
 * TZOFFSETFROM:+0100
 * TZOFFSETTO:+0200
 * TZNAME:CEST
 * END:DAYLIGHT
 * END:VTIMEZONE
 *
 * Generates components for all transitions in a date range,
 *   based on contribution by Yitzchok Lavi <icalcreator@onebigsystem.com>
 * Additional changes jpirkey
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-17
 * @param object $calendar  iCalcreator calendar instance
 * @param string $timezone  valid timezone avveptable by PHP5 DateTimeZone
 * @param array  $xProp     *[x-propName => x-propValue], optional
 * @param int    $from      unix timestamp
 * @param int    $to        unix timestamp
 * @return bool
 * @uses vcalendar::getProperty()
 * @uses vcalendar::newComponent()
 * @uses calendarComponent::setproperty()
 * @uses util::isXprefixed()
 * @uses timezoneHandler::offsetSec2His()
 * @static
 */
  public static function createTimezone( $calendar, $timezone, $xProp=array(), $from=null, $to=null ) {
    static $YMD          = 'Ymd';
    static $T000000      = 'T000000';
    static $MINUS7MONTH  = '-7 month';
    static $YMD2         = 'Y-m-d';
    static $T235959      = 'T235959';
    static $PLUS18MONTH  = '+18 month';
    static $TS           = 'ts';
    static $YMDHIS3      = 'Y-m-d-H-i-s';
    static $SECONDS      = 'seconds';
    static $ABBR         = 'abbr';
    static $ISDST        = 'isdst';
    static $NOW          = 'now';
    static $YMDTHISO     = 'Y-m-d\TH:i:s O';
    if( empty( $timezone ))
      return false;
    if( ! empty( $from ) && ! is_int( $from ))
      return false;
    if( ! empty( $to )   && ! is_int( $to ))
      return false;
    try {
      $dtz               = new DateTimeZone( $timezone );
      $transitions       = $dtz->getTransitions();
      $utcTz             = new DateTimeZone( util::$UTC );
    }
    catch( Exception $e ) {
      return false;
    }
    if( empty( $from ) || empty( $to )) {
      $dates             = array_keys( $calendar->getProperty( util::$DTSTART ));
      if( empty( $dates ))
        $dates           = array( date( $YMD ));
    }
    if( ! empty( $from )) {
      try {
        $timestamp       = sprintf( self::$FMTTIMESTAMP, $from );
        $dateFrom        = new DateTime( $timestamp );      // set lowest date (UTC)
      }
      catch( Exception $e ) {
        return false;
      }
    }
    else {
      try {
        $from            = reset( $dates );                 // set lowest date to the lowest dtstart date
        $dateFrom        = new DateTime( $from . $T000000, $dtz );
        $dateFrom->modify( $MINUS7MONTH );                  // set $dateFrom to seven month before the lowest date
        $dateFrom->setTimezone( $utcTz );                   // convert local date to UTC
      }
      catch( Exception $e ) {
        return false;
      }
    }
    $dateFromYmd         = $dateFrom->format( $YMD2 );
    if( ! empty( $to )) {
      try {
        $timestamp       = sprintf( self::$FMTTIMESTAMP, $to );
        $dateTo          = new DateTime( $timestamp );     // set end date (UTC)
      }
      catch( Exception $e ) {
        return false;
      }
    }
    else {
      try {
        $to              = end( $dates );                   // set highest date to the highest dtstart date
        $dateTo          = new DateTime( $to . $T235959, $dtz );
      }
      catch( Exception $e ) {
        return false;
      }
      $dateTo->modify( $PLUS18MONTH );                      // set $dateTo to 18 month after the highest date
      $dateTo->setTimezone( $utcTz );                       // convert local date to UTC
    }
    $dateToYmd           = $dateTo->format( $YMD2 );
    $transTemp           = array();
    $prevOffsetfrom      = 0;
    $stdIx  = $dlghtIx   = null;
    $prevTrans           = false;
    foreach( $transitions as $tix => $trans ) {             // all transitions in date-time order!!
      try {
        $timestamp       = sprintf( self::$FMTTIMESTAMP, $trans[$TS] );
        $date            = new DateTime( $timestamp );      // set transition date (UTC)
      }
      catch( Exception $e ) {
        return false;
      }
      $transDateYmd      = $date->format( $YMD2 );
      if ( $transDateYmd < $dateFromYmd ) {
        $prevOffsetfrom  = $trans[self::$OFFSET];           // previous trans offset will be 'next' trans offsetFrom
        $prevTrans       = $trans;                          // save it in case we don't find any that match
        $prevTrans[util::$TZOFFSETFROM] = ( 0 < $tix ) ? $transitions[$tix-1][self::$OFFSET] : 0;
        continue;
      }
      if( $transDateYmd > $dateToYmd )
        break;                                              // loop always (?) breaks here
      if( ! empty( $prevOffsetfrom ) || ( 0 == $prevOffsetfrom )) {
        $trans[util::$TZOFFSETFROM] = $prevOffsetfrom;      // i.e. set previous offsetto as offsetFrom
        $date->modify( $trans[util::$TZOFFSETFROM] . $SECONDS );    // convert utc date to local date
        $d               = explode( util::$MINUS, $date->format( $YMDHIS3 ));
        $trans[self::$TIME] = array( util::$LCYEAR  => (int) $d[0], // set date to array
                                     util::$LCMONTH => (int) $d[1], //  to ease up dtstart and (opt) rdate setting
                                     util::$LCDAY   => (int) $d[2],
                                     util::$LCHOUR  => (int) $d[3],
                                     util::$LCMIN   => (int) $d[4],
                                     util::$LCSEC   => (int) $d[5] );
      }
      $prevOffsetfrom    = $trans[self::$OFFSET];
      if( true !== $trans[$ISDST] ) {                       // standard timezone
        if( ! empty( $stdIx ) && isset( $transTemp[$stdIx][util::$TZOFFSETFROM] )     &&
           ( $transTemp[$stdIx][$ABBR]               == $trans[$ABBR] )               &&
           ( $transTemp[$stdIx][util::$TZOFFSETFROM] == $trans[util::$TZOFFSETFROM] ) &&
           ( $transTemp[$stdIx][self::$OFFSET]       == $trans[self::$OFFSET] )) {
          $transTemp[$stdIx][util::$RDATE][]          = $trans[self::$TIME]; // check for any repeating rdate's (in order)
          continue;
        }
        $stdIx           = $tix;
      } // end standard timezone
      else {                                                // daylight timezone
        if( ! empty( $dlghtIx ) && isset( $transTemp[$dlghtIx][util::$TZOFFSETFROM] )   &&
           ( $transTemp[$dlghtIx][$ABBR]               == $trans[$ABBR] )               &&
           ( $transTemp[$dlghtIx][util::$TZOFFSETFROM] == $trans[util::$TZOFFSETFROM] ) &&
           ( $transTemp[$dlghtIx][self::$OFFSET]       == $trans[self::$OFFSET] )) {
          $transTemp[$dlghtIx][util::$RDATE][]          = $trans[self::$TIME]; // check for any repeating rdate's (in order)
          continue;
        }
        $dlghtIx         = $tix;
      } // end daylight timezone
      $transTemp[$tix]   = $trans;
    } // end foreach( $transitions as $tix => $trans )
    $tz                  = $calendar->newComponent( util::$LCVTIMEZONE );
    $tz->setproperty( util::$TZID, $timezone );
    if( ! empty( $xProp )) {
      foreach( $xProp as $xPropName => $xPropValue )
        if( util::isXprefixed( $xPropName ))
          $tz->setproperty( $xPropName, $xPropValue );
    }
    if( empty( $transTemp )) {      // if no match found
      if( $prevTrans ) {            // then we use the last transition (before startdate) for the tz info
        try {
          $timestamp     = sprintf( self::$FMTTIMESTAMP, $prevTrans[$TS] );
          $date          = new DateTime( $timestamp );     // set transition date (UTC)
        }
        catch( Exception $e ) {
          return false;
        }
        $date->modify( $prevTrans[util::$TZOFFSETFROM] . $SECONDS );// convert utc date to local date
        $d               = explode( util::$MINUS, $date->format( $YMDHIS3 )); // set arr-date to ease up dtstart setting
        $prevTrans[self::$TIME] = array( util::$LCYEAR  => (int) $d[0],
                                         util::$LCMONTH => (int) $d[1],
                                         util::$LCDAY   => (int) $d[2],
                                         util::$LCHOUR  => (int) $d[3],
                                         util::$LCMIN   => (int) $d[4],
                                         util::$LCSEC   => (int) $d[5] );
        $transTemp[0] = $prevTrans;
      } // end if( $prevTrans )
      else {                        // or we use the timezone identifier to BUILD the standard tz info (?)
        try {
          $newTz         = new DateTimeZone( $timezone );
          $date          = new DateTime( $NOW, $newTz );
        }
        catch( Exception $e ) {
          return false;
        }
        $transTemp[0]    = array( $TIME       => $date->format( $YMDTHISO ),
                                  $OFFSET     => $date->format( util::$Z ),
                                  util::$TZOFFSETFROM => $date->format( util::$Z ),
                                  $ISDST      => false );
      }
    }
    foreach( $transTemp as $tix => $trans ) { // create standard/daylight subcomponents
      $type              = ( true !== $trans[$ISDST] ) ? util::$LCSTANDARD : util::$LCDAYLIGHT;
      $scomp             = $tz->newComponent( $type );
      $scomp->setProperty( util::$DTSTART,  $trans[self::$TIME] );
//      $scomp->setProperty( 'x-utc-timestamp', $tix.' : '.$trans[$TS] );   // test ###
      if( ! empty( $trans[$ABBR] ))
        $scomp->setProperty( util::$TZNAME, $trans[$ABBR] );
      if( isset( $trans[util::$TZOFFSETFROM] ))
        $scomp->setProperty( util::$TZOFFSETFROM, self::offsetSec2His( $trans[util::$TZOFFSETFROM] ));
      $scomp->setProperty( util::$TZOFFSETTO,     self::offsetSec2His( $trans[self::$OFFSET] ));
      if( isset( $trans[util::$RDATE] ))
        $scomp->setProperty( util::$RDATE,  $trans[util::$RDATE] );
    }
    return true;
  }
/**
 * Return iCal offset [-/+]hhmm[ss] (string) from UTC offset seconds
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-17
 * @param string $seconds
 * @return string
 * @static
 */
  public static function offsetSec2His( $seconds ) {
    static $FMT = '%02d';
    switch( substr( $seconds, 0, 1 )) {
      case util::$MINUS :
        $output = util::$MINUS;
        $seconds = substr( $seconds, 1 );
        break;
      case util::$PLUS :
        $output = util::$PLUS;
        $seconds = substr( $seconds, 1 );
        break;
      default :
        $output = util::$PLUS;
        break;
    }
    $output .= sprintf( $FMT, ((int) floor( $seconds / 3600 ))); // hour
    $seconds = $seconds % 3600;
    $output .= sprintf( $FMT, ((int) floor( $seconds / 60 )));   // min
    $seconds = $seconds % 60;
    if( 0 < $seconds )
      $output .= sprintf( $FMT, $seconds );                      // sec
    return $output;
  }
/**
 * Very simple conversion of a MS timezone to a PHP5 valid (Date-)timezone
 * matching (MS) UCT offset and time zone descriptors
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @param string $timezone     to convert
 * @return bool
 * @uses util::tz2offset()
 * @static
 */
  public static function ms2phpTZ( & $timezone ) {
    static $REPL1  = array( 'GMT', 'gmt', 'utc' );
    static $REPL2  = array( '(', ')', '&', ',', '  ' );
    static $PUTC   = '(UTC';
    static $ENDP   = ')';
    static $TIMEZONE_ID = 'timezone_id';
    if( empty( $timezone ))
      return false;
    $search = str_replace( util::$QQ, null, $timezone );
    $search = str_replace( $REPL1, util::$UTC, $search );
    if( $PUTC != substr( $search, 0, 4 ))
      return false;
    if( false === ( $pos = strpos( $search, $ENDP )))
      return false;
    $pos    = strpos( $search, $ENDP );
    $searchOffset = substr( $search, 4, ( $pos - 4 ));
    $searchOffset = util::tz2offset( str_replace( util::$COLON, null, $searchOffset ));
    while( util::$SP1 ==substr( $search, ( $pos + 1 )))
      $pos += 1;
    $searchText   = trim( str_replace( $REPL2, util::$SP1, substr( $search, ( $pos + 1 )) ));
    $searchWords  = explode( util::$SP1, $searchText );
    try {
      $timezoneAbbreviations = DateTimeZone::listAbbreviations();
    }
    catch( Exception $e ) {
      return false;
    }
    $hits = array();
    foreach( $timezoneAbbreviations as $name => $transitions ) {
      foreach( $transitions as $cnt => $transition ) {
        if( empty( $transition[self::$OFFSET] ) ||
            empty( $transition[$TIMEZONE_ID] )  ||
          ( $transition[self::$OFFSET] != $searchOffset ))
        continue;
        $cWords = explode( util::$L, $transition[$TIMEZONE_ID] );
        $cPrio   = $hitCnt = $rank = 0;
        foreach( $cWords as $cWord ) {
          if( empty( $cWord ))
            continue;
          $cPrio += 1;
          $sPrio  = 0;
          foreach( $searchWords as $sWord ) {
            if( empty( $sWord ) || ( self::$TIME == strtolower( $sWord )))
              continue;
            $sPrio += 1;
            if( strtolower( $cWord ) == strtolower( $sWord )) {
              $hitCnt += 1;
              $rank   += ( $cPrio + $sPrio );
            }
            else
              $rank += 10;
          }
        }
        if( 0 < $hitCnt ) {
          $hits[$rank][] = $transition[$TIMEZONE_ID];
        }
      } // end foreach( $transitions as $cnt => $transition )
    } // end foreach( $timezoneAbbreviations as $name => $transitions )
    if( empty( $hits ))
      return false;
    ksort( $hits );
    foreach( $hits as $rank => $tzs ) {
      if( ! empty( $tzs )) {
        $timezone = reset( $tzs );
        return true;
      }
    }
    return false;
  }
/**
 * Transforms a dateTime from a timezone to another
 * using PHP DateTime and DateTimeZone class (PHP >= PHP 5.2.0)
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-03-04
 * @param mixed  $date    date to alter
 * @param string $tzFrom  PHP valid 'from' timezone
 * @param string $tzTo    PHP valid 'to' timezone, default util::$UTC
 * @param string $format  date output format, default 'Ymd\THis'
 * @return bool true on success, false on error
 * @uses util::isArrayDate()
 * @uses util::date2strdate()
 * @uses util::chkDateArr()
 * @static
 */
  public static function transformDateTime( & $date, $tzFrom, $tzTo=null, $format=null ) {
    static $YMDTHIS = 'Ymd\THis';
    if( is_null( $tzTo ))
      $tzTo    = util::$UTC;
    elseif( util::$Z == $tzTo )
      $tzTo = util::$UTC;
    if( is_null( $format ))
      $format  = $YMDTHIS;
    if( is_array( $date ) && isset( $date[util::$LCTIMESTAMP] )) {
      try {
        $timestamp = sprintf( self::$FMTTIMESTAMP, $date[util::$LCTIMESTAMP] );
        $d     = new DateTime( $timestamp ); // set UTC date
        $newTz = new DateTimeZone( $tzFrom );
        $d->setTimezone( $newTz );           // convert to 'from' date
      }
      catch( Exception $e ) {
        return false;
      }
    }
    else {
      if( util::isArrayDate( $date )) {
        if( isset( $date[util::$LCtz] ))
          unset( $date[util::$LCtz] );
        $date  = util::date2strdate( util::chkDateArr( $date ));
      }
      if( util::$Z == substr( $date, -1 ))
        $date  = substr( $date, 0, ( strlen( $date ) - 2 ));
      try {
        $newTz = new DateTimeZone( $tzFrom );
        $d     = new DateTime( $date, $newTz );
      }
      catch( Exception $e ) {
        return false;
      }
    }
    try {
      $newTz   = new DateTimeZone( $tzTo );
      $d->setTimezone( $newTz );
    }
    catch( Exception $e ) {
      return false;
    }
    $date = $d->format( $format );
    return true;
  }
}
