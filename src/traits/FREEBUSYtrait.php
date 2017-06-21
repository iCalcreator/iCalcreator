<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * link      http://kigkonsult.se/iCalcreator/index.php
 * package   iCalcreator
 * version   2.23.18
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
 * FREEBUSY property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-17
 */
trait FREEBUSYtrait {
/**
 * @var array component property FREEBUSY value
 * @access protected
 */
  protected $freebusy = null;
/**
 * @var FREEBUSY param keywords
 * @access protected
 * @static
 */
   protected static $LCFBTYPE         = 'fbtype';
   protected static $UCFBTYPE         = 'FBTYPE';
   protected static $FREEBUSYKEYS     = ['FREE', 'BUSY', 'BUSY-UNAVAILABLE', 'BUSY-TENTATIVE'];
   protected static $FREE             = 'FREE';
   protected static $BUSY             = 'BUSY';
/*
   protected static $BUSY_UNAVAILABLE = 'BUSY-UNAVAILABLE';
   protected static $BUSY_TENTATIVE   = 'BUSY-TENTATIVE';
*/
/**
 * Return formatted output for calendar component property freebusy
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.16.27 - 2013-07-05
 * @return string
 */
  public function createFreebusy() {
    static $FMT    = ';FBTYPE=%s';
    static $SORTER = ['kigkonsult\iCalcreator\vcalendarSortHandler', 'sortRdate1'];
    if( empty( $this->freebusy ))
      return null;
    $output = null;
    foreach( $this->freebusy as $fx => $freebusyPart ) {
      if( empty( $freebusyPart[util::$LCvalue] ) ||
        (( 1 == count( $freebusyPart[util::$LCvalue] )) &&
           isset( $freebusyPart[util::$LCvalue][self::$LCFBTYPE] ))) {
        if( $this->getConfig( util::$ALLOWEMPTY ))
          $output .= util::createElement( util::$FREEBUSY );
        continue;
      }
      $attributes = $content = null;
      if( isset( $freebusyPart[util::$LCvalue][self::$LCFBTYPE] )) {
          $attributes .= sprintf( $FMT, $freebusyPart[util::$LCvalue][self::$LCFBTYPE] );
        unset( $freebusyPart[util::$LCvalue][self::$LCFBTYPE] );
        $freebusyPart[util::$LCvalue] = array_values( $freebusyPart[util::$LCvalue] );
      }
      else
        $attributes .= sprintf( $FMT, self::$BUSY );
      $attributes .= util::createParams( $freebusyPart[util::$LCparams] );
      $fno        = 1;
      $cnt        = count( $freebusyPart[util::$LCvalue]);
      if( 1 < $cnt )
        usort( $freebusyPart[util::$LCvalue], $SORTER );
      foreach( $freebusyPart[util::$LCvalue] as $periodix => $freebusyPeriod ) {
        $formatted   = util::date2strdate( $freebusyPeriod[0] );
        $content .= $formatted;
        $content .= util::$L;
        $cnt2 = count( $freebusyPeriod[1]);
        if( array_key_exists( util::$LCYEAR, $freebusyPeriod[1] )) // date-time
          $cnt2 = 7;
        elseif( array_key_exists( util::$LCWEEK, $freebusyPeriod[1] )) // duration
          $cnt2 = 5;
        if(( 7 == $cnt2 )   &&    // period=  -> date-time
            isset( $freebusyPeriod[1][util::$LCYEAR] )  &&
            isset( $freebusyPeriod[1][util::$LCMONTH] ) &&
            isset( $freebusyPeriod[1][util::$LCDAY] )) {
          $content .= util::date2strdate( $freebusyPeriod[1] );
        }
        else {                                                     // period=  -> dur-time
          $content .= util::duration2str( $freebusyPeriod[1] );
        }
        if( $fno < $cnt )
          $content .= util::$COMMA;
        $fno++;
      } // end foreach( $freebusyPart[util::$LCvalue] as $periodix => $freebusyPeriod )
      $output .= util::createElement( util::$FREEBUSY,
                                      $attributes,
                                      $content );
    } // end foreach( $this->freebusy as $fx => $freebusyPart )
    return $output;
  }
/**
 * Set calendar component property freebusy
 *
 * @param string  $fbType
 * @param array   $fbValues
 * @param array   $params
 * @param integer $index
 * @return bool
 */
  public function setFreebusy( $fbType, $fbValues, $params=null, $index=null ) {
    static $PREFIXARR = ['P', '+', '-'];
    if( empty( $fbValues )) {
      if( $this->getConfig( util::$ALLOWEMPTY )) {
        util::setMval( $this->freebusy,
                       util::$EMPTYPROPERTY,
                       $params,
                       false,
                       $index );
        return true;
      }
      else
        return false;
    }
    $fbType = strtoupper( $fbType );
    if( ! in_array( $fbType, self::$FREEBUSYKEYS ) &&
        ! util::isXprefixed( $fbType ))
      $fbType = self::$BUSY;
    $input = [self::$LCFBTYPE => $fbType];
    foreach( $fbValues as $fbPeriod ) {               // periods => period
      if( empty( $fbPeriod ))
        continue;
      $freebusyPeriod = [];
      foreach( $fbPeriod as $fbMember ) {             // pairs => singlepart
        $freebusyPairMember = [];
        if( is_array( $fbMember )) {
          if( util::isArrayDate( $fbMember )) {       // date-time value
            $freebusyPairMember       = util::chkDateArr( $fbMember, 7 );
            $freebusyPairMember[util::$LCtz] = util::$Z;
          }
          elseif( util::isArrayTimestampDate( $fbMember )) { // timestamp value
            $freebusyPairMember       = util::timestamp2date( $fbMember[util::$LCTIMESTAMP], 7 );
            $freebusyPairMember[util::$LCtz] = util::$Z;
          }
          else {                                      // array format duration
            $freebusyPairMember = util::duration2arr( $fbMember );
          }
        }
        elseif(( 3 <= strlen( trim( $fbMember ))) &&  // string format duration
                        ( in_array( $fbMember{0}, $PREFIXARR ))) {
          $freebusyPairMember = util::durationStr2arr( $fbMember );
        }
        elseif( 8 <= strlen( trim( $fbMember ))) {    // text date ex. 2006-08-03 10:12:18
          $freebusyPairMember       = util::strDate2ArrayDate( $fbMember, 7 );
          unset( $freebusyPairMember[util::$UNPARSEDTEXT] );
          $freebusyPairMember[util::$LCtz] = util::$Z;
        }
        $freebusyPeriod[]   = $freebusyPairMember;
      }
      $input[]              = $freebusyPeriod;
    }
    util::setMval( $this->freebusy,
                   $input,
                   $params,
                   false,
                   $index );
    return true;
  }
}
