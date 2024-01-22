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

use Exception;
use Kigkonsult\Icalcreator\Formatter\Property\Property;
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use Kigkonsult\Icalcreator\Util\GeoFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;

/**
 * class Prop1TextSingleTest,
 *
 * testing VALUE TEXT etc
 *   ATTACH, ATTENDEE, CATEGORIES, CLASS, COMMENT, CONTACT, DESCRIPTION, LOCATION, ORGANIZER,
 *   RELATED-TO, REQUEST_STATUS, RESOURCES, STATUS, SUMMARY, TRANSP, URL, X-PROP
 *   COLOR, IMAGE, CONFERENCE
 * testing GeoLocation
 * testing empty properties
 * testing parse eol-htab
 *
 * @since  2.39 - 2021-06-19
 */
class Prop1TextSingleTest extends DtBase
{
    /**
     * @var string[]
     */
    private static array $STCPAR   = [ 'X-PARAM' => 'Y-vALuE' ];

    /**
     * @var array|string[]
     */
    private static array $EOLCHARS = [ "\r\n ", "\r\n\t", PHP_EOL . " ", PHP_EOL . "\t" ];

    /**
     * miscTest1 provider, test values for TEXT (single) properties
     *
     * @return mixed[]
     */
    public static function textSingleTest1Provider() : array
    {
        $dataArr = [];

        // TRANSP
        $value  = IcalInterface::OPAQUE;
        $params = self::$STCPAR;
        $dataArr[] = [
            1011,
            [
                IcalInterface::TRANSP => [ IcalInterface::VEVENT ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            IcalInterface::TRANSP . Property::formatParams( $params ) . ':' . $value
        ];

        // DESCRIPTION
        $value  = 'Meeting to provide technical review for \'Phoenix\' design.\nHappy Face Conference Room. Phoenix design team MUST attend this meeting.\nRSVP to team leader.';
        $params = [
            IcalInterface::ALTREP   => 'This is an alternative representation',
            IcalInterface::LANGUAGE => 'EN'
        ] + self::$STCPAR;
        $dataArr[] = [
            1021,
            [
                IcalInterface::DESCRIPTION => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::AVAILABLE,
                    IcalInterface::VAVAILABILITY,
                    IcalInterface::VRESOURCE
                ]
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

        // LOCATION
        $value  = 'Conference Room - F123, Bldg. 002';
        $params = [
            IcalInterface::ALTREP   => 'This is an alternative representation',
            IcalInterface::LANGUAGE => 'EN'
        ] + self::$STCPAR;
        $dataArr[] = [
            1031,
            [
                IcalInterface::LOCATION => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
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
            IcalInterface::LOCATION .
            Property::formatParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        // SUMMARY
        $value  = 'Department Party';
        $params = [
            IcalInterface::ALTREP   => 'This is an alternative representation',
            IcalInterface::LANGUAGE => 'EN'
        ] + self::$STCPAR;
        $dataArr[] = [
            1041,
            [
                IcalInterface::SUMMARY => [
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
            IcalInterface::SUMMARY .
            Property::formatParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        $value = '⚽ Major League Soccer on ESPN+';
        $params = [
                IcalInterface::ALTREP   => 'This is an alternative representation',
                IcalInterface::LANGUAGE => 'EN'
            ] + self::$STCPAR;
        $dataArr[] = [ // testing utf8 char
            1042,
            [
                IcalInterface::SUMMARY => [
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
            IcalInterface::SUMMARY .
            Property::formatParams( $params, [ IcalInterface::ALTREP, IcalInterface::LANGUAGE ] ) .
            ':' . $value
        ];

        // SOURCE  - more in urlMessageTest6
        $value  = 'http://example.com/pub/calendars/jsmith/mytime.ics';
        $params = []  + self::$STCPAR;
        $dataArr[] = [
            1051,
            [
                IcalInterface::SOURCE => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL, IcalInterface::VFREEBUSY ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            IcalInterface::SOURCE . Property::formatParams( $params ) . ':' . $value
        ];

        $value1 = 'https://www.poirier-au-loup.fr/Cafe-tricot?id_evenement=980
   ';
        $value2 = 'https://www.poirier-au-loup.fr/Cafe-tricot?id_evenement=980';
        $params = []  + self::$STCPAR;
        $dataArr[] = [
            1053,
            [
                IcalInterface::SOURCE => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL, IcalInterface::VFREEBUSY ]
            ],
            $value1,
            $params,
            Pc::factory(
                $value2,
                $params
            ),
            IcalInterface::SOURCE . Property::formatParams( $params ) . ':' . $value2
        ];

        // URL - more in urlMessageTest6
        $value1  = 'https://www.masked.de/account/subscription/delivery/8878/%3Fweek=2021-W03';
        $value2  = 'https://www.masked.de/account/subscription/delivery/8878/%3Fweek=2021-W03';
        $params1 = [  IcalInterface::VALUE => IcalInterface::URI ]  + self::$STCPAR;
        $params2 = self::$STCPAR;
        $dataArr[] = [
            1062,
            [
                IcalInterface::URL => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                    IcalInterface::VFREEBUSY,
                    IcalInterface::VAVAILABILITY
                ]
            ],
            $value1,
            $params1,
            Pc::factory(
                $value2,
                $params2
            ),
            IcalInterface::URL . Property::formatParams( $params2 ) . ':' . $value2
        ];

        $value1  = 'https://www.poirier-au-loup.fr/Cafe-tricot?id_evenement=980
   ';
        $value2  = 'https://www.poirier-au-loup.fr/Cafe-tricot?id_evenement=980';
        $params1 = [  IcalInterface::VALUE => IcalInterface::URI ]  + self::$STCPAR;
        $params2 = self::$STCPAR;
        $dataArr[] = [
            1063,
            [
                IcalInterface::URL => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                    IcalInterface::VFREEBUSY,
                    IcalInterface::VAVAILABILITY
                ]
            ],
            $value1,
            $params1,
            Pc::factory(
                $value2,
                $params2
            ),
            IcalInterface::URL . Property::formatParams( $params2 ) . ':' . $value2
        ];

        // ORGANIZER
        $value  = 'mailto:ildoit1071@example.com';
        $params = [
                IcalInterface::CN             => 'John Doe',
                IcalInterface::DIR            => 'ldap://example.com:6666/o=ABC%20Industries,c=US???(cn=Jim%20Dolittle)',
                IcalInterface::SENT_BY        => 'mailto:boss1071@example.com',
                IcalInterface::LANGUAGE       => 'EN'
            ] + self::$STCPAR;
        $getValue  = Pc::factory(
            CalAddressFactory::conformCalAddress( $value ),
            $params
        );
        $dataArr[] = [
            1071,
            [
                IcalInterface::ORGANIZER => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params + [ IcalInterface::EMAIL => 'ildoit1071@example.com' ], // removed, same as value
            $getValue,
            IcalInterface::ORGANIZER .
            Property::formatParams(
                $params,
                [
                    IcalInterface::CN,
                    IcalInterface::DIR,
                    IcalInterface::SENT_BY,
                    IcalInterface::LANGUAGE
                ]
            ) .
            ':' . CalAddressFactory::conformCalAddress( $value )
        ];

        $value  = 'ildoit1072@example.com';
        $params = [
                strtolower( IcalInterface::CN )           => 'Jane Doe',
                strtolower( IcalInterface::SENT_BY )      => 'boss1072@example.com',
                strtolower( IcalInterface::EMAIL )        => 'MAILTO:another1072@example.com'
            ] + self::$STCPAR;
        $params2 = [
                IcalInterface::CN            => 'Jane Doe',
                IcalInterface::SENT_BY       => 'mailto:boss1072@example.com',
                IcalInterface::EMAIL         => 'another1072@example.com'
            ] + self::$STCPAR;
        $getValue  = Pc::factory(
            CalAddressFactory::conformCalAddress( $value, true ),
            $params2
        );
        $getValue->params[IcalInterface::EMAIL] = 'another1072@example.com';
        $dataArr[] = [
            1072,
            [
                IcalInterface::ORGANIZER => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::ORGANIZER .
            Property::formatParams(
                $params2,
                [
                    IcalInterface::CN,
                    IcalInterface::DIR,
                    IcalInterface::SENT_BY,
                    IcalInterface::LANGUAGE
                ]
            ) .
            ':' . 'mailto:' . $value
        ];

        // issue 112 : 2.41.80
        $value  = 'http://messes.info/communaute/av/84/jonquieres';
        $params = [
                IcalInterface::CN => 'Paroisse : église de Jonquières (Saint Mapalice)'
            ] + self::$STCPAR;
        $getValue  = Pc::factory(
            CalAddressFactory::conformCalAddress( $value ),
            $params
        );
        $dataArr[] = [
            1073,
            [
                IcalInterface::ORGANIZER => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::ORGANIZER .
            Property::formatParams(
                $params,
                [
                    IcalInterface::CN,
                    IcalInterface::DIR,
                    IcalInterface::SENT_BY,
                    IcalInterface::LANGUAGE
                ]
            ) .
            ':' . $value
        ];


        // CLASS
        $value  = IcalInterface::CONFIDENTIAL;
        $params = self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            1081,
            [
                IcalInterface::KLASS => [
                    IcalInterface::VEVENT,
                    IcalInterface::VTODO,
                    IcalInterface::VJOURNAL,
                    IcalInterface::VAVAILABILITY
                ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::KLASS . Property::formatParams( $params ) . ':' . $value
        ];

        // STATUS
        $value  = IcalInterface::TENTATIVE;
        $params = self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            1091,
            [
                IcalInterface::STATUS => [ IcalInterface::VEVENT ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::STATUS . Property::formatParams( $params ) . ':' . $value
        ];

        // STATUS
        $value  = IcalInterface::NEEDS_ACTION;
        $params = self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            1092,
            [
                IcalInterface::STATUS => [ IcalInterface::VTODO ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::STATUS . Property::formatParams( $params ) . ':' . $value
        ];

        // STATUS
        $value  = IcalInterface::F_NAL;
        $params = self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            1093,
            [
                IcalInterface::STATUS => [ IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::STATUS . Property::formatParams( $params ) . ':' . $value
        ];

        // GEO
        $value  = [ IcalInterface::LATITUDE => 10.10, IcalInterface::LONGITUDE => 10.10 ];
        $params = self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            1101,
            [
                IcalInterface::GEO => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VRESOURCE ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::GEO . Property::formatParams( $params ) .
            ':' .
            GeoFactory::geo2str2( $value[IcalInterface::LATITUDE], GeoFactory::$geoLatFmt ) .
            StringFactory::$SEMIC .
            GeoFactory::geo2str2( $value[IcalInterface::LONGITUDE], GeoFactory::$geoLongFmt )

        ];

        // COLOR
        $value  = 'black';
        $params = self::$STCPAR;
        $getValue  = Pc::factory(
            $value,
            $params
        );
        $dataArr[] = [
            1103,
            [
                IcalInterface::COLOR => [ IcalInterface::VEVENT, IcalInterface::VTODO, IcalInterface::VJOURNAL ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::COLOR . Property::formatParams( $params ) . ':' . $value
        ];

        // CALENDAR-ADDRESS
        $value  = 'MAILTO:ildoit1071@example.com';
        $params = self::$STCPAR;
        $getValue  = Pc::factory(
            CalAddressFactory::conformCalAddress( $value ),
            $params
        );
        $dataArr[] = [
            1201,
            [
                IcalInterface::CALENDAR_ADDRESS => [ IcalInterface::PARTICIPANT ]
            ],
            $value,
            $params,
            $getValue,
            IcalInterface::CALENDAR_ADDRESS .
            Property::formatParams( $params ) . ':' . CalAddressFactory::conformCalAddress( $value )
        ];

        // LOCATION-TYPE
        $value  = 'This is a typ of location';
        $params = self::$STCPAR;
        $dataArr[] = [
            1301,
            [
                IcalInterface::LOCATION_TYPE => [ IcalInterface::VLOCATION ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            IcalInterface::LOCATION_TYPE . Property::formatParams( $params ) . ':' . $value
        ];

        // BUSYTYPE
        $value  = IcalInterface::BUSY_UNAVAILABLE;
        $params = self::$STCPAR;
        $dataArr[] = [
            1401,
            [
                IcalInterface::BUSYTYPE => [ IcalInterface::VAVAILABILITY ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            IcalInterface::BUSYTYPE . Property::formatParams( $params ) . ':' . $value
        ];

        // RESOURCE_TYPE
        $value  = IcalInterface::BUSY_UNAVAILABLE;
        $params = self::$STCPAR;
        $dataArr[] = [
            1501,
            [
                IcalInterface::RESOURCE_TYPE => [ IcalInterface::VRESOURCE ]
            ],
            $value,
            $params,
            Pc::factory(
                $value,
                $params
            ),
            IcalInterface::RESOURCE_TYPE . Property::formatParams( $params ) . ':' . $value
        ];

        return $dataArr;
    }

    /**
     * Testing value TEXT (single) properties
     *
     * @test
     * @dataProvider textSingleTest1Provider
     * @param int     $case
     * @param mixed[] $propComps
     * @param mixed   $value
     * @param mixed   $params
     * @param Pc      $expectedGet
     * @param string  $expectedString
     * @throws Exception
     */
    public function textSingleTest1(
        int    $case,
        array  $propComps,
        mixed  $value,
        mixed  $params,
        Pc     $expectedGet,
        string $expectedString
    ) : void
    {
        $c        = new Vcalendar();
        $urlIsSet = $pcInput = false;
        foreach( $propComps as $propName => $theComps ) {
            if( IcalInterface::SOURCE === $propName ) {
                $c->setSource( $value, $params );
                $c->setUrl( $value, $params );
                continue;
            }
            foreach( $theComps as $theComp ) {
                if( IcalInterface::COLOR === $propName ) {
                    $c->setColor( $value, $params );
                }

                if( ! $urlIsSet && ( IcalInterface::URL === $propName )) {
                    $c->setUrl( $value, $params );
                    $urlIsSet = true;
                }

                $newMethod = 'new' . $theComp;
                switch( true ) {
                    case in_array( $propName, [ IcalInterface::CALENDAR_ADDRESS, IcalInterface::LOCATION_TYPE ], true ) ||
                        ( $theComp === IcalInterface::VRESOURCE ) :
                        $vevent = $c->newVevent();
                        $comp  = $vevent->{$newMethod}();
                        break;
                    case ( $theComp === IcalInterface::AVAILABLE ) :
                        $comp = $c->newVavailability()->{$newMethod}();
                        break;
                    default :
                        $comp = $c->{$newMethod}();
                        break;
                }

                [ $createMethod, $deleteMethod, $getMethod, $isMethod, $setMethod ] = self::getPropMethodnames( $propName );
                $this->assertFalse(
                    $comp->{$isMethod}(),
                    self::getErrMsg(  null, $case . '-1', __FUNCTION__, $theComp, $isMethod )
                );

                if( IcalInterface::GEO === $propName ) {
                    $comp->{$setMethod}( $value[IcalInterface::LATITUDE], $value[IcalInterface::LONGITUDE], $params );
                }
                else {
                    if( $pcInput ) {
                        $comp->{$setMethod}( Pc::factory( $value, $params ));
                    }
                    else {
                        $comp->{$setMethod}( $value, $params );
                    }
                    $pcInput = ! $pcInput;
                }
                $this->assertTrue(
                    $comp->{$isMethod}(),
                    self::getErrMsg(  null, $case . '-2', __FUNCTION__, $theComp, $isMethod ) .
                        PHP_EOL . ' value: ' . $value
                );
                if( IcalInterface::LOCATION_TYPE === $propName ) {  // passive by-pass test
                    $vevent->newParticipant()->{$newMethod}()->{$setMethod}( $value, $params );
                }

                $getValue = $comp->{$getMethod}( true );
                $this->assertEquals(
                    $expectedGet,
                    $getValue,
                    self::getErrMsg(  null, $case . '-3', __FUNCTION__, $theComp, $getMethod ) .
                        ', got : ' . var_export( $getValue, true ) . ', exp : ' . var_export( $expectedGet, true )
                );

                $createString   = str_replace( self::$EOLCHARS , null, $comp->{$createMethod}());
                $createString   = str_replace( '\,', ',', $createString );
                $this->assertEquals(
                    $expectedString,
                    trim( $createString ),
                    self::getErrMsg(  null, $case . '-4', __FUNCTION__, $theComp, $createMethod )
                );

                $comp->{$deleteMethod}();
                $this->assertFalse(
                    $comp->{$isMethod}(),
                    self::getErrMsg(  '(is-prop-set) ', $case . '-5', __FUNCTION__, $theComp, $getMethod )
                );
                $this->assertFalse(
                    $comp->{$getMethod}(),
                    self::getErrMsg(  '(after delete) ', $case . '-6', __FUNCTION__, $theComp, $getMethod )
                );

                if( IcalInterface::GEO === $propName ) {
                    $comp->{$setMethod}( $value[IcalInterface::LATITUDE], $value[IcalInterface::LONGITUDE], $params );
                }
                else {
                    $comp->{$setMethod}( $value, $params );
                }
                $this->assertTrue(
                    $comp->{$isMethod}(),
                    self::getErrMsg(  '(is-prop-set) ', $case . '-7', __FUNCTION__, $theComp, $getMethod )
                );
            } // end foreach
        } // end foreach

        $this->parseCalendarTest( $case, $c, $expectedString );
    }

    /**
     * Test Vevent/Vtodo GEO (+ geoLocation)
     *
     * @test
     */
    public function geoTest4() : void
    {
        $compProps = [
            IcalInterface::VEVENT,
            IcalInterface::VTODO,
        ];
        $calendar  = new Vcalendar();
        $location  = 'Conference Room - F123, Bldg. 002';
        $latitude  = 12.34;
        $longitude = 56.5678;

        foreach( $compProps as $theComp  ) {
            $newMethod1 = 'new' . $theComp;
            $comp = $calendar->{$newMethod1}();

            $getValue = $comp->getGeoLocation();
            $this->assertEmpty(
                $getValue,
                self::getErrMsg(  null, 1, __FUNCTION__, $theComp, 'getGeoLocation' )
            );

            $comp->setLocation( $location )
                ->setGeo(
                    $latitude,
                    $longitude
                );

            $this->assertSame(
                'GEO:+' . $latitude . ';+' . $longitude,
                trim( $comp->createGeo())
            );

            $getValue = explode( '/', $comp->getGeoLocation());
            $this->assertEquals(
                $location,
                $getValue[0],
                self::getErrMsg(  null, 2, __FUNCTION__, $theComp, 'getGeoLocation' )
            );
            $tLat = substr( StringFactory::beforeLast('+', $getValue[1] ), 1 );
            $this->assertEquals(
                $latitude,
                $tLat,
                self::getErrMsg(  null, 3, __FUNCTION__, $theComp, 'getGeoLocation' )
            );
            $tLong = substr( str_replace( $tLat, null, $getValue[1] ), 1 );
            $this->assertEquals(
                $longitude,
                $tLong,
                self::getErrMsg(  null, 4, __FUNCTION__, $theComp, 'getGeoLocation' )
            );

            $comp->setgeo( 1.1, 2.2 );
            $this->assertSame(
                'GEO:+01.1;+2.2',
                trim( $comp->createGeo())
            );
            $comp->setGeo( 0.0, 0.0 );
            $this->assertSame(
                'GEO:00;0',
                trim( $comp->createGeo())
            );
            $comp->setGeo( -0.0, -0.0 );
            $this->assertSame(
                'GEO:00;0',
                trim( $comp->createGeo())
            );
        } // end foreach
    }

    /**
     * Testing parse and set url 'decode' (SOURCE, TZURL ) + URL VALUE= URI:message... AND trailing eol (+space etc)
     *
     * @test
     * @since 2.41.81 2023-08-14
     */
    public function urlMessageTest6() : void
    {

        $URLs = [
            'https://1111@eu-west-1.amazonses.com' => 'https://1111@eu-west-1.amazonses.com',
            '%3C2222@eu-west-2.amazonses.com%3E'   => '2222@eu-west-2.amazonses.com',
            '%3C3333%40eu-west-3.amazonses.com%3E' => '3333@eu-west-3.amazonses.com',
            '<4444@eu-west-4.amazonses.com>'       => '4444@eu-west-4.amazonses.com',
            'https://www.poirier-au-loup.fr/Cafe-tricot?id_evenement=980
'                                                  => 'https://www.poirier-au-loup.fr/Cafe-tricot?id_evenement=980'
        ];

        $PROPstart = 'SOURCE:';
        $x = 10;
        foreach( $URLs as $theUrl => $expUrl) {
            $calendar  = new Vcalendar();
            $calendar->parse( $PROPstart . $theUrl );
            $this->parseCalendarTest( ++$x, $calendar, $expUrl );
        }

        $x = 20;
        foreach( $URLs as $theUrl => $expUrl ) {
            $calendar  = new Vcalendar();
            $calendar->setSource( $theUrl );
            $this->parseCalendarTest( ++$x, $calendar, $expUrl );
        }

        $PROPstart = 'TZURL:';
        $x = 30;
        foreach( $URLs as $theUrl => $expUrl ) {
            $calendar  = new Vcalendar();
            $calendar->newVtimezone()
                ->parse( $PROPstart . $theUrl );
            $this->parseCalendarTest( ++$x, $calendar, $expUrl );
        }

        $x = 40;
        foreach( $URLs as $theUrl => $expUrl ) {
            $calendar  = new Vcalendar();
            $calendar->newVtimezone()->setTzurl( $theUrl );
            $this->parseCalendarTest( ++$x, $calendar, $expUrl );
        }

        $PROPstart = 'URL';
        $PARAMs1 = [
            ':',
            ';VALUE=URI:message:',
            ';VALUE=\'URI:message\':',
            ';VALUE="URI:message":',
        ];

        $x = 60;
        foreach( $URLs as $theUrl => $expUrl) {
            foreach( $PARAMs1 as $y => $theParam ) {
                $calendar  = new Vcalendar();
                $calendar->newVevent()
                    ->parse( $PROPstart . $theParam . $theUrl );
                $this->parseCalendarTest( ( ++$x . '-' . $y ), $calendar, $expUrl );
            } // end foreach
        } // end foreach

        $x       = 60;
        $PARAMs2 = [
            [],
            self::$STCPAR,
            [ Vcalendar::VALUE => 'URI:message' ],
            self::$STCPAR + [ Vcalendar::VALUE => 'URI:message' ],
            [ Vcalendar::VALUE => '\'URI:message\'' ],
            self::$STCPAR + [ Vcalendar::VALUE => '\'URI:message\'' ],
            [ Vcalendar::VALUE => '"URI:message"' ],
            self::$STCPAR + [ Vcalendar::VALUE => '"URI:message"' ],
        ];
        $theUrl = reset( $URLs );
        foreach( $PARAMs2 as $theParam ) {
            $calendar  = new Vcalendar();
            $calendar->newVevent()
                ->setUrl( $theUrl, $theParam );
            unset( $theParam[Vcalendar::VALUE] );
            $this->parseCalendarTest(
                ( ++$x . '-' . $y ),
                $calendar,
                'URL' . Property::formatParams( $theParam ) . ':' . $theUrl
            );
        } // end foreach
    }
}
