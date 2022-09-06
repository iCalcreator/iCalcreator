<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2022 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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
use Kigkonsult\Icalcreator\Vavailability;
use Kigkonsult\Icalcreator\Vcalendar;
use Kigkonsult\Icalcreator\Vevent;
use Kigkonsult\Icalcreator\Vfreebusy;
use Kigkonsult\Icalcreator\Vjournal;
use Kigkonsult\Icalcreator\Vtodo;
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
 * @since  2.41.45 - 2022-04-27
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
     * @param null|int|array|DateTimeInterface $startY    (int) start Year,  default current Year
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
     *                                              period (implies flat=false)
     *                             false          - one occurance of component only in output array
     * @return array|bool    false on select error
     * @throws RuntimeException
     * @throws Exception
     * @since  2.41.45 - 2022-04-27
     */
    public static function selectComponents(
        Vcalendar                        $calendar,
        null|int|array|DateTimeInterface $startY = null,
        null|int|DateTimeInterface       $startM = null,
        null|int                         $startD = null,
        null|int                         $endY   = null,
        null|int                         $endM   = null,
        null|int                         $endD   = null,
        mixed                            $cType  = null,
        null|bool                        $flat   = null,
        null|bool                        $any    = null,
        null|bool                        $split  = null
    ) : bool | array
    {
        static $P1D       = 'P1D';
        static $YMDHIS2   = 'YmdHis';
        static $YMDn      = 'Ymd';
        static $HIS       = '%02d%02d%02d';
        static $DAYOFDAYS = 'day %d of %d';
        /* check  if empty calendar */
        if( 1 > $calendar->countComponents()) {
            return false;
        }
        if( is_array( $startY )) {
            return self::selectComponents2( $calendar, $startY );
        }
        /* assert boundary dates */
        $argStart = ( $startY instanceof DateTimeInterface ) ? clone $startY : null;
        $argEnd   = ( $startM instanceof DateTimeInterface ) ? clone $startM : null;
        self::assertDateArguments( $startY, $startM, $startD, $endY, $endM, $endD );
        /* assert component types */
        $cType = self::assertComponentTypes( $cType );
        /* assert bool args */
        self:: assertBoolArguments( $flat, $any, $split );
        /* iterate components */
        $result       = [];
        $calendar     = clone $calendar;
        $calendar->sort( Vcalendar::UID );
        $rscaleUids   = RecurFactory::rruleRscaleCheck( $calendar, $cType );
        $compUIDold   = null;
        $exdateList   = $recurIdList = [];
        $INTERVAL_P1D = DateIntervalFactory::factory( $P1D );
        $calendar->resetCompCounter();
        while( $component = $calendar->getComponent()) {
            if( empty( $component )) {
                continue;
            }
            /* skip invalid type components */
            if( ! in_array( $component->getCompType(), $cType, true )) {
                continue;
            }
            /* get UID */
            $compUID = $component->getUid();
            if( in_array( $compUID, $rscaleUids, true )) { // UIDs to skip, rfc7529 6. Compatibility, option 2
                continue;
            }
            /* check UID */
            if( $compUIDold !== $compUID ) {
                $compUIDold = $compUID;
                $exdateList = $recurIdList = [];
            }
            /* select start from dtstart or due if dtstart is missing */
            if(( false === ( $prop = $component->getDtstart( true ))) &&
                (( Vcalendar::VTODO === $component->getCompType()) &&
                 ( false === ( $prop = $component->getDue( true ))))) {
                continue;
            }
            $compStart    = UtilDateTime::factory( $prop->value, $prop->params );
            $dtStartTz    = $compStart->getTimezoneName();
            $compStartHis = Util::$SP0;
            if( ! $prop->hasParamValue( Vcalendar::DATE )) {
                $his          = $compStart->getTime();
                $compStartHis = sprintf( $HIS, $his[0], $his[1], $his[2] );
            }
            /* get end date from dtend/due/duration properties */
            $compEnd = self::getCompEndDate( $component, $dtStartTz );
            if( empty( $compEnd )) {
                $compDuration = null; // DateInterval: no duration
                $compEnd      = $compStart->getClone();
                $compEnd->setTime( 23, 59, 59 );        // 23:59:59 the same day as start
            }
            else {
                if( $compEnd->format( $YMDn ) < $compStart->format( $YMDn )) { // MUST be after start date!!
                    $compEnd = $compStart->getClone();
                    $compEnd->setTime( 23, 59, 59 );    // 23:59:59 the same day as start or ???
                }
                $compDuration = $compStart->diff( $compEnd ); // DateInterval
            }
            $propEndName = ( isset( $compEnd->SCbools[self::$DUEEXIST] ))
                ? Vcalendar::X_CURRENT_DUE
                : Vcalendar::X_CURRENT_DTEND;
            $compType = $component->getCompType();
            $isFreebusyCompType = ( Vcalendar::VFREEBUSY === $compType );
            /**
             * Component with recurrence-id sorted before any rDate/rRule comp
             * Used to alter date(time) when found in dtstart/recurlist.
             * (Note, a missing sequence (expected here) is the same as sequence=0 so don't test for sequence.)
             * Highest sequence always last, will replace any previous
             */
            $recurId = null;
            if( ! $isFreebusyCompType &&
               ( false !== ( $prop = $component->getRecurrenceid( true )))) {
                $recurId  = UtilDateTime::factory( $prop->value, $prop->params, $dtStartTz );
                $rangeSet = $prop->hasParamKey( Vcalendar::RANGE, Vcalendar::THISANDFUTURE );
                $recurIdList[$recurId->key] = [
                    $compStart->getClone(),
                    $compEnd->getClone(),
                    $compDuration, // DateInterval
                    $rangeSet,
                    clone $component,
                ];        // save recur day to altered YmdHis/duration/range
                continue; // ignore all but first prop in the recurrence_id component
            } // end  HAS recurrence-id/sequence
            ksort( $recurIdList, SORT_STRING );
            self::updateRecurrIdComps( $component, $recurIdList );
            /* prepare */
            if( null !== $argStart ) {
                $fcnStart = UtilDateTime::factory( $argStart );
            }
            else {
                $fcnStart = $compStart->getClone();
                $fcnStart->setDate( (int)$startY, (int)$startM, (int)$startD );
                $fcnStart->setTime( 0, 0 );
            }
            $fcnStartYmd    = $fcnStart->format( $YMDn );
            $fcnStartYmdHis = $fcnStart->format( $YMDHIS2 );
            if( null !== $argEnd ) {
                $fcnEnd = UtilDateTime::factory( $argEnd );
            }
            else {
                $fcnEnd = $compEnd->getClone();
                $fcnEnd->setDate( (int)$endY, (int)$endM, (int)$endD );
                $fcnEnd->setTime( 23, 59, 59 );
            }
            /* set up work dates */
            $workStart = $compStart->getClone();
            $duration  = ( empty( $compDuration )) ? $INTERVAL_P1D : $compDuration; // DateInterval
            $workStart->sub( $duration );
            $workEnd   = clone $fcnEnd; // !!
            $workEnd->add( $duration );
            /* make a list of optional exclude dates for component occurence from exrule and exdate */
            if( ! $isFreebusyCompType ) {
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
            if(( ! $any && self::inScope( $compStart, $fcnStart, $compStart, $fcnEnd, $compStart->dateFormat )) ||
                          // occurs within the period
                 ( $any && self::inScope( $fcnEnd, $compStart, $fcnStart, $compEnd, $compStart->dateFormat ))) {
                /* add the selected component (WITHIN valid dates) to output array */
                if( $flat ) { // any=true/false, ignores split
                    if( empty( $recurId )) {
                        $result[$compUID] = clone $component;
                    }         // copy original to output (but not anyone with recurrence-id)
                }
                elseif( $split ) { // split the original component
                    $rStart = ( $compStart->format( $YMDHIS2 ) < $fcnStart->format( $YMDHIS2 ))
                        ? $fcnStart->getClone()
                        : $compStart->getClone();
                    $rEnd   = ( $compEnd->format( $YMDHIS2 ) > $fcnEnd->format( $YMDHIS2 ))
                        ? $fcnEnd->getClone()
                        : $compEnd->getClone();
                    $k      = $rStart->key;
                    if( isset( $exdateList[$k] )) {
                        --$xRecurrence;
                    }
                    else { // not excluded in exrule/exdate
                        if( isset( $recurIdList[$k] )) {  // change start day to new YmdHis/duration
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
                                ? clone $recurIdList[$k][4]
                                : clone $component;
                        } // end in recurIdList
                        else {
                            $startHis   = $compStart->getTime();
                            $endHis     = $compEnd->getTime();
                            $component2 = clone $component;
                        }
                        $cnt   = 0;
                        $tDate = clone $compStart;
                        while( $tDate->format( $YMDn ) < $fcnStart->format( $YMDn )) {
                            $cnt++; // in case fcnStart DAY AFTER cmpStart
                            $tDate->add( $INTERVAL_P1D );
                        }
                        // exclude any recurrence START date, found in exdatelist or recurrIdList
                        // but accept the reccurence-id comp itself
                        //     count the reccurence in days (incl start day)
                        $occurenceDays = DateTimeFactory::getDayDiff( $rStart, $rEnd );
                        $rEndYmd       = $rEnd->format( $YMDn );
                        while( $rStart->format( $YMDn ) <= $rEndYmd ) {
                            ++$cnt;
                            if( 1 < $occurenceDays ) {
                                $xPropVal = sprintf( $DAYOFDAYS, $cnt, $occurenceDays );
                                $component2->setXprop( Vcalendar::X_OCCURENCE, $xPropVal );
                            }
                            if( 1 < $cnt ) {
                                $rStart->setTime( 0, 0 ); // 0:0:0
                            }
                            else { // make sure to exclude start day from the recurrence pattern
                                $rStart->setTime( $startHis[0], $startHis[1], $startHis[2] );
                                $exdateList[$rStart->key] = $compDuration; // DateInterval
                            }
                            $component2->setXprop(
                                Vcalendar::X_CURRENT_DTSTART,
                                $rStart->format( $compStart->dateFormat )
                            );
                            if( ! empty( $compDuration )) { // DateInterval
                                $rWdate = $rStart->getClone();
                                self::setDurationEndTime( $rWdate, $rEnd, $cnt, $occurenceDays, $endHis );
                                $component2->setXprop( $propEndName, $rWdate->format( $compEnd->dateFormat ));
                            } // end if
                            self::nonFlatAppend( clone $component2, $result, $rStart, $compUID ); // copy to output
                            $rStart->add( $INTERVAL_P1D );
                        } // end while(( $rStart->format( 'Ymd' ) < $rEnd->format( 'Ymd' ))
                    } // end if( ! isset( $exdateList[$rStart->key] ))
                } // end elseif( $split )   -  else use component date
                else { // !$flat && !$split, i.e. no flat array and DTSTART within period
                    if( isset( $recurIdList[$compStart->key] )) {
                        $rStart     = $recurIdList[$compStart->key][0]->getClone();
                        $rEnd       = $recurIdList[$compStart->key][1]->getClone();
                        $component2 = $recurIdList[$compStart->key][4] ?? clone $component;
                    }
                    else {
                        $rStart     = $compStart->getClone();
                        $rEnd       = $compEnd->getClone();
                        $component2 = clone $component;
                    }
                    if( ! $any || ! isset( $exdateList[$rStart->key] )) {
                        // exclude any recurrence date, found in exdatelist
                        $component2->setXprop( Vcalendar::X_CURRENT_DTSTART, $rStart->format( $compStart->dateFormat ));
                        if( ! empty( $compDuration )) { // DateInterval
                            $component2->setXprop( $propEndName, $rEnd->format( $compEnd->dateFormat ));
                        } // end if
                        self::nonFlatAppend( clone $component2, $result, $rStart, $compUID ); // copy to output
                    }
                } // end else
            } // end (dt)start within the period OR occurs within the period
            /* *************************************************************
               if 'any' components, check components with reccurrence rules, removing all excluding dates
               *********************************************************** */
            if( true === $any ) {
                $recurList = [];
                if( ! $isFreebusyCompType ) {
                    /* make a list of optional repeating dates for component occurence, rrule, rdate */
                    self::getAllRRULEdates(
                        $component, $recurList,
                        $dtStartTz, $compStart, $workStart, $workEnd,
                        $compStartHis, $exdateList, $compDuration
                    );
                    $workStart = $fcnStart->getClone()->sub( ( empty( $compDuration ))
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
                    $component2   = clone $component;
                    $compUID      = $component2->getUid();
                    $YmdOld       = null;
                    $fcnEndYmdHis = $fcnEnd->format( $YMDHIS2 );
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
                            $component3 = null;
                            $recurFound = false;
                            foreach( $recurIdList as $k => $v ) {
                                if( str_starts_with((string) $k, $recurKeyYmd )) {
                                    $rStart            = $v[0]->getClone();
                                    $durationInterval2 = empty( $v[2] ) ? null : $v[2];  // DateInterval
                                    $component3        = clone $v[4];
                                    $recurFound        = true;
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
                            if( $rEnd->format( $YMDHIS2 ) > $fcnEndYmdHis ) {
                                $rEnd = clone $fcnEnd;
                            }
                            $endHis   = $rEnd->getTime();
                            ++$xRecurrence;
                            $cnt      = 0;
                            // count the reccurence in days (incl start day)
                            $occurenceDays = DateTimeFactory::getDayDiff( $rStart, $rEnd );
                            $rEndYmd  = $rEnd->format( $YMDn );
                            while( $rStart->format( $YMDn ) <= $rEndYmd ) {   // iterate.. .
                                ++$cnt;
                                if( $rStart->format( $YMDn ) < $fcnStartYmd ) { // date before dtstart
                                    $rStart->add( $INTERVAL_P1D ); // cycle rstart to dtstart DAY
                                    $rStart->setTime( 0, 0 );
                                    continue;
                                }
                                if( 2 === $cnt ) {
                                    $rStart->setTime( 0, 0 );
                                }
                                $component3->deleteXprop( $propEndName );
                                $component3->setXprop( Vcalendar::X_RECURRENCE, $xRecurrence );
                                if( 1 < $occurenceDays ) {
                                    $xPropVal = sprintf( $DAYOFDAYS, $cnt, $occurenceDays );
                                    $component3->setXprop( Vcalendar::X_OCCURENCE, $xPropVal );
                                }
                                $component3->setXprop(
                                    Vcalendar::X_CURRENT_DTSTART,
                                    $rStart->format( $compStart->dateFormat )
                                );
                                if( ! empty( $durationInterval2 )) {
                                    $rWdate = $rStart->getClone();
                                    self::setDurationEndTime( $rWdate, $rEnd, $cnt, $occurenceDays, $endHis );
                                    $component3->setXprop( $propEndName, $rWdate->format( $compEnd->dateFormat ));
                                }
                                self::nonFlatAppend( clone $component3, $result, $rStart, $compUID ); // copy to output
                                $rStart->add( $INTERVAL_P1D );
                            } // end while( $rStart->format( 'Ymd' ) <= $rEnd->format( 'Ymd' ))
                            unset( $rStart, $rEnd );
                        } // end elseif( $split )
                        elseif(( $rStartYmdHis = $rStart->format( $YMDHIS2 )) &&
                            ( $rStartYmdHis >= $fcnStartYmdHis ) && // date equal/after start
                            ( $rStartYmdHis <= $fcnEndYmdHis )) { // date before/equal end
                            // date within period, flat=false && split=false => one comp every recur startdate
                            ++$xRecurrence;
                            $component2->setXprop( Vcalendar::X_RECURRENCE, $xRecurrence );
                            $component2->setXprop(
                                Vcalendar::X_CURRENT_DTSTART,
                                $rStart->format( $compStart->dateFormat )
                            );
                            if( empty( $durationInterval )) {
                                $component2->deleteXprop( $propEndName );
                            }
                            else {
                                $component2->setXprop(
                                    $propEndName,
                                    $rStart->getClone()->add( $durationInterval )->format( $compEnd->dateFormat )
                                );
                            }
                            self::nonFlatAppend( clone $component2, $result, $rStart, $compUID ); // copy to output
                        } // end elseif( $rStart >= $fcnStart )...
                    } // end foreach( $recurList as $recurKey => $durationInterval )
                } // end if( 0 < count( $recurList ))
            } // end if( true === $any )
        } // end while( $component = $calendar->getComponent())
        if( 0 >= count( $result )) {
            return false;
        }
        if( ! $flat ) {
            self::ymdSort( $result );
        } // end elseif( !$flat )
        return $result;
    }

    /**
     * @param CalendarComponent|Vavailability|Vevent|Vfreebusy|Vjournal|Vtodo $component
     * @param array $result
     * @param DateTimeInterface $date
     * @param null|string $compUID
     * @return void
     */
    private static function nonFlatAppend(
        CalendarComponent|Vavailability|Vevent|Vfreebusy|Vjournal|Vtodo $component,
        array & $result,
        DateTimeInterface $date,
        ? string $compUID = ''
    ) : void
    {
        static $Y = 'Y';
        static $M = 'm';
        static $D = 'd';
        $xY = (int) $date->format( $Y );
        $xM = (int) $date->format( $M );
        $xD = (int) $date->format( $D );
        if( ! isset( $result[$xY][$xM][$xD][$compUID] )) {
            $result[$xY][$xM][$xD][$compUID] = [];
        }
        $result[$xY][$xM][$xD][$compUID][] = $component;
    }

    /**
     * @param array $result
     * @return void
     */
    private static function ymdSort( array & $result ) : void
    {
        static $SORTER = [ SortFactory::class, 'cmpfcn' ];
        foreach( $result as $y => $yList ) {
            foreach( $yList as $m => $mList ) {
                foreach( $mList as $d => $dList ) {
                    if( empty( $dList )) {
                        unset( $result[$y][$m][$d] );
                    }
                    else { // skip tricky UID-index
                        $temp = [];
                        foreach( $dList as $compVal ) {
                            if( is_array( $compVal )) {
                                foreach( $compVal as $comp ) {
                                    $temp[] = $comp;
                                }
                                continue;
                            }
                            $temp[] = $compVal;
                        } // end foreach
                        $result[$y][$m][$d] = $temp;
                        if( 1 < count( $result[$y][$m][$d] )) { // sort
                            foreach( $result[$y][$m][$d] as $comp ) {
                                SortFactory::setSortArgs( $comp );
                            }
                            usort( $result[$y][$m][$d], $SORTER );
                        }
                    } // end else
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
     * @param CalendarComponent|Vavailability|Vevent|Vfreebusy|Vjournal|Vtodo $component
     * @param array $exdateList
     * @param string            $dtStartTz
     * @param UtilDateTime      $compStart
     * @param UtilDateTime      $workStart
     * @param UtilDateTime      $workEnd
     * @param string            $compStartHis
     * @throws Exception
     * @since 2.41.64 - 2022-09-03
     */
    private static function getAllEXRULEdates(
        CalendarComponent|Vavailability|Vevent|Vfreebusy|Vjournal|Vtodo $component,
        array & $exdateList,
        string $dtStartTz,
        UtilDateTime $compStart,
        UtilDateTime $workStart,
        UtilDateTime $workEnd,
        string $compStartHis
    ) : void
    {
        if( in_array( $component->getCompType(), [ Vcalendar::VAVAILABILITY, Vcalendar::VFREEBUSY ], true )) {
            return;
        }
        if( false !== ( $prop = $component->getExrule( true ))) {
            $isValueDate = $prop->hasParamValue( Vcalendar::DATE );
            if( isset( $prop->value[Vcalendar::UNTIL] ) && ! $isValueDate ) {
                // convert UNTIL date to DTSTART timezone
                $prop->value[Vcalendar::UNTIL] = UtilDateTime::factory(
                    $prop->value[Vcalendar::UNTIL],
                    [ Vcalendar::TZID => Vcalendar::UTC ],
                    $dtStartTz
                );
            }
            $exdateList2 = [];
            RecurFactory::recur2date(
                $exdateList2,
                $prop->value,
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
     * @param CalendarComponent|Vavailability|Vevent|Vfreebusy|Vjournal|Vtodo $component
     * @param array $exdateList
     * @param string            $dtStartTz
     * @throws Exception
     * @since 2.41.64 - 2022-09-03
     */
    private static function getAllEXDATEdates(
        CalendarComponent|Vavailability|Vevent|Vfreebusy|Vjournal|Vtodo $component,
        array & $exdateList,
        string $dtStartTz
    ) : void
    {
        if( in_array( $component->getCompType(), [ Vcalendar::VAVAILABILITY, Vcalendar::VFREEBUSY ], true )) {
            return;
        }
        while( false !== ( $prop = $component->getExdate( null, true ))) {
            foreach( $prop->value as $exdate ) {
                $exdate = UtilDateTime::factory( $exdate, $prop->params, $dtStartTz );
                $exdateList[$exdate->key] = true;
            } // end - foreach( $exdate as $exdate )
        } // end while
    }

    /**
     * Update $recurList all RRULE dates (multiple values allowed)
     *
     * @param CalendarComponent|Vavailability|Vevent|Vfreebusy|Vjournal|Vtodo $component
     * @param array $recurList
     * @param string            $dtStartTz
     * @param UtilDateTime      $compStart
     * @param UtilDateTime      $workStart
     * @param UtilDateTime      $workEnd
     * @param string            $compStartHis
     * @param array $exdateList
     * @param null|DateInterval $compDuration
     * @throws Exception
     * @since 2.41.64 - 2022-09-03
     */
    private static function getAllRRULEdates(
        CalendarComponent|Vavailability|Vevent|Vfreebusy|Vjournal|Vtodo $component,
        array & $recurList,
        string $dtStartTz,
        UtilDateTime $compStart,
        UtilDateTime $workStart,
        UtilDateTime $workEnd,
        string $compStartHis,
        array & $exdateList,
        ? DateInterval $compDuration = null
    ) : void
    {
        if( in_array( $component->getCompType(), [ Vcalendar::VAVAILABILITY, Vcalendar::VFREEBUSY ], true )) {
            return;
        }
        $exdateYmdList = self::getYmdList( $exdateList );
        $recurYmdList  = self::getYmdList( $recurList );
        if( false !== ( $prop = $component->getRrule( true ))) {
            $isValueDate = $prop->hasParamValue( Vcalendar::DATE );
            if( isset( $prop->value[Vcalendar::UNTIL] ) && ! $isValueDate ) {
                // convert RRULE['UNTIL'] to same timezone as DTSTART !!
                $prop->value[Vcalendar::UNTIL] = UtilDateTime::factory(
                    $prop->value[Vcalendar::UNTIL],
                    [ Vcalendar::TZID => Vcalendar::UTC ],
                    $dtStartTz
                );
            }
            $recurList2  = [];
            RecurFactory::recur2date( $recurList2, $prop->value, $compStart, $workStart, $workEnd );
            foreach( $recurList2 as $recurKey => $recurValue ) { // recurkey=Ymd
                if( isset( $exdateYmdList[$recurKey] )) {        // exclude on Ymd basis
                    continue;
                }
                $YmdHisKey = $recurKey . $compStartHis;          // add opt His
                if( isset( $recurYmdList[$recurKey] )) {         // replace on Ymd basis
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
     * @param CalendarComponent|Vavailability|Vevent|Vfreebusy|Vjournal|Vtodo $component
     * @param array             $recurList
     * @param string            $dtStartTz
     * @param UtilDateTime      $workStart
     * @param UtilDateTime      $fcnEnd
     * @param string            $format
     * @param array             $exdateList
     * @param string            $compStartHis
     * @param null|DateInterval $compDuration
     * @throws Exception
     * @since 2.41.64 - 2022-09-03
     */
    private static function getAllRDATEdates(
        CalendarComponent|Vavailability|Vevent|Vfreebusy|Vjournal|Vtodo $component,
        array & $recurList,
        string $dtStartTz,
        UtilDateTime $workStart,
        UtilDateTime $fcnEnd,
        string $format,
        array & $exdateList,
        string $compStartHis,
        ? DateInterval $compDuration = null
    ) : void
    {
        if( in_array( $component->getCompType(), [ Vcalendar::VAVAILABILITY, Vcalendar::VFREEBUSY ], true )) {
            return;
        }
        $exdateYmdList = self::getYmdList( $exdateList );
        $recurYmdList  = self::getYmdList( $recurList );
        while( false !== ( $prop = $component->getRdate( null, true ))) {
            $rDateFmt = $prop->getValueParam() ?? Vcalendar::DATE_TIME;
            // DATE or PERIOD
            foreach( $prop->value as $theRdate ) {
                if( Vcalendar::PERIOD === $rDateFmt ) {            // all days within PERIOD
                    $rDate = UtilDateTime::factory( $theRdate[0], $prop->params, $dtStartTz );
                    if( ! self::inScope( $rDate, $workStart, $rDate, $fcnEnd, $format )) {
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
                            UtilDateTime::factory( $theRdate[1], $prop->params, $dtStartTz )
                        );
                        continue;
                    }
                    // period duration
                    $recurList[$rDate->key] = $theRdate[1];
                    continue;
                } // end if( Vcalendar::PERIOD == $rDateFmt )
                if( Vcalendar::DATE === $rDateFmt ) {          // single recurrence, DATE (=Ymd)
                    $rDate = UtilDateTime::factory(
                        $theRdate,
                        array_merge( $prop->params, [ Vcalendar::TZID => $dtStartTz ] ),
                        $dtStartTz
                    );
                    $rDateYmdHisKey = $rDate->key . $compStartHis;
                }
                else { // single recurrence, DATETIME
                    $rDate = UtilDateTime::factory( $theRdate, $prop->params, $dtStartTz );
                    // set start date for recurrence + DateInterval/false (+opt His)
                    $rDateYmdHisKey = $rDate->key;
                }
                $cmpKey = substr( $rDate->key, 0, 8 );
                switch( true ) {
                    case ( isset( $exdateYmdList[$cmpKey] )) : // excluded on Ymd basis
                        break;
                    case ( ! self::inScope( $rDate, $workStart, $rDate, $fcnEnd, $format )) :
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
     * @param array $YmdHisArr
     * @return array
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
            $startM = (int) $startY->format( $M );
            $startY = (int) $startY->format( $Y );
        }
        else {
            if( empty( $startY )) {
                $startY = (int) date( $Y );
            }
            if( empty( $startM )) {
                $startM = (int) date( $M );
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
            if( ! in_array( $theType, Vcalendar::$VCOMPS, true )) {
                $theType = Vcalendar::VEVENT;
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
     * @param Vavailability|Vevent|Vfreebusy|Vjournal|Vtodo $component
     * @param string            $dtStartTz
     * @return null|UtilDateTime
     * @throws Exception
     * @since 2.41.64 - 2022-09-03
     */
    private static function getCompEndDate(
        Vavailability|Vevent|Vfreebusy|Vjournal|Vtodo $component,
        string $dtStartTz
    ) : null | UtilDateTime
    {
        static $MINUS1DAY = '-1 day';
        $prop     = null;
        $compType = $component->getCompType();
        $isVavailabilty = ( Vcalendar::VAVAILABILITY === $compType );
        $isVevent       = ( Vcalendar::VEVENT === $compType );
        $isVfreebusy    = ( Vcalendar::VFREEBUSY === $compType );
        if(( $isVavailabilty || $isVevent || $isVfreebusy ) &&
          ( false !== ( $prop = $component->getDtend( true )))) {
            $compEnd = UtilDateTime::factory( $prop->value, $prop->params, $dtStartTz );
            $compEnd->SCbools[self::$DTENDEXIST] = true;
        }
        if( empty( $prop ) &&
            ( Vcalendar::VTODO === $compType ) &&
            ( false !== ( $prop = $component->getDue( true )))) {
            $compEnd = UtilDateTime::factory( $prop->value,  $prop->params, $dtStartTz );
            $compEnd->SCbools[self::$DUEEXIST] = true;
        }
        if( empty( $prop ) && // duration in dtend format
            ( Vcalendar::VJOURNAL !== $compType ) &&
            ( false !== ( $prop = $component->getDuration( true, true )))) {
            $compEnd = UtilDateTime::factory( $prop->value, $prop->params, $dtStartTz );
            $compEnd->SCbools[self::$DURATIONEXIST] = true;
        }
        if( ! empty( $prop ) && $prop->hasParamValue( Vcalendar::DATE )) {
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
     * @param UtilDateTime $rWdate
     * @param UtilDateTime $rEnd
     * @param int          $cnt
     * @param int          $occurenceDays
     * @param array $endHis
     * @since 2.26 - 2018-11-10
     */
    private static function setDurationEndTime(
        UtilDateTime $rWdate,
        UtilDateTime $rEnd,
        int          $cnt,
        int          $occurenceDays,
        array        $endHis
    ) : void
    {
        static $YMDn = 'Ymd';
        switch( true ) {
            case ( $cnt < $occurenceDays ) :
                $rWdate->setTime( 23, 59, 59 );
                break;
            case ( $rWdate->format( $YMDn ) < $rEnd->format( $YMDn )) :
                if( ( 0 === $endHis[0] ) && ( 0 === $endHis[1] ) && ( 0 === $endHis[2] )) {
                    $rWdate->setTime( 24, 0 ); // end exactly at midnight  // 24:00:00
                }
                else {
                    $rWdate->setTime( 23, 59, 59 ); // break at midnight, cont next day  // 23:59:59
                }
                break;
            default :
                $rWdate->setTime( $endHis[0], $endHis[1], $endHis[2] );
        } // end switch
    }

    /**
     * Update recurr-id-comps properties summary, description and comment if missing
     *
     * @param Vavailability|Vevent/Vtodo|Vjournal|Vfreebusy $component
     * @param array $recurIdComps
     * @since 2.41.64 - 2022-09-03
     */
    private static function updateRecurrIdComps(
        Vavailability|Vevent|Vtodo|Vjournal|Vfreebusy $component,
        array $recurIdComps
    ) : void
    {
        if( empty( $recurIdComps )) {
            return;
        }
        if( in_array( $component->getCompType(), [ Vcalendar::VAVAILABILITY, Vcalendar::VFREEBUSY ], true  )) {
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
                    $recurIdComps[$RecurrIdKey][4]->setSummary( $summary->value, $summary->params );
                }
            }
            if( ! empty( $description )) {
                $value = $recurIdComps[$RecurrIdKey][4]->getDescription();
                if( empty( $value )) {
                    $recurIdComps[$RecurrIdKey][4]->setDescription( $description->value, $description->params );
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
                $recurIdComps[$RecurrIdKey][4]->setComment( $prop->value, $prop->params );
            }
        } // end foreach
    }

    /**
     * Return array with selected components values from calendar based on specific property value(-s)
     *
     * @param Vcalendar $calendar
     * @param array $selectOptions (string) key => (mixed) value, (key=propertyName)
     * @return array
     * @since 2.40.7 - 2021-11-19
     */
    private static function selectComponents2( Vcalendar $calendar, array $selectOptions ) : array
    {
        $output        = [];
        $selectOptions = array_change_key_case( $selectOptions, CASE_UPPER );
        while( $component3 = $calendar->getComponent()) {
            if( empty( $component3 )) {
                continue;
            }
            if( ! in_array( $component3->getCompType(), Vcalendar::$VCOMPS, true )) {
                continue;
            }
            $uid = $component3->getUid();
            foreach( $selectOptions as $propName => $propValue ) {
                if( ! in_array( $propName, Vcalendar::$SELSORTPROPS, true )) {
                    continue;
                }
                if( ! is_array( $propValue )) {
                    $propValue = [ $propValue ];
                }
                if(( Vcalendar::UID === $propName ) && in_array( $uid, $propValue, true )) {
                    $output[$uid][] = $component3;
                    continue;
                }
                if( in_array( $propName, Vcalendar::$MPROPS1, true )) {
                    $propValues = [];
                    $component3->getProperties( $propName, $propValues );
                    $propValues = array_keys( $propValues );
                    foreach( $propValue as $theValue ) {
                        if( in_array( $theValue, $propValues, true )) { //  && ! isset( $output[$uid] )) {
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
                $outputUidIsSet = isset( $output[$uid] );
                if( is_array( $d )) {
                    foreach( $d as $part ) {
                        if( ! $outputUidIsSet && in_array( $part, $propValue, true )) {
                            $output[$uid][] = $component3;
                        }
                    }
                }
                elseif( ! $outputUidIsSet && ( Vcalendar::SUMMARY === $propName )) {
                    foreach( $propValue as $pval ) {
                        if( false !== stripos( $d, $pval )) {
                            $output[$uid][] = $component3;
                            break;
                        }
                    }
                }
                elseif( ! $outputUidIsSet && in_array( $d, $propValue, true )) {
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
