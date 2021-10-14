<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
 * @license   Subject matter of licence is the software iCalcreator.
 *            The above copyright, link, package and version notices,
 *            this licence notice and the invariant [rfc5545] PRODID result use
 *            as implemented and invoked in iCalcreator shall be included in
 *            all copies or substantial portions of the iCalcreator.
 *
 *            iCalcreator is free software: you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *            iCalcreator is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public License
 *            along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 */
declare( strict_types = 1 );
namespace Kigkonsult\Icalcreator\Util;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Exception;
use Kigkonsult\Icalcreator\CalendarComponent;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Vcalendar;
use RuntimeException;

use function array_change_key_case;
use function array_keys;
use function array_unique;
use function count;
use function in_array;
use function is_array;
use function ksort;
use function method_exists;
use function sprintf;
use function stripos;
use function strtolower;
use function substr;
use function ucfirst;
use function usort;

/**
 * iCalcreator geo support class
 *
 * @since 2.27.17 - 2020-01-25
 */
class SelectFactory
{
    /**
     * @var string  component end date properties
     */
    private static string $DTENDEXIST     = 'dtendExist';

    /**
     * @var string  dito
     */
    private static string $DUEEXIST       = 'dueExist';

    /**
     * @var string  dito
     */
    private static string $DURATIONEXIST  = 'durationExist';

    /**
     * @var string  dito
     */
    private static string $ENDALLDAYEVENT = 'endAllDayEvent';

    /**
     * Return selected components from calendar on date or selectOption basis
     *
     * DTSTART MUST be set for every component.
     * No check of date.
     *
     * @param Vcalendar $calendar
     * @param null|int|mixed[]|DateTimeInterface $startY    (int) start Year,  default current Year
     *                                      ALT. (object) DateTimeInterface start date
     *                                      ALT. array selectOptions ( *[ <propName> => <uniqueValue> ] )
     * @param null|int|DateTimeInterface $startM    (int) start Month, default current Month
     *                                      ALT. (object) DateTimeInterface end date
     * @param null|int  $startD    start Day,   default current Day
     * @param null|int  $endY      end   Year,  default $startY
     * @param null|int  $endM      end   Month, default $startM
     * @param null|int  $endD      end   Day,   default $startD
     * @param mixed     $cType     calendar component type(-s), default false=all else string/array type(-s)
     * @param null|bool $flat      false (default) => output : array[Year][Month][Day][]
     *                             true            => output : array[] (ignores split)
     * @param null|bool $any       true (default) - select component(-s) that occurs within period
     *                             false          - only component(-s) that starts within period
     * @param null|bool $split     true (default) - one component copy every DAY it occurs during the
     *                             period (implies flat=false)
     *                             false          - one occurance of component only in output array
     * @return mixed[]|bool    false on select error
     * @throws RuntimeException
     * @throws Exception
     * @since  2.29.16 - 2020-01-24
     */
    public static function selectComponents(
        Vcalendar                       $calendar,
        DateTimeInterface | int | array $startY = null,
        DateTimeInterface | int         $startM = null,
        int                             $startD = null,
        int                             $endY   = null,
        int                             $endM   = null,
        int                             $endD   = null,
        mixed                           $cType  = null,
        bool                            $flat   = null,
        bool                            $any    = null,
        bool                            $split  = null
    ) : bool | array
    {
        static $P1D       = 'P1D';
        static $YMDHIS2   = 'Y-m-d H:i:s';
        static $PRA       = '%a';
        static $YMDn      = 'Ymd';
        static $HIS       = '%02d%02d%02d';
        static $DAYOFDAYS = 'day %d of %d';
        static $SORTER    = [ SortFactory::class, 'cmpfcn' ];
        /* check  if empty calendar */
        if( 1 > $calendar->countComponents()) {
            return false;
        }
        if( is_array( $startY )) {
            return self::selectComponents2( $calendar, $startY );
        }
        /* assert boundary dates */
        self::assertDateArguments( $startY, $startM, $startD, $endY, $endM, $endD );
        /* assert component types */
        $cType = self::assertComponentTypes( $cType );
        /* assert bool args */
        self:: assertBoolArguments( $flat, $any, $split );
        /* iterate components */
        $result     = [];
        $calendar   = clone $calendar;
        $calendar->sort( IcalInterface::UID );
        $compUIDold = null;
        $exdateList = $recurIdList = [];
        $INTERVAL_P1D = DateIntervalFactory::factory( $P1D );
        $cix          = -1;
        while( $component = $calendar->getComponent()) {
            ++$cix;
            if( empty( $component )) {
                continue;
            }
            /* skip invalid type components */
            if( ! Util::isCompInList( $component->getCompType(), $cType )) {
                continue;
            }
            /* select start from dtstart or due if dtstart is missing */
            if(( false === ( $prop = $component->getDtstart( true ))) &&
                (( IcalInterface::VTODO === $component->getCompType()) &&
                 ( false === ( $prop = $component->getDue( true ))))) {
                continue;
            }
            $compStart = UtilDateTime::factory(
                $prop[Util::$LCvalue],
                $prop[Util::$LCparams]
            );
            $dtStartTz = $compStart->getTimezoneName();
            if( ParameterFactory::isParamsValueSet( $prop, IcalInterface::DATE )) {
                $compStartHis = null;
            }
            else {
                $his          = $compStart->getTime();
                $compStartHis = sprintf( $HIS, $his[0], $his[1], $his[2] );
            }
            /* get end date from dtend/due/duration properties */
            $compEnd = self::getCompEndDate( $component, $dtStartTz );
            if( empty( $compEnd )) {
                $compDuration = null; // DateInterval: no duration
                $compEnd      = clone $compStart;
                $compEnd->setTime( 23, 59, 59 );        // 23:59:59 the same day as start
            }
            else {
                if( $compEnd->format( $YMDn ) < $compStart->format( $YMDn )) { // MUST be after start date!!
                    $compEnd = $compStart->getClone();
                    $compEnd->setTime( 23, 59, 59 );    // 23:59:59 the same day as start or ???
                }
                $compDuration = $compStart->diff( $compEnd ); // DateInterval
            }
            /* get UID */
            $compUID = $component->getUid();
            if( $compUIDold !== $compUID ) {
                $compUIDold = $compUID;
                $exdateList = $recurIdList = [];
            }
            $compType = $component->getCompType();
            /**
             * Component with recurrence-id sorted before any rDate/rRule comp
             * to alter date(time) when found in dtstart/recurlist.
             * (Note, a missing sequence (expected here) is the same as sequence=0 so don't test for sequence.)
             * Highest sequence always last, will replace any previous
             */
            $recurId = null;
            if(( IcalInterface::VFREEBUSY !== $compType ) &&
               ( false !== ( $prop = $component->getRecurrenceid( true )))) {
                $recurId = UtilDateTime::factory(
                    $prop[Util::$LCvalue],
                    $prop[Util::$LCparams],
                    $dtStartTz
                );
                $rangeSet = Util::issetKeyAndEquals(
                    $prop[Util::$LCparams],
                    IcalInterface::RANGE,
                    IcalInterface::THISANDFUTURE
                );
                $recurIdList[$recurId->key] = [
                    $compStart->getClone(),
                    $compEnd->getClone(),
                    $compDuration, // DateInterval
                    $rangeSet,
                    clone $component,
                ];        // save recur day to altered YmdHis/duration/range
                continue; // ignore any other props in the recurrence_id component
            } // end recurrence-id/sequence
            ksort( $recurIdList, SORT_STRING );
            self::updateRecurrIdComps( $component, $recurIdList );
            /* prepare */
            $fcnStart = $compStart->getClone();
            $fcnStart->setDate((int) $startY, (int) $startM, (int) $startD );
            $fcnStart->setTime( 0, 0 ); // 0:0:0
            $fcnEnd = $compEnd->getClone();
            $fcnEnd->setDate((int) $endY, (int) $endM, (int) $endD );
            $fcnEnd->setTime( 23, 59, 59 );
            /* set up work dates */
            $workStart = $compStart->getClone();
            $duration  = ( empty( $compDuration )) ? $INTERVAL_P1D : $compDuration; // DateInterval
            $workStart->sub( $duration );
            $workEnd = $fcnEnd->getClone();
            $workEnd->add( $duration );
            /* make a list of optional exclude dates for component occurence from exrule and exdate */
            if( IcalInterface::VFREEBUSY !== $compType ) {
                self::getAllEXRULEdates(
                    $component, $exdateList,
                    $dtStartTz, $compStart, $workStart, $workEnd,
                    $compStartHis
                );
                self::getAllEXDATEdates( $component, $exdateList, $dtStartTz );
            }
            /* select only components within.. . */
            $xRecurrence = 1;
                           // (dt)start within the period
            if(( ! $any && self::inScope(
                $compStart, $fcnStart, $compStart, $fcnEnd, $compStart->dateFormat
                    )) ||
                          // occurs within the period
                 ( $any && self::inScope(
                     $fcnEnd, $compStart, $fcnStart, $compEnd, $compStart->dateFormat
                     ))) {
                /* add the selected component (WITHIN valid dates) to output array */
                if( $flat ) { // any=true/false, ignores split
                    if( empty( $recurId )) {
                        $result[$compUID] = clone $component;
                    }         // copy original to output (but not anyone with recurrence-id)
                }
                elseif( $split ) { // split the original component
                    $rStart = ( $compStart->format( $YMDHIS2 ) <
                        $fcnStart->format( $YMDHIS2 ))
                        ? $fcnStart->getClone()
                        : $compStart->getClone();
                    $rEnd = ( $compEnd->format( $YMDHIS2 ) > $fcnEnd->format( $YMDHIS2 ))
                        ? $fcnEnd->getClone()   : $compEnd->getClone();
                    if( ! isset( $exdateList[$rStart->key] )) {      // not excluded in exrule/exdate
                        if( isset( $recurIdList[$rStart->key] )) {  // change start day to new YmdHis/duration
                            $k        = $rStart->key;
                            $rStart   = $recurIdList[$k][0]->getClone(); // UtilDateTime
                            $startHis = $rStart->getTime();
                            $rEnd     = $rStart->getClone();
                            if( ! empty( $recurIdList[$k][2] )) { // DateInterval
                                $rEnd->add( $recurIdList[$k][2] );
                            }
                            elseif( ! empty( $compDuration )) {    // DateInterval
                                $rEnd->add( $compDuration );
                            }
                            $endHis     = $rEnd->getTime();
                            $component2 = ( isset( $recurIdList[$k][4] ))
                                ? clone $recurIdList[$k][4] : clone $component;
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
                            ++$cnt;
                            if( 1 < $occurenceDays ) {
                                $component2->setXprop(
                                    IcalInterface::X_OCCURENCE,
                                    sprintf( $DAYOFDAYS, $cnt, $occurenceDays )
                                );
                            }
                            if( 1 < $cnt ) {
                                $rStart->setTime( 0, 0 ); // 0:0:0
                            }
                            else {
                                // make sure to exclude start day from the recurrence pattern
                                $rStart->setTime( $startHis[0], $startHis[1], $startHis[2] );
                                $exdateList[$rStart->key] = $compDuration; // DateInterval
                            }
                            $component2->setXprop(
                                IcalInterface::X_CURRENT_DTSTART,
                                $rStart->format( $compStart->dateFormat )
                            );
                            [ $xY, $xM, $xD ] = self::getArrayYMDkeys( $rStart );
                            if( ! empty( $compDuration )) { // DateInterval
                                $propName = ( isset( $compEnd->SCbools[self::$DUEEXIST] ))
                                    ? IcalInterface::X_CURRENT_DUE : IcalInterface::X_CURRENT_DTEND;
                                self::setDurationEndTime(
                                    $rStart,
                                    $rEnd,
                                    $cnt,
                                    $occurenceDays,
                                    $endHis
                                );
                                $component2->setXprop(
                                    $propName,
                                    $rStart->format( $compEnd->dateFormat )
                                );
                            } // end if
                            $result[$xY][$xM][$xD][$compUID] = clone $component2;    // copy to output
                            $rStart->add( $INTERVAL_P1D );
                        } // end while(( $rStart->format( 'Ymd' ) < $rEnd->format( 'Ymd' ))
                    } // end if( ! isset( $exdateList[$rStart->key] ))
                } // end elseif( $split )   -  else use component date
                else { // !$flat && !$split, i.e. no flat array and DTSTART within period
                    if( isset( $recurIdList[$compStart->key] )) {
                        $rStart     = $recurIdList[$compStart->key][0]->getClone();
                        $component2 = $recurIdList[$compStart->key][4] ?? clone $component;
                    }
                    else {
                        $rStart     = $compStart->getClone();
                        $component2 = clone $component;
                    }
                    if( ! $any || ! isset( $exdateList[$rStart->key] )) {
                        // exclude any recurrence date, found in exdatelist
                        [ $xY, $xM, $xD ] = self::getArrayYMDkeys( $rStart );
                        $result[$xY][$xM][$xD][$compUID] = clone $component2; // copy to output
                    }
                } // end else
            } // end (dt)start within the period OR occurs within the period
            /* *************************************************************
               if 'any' components, check components with reccurrence rules, removing all excluding dates
               *********************************************************** */
            if( true === $any ) {
                $recurList = [];
                if( IcalInterface::VFREEBUSY !== $compType ) {
                    /* make a list of optional repeating dates for component occurence, rrule, rdate */
                    self::getAllRRULEdates(
                        $component, $recurList,
                        $dtStartTz, $compStart, $workStart, $workEnd,
                        $compStartHis, $exdateList, $compDuration
                    );
                    $workStart = $fcnStart->getClone();
                    $workStart->sub( ( empty( $compDuration ))
                        ? $INTERVAL_P1D
                        : $compDuration
                    );
                    self::getAllRDATEdates(
                        $component, $recurList,
                        $dtStartTz, $workStart, $fcnEnd, $compStart->dateFormat,
                        $exdateList, $compStartHis, $compDuration
                    );
                    unset( $workStart );
                    ksort( $recurList, SORT_STRING );
                } // end if( Vcalendar::VFREEBUSY != $compType )
                /* output all remaining components in recurlist */
                if( 0 < count( $recurList )) {
                    $component2 = clone $component;
                    $compUID    = $component2->getUid();
                    $workStart  = $fcnStart->getClone();
                    $workStart->sub(( empty( $compDuration ))
                        ? $INTERVAL_P1D
                        : $compDuration// DateInterval
                    );
                    $YmdOld = null;
                    foreach( $recurList as $recurKey => $durationInterval ) {
                        $recurKeyYmd = substr((string) $recurKey, 0, 8 );
                        if( $YmdOld === $recurKeyYmd ) {
                            continue; // skip overlapping recur the same day, i.e. RDATE before RRULE
                        }
                        $YmdOld = $recurKeyYmd;
                        $rStart = $compStart->getClone();
                        $rStart->setDateTimeFromString((string) $recurKey );
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
                            $recurFound = false;
                            foreach( $recurIdList as $k => $v ) {
                                if( str_starts_with((string) $k, $recurKeyYmd )) {
                                    $rStart            = $v[0]->getClone();
                                    $durationInterval2 = ( empty( $v[2] ))
                                        ? null : $v[2];  // DateInterval
                                    $component3        = clone $v[4];
                                    $recurFound       = true;
                                    break;
                                }
                            } // end foreach
                            if( ! $recurFound ) {
                                $component3        = clone $component2;
                                $durationInterval2 = ( ! empty( $durationInterval ))
                                    ? $durationInterval
                                    : null;
                            }
                            $rEnd = $rStart->getClone();
                            if( ! empty( $durationInterval2 )) {
                                $rEnd->add( $durationInterval2 );
                            }
                            if( $rEnd->format( $YMDn ) > $fcnEnd->format( $YMDn )) {
                                $rEnd = clone $fcnEnd;
                            }
                            $endHis        = $rEnd->getTime();
                            ++$xRecurrence;
                            $cnt           = 0;
                            // count the days (incl start day)
                            $occurenceDays = 1 + (int) $rStart->diff( $rEnd )->format( $PRA );
                            while( $rStart->format( $YMDn ) <= $rEnd->format( $YMDn )) {   // iterate.. .
                                ++$cnt;
                                if( $rStart->format( $YMDn ) <
                                    $fcnStart->format( $YMDn )) { // date before dtstart
                                    $rStart->add( $INTERVAL_P1D ); // cycle rstart to dtstart
                                    $rStart->setTime( 0, 0 ); // 0:0:0
                                    continue;
                                }
                                if( 2 === $cnt ) {
                                    $rStart->setTime( 0, 0 ); // 0:0:0
                                }
                                [ $xY, $xM, $xD ] = self::getArrayYMDkeys( $rStart );
                                $component3->setXprop(
                                    IcalInterface::X_RECURRENCE,
                                    $xRecurrence
                                );
                                if( 1 < $occurenceDays ) {
                                    $component3->setXprop(
                                        IcalInterface::X_OCCURENCE,
                                        sprintf( $DAYOFDAYS, $cnt, $occurenceDays )
                                    );
                                }
                                else {
                                    $component3->deleteXprop( IcalInterface::X_OCCURENCE );
                                }
                                $component3->setXprop(
                                    IcalInterface::X_CURRENT_DTSTART,
                                    $rStart->format( $compStart->dateFormat )
                                );
                                $propName = ( isset( $compEnd->SCbools[self::$DUEEXIST] ))
                                    ? IcalInterface::X_CURRENT_DUE
                                    : IcalInterface::X_CURRENT_DTEND;
                                if( empty( $durationInterval2 )) {
                                    $component3->deleteXprop( $propName );
                                }
                                else {
                                    self::setDurationEndTime(
                                        $rStart,
                                        $rEnd,
                                        $cnt,
                                        $occurenceDays,
                                        $endHis
                                    );
                                    $component3->setXprop(
                                        $propName,
                                        $rStart->format( $compEnd->dateFormat )
                                    );
                                } // end else
                                $result[$xY][$xM][$xD][$compUID] = clone $component3;     // copy to output
                                $rStart->add( $INTERVAL_P1D );
                            } // end while( $rStart->format( 'Ymd' ) <= $rEnd->format( 'Ymd' ))
                            unset( $rStart, $rEnd );
                        } // end elseif( $split )
                        elseif( $rStart->format( $YMDn ) >= $fcnStart->format( $YMDn )) {
                            // date within period, flat=false && split=false => one comp every recur startdate
                            ++$xRecurrence;
                            $component2->setXprop(
                                IcalInterface::X_RECURRENCE,
                                $xRecurrence
                            );
                            $component2->setXprop(
                                IcalInterface::X_CURRENT_DTSTART,
                                $rStart->format( $compStart->dateFormat )
                            );
                            $propName = ( isset( $compEnd->SCbools[self::$DUEEXIST] ))
                                ? IcalInterface::X_CURRENT_DUE : IcalInterface::X_CURRENT_DTEND;
                            if( empty( $durationInterval )) {
                                $component2->deleteXprop( $propName );
                            }
                            else {
                                $rStart->add( $durationInterval );
                                $component2->setXprop(
                                    $propName,
                                    $rStart->format( $compEnd->dateFormat )
                                );
                            }
                            [ $xY, $xM, $xD ] = self::getArrayYMDkeys( $rStart );
                            $result[$xY][$xM][$xD][$compUID] = clone $component2; // copy to output
                        } // end elseif( $rStart >= $fcnStart )
                    } // end foreach( $recurList as $recurKey => $durationInterval )
                } // end if( 0 < count( $recurList ))
            } // end if( true === $any )
        } // end while( $component = $calendar->getComponent())
        if( 0 >= count( $result )) {
            return false;
        }
        if( ! $flat ) {
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
                                    SortFactory::setSortArgs(
                                        $result[$y][$m][$d][$cix]
                                    );
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
     * @param UtilDateTime $start
     * @param UtilDateTime $scopeStart
     * @param UtilDateTime $end
     * @param UtilDateTime $scopeEnd
     * @param string       $format
     * @return bool
     */
    private static function inScope(
        UtilDateTime $start,
        UtilDateTime $scopeStart,
        UtilDateTime $end,
        UtilDateTime $scopeEnd,
        string $format
    ) : bool
    {
        return (( $start->format( $format ) >= $scopeStart->format( $format )) &&
                  ( $end->format( $format ) <= $scopeEnd->format( $format )));
    }

    /**
     * Get all EXRULE dates (multiple values allowed)
     *
     * @param CalendarComponent $component
     * @param mixed[]           $exdateList
     * @param string            $dtStartTz
     * @param UtilDateTime      $compStart
     * @param UtilDateTime      $workStart
     * @param UtilDateTime      $workEnd
     * @param string            $compStartHis
     * @throws Exception
     * @since 2.27.14 - 2019-02-27
     */
    private static function getAllEXRULEdates(
        CalendarComponent $component,
        array & $exdateList,
        string $dtStartTz,
        UtilDateTime $compStart,
        UtilDateTime $workStart,
        UtilDateTime $workEnd,
        string $compStartHis
    ) : void
    {
        if( false !== ( $prop = $component->getExrule( true ))) {
            $isValueDate = ParameterFactory::isParamsValueSet( $prop, IcalInterface::DATE );
            $prop        = $prop[Util::$LCvalue];
            if( isset( $prop[IcalInterface::UNTIL] ) && ! $isValueDate ) {
                // convert UNTIL date to DTSTART timezone
                $prop[IcalInterface::UNTIL] = UtilDateTime::factory(
                    $prop[IcalInterface::UNTIL],
                    [ IcalInterface::TZID => IcalInterface::UTC ],
                    $dtStartTz
                );
            }
            $exdateList2 = [];
            RecurFactory::recur2date(
                $exdateList2,
                $prop,
                $compStart,
                $workStart,
                $workEnd
            );
            foreach( $exdateList2 as $k => $v ) { // point out exact every excluded ocurrence (incl. opt. His)
                $exdateList[$k . $compStartHis] = $v;
            }
        }
    }

    /**
     * Get all EXDATE dates (multiple values allowed)
     *
     * @param CalendarComponent $component
     * @param mixed[]           $exdateList
     * @param string            $dtStartTz
     * @throws Exception
     * @since 2.27.2 - 2018-12-29
     */
    private static function getAllEXDATEdates(
        CalendarComponent $component,
        array & $exdateList,
        string $dtStartTz
    ) : void
    {
        while( false !== ( $prop = $component->getExdate( null, true ))) {
            foreach( $prop[Util::$LCvalue] as $exdate ) {
                $exdate = UtilDateTime::factory(
                    $exdate,
                    $prop[Util::$LCparams],
                    $dtStartTz
                );
                $exdateList[$exdate->key] = true;
            } // end - foreach( $exdate as $exdate )
        } // end while
    }

    /**
     * Update $recurList all RRULE dates (multiple values allowed)
     *
     * @param CalendarComponent $component
     * @param mixed[]           $recurList
     * @param string            $dtStartTz
     * @param UtilDateTime      $compStart
     * @param UtilDateTime      $workStart
     * @param UtilDateTime      $workEnd
     * @param string            $compStartHis
     * @param mixed[]           $exdateList
     * @param null|DateInterval $compDuration
     * @throws Exception
     * @since 2.27.14 - 2019-02-27
     */
    private static function getAllRRULEdates(
        CalendarComponent $component,
        array & $recurList,
        string $dtStartTz,
        UtilDateTime $compStart,
        UtilDateTime $workStart,
        UtilDateTime $workEnd,
        string $compStartHis,
        array & $exdateList,
        DateInterval $compDuration = null
    ) : void
    {
        $exdateYmdList = self::getYmdList( $exdateList );
        $recurYmdList  = self::getYmdList( $recurList );
        if( false !== ( $prop = $component->getRrule( true ))) {
            $isValueDate = ParameterFactory::isParamsValueSet( $prop, IcalInterface::DATE );
            $prop        = $prop[Util::$LCvalue];
            if( isset( $prop[IcalInterface::UNTIL] ) && ! $isValueDate ) {
                // convert RRULE['UNTIL'] to same timezone as DTSTART !!
                $prop[IcalInterface::UNTIL] = UtilDateTime::factory(
                    $prop[IcalInterface::UNTIL],
                    [ IcalInterface::TZID => IcalInterface::UTC ],
                    $dtStartTz
                );
            }
            $recurList2  = [];
            RecurFactory::recur2date(
                $recurList2,
                $prop,
                $compStart,
                $workStart,
                $workEnd
            );
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
            } // end foreach
        } // end while
    }

    /**
     * Update $recurList with RDATE dates (overwrite if exists)
     *
     * @param CalendarComponent $component
     * @param mixed[]           $recurList
     * @param string            $dtStartTz
     * @param UtilDateTime      $workStart
     * @param UtilDateTime      $fcnEnd
     * @param string            $format
     * @param mixed[]           $exdateList
     * @param string            $compStartHis
     * @param null|DateInterval $compDuration
     * @throws Exception
     * @since 2.40 - 2021-10-04
     */
    private static function getAllRDATEdates(
        CalendarComponent $component,
        array & $recurList,
        string $dtStartTz,
        UtilDateTime $workStart,
        UtilDateTime $fcnEnd,
        string $format,
        array & $exdateList,
        string $compStartHis,
        DateInterval $compDuration = null
    ) : void
    {
        $exdateYmdList = self::getYmdList( $exdateList );
        $recurYmdList  = self::getYmdList( $recurList );
        while( false !== ( $prop = $component->getRdate( null, true ))) {
            $rDateFmt = $prop[Util::$LCparams][IcalInterface::VALUE] ?? IcalInterface::DATE_TIME;
            // DATE or PERIOD
            $params   = $prop[Util::$LCparams];
            $prop     = $prop[Util::$LCvalue];
            foreach( $prop as $theRdate ) {
                if( IcalInterface::PERIOD === $rDateFmt ) {            // all days within PERIOD
                    $rDate = UtilDateTime::factory( $theRdate[0], $params, $dtStartTz );
                    if( ! self::inScope( $rDate, $workStart, $rDate, $fcnEnd, $format )
                    ) {
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
                    if( $theRdate[1] instanceof DateTime ) { // date-date period end
                        $recurList[$rDate->key] = $rDate->diff( // save duration
                            UtilDateTime::factory( $theRdate[1], $params, $dtStartTz )
                        );
                        continue;
                    }
                    // period duration
                    $recurList[$rDate->key] = $theRdate[1];
                    continue;
                } // end if( Vcalendar::PERIOD == $rDateFmt )
                if( IcalInterface::DATE === $rDateFmt ) {          // single recurrence, DATE (=Ymd)
                    $rDate = UtilDateTime::factory(
                        $theRdate,
                        array_merge( $params, [ IcalInterface::TZID => $dtStartTz ] ),
                        $dtStartTz
                    );
                    $rDateYmdHisKey = $rDate->key . $compStartHis;
                }
                else { // single recurrence, DATETIME
                    $rDate = UtilDateTime::factory( $theRdate, $params, $dtStartTz );
                    // set start date for recurrence + DateInterval/false (+opt His)
                    $rDateYmdHisKey = $rDate->key;
                }
                $cmpKey = substr( $rDate->key, 0, 8 );
                switch( true ) {
                    case ( isset( $exdateYmdList[$cmpKey] )) : // excluded on Ymd basis
                        break;
                    case ( ! self::inScope(
                        $rDate,
                        $workStart,
                        $rDate,
                        $fcnEnd,
                        $format
                    )) :
                        break;
                    default :
                        if( isset( $recurYmdList[$cmpKey] )) {  // rDate replaces rRule
                            $exdateList[$recurYmdList[$cmpKey]] = true;
                        }
                        $recurList[$rDateYmdHisKey] = $compDuration;
                        break;
                } // end switch
            } // end foreach
        }  // end while
    }

    /**
     * Return Ymd-List from YmdHis-keyed array
     *
     * @param mixed[] $YmdHisArr
     * @return mixed[]
     * @since 2.26.2 - 2018-11-15
     */
    private static function getYmdList( array $YmdHisArr ) : array
    {
        $res = [];
        foreach( $YmdHisArr as $key => $value ) {
            $res[substr((string) $key, 0, 8 )] = $key;
        }
        return $res;
    }

    /**
     * Assert date arguments
     *
     * @param int|DateTimeInterface $startY
     * @param int|DateTimeInterface $startM
     * @param null|int       $startD
     * @param null|int       $endY
     * @param null|int       $endM
     * @param null|int       $endD
     * @since  2.29.16 - 2020-01-24
     */
    private static function assertDateArguments(
        mixed & $startY,
        mixed & $startM,
        ? int & $startD = null,
        ? int & $endY   = null,
        ? int & $endM   = null,
        ? int & $endD   = null
    ) : void
    {
        static $Y = 'Y';
        static $M = 'm';
        static $D = 'd';
        if(( $startY instanceof DateTimeInterface ) &&
           ( $startM instanceof DateTimeInterface )) {
            $endY   = (int) $startM->format( $Y );
            $endM   = (int) $startM->format( $M );
            $endD   = (int) $startM->format( $D );
            $startD = (int) $startY->format( $D );
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
            if( $startD === null ) {
                $startD = (int) date( $D );
            }
            if( $endY === null ) {
                $endY = (int) $startY;
            }
            if( $endM === null ) {
                $endM = (int) $startM;
            }
            if( $endD === null ) {
                $endD = $startD;
            }
        }
    }

    /**
     * Return reviewed component types
     *
     * @param null|string|string[] $cType
     * @return string[]
     * @since 2.27.18 - 2019-04-07
     */
    private static function assertComponentTypes( null | array | string $cType = null ) : array
    {
        if( empty( $cType )) {
            return Vcalendar::$VCOMPS;
        }
        if( ! is_array( $cType )) {
            $cType = [ $cType ];
        }
        foreach( $cType as & $theType ) {
            $theType     = ucfirst( strtolower( $theType ));
            if( ! Util::isCompInList( $theType, Vcalendar::$VCOMPS )) {
                $theType = IcalInterface::VEVENT;
            }
        }
        return array_unique( $cType );
    }

    /**
     * Assert bool arguments
     *
     * @param bool      $flat
     * @param bool      $any
     * @param bool      $split
     * @since 2.26.2 - 2018-11-15
     */
    private static function assertBoolArguments(
        ? bool & $flat  = null,
        ? bool & $any   = null,
        ? bool & $split = null
    ) : void
    {
        // defaults
        $flat  = $flat ?? false;
        $any   = $any ?? true;
        $split = $split ?? true;
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
     * Return comp end date(time) from dtend/due/duration properties
     *
     * @param CalendarComponent $component
     * @param string            $dtStartTz
     * @return null|UtilDateTime
     * @throws Exception
     * @since 2.27.6 - 2018-12-29
     */
    private static function getCompEndDate(
        CalendarComponent $component,
        string $dtStartTz
    ) : null | UtilDateTime
    {
        static $MINUS1DAY = '-1 day';
        $prop = null;
        $compType = $component->getCompType();
        if((( IcalInterface::VEVENT === $compType ) ||
            ( IcalInterface::VFREEBUSY === $compType  )) &&
          ( false !== ( $prop = $component->getDtend( true )))) {
            $compEnd = UtilDateTime::factory(
                $prop[Util::$LCvalue],
                $prop[Util::$LCparams],
                $dtStartTz
            );
            $compEnd->SCbools[self::$DTENDEXIST] = true;
        }
        if( empty( $prop ) &&
            ( IcalInterface::VTODO === $compType  ) &&
            ( false !== ( $prop = $component->getDue( true )))) {
            $compEnd = UtilDateTime::factory(
                $prop[Util::$LCvalue],
                $prop[Util::$LCparams],
                $dtStartTz
            );
            $compEnd->SCbools[self::$DUEEXIST] = true;
        }
        if( empty( $prop ) && // duration in dtend (array) format
            (( IcalInterface::VEVENT === $compType  ) ||
             ( IcalInterface::VTODO === $compType )) &&
            ( false !== ( $prop = $component->getDuration( true, true )))) {
            $compEnd = UtilDateTime::factory(
                $prop[Util::$LCvalue],
                $prop[Util::$LCparams],
                $dtStartTz
            );
            $compEnd->SCbools[self::$DURATIONEXIST] = true;
        }
        if( ! empty( $prop ) &&
            ParameterFactory::isParamsValueSet( $prop, IcalInterface::DATE )) {
            /* a DTEND without time part denotes an end of an event that actually ends the day before,
               for an all-day event DTSTART=20071201 DTEND=20071202, taking place 20071201!!! */
            $compEnd->SCbools[self::$ENDALLDAYEVENT] = true;
            $compEnd->modify( $MINUS1DAY );
            $compEnd->setTime( 23, 59, 59 );
        }
        return $compEnd ?? null;
    }

    /**
     * Set duration end time
     *
     * @param UtilDateTime $rStart
     * @param UtilDateTime $rEnd
     * @param int          $cnt
     * @param int          $occurenceDays
     * @param mixed[]      $endHis
     * @since 2.26 - 2018-11-10
     */
    private static function setDurationEndTime(
        UtilDateTime $rStart,
        UtilDateTime $rEnd,
        int $cnt,
        int $occurenceDays,
        array $endHis
    ) : void
    {
        static $YMDn = 'Ymd';
        if( $cnt < $occurenceDays ) {
            $rStart->setTime( 23, 59, 59 );
        }
        elseif(( 0 === $endHis[0] ) && ( 0 === $endHis[1] ) && ( 0 === $endHis[2] ) &&
            ( $rStart->format( $YMDn ) < $rEnd->format( $YMDn ))) {
            $rStart->setTime( 24, 0 ); // end exactly at midnight  // 24:0:0
        }
        else {
            $rStart->setTime( $endHis[0], $endHis[1], $endHis[2] );
        }
    }

    /**
     * Get array Y, m, d keys
     *
     * @param UtilDateTime $icaldateTime
     * @return int[]
     * @since 2.26.2 - 2018-11-15
     */
    private static function getArrayYMDkeys(
        UtilDateTime $icaldateTime
    ) : array
    {
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
     * @param CalendarComponent $component     (Vevent/Vtodo/Vjournal)
     * @param mixed[]           $recurIdComps
     * @since 2.27.1 - 2018-12-16
     */
    private static function updateRecurrIdComps(
        CalendarComponent $component,
        array $recurIdComps
    ) : void
    {
        if( empty( $recurIdComps )) {
            return;
        }
        if( IcalInterface::VFREEBUSY === $component->getCompType()) {
            return;
        }
        $summary     = $component->getSummary( true );
        $description = $component->getDescription( null, true );
        $comments    = [];
        while( false !== ( $comment = $component->getComment( null, true ))) {
            $comments[] = $comment;
        }
        foreach( array_keys( $recurIdComps ) as $RecurrIdKey ) {
            if( ! empty( $summary )) {
                $value = $recurIdComps[$RecurrIdKey][4]->getSummary();
                if( empty( $value )) {
                    $recurIdComps[$RecurrIdKey][4]->setSummary(
                        $summary[Util::$LCvalue],
                        $summary[Util::$LCparams] );
                }
            }
            if( ! empty( $description )) {
                $value = $recurIdComps[$RecurrIdKey][4]->getDescription();
                if( empty( $value )) {
                    $recurIdComps[$RecurrIdKey][4]->setDescription(
                        $description[Util::$LCvalue],
                        $description[Util::$LCparams]
                    );
                }
            } // end if
            if( empty( $comments )) {
                continue;
            }
            $value = $recurIdComps[$RecurrIdKey][4]->getComment();
            if( ! empty( $value )) {
                continue;
            }
            foreach( $comments as $prop ) {
                $recurIdComps[$RecurrIdKey][4]->setComment(
                    $prop[Util::$LCvalue],
                    $prop[Util::$LCparams] );
            }
        } // end foreach
    }

    /**
     * Return array with selected components values from calendar based on specific property value(-s)
     *
     * @param Vcalendar $calendar
     * @param mixed[]   $selectOptions (string) key => (mixed) value, (key=propertyName)
     * @return mixed[]
     * @since 2.27.17 - 2020-01-25
     */
    private static function selectComponents2(
        Vcalendar $calendar,
        array $selectOptions
    ) : array
    {
        $output        = [];
        $selectOptions = array_change_key_case( $selectOptions, CASE_UPPER );
        while( $component3 = $calendar->getComponent()) {
            if( empty( $component3 )) {
                continue;
            }
            if( ! Util::isCompInList( $component3->getCompType(), Vcalendar::$VCOMPS )) {
                continue;
            }
            $uid = $component3->getUid();
            foreach( $selectOptions as $propName => $propValue ) {
                if( ! Util::isPropInList( $propName, Vcalendar::$OTHERPROPS )) {
                    continue;
                }
                if( ! is_array( $propValue )) {
                    $propValue = [ $propValue ];
                }
                if(( IcalInterface::UID === $propName ) && in_array( $uid, $propValue, true ) ) {
                    $output[$uid][] = $component3;
                    continue;
                }
                if( Util::isPropInList( $propName, Vcalendar::$MPROPS1 )) {
                    $propValues = [];
                    $component3->getProperties( $propName, $propValues );
                    $propValues = array_keys( $propValues );
                    foreach( $propValue as $theValue ) {
                        if( in_array( $theValue, $propValues, true ) ) { //  && ! isset( $output[$uid] )) {
                            $output[$uid][] = $component3;
                            break;
                        }
                    }
                    continue;
                } // end   if( // multiple occurrence?
                $method = StringFactory::getGetMethodName( $propName );
                if( ! method_exists( $component3, $method ) ||
                    ( false === ( $d = $component3->{$method}()))) { // single occurrence
                    continue;
                }
                if( is_array( $d )) {
                    foreach( $d as $part ) {
                        if( ! isset( $output[$uid] ) && in_array( $part, $propValue )) {
                            $output[$uid][] = $component3;
                        }
                    }
                }
                elseif(( IcalInterface::SUMMARY === $propName ) && ! isset( $output[$uid] )) {
                    foreach( $propValue as $pval ) {
                        if( false !== stripos( $d, $pval )) {
                            $output[$uid][] = $component3;
                            break;
                        }
                    }
                }
                elseif( ! isset( $output[$uid] ) && in_array( $d, $propValue )) {
                    $output[$uid][] = $component3;
                }
            } // end foreach( $selectOptions as $propName => $propValue )
        } // end while( $component3 = $calendar->getComponent()) {
        if( ! empty( $output )) {
            ksort( $output ); // uid order
            $output2 = [];
            foreach( $output as $uList ) {
                foreach( $uList as $uValue ) {
                    $output2[] = $uValue;
                }
            }
            $output = $output2;
        }
        return $output;
    }
}
