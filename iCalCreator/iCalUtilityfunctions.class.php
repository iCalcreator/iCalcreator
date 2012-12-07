<?php
/**
 * moving all utility (static) functions to a utility class
 * 20111223 - move iCalUtilityFunctions class to the end of the iCalcreator class file
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.10.1 - 2011-07-16
 *
 */
class iCalUtilityFunctions {
  // Store the single instance of iCalUtilityFunctions
  private static $m_pInstance;

  // Private constructor to limit object instantiation to within the class
  private function __construct() {
    $m_pInstance = FALSE;
  }

  // Getter method for creating/returning the single instance of this class
  public static function getInstance() {
    if (!self::$m_pInstance)
      self::$m_pInstance = new iCalUtilityFunctions();

    return self::$m_pInstance;
  }
/**
 * ensures internal date-time/date format (keyed array) for an input date-time/date array (keyed or unkeyed)
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.14.1 - 2012-09-27
 * @param array $datetime
 * @param int $parno optional, default FALSE
 * @return array
 */
  public static function _date_time_array( $datetime, $parno=FALSE ) {
    return iCalUtilityFunctions::_chkDateArr( $datetime, $parno );
  }
  public static function _chkDateArr( $datetime, $parno=FALSE ) {
    $output = array();
    foreach( $datetime as $dateKey => $datePart ) {
      switch ( $dateKey ) {
        case '0': case 'year':   $output['year']  = $datePart; break;
        case '1': case 'month':  $output['month'] = $datePart; break;
        case '2': case 'day':    $output['day']   = $datePart; break;
      }
      if( 3 != $parno ) {
        switch ( $dateKey ) {
          case '0':
          case '1':
          case '2': break;
          case '3': case 'hour': $output['hour']  = $datePart; break;
          case '4': case 'min' : $output['min']   = $datePart; break;
          case '5': case 'sec' : $output['sec']   = $datePart; break;
          case '6': case 'tz'  : $output['tz']    = $datePart; break;
        }
      }
    }
    if( 3 != $parno ) {
      if( !isset( $output['hour'] ))         $output['hour'] = 0;
      if( !isset( $output['min']  ))         $output['min']  = 0;
      if( !isset( $output['sec']  ))         $output['sec']  = 0;
      if( isset( $output['tz'] ) &&
        (( '+0000' == $output['tz'] ) || ( '-0000' == $output['tz'] ) || ( '+000000' == $output['tz'] ) || ( '-000000' == $output['tz'] )))
                                             $output['tz']   = 'Z';
    }
    return $output;
  }
/**
 * check date(-time) and params arrays for an opt. timezone and if it is a DATE-TIME or DATE (updates $parno and params)
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.10.30 - 2012-01-16
 * @param array $date, date to check
 * @param int $parno, no of date parts (i.e. year, month.. .)
 * @param array $params, property parameters
 * @return void
 */
  public static function _chkdatecfg( $theDate, & $parno, & $params ) {
    if( isset( $params['TZID'] ))
      $parno = 6;
    elseif( isset( $params['VALUE'] ) && ( 'DATE' == $params['VALUE'] ))
      $parno = 3;
    else {
      if( isset( $params['VALUE'] ) && ( 'PERIOD' == $params['VALUE'] ))
        $parno = 7;
      if( is_array( $theDate )) {
        if( isset( $theDate['timestamp'] ))
          $tzid = ( isset( $theDate['tz'] )) ? $theDate['tz'] : null;
        else
          $tzid = ( isset( $theDate['tz'] )) ? $theDate['tz'] : ( 7 == count( $theDate )) ? end( $theDate ) : null;
        if( !empty( $tzid )) {
          $parno = 7;
          if( !iCalUtilityFunctions::_isOffset( $tzid ))
            $params['TZID'] = $tzid; // save only timezone
        }
        elseif( !$parno && ( 3 == count( $theDate )) &&
          ( isset( $params['VALUE'] ) && ( 'DATE' == $params['VALUE'] )))
          $parno = 3;
        else
          $parno = 6;
      }
      else { // string
        $date = trim( $theDate );
        if( 'Z' == substr( $date, -1 ))
          $parno = 7; // UTC DATE-TIME
        elseif((( 8 == strlen( $date ) && ctype_digit( $date )) || ( 11 >= strlen( $date ))) &&
          ( !isset( $params['VALUE'] ) || !in_array( $params['VALUE'], array( 'DATE-TIME', 'PERIOD' ))))
          $parno = 3; // DATE
        $date = iCalUtilityFunctions::_strdate2date( $date, $parno );
        unset( $date['unparsedtext'] );
        if( !empty( $date['tz'] )) {
          $parno = 7;
          if( !iCalUtilityFunctions::_isOffset( $date['tz'] ))
            $params['TZID'] = $date['tz']; // save only timezone
        }
        elseif( empty( $parno ))
          $parno = 6;
      }
      if( isset( $params['TZID'] ))
        $parno = 6;
    }
  }
/**
 * byte oriented line folding fix
 *
 * remove any line-endings that may include spaces or tabs
 * and convert all line endings (iCal default '\r\n'),
 * takes care of '\r\n', '\r' and '\n' and mixed '\r\n'+'\r', '\r\n'+'\n'
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.12.17 - 2012-07-12
 * @param string $text
 * @param string $nl
 * @return string
 */
  public static function convEolChar( & $text, $nl ) {
    $outp = '';
    $cix  = 0;
    while(    isset(   $text[$cix] )) {
      if(     isset(   $text[$cix + 2] ) &&  ( "\r" == $text[$cix] ) && ( "\n" == $text[$cix + 1] ) &&
        ((    " " ==   $text[$cix + 2] ) ||  ( "\t" == $text[$cix + 2] )))                    // 2 pos eolchar + ' ' or '\t'
        $cix  += 2;                                                                           // skip 3
      elseif( isset(   $text[$cix + 1] ) &&  ( "\r" == $text[$cix] ) && ( "\n" == $text[$cix + 1] )) {
        $outp .= $nl;                                                                         // 2 pos eolchar
        $cix  += 1;                                                                           // replace with $nl
      }
      elseif( isset(   $text[$cix + 1] ) && (( "\r" == $text[$cix] ) || ( "\n" == $text[$cix] )) &&
           (( " " ==   $text[$cix + 1] ) ||  ( "\t" == $text[$cix + 1] )))                     // 1 pos eolchar + ' ' or '\t'
        $cix  += 1;                                                                            // skip 2
      elseif(( "\r" == $text[$cix] )     ||  ( "\n" == $text[$cix] ))                          // 1 pos eolchar
        $outp .= $nl;                                                                          // replace with $nl
      else
        $outp .= $text[$cix];                                                                  // add any other byte
      $cix    += 1;
    }
    return $outp;
  }
/**
 * create a calendar timezone and standard/daylight components
 *
 * Result when 'Europe/Stockholm' and no from/to arguments is used as timezone:
 *
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
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.15.1 - 2012-10-22
 * Generates components for all transitions in a date range, based on contribution by Yitzchok Lavi <icalcreator@onebigsystem.com>
 * Additional changes jpirkey
 * @param object $calendar, reference to an iCalcreator calendar instance
 * @param string $timezone, a PHP5 (DateTimeZone) valid timezone
 * @param array  $xProp,    *[x-propName => x-propValue], optional
 * @param int    $from      a unix timestamp
 * @param int    $to        a unix timestamp
 * @return bool
 */
   public static function createTimezone( & $calendar, $timezone, $xProp=array(), $from=null, $to=null ) {
    if( empty( $timezone ))
      return FALSE;
    if( !empty( $from ) && !is_int( $from ))
      return FALSE;
    if( !empty( $to )   && !is_int( $to ))
      return FALSE;
    try {
      $dtz               = new DateTimeZone( $timezone );
      $transitions       = $dtz->getTransitions();
      $utcTz             = new DateTimeZone( 'UTC' );
    }
    catch( Exception $e ) { return FALSE; }
    if( empty( $to )) {
      $dates             = array_keys( $calendar->getProperty( 'dtstart' ));
      if( empty( $dates ))
        $dates           = array( date( 'Ymd' ));
    }
    if( !empty( $from ))
      $dateFrom          = new DateTime( "@$from" );             // set lowest date (UTC)
    else {
      $from              = reset( $dates );                      // set lowest date to the lowest dtstart date
      $dateFrom          = new DateTime( $from.'T000000', $dtz );
      $dateFrom->modify( '-1 month' );                           // set $dateFrom to one month before the lowest date
      $dateFrom->setTimezone( $utcTz );                          // convert local date to UTC
    }
    $dateFromYmd         = $dateFrom->format('Y-m-d' );
    if( !empty( $to ))
      $dateTo            = new DateTime( "@$to" );               // set end date (UTC)
    else {
      $to                = end( $dates );                        // set highest date to the highest dtstart date
      $dateTo            = new DateTime( $to.'T235959', $dtz );
      $dateTo->modify( '+1 year' );                              // set $dateTo to one year after the highest date
      $dateTo->setTimezone( $utcTz );                            // convert local date to UTC
    }
    $dateToYmd           = $dateTo->format('Y-m-d' );
    unset( $dtz );
    $transTemp           = array();
    $prevOffsetfrom      = 0;
    $stdIx  = $dlghtIx   = null;
    $prevTrans           = FALSE;
    foreach( $transitions as $tix => $trans ) {                  // all transitions in date-time order!!
      $date              = new DateTime( "@{$trans['ts']}" );    // set transition date (UTC)
      $transDateYmd      = $date->format('Y-m-d' );
      if ( $transDateYmd < $dateFromYmd ) {
        $prevOffsetfrom  = $trans['offset'];                     // previous trans offset will be 'next' trans offsetFrom
        $prevTrans       = $trans;                               // save it in case we don't find any that match
        $prevTrans['offsetfrom'] = ( 0 < $tix ) ? $transitions[$tix-1]['offset'] : 0;
        continue;
      }
      if( $transDateYmd > $dateToYmd )
        break;                                                   // loop always (?) breaks here
      if( !empty( $prevOffsetfrom ) || ( 0 == $prevOffsetfrom )) {
        $trans['offsetfrom'] = $prevOffsetfrom;                  // i.e. set previous offsetto as offsetFrom
        $date->modify( $trans['offsetfrom'].'seconds' );         // convert utc date to local date
        $d = $date->format( 'Y-n-j-G-i-s' );                     // set date to array to ease up dtstart and (opt) rdate setting
        $d = explode( '-', $d );
        $trans['time']   = array( 'year' => $d[0], 'month' => $d[1], 'day' => $d[2], 'hour' => $d[3], 'min' => $d[4], 'sec' => $d[5] );
      }
      $prevOffsetfrom    = $trans['offset'];
      if( TRUE !== $trans['isdst'] ) {                           // standard timezone
        if( !empty( $stdIx ) && isset( $transTemp[$stdIx]['offsetfrom'] )  && // check for any repeating rdate's (in order)
           ( $transTemp[$stdIx]['abbr']       ==   $trans['abbr'] )        &&
           ( $transTemp[$stdIx]['offsetfrom'] ==   $trans['offsetfrom'] )  &&
           ( $transTemp[$stdIx]['offset']     ==   $trans['offset'] )) {
          $transTemp[$stdIx]['rdate'][]        =   $trans['time'];
          continue;
        }
        $stdIx           = $tix;
      } // end standard timezone
      else {                                                     // daylight timezone
        if( !empty( $dlghtIx ) && isset( $transTemp[$dlghtIx]['offsetfrom'] ) && // check for any repeating rdate's (in order)
           ( $transTemp[$dlghtIx]['abbr']       ==   $trans['abbr'] )         &&
           ( $transTemp[$dlghtIx]['offsetfrom'] ==   $trans['offsetfrom'] )   &&
           ( $transTemp[$dlghtIx]['offset']     ==   $trans['offset'] )) {
          $transTemp[$dlghtIx]['rdate'][]        =   $trans['time'];
          continue;
        }
        $dlghtIx         = $tix;
      } // end daylight timezone
      $transTemp[$tix]   = $trans;
    } // end foreach( $transitions as $tix => $trans )
    $tz  = & $calendar->newComponent( 'vtimezone' );
    $tz->setproperty( 'tzid', $timezone );
    if( !empty( $xProp )) {
      foreach( $xProp as $xPropName => $xPropValue )
        if( 'x-' == strtolower( substr( $xPropName, 0, 2 )))
          $tz->setproperty( $xPropName, $xPropValue );
    }
    if( empty( $transTemp )) {      // if no match found
      if( $prevTrans ) {            // then we use the last transition (before startdate) for the tz info
        $date = new DateTime( "@{$prevTrans['ts']}" );           // set transition date (UTC)
        $date->modify( $prevTrans['offsetfrom'].'seconds' );     // convert utc date to local date
        $d = $date->format( 'Y-n-j-G-i-s' );                     // set date to array to ease up dtstart setting
        $d = explode( '-', $d );
        $prevTrans['time'] = array( 'year' => $d[0], 'month' => $d[1], 'day' => $d[2], 'hour' => $d[3], 'min' => $d[4], 'sec' => $d[5] );
        $transTemp[0] = $prevTrans;
      }
      else {                        // or we use the timezone identifier to BUILD the standard tz info (?)
        $date = new DateTime( 'now', new DateTimeZone( $timezone ));
        $transTemp[0] = array( 'time'       => $date->format( 'Y-m-d\TH:i:s O' )
                             , 'offset'     => $date->format( 'Z' )
                             , 'offsetfrom' => $date->format( 'Z' )
                             , 'isdst'      => FALSE );
      }
    }
    unset( $transitions, $date, $prevTrans );
    foreach( $transTemp as $tix => $trans ) {
      $type  = ( TRUE !== $trans['isdst'] ) ? 'standard' : 'daylight';
      $scomp = & $tz->newComponent( $type );
      $scomp->setProperty( 'dtstart',         $trans['time'] );
//      $scomp->setProperty( 'x-utc-timestamp', $tix.' : '.$trans['ts'] );   // test ###
      if( !empty( $trans['abbr'] ))
        $scomp->setProperty( 'tzname',        $trans['abbr'] );
      if( isset( $trans['offsetfrom'] ))
        $scomp->setProperty( 'tzoffsetfrom',  iCalUtilityFunctions::offsetSec2His( $trans['offsetfrom'] ));
      $scomp->setProperty( 'tzoffsetto',      iCalUtilityFunctions::offsetSec2His( $trans['offset'] ));
      if( isset( $trans['rdate'] ))
        $scomp->setProperty( 'RDATE',         $trans['rdate'] );
    }
    return TRUE;
  }
/**
 * creates formatted output for calendar component property data value type date/date-time
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.14.1 - 2012-09-17
 * @param array   $datetime
 * @param int     $parno, optional, default 6
 * @return string
 */
  public static function _format_date_time( $datetime, $parno=6 ) {
    return iCalUtilityFunctions::_date2strdate( $datetime, $parno );
  }
  public static function _date2strdate( $datetime, $parno=6 ) {
    if( !isset( $datetime['year'] )  &&
        !isset( $datetime['month'] ) &&
        !isset( $datetime['day'] )   &&
        !isset( $datetime['hour'] )  &&
        !isset( $datetime['min'] )   &&
        !isset( $datetime['sec'] ))
      return;
    $output     = null;
    foreach( $datetime as $dkey => & $dvalue )
      if( 'tz' != $dkey ) $dvalue = (integer) $dvalue;
    $output = sprintf( '%04d%02d%02d', $datetime['year'], $datetime['month'], $datetime['day'] );
    if( 3 == $parno )
      return $output;
    if( !isset( $datetime['hour'] )) $datetime['hour'] = 0;
    if( !isset( $datetime['min'] ))  $datetime['min']  = 0;
    if( !isset( $datetime['sec'] ))  $datetime['sec']  = 0;
    $output    .= sprintf( 'T%02d%02d%02d', $datetime['hour'], $datetime['min'], $datetime['sec'] );
    if( isset( $datetime['tz'] ) && ( '' < trim( $datetime['tz'] ))) {
      $datetime['tz'] = trim( $datetime['tz'] );
      if( 'Z'  == $datetime['tz'] )
        $parno  = 7;
      elseif( iCalUtilityFunctions::_isOffset( $datetime['tz'] )) {
        $parno  = 7;
        $offset = iCalUtilityFunctions::_tz2offset( $datetime['tz'] );
        try {
          $d    = new DateTime( $output, new DateTimeZone( 'UTC' ));
          if( 0 != $offset ) // adjust för offset
            $d->modify( "$offset seconds" );
          $output = $d->format( 'Ymd\THis' );
        }
        catch( Exception $e ) {
          $output = date( 'Ymd\THis', mktime( $datetime['hour'], $datetime['min'], ($datetime['sec'] - $offset), $datetime['month'], $datetime['day'], $datetime['year'] ));
        }
      }
      if( 7 == $parno )
        $output .= 'Z';
    }
    return $output;
  }
/**
 * convert a date/datetime (array) to timestamp
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.14.1 - 2012-09-29
 * @param array  $datetime  datetime(/date)
 * @param string $wtz       timezone
 * @return int
 */
  public static function _date2timestamp( $datetime, $wtz=null ) {
    if( !isset( $datetime['hour'] )) $datetime['hour'] = 0;
    if( !isset( $datetime['min'] ))  $datetime['min']  = 0;
    if( !isset( $datetime['sec'] ))  $datetime['sec']  = 0;
    if( empty( $wtz ) && ( !isset( $datetime['tz'] ) || empty(  $datetime['tz'] )))
      return mktime( $datetime['hour'], $datetime['min'], $datetime['sec'], $datetime['month'], $datetime['day'], $datetime['year'] );
    $output = $offset = 0;
    if( empty( $wtz )) {
      if( iCalUtilityFunctions::_isOffset( $datetime['tz'] )) {
        $offset = iCalUtilityFunctions::_tz2offset( $datetime['tz'] ) * -1;
        $wtz    = 'UTC';
      }
      else
        $wtz    = $datetime['tz'];
    }
    if(( 'Z' == $wtz ) || ( 'GMT' == strtoupper( $wtz )))
      $wtz      = 'UTC';
    try {
      $strdate  = sprintf( '%04d-%02d-%02d %02d:%02d:%02d', $datetime['year'], $datetime['month'], $datetime['day'], $datetime['hour'], $datetime['min'], $datetime['sec'] );
      $d        = new DateTime( $strdate, new DateTimeZone( $wtz ));
      if( 0    != $offset )  // adjust for offset
        $d->modify( $offset.' seconds' );
      $output   = $d->format( 'U' );
      unset( $d );
    }
    catch( Exception $e ) {
      $output = mktime( $datetime['hour'], $datetime['min'], $datetime['sec'], $datetime['month'], $datetime['day'], $datetime['year'] );
    }
    return $output;
  }
/**
 * ensures internal duration format for input in array format
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.14.1 - 2012-09-25
 * @param array $duration
 * @return array
 */
  public static function _duration_array( $duration ) {
    return iCalUtilityFunctions::_duration2arr( $duration );
  }
  public static function _duration2arr( $duration ) {
    $output = array();
    if(    is_array( $duration )        &&
       ( 1 == count( $duration ))       &&
              isset( $duration['sec'] ) &&
              ( 60 < $duration['sec'] )) {
      $durseconds  = $duration['sec'];
      $output['week'] = (int) floor( $durseconds / ( 60 * 60 * 24 * 7 ));
      $durseconds     =              $durseconds % ( 60 * 60 * 24 * 7 );
      $output['day']  = (int) floor( $durseconds / ( 60 * 60 * 24 ));
      $durseconds     =              $durseconds % ( 60 * 60 * 24 );
      $output['hour'] = (int) floor( $durseconds / ( 60 * 60 ));
      $durseconds     =              $durseconds % ( 60 * 60 );
      $output['min']  = (int) floor( $durseconds / ( 60 ));
      $output['sec']  =            ( $durseconds % ( 60 ));
    }
    else {
      foreach( $duration as $durKey => $durValue ) {
        if( empty( $durValue )) continue;
        switch ( $durKey ) {
          case '0': case 'week': $output['week']  = $durValue; break;
          case '1': case 'day':  $output['day']   = $durValue; break;
          case '2': case 'hour': $output['hour']  = $durValue; break;
          case '3': case 'min':  $output['min']   = $durValue; break;
          case '4': case 'sec':  $output['sec']   = $durValue; break;
        }
      }
    }
    if( isset( $output['week'] ) && ( 0 < $output['week'] )) {
      unset( $output['day'], $output['hour'], $output['min'], $output['sec'] );
      return $output;
    }
    unset( $output['week'] );
    if( empty( $output['day'] ))
      unset( $output['day'] );
    if ( isset( $output['hour'] ) || isset( $output['min'] ) || isset( $output['sec'] )) {
      if( !isset( $output['hour'] )) $output['hour'] = 0;
      if( !isset( $output['min']  )) $output['min']  = 0;
      if( !isset( $output['sec']  )) $output['sec']  = 0;
      if(( 0 == $output['hour'] ) && ( 0 == $output['min'] ) && ( 0 == $output['sec'] ))
        unset( $output['hour'], $output['min'], $output['sec'] );
    }
    return $output;
  }
/**
 * convert startdate+duration to a array format datetime
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.15.12 - 2012-10-31
 * @param array   $startdate
 * @param array   $duration
 * @return array, date format
 */
  public static function _duration2date( $startdate, $duration ) {
    $dateOnly          = ( isset( $startdate['hour'] ) || isset( $startdate['min'] ) || isset( $startdate['sec'] )) ? FALSE : TRUE;
    $startdate['hour'] = ( isset( $startdate['hour'] )) ? $startdate['hour'] : 0;
    $startdate['min']  = ( isset( $startdate['min'] ))  ? $startdate['min']  : 0;
    $startdate['sec']  = ( isset( $startdate['sec'] ))  ? $startdate['sec']  : 0;
    $dtend = 0;
    if(    isset( $duration['week'] )) $dtend += ( $duration['week'] * 7 * 24 * 60 * 60 );
    if(    isset( $duration['day'] ))  $dtend += ( $duration['day'] * 24 * 60 * 60 );
    if(    isset( $duration['hour'] )) $dtend += ( $duration['hour'] * 60 *60 );
    if(    isset( $duration['min'] ))  $dtend += ( $duration['min'] * 60 );
    if(    isset( $duration['sec'] ))  $dtend +=   $duration['sec'];
    $date     = date( 'Y-m-d-H-i-s', mktime((int) $startdate['hour'], (int) $startdate['min'], (int) ( $startdate['sec'] + $dtend ), (int) $startdate['month'], (int) $startdate['day'], (int) $startdate['year'] ));
    $d        = explode( '-', $date );
    $dtend2   = array( 'year' => $d[0], 'month' => $d[1], 'day' => $d[2], 'hour' => $d[3], 'min' => $d[4], 'sec' => $d[5] );
    if( isset( $startdate['tz'] ))
      $dtend2['tz']   = $startdate['tz'];
    if( $dateOnly && (( 0 == $dtend2['hour'] ) && ( 0 == $dtend2['min'] ) && ( 0 == $dtend2['sec'] )))
      unset( $dtend2['hour'], $dtend2['min'], $dtend2['sec'] );
    return $dtend2;
  }
/**
 * ensures internal duration format for an input string (iCal) formatted duration
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.14.1 - 2012-09-25
 * @param string $duration
 * @return array
 */
  public static function _duration_string( $duration ) {
    return iCalUtilityFunctions::_durationStr2arr( $duration );
  }
  public static function _durationStr2arr( $duration ) {
    $duration = (string) trim( $duration );
    while( 'P' != strtoupper( substr( $duration, 0, 1 ))) {
      if( 0 < strlen( $duration ))
        $duration = substr( $duration, 1 );
      else
        return false; // no leading P !?!?
    }
    $duration = substr( $duration, 1 ); // skip P
    $duration = str_replace ( 't', 'T', $duration );
    $duration = str_replace ( 'T', '', $duration );
    $output = array();
    $val    = null;
    for( $ix=0; $ix < strlen( $duration ); $ix++ ) {
      switch( strtoupper( substr( $duration, $ix, 1 ))) {
       case 'W':
         $output['week'] = $val;
         $val            = null;
         break;
       case 'D':
         $output['day']  = $val;
         $val            = null;
         break;
       case 'H':
         $output['hour'] = $val;
         $val            = null;
         break;
       case 'M':
         $output['min']  = $val;
         $val            = null;
         break;
       case 'S':
         $output['sec']  = $val;
         $val            = null;
         break;
       default:
         if( !ctype_digit( substr( $duration, $ix, 1 )))
           return false; // unknown duration control character  !?!?
         else
           $val .= substr( $duration, $ix, 1 );
      }
    }
    return iCalUtilityFunctions::_duration2arr( $output );
  }
/**
 * creates formatted output for calendar component property data value type duration
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.15.8 - 2012-10-30
 * @param array $duration, array( week, day, hour, min, sec )
 * @return string
 */
  public static function _format_duration( $duration ) {
    return iCalUtilityFunctions::_duration2str( $duration );
  }
  public static function _duration2str( $duration ) {
    if( isset( $duration['week'] ) ||
        isset( $duration['day'] )  ||
        isset( $duration['hour'] ) ||
        isset( $duration['min'] )  ||
        isset( $duration['sec'] ))
       $ok = TRUE;
    else
      return;
    if( isset( $duration['week'] ) && ( 0 < $duration['week'] ))
      return 'P'.$duration['week'].'W';
    $output = 'P';
    if( isset($duration['day'] ) && ( 0 < $duration['day'] ))
      $output .= $duration['day'].'D';
    if(( isset( $duration['hour']) && ( 0 < $duration['hour'] )) ||
       ( isset( $duration['min'])  && ( 0 < $duration['min'] ))  ||
       ( isset( $duration['sec'])  && ( 0 < $duration['sec'] ))) {
      $output .= 'T';
      $output .= ( isset( $duration['hour']) && ( 0 < $duration['hour'] )) ? $duration['hour'].'H' : '0H';
      $output .= ( isset( $duration['min'])  && ( 0 < $duration['min'] ))  ? $duration['min']. 'M' : '0M';
      $output .= ( isset( $duration['sec'])  && ( 0 < $duration['sec'] ))  ? $duration['sec']. 'S' : '0S';
    }
    if( 'P' == $output )
      $output = 'PT0H0M0S';
    return $output;
  }
/**
 * removes expkey+expvalue from array and returns hitval (if found) else returns elseval
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.4.16 - 2008-11-08
 * @param array $array
 * @param string $expkey, expected key
 * @param string $expval, expected value
 * @param int $hitVal optional, return value if found
 * @param int $elseVal optional, return value if not found
 * @param int $preSet optional, return value if already preset
 * @return int
 */
  public static function _existRem( &$array, $expkey, $expval=FALSE, $hitVal=null, $elseVal=null, $preSet=null ) {
    if( $preSet )
      return $preSet;
    if( !is_array( $array ) || ( 0 == count( $array )))
      return $elseVal;
    foreach( $array as $key => $value ) {
      if( strtoupper( $expkey ) == strtoupper( $key )) {
        if( !$expval || ( strtoupper( $expval ) == strtoupper( $array[$key] ))) {
          unset( $array[$key] );
          return $hitVal;
        }
      }
    }
    return $elseVal;
  }
/**
 * checks if input contains a (array formatted) date/time
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.11.8 - 2012-01-20
 * @param array $input
 * @return bool
 */
  public static function _isArrayDate( $input ) {
    if( !is_array( $input ))
      return FALSE;
    if( isset( $input['week'] ) || ( !in_array( count( $input ), array( 3, 6, 7 ))))
      return FALSE;
    if( 7 == count( $input ))
      return TRUE;
    if( isset( $input['year'] ) && isset( $input['month'] ) && isset( $input['day'] ))
      return checkdate( (int) $input['month'], (int) $input['day'], (int) $input['year'] );
    if( isset( $input['day'] ) || isset( $input['hour'] ) || isset( $input['min'] ) || isset( $input['sec'] ))
      return FALSE;
    if( in_array( 0, $input ))
      return FALSE;
    if(( 1970 > $input[0] ) || ( 12 < $input[1] ) || ( 31 < $input[2] ))
      return FALSE;
    if(( isset( $input[0] ) && isset( $input[1] ) && isset( $input[2] )) &&
         checkdate( (int) $input[1], (int) $input[2], (int) $input[0] ))
      return TRUE;
    $input = iCalUtilityFunctions::_strdate2date( $input[1].'/'.$input[2].'/'.$input[0], 3 ); //  m - d - Y
    if( isset( $input['year'] ) && isset( $input['month'] ) && isset( $input['day'] ))
      return checkdate( (int) $input['month'], (int) $input['day'], (int) $input['year'] );
    return FALSE;
  }
/**
 * checks if input array contains a timestamp date
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.4.16 - 2008-10-18
 * @param array $input
 * @return bool
 */
  public static function _isArrayTimestampDate( $input ) {
    return ( is_array( $input ) && isset( $input['timestamp'] )) ? TRUE : FALSE ;
  }
/**
 * controls if input string contains (trailing) UTC/iCal offset
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.14.1 - 2012-09-21
 * @param string $input
 * @return bool
 */
  public static function _isOffset( $input ) {
    $input         = trim( (string) $input );
    if( 'Z' == substr( $input, -1 ))
      return TRUE;
    elseif((   5 <= strlen( $input )) &&
       ( in_array( substr( $input, -5, 1 ), array( '+', '-' ))) &&
       (   '0000' <= substr( $input, -4 )) && (   '9999' >= substr( $input, -4 )))
      return TRUE;
    elseif((    7 <= strlen( $input )) &&
       ( in_array( substr( $input, -7, 1 ), array( '+', '-' ))) &&
       ( '000000' <= substr( $input, -6 )) && ( '999999' >= substr( $input, -6 )))
      return TRUE;
    return FALSE;
  }
/**
 * (very simple) conversion of a MS timezone to a PHP5 valid (Date-)timezone
 * matching (MS) UCT offset and time zone descriptors
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.14.1 - 2012-09-16
 * @param string $timezone, input/output variable reference
 * @return bool
 */
  public static function ms2phpTZ( & $timezone ) {
    if( empty( $timezone ))
      return FALSE;
    $search = str_replace( '"', '', $timezone );
    $search = str_replace( array('GMT', 'gmt', 'utc' ), 'UTC', $search );
    if( '(UTC' != substr( $search, 0, 4 ))
      return FALSE;
    if( FALSE === ( $pos = strpos( $search, ')' )))
      return FALSE;
    $pos    = strpos( $search, ')' );
    $searchOffset = substr( $search, 4, ( $pos - 4 ));
    $searchOffset = iCalUtilityFunctions::_tz2offset( str_replace( ':', '', $searchOffset ));
    while( ' ' ==substr( $search, ( $pos + 1 )))
      $pos += 1;
    $searchText   = trim( str_replace( array( '(', ')', '&', ',', '  ' ), ' ', substr( $search, ( $pos + 1 )) ));
    $searchWords  = explode( ' ', $searchText );
    $timezone_abbreviations = DateTimeZone::listAbbreviations();
    $hits = array();
    foreach( $timezone_abbreviations as $name => $transitions ) {
      foreach( $transitions as $cnt => $transition ) {
        if( empty( $transition['offset'] )      ||
            empty( $transition['timezone_id'] ) ||
          ( $transition['offset'] != $searchOffset ))
        continue;
        $cWords = explode( '/', $transition['timezone_id'] );
        $cPrio   = $hitCnt = $rank = 0;
        foreach( $cWords as $cWord ) {
          if( empty( $cWord ))
            continue;
          $cPrio += 1;
          $sPrio  = 0;
          foreach( $searchWords as $sWord ) {
            if( empty( $sWord ) || ( 'time' == strtolower( $sWord )))
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
          $hits[$rank][] = $transition['timezone_id'];
        }
      }
    }
    unset( $timezone_abbreviations );
    if( empty( $hits ))
      return FALSE;
    ksort( $hits );
    foreach( $hits as $rank => $tzs ) {
      if( !empty( $tzs )) {
        $timezone = reset( $tzs );
        return TRUE;
      }
    }
    return FALSE;
  }
/**
 * transforms offset in seconds to [-/+]hhmm[ss]
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2011-05-02
 * @param string $seconds
 * @return string
 */
  public static function offsetSec2His( $seconds ) {
    if( '-' == substr( $seconds, 0, 1 )) {
      $prefix  = '-';
      $seconds = substr( $seconds, 1 );
    }
    elseif( '+' == substr( $seconds, 0, 1 )) {
      $prefix  = '+';
      $seconds = substr( $seconds, 1 );
    }
    else
      $prefix  = '+';
    $output  = '';
    $hour    = (int) floor( $seconds / 3600 );
    if( 10 > $hour )
      $hour  = '0'.$hour;
    $seconds = $seconds % 3600;
    $min     = (int) floor( $seconds / 60 );
    if( 10 > $min )
      $min   = '0'.$min;
    $output  = $hour.$min;
    $seconds = $seconds % 60;
    if( 0 < $seconds) {
      if( 9 < $seconds)
        $output .= $seconds;
      else
        $output .= '0'.$seconds;
    }
    return $prefix.$output;
  }
/**
 * updates an array with dates based on a recur pattern
 *
 * if missing, UNTIL is set 1 year from startdate (emergency break)
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.10.19 - 2011-10-31
 * @param array $result, array to update, array([timestamp] => timestamp)
 * @param array $recur, pattern for recurrency (only value part, params ignored)
 * @param array $wdate, component start date
 * @param array $startdate, start date
 * @param array $enddate, optional
 * @return void
 * @todo BYHOUR, BYMINUTE, BYSECOND, WEEKLY at year end/start
 */
  public static function _recur2date( & $result, $recur, $wdate, $startdate, $enddate=FALSE ) {
    foreach( $wdate as $k => $v ) if( ctype_digit( $v )) $wdate[$k] = (int) $v;
    $wdateStart  = $wdate;
    $wdatets     = iCalUtilityFunctions::_date2timestamp( $wdate );
    $startdatets = iCalUtilityFunctions::_date2timestamp( $startdate );
    if( !$enddate ) {
      $enddate = $startdate;
      $enddate['year'] += 1;
    }
// echo "recur __in_ comp start ".implode('-',$wdate)." period start ".implode('-',$startdate)." period end ".implode('-',$enddate)."<br />\n";print_r($recur);echo "<br />\n";//test###
    $endDatets = iCalUtilityFunctions::_date2timestamp( $enddate ); // fix break
    if( !isset( $recur['COUNT'] ) && !isset( $recur['UNTIL'] ))
      $recur['UNTIL'] = $enddate; // create break
    if( isset( $recur['UNTIL'] )) {
      $tdatets = iCalUtilityFunctions::_date2timestamp( $recur['UNTIL'] );
      if( $endDatets > $tdatets ) {
        $endDatets = $tdatets; // emergency break
        $enddate   = iCalUtilityFunctions::_timestamp2date( $endDatets, 6 );
      }
      else
        $recur['UNTIL'] = iCalUtilityFunctions::_timestamp2date( $endDatets, 6 );
    }
    if( $wdatets > $endDatets ) {
// echo "recur out of date ".date('Y-m-d H:i:s',$wdatets)."<br />\n";//test
      return array(); // nothing to do.. .
    }
    if( !isset( $recur['FREQ'] )) // "MUST be specified.. ."
      $recur['FREQ'] = 'DAILY'; // ??
    $wkst = ( isset( $recur['WKST'] ) && ( 'SU' == $recur['WKST'] )) ? 24*60*60 : 0; // ??
    $weekStart = (int) date( 'W', ( $wdatets + $wkst ));
    if( !isset( $recur['INTERVAL'] ))
      $recur['INTERVAL'] = 1;
    $countcnt = ( !isset( $recur['BYSETPOS'] )) ? 1 : 0; // DTSTART counts as the first occurrence
            /* find out how to step up dates and set index for interval count */
    $step = array();
    if( 'YEARLY' == $recur['FREQ'] )
      $step['year']  = 1;
    elseif( 'MONTHLY' == $recur['FREQ'] )
      $step['month'] = 1;
    elseif( 'WEEKLY' == $recur['FREQ'] )
      $step['day']   = 7;
    else
      $step['day']   = 1;
    if( isset( $step['year'] ) && isset( $recur['BYMONTH'] ))
      $step = array( 'month' => 1 );
    if( empty( $step ) && isset( $recur['BYWEEKNO'] )) // ??
      $step = array( 'day' => 7 );
    if( isset( $recur['BYYEARDAY'] ) || isset( $recur['BYMONTHDAY'] ) || isset( $recur['BYDAY'] ))
      $step = array( 'day' => 1 );
    $intervalarr = array();
    if( 1 < $recur['INTERVAL'] ) {
      $intervalix = iCalUtilityFunctions::_recurIntervalIx( $recur['FREQ'], $wdate, $wkst );
      $intervalarr = array( $intervalix => 0 );
    }
    if( isset( $recur['BYSETPOS'] )) { // save start date + weekno
      $bysetposymd1 = $bysetposymd2 = $bysetposw1 = $bysetposw2 = array();
// echo "bysetposXold_start=$bysetposYold $bysetposMold $bysetposDold<br />\n"; // test ###
      if( is_array( $recur['BYSETPOS'] )) {
        foreach( $recur['BYSETPOS'] as $bix => $bval )
          $recur['BYSETPOS'][$bix] = (int) $bval;
      }
      else
        $recur['BYSETPOS'] = array( (int) $recur['BYSETPOS'] );
      if( 'YEARLY' == $recur['FREQ'] ) {
        $wdate['month'] = $wdate['day'] = 1; // start from beginning of year
        $wdatets        = iCalUtilityFunctions::_date2timestamp( $wdate );
        iCalUtilityFunctions::_stepdate( $enddate, $endDatets, array( 'year' => 1 )); // make sure to count whole last year
      }
      elseif( 'MONTHLY' == $recur['FREQ'] ) {
        $wdate['day']   = 1; // start from beginning of month
        $wdatets        = iCalUtilityFunctions::_date2timestamp( $wdate );
        iCalUtilityFunctions::_stepdate( $enddate, $endDatets, array( 'month' => 1 )); // make sure to count whole last month
      }
      else
        iCalUtilityFunctions::_stepdate( $enddate, $endDatets, $step); // make sure to count whole last period
// echo "BYSETPOS endDat++ =".implode('-',$enddate).' step='.var_export($step,TRUE)."<br />\n";//test###
      $bysetposWold = (int) date( 'W', ( $wdatets + $wkst ));
      $bysetposYold = $wdate['year'];
      $bysetposMold = $wdate['month'];
      $bysetposDold = $wdate['day'];
    }
    else
      iCalUtilityFunctions::_stepdate( $wdate, $wdatets, $step);
    $year_old     = null;
    $daynames     = array( 'SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA' );
             /* MAIN LOOP */
// echo "recur start ".implode('-',$wdate)." end ".implode('-',$enddate)."<br />\n";//test
    while( TRUE ) {
      if( isset( $endDatets ) && ( $wdatets > $endDatets ))
        break;
      if( isset( $recur['COUNT'] ) && ( $countcnt >= $recur['COUNT'] ))
        break;
      if( $year_old != $wdate['year'] ) {
        $year_old   = $wdate['year'];
        $daycnts    = array();
        $yeardays   = $weekno = 0;
        $yeardaycnt = array();
        foreach( $daynames as $dn )
          $yeardaycnt[$dn] = 0;
        for( $m = 1; $m <= 12; $m++ ) { // count up and update up-counters
          $daycnts[$m] = array();
          $weekdaycnt = array();
          foreach( $daynames as $dn )
            $weekdaycnt[$dn] = 0;
          $mcnt     = date( 't', mktime( 0, 0, 0, $m, 1, $wdate['year'] ));
          for( $d   = 1; $d <= $mcnt; $d++ ) {
            $daycnts[$m][$d] = array();
            if( isset( $recur['BYYEARDAY'] )) {
              $yeardays++;
              $daycnts[$m][$d]['yearcnt_up'] = $yeardays;
            }
            if( isset( $recur['BYDAY'] )) {
              $day    = date( 'w', mktime( 0, 0, 0, $m, $d, $wdate['year'] ));
              $day    = $daynames[$day];
              $daycnts[$m][$d]['DAY'] = $day;
              $weekdaycnt[$day]++;
              $daycnts[$m][$d]['monthdayno_up'] = $weekdaycnt[$day];
              $yeardaycnt[$day]++;
              $daycnts[$m][$d]['yeardayno_up'] = $yeardaycnt[$day];
            }
            if(  isset( $recur['BYWEEKNO'] ) || ( $recur['FREQ'] == 'WEEKLY' ))
              $daycnts[$m][$d]['weekno_up'] =(int)date('W',mktime(0,0,$wkst,$m,$d,$wdate['year']));
          }
        }
        $daycnt = 0;
        $yeardaycnt = array();
        if(  isset( $recur['BYWEEKNO'] ) || ( $recur['FREQ'] == 'WEEKLY' )) {
          $weekno = null;
          for( $d=31; $d > 25; $d-- ) { // get last weekno for year
            if( !$weekno )
              $weekno = $daycnts[12][$d]['weekno_up'];
            elseif( $weekno < $daycnts[12][$d]['weekno_up'] ) {
              $weekno = $daycnts[12][$d]['weekno_up'];
              break;
            }
          }
        }
        for( $m = 12; $m > 0; $m-- ) { // count down and update down-counters
          $weekdaycnt = array();
          foreach( $daynames as $dn )
            $yeardaycnt[$dn] = $weekdaycnt[$dn] = 0;
          $monthcnt = 0;
          $mcnt     = date( 't', mktime( 0, 0, 0, $m, 1, $wdate['year'] ));
          for( $d   = $mcnt; $d > 0; $d-- ) {
            if( isset( $recur['BYYEARDAY'] )) {
              $daycnt -= 1;
              $daycnts[$m][$d]['yearcnt_down'] = $daycnt;
            }
            if( isset( $recur['BYMONTHDAY'] )) {
              $monthcnt -= 1;
              $daycnts[$m][$d]['monthcnt_down'] = $monthcnt;
            }
            if( isset( $recur['BYDAY'] )) {
              $day  = $daycnts[$m][$d]['DAY'];
              $weekdaycnt[$day] -= 1;
              $daycnts[$m][$d]['monthdayno_down'] = $weekdaycnt[$day];
              $yeardaycnt[$day] -= 1;
              $daycnts[$m][$d]['yeardayno_down'] = $yeardaycnt[$day];
            }
            if(  isset( $recur['BYWEEKNO'] ) || ( $recur['FREQ'] == 'WEEKLY' ))
              $daycnts[$m][$d]['weekno_down'] = ($daycnts[$m][$d]['weekno_up'] - $weekno - 1);
          }
        }
      }
            /* check interval */
      if( 1 < $recur['INTERVAL'] ) {
            /* create interval index */
        $intervalix = iCalUtilityFunctions::_recurIntervalIx( $recur['FREQ'], $wdate, $wkst );
            /* check interval */
        $currentKey = array_keys( $intervalarr );
        $currentKey = end( $currentKey ); // get last index
        if( $currentKey != $intervalix )
          $intervalarr = array( $intervalix => ( $intervalarr[$currentKey] + 1 ));
        if(( $recur['INTERVAL'] != $intervalarr[$intervalix] ) &&
           ( 0 != $intervalarr[$intervalix] )) {
            /* step up date */
// echo "skip: ".implode('-',$wdate)." ix=$intervalix old=$currentKey interval=".$intervalarr[$intervalix]."<br />\n";//test
          iCalUtilityFunctions::_stepdate( $wdate, $wdatets, $step);
          continue;
        }
        else // continue within the selected interval
          $intervalarr[$intervalix] = 0;
// echo "cont: ".implode('-',$wdate)." ix=$intervalix old=$currentKey interval=".$intervalarr[$intervalix]."<br />\n";//test
      }
      $updateOK = TRUE;
      if( $updateOK && isset( $recur['BYMONTH'] ))
        $updateOK = iCalUtilityFunctions::_recurBYcntcheck( $recur['BYMONTH']
                                           , $wdate['month']
                                           ,($wdate['month'] - 13));
      if( $updateOK && isset( $recur['BYWEEKNO'] ))
        $updateOK = iCalUtilityFunctions::_recurBYcntcheck( $recur['BYWEEKNO']
                                           , $daycnts[$wdate['month']][$wdate['day']]['weekno_up']
                                           , $daycnts[$wdate['month']][$wdate['day']]['weekno_down'] );
      if( $updateOK && isset( $recur['BYYEARDAY'] ))
        $updateOK = iCalUtilityFunctions::_recurBYcntcheck( $recur['BYYEARDAY']
                                           , $daycnts[$wdate['month']][$wdate['day']]['yearcnt_up']
                                           , $daycnts[$wdate['month']][$wdate['day']]['yearcnt_down'] );
      if( $updateOK && isset( $recur['BYMONTHDAY'] ))
        $updateOK = iCalUtilityFunctions::_recurBYcntcheck( $recur['BYMONTHDAY']
                                           , $wdate['day']
                                           , $daycnts[$wdate['month']][$wdate['day']]['monthcnt_down'] );
// echo "efter BYMONTHDAY: ".implode('-',$wdate).' status: '; echo ($updateOK) ? 'TRUE' : 'FALSE'; echo "<br />\n";//test###
      if( $updateOK && isset( $recur['BYDAY'] )) {
        $updateOK = FALSE;
        $m = $wdate['month'];
        $d = $wdate['day'];
        if( isset( $recur['BYDAY']['DAY'] )) { // single day, opt with year/month day order no
          $daynoexists = $daynosw = $daynamesw =  FALSE;
          if( $recur['BYDAY']['DAY'] == $daycnts[$m][$d]['DAY'] )
            $daynamesw = TRUE;
          if( isset( $recur['BYDAY'][0] )) {
            $daynoexists = TRUE;
            if(( isset( $recur['FREQ'] ) && ( $recur['FREQ'] == 'MONTHLY' )) || isset( $recur['BYMONTH'] ))
              $daynosw = iCalUtilityFunctions::_recurBYcntcheck( $recur['BYDAY'][0]
                                                , $daycnts[$m][$d]['monthdayno_up']
                                                , $daycnts[$m][$d]['monthdayno_down'] );
            elseif( isset( $recur['FREQ'] ) && ( $recur['FREQ'] == 'YEARLY' ))
              $daynosw = iCalUtilityFunctions::_recurBYcntcheck( $recur['BYDAY'][0]
                                                , $daycnts[$m][$d]['yeardayno_up']
                                                , $daycnts[$m][$d]['yeardayno_down'] );
          }
          if((  $daynoexists &&  $daynosw && $daynamesw ) ||
             ( !$daynoexists && !$daynosw && $daynamesw )) {
            $updateOK = TRUE;
// echo "m=$m d=$d day=".$daycnts[$m][$d]['DAY']." yeardayno_up=".$daycnts[$m][$d]['yeardayno_up']." daynoexists:$daynoexists daynosw:$daynosw daynamesw:$daynamesw updateOK:$updateOK<br />\n"; // test ###
          }
//echo "m=$m d=$d day=".$daycnts[$m][$d]['DAY']." yeardayno_up=".$daycnts[$m][$d]['yeardayno_up']." daynoexists:$daynoexists daynosw:$daynosw daynamesw:$daynamesw updateOK:$updateOK<br />\n"; // test ###
        }
        else {
          foreach( $recur['BYDAY'] as $bydayvalue ) {
            $daynoexists = $daynosw = $daynamesw = FALSE;
            if( isset( $bydayvalue['DAY'] ) &&
                     ( $bydayvalue['DAY'] == $daycnts[$m][$d]['DAY'] ))
              $daynamesw = TRUE;
            if( isset( $bydayvalue[0] )) {
              $daynoexists = TRUE;
              if(( isset( $recur['FREQ'] ) && ( $recur['FREQ'] == 'MONTHLY' )) ||
                   isset( $recur['BYMONTH'] ))
                $daynosw = iCalUtilityFunctions::_recurBYcntcheck( $bydayvalue['0']
                                                  , $daycnts[$m][$d]['monthdayno_up']
                                                  , $daycnts[$m][$d]['monthdayno_down'] );
              elseif( isset( $recur['FREQ'] ) && ( $recur['FREQ'] == 'YEARLY' ))
                $daynosw = iCalUtilityFunctions::_recurBYcntcheck( $bydayvalue['0']
                                                  , $daycnts[$m][$d]['yeardayno_up']
                                                  , $daycnts[$m][$d]['yeardayno_down'] );
            }
// echo "daynoexists:$daynoexists daynosw:$daynosw daynamesw:$daynamesw<br />\n"; // test ###
            if((  $daynoexists &&  $daynosw && $daynamesw ) ||
               ( !$daynoexists && !$daynosw && $daynamesw )) {
              $updateOK = TRUE;
              break;
            }
          }
        }
      }
// echo "efter BYDAY: ".implode('-',$wdate).' status: '; echo ($updateOK) ? 'TRUE' : 'FALSE'; echo "<br />\n"; // test ###
            /* check BYSETPOS */
      if( $updateOK ) {
        if( isset( $recur['BYSETPOS'] ) &&
          ( in_array( $recur['FREQ'], array( 'YEARLY', 'MONTHLY', 'WEEKLY', 'DAILY' )))) {
          if( isset( $recur['WEEKLY'] )) {
            if( $bysetposWold == $daycnts[$wdate['month']][$wdate['day']]['weekno_up'] )
              $bysetposw1[] = $wdatets;
            else
              $bysetposw2[] = $wdatets;
          }
          else {
            if(( isset( $recur['FREQ'] ) && ( 'YEARLY'      == $recur['FREQ'] )  &&
                                            ( $bysetposYold == $wdate['year'] ))   ||
               ( isset( $recur['FREQ'] ) && ( 'MONTHLY'     == $recur['FREQ'] )  &&
                                           (( $bysetposYold == $wdate['year'] )  &&
                                            ( $bysetposMold == $wdate['month'] ))) ||
               ( isset( $recur['FREQ'] ) && ( 'DAILY'       == $recur['FREQ'] )  &&
                                           (( $bysetposYold == $wdate['year'] )  &&
                                            ( $bysetposMold == $wdate['month'])  &&
                                            ( $bysetposDold == $wdate['day'] )))) {
// echo "bysetposymd1[]=".date('Y-m-d H:i:s',$wdatets)."<br />\n";//test
              $bysetposymd1[] = $wdatets;
            }
            else {
// echo "bysetposymd2[]=".date('Y-m-d H:i:s',$wdatets)."<br />\n";//test
              $bysetposymd2[] = $wdatets;
            }
          }
        }
        else {
            /* update result array if BYSETPOS is set */
          $countcnt++;
          if( $startdatets <= $wdatets ) { // only output within period
            $result[$wdatets] = TRUE;
// echo "recur ".date('Y-m-d H:i:s',$wdatets)."<br />\n";//test
          }
// echo "recur undate ".date('Y-m-d H:i:s',$wdatets)." okdatstart ".date('Y-m-d H:i:s',$startdatets)."<br />\n";//test
          $updateOK = FALSE;
        }
      }
            /* step up date */
      iCalUtilityFunctions::_stepdate( $wdate, $wdatets, $step);
            /* check if BYSETPOS is set for updating result array */
      if( $updateOK && isset( $recur['BYSETPOS'] )) {
        $bysetpos       = FALSE;
        if( isset( $recur['FREQ'] ) && ( 'YEARLY'  == $recur['FREQ'] ) &&
          ( $bysetposYold != $wdate['year'] )) {
          $bysetpos     = TRUE;
          $bysetposYold = $wdate['year'];
        }
        elseif( isset( $recur['FREQ'] ) && ( 'MONTHLY' == $recur['FREQ'] &&
         (( $bysetposYold != $wdate['year'] ) || ( $bysetposMold != $wdate['month'] )))) {
          $bysetpos     = TRUE;
          $bysetposYold = $wdate['year'];
          $bysetposMold = $wdate['month'];
        }
        elseif( isset( $recur['FREQ'] ) && ( 'WEEKLY'  == $recur['FREQ'] )) {
          $weekno = (int) date( 'W', mktime( 0, 0, $wkst, $wdate['month'], $wdate['day'], $wdate['year']));
          if( $bysetposWold != $weekno ) {
            $bysetposWold = $weekno;
            $bysetpos     = TRUE;
          }
        }
        elseif( isset( $recur['FREQ'] ) && ( 'DAILY'   == $recur['FREQ'] ) &&
         (( $bysetposYold != $wdate['year'] )  ||
          ( $bysetposMold != $wdate['month'] ) ||
          ( $bysetposDold != $wdate['day'] ))) {
          $bysetpos     = TRUE;
          $bysetposYold = $wdate['year'];
          $bysetposMold = $wdate['month'];
          $bysetposDold = $wdate['day'];
        }
        if( $bysetpos ) {
          if( isset( $recur['BYWEEKNO'] )) {
            $bysetposarr1 = & $bysetposw1;
            $bysetposarr2 = & $bysetposw2;
          }
          else {
            $bysetposarr1 = & $bysetposymd1;
            $bysetposarr2 = & $bysetposymd2;
          }
// echo 'test före out startYMD (weekno)='.$wdateStart['year'].':'.$wdateStart['month'].':'.$wdateStart['day']." ($weekStart) "; // test ###
          foreach( $recur['BYSETPOS'] as $ix ) {
            if( 0 > $ix ) // both positive and negative BYSETPOS allowed
              $ix = ( count( $bysetposarr1 ) + $ix + 1);
            $ix--;
            if( isset( $bysetposarr1[$ix] )) {
              if( $startdatets <= $bysetposarr1[$ix] ) { // only output within period
//                $testdate   = iCalUtilityFunctions::_timestamp2date( $bysetposarr1[$ix], 6 );                // test ###
//                $testweekno = (int) date( 'W', mktime( 0, 0, $wkst, $testdate['month'], $testdate['day'], $testdate['year'] )); // test ###
// echo " testYMD (weekno)=".$testdate['year'].':'.$testdate['month'].':'.$testdate['day']." ($testweekno)";   // test ###
                $result[$bysetposarr1[$ix]] = TRUE;
// echo " recur ".date('Y-m-d H:i:s',$bysetposarr1[$ix]); // test ###
              }
              $countcnt++;
            }
            if( isset( $recur['COUNT'] ) && ( $countcnt >= $recur['COUNT'] ))
              break;
          }
// echo "<br />\n"; // test ###
          $bysetposarr1 = $bysetposarr2;
          $bysetposarr2 = array();
        }
      }
    }
  }
  public static function _recurBYcntcheck( $BYvalue, $upValue, $downValue ) {
    if( is_array( $BYvalue ) &&
      ( in_array( $upValue, $BYvalue ) || in_array( $downValue, $BYvalue )))
      return TRUE;
    elseif(( $BYvalue == $upValue ) || ( $BYvalue == $downValue ))
      return TRUE;
    else
      return FALSE;
  }
  public static function _recurIntervalIx( $freq, $date, $wkst ) {
            /* create interval index */
    switch( $freq ) {
      case 'YEARLY':
        $intervalix = $date['year'];
        break;
      case 'MONTHLY':
        $intervalix = $date['year'].'-'.$date['month'];
        break;
      case 'WEEKLY':
        $wdatets    = iCalUtilityFunctions::_date2timestamp( $date );
        $intervalix = (int) date( 'W', ( $wdatets + $wkst ));
       break;
      case 'DAILY':
           default:
        $intervalix = $date['year'].'-'.$date['month'].'-'.$date['day'];
        break;
    }
    return $intervalix;
  }
/**
 * convert input format for exrule and rrule to internal format
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.14.1 - 2012-09-24
 * @param array $rexrule
 * @return array
 */
  public static function _setRexrule( $rexrule ) {
    $input          = array();
    if( empty( $rexrule ))
      return $input;
    foreach( $rexrule as $rexrulelabel => $rexrulevalue ) {
      $rexrulelabel = strtoupper( $rexrulelabel );
      if( 'UNTIL'  != $rexrulelabel )
        $input[$rexrulelabel]   = $rexrulevalue;
      else {
        iCalUtilityFunctions::_strDate2arr( $rexrulevalue );
        if( iCalUtilityFunctions::_isArrayTimestampDate( $rexrulevalue )) // timestamp, always date-time UTC
          $input[$rexrulelabel] = iCalUtilityFunctions::_timestamp2date( $rexrulevalue, 7, 'UTC' );
        elseif( iCalUtilityFunctions::_isArrayDate( $rexrulevalue )) { // date or UTC date-time
          $parno = ( isset( $rexrulevalue['hour'] ) || isset( $rexrulevalue[4] )) ? 7 : 3;
          $d = iCalUtilityFunctions::_chkDateArr( $rexrulevalue, $parno );
          if(( 3 < $parno ) && isset( $d['tz'] ) && ( 'Z' != $d['tz'] ) && iCalUtilityFunctions::_isOffset( $d['tz'] )) {
            $strdate              = sprintf( '%04d-%02d-%02d %02d:%02d:%02d %s', $d['year'], $d['month'], $d['day'], $d['hour'], $d['min'], $d['sec'], $d['tz'] );
            $input[$rexrulelabel] = iCalUtilityFunctions::_strdate2date( $strdate, 7 );
            unset( $input[$rexrulelabel]['unparsedtext'] );
          }
          else
           $input[$rexrulelabel] = $d;
        }
        elseif( 8 <= strlen( trim( $rexrulevalue ))) { // ex. textual date-time 2006-08-03 10:12:18 => UTC
          $input[$rexrulelabel] = iCalUtilityFunctions::_strdate2date( $rexrulevalue );
          unset( $input['$rexrulelabel']['unparsedtext'] );
        }
        if(( 3 < count( $input[$rexrulelabel] )) && !isset( $input[$rexrulelabel]['tz'] ))
          $input[$rexrulelabel]['tz'] = 'Z';
      }
    }
            /* set recurrence rule specification in rfc2445 order */
    $input2 = array();
    if( isset( $input['FREQ'] ))
      $input2['FREQ']       = $input['FREQ'];
    if( isset( $input['UNTIL'] ))
      $input2['UNTIL']      = $input['UNTIL'];
    elseif( isset( $input['COUNT'] ))
      $input2['COUNT']      = $input['COUNT'];
    if( isset( $input['INTERVAL'] ))
      $input2['INTERVAL']   = $input['INTERVAL'];
    if( isset( $input['BYSECOND'] ))
      $input2['BYSECOND']   = $input['BYSECOND'];
    if( isset( $input['BYMINUTE'] ))
      $input2['BYMINUTE']   = $input['BYMINUTE'];
    if( isset( $input['BYHOUR'] ))
      $input2['BYHOUR']     = $input['BYHOUR'];
    if( isset( $input['BYDAY'] )) {
      if( !is_array( $input['BYDAY'] )) // ensure upper case.. .
        $input2['BYDAY']    = strtoupper( $input['BYDAY'] );
      else {
        foreach( $input['BYDAY'] as $BYDAYx => $BYDAYv ) {
          if( 'DAY'        == strtoupper( $BYDAYx ))
             $input2['BYDAY']['DAY'] = strtoupper( $BYDAYv );
          elseif( !is_array( $BYDAYv )) {
             $input2['BYDAY'][$BYDAYx]  = $BYDAYv;
          }
          else {
            foreach( $BYDAYv as $BYDAYx2 => $BYDAYv2 ) {
              if( 'DAY'    == strtoupper( $BYDAYx2 ))
                 $input2['BYDAY'][$BYDAYx]['DAY'] = strtoupper( $BYDAYv2 );
              else
                 $input2['BYDAY'][$BYDAYx][$BYDAYx2] = $BYDAYv2;
            }
          }
        }
      }
    }
    if( isset( $input['BYMONTHDAY'] ))
      $input2['BYMONTHDAY'] = $input['BYMONTHDAY'];
    if( isset( $input['BYYEARDAY'] ))
      $input2['BYYEARDAY']  = $input['BYYEARDAY'];
    if( isset( $input['BYWEEKNO'] ))
      $input2['BYWEEKNO']   = $input['BYWEEKNO'];
    if( isset( $input['BYMONTH'] ))
      $input2['BYMONTH']    = $input['BYMONTH'];
    if( isset( $input['BYSETPOS'] ))
      $input2['BYSETPOS']   = $input['BYSETPOS'];
    if( isset( $input['WKST'] ))
      $input2['WKST']       = $input['WKST'];
    return $input2;
  }
/**
 * convert format for input date to internal date with parameters
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.14.1 - 2012-10-15
 * @param mixed  $year
 * @param mixed  $month   optional
 * @param int    $day     optional
 * @param int    $hour    optional
 * @param int    $min     optional
 * @param int    $sec     optional
 * @param string $tz      optional
 * @param array  $params  optional
 * @param string $caller  optional
 * @param string $objName optional
 * @param string $tzid    optional
 * @return array
 */
  public static function _setDate( $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $tz=FALSE, $params=FALSE, $caller=null, $objName=null, $tzid=FALSE ) {
    $input = $parno = null;
    $localtime = (( 'dtstart' == $caller ) && in_array( $objName, array( 'vtimezone', 'standard', 'daylight' ))) ? TRUE : FALSE;
    iCalUtilityFunctions::_strDate2arr( $year );
    if( iCalUtilityFunctions::_isArrayDate( $year )) {
      $input['value']  = iCalUtilityFunctions::_chkDateArr( $year, $parno );
      if( 100 > $input['value']['year'] )
        $input['value']['year'] += 2000;
      if( $localtime )
        unset( $month['VALUE'], $month['TZID'] );
      elseif( !isset( $month['TZID'] ) && isset( $tzid ))
        $month['TZID'] = $tzid;
      if( isset( $input['value']['tz'] ) && iCalUtilityFunctions::_isOffset( $input['value']['tz'] ))
        unset( $month['TZID'] );
      elseif( isset( $month['TZID'] ) && iCalUtilityFunctions::_isOffset( $month['TZID'] )) {
        $input['value']['tz'] = $month['TZID'];
        unset( $month['TZID'] );
      }
      $input['params'] = iCalUtilityFunctions::_setParams( $month, array( 'VALUE' => 'DATE-TIME' ));
      $hitval          = ( isset( $input['value']['tz'] )) ? 7 : 6;
      $parno           = iCalUtilityFunctions::_existRem( $input['params'], 'VALUE', 'DATE-TIME', $hitval );
      $parno           = iCalUtilityFunctions::_existRem( $input['params'], 'VALUE', 'DATE', 3, count( $input['value'] ), $parno );
      if(( 3 != $parno ) && isset( $input['value']['tz'] ) && ( 'Z' != $input['value']['tz'] ) && iCalUtilityFunctions::_isOffset( $input['value']['tz'] )) {
        $d             = $input['value'];
        $strdate       = sprintf( '%04d-%02d-%02d %02d:%02d:%02d %s', $d['year'], $d['month'], $d['day'], $d['hour'], $d['min'], $d['sec'], $d['tz'] );
        $input['value'] = iCalUtilityFunctions::_strdate2date( $strdate, $parno );
        unset( $input['value']['unparsedtext'], $input['params']['TZID'] );
      }
      if( isset( $input['value']['tz'] ) && !iCalUtilityFunctions::_isOffset( $input['value']['tz'] )) {
        $input['params']['TZID'] = $input['value']['tz'];
        unset( $input['value']['tz'] );
      }
    } // end if( iCalUtilityFunctions::_isArrayDate( $year ))
    elseif( iCalUtilityFunctions::_isArrayTimestampDate( $year )) {
      if( $localtime ) unset ( $month['VALUE'], $month['TZID'] );
      $input['params'] = iCalUtilityFunctions::_setParams( $month, array( 'VALUE' => 'DATE-TIME' ));
      $parno           = iCalUtilityFunctions::_existRem( $input['params'], 'VALUE', 'DATE', 3 );
      $hitval          = 7;
      $parno           = iCalUtilityFunctions::_existRem( $input['params'], 'VALUE', 'DATE-TIME', $hitval, $parno );
      if( !isset( $input['params']['TZID'] ) && !empty( $tzid ))
        $input['params']['TZID'] = $tzid;
      if( isset( $year['tz'] )) {
        $parno         = 6;
        if( !iCalUtilityFunctions::_isOffset( $year['tz'] ))
          $input['params']['TZID'] = $year['tz'];
      }
      elseif( isset( $input['params']['TZID'] )) {
        $year['tz']    = $input['params']['TZID'];
        $parno         = 6;
        if( iCalUtilityFunctions::_isOffset( $input['params']['TZID'] )) {
          unset( $input['params']['TZID'] );
          $parno       = 7;
        }
      }
      $input['value']  = iCalUtilityFunctions::_timestamp2date( $year, $parno );
    } // end elseif( iCalUtilityFunctions::_isArrayTimestampDate( $year ))
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18 [[[+/-]1234[56]] / timezone]
      if( $localtime )
        unset( $month['VALUE'], $month['TZID'] );
      elseif( !isset( $month['TZID'] ) && !empty( $tzid ))
        $month['TZID'] = $tzid;
      $input['params'] = iCalUtilityFunctions::_setParams( $month, array( 'VALUE' => 'DATE-TIME' ));
      $parno           = iCalUtilityFunctions::_existRem( $input['params'], 'VALUE', 'DATE-TIME', 7, $parno );
      $parno           = iCalUtilityFunctions::_existRem( $input['params'], 'VALUE', 'DATE', 3, $parno, $parno );
      $input['value']  = iCalUtilityFunctions::_strdate2date( $year, $parno );
      unset( $input['value']['unparsedtext'] );
      if( isset( $input['value']['tz'] )) {
        if( iCalUtilityFunctions::_isOffset( $input['value']['tz'] )) {
          $d           = $input['value'];
          $strdate     = sprintf( '%04d-%02d-%02d %02d:%02d:%02d %s', $d['year'], $d['month'], $d['day'], $d['hour'], $d['min'], $d['sec'], $d['tz'] );
          $input['value'] = iCalUtilityFunctions::_strdate2date( $strdate, 7 );
          unset( $input['value']['unparsedtext'], $input['params']['TZID'] );
        }
        else {
          $input['params']['TZID'] = $input['value']['tz'];
          unset( $input['value']['tz'] );
        }
      }
      elseif( isset( $input['params']['TZID'] ) && iCalUtilityFunctions::_isOffset( $input['params']['TZID'] )) {
        $d             = $input['value'];
        $strdate       = sprintf( '%04d-%02d-%02d %02d:%02d:%02d %s', $d['year'], $d['month'], $d['day'], $d['hour'], $d['min'], $d['sec'], $input['params']['TZID'] );
        $input['value'] = iCalUtilityFunctions::_strdate2date( $strdate, 7 );
        unset( $input['value']['unparsedtext'], $input['params']['TZID'] );
      }
    } // end elseif( 8 <= strlen( trim( $year )))
    else {
      if( is_array( $params ))
        $input['params'] = iCalUtilityFunctions::_setParams( $params, array( 'VALUE' => 'DATE-TIME' ));
      elseif( is_array( $tz )) {
        $input['params'] = iCalUtilityFunctions::_setParams( $tz,     array( 'VALUE' => 'DATE-TIME' ));
        $tz = FALSE;
      }
      elseif( is_array( $hour )) {
        $input['params'] = iCalUtilityFunctions::_setParams( $hour,   array( 'VALUE' => 'DATE-TIME' ));
        $hour = $min = $sec = $tz = FALSE;
      }
      if( $localtime )
        unset ( $input['params']['VALUE'], $input['params']['TZID'] );
      elseif( !isset( $tz ) && !isset( $input['params']['TZID'] ) && !empty( $tzid ))
        $input['params']['TZID'] = $tzid;
      elseif( isset( $tz ) && iCalUtilityFunctions::_isOffset( $tz ))
        unset( $input['params']['TZID'] );
      elseif( isset( $input['params']['TZID'] ) && iCalUtilityFunctions::_isOffset( $input['params']['TZID'] )) {
        $tz            = $input['params']['TZID'];
        unset( $input['params']['TZID'] );
      }
      $parno           = iCalUtilityFunctions::_existRem( $input['params'], 'VALUE', 'DATE', 3 );
      $hitval          = ( iCalUtilityFunctions::_isOffset( $tz )) ? 7 : 6;
      $parno           = iCalUtilityFunctions::_existRem( $input['params'], 'VALUE', 'DATE-TIME', $hitval, $parno, $parno );
      $input['value']  = array( 'year'  => $year, 'month' => $month, 'day'   => $day );
      if( 3 != $parno ) {
        $input['value']['hour'] = ( $hour ) ? $hour : '0';
        $input['value']['min']  = ( $min )  ? $min  : '0';
        $input['value']['sec']  = ( $sec )  ? $sec  : '0';
        if( !empty( $tz ))
          $input['value']['tz'] = $tz;
        $strdate       = iCalUtilityFunctions::_date2strdate( $input['value'], $parno );
        if( !empty( $tz ) && !iCalUtilityFunctions::_isOffset( $tz ))
          $strdate    .= ( 'Z' == $tz ) ? $tz : ' '.$tz;
        $input['value'] = iCalUtilityFunctions::_strdate2date( $strdate, $parno );
        unset( $input['value']['unparsedtext'] );
        if( isset( $input['value']['tz'] )) {
          if( iCalUtilityFunctions::_isOffset( $input['value']['tz'] )) {
            $d           = $input['value'];
            $strdate     = sprintf( '%04d-%02d-%02d %02d:%02d:%02d %s', $d['year'], $d['month'], $d['day'], $d['hour'], $d['min'], $d['sec'], $d['tz'] );
            $input['value'] = iCalUtilityFunctions::_strdate2date( $strdate, 7 );
            unset( $input['value']['unparsedtext'], $input['params']['TZID'] );
          }
          else {
            $input['params']['TZID'] = $input['value']['tz'];
            unset( $input['value']['tz'] );
          }
        }
        elseif( isset( $input['params']['TZID'] ) && iCalUtilityFunctions::_isOffset( $input['params']['TZID'] )) {
          $d             = $input['value'];
          $strdate       = sprintf( '%04d-%02d-%02d %02d:%02d:%02d %s', $d['year'], $d['month'], $d['day'], $d['hour'], $d['min'], $d['sec'], $input['params']['TZID'] );
          $input['value'] = iCalUtilityFunctions::_strdate2date( $strdate, 7 );
          unset( $input['value']['unparsedtext'], $input['params']['TZID'] );
        }
      }
    } // end else (i.e. using all arguments)
    if(( 3 == $parno ) || ( isset( $input['params']['VALUE'] ) && ( 'DATE' == $input['params']['VALUE'] ))) {
      $input['params']['VALUE'] = 'DATE';
      unset( $input['value']['hour'], $input['value']['min'], $input['value']['sec'], $input['value']['tz'], $input['params']['TZID'] );
    }
    elseif( isset( $input['params']['TZID'] )) {
      if(( 'UTC' == strtoupper( $input['params']['TZID'] )) || ( 'GMT' == strtoupper( $input['params']['TZID'] ))) {
        $input['value']['tz'] = 'Z';
        unset( $input['params']['TZID'] );
      }
      else
        unset( $input['value']['tz'] );
    }
    elseif( isset( $input['value']['tz'] )) {
      if(( 'UTC' == strtoupper( $input['value']['tz'] )) || ( 'GMT' == strtoupper( $input['value']['tz'] )))
        $input['value']['tz'] = 'Z';
      if( 'Z' != $input['value']['tz'] ) {
        $input['params']['TZID'] = $input['value']['tz'];
        unset( $input['value']['tz'] );
      }
      else
        unset( $input['params']['TZID'] );
    }
    if( $localtime )
      unset( $input['value']['tz'], $input['params']['TZID'] );
    return $input;
  }
/**
 * convert format for input date (UTC) to internal date with parameters
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.14.4 - 2012-10-06
 * @param mixed $year
 * @param mixed $month  optional
 * @param int   $day    optional
 * @param int   $hour   optional
 * @param int   $min    optional
 * @param int   $sec    optional
 * @param array $params optional
 * @return array
 */
  public static function _setDate2( $year, $month=FALSE, $day=FALSE, $hour=FALSE, $min=FALSE, $sec=FALSE, $params=FALSE ) {
    $input = null;
    iCalUtilityFunctions::_strDate2arr( $year );
    if( iCalUtilityFunctions::_isArrayDate( $year )) {
      $input['value']  = iCalUtilityFunctions::_chkDateArr( $year, 7 );
      if( isset( $input['value']['year'] ) && ( 100 > $input['value']['year'] ))
        $input['value']['year'] += 2000;
      $input['params'] = iCalUtilityFunctions::_setParams( $month, array( 'VALUE' => 'DATE-TIME' ));
      if( isset( $input['value']['tz'] ) && ( 'Z' != $input['value']['tz'] ) && iCalUtilityFunctions::_isOffset( $input['value']['tz'] )) {
        $d             = $input['value'];
        $strdate       = sprintf( '%04d-%02d-%02d %02d:%02d:%02d %s', $d['year'], $d['month'], $d['day'], $d['hour'], $d['min'], $d['sec'], $d['tz'] );
        $input['value'] = iCalUtilityFunctions::_strdate2date( $strdate, 7 );
        unset( $input['value']['unparsedtext'] );
      }
    }
    elseif( iCalUtilityFunctions::_isArrayTimestampDate( $year )) {
      $year['tz']      = 'UTC';
      $input['value']  = iCalUtilityFunctions::_timestamp2date( $year, 7 );
      $input['params'] = iCalUtilityFunctions::_setParams( $month, array( 'VALUE' => 'DATE-TIME' ));
    }
    elseif( 8 <= strlen( trim( $year ))) { // ex. 2006-08-03 10:12:18
      $input['value']  = iCalUtilityFunctions::_strdate2date( $year, 7 );
      unset( $input['value']['unparsedtext'] );
      $input['params'] = iCalUtilityFunctions::_setParams( $month, array( 'VALUE' => 'DATE-TIME' ));
    }
    else {
      $input['value']  = array( 'year'  => $year
                              , 'month' => $month
                              , 'day'   => $day
                              , 'hour'  => $hour
                              , 'min'   => $min
                              , 'sec'   => $sec );
      if(  isset( $tz )) $input['value']['tz'] = $tz;
      if(( isset( $tz ) && iCalUtilityFunctions::_isOffset( $tz )) ||
         ( isset( $input['params']['TZID'] ) && iCalUtilityFunctions::_isOffset( $input['params']['TZID'] ))) {
          if( !isset( $tz ) && isset( $input['params']['TZID'] ) && iCalUtilityFunctions::_isOffset( $input['params']['TZID'] ))
            $input['value']['tz'] = $input['params']['TZID'];
          unset( $input['params']['TZID'] );
        $strdate        = iCalUtilityFunctions::_date2strdate( $input['value'], 7 );
        $input['value'] = iCalUtilityFunctions::_strdate2date( $strdate, 7 );
        unset( $input['value']['unparsedtext'] );
      }
      $input['params'] = iCalUtilityFunctions::_setParams( $params, array( 'VALUE' => 'DATE-TIME' ));
    }
    $parno = iCalUtilityFunctions::_existRem( $input['params'], 'VALUE', 'DATE-TIME', 7 ); // remove default
    if( !isset( $input['value']['hour'] )) $input['value']['hour'] = 0;
    if( !isset( $input['value']['min'] ))  $input['value']['min']  = 0;
    if( !isset( $input['value']['sec'] ))  $input['value']['sec']  = 0;
    $input['value']['tz'] = 'Z';
    return $input;
  }
/**
 * check index and set (an indexed) content in multiple value array
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.6.12 - 2011-01-03
 * @param array $valArr
 * @param mixed $value
 * @param array $params
 * @param array $defaults
 * @param int $index
 * @return void
 */
  public static function _setMval( & $valArr, $value, $params=FALSE, $defaults=FALSE, $index=FALSE ) {
    if( !is_array( $valArr )) $valArr = array();
    if( $index )
      $index = $index - 1;
    elseif( 0 < count( $valArr )) {
      $keys  = array_keys( $valArr );
      $index = end( $keys ) + 1;
    }
    else
      $index = 0;
    $valArr[$index] = array( 'value' => $value, 'params' => iCalUtilityFunctions::_setParams( $params, $defaults ));
    ksort( $valArr );
  }
/**
 * set input (formatted) parameters- component property attributes
 *
 * default parameters can be set, if missing
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 1.x.x - 2007-05-01
 * @param array $params
 * @param array $defaults
 * @return array
 */
  public static function _setParams( $params, $defaults=FALSE ) {
    if( !is_array( $params))
      $params = array();
    $input = array();
    foreach( $params as $paramKey => $paramValue ) {
      if( is_array( $paramValue )) {
        foreach( $paramValue as $pkey => $pValue ) {
          if(( '"' == substr( $pValue, 0, 1 )) && ( '"' == substr( $pValue, -1 )))
            $paramValue[$pkey] = substr( $pValue, 1, ( strlen( $pValue ) - 2 ));
        }
      }
      elseif(( '"' == substr( $paramValue, 0, 1 )) && ( '"' == substr( $paramValue, -1 )))
        $paramValue = substr( $paramValue, 1, ( strlen( $paramValue ) - 2 ));
      if( 'VALUE' == strtoupper( $paramKey ))
        $input['VALUE']                 = strtoupper( $paramValue );
      else
        $input[strtoupper( $paramKey )] = $paramValue;
    }
    if( is_array( $defaults )) {
      foreach( $defaults as $paramKey => $paramValue ) {
        if( !isset( $input[$paramKey] ))
          $input[$paramKey] = $paramValue;
      }
    }
    return (0 < count( $input )) ? $input : null;
  }
/**
 * step date, return updated date, array and timpstamp
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.14.1 - 2012-09-24
 * @param array $date, date to step
 * @param int   $timestamp
 * @param array $step, default array( 'day' => 1 )
 * @return void
 */
  public static function _stepdate( &$date, &$timestamp, $step=array( 'day' => 1 )) {
    if( !isset( $date['hour'] )) $date['hour'] = 0;
    if( !isset( $date['min'] ))  $date['min']  = 0;
    if( !isset( $date['sec'] ))  $date['sec']  = 0;
    foreach( $step as $stepix => $stepvalue )
      $date[$stepix] += $stepvalue;
    $timestamp  = mktime( $date['hour'], $date['min'], $date['sec'], $date['month'], $date['day'], $date['year'] );
    $d          = date( 'Y-m-d-H-i-s', $timestamp);
    $d          = explode( '-', $d );
    $date       = array( 'year' => $d[0], 'month' => $d[1], 'day' => $d[2], 'hour' => $d[3], 'min' => $d[4], 'sec' => $d[5] );
    foreach( $date as $k => $v )
      $date[$k] = (int) $v;
  }
/**
 * convert a date from specific string to array format
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.11.8 - 2012-01-27
 * @param mixed $input
 * @return bool, TRUE on success
 */
  public static function _strDate2arr( & $input ) {
    if( is_array( $input ))
      return FALSE;
    if( 5 > strlen( (string) $input ))
      return FALSE;
    $work = $input;
    if( 2 == substr_count( $work, '-' ))
      $work = str_replace( '-', '', $work );
    if( 2 == substr_count( $work, '/' ))
      $work = str_replace( '/', '', $work );
    if( !ctype_digit( substr( $work, 0, 8 )))
      return FALSE;
    $temp = array( 'year'  => (int) substr( $work,  0, 4 )
                 , 'month' => (int) substr( $work,  4, 2 )
                 , 'day'   => (int) substr( $work,  6, 2 ));
    if( !checkdate( $temp['month'], $temp['day'], $temp['year'] ))
      return FALSE;
    if( 8 == strlen( $work )) {
      $input = $temp;
      return TRUE;
    }
    if(( ' ' == substr( $work, 8, 1 )) || ( 'T' == substr( $work, 8, 1 )) || ( 't' == substr( $work, 8, 1 )))
      $work =  substr( $work, 9 );
    elseif( ctype_digit( substr( $work, 8, 1 )))
      $work = substr( $work, 8 );
    else
     return FALSE;
    if( 2 == substr_count( $work, ':' ))
      $work = str_replace( ':', '', $work );
    if( !ctype_digit( substr( $work, 0, 4 )))
      return FALSE;
    $temp['hour']  = substr( $work, 0, 2 );
    $temp['min']   = substr( $work, 2, 2 );
    if((( 0 > $temp['hour'] ) || ( $temp['hour'] > 23 )) ||
       (( 0 > $temp['min'] )  || ( $temp['min']  > 59 )))
      return FALSE;
    if( ctype_digit( substr( $work, 4, 2 ))) {
      $temp['sec'] = substr( $work, 4, 2 );
      if((  0 > $temp['sec'] ) || ( $temp['sec']  > 59 ))
        return FALSE;
      $len = 6;
    }
    else {
      $temp['sec'] = 0;
      $len = 4;
    }
    if( $len < strlen( $work))
      $temp['tz'] = trim( substr( $work, 6 ));
    $input = $temp;
    return TRUE;
  }
/**
 * ensures internal date-time/date format for input date-time/date in string fromat
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.14.1 - 2012-10-07
 * Modified to also return original string value by Yitzchok Lavi <icalcreator@onebigsystem.com>
 * @param array $datetime
 * @param int   $parno optional, default FALSE
 * @param moxed $wtz optional, default null
 * @return array
 */
  public static function _date_time_string( $datetime, $parno=FALSE ) {
    return iCalUtilityFunctions::_strdate2date( $datetime, $parno, null );
  }
  public static function _strdate2date( $datetime, $parno=FALSE, $wtz=null ) {
    // save original input string to return it later
    $unparseddatetime = $datetime;
    $datetime   = (string) trim( $datetime );
    $tz         = null;
    $offset     = 0;
    $tzSts      = FALSE;
    $len        = strlen( $datetime );
    if( 'Z' == substr( $datetime, -1 )) {
      $tz       = 'Z';
      $datetime = trim( substr( $datetime, 0, ( $len - 1 )));
      $tzSts    = TRUE;
      $len      = 88;
    }
    if( iCalUtilityFunctions::_isOffset( substr( $datetime, -5, 5 ))) { // [+/-]NNNN offset
      $tz       = substr( $datetime, -5, 5 );
      $datetime = trim( substr( $datetime, 0, ($len - 5)));
      $len      = strlen( $datetime );
    }
    elseif( iCalUtilityFunctions::_isOffset( substr( $datetime, -7, 7 ))) { // [+/-]NNNNNN offset
      $tz       = substr( $datetime, -7, 7 );
      $datetime = trim( substr( $datetime, 0, ($len - 7)));
      $len      = strlen( $datetime );
    }
    elseif( empty( $wtz ) && ctype_digit( substr( $datetime, 0, 4 )) && ctype_digit( substr( $datetime, -2, 2 )) && iCalUtilityFunctions::_strDate2arr( $datetime )) {
      $output = $datetime;
      if( !empty( $tz ))
        $output['tz'] = 'Z';
      $output['unparsedtext'] = $unparseddatetime;
      return $output;
    }
    else {
      $cx  = $tx = 0;    //  find any trailing timezone or offset
      for( $cx = -1; $cx > ( 9 - $len ); $cx-- ) {
        $char = substr( $datetime, $cx, 1 );
        if(( ' ' == $char) || ctype_digit( $char ))
          break; // if exists, tz ends here.. . ?
        else
           $tx--; // tz length counter
      }
      if( 0 > $tx ) { // if any
        $tz     = substr( $datetime, $tx );
        $datetime = trim( substr( $datetime, 0, $len + $tx ));
        $len    = strlen( $datetime );
      }
      if(( 17 <= $len ) ||  // long textual datetime
         ( ctype_digit( substr( $datetime, 0, 8 )) && ( 'T' ==  substr( $datetime, 8, 1 )) && ctype_digit( substr( $datetime, -6, 6 ))) ||
         ( ctype_digit( substr( $datetime, 0, 14 )))) {
        $len    = 88;
        $tzSts  = TRUE;
      }
      else
        $tz     = null; // no tz for Y-m-d dates
    }
    if( empty( $tz ) && !empty( $wtz ))
      $tz       = $wtz;
    if( 17 >= $len ) // any Y-m-d textual date
      $tz       = null;
    if( !empty( $tz ) && ( 17 < $len )) { // tz set AND long textual datetime
      if(( 'Z' != $tz ) && ( iCalUtilityFunctions::_isOffset( $tz ))) {
        $offset = (string) iCalUtilityFunctions::_tz2offset( $tz ) * -1;
        $tz     = 'UTC';
        $tzSts  = TRUE;
      }
      elseif( !empty( $wtz ))
        $tzSts  = TRUE;
      $tz       = trim( $tz );
      if(( 'Z' == $tz ) || ( 'GMT' == strtoupper( $tz )))
        $tz     = 'UTC';
      if( 0 < substr_count( $datetime, '-' ))
        $datetime = str_replace( '-', '/', $datetime );
      try {
        $d        = new DateTime( $datetime, new DateTimeZone( $tz ));
        if( 0  != $offset )  // adjust for offset
          $d->modify( $offset.' seconds' );
        $datestring = $d->format( 'Y-m-d-H-i-s' );
        unset( $d );
      }
      catch( Exception $e ) {
        $datestring = date( 'Y-m-d-H-i-s', strtotime( $datetime ));
      }
    } // end if( !empty( $tz ) && ( 17 < $len ))
    else
      $datestring = date( 'Y-m-d-H-i-s', strtotime( $datetime ));
// echo "<tr><td>&nbsp;<td colspan='3'>_strdate2date input=$datetime, tz=$tz, offset=$offset, wtz=$wtz, len=$len, prepDate=$datestring\n";
    if( 'UTC' == $tz )
      $tz         = 'Z';
    $d            = explode( '-', $datestring );
    $output       = array( 'year' => $d[0], 'month' => $d[1], 'day' => $d[2] );
    if((( FALSE !== $parno ) && ( 3 != $parno )) || // parno is set to 6 or 7
       (( FALSE === $parno ) && ( 'Z' == $tz ))  || // parno is not set and UTC
       (( FALSE === $parno ) && ( 'Z' != $tz ) && ( 0 != $d[3] + $d[4] + $d[5] ) && ( 17 < $len ))) { // !parno and !UTC and 0 != hour+min+sec and long input text
      $output['hour'] = $d[3];
      $output['min']  = $d[4];
      $output['sec']  = $d[5];
      if(( $tzSts || ( 7 == $parno )) && !empty( $tz ))
        $output['tz'] = $tz;
    }
    // return original string in the array in case strtotime failed to make sense of it
    $output['unparsedtext'] = $unparseddatetime;
    return $output;
  }
/**
 * convert timestamp to date array, default UTC or adjusted for offset/timezone
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.15.1 - 2012-10-17
 * @param mixed   $timestamp
 * @param int     $parno
 * @param string  $wtz
 * @return array
 */
  public static function _timestamp2date( $timestamp, $parno=6, $wtz=null ) {
    if( is_array( $timestamp )) {
      $tz        = ( isset( $timestamp['tz'] )) ? $timestamp['tz'] : $wtz;
      $timestamp = $timestamp['timestamp'];
    }
    $tz          = ( isset( $tz )) ? $tz : $wtz;
    if( empty( $tz ) || ( 'Z' == $tz ) || ( 'GMT' == strtoupper( $tz )))
      $tz        = 'UTC';
    elseif( iCalUtilityFunctions::_isOffset( $tz )) {
      $offset    = iCalUtilityFunctions::_tz2offset( $tz );
      $tz        = 'UTC';
    }
    try {
      $d         = new DateTime( "@$timestamp" );  // set UTC date
      if( isset( $offset ) && ( 0 != $offset ))    // adjust for offset
        $d->modify( $offset.' seconds' );
      elseif( 'UTC' != $tz )
        $d->setTimezone( new DateTimeZone( $tz )); // convert to local date
      $date      = $d->format( 'Y-m-d-H-i-s' );
      unset( $d );
    }
    catch( Exception $e ) {
      $date      = date( 'Y-m-d-H-i-s', $timestamp );
    }
    $date        = explode( '-', $date );
    $output      = array( 'year' => $date[0], 'month' => $date[1], 'day' => $date[2] );
    if( 3 != $parno ) {
      $output['hour'] = $date[3];
      $output['min']  = $date[4];
      $output['sec']  = $date[5];
      if( 'UTC' == $tz && ( !isset( $offset ) || ( 0 == $offset )))
        $output['tz'] = 'Z';
    }
    return $output;
  }
/**
 * convert timestamp (seconds) to duration in array format
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.6.23 - 2010-10-23
 * @param int $timestamp
 * @return array, duration format
 */
  public static function _timestamp2duration( $timestamp ) {
    $dur         = array();
    $dur['week'] = (int) floor( $timestamp / ( 7 * 24 * 60 * 60 ));
    $timestamp   =              $timestamp % ( 7 * 24 * 60 * 60 );
    $dur['day']  = (int) floor( $timestamp / ( 24 * 60 * 60 ));
    $timestamp   =              $timestamp % ( 24 * 60 * 60 );
    $dur['hour'] = (int) floor( $timestamp / ( 60 * 60 ));
    $timestamp   =              $timestamp % ( 60 * 60 );
    $dur['min']  = (int) floor( $timestamp / ( 60 ));
    $dur['sec']  = (int)        $timestamp % ( 60 );
    return $dur;
  }
/**
 * transforms a dateTime from a timezone to another using PHP DateTime and DateTimeZone class (PHP >= PHP 5.2.0)
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.15.1 - 2012-10-17
 * @param mixed  $date,   date to alter
 * @param string $tzFrom, PHP valid 'from' timezone
 * @param string $tzTo,   PHP valid 'to' timezone, default 'UTC'
 * @param string $format, date output format, default 'Ymd\THis'
 * @return bool
 */
  public static function transformDateTime( & $date, $tzFrom, $tzTo='UTC', $format = 'Ymd\THis' ) {
    if( is_array( $date ) && isset( $date['timestamp'] )) {
      try {
        $d = new DateTime( "@{$date['timestamp']}" ); // set UTC date
        $d->setTimezone(new DateTimeZone( $tzFrom )); // convert to 'from' date
      }
      catch( Exception $e ) { return FALSE; }
    }
    else {
      if( iCalUtilityFunctions::_isArrayDate( $date )) {
        if( isset( $date['tz'] ))
          unset( $date['tz'] );
        $date  = iCalUtilityFunctions::_date2strdate( iCalUtilityFunctions::_chkDateArr( $date ));
      }
      if( 'Z' == substr( $date, -1 ))
        $date = substr( $date, 0, ( strlen( $date ) - 2 ));
      try { $d = new DateTime( $date, new DateTimeZone( $tzFrom )); }
      catch( Exception $e ) { return FALSE; }
    }
    try { $d->setTimezone( new DateTimeZone( $tzTo )); }
    catch( Exception $e ) { return FALSE; }
    $date = $d->format( $format );
    return TRUE;
  }
/**
 * convert offset, [+/-]HHmm[ss], to seconds used when correcting UTC to localtime or v.v.
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.11.4 - 2012-01-11
 * @param string $offset
 * @return integer
 */
  public static function _tz2offset( $tz ) {
    $tz           = trim( (string) $tz );
    $offset       = 0;
    if(((     5  != strlen( $tz ))       && ( 7  != strlen( $tz ))) ||
       ((    '+' != substr( $tz, 0, 1 )) && ( '-' != substr( $tz, 0, 1 ))) ||
       (( '0000' >= substr( $tz, 1, 4 )) && ( '9999' < substr( $tz, 1, 4 ))) ||
           (( 7  == strlen( $tz ))       && ( '00' > substr( $tz, 5, 2 )) && ( '99' < substr( $tz, 5, 2 ))))
      return $offset;
    $hours2sec    = (int) substr( $tz, 1, 2 ) * 3600;
    $min2sec      = (int) substr( $tz, 3, 2 ) *   60;
    $sec          = ( 7  == strlen( $tz )) ? (int) substr( $tz, -2 ) : '00';
    $offset       = $hours2sec + $min2sec + $sec;
    $offset       = ('-' == substr( $tz, 0, 1 )) ? $offset * -1 : $offset;
    return $offset;
  }
}
