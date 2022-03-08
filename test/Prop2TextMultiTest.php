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
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
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
     * @var string
     */
    private static string $ERRFMT   = "Error %sin case #%s, %s <%s>->%s";

    /**
     * @var array|string[]
     */
    private static array $STCPAR   = [ 'X-PARAM' => 'Y-vALuE' ];

    /**
     * @var string[]
     */
    private static array $EOLCHARS = [ "\r\n ", "\r\n\t", PHP_EOL . " ", PHP_EOL . "\t" ];

    /**
     * miscTest2 provider, test values for TEXT (MULTI) properties
     *
     * @return mixed[]
     */
    public function textMulti2Provider() : array
    {

        $dataArr = [];

        // CATEGORIES
        $value  = 'ANNIVERSARY,APPOINTMENT,BUSINESS,EDUCATION,HOLIDAY,MEETING,MISCELLANEOUS,NON-WORKING HOURS,NOT IN OFFICE,PERSONAL,PHONE CALL,SICK DAY,SPECIAL OCCASION,TRAVEL,VACATION';
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
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            strtoupper( IcalInterface::CATEGORIES ) .
            ParameterFactory::createParams( $params, [ IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        // COMMENT
        $value  = 'This is a comment';
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
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            IcalInterface::COMMENT .
            ParameterFactory::createParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        // CONTACT
        $value  = 'Jim Dolittle, ABC Industries, +1-919-555-1234';
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
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            IcalInterface::CONTACT .
            ParameterFactory::createParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        // DESCRIPTION
        $value  = 'Meeting to provide technical review for \'Phoenix\' design.\nHappy Face Conference Room. Phoenix design team MUST attend this meeting.\nRSVP to team leader.';
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
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            IcalInterface::DESCRIPTION .
                ParameterFactory::createParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
                ':' . $value
        ];

        // RESOURCES
        $value  = 'EASEL,PROJECTOR,VCR';
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
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            IcalInterface::RESOURCES .
            ParameterFactory::createParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        // ATTENDEE
        $value  = 'MAILTO:ildoit2061@example.com';
        $params = [
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
                IcalInterface::EMAIL          => 'MAILTO:hammer@example.com', // MAILTO: woíll be removed
                IcalInterface::CN             => 'John Doe',
                IcalInterface::DIR            => 'ldap://example.com:6666/o=ABC%20Industries,c=US???(cn=Jim%20Dolittle)',
                IcalInterface::LANGUAGE       => 'EN'
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $getValue2 = $getValue;
        $getValue2[Util::$LCparams][IcalInterface::SENT_BY] = 'MAILTO:boss@example.com';
        $getValue2[Util::$LCparams][IcalInterface::EMAIL]   = 'hammer@example.com';
        $expectedString = trim( CalAddressFactory::outputFormatAttendee( [ $getValue2 ], true ));
        $expectedString = str_replace( Util::$CRLF . ' ' , null, $expectedString);
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

        $value     = 'MAILTO:ildoit2062@example.com';
        $params    =  [
                IcalInterface::MEMBER         => '"DEV-GROUP2062@example.com"',
                IcalInterface::DELEGATED_TO   => '"bob2062@example.com"',
                IcalInterface::DELEGATED_FROM => '"jane2062@example.com"',
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $getValue2 = $getValue;
        $getValue2[Util::$LCparams][IcalInterface::MEMBER]         = [ 'MAILTO:DEV-GROUP2062@example.com' ];
        $getValue2[Util::$LCparams][IcalInterface::DELEGATED_TO]   = [ 'MAILTO:bob2062@example.com' ];
        $getValue2[Util::$LCparams][IcalInterface::DELEGATED_FROM] = [ 'MAILTO:jane2062@example.com' ];
        $expectedString = trim( CalAddressFactory::outputFormatAttendee( [ $getValue2 ], true ));
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
                IcalInterface::ATTENDEE => [ IcalInterface::VFREEBUSY ] // , Vcalendar::VFREEBUSY
            ],
            $value,
            $params + [ IcalInterface::EMAIL => 'ildoit2063-2@example.com' ], // will be skipped
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
                IcalInterface::RELATED_TO => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::RELATED_TO .
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
                IcalInterface::ATTACH => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::ATTACH . ParameterFactory::createParams( $params ) . ':' . $value
        ];

        // ATTACH
        $value  = 'ftp://example.com/pub/reports/r-960812.ps';
        $params = [ IcalInterface::FMTTYPE => 'application/postscript' ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2082,
            [
                IcalInterface::ATTACH => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::ATTACH . ParameterFactory::createParams( $params ) . ':' . $value
        ];

        // ATTACH
        $value  = 'AAABAAEAEBAQAAEABAAoAQAAFgAAACgAAAAQAAAAIAAAAAEABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAAAAAgIAAAICAgADAwMAA////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMwAAAAAAABNEMQAAAAAAAkQgAAAAAAJEREQgAAACECQ0QgEgAAQxQzM0E0AABERCRCREQAADRDJEJEQwAAAhA0QwEQAAAAAEREAAAAAAAAREQAAAAAAAAkQgAAAAAAAAMgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $params = [
            IcalInterface::FMTTYPE  => 'image/vnd.microsoft.icon',
            IcalInterface::ENCODING => IcalInterface::BASE64,
            IcalInterface::VALUE    => IcalInterface::BINARY,
        ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2083,
            [
                IcalInterface::ATTACH => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::ATTACH . ParameterFactory::createParams( $params ) . ':' . $value
        ];


        // IMAGE
        $value  = 'CID:jsmith.part3.960817T083000.xyzMail@example.com';
        $params = [ IcalInterface::VALUE => IcalInterface::URI ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2091,
            [
                IcalInterface::IMAGE => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::IMAGE . ParameterFactory::createParams( $params ) . ':' . $value
        ];

        // IMAGE
        $value  = 'ftp://example.com/pub/reports/r-960812.png';
        $params = [
            IcalInterface::VALUE   => IcalInterface::URI,
            IcalInterface::FMTTYPE => 'application/png',
            IcalInterface::DISPLAY => IcalInterface::BADGE . ',' . IcalInterface::THUMBNAIL
        ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2092,
            [
                IcalInterface::IMAGE => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::IMAGE . ParameterFactory::createParams( $params ) . ':' . $value
        ];

        // IMAGE
        $value  = 'AAABAAEAEBAQAAEABAAoAQAAFgAAACgAAAAQAAAAIAAAAAEABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAAAAAgIAAAICAgADAwMAA////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMwAAAAAAABNEMQAAAAAAAkQgAAAAAAJEREQgAAACECQ0QgEgAAQxQzM0E0AABERCRCREQAADRDJEJEQwAAAhA0QwEQAAAAAEREAAAAAAAAREQAAAAAAAAkQgAAAAAAAAMgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
        $params = [
                IcalInterface::VALUE    => IcalInterface::BINARY,
                IcalInterface::FMTTYPE  => 'image/vnd.microsoft.icon',
                IcalInterface::ENCODING => IcalInterface::BASE64,
                IcalInterface::DISPLAY  => IcalInterface::BADGE . ',' . IcalInterface::THUMBNAIL
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2093,
            [
                IcalInterface::IMAGE => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::IMAGE . ParameterFactory::createParams( $params ) . ':' . $value
        ];


        // REQUEST_STATUS
        $value  = [
            IcalInterface::STATCODE => '3.70',
            IcalInterface::STATDESC => 'Invalid calendar user',
            IcalInterface::EXTDATA  => 'ATTENDEE:mailto:jsmith@example.com'
        ];
        $params = [ IcalInterface::LANGUAGE => 'EN' ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
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
            ParameterFactory::createParams( $params, [ IcalInterface::LANGUAGE ] ) .
            ':' .
            number_format(
                (float) $value[IcalInterface::STATCODE],
                2,
                Util::$DOT,
                null
            ) . ';' .
            StringFactory::strrep( $value[IcalInterface::STATDESC] ) . ';' .
            StringFactory::strrep( $value[IcalInterface::EXTDATA] )
        ];


        // CONFERENCE
        $value  = 'rtsp://audio.example.com/';
        $params = [
                IcalInterface::VALUE   => IcalInterface::URI,
                IcalInterface::FEATURE => IcalInterface::AUDIO
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
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
            IcalInterface::CONFERENCE . ParameterFactory::createParams( $params ) . ':' . $value
        ];

        // CONFERENCE
        $value  = 'https://video-chat.example.com/;group-id=1234';
        $params = [
                IcalInterface::VALUE    => IcalInterface::URI,
                IcalInterface::FEATURE  => IcalInterface::AUDIO . ',' . IcalInterface::VIDEO,
                IcalInterface::LANGUAGE => 'EN',
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
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
            IcalInterface::CONFERENCE . ParameterFactory::createParams( $params ) . ':' . $value
        ];

        // CONFERENCE
        $value  = 'https://video-chat.example.com/;group-id=1234';
        $params = [
                IcalInterface::VALUE   => IcalInterface::URI,
                IcalInterface::FEATURE => IcalInterface::VIDEO,
                IcalInterface::LABEL   => "Web video chat, access code=76543"
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
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
            IcalInterface::CONFERENCE . ParameterFactory::createParams( $params ) . ':' . $value
        ];

        // NAME
        $value  = 'A calendar name';
        $params = [
                IcalInterface::ALTREP   => 'This is an alternative representation',
                IcalInterface::LANGUAGE => 'EN'
            ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2401,
            [
                IcalInterface::NAME => []
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::NAME .
            ParameterFactory::createParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        // STRUCTURED-DATA - text
        $value   = 'This is a STRUCTURED-DATA 2501';
        $params  = self::$STCPAR;
        $params2 = [ IcalInterface::VALUE => IcalInterface::TEXT ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params2
        ];
        $dataArr[] = [
            2501,
            [
                IcalInterface::STRUCTURED_DATA => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::STRUCTURED_DATA . ParameterFactory::createParams( $params2 ) . ':' . $value
        ];

        // STRUCTURED-DATA - uri
        $value   = 'https://structured.data.org/structured_data2502';
        $params  = [ IcalInterface::VALUE => IcalInterface::URI ] + self::$STCPAR;
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params
        ];
        $dataArr[] = [
            2502,
            [
                IcalInterface::STRUCTURED_DATA => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::STRUCTURED_DATA . ParameterFactory::createParams( $params ) . ':' . $value
        ];

        // STRUCTURED-DATA - binary
        $value  = 'This is a BASE64 encoded data 2503==';
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
        $getValue  = [
            Util::$LCvalue  => $value,
            Util::$LCparams => $params2
        ];
        $dataArr[] = [
            2503,
            [
                IcalInterface::STRUCTURED_DATA => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::STRUCTURED_DATA . ParameterFactory::createParams( $params2 ) . ':' . $value
        ];

        // STYLED-DESCRIPTION - uri
        $value  = 'http://example.org/desc2602.html';
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
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params2
            ],
            IcalInterface::STYLED_DESCRIPTION .
                ParameterFactory::createParams( $params2, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] )
                . ':' . $value
        ];

        // STYLED-DESCRIPTION - text
        $value  = 'This is a longer styled 2603 description property with a number of meaningless words';
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
//                  IcalInterface::PARTICIPANT,   // todo
                ]
            ],
            $value,
            $params,
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            IcalInterface::STYLED_DESCRIPTION .
                ParameterFactory::createParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
                ':' . $value
        ];

        // DESCRIPTION may appear more than once in VJOURNAL
        $value  = 'Meeting to provide technical review for \'Phoenix\' design.\nHappy Face Conference Room. Phoenix design team MUST attend this meeting.\nRSVP to team leader.';
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
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            IcalInterface::DESCRIPTION .
            ParameterFactory::createParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        // LOCATION may appear more than once in PARTICIPANT
        $value  = 'Conference Room - F123, Bldg. 002';
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
            [
                Util::$LCvalue  => $value,
                Util::$LCparams => $params
            ],
            IcalInterface::LOCATION .
            ParameterFactory::createParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        return $dataArr;
    }

    /**
     * Testing value TEXT (MULTI) properties
     *
     * @test
     * @dataProvider textMulti2Provider
     *
     * @param int     $case
     * @param mixed[] $propComps
     * @param mixed   $value
     * @param mixed   $params
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function textMultiTest2(
        int    $case,
        array  $propComps,
        mixed  $value,
        mixed  $params,
        array  $expectedGet,
        string $expectedString
    ) : void
    {
        $c = new Vcalendar();

        foreach( array_keys( $propComps ) as $propName ) {
            if( in_array( $propName, [
                IcalInterface::CATEGORIES,
                IcalInterface::DESCRIPTION,
                IcalInterface::IMAGE,
                IcalInterface::LOCATION,
                IcalInterface::NAME
            ], true ) ) {
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
                $newMethod = 'new' . $theComp;
                $comp = match ( true ) {
                    IcalInterface::AVAILABLE === $theComp   => $c->newVavailability()->{$newMethod}(),
                    IcalInterface::PARTICIPANT === $theComp => $c->newVevent()->{$newMethod}(),
                    default                                 => $c->{$newMethod}(),
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
            sprintf( self::$ERRFMT, null, $case . '-25', __FUNCTION__, 'Vcalendar', 'createComponent' )
        );

        $c2 = new Vcalendar();
        $c2->parse( $calendar1 );
        $this->assertEquals(
            $calendar1,
            $c2->createCalendar(),
            sprintf( self::$ERRFMT, null, $case . '-26', __FUNCTION__, 'Vcalendar', 'parse, create and compare' )
        );

        if( IcalInterface::DESCRIPTION === $propName ) {
            $c->setName( $value, $params );
            $c->setName( $value, $params );
        }
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
     * @param mixed[] $expectedGet
     * @param string  $expectedString
     */
    private function propNameTest(
        string                        $case,
        Vcalendar | CalendarComponent $instance,
        string                        $propName,
        mixed                         $value,
        mixed                         $params,
        array                         $expectedGet,
        string                        $expectedString
    ) : void
    {
        $getMethod    = StringFactory::getGetMethodName( $propName );
        if( ! method_exists( $instance, $getMethod )) {
            return;
        }
        $createMethod = StringFactory::getCreateMethodName( $propName );
        $deleteMethod = StringFactory::getDeleteMethodName( $propName );
        $setMethod    = StringFactory::getSetMethodName( $propName );

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

        if( IcalInterface::REQUEST_STATUS === $propName ) {
            $instance->{$setMethod}(
                $value[IcalInterface::STATCODE],
                $value[IcalInterface::STATDESC],
                $value[IcalInterface::EXTDATA],
                $params
            );
            $instance->{$setMethod}(
                $value[IcalInterface::STATCODE],
                $value[IcalInterface::STATDESC],
                $value[IcalInterface::EXTDATA],
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

        if( IcalInterface::REQUEST_STATUS === $propName ) {
            $instance->{$setMethod}(
                $value[IcalInterface::STATCODE],
                $value[IcalInterface::STATDESC],
                $value[IcalInterface::EXTDATA],
                $params
            );
            $instance->{$setMethod}(
                $value[IcalInterface::STATCODE],
                $value[IcalInterface::STATDESC],
                $value[IcalInterface::EXTDATA],
                $params
            );
        }
        else {
            $instance->{$setMethod}( $value, $params );
            $instance->{$setMethod}( $value, $params );
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
        for( $x = 1; $x <= 5; ++$x ) {
            $calendar->setDescription( 'Description ' . $x );
            $calendar->setXprop( 'x-' . $x, $x );
        }

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
            '#1a multi Vcalendar::getDescripton() counts is NOT 5'
        );
        $this->assertSame(
            $cnt1,
            $cnt2,
            '#1b double multi Vcalendar::getDescripton() session counts do not match'
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
        for( $x = 1; $x <= 5; ++$x ) {
            $event->setComment( 'Comment ' . $x );
            $event->setXprop( 'x-' . $x, $x );
        }

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
            '#3a multi Vevent::getComment() counts is NOT 5'
        );
        $this->assertSame(
            $cnt1,
            $cnt2,
            '#3b double multi Vevent::getComment() session counts do not match'
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
        for( $x = 1; $x <= 5; ++$x ) {
            $alarm->setAttach( 'https://test' . $x . 'info/doloribus-fuga-optio-enim-doloremque-consectetur.html');
            $alarm->setXprop( 'x-' . $x, $x );
        }
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
            '#5a multi Valarm::getAttach() counts is NOT 5'
        );
        $this->assertSame(
            $cnt1,
            $cnt2,
            '#5b double multi Valarm::getAttach() session counts do not match'
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
