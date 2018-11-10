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

use Kigkonsult\Icalcreator\Vcalendar;
use Kigkonsult\Icalcreator\CalendarComponent;
use Kigkonsult\Icalcreator\VcalendarSortHandler;
use Kigkonsult\Icalcreator\IcaldateTime;
use DateTime;
use DateInterval;
use Exception;

/**
 * iCalcreator geo support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26 - 2018-11-10
 */
class UtilSelect
{
    /**
     * @const string  Vcalendar::selectComponents added x-property names
     */
    const X_CURRENT_DTSTART = 'X-CURRENT-DTSTART';
    const X_CURRENT_DTEND   = 'X-CURRENT-DTEND';
    const X_CURRENT_DUE     = 'X-CURRENT-DUE';
    const X_RECURRENCE      = 'X-RECURRENCE';
    const X_OCCURENCE       = 'X-OCCURENCE';

    /**
     * Return selected components from calendar on date or selectOption basis
     *
     * DTSTART MUST be set for every component.
     * No check of date.
     *
     * @param Vcalendar $calendar
     * @param mixed     $startY    (int) start Year,  default current Year
     *                              ALT. (obj) start date (datetime)
     *                              ALT. array selecOptions ( *[ <propName> => <uniqueValue> ] )
     * @param mixed     $startM    (int) start Month, default current Month
     *                              ALT. (obj) end date (datetime)
     * @param int       $startD    start Day,   default current Day
     * @param int       $endY      end   Year,  default $startY
     * @param int       $endM      end   Month, default $startM
     * @param int       $endD      end   Day,   default $startD
     * @param mixed     $cType     calendar component type(-s), default false=all else string/array type(-s)
     * @param bool      $flat      false (default) => output : array[Year][Month][Day][]
     *                             true            => output : array[] (ignores split)
     * @param bool      $any       true (default) - select component(-s) that occurs within period
     *                             false          - only component(-s) that starts within period
     * @param bool      $split     true (default) - one component copy every DAY it occurs during the
     *                             period (implies flat=false)
     *                             false          - one occurance of component only in output array
     * @return mixed  array on success, bool false on error
     * @static
     * @since 2.26 - 2018-11-10
     */
    public static function selectComponents(
        Vcalendar $calendar,
        $startY = null,
        $startM = null,
        $startD = null,
        $endY   = null,
        $endM   = null,
        $endD   = null,
        $cType  = null,
        $flat   = null,
        $any    = null,
        $split  = null
    ) {
        static $Y              = 'Y';
        static $M              = 'm';
        static $D              = 'd';
        static $STRTOLOWER     = 'strtolower';
        static $UCFIRST        = 'ucfirst';
        static $P1D            = 'P1D';
        static $DTENDEXIST     = 'dtendExist';
        static $DUEEXIST       = 'dueExist';
        static $DURATIONEXIST  = 'durationExist';
        static $ENDALLDAYEVENT = 'endAllDayEvent';
        static $MINUS1DAY      = '-1 day';
        static $RANGE          = 'RANGE';
        static $THISANDFUTURE  = 'THISANDFUTURE';
        static $YMDHIS2        = 'Y-m-d H:i:s';
        static $PRA            = '%a';
        static $YMD2           = 'Y-m-d';
        static $HIS            = '%02d%02d%02d';
        static $DAYOFDAYS      = 'day %d of %d';
        static $SORTER         = [ 'Kigkonsult\Icalcreator\VcalendarSortHandler', 'cmpfcn' ];
        /* check  if empty calendar */
        if( 1 > $calendar->countComponents()) {
            return false;
        }
        if( \is_array( $startY )) {
            return self::selectComponents2( $calendar, $startY );
        }
        /* check default dates */
        if(( $startY instanceof DateTime ) &&
           ( $startY instanceof DateTime )) {
            $endY   = $startM->format( $Y );
            $endM   = $startM->format( $M );
            $endD   = $startM->format( $D );
            $startD = $startY->format( $D );
            $startM = $startY->format( $M );
            $startY = $startY->format( $Y );
        }
        else {
            if( empty( $startY )) {
                $startY = date( $Y );
            }
            if( empty( $startM )) {
                $startM = date( $M );
            }
            if( empty( $startD )) {
                $startD = date( $D );
            }
            if( empty( $endY )) {
                $endY = $startY;
            }
            if( empty( $endM )) {
                $endM = $startM;
            }
            if( empty( $endD )) {
                $endD = $startD;
            }
        }
        /* check component types */
        if( empty( $cType )) {
            $cType = Util::$VCOMPS;
        }
        else {
            if( ! \is_array( $cType )) {
                $cType = [ $cType ];
            }
            $cType = \array_map( $UCFIRST, \array_map( $STRTOLOWER, $cType ));
            foreach( $cType as $cix => $theType ) {
                if( ! Util::isPropInList( $theType, Util::$VCOMPS )) {
                    $cType[$cix] = Vcalendar::VEVENT;
                }
            }
            $cType = \array_unique( $cType );
        }
        $flat  = ( \is_null( $flat ))  ? false : (bool) $flat; // defaults
        $any   = ( \is_null( $any ))   ? true  : (bool) $any;
        $split = ( \is_null( $split )) ? true  : (bool) $split;
        if(( false === $flat ) && ( false === $any )) { // invalid combination
            $split = false;
        }
        if(( true === $flat ) && ( true === $split )) { // invalid combination
            $split = false;
        }
        /* iterate components */
        $result = [];
        $calendar->sort( Util::$UID );
        $compUIDcmp   = null;
        $exdatelist   = $recurrIdList = [];
        try {
            $INTERVAL_P1D = new DateInterval( $P1D ); // todo error mgnt
        }
        catch( Exception $e ) {
            return false;
        }
        $cix          = -1;
        while( $component = $calendar->getComponent()) {
            $cix += 1;
            if( empty( $component )) {
                continue;
            }
            /* skip unvalid type components */
            if( ! Util::isCompInList( $component->compType, $cType )) {
                continue;
            }
            /* select start from dtstart or due if dtstart is missing */
            $prop = $component->getProperty( Util::$DTSTART, false, true );
            if( empty( $prop ) &&
                ( $component->compType == Vcalendar::VTODO ) &&
                ( false === ( $prop = $component->getProperty( Util::$DUE, false, true )))) {
                continue;
            }
            if( empty( $prop )) {
                continue;
            }
            /* get UID */
            $compUID = $component->getProperty( Util::$UID );
            if( $compUIDcmp != $compUID ) {
                $compUIDcmp = $compUID;
                $exdatelist = $recurrIdList = [];
            }
            $compStart = IcaldateTime::factory( $prop[Util::$LCvalue], $prop[Util::$LCparams], $prop[Util::$LCvalue] );
            $dtstartTz = $compStart->getTimezoneName();
            if( Util::isParamsValueSet( $prop, Util::$DATE )) {
                $compStartHis = null;
            }
            else {
                $his          = $compStart->getTime();
                $compStartHis = sprintf( $HIS, (int) $his[0], (int) $his[1], (int) $his[2] );
            }
            /* get end date from dtend/due/duration properties */
            $compEnd = null;
            if( false !== ( $prop = $component->getProperty( Util::$DTEND, false, true ))) {
                $compEnd                       = IcaldateTime::factory( $prop[Util::$LCvalue], $prop[Util::$LCparams],
                                                                        $prop[Util::$LCvalue], $dtstartTz
                );
                $compEnd->SCbools[$DTENDEXIST] = true;
            }
            if( empty( $prop ) &&
                ( $component->compType == Vcalendar::VTODO ) &&
                ( false !== ( $prop = $component->getProperty( Util::$DUE, false, true )))) {
                $compEnd                     = IcaldateTime::factory( $prop[Util::$LCvalue], $prop[Util::$LCparams],
                                                                      $prop[Util::$LCvalue], $dtstartTz
                );
                $compEnd->SCbools[$DUEEXIST] = true;
            }
            if( empty( $prop ) && // duration in dtend (array) format
                ( false !== ( $prop = $component->getProperty( Util::$DURATION, false, true, true )))) {
                $compEnd                          = IcaldateTime::factory( $prop[Util::$LCvalue],
                                                                           $prop[Util::$LCparams],
                                                                           $prop[Util::$LCvalue], $dtstartTz
                );
                $compEnd->SCbools[$DURATIONEXIST] = true;
            }
            if( ! empty( $prop ) && ! isset( $prop[Util::$LCvalue][Util::$LCHOUR] )) {
                /* a DTEND without time part denotes an end of an event that actually ends the day before,
                   for an all-day event DTSTART=20071201 DTEND=20071202, taking place 20071201!!! */
                $compEnd->SCbools[$ENDALLDAYEVENT] = true;
                $compEnd->modify( $MINUS1DAY );
                $compEnd->setTime( 23, 59, 59 );
            }
            unset( $prop );
            if( empty( $compEnd )) {
                $compDuration = null; // DateInterval: no duration
                $compEnd      = clone $compStart;
                $compEnd->setTime( 23, 59, 59 );            // 23:59:59 the same day as start
            }
            else {
                if( $compEnd->format( $YMD2 ) < $compStart->format( $YMD2 )) { // MUST be after start date!!
                    $compEnd = clone $compStart;
                    $compEnd->setTime( 23, 59, 59 );        // 23:59:59 the same day as start or ???
                }
                $compDuration = $compStart->diff( $compEnd ); // DateInterval
            }
            /* check recurrence-id (note, a missing sequence (expected here) is the same as sequence=0
               so don't test for sequence), to alter when hit in dtstart/recurlist */
            $recurrid = null;
            if( false !== ( $prop = $component->getProperty( Util::$RECURRENCE_ID, false, true ))) {
                $recurrid = IcaldateTime::factory( $prop[Util::$LCvalue], $prop[Util::$LCparams], $prop[Util::$LCvalue], $dtstartTz );
                $rangeSet =    ( isset( $prop[Util::$LCparams][$RANGE] ) &&
                    ( $THISANDFUTURE == $prop[Util::$LCparams][$RANGE] ))
                    ? true : false;
                $recurrIdList[$recurrid->key] = [
                    clone $compStart,
                    clone $compEnd,
                    $compDuration, // DateInterval
                    $rangeSet,
                    clone $component,
                ];        // change recur this day to new YmdHis/duration/range
                unset( $prop );
                continue; // ignore any other props in the recurrence_id component
            } // end recurrence-id/sequence test
            \ksort( $recurrIdList, SORT_STRING );
            $fcnStart = clone $compStart;
            $fcnStart->setDate((int) $startY, (int) $startM, (int) $startD );
            $fcnStart->setTime( 0, 0, 0 );
            $fcnEnd = clone $compEnd;
            $fcnEnd->setDate((int) $endY, (int) $endM, (int) $endD );
            $fcnEnd->setTime( 23, 59, 59 );
            /* make a list of optional exclude dates for component occurence from exrule and exdate */
            $workStart = clone $compStart;
            $duration  = ( ! empty( $compDuration )) ? $compDuration : $INTERVAL_P1D; // DateInterval
            $workStart->sub( $duration );
            $workEnd = clone $fcnEnd;
            $workEnd->add( $duration );
            self::getAllEXRULEdates( $component, $exdatelist,
                                     $dtstartTz, $compStart, $workStart, $workEnd,
                                     $compStartHis
            );
            self::getAllEXDATEdates( $component, $exdatelist, $dtstartTz );
            /* select only components within.. . */
            $xRecurrence = 1;
                           // (dt)start within the period
            if(( ! $any && self::inScope( $compStart, $fcnStart, $compStart, $fcnEnd, $compStart->dateFormat )) ||
                          // occurs within the period
                 ( $any && self::inScope( $fcnEnd, $compStart, $fcnStart, $compEnd, $compStart->dateFormat ))) {
                /* add the selected component (WITHIN valid dates) to output array */
                if( $flat ) { // any=true/false, ignores split
                    if( empty( $recurrid )) {
                        $result[$compUID] = clone $component;
                    }         // copy original to output (but not anyone with recurrence-id)
                }
                elseif( $split ) { // split the original component
                    if( $compStart->format( $YMDHIS2 ) < $fcnStart->format( $YMDHIS2 )) {
                        $rstart = clone $fcnStart;
                    }
                    else {
                        $rstart = clone $compStart;
                    }
                    if( $compEnd->format( $YMDHIS2 ) > $fcnEnd->format( $YMDHIS2 )) {
                        $rend = clone $fcnEnd;
                    }
                    else {
                        $rend = clone $compEnd;
                    }
                    if( ! isset( $exdatelist[$rstart->key] )) {     // not excluded in exrule/exdate
                        if( isset( $recurrIdList[$rstart->key] )) {   // change start day to new YmdHis/duration
                            $k        = $rstart->key;
                            $rstart   = clone $recurrIdList[$k][0];
                            $startHis = $rstart->getTime();
                            $rend     = clone $rstart;
                            if( ! empty( $recurrIdList[$k][2] )) { // DateInterval
                                $rend->add( $recurrIdList[$k][2] );
                            }
                            elseif( ! empty( $compDuration )) {    // DateInterval
                                $rend->add( $compDuration );
                            }
                            $endHis     = $rend->getTime();
                            $component2 = ( isset( $recurrIdList[$k][4] )) ? clone $recurrIdList[$k][4] : clone $component;
                        }
                        else {
                            $startHis   = $compStart->getTime();
                            $endHis     = $compEnd->getTime();
                            $component2 = clone $component;
                        }
                        $cnt           = 0; // exclude any recurrence START date, found in exdatelist or recurrIdList but accept the reccurence-id comp itself
                        $occurenceDays = 1 + (int) $rstart->diff( $rend )->format( $PRA
                            );  // count the days (incl start day)
                        while( $rstart->format( $YMD2 ) <= $rend->format( $YMD2 )) {
                            $cnt += 1;
                            if( 1 < $occurenceDays ) {
                                $component2->setProperty( self::X_OCCURENCE, sprintf( $DAYOFDAYS, $cnt, $occurenceDays ));
                            }
                            if( 1 < $cnt ) {
                                $rstart->setTime( 0, 0, 0 );
                            }
                            else {
                                $rstart->setTime( $startHis[0], $startHis[1], $startHis[2] );
                                $exdatelist[$rstart->key] = $compDuration; // make sure to exclude start day from the recurrence pattern // DateInterval
                            }
                            $component2->setProperty( self::X_CURRENT_DTSTART, $rstart->format( $compStart->dateFormat ));
                            $xY = (int) $rstart->format( $Y );
                            $xM = (int) $rstart->format( $M );
                            $xD = (int) $rstart->format( $D );
                            if( ! empty( $compDuration )) { // DateInterval
                                $propName = ( isset( $compEnd->SCbools[$DUEEXIST] )) ? self::X_CURRENT_DUE : self::X_CURRENT_DTEND;
                                if( $cnt < $occurenceDays ) {
                                    $rstart->setTime( 23, 59, 59 );
                                }
                                elseif(( $rstart->format( $YMD2 ) < $rend->format( $YMD2 )) &&
                                    ( '00' == $endHis[0] ) &&
                                    ( '00' == $endHis[1] ) &&
                                    ( '00' == $endHis[2] )) { // end exactly at midnight
                                    $rstart->setTime( 24, 0, 0 );
                                }
                                else {
                                    $rstart->setTime( $endHis[0], $endHis[1], $endHis[2] );
                                }
                                $component2->setProperty( $propName, $rstart->format( $compEnd->dateFormat ));
                            }
                            $result[$xY][$xM][$xD][$compUID] = clone $component2;    // copy to output
                            $rstart->add( $INTERVAL_P1D );
                        } // end while(( $rstart->format( 'Ymd' ) < $rend->format( 'Ymd' ))
                        unset( $cnt, $occurenceDays );
                    } // end if( ! isset( $exdatelist[$rstart->key] ))
                    unset( $rstart, $rend );
                } // end elseif( $split )   -  else use component date
                else { // !$flat && !$split, i.e. no flat array and DTSTART within period
                    if( isset( $recurrIdList[$compStart->key] )) {
                        $tstart     = clone $recurrIdList[$compStart->key][0];
                        $component2 = ( isset( $recurrIdList[$compStart->key][4] )) ? $recurrIdList[$compStart->key][4] : clone $component;
                    }
                    else {
                        $tstart     = clone $compStart;
                        $component2 = clone $component;
                    }
                    if( ! $any || ! isset( $exdatelist[$tstart->key] )) {  // exclude any recurrence date, found in exdatelist
                        $xY                              = (int) $tstart->format( $Y );
                        $xM                              = (int) $tstart->format( $M );
                        $xD                              = (int) $tstart->format( $D );
                        $result[$xY][$xM][$xD][$compUID] = clone $component2;      // copy to output
                    }
                    unset( $tstart );
                }
            } // end (dt)start within the period OR occurs within the period
            /* *************************************************************
               if 'any' components, check components with reccurrence rules, removing all excluding dates
               *********************************************************** */
            if( true === $any ) {
                $recurlist = [];
                /* make a list of optional repeating dates for component occurence, rrule, rdate */
                self::getAllRRULEdates( $component, $recurlist,
                                        $dtstartTz, $compStart, $workStart, $workEnd,
                                        $compStartHis, $exdatelist, $compDuration
                );
                $workStart = clone $fcnStart;
                $workStart->sub(( ! empty( $compDuration )) ? $compDuration : $INTERVAL_P1D );
                try {
                    self::getAllRDATEdates( $component, $recurlist,
                                            $dtstartTz, $workStart, $fcnEnd, $compStart->dateFormat,
                                            $exdatelist, $compStartHis, $compDuration
                    );
                }
                catch( Exception $e ){
                    return false;
                }
                unset( $workStart, $rend );
                foreach( $recurrIdList as $rKey => $rVal ) { // check for recurrence-id, i.e. alter recur Ymd[His] and duration
                    if( 3 < $rKey ) {
                        continue;
                    }
                    if( isset( $recurlist[$rKey] )) {
                        unset( $recurlist[$rKey] );
                        $recurlist[$rVal[0]->key] = ( ! empty( $rVal[2] )) ? $rVal[2] : $compDuration;  // DateInterval
                    }
                }
                ksort( $recurlist, SORT_STRING );
                /* output all remaining components in recurlist */
                if( 0 < count( $recurlist )) {
                    $component2 = clone $component;
                    $compUID    = $component2->getProperty( Util::$UID );
                    $workStart  = clone $fcnStart;
                    $workStart->sub(( ! empty( $compDuration )) ? $compDuration : $INTERVAL_P1D ); // DateInterval
                    $YmdOld = null;
                    foreach( $recurlist as $recurkey => $durationInterval ) {
                        if( $YmdOld == \substr( $recurkey, 0, 8
                            ))     // skip overlapping recur the same day, i.e. RDATE before RRULE
                        {
                            continue;
                        }
                        $YmdOld = \substr( $recurkey, 0, 8 );
                        $rstart = clone $compStart;
                        $rstart->setDate((int) \substr( $recurkey, 0, 4 ),
                                         (int) \substr( $recurkey, 4, 2 ),
                                         (int) \substr( $recurkey, 6, 2 )
                        );
                        $rstart->setTime((int) \substr( $recurkey, 8, 2 ),
                                         (int) \substr( $recurkey, 10, 2 ),
                                         (int) \substr( $recurkey, 12, 2 )
                        );
                        /* add recurring components within valid dates to output array, only start date set */
                        if( $flat ) {
                            if( ! isset( $result[$compUID] )) { // only one comp
                                $result[$compUID] = clone $component2;  // copy to output
                            }
                        }
                        /* add recurring components within valid dates to output array, split for each day */
                        elseif( $split ) {
                            /* check and alter current component to recurr-comp if YMD match */
                            $component3  = null;
                            $recurrFound = false;
                            foreach( $recurrIdList as $k => $v ) {
                                if( \substr( $k, 0, 8 ) == \substr( $recurkey, 0, 8 )) {
                                    $rstart            = clone $recurrIdList[$k][0];
                                    $durationInterval2 = ( ! empty( $recurrIdList[$k][2] )) ? $recurrIdList[$k][2] : null;  // DateInterval
                                    $component3        = clone $recurrIdList[$k][4];
                                    $recurrFound       = true;
                                    break;
                                }
                            }
                            if( ! $recurrFound ) {
                                $component3        = clone $component2;
                                $durationInterval2 = ( ! empty( $durationInterval )) ? $durationInterval : null;
                            }
                            $rend = clone $rstart;
                            if( ! empty( $durationInterval2 )) {
                                $rend->add( $durationInterval2 );
                            }
                            if( $rend->format( $YMD2 ) > $fcnEnd->format( $YMD2 )) {
                                $rend = clone $fcnEnd;
                            }
                            $endHis        = $rend->getTime();
                            $xRecurrence   += 1;
                            $cnt           = 0;
                            $occurenceDays = 1 + (int) $rstart->diff( $rend )->format( $PRA
                                );  // count the days (incl start day)
                            while( $rstart->format( $YMD2 ) <= $rend->format( $YMD2 )) {   // iterate.. .
                                $cnt += 1;
                                if( $rstart->format( $YMD2 ) < $fcnStart->format( $YMD2 )) { // date before dtstart
                                    $rstart->add( $INTERVAL_P1D ); // cycle rstart to dtstart
                                    $rstart->setTime( 0, 0, 0 );
                                    continue;
                                }
                                elseif( 2 == $cnt ) {
                                    $rstart->setTime( 0, 0, 0 );
                                }
                                $xY = (int) $rstart->format( $Y );
                                $xM = (int) $rstart->format( $M );
                                $xD = (int) $rstart->format( $D );
                                $component3->setProperty( self::X_RECURRENCE, $xRecurrence );
                                if( 1 < $occurenceDays ) {
                                    $component3->setProperty( self::X_OCCURENCE, sprintf( $DAYOFDAYS, $cnt, $occurenceDays ));
                                }
                                else {
                                    $component3->deleteProperty( self::X_OCCURENCE );
                                }
                                $component3->setProperty( self::X_CURRENT_DTSTART, $rstart->format( $compStart->dateFormat ));
                                $propName = ( isset( $compEnd->SCbools[$DUEEXIST] )) ? self::X_CURRENT_DUE : self::X_CURRENT_DTEND;
                                if( ! empty( $durationInterval2 )) {
                                    if( $cnt < $occurenceDays ) {
                                        $rstart->setTime( 23, 59, 59 );
                                    }
                                    elseif(( $rstart->format( $YMD2 ) < $rend->format( $YMD2 )) &&
                                        ( '00' == $endHis[0] ) && ( '00' == $endHis[1] ) && ( '00' == $endHis[2] )) // end exactly at midnight
                                    {
                                        $rstart->setTime( 24, 0, 0 );
                                    }
                                    else {
                                        $rstart->setTime( $endHis[0], $endHis[1], $endHis[2] );
                                    }
                                    $component3->setProperty( $propName, $rstart->format( $compEnd->dateFormat ));
                                }
                                else {
                                    $component3->deleteProperty( $propName );
                                }
                                $result[$xY][$xM][$xD][$compUID] = clone $component3;     // copy to output
                                $rstart->add( $INTERVAL_P1D );
                            } // end while( $rstart->format( 'Ymd' ) <= $rend->format( 'Ymd' ))
                            unset( $rstart, $rend );
                        } // end elseif( $split )
                        elseif( $rstart->format( $YMD2 ) >= $fcnStart->format( $YMD2 )) {
                            $xRecurrence += 1;                                            // date within period, flat=false && split=false => one comp every recur startdate
                            $component2->setProperty( self::X_RECURRENCE, $xRecurrence );
                            $component2->setProperty( self::X_CURRENT_DTSTART, $rstart->format( $compStart->dateFormat ));
                            $propName = ( isset( $compEnd->SCbools[$DUEEXIST] )) ? self::X_CURRENT_DUE : self::X_CURRENT_DTEND;
                            if( ! empty( $durationInterval )) {
                                $rstart->add( $durationInterval );
                                $component2->setProperty( $propName, $rstart->format( $compEnd->dateFormat ));
                            }
                            else {
                                $component2->deleteProperty( $propName );
                            }
                            $xY                              = (int) $rstart->format( $Y );
                            $xM                              = (int) $rstart->format( $M );
                            $xD                              = (int) $rstart->format( $D );
                            $result[$xY][$xM][$xD][$compUID] = clone $component2; // copy to output
                        } // end elseif( $rstart >= $fcnStart )
                        unset( $rstart );
                    } // end foreach( $recurlist as $recurkey => $durationInterval )
                    unset( $component2, $component3, $xRecurrence, $compUID, $workStart, $rstart );
                } // end if( 0 < count( $recurlist ))
            } // end if( true === $any )
            unset( $component );
        } // end while( $component = $calendar->getComponent())
        if( 0 >= count( $result )) {
            return false;
        }
        elseif( ! $flat ) {
            foreach( $result as $y => $yList ) {
                foreach( $yList as $m => $mList ) {
                    foreach( $mList as $d => $dList ) {
                        if( empty( $dList )) {
                            unset( $result[$y][$m][$d] );
                        }
                        else {
                            $result[$y][$m][$d] = array_values( $dList ); // skip tricky UID-index
                            if( 1 < count( $result[$y][$m][$d] )) {
                                foreach( $result[$y][$m][$d] as $cix => $d2List ) { // sort
                                    VcalendarSortHandler::setSortArgs( $result[$y][$m][$d][$cix] );
                                }
                                \usort( $result[$y][$m][$d], $SORTER );
                            }
                        }
                    } // end foreach( $mList as $d => $dList )
                    if( empty( $result[$y][$m] )) {
                        unset( $result[$y][$m] );
                    }
                    else {
                        \ksort( $result[$y][$m] );
                    }
                } // end foreach( $yList as $m => $mList )
                if( empty( $result[$y] )) {
                    unset( $result[$y] );
                }
                else {
                    \ksort( $result[$y] );
                }
            } // end foreach(  $result as $y => $yList )
            if( empty( $result )) {
                unset( $result );
            }
            else {
                \ksort( $result );
            }
        } // end elseif( !$flat )
        return $result;
    }

    /**
     * Return bool true if dates are in scope
     *
     * @param IcaldateTime $start
     * @param IcaldateTime $scopeStart
     * @param IcaldateTime $end
     * @param IcaldateTime $scopeEnd
     * @param string       $format
     * @return bool
     * @access private
     * @static
     */
    private static function inScope(
        IcaldateTime $start,
        IcaldateTime $scopeStart,
        IcaldateTime $end,
        IcaldateTime $scopeEnd,
        $format
    ) {
        return (( $start->format( $format ) >= $scopeStart->format( $format )) &&
                  ( $end->format( $format ) <= $scopeEnd->format( $format )));
    }

    /**
     * Get all EXRULE dates (multiple values allowed)
     *
     * @param CalendarComponent $component
     * @param array             $exdatelist
     * @param string            $dtstartTz
     * @param IcaldateTime      $compStart
     * @param IcaldateTime      $workStart
     * @param IcaldateTime      $workEnd
     * @param string            $compStartHis
     */
    private static function getAllEXRULEdates(
        CalendarComponent $component,
                  array & $exdatelist,
                          $dtstartTz,
             IcaldateTime $compStart,
             IcaldateTime $workStart,
             IcaldateTime $workEnd,
                          $compStartHis
    ) {
        while( false !== ( $prop = $component->getProperty( Util::$EXRULE ))) {
            $exdatelist2 = [];
            if( isset( $prop[Util::$UNTIL][Util::$LCHOUR] )) { // convert UNTIL date to DTSTART timezone
                $until = IcaldateTime::factory( $prop[Util::$UNTIL], [ Util::$TZID => Util::$UTC ], null, $dtstartTz );
                $until = $until->format();
                Util::strDate2arr( $until );
                $prop[Util::$UNTIL] = $until;
            }
            UtilRecur::recur2date( $exdatelist2, $prop, $compStart, $workStart, $workEnd );
            foreach( $exdatelist2 as $k => $v ) { // point out exact every excluded ocurrence (incl. opt. His)
                $exdatelist[$k . $compStartHis] = $v;
            }
            unset( $until, $exdatelist2 );
        }
    }

    /**
     * Get all EXDATE dates (multiple values allowed)
     *
     * @param CalendarComponent $component
     * @param array             $exdatelist
     * @param string            $dtstartTz
     */
    private static function getAllEXDATEdates(
        CalendarComponent $component,
        array & $exdatelist,
        $dtstartTz
    ) {
        while( false !== ( $prop = $component->getProperty( Util::$EXDATE, false, true ))) {
            foreach( $prop[Util::$LCvalue] as $exdate ) {
                $exdate = IcaldateTime::factory( $exdate, $prop[Util::$LCparams], $exdate, $dtstartTz );
                $exdatelist[$exdate->key] = true;
            } // end - foreach( $exdate as $exdate )
        }
    }

    /**
     * Update $recurlist all RRULE dates (multiple values allowed)
     *
     * @param CalendarComponent $component
     * @param array             $recurlist
     * @param string            $dtstartTz
     * @param IcaldateTime      $compStart
     * @param IcaldateTime      $workStart
     * @param IcaldateTime      $workEnd
     * @param string            $compStartHis
     * @param array             $exdatelist
     * @param DateInterval      $compDuration
     */
    private static function getAllRRULEdates(
        CalendarComponent $component,
                  array & $recurlist,
                          $dtstartTz,
             IcaldateTime $compStart,
             IcaldateTime $workStart,
             IcaldateTime $workEnd,
                          $compStartHis,
                    array $exdatelist,
             DateInterval $compDuration = null
    ) {
        while( false !== ( $prop = $component->getProperty( Util::$RRULE ))) {
            $recurlist2 = [];
            if( isset( $prop[Util::$UNTIL][Util::$LCHOUR] )) { // convert RRULE['UNTIL'] to the same timezone as DTSTART !!
                $until = IcaldateTime::factory( $prop[Util::$UNTIL], [ Util::$TZID => Util::$UTC ], null, $dtstartTz );
                $until = $until->format();
                Util::strDate2arr( $until );
                $prop[Util::$UNTIL] = $until;
            }
            UtilRecur::recur2date( $recurlist2, $prop, $compStart, $workStart, $workEnd );
            foreach( $recurlist2 as $recurkey => $recurvalue ) { // recurkey=Ymd
                $recurkey .= $compStartHis;                        // add opt His
                if( ! isset( $exdatelist[$recurkey] )) {
                    $recurlist[$recurkey] = $compDuration;
                }           // DateInterval or false
            }
            unset( $prop, $until, $recurlist2 );
        }
    }

    /**
     * Update $recurlist with RDATE dates (multiple values allowed)
     *
     * @param CalendarComponent $component
     * @param array             $recurlist
     * @param string            $dtstartTz
     * @param IcaldateTime      $workStart
     * @param IcaldateTime      $fcnEnd
     * @param string            $format
     * @param array             $exdatelist
     * @param string            $compStartHis
     * @param DateInterval      $compDuration
     * @throws Exception
     */
    private static function getAllRDATEdates(
        CalendarComponent $component,
                  array & $recurlist,
                          $dtstartTz,
             IcaldateTime $workStart,
             IcaldateTime $fcnEnd,
                          $format,
                    array $exdatelist,
                          $compStartHis,
             DateInterval $compDuration = null
    ) {
        while( false !== ( $prop = $component->getProperty( Util::$RDATE, false, true ))) {
            $rdateFmt = ( isset( $prop[Util::$LCparams][Util::$VALUE] ))
                ? $prop[Util::$LCparams][Util::$VALUE]
                : Util::$DATE_TIME;
            $params   = $prop[Util::$LCparams];
            $prop     = $prop[Util::$LCvalue];
            foreach( $prop as $rix => $theRdate ) {
                if( Util::$PERIOD == $rdateFmt ) {            // all days within PERIOD
                    $rdate = IcaldateTime::factory( $theRdate[0], $params, $theRdate[0], $dtstartTz );
                    if( ! self::inScope( $rdate, $workStart, $rdate, $fcnEnd, $format ) ||
                        isset( $exdatelist[$rdate->key] )) {
                        continue;
                    }
                    if( isset( $theRdate[1][Util::$LCYEAR] )) { // date-date period end
                        $recurlist[$rdate->key] = $rdate->diff(
                            IcaldateTime::factory( $theRdate[1], $params, $theRdate[1], $dtstartTz )
                        );
                    }
                    else {                                      // period duration
                        try {
                            $recurlist[$rdate->key] = new DateInterval( Util::duration2str( $theRdate[1] )
                            );
                        }
                        catch( Exception $e ) { // todo better error mgnt
                            throw $e;
                        }
                    }
                } // end if( Util::$PERIOD == $rdateFmt )
                elseif( Util::$DATE == $rdateFmt ) {          // single recurrence, DATE
                    $rdate = IcaldateTime::factory( $theRdate,
                                                    array_merge( $params, [ Util::$TZID => $dtstartTz ] ),
                                                    null,
                                                    $dtstartTz
                    );
                    if( self::inScope( $rdate, $workStart, $rdate, $fcnEnd, $format ) &&
                        ! isset( $exdatelist[$rdate->key] )) // set start date for recurrence + DateInterval/false (+opt His)
                    {
                        $recurlist[$rdate->key . $compStartHis] = $compDuration;
                    }
                } // end DATE
                else { // start DATETIME
                    $rdate = IcaldateTime::factory( $theRdate, $params, $theRdate, $dtstartTz );
                    if( self::inScope( $rdate, $workStart, $rdate, $fcnEnd, $format ) &&
                        ! isset( $exdatelist[$rdate->key] )) {
                        $recurlist[$rdate->key] = $compDuration;
                    }  // set start datetime for recurrence DateInterval/false
                } // end DATETIME
            } // end foreach( $prop as $rix => $theRdate )
        }  // end while( false !== ( $prop = $component->getProperty( Util::$RDATE, false, true )))
    }

    /**
     * Return array with selected components values from calendar based on specific property value(-s)
     *
     * @param Vcalendar $calendar
     * @param array     $selectOptions (string) key => (mixed) value, (key=propertyName)
     * @return array
     * @access private
     * @static
     */
    private static function selectComponents2(
        Vcalendar $calendar,
        array $selectOptions
    ) {
        $output        = [];
        $selectOptions = \array_change_key_case( $selectOptions, CASE_UPPER );
        while( $component3 = $calendar->getComponent()) {
            if( empty( $component3 )) {
                continue;
            }
            if( ! Util::isCompInList( $component3->compType, Util::$VCOMPS )) {
                continue;
            }
            $uid = $component3->getProperty( Util::$UID );
            foreach( $selectOptions as $propName => $propValue ) {
                if( ! Util::isPropInList( $propName, Util::$OTHERPROPS )) {
                    continue;
                }
                if( ! \is_array( $propValue )) {
                    $propValue = [ $propValue ];
                }
                if(( Util::$UID == $propName ) && \in_array( $uid, $propValue )) {
                    $output[$uid][] = $component3;
                    continue;
                }
                elseif( Util::isPropInList( $propName, Util::$MPROPS1 )) {
                    $propValues = [];
                    $component3->getProperties( $propName, $propValues );
                    $propValues = \array_keys( $propValues );
                    foreach( $propValue as $theValue ) {
                        if( \in_array( $theValue, $propValues )) { //  && ! isset( $output[$uid] )) {
                            $output[$uid][] = $component3;
                            break;
                        }
                    }
                    continue;
                } // end   elseif( // multiple occurrence?
                elseif( false === ( $d = $component3->getProperty( $propName ))) { // single occurrence
                    continue;
                }
                if( \is_array( $d )) {
                    foreach( $d as $part ) {
                        if( \in_array( $part, $propValue ) && ! isset( $output[$uid] )) {
                            $output[$uid][] = $component3;
                        }
                    }
                }
                elseif(( Util::$SUMMARY == $propName ) && ! isset( $output[$uid] )) {
                    foreach( $propValue as $pval ) {
                        if( false !== \stripos( $d, $pval )) {
                            $output[$uid][] = $component3;
                            break;
                        }
                    }
                }
                elseif( \in_array( $d, $propValue ) && ! isset( $output[$uid] )) {
                    $output[$uid][] = $component3;
                }
            } // end foreach( $selectOptions as $propName => $propValue )
        } // end while( $component3 = $calendar->getComponent()) {
        if( ! empty( $output )) {
            \ksort( $output ); // uid order
            $output2 = [];
            foreach( $output as $uid => $uList ) {
                foreach( $uList as $cx => $uValue ) {
                    $output2[] = $uValue;
                }
            }
            $output = $output2;
        }
        return $output;
    }
}
