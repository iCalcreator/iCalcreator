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
namespace Kigkonsult\Icalcreator;

use Exception;
use Kigkonsult\Icalcreator\Formatter\Property\Attendee;
use Kigkonsult\Icalcreator\Formatter\Property\Property;
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * class Prop2TextMultiTest,
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
class Prop2TextMultiTest extends DtBase
{
    /**
     * @var array|string[]
     */
    private static array $STCPAR   = [ IcalInterface::ORDER => 1, 'X-PARAM' => 'Y-vALuE' ];

    /**
     * @var string[]
     */
    private static array $EOLCHARS = [ "\r\n ", "\r\n\t", PHP_EOL . " ", PHP_EOL . "\t" ];

    /**
     * textMultiTest1/2 provider, test values for TEXT (MULTI) properties
     *
     * @return mixed[]
     * @throws Exception
     */
    public function textMultiProvider() : array
    {

        $dataArr = [];

        // CATEGORIES
        $value  = 'test,ANNIVERSARY,APPOINTMENT,BUSINESS,EDUCATION,HOLIDAY,MEETING,MISCELLANEOUS,NON-WORKING HOURS,NOT IN OFFICE,PERSONAL,PHONE CALL,SICK DAY,SPECIAL OCCASION,TRAVEL,VACATION';
        $params = [
            IcalInterface::LANGUAGE => 'EN'
        ] + self::$STCPAR;
        $dataArr[] = [
            2011,
            [
                IcalInterface::CATEGORIES  => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                    IcalInterface::AVAILABLE,
                    IcalInterface::VAVAILABILITY
                ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            strtoupper( IcalInterface::CATEGORIES ) .
            Property::formatParams( $params, [ IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        // COMMENT
        $value  = 'This is a test comment';
        $params = [
            IcalInterface::ALTREP   => 'This is an alternative representation',
            IcalInterface::LANGUAGE => 'EN'
        ] + self::$STCPAR;
        $dataArr[] = [
            2021,
            [
                IcalInterface::COMMENT  => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                    IcalInterface::VFREEBUSY,
                    IcalInterface::AVAILABLE,
                    IcalInterface::VAVAILABILITY
                ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            IcalInterface::COMMENT .
            Property::formatParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        // CONTACT
        $value  = 'Jim Dolittle, ABC test Industries, +1-919-555-1234';
        $params = [
            IcalInterface::ALTREP   => 'http://example.com/pdi/jdoe.vcf',
            IcalInterface::LANGUAGE => 'EN'
        ] + self::$STCPAR;
        $dataArr[] = [
            2031,
            [
                IcalInterface::CONTACT  => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                    IcalInterface::VFREEBUSY,
                    IcalInterface::AVAILABLE,
                    IcalInterface::VAVAILABILITY
                ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            IcalInterface::CONTACT .
            Property::formatParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        // DESCRIPTION
        $value  = 'Meeting to provide technical test review for \'Phoenix\' design.\nHappy Face Conference Room. Phoenix design team MUST attend this meeting.\nRSVP to team leader.';
        $params = [
            IcalInterface::ALTREP   => 'This is an alternative representation',
            IcalInterface::LANGUAGE => 'EN'
        ] + self::$STCPAR;
        $dataArr[] = [
            2041,
            [
                IcalInterface::DESCRIPTION => [ IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            IcalInterface::DESCRIPTION .
                Property::formatParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
                ':' . $value
        ];

        // RESOURCES
        $value  = 'test,EASEL,PROJECTOR,VCR';
        $params = [
                IcalInterface::ALTREP   => 'This is an alternative representation',
                IcalInterface::LANGUAGE => 'EN'
            ] + self::$STCPAR;
        $dataArr[] = [
            2051,
            [
                IcalInterface::RESOURCES => [ IcalInterface::VEVENT, IcalInterface::VTODO ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            IcalInterface::RESOURCES .
            Property::formatParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        // ATTENDEE
        $value       = 'MAILTO:ildoit2061.test@example.com';
        $params      = [
                IcalInterface::CUTYPE         => IcalInterface::GROUP,
                IcalInterface::MEMBER         => [
                    'MAILTO:DEV-GROUP1@example.com',
                    'MAILTO:DEV-GROUP2@example.com',
                    'MAILTO:DEV-GROUP3@example.com',
                ],
                IcalInterface::ROLE           => IcalInterface::OPT_PARTICIPANT,
                IcalInterface::PARTSTAT       => IcalInterface::TENTATIVE,
                IcalInterface::RSVP           => IcalInterface::TRUE,
                IcalInterface::DELEGATED_TO   => [
                    'MAILTO:bob@example.com',
                    'MAILTO:rob@example.com',
                ],
                IcalInterface::DELEGATED_FROM => [
                    'MAILTO:jane@example.com',
                    'MAILTO:mary@example.com',
                ],
                IcalInterface::SENT_BY        => 'boss@example.com',          // note missing MAILTO:
                IcalInterface::EMAIL          => 'MAILTO:hammer@example.com', // MAILTO: wíll be removed
                IcalInterface::CN             => 'John Doe',
                IcalInterface::DIR            => 'ldap://example.com:6666/o=ABC%20Industries,c=US???(cn=Jim%20Dolittle)',
                IcalInterface::LANGUAGE       => 'EN'
            ] + self::$STCPAR;
        $getValue  = Pc::factory(
            CalAddressFactory::conformCalAddress( $value ),
            $params
        );
        $getValue2 = clone $getValue;
        $getValue2->params[IcalInterface::SENT_BY] = 'mailto:boss@example.com';
        $getValue2->params[IcalInterface::EMAIL]   = 'hammer@example.com';
        foreach( $getValue2->getParams() as $pKey => $pValue ) {
            if( in_array( $pKey, [ IcalInterface::MEMBER, IcalInterface::DELEGATED_TO, IcalInterface::DELEGATED_FROM], true )) {
                foreach( $pValue as $pIx => $pValue2 ) {
                    $getValue2->params[$pKey][$pIx] = CalAddressFactory::conformCalAddress( $pValue2 );
                }
            }
        } // end foreach
        $expectedString = trim( Attendee::format( IcalInterface::ATTENDEE, [ $getValue2 ], true ));
        $expectedString = str_replace( Util::$CRLF . ' ' , '', $expectedString);
        $expectedString = str_replace( '\,', ',', $expectedString );
        $dataArr[] = [
            2061,
            [
                IcalInterface::ATTENDEE => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue2,
            $expectedString
        ];

        $value     = 'MAILTO:ildoit2062.test@example.com';
        $params    =  [
                IcalInterface::MEMBER         => '"DEV-GROUP2062@example.com"',
                IcalInterface::DELEGATED_TO   => '"bob2062@example.com"',
                IcalInterface::DELEGATED_FROM => '"jane2062@example.com"',
            ] + self::$STCPAR;
        $getValue  = Pc::factory(
            CalAddressFactory::conformCalAddress( $value ),
            $params
        );
        $getValue2 = $getValue;
        $getValue2->params[IcalInterface::MEMBER]         = [ 'mailto:DEV-GROUP2062@example.com' ];
        $getValue2->params[IcalInterface::DELEGATED_TO]   = [ 'mailto:bob2062@example.com' ];
        $getValue2->params[IcalInterface::DELEGATED_FROM] = [ 'mailto:jane2062@example.com' ];
        $expectedString = trim( Attendee::format( IcalInterface::ATTENDEE, [ $getValue2 ], true ));
        $expectedString = str_replace( self::$EOLCHARS , null, $expectedString);
        $expectedString = str_replace( '\,', ',', $expectedString );
        $dataArr[] = [
            2062,
            [
                IcalInterface::ATTENDEE => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue2,
            $expectedString
        ];

        $value     = 'MAILTO:ildoit2063.test@example.com';
        $params    = [ 'X-PARAM' => 'Y-vALuE' ];
        $getValue  = Pc::factory(
            CalAddressFactory::conformCalAddress( $value ),
            $params
        );
        $expectedString = trim( Attendee::format( IcalInterface::ATTENDEE, [ $getValue ], true ));
        $expectedString = str_replace( self::$EOLCHARS , null, $expectedString);
        $expectedString = str_replace( '\,', ',', $expectedString );
        $dataArr[] = [
            2063,
            [
                IcalInterface::ATTENDEE => [ IcalInterface::VFREEBUSY ] // , Vcalendar::VFREEBUSY
            ],
            $value,
            $params + [ IcalInterface::EMAIL => 'ildoit2063-2@example.com' ], // will be skipped
            $getValue,
            $expectedString
        ];

        // RELATED-TO
        $value    = 'test' . StringFactory::getRandChars( 32 );
        $params   = self::$STCPAR;
        $getValue = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            2071,
            [
                IcalInterface::RELATED_TO => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::RELATED_TO .
            Property::formatParams( $params ) .
            ':' . $value
        ];

        // ATTACH
        $value  = 'CID:jsmith.part3.test.960817T083000.xyzMail@example.com';
        $params = self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            2081,
            [
                IcalInterface::ATTACH => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::ATTACH . Property::formatParams( $params ) . ':' . $value
        ];

        // ATTACH
        $value  = 'ftp://example.com/pub/reports/test/r-960812.ps';
        $params = [ IcalInterface::FMTTYPE => 'application/postscript' ] + self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            2082,
            [
                IcalInterface::ATTACH => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::ATTACH . Property::formatParams( $params ) . ':' . $value
        ];

        // ATTACH
        $value  = 'testAAABAAEAEBAQAAEABAAoAQAAFgAAACgAAAAQAAAAIAAAAAEABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAAAAAgIAAAICAgADAwMAA////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMwAAAAAAABNEMQAAAAAAAkQgAAAAAAJEREQgAAACECQ0QgEgAAQxQzM0E0AABERCRCREQAADRDJEJEQwAAAhA0QwEQAAAAAEREAAAAAAAAREQAAAAAAAAkQgAAAAAAAAMgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $params = [
            IcalInterface::FMTTYPE  => 'image/vnd.microsoft.icon',
            IcalInterface::ENCODING => IcalInterface::BASE64,
            IcalInterface::VALUE    => IcalInterface::BINARY,
        ] + self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            2083,
            [
                IcalInterface::ATTACH => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::ATTACH . Property::formatParams( $params ) . ':' . $value
        ];


        // IMAGE
        $value  = 'CID:jsmith.part3.test.960817T083000.xyzMail@example.com';
        $params = [ IcalInterface::VALUE => IcalInterface::URI ] + self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            2091,
            [
                IcalInterface::IMAGE => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::IMAGE . Property::formatParams( $params ) . ':' . $value
        ];

        // IMAGE
        $value  = 'ftp://example.com/pub/reports/test//r-960812.png';
        $params = [
            IcalInterface::VALUE   => IcalInterface::URI,
            IcalInterface::FMTTYPE => 'application/png',
            IcalInterface::DISPLAY => IcalInterface::BADGE . ',' . IcalInterface::THUMBNAIL
        ] + self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            2092,
            [
                IcalInterface::IMAGE => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::IMAGE . Property::formatParams( $params ) . ':' . $value
        ];

        // IMAGE
        $value  = 'testAAABAAEAEBAQAAEABAAoAQAAFgAAACgAAAAQAAAAIAAAAAEABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAAAAAgIAAAICAgADAwMAA////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMwAAAAAAABNEMQAAAAAAAkQgAAAAAAJEREQgAAACECQ0QgEgAAQxQzM0E0AABERCRCREQAADRDJEJEQwAAAhA0QwEQAAAAAEREAAAAAAAAREQAAAAAAAAkQgAAAAAAAAMgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $params = [
                IcalInterface::VALUE    => IcalInterface::BINARY,
                IcalInterface::FMTTYPE  => 'image/vnd.microsoft.icon',
                IcalInterface::ENCODING => IcalInterface::BASE64,
                IcalInterface::DISPLAY  => IcalInterface::BADGE . ',' . IcalInterface::THUMBNAIL
            ] + self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            2093,
            [
                IcalInterface::IMAGE => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::IMAGE . Property::formatParams( $params ) . ':' . $value
        ];


        // REQUEST_STATUS
        $value  = [
            IcalInterface::STATCODE => '3.70',
            IcalInterface::STATDESC => 'Invalid test calendar user',
            IcalInterface::EXTDATA  => 'ATTENDEE:mailto:jsmith@example.com'
        ];
        $params = [ IcalInterface::LANGUAGE => 'EN' ] + self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            2111,
            [
                IcalInterface::REQUEST_STATUS => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                    IcalInterface::VFREEBUSY
                ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::REQUEST_STATUS .
            Property::formatParams( $params, [ IcalInterface::LANGUAGE ] ) .
            ':' .
            number_format(
                (float) $value[IcalInterface::STATCODE],
                2,
                Util::$DOT,
                null
            ) . ';' .
            Property::strrep( $value[IcalInterface::STATDESC] ) . ';' .
            Property::strrep( $value[IcalInterface::EXTDATA] )
        ];


        // CONFERENCE
        $value  = 'rtsp://audio.example.test.com/';
        $params = [
                IcalInterface::VALUE   => IcalInterface::URI,
                IcalInterface::FEATURE => IcalInterface::AUDIO
            ] + self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            2121,
            [
                IcalInterface::CONFERENCE => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::CONFERENCE . Property::formatParams( $params ) . ':' . $value
        ];

        // CONFERENCE
        $value  = 'https://video-chat.example.test.com/group-id=1234';
        $params = [
                IcalInterface::VALUE    => IcalInterface::URI,
                IcalInterface::FEATURE  => IcalInterface::AUDIO . ',' . IcalInterface::VIDEO,
                IcalInterface::LANGUAGE => 'EN',
            ] + self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $specKeys  = [ IcalInterface::FEATURE, IcalInterface::LABEL, IcalInterface::LANGUAGE ];
        $dataArr[] = [
            2122,
            [
                IcalInterface::CONFERENCE => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::CONFERENCE . Property::formatParams( $params, $specKeys ) . ':' . $value
        ];

        // CONFERENCE
        $value  = 'https://video-chat.example.test.com/group-id=1234';
        $params = [
                IcalInterface::VALUE   => IcalInterface::URI,
                IcalInterface::FEATURE => IcalInterface::VIDEO,
                IcalInterface::LABEL   => "Web video chat, access code=76543"
            ] + self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            2123,
            [
                IcalInterface::CONFERENCE => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::CONFERENCE . Property::formatParams( $params, $specKeys ) . ':' . $value
        ];

        // NAME
        $value  = 'A calendar test name';
        $params = [
                IcalInterface::ALTREP   => 'This is an alternative representation',
                IcalInterface::LANGUAGE => 'EN'
            ] + self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            2401,
            [
                IcalInterface::NAME => []
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::NAME .
            Property::formatParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        // STRUCTURED-DATA - text
        $strDtaSpecKeys = [ IcalInterface::FMTTYPE, IcalInterface::SCHEMA, IcalInterface::VALUE ];
        $value   = 'This is a test STRUCTURED-DATA 2501';
        $params  = self::$STCPAR;
        $params2 = [ IcalInterface::VALUE => IcalInterface::TEXT ] + self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params2
        );
        $dataArr[] = [
            2501,
            [
                IcalInterface::STRUCTURED_DATA => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                    IcalInterface::VLOCATION,
                    IcalInterface::VRESOURCE
                ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::STRUCTURED_DATA . Property::formatParams( $params2, $strDtaSpecKeys ) . ':' . $value
        ];

        // STRUCTURED-DATA - uri
        $strDtaSpecKeys = [ IcalInterface::FMTTYPE, IcalInterface::SCHEMA, IcalInterface::VALUE ];
        $value   = 'https://structured.data.test.org/structured_data2502';
        $params  = [ IcalInterface::VALUE => IcalInterface::URI ] + self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            2502,
            [
                IcalInterface::STRUCTURED_DATA => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                    IcalInterface::VLOCATION,
                    IcalInterface::VRESOURCE
                ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::STRUCTURED_DATA . Property::formatParams( $params, $strDtaSpecKeys ) . ':' . $value
        ];

        // STRUCTURED-DATA - binary
        $strDtaSpecKeys = [ IcalInterface::FMTTYPE, IcalInterface::SCHEMA, IcalInterface::VALUE, IcalInterface::ENCODING ];
        $value  = 'This is a test BASE64 encoded data 2503==';
        $params = [
                IcalInterface::VALUE    => IcalInterface::BINARY,
                IcalInterface::FMTTYPE  => 'application/ld+json',
                IcalInterface::SCHEMA   => "https://schema.org/FlightReservation",
            ] + self::$STCPAR;
        $params2 =  [
                IcalInterface::VALUE    => IcalInterface::BINARY,
                IcalInterface::FMTTYPE  => 'application/ld+json',
                IcalInterface::SCHEMA   => "https://schema.org/FlightReservation",
                IcalInterface::ENCODING => IcalInterface::BASE64,
            ] + self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params2
        );
        $dataArr[] = [
            2503,
            [
                IcalInterface::STRUCTURED_DATA => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                    IcalInterface::VLOCATION,
                    IcalInterface::VRESOURCE
                ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::STRUCTURED_DATA . Property::formatParams( $params2, $strDtaSpecKeys ) . ':' . $value
        ];

        // STYLED-DESCRIPTION - uri
        $value  = 'http://example.org/desc2602.test.html';
        $params = [
                IcalInterface::VALUE    => IcalInterface::URI,
                IcalInterface::ALTREP   => 'http://example.org/altrep202.html', // skipped
                IcalInterface::LANGUAGE => 'EN'                                 // skipped
            ] + self::$STCPAR;
        $params2 = [
                IcalInterface::VALUE   => IcalInterface::URI,
                IcalInterface::DERIVED => IcalBase::FALSE
            ] + self::$STCPAR;
        $dataArr[] = [
            2602,
            [
                IcalInterface::STYLED_DESCRIPTION => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                    IcalInterface::VFREEBUSY,
//                  IcalInterface::PARTICIPANT,   // todo
                ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params2
            ),
            IcalInterface::STYLED_DESCRIPTION .
                Property::formatParams( $params2, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] )
                . ':' . $value
        ];

        // STYLED-DESCRIPTION - text
        $value  = 'This is a longer test styled 2603 description property with a number of meaningless words';
        $params = [
                IcalInterface::VALUE    => IcalInterface::TEXT,
                IcalInterface::ALTREP   => 'http://example.org/altrep203.html',
                IcalInterface::LANGUAGE => 'EN',
                IcalInterface::DERIVED  => IcalInterface::TRUE
            ] + self::$STCPAR;
        $dataArr[] = [
            2603,
            [
                IcalInterface::STYLED_DESCRIPTION => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                    IcalInterface::VFREEBUSY,
//                  IcalInterface::PARTICIPANT,   // @todo
                ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            IcalInterface::STYLED_DESCRIPTION .
                Property::formatParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
                ':' . $value
        ];

        // DESCRIPTION may appear more than once in VJOURNAL
        $value  = 'Meeting to provide test technical review for \'Phoenix\' design.\nHappy Face Conference Room. Phoenix design team MUST attend this meeting.\nRSVP to team leader.';
        $params = [
                IcalInterface::ALTREP   => 'This is an alternative representation',
                IcalInterface::LANGUAGE => 'EN'
            ] + self::$STCPAR;
        $dataArr[] = [
            2701,
            [
                IcalInterface::DESCRIPTION => [ IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            IcalInterface::DESCRIPTION .
            Property::formatParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        // LOCATION may appear more than once in PARTICIPANT
        $value  = 'Conference test Room - F123, Bldg. 002';
        $params = [
                IcalInterface::ALTREP   => 'This is an alternative representation',
                IcalInterface::LANGUAGE => 'EN'
            ] + self::$STCPAR;
        $dataArr[] = [
            2801,
            [
                IcalInterface::LOCATION => [ IcalInterface::PARTICIPANT ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            IcalInterface::LOCATION .
            Property::formatParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        return $dataArr;
    }

    /**
     * Testing value TEXT (MULTI) properties
     *
     * @test
     * @dataProvider textMultiProvider
     *
     * @param int     $case
     * @param mixed[] $propComps
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function textMultiTest1(
        int    $case,
        array  $propComps,
        mixed  $value,
        mixed  $params,
        Pc     $expectedGet,
        string $expectedString
    ) : void
    {
        static $CDIN = [
            IcalInterface::CATEGORIES,
            IcalInterface::DESCRIPTION,
            IcalInterface::IMAGE,
            IcalInterface::NAME
        ];
        static $PAVLVR = [ IcalInterface::PARTICIPANT, IcalInterface::VLOCATION, IcalInterface::VRESOURCE ];
        $c = new Vcalendar();

        foreach( array_keys( $propComps ) as $propName ) {
            if( in_array( $propName, $CDIN, true )) {
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
        if( IcalInterface::NAME === $propName ) {
            return;
        }
        foreach( $propComps as $propName => $theComps ) {
            foreach( $theComps as $theComp ) {
                if( IcalInterface::ATTENDEE === $propName ) {
                    $expectedGet->params = CalAddressFactory::inputPrepAttendeeParams(
                        $expectedGet->params,
                        $theComp,
                        ''
                    );
                }
                $newMethod = 'new' . $theComp;
                $comp = match ( true ) {
                    ( IcalInterface::AVAILABLE === $theComp ) => $c->newVavailability()->{$newMethod}(),
                    in_array( $theComp, $PAVLVR, true )  => $c->newVevent()->{$newMethod}(),
                    default                                   => $c->{$newMethod}(),
                };

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
            self::getErrMsg(  null, $case . '-25', __FUNCTION__, 'Vcalendar', 'createComponent' )
        );

        $c2 = new Vcalendar();
        $c2->parse( $calendar1 );
        $this->assertEquals(
            $calendar1,
            $c2->createCalendar(),
            self::getErrMsg(  null, $case . '-26', __FUNCTION__, 'Vcalendar', 'parse, create and compare' )
        );

        if( IcalInterface::DESCRIPTION === $propName ) {
            $this->assertFalse(
                $c->isNameSet(),
                self::getErrMsg(  '(is-prop-set) ', $case . '-27', __FUNCTION__, $c->getCompType(), 'isNamSet' )
            );
            $c->setName( $value, $params );
            $c->setName( $value, $params );
            $this->assertTrue(
                $c->isNameSet(),
                self::getErrMsg(  '(is-prop-set) ', $case . '-28', __FUNCTION__, $c->getCompType(), 'isNamSet' )
            );
        } // end DESCRIPTION
        $this->parseCalendarTest( $case, $c, $expectedString );
    }

    /**
     * Testing calendar/component instance with multi-propName
     *
     * @param string  $case
     * @param CalendarComponent|Vcalendar $instance
     * @param string  $propName
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @param string  $expectedString
     */
    private function propNameTest(
        string                        $case,
        Vcalendar | CalendarComponent $instance,
        string                        $propName,
        mixed                         $value,
        mixed                         $params,
        Pc                            $expectedGet,
        string                        $expectedString
    ) : void
    {
        $getMethod    = StringFactory::getGetMethodName( $propName );
        if( ! method_exists( $instance, $getMethod )) {
            return;
        }

        [ $createMethod, $deleteMethod, , $isMethod, $setMethod ] = self::getPropMethodnames( $propName );
        $this->assertFalse(
            $instance->{$isMethod}(),
            self::getErrMsg(  null, $case . '-1', __FUNCTION__, $instance->getCompType(), $isMethod )
        );

        if( IcalInterface::REQUEST_STATUS === $propName ) {
            $instance->{$setMethod}(
                $value[IcalInterface::STATCODE],
                $value[IcalInterface::STATDESC],
                $value[IcalInterface::EXTDATA],
                $params
            );
        }
        else {
            $instance->{$setMethod}( $value, $params );
        }
        $this->assertTrue(
            $instance->{$isMethod}(),
            self::getErrMsg(  null, $case . '-2', __FUNCTION__, $instance->getCompType(), $isMethod )
        );

        $getValue = $instance->{$getMethod}( null, true );
        $this->assertEquals(
            $expectedGet,
            $getValue,
            self::getErrMsg(  null, $case . '-3', __FUNCTION__, $instance->getCompType(), $getMethod )
        );

        // parameter ORDER test
        if( $getValue->hasParamkey( Vcalendar::ORDER )) {
            $this->assertSame(
                1,
                $getValue->getParams( Vcalendar::ORDER ),
                self::getErrMsg(  null, $case . '-3ParamInt', __FUNCTION__, $instance->getCompType(), $getMethod )
            );
        }

        $createString = str_replace( Util::$CRLF . ' ' , null, $instance->{$createMethod}());
        $createString = str_replace( '\,', ',', $createString );
        $this->assertEquals(
            $expectedString,
            trim( $createString ),
            self::getErrMsg(  null, $case . '-4', __FUNCTION__, $instance->getCompType(), $createMethod )
        );

        $instance->{$deleteMethod}();
        $this->assertFalse(
            $instance->{$getMethod}(),
            self::getErrMsg(  '(after delete) ', $case . '-5a', __FUNCTION__, $instance->getCompType(), $getMethod )
        );
        $instance->{$deleteMethod}();
        $this->assertFalse(
            $instance->{$getMethod}(),
            self::getErrMsg(  '(after delete) ', $case . '-5b', __FUNCTION__, $instance->getCompType(), $getMethod )
        );
        $this->assertFalse(
            $instance->{$isMethod}(),
            self::getErrMsg(  '(is-prop-set) ', $case . '-5c', __FUNCTION__, $instance->getCompType(), $getMethod )
        );

        if( IcalInterface::REQUEST_STATUS === $propName ) {
            $instance->{$setMethod}(
                $value[IcalInterface::STATCODE],
                $value[IcalInterface::STATDESC],
                $value[IcalInterface::EXTDATA],
                $params
            );
            $instance->{$setMethod}(
                Pc::factory(
                    [
                        IcalInterface::STATCODE => $value[IcalInterface::STATCODE],
                        IcalInterface::STATDESC => $value[IcalInterface::STATDESC],
                        IcalInterface::EXTDATA  => $value[IcalInterface::EXTDATA],
                    ],
                    $params
                )
            );
        }
        else {
            $instance->{$setMethod}( $value, $params );
            $instance->{$setMethod}( Pc::factory( $value, $params ));
        }
        $this->assertTrue(
            $instance->{$isMethod}(),
            self::getErrMsg(  '(is-prop-set) ', $case . '-6a', __FUNCTION__, $instance->getCompType(), $isMethod )
        );

        $instance->{$deleteMethod}();
        $instance->{$deleteMethod}();
        $this->assertFalse(
            $instance->{$isMethod}(),
            self::getErrMsg(  '(is-prop-set) ', $case . '-6b', __FUNCTION__, $instance->getCompType(), $isMethod )
        );
        $this->assertFalse(
            $instance->{$getMethod}(),
            self::getErrMsg(  '(after delete) ', $case . '-6c', __FUNCTION__, $instance->getCompType(), $getMethod )
        );

        if( IcalInterface::REQUEST_STATUS === $propName ) {
            $instance->{$setMethod}(
                Pc::factory(
                    [
                        IcalInterface::STATCODE => $value[IcalInterface::STATCODE],
                        IcalInterface::STATDESC => $value[IcalInterface::STATDESC],
                        IcalInterface::EXTDATA  => $value[IcalInterface::EXTDATA],
                        ],
                    $params
                )
            );
            $instance->{$setMethod}(
                $value[IcalInterface::STATCODE],
                $value[IcalInterface::STATDESC],
                $value[IcalInterface::EXTDATA],
                $params
            );
        }
        else {
            $instance->{$setMethod}( Pc::factory( $value, $params ));
            $instance->{$setMethod}( $value, $params );
        }
    }

    /**
     * Testing value TEXT (MULTI) properties getAll<property> methods
     *
     * @test
     * @dataProvider textMultiProvider
     *
     * @param int     $case
     * @param mixed[] $propComps
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet     NOT used here
     * @param string  $expectedString  NOT used here
     * @throws Exception
     */
    public function textMultiTest2(
        int    $case,
        array  $propComps,
        mixed  $value,
        mixed  $params,
        Pc     $expectedGet,
        string $expectedString
    ) : void
    {
        $c = new Vcalendar();

        foreach( $propComps as $propName => $theComps ) {
            // vCalendar test
            if( in_array( $propName, [
                IcalInterface::CATEGORIES,
                IcalInterface::DESCRIPTION,
                IcalInterface::IMAGE,
                IcalInterface::NAME
            ], true )) {
                $this->textMultiTest2test($case . '-21', $c, $propName, $value, $params );
            }
            if( IcalInterface::NAME === $propName ) {
                return;
            }

            // components test
            foreach( $theComps as $theComp ) {
                if(( Vcalendar::CONTACT === $propName ) && ( Vcalendar::VFREEBUSY === $theComp )) {
                    return;
                }
                $newMethod = 'new' . $theComp;
                $comp = match ( true ) {
                    IcalInterface::AVAILABLE === $theComp => $c->newVavailability()->{$newMethod}(),
                    in_array( $theComp, [ IcalInterface::PARTICIPANT, IcalInterface::VLOCATION, IcalInterface::VRESOURCE ], true )
                                                          => $c->newVevent()->{$newMethod}(),
                    default                               => $c->{$newMethod}(),
                };
                $this->textMultiTest2test($case . '-22', $comp, $propName, $value, $params );
            } // end foreach
        } // end foreach
    }

    /**
     * @param string $case
     * @param Vcalendar|CalendarComponent $instance
     * @param string $propName
     * @param mixed $value
     * @param mixed $params
     */
    public function textMultiTest2test(
        string                        $case,
        Vcalendar | CalendarComponent $instance,
        string                        $propName,
        mixed                         $value,
        mixed                         $params
    ) : void
    {
        static $TEST   = 'test';
        $setMethodName = StringFactory::getSetMethodName( $propName );
        $foreachName   = str_replace('set', 'getAll', $setMethodName );
        if( IcalInterface::REQUEST_STATUS === $propName ) {
            $instance->{$setMethodName}(
                $value[IcalInterface::STATCODE],
                str_replace( $TEST, $TEST . 0, $value[IcalInterface::STATDESC] ),
                $value[IcalInterface::EXTDATA],
                $params
            );
            $instance->{$setMethodName}(
                $value[IcalInterface::STATCODE],
                str_replace( $TEST, $TEST . 1, $value[IcalInterface::STATDESC] ),
                $value[IcalInterface::EXTDATA],
                $params
            );
        }
        else {
            $value1        = str_replace( $TEST, $TEST . 0, $value );
            $value2        = str_replace( $TEST, $TEST . 1, $value );
            $instance->{$setMethodName}( $value1, $params );
            $instance->{$setMethodName}( $value2, $params );
        }

        foreach( $instance->{$foreachName}() as $x => $propValue ) {
            $testValue = ( IcalInterface::REQUEST_STATUS === $propName ) ? $propValue[IcalInterface::STATDESC] : $propValue;
            $this->assertStringContainsString(
                $TEST . $x,
                $testValue,
                self::getErrMsg(  '', $case . '-1', __FUNCTION__, $instance->getCompType(), $foreachName ) .
                 PHP_EOL . var_export( $instance->{$foreachName}(), true )
            );
        }
        foreach( $instance->{$foreachName}( true ) as $x => $propValue ) {
            $testValue = ( IcalInterface::REQUEST_STATUS === $propName ) ? $propValue->value[IcalInterface::STATDESC] : $propValue->value;
            $this->assertStringContainsString(
                $TEST . $x,
                $testValue,
                self::getErrMsg(  '', $case . '-2', __FUNCTION__, $instance->getCompType(), $foreachName ) .
                PHP_EOL . var_export( $instance->{$foreachName}( true ), true )
            );
        }
    }


    /**
     * Testing value TEXT (MULTI) properties multi read
     *
     * @test
     */
    public function textMultiTest3() : void
    {
        $calendar = Vcalendar::factory();
        $this->assertFalse(
            $calendar->isDescriptionSet(),
            '#0a multi Vcalendar::isDescriptionSet() NOT false'
        );
        $this->assertFalse(
            $calendar->isDescriptionSet(),
            '#0b multi Vcalendar::isDescriptionSet() NOT False '
        );
        static $DESCRIPTION = 'Description ';
        for( $x = 1; $x <= 5; ++$x ) {
            $calendar->setDescription( $DESCRIPTION . $x );
            $this->assertFalse(
                $calendar->isXpropSet( 'x-' . $x ),
                '#0c multi Vcalendar::isXpropset() NOT False for ' . 'x-' . $x
            );
            $calendar->setXprop( 'x-' . $x, $x );
        }
        $this->assertTrue(
            $calendar->isDescriptionSet(),
            '#0d multi Vcalendar::isDescriptionSet() NOT true'
        );
        $x = 0;
        foreach( $calendar->getAllDescription() as $description ) { // and inclParams = false
            $this->assertSame(
                $description,
                $DESCRIPTION . ++$x,
                '#0ea multi Vcalendar::getAllDescription() diff'
            );
        } // end foreach

        $cnt1 = 0;
        while( false !== $calendar->getDescription()) {
            ++$cnt1;
        }
        $cnt2 = 0;
        while( false !== $calendar->getDescription()) {
            ++$cnt2;
        }
        $this->assertSame(
            5,
            $cnt2,
            '#1a multi Vcalendar::getDescription() counts is NOT 5'
        );
        $this->assertSame(
            $cnt1,
            $cnt2,
            '#1b double multi Vcalendar::getDescripton() session counts do not match'
        );

        $this->assertTrue(
            $calendar->isXpropSet(),
            '#2_ multi Vcalendar::isXpropSetSet() NOT true'
        );
        $cnt1 = 0;
        while( false !== $calendar->getXprop()) {
            ++$cnt1;
        }
        $cnt2 = 0;
        while( false !== $calendar->getXprop()) {
            ++$cnt2;
        }
        $this->assertSame(
            5,
            $cnt1,
            '#2a multi Vcalendar::getXprop() counts is NOT 5'
        );
        $this->assertSame(
            $cnt1,
            $cnt2,
            '#2b double multi Vcalendar::getXprop() session counts do not match'
        );

        $event = $calendar->newVevent();
        $this->assertFalse(
            $event->isCommentSet(),
            '#3a multi Vcalendar::isComentSet() NOT false'
        );
        static $COMMENT = 'Comment ';
        for( $x = 1; $x <= 5; ++$x ) {
            $event->setComment( $COMMENT . $x );
            $event->setXprop( 'x-' . $x, $x );
        }
        $this->assertTrue(
            $event->isCommentSet(),
            '#3b multi event->isCommentSet NOT true'
        );
        $x = 0;
        foreach( $event->getAllComment() as $comment ) { // and inclParams = false
            $this->assertSame(
                $comment,
                $COMMENT . ++$x,
                '#3c multi Vevent::getAllComment() diff'
            );
        } // end foreach

        $cnt1 = 0;
        while( false !== $event->getComment()) {
            ++$cnt1;
        }
        $cnt2 = 0;
        while( false !== $event->getComment()) {
            ++$cnt2;
        }
        $this->assertSame(
            5,
            $cnt1,
            '#3d multi Vevent::getComment() counts is NOT 5'
        );
        $this->assertSame(
            $cnt1,
            $cnt2,
            '#3e double multi Vevent::getComment() session counts do not match'
        );

        $cnt1 = 0;
        while( false !== $event->getXprop()) {
            ++$cnt1;
        }
        $cnt2 = 0;
        while( false !== $event->getXprop()) {
            ++$cnt2;
        }
        $this->assertSame(
            5,
            $cnt1,
            '#4a multi Vevent::getXprop() counts is NOT 5'
        );
        $this->assertSame(
            $cnt1,
            $cnt2,
            '#4b double multi Vevent::getXprop() session counts do not match'
        );

        $alarm = $event->newValarm();
        static $ATTACHval1 = 'https://test';
        static $ATTACHval2 = 'info/doloribus-fuga-optio-enim-doloremque-consectetur.html';
        for( $x = 1; $x <= 5; ++$x ) {
            $alarm->setAttach( $ATTACHval1 . $x . $ATTACHval2 );
            $alarm->setXprop( 'x-' . $x, $x );
        }
        $this->assertTrue(
            $alarm->isAttachSet(),
            '#5a multi alarm->isAttachSet NOT true'
        );
        $x = 0;
        foreach( $alarm->getAllAttach() as $attach ) { // and inclParams = false
            $this->assertSame(
                $attach,
                $ATTACHval1 . ++$x . $ATTACHval2,
                '#5b multi Valarm::getAllAttach() diff'
            );
        } // end foreach

        $cnt1 = 0;
        while( false !== $alarm->getAttach()) {
            ++$cnt1;
        }
        $cnt2 = 0;
        while( false !== $alarm->getAttach()) {
            ++$cnt2;
        }
        $this->assertSame(
            5,
            $cnt1,
            '#5c multi Valarm::getAttach() counts is NOT 5'
        );
        $this->assertSame(
            $cnt1,
            $cnt2,
            '#5d double multi Valarm::getAttach() session counts do not match'
        );

        $cnt1 = 0;
        while( false !== $alarm->getXprop()) {
            ++$cnt1;
        }
        $cnt2 = 0;
        while( false !== $alarm->getXprop()) {
            ++$cnt2;
        }
        $this->assertSame(
            5,
            $cnt1,
            '#6a multi Valarm::getXprop() counts is NOT 5'
        );
        $this->assertSame(
            $cnt1,
            $cnt2,
            '#6b double multi Valarm::getXprop() session counts do not match'
        );
    }
}
