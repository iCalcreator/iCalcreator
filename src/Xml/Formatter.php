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
declare( strict_types = 1 );
namespace Kigkonsult\Icalcreator\Xml;

use DateInterval;
use DateTime;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\CalendarComponent;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\DateIntervalFactory;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\GeoFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Vcalendar;
use Kigkonsult\Icalcreator\Vevent;
use SimpleXMLElement;

use function array_change_key_case;
use function htmlspecialchars;
use function implode;
use function is_array;
use function number_format;
use function sprintf;
use function strcasecmp;
use function strlen;
use function strstr;
use function strtolower;
use function strtoupper;
use function substr;

/**
 * iCalcreator XML (rfc6321) formatter class
 *
 * @since 2.41.69 2022-10-21
 */
final class Formatter extends XmlBase
{
    /**
     * @var string[]
     */
    private static array $calProps = [
        IcalInterface::VERSION,
        IcalInterface::PRODID,
        IcalInterface::CALSCALE,
        IcalInterface::METHOD,
    ];

    /**
     * @var string[]
     */
    private static array $calPropsrfc7986Single = [
        IcalInterface::UID,
        IcalInterface::LAST_MODIFIED,
        IcalInterface::URL,
        IcalInterface::REFRESH_INTERVAL,
        IcalInterface::SOURCE,
        IcalInterface::COLOR
    ];

    /**
     * @var string[]
     */
    private static array $calPropsrfc7986Multi = [
        IcalInterface::NAME,
        IcalInterface::DESCRIPTION,
        IcalInterface::CATEGORIES,
        IcalInterface::IMAGE
    ];

    /**
     * @var string
     */
    private static string $integer        = 'integer';

    /**
     * @var string
     */
    private static string $rstatus        = 'rstatus';

    /**
     * @var string
     */
    private static string $time           = 'time';

    /**
     * @var string
     */
    private static string $utc_offset     = 'utc-offset';


    /**
     * @var string
     */
    private static string $bysecond       = 'bysecond';

    /**
     * @var string
     */
    private static string $byminute       = 'byminute';

    /**
     * @var string
     */
    private static string $byhour         = 'byhour';

    /**
     * @var string
     */
    private static string $bymonthday = 'bymonthday';

    /**
     * @var string
     */
    private static string $byyearday  = 'byyearday';

    /**
     * @var string
     */
    private static string $byweekno   = 'byweekno';

    /**
     * @var string
     */
    private static string $bymonth    = 'bymonth';

    /**
     * @var string
     */
    private static string $bysetpos   = 'bysetpos';

    /**
     * @var string
     */
    private static string $byday      = 'byday';

    /**
     * @var string
     */
    private static string $freq       = 'freq';

    /**
     * @var string
     */
    private static string $count      = 'count';

    /**
     * @var string
     */
    private static string $interval   = 'interval';

    /**
     * @var string
     */
    private static string $wkst       = 'wkst';

    /**
     * @var string
     */
    public static string $XMLstart =
        '<?xml version="1.0" encoding="utf-8"?><icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0"><!-- kigkonsult.se %s, iCal2XMl (rfc6321), %s --></icalendar>';

    /**
     * Return iCal XML (rfc6321) string output, false on error
     *
     * @param Vcalendar $calendar iCalcreator Vcalendar instance
     * @return bool|string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.69 2022-10-04
     */
    public static function iCal2XML( Vcalendar $calendar ) : bool | string
    {
        static $YMDTHISZ = 'Ymd\THis\Z';
        // fix an SimpleXMLElement instance and create root element
        $xml       = new SimpleXMLElement(
            sprintf( self::$XMLstart, ICALCREATOR_VERSION, gmdate( $YMDTHISZ ))
        );
        $vCalendarXml = $xml->addChild( self::$Vcalendar );
        $langCal      = $calendar->getConfig( IcalInterface::LANGUAGE );
        // fix calendar properties
        self::calendarProps2Xml( $calendar, $vCalendarXml, $langCal );
        // prepare to fix components with properties/subComponents
        $componentsXml = $vCalendarXml->addChild( self::$components );
        // fix component properties
        foreach( $calendar->getComponents() as $component ) {
            self::componentProps2Xml(
                $component,
                $componentsXml->addChild( strtolower( $component->getCompType())),
                $langCal
            );
        } // end foreach
        return $xml->asXML();
    }

    /**
     * Parse Vcalendar properties into XML
     *
     * @param Vcalendar $calendar iCalcreator Vcalendar instance
     * @param SimpleXMLElement $vCalendarXml
     * @param bool|string $langCal
     * @throws Exception
     * @since 2.41.69 2022-10-07
     */
    private static function calendarProps2Xml(
        Vcalendar $calendar,
        SimpleXMLElement $vCalendarXml,
        bool | string $langCal
    ) : void
    {
        $properties = $vCalendarXml->addChild( self::$properties );
        foreach( self::$calProps as $propName ) {
            $method = StringFactory::getGetMethodName( $propName );
            if( false !== ( $content = $calendar->{$method}())) {
                self::addXMLchildText( $properties, $propName, Pc::factory( $content ));
            }
        } // end foreach
        foreach( self::$calPropsrfc7986Single as $propName ) {
            $getMethod = StringFactory::getGetMethodName( $propName );
            if( false === ( $content = $calendar->{$getMethod}( true ))) {
                continue;
            }
            switch( strtoupper( $propName )) {
                case IcalInterface::UID :   // fall through
                case IcalInterface::COLOR :
                    self::addXMLchildText( $properties, $propName, $content );
                    break;
                case IcalInterface::LAST_MODIFIED :
                    unset( $content->params[IcalInterface::VALUE] );
                    self::addXMLchildDateTime( $properties, $propName, $content );
                    break;
                case IcalInterface::SOURCE : // fall through
                case IcalInterface::URL :
                    self::addXMLchildUri( $properties, $propName, $content );
                    break;
                case IcalInterface::REFRESH_INTERVAL :
                    self::addXMLchildDuration( $properties, $propName, $content );
                    break;
            } // end switch
        } // end foreach
        foreach( self::$calPropsrfc7986Multi as $propName ) {
            $method = StringFactory::getGetAllMethodName( $propName );
            switch( strtoupper( $propName )) {
                case IcalInterface::CATEGORIES :  // fall through
                case IcalInterface::DESCRIPTION : // fall through
                case IcalInterface::NAME :
                    foreach( $calendar->{$method}( true ) as $content ) {
                        self::addXMLchildText( $properties, $propName, $content, $langCal );
                    } // end while
                    break;
                case IcalInterface::IMAGE :
                    self::addXMLchildBinaryUriArr( $properties, $propName, $calendar->{$method}( true )); // array
                    break;
            } // end switch
        } // end foreach
        foreach( $calendar->getAllXprop( true ) as $contents ) {
            self::addXMLchild( $properties, $contents[0], self::$unknown, $contents[1] );
        } // end while
    }

    /**
     * Parse component into XML
     *
     * @param CalendarComponent|Vevent $component
     * @param SimpleXMLElement $parentXml
     * @param bool|string $langCal
     * @throws Exception
     * @since 2.41.69 2022-10-04
     */
    private static function componentProps2Xml(
        CalendarComponent|Vevent $component,
        SimpleXMLElement $parentXml,
        bool | string $langCal
    ) : void
    {
        $compName   = $component->getCompType();
        $properties = $parentXml->addChild( self::$properties );
        $langComp   = $component->getConfig( IcalInterface::LANGUAGE );
        $props      = $component->getConfig( IcalInterface::SETPROPERTYNAMES );
        foreach( $props as $propName ) {
            $method = Vcalendar::isMultiProp( $propName )
                ? StringFactory::getGetAllMethodName( $propName )
                : StringFactory::getGetMethodName( $propName );
            switch( strtoupper( $propName )) {
                case IcalInterface::ATTACH :          // may occur multiple times
                case IcalInterface::IMAGE :
                    self::addXMLchildBinaryUriArr( $properties, $propName, $component->{$method}( true )); // array
                    break;
                case IcalInterface::ATTENDEE :        // may occur multiple times
                    self::addXMLchildCalAddress(
                        $properties,
                        $propName,
                        $component->{$method}( true ),
                        ( $langComp ?: $langCal )
                    );
                    break;
                case IcalInterface::EXDATE :
                    foreach( $component->{$method}( true ) as $content ) {
                        if( self::hasValueDateSet( $content )) {
                            self::addXMLchildDate( $properties, $propName, $content );
                            continue;
                        }
                        self::addXMLchildDateTime( $properties, $propName, $content );
                    } // end foreach
                    break;
                case IcalInterface::FREEBUSY :
                    foreach( $component->{$method}( true ) as $content ) {
                        self::addXMLchild( $properties, $propName, self::$period, $content );
                    } // end foreach
                    break;
                case IcalInterface::REQUEST_STATUS :
                    foreach( $component->{$method}( true ) as $content ) {
                        self::addLanguage( $content, $langComp, $langCal );
                        self::addXMLchild( $properties, $propName, self::$rstatus, $content );
                    } // end foreach
                    break;
                case IcalInterface::RDATE :
                    foreach( $component->{$method}( true ) as $content ) {
                        $type = self::getRdateValueType( $content );
                        self::addXMLchild( $properties, $propName, $type, $content );
                    } // end foreach
                    break;
                case IcalInterface::DESCRIPTION : // fall through, multiple in VCALENDAR/VJOURNAL, single elsewere
                case IcalInterface::LOCATION :    // multiple in PARTICIPANT, single elsewere
                    foreach( $component->{$method}( true ) as $content ) {
                        self::addXMLchildText( $properties, $propName, $content, ( $langComp ?: $langCal ));
                        if(( IcalInterface::DESCRIPTION === $propName ) &&
                            $component::isDescriptionSingleProp( $compName )) {
                            break;
                        }
                        if(( IcalInterface::LOCATION === $propName ) &&
                            $component::isLocationSingleProp( $compName )) {
                            break;
                        }
                    } // end foreach
                    break;
                case IcalInterface::STYLED_DESCRIPTION :
                    foreach( $component->{$method}( true ) as $content ) {
                        if( $content->hasParamValue( IcalInterface::URI )) {
                            self::addXMLchildUri( $properties, $propName, $content );
                            continue;
                        }
                        self::addXMLchildText( $properties, $propName, $content, ( $langComp ?: $langCal ));
                    } // end foreach
                    break;
                case IcalInterface::CATEGORIES :    // fall through
                case IcalInterface::COMMENT :       // fall through
                case IcalInterface::CONTACT :       // fall through  // single in VFREEBUSY, multiple elsewhere
                case IcalInterface::NAME :          // dito, multi i Vcalendar, single in Vlocation/Vresource (here)
                case IcalInterface::TZID_ALIAS_OF : // fall through
                case IcalInterface::TZNAME :        // fall through
                case IcalInterface::RESOURCES :     // fall through
                case IcalInterface::RELATED_TO :
                    foreach( $component->{$method}( true ) as $content ) {
                        if( $propName !== IcalInterface::RELATED_TO ) {
                            self::addLanguage( $content, $langComp, $langCal );
                        }
                        self::addXMLchildText( $properties, $propName, $content );
                        if(( IcalInterface::NAME === $propName ) ||
                            (( IcalInterface::CONTACT === $propName ) &&
                                $component::isContactSingleProp( $compName ))) {
                            break;
                        }
                    } // end foreach
                    break;
                case IcalInterface::ACKNOWLEDGED :    // fall through
                case IcalInterface::CREATED :         // fall through
                case IcalInterface::COMPLETED :       // fall through
                case IcalInterface::DTSTAMP :         // fall through
                case IcalInterface::LAST_MODIFIED :   // fall through
                case IcalInterface::DTSTART :         // fall through
                case IcalInterface::DTEND :           // fall through
                case IcalInterface::DUE :             // fall through
                case IcalInterface::RECURRENCE_ID :   // fall through
                case IcalInterface::TZUNTIL :         // fall through
                    $content = $component->{$method}( true );
                    if( self::hasValueDateSet( $content )) {
                        self::addXMLchildDate( $properties, $propName, $content );
                        break;
                    }
                    self::addXMLchildDateTime( $properties, $propName, $content );
                    break;
                case IcalInterface::DURATION :
                    self::addXMLchildDuration( $properties, $propName, $component->{$method}( true ));
                    break;
                case IcalInterface::EXRULE :
                case IcalInterface::RRULE :
                    self::addXMLchildRecur( $properties, $propName, $component->{$method}( true ));
                    break;
                case IcalInterface::LOCATION_TYPE : // fall through
                case IcalInterface::SUMMARY :   // fall through
                case IcalInterface::ACTION :    // fall through
                case IcalInterface::BUSYTYPE :  // dito
                case IcalInterface::KLASS :     // fall through
                case IcalInterface::COLOR :     // fall through
                case IcalInterface::PROXIMITY : // fall through
                case IcalInterface::PARTICIPANT_TYPE : // dito
                case IcalInterface::RESOURCE_TYPE :    // dito
                case IcalInterface::STATUS :    // fall through
                case IcalInterface::TRANSP :    // fall through
                case IcalInterface::TZID :      // fall through
                case IcalInterface::UID :
                    $content = $component->{$method}( true );
                    static $LOCTSUM = [ IcalInterface::LOCATION_TYPE, IcalInterface::SUMMARY ];
                    if( in_array( $propName, $LOCTSUM, true )) {
                        self::addLanguage( $content, $langComp, $langCal );
                    }
                    self::addXMLchildText( $properties, $propName, $content );
                    break;
                case IcalInterface::GEO :
                    self::addXMLchild(
                        $properties,
                        $propName,
                        strtolower( IcalInterface::GEO ),
                        $component->{$method}( true )
                    );
                    break;
                case IcalInterface::CALENDAR_ADDRESS : // fall through
                case IcalInterface::ORGANIZER :
                    self::addXMLchildCalAddress(
                        $properties,
                        $propName,
                        [ $component->{$method}( true ) ],
                        ( $langComp ?: $langCal )
                    );
                    break;
                case IcalInterface::PERCENT_COMPLETE : // fall through
                case IcalInterface::PRIORITY :         // fall through
                case IcalInterface::REPEAT :           // fall through
                case IcalInterface::SEQUENCE :
                    self::addXMLchildInteger( $properties, $propName, $component->{$method}( true ));
                    break;
                case IcalInterface::STRUCTURED_DATA :
                    foreach( $component->{$method}( true ) as $content ) {
                        if( $content->hasParamValue( IcalInterface::TEXT )) {
                            self::addXMLchildText( $properties, $propName, $content );
                            continue;
                        }
                        self::addXMLchildBinaryUri( $properties, $propName, $content );
                    } // end foreach
                    break;
                case IcalInterface::CONFERENCE :
                    foreach( $component->{$method}( true ) as $content ) {
                        self::addXMLchildUri( $properties, $propName, $content );
                    }
                    break;
                case IcalInterface::TRIGGER :
                    $content = $component->{$method}( true );
                    if( $content->value instanceof DateInterval ) {
                        self::addXMLchildDuration( $properties, $propName, $content );
                        break;
                    }
                    self::addXMLchildDateTime( $properties, $propName, $content );
                    break;
                case IcalInterface::TZOFFSETFROM : // fall through
                case IcalInterface::TZOFFSETTO :
                    self::addXMLchild( $properties, $propName, self::$utc_offset, $component->{$method}( true ));
                    break;
                case IcalInterface::TZURL :       // fall through
                case IcalInterface::URL :
                    self::addXMLchildUri( $properties, $propName, $component->{$method}( true ));
                    break;
                default :
                    if( StringFactory::isXprefixed( $propName )) {
                        $content = $component->getXprop( $propName, null, true );
                        self::addXMLchild( $properties, $content[0], self::$unknown, $content[1] );
                    }
                    break;
            } // end switch( $propName )
        } // end foreach( $props as $pix => $propName )
        // fix subComponent properties, if any
        foreach( $component->getComponents() as $subComp ) {
            self::componentProps2Xml(
                $subComp,
                $parentXml->addChild( strtolower( $subComp->getCompType())),
                $langCal
            );
        } // end foreach
    }

    /**
     * Add Pc content parameter language if not set
     *
     * @param Pc $content  property content
     * @param bool|string $langComp
     * @param bool|string $langCal
     * @return void
     * @since 2.41.69 2022-10-05
     */
    private static function addLanguage( Pc $content, bool | string $langComp, bool | string $langCal ) : void
    {
        switch( true ) {
            case $content->hasParamKey( IcalInterface::LANGUAGE ) :
                break;
            case ( ! empty( $langComp )) :
                $content->addParam( IcalInterface::LANGUAGE, $langComp );
                break;
            case ( ! empty( $langCal )) :
                $content->addParam( IcalInterface::LANGUAGE, $langCal );
                break;
        } // end switch
    }

    /**
     * Return bool true if VALUE=DATE, remove param VALUE
     *
     * @param Pc $content
     * @return bool
     */
    private static function hasValueDateSet( Pc $content ) : bool
    {
        $isDateSet = $content->hasParamValue( IcalInterface::DATE );
        $content->removeParam( IcalInterface::VALUE );
        return $isDateSet;
    }

    /**
     * @param Pc $content
     * @return string
     */
    private static function getRdateValueType( Pc $content ) : string
    {
        $type     = self::$date_time;
        if( $content->hasParamKey( IcalInterface::VALUE, IcalInterface::DATE )) {
            $type = self::$date;
        }
        elseif( $content->hasParamKey( IcalInterface::VALUE, IcalInterface::PERIOD )) {
            $type = self::$period;
        } // end if
        $content->removeParam( IcalInterface::VALUE );
        return $type;
    }

    /**
     * Add XML (rfc6321) binary/uri children to SimpleXMLelement from array
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc[]             $contents new subelements content
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.69 2022-10-05
     */
    private static function addXMLchildBinaryUriArr( SimpleXMLElement $parent, string $name, array $contents ) : void
    {
        foreach( $contents as $content ) {
            self::addXMLchildBinaryUri( $parent, $name, $content );
        }
    }

    /**
     * Add XML (rfc6321) binary/uri children to SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc               $content new subelement content
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.69 2022-10-04
     */
    private static function addXMLchildBinaryUri( SimpleXMLElement $parent, string $name, Pc $content ) : void
    {
        $type = $content->hasParamValue( IcalInterface::BINARY )
            ? self::$binary
            : self::$uri;
        $content->removeParam( IcalInterface::VALUE );
        self::addXMLchild( $parent, $name, $type, $content );
    }

    /**
     * Add XML (rfc6321) cal-address children to SimpleXMLelement
     *
     * @param SimpleXMLElement $parent a SimpleXMLelement class instance
     * @param string $name new element node name
     * @param Pc[] $contents new subelement contents
     * @param bool|string $lang
     * @return void
     * @throws Exception
     * @since 2.41.69 2022-11-02
     */
    private static function addXMLchildCalAddress(
        SimpleXMLElement $parent,
        string $name,
        array $contents,
        bool|string $lang
    ) : void
    {
        foreach( $contents as $content ) {
            if( $content->hasParamKey( IcalInterface::CN )) {
                self::addLanguage( $content, $lang, false );
            }
            self::addXMLchild( $parent, $name, self::$cal_address, $content );
        } // end foreach
    }

    /**
     * Add XML (rfc6321) date children to SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc               $content new subelement contents
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.69 2022-10-04
     */
    private static function addXMLchildDate( SimpleXMLElement $parent, string $name, Pc $content ) : void
    {
        self::addXMLchild( $parent, $name, self::$date, $content );
    }

    /**
     * Add XML (rfc6321) date-time children to SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc               $content new subelement content
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.69 2022-10-04
     */
    private static function addXMLchildDateTime( SimpleXMLElement $parent, string $name, Pc $content ) : void
    {
        self::addXMLchild( $parent, $name, self::$date_time, $content );
    }

    /**
     * Add XML (rfc6321) duration children to SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc               $content new subelement content
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.69 2022-10-04
     */
    private static function addXMLchildDuration( SimpleXMLElement $parent, string $name, Pc $content ) : void
    {
        self::addXMLchild( $parent, $name, strtolower( IcalInterface::DURATION ), $content );
    }

    /**
     * Add XML (rfc6321) integer children to SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc               $content new subelement content
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.69 2022-10-04
     */
    private static function addXMLchildInteger( SimpleXMLElement $parent, string $name, Pc $content ) : void
    {
        self::addXMLchild( $parent, $name, self::$integer, $content );
    }

    /**
     * Add XML (rfc6321) recur children to SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc               $content new subelement content
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.69 2022-10-04
     */
    private static function addXMLchildRecur( SimpleXMLElement $parent, string $name, Pc $content ) : void
    {
        self::addXMLchild( $parent, $name, self::$recur, $content );
    }

    /**
     * Add XML (rfc6321) text children to SimpleXMLelement
     *
     * @param SimpleXMLElement $parent   a SimpleXMLelement class instance
     * @param string           $name     new element node name
     * @param Pc               $content  new subelement content
     * @param null|bool|string $lang
     * @return void
     * @throws Exception
     * @since 2.41.69 2022-10-04
     */
    private static function addXMLchildText(
        SimpleXMLElement $parent,
        string $name,
        Pc $content,
        null|bool|string $lang = false
    ) : void
    {
        if( $lang ) {
            self::addLanguage( $content, false, $lang );
        }
        self::addXMLchild( $parent, $name, self::$text, $content );
    }

    /**
     * Add XML (rfc6321) uri children to SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc               $content new subelement content
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.69 2022-10-04
     */
    private static function addXMLchildUri( SimpleXMLElement $parent, string $name, Pc $content ) : void
    {
        $content->value = htmlentities( $content->value );
        self::addXMLchild( $parent, $name, self::$uri, $content );
    }

    /**
     * Add XML (rfc6321) children to SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param string           $type    content type, subelement(-s) name
     * @param Pc               $subData new subelement value and 'attributes'
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.69 2022-10-04
     */
    private static function addXMLchild(
        SimpleXMLElement $parent,
        string $name,
        string $type,
        Pc $subData
    ) : void
    {
        static $BOOLEAN      = 'boolean';
        static $UNTIL        = 'until';
        static $START        = 'start';
        static $END          = 'end';
        static $SP0          = '';
        static $altrep       = 'altrep';
        static $dir          = 'dir';
        static $delegated_from = 'delegated-from';
        static $delegated_to = 'delegated-to';
        static $member       = 'member';
        static $order        = 'order';
        static $sent_by      = 'sent-by';
        static $rsvp         = 'rsvp';
        static $derived      = 'derived';
        /** create new child node */
        $name  = strtolower( $name );
        $child = $parent->addChild( $name );
        if( empty( $subData->value ) && ( Util::$ZERO !== (string) $subData->value )) {
            $child->addChild( $type );
            return;
        }
        $recurDateIsSet = false;
        $isLocalTime    = $subData->hasParamKey(IcalInterface::ISLOCALTIME );
        $isOneParm      = ( 1 === count( $subData->params ));
        switch( true ) {
            case ( empty( $subData->params ) || ( $isOneParm && $isLocalTime )) :
                break;
            case ( self::$recur === $type ) :
                $recurDateIsSet = $subData->hasParamValue( IcalInterface::DATE );
                if( $isOneParm && $subData->hasParamKey( IcalInterface::VALUE )) {
                    break;
                }
                $subData->removeParam( IcalInterface::VALUE );
                // fall through
            default :
                $parameters = $child->addChild( self::$PARAMETERS );
                foreach((array) $subData->getParams() as $pKey => $parVal ) {
                    if( IcalInterface::VALUE === $pKey ) {
                        if( str_contains( $parVal, Util::$COLON )) {
                            $p1   = $parameters->addChild( strtolower( $pKey ));
                            $p1->addChild( self::$unknown, htmlspecialchars( $parVal ));
                            $type = strtolower( strstr( $parVal, Util::$COLON, true ));
                        }
                        elseif( 0 !== strcasecmp( $type, $parVal )) {
                            $type = strtolower( $parVal );
                        }
                        continue;
                    } // end if VALUE
                    if( IcalInterface::ISLOCALTIME === $pKey ) {
                        continue;
                    }
                    $pKey = strtolower( $pKey );
                    if( StringFactory::isXprefixed( $pKey )) {
                        $p1 = $parameters->addChild( $pKey );
                        $p1->addChild( self::$unknown, htmlspecialchars( $parVal ));
                        continue;
                    }
                    $p1 = $parameters->addChild( $pKey );
                    $ptype = match ( $pKey ) {
                        $altrep, $dir   => self::$uri,
                        $delegated_from, $delegated_to, $member, $sent_by => self::$cal_address,
                        $order          => self::$integer,
                        $rsvp, $derived => $BOOLEAN,
                        default         => self::$text,
                    }; // end switch
                    if( is_array( $parVal )) {
                        foreach( $parVal as $pV ) {
                            $p1->addChild( $ptype, htmlspecialchars((string) $pV ));
                        }
                    }
                    else {
                        $p1->addChild( $ptype, htmlspecialchars((string) $parVal ));
                    }
                } // end foreach $params
                break;
        } // end switch
        /** store content on type */
        $value = $subData->value;
        switch( $type ) {
            case self::$binary :
                $child->addChild( $type, $value );
                break;
            case $BOOLEAN :
                break;
            case self::$cal_address :
                $child->addChild( $type, $value );
                break;
            case self::$date :
                if( $value instanceof DateTime ) {
                    $value = [ $value ];
                }
                foreach( $value as $date ) {
                    $child->addChild( $type, DateTimeFactory::dateTime2Str( $date, true ));
                }
                break;
            case self::$date_time :
                if( $value instanceof DateTime ) {
                    $value = [ $value ];
                }
                foreach( $value as $dt ) {
                    $child->addChild( $type, DateTimeFactory::dateTime2Str( $dt, false, $isLocalTime ));
                } // end foreach
                break;
            case strtolower( IcalInterface::DURATION ) :
                $child->addChild(
                    $type,
                    DateIntervalFactory::dateInterval2String( $value, true )
                );
                break;
            case strtolower( IcalInterface::GEO ) :
                if( ! empty( $value )) {
                    $child->addChild(
                        IcalInterface::LATITUDE,
                        GeoFactory::geo2str2( $value[IcalInterface::LATITUDE], GeoFactory::$geoLatFmt )
                    );
                    $child->addChild(
                        IcalInterface::LONGITUDE,
                        GeoFactory::geo2str2( $value[IcalInterface::LONGITUDE], GeoFactory::$geoLongFmt )
                    );
                }
                break;
            case self::$integer :
                $child->addChild( $type, (string) $value );
                break;
            case self::$period :
                if( ! is_array( $value )) {
                    break;
                }
                foreach( $value as $period ) {
                    $v1  = $child->addChild( $type );
                    $str = DateTimeFactory::dateTime2Str( $period[0], false, $isLocalTime );
                    $v1->addChild( $START, $str );
                    if( $period[1] instanceof DateInterval ) {
                        $v1->addChild(
                            strtolower( IcalInterface::DURATION ),
                            DateIntervalFactory::dateInterval2String( $period[1] )
                        );
                    }
                    elseif( $period[1] instanceof DateTime ) {
                        $str = DateTimeFactory::dateTime2Str( $period[1], false, $isLocalTime );
                        $v1->addChild( $END, $str );
                    }
                } // end foreach
                break;
            case self::$recur :
                $value = array_change_key_case( $value );
                foreach( $value as $ruleLabel => $ruleValue ) {
                    switch( $ruleLabel ) {
                        case $UNTIL :
                            $child->addChild(
                                $ruleLabel,
                                DateTimeFactory::dateTime2Str( $ruleValue, $recurDateIsSet )
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
                                foreach( $ruleValue as $valuePart ) {
                                    $child->addChild( $ruleLabel, (string) $valuePart );
                                }
                            }
                            else {
                                $child->addChild( $ruleLabel, $ruleValue );
                            }
                            break;
                        case self::$byday :
                            if( isset( $ruleValue[IcalInterface::DAY] )) {
                                $str  = $ruleValue[0] ?? Util::$SP0;
                                $str .= $ruleValue[IcalInterface::DAY];
                                $child->addChild( $ruleLabel, $str );
                            }
                            else {
                                foreach( $ruleValue as $valuePart ) {
                                    if( isset( $valuePart[IcalInterface::DAY] )) {
                                        $str  = $valuePart[0] ?? Util::$SP0;
                                        $str .= $valuePart[IcalInterface::DAY];
                                        $child->addChild( $ruleLabel, $str );
                                    }
                                    else {
                                        $child->addChild( $ruleLabel, $valuePart );
                                    }
                                } // end foreach
                            }
                            break;
                        case self::$freq :
                        case self::$count :
                        case self::$interval :
                        case self::$wkst :
                        default:
                            $child->addChild( $ruleLabel, (string) $ruleValue );
                            break;
                    } // end switch( $ruleLabel )
                } // end foreach( $value as $ruleLabel => $ruleValue )
                break;
            case self::$rstatus :
                $child->addChild(
                    self::$code,
                    number_format((float) $value[IcalInterface::STATCODE], 2, Util::$DOT, $SP0 ));
                $child->addChild(
                    self::$description,
                    htmlspecialchars( $value[IcalInterface::STATDESC] )
                );
                if( isset( $value[IcalInterface::EXTDATA] )) {
                    $child->addChild(
                        self::$data,
                        htmlspecialchars( $value[IcalInterface::EXTDATA] )
                    );
                }
                break;
            case self::$text :
                if( ! is_array( $value )) {
                    $value = [ $value ];
                }
                foreach( $value as $part ) {
                    $child->addChild( $type, htmlspecialchars( $part ));
                }
                break;
            case self::$time :
                break;
            case self::$uri :
                $child->addChild( $type, $value );
                break;
            case self::$utc_offset :
                if( DateIntervalFactory::hasPlusMinusPrefix( $value )) {
                    $str   = $value[0];
                    $value = substr( $value, 1 );
                }
                else {
                    $str = Util::$PLUS;
                }
                $str .= substr( $value, 0, 2 ) .
                    Util::$COLON . substr( $value, 2, 2 );
                if( 4 < strlen( $value )) {
                    $str .= Util::$COLON . substr( $value, 4 );
                }
                $child->addChild( $type, $str );
                break;
            case self::$unknown : // fall through
            default:
                if( is_array( $value )) {
                    $value = implode( $value );
                }
                $child->addChild( self::$unknown, htmlspecialchars( $value ));
                break;
        } // end switch
    }
}
