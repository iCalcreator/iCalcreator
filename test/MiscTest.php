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

use Kigkonsult\Icalcreator\Util\ParameterFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\GeoFactory;

/**
 * class MiscTest, testing VALUE TEXT etc
 * ATTENDEE
 * CATEGORIES
 * CLASS
 * COMMENT
 * CONTACT
 * DESCRIPTION
 * LOCATION
 * ORGANIZER
 * RELATED-TO
 * RESOURCES
 * STATUS
 * SUMMARY
 * TRANSP
 * URL
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 * @since  2.27.14 - 2019-02-19
 */
class MiscTest extends DtBase
{
    private static $ERRFMT   = "Error %sin case #%s, %s <%s>->%s";
    private static $STCPAR   = [ 'X-PARAM' => 'Y-vALuE' ];
    private static $EOLCHARS = [ "\r\n ", "\r\n\t", PHP_EOL . " ", PHP_EOL . "\t" ];

    /**
     * testMisc1 provider
     */
    public function Misc1Provider() {

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

        // URL
        $value  = 'http://example.com/pub/calendars/jsmith/mytime.ics';
        $params = []  + self::$STCPAR;
        $dataArr[] = [
            1051,
            [
                Vcalendar::URL => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL, Vcalendar::VFREEBUSY ]
            ],
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            strtoupper( Vcalendar::URL ) .
            ParameterFactory::createParams( $params ) .
            ':' . $value
        ];

        // ORGANIZER
        $value  = 'MAILTO:ildoit@example.com';
        $params = [
                Vcalendar::CN             => 'John Doe',
                Vcalendar::DIR            => 'ldap://example.com:6666/o=ABC%20Industries,c=US???(cn=Jim%20Dolittle)',
                Vcalendar::SENT_BY        => 'MAILTO:boss@example.com',
                Vcalendar::LANGUAGE       => 'EN'
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            1061,
            [
                Vcalendar::ORGANIZER => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ]
            ],
            $value,
            $params,
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

        // CLASS
        $value  = Vcalendar::CONFIDENTIAL;
        $params = self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            1071,
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
            1081,
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
            1082,
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
            1083,
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
            1091,
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
        foreach( $propComps as $propName => $theComps ) {
            foreach( $theComps as $theComp ) {
                $newMethod = 'new' . $theComp;
                $comp      = $c->{$newMethod}();

                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );

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
            }
        }

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

        // RESOURCES
        $value  = ['EASEL','PROJECTOR','VCR'];
        $params = [
                Vcalendar::ALTREP   => 'This is an alternative representation',
                Vcalendar::LANGUAGE => 'EN'
            ] + self::$STCPAR;
        $dataArr[] = [
            2052,
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
            ':' . implode( ',', $value )
        ];

        // ATTENDEE
        $value  = 'MAILTO:ildoit@example.com';
        $params = [
                Vcalendar::CUTYPE         => Vcalendar::GROUP,
                Vcalendar::MEMBER         => 'MAILTO:DEV-GROUP@example.com',
                Vcalendar::ROLE           => Vcalendar::OPT_PARTICIPANT,
                Vcalendar::PARTSTAT       => Vcalendar::TENTATIVE,
                Vcalendar::RSVP           => Vcalendar::TRUE,
                Vcalendar::DELEGATED_TO   => 'MAILTO:bob@example.com',
                Vcalendar::DELEGATED_FROM => 'MAILTO:jane@example.com',
                Vcalendar::SENT_BY        => 'MAILTO:boss@example.com',
                Vcalendar::CN             => 'John Doe',
                Vcalendar::DIR            => 'ldap://example.com:6666/o=ABC%20Industries,c=US???(cn=Jim%20Dolittle)',
                Vcalendar::LANGUAGE       => 'EN'
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $getValue[Util::$LCparams][Vcalendar::MEMBER]         = [$getValue[Util::$LCparams][Vcalendar::MEMBER]];
        $getValue[Util::$LCparams][Vcalendar::DELEGATED_TO]   = [$getValue[Util::$LCparams][Vcalendar::DELEGATED_TO]];
        $getValue[Util::$LCparams][Vcalendar::DELEGATED_FROM] = [$getValue[Util::$LCparams][Vcalendar::DELEGATED_FROM]];

        $expectedString = trim( CalAddressFactory::outputFormatAttendee( [ $getValue ], true ));
        $expectedString = str_replace( Util::$CRLF . ' ' , null, $expectedString);
        $expectedString = str_replace( '\,', ',', $expectedString );
        $dataArr[] = [
            2061,
            [
                Vcalendar::ATTENDEE => [ Vcalendar::VEVENT, Vcalendar::VTODO, Vcalendar::VJOURNAL ] // , Vcalendar::VFREEBUSY
            ],
            $value,
            $params,
            $getValue,
            $expectedString
        ];

        $value  = 'MAILTO:ildoit@example.com';
        $params = self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $expectedString = trim( CalAddressFactory::outputFormatAttendee( [ $getValue ], true ));
        $expectedString = str_replace( self::$EOLCHARS , null, $expectedString);
        $expectedString = str_replace( '\,', ',', $expectedString );
        $dataArr[] = [
            2062,
            [
                Vcalendar::ATTENDEE => [ Vcalendar::VFREEBUSY ] // , Vcalendar::VFREEBUSY
            ],
            $value,
            $params,
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
            2091,
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

        return $dataArr;
    }

    /**
     * Testing value TEXT (MULTI) properties
     *
     * @test
     * @dataProvider Misc2Provider
     * @param int    $case
     * @param array  $propComps
     * @param mixed  $value
     * @param mixed  $params
     * @param array  $expectedGet
     * @param string $expectedString
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
        foreach( $propComps as $propName => $theComps ) {
            foreach( $theComps as $theComp ) {
                $newMethod = 'new' . $theComp;
                $comp      = $c->{$newMethod}();

                $getMethod    = Vcalendar::getGetMethodName( $propName );
                $createMethod = Vcalendar::getCreateMethodName( $propName );
                $deleteMethod = Vcalendar::getDeleteMethodName( $propName );
                $setMethod    = Vcalendar::getSetMethodName( $propName );

                if( Vcalendar::REQUEST_STATUS == $propName ) {
                    $comp->{$setMethod}(
                        $value[Vcalendar::STATCODE],
                        $value[Vcalendar::STATDESC],
                        $value[Vcalendar::EXTDATA],
                        $params
                    );
                }
                else {
                    $comp->{$setMethod}( $value, $params );
                }

                $getValue = $comp->{$getMethod}( null, true );
                $this->assertEquals(
                    $expectedGet,
                    $getValue,
                    sprintf( self::$ERRFMT, null, $case, __FUNCTION__, $theComp, $getMethod )
                );

                $createString = str_replace( self::$EOLCHARS , null, $comp->{$createMethod}());
                $createString = str_replace( '\,', ',', $createString );
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

                if( Vcalendar::REQUEST_STATUS == $propName ) {
                    $comp->{$setMethod}(
                        $value[Vcalendar::STATCODE],
                        $value[Vcalendar::STATDESC],
                        $value[Vcalendar::EXTDATA],
                        $params
                    );
                }
                else {
                    $comp->{$setMethod}( $value, $params );
                }
            }
        }

        $this->parseCalendarTest( $case, $c, $expectedString );

    }

    /**
     * testMisc3 provider
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
    public function AllowEmptyTest4(
    ) {
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
            $tLat = substr( StringFactory::before_last('+', $getValue[1] ), 1 );
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
     * Testing parse eol-htab
     *
     * @test
     * @dataProvider parse5Provider
     * @param int    $case
     * @paran string $value
     */
    public function testparse5( $case, $value ) {
        $c = new Vcalendar();
        $c->parse( $value );

        $this->parseCalendarTest( $case, $c );

    }

    /**
     * testparse5 provider
     */
    public function parse5Provider() {

        $dataArr = [];

        $dataArr[] = [
            501,
            "BEGIN:VCALENDAR\r\n" .
            "VERSION:2.0\r\n" .
            "PRODID:-//ShopReply Inc//CalReply 1.0//EN\r\n" .
            "METHOD:REFRESH\r\n" .
            "X-WR-CALNAME:ESPN Daily Calendar\r\n" .
            "X-WR-RELCALID:657d63b8-df1d-e611-8b88-06bb54d48d13\r\n" .
            "X-PUBLISH-TTL:P1D\r\n" .
            "BEGIN:VTIMEZONE\r\n" .
            "TZID:America/New_York\r\n" .
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

}