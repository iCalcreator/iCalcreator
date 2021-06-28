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
declare( strict_types = 1 );
namespace Kigkonsult\Icalcreator;

use InvalidArgumentException;
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
    define( 'ICALCREATOR_VERSION', 'iCalcreator 2.39.1' );
}

/**
 * iCalcreator base class
 *
 * Properties and methods shared by Vcalendar and CalendarComponents
 *
 * @since  2.39.1 - 2021-06-26
 */
abstract class IcalBase implements IcalInterface
{
    use Traits\X_PROPtrait;

    /**
     * @var string
     */
    protected static $INDEX = 'INDEX';

    /**
     * @var array  iCal V*-component collection
     * @usedby IcalBase+Vcalendar+SelectFactory
     */
    public static $VCOMPS   = [
        Vcalendar::VEVENT,
        Vcalendar::VTODO,
        Vcalendar::VJOURNAL,
        Vcalendar::VFREEBUSY
    ];

    /**
     * @var array  iCal timezone component collection
     * @usedby DTSTARTtrait+RexdateFactory
     */
    public static $TZCOMPS  = [
        Vcalendar::VTIMEZONE,
        Vcalendar::STANDARD,
        Vcalendar::DAYLIGHT
    ];

    /**
     * @var array  iCal component collection
     */
    protected static $CALCOMPS = [
        Vcalendar::VEVENT,
        Vcalendar::VTODO,
        Vcalendar::VJOURNAL,
        Vcalendar::VFREEBUSY,
        Vcalendar::VALARM,
        Vcalendar::VTIMEZONE
    ];

    /**
     * @var array  iCal sub-component collection
     * @usedby CalendarComponent+IcalBase+DTSTAMPtrait
     */
    protected static $SUBCOMPS = [
        Vcalendar::VALARM,
        Vcalendar::VTIMEZONE,
        Vcalendar::STANDARD,
        Vcalendar::DAYLIGHT
    ];

    /**
     * @var array  iCal component multiple property sub-collection
     * @usedby CalendarComponent + Vcalendar + SelectFactory + SortFactory
     */
    public static $MPROPS1    = [
        Vcalendar::ATTENDEE, Vcalendar::CATEGORIES, Vcalendar::CONTACT,
        Vcalendar::RELATED_TO, Vcalendar::RESOURCES,
    ];

    /**
     * @var array  iCal component multiple property collection
     * @since  2.27.20 - 2019-05-20
     * @usedby IcalBase
     */
    protected static $MPROPS2    = [
        Vcalendar::ATTACH, Vcalendar::ATTENDEE, Vcalendar::CATEGORIES,
        Vcalendar::COMMENT, Vcalendar::CONTACT, Vcalendar::DESCRIPTION,
        Vcalendar::EXDATE, Vcalendar::FREEBUSY, Vcalendar::RDATE,
        Vcalendar::RELATED_TO, Vcalendar::RESOURCES,
        Vcalendar::REQUEST_STATUS, Vcalendar::TZNAME, Vcalendar::X_PROP,
    ];

    /**
     * @var array  iCal component misc. property collection
     * @usedby Vcalendar + SelectFactory
     */
    public static $OTHERPROPS = [
        Vcalendar::ATTENDEE, Vcalendar::CATEGORIES, Vcalendar::CONTACT, Vcalendar::LOCATION,
        Vcalendar::ORGANIZER, Vcalendar::PRIORITY, Vcalendar::RELATED_TO, Vcalendar::RESOURCES,
        Vcalendar::STATUS, Vcalendar::SUMMARY, Vcalendar::UID, Vcalendar::URL,
    ];

    /**
     * @var array  iCal component TEXT properties
     * @usedby Vcalendar + CalendarComponent
     */
    protected static $TEXTPROPS = [
        self::CATEGORIES,
        self::COLOR,
        self::COMMENT,
        self::DESCRIPTION,
        self::NAME,
        self::SUMMARY,
    ];

    /**
     * @var string
     */
    protected static $FMTERRPROPFMT = 'Invalid %s input format (%s)';

    /**
     * @var array
     */
    protected static $ALTRPLANGARR  = [ self::ALTREP, self::LANGUAGE ];

    /**
     * @var array container for sub-components
     */
    protected $components = [];

    /**
     * @var array $unparsed calendar/components in 'raw' text...
     */
    protected $unparsed = null;

    /**
     * @var array $config configuration with defaults
     */
    protected $config = [
        self::ALLOWEMPTY => true,
    ];

    /**
     * @var string component type
     */
    protected $compType = null;

    /**
     * @var array component index
     */
    protected $compix = [];

    /**
     * @var array get multi property index
     */
    protected $propIx = [];

    /**
     * @var array delete multi property index
     */
    protected $propDelIx = [];

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
            }
        } // end foreach
        $this->compix    = [];
        $this->propIx    = [];
        $this->propDelIx = [];
    }

    /**
     * Reset internal counters
     *
     * @return static
     * @since  2.27.14 - 2019-03-11
     */
    public function reset() : self
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
    public function deleteConfig( $key ) : self
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
     * @param mixed $config
     * @return mixed   bool false on not found or empty
     * @since  2.39.1 - 2021-06-26
     */
    public function getConfig( $config = null )
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
            $this->config[self::UNIQUE_ID] = Util::$SP0;
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
                    foreach( $this->components as $cix => $component ) {
                        if( empty( $component )) {
                            continue;
                        }
                        $info[$cix][$LCORDNO] = $cix + 1;
                        $info[$cix][$LCTYPE]  = $component->getCompType();
                        if( ! Util::isCompInList( $component->getCompType(), self::$SUBCOMPS )) {
                            $info[$cix][$LCUID] = $component->getUid();
                        }
                        $info[$cix][$LCPROPS] = $component->getConfig( self::PROPINFO );
                        $info[$cix][$LCSUB]   = $component->getConfig( self::COMPSINFO );
                    } // end foreach
                }
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
     * @since  2.29.05 - 2019-06-20
     */
    protected function getpropInfo() : array
    {
        static $PROPNAMES  = [
            self::ACTION, self::ATTACH, self::ATTENDEE, self::CATEGORIES,
            self::KLASS, self::COLOR, self::COMMENT,
            self::COMPLETED, self::CONFERENCE, self::CONTACT,
            self::CREATED, self::DESCRIPTION, self::DTEND, self::DTSTAMP,
            self::DTSTART, self::DUE, self::DURATION, self::EXDATE, self::EXRULE,
            self::FREEBUSY, self::GEO, self::IMAGE,
            self::LAST_MODIFIED, self::LOCATION, self::NAME,
            self::ORGANIZER, self::PERCENT_COMPLETE, self::PRIORITY,
            self::RECURRENCE_ID, self::REFRESH_INTERVAL, self::RELATED_TO, self::REPEAT,
            self::REQUEST_STATUS, self::RESOURCES, self::RRULE, self::RDATE,
            self::SEQUENCE, self::SOURCE, self::STATUS, self::SUMMARY, self::TRANSP,
            self::TRIGGER, self::TZNAME, self::TZID, self::TZOFFSETFROM,
            self::TZOFFSETTO, self::TZURL, self::UID, self::URL, self::X_PROP,
        ];
        if( ! Util::isCompInList( $this->getCompType(), self::$SUBCOMPS )) {
            $this->getUid();
            $this->getDtstamp();
        }
        $output = [];
        foreach( $PROPNAMES as $propName ) {
            $propName2 = StringFactory::getInternalPropName( $propName );
            switch( true ) {
                case ( ! property_exists( $this, $propName2 )) :
                    break;
                case ( empty( $this->{$propName2} )) :
                    break;
                case ( self::X_PROP == $propName ) :
                    foreach( array_keys( $this->{$propName2}) as $propName3 ) {
                        $output[$propName3] = 1;
                    }
                    break;
                case ( Util::isPropInList( $propName, self::$MPROPS2 )) :
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
     * @param mixed  $config
     * @param mixed $value
     * @param bool   $softUpdate
     * @return static
     * @throws InvalidArgumentException
     * @since  2.29.4 - 2019-07-02
     */
    public function setConfig( $config, $value = null, $softUpdate = false ) : self
    {
        static $ERRMSG9 = 'Invalid config value %s';
        $isComponent = ( ! property_exists(
            $this,
            StringFactory::getInternalPropName( self::PRODID )
        ));
        if( is_array( $config )) {
            $config = array_change_key_case( $config, CASE_UPPER );
            foreach( $config as $cKey => $cValue ) {
                $this->setConfig( $cKey, $cValue );
            }
            return $this;
        }
        $key    = strtoupper( $config );
        $subCfg = null;
        switch( true ) {
            case ( self::ALLOWEMPTY == $key ) :
                $this->config[self::ALLOWEMPTY] = $value;
                $subCfg = [ self::ALLOWEMPTY => $value ];
                break;
            case ( self::LANGUAGE == $key ) :
                // set language for calendar component as defined in [RFC 1766]
                $value  = trim( $value );
                if( empty( $this->config[self::LANGUAGE] ) || ! $softUpdate ) { // ??
                    $this->config[self::LANGUAGE] = $value;
                }
                $subCfg = [ self::LANGUAGE => $value ];
                break;
            case ( self::UNIQUE_ID == $key ) :
                $value  = trim( $value );
                $this->config[self::UNIQUE_ID] = $value;
                $subCfg = [ self::UNIQUE_ID => $value ];
                break;
            case ( $isComponent ) :
                break;
            default:  // any invalid config key.. .
                throw new InvalidArgumentException( sprintf( $ERRMSG9, $config ));
        } // end switch
        if( ! empty( $subCfg ) && ! empty( $this->components )) {
            foreach( $subCfg as $cfgkey => $cfgValue ) {
                foreach( $this->components as $cix => $component ) {
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
     * @throws InvalidArgumentException
     * @since  2.27.1 - 2018-12-12
     */
    protected function assertEmptyValue( $value, $propName )
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
     * @since  2.23.5 - 2017-04-13
     */
    public function countComponents() : int
    {
        return ( empty( $this->components )) ? 0 : count( $this->components );
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
            : key( array_slice( $this->components, -1, 1, true )) + 1;
    }

    /**
     * Add calendar component as subcomponent to container for subcomponents
     *
     * @param CalendarComponent $component
     * @param mixed             $arg1      ordno/component type/ component uid
     * @param mixed             $arg2      ordno if arg1 = component type
     * @throws InvalidArgumentException
     * @return static
     * @since  2.27.3 - 2018-12-21
     */
    public function setComponent(
        CalendarComponent $component,
        $arg1 = false,
        $arg2 = false
    ) : self
    {
        $component->setConfig( $this->getConfig(), false, true );
        if( ! Util::isCompInList( $component->getCompType(), self::$SUBCOMPS )) {
            /* make sure dtstamp and uid is set */
            $component->getUid();
            $component->getDtstamp();
        }
        if( ! $arg1 ) { // plain insert, last in chain
            self::assertComponents( $this, $component );
            $this->components[] = clone $component;
            return $this;
        }
        $argType = $index = null;
        if( ctype_digit((string) $arg1 )) { // index insert/replace
            $argType = self::$INDEX;
            $index   = (int) $arg1 - 1;
        }
        elseif( Util::isCompInList( $arg1, self::$CALCOMPS )) {
            $argType = ucfirst( strtolower( $arg1 ));
            $index   = ( ctype_digit((string) $arg2 )) ? ((int) $arg2 ) - 1 : 0;
        }
        // else if arg1 is set, arg1 must be an UID
        $cix2sC = 0;
        foreach( $this->components as $cix => $component2 ) {
            if( empty( $component2 )) {
                continue;
            }
            if(( self::$INDEX == $argType ) && ( $index == $cix )) {
                // index insert/replace
                $this->components[$cix] = clone $component;
                return $this;
            }
            elseif( $argType == $component2->getCompType()) {
                // component Type index insert/replace
                if( $index == $cix2sC ) {
                    $this->components[$cix] = clone $component;
                    return $this;
                }
                $cix2sC++;
            }
            elseif( ! $argType &&
                ! Util::isCompInList( $component2->getCompType(), self::$SUBCOMPS ) &&
                ( $arg1 == $component2->getUid())) { // UID insert/replace
                $this->components[$cix] = clone $component;
                return $this;
            }
        } // end foreach
        if( self::$INDEX == $argType ) { // arg1=index and not found.. . insert at index .. .
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
     * @param mixed    $arg1 ordno / component type / component uid
     * @param mixed    $arg2 ordno if arg1 = component type
     * @return bool  true on success, false on not found (last one deleted)
     * @since  2.26.14 - 2019-02-25
     * @todo   Exception mgnt on unknown component
     */
    public function deleteComponent( $arg1, $arg2 = false ) : bool
    {
        if( ! isset( $this->components )) {
            return false;
        }
        $argType = $index = null;
        if( ctype_digit((string) $arg1 )) {
            $argType = self::$INDEX;
            $index   = (int)$arg1 - 1;
        }
        elseif( property_exists(
                $this,
                StringFactory::getInternalPropName( IcalInterface::PRODID )
            )) {
            $cmpArg = ucfirst( strtolower( $arg1 ));
            if( Util::isCompInList( $cmpArg, IcalBase::$CALCOMPS ) &&
                ( 0 != strcasecmp( $cmpArg, IcalInterface::VALARM ))) {
                $argType = ucfirst( strtolower( $arg1 ) );
                $index   = ( ! empty( $arg2 ) && ctype_digit((string) $arg2 ))
                    ? ((int) $arg2 - 1 )
                    : 0;
            }
        } // end elseif
        $cix2dC = 0;
        $remove = false;
        foreach( $this->components as $cix => $component ) {
            if(( IcalBase::$INDEX == $argType ) && ( $index == $cix )) {
                unset( $this->components[$cix] );
                $remove = true;
                break;
            }
            elseif( $argType == $component->getCompType()) {
                if( $index == $cix2dC ) {
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
                ! Util::isCompInList( $component->getCompType(), IcalBase::$SUBCOMPS ) &&
                ( $arg1 == $component->getUid())) {
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
     * Assert base components
     *
     * @param IcalBase $component
     * @throws InvalidArgumentException
     * @since  2.27.6 - 2018-12-28
     */
    protected static function assertBaseComponents( IcalBase $component )
    {
        static $ERRMSG = 'Invalid component type \'%s\'';
        $compType = $component->getCompType();
        if( ! Util::isCompInList( $compType, self::$VCOMPS ) &&
             ( self::VTIMEZONE != $compType )) {
            throw new InvalidArgumentException( sprintf( $ERRMSG, $compType ));
        }
    }

    /**
     * Assert components
     *
     * @param IcalBase $comp
     * @param CalendarComponent $subComp
     * @throws InvalidArgumentException
     * @since  2.27.6 - 2018-12-28
     */
    private static function assertComponents(
        IcalBase $comp,
        CalendarComponent $subComp
    ) {
        static $MSG = 'Unknown component %s';
        $type    = $comp->getCompType();
        $subType = $subComp->getCompType();
        switch( $type ) {
            case Vcalendar::VCALENDAR :
                self::assertBaseComponents( $subComp );
                break;
            case self::VTIMEZONE :
                switch( $subType ) {
                    case self::STANDARD :
                        break 2;
                    case self::DAYLIGHT :
                        break 2;
                    default :
                        throw new InvalidArgumentException( sprintf( $MSG, $subType ));
                }
            case self::VEVENT :
                // fall through
            case self::VTODO :
                switch( $subType ) {
                    case self::VALARM :
                        break 2;
                    default :
                        throw new InvalidArgumentException( sprintf( $MSG, $subType ));
                }
            case self::VFREEBUSY :
                break;
            case self::VJOURNAL :
                break;
            default :
                throw new InvalidArgumentException( sprintf( $MSG, $type ));
        } // end switch
    }
}
