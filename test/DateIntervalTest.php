<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.28
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

namespace Kigkonsult\Icalcreator;

use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Exception;

/**
 * class DateTzTest, testing VALUE DATETIME for Standard/Daylight (allways local time), also empty value, DTSTART
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  2.27.14 - 2019-01-24
 */
class DateIntervalTest extends DtBase
{
    /**
     * set and restore local timezone from const
     */
    public static $oldTimeZone = null;
    public static function setUpBeforeClass() {
        self::$oldTimeZone = date_default_timezone_get();
        date_default_timezone_set( LTZ );
    }
    public static function tearDownAfterClass() {
        date_default_timezone_set( self::$oldTimeZone );
    }

    /**
     * DateInterval123Provider Generator
     *
     * @param int $inclYearMonth
     * @return array
     * @throws Exception
     * @static
     */
    public static function DateIntervalArrayGenerator( $inclYearMonth = true) {
        $base = [
            Util::$LCYEAR  => random_int( 1, 2 ),
            Util::$LCMONTH => random_int( 1, 12 ),
            Util::$LCDAY   => random_int( 1, 28 ),
            Util::$LCWEEK  => random_int( 1, 4 ),
            Util::$LCHOUR  => random_int( 1, 23 ),
            Util::$LCMIN   => random_int( 1, 59 ),
            Util::$LCSEC   => random_int( 1, 59 )
        ];

        do {
            $random = [];
            $cnt = random_int( 1, 7 );
            for( $x = 0; $x < $cnt; $x++ ) {
                $random = array_merge(
                    $random,
                    array_slice( $base, random_int( 1, 7 ), 1, true )
                );
            }
            if( 1 == random_int( 1, 2 )) {
                unset( $random[Util::$LCWEEK] );
                $random = array_filter( $random );
            }
            if( ! $inclYearMonth ) {
                unset( $random[Util::$LCYEAR], $random[Util::$LCMONTH] );
                $random = array_filter( $random );
            }
        } while( 1 > count( $random ));
        if( isset( $random[Util::$LCWEEK] )) {
            $random = [ Util::$LCWEEK => $random[Util::$LCWEEK] ];
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
     * DateInterval123Provider DateInterval sub-provider
     *
     * @param array $input
     * @param int   $cnt
     * @return array
     * @throws Exception
     */
    public static function DateInterval123ProviderDateInterval( array $input, $cnt ) {
        $dateIntervalArray = $input;
        $dateInterval = (array) DateIntervalFactory::factory(
            DateIntervalFactory::durationArray2string( $dateIntervalArray )
        );
        $getValue = DateIntervalFactory::dateInterval2arr(
            DateIntervalFactory::conformDateInterval(
                DateIntervalFactory::DateIntervalArr2DateInterval( $dateInterval )
            )
        );
        if( isset( $getValue[Util::$LCWEEK] ) && empty( $getValue[Util::$LCWEEK] )) {
            unset( $getValue[Util::$LCWEEK] );
        }
        return [
            1000 + $cnt,
            DateIntervalFactory::DateIntervalArr2DateInterval( $dateInterval ),
            [
                Util::$LCvalue  => $getValue,
                Util::$LCparams => []
            ],
            ':' . DateIntervalFactory::dateInterval2String(
                DateIntervalFactory::conformDateInterval(
                    DateIntervalFactory::DateIntervalArr2DateInterval( $dateInterval )
                )
            )
        ];
    }

    /**
     * DateInterval123Provider DateIntervalArray sub-provider
     *
     * @param array $input
     * @param int   $cnt
     * @return array
     * @throws Exception
     */
    public static function DateInterval123ProviderDateIntervalArray( array $input, $cnt ) {
        $dateIntervalArray = $input;
        $getValue          = DateIntervalFactory::dateInterval2arr(
            DateIntervalFactory::conformDateInterval(
                DateIntervalFactory::factory(
                    DateIntervalFactory::durationArray2string( $dateIntervalArray )
                )
            )
        );
        if( isset( $getValue[Util::$LCWEEK] ) && empty( $getValue[Util::$LCWEEK] )) {
            unset( $getValue[Util::$LCWEEK] );
        }
        return [
            2000 + $cnt,
            $dateIntervalArray,
            [
                Util::$LCvalue  => $getValue,
                Util::$LCparams => [],
            ],
            ':' . DateIntervalFactory::dateInterval2String(
                DateIntervalFactory::conformDateInterval(
                    DateIntervalFactory::factory(
                        DateIntervalFactory::durationArray2string( $dateIntervalArray )
                    )
                )
            ),
        ];
    }

    /**
     * DateInterval123Provider DateIntervalString sub-provider
     *
     * @param array $input
     * @param int   $cnt
     * @return array
     * @throws Exception
     */
    public static function DateInterval123ProviderDateIntervalString( array $input, $cnt ) {
        $dateIntervalArray = $input;
        $getValue          = DateIntervalFactory::dateInterval2arr(
            DateIntervalFactory::conformDateInterval(
                DateIntervalFactory::factory(
                    DateIntervalFactory::durationArray2string( $dateIntervalArray )
                )
            )
        );
        if( isset( $getValue[Util::$LCWEEK] ) && empty( $getValue[Util::$LCWEEK] )) {
            unset( $getValue[Util::$LCWEEK] );
        }
        return [
            3000 + $cnt,
            DateIntervalFactory::durationArray2string( $dateIntervalArray ),
            [
                Util::$LCvalue  => $getValue,
                Util::$LCparams => [],
            ],
            ':' . DateIntervalFactory::dateInterval2String(
                DateIntervalFactory::conformDateInterval(
                    DateIntervalFactory::factory(
                        DateIntervalFactory::durationArray2string( $dateIntervalArray )
                    )
                )
            ),
        ];
    }

    /**
     * testDateInterval123 provider
     *
     * @return array
     * @throws Exception
     */
    public function DateInterval123Provider() {

        $zeroInput = [
            Util::$LCHOUR  => 0,
            Util::$LCMIN   => 0,
            Util::$LCSEC   => 0
        ];

        $dataArr = [];

        // DateInterval zero input
        $cnt = 1;
        $dataArr[] = self::DateInterval123ProviderDateInterval( $zeroInput, $cnt );
        // DateInterval non-zero input
        while( 300 > $cnt ) {
            $cnt += 1;
            $dataArr[] = self::DateInterval123ProviderDateInterval( self::DateIntervalArrayGenerator(), $cnt );
        }

        // array zero input
        $cnt = 1;
        $dataArr[] = self::DateInterval123ProviderDateIntervalArray( $zeroInput, $cnt );
        // array non-zero input
        while( 300 > $cnt ) {
            $cnt += 1;
            $dataArr[] = self::DateInterval123ProviderDateIntervalArray( self::DateIntervalArrayGenerator(), $cnt );
        }

        // string zero input
        $cnt = 1;
        $dataArr[] = self::DateInterval123ProviderDateIntervalString( $zeroInput, $cnt );
        // string non-zero input
        while( 300 > $cnt ) {
            $cnt += 1;
            $dataArr[] = self::DateInterval123ProviderDateIntervalString( self::DateIntervalArrayGenerator(), $cnt );
        }

        return $dataArr;
    }

    /**
     * Testing DateInterval for DURATION and TRIGGER, input DateInterval, array and string
     *
     * @test
     * @dataProvider DateInterval123Provider
     * @param int|string $case
     * @param mixed  $value
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateInterval123(
        $case,
        $value,
        $expectedGet,
        $expectedString
    ) {
        static $compProp = [
            Vcalendar::VEVENT    => [ Vcalendar::DURATION ],
            Vcalendar::VTODO     => [ Vcalendar::DURATION ],
            Vcalendar::VFREEBUSY => [ Vcalendar::DURATION ],
            Vcalendar::VALARM    => [ Vcalendar::DURATION, Vcalendar::TRIGGER ],
        ];
        $c = new Vcalendar();
        foreach( $compProp as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            if( Vcalendar::VALARM == $theComp ) {
                $comp   = $c->newVevent()->{$newMethod}();
            }
            else {
                $comp   = $c->{$newMethod}();
            }
            foreach( $props as $propName ) {
                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );
                // error_log( __FUNCTION__ . ' #' . $case . ' in ' . var_export( $value, true )); // test ###
                $comp->{$setMethod}( $value );

                $getValue = $comp->{$getMethod}( true );
                // error_log( __FUNCTION__ . ' #' . $case . ' get ' . var_export( $getValue, true )); // test ###
                if( Vcalendar::TRIGGER == $propName ) {
                    $expectedGet[Util::$LCvalue]['relatedStart'] = true;
                    unset( $expectedGet[Util::$LCvalue]['before'], $getValue[Util::$LCvalue]['before'] );
                }
                $this->assertEquals(
                    $expectedGet,
                    $getValue,
                    "get error in case #{$case}, <{$theComp}>->{$createMethod}"
                );

                $this->assertEquals(
                    strtoupper( $propName ) . $expectedString,
                    trim( $comp->{$createMethod}()),
                    "create error in case #{$case}, <{$theComp}>->{$createMethod}"
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}( true ),
                    "get (after delete) error in case #{$case}, <{$theComp}>->{$createMethod}"
                );
                $comp->{$setMethod}( $value ); // test ###
            }
            if( Vcalendar::VALARM != $theComp ) {
                $comp->setDtstart( '20190101T080000 UTC' );
                $this->assertGreaterThanOrEqual(
                    '20190101080000Z',
                    implode( '', $comp->getDuration( false, true ))
                );
            }
        }

        $this->parseCalendarTest( $case, $c, $expectedString );

    }


    /**
     * testDateInterval4 provider
     *
     * @return array
     * @throws Exception
     */
    public function DateInterval4Provider() {

        $dataArr = [];

        // all args input input
        $cnt = 0;
        while( 410 > $cnt ) {
            $dateIntervalArray = ( 401 > $cnt )
                ? self::DateIntervalArrayGenerator( false )
                : self::DateIntervalArrayGenerator( true );
            $value = DateIntervalFactory::dateInterval2arr(
                DateIntervalFactory::conformDateInterval(
                    DateIntervalFactory::factory(
                        DateIntervalFactory::durationArray2string( $dateIntervalArray )
                    )
                )
            );
            $value = array_filter( $value );
            $params   = [];
            $s      = array_rand( [Vcalendar::START => 1, Vcalendar::END => 2] );
            $s1     = null;
            if( Vcalendar::START == $s ) {
                $value['relatedStart'] = true;
                $s1 = random_int( 1, 4 );
                switch( $s1 ) {
                    case 1 :
                        $dateIntervalArray['relatedStart'] = true;
                        break;
                    case 2 :
                        $params[Vcalendar::RELATED]        = Vcalendar::START; // default
                        break;
                    case 3 :
                        break;
                    default :
                        $dateIntervalArray['relatedStart'] = true;
                        $params[Vcalendar::RELATED]        = Vcalendar::START;
                }
            }
            else {
                $value['relatedStart']      = false;
                $params[Vcalendar::RELATED] = Vcalendar::END;
                $s1 = random_int( 5, 6 );
                if( 5 == $s1 ) {
                    $dateIntervalArray['relatedStart'] = false;
                }
            }
            $b = array_rand( ['before' => 1, 'after' => 2] );
            if( 'before' == $b ) {
                $dateIntervalArray['before'] = true;
                $value['before']             = true;
                $diPrefix                    = '-';
            }
            else {
                $dateIntervalArray['before'] = false;
                $value['before']             = false;
                $diPrefix                    = null;
            }
            $params['X-KEY'] = 'X-Value';
            $getValue =  [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params,
            ];
            if( isset( $params[Vcalendar::RELATED] ) && ( Vcalendar::START == $params[Vcalendar::RELATED] )) { // remove default
                unset( $getValue[Util::$LCparams][Vcalendar::RELATED] );
            }
            $dataArr[$cnt] = [
                ( 4000 + $cnt ) . $s . $s1 . $b,
                $dateIntervalArray,
                $params,
                $getValue,
                ParameterFactory::createParams( $getValue[Util::$LCparams] ) .
                    ':' . $diPrefix . DateIntervalFactory::dateInterval2String(
                    DateIntervalFactory::conformDateInterval(
                        DateIntervalFactory::factory(
                            DateIntervalFactory::durationArray2string( array_filter( $dateIntervalArray ))
                        )
                    )
                ),
            ];
            $cnt += 1;
        } // end while
        // testing Ymd only as arg input
        for( $cnt = 401; $cnt < 211; $cnt++ ) {
            $dataArr[$cnt][3][Util::$LCvalue][Util::$LCWEEK] = null;
            $dataArr[$cnt][3][Util::$LCvalue][Util::$LCHOUR] = null;
            $dataArr[$cnt][3][Util::$LCvalue][Util::$LCMIN]  = null;
            $dataArr[$cnt][3][Util::$LCvalue][Util::$LCSEC]  = null;
            $dataArr[$cnt][3][Util::$LCvalue] = array_filter( $dataArr[$cnt][3][Util::$LCvalue] );
        }
        return $dataArr;
    }

    /**
     * Testing DateInterval, test using the 'all args' set-methods invoke, for TRIGGER
     *
     * @test
     * @dataProvider DateInterval4Provider
     * @param int|string $case
     * @param mixed  $value
     * @param array  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateInterval4(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compProp = [
            Vcalendar::VALARM    => [ Vcalendar::TRIGGER ],
        ];
        static $keys = null;
        if( empty( $keys )) {
            $keys = [
                Util::$LCYEAR, Util::$LCMONTH, Util::$LCDAY, Util::$LCWEEK,
                Util::$LCHOUR, Util::$LCMIN,   Util::$LCSEC, 'relatedStart', 'before'
            ];
        }
        $c = new Vcalendar();
        foreach( $compProp as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            if( Vcalendar::VALARM == $theComp ) {
                $comp   = $c->newVevent()->{$newMethod}();
            }
            else {
                $comp   = $c->{$newMethod}();
            }
            foreach( $props as $propName ) {
                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );
                // error_log( __FUNCTION__ . ' #' . $case . ' in ' . var_export( $value, true )); // test ###
                $relatedStart = $before = null;
                foreach( $keys as $key ) {
                    ${$key} = ( isset( $value[$key] )) ? $value[$key] : null;
                }
                $value['before']             = false;


                $comp->{$setMethod}(
                    ${Util::$LCYEAR}, ${Util::$LCMONTH}, ${Util::$LCDAY}, ${Util::$LCWEEK},
                    ${Util::$LCHOUR}, ${Util::$LCMIN}, ${Util::$LCSEC},
                    $relatedStart, $before, $params
                );
                $getValue = $comp->{$getMethod}( true );
                // error_log( __FUNCTION__ . ' #' . $case . ' get ' . var_export( $getValue, true )); // test ###
                $this->assertEquals(
                    $expectedGet,
                    $getValue,
                    "get error in case #{$case}, <{$theComp}>->{$getMethod}"
                );
                $this->assertEquals(
                    strtoupper( $propName ) . $expectedString,
                    trim( $comp->{$createMethod}()),
                    "create error in case #{$case}, <{$theComp}>->{$createMethod}"
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}( true ),
                    "get (after delete) error in case #{$case}, <{$theComp}>->{$deleteMethod}" );

                if( Vcalendar::TRIGGER == $propName ) {
                    $comp->{$setMethod}(
                        ${Util::$LCYEAR}, ${Util::$LCMONTH}, ${Util::$LCDAY}, ${Util::$LCWEEK},
                        ${Util::$LCHOUR}, ${Util::$LCMIN}, ${Util::$LCSEC},
                        null, $before, $params
                    );
                }
                else {
                    $comp->{$setMethod}( ${Util::$LCDAY}, ${Util::$LCHOUR}, ${Util::$LCMIN}, ${Util::$LCSEC} ); // test ###
                }
            }
        }

        $this->parseCalendarTest( $case, $c, $expectedString );

    }

    /**
     * testDateInterval5 provider
     *
     * @return array
     * @throws Exception
     */
    public function DateInterval5Provider() {

        $dataArr = [];

        // all args input input
        $cnt = 0;
        while( 200 > $cnt ) {
            $dateIntervalArray = self::DateIntervalArrayGenerator( false );
            $value = DateIntervalFactory::dateInterval2arr(
                DateIntervalFactory::conformDateInterval(
                    DateIntervalFactory::factory(
                        DateIntervalFactory::durationArray2string( $dateIntervalArray )
                    )
                )
            );
            $value = array_filter( $value );
            $params['X-KEY'] = 'X-Value';
            $getValue =  [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params,
            ];
            $dataArr[] = [
                ( 5000 + $cnt ),
                $dateIntervalArray,
                $params,
                $getValue,
                ParameterFactory::createParams( $getValue[Util::$LCparams] ) .
                ':' . DateIntervalFactory::dateInterval2String(
                    DateIntervalFactory::conformDateInterval(
                        DateIntervalFactory::factory(
                            DateIntervalFactory::durationArray2string( array_filter( $dateIntervalArray ))
                        )
                    )
                ),
            ];
            $cnt += 1;
        }
        return $dataArr;
    }

    /**
     * Testing DateInterval, test using the 'all args' set-methods invoke, for DURATION
     *
     * @test
     * @dataProvider DateInterval5Provider
     * @param int|string $case
     * @param mixed  $value
     * @param array  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateInterval5(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compProp = [
            Vcalendar::VEVENT => [ Vcalendar::DURATION ],
        ];
        static $keys = null;
        if( empty( $keys )) {
            $keys = [
                Util::$LCWEEK, Util::$LCDAY, Util::$LCHOUR, Util::$LCMIN,   Util::$LCSEC,
            ];
        }
        $c = new Vcalendar();
        foreach( $compProp as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $c->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );
                // error_log( __FUNCTION__ . ' #' . $case . ' in ' . var_export( $value, true )); // test ###
                foreach( $keys as $key ) {
                    ${$key} = ( isset( $value[$key] )) ? $value[$key] : null;
                }
                $comp->{$setMethod}(
                    ${Util::$LCWEEK}, ${Util::$LCDAY}, ${Util::$LCHOUR}, ${Util::$LCMIN}, ${Util::$LCSEC}, $params
                );
                
                $getValue = $comp->{$getMethod}( true );
                // error_log( __FUNCTION__ . ' #' . $case . ' get ' . var_export( $getValue, true )); // test ###
                $this->assertEquals(
                    $expectedGet,
                    $getValue,
                    "get error in case #{$case}, <{$theComp}>->{$getMethod}"
                );
                $this->assertEquals(
                    strtoupper( $propName ) . $expectedString,
                    trim( $comp->{$createMethod}()),
                    "create error in case #{$case}, <{$theComp}>->{$createMethod}"
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}( true ),
                    "get (after delete) error in case #{$case}, <{$theComp}>->{$deleteMethod}" );

                $comp->{$setMethod}(
                    ${Util::$LCWEEK}, ${Util::$LCDAY}, ${Util::$LCHOUR}, ${Util::$LCMIN}, ${Util::$LCSEC}, $params
                );
            }
        }

        $this->parseCalendarTest( $case, $c, $expectedString );

    }

    /**
     * DateInterval678Provider DateInterval sub-provider
     *
     * @param array $dateIntervalArray
     * @param int   $cnt
     * @return array
     * @throws Exception
     */
    public static function DateInterval678ProviderDateInterval( array $dateIntervalArray, $cnt ) {
        $dateInterval = (array) DateIntervalFactory::factory(
            DateIntervalFactory::durationArray2string( $dateIntervalArray )
        );
        $getValue = DateIntervalFactory::dateInterval2arr(
            DateIntervalFactory::conformDateInterval(
                DateIntervalFactory::DateIntervalArr2DateInterval( $dateInterval )
            )
        );
        if( isset( $getValue[Util::$LCWEEK] ) && empty( $getValue[Util::$LCWEEK] )) {
            unset( $getValue[Util::$LCWEEK] );
        }
        $params   = [];
        $s      = array_rand( [Vcalendar::START => 1, Vcalendar::END => 2] );
        $s1     = null;
        if( Vcalendar::START == $s ) {
            $getValue['relatedStart']   = true;
            $s1 = random_int( 1, 2 );
            if( 1 == $s1 ) {
                $params[Vcalendar::RELATED] = Vcalendar::START; // default
            }
        }
        else {
            $getValue['relatedStart']   = false;
            $params[Vcalendar::RELATED] = Vcalendar::END;
        }
        $b = array_rand( ['before' => 1, 'after' => 2] );
        if( 'before' == $b ) {
            $diPrefix                   = '-';
            $dateInterval['invert']     = 1;
            $getValue['before']         = true;
        }
        else {
            $diPrefix                   = null;
            $getValue['before']         = false;
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
     * DateInterval678Provider DateIntervalArray sub-provider
     *
     * @param array $input
     * @param int   $cnt
     * @return array
     * @throws Exception
     */
    public static function DateInterval678ProviderDateIntervalArray( array $input, $cnt ) {
        $dateIntervalArray = $input;
        $getValue          = DateIntervalFactory::dateInterval2arr(
            DateIntervalFactory::conformDateInterval(
                DateIntervalFactory::factory(
                    DateIntervalFactory::durationArray2string( $dateIntervalArray )
                )
            )
        );
        if( isset( $getValue[Util::$LCWEEK] ) && empty( $getValue[Util::$LCWEEK] )) {
            unset( $getValue[Util::$LCWEEK] );
        }
        $params = [];
        $s      = array_rand( [Vcalendar::START => 1, Vcalendar::END => 2] );
        $s1     = null;
        if( Vcalendar::START == $s ) {
            $getValue['relatedStart']   = true;
            $s1 = random_int( 1, 2 );
            if( 1 == $s1 ) {
                $params[Vcalendar::RELATED] = Vcalendar::START; // default
            }
        }
        else {
            $getValue['relatedStart']   = false;
            $params[Vcalendar::RELATED] = Vcalendar::END;
        }
        $b = array_rand( ['before' => 1, 'after' => 2] );
        if( 'before' == $b ) {
            $diPrefix                    = '-';
            $dateIntervalArray['before'] = true;
            $dateInterval['invert']      = 1;
            $getValue['before']          = true;
        }
        else {
            $diPrefix                    = null;
            $dateIntervalArray['before'] = false;
            $getValue['before']          = false;
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
            ( 7000 + $cnt ) . $s . $s1 . $b,
            $dateIntervalArray,
            $params,
            $getValue,
            ParameterFactory::createParams( $getValue[Util::$LCparams] ) .
            ':' . $diPrefix . DateIntervalFactory::dateInterval2String(
                DateIntervalFactory::conformDateInterval(
                    DateIntervalFactory::factory(
                        DateIntervalFactory::durationArray2string( $dateIntervalArray )
                    )
                )
            ),
        ];
    }

    /**
     * DateInterval678Provider DateIntervalString sub-provider
     *
     * @param array $input
     * @param int   $cnt
     * @return array
     * @throws Exception
     */
    public static function DateInterval678ProviderDateIntervalDateIntervalString( array $input, $cnt ) {
        $dateIntervalArray = $input;
        $value = DateIntervalFactory::dateInterval2arr(
            DateIntervalFactory::conformDateInterval(
                DateIntervalFactory::factory(
                    DateIntervalFactory::durationArray2string( $dateIntervalArray )
                )
            )
        );
        if( isset( $value[Util::$LCWEEK] ) && empty( $value[Util::$LCWEEK] )) {
            unset( $value[Util::$LCWEEK] );
        }
        $params = [];
        $s      = array_rand( [Vcalendar::START => 1, Vcalendar::END => 2] );
        $s1     = null;
        if( Vcalendar::START == $s ) {
            $value['relatedStart'] = true;
            $s1 = random_int( 1, 2 );
            if( 1 == $s1) {
                $params[Vcalendar::RELATED] = Vcalendar::START; // default
            }
        }
        else {
            $value['relatedStart'] = false;
            $params[Vcalendar::RELATED] = Vcalendar::END;
        }
        $b = array_rand( ['before' => 1, 'after' => 2] );
        if( 'before' == $b) {
            $diPrefix        = '-';
            $value['before'] = true;
        }
        else {
            $diPrefix        = null;
            $value['before'] = false;
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
            $diPrefix . DateIntervalFactory::durationArray2string( $dateIntervalArray ),
            $params,
            $getValue,
            ParameterFactory::createParams( $getValue[Util::$LCparams] ) .
            ':' . $diPrefix . DateIntervalFactory::dateInterval2String(
                DateIntervalFactory::conformDateInterval(
                    DateIntervalFactory::factory(
                        DateIntervalFactory::durationArray2string( $dateIntervalArray )
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
    public function DateInterval678Provider() {

        $dataArr = [];

        // DateInterval input
        $cnt = 0;
        while( 200 > $cnt ) {
            $dataArr[] = self::DateInterval678ProviderDateInterval(
                self::DateIntervalArrayGenerator(),
                $cnt
            );
            $cnt += 1;
        }

        // array input
        $cnt = 0;
        while( 200 > $cnt ) {
            $dataArr[] = self::DateInterval678ProviderDateIntervalArray(
                self::DateIntervalArrayGenerator( false ),
                $cnt
            );
            $cnt += 1;
        }

        // string input
        $cnt = 0;
        while( 200 > $cnt ) {
            $dataArr[] = self::DateInterval678ProviderDateIntervalDateIntervalString(
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
     */
    public function testDateInterval678(
        $case,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $compProp = [
            Vcalendar::VALARM  => [ Vcalendar::TRIGGER ],
        ];
        $c = new Vcalendar();
        foreach( $compProp as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp   = $c->newVevent()->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );
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
                    "get error in case #{$case}, <{$theComp}>->{$getMethod}"
                );

                $this->assertEquals(
                    strtoupper( $propName ) . $expectedString,
                    trim( $comp->{$createMethod}()),
                    "create error in case #{$case}, <{$theComp}>->{$createMethod}"
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}( true ),
                    "get (after delete) error in case #{$case}, <{$theComp}>->{$deleteMethod}"
                );
                $comp->{$setMethod}( $value, $params ); // test ###
            }
        }

        $this->parseCalendarTest( $case, $c, $expectedString );

    }

    /**
     * DateInterval101112Provider DateTime / DateInterval sub-provider
     *
     * @param array $input
     * @param int   $cnt
     * @return array
     * @throws Exception
     */
    public static function DateInterval101112ProviderDateInterval( $input, $cnt ) {
        $cnt += 10000;
        $dateInterval   = (array) DateIntervalFactory::factory(
            DateIntervalFactory::durationArray2string( $input )
        );
        $diInput        = DateIntervalFactory::DateIntervalArr2DateInterval( $dateInterval );
        $diArray        = DateIntervalFactory::dateInterval2arr(
            DateIntervalFactory::conformDateInterval(
                DateIntervalFactory::DateIntervalArr2DateInterval( $dateInterval )
            )
        );
        unset( $diArray[Util::$LCWEEK] );
        $diString       = DateIntervalFactory::dateInterval2String(
            DateIntervalFactory::conformDateInterval(
                DateIntervalFactory::DateIntervalArr2DateInterval( $dateInterval )
            )
        );
        $baseDateTime   = DateTimeFactory::factory( 'now', Vcalendar::UTC );
        $dateTimeArray  = DateTimeFactory::getDateArrayFromDateTime( $baseDateTime );
        $dateTimeString = DateTimeFactory::dateArrayToStr( $dateTimeArray );
        $outputString   = ';' . Vcalendar::FBTYPE . '=' . Vcalendar::BUSY . ':' .  $dateTimeString . '/' . $diString;
        switch( random_int( 1, 3 )) {
            case 1: // DateTime
                return [
                    $cnt . 'DateTime/DateInterval',
                    [   // input
                        $baseDateTime,
                        $diInput
                    ],
                    [   // getValue
                        Util::$LCvalue => [
                            Vcalendar::FBTYPE => Vcalendar::BUSY,
                            [
                                $dateTimeArray,
                                $diArray
                            ]
                        ],
                        Util::$LCparams => []
                    ],
                    $outputString
                ];
                break;
            case 2: // date array
                return [
                    $cnt . 'DateArray/DateInterval',
                    [   // input
                        $dateTimeArray,
                        $diInput
                    ],
                    [   // getValue
                        Util::$LCvalue => [
                            Vcalendar::FBTYPE => Vcalendar::BUSY,
                            [
                                $dateTimeArray,
                                $diArray
                            ]
                        ],
                        Util::$LCparams => []
                    ],
                    $outputString
                ];
                break;
            default : // string
                return [
                    $cnt . 'DateString/DateInterval',
                    [   // input
                        DateTimeFactory::dateTime2Str( $baseDateTime ),
                        $diInput
                    ],
                    [   // getValue
                        Util::$LCvalue => [
                            Vcalendar::FBTYPE => Vcalendar::BUSY,
                            [
                                $dateTimeArray,
                                $diArray
                            ]
                        ],
                        Util::$LCparams => []
                    ],
                    $outputString
                ];
                break;
        } // end switch
    }

    /**
     * DateInterval101112Provider DateTime / DateInterval array sub-provider
     *
     * @param array $input
     * @param int   $cnt
     * @return array
     * @throws Exception
     */
    public static function DateInterval101112ProviderDateIntervalArray( $input, $cnt ) {
        $cnt += 11000;
        $dateInterval   = (array) DateIntervalFactory::factory(
            DateIntervalFactory::durationArray2string( $input )
        );
        $diArray        = DateIntervalFactory::dateInterval2arr(
            DateIntervalFactory::conformDateInterval(
                DateIntervalFactory::DateIntervalArr2DateInterval( $dateInterval )
            )
        );
        unset( $diArray[Util::$LCWEEK] );
        $diString       = DateIntervalFactory::dateInterval2String(
            DateIntervalFactory::conformDateInterval(
                DateIntervalFactory::DateIntervalArr2DateInterval( $dateInterval )
            )
        );
        $baseDateTime   = DateTimeFactory::factory( 'now', Vcalendar::UTC );
        $dateTimeArray  = DateTimeFactory::getDateArrayFromDateTime( $baseDateTime );
        $dateTimeString = DateTimeFactory::dateArrayToStr( $dateTimeArray );
        $outputString   = ';' . Vcalendar::FBTYPE . '=' . Vcalendar::BUSY . ':' .  $dateTimeString . '/' . $diString;
        switch( random_int( 1, 3 )) {
            case 1: // DateTime
                return [
                    $cnt . 'DateTime/DateInterval',
                    [   // input
                        $baseDateTime,
                        $diArray
                    ],
                    [   // getValue
                        Util::$LCvalue => [
                            Vcalendar::FBTYPE => Vcalendar::BUSY,
                            [
                                $dateTimeArray,
                                $diArray
                            ]
                        ],
                        Util::$LCparams => []
                    ],
                    $outputString
                ];
                break;
            case 2: // date array
                return [
                    $cnt . 'DateArray/DateInterval',
                    [   // input
                        $dateTimeArray,
                        $diArray
                    ],
                    [   // getValue
                        Util::$LCvalue => [
                            Vcalendar::FBTYPE => Vcalendar::BUSY,
                            [
                                $dateTimeArray,
                                $diArray
                            ]
                        ],
                        Util::$LCparams => []
                    ],
                    $outputString
                ];
                break;
            default : // string
                return [
                    $cnt . 'DateString/DateInterval',
                    [   // input
                        $dateTimeString,
                        $diArray
                    ],
                    [   // getValue
                        Util::$LCvalue => [
                            Vcalendar::FBTYPE => Vcalendar::BUSY,
                            [
                                $dateTimeArray,
                                $diArray
                            ]
                        ],
                        Util::$LCparams => []
                    ],
                    $outputString
                ];
                break;
        } // end switch
    }

    /**
     * DateInterval101112Provider DateTime / DateInterval string sub-provider
     *
     * @param array $input
     * @param int   $cnt
     * @return array
     * @throws Exception
     */
    public static function DateInterval101112ProviderDateIntervalString( $input, $cnt ) {
        $cnt += 12000;
        $dateInterval   = (array) DateIntervalFactory::factory(
            DateIntervalFactory::durationArray2string( $input )
        );
        $diArray        = DateIntervalFactory::dateInterval2arr(
            DateIntervalFactory::conformDateInterval(
                DateIntervalFactory::DateIntervalArr2DateInterval( $dateInterval )
            )
        );
        unset( $diArray[Util::$LCWEEK] );
        $diString       = DateIntervalFactory::dateInterval2String(
            DateIntervalFactory::conformDateInterval(
                DateIntervalFactory::DateIntervalArr2DateInterval( $dateInterval )
            )
        );
        $baseDateTime   = DateTimeFactory::factory( 'now', Vcalendar::UTC );
        $dateTimeArray  = DateTimeFactory::getDateArrayFromDateTime( $baseDateTime );
        $dateTimeString = DateTimeFactory::dateArrayToStr( $dateTimeArray );
        $outputString   = ';' . Vcalendar::FBTYPE . '=' . Vcalendar::BUSY . ':' .  $dateTimeString . '/' . $diString;
        switch( random_int( 1, 3 )) {
            case 1: // DateTime
                return [
                    $cnt . 'DateTime/diString',
                    [   // input
                        $baseDateTime,
                        $diString
                    ],
                    [   // getValue
                        Util::$LCvalue => [
                            Vcalendar::FBTYPE => Vcalendar::BUSY,
                            [
                                $dateTimeArray,
                                $diArray
                            ]
                        ],
                        Util::$LCparams => []
                    ],
                    $outputString
                ];
                break;
            case 2: // date array
                return [
                    $cnt . 'DateArray/diString',
                    [   // input
                        $dateTimeArray,
                        $diString
                    ],
                    [   // getValue
                        Util::$LCvalue => [
                            Vcalendar::FBTYPE => Vcalendar::BUSY,
                            [
                                $dateTimeArray,
                                $diArray
                            ]
                        ],
                        Util::$LCparams => []
                    ],
                    $outputString
                ];
                break;
            default : // string
                return [
                    $cnt . 'DateString/diString',
                    [   // input
                        $dateTimeString,
                        $diString
                    ],
                    [   // getValue
                        Util::$LCvalue => [
                            Vcalendar::FBTYPE => Vcalendar::BUSY,
                            [
                                $dateTimeArray,
                                $diArray
                            ]
                        ],
                        Util::$LCparams => []
                    ],
                    $outputString
                ];
                break;
        } // end switch
    }

    /**
     * testDateInterval101112 provider
     *
     * @return array
     * @throws Exception
     */
    public function DateInterval101112Provider() {

        $dataArr = [];

        // (random) dateTime + DateInterval input
        $cnt = 0;
        while( 50 > $cnt ) {
           $dataArr[] = self::DateInterval101112ProviderDateInterval(
               self::DateIntervalArrayGenerator(),
               $cnt
           );
            $cnt += 1;
        }

        // (random) dateTime + array input
        $cnt = 0;
        while( 50 > $cnt ) {
            $dataArr[] = self::DateInterval101112ProviderDateIntervalArray(
                self::DateIntervalArrayGenerator( false ),
                $cnt
            );
            $cnt += 1;
        }

        // (random) dateTime + string input
        $cnt = 0;
        while( 50 > $cnt ) {
            $dataArr[] = self::DateInterval101112ProviderDateIntervalString(
                self::DateIntervalArrayGenerator(),
                $cnt
            );
            $cnt += 1;
        }

        return $dataArr;
    }

    /**
     * Testing (PERIOD DateTime-)DateInterval for FREEBUSY
     *
     * @test
     * @dataProvider DateInterval101112Provider
     * @param int    $case
     * @param mixed  $value
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateInterval101112a(
        $case,
        $value,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VFREEBUSY => [ Vcalendar::FREEBUSY ],
        ];
        $c = new Vcalendar();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $c->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );
                // error_log( __FUNCTION__ . ' #' . $case . ' <' . $theComp . '>->' . $propName . ' value : ' . var_export( $value, true )); // test ###
                $comp->{$setMethod}( Vcalendar::BUSY, $value );

                $getValue = $comp->{$getMethod}( null, true );
                // error_log( __FUNCTION__ . ' #' . $case . ' get ' . var_export( $getValue, true )); // test ###
                $this->assertEquals(
                    $expectedGet,
                    $getValue,
                    "Error in case #{$case}, " . __FUNCTION__ . " <{$theComp}>->{$getMethod}"
                );
                $this->assertEquals(
                    $propName . $expectedString,
                    trim( $comp->{$createMethod}()),
                    "Error in case #{$case}, " . __FUNCTION__. " <{$theComp}>->{$createMethod}"
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    "(after delete) Error in case #{$case}, " . __FUNCTION__ . " <{$theComp}>->{$getMethod}"
                );
                $comp->{$setMethod}( Vcalendar::BUSY, $value );
            }
        }

        $this->parseCalendarTest( $case, $c, $expectedString );

    }

    /**
     * Testing (PERIOD DateTime-)DateInterval for FREEBUSY
     *
     * @test
     * @dataProvider DateInterval101112Provider
     * @param int    $case
     * @param mixed  $value
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateInterval101112b(
        $case,
        $value,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VFREEBUSY => [ Vcalendar::FREEBUSY ],
        ];
        $c = new Vcalendar();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $c->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );
                // error_log( __FUNCTION__ . ' #' . $case . ' <' . $theComp . '>->' . $propName . ' value : ' . var_export( $value, true )); // test ###
                $comp->{$setMethod}( Vcalendar::BUSY, [ $value ] );

                $getValue = $comp->{$getMethod}( null, true );
                // error_log( __FUNCTION__ . ' #' . $case . ' get ' . var_export( $getValue, true )); // test ###
                $this->assertEquals(
                    $expectedGet,
                    $getValue,
                    "Error in case #{$case}, " . __FUNCTION__ . " <{$theComp}>->{$getMethod}"
                );
                $this->assertEquals(
                    $propName . $expectedString,
                    trim( $comp->{$createMethod}()),
                    "Error in case #{$case}, " . __FUNCTION__. " <{$theComp}>->{$createMethod}"
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    "(after delete) Error in case #{$case}, " . __FUNCTION__ . " <{$theComp}>->{$getMethod}"
                );
                $comp->{$setMethod}( Vcalendar::BUSY, $value );
            }
        }

        $this->parseCalendarTest( $case, $c, $expectedString );

    }

    /**
     * Testing (PERIOD DateTime-)DateInterval for FREEBUSY
     *
     * @test
     * @dataProvider DateInterval101112Provider
     * @param int    $case
     * @param mixed  $value
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function testDateInterval101112c(
        $case,
        $value,
        $expectedGet,
        $expectedString
    ) {
        static $compsProps = [
            Vcalendar::VFREEBUSY => [ Vcalendar::FREEBUSY ],
        ];
        $expectedStringOrg = $expectedString;
        $c = new Vcalendar();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $c->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );
                // error_log( __FUNCTION__ . ' #' . $case . ' <' . $theComp . '>->' . $propName . ' value : ' . var_export( $value, true )); // test ###
                $comp->{$setMethod}( Vcalendar::BUSY, [ $value, $value ] );

                $getValue = $comp->{$getMethod}( null, true );
                // error_log( __FUNCTION__ . ' #' . $case . ' get ' . var_export( $getValue, true )); // test ###
                $expectedGet[Util::$LCvalue][] = end( $expectedGet[Util::$LCvalue] );
                $this->assertEquals(
                    $expectedGet,
                    $getValue,
                    "Error in case #{$case}, " . __FUNCTION__ . " <{$theComp}>->{$getMethod}"
                );
                $expectedString .= ',' . StringFactory::after_last( ':', $expectedString );
                $this->assertEquals(
                    $propName . $expectedString,
                    str_replace( ["\r\n", ' '], null, $comp->{$createMethod}()),
                    "Error in case #{$case}, " . __FUNCTION__. " <{$theComp}>->{$createMethod}"
                );
                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    "(after delete) Error in case #{$case}, " . __FUNCTION__ . " <{$theComp}>->{$getMethod}"
                );
                $comp->{$setMethod}( Vcalendar::BUSY, $value );
            }
        }

        $this->parseCalendarTest( $case, $c, $expectedStringOrg );

    }

}