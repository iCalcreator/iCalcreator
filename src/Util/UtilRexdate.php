<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * This file is a part of iCalcreator.
 *
 * Copyright (c) 2007-2018 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      http://kigkonsult.se/iCalcreator/index.php
 * Package   iCalcreator
 * Version   2.26
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the [rfc5545] PRODID as implemented and
 *           invoked in iCalcreator shall be included in all copies or
 *           substantial portions of the iCalcreator.
 *           iCalcreator can be used either under the terms of
 *           a proprietary license, available from iCal_at_kigkonsult_dot_se
 *           or the GNU Affero General Public License, version 3:
 *           iCalcreator is free software: you can redistribute it and/or
 *           modify it under the terms of the GNU Affero General Public License
 *           as published by the Free Software Foundation, either version 3 of
 *           the License, or (at your option) any later version.
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *           GNU Affero General Public License for more details.
 *           You should have received a copy of the GNU Affero General Public
 *           License along with this program.
 *           If not, see <http://www.gnu.org/licenses/>.
 */

namespace Kigkonsult\Icalcreator\Util;

/**
 * iCalcreator EXDATE/RDATE support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26 - 2018-11-10
 */
class UtilRexdate
{
    /**
     * Check (EXDATE/RDATE) date(-time) and params arrays for an opt. timezone
     *
     * If it is a DATE-TIME or DATE, updates $parno and (opt) $params)
     *
     * @param array $theDate date to check
     * @param int   $parno   no of date parts (i.e. year, month.. .)
     * @param array $params  property parameters
     * @access private
     * @static
     */
    private static function chkDateCfg( $theDate, & $parno, & $params ) {
        $paramsValueIsDATE = Util::isParamsValueSet( [ Util::$LCparams => $params ], Util::$DATE );
        switch( true ) {
            case ( isset( $params[Util::$TZID] )) :
                $parno = 6;
                break;
            case ( $paramsValueIsDATE ) :
                $params[Util::$VALUE] = Util::$DATE;
                $parno                = 3;
                break;
            default:
                if( Util::isParamsValueSet( [ Util::$LCparams => $params ], Util::$PERIOD )) {
                    $params[Util::$VALUE] = Util::$PERIOD;
                    $parno                = 7;
                }
                switch( true ) {
                    case ( \is_array( $theDate )) :
                        if( isset( $theDate[Util::$LCTIMESTAMP] )) {
                            $tzid = ( isset( $theDate[Util::$LCtz] )) ? $theDate[Util::$LCtz] : null;
                        }
                        else {
                            $tzid = ( isset( $theDate[Util::$LCtz] ))
                                ? $theDate[Util::$LCtz] : ( 7 == \count( $theDate )) ? end( $theDate ) : null;
                        }
                        if( ! empty( $tzid )) {
                            $parno = 7;
                            if( ! Util::isOffset( $tzid )) {
                                $params[Util::$TZID] = $tzid;
                            } // save only timezone
                        }
                        elseif( ! $parno && ( 3 == \count( $theDate )) && $paramsValueIsDATE ) {
                            $parno = 3;
                        }
                        else {
                            $parno = 6;
                        }
                        break;
                    default : // i.e. string
                        $date = trim((string) $theDate );
                        if( Util::$Z == \substr( $date, -1 )) {
                            $parno = 7;
                        } // UTC DATE-TIME
                        elseif((( 8 == strlen( $date ) && \ctype_digit( $date )) || ( 11 >= strlen( $date )))
                            && $paramsValueIsDATE ) {
                            $parno = 3;
                        }
                        $date = Util::strDate2ArrayDate( $date, $parno );
                        unset( $date[Util::$UNPARSEDTEXT] );
                        if( ! empty( $date[Util::$LCtz] )) {
                            $parno = 7;
                            if( ! Util::isOffset( $date[Util::$LCtz] )) {
                                $params[Util::$TZID] = $date[Util::$LCtz];
                            } // save only timezone
                        }
                        elseif( empty( $parno )) {
                            $parno = 6;
                        }
                } // end switch( true )
                if( isset( $params[Util::$TZID] )) {
                    $parno = 6;
                }
                break;
        } // end switch( true )
    }

    /**
     * Return formatted output for calendar component property data value type recur
     *
     * @param array $exdateData
     * @param bool  $allowEmpty
     * @return string
     * @static
     */
    public static function formatExdate( $exdateData, $allowEmpty ) {
        static $SORTER1 = [
            'Kigkonsult\Icalcreator\VcalendarSortHandler',
            'sortExdate1',
        ];
        static $SORTER2 = [
            'Kigkonsult\Icalcreator\VcalendarSortHandler',
            'sortExdate2',
        ];
        $output  = null;
        $exdates = [];
        foreach(( \array_keys( $exdateData )) as $ex ) {
            $theExdate = $exdateData[$ex];
            if( empty( $theExdate[Util::$LCvalue] )) {
                if( $allowEmpty ) {
                    $output .= Util::createElement( Util::$EXDATE );
                }
                continue;
            }
            if( 1 < \count( $theExdate[Util::$LCvalue] )) {
                usort( $theExdate[Util::$LCvalue], $SORTER1 );
            }
            $exdates[] = $theExdate;
        }
        if( 1 < \count( $exdates )) {
            usort( $exdates, $SORTER2 );
        }
        foreach(( \array_keys( $exdates )) as $ex ) {
            $theExdate = $exdates[$ex];
            $content   = $attributes = null;
            foreach(( \array_keys( $theExdate[Util::$LCvalue] )) as $eix ) {
                $exdatePart = $theExdate[Util::$LCvalue][$eix];
                $parno      = \count( $exdatePart );
                $formatted  = Util::date2strdate( $exdatePart, $parno );
                if( isset( $theExdate[Util::$LCparams][Util::$TZID] )) {
                    $formatted = \str_replace( Util::$Z, null, $formatted );
                }
                if( 0 < $eix ) {
                    if( isset( $theExdate[Util::$LCvalue][0][Util::$LCtz] )) {
                        if( \ctype_digit( \substr( $theExdate[Util::$LCvalue][0][Util::$LCtz], -4 )) ||
                            ( Util::$Z == $theExdate[Util::$LCvalue][0][Util::$LCtz] )) {
                            if( Util::$Z != \substr( $formatted, -1 )) {
                                $formatted .= Util::$Z;
                            }
                        }
                        else {
                            $formatted = \str_replace( Util::$Z, null, $formatted );
                        }
                    }
                    else {
                        $formatted = \str_replace( Util::$Z, null, $formatted );
                    }
                } // end if( 0 < $eix )
                $content .= ( 0 < $eix ) ? Util::$COMMA . $formatted : $formatted;
            } // end foreach(( \array_keys( $theExdate[Util::$LCvalue]...
            $output .= Util::createElement( Util::$EXDATE,
                                            Util::createParams( $theExdate[Util::$LCparams] ),
                                            $content
            );
        } // end foreach(( \array_keys( $exdates...
        return $output;
    }

    /**
     * Return prepared calendar component property exdate input
     *
     * @param array $exdates
     * @param array $params
     * @return mixed array|bool
     * @static
     * @since 2.26 - 2018-11-10
     */
    public static function prepInputExdate( $exdates, $params = null ) {
        static $GMTUTCZARR = [ 'GMT', 'UTC', 'Z' ];
        $input = [
            Util::$LCparams => Util::setParams( $params, Util::$DEFAULTVALUEDATETIME ),
            Util::$LCvalue  => [],
        ];
        $toZ   = ( isset( $input[Util::$LCparams][Util::$TZID] ) &&
            in_array( strtoupper( $input[Util::$LCparams][Util::$TZID] ), $GMTUTCZARR )) ? true : false;
        /* ev. check 1:st date and save ev. timezone **/
        self::chkDateCfg( \reset( $exdates ), $parno, $input[Util::$LCparams] );
        Util::existRem( $input[Util::$LCparams], Util::$VALUE, Util::$DATE_TIME ); // remove default parameter
        foreach(( \array_keys( $exdates )) as $eix ) {
            $theExdate = $exdates[$eix];
            Util::strDate2arr( $theExdate );
            $wDate     = [];
            if( Util::isArrayTimestampDate( $theExdate )) {
                if( isset( $theExdate[Util::$LCtz] ) &&
                    ! Util::isOffset( $theExdate[Util::$LCtz] )) {
                    if( isset( $input[Util::$LCparams][Util::$TZID] )) {
                        $theExdate[Util::$LCtz] = $input[Util::$LCparams][Util::$TZID];
                    }
                    else {
                        $input[Util::$LCparams][Util::$TZID] = $theExdate[Util::$LCtz];
                    }
                }
                $wDate = Util::timestamp2date( $theExdate, $parno );
            }
            elseif( \is_array( $theExdate )) {
                $d = Util::chkDateArr( $theExdate, $parno );
                if( isset( $d[Util::$LCtz] ) && ( Util::$Z != $d[Util::$LCtz] ) && Util::isOffset( $d[Util::$LCtz] )) {
                    $wDate = Util::ensureArrDatetime( [Util::$LCvalue => $d], $d[Util::$LCtz], 7 );
                    unset( $wDate[Util::$UNPARSEDTEXT] );
                }
                else {
                    $wDate = $d;
                }
            }
            elseif( 8 <= strlen( trim( $theExdate ))) { // ex. 2006-08-03 10:12:18
                $wDate = Util::strDate2ArrayDate( $theExdate, $parno );
                unset( $wDate[Util::$UNPARSEDTEXT] );
            }
            if( 3 == $parno ) {
                unset( $wDate[Util::$LCHOUR], $wDate[Util::$LCMIN], $wDate[Util::$LCSEC], $wDate[Util::$LCtz] );
            }
            elseif( isset( $wDate[Util::$LCtz] )) {
                $wDate[Util::$LCtz] = (string) $wDate[Util::$LCtz];
            }
            if( isset( $input[Util::$LCparams][Util::$TZID] ) ||
                ( isset( $wDate[Util::$LCtz] ) &&
                    ! Util::isOffset( $wDate[Util::$LCtz] )) ||
                ( isset( $input[Util::$LCvalue][0] ) &&
                    ( ! isset( $input[Util::$LCvalue][0][Util::$LCtz] ))) ||
                ( isset( $input[Util::$LCvalue][0][Util::$LCtz] ) &&
                    ! Util::isOffset( $input[Util::$LCvalue][0][Util::$LCtz] ))) {
                unset( $wDate[Util::$LCtz] );
            }
            if( $toZ ) { // time zone Z
                $wDate[Util::$LCtz] = Util::$Z;
            }
            $input[Util::$LCvalue][] = $wDate;
        } // end foreach(( \array_keys( $exdates...
        if( 0 >= \count( $input[Util::$LCvalue] )) {
            return false;
        }
        if( 3 == $parno ) {
            $input[Util::$LCparams][Util::$VALUE] = Util::$DATE;
            unset( $input[Util::$LCparams][Util::$TZID] );
        }
        if( $toZ ) // time zone Z
        {
            unset( $input[Util::$LCparams][Util::$TZID] );
        }
        return $input;
    }

    /**
     * Return formatted output for calendar component property rdate
     *
     * @param array  $rdateData
     * @param bool   $allowEmpty
     * @param string $compType
     * @return string
     * @static
     */
    public static function formatRdate( $rdateData, $allowEmpty, $compType ) {
        static $SORTER1 = [
            'Kigkonsult\Icalcreator\VcalendarSortHandler',
            'sortRdate1',
        ];
        static $SORTER2 = [
            'Kigkonsult\Icalcreator\VcalendarSortHandler',
            'sortRdate2',
        ];
        $utcTime = ( Util::isCompInList( $compType, Util::$TZCOMPS )) ? true : false;
        $output  = null;
        $rdates  = [];
        foreach(( \array_keys( $rdateData )) as $rpix ) {
            $theRdate = $rdateData[$rpix];
            if( empty( $theRdate[Util::$LCvalue] )) {
                if( $allowEmpty ) {
                    $output .= Util::createElement( Util::$RDATE );
                }
                continue;
            }
            if( $utcTime ) {
                unset( $theRdate[Util::$LCparams][Util::$TZID] );
            }
            if( 1 < \count( $theRdate[Util::$LCvalue] )) {
                usort( $theRdate[Util::$LCvalue], $SORTER1 );
            }
            $rdates[] = $theRdate;
        }
        if( 1 < \count( $rdates )) {
            usort( $rdates, $SORTER2 );
        }
        foreach(( \array_keys( $rdates )) as $rpix ) {
            $theRdate   = $rdates[$rpix];
            $attributes = Util::createParams( $theRdate[Util::$LCparams] );
            $cnt        = \count( $theRdate[Util::$LCvalue] );
            $content    = null;
            $rno        = 1;
            foreach(( \array_keys( $theRdate[Util::$LCvalue] )) as $rix ) {
                $rdatePart   = $theRdate[Util::$LCvalue][$rix];
                $contentPart = null;
                if( \is_array( $rdatePart ) &&
                    Util::isParamsValueSet( $theRdate, Util::$PERIOD )) { // PERIOD
                    if( $utcTime ) {
                        unset( $rdatePart[0][Util::$LCtz] );
                    }
                    $formatted = Util::date2strdate( $rdatePart[0] ); // PERIOD part 1
                    if( $utcTime || ! empty( $theRdate[Util::$LCparams][Util::$TZID] )) {
                        $formatted = \str_replace( Util::$Z, null, $formatted );
                    }
                    $contentPart .= $formatted;
                    $contentPart .= '/';
                    $cnt2         = \count( $rdatePart[1] );
                    if( array_key_exists( Util::$LCYEAR, $rdatePart[1] )) {
                        if( array_key_exists( Util::$LCHOUR, $rdatePart[1] )) {
                            $cnt2 = 7; // date-time
                        }             
                        else {
                            $cnt2 = 3; // date
                        }             
                    }
                    elseif( array_key_exists( Util::$LCWEEK, $rdatePart[1] )) { // duration
                        $cnt2 = 5;
                    }
                    if(( 7 == $cnt2 ) &&    // period=  -> date-time
                        isset( $rdatePart[1][Util::$LCYEAR] ) &&
                        isset( $rdatePart[1][Util::$LCMONTH] ) &&
                        isset( $rdatePart[1][Util::$LCDAY] )) {
                        if( $utcTime ) {
                            unset( $rdatePart[1][Util::$LCtz] );
                        }
                        $formatted = Util::date2strdate( $rdatePart[1] ); // PERIOD part 2
                        if( $utcTime || ! empty( $theRdate[Util::$LCparams][Util::$TZID] )) {
                            $formatted = \str_replace( Util::$Z, null, $formatted );
                        }
                        $contentPart .= $formatted;
                    }
                    else {                  // period=  -> dur-time
                        $contentPart .= Util::duration2str( $rdatePart[1] );
                    }
                } // PERIOD end
                else { // SINGLE date start
                    if( $utcTime ) {
                        unset( $rdatePart[Util::$LCtz] );
                    }
                    $parno     = ( Util::isParamsValueSet( $theRdate, Util::$DATE )) ? 3 : null;
                    $formatted = Util::date2strdate( $rdatePart, $parno );
                    if( $utcTime || ! empty( $theRdate[Util::$LCparams][Util::$TZID] )) {
                        $formatted = \str_replace( Util::$Z, null, $formatted );
                    }
                    $contentPart .= $formatted;
                }
                $content .= $contentPart;
                if( $rno < $cnt ) {
                    $content .= Util::$COMMA;
                }
                $rno++;
            } // end foreach(( \array_keys( $theRdate[Util::$LCvalue]...
            $output .= Util::createElement( Util::$RDATE, $attributes, $content );
        } // foreach(( \array_keys( $rdates...
        return $output;
    }

    /**
     * Return prepared calendar component property rdate input
     *
     * @param array  $rdates
     * @param array  $params
     * @param string $compType
     * @return array
     * @static
     */
    public static function prepInputRdate( $rdates, $params, $compType ) {
        static $PREFIXARR  = [ 'P', '+', '-' ];
        static $GMTUTCZARR = [ 'GMT', 'UTC', 'Z' ];
        static $P          = 'P';
        $input = [ Util::$LCparams => Util::setParams( $params, Util::$DEFAULTVALUEDATETIME ) ];
        if( Util::isCompInList( $compType, Util::$TZCOMPS )) {
            unset( $input[Util::$LCparams][Util::$TZID] );
            $input[Util::$LCparams][Util::$VALUE] = Util::$DATE_TIME;
        }
        $toZ = ( isset( $params[Util::$TZID] ) &&
            in_array( strtoupper( $params[Util::$TZID] ), $GMTUTCZARR )) ? true : false;
        /*  check if PERIOD, if not set */
        if(( ! isset( $input[Util::$LCparams][Util::$VALUE] ) ||
                ( ! Util::isParamsValueSet( $input, Util::$DATE ) &&
                    ! Util::isParamsValueSet( $input, Util::$PERIOD ))) &&
            isset( $rdates[0] ) && \is_array( $rdates[0] ) && ( 2 == \count( $rdates[0] )) &&
            isset( $rdates[0][0] ) && isset( $rdates[0][1] ) && ! isset( $rdates[0][Util::$LCTIMESTAMP] ) &&
            (( \is_array( $rdates[0][0] ) && ( isset( $rdates[0][0][Util::$LCTIMESTAMP] ) ||
                        Util::isArrayDate( $rdates[0][0] ))) ||
                ( \is_string( $rdates[0][0] ) && ( 8 <= strlen( trim( $rdates[0][0] ))))) &&
            ( \is_array( $rdates[0][1] ) || ( \is_string( $rdates[0][1] ) && ( 3 <= strlen( trim( $rdates[0][1] )
                        ))))) {
            $input[Util::$LCparams][Util::$VALUE] = Util::$PERIOD;
        }
        /* check 1:st date, upd. $parno (opt) and save opt. timezone */
        $date = \reset( $rdates );
        if( isset( $input[Util::$LCparams][Util::$VALUE] ) &&
            ( Util::$PERIOD == $input[Util::$LCparams][Util::$VALUE] )) { // PERIOD
            $date = \reset( $date );
        }
        self::chkDateCfg( $date, $parno, $input[Util::$LCparams] );
        Util::existRem( $input[Util::$LCparams], Util::$VALUE, Util::$DATE_TIME ); // remove default
        foreach( $rdates as $rpix => $theRdate ) {
            $wDate = null;
            Util::strDate2arr( $theRdate );
            if( \is_array( $theRdate )) {
                if( isset( $input[Util::$LCparams][Util::$VALUE] ) &&
                    ( Util::$PERIOD == $input[Util::$LCparams][Util::$VALUE] )) { // PERIOD
                    foreach( $theRdate as $rix => $rPeriod ) {
                        Util::strDate2arr( $theRdate );
                        $wDate2 = null;
                        if( \is_array( $rPeriod )) {
                            if( Util::isArrayTimestampDate( $rPeriod )) {    // timestamp
                                if( isset( $rPeriod[Util::$LCtz] ) &&
                                    ! Util::isOffset( $rPeriod[Util::$LCtz] )) {
                                    if( isset( $input[Util::$LCparams][Util::$TZID] )) {
                                        $rPeriod[Util::$LCtz] = $input[Util::$LCparams][Util::$TZID];
                                    }
                                    else {
                                        $input[Util::$LCparams][Util::$TZID] = $rPeriod[Util::$LCtz];
                                    }
                                }
                                $wDate2 = Util::timestamp2date( $rPeriod, $parno );
                            } // end if( Util::isArrayTimestampDate( $rPeriod ))
                            elseif( Util::isArrayDate( $rPeriod )) {
                                $d = ( 3 < \count( $rPeriod )) ? Util::chkDateArr( $rPeriod, $parno ) : Util::chkDateArr( $rPeriod, 6 );
                                if( isset( $d[Util::$LCtz] ) && ( Util::$Z != $d[Util::$LCtz] ) && Util::isOffset( $d[Util::$LCtz] )) {
                                    $wDate2 = Util::ensureArrDatetime( [Util::$LCvalue => $d], $d[Util::$LCtz], 7 );
                                    unset( $wDate2[Util::$UNPARSEDTEXT] );
                                }
                                else {
                                    $wDate2 = $d;
                                }
                            } // end elseif( Util::isArrayDate( $rPeriod ))
                            elseif(( 1 == \count( $rPeriod )) &&
                                ( 8 <= strlen( \reset( $rPeriod )))) { // text-date
                                $wDate2 = Util::strDate2ArrayDate( \reset( $rPeriod ), $parno );
                                unset( $wDate2[Util::$UNPARSEDTEXT] );
                            }
                            else {                                     // array format duration
                                $wDate2 = Util::duration2arr( $rPeriod );
                            }
                        } // end if( \is_array( $rPeriod ))
                        elseif(( 3 <= strlen( trim( $rPeriod ))) &&    // string format duration
                            ( in_array( $rPeriod[0], $PREFIXARR ))) {
                            if( $P != $rPeriod[0] ) {
                                $rPeriod = \substr( $rPeriod, 1 );
                            }
                            $wDate2 = Util::durationStr2arr( $rPeriod );
                        }
                        elseif( 8 <= strlen( trim( $rPeriod ))) {      // text date ex. 2006-08-03 10:12:18
                            $wDate2 = Util::strDate2ArrayDate( $rPeriod, $parno );
                            unset( $wDate2[Util::$UNPARSEDTEXT] );
                        }
                        if(( 0 == $rpix ) && ( 0 == $rix )) {
                            if( isset( $wDate2[Util::$LCtz] ) &&
                                in_array( strtoupper( $wDate2[Util::$LCtz] ), $GMTUTCZARR )) {
                                $wDate2[Util::$LCtz] = Util::$Z;
                                $toZ                  = true;
                            }
                        }
                        else {
                            if( isset( $wDate[0][Util::$LCtz] ) &&
                                ( Util::$Z == $wDate[0][Util::$LCtz] ) &&
                                isset( $wDate2[Util::$LCYEAR] )) {
                                $wDate2[Util::$LCtz] = Util::$Z;
                            }
                            else {
                                unset( $wDate2[Util::$LCtz] );
                            }
                        }
                        if( $toZ && isset( $wDate2[Util::$LCYEAR] )) {
                            $wDate2[Util::$LCtz] = Util::$Z;
                        }
                        $wDate[] = $wDate2;
                    } // end foreach( $theRdate as $rix => $rPeriod )
                } // PERIOD end
                elseif( Util::isArrayTimestampDate( $theRdate )) {    // timestamp
                    if( isset( $theRdate[Util::$LCtz] ) && ! Util::isOffset( $theRdate[Util::$LCtz] )) {
                        if( isset( $input[Util::$LCparams][Util::$TZID] )) {
                            $theRdate[Util::$LCtz] = $input[Util::$LCparams][Util::$TZID];
                        }
                        else {
                            $input[Util::$LCparams][Util::$TZID] = $theRdate[Util::$LCtz];
                        }
                    }
                    $wDate = Util::timestamp2date( $theRdate, $parno );
                }
                else {                                                 // date[-time]
                    $wDate = Util::chkDateArr( $theRdate, $parno );
                    if( isset( $wDate[Util::$LCtz] ) && ( Util::$Z != $wDate[Util::$LCtz] ) && Util::isOffset( $wDate[Util::$LCtz] )) {
                        $wDate = Util::ensureArrDatetime( [Util::$LCvalue => $wDate], $wDate[Util::$LCtz], 7 );
                        unset( $wDate[Util::$UNPARSEDTEXT] );
                    }
                }
            } // end if( \is_array( $theRdate ))
            elseif( 8 <= strlen( trim( $theRdate ))) {                 // text date ex. 2006-08-03 10:12:18
                $wDate = Util::strDate2ArrayDate( $theRdate, $parno );
                unset( $wDate[Util::$UNPARSEDTEXT] );
                if( $toZ ) {
                    $wDate[Util::$LCtz] = Util::$Z;
                }
            }
            if( ! isset( $input[Util::$LCparams][Util::$VALUE] ) ||
                ( Util::$PERIOD != $input[Util::$LCparams][Util::$VALUE] )) { // no PERIOD
                if(( 0 == $rpix ) && ! $toZ ) {
                    $toZ = ( isset( $wDate[Util::$LCtz] ) &&
                        in_array( strtoupper( $wDate[Util::$LCtz] ), $GMTUTCZARR )) ? true : false;
                }
                if( $toZ ) {
                    $wDate[Util::$LCtz] = Util::$Z;
                }
                if( 3 == $parno ) {
                    unset( $wDate[Util::$LCHOUR], $wDate[Util::$LCMIN], $wDate[Util::$LCSEC], $wDate[Util::$LCtz] );
                }
                elseif( isset( $wDate[Util::$LCtz] )) {
                    $wDate[Util::$LCtz] = (string) $wDate[Util::$LCtz];
                }
                if(   isset( $input[Util::$LCparams][Util::$TZID] ) ||
                    ( isset( $input[Util::$LCvalue][0] ) &&
                  ( ! isset( $input[Util::$LCvalue][0][Util::$LCtz] )))) {
                    if( ! $toZ ) {
                        unset( $wDate[Util::$LCtz] );
                    }
                }
            } // end if
            $input[Util::$LCvalue][] = $wDate;
        } // end foreach( $rdates as $rpix => $theRdate )
        if( 3 == $parno ) {
            $input[Util::$LCparams][Util::$VALUE] = Util::$DATE;
            unset( $input[Util::$LCparams][Util::$TZID] );
        }
        if( $toZ ) {
            unset( $input[Util::$LCparams][Util::$TZID] );
        }
        return $input;
    }
}
