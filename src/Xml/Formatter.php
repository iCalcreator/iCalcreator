<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2024 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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
use Kigkonsult\Icalcreator\Vcalendar;
use Kigkonsult\Icalcreator\Vevent;
use SimpleXMLElement;

use function array_change_key_case;
use function count;
use function htmlspecialchars;
use function implode;
use function in_array;
use function is_array;
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
 * @since 2.41.88 2024-01-17
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
    private static string $boolean        = 'boolean';

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
        $vCalendarXml = self::addSubChild( $xml, self::$Vcalendar );
        $langCal      = $calendar->getConfig( IcalInterface::LANGUAGE );
        // fix calendar properties
        self::calendarProps2Xml( $calendar, $vCalendarXml, $langCal );
        // prepare to fix components with properties/subComponents
        $componentsXml = self::addSubChild( $vCalendarXml, self::$components );
        // fix component properties
        foreach( $calendar->getComponents() as $component ) {
            $componentChild = self::addSubChild( $componentsXml, strtolower( $component->getCompType()));
            self::componentProps2Xml( $component, $componentChild, $langCal );
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
        $properties = self::addSubChild( $vCalendarXml, self::$properties );
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
            match( strtoupper( $propName )) {
                IcalInterface::UID, IcalInterface::COLOR =>
                    self::addXMLchildText( $properties, $propName, $content ),
                IcalInterface::LAST_MODIFIED =>
                    self::addXMLchildDateTime( $properties, $propName, $content, true ),
                IcalInterface::SOURCE, IcalInterface::URL =>
                    self::addXMLchildUri( $properties, $propName, $content ),
                IcalInterface::REFRESH_INTERVAL =>
                    self::addXMLchildDuration( $properties, $propName, $content ),
                default => null
            }; // end match
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
     * @throws InvalidArgumentException
     * @since 2.41.88 2024-01-16
     */
    private static function componentProps2Xml(
        CalendarComponent|Vevent $component,
        SimpleXMLElement $parentXml,
        bool | string $langCal
    ) : void
    {
        $compName   = $component->getCompType();
        $properties = self::addSubChild( $parentXml, self::$properties );
        $langComp   = $component->getConfig( IcalInterface::LANGUAGE );
        $lang       = ( $langComp ?: $langCal );
        $props      = $component->getConfig( IcalInterface::SETPROPERTYNAMES );
        foreach( $props as $propName ) {
            if( StringFactory::isXprefixed( $propName )) {
                $content = $component->getXprop( $propName, null, true );
                self::addXMLchild( $properties, $content[0], self::$unknown, $content[1] );
                continue;
            }
            $method = Vcalendar::isMultiProp( $propName )
                ? StringFactory::getGetAllMethodName( $propName )
                : StringFactory::getGetMethodName( $propName );
            $data   = $component->{$method}( true );
            match( strtoupper( $propName )) {
                IcalInterface::ATTACH,           // may occur multiple times
                IcalInterface::IMAGE =>
                    self::addXMLchildBinaryUriArr( $properties, $propName, $data ), // array
                IcalInterface::ATTENDEE =>        // may occur multiple times
                    self::addXMLchildCalAddress( $properties, $propName, $data, $langComp ),
                IcalInterface::EXDATE =>
                    self::addXMLchildExdate( $properties, $propName, $data ),
                IcalInterface::FREEBUSY =>
                    self::addXMLchildFreebusy( $properties, $propName, $data ),
                IcalInterface::REQUEST_STATUS =>
                    self::addXMLchildReqStat( $properties, $propName, $data, $langComp, $langCal ),
                IcalInterface::RDATE =>
                    self::addXMLchildRdate( $properties, $propName, $data),
                IcalInterface::DESCRIPTION,   // multiple in VCALENDAR/VJOURNAL, single elsewere
                IcalInterface::LOCATION =>    // multiple in PARTICIPANT, single elsewere
                    self::addXMLchildDescrLoc( $component, $properties, $propName, $data, $lang, $compName ),
                IcalInterface::STYLED_DESCRIPTION =>
                    self::addXMLchildStlDescr( $properties, $propName, $data, $lang ),
                IcalInterface::CATEGORIES, IcalInterface::COMMENT,
                IcalInterface::CONTACT,       //   // single in VFREEBUSY, multiple elsewhere
                IcalInterface::NAME,          // dito, multi i Vcalendar, single in Vlocation/Vresource (here)
                IcalInterface::TZID_ALIAS_OF, IcalInterface::TZNAME,
                IcalInterface::RESOURCES, IcalInterface::RELATED_TO =>
                    self::addXMLchildGroup1( $component, $properties, $propName, $data, $langComp, $langCal, $compName ),
                IcalInterface::ACKNOWLEDGED, IcalInterface::CREATED, IcalInterface::COMPLETED,
                IcalInterface::DTSTAMP, IcalInterface::LAST_MODIFIED,
                IcalInterface::DTSTART, IcalInterface::DTEND, IcalInterface::DUE,
                IcalInterface::RECURRENCE_ID, IcalInterface::TZUNTIL =>
                    self::addXMLchildGroup2( $properties, $propName, $data ),
                IcalInterface::DURATION  =>
                    self::addXMLchildDuration( $properties, $propName, $data ),
                IcalInterface::EXRULE, IcalInterface::RRULE =>
                    self::addXMLchildRecur( $properties, $propName, $data ),
                IcalInterface::LOCATION_TYPE, IcalInterface::SUMMARY, IcalInterface::ACTION,
                IcalInterface::BUSYTYPE, IcalInterface::KLASS, IcalInterface::COLOR,
                IcalInterface::PROXIMITY, IcalInterface::PARTICIPANT_TYPE, IcalInterface::RESOURCE_TYPE,
                IcalInterface::STATUS, IcalInterface::TRANSP, IcalInterface::TZID, IcalInterface::UID =>
                    self::addXMLchildGroup3( $properties, $propName, $data, $langComp, $langCal ),
                IcalInterface::GEO =>
                    self::addXMLchild( $properties, $propName, strtolower( IcalInterface::GEO ), $data ),
                IcalInterface::CALENDAR_ADDRESS, IcalInterface::ORGANIZER =>
                    self::addXMLchildCalAddress( $properties, $propName, [ $data ], $lang ),
                IcalInterface::PERCENT_COMPLETE, IcalInterface::PRIORITY,
                IcalInterface::REPEAT, IcalInterface::SEQUENCE =>
                    self::addXMLchildInteger( $properties, $propName, $data ),
                IcalInterface::STRUCTURED_DATA =>
                    self::addXMLchildStrData( $properties, $propName, $data ),
                IcalInterface::CONFERENCE =>
                    self::addXMLchildConferens( $properties, $propName, $data ),
                IcalInterface::TRIGGER =>
                    self::addXMLchildTrigger( $properties, $propName, $data ),
                IcalInterface::TZOFFSETFROM, IcalInterface::TZOFFSETTO =>
                    self::addXMLchild( $properties, $propName, self::$utc_offset, $data ),
                IcalInterface::TZURL, IcalInterface::URL =>
                    self::addXMLchildUri( $properties, $propName, $data ),
                default => null
            }; // end match( $propName )
        } // end foreach( $props as $pix => $propName )
        // fix subComponent properties, if any
        foreach( $component->getComponents() as $subComp ) {
            $subCompChild =self::addSubChild(  $parentXml, strtolower( $subComp->getCompType()));
            self::componentProps2Xml( $subComp, $subCompChild, $langCal );
        } // end foreach
    }

    /**
     * Manage Exdate
     *
     * @param SimpleXMLElement $properties
     * @param string           $propName
     * @param array            $data
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.88 2024-01-16
     */
    private static function addXMLchildExdate(
        SimpleXMLElement $properties,
        string $propName,
        array $data
    ) : void
    {
        foreach( $data as $content ) {
            if( self::hasValueDateSet( $content )) {
                self::addXMLchildDate( $properties, $propName, $content );
                continue;
            }
            self::addXMLchildDateTime( $properties, $propName, $content );
        } // end foreach
    }

    /**
     * Manage FREEBUSY
     *
     * @param SimpleXMLElement $properties
     * @param string           $propName
     * @param array            $data
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.88 2024-01-16
     */
    private static function addXMLchildFreebusy(
        SimpleXMLElement $properties,
        string $propName,
        array $data
    ) : void
    {
        foreach( $data as $content ) {
            self::addXMLchild( $properties, $propName, self::$period, $content );
        } // end foreach
    }

    /**
     * Manage REQUEST_STATUS
     *
     * @param SimpleXMLElement $properties
     * @param string           $propName
     * @param array            $data
     * @param bool | string $langComp
     * @param bool | string $langCal
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.88 2024-01-16
     */
    private static function addXMLchildReqStat(
        SimpleXMLElement $properties,
        string $propName,
        array $data,
        bool | string $langComp,
        bool | string $langCal
    ) : void
    {
        foreach( $data as $content ) {
            self::addLanguage( $content, $langComp, $langCal );
            self::addXMLchild( $properties, $propName, self::$rstatus, $content );
        } // end foreach
    }

    /**
     * Manage Rdate
     *
     * @param SimpleXMLElement $properties
     * @param string           $propName
     * @param array            $data
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.88 2024-01-16
     */
    private static function addXMLchildRdate(
        SimpleXMLElement $properties,
        string $propName,
        array $data
    ) : void
    {
        foreach( $data as $content ) {
            $type = self::getRdateValueType( $content );
            self::addXMLchild( $properties, $propName, $type, $content );
        } // end foreach
    }

    /**
     * Manage DESCRIPTION+LOCATION
     *
     * @param CalendarComponent|Vevent $component
     * @param SimpleXMLElement $properties
     * @param string      $propName
     * @param array       $data
     * @param bool|string $lang
     * @param string      $compName
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.88 2024-01-16
     */
    private static function addXMLchildDescrLoc(
        CalendarComponent|Vevent $component,
        SimpleXMLElement $properties,
        string $propName,
        array $data,
        bool|string $lang,
        string $compName
    ) : void
    {
        foreach( $data as $content ) {
            self::addXMLchildText( $properties, $propName, $content, $lang );
            if(( IcalInterface::DESCRIPTION === $propName ) &&
                $component::isDescriptionSingleProp( $compName )) {
                break;
            }
            if(( IcalInterface::LOCATION === $propName ) &&
                $component::isLocationSingleProp( $compName )) {
                break;
            }
        } // end foreach
    }

    /**
     * Manage STYLED_DESCRIPTION
     *
     * @param SimpleXMLElement $properties
     * @param string      $propName
     * @param array       $data
     * @param bool|string $lang
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.88 2024-01-16
     */
    private static function addXMLchildStlDescr(
        SimpleXMLElement $properties,
        string $propName,
        array $data,
        bool|string $lang
    ) : void
    {
        foreach( $data as $content ) {
            if( $content->hasParamValue( IcalInterface::URI )) {
                self::addXMLchildUri( $properties, $propName, $content );
                continue;
            }
            self::addXMLchildText( $properties, $propName, $content, $lang );
        } // end foreach
    }

    /**
     * Manage CATEGORIES+COMMENT+CONTACT+NAME+TZID_ALIAS_OF+TZNAME+RESOURCES+RELATED_TO
     *
     * @param CalendarComponent|Vevent $component
     * @param SimpleXMLElement $properties
     * @param string           $propName
     * @param array            $data
     * @param bool | string $langComp
     * @param bool | string $langCal
     * @param string $compName
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.88 2024-01-16
     */
    private static function addXMLchildGroup1(
        CalendarComponent|Vevent $component,
        SimpleXMLElement $properties,
        string $propName,
        array $data,
        bool | string $langComp,
        bool | string $langCal,
        string $compName
    ) : void
    {
        foreach( $data as $content ) {
            if( $propName !== IcalInterface::RELATED_TO ) {
                self::addLanguage( $content, $langComp, $langCal );
            }
            self::addXMLchildText( $properties, $propName, $content );
            if( ( IcalInterface::NAME === $propName ) ||
                ( ( IcalInterface::CONTACT === $propName ) &&
                    $component::isContactSingleProp( $compName ) ) ) {
                break;
            }
        } // end foreach
    }

    /**
     * Manage ACKNOWLEDGED+CREATED+COMPLETED+DTSTAMP+LAST_MODIFIED+DTSTART+DTEND+DUE+RECURRENCE_IDTZUNTIL
     *
     * @param SimpleXMLElement $properties
     * @param string $propName
     * @param Pc     $content
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.88 2024-01-16
     */
    private static function addXMLchildGroup2(
        SimpleXMLElement $properties,
        string $propName,
        Pc $content
    ) : void
    {
        if( self::hasValueDateSet( $content )) {
            self::addXMLchildDate( $properties, $propName, $content );
        }
        else {
            self::addXMLchildDateTime( $properties, $propName, $content );
        }
    }

    /**
     * Manage group3 of properties
     *
     * LOCATION_TYPE+SUMMARY+ACTION+BUSYTYPE+KLASS+COLOR+PROXIMITY+PARTICIPANT_TYPE+
     * RESOURCE_TYPE+STATUS+TRANSP+TZID+UID :
     *
     * @param SimpleXMLElement $properties
     * @param string           $propName
     * @param Pc               $content
     * @param bool | string    $langCal
     * @param bool | string    $langComp
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.88 2024-01-16
     */
    private static function addXMLchildGroup3(
        SimpleXMLElement $properties,
        string $propName,
        Pc $content,
        bool | string $langComp,
        bool | string $langCal
    ) : void
    {
        static $LOCTSUM = [ IcalInterface::LOCATION_TYPE, IcalInterface::SUMMARY ];
        if( in_array( $propName, $LOCTSUM, true )) {
            self::addLanguage( $content, $langComp, $langCal );
        }
        self::addXMLchildText( $properties, $propName, $content );
    }

    /**
     * Manage STRUCTURED_DATA
     *
     * @param SimpleXMLElement $properties
     * @param string           $propName
     * @param array            $data
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.88 2024-01-16
     */
    private static function addXMLchildStrData(
        SimpleXMLElement $properties,
        string $propName,
        array $data
    ) : void
    {
        foreach( $data as $content ) {
            if( $content->hasParamValue( IcalInterface::TEXT )) {
                self::addXMLchildText( $properties, $propName, $content );
                continue;
            }
            self::addXMLchildBinaryUri( $properties, $propName, $content );
        } // end foreach
    }

    /**
     * Manage CONFERENCE
     *
     * @param SimpleXMLElement $properties
     * @param string           $propName
     * @param array            $data
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.88 2024-01-16
     */
    private static function addXMLchildConferens(
        SimpleXMLElement $properties,
        string $propName,
        array $data
    ) : void
    {
        foreach( $data as $content ) {
            self::addXMLchildUri( $properties, $propName, $content );
        }
    }

    /**
     * Manage TRIGGER
     *
     * @param SimpleXMLElement $properties
     * @param string $propName
     * @param Pc     $content
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @since 2.41.88 2024-01-16
     */
    private static function addXMLchildTrigger(
        SimpleXMLElement $properties,
        string $propName,
        Pc $content
    ) : void
    {
        if( $content->getValue() instanceof DateInterval ) {
            self::addXMLchildDuration( $properties, $propName, $content );
        }
        else {
            self::addXMLchildDateTime( $properties, $propName, $content );
        }
    }

    /**
     * Add Pc content parameter language if not set
     *
     * @param Pc $content  property content
     * @param bool|string $langComp
     * @param bool|string $langCal
     * @return void
     * @since 2.41.88 2024-01-17
     */
    private static function addLanguage( Pc $content, bool | string $langComp, bool | string $langCal ) : void
    {
        match( true ) {
            $content->hasParamKey( IcalInterface::LANGUAGE ) => null,
            ( ! empty( $langComp )) =>
                $content->addParam( IcalInterface::LANGUAGE, $langComp ),
            ( ! empty( $langCal )) =>
                $content->addParam( IcalInterface::LANGUAGE, $langCal ),
            default => null,
        }; // end match
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
        if( $content->hasParamValue( IcalInterface::DATE )) {
            $type = self::$date;
        }
        elseif( $content->hasParamValue( IcalInterface::PERIOD )) {
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
    private static function addXMLchildDateTime(
        SimpleXMLElement $parent,
        string $name,
        Pc $content,
        ? bool $skipValue = false
    ) : void
    {
        if( $skipValue ) {
            $content->removeParam(IcalInterface::VALUE );
        }
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
     * @since 2.41.88 - 2024-01-18
     */
    private static function addXMLchildUri( SimpleXMLElement $parent, string $name, Pc $content ) : void
    {
        $content->setValue( htmlentities( $content->getValue()));
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
     * @since 2.41.88 - 2024-01-18
     */
    private static function addXMLchild(
        SimpleXMLElement $parent,
        string $name,
        string $type,
        Pc $subData
    ) : void
    {
        /** create new child node */
        $name  = strtolower( $name );
        $child = self::addSubChild( $parent, $name );
        $value = $subData->getValue();
        if( empty( $value ) && ( StringFactory::$ZERO !== (string) $value )) {
            self::addSubChild( $child, $type );
            return;
        }
        $recurDateIsSet = false;
        $isLocalTime    = $subData->hasParamIsLocalTime();
        $params         = (array) $subData->getParams();
        $isOneParm      = ( 1 === count( $params ));
        switch( true ) {
            case ( empty( $params ) || ( $isOneParm && $isLocalTime )) :
                break;
            case ( self::$recur === $type ) :
                $recurDateIsSet = $subData->hasParamValue( IcalInterface::DATE );
                if( $isOneParm && $subData->hasParamValue()) {
                    break;
                }
                $subData->removeParam( IcalInterface::VALUE );
                // fall through
            default :
                $parameters = self::addSubChild( $child, self::$PARAMETERS );
                self::addXMLchildParams( $parameters, $type, $params );
                break;
        } // end switch
        /** store content dep. on type */
        self::addXMLchildForType( $child, $type, $subData->getValue(), $isLocalTime, $recurDateIsSet );
    }

    /**
     * @param SimpleXMLElement $parameters
     * @param string           $type
     * @param array            $params
     * @return void
     * @throws Exception
     */
    private static function addXMLchildParams( SimpleXMLElement $parameters, string $type, array $params ) : void
    {
        static $altrep       = 'altrep';
        static $dir          = 'dir';
        static $delegated_from = 'delegated-from';
        static $delegated_to = 'delegated-to';
        static $member       = 'member';
        static $order        = 'order';
        static $sent_by      = 'sent-by';
        static $rsvp         = 'rsvp';
        static $derived      = 'derived';
        foreach( $params as $pKey => $parVal ) {
            if( IcalInterface::VALUE === $pKey ) {
                if( str_contains( $parVal, StringFactory::$COLON )) {
                    $p1   = self::addSubChild( $parameters, strtolower( $pKey ));
                    self::addSubChild( $p1, self::$unknown, htmlspecialchars( $parVal ));
                    $type = strtolower( strstr( $parVal, StringFactory::$COLON, true ));
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
                $p1 = self::addSubChild( $parameters, $pKey );
                self::addSubChild( $p1, self::$unknown, htmlspecialchars( $parVal ));
                continue;
            }
            $p1 = self::addSubChild( $parameters, $pKey );
            $ptype = match ( $pKey ) {
                $altrep, $dir   => self::$uri,
                $delegated_from, $delegated_to, $member, $sent_by => self::$cal_address,
                $order          => self::$integer,
                $rsvp, $derived => self::$boolean,
                default         => self::$text,
            }; // end match
            if( is_array( $parVal )) {
                foreach( $parVal as $pV ) {
                    self::addSubChild( $p1, $ptype, htmlspecialchars((string) $pV ));
                }
            }
            else {
                self::addSubChild( $p1, $ptype, htmlspecialchars((string) $parVal ));
            }
        } // end foreach $params
    }

    /**
     * @param SimpleXMLElement $child
     * @param string $type
     * @param mixed $value
     * @param bool $isLocalTime
     * @param bool $recurDateIsSet
     * @return void
     * @throws Exception
     */
    private static function addXMLchildForType(
        SimpleXMLElement $child,
        string $type,
        mixed $value,
        bool $isLocalTime,
        bool $recurDateIsSet
    ) : void
    {
        match( $type ) {
            self::$boolean, self::$time => null,
            self::$binary, self::$uri, self::$cal_address =>
               self::addSubChild( $child, $type, $value ),
            self::$date =>
                self::addXMLchildForTypeDate( $child, $type, $value ),
            self::$date_time =>
                self::addXMLchildForTypeDateTime( $child, $type, $value, $isLocalTime ),
            strtolower( IcalInterface::DURATION ) =>
                self::addSubChild( $child, $type, DateIntervalFactory::dateInterval2String( $value, true )),
            strtolower( IcalInterface::GEO ) =>
                self::addXMLchildForTypeGeo( $child, $value ),
            self::$integer =>
                self::addSubChild( $child, $type, (string) $value ),
            self::$period =>
                self::addXMLchildForTypePeriod( $child, $type, $value, $isLocalTime ),
            self::$recur =>
                self::addXMLchildForTypeRecur( $child, $value, $recurDateIsSet ),
            self::$rstatus =>
                self::addXMLchildForTypeRstatus( $child, $value ),
            self::$text =>
                self::addXMLchildForTypeText( $child, $type, $value ),
            self::$utc_offset =>
                self::addXMLchildForTypeUtcOffset( $child, $type, $value ),
            default => // also self::$unknown
                self::addXMLchildForTypeUnknowDefault( $child, $value ),
        }; // end match
    }

    /**
     * Manage date type
     *
     * @param SimpleXMLElement $child
     * @param string           $type
     * @param mixed            $value
     * @return void
     * @throws Exception
     */
    private static function addXMLchildForTypeDate( SimpleXMLElement $child, string $type, mixed $value ) : void
    {
        if( $value instanceof DateTime ) {
            $value = [ $value ];
        }
        foreach( $value as $date ) {
            self::addSubChild( $child, $type, DateTimeFactory::dateTime2Str( $date, true ));
        }
    }

    /**
     * Manage date-time type
     *
     * @param SimpleXMLElement $child
     * @param string           $type
     * @param mixed            $value
     * @param bool $isLocalTime
     * @return void
     * @throws Exception
     */
    private static function addXMLchildForTypeDateTime(
        SimpleXMLElement $child,
        string $type,
        mixed $value,
        bool $isLocalTime
    ) : void
    {
        if( $value instanceof DateTime ) {
            $value = [ $value ];
        }
        foreach( $value as $dt ) {
            self::addSubChild( $child, $type, DateTimeFactory::dateTime2Str( $dt, false, $isLocalTime ));
        } // end foreach
    }

    /**
     * Manage geo type
     *
     * @param SimpleXMLElement $child
     * @param mixed            $value
     * @return void
     * @throws Exception
     */
    private static function addXMLchildForTypeGeo( SimpleXMLElement $child, mixed $value ) : void
    {
        if( ! empty( $value )) {
            self::addSubChild(
                $child,
                IcalInterface::LATITUDE,
                GeoFactory::geo2str2( $value[IcalInterface::LATITUDE], GeoFactory::$geoLatFmt )
            );
            self::addSubChild(
                $child,
                IcalInterface::LONGITUDE,
                GeoFactory::geo2str2( $value[IcalInterface::LONGITUDE], GeoFactory::$geoLatFmt )
            );
        } // end if
    }

    /**
     * Manage period type
     *
     * @param SimpleXMLElement $child
     * @param string $type
     * @param mixed  $value
     * @param bool   $isLocalTime
     * @return void
     * @throws Exception
     */
    private static function addXMLchildForTypePeriod(
        SimpleXMLElement $child,
        string $type,
        mixed $value,
        bool $isLocalTime
    ) : void
    {
        static $START = 'start';
        static $END   = 'end';
        if( ! is_array( $value )) {
            return;
        }
        foreach( $value as $period ) {
            $v1  = self::addSubChild( $child, $type );
            $str = DateTimeFactory::dateTime2Str( $period[0], false, $isLocalTime );
            self::addSubChild( $v1, $START, $str );
            if( $period[1] instanceof DateInterval ) {
                self::addSubChild(
                    $v1,
                    strtolower( IcalInterface::DURATION ),
                    DateIntervalFactory::dateInterval2String( $period[1] )
                );
            }
            elseif( $period[1] instanceof DateTime ) {
                $str = DateTimeFactory::dateTime2Str( $period[1], false, $isLocalTime );
                self::addSubChild( $v1, $END, $str );
            }
        } // end foreach
    }

    /**
     * Manage recur type
     *
     * @param SimpleXMLElement $child
     * @param mixed  $value
     * @param bool   $recurDateIsSet
     * @return void
     * @throws Exception
     */
    private static function addXMLchildForTypeRecur(
        SimpleXMLElement $child,
        mixed $value,
        bool $recurDateIsSet
    ) : void
    {
        static $UNTIL = 'until';
        $value        = array_change_key_case( $value );
        foreach( $value as $ruleLabel => $ruleValue ) {
            match( $ruleLabel ) {
                $UNTIL =>
                    self::addSubChild( $child, $ruleLabel, DateTimeFactory::dateTime2Str( $ruleValue, $recurDateIsSet )),
                self::$bysecond, self::$byminute, self::$byhour,
                self::$bymonthday, self::$byyearday, self::$byweekno, self::$bymonth, self::$bysetpos =>
                    self::addXMLchildForTypeRecurGroup1( $child, $ruleLabel, $ruleValue ),
                self::$byday =>
                    self::addXMLchildForTypeRecurByDay( $child, $ruleLabel, $ruleValue ),
                default => // self::$freq, self::$count, self::$interval, self::$wkst
                    self::addSubChild( $child, $ruleLabel, (string) $ruleValue ),
            }; // end match( $ruleLabel )
        } // end foreach( $value as $ruleLabel => $ruleValue )
    }

    /**
     * @param SimpleXMLElement $child
     * @param string           $ruleLabel
     * @param mixed            $ruleValue
     * @return void
     * @throws Exception
     */
     private static function addXMLchildForTypeRecurGroup1(
         SimpleXMLElement $child,
         string $ruleLabel,
         mixed $ruleValue
     ) : void
     {
         if( is_array( $ruleValue )) {
             foreach( $ruleValue as $valuePart ) {
                 self::addSubChild( $child, $ruleLabel, (string) $valuePart );
             }
         }
         else {
             self::addSubChild( $child, $ruleLabel, $ruleValue );
         }
    }

    /**
     * @param SimpleXMLElement $child
     * @param string           $ruleLabel
     * @param mixed            $ruleValue
     * @return void
     * @throws Exception
     */
    private static function addXMLchildForTypeRecurByDay(
        SimpleXMLElement $child,
        string $ruleLabel,
        mixed $ruleValue
    ) : void
    {
        if( isset( $ruleValue[IcalInterface::DAY] )) {
            $str  = $ruleValue[0] ?? StringFactory::$SP0;
            $str .= $ruleValue[IcalInterface::DAY];
            self::addSubChild( $child, $ruleLabel, $str );
        }
        else {
            foreach( $ruleValue as $valuePart ) {
                if( isset( $valuePart[IcalInterface::DAY] )) {
                    $str  = $valuePart[0] ?? StringFactory::$SP0;
                    $str .= $valuePart[IcalInterface::DAY];
                    self::addSubChild( $child, $ruleLabel, $str );
                }
                else {
                    self::addSubChild( $child, $ruleLabel, $valuePart );
                }
            } // end foreach
        }
    }

    /**
     * Manage rstatus type
     *
     * @param SimpleXMLElement $child
     * @param mixed            $value
     * @return void
     * @throws Exception
     */
    private static function addXMLchildForTypeRstatus( SimpleXMLElement $child, mixed $value ) : void
    {
        self::addSubChild(
            $child,
            self::$code,
            StringFactory::numberFormat( $value[IcalInterface::STATCODE] )
        );
        self::addSubChild( $child, self::$description, htmlspecialchars( $value[IcalInterface::STATDESC] ));
        if( isset( $value[IcalInterface::EXTDATA] )) {
            self::addSubChild( $child, self::$data, htmlspecialchars( $value[IcalInterface::EXTDATA] ));
        }
    }

    /**
     * Manage text type
     *
     * @param SimpleXMLElement $child
     * @param string $type
     * @param mixed  $value
     * @return void
     * @throws Exception
     */
    private static function addXMLchildForTypeText( SimpleXMLElement $child, string $type, mixed $value ) : void
    {
        if( ! is_array( $value )) {
            $value = [ $value ];
        }
        foreach( $value as $part ) {
            self::addSubChild( $child, $type, htmlspecialchars( $part ) );
        }
    }

    /**
     * Manage utc_offset type
     *
     * @param SimpleXMLElement $child
     * @param string $type
     * @param mixed  $value
     * @return void
     * @throws Exception
     */
    private static function addXMLchildForTypeUtcOffset( SimpleXMLElement $child, string $type, mixed $value ) : void
    {
        if( DateIntervalFactory::hasPlusMinusPrefix( $value )) {
            $str   = $value[0];
            $value = substr( $value, 1 );
        }
        else {
            $str = StringFactory::$PLUS;
        }
        $str .= substr( $value, 0, 2 ) .
            StringFactory::$COLON . substr( $value, 2, 2 );
        if( 4 < strlen( $value )) {
            $str .= StringFactory::$COLON . substr( $value, 4 );
        }
        self::addSubChild( $child, $type, $str );
    }

    /**
     * Manage unknown and default type
     *
     * @param SimpleXMLElement $child
     * @param mixed  $value
     * @return void
     * @throws Exception
     */
    private static function addXMLchildForTypeUnknowDefault( SimpleXMLElement $child, mixed $value ) : void
    {
        if( is_array( $value )) {
            $value = implode( $value );
        }
        self::addSubChild( $child, self::$unknown, htmlspecialchars( $value ));
    }

    /**
     * @param SimpleXMLElement $child
     * @param string           $name
     * @param null|string      $value
     * @return SimpleXMLElement
     * @throws Exception
     */
    private static function addSubChild( SimpleXMLElement $child, string $name, ? string $value = null ) : SimpleXMLElement
    {
        $subChild = $child->addChild( $name, $value );
        if( null === $subChild ) {
            throw new Exception( 'Can\'t add XML child ' . $name );
        }
        return $subChild;
    }
}
