<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.26.9
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

use Kigkonsult\Icalcreator\Util\Util;

use function define;
use function defined;
use function array_change_key_case;
use function array_filter;
use function array_keys;
use function array_slice;
use function array_unshift;
use function count;
use function ctype_digit;
use function get_object_vars;
use function gethostbyname;
use function is_array;
use function is_null;
use function is_object;
use function key;
use function strtolower;
use function strtoupper;
use function trim;
use function ucfirst;

/**
 *         Do NOT alter or remove the constant!!
 */
if( ! defined( 'ICALCREATOR_VERSION' )) {
    define( 'ICALCREATOR_VERSION', 'iCalcreator 2.26.9' );
}

/**
 * iCalcreator base class
 *
 * Properties and methods shared by Vcalendar and CalendarComponents
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26.1 - 2018-11-17
 */
abstract class IcalBase implements IcalInterface
{
    use Traits\X_PROPtrait;

    /**
     * @var string
     * @access protected
     * @static
     */
    protected static $INDEX = 'INDEX';

    /**
     * @var array container for sub-components
     * @access protected
     */
    protected $components = [];
    /**
     * @var array $unparsed calendar/components in 'raw' text...
     * @access protected
     */
    protected $unparsed = null;
    /**
     * @var array $config configuration
     * @access protected
     */
    protected $config = [];
    /**
     * @var array component index
     * @access protected
     */
    protected $compix = [];
    /**
     * @var array get multi property index
     * @access protected
     */
    protected $propix = [];
    /**
     * @var array delete multi property index
     * @access protected
     */
    protected $propdelix = [];

    /**
     * __clone method
     *
     * @link https://php.net/manual/en/language.oop5.cloning.php#116329
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     */
    public function __clone() {
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
                    unset( $attr_array_value);
                }
            }
        }
        $this->compix    = [];
        $this->propix    = [];
        $this->propdelix = [];
    }

    /**
     * Return config value or info about subcomponents, false on not found
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param mixed $config
     * @return mixed
     */
    public function getConfig( $config = false ) {
        static $LCORDNO = 'ordno';
        static $LCTYPE  = 'type';
        static $LCUID   = 'uid';
        static $LCPROPS = 'props';
        static $LCSUB   = 'sub';
        if( empty( $config )) {
            $return                    = [];
            $return[Util::$ALLOWEMPTY] = $this->getConfig( Util::$ALLOWEMPTY );
            if( false !== ( $lang = $this->getConfig( Util::$LANGUAGE ))) {
                $return[Util::$LANGUAGE] = $lang;
            }
            $return[Util::$TZID]      = $this->getConfig( Util::$TZID );
            $return[Util::$UNIQUE_ID] = $this->getConfig( Util::$UNIQUE_ID );
            return $return;
        }
        switch( strtoupper( $config )) {
            case Util::$ALLOWEMPTY:
                if( isset( $this->config[Util::$ALLOWEMPTY] )) {
                    return $this->config[Util::$ALLOWEMPTY];
                }
                break;
            case Util::$COMPSINFO:
                $this->compix = [];
                $info = [];
                if( ! empty( $this->components )) {
                    foreach( $this->components as $cix => $component ) {
                        if( empty( $component )) {
                            continue;
                        }
                        $info[$cix][$LCORDNO] = $cix + 1;
                        $info[$cix][$LCTYPE]  = $component->compType;
                        $info[$cix][$LCUID]   = $component->getProperty( Util::$UID );
                        $info[$cix][$LCPROPS] = $component->getConfig( Util::$PROPINFO );
                        $info[$cix][$LCSUB]   = $component->getConfig( Util::$COMPSINFO );
                    }
                }
                return $info;
                break;
            case Util::$LANGUAGE: // get language for calendar component as defined in [RFC 1766]
                if( isset( $this->config[Util::$LANGUAGE] )) {
                    return $this->config[Util::$LANGUAGE];
                }
                break;
            case Util::$PROPINFO:
                $output = [];
                if( ! Util::isCompInList( $this->compType, Util::$SUBCOMPS )) {
                    if( empty( $this->uid )) {
                        $this->uid = Util::makeUid( $this->getConfig( Util::$UNIQUE_ID ));
                    }

                    $output[Util::$UID] = 1;
                    if( empty( $this->dtstamp )) {
                        $this->dtstamp = Util::makeDtstamp();
                    }
                    $output[Util::$DTSTAMP] = 1;
                }
                if( ! empty( $this->summary )) {
                    $output[Util::$SUMMARY] = 1;
                }
                if( ! empty( $this->description )) {
                    $output[Util::$DESCRIPTION] = count( $this->description );
                }
                if( ! empty( $this->dtstart )) {
                    $output[Util::$DTSTART] = 1;
                }
                if( ! empty( $this->dtend )) {
                    $output[Util::$DTEND] = 1;
                }
                if( ! empty( $this->due )) {
                    $output[Util::$DUE] = 1;
                }
                if( ! empty( $this->duration )) {
                    $output[Util::$DURATION] = 1;
                }
                if( ! empty( $this->rrule )) {
                    $output[Util::$RRULE] = count( $this->rrule );
                }
                if( ! empty( $this->rdate )) {
                    $output[Util::$RDATE] = count( $this->rdate );
                }
                if( ! empty( $this->exdate )) {
                    $output[Util::$EXDATE] = count( $this->exdate );
                }
                if( ! empty( $this->exrule )) {
                    $output[Util::$EXRULE] = count( $this->exrule );
                }
                if( ! empty( $this->action )) {
                    $output[Util::$ACTION] = 1;
                }
                if( ! empty( $this->attach )) {
                    $output[Util::$ATTACH] = count( $this->attach );
                }
                if( ! empty( $this->attendee )) {
                    $output[Util::$ATTENDEE] = count( $this->attendee );
                }
                if( ! empty( $this->categories )) {
                    $output[Util::$CATEGORIES] = count( $this->categories );
                }
                if( ! empty( $this->class )) {
                    $output[Util::$CLASS] = 1;
                }
                if( ! empty( $this->comment )) {
                    $output[Util::$COMMENT] = count( $this->comment );
                }
                if( ! empty( $this->completed )) {
                    $output[Util::$COMPLETED] = 1;
                }
                if( ! empty( $this->contact )) {
                    $output[Util::$CONTACT] = count( $this->contact );
                }
                if( ! empty( $this->created )) {
                    $output[Util::$CREATED] = 1;
                }
                if( ! empty( $this->freebusy )) {
                    $output[Util::$FREEBUSY] = count( $this->freebusy );
                }
                if( ! empty( $this->geo )) {
                    $output[Util::$GEO] = 1;
                }
                if( ! empty( $this->lastmodified )) {
                    $output[Util::$LAST_MODIFIED] = 1;
                }
                if( ! empty( $this->location )) {
                    $output[Util::$LOCATION] = 1;
                }
                if( ! empty( $this->organizer )) {
                    $output[Util::$ORGANIZER] = 1;
                }
                if( ! empty( $this->percentcomplete )) {
                    $output[Util::$PERCENT_COMPLETE] = 1;
                }
                if( ! empty( $this->priority )) {
                    $output[Util::$PRIORITY] = 1;
                }
                if( ! empty( $this->recurrenceid )) {
                    $output[Util::$RECURRENCE_ID] = 1;
                }
                if( ! empty( $this->relatedto )) {
                    $output[Util::$RELATED_TO] = count( $this->relatedto );
                }
                if( ! empty( $this->repeat )) {
                    $output[Util::$REPEAT] = 1;
                }
                if( ! empty( $this->requeststatus )) {
                    $output[Util::$REQUEST_STATUS] = count( $this->requeststatus );
                }
                if( ! empty( $this->resources )) {
                    $output[Util::$RESOURCES] = count( $this->resources );
                }
                if( ! empty( $this->sequence )) {
                    $output[Util::$SEQUENCE] = 1;
                }
                if( ! empty( $this->status )) {
                    $output[Util::$STATUS] = 1;
                }
                if( ! empty( $this->transp )) {
                    $output[Util::$TRANSP] = 1;
                }
                if( ! empty( $this->trigger )) {
                    $output[Util::$TRIGGER] = 1;
                }
                if( ! empty( $this->tzid )) {
                    $output[Util::$TZID] = 1;
                }
                if( ! empty( $this->tzname )) {
                    $output[Util::$TZNAME] = count( $this->tzname );
                }
                if( ! empty( $this->tzoffsetfrom )) {
                    $output[Util::$TZOFFSETFROM] = 1;
                }
                if( ! empty( $this->tzoffsetto )) {
                    $output[Util::$TZOFFSETTO] = 1;
                }
                if( ! empty( $this->tzurl )) {
                    $output[Util::$TZURL] = 1;
                }
                if( ! empty( $this->url )) {
                    $output[Util::$URL] = 1;
                }
                if( ! empty( $this->xprop )) {
                    $output[Util::$X_PROP] = count( $this->xprop );
                }
                return $output;
                break;
            case Util::$SETPROPERTYNAMES:
                return array_keys( $this->getConfig( Util::$PROPINFO ));
                break;
            case Util::$TZID:
                if( isset( $this->config[Util::$TZID] )) {
                    return $this->config[Util::$TZID];
                }
                break;
            case Util::$UNIQUE_ID:
                if( empty( $this->config[Util::$UNIQUE_ID] )) {
                    $this->config[Util::$UNIQUE_ID] = ( isset( $_SERVER[Util::$SERVER_NAME] ))
                        ? gethostbyname( $_SERVER[Util::$SERVER_NAME] )
                        : Util::$LOCALHOST;
                }
                return $this->config[Util::$UNIQUE_ID];
                break;
        }
        return false;
    }

    /**
     * General component config setting
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.12 - 2017-04-22
     * @param mixed  $config
     * @param string $value
     * @param bool   $softUpdate
     * @return bool   true on success
     */
    public function setConfig( $config, $value = null, $softUpdate = null ) {
        if( is_null( $softUpdate )) {
            $softUpdate = false;
        }
        if( is_array( $config )) {
            $config = array_change_key_case( $config, CASE_UPPER );
            foreach( $config as $cKey => $cValue ) {
                if( false === $this->setConfig( $cKey, $cValue, $softUpdate )) {
                    return false;
                }
            }
            return true;
        }
        $res = false;
        switch( strtoupper( $config )) {
            case Util::$ALLOWEMPTY:
                $this->config[Util::$ALLOWEMPTY] = $value;
                $subcfg = [ Util::$ALLOWEMPTY => $value ];
                $res    = true;
                break;
            case Util::$LANGUAGE: // set language for component as defined in [RFC 1766]
                $value  = trim( $value );
                if( empty( $this->config[Util::$LANGUAGE] ) || ! $softUpdate ) {
                    $this->config[Util::$LANGUAGE] = $value;
                }
                $subcfg = [ Util::$LANGUAGE => $value ];
                $res    = true;
                break;
            case Util::$TZID:
                $this->config[Util::$TZID] = trim( $value );
                $subcfg = [ Util::$TZID => trim( $value ) ];
                $res    = true;
                break;
            case Util::$UNIQUE_ID:
                $value  = trim( $value );
                $this->config[Util::$UNIQUE_ID] = $value;
                $subcfg = [ Util::$UNIQUE_ID => $value ];
                $res    = true;
                break;
            default:  // any unvalid config key.. .
                return true;
        }
        if( ! $res ) {
            return false;
        }
        if( isset( $subcfg ) && ! empty( $this->components )) {
            foreach( $subcfg as $cfgkey => $cfgvalue ) {
                foreach( $this->components as $cix => $component ) {
                    $res = $this->components[$cix]->setConfig( $cfgkey, $cfgvalue, $softUpdate );
                    if( ! $res ) {
                        break 2;
                    }
                }
            }
        }
        return $res;
    }

    /**
     * Return number of components
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.5 - 2017-04-13
     * @return int
     */
    public function countComponents() {
        return ( empty( $this->components )) ? 0 : count( $this->components );
    }

    /**
     * Return new calendar component, included in calendar or component
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param string $compType component type
     * @return mixed CalendarComponent|bool
     */
    public function newComponent( $compType ) {
        $config = $this->getConfig();
        $ix     = ( empty( $this->components ))
            ? 0
            : key( array_slice( $this->components, -1, 1, true )) + 1;
        switch( ucfirst( \strtolower( $compType ))) {
            case self::VALARM :
                $this->components[$ix] = new Valarm( $config );
                break;
            case self::VEVENT :
                $this->components[$ix] = new Vevent( $config );
                break;
            case self::VTODO :
                $this->components[$ix] = new Vtodo( $config );
                break;
            case self::VJOURNAL :
                $this->components[$ix] = new Vjournal( $config );
                break;
            case self::VFREEBUSY :
                $this->components[$ix] = new Vfreebusy( $config );
                break;
            case self::VTIMEZONE :
                array_unshift( $this->components, new Vtimezone( $config ));
                $ix = 0;
                break;
            case self::STANDARD :
                array_unshift( $this->components, new Vtimezone( self::STANDARD, $config ));
                $ix = 0;
                break;
            case self::DAYLIGHT :
                $this->components[$ix] = new Vtimezone( self::DAYLIGHT, $config );
                break;
            default:
                return false;
        }
        return $this->components[$ix];
    }

    /**
     * Delete calendar subcomponent from component container
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.1 - 2018-11-17
     * @param mixed $arg1 ordno / component type / component uid
     * @param mixed $arg2 ordno if arg1 = component type
     * @return bool  true on success
     */
    public function deleteComponent( $arg1, $arg2 = false ) {
        if( ! isset( $this->components )) {
            return false;
        }
        $argType = $index = null;
        if( ctype_digit((string) $arg1 )) {
            $argType = self::$INDEX;
            $index   = (int) $arg1 - 1;
        }
        elseif( Util::isCompInList( $arg1, Util::$ALLCOMPS )) {
            $argType = ucfirst( strtolower( $arg1 ));
            $index   = ( ! empty( $arg2 ) && ctype_digit((string) $arg2 )) ? (( int ) $arg2 - 1 ) : 0;
        }
        $cix2dC = 0;
        $remove = false;
        foreach( $this->components as $cix => $component ) {
            if(( self::$INDEX == $argType ) && ( $index == $cix )) {
                unset( $this->components[$cix] );
                $remove = true;
                break;
            }
            elseif( $argType == $component->compType ) {
                if( $index == $cix2dC ) {
                    unset( $this->components[$cix] );
                    $remove = true;
                    break;
                }
                $cix2dC++;
            }
            elseif( ! $argType &&
                ( $arg1 == $component->getProperty( Util::$UID ))) {
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
     * Add calendar component as subcomponent to container for subcomponents
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.1 - 2018-11-17
     * @param object $component CalendarComponent
     * @param mixed  $arg1      ordno/component type/ component uid
     * @param mixed  $arg2      ordno if arg1 = component type
     * @return bool
     */
    public function setComponent( $component, $arg1 = false, $arg2 = false ) {
        if( ! isset( $this->components )) {
            return false;
        }
        $component->setConfig( $this->getConfig(), false, true );
        if( ! Util::isCompInList( $component->compType, Util::$SUBCOMPS )) {
            /* make sure dtstamp and uid is set */
            $component->getProperty( Util::$DTSTAMP );
            $component->getProperty( Util::$UID );
        }
        if( ! $arg1 ) { // plain insert, last in chain
            $this->components[] = clone $component;
            return true;
        }
        $argType = $index = null;
        if( ctype_digit((string) $arg1 )) { // index insert/replace
            $argType = self::$INDEX;
            $index   = (int) $arg1 - 1;
        }
        elseif( Util::isCompInList( $arg1, Util::$MCOMPS )) {
            $argType = ucfirst( \strtolower( $arg1 ));
            $index   = ( ctype_digit((string) $arg2 )) ? ((int) $arg2 ) - 1 : 0;
        }
        // else if arg1 is set, arg1 must be an UID
        $cix2sC = 0;
        foreach( $this->components as $cix => $component2 ) {
            if( empty( $component2 )) {
                continue;
            }
            if(( self::$INDEX == $argType ) && ( $index == $cix )) { // index insert/replace
                $this->components[$cix] = clone $component;
                return true;
            }
            elseif( $argType == $component2->compType ) {       // component Type index insert/replace
                if( $index == $cix2sC ) {
                    $this->components[$cix] = clone $component;
                    return true;
                }
                $cix2sC++;
            }
            elseif( ! $argType && ( $arg1 == $component2->getProperty( Util::$UID ))) {
                $this->components[$cix] = clone $component;      // UID insert/replace
                return true;
            }
        }
        /* arg1=index and not found.. . insert at index .. .*/
        if( self::$INDEX == $argType ) {
            $this->components[$index] = clone $component;
            \ksort( $this->components, SORT_NUMERIC );
        }
        else {   /* not found.. . insert last in chain anyway .. .*/
            $this->components[] = clone $component;
        }
        return true;
    }
}
