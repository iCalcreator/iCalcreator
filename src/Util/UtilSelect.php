<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.26.8
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

namespace Kigkonsult\Icalcreator\Util;

use Kigkonsult\Icalcreator\Vcalendar;
use Kigkonsult\Icalcreator\CalendarComponent;
use DateTime;
use DateInterval;
use Exception;

use function array_change_key_case;
use function array_keys;
use function array_map;
use function array_unique;
use function count;
use function in_array;
use function is_array;
use function is_null;
use function ksort;
use function sprintf;
use function stripos;
use function substr;
use function usort;

/**
 * iCalcreator geo support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.26.2 - 2018-11-15
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
     * @var string  component end date properties
     * @access private
     * @static
     */
    private static $DTENDEXIST     = 'dtendExist';
    private static $DUEEXIST       = 'dueExist';
    private static $DURATIONEXIST  = 'durationExist';
    private static $ENDALLDAYEVENT = 'endAllDayEvent';

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
     * @since 2.26.5 - 2018-11-16
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
        static $P1D            = 'P1D';
        static $RANGE          = 'RANGE';
        static $THISANDFUTURE  = 'THISANDFUTURE';
        static $YMDHIS2        = 'Y-m-d H:i:s';
        static $PRA            = '%a';
        static $YMDn           = 'Ymd';
        static $HIS            = '%02d%02d%02d';
        static $DAYOFDAYS      = 'day %d of %d';
        static $SORTER         = [ 'Kigkonsult\Icalcreator\Util\VcalendarSortHandler', 'cmpfcn' ];
        /* check  if empty calendar */
        if( 1 > $calendar->countComponents()) {
            return false;
        }
        if( is_array( $startY )) {
            return UtilSelect::selectComponents2( $calendar, $startY );
        }
        /* check default dates */
        UtilSelect::assertDateArguments($startY, $startM, $startD, $endY, $endM, $endD );
        /* check component types */
        $cType = UtilSelect::assertComponentTypes( $cType );
        /* check bool args */
        UtilSelect:: assertBoolArguments( $flat, $any, $split );
        /* iterate components */
        $result     = [];
        $calendar->sort( Util::$UID );
        $compUIDold = null;
        $exdateList = $recurrIdList = [];
        try {
            $INTERVAL_P1D = new DateInterval( $P1D );
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
            if( empty( $prop )) {
                if( $component->compType == Vcalendar::VTODO ) {
                    if( false === ( $prop = $component->getProperty( Util::$DUE, false, true ))) {
                        continue;
                    }
                }
                else
                    continue;
            }
            $compStart = IcaldateTime::factory( $prop[Util::$LCvalue], $prop[Util::$LCparams], $prop[Util::$LCvalue] );
            $dtstartTz = $compStart->getTimezoneName();
            if( Util::isParamsValueSet( $prop, Util::$DATE )) {
                $compStartHis = null;
            }
            else {
                $his          = $compStart->getTime();
                $compStartHis = sprintf( $HIS, $his[0], $his[1], $his[2] );
            }
            /* get end date from dtend/due/duration properties */
            $compEnd = UtilSelect::getCompEndDate( $component, $dtstartTz );
            if( empty( $compEnd )) {
                $compDuration = null; // DateInterval: no duration
                $compEnd      = clone $compStart;
                $compEnd->setTime( 23, 59, 59 );            // 23:59:59 the same day as start
            }
            else {
                if( $compEnd->format( $YMDn ) < $compStart->format( $YMDn )) { // MUST be after start date!!
                    $compEnd = $compStart->getClone();
                    $compEnd->setTime( 23, 59, 59 );        // 23:59:59 the same day as start or ???
                }
                $compDuration = $compStart->diff( $compEnd ); // DateInterval
            }
            /* get UID */
            $compUID = $component->getProperty( Util::$UID );
            if( $compUIDold != $compUID ) {
                $compUIDold = $compUID;
                $exdateList = $recurrIdList = [];
            }
            /**
             * Component with recurrence-id sorted before any rDate/rRule comp
             * (note, a missing sequence (expected here) is the same as sequence=0 so don't test for sequence),
             * to alter date(time) when found in dtstart/recurlist,
             * Highest sequence always last, will replace any previous
             */
            $recurrId = null;
            if( false !== ( $prop = $component->getProperty( Util::$RECURRENCE_ID, false, true ))) {
                $recurrId = IcaldateTime::factory(
                    $prop[Util::$LCvalue],
                    $prop[Util::$LCparams],
                    $prop[Util::$LCvalue],
                    $dtstartTz
                );
                $rangeSet =    ( isset( $prop[Util::$LCparams][$RANGE] ) &&
                    ( $THISANDFUTURE == $prop[Util::$LCparams][$RANGE] ))
                    ? true : false;
                $recurrIdList[$recurrId->key] = [
                    $compStart->getClone(),
                    $compEnd->getClone(),
                    $compDuration, // DateInterval
                    $rangeSet,
                    clone $component,
                ];        // change recur this day to new YmdHis/duration/range
                continue; // ignore any other props in the recurrence_id component
            } // end recurrence-id/sequence test
            ksort( $recurrIdList, SORT_STRING );
            UtilSelect::updateRecurrIdComps( $component, $recurrIdList );
            /* prepare */
            $fcnStart = $compStart->getClone();
            $fcnStart->setDate((int) $startY, (int) $startM, (int) $startD );
            $fcnStart->setTime( 0, 0, 0 );
            $fcnEnd = $compEnd->getClone();
            $fcnEnd->setDate((int) $endY, (int) $endM, (int) $endD );
            $fcnEnd->setTime( 23, 59, 59 );
            /* make a list of optional exclude dates for component occurence from exrule and exdate */
            $workStart = $compStart->getClone();
            $duration  = ( ! empty( $compDuration )) ? $compDuration : $INTERVAL_P1D; // DateInterval
            $workStart->sub( $duration );
            $workEnd = $fcnEnd->getClone();
            $workEnd->add( $duration );
            /* fetch all excludes */
            UtilSelect::getAllEXRULEdates(
                $component, $exdateList,
                $dtstartTz, $compStart, $workStart, $workEnd,
                $compStartHis
            );
            UtilSelect::getAllEXDATEdates( $component, $exdateList, $dtstartTz );
            /* select only components within.. . */
            $xRecurrence = 1;
                           // (dt)start within the period
            if(( ! $any && UtilSelect::inScope( $compStart, $fcnStart, $compStart, $fcnEnd, $compStart->dateFormat )) ||
                          // occurs within the period
                 ( $any && UtilSelect::inScope( $fcnEnd, $compStart, $fcnStart, $compEnd, $compStart->dateFormat ))) {
                /* add the selected component (WITHIN valid dates) to output array */
                if( $flat ) { // any=true/false, ignores split
                    if( empty( $recurrId )) {
                        $result[$compUID] = clone $component;
                    }         // copy original to output (but not anyone with recurrence-id)
                }
                elseif( $split ) { // split the original component
                    $rStart = ( $compStart->format( $YMDHIS2 ) < $fcnStart->format( $YMDHIS2 )) 
                        ? $fcnStart->getClone() : $compStart->getClone();
                    $rEnd = ( $compEnd->format( $YMDHIS2 ) > $fcnEnd->format( $YMDHIS2 ))
                        ? $fcnEnd->getClone()   : $compEnd->getClone();
                    if( ! isset( $exdateList[$rStart->key] )) {      // not excluded in exrule/exdate
                        if( isset( $recurrIdList[$rStart->key] )) {  // change start day to new YmdHis/duration
                            $k        = $rStart->key;
                            $rStart   = $recurrIdList[$k][0]->getClone(); // IcaldateTime
                            $startHis = $rStart->getTime();
                            $rEnd     = $rStart->getClone();
                            if( ! empty( $recurrIdList[$k][2] )) { // DateInterval
                                $rEnd->add( $recurrIdList[$k][2] );
                            }
                            elseif( ! empty( $compDuration )) {    // DateInterval
                                $rEnd->add( $compDuration );
                            }
                            $endHis     = $rEnd->getTime();
                            $component2 = ( isset( $recurrIdList[$k][4] )) 
                                ? clone $recurrIdList[$k][4] : clone $component;
                        }
                        else {
                            $startHis   = $compStart->getTime();
                            $endHis     = $compEnd->getTime();
                            $component2 = clone $component;
                        }
                        $cnt  = 0;
                        // exclude any recurrence START date, found in exdatelist or recurrIdList
                        // but accept the reccurence-id comp itself
                        //     count the days (incl start day)
                        $occurenceDays = 1 + (int) $rStart->diff( $rEnd )->format( $PRA );
                        while( $rStart->format( $YMDn ) <= $rEnd->format( $YMDn )) {
                            $cnt += 1;
                            if( 1 < $occurenceDays ) {
                                $component2->setProperty(
                                    UtilSelect::X_OCCURENCE,
                                    sprintf( $DAYOFDAYS, $cnt, $occurenceDays )
                                );
                            }
                            if( 1 < $cnt ) {
                                $rStart->setTime( 0, 0, 0 );
                            }
                            else {
                                // make sure to exclude start day from the recurrence pattern
                                $rStart->setTime( $startHis[0], $startHis[1], $startHis[2] );
                                $exdateList[$rStart->key] = $compDuration; // DateInterval
                            }
                            $component2->setProperty( 
                                UtilSelect::X_CURRENT_DTSTART,
                                $rStart->format( $compStart->dateFormat )
                            );
                            list( $xY, $xM, $xD ) = UtilSelect::getArrayYMDkeys( $rStart );
                            if( ! empty( $compDuration )) { // DateInterval
                                $propName = ( isset( $compEnd->SCbools[UtilSelect::$DUEEXIST] ))
                                    ? UtilSelect::X_CURRENT_DUE : UtilSelect::X_CURRENT_DTEND;
                                UtilSelect::setDurationEndTime( $rStart, $rEnd, $cnt, $occurenceDays, $endHis );
                                $component2->setProperty( $propName, $rStart->format( $compEnd->dateFormat ));
                            }
                            $result[$xY][$xM][$xD][$compUID] = clone $component2;    // copy to output
                            $rStart->add( $INTERVAL_P1D );
                        } // end while(( $rStart->format( 'Ymd' ) < $rEnd->format( 'Ymd' ))
                    } // end if( ! isset( $exdateList[$rStart->key] ))
                } // end elseif( $split )   -  else use component date
                else { // !$flat && !$split, i.e. no flat array and DTSTART within period
                    if( isset( $recurrIdList[$compStart->key] )) {
                        $rStart     = $recurrIdList[$compStart->key][0]->getClone();
                        $component2 = ( isset( $recurrIdList[$compStart->key][4] )) 
                            ? $recurrIdList[$compStart->key][4] : clone $component;
                    }
                    else {
                        $rStart     = $compStart->getClone();
                        $component2 = clone $component;
                    }
                    if( ! $any || ! isset( $exdateList[$rStart->key] )) {
                        // exclude any recurrence date, found in exdatelist
                        list( $xY, $xM, $xD ) = UtilSelect::getArrayYMDkeys( $rStart );
                        $result[$xY][$xM][$xD][$compUID] = clone $component2; // copy to output
                    }
                } // end else
            } // end (dt)start within the period OR occurs within the period
            /* *************************************************************
               if 'any' components, check components with reccurrence rules, removing all excluding dates
               *********************************************************** */
            if( true === $any ) {
                $recurList = [];
                /* make a list of optional repeating dates for component occurence, rrule, rdate */
                UtilSelect::getAllRRULEdates(
                    $component, $recurList,
                    $dtstartTz, $compStart, $workStart, $workEnd,
                    $compStartHis, $exdateList, $compDuration
                );
                $workStart = $fcnStart->getClone();
                $workStart->sub(( ! empty( $compDuration )) ? $compDuration : $INTERVAL_P1D );
                try {
                    UtilSelect::getAllRDATEdates(
                        $component, $recurList,
                        $dtstartTz, $workStart, $fcnEnd, $compStart->dateFormat,
                        $exdateList, $compStartHis, $compDuration
                    );
                }
                catch( Exception $e ){
                    return false;
                }
                unset( $workStart, $rEnd );
                // check for recurrence-id, i.e. alter recur Ymd[His] and duration
                foreach( $recurrIdList as $rKey => $rVal ) {
                    if( isset( $recurList[$rKey] )) {
                        unset( $recurList[$rKey] );
                        $recurList[$rVal[0]->key] = ( ! empty( $rVal[2] )) ? $rVal[2] : $compDuration;  // DateInterval
                    }
                }
                ksort( $recurList, SORT_STRING );
                /* output all remaining components in recurlist */
                if( 0 < count( $recurList )) {
                    $component2 = clone $component;
                    $compUID    = $component2->getProperty( Util::$UID );
                    $workStart  = $fcnStart->getClone();
                    $workStart->sub(( ! empty( $compDuration )) ? $compDuration : $INTERVAL_P1D );// DateInterval
                    $YmdOld = null;
                    foreach( $recurList as $recurKey => $durationInterval ) {
                        $recurKeyYmd = substr( $recurKey, 0, 8 );
                        if( $YmdOld == $recurKeyYmd ) {
                            continue; // skip overlapping recur the same day, i.e. RDATE before RRULE
                        }
                        $YmdOld = $recurKeyYmd;
                        $rStart = $compStart->getClone();
                        $rStart->setDateTimeFromString( $recurKey );
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
                                if( substr( $k, 0, 8 ) == $recurKeyYmd ) {
                                    $rStart            = $recurrIdList[$k][0]->getClone();
                                    $durationInterval2 = ( ! empty( $recurrIdList[$k][2] ))
                                        ? $recurrIdList[$k][2] : null;  // DateInterval
                                    $component3        = clone $recurrIdList[$k][4];
                                    $recurrFound       = true;
                                    break;
                                }
                            }
                            if( ! $recurrFound ) {
                                $component3        = clone $component2;
                                $durationInterval2 = ( ! empty( $durationInterval )) ? $durationInterval : null;
                            }
                            $rEnd = $rStart->getClone();
                            if( ! empty( $durationInterval2 )) {
                                $rEnd->add( $durationInterval2 );
                            }
                            if( $rEnd->format( $YMDn ) > $fcnEnd->format( $YMDn )) {
                                $rEnd = clone $fcnEnd;
                            }
                            $endHis        = $rEnd->getTime();
                            $xRecurrence   += 1;
                            $cnt           = 0;
                            // count the days (incl start day)
                            $occurenceDays = 1 + (int) $rStart->diff( $rEnd )->format( $PRA );
                            while( $rStart->format( $YMDn ) <= $rEnd->format( $YMDn )) {   // iterate.. .
                                $cnt += 1;
                                if( $rStart->format( $YMDn ) < $fcnStart->format( $YMDn )) { // date before dtstart
                                    $rStart->add( $INTERVAL_P1D ); // cycle rstart to dtstart
                                    $rStart->setTime( 0, 0, 0 );
                                    continue;
                                }
                                elseif( 2 == $cnt ) {
                                    $rStart->setTime( 0, 0, 0 );
                                }
                                list( $xY, $xM, $xD ) = UtilSelect::getArrayYMDkeys( $rStart );
                                $component3->setProperty( UtilSelect::X_RECURRENCE, $xRecurrence );
                                if( 1 < $occurenceDays ) {
                                    $component3->setProperty(
                                        UtilSelect::X_OCCURENCE,
                                        sprintf( $DAYOFDAYS, $cnt, $occurenceDays )
                                    );
                                }
                                else {
                                    $component3->deleteProperty( UtilSelect::X_OCCURENCE );
                                }
                                $component3->setProperty(
                                    UtilSelect::X_CURRENT_DTSTART,
                                    $rStart->format( $compStart->dateFormat )
                                );
                                $propName = ( isset( $compEnd->SCbools[UtilSelect::$DUEEXIST] ))
                                    ? UtilSelect::X_CURRENT_DUE : UtilSelect::X_CURRENT_DTEND;
                                if( ! empty( $durationInterval2 )) {
                                    UtilSelect::setDurationEndTime( $rStart, $rEnd, $cnt, $occurenceDays, $endHis );
                                    $component3->setProperty( $propName, $rStart->format( $compEnd->dateFormat ));
                                }
                                else {
                                    $component3->deleteProperty( $propName );
                                }
                                $result[$xY][$xM][$xD][$compUID] = clone $component3;     // copy to output
                                $rStart->add( $INTERVAL_P1D );
                            } // end while( $rStart->format( 'Ymd' ) <= $rEnd->format( 'Ymd' ))
                            unset( $rStart, $rEnd );
                        } // end elseif( $split )
                        elseif( $rStart->format( $YMDn ) >= $fcnStart->format( $YMDn )) {
                            // date within period, flat=false && split=false => one comp every recur startdate
                            $xRecurrence += 1;
                            $component2->setProperty( UtilSelect::X_RECURRENCE, $xRecurrence );
                            $component2->setProperty(
                                UtilSelect::X_CURRENT_DTSTART,
                                $rStart->format( $compStart->dateFormat )
                            );
                            $propName = ( isset( $compEnd->SCbools[UtilSelect::$DUEEXIST] ))
                                ? UtilSelect::X_CURRENT_DUE : UtilSelect::X_CURRENT_DTEND;
                            if( ! empty( $durationInterval )) {
                                $rStart->add( $durationInterval );
                                $component2->setProperty( $propName, $rStart->format( $compEnd->dateFormat ));
                            }
                            else {
                                $component2->deleteProperty( $propName );
                            }
                            list( $xY, $xM, $xD ) = UtilSelect::getArrayYMDkeys( $rStart );
                            $result[$xY][$xM][$xD][$compUID] = clone $component2; // copy to output
                        } // end elseif( $rStart >= $fcnStart )
                    } // end foreach( $recurList as $recurKey => $durationInterval )
                } // end if( 0 < count( $recurList ))
            } // end if( true === $any )
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
                                usort( $result[$y][$m][$d], $SORTER );
                            }
                        }
                    } // end foreach( $mList as $d => $dList )
                    if( empty( $result[$y][$m] )) {
                        unset( $result[$y][$m] );
                    }
                    else {
                        ksort( $result[$y][$m] );
                    }
                } // end foreach( $yList as $m => $mList )
                if( empty( $result[$y] )) {
                    unset( $result[$y] );
                }
                else {
                    ksort( $result[$y] );
                }
            } // end foreach(  $result as $y => $yList )
            if( empty( $result )) {
                unset( $result );
            }
            else {
                ksort( $result );
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
     * @param array             $exdateList
     * @param string            $dtstartTz
     * @param IcaldateTime      $compStart
     * @param IcaldateTime      $workStart
     * @param IcaldateTime      $workEnd
     * @param string            $compStartHis
     */
    private static function getAllEXRULEdates(
        CalendarComponent $component,
                  array & $exdateList,
                          $dtstartTz,
             IcaldateTime $compStart,
             IcaldateTime $workStart,
             IcaldateTime $workEnd,
                          $compStartHis
    ) {
        while( false !== ( $prop = $component->getProperty( Util::$EXRULE ))) {
            $exdateList2 = [];
            if( isset( $prop[Util::$UNTIL][Util::$LCHOUR] )) { // convert UNTIL date to DTSTART timezone
                $until = IcaldateTime::factory(
                    $prop[Util::$UNTIL],
                    [ Util::$TZID => Util::$UTC ],
                    null,
                    $dtstartTz
                );
                $until = $until->format();
                Util::strDate2arr( $until );
                $prop[Util::$UNTIL] = $until;
            }
            UtilRecur::recur2date( $exdateList2, $prop, $compStart, $workStart, $workEnd );
            foreach( $exdateList2 as $k => $v ) { // point out exact every excluded ocurrence (incl. opt. His)
                $exdateList[$k . $compStartHis] = $v;
            }
        }
    }

    /**
     * Get all EXDATE dates (multiple values allowed)
     *
     * @param CalendarComponent $component
     * @param array             $exdateList
     * @param string            $dtstartTz
     */
    private static function getAllEXDATEdates(
        CalendarComponent $component,
        array & $exdateList,
        $dtstartTz
    ) {
        while( false !== ( $prop = $component->getProperty( Util::$EXDATE, false, true ))) {
            foreach( $prop[Util::$LCvalue] as $exdate ) {
                $exdate = IcaldateTime::factory( $exdate, $prop[Util::$LCparams], $exdate, $dtstartTz );
                $exdateList[$exdate->key] = true;
            } // end - foreach( $exdate as $exdate )
        }
    }

    /**
     * Update $recurList all RRULE dates (multiple values allowed)
     *
     * @param CalendarComponent $component
     * @param array             $recurList
     * @param string            $dtstartTz
     * @param IcaldateTime      $compStart
     * @param IcaldateTime      $workStart
     * @param IcaldateTime      $workEnd
     * @param string            $compStartHis
     * @param array             $exdateList
     * @param DateInterval      $compDuration
     * @since 2.26.4 - 2018-11-15
     */
    private static function getAllRRULEdates(
        CalendarComponent $component,
                  array & $recurList,
                          $dtstartTz,
             IcaldateTime $compStart,
             IcaldateTime $workStart,
             IcaldateTime $workEnd,
                          $compStartHis,
                  array & $exdateList,
             DateInterval $compDuration = null
    ) {
        $exdateYmdList = UtilSelect::getYmdList($exdateList );
        $recurYmdList  = UtilSelect::getYmdList($recurList );
        while( false !== ( $prop = $component->getProperty( Util::$RRULE ))) {
            $recurList2 = [];
            if( isset( $prop[Util::$UNTIL][Util::$LCHOUR] )) {
                // convert RRULE['UNTIL'] to same timezone as DTSTART !!
                $until = IcaldateTime::factory(
                    $prop[Util::$UNTIL],
                    [ Util::$TZID => Util::$UTC ],
                    null,
                    $dtstartTz
                );
                $until = $until->format();
                Util::strDate2arr( $until );
                $prop[Util::$UNTIL] = $until;
            }
            UtilRecur::recur2date( $recurList2, $prop, $compStart, $workStart, $workEnd );
            foreach( $recurList2 as $recurKey => $recurValue ) { // recurkey=Ymd
                if( isset( $exdateYmdList[$recurKey] )) {  // exclude on Ymd basis
                    continue;
                }
                $YmdHisKey = $recurKey . $compStartHis;          // add opt His
                if( isset( $recurYmdList[$recurKey] )) {  // replace on Ymd basis
                    $exdateList[$YmdHisKey] = true;
                    continue;
                }
                if( ! isset( $exdateList[$YmdHisKey] )) {
                    $recurList[$YmdHisKey] = $compDuration; // DateInterval or false
                }
            }
        } // end while
    }

    /**
     * Update $recurList with RDATE dates (overwrite if exists)
     *
     * @param CalendarComponent $component
     * @param array             $recurList
     * @param string            $dtstartTz
     * @param IcaldateTime      $workStart
     * @param IcaldateTime      $fcnEnd
     * @param string            $format
     * @param array             $exdateList
     * @param string            $compStartHis
     * @param DateInterval      $compDuration
     * @throws Exception
     * @since 2.26.4 - 2018-11-15
     * @todo catch mgnt
     */
    private static function getAllRDATEdates(
        CalendarComponent $component,
                  array & $recurList,
                          $dtstartTz,
             IcaldateTime $workStart,
             IcaldateTime $fcnEnd,
                          $format,
                  array & $exdateList,
                          $compStartHis,
             DateInterval $compDuration = null
    ) {
        $exdateYmdList = UtilSelect::getYmdList( $exdateList );
        $recurYmdList  = UtilSelect::getYmdList( $recurList );
        while( false !== ( $prop = $component->getProperty( Util::$RDATE, false, true ))) {
            $rDateFmt = ( isset( $prop[Util::$LCparams][Util::$VALUE] ))
                ? $prop[Util::$LCparams][Util::$VALUE] // DATE or PERIOD
                : Util::$DATE_TIME;
            $params   = $prop[Util::$LCparams];
            $prop     = $prop[Util::$LCvalue];
            foreach( $prop as $rix => $theRdate ) {
                if( Util::$PERIOD == $rDateFmt ) {            // all days within PERIOD
                    $rDate = IcaldateTime::factory( $theRdate[0], $params, $theRdate[0], $dtstartTz );
                    if( ! UtilSelect::inScope( $rDate, $workStart, $rDate, $fcnEnd, $format )) {
                        continue;
                    }
                    $cmpKey = substr( $rDate->key, 0, 8 );
                    // exclude on Ymd basis (rRules already excluded)
                    if( isset( $exdateYmdList[$cmpKey] )) {
                        continue;
                    }
                    // exclude on Ymd(His) basis
                    if( isset( $exdateList[$rDate->key] )) {
                        continue;
                    }
                    // rDate replaces rRule, update excludeList
                    if( isset( $recurYmdList[$cmpKey] )) {
                        $exdateList[$recurYmdList[$cmpKey]] = true;
                    }
                    if( isset( $theRdate[1][Util::$LCYEAR] )) { // date-date period end
                        $recurList[$rDate->key] = $rDate->diff( // save duration
                            IcaldateTime::factory( $theRdate[1], $params, $theRdate[1], $dtstartTz )
                        );
                        continue;
                    }
                    try {                                   // period duration
                        $recurList[$rDate->key] = new DateInterval( UtilDuration::duration2str( $theRdate[1] ));
                    }
                    catch( Exception $e ) { // todo better error mgnt
                        throw $e;
                    }
                    continue;
                } // end if( Util::$PERIOD == $rDateFmt )
                elseif( Util::$DATE == $rDateFmt ) {          // single recurrence, DATE (=Ymd)
                    $rDate = IcaldateTime::factory(
                        $theRdate,
                        array_merge( $params, [ Util::$TZID => $dtstartTz ] ),
                        null,
                        $dtstartTz
                    );
                    $rDateYmdHisKey = $rDate->key . $compStartHis;
                }
                else { // single recurrence, DATETIME
                    $rDate = IcaldateTime::factory( $theRdate, $params, $theRdate, $dtstartTz );
                    // set start date for recurrence + DateInterval/false (+opt His)
                    $rDateYmdHisKey = $rDate->key;
                }
                $cmpKey = substr( $rDate->key, 0, 8 );
                switch( true ) {
                    case ( isset( $exdateYmdList[$cmpKey] )) : // excluded on Ymd basis
                        break;
                    case ( ! UtilSelect::inScope( $rDate, $workStart, $rDate, $fcnEnd, $format )) :
                        break;
                    default :
                        if( isset( $recurYmdList[$cmpKey] )) {  // rDate replaces rRule
                            $exdateList[$recurYmdList[$cmpKey]] = true;
                        }
                        $recurList[$rDateYmdHisKey] = $compDuration;
                        break;
                }
            } // end foreach
        }  // end while
    }

    /**
     * Return YmdList from YmdHis keyed array
     *
     * @param array $YmdHisArr
     * @return array
     * @access private
     * @static
     * @since 2.26.2 - 2018-11-15
     */
    private static function getYmdList( array $YmdHisArr ) {
        $res = [];
        foreach( $YmdHisArr as $key => $value ) {
            $res[substr( $key, 0, 8 )] = $key;
        }
        return $res;
    }

    /**
     * Assert date arguments
     *
     * @param mixed     $startY
     * @param mixed     $startM
     * @param int       $startD
     * @param int       $endY
     * @param int       $endM
     * @param int       $endD
     * @access private
     * @static
     * @since 2.26.2 - 2018-11-15
     */
    private static function assertDateArguments(
        & $startY = null,
        & $startM = null,
        & $startD = null,
        & $endY   = null,
        & $endM   = null,
        & $endD   = null
    ) {
        static $Y = 'Y';
        static $M = 'm';
        static $D = 'd';
        if(( $startY instanceof DateTime ) &&
           ( $startM instanceof DateTime )) {
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
    }

    /**
     * Assert bool arguments
     *
     * @param bool      $flat
     * @param bool      $any
     * @param bool      $split
     * @access private
     * @static
     * @since 2.26.2 - 2018-11-15
     */
    private static function assertBoolArguments(
        & $flat  = null,
        & $any   = null,
        & $split = null
    ) {
        // defaults
        $flat  = ( is_null( $flat ))  ? false : (bool) $flat;
        $any   = ( is_null( $any ))   ? true  : (bool) $any;
        $split = ( is_null( $split )) ? true  : (bool) $split;
        if(( false === $flat ) && ( false === $any )) {
            // invalid combination
            $split = false;
        }
        if(( true === $flat ) && ( true === $split )) {
            // invalid combination
            $split = false;
        }
    }

    /**
     * Return checked compoment types
     *
     * @param array|string $cType
     * @return array
     * @access private
     * @static
     * @since 2.26.2 - 2018-11-15
     */
    private static function assertComponentTypes( $cType ) {
        static $STRTOLOWER = 'strtolower';
        static $UCFIRST    = 'ucfirst';
        if( empty( $cType ) ) {
            return Util::$VCOMPS;
        }
        if( ! is_array( $cType ) ) {
            $cType = [ $cType ];
        }
        $cType = array_map( $UCFIRST, array_map( $STRTOLOWER, $cType ) );
        foreach( $cType as $cix => $theType ) {
            if( ! Util::isPropInList( $theType, Util::$VCOMPS ) ) {
                $cType[$cix] = Vcalendar::VEVENT;
            }
        }
        return array_unique( $cType );
    }

    /**
     * Return comp end date(time) from dtend/due/duration properties
     *
     * @param CalendarComponent $component
     * @param string            $dtstartTz
     * @return IcaldateTime
     * @access private
     * @static
     * @since 2.26.2 - 2018-11-15
     */
    private static function getCompEndDate(
        CalendarComponent $component,
                          $dtstartTz
    ) {
        static $MINUS1DAY = '-1 day';
        $compEnd = $prop = null;
        if( false !== ( $prop = $component->getProperty( Util::$DTEND, false, true ))) {
            $compEnd = IcaldateTime::factory(
                $prop[Util::$LCvalue],
                $prop[Util::$LCparams],
                $prop[Util::$LCvalue],
                $dtstartTz
            );
            $compEnd->SCbools[UtilSelect::$DTENDEXIST] = true;
        }
        if( empty( $prop ) &&
            ( $component->compType == Vcalendar::VTODO ) &&
            ( false !== ( $prop = $component->getProperty( Util::$DUE, false, true )))) {
            $compEnd = IcaldateTime::factory(
                $prop[Util::$LCvalue],
                $prop[Util::$LCparams],
                $prop[Util::$LCvalue],
                $dtstartTz
            );
            $compEnd->SCbools[UtilSelect::$DUEEXIST] = true;
        }
        if( empty( $prop ) && // duration in dtend (array) format
            ( false !== ( $prop = $component->getProperty( Util::$DURATION,false,true,true )))) {
            $compEnd = IcaldateTime::factory(
                $prop[Util::$LCvalue],
                $prop[Util::$LCparams],
                $prop[Util::$LCvalue],
                $dtstartTz
            );
            $compEnd->SCbools[UtilSelect::$DURATIONEXIST] = true;
        }
        if( ! empty( $prop ) && ! isset( $prop[Util::$LCvalue][Util::$LCHOUR] )) {
            /* a DTEND without time part denotes an end of an event that actually ends the day before,
               for an all-day event DTSTART=20071201 DTEND=20071202, taking place 20071201!!! */
            $compEnd->SCbools[UtilSelect::$ENDALLDAYEVENT] = true;
            $compEnd->modify( $MINUS1DAY );
            $compEnd->setTime( 23, 59, 59 );
        }
        unset( $prop );
        return $compEnd;
    }

    /**
     * Set duration end time
     *
     * @param IcaldateTime $rStart
     * @param IcaldateTime $rEnd
     * @param int          $cnt
     * @param int          $occurenceDays
     * @param array        $endHis
     * @access private
     * @static
     * @since 2.26 - 2018-11-10
     */
    private static function setDurationEndTime(
        IcaldateTime $rStart,
        IcaldateTime $rEnd,
        $cnt,
        $occurenceDays,
        array $endHis
    ) {
        static $YMDn = 'Ymd';
        if( $cnt < $occurenceDays ) {
            $rStart->setTime( 23, 59, 59 );
        }
        elseif(( $rStart->format( $YMDn ) < $rEnd->format( $YMDn )) &&
            ( 0 == $endHis[0] ) && ( 0 == $endHis[1] ) && ( 0 == $endHis[2] )) {
            $rStart->setTime( 24, 0, 0 ); // end exactly at midnight
        }
        else {
            $rStart->setTime( $endHis[0], $endHis[1], $endHis[2] );
        }
    }

    /**
     * Get array Y, m, d keys
     *
     * @param IcaldateTime $icaldateTime
     * @return array
     * @access private
     * @static
     * @since 2.26.2 - 2018-11-15
     */
    private static function getArrayYMDkeys(
        IcaldateTime $icaldateTime
    ) {
        static $Y = 'Y';
        static $M = 'm';
        static $D = 'd';
        return [
            (int) $icaldateTime->format( $Y ),
            (int) $icaldateTime->format( $M ),
            (int) $icaldateTime->format( $D )
        ];
    }

    /**
     * Update recurr-id-comps properties summary, description and comment if missing
     *
     * @param CalendarComponent $component
     * @param array             $recurrIdComps
     * @access private
     * @static
     * @since 2.26.5 - 2018-11-16
     */
    private static function updateRecurrIdComps(
        CalendarComponent $component,
        array           & $recurrIdComps
    ) {
        if( empty( $recurrIdComps )) {
            return;
        }
        $summary     = $component->getProperty( Util::$SUMMARY, null, true );
        $description = $component->getProperty( Util::$DESCRIPTION, null, true );
        $comments    = [];
        while( false !== ( $comment = $component->getProperty( Util::$COMMENT, null, true ))) {
            $comments[] = $comment;
        }
        foreach( array_keys( $recurrIdComps ) as $RecurrIdKey ) {
            if( ! empty( $summary )) {
                $value = $recurrIdComps[$RecurrIdKey][4]->getProperty( Util::$SUMMARY );
                if( empty( $value )) {
                    $recurrIdComps[$RecurrIdKey][4]->setProperty(
                        Util::$SUMMARY,
                        $summary[Util::$LCvalue],
                        $summary[Util::$LCparams] );
                }
            }
            if( ! empty( $description )) {
                $value = $recurrIdComps[$RecurrIdKey][4]->getProperty( Util::$DESCRIPTION );
                if( empty( $value )) {
                    $recurrIdComps[$RecurrIdKey][4]->setProperty(
                        Util::$DESCRIPTION,
                        $description[Util::$LCvalue],
                        $description[Util::$LCparams]
                    );
                }
            }
            if( empty( $comments )) {
                continue;
            }
            $value = $recurrIdComps[$RecurrIdKey][4]->getProperty( Util::$COMMENT );
            if( ! empty( $value )) {
                continue;
            }
            foreach( $comments as $prop ) {
                $recurrIdComps[$RecurrIdKey][4]->setProperty(
                    Util::$COMMENT,
                    $prop[Util::$LCvalue],
                    $prop[Util::$LCparams]
                );
            }
        } // end foreach
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
        $selectOptions = array_change_key_case( $selectOptions, CASE_UPPER );
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
                if( ! is_array( $propValue )) {
                    $propValue = [ $propValue ];
                }
                if(( Util::$UID == $propName ) && in_array( $uid, $propValue )) {
                    $output[$uid][] = $component3;
                    continue;
                }
                elseif( Util::isPropInList( $propName, Util::$MPROPS1 )) {
                    $propValues = [];
                    $component3->getProperties( $propName, $propValues );
                    $propValues = array_keys( $propValues );
                    foreach( $propValue as $theValue ) {
                        if( in_array( $theValue, $propValues )) { //  && ! isset( $output[$uid] )) {
                            $output[$uid][] = $component3;
                            break;
                        }
                    }
                    continue;
                } // end   elseif( // multiple occurrence?
                elseif( false === ( $d = $component3->getProperty( $propName ))) { // single occurrence
                    continue;
                }
                if( is_array( $d )) {
                    foreach( $d as $part ) {
                        if( in_array( $part, $propValue ) && ! isset( $output[$uid] )) {
                            $output[$uid][] = $component3;
                        }
                    }
                }
                elseif(( Util::$SUMMARY == $propName ) && ! isset( $output[$uid] )) {
                    foreach( $propValue as $pval ) {
                        if( false !== stripos( $d, $pval )) {
                            $output[$uid][] = $component3;
                            break;
                        }
                    }
                }
                elseif( in_array( $d, $propValue ) && ! isset( $output[$uid] )) {
                    $output[$uid][] = $component3;
                }
            } // end foreach( $selectOptions as $propName => $propValue )
        } // end while( $component3 = $calendar->getComponent()) {
        if( ! empty( $output )) {
            ksort( $output ); // uid order
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
