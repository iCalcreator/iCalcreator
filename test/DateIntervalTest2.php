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

namespace Kigkonsult\Icalcreator;

use Exception;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\RecurFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * class DateIntervalTest2, Testing DateInterval for TRIGGER
 *
 * @since  2.29.05 - 2019-06-20
 */
class DateIntervalTest2 extends DtBase
{
    /**
     * set and restore local timezone from const
     */
    public static $oldTimeZone = null;

    public static function setUpBeforeClass()
    {
        self::$oldTimeZone = date_default_timezone_get();
        date_default_timezone_set( LTZ );
    }

    public static function tearDownAfterClass()
    {
        date_default_timezone_set( self::$oldTimeZone );
    }

    /**
     * DateInterval123Provider Generator
     *
     * @param bool $inclYearMonth
     * @return array
     * @throws Exception
     * @static
     * @todo replace with DateInterval properties, remove durationArray2string()
     */
    public static function DateIntervalArrayGenerator( $inclYearMonth = true )
    {
        $base = [
            RecurFactory::$LCYEAR  => array_rand( array_flip( [ 1, 2 ] )),
            RecurFactory::$LCMONTH => array_rand( array_flip( [ 1, 12 ] )),
            RecurFactory::$LCDAY   => array_rand( array_flip( [ 1, 28 ] )),
            RecurFactory::$LCWEEK  => array_rand( array_flip( [ 1, 4 ] )),
            RecurFactory::$LCHOUR  => array_rand( array_flip( [ 1, 23 ] )),
            RecurFactory::$LCMIN   => array_rand( array_flip( [ 1, 59 ] )),
            RecurFactory::$LCSEC   => array_rand( array_flip( [ 1, 59 ] ))
        ];

        do {
            $random = [];
            $cnt = array_rand( array_flip( [ 1, 7 ] ));
            for( $x = 0; $x < $cnt; $x++ ) {
                $random = array_merge(
                    $random,
                    array_slice( $base, array_rand( array_flip( [ 1, 7 ] )), 1, true )
                );
            }
            if( 1 == array_rand( [ 1 => 1, 2 => 2 ] )) {
                unset( $random[RecurFactory::$LCWEEK] );
                $random = array_filter( $random );
            }
            if( ! $inclYearMonth ) {
                unset( $random[RecurFactory::$LCYEAR], $random[RecurFactory::$LCMONTH] );
                $random = array_filter( $random );
            }
        } while( 1 > count( $random ));
        if( isset( $random[RecurFactory::$LCWEEK] )) {
            $random = [ RecurFactory::$LCWEEK => $random[RecurFactory::$LCWEEK] ];
        }
        $random2 = [];
        foreach( array_keys( $base ) as $key ) {
            if( isset( $random[$key] )) {
                $random2[$key] = $random[$key];
            }
        }
        return $random2;
    }

    /**
     * Return an iCal formatted string from (internal array) duration
     *
     * @param array $duration , array( year, month, day, week, day, hour, min, sec )
     * @return string
     * @static
     * @since  2.26.14 - 2019-02-12
     */
    public static function durationArray2string( array $duration )
    {
        static $PT0H0M0S = 'PT0H0M0S';
        static $Y = 'Y';
        static $T = 'T';
        static $W = 'W';
        static $D = 'D';
        static $H = 'H';
        static $M = 'M';
        static $S = 'S';
        if( ! isset( $duration[RecurFactory::$LCYEAR] )  &&
            ! isset( $duration[RecurFactory::$LCMONTH] ) &&
            ! isset( $duration[RecurFactory::$LCDAY] )   &&
            ! isset( $duration[RecurFactory::$LCWEEK] )  &&
            ! isset( $duration[RecurFactory::$LCHOUR] )  &&
            ! isset( $duration[RecurFactory::$LCMIN] )   &&
            ! isset( $duration[RecurFactory::$LCSEC] )) {
            return Util::$SP0;
        }
        if( Util::issetAndNotEmpty( $duration, RecurFactory::$LCWEEK )) {
            return DateIntervalFactory::$P . $duration[RecurFactory::$LCWEEK] . $W;
        }
        $result = DateIntervalFactory::$P;
        if( Util::issetAndNotEmpty( $duration, RecurFactory::$LCYEAR )) {
            $result .= $duration[RecurFactory::$LCYEAR] . $Y;
        }
        if( Util::issetAndNotEmpty( $duration, RecurFactory::$LCMONTH )) {
            $result .= $duration[RecurFactory::$LCMONTH] . $M;
        }
        if( Util::issetAndNotEmpty( $duration, RecurFactory::$LCDAY )) {
            $result .= $duration[RecurFactory::$LCDAY] . $D;
        }
        $hourIsSet = ( Util::issetAndNotEmpty( $duration, RecurFactory::$LCHOUR ));
        $minIsSet  = ( Util::issetAndNotEmpty( $duration, RecurFactory::$LCMIN ));
        $secIsSet  = ( Util::issetAndNotEmpty( $duration, RecurFactory::$LCSEC ));
        if( $hourIsSet || $minIsSet || $secIsSet ) {
            $result .= $T;
        }
        if( $hourIsSet ) {
            $result .= $duration[RecurFactory::$LCHOUR] . $H;
        }
        if( $minIsSet ) {
            $result .= $duration[RecurFactory::$LCMIN] . $M;
        }
        if( $secIsSet ) {
            $result .= $duration[RecurFactory::$LCSEC] . $S;
        }
        if( DateIntervalFactory::$P == $result ) {
            $result = $PT0H0M0S;
        }
        return $result;
    }


    /**
     * DateInterval678ProviderDateInterval sub-provider, TRIGGER
     *
     * @param array $dateIntervalArray
     * @param int   $cnt
     * @return array
     * @throws Exception
     */
    public static function DateInterval678ProviderDateInterval( array $dateIntervalArray, $cnt )
    {
        $dateInterval = (array) DateIntervalFactory::factory(
            self::durationArray2string( $dateIntervalArray )
        );
        $getValue = DateIntervalFactory::DateIntervalArr2DateInterval( $dateInterval );
        $params   = [];
        $s      = array_rand( [Vcalendar::START => 1, Vcalendar::END => 2] );
        $s1     = null;
        if( Vcalendar::START == $s ) {
            $s1 = array_rand( [ 1 => 1, 2 => 2 ] );
            if( 1 == $s1 ) {
                $params[Vcalendar::RELATED] = Vcalendar::START; // default
            }
        }
        else {
            $params[Vcalendar::RELATED] = Vcalendar::END;
        }
        $b = array_rand( ['before' => 1, 'after' => 2] );
        if( 'before' == $b ) {
            $diPrefix                   = '-';
            $dateInterval['invert']     = 1;
            $getValue->invert           = 1;
        }
        else {
            $diPrefix                   = null;
        }
        $params['X-KEY'] = 'X-Value';
        $getValue = [
            Util::$LCvalue  => $getValue,
            Util::$LCparams => $params,
        ];
        if( isset( $params[Vcalendar::RELATED] ) && ( Vcalendar::START == $params[Vcalendar::RELATED] )) { // remove default
            unset( $getValue[Util::$LCparams][Vcalendar::RELATED] );
        }
        return [
            ( 6000 + $cnt ) . $s . $s1 . $b,
            DateIntervalFactory::DateIntervalArr2DateInterval( $dateInterval ),
            $params,
            $getValue,
            ParameterFactory::createParams( $getValue[Util::$LCparams] ) .
            ':' . $diPrefix . DateIntervalFactory::dateInterval2String(
                DateIntervalFactory::conformDateInterval(
                    DateIntervalFactory::DateIntervalArr2DateInterval( $dateInterval )
                )
            )
        ];
    }


    /**
     * DateInterval678ProviderDateIntervalString sub-provider, TRIGGER
     *
     * @param array $input
     * @param int   $cnt
     * @return array
     * @throws Exception
     */
    public static function DateInterval678ProviderDateIntervalString( array $input, $cnt )
    {
        $dateIntervalArray = $input;
        $value  = DateIntervalFactory::factory( self::durationArray2string( $dateIntervalArray ));
        $params = [];
        $s      = array_rand( [Vcalendar::START => 1, Vcalendar::END => 2] );
        $s1     = null;
        if( Vcalendar::START == $s ) {
            $s1 = array_rand( [ 1 => 1, 2 => 2 ] );
            if( 1 == $s1) {
                $params[Vcalendar::RELATED] = Vcalendar::START; // default
            }
        }
        else {
            $params[Vcalendar::RELATED] = Vcalendar::END;
        }
        $b = array_rand( ['before' => 1, 'after' => 2] );
        if( 'before' == $b) {
            $diPrefix      = '-';
            $value->invert = 1;
        }
        else {
            $diPrefix    = null;
        }
        $params['X-KEY'] = 'X-Value';
        $getValue = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params,
        ];
        if( isset( $params[Vcalendar::RELATED] ) && ( Vcalendar::START == $params[Vcalendar::RELATED] )) { // remove default
            unset( $getValue[Util::$LCparams][Vcalendar::RELATED] );
        }
        return [
            ( 8000 + $cnt ) . $s . $s1 . $b,
            $diPrefix . self::durationArray2string( $dateIntervalArray ),
            $params,
            $getValue,
            ParameterFactory::createParams( $getValue[Util::$LCparams] ) .
            ':' . $diPrefix . DateIntervalFactory::dateInterval2String(
                DateIntervalFactory::conformDateInterval(
                    DateIntervalFactory::factory(
                        self::durationArray2string( $dateIntervalArray )
                    )
                )
            ),
        ];
    }

    /**
     * testDateInterval678 provider
     *
     * @return array
     * @throws Exception
     */
    public function DateInterval678Provider()
    {
        $dataArr = [];

        // DateInterval input
        $cnt = 0;
        while( 100 > $cnt ) {
            $dataArr[] = self::DateInterval678ProviderDateInterval(
                self::DateIntervalArrayGenerator(),
                $cnt
            );
            $cnt += 1;
        }

        // string input
        $cnt = 0;
        while( 100 > $cnt ) {
            $dataArr[] = self::DateInterval678ProviderDateIntervalString(
                self::DateIntervalArrayGenerator(),
                $cnt
            );
            $cnt += 1;
        }

        return $dataArr;
    }

    /**
     * Testing DateInterval for TRIGGER
     *
     * @test
     * @dataProvider DateInterval678Provider
     * @param int|string $case
     * @param mixed  $value
     * @param array  $params
     * @param array  $expectedGet
     * @param string $expectedString
     * @throws Exception
     */
    public function testDateInterval678( $case, $value, $params, $expectedGet, $expectedString )
    {
        static $compProp = [
            Vcalendar::VALARM  => [ Vcalendar::TRIGGER ],
        ];
        $c = new Vcalendar();
        foreach( $compProp as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp   = $c->newVevent()->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = StringFactory::getGetMethodName( $propName );
                $createMethod = StringFactory::getCreateMethodName( $propName );
                $deleteMethod = StringFactory::getDeleteMethodName( $propName );
                $setMethod    = StringFactory::getSetMethodName( $propName );
                /*
                error_log( __FUNCTION__ . ' #' . $case . ' in ' . // test ###
                    var_export( [ Util::$LCvalue => $value, Util::$LCparams => $params ], true ) // test ###
                ); // test ###
                */
                $comp->{$setMethod}( $value, $params );

                $getValue = $comp->{$getMethod}( true );
                // error_log( __FUNCTION__ . ' #' . $case . ' get ' . var_export( $getValue, true )); // test ###
                $this->assertEquals(
                    $expectedGet,
                    $getValue,
                    "get error in case #{$case}-1, <{$theComp}>->{$getMethod}"
                );

                $this->assertEquals(
                    strtoupper( $propName ) . $expectedString,
                    trim( $comp->{$createMethod}()),
                    "create error in case #{$case}-2, <{$theComp}>->{$createMethod}"
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}( true ),
                    "get (after delete) error in case #{$case}-3, <{$theComp}>->{$deleteMethod}"
                );
                $comp->{$setMethod}( $value, $params ); // test ###
            }
        } // end foreach

        $this->parseCalendarTest( $case, $c, $expectedString );
    }
}
