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
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use Kigkonsult\Icalcreator\Util\GeoFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * class MiscTest,
 *
 * testing VALUE TEXT etc
 *   ATTACH, ATTENDEE, CATEGORIES, CLASS, COMMENT, CONTACT, DESCRIPTION, LOCATION, ORGANIZER,
 *   RELATED-TO, REQUEST_STATUS, RESOURCES, STATUS, SUMMARY, TRANSP, URL, X-PROP
 *   COLOR, IMAGE, CONFERENCE, NAME
 * testing GeoLocation
 * testing empty properties
 * testing parse eol-htab
 *
 * @since  2.39 - 2021-06-19
 */
class MiscTest extends DtBase
{
    private static $ERRFMT   = "Error %sin case #%s, %s <%s>->%s";
    private static $STCPAR   = [ 'X-PARAM' => 'Y-vALuE' ];
    private static $EOLCHARS = [ "\r\n ", "\r\n\t", PHP_EOL . " ", PHP_EOL . "\t" ];

    /**
     * testMisc1 provider
     */
    public function Misc1Provider()
    {
        $dataArr = [];

        // TRANSP
        $value  = Vcalendar::OPAQUE;
        $params = self::$STCPAR;
        $dataArr[] = [
            1011,
            [
                Vcalendar::TRANSP => [ Vcalendar::VEVENT ]
            ],
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            strtoupper( Vcalendar::TRANSP ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        $value  = 'Meeting to provide technical review for \'Phoenix\' design.\nHappy Face Conference Room. Phoenix design team MUST attend this meeting.\nRSVP to team leader.';
        $params = [
            Vcalendar::ALTREP   => 'This is an alternative representation',
            Vcalendar::LANGUAGE => 'EN'
        ] + self::$STCPAR;
        $dataArr[] = [
            1021,
            [
                Vcalendar::DESCRIPTION => [ Vcalendar::VEVENT, Vcalendar::VTODO ]
            ],
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            strtoupper( Vcalendar::DESCRIPTION ) .
            ParameterFactory::createParams( $params, [ Vcalendar::ALTREP, Vcalendar::LANGUAGE ] ) .
            ':' . $value
        ];

        // LOCATION
        $value  = 'Conference Room - F123, Bldg. 002';
        $params = [
            Vcalendar::ALTREP   => 'This is an alternative representation',
            Vcalendar::LANGUAGE => 'EN'
        ] + self::$STCPAR;
        $dataArr[] = [
            1031,
            [
                Vcalendar::LOCATION => [ Vcalendar::VEVENT, Vcalendar::VTODO ]
            ],
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            strtoupper( Vcalendar::LOCATION ) .
            ParameterFactory::createParams( $params, [ Vcalendar::ALTREP, Vcalendar::LANGUAGE ] ) .
            ':' . $value
        ];

        // SUMMARY
        $value  = 'Department Party';
        $params = [
            Vcalendar::ALTREP   => 'This is an alternative representation',
            Vcalendar::LANGUAGE => 'EN'
        ] + self::$STCPAR;
        $dataArr[] = [
            1041,
            [
                Vcalendar::SUMMARY => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            strtoupper( Vcalendar::SUMMARY ) .
            ParameterFactory::createParams( $params, [ Vcalendar::ALTREP, Vcalendar::LANGUAGE ] ) .
            ':' . $value
        ];

        $value = '⚽ Major League Soccer on ESPN+';
        $params = [
                Vcalendar::ALTREP   => 'This is an alternative representation',
                Vcalendar::LANGUAGE => 'EN'
            ] + self::$STCPAR;
        $dataArr[] = [ // testing utf8 char
            1042,
            [
                Vcalendar::SUMMARY => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            strtoupper( Vcalendar::SUMMARY ) .
            ParameterFactory::createParams( $params, [ Vcalendar::ALTREP, Vcalendar::LANGUAGE ] ) .
            ':' . $value
        ];

        // SOURCE
        $value  = 'http://example.com/pub/calendars/jsmith/mytime.ics';
        $params = []  + self::$STCPAR;
        $dataArr[] = [
            1051,
            [
                Vcalendar::SOURCE => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL, Vcalendar::VFREEBUSY ]
            ],
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            strtoupper( Vcalendar::SOURCE ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // URL 1
        $value1  = '%3C01020175ae0fa363-b7ebfe82-02d0-420a-a8d9-331e43fa1867-000000@eu-west-1.amazonses.com%3E';
        $value2  = '01020175ae0fa363-b7ebfe82-02d0-420a-a8d9-331e43fa1867-000000@eu-west-1.amazonses.com';
        $params1 = [  Vcalendar::VALUE => 'URI' ]  + self::$STCPAR;
        $params2 = self::$STCPAR;
        $dataArr[] = [
            1061,
            [
                Vcalendar::URL => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL, Vcalendar::VFREEBUSY ]
            ],
            $value1,
            $params1,
            [
                Util::$LCvalue  => $value2,
                Util::$LCparams => $params2
            ],
            strtoupper( Vcalendar::URL ) .
            ParameterFactory::createParams( $params2 ) . ':' . $value2
        ];

        // URL 2
        $value1  = 'https://www.masked.de/account/subscription/delivery/8878/%3Fweek=2021-W03';
        $value2  = 'https://www.masked.de/account/subscription/delivery/8878/%3Fweek=2021-W03';
        $params1 = [  Vcalendar::VALUE => Vcalendar::URI ]  + self::$STCPAR;
        $params2 = self::$STCPAR;
        $dataArr[] = [
            1062,
            [
                Vcalendar::URL => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL, Vcalendar::VFREEBUSY ]
            ],
            $value1,
            $params1,
            [
                Util::$LCvalue  => $value2,
                Util::$LCparams => $params2
            ],
            strtoupper( Vcalendar::URL ) .
            ParameterFactory::createParams( $params2 ) . ':' . $value2
        ];


        // URL 4
        $value1  = 'message://https://www.masked.de/account/subscription/delivery/8878/%3Fweek=2021-W03';
        $value2  = 'message://https://www.masked.de/account/subscription/delivery/8878/%3Fweek=2021-W03';
        $params1 = self::$STCPAR + [  Vcalendar::VALUE => Vcalendar::URI ];
        $params2 = self::$STCPAR;
        $dataArr[] = [
            1064,
            [
                Vcalendar::URL => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL, Vcalendar::VFREEBUSY ]
            ],
            $value1,
            $params1,
            [
                Util::$LCvalue  => $value2,
                Util::$LCparams => $params2
            ],
            strtoupper( Vcalendar::URL ) .
            ParameterFactory::createParams( $params2 ) . ':' . $value2
        ];

        // URL 5
        $value1  = 'message://%3C1714214488.13907.1453128266311.JavaMail.tomcat%40web-pdfe-f02%3E?c=1453128266&k1=ticket&k2=1797815930&k3=2016-07-20';
        $value2  = 'message://1714214488.13907.1453128266311.JavaMail.tomcat@web-pdfe-f02?c=1453128266&k1=ticket&k2=1797815930&k3=2016-07-20';
        $params1 = self::$STCPAR + [  Vcalendar::VALUE => Vcalendar::URI ];
        $params2 = self::$STCPAR;
        $dataArr[] = [
            1065,
            [
                Vcalendar::URL => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL, Vcalendar::VFREEBUSY ]
            ],
            $value1,
            $params1,
            [
                Util::$LCvalue  => $value2,
                Util::$LCparams => $params2
            ],
            strtoupper( Vcalendar::URL ) .
            ParameterFactory::createParams( $params2 ) . ':' . $value2
        ];

        // URL 6
        $value1  = 'message://%3C1714214488.13907.1453128266311.JavaMail.tomcat%40web-pdfe-f02%3E?c=1453128266&k1=ticket&k2=1797815930&k3=2016-07-20';
        $value2  = 'message://1714214488.13907.1453128266311.JavaMail.tomcat@web-pdfe-f02?c=1453128266&k1=ticket&k2=1797815930&k3=2016-07-20';
        $params1 = self::$STCPAR + [  strtolower( Vcalendar::VALUE ) => strtolower( Vcalendar::URI ) ];
        $params2 = self::$STCPAR;
        $dataArr[] = [
            1066,
            [
                Vcalendar::URL => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL, Vcalendar::VFREEBUSY ]
            ],
            $value1,
            $params1,
            [
                Util::$LCvalue  => $value2,
                Util::$LCparams => $params2
            ],
            strtoupper( Vcalendar::URL ) .
            ParameterFactory::createParams( $params2 ) . ':' . $value2
        ];

        // ORGANIZER
        $value  = 'MAILTO:ildoit1071@example.com';
        $params = [
                Vcalendar::CN             => 'John Doe',
                Vcalendar::DIR            => 'ldap://example.com:6666/o=ABC%20Industries,c=US???(cn=Jim%20Dolittle)',
                Vcalendar::SENT_BY        => 'MAILTO:boss1071@example.com',
                Vcalendar::LANGUAGE       => 'EN'
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            1071,
            [
                Vcalendar::ORGANIZER => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params + [ Vcalendar::EMAIL => 'ildoit1071@example.com' ], // removed, same as value
            $getValue,
            strtoupper( Vcalendar::ORGANIZER ) .
            ParameterFactory::createParams(
                $params,
                [
                    Vcalendar::CN,
                    Vcalendar::DIR,
                    Vcalendar::SENT_BY,
                    Vcalendar::LANGUAGE
                ]
            ) .
            ':' . $value
        ];

        $value  = 'ildoit1072@example.com';
        $params = [
                strtolower( Vcalendar::CN )           => 'Jane Doe',
                strtolower( Vcalendar::SENT_BY )      => 'boss1072@example.com',
                strtolower( Vcalendar::EMAIL )        => 'MAILTO:another1072@example.com'
            ] + self::$STCPAR;
        $params2 = [
                Vcalendar::CN            => 'Jane Doe',
                Vcalendar::SENT_BY       => 'MAILTO:boss1072@example.com',
                Vcalendar::EMAIL         => 'another1072@example.com'
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => 'MAILTO:' . $value,
            Util::$LCparams => $params2
        ];
        $dataArr[] = [
            1072,
            [
                Vcalendar::ORGANIZER => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::ORGANIZER ) .
            ParameterFactory::createParams(
                $params2,
                [
                    Vcalendar::CN,
                    Vcalendar::DIR,
                    Vcalendar::SENT_BY,
                    Vcalendar::LANGUAGE
                ]
            ) .
            ':' . 'MAILTO:' . $value
        ];

        // CLASS
        $value  = Vcalendar::CONFIDENTIAL;
        $params = self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            1081,
            [
                Vcalendar::KLASS => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::KLASS ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // STATUS
        $value  = Vcalendar::TENTATIVE;
        $params = self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            1091,
            [
                Vcalendar::STATUS => [ Vcalendar::VEVENT ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::STATUS ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // STATUS
        $value  = Vcalendar::NEEDS_ACTION;
        $params = self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            1092,
            [
                Vcalendar::STATUS => [ Vcalendar::VTODO ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::STATUS ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // STATUS
        $value  = Vcalendar::F_NAL;
        $params = self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            1093,
            [
                Vcalendar::STATUS => [ Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::STATUS ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // GEO
        $value  = [ Vcalendar::LATITUDE => 10.10, Vcalendar::LONGITUDE => 10.10 ];
        $params = self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            1101,
            [
                Vcalendar::GEO => [ Vcalendar::VEVENT, Vcalendar::VTODO ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::GEO ) .
            ParameterFactory::createParams( $params ) .
            ':' .
            GeoFactory::geo2str2( $getValue[Util::$LCvalue][Vcalendar::LATITUDE], GeoFactory::$geoLatFmt ) .
            Util::$SEMIC .
            GeoFactory::geo2str2( $getValue[Util::$LCvalue][Vcalendar::LONGITUDE], GeoFactory::$geoLongFmt )

        ];

        // COLOR
        $value  = 'black';
        $params = self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            1103,
            [
                Vcalendar::COLOR => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::COLOR ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        return $dataArr;
    }

    /**
     * Testing value TEXT (single) properties
     *
     * @test
     * @dataProvider Misc1Provider
     * @param int    $case
     * @param array  $propComps
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     * @throws Exception
     */
    public function testMisc1(
        $case,
        $propComps,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        $c = new Vcalendar();
        $urlIsSet = false;
        foreach( $propComps as $propName => $theComps ) {
            if( Vcalendar::SOURCE == $propName ) {
                $c->setSource( $value, $params );
                $c->setUrl( $value, $params );
                continue;
            }
            foreach( $theComps as $theComp ) {
                if( Vcalendar::COLOR == $propName ) {
                    $c->setColor( $value, $params );
                }

                if( ! $urlIsSet && ( Vcalendar::URL == $propName )) {
                    $c->setUrl( $value, $params );
                    $urlIsSet = true;
                }

                $newMethod = 'new' . $theComp;
                $comp      = $c->{$newMethod}();

                $getMethod    = StringFactory::getGetMethodName( $propName );
                $createMethod = StringFactory::getCreateMethodName( $propName );
                $deleteMethod = StringFactory::getDeleteMethodName( $propName );
                $setMethod    = StringFactory::getSetMethodName( $propName );

                if( Vcalendar::GEO == $propName ) {
                    $comp->{$setMethod}( $value[Vcalendar::LATITUDE], $value[Vcalendar::LONGITUDE], $params );
                }
                else {
                    $comp->{$setMethod}( $value, $params );
                }

                $getValue = $comp->{$getMethod}( true );
                $this->assertEquals(
                    $expectedGet,
                    $getValue,
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $getMethod )
                );

                $createString   = str_replace( self::$EOLCHARS , null, $comp->{$createMethod}() );
                $createString   = str_replace( '\,', ',', $createString );
                $this->assertEquals(
                    $expectedString,
                    trim( $createString ),
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $createMethod )
                );

                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    sprintf( self::$ERRFMT, '(after delete) ', $case, __FUNCTION__, $theComp, $getMethod )
                );

                if( Vcalendar::GEO == $propName ) {
                    $comp->{$setMethod}( $value[Vcalendar::LATITUDE], $value[Vcalendar::LONGITUDE], $params );
                }
                else {
                    $comp->{$setMethod}( $value, $params );
                }
            } // end foreach
        } // end foreach

        $this->parseCalendarTest( $case, $c, $expectedString );
    }

    /**
     * testMisc2 provider
     */
    public function Misc2Provider() {

        $dataArr = [];

        // CATEGORIES
        $value  = 'ANNIVERSARY,APPOINTMENT,BUSINESS,EDUCATION,HOLIDAY,MEETING,MISCELLANEOUS,NON-WORKING HOURS,NOT IN OFFICE,PERSONAL,PHONE CALL,SICK DAY,SPECIAL OCCASION,TRAVEL,VACATION';
        $params = [
            Vcalendar::LANGUAGE => 'EN'
        ] + self::$STCPAR;
        $dataArr[] = [
            2011,
            [
                Vcalendar::CATEGORIES  => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            strtoupper( Vcalendar::CATEGORIES ) .
            ParameterFactory::createParams( $params, [ Vcalendar::LANGUAGE ] ) .
            ':' . $value
        ];

        // COMMENT
        $value  = 'This is a comment';
        $params = [
            Vcalendar::ALTREP   => 'This is an alternative representation',
            Vcalendar::LANGUAGE => 'EN'
        ] + self::$STCPAR;
        $dataArr[] = [
            2021,
            [
                Vcalendar::COMMENT  => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL, Vcalendar::VFREEBUSY ]
            ],
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            strtoupper( Vcalendar::COMMENT ) .
            ParameterFactory::createParams( $params, [ Vcalendar::ALTREP, Vcalendar::LANGUAGE ] ) .
            ':' . $value
        ];

        // CONTACT
        $value  = 'Jim Dolittle, ABC Industries, +1-919-555-1234';
        $params = [
            Vcalendar::ALTREP   => 'http://example.com/pdi/jdoe.vcf',
            Vcalendar::LANGUAGE => 'EN'
        ] + self::$STCPAR;
        $dataArr[] = [
            2031,
            [
                Vcalendar::CONTACT  => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL, Vcalendar::VFREEBUSY ]
            ],
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            strtoupper( Vcalendar::CONTACT ) .
            ParameterFactory::createParams( $params, [ Vcalendar::ALTREP, Vcalendar::LANGUAGE ] ) .
            ':' . $value
        ];

        // DESCRIPTION
        $value  = 'Meeting to provide technical review for \'Phoenix\' design.\nHappy Face Conference Room. Phoenix design team MUST attend this meeting.\nRSVP to team leader.';
        $params = [
            Vcalendar::ALTREP   => 'This is an alternative representation',
            Vcalendar::LANGUAGE => 'EN'
        ] + self::$STCPAR;
        $dataArr[] = [
            2041,
            [
                Vcalendar::DESCRIPTION => [ Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            strtoupper( Vcalendar::DESCRIPTION ) .
                ParameterFactory::createParams( $params, [ Vcalendar::ALTREP, Vcalendar::LANGUAGE ] ) .
                ':' . $value
        ];

        // RESOURCES
        $value  = 'EASEL,PROJECTOR,VCR';
        $params = [
                Vcalendar::ALTREP   => 'This is an alternative representation',
                Vcalendar::LANGUAGE => 'EN'
            ] + self::$STCPAR;
        $dataArr[] = [
            2051,
            [
                Vcalendar::RESOURCES => [ Vcalendar::VEVENT, Vcalendar::VTODO ]
            ],
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            strtoupper( Vcalendar::RESOURCES ) .
            ParameterFactory::createParams( $params, [ Vcalendar::ALTREP, Vcalendar::LANGUAGE ] ) .
            ':' . $value
        ];

        // ATTENDEE
        $value  = 'MAILTO:ildoit2061@example.com';
        $params = [
                Vcalendar::CUTYPE         => Vcalendar::GROUP,
                Vcalendar::MEMBER         => [
                    'MAILTO:DEV-GROUP1@example.com',
                    'MAILTO:DEV-GROUP2@example.com',
                    'MAILTO:DEV-GROUP3@example.com',
                ],
                Vcalendar::ROLE           => Vcalendar::OPT_PARTICIPANT,
                Vcalendar::PARTSTAT       => Vcalendar::TENTATIVE,
                Vcalendar::RSVP           => Vcalendar::TRUE,
                Vcalendar::DELEGATED_TO   => [
                    'MAILTO:bob@example.com',
                    'MAILTO:rob@example.com',
                ],
                Vcalendar::DELEGATED_FROM => [
                    'MAILTO:jane@example.com',
                    'MAILTO:mary@example.com',
                ],
                Vcalendar::SENT_BY        => 'boss@example.com',          // note missing MAILTO:
                Vcalendar::EMAIL          => 'MAILTO:hammer@example.com', // MAILTO: woíll be removed
                Vcalendar::CN             => 'John Doe',
                Vcalendar::DIR            => 'ldap://example.com:6666/o=ABC%20Industries,c=US???(cn=Jim%20Dolittle)',
                Vcalendar::LANGUAGE       => 'EN'
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $getValue2 = $getValue;
        $getValue2[Util::$LCparams][Vcalendar::SENT_BY] = 'MAILTO:boss@example.com';
        $getValue2[Util::$LCparams][Vcalendar::EMAIL]   = 'hammer@example.com';
        $expectedString = trim( CalAddressFactory::outputFormatAttendee( [ $getValue2 ], true ));
        $expectedString = str_replace( Util::$CRLF . ' ' , null, $expectedString);
        $expectedString = str_replace( '\,', ',', $expectedString );
        $dataArr[] = [
            2061,
            [
                Vcalendar::ATTENDEE => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue2,
            $expectedString
        ];

        $value     = 'MAILTO:ildoit2062@example.com';
        $params    =  [
                Vcalendar::MEMBER         => '"DEV-GROUP2062@example.com"',
                Vcalendar::DELEGATED_TO   => '"bob2062@example.com"',
                Vcalendar::DELEGATED_FROM => '"jane2062@example.com"',
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $getValue2 = $getValue;
        $getValue2[Util::$LCparams][Vcalendar::MEMBER]         = [ 'MAILTO:DEV-GROUP2062@example.com' ];
        $getValue2[Util::$LCparams][Vcalendar::DELEGATED_TO]   = [ 'MAILTO:bob2062@example.com' ];
        $getValue2[Util::$LCparams][Vcalendar::DELEGATED_FROM] = [ 'MAILTO:jane2062@example.com' ];
        $expectedString = trim( CalAddressFactory::outputFormatAttendee( [ $getValue2 ], true ));
        $expectedString = str_replace( self::$EOLCHARS , null, $expectedString);
        $expectedString = str_replace( '\,', ',', $expectedString );
        $dataArr[] = [
            2062,
            [
                Vcalendar::ATTENDEE => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue2,
            $expectedString
        ];

        $value     = 'MAILTO:ildoit2063@example.com';
        $params    =  self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $expectedString = trim( CalAddressFactory::outputFormatAttendee( [ $getValue ], true ));
        $expectedString = str_replace( self::$EOLCHARS , null, $expectedString);
        $expectedString = str_replace( '\,', ',', $expectedString );
        $dataArr[] = [
            2063,
            [
                Vcalendar::ATTENDEE => [ Vcalendar::VFREEBUSY ] // , Vcalendar::VFREEBUSY
            ],
            $value,
            $params + [ Vcalendar::EMAIL => 'ildoit2063-2@example.com' ], // will be skipped
            $getValue,
            $expectedString
        ];

        // RELATED-TO
        $value  = StringFactory::getRandChars( 32 );
        $params = self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2071,
            [
                Vcalendar::RELATED_TO => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::RELATED_TO ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // ATTACH
        $value  = 'CID:jsmith.part3.960817T083000.xyzMail@example.com';
        $params = self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2081,
            [
                Vcalendar::ATTACH => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::ATTACH ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // ATTACH
        $value  = 'ftp://example.com/pub/reports/r-960812.ps';
        $params = [ Vcalendar::FMTTYPE => 'application/postscript' ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2082,
            [
                Vcalendar::ATTACH => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::ATTACH ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // ATTACH
        $value  = 'AAABAAEAEBAQAAEABAAoAQAAFgAAACgAAAAQAAAAIAAAAAEABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAAAAAgIAAAICAgADAwMAA////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMwAAAAAAABNEMQAAAAAAAkQgAAAAAAJEREQgAAACECQ0QgEgAAQxQzM0E0AABERCRCREQAADRDJEJEQwAAAhA0QwEQAAAAAEREAAAAAAAAREQAAAAAAAAkQgAAAAAAAAMgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $params = [
            Vcalendar::FMTTYPE  => 'image/vnd.microsoft.icon',
            Vcalendar::ENCODING => Vcalendar::BASE64,
            Vcalendar::VALUE    => Vcalendar::BINARY,
        ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2083,
            [
                Vcalendar::ATTACH => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::ATTACH ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];


        // IMAGE
        $value  = 'CID:jsmith.part3.960817T083000.xyzMail@example.com';
        $params = [ Vcalendar::VALUE => Vcalendar::URI ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2091,
            [
                Vcalendar::IMAGE => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::IMAGE ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // IMAGE
        $value  = 'ftp://example.com/pub/reports/r-960812.png';
        $params = [
            Vcalendar::VALUE   => Vcalendar::URI,
            Vcalendar::FMTTYPE => 'application/png',
            Vcalendar::DISPLAY => Vcalendar::BADGE . ',' . Vcalendar::THUMBNAIL
        ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2092,
            [
                Vcalendar::IMAGE => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::IMAGE ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // IMAGE
        $value  = 'AAABAAEAEBAQAAEABAAoAQAAFgAAACgAAAAQAAAAIAAAAAEABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAAAAAgIAAAICAgADAwMAA////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMwAAAAAAABNEMQAAAAAAAkQgAAAAAAJEREQgAAACECQ0QgEgAAQxQzM0E0AABERCRCREQAADRDJEJEQwAAAhA0QwEQAAAAAEREAAAAAAAAREQAAAAAAAAkQgAAAAAAAAMgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $params = [
                Vcalendar::VALUE    => Vcalendar::BINARY,
                Vcalendar::FMTTYPE  => 'image/vnd.microsoft.icon',
                Vcalendar::ENCODING => Vcalendar::BASE64,
                Vcalendar::DISPLAY  => Vcalendar::BADGE . ',' . Vcalendar::THUMBNAIL
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2093,
            [
                Vcalendar::IMAGE => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::IMAGE ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];


        // REQUEST_STATUS
        $value  = [
            Vcalendar::STATCODE => '3.70',
            Vcalendar::STATDESC => 'Invalid calendar user',
            Vcalendar::EXTDATA  => 'ATTENDEE:mailto:jsmith@example.com'
        ];
        $params = [ Vcalendar::LANGUAGE => 'EN' ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2111,
            [
                Vcalendar::REQUEST_STATUS => [
                    Vcalendar::VEVENT,
                    Vcalendar::VTODO,
                    Vcalendar::VJOURNAL,
                    Vcalendar::VFREEBUSY
                ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::REQUEST_STATUS ) .
            ParameterFactory::createParams( $params, [ Vcalendar::LANGUAGE ] ) .
            ':' .
            number_format(
                (float) $value[Vcalendar::STATCODE],
                2,
                Util::$DOT,
                null
            ) . ';' .
            StringFactory::strrep( $value[Vcalendar::STATDESC] ) . ';' .
            StringFactory::strrep( $value[Vcalendar::EXTDATA] )
        ];


        // CONFERENCE
        $value  = 'rtsp://audio.example.com/';
        $params = [
                Vcalendar::VALUE   => Vcalendar::URI,
                Vcalendar::FEATURE => Vcalendar::AUDIO
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2121,
            [
                Vcalendar::CONFERENCE => [
                    Vcalendar::VEVENT,
                    Vcalendar::VTODO,
                ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::CONFERENCE ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // CONFERENCE
        $value  = 'https://video-chat.example.com/;group-id=1234';
        $params = [
                Vcalendar::VALUE    => Vcalendar::URI,
                Vcalendar::FEATURE  => Vcalendar::AUDIO . ',' . Vcalendar::VIDEO,
                Vcalendar::LANGUAGE => 'EN',
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2122,
            [
                Vcalendar::CONFERENCE => [
                    Vcalendar::VEVENT,
                    Vcalendar::VTODO,
                ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::CONFERENCE ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // CONFERENCE
        $value  = 'https://video-chat.example.com/;group-id=1234';
        $params = [
                Vcalendar::VALUE   => Vcalendar::URI,
                Vcalendar::FEATURE => Vcalendar::VIDEO,
                Vcalendar::LABEL   => "Web video chat, access code=76543"
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2123,
            [
                Vcalendar::CONFERENCE => [
                    Vcalendar::VEVENT,
                    Vcalendar::VTODO,
                ]
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::CONFERENCE ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // NAME
        $value  = 'A calendar name';
        $params = [
                Vcalendar::ALTREP   => 'This is an alternative representation',
                Vcalendar::LANGUAGE => 'EN'
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2401,
            [
                Vcalendar::NAME => []
            ],
            $value,
            $params,
            $getValue,
            strtoupper( Vcalendar::NAME ) .
            ParameterFactory::createParams( $params, [ Vcalendar::ALTREP, Vcalendar::LANGUAGE ] ) .
            ':' . $value
        ];

        return $dataArr;
    }

    /**
     * Testing value TEXT (MULTI) properties
     *
     * @test
     * @dataProvider Misc2Provider
     *
     * @param int    $case
     * @param array  $propComps
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     * @throws Exception
     */
    public function testMisc2(
        $case,
        $propComps,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        $c = new Vcalendar();

        foreach( array_keys( $propComps ) as $propName ) {
            if( in_array( $propName, [
                Vcalendar::CATEGORIES,
                Vcalendar::DESCRIPTION,
                Vcalendar::IMAGE,
                Vcalendar::NAME
            ] )) {
                $this->propNameTest(
                    $case . '-1',
                    $c,
                    $propName,
                    $value,
                    $params,
                    $expectedGet,
                    $expectedString
                );
            }
        } // end foreach
        if( Vcalendar::NAME == $propName ) {
            return;
        }

        foreach( $propComps as $propName => $theComps ) {
            foreach( $theComps as $theComp ) {
                $newMethod = 'new' . $theComp;
                $comp      = $c->{$newMethod}();
                $this->propNameTest(
                    $case . '-2',
                    $comp,
                    $propName,
                    $value,
                    $params,
                    $expectedGet,
                    $expectedString
                );
            } // end foreach
        } // end foreach
        $calendar1    = $c->createCalendar();
        $createString = str_replace( [ Util::$CRLF . ' ', Util::$CRLF ], null, $calendar1 );
        $createString = str_replace( '\,', ',', $createString );
        $this->assertNotFalse(
            strpos( $createString, $expectedString ),
            sprintf( self::$ERRFMT, null, $case . '-25', __FUNCTION__, 'Vcalendar', 'createComponent' )
        );

        $c2 = new Vcalendar();
        $c2->parse( $calendar1 );
        $this->assertEquals(
            $calendar1,
            $c2->createCalendar(),
            sprintf( self::$ERRFMT, null, $case . '-26', __FUNCTION__, 'Vcalendar', 'parse, create and compare' )
        );

        if( Vcalendar::DESCRIPTION == $propName ) {
            $c->setName( $value, $params );
            $c->setName( $value, $params );
        }
        $this->parseCalendarTest( $case, $c, $expectedString );
    }

    /**
     * Testing calendar/component instance with multi-propName
     *
     * @param string   $case
     * @param Vcalendar|CalendarComponent $instance
     * @param string   $propName
     * @param mixed    $value
     * @param mixed    $params
     * @param array    $expectedGet
     * @param string   $expectedString
     */
    public function propNameTest(
        $case,
        $instance,
        $propName,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        $getMethod    = StringFactory::getGetMethodName( $propName );
        if( ! method_exists( $instance, $getMethod )) {
            return;
        }
        $createMethod = StringFactory::getCreateMethodName( $propName );
        $deleteMethod = StringFactory::getDeleteMethodName( $propName );
        $setMethod    = StringFactory::getSetMethodName( $propName );

        if( Vcalendar::REQUEST_STATUS == $propName ) {
            $instance->{$setMethod}(
                $value[Vcalendar::STATCODE],
                $value[Vcalendar::STATDESC],
                $value[Vcalendar::EXTDATA],
                $params
            );
        }
        else {
            $instance->{$setMethod}( $value, $params );
        }

        $getValue = $instance->{$getMethod}( null, true );
        $this->assertEquals(
            $expectedGet,
            $getValue,
            sprintf( self::$ERRFMT, null, $case . '-1', __FUNCTION__, $instance->getCompType(), $getMethod )
        );

        $createString = str_replace( Util::$CRLF . ' ' , null, $instance->{$createMethod}());
        $createString = str_replace( '\,', ',', $createString );
        $this->assertEquals(
            $expectedString,
            trim( $createString ),
            sprintf( self::$ERRFMT, null, $case . '-2', __FUNCTION__, $instance->getCompType(), $createMethod )
        );

        $instance->{$deleteMethod}();
        $this->assertFalse(
            $instance->{$getMethod}(),
            sprintf( self::$ERRFMT, '(after delete) ', $case . '-3a', __FUNCTION__, $instance->getCompType(), $getMethod )
        );
        $instance->{$deleteMethod}();
        $this->assertFalse(
            $instance->{$getMethod}(),
            sprintf( self::$ERRFMT, '(after delete) ', $case . '-3b', __FUNCTION__, $instance->getCompType(), $getMethod )
        );

        if( Vcalendar::REQUEST_STATUS == $propName ) {
            $instance->{$setMethod}(
                $value[Vcalendar::STATCODE],
                $value[Vcalendar::STATDESC],
                $value[Vcalendar::EXTDATA],
                $params
            );
            $instance->{$setMethod}(
                $value[Vcalendar::STATCODE],
                $value[Vcalendar::STATDESC],
                $value[Vcalendar::EXTDATA],
                $params
            );
        }
        else {
            $instance->{$setMethod}( $value, $params );
            $instance->{$setMethod}( $value, $params );
        }

        $instance->{$deleteMethod}();
        $instance->{$deleteMethod}();
        $this->assertFalse(
            $instance->{$getMethod}(),
            sprintf( self::$ERRFMT, '(after delete) ', $case . '-4', __FUNCTION__, $instance->getCompType(), $getMethod )
        );

        if( Vcalendar::REQUEST_STATUS == $propName ) {
            $instance->{$setMethod}(
                $value[Vcalendar::STATCODE],
                $value[Vcalendar::STATDESC],
                $value[Vcalendar::EXTDATA],
                $params
            );
            $instance->{$setMethod}(
                $value[Vcalendar::STATCODE],
                $value[Vcalendar::STATDESC],
                $value[Vcalendar::EXTDATA],
                $params
            );
        }
        else {
            $instance->{$setMethod}( $value, $params );
            $instance->{$setMethod}( $value, $params );
        }
    }

    /**
     * Testing component X-property
     */
    public function Misc3Provider() {

        $dataArr = [];

        $propName  = 'X-ABC-MMSUBJ';
        $value     = 'This is an X-property value';
        $params    = [] + self::$STCPAR;
        $dataArr[] = [
            1,
            $propName,
            [
                $propName => [
                    Vcalendar::VEVENT,
                    Vcalendar::VTODO,
                    Vcalendar::VJOURNAL,
                    Vcalendar::VFREEBUSY,
//                    Vcalendar::VTIMEZONE
                ]
            ],
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) . ':' . $value
        ];

        $propName  = 'X-ALARM-CNT';
        $value     = '1000 : -PT1000M';
        $params    = [] + self::$STCPAR;
        $dataArr[] = [
            2,
            $propName,
            [
                $propName => [
                    Vcalendar::VEVENT,
                    Vcalendar::VTODO,
                    Vcalendar::VJOURNAL,
                    Vcalendar::VFREEBUSY,
//                    Vcalendar::VTIMEZONE // as for now, can't sort Vtimezone...
                ]
            ],
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            ParameterFactory::createParams( $params ) . ':' . $value
        ];

        return $dataArr;
    }
    /**
     * Testing Vcalendar and component X-property
     *
     * @test
     * @dataProvider Misc3Provider
     * @param int    $case
     * @param string $propName
     * @param array  $propComps
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
     * @throws Exception
     */
    public function testMisc3(
        $case,
        $propName,
        $propComps,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        // set two Vcalendar X-properties
        $c = new Vcalendar();
        for( $x = 1; $x < 2; $x++ ) {

            $this->misc3factory(
                $c,
                'Vcalendar',
                $case . 31,
                $propName . $x,
                $value,
                $params,
                $expectedGet,
                $expectedString
            );
        }

        // set single component X-property
        foreach( $propComps as $propName => $theComps ) {
            foreach( $theComps as $theComp ) {
                $newMethod = 'new' . $theComp;
                $comp      = $c->{$newMethod}();

                if( in_array($theComp, [ Vcalendar::VEVENT, Vcalendar::VTODO ] )) {
                    $a     = $comp->newValarm();
                    $this->misc3factory(
                        $a,
                        'Valarm',
                        $case . 32,
                        $propName,
                        $value,
                        $params,
                        $expectedGet,
                        $expectedString
                    );
                }

                $this->misc3factory(
                    $comp,
                    $theComp,
                    $case . 33,
                    $propName,
                    $value,
                    $params,
                    $expectedGet,
                    $expectedString
                );
            }
        }

        // set two component X-properties and two in Vevent/Vtodo Valarms
        foreach( $propComps as $propName => $theComps ) {
            foreach( $theComps as $theComp ) {
                $newMethod = 'new' . $theComp;
                $comp      = $c->{$newMethod}();
                if( in_array( $theComp, [ Vcalendar::VEVENT, Vcalendar::VTODO ] )) {
                    $a     = $comp->newValarm();
                }
                for( $x = 1; $x < 2; $x++ ) {
                    if( in_array($theComp, [ Vcalendar::VEVENT, Vcalendar::VTODO ] )) {
                        $this->misc3factory(
                            $a,
                            'Valarm',
                            $case . 34,
                            $propName . $x,
                            $value,
                            $params,
                            $expectedGet,
                            $expectedString
                        );
                    }

                    $this->misc3factory(
                        $comp,
                        $theComp,
                        $case . 35,
                        $propName . $x,
                        $value,
                        $params,
                        $expectedGet,
                        $expectedString
                    );
                }
            }
        }
        $c->sort();

        $this->parseCalendarTest( $case, $c, $expectedString );

    }

    /**
     * Testing component X-property factory
     *
     * @param IcalBase $comp,
     * @param string   $compName,
     * @param int      $Number,
     * @param string   $propName,
     * @param string   $value,
     * @param array    $params,
     * @param array    $expectedGet,
     * @param string   $expectedString
     */
    public function misc3factory(
        $comp,
        $compName,
        $Number,
        $propName,
        $value,
        $params,
        $expectedGet,
        $expectedString
    ) {
        $comp->setXprop( $propName, $value, $params );

        $getValue = $comp->getXprop( $propName, null, true );
        $this->assertEquals(
            [ $propName, $expectedGet ],
            $getValue,
            sprintf( self::$ERRFMT, null, $Number . 1, __FUNCTION__, $compName, 'getXprop' )
        );

        $createString   = str_replace( Util::$CRLF . ' ', null, $comp->createXprop());
        $createString   = str_replace( '\,', ',', $createString );
        $this->assertNotFalse(
            strpos( $createString, $expectedString ),
            sprintf( self::$ERRFMT, null, $Number . 2, __FUNCTION__, 'Vcalendar', 'createXprop' )
        );

        $comp->deleteXprop( $propName );
        $this->assertFalse(
            $comp->getXprop( $propName ),
            sprintf(
                self::$ERRFMT, '(after delete) ', $Number . '3 ' . $propName, __FUNCTION__, 'Vcalendar', 'getXprop'
            )
        );

        $comp->setXprop( $propName, $value, $params );
    }

    /**
     * Test Vevent/Vtodo GEO
     *
     * @test
     */
    public function geoLocationTest4() {
        $compProps = [
            Vcalendar::VEVENT,
            Vcalendar::VTODO,
        ];
        $calendar  = new Vcalendar();
        $location  = 'Conference Room - F123, Bldg. 002';
        $latitude  = 12.34;
        $longitude = 56.5678;

        foreach( $compProps as $compNames => $theComp  ) {
            $newMethod1 = 'new' . $theComp;
            $comp = $calendar->{$newMethod1}();

            $getValue = $comp->getGeoLocation();
            $this->assertEmpty(
                $getValue,
                sprintf( self::$ERRFMT, null, 1, __FUNCTION__, $theComp, 'getGeoLocation' )
            );

            $comp->setLocation( $location )
                 ->setGeo(
                     $latitude,
                     $longitude
                 );
            $getValue = explode( '/', $comp->getGeoLocation());
            $this->assertEquals(
                $location,
                $getValue[0],
                sprintf( self::$ERRFMT, null, 2, __FUNCTION__, $theComp, 'getGeoLocation' )
            );
            $tLat = substr( StringFactory::beforeLast('+', $getValue[1] ), 1 );
            $this->assertEquals(
                $latitude,
                $tLat,
                sprintf( self::$ERRFMT, null, 3, __FUNCTION__, $theComp, 'getGeoLocation' )
            );
            $tLong = substr( str_replace( $tLat, null, $getValue[1] ), 1 );
            $this->assertEquals(
                $longitude,
                $tLong,
                sprintf( self::$ERRFMT, null, 4, __FUNCTION__, $theComp, 'getGeoLocation' )
            );
        }
    }
    /**
     * Testing empty properties
     *
     * @test
     */
    public function emptyTest5() {
        $c = Vcalendar::factory()
                      ->setCalscale( 'gregorian' )
                      ->setMethod( 'testing' )
                      ->setXprop( 'X-vcalendar-empty' )

                      ->setUid()
                      ->setLastmodified()
                      ->setUrl()
                      ->setRefreshinterval()
                      ->setSource()
                      ->setColor()

                      ->setName()
                      ->setDescription()
                      ->setCategories()
                      ->setImage()
        ;

        $o = $c->newVevent()
               ->setClass()
               ->setComment()
               ->setCreated()
               ->setDtstart()
               ->setDuration()
               ->setGeo()
               ->setExrule()
               ->setRrule()
               ->setExdate()
               ->setOrganizer()
               ->setRdate()
               ->setPriority()
               ->setResources()
               ->setSummary()

               ->setImage()
               ->setColor()
               ->setConference()

               ->setXprop( 'X-vevent-empty' );

        $a1 = $o->newValarm()
                ->setAction()
                ->setAttach()
                ->setDuration()
                ->setRepeat()
                ->setTrigger()
                ->setXprop( 'X-valarm-empty' );

        $a2 = $o->newValarm()
                ->setAction()
                ->setDescription()
                ->setDuration()
                ->setRepeat()
                ->setTrigger()
                ->setXprop( 'X-valarm-empty' );

        $o = $c->newVevent()
               ->setConfig( 'language', 'fr' )
               ->setAttendee()
               ->setAttendee()
               ->setComment()
               ->setComment()
               ->setComment()
               ->setDtstart()
               ->setDuration()
               ->setOrganizer()
               ->setStatus()
               ->setTransp()
               ->setUid()
               ->setUrl()

               ->setImage()
               ->setColor()
               ->setConference()

               ->setXprop( 'X-ABC-MMSUBJ' )
               ->setXprop( 'X-vevent-empty' );

        $o = $c->newVtodo()
               ->setComment()
               ->setCompleted()
               ->setDtstart()
               ->setDuration()
               ->setLocation()
               ->setOrganizer()

               ->setImage()
               ->setColor()
               ->setConference()

               ->setXprop( 'X-vtodo-empty' );

        $o = $c->newVevent()
               ->setCategories()
               ->setCategories()
               ->setComment()
               ->setDtstart()
               ->setDtend()
               ->setExdate()
               ->setRrule()
               ->setExdate()
               ->setRdate()
               ->setLastmodified()
               ->setOrganizer()
               ->setRecurrenceid()

               ->setImage()
               ->setColor()
               ->setConference()

               ->setXprop( 'X-vevent-empty' );

        $o = $c->newVjournal()
               ->setComment()
               ->setContact()
               ->setContact()
               ->setDtstart()
               ->setLastmodified()
               ->setRecurrenceid()
               ->setRequeststatus()

               ->setImage()
               ->setColor()

               ->setXprop( 'X-vjournal-empty' );

        $o = $c->newVfreebusy()
               ->setComment()
               ->setContact()
               ->setDtstart()
               ->setDuration()
               ->setFreebusy()
               ->setOrganizer()
               ->setXprop( 'X-vfreebusy-empty' );

        $o = $c->newVtodo()
               ->setComment()
               ->setContact()
               ->setDtstart()
               ->setDue()
               ->setOrganizer()
               ->setPercentcomplete()
               ->setRelatedto()
               ->setSequence()

               ->setImage()
               ->setColor()
               ->setConference()

               ->setXprop( 'X-vtodo-empty' );

        $o = $c->newVjournal()
               ->setComment()
               ->setContact()
               ->setContact()
               ->setDtstart()
               ->setLastmodified()
               ->setRequeststatus()

               ->setImage()
               ->setColor()

               ->setXprop( 'X-vjournal-empty' );

        $o = $c->newVtodo()
               ->setComment()
               ->setContact()
               ->setDtstart()
               ->setDuration()
               ->setOrganizer()
               ->setPercentcomplete()
               ->setRelatedto()
               ->setSequence()

               ->setImage()
               ->setColor()
               ->setConference()

               ->setXprop( 'X-vtodo-empty' );

        $this->parseCalendarTest( 1, $c );
    }

    /**
     * parseTest6 provider
     */
    public function parse6Provider() {

        $dataArr = [];

        $dataArr[] = [
            601,
            "BEGIN:VCALENDAR\r\n" .
            "VERSION:2.0\r\n" .
            "PRODID:-//ShopReply Inc//CalReply 1.0//EN\r\n" .
            "METHOD:REFRESH\r\n" .
            "SOURCE;x-a=first;VALUE=uri:message://https://www.masked.de/account/subscripti\r\n" .
            " on/delivery/8878/%3Fweek=2021-W03\r\n" .
            "X-WR-CALNAME:ESPN Daily Calendar\r\n" .
            "X-WR-RELCALID:657d63b8-df1d-e611-8b88-06bb54d48d13\r\n" .
            "X-PUBLISH-TTL:P1D\r\n" .
            "BEGIN:VTIMEZONE\r\n" .
            "TZID:America/New_York\r\n" .
            "TZURL;x-a=first;VALUE=uri:message//:https://www.masked.de/account/subscriptio\r\n" .
            " n/delivery/8878/%3Fweek=2021-W03" .
            "BEGIN:STANDARD\r\n" .
            "DTSTART:20070101T020000\r\n" .
            "RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU;\r\n" .
            "TZOFFSETFROM:-0400\r\n" .
            "TZOFFSETTO:-0500\r\n" .
            "END:STANDARD\r\n" .
            "BEGIN:DAYLIGHT\r\n" .
            "DTSTART:20070101T020000\r\n" .
            "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU;\r\n" .
            "TZOFFSETFROM:-0500\r\n" .
            "TZOFFSETTO:-0400\r\n" .
            "END:DAYLIGHT\r\n" .
            "END:VTIMEZONE\r\n" .
            "BEGIN:VEVENT\r\n" .
            "UID:e2317772-f3a2-42cf-a5ac-e639fb6b2af0\r\n" .
            "CLASS:PUBLIC\r\n" .
            "TRANSP:TRANSPARENT\r\n" .
            "SUMMARY:⚽ English FA Cup on ESPN+\r\n" .
            "DTSTART;TZID=\"America/New_York\":20190316T081500\r\n" .
            "DTEND;TZID=\"America/New_York\":20190316T091500\r\n" .
            'DESCRIPTION:Watch live: http://bit.ly/FACuponEPlus\n\nNot an ESPN+ subscrib' . "\r\n\t" .
            'er? Start your free trial here: http://bit.ly/ESPNPlusSignup\n\nShare - http:' . "\r\n\t" .
            '//calrep.ly/2pLaM0n\n\nYou may unsubscribe by following - https://espn.calrep' . "\r\n\t" .
            'lyapp.com/unsubscribe/9bba908612a34be1881bc5098e8adbda\n\nPowered by CalReply' . "\r\n\t" .
            " - http://calrep.ly/poweredby\r\n" .
            'LOCATION:England\'s biggest soccer competition continues.\n\n• Watford vs. C' . "\r\n\t" .
            'rystal Palace (8:15 a.m. ET)\n• Swansea City vs. Manchester City (1:20 p.m.)\\' . "\r\n\t" .
            'n• Wolverhampton vs. Manchester United (3:55 p.m.)\n\nWatch live: http://bit.' . "\r\n\t" .
            "ly/FACuponEPlus\r\n" .
            "DTSTAMP:20190315T211012Z\r\n" .
            "LAST-MODIFIED:20190315T211012Z\r\n" .
            "SEQUENCE:1\r\n" .
            "URL;x-a=first;VALUE=uri:message//:https://www.masked.de/account/subscription/\r\n" .
            " delivery/8878/%3Fweek=2021-W03\r\n" .
            "BEGIN:VALARM\r\n" .
            "ACTION:DISPLAY\r\n" .
            "DESCRIPTION:Reminder\r\n" .
            "TRIGGER:-PT15M\r\n" .
            "END:VALARM\r\n" .
            "END:VEVENT\r\n" .
            "END:VCALENDAR\r\n"
        ];

        return $dataArr;
    }

    /**
     * Testing parse eol-htab, also test of empty unique_id
     *
     * @test
     * @dataProvider parse6Provider
     * @param int    $case
     * @param string $value
     * @throws Exception
     */
    public function parseTest6( $case, $value ) {
        $c = new Vcalendar();
        $c->parse( $value );

        $this->parseCalendarTest( $case, $c );

        // echo $c->createCalendar(); // test ###
    }
}
