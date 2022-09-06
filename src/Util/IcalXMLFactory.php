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
declare( strict_types = 1 );
namespace Kigkonsult\Icalcreator\Util;

use DateInterval;
use DateTime;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Icalcreator\CalendarComponent;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Vcalendar;
use SimpleXMLElement;

use function array_change_key_case;
use function html_entity_decode;
use function htmlspecialchars;
use function implode;
use function in_array;
use function is_array;
use function number_format;
use function sprintf;
use function str_replace;
use function str_starts_with;
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
 * @since 2.41.63 2022-09-05
 */
class IcalXMLFactory
{
    /**
     * @var string
     */
    private static string $Vcalendar      = 'vcalendar';

    /**
     * @var string[]
     */
    private static array $calProps       = [
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
    private static string $properties     = 'properties';

    /**
     * @var string
     */
    private static string $PARAMETERS     = 'parameters';

    /**
     * @var string
     */
    private static string $components     = 'components';


    /**
     * @var string
     */
    private static string $text           = 'text';

    /**
     * @var string
     */
    private static string $binary         = 'binary';

    /**
     * @var string
     */
    private static string $uri            = 'uri';

    /**
     * @var string
     */
    private static string $date           = 'date';

    /**
     * @var string
     */
    private static string $date_time      = 'date-time';

    /**
     * @var string
     */
    private static string $period         = 'period';

    /**
     * @var string
     */
    private static string $rstatus        = 'rstatus';

    /**
     * @var string
     */
    private static string $unknown        = 'unknown';

    /**
     * @var string
     */
    private static string $recur          = 'recur';

    /**
     * @var string
     */
    private static string $cal_address    = 'cal-address';

    /**
     * @var string
     */
    private static string $integer        = 'integer';

    /**
     * @var string
     */
    private static string $utc_offset     = 'utc-offset';

    /**
     * @var string
     */
    private static string $code           = 'code';

    /**
     * @var string
     */
    private static string $description    = 'description';

    /**
     * @var string
     */
    private static string $data           = 'data';

    /**
     * @var string
     */
    private static string $time           = 'time';

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
    private static string $bymonthday     = 'bymonthday';

    /**
     * @var string
     */
    private static string $byyearday      = 'byyearday';

    /**
     * @var string
     */
    private static string $byweekno       = 'byweekno';

    /**
     * @var string
     */
    private static string $bymonth        = 'bymonth';

    /**
     * @var string
     */
    private static string $bysetpos       = 'bysetpos';

    /**
     * @var string
     */
    private static string $byday          = 'byday';

    /**
     * @var string
     */
    private static string $freq           = 'freq';

    /**
     * @var string
     */
    private static string $count          = 'count';

    /**
     * @var string
     */
    private static string $interval       = 'interval';

    /**
     * @var string
     */
    private static string $wkst           = 'wkst';

    /**
     * @var string
     */
    public static string $XMLstart =
        '<?xml version="1.0" encoding="utf-8"?><icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0"><!-- kigkonsult.se %s, iCal2XMl (rfc6321), %s --></icalendar>';

    /**
     * Return iCal XML (rfc6321) output, using PHP SimpleXMLElement
     *
     * @param Vcalendar $calendar iCalcreator Vcalendar instance reference
     * @return bool|string
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.51 2022-08-09
     */
    public static function iCal2XML( Vcalendar $calendar ) : bool | string
    {
        static $YMDTHISZ = 'Ymd\THis\Z';
        /** fix an SimpleXMLElement instance and create root element */
        $xml       = new SimpleXMLElement(
            sprintf( self::$XMLstart, ICALCREATOR_VERSION, gmdate( $YMDTHISZ ))
        );
        $VcalendarXml = $xml->addChild( self::$Vcalendar );
        $langCal      = $calendar->getConfig( IcalInterface::LANGUAGE );
        /** fix calendar properties */
        $properties = $VcalendarXml->addChild( self::$properties );
        foreach( self::$calProps as $propName ) {
            $method = StringFactory::getGetMethodName( $propName );
            if( false !== ( $contents = $calendar->{$method}())) {
                self::addXMLchildText( $properties, $propName, Pc::factory( $contents ));
            }
        } // end foreach
        foreach( self::$calPropsrfc7986Single as $propName ) {
            $method = StringFactory::getGetMethodName( $propName );
            switch( strtoupper( $propName )) {
                case IcalInterface::UID :   // fall through
                case IcalInterface::COLOR :
                    if( false !== ( $contents = $calendar->{$method}( true ))) {
                        self::addXMLchildText( $properties, $propName, $contents );
                    }
                    break;
                case IcalInterface::LAST_MODIFIED :
                    if( false !== ( $contents = $calendar->{$method}( true ))) {
                        unset( $contents->params[IcalInterface::VALUE] );
                        self::addXMLchildDateTime( $properties, $propName, $contents );
                    }
                    break;
                case IcalInterface::SOURCE : // fall through
                case IcalInterface::URL :
                    if( false !== ( $contents = $calendar->{$method}( true ))) {
                        self::addXMLchildUri( $properties, $propName, $contents );
                    }
                    break;
                case IcalInterface::REFRESH_INTERVAL :
                    if( false !== ( $contents = $calendar->{$method}( true ))) {
                        self::addXMLchildDuration( $properties, $propName, $contents );
                    }
                    break;
            } // end switch
        } // end foreach
        foreach( self::$calPropsrfc7986Multi as $propName ) {
            $method = StringFactory::getGetAllMethodName( $propName );
            switch( strtoupper( $propName )) {
                case IcalInterface::CATEGORIES :  // fall through
                case IcalInterface::DESCRIPTION : // fall through
                case IcalInterface::NAME :
                    foreach( $calendar->{$method}( true ) as $contents ) {
                        if( ! empty( $langCal )) {
                            $contents->addParam( IcalInterface::LANGUAGE, $langCal, false );
                        }
                        self::addXMLchildText( $properties, $propName, $contents );
                    } // end while
                    break;
                case IcalInterface::IMAGE :
                    foreach( $calendar->{$method}( true ) as $contents ) {
                        self::addXMLchildBinaryUri( $properties, $propName, $contents );
                    } // end while
                    break;
            } // end switch
        } // end foreach
        foreach( $calendar->getAllXprop( true ) as $contents ) {
            self::addXMLchild( $properties, $contents[0], self::$unknown, $contents[1] );
        } // end while
        /** prepare to fix components with properties/subComponents */
        $componentsXml = $VcalendarXml->addChild( self::$components );
        /** fix component properties */
        while( false !== ( $component = $calendar->getComponent())) {
            self::compProps2Xml(
                $component,
                $componentsXml->addChild( strtolower( $component->getCompType())),
                $langCal
            );
        } // end while
        return $xml->asXML();
    }

    /**
     * Parse component into XML
     *
     * @param CalendarComponent $component
     * @param SimpleXMLElement $parentXml
     * @param bool|string $langCal
     * @throws Exception
     * @since 2.41.51 2022-08-09
     */
    private static function compProps2Xml(
        CalendarComponent $component,
        SimpleXMLElement  $parentXml,
        bool | string     $langCal
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
                    foreach( $component->{$method}( true ) as $contents ) {
                        self::addXMLchildBinaryUri( $properties, $propName, $contents );
                    } // end while
                    break;
                case IcalInterface::ATTENDEE :
                    foreach( $component->{$method}( true ) as $contents ) {
                        if( $langCal && $contents->hasParamKey( IcalInterface::CN )) {
                            self::addLanguage( $contents->params, $langComp, $langCal );
                        }
                        self::addXMLchildCalAddress( $properties, $propName, $contents );
                    } // end foreach
                    break;
                case IcalInterface::EXDATE :
                    foreach( $component->{$method}( true ) as $contents ) {
                        $isDateSet = $contents->hasParamValue( IcalInterface::DATE );
                        $contents->removeParam( IcalInterface::VALUE );
                        if( $isDateSet ) {
                            self::addXMLchildDate( $properties, $propName, $contents );
                        }
                        else {
                            self::addXMLchildDateTime( $properties, $propName, $contents );
                        }
                    } // end foreach
                    break;
                case IcalInterface::FREEBUSY :
                    foreach( $component->{$method}( true ) as $contents ) {
                        self::addXMLchild( $properties, $propName, self::$period, $contents );
                    } // end foreach
                    break;
                case IcalInterface::REQUEST_STATUS :
                    foreach( $component->{$method}( true ) as $contents ) {
                        self::addLanguage( $contents->params, $langComp, $langCal );
                        self::addXMLchild( $properties, $propName, self::$rstatus, $contents );
                    } // end foreach
                    break;
                case IcalInterface::RDATE :
                    foreach( $component->{$method}( true ) as $contents ) {
                        $type = self::$date_time;
                        if( $contents->hasParamKey( IcalInterface::VALUE, IcalInterface::DATE )) {
                            $type = self::$date;
                        }
                        elseif( $contents->hasParamKey( IcalInterface::VALUE, IcalInterface::PERIOD )) {
                            $type = self::$period;
                        } // end if
                        $contents->removeParam( IcalInterface::VALUE );
                        self::addXMLchild( $properties, $propName, $type, $contents );
                    } // end foreach
                    break;
                case IcalInterface::DESCRIPTION :   // multiple in VCALENDAR/VJOURNAL, single elsewere
                    // fall through
                case IcalInterface::LOCATION :      // multiple in PARTICIPANT, single elsewere
                    foreach( $component->{$method}( true ) as $contents ) {
                        self::addLanguage( $contents->params, $langComp, $langCal );
                        self::addXMLchildText( $properties, $propName, $contents );
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
                    foreach( $component->{$method}( true ) as $contents ) {
                        if( $contents->hasParamValue( IcalInterface::URI )) {
                            self::addXMLchildUri( $properties, $propName, $contents );
                        }
                        else {
                            self::addLanguage( $contents->params, $langComp, $langCal );
                            self::addXMLchildText( $properties, $propName, $contents );
                        }
                    } // end foreach
                    break;
                case IcalInterface::CATEGORIES :    // fall through
                case IcalInterface::COMMENT :       // fall through
                case IcalInterface::CONTACT :       // fall through  // single in VFREEBUSY, multiple elsewere
                case IcalInterface::NAME :          // dito, multi i Vcalendar, single in Vlocation/Vresource (here)
                case IcalInterface::RELATED_TO :    // fall through
                case IcalInterface::TZID_ALIAS_OF : // fall through
                case IcalInterface::TZNAME :        // fall through
                case IcalInterface::RESOURCES :
                    foreach( $component->{$method}( true ) as $contents ) {
                        if(( IcalInterface::RELATED_TO !== $propName )) {
                            self::addLanguage( $contents->params, $langComp, $langCal );
                        } // end if
                        self::addXMLchildText( $properties, $propName, $contents );
                        if(( IcalInterface::CONTACT === $propName ) && $component::isContactSingleProp( $compName )) {
                            break;
                        }
                        if( IcalInterface::NAME === $propName ) {
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
                    if( false !== ( $contents = $component->{$method}( true ))) {
                        $sDateSet = $contents->hasParamValue( IcalInterface::DATE );
                        $contents->removeParam( IcalInterface::VALUE );
                        if( $sDateSet  ) {
                            self::addXMLchildDate( $properties, $propName, $contents );
                        }
                        else {
                            self::addXMLchildDateTime( $properties, $propName, $contents );
                        }
                    } // end if
                    break;
                case IcalInterface::DURATION :
                    if( false !== ( $contents = $component->{$method}( true ))) {
                        self::addXMLchildDuration( $properties, $propName, $contents );
                    }
                    break;
                case IcalInterface::EXRULE :
                case IcalInterface::RRULE :
                    if( false !== ( $contents = $component->{$method}( true ))) {
                        self::addXMLchildRecur( $properties, $propName, $contents );
                    }
                    break;
                case IcalInterface::ACTION :    // fall through
                case IcalInterface::BUSYTYPE :  // dito
                case IcalInterface::KLASS :     // fall through
                case IcalInterface::COLOR :     // fall through
                case IcalInterface::LOCATION_TYPE :    // dito
                case IcalInterface::PROXIMITY : // fall through
                case IcalInterface::PARTICIPANT_TYPE : // dito
                case IcalInterface::RESOURCE_TYPE :    // dito
                case IcalInterface::STATUS :    // fall through
                case IcalInterface::SUMMARY :   // fall through
                case IcalInterface::TRANSP :    // fall through
                case IcalInterface::TZID :      // fall through
                case IcalInterface::UID :
                    static $locNameSum = [ IcalInterface::LOCATION_TYPE, IcalInterface::NAME, IcalInterface::SUMMARY ];
                    if( false !== ( $contents = $component->{$method}( true ))) {
                        if( in_array( $propName, $locNameSum,true ))  {
                            self::addLanguage( $contents->params, $langComp, $langCal );
                        }
                        self::addXMLchildText( $properties, $propName, $contents );
                    }
                    break;
                case IcalInterface::GEO :
                    if( false !== ( $contents = $component->{$method}( true ))) {
                        self::addXMLchild( $properties, $propName, strtolower( IcalInterface::GEO ), $contents );
                    }
                    break;
                case IcalInterface::CALENDAR_ADDRESS : // fall through
                case IcalInterface::ORGANIZER :
                    if( false !== ( $contents = $component->{$method}( true ))) {
                        if(( IcalInterface::ORGANIZER === $propName ) &&
                            $contents->hasParamKey( IcalInterface::CN )) {
                            self::addLanguage( $contents->params, $langComp, $langCal );
                        }
                        self::addXMLchildCalAddress( $properties, $propName, $contents );
                    }
                    break;
                case IcalInterface::PERCENT_COMPLETE : // fall through
                case IcalInterface::PRIORITY :         // fall through
                case IcalInterface::REPEAT :           // fall through
                case IcalInterface::SEQUENCE :
                    if( false !== ( $contents = $component->{$method}( true ))) {
                        self::addXMLchildInteger( $properties, $propName, $contents );
                    }
                    break;
                case IcalInterface::STRUCTURED_DATA :
                    foreach( $component->{$method}( true ) as $contents ) {
                        if( $contents->hasParamValue( IcalInterface::TEXT )) {
                            self::addXMLchildText( $properties, $propName, $contents );
                        }
                        else {
                            self::addXMLchildBinaryUri( $properties, $propName, $contents );
                        }
                    } // end foreach
                    break;
                case IcalInterface::CONFERENCE :
                    foreach( $component->{$method}( true ) as $contents ) {
                        self::addXMLchildUri( $properties, $propName, $contents );
                    } // end foreach
                    break;
                case IcalInterface::TRIGGER :
                    if( false !== ( $contents = $component->{$method}( true ))) {
                        if( $contents->value instanceof DateInterval ) {
                            self::addXMLchildDuration( $properties, $propName, $contents );
                        }
                        else {
                            self::addXMLchildDateTime( $properties, $propName, $contents );
                        }
                    } // end if
                    break;
                case IcalInterface::TZOFFSETFROM : // fall through
                case IcalInterface::TZOFFSETTO :
                    if( false !== ( $contents = $component->{$method}( true ))) {
                        self::addXMLchild( $properties, $propName, self::$utc_offset, $contents );
                    }
                    break;
                case IcalInterface::TZURL :       // fall through
                case IcalInterface::URL :
                    $method = StringFactory::getGetMethodName( $propName );
                    if( false !== ( $contents = $component->{$method}( true ))) {
                        self::addXMLchildUri( $properties, $propName, $contents );
                    }
                    break;
                default :
                    if( ! StringFactory::isXprefixed( $propName )) {
                        break;
                    }
                    if( false !== ( $contents = $component->getXprop( $propName, null, true ))) {
                        self::addXMLchild( $properties, $contents[0], self::$unknown, $contents[1] );
                    }
                    break;
            } // end switch( $propName )
        } // end foreach( $props as $pix => $propName )
        /** fix subComponent properties, if any */
        while( false !== ( $subcomp = $component->getComponent())) {
            self::compProps2Xml(
                $subcomp,
                $parentXml->addChild( strtolower( $subcomp->getCompType())),
                $langCal
            );
        }
    }

    /**
     * Add parameter language if not set
     *
     * @param string[] $params
     * @param bool|string $langComp
     * @param bool|string $langCal
     * @return void
     */
    private static function addLanguage( array & $params, bool | string $langComp, bool | string $langCal ) : void
    {
        switch( true ) {
            case isset( $params[IcalInterface::LANGUAGE] ) :
                break;
            case ( ! empty( $langComp )) :
                $params[IcalInterface::LANGUAGE] = $langComp;
                break;
            case ( ! empty( $langCal )) :
                $params[IcalInterface::LANGUAGE] = $langCal;
                break;
        } // end switch
    }

    /**
     * Add XML (rfc6321) binary/uri children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc               $contents
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-03-30
     */
    private static function addXMLchildBinaryUri( SimpleXMLElement $parent, string $name, Pc $contents ) : void
    {
        $type = $contents->hasParamValue( IcalInterface::BINARY )
            ? self::$binary
            : self::$uri;
        $contents->removeParam( IcalInterface::VALUE );
        self::addXMLchild( $parent, $name, $type, $contents );
    }

    /**
     * Add XML (rfc6321) cal-address children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc               $contents new subelement contents
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-03-30
     */
    private static function addXMLchildCalAddress( SimpleXMLElement $parent, string $name, Pc $contents ) : void
    {
        self::addXMLchild( $parent, $name, self::$cal_address, $contents );
    }

    /**
     * Add XML (rfc6321) date children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc               $contents new subelement contents
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-03-30
     */
    private static function addXMLchildDate( SimpleXMLElement $parent, string $name, Pc $contents ) : void
    {
        self::addXMLchild( $parent, $name, self::$date, $contents );
    }

    /**
     * Add XML (rfc6321) date-time children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc               $contents new subelement contents
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-03-30
     */
    private static function addXMLchildDateTime( SimpleXMLElement $parent, string $name, Pc $contents ) : void
    {
        self::addXMLchild( $parent, $name, self::$date_time, $contents );
    }

    /**
     * Add XML (rfc6321) duration children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc               $contents new subelement contents
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-03-30
     */
    private static function addXMLchildDuration( SimpleXMLElement $parent, string $name, Pc $contents ) : void
    {
        self::addXMLchild( $parent, $name, strtolower( IcalInterface::DURATION ), $contents );
    }

    /**
     * Add XML (rfc6321) integer children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc               $contents new subelement contents
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-03-30
     */
    private static function addXMLchildInteger( SimpleXMLElement $parent, string $name, Pc $contents ) : void
    {
        self::addXMLchild( $parent, $name, self::$integer, $contents );
    }

    /**
     * Add XML (rfc6321) recur children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc               $contents new subelement contents
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-03-30
     */
    private static function addXMLchildRecur( SimpleXMLElement $parent, string $name, Pc $contents ) : void
    {
        self::addXMLchild( $parent, $name, self::$recur, $contents );
    }

    /**
     * Add XML (rfc6321) text children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent    a SimpleXMLelement class instance
     * @param string           $name      new element node name
     * @param Pc               $contents  new subelement contents
     * @return void
     * @throws Exception
     * @since 2.41.36 2022-03-30
     */
    private static function addXMLchildText( SimpleXMLElement $parent, string $name, Pc $contents ) : void
    {
        self::addXMLchild( $parent, $name, self::$text, $contents );
    }

    /**
     * Add XML (rfc6321) uri children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param Pc               $contents new subelement contents
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-03-30
     */
    private static function addXMLchildUri( SimpleXMLElement $parent, string $name, Pc $contents ) : void
    {
        $contents->value = htmlentities( $contents->value );
        self::addXMLchild( $parent, $name, self::$uri, $contents );
    }

    /**
     * Add XML (rfc6321) children to a SimpleXMLelement
     *
     * @param SimpleXMLElement $parent  a SimpleXMLelement class instance
     * @param string           $name    new element node name
     * @param string           $type    content type, subelement(-s) name
     * @param Pc               $subData new subelement value and 'attributes'
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.63 2022-09-05
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
                foreach( $subData->getParams() as $pKey => $parVal ) {
                    if( IcalInterface::VALUE === $pKey ) {
                        if( str_contains( $parVal, Util::$COLON )) {
                            $p1   = $parameters->addChild( strtolower( $pKey ));
                            $p1->addChild( self::$unknown, htmlspecialchars( $parVal ));
                            $type = strtolower( StringFactory::before( Util::$COLON, $parVal ));
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

    /**
     * Parse (rfc6321) XML string into iCalcreator instance
     *
     * @param string $xmlStr
     * @param null|string[] $iCalcfg Vcalendar config array (opt)
     * @return Vcalendar|bool   false on error
     * @throws Exception
     * @since  2.20.23 - 2017-02-25
     */
    public static function XML2iCal( string $xmlStr, ? array $iCalcfg = [] ) : Vcalendar | bool
    {
        static $CRLF = [ "\r\n", "\n\r", "\n", "\r" ];
        $xmlStr      = str_replace( $CRLF, Util::$SP0, $xmlStr );
        $xml         = self::XMLgetTagContent1( $xmlStr, self::$Vcalendar, $endIx );
        $iCal        = new Vcalendar( $iCalcfg ?? [] );
        if( false === self::XMLgetComps( $iCal, $xmlStr )) {
            return false;
        }
        return $iCal;
    }

    /**
     * Parse (rfc6321) XML string into iCalcreator components
     *
     * @param IcalInterface $iCal
     * @param string    $xml
     * @return IcalInterface|bool    false on error
     * @since  2.41.54 - 2022-08-13
     */
    private static function XMLgetComps( IcalInterface $iCal, string $xml ) : IcalInterface | bool
    {
        static $PROPSTAGempty = '<properties/>';
        static $PROPSTAGstart = '<properties>';
        static $COMPSTAGempty = '<components/>';
        static $COMPSTAGstart = '<components>';
        static $NEW      = 'new';
        static $ALLCOMPS = [ // all IcalBase::$CALCOMPS + IcalBase::$TZCOMPS
            IcalInterface::AVAILABLE,
            IcalInterface::DAYLIGHT,
            IcalInterface::PARTICIPANT,
            IcalInterface::STANDARD,
            IcalInterface::VALARM,
            IcalInterface::VAVAILABILITY,
            IcalInterface::VEVENT,
            IcalInterface::VFREEBUSY,
            IcalInterface::VJOURNAL,
            IcalInterface::VLOCATION,
            IcalInterface::VRESOURCE,
            IcalInterface::VTIMEZONE,
            IcalInterface::VTODO,
        ];
        $len = strlen( $xml );
        $sx  = 0;
        while(
            ((( $sx + 12 ) < $len ) &&
                ! str_starts_with( substr( $xml, $sx ), $PROPSTAGstart ) &&
                ! str_starts_with( substr( $xml, $sx ), $COMPSTAGstart )
                ) &&
            ((( $sx + 13 ) < $len ) &&
                ! str_starts_with( substr( $xml, $sx ), $PROPSTAGempty ) &&
                ! str_starts_with( substr( $xml, $sx ), $COMPSTAGempty ))) {
            ++$sx;
        } // end while
        if(( $sx + 11 ) >= $len ) {
            return false;
        }
        if( str_starts_with( $xml, $PROPSTAGempty )) {
            $pos = strlen( $PROPSTAGempty );
            $xml = substr( $xml, $pos );
        }
        elseif( str_starts_with( substr( $xml, $sx ), $PROPSTAGstart )) {
            $xml2 = self::XMLgetTagContent1( $xml, self::$properties, $endIx );
            self::XMLgetProps( $iCal, $xml2 );
            $xml  = substr( $xml, $endIx );
        }
        if( str_starts_with( $xml, $COMPSTAGempty )) {
            $pos = strlen( $COMPSTAGempty );
            $xml = substr( $xml, $pos );
        }
        elseif( str_starts_with( $xml, $COMPSTAGstart )) {
            $xml = self::XMLgetTagContent1( $xml, self::$components, $endIx );
        }
        while( ! empty( $xml )) {
            $xml2     = self::XMLgetTagContent2( $xml, $tagName, $endIx );
            $compType = ucfirst( strtolower( $tagName ));
            if( in_array( $compType, $ALLCOMPS, true )) {
                $newCompMethod = $NEW . $compType;
                $iCalComp      = $iCal->{$newCompMethod}();
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
     * @return void
     * @since  2.41.56 - 2022-08-15
     * @noinspection UnsupportedStringOffsetOperationsInspection
     */
    private static function XMLgetProps( IcalInterface $iCalComp, string $xml ) : void
    {
        static $VERSIONPRODID   = [ IcalInterface::VERSION, IcalInterface::PRODID ];
        static $PARAMENDTAG     = '<parameters/>';
        static $PARAMTAG        = '<parameters>';
        static $DATETAGST       = '<date';
        static $PERIODTAG       = '<period>';
        static $ATTENDEEPARKEYS    = [
            IcalInterface::DELEGATED_FROM,
            IcalInterface::DELEGATED_TO,
            IcalInterface::MEMBER
        ];
        while( ! empty( $xml )) {
            $xml2     = self::XMLgetTagContent2( $xml, $propName, $endIx );
            $propName = strtoupper( $propName );
            if( empty( $xml2 ) && ( Util::$ZERO !== $xml2 )) {
                if( StringFactory::isXprefixed( $propName )) {
                    $iCalComp->setXprop( $propName );
                }
                else {
                    $method = StringFactory::getSetMethodName( $propName );
                    $iCalComp->{$method}();
                }
                $xml = substr( $xml, $endIx );
                continue;
            }
            $params = [];
            if( str_starts_with( $xml2, $PARAMENDTAG )) {
                $xml2 = substr( $xml2, 13 );
            }
            elseif( str_starts_with( $xml2, $PARAMTAG )) {
                $xml3   = self::XMLgetTagContent1( $xml2, self::$PARAMETERS, $endIx2 );
                $endIx3 = 0;
                while( ! empty( $xml3 )) {
                    $xml4     = self::XMLgetTagContent2( $xml3, $paramKey, $endIx3 );
                    $paramKey = strtoupper( $paramKey );
                    if( in_array( $paramKey, $ATTENDEEPARKEYS, true )) {
                        while( ! empty( $xml4 )) {
                            $paramValue = self::XMLgetTagContent1( $xml4, self::$cal_address, $endIx4 );
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
                        $pType      = Util::$SP0; // skip parameter valueType
                        $paramValue = html_entity_decode(
                            self::XMLgetTagContent2( $xml4, $pType, $endIx4 )
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
            $valueType = Util::$SP0;
            $value     = ( ! empty( $xml2 ) || ( Util::$ZERO === $xml2 ))
                ? self::XMLgetTagContent2( $xml2, $valueType, $endIx3 )
                : Util::$SP0;
            switch( $propName ) {
                case IcalInterface::URL : // fall through
                case IcalInterface::TZURL :
                    $value = html_entity_decode( $value );
                    break;
                case IcalInterface::EXDATE :   // multiple single-date(-times) may exist
                // fall through
                case IcalInterface::RDATE :
                    if( self::$period !== $valueType ) {
                        if( self::$date === $valueType ) {
                            $params[IcalInterface::VALUE] = IcalInterface::DATE;
                        }
                        $t = [];
                        while( ! empty( $xml2 ) && str_starts_with( $xml2, $DATETAGST )) {
                            $t[]  = self::XMLgetTagContent2( $xml2, $pType, $endIx4);
                            $xml2 = substr( $xml2, $endIx4 );
                        } // end while
                        $value = $t;
                        break;
                    }
                // fall through
                case IcalInterface::FREEBUSY :
                    if( IcalInterface::RDATE === $propName ) {
                        $params[IcalInterface::VALUE] = IcalInterface::PERIOD;
                    }
                    $value = [];
                    while( ! empty( $xml2 ) && str_starts_with( $xml2, $PERIODTAG )) {
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
                case IcalInterface::TZOFFSETTO : // fall through
                case IcalInterface::TZOFFSETFROM :
                    $value = str_replace( Util::$COLON, Util::$SP0, $value );
                    break;
                case IcalInterface::GEO :
                    $tValue = [ IcalInterface::LATITUDE => $value ];
                    $tValue[IcalInterface::LONGITUDE] = self::XMLgetTagContent1(
                        substr( $xml2, $endIx3 ),
                        IcalInterface::LONGITUDE,
                        $endIx3
                    );
                    $value = $tValue;
                    break;
                case IcalInterface::EXRULE :
                // fall through
                case IcalInterface::RRULE :
                    $tValue    = [ $valueType => $value ];
                    $xml2      = substr( $xml2, $endIx3 );
                    $valueType = Util::$SP0;
                    while( ! empty( $xml2 )) {
                        $t = self::XMLgetTagContent2( $xml2, $valueType, $endIx4 );
                        switch( strtoupper( $valueType )) {
                            case IcalInterface::FREQ :     // fall through
                            case IcalInterface::COUNT :    // fall through
                            case IcalInterface::INTERVAL : // fall through
                            case IcalInterface::RSCALE :   // fall through
                            case IcalInterface::SKIP :     // fall through
                            case IcalInterface::UNTIL :    // fall through
                            case IcalInterface::WKST :
                                $tValue[$valueType] = $t;
                                break;
                            case IcalInterface::BYDAY :
                                if( 2 === strlen( $t )) {
                                    $tValue[$valueType][] = [ IcalInterface::DAY => $t ];
                                }
                                else {
                                    $day = substr( $t, -2 );
                                    $key = substr( $t, 0, ( strlen( $t ) - 2 ));
                                    $tValue[$valueType][] = [ $key, IcalInterface::DAY => $day ];
                                }
                                break;
                            default:
                                $tValue[$valueType][] = $t;
                        } // end switch
                        $xml2 = substr( $xml2, $endIx4 );
                    } // end while
                    $value = $tValue;
                    break;
                case IcalInterface::REQUEST_STATUS :
                    $value = [
                        self::$code        => null,
                        self::$description => null,
                        self::$data        => null
                    ];
                    while( ! empty( $xml2 )) {
                        $t    = html_entity_decode(
                            self::XMLgetTagContent2( $xml2, $valueType, $endIx4 ));
                        $value[$valueType] = $t;
                        $xml2 = substr( $xml2, $endIx4 );
                    } // end while
                    break;
                case IcalInterface::STRUCTURED_DATA :
                    $params[IcalInterface::VALUE] = match( $valueType ) {
                        self::$binary => IcalInterface::BINARY,
                        self::$text   => IcalInterface::TEXT,
                        self::$uri    => IcalInterface::URI,
                    };
                    break;
                case IcalInterface::STYLED_DESCRIPTION :
                    $params[IcalInterface::VALUE] = match( $valueType ) {
                        self::$text => IcalInterface::TEXT,
                        self::$uri  => IcalInterface::URI
                    };
                    break;
                default:
                    switch( $valueType ) {
                        case self::$uri :
                            $value = html_entity_decode( $value );
                            if( in_array( $propName, [ IcalInterface::ATTACH, IcalInterface::SOURCE ], true )) {
                                break;
                            }
                            $params[IcalInterface::VALUE] = IcalInterface::URI;
                            break;
                        case self::$binary :
                            $params[IcalInterface::VALUE] = IcalInterface::BINARY;
                            break;
                        case self::$date :
                            $params[IcalInterface::VALUE] = IcalInterface::DATE;
                            break;
                        case self::$date_time :
                            $params[IcalInterface::VALUE] = IcalInterface::DATE_TIME;
                            break;
                        case self::$text :
                            // fall through
                        case self::$unknown :
                            $value = html_entity_decode( $value );
                            break;
                        default :
                            if( StringFactory::isXprefixed( $propName ) &&
                                ( self::$unknown !== strtolower( $valueType ))) {
                                $params[IcalInterface::VALUE] = strtoupper( $valueType );
                            }
                            break;
                    } // end switch
                    break;
            } // end switch( $propName )
            $method = StringFactory::getSetMethodName( $propName );
            switch( true ) {
                case ( in_array( $propName, $VERSIONPRODID, true )) :
                    break;
                case ( StringFactory::isXprefixed( $propName )) :
                    $iCalComp->setXprop( $propName, $value, $params );
                    break;
                case ( in_array( $propName, [ IcalInterface::EXRULE, IcalInterface::RRULE ], true ) &&
                    isset( $value[self::$recur] ) && empty( $value[self::$recur] )) :
                    $iCalComp->{$method}(); // empty rexRule
                    break;
                case ( IcalInterface::FREEBUSY === $propName ) :
                    $fbtype = $params[IcalInterface::FBTYPE] ?? null;
                    unset( $params[IcalInterface::FBTYPE] );
                    $iCalComp->{$method}( $fbtype, $value, $params );
                    break;
                case ( IcalInterface::GEO === $propName ) :
                    if( ( Util::$SP0 !== $value[IcalInterface::LATITUDE] ) &&
                        ( Util::$SP0 !== $value[IcalInterface::LONGITUDE] )) {
                        $iCalComp->{$method}(
                            $value[IcalInterface::LATITUDE],
                            $value[IcalInterface::LONGITUDE],
                            $params
                        );
                    }
                    else {
                        $iCalComp->{$method}();                    }
                    break;
                case ( IcalInterface::REQUEST_STATUS === $propName ) :
                    $iCalComp->{$method}(
                        $value[self::$code],
                        $value[self::$description],
                        $value[self::$data],
                        $params
                    );
                    break;
                default :
                    if( empty( $value ) && ( is_array( $value ) || ( Util::$ZERO > $value ))) {
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
     * @param string   $xml
     * @param string   $tagName
     * @param null|int $endIx
     * @return string
     * @since  2.23.8 - 2017-04-17
     */
    private static function XMLgetTagContent1( string $xml, string $tagName, ? int & $endIx = 0 ) : string
    {
        static $FMT0 = '<%s>';
        static $FMT1 = '<%s />';
        static $FMT2 = '<%s/>';
        static $FMT3 = '</%s>';
        $tagName = strtolower( $tagName );
        $strLen  = strlen( $tagName );
        $xmlLen  = strlen( $xml );
        $sx1     = 0;
        while( $sx1 < $xmlLen ) {
            if((( $sx1 + $strLen + 1 ) < $xmlLen ) && // start tag
                ( sprintf( $FMT0, $tagName ) === strtolower( substr( $xml, $sx1, ( $strLen + 2 ))))
            ) {
                break;
            }
            if((( $sx1 + $strLen + 3 ) < $xmlLen ) && // empty tag1
                ( sprintf( $FMT1, $tagName ) === strtolower( substr( $xml, $sx1, ( $strLen + 4 ))))
            ) {
                $endIx = $strLen + 5;
                return Util::$SP0; // empty tag
            }
            if((( $sx1 + $strLen + 2 ) < $xmlLen ) && // empty tag2
                ( sprintf( $FMT2, $tagName ) ===  strtolower( substr( $xml, $sx1, ( $strLen + 3 ))))
            ) {
                $endIx = $strLen + 4;
                return Util::$SP0; // empty tag
            }
            ++$sx1;
        } // end while...
        if( ! isset( $xml[$sx1] )) {
            $endIx = ( empty( $sx1 )) ? 0 : $sx1 - 1; // ??
            return Util::$SP0;
        }
        $endTag = sprintf( $FMT3, $tagName );
        if( false === ( $pos = stripos( $xml, $endTag ))) { // missing end tag??
            $endIx = $xmlLen + 1;
            return Util::$SP0;
        }
        $endIx = $pos + $strLen + 3;
        $start = $sx1 + $strLen + 2;
        $len   = $pos - $sx1 - 2 - $strLen;
        return substr( $xml, $start, $len );
    }

    /**
     * Fetch next (unknown) XML tagname AND content
     *
     * @param string $xml
     * @param string|null $tagName
     * @param int|null $endIx
     * @return string
     * @since  2.23.8 - 2017-04-17
     */
    private static function XMLgetTagContent2( string $xml, ? string & $tagName = null, ? int & $endIx = null ) : string
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
            if( $LT === $xml[$sx1] ) {
                if((( $sx1 + 3 ) < $xmlLen ) &&
                    str_starts_with( substr( $xml, $sx1 ), $CMTSTART )) { // skip comment
                    ++$sx1;
                }
                else {
                    break;
                } // tagname start here
            }
            else {
                ++$sx1;
            }
        } // end while...
        $sx2 = $sx1;
        while( $sx2 < $xmlLen ) {
            if((( $sx2 + 1 ) < $xmlLen ) &&
                str_starts_with( substr( $xml, $sx2 ), $EMPTYTAGEND )) { // tag with no content
                $tagName = trim( substr( $xml, ( $sx1 + 1 ), ( $sx2 - $sx1 - 1 )));
                $endIx   = $sx2 + 2;
                return Util::$SP0;
            }
            if( $GT === $xml[$sx2] ) { // tagname ends here
                break;
            }
            ++$sx2;
        } // end while...
        $tagName = substr( $xml, ( $sx1 + 1 ), ( $sx2 - $sx1 - 1 ));
        $endIx   = $sx2 + 1;
        if( $sx2 >= $xmlLen ) {
            return Util::$SP0;
        }
        $strLen = strlen( $tagName );
        if(( $DURATION === $tagName ) &&
            ( false !== ( $pos1 = stripos( $xml, $DURATIONTAG, $sx1 + 1 ))) &&
            ( false !== ( $pos2 = stripos( $xml, $DURENDTAG,  $pos1 + 1 ))) &&
            ( false !== ( $pos3 = stripos( $xml, $DURENDTAG,  $pos2 + 1 ))) &&
            ( $pos1 < $pos2 ) && ( $pos2 < $pos3 )) {
            $pos = $pos3;
        }
        elseif( false === ( $pos = stripos( $xml, sprintf( $FMTTAG, $tagName ), $sx2 ))) {
            return Util::$SP0;
        }
        $endIx = $pos + $strLen + 3;
        return substr( $xml, ( $sx1 + $strLen + 2 ), ( $pos - $strLen - 2 ));
    }
}
