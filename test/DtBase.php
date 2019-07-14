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

use PHPUnit\Framework\TestCase;
use Kigkonsult\Icalcreator\Util\DateTimeZoneFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\IcalXMLFactory;
use DateTime;
use SimpleXMLElement;
use Exception;

/**
 * class DtBase
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  2.27.14 - 2019-01-24
 */
class DtBase extends TestCase
{
    private static $ERRFMT = "Error %sin case #%s, %s <%s>->%s";

    /**
     * The test method
     *
     * @param int    $case
     * @param array  $compsProps
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function theTestMethod(
        $case,
        array $compsProps,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        $c = new Vcalendar();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $c->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );

//              error_log( __FUNCTION__ . ' #' . $case . ' <' . $theComp . '>->' . $propName . ' value : ' . var_export( $value, true )); // test ###

                if( in_array( $propName, [ Vcalendar::EXDATE, Vcalendar::RDATE ] )) {
                    $comp->{$setMethod}( [ $value ], $params );
                    $getValue = $comp->{$getMethod}( null, true );
                    if( ! empty( $getValue[Util::$LCvalue] )) {
                        $getValue[Util::$LCvalue] = reset( $getValue[Util::$LCvalue] );
                    }
                }
                else {
                    if( in_array( $propName, [ Vcalendar::DTEND, Vcalendar::DUE, Vcalendar::RECURRENCE_ID, ])) {
                        $comp->setDtstart( $value, $params );
                    }
                    $comp->{$setMethod}( $value, $params );
                    $getValue = $comp->{$getMethod}( true );
                }

                $this->assertEquals(
                    $expectedGet,
                    $getValue,
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    strtoupper( $propName ) . $expectedString,
                    trim( $comp->{$createMethod}() ),
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                if( Vcalendar::DTSTAMP == $propName ) {
                    $this->assertNotFalse(
                        $comp->{$getMethod}(),
                        sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                    );
                }
                else {
                    $this->assertFalse(
                        $comp->{$getMethod}(),
                        sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                    );
                }
                $comp->{$setMethod}( $value, $params );
            }
        }

        $this->parseCalendarTest( $case, $c, $expectedString );

    }

    /**
     * Testing calendar parse and (-re-)create
     *
     * @param int       $case
     * @param Vcalendar $calendar
     * @param string    $expectedString
     */
    public function parseCalendarTest( $case, Vcalendar $calendar, $expectedString = null ) {
        static $xmlStartChars = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<icalendar xmlns=\"urn:ietf:params:xml:ns:icalendar-2.0\"><!-- kigkonsult iCalcreator";
        static $xmlEndChars   = "</icalendar>\n";
        static $strVcalendar  = 'Vcalendar';
        static $strCC         = 'createCalendar';
        static $strIcalXML    = 'IcalXMLFactory';
        static $striCal2XML   = 'iCal2XML';
        static $strXML2iCal   = 'XML2iCal';
        static $strPCC        = 'parse, create and compare';

        $calendarStr1 = $calendar->createCalendar();
        if( ! empty( $expectedString )) {
            $createString = str_replace( [ Util::$CRLF . ' ', Util::$CRLF ], null, $calendarStr1 );
            $createString = str_replace( '\,', ',', $createString );
            $this->assertNotFalse(
                strpos( $createString, $expectedString ),
                sprintf( self::$ERRFMT, null, $case . '-1', __FUNCTION__, $strVcalendar, $strCC )
            );
        }

        if( ! empty( $expectedString )) {
            $xml = IcalXMLFactory::iCal2XML( $calendar );
            $this->assertStringStartsWith( $xmlStartChars, $xml );
            $this->assertNotFalse( strpos( $xml, Vcalendar::iCalcreatorVersion()));
            $this->assertStringEndsWith( $xmlEndChars, $xml );
            try {
                $test = new SimpleXMLElement( $xml );
            }
            catch( Exception $e ) {
                $this->assertTrue(
                    false,
                    sprintf( self::$ERRFMT, null, $case . '-2', __FUNCTION__, $strIcalXML, $striCal2XML )
                );
            }

            $c2  = IcalXMLFactory::XML2iCal( $xml );
            $this->assertTrue(
                ( $c2 instanceof Vcalendar ),
                sprintf( self::$ERRFMT, null, $case . '-3', __FUNCTION__, $strIcalXML, $strXML2iCal )
            );

            $calendarStr2 = $c2->createCalendar();
            $this->assertEquals(
                $calendarStr1,
                $calendarStr2,
                sprintf( self::$ERRFMT, null, $case . '-4', __FUNCTION__, $strIcalXML, $strPCC )
            );
        }
        else {
            $calendarStr2 = $calendarStr1;
        }

        $calendar3    = new Vcalendar();
        $calendar3->parse( $calendarStr2 );
        $this->assertEquals(
            $calendarStr1,
            $calendar3->createCalendar(),
            sprintf( self::$ERRFMT, null, $case . '-5', __FUNCTION__, $strVcalendar, $strPCC )
        );
    }

/**
 * The test method 1b, single EXDATE + RDATE
 *
 * @param int    $case
 * @param array  $compsProps
 * @param mixed  $value
 * @param mixed  $params
 * @param array  $expectedGet
 * @param string $expectedString
 */
public function theTestMethod1b(
        $case,
        array $compsProps,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        $c = new Vcalendar();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $c->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );
//              error_log( __FUNCTION__ . ' #' . $case . ' <' . $theComp . '>->' . $propName . ' value : ' . var_export( $value, true )); // test ###

                $comp->{$setMethod}( $value, $params );
                $getValue = $comp->{$getMethod}( null, true );
                if( ! empty( $getValue[Util::$LCvalue] )) {
                    $getValue[Util::$LCvalue] = reset( $getValue[Util::$LCvalue] );
                }
                $this->assertEquals(
                    $expectedGet,
                    $getValue,
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    strtoupper( $propName ) . $expectedString,
                    trim( $comp->{$createMethod}() ),
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                if( Vcalendar::DTSTAMP == $propName ) {
                    $this->assertNotFalse(
                        $comp->{$getMethod}(),
                        sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                    );
                }
                else {
                    $this->assertFalse(
                        $comp->{$getMethod}(),
                        sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                    );
                }
                $comp->{$setMethod}( $value, $params );
                $comp->{$setMethod}( $value, $params );
                if( ! empty( $value )) {
                    $comp->{$setMethod}( [ $value, $value ], $params );
                }
            }
        }

        $this->parseCalendarTest( $case, $c, $expectedString );

    }

    /**
     * The test method 2, test using the 'all args' set-methods invoke
     *
     * @param int    $case
     * @param array  $compsProps
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     */
    public function theTestMethod2(
        $case,
        array $compsProps,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        static $keys = null;
        if( empty( $keys )) {
            $keys = [
                Util::$LCYEAR, Util::$LCMONTH, Util::$LCDAY,
                Util::$LCHOUR, Util::$LCMIN,   Util::$LCSEC,
                Util::$LCtz
            ];
        }
        $c = new Vcalendar();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp      = $c->{$newMethod}();
            foreach( $props as $propName ) {
                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );
                foreach( $keys as $key ) {
                    ${$key} = ( isset( $value[$key] )) ? $value[$key] : null;
                }

                $comp->{$setMethod}( ${Util::$LCYEAR}, ${Util::$LCMONTH}, ${Util::$LCDAY},
                                     ${Util::$LCHOUR}, ${Util::$LCMIN},   ${Util::$LCSEC},
                                     ${Util::$LCtz},
                                     $params );

                $getValue = $comp->{$getMethod}( true );
                $this->assertEquals(
                    $expectedGet,
                    $getValue,
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    strtoupper( $propName ) . $expectedString,
                    trim( $comp->{$createMethod}() ),
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                if( Vcalendar::DTSTAMP == $propName ) {
                    $this->assertNotFalse(
                        $comp->{$getMethod}(),
                        sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                    );
                }
                else {
                    $this->assertFalse(
                        $comp->{$getMethod}(),
                        sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                    );
                }
                $comp->{$setMethod}( ${Util::$LCYEAR}, ${Util::$LCMONTH}, ${Util::$LCDAY},
                                     ${Util::$LCHOUR}, ${Util::$LCMIN},   ${Util::$LCSEC},
                                     ${Util::$LCtz},
                                     $params );
            }
        }
    }

    /**
     * Return the datetime as assoc array
     *
     * @param DateTime $dateTime
     * @return array
     */
    public function getDateTimeAsArray( DateTime $dateTime ) {
        $output =  [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
            Util::$LCHOUR  => $dateTime->format( 'H' ),
            Util::$LCMIN   => $dateTime->format( 'i' ),
            Util::$LCSEC   => $dateTime->format( 's' ),
        ];
        if( DateTimeZoneFactory::isUTCtimeZone( $dateTime->getTimezone()->getName() )) {
            $output[Util::$LCtz] = 'Z';
        }
        return $output;
    }

    /**
     * Return the datetime as assoc array
     *
     * @param DateTime $dateTime
     * @return array
     */
    public function getDateTimeAsShortArray( DateTime $dateTime ) {
        return [
            Util::$LCYEAR  => $dateTime->format( 'Y' ),
            Util::$LCMONTH => $dateTime->format( 'm' ),
            Util::$LCDAY   => $dateTime->format( 'd' ),
        ];
    }

    /**
     * Return the datetime as (ical create-) long string
     *
     * @param DateTime $dateTime
     * @param string   $tz
     * @return string
     */
    public function getDateTimeAsCreateLongString( DateTime $dateTime, $tz = null ) {
        static $FMT1 = ';TZID=%s:';
        static $FMT2 = '%04d%02d%02dT%02d%02d%02d';
        $isUTCtimeZone = ( empty( $tz )) ? false : DateTimeZoneFactory::isUTCtimeZone( $tz );
        $output  = ( empty( $tz ) || $isUTCtimeZone ) ? ':' : sprintf( $FMT1, $tz );
        $output .= sprintf(
            $FMT2,
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' ),
            $dateTime->format( 'H' ),
            $dateTime->format( 'i' ),
            $dateTime->format( 's' )
        );
        if( $isUTCtimeZone ) {
            $output .= 'Z';
        }
        return $output;
    }

    /**
     * Return the datetime as (ical create-) short string
     *
     * @param DateTime $dateTime
     * @return string
     */
    public function getDateTimeAsCreateShortString( DateTime $dateTime ) {
        static $FMT2 = ';VALUE=DATE:%04d%02d%02d';
        return sprintf(
            $FMT2,
            $dateTime->format( 'Y' ),
            $dateTime->format( 'm' ),
            $dateTime->format( 'd' )
        );
    }
}
