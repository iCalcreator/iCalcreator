<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2023 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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

use DateTime;
use DateTimeInterface;
use Exception;
use IntlTimeZone;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\DateTimeZoneFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Xml\Formatter as XmlFormatter;
use Kigkonsult\Icalcreator\Xml\Parser    as XmlParser;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

/**
 * class DtBase
 *
 * @since 2.41.44 2022-04-27
 */
abstract class DtBase extends TestCase
{
    use GetPropMethodNamesTrait;

    protected static function getErrMsg(
        ? string $spec = null,
        int|string $case,
        string $testFcn,
        ? string $inst = null,
        ? string $method = null,
        mixed $inValue = null,
        mixed $inParams = null
    )
    {
        static $ERRFMT = "Error %s in case #%s, %s <%s>->%s";
        $output = sprintf( $ERRFMT, ( $spec ?? '' ), $case, $testFcn, $inst, $method );
        if( ! empty( $inValue )) {
            $output .= PHP_EOL . '  inValue : ' . str_replace( PHP_EOL, '', var_export( $inValue, true ));
        }
        if( ! empty( $inParams )) {
            $output .= PHP_EOL . ' inParams : ' . str_replace( PHP_EOL, '', var_export( $inParams, true ));
        }
        return $output;
    }

    /**
     * The test method, case suffix '-1xx', test prop create-, delete-, get- is- and set-methods
     *
     * @param int     $case
     * @param array   $compsProps
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @param string  $expectedString
     * @throws Exception
     * @since 2.41.47 2022-04-29
     */
    public function thePropTest(
        int    $case,
        array  $compsProps,
        mixed  $value,
        mixed  $params,
        Pc     $expectedGet,
        string $expectedString
    ) : void
    {

//      error_log( __METHOD__ . ' IN 1 ' . $case . ' ' . var_export( $value, true )); // test ###

        $c       = new Vcalendar();
        $pcInput = $firstLastmodifiedLoad = false;
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp = match ( true ) {
                IcalInterface::PARTICIPANT === $theComp => $c->newVevent()->{$newMethod}()
                    ->setDtstamp( $value, $params ),
                IcalInterface::AVAILABLE === $theComp   => $c->newVavailability()->{$newMethod}(),
                default                                 => $c->{$newMethod}(),
            };
            foreach( $props as $propName ) {
                [ $createMethod, $deleteMethod, $getMethod, $isMethod, $setMethod ] = self::getPropMethodnames( $propName );
                if( IcalInterface::LAST_MODIFIED === $propName ) {
                    if( ! $firstLastmodifiedLoad ) {
                        $this->assertFalse(
                            $c->{$isMethod}(),
                            self::getErrMsg( null, $case . '-110', __FUNCTION__, Vcalendar::VCALENDAR, $isMethod ) .
                            PHP_EOL . $c->createCalendar() // test ###
                        );
                    }
                    $c->setLastmodified( $value, $params );
                    $firstLastmodifiedLoad = true;
                    $this->assertTrue(
                        $c->{$isMethod}(),
                        self::getErrMsg( null, $case . '-111', __FUNCTION__, Vcalendar::VCALENDAR, $isMethod )
                    );
                } // end if last-mod...

// error_log( __METHOD__ . ' #' . $case . ' start <' . $theComp . '>->' . $propName . ' value IN : ' . var_export( $value, true )); // test ###

                if( $propName !== IcalInterface::DTSTAMP ) {
                    $this->assertFalse(
                        $comp->{$isMethod}(),
                        self::getErrMsg( null, $case . '-112', __FUNCTION__, $theComp, $isMethod )
                    );
                }
                if( in_array( $propName, [ IcalInterface::EXDATE, IcalInterface::RDATE ], true )) {
                    $comp->{$setMethod}( [ $value ], $params );
                    $getValue = $comp->{$getMethod}( null, true );
                    if( ! empty( $getValue->value )) {
                        $getValue->value = reset( $getValue->value );
                    }
                }
                else {
                    if( in_array( $propName, [ IcalInterface::DTEND, IcalInterface::DUE, IcalInterface::RECURRENCE_ID, ], true ) ) {
                        $comp->setDtstart( $value, $params );
                    }
                    $comp->{$setMethod}( $value, $params );
                    $getValue = $comp->{$getMethod}( true );

                } // end else

                if( null !== $value ) {
                    $this->assertTrue(
                        $comp->{$isMethod}(),
                        self::getErrMsg( null, $case . '-113', __FUNCTION__, $theComp, $isMethod )
                    );
                }

                if( $expectedGet->value instanceof DateTime && $getValue->value instanceof DateTime ) {
                    if( $expectedGet->hasParamKey( IcalInterface::TZID ) &&
                        $getValue->hasParamKey( IcalInterface::TZID )) {
                        // has same offset (TZID might differ)
                        $this->assertEquals(
                            DateTimeFactory::factory(
                                null,
                                $expectedGet->getParams( IcalInterface::TZID )
                            )->getOffset(),
                            DateTimeFactory::factory(
                                null,
                                $getValue->getParams( IcalInterface::TZID )
                            )->getOffset(),
                            self::getErrMsg( null, $case . '-114-1', __FUNCTION__, $theComp, $getMethod )
                        );
                    }
                    else {
                        $getValue->removeParam( IcalInterface::ISLOCALTIME );
                        $this->assertEquals(
                            var_export( $expectedGet->params, true ),
                            var_export( $getValue->params, true ),
                            self::getErrMsg( null, $case . '-114-2', __FUNCTION__, $theComp, $getMethod, $value, $params )
                        );
                    }
                    $fmt = $expectedGet->hasParamValue( IcalInterface::DATE )
                        ? DateTimeFactory::$Ymd
                        : DateTimeFactory::$YmdHis;
                    $this->assertEquals(
                        $expectedGet->value->format( $fmt ),
                        $getValue->value->format( $fmt ),
                        self::getErrMsg( null, $case . '-114-3', __FUNCTION__, $theComp, $getMethod, $value, $params )
                    );
                    $this->assertEquals( // in case of diff. timezones but with equal offset
                        $expectedGet->value->getOffset(),
                        $getValue->value->getOffset(),
                        self::getErrMsg( null, $case . '-114-4', __FUNCTION__, $theComp, $getMethod, $value, $params )
                    );
                } // end if ..DateTime..
                $this->assertEquals(
                    strtoupper( $propName ) . $expectedString,
                    trim( $comp->{$createMethod}() ),
                    self::getErrMsg( null, $case . '-116', __FUNCTION__, $theComp, $createMethod )
                );
                if( method_exists( $comp, $deleteMethod )) { // Dtstamp/Uid has NO deleteMethod
                    $comp->{$deleteMethod}();
                }
                if( IcalInterface::DTSTAMP === $propName ) {
                    $this->assertTrue(
                        $comp->{$isMethod}(),
                        self::getErrMsg( null, $case . '-117', __FUNCTION__, $theComp, $isMethod )
                    );
                    $this->assertNotFalse(
                        $comp->{$getMethod}(),
                        self::getErrMsg( '(after delete) ', $case . '-118', __FUNCTION__, $theComp, $getMethod )
                    );
                } // end if
                else {
                    $this->assertFalse(
                        $comp->{$isMethod}(),
                        self::getErrMsg( null, $case . '-119', __FUNCTION__, $theComp, $isMethod )
                    );
                    $this->assertFalse(
                        $comp->{$getMethod}(),
                        self::getErrMsg( '(after delete) ', $case . '-120', __FUNCTION__, $theComp, $getMethod )
                    );
                } // end else
                if( $pcInput ) {
                    $comp->{$setMethod}( Pc::factory( $value, $params ));
                }
                else {
                    $comp->{$setMethod}( $value, $params );
                }
                if( null !== $value ) {
                    $this->assertTrue(
                        $comp->{$isMethod}(),
                        self::getErrMsg( null, $case . '-121', __FUNCTION__, $theComp, $isMethod )
                    );
                }
                $pcInput = ! $pcInput;

                if( IcalInterface::Z === substr( $expectedString, -1 )) {
                    $this->parseCalendarTest( $case, $c, $expectedString, $theComp, $propName ); // test ###
                }

            } // end foreach  .. => propName
        } // end foreach  comp => props

        $this->parseCalendarTest( $case, $c, $expectedString, $theComp, $propName );
    }

    /**
     * The test method, case suffix '-2xx', test prop get--method without params
     *
     * @param int     $case
     * @param array   $compsProps
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @throws Exception
     * @since 2.41.44 2022-04-21
     */
    public function propGetNoParamsTest(
        int    $case,
        array  $compsProps,
        mixed  $value,
        mixed  $params,
        Pc     $expectedGet
    ) : void
    {
        $c = new Vcalendar();
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            $comp = match ( true ) {
                IcalInterface::PARTICIPANT === $theComp => $c->newVevent()->{$newMethod}()
                    ->setDtstamp( $value, $params ),
                IcalInterface::AVAILABLE === $theComp => $c->newVavailability()->{$newMethod}(),
                default => $c->{$newMethod}(),
            };
            foreach( $props as $propName ) {
                [ , , $getMethod, $isMethod, $setMethod ] = self::getPropMethodnames( $propName );
                if( IcalInterface::DTSTAMP !== $propName ) {
                    $this->assertFalse(
                        $comp->{$isMethod}(),
                        self::getErrMsg( null, $case . '-211', __FUNCTION__, $theComp, $isMethod )
                    );
                }
                if( in_array( $propName, [ IcalInterface::EXDATE, IcalInterface::RDATE ], true )) {
                    $comp->{$setMethod}( [ $value ], $params );
                    $getValue = $comp->{$getMethod}();
                    if( ! empty( $getValue )) {
                        $getValue = reset( $getValue );
                    }
                }
                else {
                    if( in_array( $propName, [ IcalInterface::DTEND, IcalInterface::DUE, IcalInterface::RECURRENCE_ID, ], true ) ) {
                        $comp->setDtstart( $value, $params );
                    }
                    $comp->{$setMethod}( $value, $params );
                    $getValue = $comp->{$getMethod}();
                } // end else
                $this->assertSame(
                    ! empty( $value ),
                    $comp->{$isMethod}(),
                    self::getErrMsg( null, $case . '-212', __FUNCTION__, $theComp, $isMethod )
                       . ', exp ' . empty( $value ) ? Vcalendar::FALSE : Vcalendar::TRUE
                );
                if( $expectedGet->value instanceof DateTime && $getValue instanceof DateTime ) {
                    $this->assertEquals(
                        $expectedGet->value->format( DateTimeFactory::$YmdHis ),
                        $getValue->format( DateTimeFactory::$YmdHis ),
                        self::getErrMsg( null, $case . '-213', __FUNCTION__, $theComp, $getMethod )
                    );
                } // end if
                else {
                    $this->assertEquals(
                        $expectedGet->value ?? '',
                        $getValue,
                        self::getErrMsg( null, $case . '-214', __FUNCTION__, $theComp, $getMethod )
                    );
                }

            } // end foreach props...
        } // end foreach $compsProps...
    }

    /**
     * The test method suffix -1b... , single EXDATE + RDATE, case prefix '-1bx' also multi EXDATE + RDATE
     *
     * @param int|string $case
     * @param array      $compsProps
     * @param mixed      $value
     * @param mixed      $params
     * @param pc         $expectedGet
     * @param string     $expectedString
     * @throws Exception
     */
    public function exdateRdateSpecTest(
        int | string $case,
        array        $compsProps,
        mixed        $value,
        mixed        $params,
        Pc           $expectedGet,
        string       $expectedString
    ) : void
    {
        $c       = new Vcalendar();
        $pcInput = false;
        foreach( $compsProps as $theComp => $props ) {
            $newMethod = 'new' . $theComp;
            if( IcalInterface::AVAILABLE === $theComp ) {
                $comp = $c->newVavailability()->{$newMethod}();
            }
            else {
                $comp = $c->{$newMethod}();
            }
            foreach( $props as $propName ) {
                [ $createMethod, $deleteMethod, $getMethod, $isMethod, $setMethod ] = self::getPropMethodnames( $propName );
                $this->assertFalse(
                    $comp->{$isMethod}(),
                    self::getErrMsg( null, $case . '-1b2', __FUNCTION__, $theComp, $isMethod )
                );

                if( $pcInput ) {
                    $comp->{$setMethod}( Pc::factory( $value, $params ));
                }
                else {
                    $comp->{$setMethod}( $value, $params );
                }
                $pcInput = ! $pcInput;
                $this->assertSame(
                    ! empty( $value ),
                    $comp->{$isMethod}(),
                    self::getErrMsg( null, $case . '-1b1', __FUNCTION__, $theComp, $isMethod )
                         . ', exp ' . empty( $value ) ? Vcalendar::FALSE : Vcalendar::TRUE
                );

                $getValue = $comp->{$getMethod}( null, true );
                $getValue->removeParam( IcalInterface::ISLOCALTIME );
                $this->assertEquals(
                    $expectedGet->params,
                    $getValue->params,
                    self::getErrMsg( null, $case . '-1b2', __FUNCTION__, $theComp, $isMethod )
                );
                if( ! empty( $expectedGet->value )) {
                    $expVal = $expectedGet->value;

                    switch( true ) {
                        case $expectedGet->hasParamValue(IcalInterface::DATE ) :
                            $fmt = DateTimeFactory::$Ymd;
                            break;
                        case $getValue->hasParamKey( IcalInterface::ISLOCALTIME ) :
                            $fmt = DateTimeFactory::$YmdHis;
                            break;
                        default :
                            $fmt = DateTimeFactory::$YMDHISe;
                            break;
                    }
                    while( is_array( $expVal ) && ! $expVal instanceof DateTime ) {
                        $expVal = reset( $expVal );
                    }
                    $expGet = $expVal->format( $fmt );
                    $getVal = reset( $getValue->value );
                    while( is_array( $getVal ) && ! $getVal instanceof DateTime ) { // exDate/Rdate
                        $getVal = reset( $getVal );
                    }
                    $getVal = $getVal->format( $fmt );
                } // end if
                else {
                    $expGet = $expectedGet;
                    $getVal = $getValue;
                }
                $this->assertEquals(
                    $expGet,
                    $getVal,
                    self::getErrMsg( null, $case . '-1b3', __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertEquals(
                    strtoupper( $propName ) . $expectedString,
                    trim( $comp->{$createMethod}() ),
                    self::getErrMsg( null, $case . '-1b4', __FUNCTION__, $theComp, $createMethod )
                );
                $comp->{$deleteMethod}();
                if( IcalInterface::DTSTAMP === $propName ) {
                    $this->assertNotFalse(
                        $comp->{$getMethod}(),
                        self::getErrMsg( '(after delete) ', $case . '-1b5', __FUNCTION__, $theComp, $getMethod )
                    );
                }
                else {
                    $this->assertFalse(
                        $comp->{$getMethod}(),
                        self::getErrMsg( '(after delete) ', $case . '-1b6', __FUNCTION__, $theComp, $getMethod )
                    );
                }
                $comp->{$setMethod}( $value, $params );
                if( ! empty( $value ) && ! is_array( $value )) {
                    $comp->{$setMethod}( [ $value, $value ], $params ); // set aray of (single) values
                }
            } // end foreach
        } // end foreach

        $this->parseCalendarTest( $case, $c, $expectedString );
    }

    /**
     * Testing calendar parse and (-re-)create, case prefix '-3x'
     *
     * @param int|string $case
     * @param Vcalendar   $calendar
     * @param string|null $expectedString
     * @param mixed|null $theComp
     * @param string|null $propName
     * @throws Exception
     */
    public function parseCalendarTest(
        int | string $case,
        Vcalendar    $calendar,
        string       $expectedString = null,
        mixed        $theComp = null,
        string       $propName = null
    ) : void
    {
        static $xmlStartChars = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<icalendar xmlns=\"urn:ietf:params:xml:ns:icalendar-2.0\"><!-- kigkonsult.se iCalcreator";
        static $xmlEndChars   = "</icalendar>\n";

        $calendarStr1 = $calendar->createCalendar();

        if( ! empty( $expectedString )) {
            $createString = str_replace( [ Util::$CRLF . ' ', Util::$CRLF ], Util::$SP0, $calendarStr1 );
            $createString = str_replace( '\,', ',', $createString );
            $this->assertNotFalse(
                strpos( $createString, $expectedString ),
                self::getErrMsg( null, $case . '-31', __FUNCTION__, $theComp ?? '?', $propName ?? '?' )
                    . PHP_EOL . $createString . PHP_EOL . $expectedString
            );
        }

        if( ! empty( $expectedString )) {

            $calendarUid = $calendar->getUid();
            // iCal convert to XML
            $xml         = XmlFormatter::iCal2XML( $calendar );

            $this->assertStringStartsWith( $xmlStartChars, $xml );
            $this->assertNotFalse( strpos( $xml, Vcalendar::iCalcreatorVersion()));
            $this->assertStringEndsWith( $xmlEndChars, $xml );
            try {
                $test = new SimpleXMLElement( $xml );
            }
            catch( Exception $e ) {
                $this->fail(
                    self::getErrMsg( null, $case . '-32', __FUNCTION__, $theComp, $propName )
                );
            }

            // XML convert to iCal
            $c2  = XmlParser::XML2iCal( $xml );
            $this->assertTrue(
                ( $c2 instanceof Vcalendar ),
                self::getErrMsg( null, $case . '-33', __FUNCTION__, $theComp, $propName )
            );

            $c2->setUid( $calendarUid ); // else UID compare error
            $calendarStr2 = $c2->createCalendar();

            $this->assertEquals(
                $calendarStr1,
                $calendarStr2,
                self::getErrMsg( null, $case . '-34', __FUNCTION__, $theComp, $propName )
                . PHP_EOL . str_replace( '><', '>' . PHP_EOL . '<', $xml ) . PHP_EOL
            );
        } // end if( ! empty( $expectedString ))
        else {
            $calendarStr2 = $calendarStr1;
        }

        $calendar3    = new Vcalendar();
        $calendar3->parse( $calendarStr2 );
        $calendarStr3 = $calendar3->createCalendar();


        $this->assertEquals(
            $calendarStr1,
            $calendarStr3,
            self::getErrMsg( null, $case . '-35', __FUNCTION__, $theComp, $propName )
        );
        ob_start();
        $calendar3->returnCalendar();
        $hdrs = PHP_SAPI === 'cli' ? xdebug_get_headers() : headers_list();
        $out3 = ob_get_clean();

        $calEnd = 'END:VCALENDAR' . Util::$CRLF;
        $this->assertTrue(
            str_ends_with( $calendarStr1, $calEnd ),
            self::getErrMsg( null, $case . '-36a', __FUNCTION__, $theComp, $propName )
        );
        $this->assertTrue(
            str_ends_with( $out3, $calEnd ),
            self::getErrMsg( null, $case . '-36b', __FUNCTION__, $theComp, $propName )
        );
        $this->assertEquals(
            strlen( $calendarStr1 ),
            strlen( $out3 ),
            self::getErrMsg( null, $case . '-36c', __FUNCTION__, $theComp, $propName )
        );
        $needle = 'Content-Length: ';
        $contentLength = 0;
        foreach( $hdrs as $hdr ) {
            if( str_starts_with( strtolower( $hdr ), strtolower( $needle ))) {
                $contentLength = trim( StringFactory::after( $needle, $hdr ));
                break;
            }
        }
        $exp = strlen( $out3 );
        $this->assertEquals(
            $exp,
            $contentLength,
            'Error in case #' . $case . ' -36d, ' . __FUNCTION__ . ' exp: ' . $exp . ', found: ' . var_export( $hdrs, true )
        );
        // testing end

        $this->assertEquals(
            $calendarStr1,
            $out3,
            self::getErrMsg( null, $case . '-36e', __FUNCTION__, $theComp, $propName )
        );
    }

    /**
     * Return the datetime as (ical create-) long string
     *
     * @param DateTimeInterface $dateTime
     * @param string|null $tz
     * @return string
     */
    public function getDateTimeAsCreateLongString( DateTimeInterface $dateTime, string $tz = null ) : string
    {
        static $FMT1   = ';TZID=%s:';
        $ymdHis        = $dateTime->format( DateTimeFactory::$YmdTHis );
        $isUTCtimeZone = ( ! ( empty( $tz ) ) && DateTimeZoneFactory::isUTCtimeZone( $tz, $ymdHis ));
        $output        = ( empty( $tz ) || $isUTCtimeZone ) ? Util::$COLON : sprintf( $FMT1, $tz );
        $output       .= $ymdHis;
        if( $isUTCtimeZone ) {
            $output   .= 'Z';
        }
        return $output;
    }

    /**
     * Return the datetime as (ical create-) short string
     *
     * @param DateTimeInterface $dateTime
     * @param bool $prefix
     * @return string
     */
    public function getDateTimeAsCreateShortString( DateTimeInterface $dateTime, bool $prefix = true ) : string
    {
        static $FMT1 = ';VALUE=DATE:%d';
        static $FMT2 = ':%d';
        $fmt = $prefix ? $FMT1 : $FMT2;
        return sprintf( $fmt, $dateTime->format( DateTimeFactory::$Ymd ));
    }

    /**
     * Return a random ms timezone and the corr PHP one
     *
     * @link https://docs.microsoft.com/en-us/windows-hardware/manufacture/desktop/default-time-zones?view=windows-11
     * @link testSuite/TZMS_iCal_test.php
     * @return string[]
     * @since  2.41.57 - 2022-10-12
     */
    public static function getRandomMsAndPhpTz() : array
    {
        static $MSTZ = [
            'Afghanistan Standard Time'       => '+04:30',
            'Arab Standard Time'              => '+03:00',
            'Arabian Standard Time'           => '+04:00',
            'Arabic Standard Time'            => '+03:00',
            'Argentina Standard Time'         => '-03:00',
            'Atlantic Standard Time'          => '-04:00',
            'AUS Eastern Standard Time'       => '+10:00',
            'Azerbaijan Standard Time'        => '+04:00',
            'Bangladesh Standard Time'        => '+06:00',
            'Belarus Standard Time'           => '+03:00',
            'Cape Verde Standard Time'        => '-01:00',
            'Caucasus Standard Time'          => '+04:00',
            'Central America Standard Time'   => '-06:00',
            'Central Asia Standard Time'      => '+06:00',
            'Central Europe Standard Time'    => '+01:00',
            'Central European Standard Time'  => '+01:00',
            'Central Pacific Standard Time'   => '+11:00',
            'Central Standard Time (Mexico)'  => '-06:00',
            'China Standard Time'             => '+08:00',
            'E. Africa Standard Time'         => '+03:00',
            'E. Europe Standard Time'         => '+02:00',
            'E. South America Standard Time'  => '-03:00',
            'Eastern Standard Time'           => '-05:00',
            'Egypt Standard Time'             => '+02:00',
            'Fiji Standard Time'              => '+12:00',
            'FLE Standard Time'               => '+02:00',
            'Georgian Standard Time'          => '+04:00',
            'GMT Standard Time'               => '',
            'Greenland Standard Time'         => '-03:00',
            'Greenwich Standard Time'         => '',
            'GTB Standard Time'               => '+02:00',
            'Hawaiian Standard Time'          => '-10:00',
            'India Standard Time'             => '+05:30',
            'Israel Standard Time'            => '+02:00',
            'Jordan Standard Time'            => '+02:00',
            'Korea Standard Time'             => '+09:00',
            'Mauritius Standard Time'         => '+04:00',
            'Middle East Standard Time'       => '+02:00',
            'Montevideo Standard Time'        => '-03:00',
            'Morocco Standard Time'           => '',
            'Myanmar Standard Time'           => '+06:30',
            'Namibia Standard Time'           => '+01:00',
            'Nepal Standard Time'             => '+05:45',
            'New Zealand Standard Time'       => '+12:00',
            'Pacific SA Standard Time'        => '-03:00',
            'Pacific Standard Time'           => '-08:00',
            'Pakistan Standard Time'          => '+05:00',
            'Paraguay Standard Time'          => '-04:00',
            'Romance Standard Time'           => '+01:00',
            'Russian Standard Time'           => '+03:00',
            'SA Eastern Standard Time'        => '-03:00',
            'SA Pacific Standard Time'        => '-05:00',
            'SA Western Standard Time'        => '-04:00',
            'Samoa Standard Time'             => '+13:00',
            'SE Asia Standard Time'           => '+07:00',
            'Singapore Standard Time'         => '+08:00',
            'South Africa Standard Time'      => '+02:00',
            'Sri Lanka Standard Time'         => '+05:30',
            'Syria Standard Time'             => '+02:00',
            'Taipei Standard Time'            => '+08:00',
            'Tokyo Standard Time'             => '+09:00',
            'Tonga Standard Time'             => '+13:00',
            'Turkey Standard Time'            => '+02:00',
            'Ulaanbaatar Standard Time'       => '+08:00',
            'UTC'                             => '',
            'UTC-02'                          => '-02:00',
            'UTC-11'                          => '-11:00',
            'UTC+12'                          => '+12:00',
            'Venezuela Standard Time'         => '-04:30',
            'W. Central Africa Standard Time' => '+01:00',
            'W. Europe Standard Time'         => '+01:00',
            'West Asia Standard Time'         => '+05:00',
            'West Pacific Standard Time'      => '+10:00'
        ];
        $msTz   = array_rand( $MSTZ, 1 );
        return [
            $msTz,
            IntlTimeZone::getIDForWindowsID( $msTz )
        ];
    }

    /**
     * PHp date constant formats etc
     *
     * @var array
     */
    protected static array $DATECONSTANTFORMTS = [
        DATE_ATOM,
        DATE_COOKIE,
        DATE_ISO8601,
        DATE_RFC822,
        DATE_RFC850,
        DATE_RFC1036,
        DATE_RFC1123,
//      DATE_RFC7231, date in fixed GMT
        DATE_RFC2822,
        DATE_RFC3339,
        DATE_RFC3339_EXTENDED,
        DATE_RSS,
        DATE_W3C,
        'j F Y G:i:s T', // not tested elsewhere, @todo minutes/seconds without leading zeros
//      'l jS \of F Y h:i:s A T' // somewhat odd...  @todo fix trailing am/pm?
    ];
}
