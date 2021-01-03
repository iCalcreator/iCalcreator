<?php
/**
  * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.30
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

namespace Kigkonsult\Icalcreator\Util;

use DateInterval;
use DateTime;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Vcalendar;
use SimpleXMLElement;

use function array_change_key_case;
use function html_entity_decode;
use function htmlspecialchars;
use function implode;
use function in_array;
use function is_null;
use function is_array;
use function number_format;
use function sprintf;
use function str_replace;
use function strcasecmp;
use function stripos;
use function strlen;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;
use function ucfirst;

/**
 * iCalcreator XML (rfc6321) support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.29.30 - 2020-12-09
 */
class IcalXMLFactory
{
    private static $Vcalendar      = 'vcalendar';
    private static $calProps       = [
        Vcalendar::VERSION,
        Vcalendar::PRODID,
        Vcalendar::CALSCALE,
        Vcalendar::METHOD,
    ];
    private static $calPropsrfc7986Single = [
        Vcalendar::UID,
        Vcalendar::LAST_MODIFIED,
        Vcalendar::URL,
        Vcalendar::REFRESH_INTERVAL,
        Vcalendar::SOURCE,
        Vcalendar::COLOR
    ];
    private static $calPropsrfc7986Multi = [
        Vcalendar::NAME,
        Vcalendar::DESCRIPTION,
        Vcalendar::CATEGORIES,
        Vcalendar::IMAGE
    ];
    private static $properties     = 'properties';
    private static $PARAMETERS     = 'parameters';
    private static $components     = 'components';

    private static $text           = 'text';
    private static $binary         = 'binary';
    private static $uri            = 'uri';
    private static $date           = 'date';
    private static $date_time      = 'date-time';
    private static $period         = 'period';
    private static $rstatus        = 'rstatus';
    private static $unknown        = 'unknown';
    private static $recur          = 'recur';
    private static $cal_address    = 'cal-address';
    private static $integer        = 'integer';
    private static $utc_offset     = 'utc-offset';
    private static $code           = 'code';
    private static $description    = 'description';
    private static $data           = 'data';
    private static $time           = 'time';

    private static $altrep         = 'altrep';
    private static $dir            = 'dir';
    private static $delegated_from = 'delegated-from';
    private static $delegated_to   = 'delegated-to';
    private static $member         = 'member';
    private static $sent_by        = 'sent-by';
    private static $rsvp           = 'rsvp';
    private static $bysecond       = 'bysecond';
    private static $byminute       = 'byminute';
    private static $byhour         = 'byhour';
    private static $bymonthday     = 'bymonthday';
    private static $byyearday      = 'byyearday';
    private static $byweekno       = 'byweekno';
    private static $bymonth        = 'bymonth';
    private static $bysetpos       = 'bysetpos';
    private static $byday          = 'byday';
    private static $freq           = 'freq';
    private static $count          = 'count';
    private static $interval       = 'interval';
    private static $wkst           = 'wkst';

    public static $XMLstart =
        '<?xml version="1.0" encoding="utf-8"?><icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0"><!-- kigkonsult.se %s, iCal2XMl (rfc6321), %s --></icalendar>';

    /**
     * Return iCal XML (rfc6321) output, using PHP SimpleXMLElement
     *
     * @param Vcalendar $calendar iCalcreator Vcalendar instance reference
     * @return string
     * @static
     * @since  2.29.6 - 2019-07-03
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public static function iCal2XML( Vcalendar $calendar )
    {
        static $YMDTHISZ = 'Ymd\THis\Z';
        /** fix an SimpleXMLElement instance and create root element */
        $xml       = new SimpleXMLElement(
            sprintf( self::$XMLstart, ICALCREATOR_VERSION, gmdate( $YMDTHISZ ))
        );
        $Vcalendar = $xml->addChild( self::$Vcalendar );
        $langCal   = $calendar->getConfig( Vcalendar::LANGUAGE );
        /** fix calendar properties */
        $properties = $Vcalendar->addChild( self::$properties );
        foreach( self::$calProps as $propName ) {
            $method = Vcalendar::getGetMethodName( $propName );
            if( false !== ( $content = $calendar->{$method}())) {
                self::addXMLchildText($properties, $propName, $content );
            }
        }
        foreach( self::$calPropsrfc7986Single as $propName ) {
            $method = Vcalendar::getGetMethodName( $propName );
            switch( strtoupper( $propName )) {
                case Vcalendar::UID :   // fall through
                case Vcalendar::COLOR :
                    if( false !== ( $content = $calendar->{$method}( true ))) {
                        self::addXMLchildText(
                            $properties,
                            $propName,
                            $content[Util::$LCvalue],
                            $content[Util::$LCparams]
                        );
                    }
                    break;
                case Vcalendar::LAST_MODIFIED :
                    if( false !== ( $content = $calendar->{$method}( true ))) {
                        unset( $content[Util::$LCparams][Vcalendar::VALUE] );
                        self::addXMLchildDateTime(
                            $properties,
                            $propName,
                            $content[Util::$LCvalue],
                            $content[Util::$LCparams]
                        );
                    }
                    break;
                case Vcalendar::SOURCE : // fall through
                case Vcalendar::URL :
                    if( false !== ( $content = $calendar->{$method}( true ))) {
                        self::addXMLchildUri(
                            $properties,
                            $propName,
                            $content[Util::$LCvalue],
                            $content[Util::$LCparams]
                        );
                    }
                    break;
                case Vcalendar::REFRESH_INTERVAL :
                    if( false !== ( $content = $calendar->{$method}( true ))) {
                        self::addXMLchildDuration(
                            $properties,
                            $propName,
                            $content[Util::$LCvalue],
                            $content[Util::$LCparams]
                        );
                    }
                    break;
            } // end switch
        } // end foreach
        foreach( self::$calPropsrfc7986Multi as $propName ) {
            $method = Vcalendar::getGetMethodName( $propName );
            switch( strtoupper( $propName )) {
                case Vcalendar::NAME :        // fall through
                case Vcalendar::CATEGORIES :  // fall through
                case Vcalendar::DESCRIPTION :
                    while( false !== ( $content = $calendar->{$method}( null, true ))) {
                        if( ! isset( $content[Util::$LCparams][Vcalendar::LANGUAGE] ) &&
                            $langCal ) {
                            $content[Util::$LCparams][Vcalendar::LANGUAGE] = $langCal;
                        }
                        self::addXMLchildText(
                            $properties,
                            $propName,
                            $content[Util::$LCvalue],
                            $content[Util::$LCparams]
                        );
                    } // end while
                    break;
                case Vcalendar::IMAGE :
                    while( false !== ( $content = $calendar->{$method}( null, true ))) {
                        self::addXMLchildBinaryUri( $properties, $propName, $content );
                    }
                    break;
            } // end switch
        } // end foreach
        while( false !== ( $content = $calendar->getXprop( null, null, true ))) {
            self::addXMLchild(
                $properties,
                $content[0],
                self::$unknown,
                $content[1][Util::$LCvalue],
                $content[1][Util::$LCparams]
            );
        } // end while
        /** prepare to fix components with properties */
        $components = $Vcalendar->addChild( self::$components );
        /** fix component properties */
        while( false !== ( $component = $calendar->getComponent())) {
            $compName   = $component->getCompType();
            $child      = $components->addChild( strtolower( $compName ));
            $properties = $child->addChild( self::$properties );
            $langComp   = $component->getConfig( Vcalendar::LANGUAGE );
            $props      = $component->getConfig( Vcalendar::SETPROPERTYNAMES );
            foreach( $props as $pix => $propName ) {
                switch( strtoupper( $propName )) {
                    case Vcalendar::ATTACH :          // may occur multiple times
                    case Vcalendar::IMAGE :
                        $method = Vcalendar::getGetMethodName( $propName );
                        while( false !== ( $content = $component->{$method }( null, true ))) {
                            self::addXMLchildBinaryUri(
                                $properties,
                                $propName,
                                $content
                            );
                        } // end while
                        break;
                    case Vcalendar::ATTENDEE :
                        while( false !== ( $content = $component->getAttendee( null, true ))) {
                            if( isset( $content[Util::$LCparams][Vcalendar::CN] )) {
                                self::addLanguage(
                                    $content[Util::$LCparams],
                                    $langComp,
                                    $langCal
                                );
                            }
                            self::addXMLchildCalAddress(
                                $properties,
                                $propName,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        } // end while
                        break;
                    case Vcalendar::EXDATE :
                        while( false !== ( $content = $component->getExdate( null, true ))) {
                            $isDateSet =
                                ParameterFactory::isParamsValueSet(
                                    $content,
                                    Vcalendar::DATE
                                );
                            unset( $content[Util::$LCparams][Vcalendar::VALUE] );
                            if( $isDateSet ) {
                                self::addXMLchildDate(
                                    $properties,
                                    $propName,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            else {
                                self::addXMLchildDateTime(
                                    $properties,
                                    $propName,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                        } // end while
                        break;
                    case Vcalendar::FREEBUSY :
                        while( false !==
                            ( $content = $component->getFreebusy( null, true ))
                        ) {
                            if( is_array( $content ) &&
                                isset( $content[Util::$LCvalue][Vcalendar::FBTYPE] )) {
                                $content[Util::$LCparams][Vcalendar::FBTYPE] =
                                    $content[Util::$LCvalue][Vcalendar::FBTYPE];
                                unset( $content[Util::$LCvalue][Vcalendar::FBTYPE] );
                            }
                            self::addXMLchild(
                                $properties,
                                $propName,
                                self::$period,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        } // end while
                        break;
                    case Vcalendar::REQUEST_STATUS :
                        while( false !==
                            ( $content = $component->getRequeststatus( null, true ))
                        ) {
                            self::addLanguage(
                                $content[Util::$LCparams],
                                $langComp,
                                $langCal
                            );
                            self::addXMLchild(
                                $properties,
                                $propName,
                                self::$rstatus,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        } // end while
                        break;
                    case Vcalendar::RDATE :
                        while( false !==
                            ( $content = $component->getRdate( null, true ))
                        ) {
                            $type = self::$date_time;
                            if( isset( $content[Util::$LCparams][Vcalendar::VALUE] )) {
                                if( ParameterFactory::isParamsValueSet(
                                    $content,
                                    Vcalendar::DATE
                                )) {
                                    $type = self::$date;
                                }
                                elseif( ParameterFactory::isParamsValueSet(
                                    $content,
                                    Vcalendar::PERIOD
                                )) {
                                    $type = self::$period;
                                }
                            } // end if
                            unset( $content[Util::$LCparams][Vcalendar::VALUE] );
                            self::addXMLchild(
                                $properties,
                                $propName,
                                $type,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        } // end while
                        break;
                    case Vcalendar::DESCRIPTION :
                        $method = Vcalendar::getGetMethodName( $propName );
                        while( false !==
                            ( $content = $component->{$method}( null, true ))
                        ) {
                            self::addXMLchildText(
                                $properties,
                                $propName,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                            if( Vcalendar::VJOURNAL != $compName ) {
                                break;
                            }
                        } // end while
                        break;
                    case Vcalendar::CATEGORIES :  // fall through
                    case Vcalendar::COMMENT :     // fall through
                    case Vcalendar::CONTACT :     // fall through
                    case Vcalendar::RELATED_TO :  // fall through
                    case Vcalendar::RESOURCES :
                        $method = Vcalendar::getGetMethodName( $propName );
                        while( false !==
                            ( $content = $component->{$method}( null, true )
                            )) {
                            if(( Vcalendar::RELATED_TO != $propName )) {
                                self::addLanguage(
                                    $content[Util::$LCparams],
                                    $langComp,
                                    $langCal
                                );
                            } // end if
                            self::addXMLchildText(
                                $properties,
                                $propName,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        } // end while
                        break;
                    case Vcalendar::CREATED :         // single occurrence below, if set
                    case Vcalendar::COMPLETED :       // fall through
                    case Vcalendar::DTSTAMP :         // fall through
                    case Vcalendar::LAST_MODIFIED :   // fall through
                    case Vcalendar::DTSTART :         // fall through
                    case Vcalendar::DTEND :           // fall through
                    case Vcalendar::DUE :             // fall through
                    case Vcalendar::RECURRENCE_ID :   // fall through
                        $method = Vcalendar::getGetMethodName( $propName );
                        if( false !== ( $content = $component->{$method}( true ))) {
                            $isDateSet =
                                ParameterFactory::isParamsValueSet(
                                    $content,
                                    Vcalendar::DATE
                                );
                            unset( $content[Util::$LCparams][Vcalendar::VALUE] );
                            if( $isDateSet ) {
                                self::addXMLchildDate(
                                    $properties,
                                    $propName,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            else {
                                self::addXMLchildDateTime(
                                    $properties,
                                    $propName,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                        } // end if
                        break;
                    case Vcalendar::DURATION :
                        if( false !== ( $content = $component->getDuration( true ))) {
                            self::addXMLchildDuration(
                                $properties,
                                $propName,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Vcalendar::EXRULE :
                    case Vcalendar::RRULE :
                        $method = Vcalendar::getGetMethodName( $propName );
                        if( false !== ( $content = $component->{$method}( true ))) {
                            self::addXMLchildRecur(
                                $properties,
                                $propName,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Vcalendar::COLOR :    // fall through
                    case Vcalendar::KLASS :    // fall through
                    case Vcalendar::LOCATION : // fall through
                    case Vcalendar::STATUS :   // fall through
                    case Vcalendar::SUMMARY :  // fall through
                    case Vcalendar::TRANSP :   // fall through
                    case Vcalendar::TZID :     // fall through
                    case Vcalendar::UID :
                        $method = Vcalendar::getGetMethodName( $propName );
                        if( false !== ( $content = $component->{$method}( true ))) {
                            if(( Vcalendar::LOCATION == $propName ) ||
                                ( Vcalendar::SUMMARY == $propName ))  {
                                self::addLanguage(
                                    $content[Util::$LCparams],
                                    $langComp,
                                    $langCal
                                );
                            }
                            self::addXMLchildText(
                                $properties,
                                $propName,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Vcalendar::GEO :
                        if( false !== ( $content = $component->getGeo( true ))) {
                            self::addXMLchild(
                                $properties,
                                $propName,
                                strtolower( Vcalendar::GEO ),
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Vcalendar::ORGANIZER :
                        if( false !== ( $content = $component->getOrganizer( true ))) {
                            if( isset( $content[Util::$LCparams][Vcalendar::CN] )) {
                                self::addLanguage(
                                    $content[Util::$LCparams],
                                    $langComp,
                                    $langCal
                                );
                            }
                            self::addXMLchildCalAddress(
                                $properties,
                                $propName,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Vcalendar::PERCENT_COMPLETE : // fall through
                    case Vcalendar::PRIORITY :         // fall through
                    case Vcalendar::SEQUENCE :
                        $method = Vcalendar::getGetMethodName( $propName );
                        if( false !== ( $content = $component->{$method}( true ))) {
                            self::addXMLchildInteger(
                                $properties,
                                $propName,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Vcalendar::CONFERENCE :
                        $method = Vcalendar::getGetMethodName( $propName );
                        while( false !==
                            ( $content = $component->{$method}( null, true )
                            )) {
                            self::addXMLchildUri(
                                $properties,
                                $propName,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        } // end while
                        break;
                    case Vcalendar::TZURL :       // fall through
                    case Vcalendar::URL :
                        $method = Vcalendar::getGetMethodName( $propName );
                        if( false !== ( $content = $component->{$method}( true ))) {
                            self::addXMLchildUri(
                                $properties,
                                $propName,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    default :
                        if( ! StringFactory::isXprefixed( $propName )) {
                            break;
                        }
                        if( false !==
                            ( $content = $component->getXprop( $propName, null, true )
                            )) {
                            self::addXMLchild(
                                $properties,
                                $content[0],
                                self::$unknown,
                                $content[1][Util::$LCvalue],
                                $content[1][Util::$LCparams]
                            );
                        }
                        break;
                } // end switch( $propName )
            } // end foreach( $props as $pix => $propName )
            /** fix subComponent properties, if any */
            while( false !== ( $subcomp = $component->getComponent())) {
                $subCompName  = $subcomp->getCompType();
                $child2       = $child->addChild( strtolower( $subCompName ));
                $properties   = $child2->addChild( self::$properties );
                $langComp     = $subcomp->getConfig( Vcalendar::LANGUAGE );
                $subCompProps = $subcomp->getConfig( Vcalendar::SETPROPERTYNAMES );
                foreach( $subCompProps as $pix2 => $propName ) {
                    switch( strtoupper( $propName )) {
                        case Vcalendar::ATTACH :          // may occur multiple times, below
                            while( false !==
                                ( $content = $subcomp->getAttach( null, true )
                                )) {
                                self::addXMLchildBinaryUri(
                                    $properties,
                                    $propName,
                                    $content
                                );
                            } // end while
                            break;
                        case Vcalendar::ATTENDEE :
                            while( false !==
                                ( $content = $subcomp->getAttendee( null, true )
                                )) {
                                if( isset( $content[Util::$LCparams][Vcalendar::CN] )) {
                                    self::addLanguage(
                                        $content[Util::$LCparams],
                                        $langComp,
                                        $langCal
                                    );
                                }
                                self::addXMLchildCalAddress(
                                    $properties,
                                    $propName,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            } // end while
                            break;
                        case Vcalendar::COMMENT : // fall through
                        case Vcalendar::TZNAME :
                            $method = Vcalendar::getGetMethodName( $propName );
                            while( false !==
                                ( $content = $subcomp->{$method}( null, true )
                                )) {
                                self::addLanguage(
                                    $content[Util::$LCparams],
                                    $langComp,
                                    $langCal
                                );
                                self::addXMLchildText(
                                    $properties,
                                    $propName,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            } // end while
                            break;
                        case Vcalendar::RDATE :
                            while( false !==
                                ( $content = $subcomp->getRdate( null, true )
                                )) {
                                $type = self::$date_time;
                                if( isset( $content[Util::$LCparams][Vcalendar::VALUE] )) {
                                    if( ParameterFactory::isParamsValueSet(
                                        $content,
                                        Vcalendar::DATE
                                    )) {
                                        $type = self::$date;
                                    }
                                    elseif( ParameterFactory::isParamsValueSet(
                                        $content,
                                        Vcalendar::PERIOD
                                    )) {
                                        $type = self::$period;
                                    }
                                } // end if
                                unset( $content[Util::$LCparams][Vcalendar::VALUE] );
                                self::addXMLchild(
                                    $properties,
                                    $propName,
                                    $type,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            } // end while
                            break;
                        case Vcalendar::ACTION :      // single occurrence below, if set
                        case Vcalendar::DESCRIPTION : // fall through
                        case Vcalendar::SUMMARY :
                            $method = Vcalendar::getGetMethodName( $propName );
                            if( false !== ( $content = $subcomp->{$method}( true ))) {
                                if(( Vcalendar::ACTION != $propName ) ) {
                                    self::addLanguage(
                                        $content[Util::$LCparams],
                                        $langComp,
                                        $langCal
                                    );
                                }
                                self::addXMLchildText(
                                    $properties,
                                    $propName,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        case Vcalendar::DTSTART :
                            if( false !== ( $content = $subcomp->getDtstart( true ))) {
                                unset( $content[Util::$LCparams][Vcalendar::VALUE] );
                                self::addXMLchildDateTime(
                                    $properties,
                                    $propName,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        case Vcalendar::DURATION :
                            if( false !== ( $content = $subcomp->getDuration( true ))) {
                                self::addXMLchildDuration(
                                    $properties,
                                    $propName,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        case Vcalendar::REPEAT :
                            if( false !== ( $content = $subcomp->getRepeat( true ))) {
                                self::addXMLchildInteger(
                                    $properties,
                                    $propName,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        case Vcalendar::TRIGGER :
                            if( false !== ( $content = $subcomp->getTrigger( true ))) {
                                if( DateIntervalFactory::isDateIntervalArrayInvertSet(
                                    $content[Util::$LCvalue]
                                )) {
                                    $content[Util::$LCvalue] =
                                        DateIntervalFactory::DateIntervalArr2DateInterval(
                                            $content[Util::$LCvalue]
                                        );
                                }
                                if( $content[Util::$LCvalue] instanceof DateInterval ) {
                                    self::addXMLchildDuration(
                                        $properties,
                                        $propName,
                                        $content[Util::$LCvalue],
                                        $content[Util::$LCparams]
                                    );
                                }
                                else {
                                    self::addXMLchildDateTime(
                                        $properties,
                                        $propName,
                                        $content[Util::$LCvalue],
                                        $content[Util::$LCparams]
                                    );
                                }
                            } // end if
                            break;
                        case Vcalendar::TZOFFSETFROM : // fall through
                        case Vcalendar::TZOFFSETTO :
                            $method = Vcalendar::getGetMethodName( $propName );
                            if( false !== ( $content = $subcomp->{$method}( true ))) {
                                self::addXMLchild(
                                    $properties,
                                    $propName,
                                    self::$utc_offset,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        case Vcalendar::RRULE :
                            // rfc5545 restriction: .. SHOULD NOT occur more than once
                            if( false !== ( $content = $subcomp->getRrule( true ))) {
                                self::addXMLchildRecur(
                                    $properties,
                                    $propName,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        default :
                            if( ! StringFactory::isXprefixed( $propName )) {
                                break;
                            }
                            if( false !==
                                ( $content = $subcomp->getXprop( $propName, null, true )
                                )) {
                                self::addXMLchild(
                                    $properties,
                                    $content[0],
                                    self::$unknown,
                                    $content[1][Util::$LCvalue],
                                    $content[1][Util::$LCparams]
                                );
                            }
                            break;
                    } // switch( $propName )
                } // end foreach( $subCompProps as $pix2 => $propName )
            } // end while( false !== ( $subcomp = $component->getComponent()))
        } // end while( false !== ( $component = $calendar->getComponent()))
        return $xml->asXML();
    }

    /**
     * Add parameter language if not set
     *
     * @param array $params
     * @param string $langComp
     * @param string $langCal
     */
    private static function addLanguage( & $params, $langComp, $langCal )
    {
        switch( true ) {
            case isset( $params[Vcalendar::LANGUAGE] ) :
                break;
            case ( ! empty( $langComp )) :
                $params[Vcalendar::LANGUAGE] = $langComp;
                break;
            case ( ! empty( $langCal )) :
                $params[Vcalendar::LANGUAGE] = $langCal;
                break;
        } // end switch
    }

    /**
     * Add XML (rfc6321) binary/uri children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param array            $content
     * @throws Exception
     * @throws InvalidArgumentException
     * @static
     * @since  2.29.2 - 2019-06-29
     */
    private static function addXMLchildBinaryUri(
        SimpleXMLElement $parent,
        $name,
        $content
    ) {
        $type = ( ParameterFactory::isParamsValueSet( $content, Vcalendar::BINARY ))
            ? self::$binary
            : self::$uri;
        unset( $content[Util::$LCparams][Vcalendar::VALUE] );
        self::addXMLchild(
            $parent,
            $name,
            $type,
            $content[Util::$LCvalue],
            $content[Util::$LCparams]
        );
    }

    /**
     * Add XML (rfc6321) cal-address children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param string|array|DateTime|DateInterval  $content new subelement content
     * @param array            $params  new element 'attributes'
     * @throws Exception
     * @throws InvalidArgumentException
     * @static
     * @since  2.29.2 - 2019-06-29
     */
    private static function addXMLchildCalAddress(
        SimpleXMLElement $parent,
        $name,
        $content,
        $params = []
    ) {
        self::addXMLchild($parent, $name, self::$cal_address, $content, $params );
    }
    /**
     * Add XML (rfc6321) date children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param string|array|DateTime|DateInterval  $content new subelement content
     * @param array            $params  new element 'attributes'
     * @throws Exception
     * @throws InvalidArgumentException
     * @static
     * @since  2.29.2 - 2019-06-29
     */
    private static function addXMLchildDate(
        SimpleXMLElement $parent,
        $name,
        $content,
        $params = []
    ) {
        self::addXMLchild($parent, $name, self::$date, $content, $params );
    }

    /**
     * Add XML (rfc6321) date-time children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param string|array|DateTime|DateInterval  $content new subelement content
     * @param array            $params  new element 'attributes'
     * @throws Exception
     * @throws InvalidArgumentException
     * @static
     * @since  2.29.2 - 2019-06-29
     */
    private static function addXMLchildDateTime(
        SimpleXMLElement $parent,
        $name,
        $content,
        $params = []
    ) {
        self::addXMLchild($parent, $name, self::$date_time, $content, $params );
    }

    /**
     * Add XML (rfc6321) duration children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param string|array|DateTime|DateInterval  $content new subelement content
     * @param array            $params  new element 'attributes'
     * @throws Exception
     * @throws InvalidArgumentException
     * @static
     * @since  2.29.2 - 2019-06-29
     */
    private static function addXMLchildDuration(
        SimpleXMLElement $parent,
        $name,
        $content,
        $params = []
    ) {
        self::addXMLchild(
            $parent,
            $name,
            strtolower( Vcalendar::DURATION ),
            $content,
            $params
        );
    }

    /**
     * Add XML (rfc6321) integer children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param string|array|DateTime|DateInterval  $content new subelement content
     * @param array            $params  new element 'attributes'
     * @throws Exception
     * @throws InvalidArgumentException
     * @static
     * @since  2.29.2 - 2019-06-29
     */
    private static function addXMLchildInteger(
        SimpleXMLElement $parent,
        $name,
        $content,
        $params = []
    ) {
        self::addXMLchild($parent, $name, self::$integer, $content, $params );
    }

    /**
     * Add XML (rfc6321) recur children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param string|array|DateTime|DateInterval  $content new subelement content
     * @param array            $params  new element 'attributes'
     * @throws Exception
     * @throws InvalidArgumentException
     * @static
     * @since  2.29.2 - 2019-06-29
     */
    private static function addXMLchildRecur(
        SimpleXMLElement $parent,
        $name,
        $content,
        $params = []
    ) {
        self::addXMLchild($parent, $name, self::$recur, $content, $params );
    }

    /**
     * Add XML (rfc6321) text children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param string|array|DateTime|DateInterval  $content new subelement content
     * @param array            $params  new element 'attributes'
     * @throws Exception
     * @throws InvalidArgumentException
     * @static
     * @since  2.29.2 - 2019-06-29
     */
    private static function addXMLchildText(
        SimpleXMLElement $parent,
        $name,
        $content,
        $params = []
    ) {
        self::addXMLchild($parent, $name, self::$text, $content, $params );
    }

    /**
     * Add XML (rfc6321) uri children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param string|array|DateTime|DateInterval  $content new subelement content
     * @param array            $params  new element 'attributes'
     * @throws Exception
     * @throws InvalidArgumentException
     * @static
     * @since  2.29.2 - 2019-06-29
     */
    private static function addXMLchildUri(
        SimpleXMLElement $parent,
        $name,
        $content,
        $params = []
    ) {
        self::addXMLchild( $parent, $name, self::$uri, $content, $params );
    }

    /**
     * Add XML (rfc6321) children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param string           $type    content type, subelement(-s) name
     * @param string|array|DateTime|DateInterval  $content new subelement content
     * @param array            $params  new element 'attributes'
     * @throws Exception
     * @throws InvalidArgumentException
     * @static
     * @since  2.29.30 - 2020-12-09
     */
    private static function addXMLchild(
        SimpleXMLElement $parent,
        $name,
        $type,
        $content,
        $params = []
    ) {
        static $BOOLEAN      = 'boolean';
        static $UNTIL        = 'until';
        static $START        = 'start';
        static $END          = 'end';
        static $SP0          = '';
        /** create new child node */
        $name  = strtolower( $name );
        $child = $parent->addChild( $name );
        if(( empty( $content ) && ( Util::$ZERO != $content )) ||
            ( is_string( $content) &&
                ( Util::$MINUS != substr( $content, 0, 1 )) &&
                ( 0 > $content ))
        ) { // ??
            $v = $child->addChild( $type );
            return;
        }
        $recurDateIsSet = false;
        switch( true ) {
            case empty( $params ) :
                break;
            case (( 1 == count( $params )) && isset( $params[Util::$ISLOCALTIME] )) :
                break;
            case ( self::$recur == $type ) :
                $recurDateIsSet = ParameterFactory::isParamsValueSet(
                    [ Util::$LCparams => $params ],
                    Vcalendar::DATE
                );
                if(( 1 == count( $params )) && isset( $params[Vcalendar::VALUE ] )) {
                    break;
                }
                unset( $params[Vcalendar::VALUE ] );
                // fall through
            default :
                $parameters = $child->addChild( self::$PARAMETERS );
                foreach( $params as $param => $parVal ) {
                    if( Vcalendar::VALUE === $param ) {
                        if( false !== strpos( $parVal, Util::$COLON )) {
                            $p1   = $parameters->addChild( strtolower( $param ));
                            $p2   = $p1->addChild( self::$unknown, htmlspecialchars( $parVal ));
                            $type = strtolower( StringFactory::before( Util::$COLON, $parVal ));
                        }
                        elseif( 0 != strcasecmp( $type, $parVal )) {
                            $type = strtolower( $parVal );
                        }
                        continue;
                    }
                    elseif( Util::$ISLOCALTIME == $param ) {
                        continue;
                    }
                    $param = strtolower( $param );
                    if( StringFactory::isXprefixed( $param )) {
                        $p1 = $parameters->addChild( $param );
                        $p2 = $p1->addChild( self::$unknown, htmlspecialchars( $parVal ));
                        continue;
                    }
                    $p1 = $parameters->addChild( $param );
                    switch( $param ) {
                        case self::$altrep :
                        case self::$dir :
                            $ptype = self::$uri;
                            break;
                        case self::$delegated_from :
                        case self::$delegated_to :
                        case self::$member :
                        case self::$sent_by :
                            $ptype = self::$cal_address;
                            break;
                        case self::$rsvp :
                            $ptype = $BOOLEAN;
                            break;
                        default :
                            $ptype = self::$text;
                            break;
                    } // end switch
                    if( is_array( $parVal )) {
                        foreach( $parVal as $pV ) {
                            $p2 = $p1->addChild( $ptype, htmlspecialchars( $pV ));
                        }
                    }
                    else {
                        $p2 = $p1->addChild( $ptype, htmlspecialchars( $parVal ));
                    }
                } // end foreach
                break;
        } // end switch
        /** store content */
        switch( $type ) {
            case self::$binary :
                $v = $child->addChild( $type, $content );
                break;
            case $BOOLEAN :
                break;
            case self::$cal_address :
                $v = $child->addChild( $type, $content );
                break;
            case self::$date :
                if( $content instanceof DateTime ) {
                    $content = [ $content ];
                }
                foreach( $content as $date ) {
                    $v = $child->addChild(
                        $type,
                        DateTimeFactory::dateTime2Str( $date, true )
                    );
                }
                break;
            case self::$date_time :
                if( $content instanceof DateTime ) {
                    $content = [ $content ];
                }
                $isLocalTime = isset( $params[Util::$ISLOCALTIME] );
                foreach( $content as $dt ) {
                    $v = $child->addChild(
                        $type,
                        DateTimeFactory::dateTime2Str( $dt, false, $isLocalTime )
                    );
                } // end foreach
                break;
            case strtolower( Vcalendar::DURATION ) :
                $v = $child->addChild(
                    $type,
                    DateIntervalFactory::dateInterval2String( $content, true )
                );
                break;
            case strtolower( Vcalendar::GEO ) :
                if( ! empty( $content )) {
                    $v1 = $child->addChild(
                        Vcalendar::LATITUDE,
                        GeoFactory::geo2str2(
                            $content[Vcalendar::LATITUDE],
                            GeoFactory::$geoLatFmt
                        )
                    );
                    $v1 = $child->addChild(
                        Vcalendar::LONGITUDE,
                        GeoFactory::geo2str2(
                            $content[Vcalendar::LONGITUDE],
                            GeoFactory::$geoLongFmt
                        )
                    );
                }
                break;
            case self::$integer :
                $v = $child->addChild( $type, (string) $content );
                break;
            case self::$period :
                if( ! is_array( $content )) {
                    break;
                }
                $isLocalTime = isset( $params[Util::$ISLOCALTIME] );
                foreach( $content as $period ) {
                    $v1  = $child->addChild( $type );
                    $str = DateTimeFactory::dateTime2Str(
                        $period[0],
                        false,
                        $isLocalTime
                    );
                    $v2 = $v1->addChild( $START, $str );
                    if( $period[1] instanceof DateInterval ) {
                        $v2 = $v1->addChild(
                            strtolower( Vcalendar::DURATION ),
                            DateIntervalFactory::dateInterval2String( $period[1] )
                        );
                    }
                    elseif( $period[1] instanceof DateTime ) {
                        $str = DateTimeFactory::dateTime2Str(
                            $period[1],
                            false,
                            $isLocalTime
                        );
                        $v2 = $v1->addChild( $END, $str );
                    }
                } // end foreach
                break;
            case self::$recur :
                $content = array_change_key_case( $content );
                foreach( $content as $ruleLabel => $ruleValue ) {
                    switch( $ruleLabel ) {
                        case $UNTIL :
                            $v = $child->addChild(
                                $ruleLabel,
                                DateTimeFactory::dateTime2Str(
                                    $ruleValue,
                                    $recurDateIsSet
                                )
                            );
                            break;
                        case self::$bysecond :
                        case self::$byminute :
                        case self::$byhour :
                        case self::$bymonthday :
                        case self::$byyearday :
                        case self::$byweekno :
                        case self::$bymonth :
                        case self::$bysetpos :
                            if( is_array( $ruleValue )) {
                                foreach( $ruleValue as $vix => $valuePart ) {
                                    $v = $child->addChild( $ruleLabel, $valuePart );
                                }
                            }
                            else {
                                $v = $child->addChild( $ruleLabel, $ruleValue );
                            }
                            break;
                        case self::$byday :
                            if( isset( $ruleValue[Vcalendar::DAY] )) {
                                $str  = ( isset( $ruleValue[0] )) ? $ruleValue[0] : null;
                                $str .= $ruleValue[Vcalendar::DAY];
                                $p    = $child->addChild( $ruleLabel, $str );
                            }
                            else {
                                foreach( $ruleValue as $valuePart ) {
                                    if( isset( $valuePart[Vcalendar::DAY] )) {
                                        $str  = ( isset( $valuePart[0] ))
                                            ? $valuePart[0]
                                            : null;
                                        $str .= $valuePart[Vcalendar::DAY];
                                        $p    = $child->addChild( $ruleLabel, $str );
                                    }
                                    else {
                                        $p = $child->addChild( $ruleLabel, $valuePart );
                                    }
                                } // end foreach
                            }
                            break;
                        case self::$freq :
                        case self::$count :
                        case self::$interval :
                        case self::$wkst :
                        default:
                            $p = $child->addChild( $ruleLabel, $ruleValue );
                            break;
                    } // end switch( $ruleLabel )
                } // end foreach( $content as $ruleLabel => $ruleValue )
                break;
            case self::$rstatus :
                $v = $child->addChild(
                    self::$code,
                    number_format(
                        (float) $content[Vcalendar::STATCODE],
                        2,
                        Util::$DOT,
                        $SP0
                    )
                );
                $v = $child->addChild(
                    self::$description,
                    htmlspecialchars( $content[Vcalendar::STATDESC] )
                );
                if( isset( $content[Vcalendar::EXTDATA] )) {
                    $v = $child->addChild(
                        self::$data,
                        htmlspecialchars( $content[Vcalendar::EXTDATA] )
                    );
                }
                break;
            case self::$text :
                if( ! is_array( $content )) {
                    $content = [ $content ];
                }
                foreach( $content as $part ) {
                    $v = $child->addChild( $type, htmlspecialchars( $part ));
                }
                break;
            case self::$time :
                break;
            case self::$uri :
                $v = $child->addChild( $type, $content );
                break;
            case self::$utc_offset :
                if( DateIntervalFactory::hasPlusMinusPrefix( $content )) {
                    $str     = substr( $content, 0, 1 );
                    $content = substr( $content, 1 );
                }
                else {
                    $str = Util::$PLUS;
                }
                $str .= substr( $content, 0, 2 ) .
                    Util::$COLON . substr( $content, 2, 2 );
                if( 4 < strlen( $content )) {
                    $str .= Util::$COLON . substr( $content, 4 );
                }
                $v = $child->addChild( $type, $str );
                break;
            case self::$unknown :
            default:
                if( is_array( $content )) {
                    $content = implode( $content );
                }
                $v = $child->addChild( self::$unknown, htmlspecialchars( $content ));
                break;
        } // end switch
    }

    /**
     * Parse (rfc6321) XML string into iCalcreator instance
     *
     * @param  string $xmlStr
     * @param  array  $iCalcfg iCalcreator config array (opt)
     * @return mixed  iCalcreator instance or false on error
     * @static
     * @since  2.20.23 - 2017-02-25
     */
    public static function XML2iCal( $xmlStr, $iCalcfg = [] )
    {
        static $CRLF = [ "\r\n", "\n\r", "\n", "\r" ];
        $xmlStr = str_replace( $CRLF, null, $xmlStr );
        $xml    = self::XMLgetTagContent1( $xmlStr, self::$Vcalendar, $endIx );
        $iCal   = new Vcalendar( $iCalcfg );
        if( false === self::XMLgetComps( $iCal, $xmlStr ))
            return false;
        return $iCal;
    }

    /**
     * Parse (rfc6321) XML string into iCalcreator components
     *
     * @param IcalInterface $iCal
     * @param string    $xml
     * @return mixed Vcalendar|bool
     * @static
     * @since  2.27.14 - 2019-03-09
     */
    private static function XMLgetComps( IcalInterface $iCal, $xml )
    {
        static $PROPSTAGempty = '<properties/>';
        static $PROPSTAGstart = '<properties>';
        static $COMPSTAGempty = '<components/>';
        static $COMPSTAGstart = '<components>';
        static $NEW      = 'new';
        static $ALLCOMPS = [
            Vcalendar::VTIMEZONE,
            Vcalendar::STANDARD,
            Vcalendar::DAYLIGHT,
            Vcalendar::VEVENT,
            Vcalendar::VTODO,
            Vcalendar::VJOURNAL,
            Vcalendar::VFREEBUSY,
            Vcalendar::VALARM
        ];
        $len = strlen( $xml );
        $sx  = 0;
        while(
            ((( $sx + 12 ) < $len ) &&
                ! StringFactory::startsWith( substr( $xml, $sx ), $PROPSTAGstart ) &&
                ! StringFactory::startsWith( substr( $xml, $sx ), $COMPSTAGstart )
                ) &&
            ((( $sx + 13 ) < $len ) &&
                ! StringFactory::startsWith( substr( $xml, $sx ), $PROPSTAGempty ) &&
                ! StringFactory::startsWith( substr( $xml, $sx ), $COMPSTAGempty ))) {
            $sx += 1;
        } // end while
        if(( $sx + 11 ) >= $len ) {
            return false;
        }
        if( StringFactory::startsWith( $xml, $PROPSTAGempty, $pos )) {
            $xml = substr( $xml, $pos );
        }
        elseif( StringFactory::startsWith( substr( $xml, $sx ), $PROPSTAGstart )) {
            $xml2 = self::XMLgetTagContent1( $xml, self::$properties, $endIx );
            self::XMLgetProps( $iCal, $xml2 );
            $xml = substr( $xml, $endIx );
        }
        if( StringFactory::startsWith( $xml, $COMPSTAGempty, $pos )) {
            $xml = substr( $xml, $pos );
        }
        elseif( StringFactory::startsWith( $xml, $COMPSTAGstart )) {
            $xml = self::XMLgetTagContent1( $xml, self::$components, $endIx );
        }
        while( ! empty( $xml )) {
            $xml2 = self::XMLgetTagContent2( $xml, $tagName, $endIx );
            $newCompMethod = $NEW . ucfirst( strtolower( $tagName ));
            if( Util::isCompInList( $tagName, $ALLCOMPS )) {
                $iCalComp = $iCal->{$newCompMethod}();
                self::XMLgetComps( $iCalComp, $xml2 );
            }
            $xml = substr( $xml, $endIx );
        } // end while( ! empty( $xml ))
        return $iCal;
    }

    /**
     * Parse (rfc6321) XML into iCalcreator properties
     *
     * @param  IcalInterface $iCalComp iCalcreator calendar/component instance
     * @param  string        $xml
     * @static
     * @since  2.29.30 - 2020-12-09
     */
    private static function XMLgetProps( IcalInterface $iCalComp, $xml )
    {
        static $VERSIONPRODID   = null;
        static $PARAMENDTAG     = '<parameters/>';
        static $PARAMTAG        = '<parameters>';
        static $DATETAGST       = '<date';
        static $PERIODTAG       = '<period>';
        static $ATTENDEEPARKEYS    = [
            Vcalendar::DELEGATED_FROM,
            Vcalendar::DELEGATED_TO,
            Vcalendar::MEMBER
        ];
        if( is_null( $VERSIONPRODID )) {
            $VERSIONPRODID = [
                Vcalendar::VERSION,
                Vcalendar::PRODID,
            ];
        }
        while( ! empty( $xml )) {
            $xml2     = self::XMLgetTagContent2( $xml, $propName, $endIx );
            $propName = strtoupper( $propName );
            if( empty( $xml2 ) && ( Util::$ZERO != $xml2 )) {
                if( StringFactory::isXprefixed( $propName )) {
                    $iCalComp->setXprop( $propName );
                }
                else {
                    $method = Vcalendar::getSetMethodName( $propName );
                    $iCalComp->{$method}();
                }
                $xml = substr( $xml, $endIx );
                continue;
            }
            $params = [];
            if( StringFactory::startsWith( $xml2, $PARAMENDTAG, $pos )) {
                $xml2 = substr( $xml2, 13 );
            }
            elseif( StringFactory::startsWith( $xml2, $PARAMTAG )) {
                $xml3 = self::XMLgetTagContent1( $xml2, self::$PARAMETERS, $endIx2 );
                $endIx3 = 0;
                while( ! empty( $xml3 )) {
                    $xml4     = self::XMLgetTagContent2( $xml3, $paramKey, $endIx3 );
                    $pType    = false; // skip parameter valueType
                    $paramKey = strtoupper( $paramKey );
                    if( in_array( $paramKey, $ATTENDEEPARKEYS )) {
                        while( ! empty( $xml4 )) {
                            $paramValue = self::XMLgetTagContent1(
                                $xml4,
                                self::$cal_address,
                                $endIx4
                            );
                            if( ! isset( $params[$paramKey] )) {
                                $params[$paramKey] = [ $paramValue ];
                            }
                            else {
                                $params[$paramKey][] = $paramValue;
                            }
                            $xml4 = substr( $xml4, $endIx4 );
                        } // end while
                    } // end if( in_array( $paramKey, Util::$ATTENDEEPARKEYS ))
                    else {
                        $paramValue = html_entity_decode(
                            self::XMLgetTagContent2(
                                $xml4,
                                $pType,
                                $endIx4
                            )
                        );
                        if( ! isset( $params[$paramKey] )) {
                            $params[$paramKey] = $paramValue;
                        }
                        else {
                            $params[$paramKey] .= Util::$COMMA . $paramValue;
                        }
                    }
                    $xml3 = substr( $xml3, $endIx3 );
                } // end while
                $xml2 = substr( $xml2, $endIx2 );
            } // end elseif - parameters
            $valueType = false;
            $value     = ( ! empty( $xml2 ) || ( Util::$ZERO == $xml2 ))
                ? self::XMLgetTagContent2( $xml2, $valueType, $endIx3 )
                : null;
            switch( $propName ) {
                case Vcalendar::URL : // fall through
                case Vcalendar::TZURL :
                    break;
                case Vcalendar::EXDATE :   // multiple single-date(-times) may exist
                // fall through
                case Vcalendar::RDATE :
                    if( self::$period != $valueType ) {
                        if( self::$date == $valueType ) {
                            $params[Vcalendar::VALUE] = Vcalendar::DATE;
                        }
                        $t = [];
                        while( ! empty( $xml2 ) &&
                            ( StringFactory::startsWith( $xml2, $DATETAGST ))) {
                            $t[]  = self::XMLgetTagContent2( $xml2, $pType, $endIx4);
                            $xml2 = substr( $xml2, $endIx4 );
                        } // end while
                        $value = $t;
                        break;
                    }
                // fall through
                case Vcalendar::FREEBUSY :
                    if( Vcalendar::RDATE == $propName ) {
                        $params[Vcalendar::VALUE] = Vcalendar::PERIOD;
                    }
                    $value = [];
                    while( ! empty( $xml2 ) &&
                        ( StringFactory::startsWith( $xml2, $PERIODTAG ))) {
                        $xml3 = self::XMLgetTagContent1( $xml2, self::$period, $endIx4);
                        $t    = [];
                        while( ! empty( $xml3 )) { // start - end/duration
                            $t[]  = self::XMLgetTagContent2( $xml3, $pType, $endIx5 );
                            $xml3 = substr( $xml3, $endIx5 );
                        } // end while
                        $value[] = $t;
                        $xml2    = substr( $xml2, $endIx4 );
                    } // end while
                    break;
                case Vcalendar::TZOFFSETTO :
                // fall through
                case Vcalendar::TZOFFSETFROM :
                    $value = str_replace( Util::$COLON, null, $value );
                    break;
                case Vcalendar::GEO :
                    $tValue                       = [ Vcalendar::LATITUDE => $value ];
                    $tValue[Vcalendar::LONGITUDE] = self::XMLgetTagContent1(
                        substr( $xml2, $endIx3 ),
                        Vcalendar::LONGITUDE,
                        $endIx3
                    );
                    $value = $tValue;
                    break;
                case Vcalendar::EXRULE :
                // fall through
                case Vcalendar::RRULE :
                    $tValue    = [ $valueType => $value ];
                    $xml2      = substr( $xml2, $endIx3 );
                    $valueType = false;
                    while( ! empty( $xml2 )) {
                        $t = self::XMLgetTagContent2( $xml2, $valueType, $endIx4 );
                        switch( strtoupper( $valueType )) {
                            case Vcalendar::FREQ :
                            case Vcalendar::COUNT :
                            case Vcalendar::UNTIL :
                            case Vcalendar::INTERVAL :
                            case Vcalendar::WKST :
                                $tValue[$valueType] = $t;
                                break;
                            case Vcalendar::BYDAY :
                                if( 2 == strlen( $t )) {
                                    $tValue[$valueType][] = [ Vcalendar::DAY => $t ];
                                }
                                else {
                                    $day = substr( $t, -2 );
                                    $key = substr( $t, 0, ( strlen( $t ) - 2 ));
                                    $tValue[$valueType][] = [ $key, Vcalendar::DAY => $day ];
                                }
                                break;
                            default:
                                $tValue[$valueType][] = $t;
                        } // end switch
                        $xml2 = substr( $xml2, $endIx4 );
                    } // end while
                    $value = $tValue;
                    break;
                case Vcalendar::REQUEST_STATUS :
                    $value = [
                        self::$code        => null,
                        self::$description => null,
                        self::$data        => null
                    ];
                    while( ! empty( $xml2 )) {
                        $t    = html_entity_decode(
                            self::XMLgetTagContent2(
                                $xml2,
                                $valueType,
                                $endIx4 )
                        );
                        $value[$valueType] = $t;
                        $xml2 = substr( $xml2, $endIx4 );
                    } // end while
                    break;
                default:
                    switch( $valueType ) {
                        case self::$uri :
                            if( in_array( $propName, [ Vcalendar::ATTACH, Vcalendar::SOURCE ] )) {
                                break;
                            }
                            $params[Vcalendar::VALUE] = Vcalendar::URI;
                            break;
                        case self::$binary :
                            $params[Vcalendar::VALUE] = Vcalendar::BINARY;
                            break;
                        case self::$date :
                            $params[Vcalendar::VALUE] = Vcalendar::DATE;
                            break;
                        case self::$date_time :
                            $params[Vcalendar::VALUE] = Vcalendar::DATE_TIME;
                            break;
                        case self::$text :
                            // fall through
                        case self::$unknown :
                            $value = html_entity_decode( $value );
                            break;
                        default :
                            if( StringFactory::isXprefixed( $propName ) &&
                                ( self::$unknown != strtolower( $valueType ))) {
                                $params[Vcalendar::VALUE] = strtoupper( $valueType );
                            }
                            break;
                    } // end switch
                    break;
            } // end switch( $propName )
            $method = Vcalendar::getSetMethodName( $propName );
            switch( true ) {
                case ( Util::isPropInList( $propName, $VERSIONPRODID )) :
                    break;
                case ( StringFactory::isXprefixed( $propName )) :
                    $iCalComp->setXprop( $propName, $value, $params );
                    break;
                case ( Vcalendar::FREEBUSY == $propName ) :
                    $fbtype = isset( $params[Vcalendar::FBTYPE] )
                        ? $params[Vcalendar::FBTYPE]
                        : null;
                    unset( $params[Vcalendar::FBTYPE] );
                    $iCalComp->{$method}( $fbtype, $value, $params );
                    break;
                case ( Vcalendar::GEO == $propName ) :
                    $iCalComp->{$method}(
                        $value[Vcalendar::LATITUDE],
                        $value[Vcalendar::LONGITUDE],
                        $params
                    );
                    break;
                case ( Vcalendar::REQUEST_STATUS == $propName ) :
                    $iCalComp->{$method}(
                        $value[self::$code],
                        $value[self::$description],
                        $value[self::$data],
                        $params
                    );
                    break;
                default :
                    if( empty( $value )
                        && ( is_array( $value ) || ( Util::$ZERO > $value ))) {
                        $value = null;
                    }
                    $iCalComp->{$method}( $value, $params );
                    break;
            } // end switch
            $xml = substr( $xml, $endIx );
        } // end while( ! empty( $xml ))
    }

    /**
     * Fetch a specific XML tag content
     *
     * @param string $xml
     * @param string $tagName
     * @param int    $endIx
     * @return mixed
     * @static
     * @since  2.23.8 - 2017-04-17
     */
    private static function XMLgetTagContent1( $xml, $tagName, & $endIx = 0 ) {
        static $FMT0 = '<%s>';
        static $FMT1 = '<%s />';
        static $FMT2 = '<%s/>';
        static $FMT3 = '</%s>';
        $tagName = strtolower( $tagName );
        $strLen  = strlen( $tagName );
        $xmlLen  = strlen( $xml );
        $sx1     = 0;
        while( $sx1 < $xmlLen ) {
            if((( $sx1 + $strLen + 1 ) < $xmlLen ) &&
                ( sprintf( $FMT0, $tagName ) ==
                    strtolower( substr( $xml, $sx1, ( $strLen + 2 ))))
            ) {
                break;
            }
            if((( $sx1 + $strLen + 3 ) < $xmlLen ) &&
                ( sprintf( $FMT1, $tagName ) ==
                    strtolower( substr( $xml, $sx1, ( $strLen + 4 ))))
            ) {
                $endIx = $strLen + 5;
                return null; // empty tag
            }
            if((( $sx1 + $strLen + 2 ) < $xmlLen ) &&
                ( sprintf( $FMT2, $tagName ) ==
                    strtolower( substr( $xml, $sx1, ( $strLen + 3 ))))
            ) {
                $endIx = $strLen + 4;
                return null; // empty tag
            }
            $sx1 += 1;
        } // end while...
        if( false === substr( $xml, $sx1, 1 )) {
            $endIx = ( empty( $sx )) ? 0 : $sx - 1; // ??
            return null;
        }
        $endTag = sprintf( $FMT3, $tagName );
        if( false === ( $pos = stripos( $xml, $endTag ))) { // missing end tag??
            $endIx = $xmlLen + 1;
            return null;
        }
        $endIx = $pos + $strLen + 3;
        return substr( $xml, ( $sx1 + $strLen + 2 ), ( $pos - $sx1 - 2 - $strLen ));
    }

    /**
     * Fetch next (unknown) XML tagname AND content
     *
     * @param string $xml
     * @param string $tagName
     * @param int    $endIx
     * @return mixed
     * @static
     * @since  2.23.8 - 2017-04-17
     */
    private static function XMLgetTagContent2( $xml, & $tagName, & $endIx )
    {
        static $LT          = '<';
        static $CMTSTART    = '<!--';
        static $EMPTYTAGEND = '/>';
        static $GT          = '>';
        static $DURATION    = 'duration';
        static $DURATIONTAG = '<duration>';
        static $DURENDTAG   = '</duration>';
        static $FMTTAG      = '</%s>';
        $xmlLen = strlen( $xml );
        $endIx  = $xmlLen + 1; // just in case.. .
        $sx1    = 0;
        while( $sx1 < $xmlLen ) {
            if( $LT == substr( $xml, $sx1, 1 )) {
                if((( $sx1 + 3 ) < $xmlLen ) &&
                    ( StringFactory::startsWith( substr( $xml, $sx1 ), $CMTSTART ))
                ) { // skip comment
                    $sx1 += 1;
                }
                else {
                    break;
                } // tagname start here
            }
            else {
                $sx1 += 1;
            }
        } // end while...
        $sx2 = $sx1;
        while( $sx2 < $xmlLen ) {
            if((( $sx2 + 1 ) < $xmlLen ) &&
                ( StringFactory::startsWith( substr( $xml, $sx2 ), $EMPTYTAGEND ))
            ) { // tag with no content
                $tagName = trim( substr( $xml, ( $sx1 + 1 ), ( $sx2 - $sx1 - 1 )));
                $endIx   = $sx2 + 2;
                return null;
            }
            if( $GT == substr( $xml, $sx2, 1 )) // tagname ends here
            {
                break;
            }
            $sx2 += 1;
        } // end while...
        $tagName = substr( $xml, ( $sx1 + 1 ), ( $sx2 - $sx1 - 1 ));
        $endIx   = $sx2 + 1;
        if( $sx2 >= $xmlLen ) {
            return null;
        }
        $strLen = strlen( $tagName );
        if(( $DURATION == $tagName ) &&
            ( false !== ( $pos1 = stripos( $xml, $DURATIONTAG, $sx1 + 1 ))) &&
            ( false !== ( $pos2 = stripos( $xml, $DURENDTAG,  $pos1 + 1 ))) &&
            ( false !== ( $pos3 = stripos( $xml, $DURENDTAG,  $pos2 + 1 ))) &&
            ( $pos1 < $pos2 ) && ( $pos2 < $pos3 )) {
            $pos = $pos3;
        }
        elseif( false === ( $pos = stripos( $xml, sprintf( $FMTTAG, $tagName ), $sx2 ))) {
            return null;
        }
        $endIx = $pos + $strLen + 3;
        return substr( $xml, ( $sx1 + $strLen + 2 ), ( $pos - $strLen - 2 ));
    }
}

