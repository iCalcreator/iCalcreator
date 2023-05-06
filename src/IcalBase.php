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
namespace Kigkonsult\Icalcreator;

use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use Kigkonsult\Icalcreator\Util\DateTimeFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\StringFactory;

use function define;
use function defined;
use function array_change_key_case;
use function array_keys;
use function array_slice;
use function count;
use function ctype_digit;
use function get_object_vars;
use function in_array;
use function is_array;
use function is_object;
use function key;
use function ksort;
use function property_exists;
use function sprintf;
use function strtolower;
use function strtoupper;
use function trim;
use function ucfirst;

/**
 *         Do NOT alter or remove the constant!!
 */
if( ! defined( 'ICALCREATOR_VERSION' )) {
    define( 'ICALCREATOR_VERSION', 'iCalcreator 2.41.76' );
}

/**
 * iCalcreator base class
 *
 * Properties and methods shared by Vcalendar and CalendarComponents
 *
 * @since 2.41.68 2022-10-03
 */
abstract class IcalBase implements IcalInterface
{
    /**
     * @var string
     */
    protected static string $INDEX = 'INDEX';

    /**
     * @var string
     */
    protected static string $SP0 = '';

    /**
     * @var string[]  iCal V*-component collection, subcomps to Vclendar
     */
    public static array $VCOMPS   = [
        self::VAVAILABILITY,
        self::VEVENT,
        self::VTODO,
        self::VJOURNAL,
        self::VFREEBUSY
    ];

    /**
     * Return bool true if compName is one of VAVAILABILITY, VEVENT, VTODO, VJOURNAL, VFREEBUSY
     *
     * @param string $compName
     * @return bool
     * @since 2.41.68 2022-10-03
     * @usedby IcalBase,Vcalendar,SelectFactory
     */
    public static function isVcalendarVcomp( string $compName ) : bool
    {
        return in_array( $compName, self::$VCOMPS, true );
    }

    /**
     * iCal timezone component collection
     *
     * @param null|string $compName
     * @return bool
     * @usedby DTSTARTtrait,Rdatetrait,RexdateFactory
     * @since 2.41.68 2022-10-03
     */
    public static function isTzComp( ? string $compName ) : bool
    {
        static $TZCOMPS = [
            self::VTIMEZONE,
            self::STANDARD,
            self::DAYLIGHT
        ];
        return in_array( $compName, $TZCOMPS, true );
    }
    /**
     * Return bool true if compName is a ICal component (all but Vtimzones subcombs)
     *
     * On update here, upd also IcalXMLFactory::XMLgetComps $ALLCOMPS list
     *
     * @param string $compName
     * @return bool
     * @since 2.41.68 2022-10-03
     */
    protected static function isCalendarSubComp( string $compName ) : bool
    {
        static $CALCOMPS = [
            self::AVAILABLE,
            self::PARTICIPANT,
            self::VALARM,
            self::VAVAILABILITY,
            self::VEVENT,
            self::VFREEBUSY,
            self::VJOURNAL,
            self::VLOCATION,
            self::VRESOURCE,
            self::VTIMEZONE,
            self::VTODO,
        ];
        return in_array( $compName, $CALCOMPS, true );
    }

    /**
     * Return bool true if component has the UID property
     * @param IcalBase $component
     * @return bool
     * @since 2.47.68 2022-10-01
     */
    protected static function hasUid( IcalBase $component ) : bool
    {
        static $UIDCOMPS = [
            self::AVAILABLE,
            self::PARTICIPANT,
            self::VALARM,
            self::VAVAILABILITY,
            Vcalendar::VCALENDAR,
            self::VEVENT,
            self::VFREEBUSY,
            self::VJOURNAL,
            self::VLOCATION,
            self::VRESOURCE,
            self::VTODO,
        ];
        return in_array( $component->getCompType(), $UIDCOMPS, true );
    }

    /**
     * Return bool true if prop is a iCal component multiple 'text property

     * @param string $propName
     * @return bool
     * @since 2.41.68 2022-10-03
     * @usedby IcalBase::isFoundInCompsProps, CalendarComponent::getProperties, Vcalendar:.getProperty,
     *         SelectFactory::selectComponents2, SortFactory::setSortArgs
     */
    public static function isMultiProp1( string $propName ) : bool
    {
        static $MPROPS1 = [
            self::ATTENDEE, self::CATEGORIES, self::CONTACT,
            self::RELATED_TO, self::RESOURCES,
        ];
        return in_array( $propName, $MPROPS1, true );
    }

    /**
     * Return bool true if prop is a iCal component multiple property
     *
     * @param string $propName
     * @return bool
     * @since 2.41.68 2022-10-03
     * @usedby Icalbase::getpropInfo(), IcalXMLFactory::compProps2Xml()
     */
    public static function isMultiProp( string $propName ) : bool
    {
        static $MPROPS2 = [
            self::ATTACH, self::ATTENDEE, self::CATEGORIES,
            self::COMMENT, self::CONFERENCE, self::CONTACT, self::DESCRIPTION,
            self::EXDATE, self::FREEBUSY, self::IMAGE, self::LOCATION, self::NAME,
            self::RDATE, self::RELATED_TO, self::REQUEST_STATUS, self::RESOURCES,
            self::STRUCTURED_DATA, self::STYLED_DESCRIPTION,
            self::TZID_ALIAS_OF, self::TZNAME, self::X_PROP,
        ];
        return in_array( $propName, $MPROPS2, true );
    }

    /**
     * Return bool true if prop is a iCal component date-property
     *
     * @param string $propName
     * @return bool
     * @since 2.41.68 2022-10-03
     * @uedby self::isFoundInCompsProps()
     */
    protected static function isDateProp( string $propName ) : bool
    {
        static $DATEPROPS = [
            self::ACKNOWLEDGED, self::COMPLETED, self::CREATED,
            self::DTEND, self::DTSTAMP, self::DTSTART, self::DUE,
            self::LAST_MODIFIED, self::RECURRENCE_ID, self::TZUNTIL
        ];
        return in_array( $propName, $DATEPROPS, true );
    }

    /**
     * @var CalendarComponent[] container for sub-components
     */
    protected array $components = [];

    /**
     * @var array $config configuration with defaults
     */
    protected array $config = [
        self::ALLOWEMPTY => true,
    ];

    /**
     * @var string component type
     */
    protected string $compType;

    /**
     * @var int[] component index
     */
    public array $compix = [];

    /**
     * @var array<string, int> get multi-property index
     */
    protected $propIx = [];

    /**
     * @var array<string, int> delete multi-property index
     */
    protected $propDelIx = [];

    /**
     * X-prefixed properties
     */
    use Traits\X_PROPtrait;

    /**
     * __clone method
     *
     * @link https://php.net/manual/en/language.oop5.cloning.php#116329
     * @since  2.26 - 2018-11-10
     */
    public function __clone()
    {
        $object_vars = get_object_vars( $this );
        foreach( $object_vars as $attr_name => $attr_value ) {
            if( is_object( $this->$attr_name )) {
                $this->$attr_name = clone $this->$attr_name;
            }
            else if( is_array( $this->$attr_name )) {
                // Note: This copies only one dimension arrays
                foreach( $this->$attr_name as & $attr_array_value ) {
                    if( is_object( $attr_array_value )) {
                        $attr_array_value = clone $attr_array_value;
                    }
                    unset( $attr_array_value );
                }
                unset( $attr_array_value );
            }// end else
        } // end foreach
        $this->compix    = [];
        $this->propIx    = [];
        $this->propDelIx = [];
    }

    /**
     * Reset all internal compnent counter
     *
     * @return static
     * @since  2.27.14 - 2019-03-11
     * @deprecated
     */
    public function reset() : static
    {
        $this->compix = [];
        return $this;
    }

    /**
     * Reset all internal compnent counter
     *
     * @return static
     * @since  2.27.14 - 2019-03-11
     */
    public function resetCompCounter() : static
    {
        $this->compix = [];
        return $this;
    }

    /**
     * Return component type
     *
     * @return string
     * @since  2.27.2 - 2018-12-21
     */
    public function getCompType() : string
    {
        return $this->compType;
    }

    /**
     * Remove Vcalendar/component config key value
     *
     * @param string $key
     * @return static
     * @since  2.27.14 - 2019-02-04
     */
    public function deleteConfig( string $key ) : static
    {
        $key = strtoupper( $key );
        if( isset( $this->config[$key] )) {
            unset( $this->config[$key] );
        }
        return $this;
    }

    /**
     * Return Vcalendar/component config value, false on not found
     *
     * @param null|string $config
     * @return mixed   bool false on not found or empty
     * @since 2.47.68 2022-10-01
     */
    public function getConfig( ? string $config = null ) : mixed
    {
        static $LCORDNO = 'ordno';
        static $LCTYPE  = 'type';
        static $LCUID   = 'uid';
        static $LCPROPS = 'props';
        static $LCSUB   = 'sub';
        if( ! isset( $this->config[self::ALLOWEMPTY] )) { // default
            $this->config[self::ALLOWEMPTY] = true;
        }
        if( ! isset( $this->config[self::UNIQUE_ID] )) {
            $this->config[self::UNIQUE_ID] = self::$SP0;
        }
        if( empty( $config )) {
            $output                      = [];
            $output[self::ALLOWEMPTY]    = $this->getConfig( self::ALLOWEMPTY );
            if( false !== ( $cfg = $this->getConfig( self::LANGUAGE ))) {
                $output[self::LANGUAGE]  = $cfg;
            }
            $output[self::UNIQUE_ID]     = $this->getConfig( self::UNIQUE_ID );
            return $output;
        }
        switch( strtoupper( $config )) {
            case self::ALLOWEMPTY:
                return $this->config[self::ALLOWEMPTY];
            case self::UNIQUE_ID:
                return $this->config[self::UNIQUE_ID];
            case self::LANGUAGE: // get language for calendar component as defined in [RFC 1766]
                if( isset( $this->config[self::LANGUAGE] )) {
                    return $this->config[self::LANGUAGE];
                }
                break;
            case self::COMPSINFO:
                $this->compix = [];
                $info = [];
                if( ! empty( $this->components )) {
                    foreach( array_keys( $this->components ) as $cix ) {
                        if( empty( $this->components[$cix] )) {
                            continue;
                        }
                        $info[$cix][$LCORDNO] = $cix + 1;
                        $info[$cix][$LCTYPE]  = $this->components[$cix]->getCompType();
                        if( self::hasUid( $this->components[$cix] )) {
                            $info[$cix][$LCUID] = $this->components[$cix]->getUid();
                        }
                        $info[$cix][$LCPROPS] = $this->components[$cix]->getConfig( self::PROPINFO );
                        $info[$cix][$LCSUB]   = $this->components[$cix]->getConfig( self::COMPSINFO );
                    } // end foreach
                } // end if
                return $info;
            case self::PROPINFO:
                return $this->getpropInfo();
            case self::SETPROPERTYNAMES:
                return array_keys( $this->getConfig( self::PROPINFO ));
            default :
                break;
        } // end switch
        return false;
    }

    /**
     * Return array( propertyName => count )
     *
     * @return array
     * @since 2.41.51 2022-08-09
     */
    protected function getpropInfo() : array
    {
        static $PROPNAMES  = [
            self::ACKNOWLEDGED, self::ACTION, self::ATTACH, self::ATTENDEE,
            self::BUSYTYPE, self::CALENDAR_ADDRESS, self::CATEGORIES,
            self::KLASS, self::COLOR, self::COMMENT,
            self::COMPLETED, self::CONFERENCE, self::CONTACT,
            self::CREATED, self::DESCRIPTION, self::DTEND, self::DTSTAMP,
            self::DTSTART, self::DUE, self::DURATION, self::EXDATE, self::EXRULE,
            self::FREEBUSY, self::GEO, self::IMAGE,
            self::LAST_MODIFIED, self::LOCATION, self::LOCATION_TYPE, self::NAME,
            self::ORGANIZER, self::PARTICIPANT_TYPE, self::PERCENT_COMPLETE, self::PRIORITY, self::PROXIMITY,
            self::RDATE, self::RECURRENCE_ID, self::REFRESH_INTERVAL, self::RELATED_TO, self::REPEAT,
            self::REQUEST_STATUS, self::RESOURCE_TYPE, self::RESOURCES, self::RRULE,
            self::SEQUENCE, self::SOURCE, self::STATUS, self::STRUCTURED_DATA, self::STYLED_DESCRIPTION,
            self::SUMMARY, self::TRANSP,
            self::TRIGGER, self::TZID, self::TZID_ALIAS_OF, self::TZNAME, self::TZUNTIL,
            self::TZOFFSETFROM, self::TZOFFSETTO, self::TZURL, self::UID, self::URL, self::X_PROP,
        ];
        $output = [];
        foreach( $PROPNAMES as $propName ) {
            $propName2 = StringFactory::getInternalPropName( $propName );
            switch( true ) {
                case ( ! property_exists( $this, $propName2 )) :
                    break;
                case empty( $this->{$propName2} ) :
                    break;
                case ( self::X_PROP === $propName ) :
                    foreach( array_keys( $this->{$propName2}) as $propName3 ) {
                        $output[$propName3] = 1;
                    }
                    break;
                case self::isMultiProp( $propName ) :
                    $output[$propName] = count( $this->{$propName2} );
                    break;
                default :
                    $output[$propName] = 1;
            } // end switch
        } // end foreach
        return $output;
    }

    /**
     * Set Vcalendar/component config
     *
     * @param string|string[]        $config
     * @param null|bool|string|array $value
     * @param bool                   $softUpdate
     * @return static
     * @throws InvalidArgumentException
     * @since  2.40.11 - 2022-01-25
     */
    public function setConfig(
        string|array $config,
        null|bool|string|array $value = null,
        ? bool $softUpdate = false
    ) : static
    {
        static $ERRMSG9 = 'Invalid config value %s';
        if( is_array( $config )) {
            $config = array_change_key_case( $config, CASE_UPPER );
            foreach( $config as $cKey => $cValue ) {
                $this->setConfig( $cKey, $cValue );
            }
            return $this;
        }
        $prodIdPropName = StringFactory::getInternalPropName( self::PRODID );
        $key    = strtoupper( $config );
        $subCfg = null;
        switch( true ) {
            case ( self::ALLOWEMPTY === $key ) :
                $this->config[self::ALLOWEMPTY] = $value;
                $subCfg = [ self::ALLOWEMPTY => $value ];
                break;
            case ( self::LANGUAGE === $key ) :
                // set language for calendar component as defined in [RFC 1766]
                $value  = trim((string) $value );
                if( empty( $this->config[self::LANGUAGE] ) || ! $softUpdate ) { // ??
                    $this->config[self::LANGUAGE] = $value;
                }
                $subCfg = [ self::LANGUAGE => $value ];
                break;
            case ( self::UNIQUE_ID === $key ) :
                $value  = trim((string) $value );
                $this->config[self::UNIQUE_ID] = $value;
                $subCfg = [ self::UNIQUE_ID => $value ];
                break;
            case ( ! property_exists( $this, $prodIdPropName )) : // no component
                break;
            default:  // any invalid config key.. .
                throw new InvalidArgumentException( sprintf( $ERRMSG9, $config ));
        } // end switch
        if( ! empty( $subCfg ) && ! empty( $this->components )) {
            foreach( $subCfg as $cfgkey => $cfgValue ) {
                foreach( array_keys( $this->components ) as $cix ) {
                    $this->components[$cix]->setConfig( $cfgkey, $cfgValue, true );
                }
            }
        }
        return $this;
    }

    /**
     * Assert empty value
     *
     * @param mixed  $value
     * @param string $propName
     * @return void
     * @throws InvalidArgumentException
     * @since  2.27.1 - 2018-12-12
     */
    protected function assertEmptyValue( mixed $value, string $propName ) : void
    {
        static $ERRMSG = 'Empty %s value not allowed';
        if( empty( $value ) && ! $this->getConfig( self::ALLOWEMPTY )) {
            throw new InvalidArgumentException( sprintf( $ERRMSG, $propName ));
        }
    }

    /**
     * Return number of components
     *
     * @return int
     * @since  2.40.11 - 2022-01-25
     */
    public function countComponents() : int
    {
        return count( $this->components );
    }

    /**
     * Return next component index
     *
     * @return int
     * @since  2.27.2 - 2018-12-21
     */
    protected function getNextComponentIndex() : int
    {
        return ( empty( $this->components ))
            ? 0
            : (int) key( array_slice( $this->components, -1, 1, true )) + 1;
    }

    /**
     * Return array of CalendarComponents
     *
     * @param string|null $compType
     * @return CalendarComponent[]
     * @since 2.41.50 - 2022-08-06
     */
    public function getComponents( ? string $compType = null ) : array
    {
        if( null === $compType ) {
            return $this->components;
        }
        $output = [];
        foreach( $this->components as $component ) {
            if( $compType === $component->getCompType()) {
                $output[] = $component;
            }
        }
        return $output;
    }

    /**
     * Return clone of calendar component or component subComponent
     *
     * @param null|int|string|string[] $arg1 ordno/component type/component uid, array[ *[propertyName => propertyValue] ]
     * @param null|int        $arg2 ordno if arg1 = component type
     * @return bool|CalendarComponent  (false on error)
     * @since 2.41.68 2022-10-03
     * @todo throw InvalidArgumentException on unknown component
     */
    public function getComponent(
        null|int|string|array $arg1 = null,
        null|int $arg2 = null
    ) : bool|CalendarComponent
    {
        if( empty( $this->components )) {
            $this->compix = [];
            return false;
        }
        $index   = -1;
        $argType = null;
        switch( true ) {
            case empty( $arg1 ) : // first or next in component chain
                $argType = self::$INDEX;
                $index   = $this->getCompixIndex( self::$INDEX );
                break;
            case is_array( $arg1 ) : // [ *[propertyName => propertyValue] ]
                $key   = implode( Util::$MINUS, array_keys( $arg1 ));
                $index = $this->getCompixIndex( $key );
                break;
            case ctype_digit((string) $arg1 ) : // specific component in chain
                $argType      = self::$INDEX;
                $index        = (int) $arg1;
                $this->compix = [];
                break;
            case self::isCalendarSubComp((string) $arg1 ) : // all but Vtimzones subcombs
                $argType = $arg1;
                $index   = $this->getCompixIndex( $arg1, $arg2 );
                break;
            case ( ! self::hasUid( $this )) : // comps without uid
                $arg1    = null;
                $argType = self::$INDEX;
                $index   = $this->getCompixIndex( self::$INDEX );
                break;
            case is_string( $arg1 ) : // assume UID as 1st argument
                $index = $this->getCompixIndex( $arg1, $arg2 );
                break;
        } // end switch( true )
        if( 0 < $index ) {
            --$index;
        }
        $cKeys = array_keys( $this->components );
        if(( self::$INDEX === $argType ) &&
            ( $index > end( $cKeys ))) {
            $this->compix = [];
            return false;
        }
        $cix1gC = 0;
        foreach( $cKeys as $cix ) {
            switch( true ) {
                case  empty( $this->components[$cix] ) :
                    break;
                case (( self::$INDEX === $argType ) && ( $index === $cix )) :
                    return clone $this->components[$cix];
                case ( ! empty( $argType ) &&
                    ( 0 === strcasecmp((string) $argType, $this->components[$cix]->getCompType()))) :
                    if( $index === $cix1gC ) {
                        return clone $this->components[$cix];
                    }
                    ++$cix1gC;
                    break;
                case is_array( $arg1 ) : // [ *[propertyName => propertyValue] ]
                    if( self::isFoundInCompsProps( $this->components[$cix], $arg1 )) {
                        if( $index === $cix1gC ) {
                            return clone $this->components[$cix];
                        }
                        ++$cix1gC;
                    }
                    break;
                case ( ! $argType && ( $arg1 === $this->components[$cix]->getUid())) :
                    if( $index === $cix1gC ) {
                        return clone $this->components[$cix];
                    }
                    ++$cix1gC;
                    break;
            } // end switch
        } // end foreach( $cKeys as $cix )
        /* not found.. . */
        $this->compix = [];
        return false;
    }

    /**
     * @param string $arg1
     * @param null|int|string $arg2
     * @return int
     * @since 2.41.62 2022-09-26
     */
    protected function getCompixIndex( string $arg1, null|int|string $arg2 = null ) : int
    {
        if( null === $arg2 ) {
            if( isset( $this->compix[$arg1] )) {
                ++$this->compix[$arg1];
            }
            else {
                $this->compix[$arg1] = 1;
            }
            return $this->compix[$arg1];
        } // end if
        if( ctype_digit((string) $arg2 )) {
            return (int) $arg2;
        }
        return -1;
    }

    /**
     * Return bool true on argList values found in any component property
     *
     * @param CalendarComponent $component
     * @param string[]          $argList
     * @return bool
     * @since  2.47.76 - 2023-04-29
     */
    protected static function isFoundInCompsProps(
        CalendarComponent $component,
        array $argList
    ) : bool
    {
        foreach( $argList as $propName => $propValue ) {
            if( in_array( $propName, [ self::ATTENDEE, self::CONTACT, self::ORGANIZER ], true ) ) {
                $propValue = CalAddressFactory::conformCalAddress( $propValue );
            }
            switch( true ) {
                case ( ! self::isDateProp( $propName ) &&
                    ! Vcalendar::isSelectSortProp( $propName )) :
                    continue 2;
                case ( ! property_exists( $component, StringFactory::getInternalPropName( $propName ))) :
                    continue 2;
                case self::isMultiProp1( $propName ) : // 'text' multiple occurrence
                    $propValues = [];
                    $component->getProperties( $propName, $propValues );
                    if( array_key_exists( $propValue, $propValues )) {
                        return true;
                    }
                    continue 2;
            } // end switch
            $method = StringFactory::getGetMethodName( $propName );
            if( ! method_exists( $component, $method )) {
                continue;
            }
            if( false === ( $value = $component->{$method}())) { // single occurrence
                continue; // missing/empty property
            }
            switch( true ) {
                case ( self::SUMMARY === $propName ) : // exists in (any case)
                    if( false !== stripos( $value, $propValue )) {
                        return true;
                    }
                    continue 2;
                case ( self::isDateProp( $propName )) :
                    $fmt       = ( 9 > strlen( $propValue ))
                        ? DateTimeFactory::$Ymd
                        : DateTimeFactory::$YmdHis;
                    $valueDate = $value->format( $fmt );
                    if( $propValue === $valueDate ) {
                        return true;
                    }
                    continue 2;
                case ! is_array( $value ) :
                    $value = [ $value ];
                    break;
            } // end switch
            foreach( $value as $part ) {
                $part = ( is_string( $part ) && ( str_contains( $part, Util::$COMMA )))
                    ? explode( Util::$COMMA, $part )
                    : [ $part ];
                if( in_array( $propValue, $part )) {  // note no true
                    return true;
                }
            } // end foreach( $value as $part )
        } // end  foreach( $arg1 as $propName => $propValue )
        return false;
    }

    /**
     * Add calendar component to container for subcomponents
     *
     * @param CalendarComponent $component
     * @param null|int|string   $arg1      ordno/component type/ component uid
     * @param null|int          $arg2      ordno if arg1 = component type
     * @throws InvalidArgumentException
     * @return static
     * @since 2.41.68 2022-10-03
     */
    public function setComponent(
        CalendarComponent $component,
        null|int|string $arg1 = null,
        null|int $arg2 = null
    ) : static
    {
        $component->setConfig( $this->getConfig(), false, true );
        if( self::isTzComp( $component->getCompType())) {
            array_unshift( $this->components, clone $component );
            return $this;
        }
        if( null === $arg1 ) { // plain insert, last in chain
            self::assertComponents( $this, $component );
            $this->components[] = clone $component;
            return $this;
        }
        $argType = $index = null;
        if( ctype_digit((string) $arg1 )) { // index insert/replace
            $argType = self::$INDEX;
            $index   = (int) $arg1 - 1;
        }
        elseif( self::isCalendarSubComp((string) $arg1 )) { // all but Vtimzones subcombs
            $argType = ucfirst( strtolower((string) $arg1 ));
            $index   = ( ctype_digit((string) $arg2 )) ? ((int) $arg2 ) - 1 : 0;
        }
        // else if arg1 is set, arg1 must be an UID
        $cix2sC = 0;
        foreach( array_keys( $this->components ) as $cix ) {
            if( empty( $this->components[$cix] )) {
                continue;
            }
            if(( self::$INDEX === $argType ) && ( $index === $cix )) {
                // index insert/replace
                $this->components[$cix] = clone $component;
                return $this;
            }
            if( $argType === $this->components[$cix]->getCompType()) {
                // component Type index insert/replace
                if( $index === $cix2sC ) {
                    $this->components[$cix] = clone $component;
                    return $this;
                }
                $cix2sC++;
            }
            elseif( ! $argType &&
                self::hasUid( $this->components[$cix] ) &&
                ( $arg1 === $this->components[$cix]->getUid())) { // UID insert/replace
                $this->components[$cix] = clone $component;
                return $this;
            }
        } // end foreach
        if( self::$INDEX === $argType ) { // arg1=index and not found.. . insert at index .. .
            $this->components[$index] = clone $component;
            ksort( $this->components, SORT_NUMERIC );
        }
        else {   /* not found.. . insert last in chain anyway .. .*/
            $this->components[] = clone $component;
        }
        return $this;
    }

    /**
     * Delete calendar subcomponent from component container
     *
     * @param int|string $arg1 ordno / component type / component uid
     * @param null|int   $arg2 ordno if arg1 = component type
     * @return bool  true on success, false on not found (last one deleted)
     * @since 2.41.68 2022-10-03
     */
    public function deleteComponent( int|string $arg1, null|int $arg2 = null ) : bool
    {
        if( ! isset( $this->components )) {
            return false;
        }
        $argType = $index = null;
        if( ctype_digit((string) $arg1 )) {
            $argType = self::$INDEX;
            $index   = (int)$arg1 - 1;
        }
        elseif( property_exists( $this, StringFactory::getInternalPropName( self::PRODID ))) {
            $cmpArg = ucfirst( strtolower((string) $arg1 ));
            if( self::isVcalendarVcomp( $cmpArg ) || // subcomps to Vcalendar
                ( 0 === strcasecmp( $cmpArg, self::VTIMEZONE ))) {
                $argType = $cmpArg;
                $index   = ( $arg2 !== null && ctype_digit((string) $arg2 ))
                    ? ( $arg2 - 1 )
                    : 0;
            }
        } // end elseif
        $cix2dC = 0;
        $remove = false;
        foreach( array_keys( $this->components ) as $cix ) {
            if(( self::$INDEX === $argType ) && ( $index === $cix )) {
                unset( $this->components[$cix] );
                $remove = true;
                break;
            }
            if( $argType === $this->components[$cix]->getCompType()) {
                if( $index === $cix2dC ) {
                    unset( $this->components[$cix] );
                    $argType = strtolower( $argType );
                    if( isset( $this->compix[$argType] )) {
                        unset( $this->compix[$argType] );
                    }
                    $remove = true;
                    break;
                }
                $cix2dC++;
            }
            elseif( ! $argType &&
                self::hasUid( $this->components[$cix] ) &&
                ( $arg1 === $this->components[$cix]->getUid())) {
                unset( $this->components[$cix] );
                $remove = true;
                break;
            }
        } // end foreach( $this->components as $cix => $component )
        if( $remove ) {
            $this->components = array_filter( $this->components );
            return true;
        }
        return false;
    }

    /**
     * Assert components parent/child
     *
     * @param IcalBase $comp
     * @param CalendarComponent $subComp
     * @return void
     * @throws InvalidArgumentException
     * @since  2.47.68 - 2022-10-03
     */
    protected static function assertComponents(
        IcalBase $comp,
        CalendarComponent $subComp
    ) : void
    {
        static $MSG = 'Unknown component %s / %s';
        static $SUBS = [
            self::PARTICIPANT      => [ self::VLOCATION, self::VRESOURCE ],
            self::VAVAILABILITY    => [ self::AVAILABLE ],
            self::VALARM           => [ self::VLOCATION ],
            self::VEVENT           => [ self::PARTICIPANT, self::VALARM, self::VLOCATION, self::VRESOURCE ],
            self::VTIMEZONE        => [ self::DAYLIGHT, self::STANDARD ],
            self::VTODO            => [ self::PARTICIPANT, self::VALARM, self::VLOCATION, self::VRESOURCE ],
            self::VFREEBUSY        => [ self::VLOCATION, self::VRESOURCE ],
            self::VJOURNAL         => [ self::VLOCATION, self::VRESOURCE ],
        ];
        $type    = $comp->getCompType();
        $subType = $subComp->getCompType();
        if(( Vcalendar::VCALENDAR === $type ) &&
            (( self::VTIMEZONE === $subType ) || self::isVcalendarVcomp( $subType ))) {
            return;
        }
        if(( ! isset( $SUBS[$type] ) || ! in_array( $subType, $SUBS[$type], true ))) {
            throw new InvalidArgumentException( sprintf( $MSG, $type, $subType ));
        }
    }
}
