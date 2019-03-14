<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.26.8
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
use Kigkonsult\Icalcreator\Util\UtilDuration;
use Kigkonsult\Icalcreator\Util\UtilGeo;

use function array_keys;
use function array_merge;
use function array_unshift;
use function count;
use function ctype_alpha;
use function ctype_digit;
use function end;
use function explode;
use function func_get_args;
use function func_num_args;
use function get_called_class;
use function in_array;
use function is_array;
use function is_null;
use function ksort;
use function property_exists;
use function reset;
use function sprintf;
use function strcasecmp;
use function stripos;
use function strlen;
use function strpos;
use function strrpos;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;

/**
 *  Parent class for calendar components
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26 - 2018-11-10
 */
class CalendarComponent extends IcalBase
{
    /**
     * @var string component type
     */
    public $compType = null;

    /**
     * @var int component number
     */
    public $cno = 0;

    /**
     * @var array  compoment sort params
     */
    public $srtk = null;

    /**
     * Constructor for calendar component object
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     */
    public function __construct() {
        static $BS = '\\';
        if( isset( $this->timezonetype )) {
            $this->compType = $this->timezonetype;
        }
        else {
            $className      = get_called_class();
            $this->compType = substr( $className, strrpos( $className, $BS ) + 1 );
        }
        if( Util::isCompInList( $this->compType, Util::$VCOMPS )) {
            $this->dtstamp = Util::makeDtstamp();
        }
    }

    /**
     * Return unique instance number
     *
     * @return int
     */
    protected static function getObjectNo() {
        static $objectNo = 0;
        return ++$objectNo;
    }

    /**
     * Delete component property value
     *
     * Return false at successfull removal of non-multiple property
     * Return false at successfull removal of last multiple property part
     * otherwise true (there is more to remove)
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param mixed $propName bool false => X-property
     * @param int   $propix   specific property in case of multiply occurences
     * @return bool
     */
    public function deleteProperty( $propName = null, $propix = null ) {
        if( $this->notExistProp( $propName )) {
            return false;
        }
        $propName = strtoupper( $propName );
        if( Util::isPropInList( $propName, Util::$MPROPS2 )) {
            if( is_null( $propix )) {
                $propix = ( isset( $this->propdelix[$propName] ) && ( Util::$X_PROP != $propName ))
                    ? $this->propdelix[$propName] + 2 : 1;
            }
            $this->propdelix[$propName] = --$propix;
        }
        switch( $propName ) {
            case Util::$ACTION:
                $this->action = null;
                return false;
                break;
            case Util::$ATTACH:
                return Util::deletePropertyM( $this->attach,$this->propdelix[$propName] );
                break;
            case Util::$ATTENDEE:
                return Util::deletePropertyM( $this->attendee, $this->propdelix[$propName] );
                break;
            case Util::$CATEGORIES:
                return Util::deletePropertyM( $this->categories, $this->propdelix[$propName] );
                break;
            case Util::$CLASS:
                $this->class = null;
                return false;
                break;
            case Util::$COMMENT:
                return Util::deletePropertyM( $this->comment, $this->propdelix[$propName] );
                break;
            case Util::$COMPLETED:
                $this->completed = null;
                return false;
                break;
            case Util::$CONTACT:
                return Util::deletePropertyM( $this->contact, $this->propdelix[$propName] );
                break;
            case Util::$CREATED:
                $this->created = null;
                return false;
                break;
            case Util::$DESCRIPTION:
                return Util::deletePropertyM( $this->description,$this->propdelix[$propName] );
                break;
            case Util::$DTEND:
                $this->dtend = null;
                return false;
                break;
            case Util::$DTSTAMP:
                if( Util::isCompInList( $this->compType, Util::$SUBCOMPS )) {
                    return false;
                }
                $this->dtstamp = null;
                return false;
                break;
            case Util::$DTSTART:
                $this->dtstart = null;
                return false;
                break;
            case Util::$DUE:
                $this->due = null;
                return false;
                break;
            case Util::$DURATION:
                $this->duration = null;
                return false;
                break;
            case Util::$EXDATE:
                return Util::deletePropertyM( $this->exdate, $this->propdelix[$propName] );
                break;
            case Util::$EXRULE:
                return Util::deletePropertyM( $this->exrule, $this->propdelix[$propName] );
                break;
            case Util::$FREEBUSY:
                return Util::deletePropertyM( $this->freebusy,$this->propdelix[$propName] );
                break;
            case Util::$GEO:
                $this->geo = null;
                return false;
                break;
            case Util::$LAST_MODIFIED:
                $this->lastmodified = null;
                return false;
                break;
            case Util::$LOCATION:
                $this->location = null;
                return false;
                break;
            case Util::$ORGANIZER:
                $this->organizer = null;
                return false;
                break;
            case Util::$PERCENT_COMPLETE:
                $this->percentcomplete = null;
                return false;
                break;
            case Util::$PRIORITY:
                $this->priority = null;
                return false;
                break;
            case Util::$RDATE:
                return Util::deletePropertyM( $this->rdate, $this->propdelix[$propName] );
                break;
            case Util::$RECURRENCE_ID:
                $this->recurrenceid = null;
                return false;
                break;
            case Util::$RELATED_TO:
                return Util::deletePropertyM( $this->relatedto,$this->propdelix[$propName] );
                break;
            case Util::$REPEAT:
                $this->repeat = null;
                return false;
                break;
            case Util::$REQUEST_STATUS:
                return Util::deletePropertyM( $this->requeststatus,$this->propdelix[$propName] );
                break;
            case Util::$RESOURCES:
                return Util::deletePropertyM( $this->resources, $this->propdelix[$propName] );
                break;
            case Util::$RRULE:
                return Util::deletePropertyM( $this->rrule, $this->propdelix[$propName] );
                break;
            case Util::$SEQUENCE:
                $this->sequence = null;
                return false;
                break;
            case Util::$STATUS:
                $this->status = null;
                return false;
                break;
            case Util::$SUMMARY:
                $this->summary = null;
                return false;
                break;
            case Util::$TRANSP:
                $this->transp = null;
                return false;
                break;
            case Util::$TRIGGER:
                $this->trigger = null;
                return false;
                break;
            case Util::$TZID:
                $this->tzid = null;
                return false;
                break;
            case Util::$TZNAME:
                return Util::deletePropertyM( $this->tzname, $this->propdelix[$propName] );
                break;
            case Util::$TZOFFSETFROM:
                $this->tzoffsetfrom = null;
                return false;
                break;
            case Util::$TZOFFSETTO:
                $this->tzoffsetto = null;
                return false;
                break;
            case Util::$TZURL:
                $this->tzurl = null;
                return false;
                break;
            case Util::$UID:
                if( Util::isCompInList( $this->compType, Util::$SUBCOMPS )) {
                    return false;
                }
                $this->uid = null;
                return false;
                break;
            case Util::$URL:
                $this->url = null;
                return false;
                break;
            default:
                return parent::deleteXproperty( $propName, $this->xprop, $propix, $this->propdelix );
        }
    }

    /**
     * Return true if property NOT exists within component
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param string $propName
     * @return bool
     */
    public function notExistProp( $propName ) {
        static $LASTMODIFIED    = 'lastmodified';
        static $PERCENTCOMPLETE = 'percentcomplete';
        static $RECURRENCEID    = 'recurrenceid';
        static $RELATEDTO       = 'relatedto';
        static $REQUESTSTATUS   = 'requeststatus';
        if( empty( $propName )) {
            return false;
        } // when deleting x-prop, an empty propName may be used=allowed
        switch( strtoupper( $propName )) {
            case Util::$LAST_MODIFIED :
                if( ! property_exists( $this, $LASTMODIFIED )) {
                    return true;
                }
                break;
            case Util::$PERCENT_COMPLETE :
                if( ! property_exists( $this, $PERCENTCOMPLETE )) {
                    return true;
                }
                break;
            case Util::$RECURRENCE_ID :
                if( ! property_exists( $this, $RECURRENCEID )) {
                    return true;
                }
                break;
            case Util::$RELATED_TO :
                if( ! property_exists( $this, $RELATEDTO )) {
                    return true;
                }
                break;
            case Util::$REQUEST_STATUS :
                if( ! property_exists( $this, $REQUESTSTATUS )) {
                    return true;
                }
                break;
            default :
                if( ! Util::isXprefixed( $propName ) &&
                    ! property_exists( $this, strtolower( $propName ))) {
                    return true;
                }
                break;
        }
        return false;
    }

    /**
     * Return component property value/params
     *
     * Return array with keys VALUE/PARAMS rf arg $inclParam is true
     * If property has multiply values, consequtive function calls are needed
     *
     * @param string $propName
     * @param int    $propix specific property in case of multiply occurences
     * @param bool   $inclParam
     * @param bool   $specform
     * @return mixed
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-11-28
     */
    public function getProperty(
        $propName  = null,
        $propix    = null,
        $inclParam = false,
        $specform  = false
    ) {
        if( 0 == strcasecmp( Util::$GEOLOCATION, $propName )) {
            if( false === ( $geo = $this->getProperty( Util::$GEO ))) {
                return false;
            }
            $loc     = $this->getProperty( Util::$LOCATION );
            $content = ( empty( $loc )) ? null : $loc . Util::$SP1;
            return $content .
                UtilGeo::geo2str2( $geo[UtilGeo::$LATITUDE], UtilGeo::$geoLatFmt ) .
                UtilGeo::geo2str2( $geo[UtilGeo::$LONGITUDE], UtilGeo::$geoLongFmt) .
                Util::$L;
        }
        if( $this->notExistProp( $propName )) {
            return false;
        }
        $propName = ( $propName ) ? strtoupper( $propName ) : Util::$X_PROP;
        if( Util::isPropInList( $propName, Util::$MPROPS2 )) {
            if( empty( $propix )) {
                $propix = ( isset( $this->propix[$propName] )) ? $this->propix[$propName] + 2 : 1;
            }
            $this->propix[$propName] = --$propix;
        }
        switch( $propName ) {
            case Util::$ATTACH:
                Util::recountMvalPropix( $this->attach, $propix );
                $this->propix[$propName] = $propix;
                if( ! isset( $this->attach[$propix] )) {
                    unset( $this->propix[$propName] );
                    return false;
                }
                return ( $inclParam ) ? $this->attach[$propix] : $this->attach[$propix][Util::$LCvalue];
                break;
            case Util::$ATTENDEE:
                Util::recountMvalPropix( $this->attendee, $propix );
                $this->propix[$propName] = $propix;
                if( ! isset( $this->attendee[$propix] )) {
                    unset( $this->propix[$propName] );
                    return false;
                }
                return ( $inclParam ) ? $this->attendee[$propix] : $this->attendee[$propix][Util::$LCvalue];
                break;
            case Util::$CATEGORIES:
                Util::recountMvalPropix( $this->categories, $propix );
                $this->propix[$propName] = $propix;
                if( ! isset( $this->categories[$propix] )) {
                    unset( $this->propix[$propName] );
                    return false;
                }
                return ( $inclParam ) ? $this->categories[$propix] : $this->categories[$propix][Util::$LCvalue];
                break;
            case Util::$CLASS:
                if( isset( $this->class[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->class : $this->class[Util::$LCvalue];
                }
                break;
            case Util::$COMMENT:
                Util::recountMvalPropix( $this->comment, $propix );
                $this->propix[$propName] = $propix;
                if( ! isset( $this->comment[$propix] )) {
                    unset( $this->propix[$propName] );
                    return false;
                }
                return ( $inclParam ) ? $this->comment[$propix] : $this->comment[$propix][Util::$LCvalue];
                break;
            case Util::$COMPLETED:
                if( isset( $this->completed[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->completed : $this->completed[Util::$LCvalue];
                }
                break;
            case Util::$CONTACT:
                Util::recountMvalPropix( $this->contact, $propix );
                $this->propix[$propName] = $propix;
                if( ! isset( $this->contact[$propix] )) {
                    unset( $this->propix[$propName] );
                    return false;
                }
                return ( $inclParam ) ? $this->contact[$propix] : $this->contact[$propix][Util::$LCvalue];
                break;
            case Util::$CREATED:
                if( isset( $this->created[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->created : $this->created[Util::$LCvalue];
                }
                break;
            case Util::$DESCRIPTION:
                Util::recountMvalPropix( $this->description, $propix );
                $this->propix[$propName] = $propix;
                if( ! isset( $this->description[$propix] )) {
                    unset( $this->propix[$propName] );
                    return false;
                }
                return ( $inclParam ) ? $this->description[$propix] : $this->description[$propix][Util::$LCvalue];
                break;
            case Util::$DTEND:
                if( isset( $this->dtend[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->dtend : $this->dtend[Util::$LCvalue];
                }
                break;
            case Util::$DTSTAMP:
                if( Util::isCompInList( $this->compType, Util::$SUBCOMPS )) {
                    return false;
                }
                if( ! isset( $this->dtstamp[Util::$LCvalue] )) {
                    $this->dtstamp = Util::makeDtstamp();
                }
                return ( $inclParam ) ? $this->dtstamp : $this->dtstamp[Util::$LCvalue];
                break;
            case Util::$DTSTART:
                if( isset( $this->dtstart[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->dtstart : $this->dtstart[Util::$LCvalue];
                }
                break;
            case Util::$DUE:
                if( isset( $this->due[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->due : $this->due[Util::$LCvalue];
                }
                break;
            case Util::$DURATION:
                if( ! isset( $this->duration[Util::$LCvalue] )) {
                    return false;
                }
                $dateInterval = UtilDuration::DateIntervalArr2DateInterval( $this->duration[Util::$LCvalue] );
                if( $specform && isset( $this->dtstart[Util::$LCvalue] )) {
                    $dtStart = $this->dtstart[Util::$LCvalue];
                    if( isset( $this->dtstart[Util::$LCparams][Util::$TZID] )) {
                        $dtStart[Util::$LCtz] = $this->dtstart[Util::$LCparams][Util::$TZID];
                    }
                    $value = UtilDuration::dateInterval2date( $dtStart, $dateInterval );
                }
                else {
                    $value = UtilDuration::dateInterval2arr( $dateInterval );
                }
                $params = ( $specform && $inclParam &&
                    isset( $this->dtstart[Util::$LCparams][Util::$TZID] ))
                    ? array_merge((array) $this->duration[Util::$LCparams], $this->dtstart[Util::$LCparams] )
                    : $this->duration[Util::$LCparams];
                return ( $inclParam ) ? [ Util::$LCvalue  => $value, Util::$LCparams => $params, ] : $value;
                break;
            case Util::$EXDATE:
                Util::recountMvalPropix( $this->exdate, $propix );
                $this->propix[$propName] = $propix;
                if( ! isset( $this->exdate[$propix] )) {
                    unset( $this->propix[$propName] );
                    return false;
                }
                return ( $inclParam ) ? $this->exdate[$propix] : $this->exdate[$propix][Util::$LCvalue];
                break;
            case Util::$EXRULE:
                Util::recountMvalPropix( $this->exrule, $propix );
                $this->propix[$propName] = $propix;
                if( ! isset( $this->exrule[$propix] )) {
                    unset( $this->propix[$propName] );
                    return false;
                }
                return ( $inclParam ) ? $this->exrule[$propix] : $this->exrule[$propix][Util::$LCvalue];
                break;
            case Util::$FREEBUSY:
                Util::recountMvalPropix( $this->freebusy, $propix );
                $this->propix[$propName] = $propix;
                if( ! isset( $this->freebusy[$propix] )) {
                    unset( $this->propix[$propName] );
                    return false;
                }
                $output = $this->freebusy[$propix];
                foreach( $output[Util::$LCvalue] as $perIx => $freebusyPeriod ) {
                    if( isset( $freebusyPeriod[1]['invert'] )) { // fix pre 7.0.5 bug
                        $dateInterval = UtilDuration::DateIntervalArr2DateInterval( $freebusyPeriod[1] );
                        $output[Util::$LCvalue][$perIx][1] = UtilDuration::dateInterval2arr( $dateInterval );
                    }
                }
                return ( $inclParam ) ? $output : $output[Util::$LCvalue];
                break;
            case Util::$GEO:
                if( isset( $this->geo[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->geo : $this->geo[Util::$LCvalue];
                }
                break;
            case Util::$LAST_MODIFIED:
                if( isset( $this->lastmodified[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->lastmodified : $this->lastmodified[Util::$LCvalue];
                }
                break;
            case Util::$LOCATION:
                if( isset( $this->location[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->location : $this->location[Util::$LCvalue];
                }
                break;
            case Util::$ORGANIZER:
                if( isset( $this->organizer[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->organizer : $this->organizer[Util::$LCvalue];
                }
                break;
            case Util::$PERCENT_COMPLETE:
                if( isset( $this->percentcomplete[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->percentcomplete : $this->percentcomplete[Util::$LCvalue];
                }
                break;
            case Util::$PRIORITY:
                if( isset( $this->priority[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->priority : $this->priority[Util::$LCvalue];
                }
                break;
            case Util::$RDATE:
                Util::recountMvalPropix( $this->rdate, $propix );
                $this->propix[$propName] = $propix;
                if( ! isset( $this->rdate[$propix] ) ||
                    empty( $this->rdate[$propix] ) ||
                    empty( $this->rdate[$propix][Util::$LCvalue] )
                ) {
                    unset( $this->propix[$propName] );
                    return false;
                }
                $output = $this->rdate[$propix];
                foreach( $output[Util::$LCvalue] as $rIx => $rdatePart ) {
                    if( isset( $rdatePart[1]['invert'] )) { // fix pre 7.0.5 bug
                        $dateInterval = UtilDuration::DateIntervalArr2DateInterval( $rdatePart[1] );
                        $output[Util::$LCvalue][$rIx][1] = UtilDuration::dateInterval2arr( $dateInterval );
                    }
                }
                return ( $inclParam ) ? $output : $output[Util::$LCvalue];
                break;
            case Util::$RECURRENCE_ID:
                if( isset( $this->recurrenceid[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->recurrenceid : $this->recurrenceid[Util::$LCvalue];
                }
                break;
            case Util::$RELATED_TO:
                Util::recountMvalPropix( $this->relatedto, $propix );
                $this->propix[$propName] = $propix;
                if( ! isset( $this->relatedto[$propix] )) {
                    unset( $this->propix[$propName] );
                    return false;
                }
                return ( $inclParam ) ? $this->relatedto[$propix] : $this->relatedto[$propix][Util::$LCvalue];
                break;
            case Util::$REQUEST_STATUS:
                Util::recountMvalPropix( $this->requeststatus, $propix );
                $this->propix[$propName] = $propix;
                if( ! isset( $this->requeststatus[$propix] )) {
                    unset( $this->propix[$propName] );
                    return false;
                }
                return ( $inclParam ) ? $this->requeststatus[$propix] : $this->requeststatus[$propix][Util::$LCvalue];
                break;
            case Util::$RESOURCES:
                Util::recountMvalPropix( $this->resources, $propix );
                $this->propix[$propName] = $propix;
                if( ! isset( $this->resources[$propix] )) {
                    unset( $this->propix[$propName] );
                    return false;
                }
                return ( $inclParam ) ? $this->resources[$propix] : $this->resources[$propix][Util::$LCvalue];
                break;
            case Util::$RRULE:
                Util::recountMvalPropix( $this->rrule, $propix );
                $this->propix[$propName] = $propix;
                if( ! isset( $this->rrule[$propix] )) {
                    unset( $this->propix[$propName] );
                    return false;
                }
                return ( $inclParam ) ? $this->rrule[$propix] : $this->rrule[$propix][Util::$LCvalue];
                break;
            case Util::$SEQUENCE:
                if( isset( $this->sequence[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->sequence : $this->sequence[Util::$LCvalue];
                }
                break;
            case Util::$STATUS:
                if( isset( $this->status[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->status : $this->status[Util::$LCvalue];
                }
                break;
            case Util::$SUMMARY:
                if( isset( $this->summary[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->summary : $this->summary[Util::$LCvalue];
                }
                break;
            case Util::$TRANSP:
                if( isset( $this->transp[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->transp : $this->transp[Util::$LCvalue];
                }
                break;
            case Util::$TZNAME:
                Util::recountMvalPropix( $this->tzname, $propix );
                $this->propix[$propName] = $propix;
                if( ! isset( $this->tzname[$propix] )) {
                    unset( $this->propix[$propName] );
                    return false;
                }
                return ( $inclParam ) ? $this->tzname[$propix] : $this->tzname[$propix][Util::$LCvalue];
                break;
            case Util::$UID:
                if( Util::isCompInList( $this->compType, Util::$SUBCOMPS )) {
                    return false;
                }
                if( empty( $this->uid )) {
                    $this->uid = Util::makeUid( $this->getConfig( Util::$UNIQUE_ID ));
                }
                return ( $inclParam ) ? $this->uid : $this->uid[Util::$LCvalue];
                break;
            case Util::$URL:
                if( isset( $this->url[Util::$LCvalue] )) {
                    return ( $inclParam ) ? $this->url : $this->url[Util::$LCvalue];
                }
                break;
            default:
                if( $propName != Util::$X_PROP ) {
                    if( ! isset( $this->xprop[$propName] )) {
                        return false;
                    }
                    return ( $inclParam )
                        ? [ $propName, $this->xprop[$propName], ]
                        : [ $propName, $this->xprop[$propName][Util::$LCvalue], ];
                }
                else {
                    if( empty( $this->xprop )) {
                        return false;
                    }
                    $xpropno = 0;
                    foreach( $this->xprop as $xpropkey => $xpropvalue ) {
                        if( $propix == $xpropno ) {
                            return ( $inclParam )
                                ? [ $xpropkey, $this->xprop[$xpropkey], ]
                                : [ $xpropkey, $this->xprop[$xpropkey][Util::$LCvalue], ];
                        }
                        else {
                            $xpropno++;
                        }
                    }
                    return false; // not found ??
                }
        } // end switch( $propName )
        return false;
    }

    /**
     * Returns calendar property unique values
     *
     * For ATTENDEE, CATEGORIES, CONTACT, RELATED_TO or RESOURCES (keys)
     * and for each, number of occurrence (values)
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param string $propName
     * @param array  $output incremented result array
     */
    public function getProperties( $propName, & $output ) {
        if( empty( $output )) {
            $output = [];
        }
        if( ! Util::isPropInList( $propName, Util::$MPROPS1 )) {
            return;
        }
        while( false !== ( $content = $this->getProperty( $propName ))) {
            if( empty( $content )) {
                continue;
            }
            if( is_array( $content )) {
                foreach( $content as $part ) {
                    if( false !== strpos( $part, Util::$COMMA )) {
                        $part = explode( Util::$COMMA, $part );
                        foreach( $part as $contentPart ) {
                            $contentPart = trim( $contentPart );
                            if( ! empty( $contentPart )) {
                                if( ! isset( $output[$contentPart] )) {
                                    $output[$contentPart] = 1;
                                }
                                else {
                                    $output[$contentPart] += 1;
                                }
                            }
                        }
                    }
                    else {
                        $part = trim( $part );
                        if( ! isset( $output[$part] )) {
                            $output[$part] = 1;
                        }
                        else {
                            $output[$part] += 1;
                        }
                    }
                }
            } // end if( is_array( $content ))
            elseif( false !== strpos( $content, Util::$COMMA )) {
                $content = explode( Util::$COMMA, $content );
                foreach( $content as $contentPart ) {
                    $contentPart = trim( $contentPart );
                    if( ! empty( $contentPart )) {
                        if( ! isset( $output[$contentPart] )) {
                            $output[$contentPart] = 1;
                        }
                        else {
                            $output[$contentPart] += 1;
                        }
                    }
                }
            } // end elseif( false !== strpos( $content, Util::$COMMA ))
            else {
                $content = trim( $content );
                if( ! empty( $content )) {
                    if( ! isset( $output[$content] )) {
                        $output[$content] = 1;
                    }
                    else {
                        $output[$content] += 1;
                    }
                }
            }
        }
        ksort( $output );
    }

    /**
     * General component setProperty method
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param mixed $args variable number of function arguments,
     *                    first argument is ALWAYS component name,
     *                    second ALWAYS component value!
     * @return mixed array|bool
     */
    public function setProperty( $args ) {
        $numargs = func_num_args();
        if( 1 > $numargs ) {
            return false;
        }
        $args    = func_get_args();
        if( $this->notExistProp( $args[0] )) {
            return false;
        }
        if( ! $this->getConfig( Util::$ALLOWEMPTY ) &&
            ( ! isset( $args[1] ) || empty( $args[1] ))) {
            return false;
        }
        $args[0] = strtoupper( $args[0] );
        for( $argix = $numargs; $argix < 12; $argix++ ) {
            if( ! isset( $args[$argix] )) {
                $args[$argix] = null;
            }
        }
        switch( $args[0] ) {
            case Util::$ACTION:
                return $this->setAction( $args[1], $args[2] );
            case Util::$ATTACH:
                return $this->setAttach( $args[1], $args[2], $args[3] );
            case Util::$ATTENDEE:
                return $this->setAttendee( $args[1], $args[2], $args[3] );
            case Util::$CATEGORIES:
                return $this->setCategories( $args[1], $args[2], $args[3] );
            case Util::$CLASS:
                return $this->setClass( $args[1], $args[2] );
            case Util::$COMMENT:
                return $this->setComment( $args[1], $args[2], $args[3] );
            case Util::$COMPLETED:
                return $this->setCompleted( $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7] );
            case Util::$CONTACT:
                return $this->setContact( $args[1], $args[2], $args[3] );
            case Util::$CREATED:
                return $this->setCreated( $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7] );
            case Util::$DESCRIPTION:
                return $this->setDescription( $args[1], $args[2], $args[3] );
            case Util::$DTEND:
                return $this->setDtend( $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8] );
            case Util::$DTSTAMP:
                return $this->setDtstamp( $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7] );
            case Util::$DTSTART:
                return $this->setDtstart( $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8] );
            case Util::$DUE:
                return $this->setDue( $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8] );
            case Util::$DURATION:
                return $this->setDuration( $args[1], $args[2], $args[3], $args[4], $args[5],  $args[6] );
            case Util::$EXDATE:
                return $this->setExdate( $args[1], $args[2], $args[3] );
            case Util::$EXRULE:
                return $this->setExrule( $args[1], $args[2], $args[3] );
            case Util::$FREEBUSY:
                return $this->setFreebusy( $args[1], $args[2], $args[3], $args[4] );
            case Util::$GEO:
                return $this->setGeo( $args[1], $args[2], $args[3] );
            case Util::$LAST_MODIFIED:
                return $this->setLastModified( $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7] );
            case Util::$LOCATION:
                return $this->setLocation( $args[1], $args[2] );
            case Util::$ORGANIZER:
                return $this->setOrganizer( $args[1], $args[2] );
            case Util::$PERCENT_COMPLETE:
                return $this->setPercentComplete( $args[1], $args[2] );
            case Util::$PRIORITY:
                return $this->setPriority( $args[1], $args[2] );
            case Util::$RDATE:
                return $this->setRdate( $args[1], $args[2], $args[3] );
            case Util::$RECURRENCE_ID:
                return $this->setRecurrenceid( $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8] );
            case Util::$RELATED_TO:
                return $this->setRelatedTo( $args[1], $args[2], $args[3] );
            case Util::$REPEAT:
                return $this->setRepeat( $args[1], $args[2] );
            case Util::$REQUEST_STATUS:
                return $this->setRequestStatus( $args[1], $args[2], $args[3], $args[4], $args[5] );
            case Util::$RESOURCES:
                return $this->setResources( $args[1], $args[2], $args[3] );
            case Util::$RRULE:
                return $this->setRrule( $args[1], $args[2], $args[3] );
            case Util::$SEQUENCE:
                return $this->setSequence( $args[1], $args[2] );
            case Util::$STATUS:
                return $this->setStatus( $args[1], $args[2] );
            case Util::$SUMMARY:
                return $this->setSummary( $args[1], $args[2] );
            case Util::$TRANSP:
                return $this->setTransp( $args[1], $args[2] );
            case Util::$TRIGGER:
                return $this->setTrigger( $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9], $args[10], $args[11] );
            case Util::$TZID:
                return $this->setTzid( $args[1], $args[2] );
            case Util::$TZNAME:
                return $this->setTzname( $args[1], $args[2], $args[3] );
            case Util::$TZOFFSETFROM:
                return $this->setTzoffsetfrom( $args[1], $args[2] );
            case Util::$TZOFFSETTO:
                return $this->setTzoffsetto( $args[1], $args[2] );
            case Util::$TZURL:
                return $this->setTzurl( $args[1], $args[2] );
            case Util::$UID:
                return $this->setUid( $args[1], $args[2] );
            case Util::$URL:
                return $this->setUrl( $args[1], $args[2] );
            default:
                return $this->setXprop( $args[0], $args[1], $args[2] );
        }
    }

    /**
     * Parse data into component properties
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param mixed $unparsedtext strict rfc2445 formatted, single property string or array of strings
     * @return bool true if ok else false if error occurs during parsing
     */
    public function parse( $unparsedtext = null ) {
        static $NLCHARS       = '\n';
        static $BEGIN         = 'BEGIN:';
        static $ENDALARM      = 'END:VALARM';
        static $ENDDAYLIGHT   = 'END:DAYLIGHT';
        static $ENDSTANDARD   = 'END:STANDARD';
        static $END           = 'END:';
        static $BEGINVALARM   = 'BEGIN:VALARM';
        static $BEGINSTANDARD = 'BEGIN:STANDARD';
        static $BEGINDAYLIGHT = 'BEGIN:DAYLIGHT';
        static $TEXTPROPS     = [ 'CATEGORIES', 'COMMENT', 'DESCRIPTION', 'SUMMARY', ];
        static $SS            = '/';
        static $EQ            = '=';
        if( ! empty( $unparsedtext )) {
            $arrParse = false;
            if( is_array( $unparsedtext )) {
                $unparsedtext = \implode( $NLCHARS . Util::$CRLF, $unparsedtext );
                $arrParse     = true;
            }
            $rows = Util::convEolChar( $unparsedtext );
            if( $arrParse ) {
                foreach( $rows as $lix => $row ) {
                    $rows[$lix] = Util::trimTrailNL( $row );
                }
            }
        }
        elseif( ! isset( $this->unparsed )) {
            $rows = [];
        }
        else {
            $rows = $this->unparsed;
        }
        /* skip leading (empty/invalid) lines */
        foreach( $rows as $lix => $row ) {
            if( false !== ( $pos = stripos( $row, $BEGIN ))) {
                $rows[$lix] = substr( $row, $pos );
                break;
            }
            $tst = trim( $row );
            if(( $NLCHARS == $tst ) || empty( $tst )) {
                unset( $rows[$lix] );
            }
        }
        $this->unparsed = [];
        $comp           = $this;
        $config         = $this->getConfig();
        $compSync       = $subSync = 0;
        foreach( $rows as $lix => $row ) {
            switch( true ) {
                case ( 0 == strcasecmp( $ENDALARM, substr( $row, 0, 10 ))) :
                    if( 1 != $subSync ) {
                        return false;
                    }
                    $this->components[] = $comp;
                    $subSync -= 1;
                    break;
                case ( 0 == strcasecmp( $ENDDAYLIGHT, substr( $row, 0, 12 ))) :
                    if( 1 != $subSync ) {
                        return false;
                    }
                    $this->components[] = $comp;
                    $subSync -= 1;
                    break;
                case ( 0 == strcasecmp( $ENDSTANDARD, substr( $row, 0, 12 ))) :
                    if( 1 != $subSync ) {
                        return false;
                    }
                    array_unshift( $this->components, $comp );
                    $subSync -= 1;
                    break;
                case ( 0 == strcasecmp( $END, substr( $row, 0, 4 ))) :
                    if( 1 != $compSync ) { // end:<component>
                        return false;
                    }
                    if( 0 < $subSync ) {
                        $this->components[] = $comp;
                    }
                    $compSync -= 1;
                    break 2;  /* skip trailing empty lines */
                case ( 0 == strcasecmp( $BEGINVALARM, substr( $row, 0, 12 ))) :
                    $comp = new Valarm( $config );
                    $subSync += 1;
                    break;
                case ( 0 == strcasecmp( $BEGINSTANDARD, substr( $row, 0, 14 ))) :
                    $comp = new Vtimezone( self::STANDARD, $config );
                    $subSync += 1;
                    break;
                case ( 0 == strcasecmp( $BEGINDAYLIGHT, substr( $row, 0, 14 ))) :
                    $comp = new Vtimezone( self::DAYLIGHT, $config );
                    $subSync += 1;
                    break;
                case ( 0 == strcasecmp( $BEGIN, substr( $row, 0, 6 ))) :
                    $compSync += 1;         // begin:<component>
                    break;
                default :
                    $comp->unparsed[] = $row;
                    break;
            } // end switch( true )
        } // end foreach( $rows as $lix => $row )
        if( 0 < $subSync ) { // subcomp without END...
            $this->components[] = $comp;
            unset( $comp );
        }
        /* concatenate property values spread over several lines */
        $this->unparsed = Util::concatRows( $this->unparsed );
        /* parse each property 'line' */
        foreach( $this->unparsed as $lix => $row ) {
            /* get propname */
            /* split property name  and  opt.params and value */
            list( $propName, $row ) = Util::getPropName( $row );
            if( Util::isXprefixed( $propName )) {
                $propName2 = $propName;
                $propName  = Util::$X_;
            }
            if( ! Util::isPropInList( strtoupper( $propName ), Util::$PROPNAMES )) {
                continue;
            } // skip non standard property names
            /* separate attributes from value */
            Util::splitContent( $row, $propAttr );
            if(( $NLCHARS == strtolower( substr( $row, -2 ))) &&
                ! Util::isPropInList( strtoupper( $propName ), $TEXTPROPS ) &&
                ( ! Util::isXprefixed( $propName ))) {
                $row = Util::trimTrailNL( $row );
            }
            /* call setProperty( $propName.. . */
            switch( strtoupper( $propName )) {
                case Util::$ATTENDEE :
                    foreach( $propAttr as $pix => $attr ) {
                        if( ! in_array( strtoupper( $pix ), Util::$ATTENDEEPARKEYS )) {
                            continue;
                        }  // 'MEMBER', 'DELEGATED-TO', 'DELEGATED-FROM'
                        $attr2 = explode( Util::$COMMA, $attr );
                        if( 1 < count( $attr2 )) {
                            $propAttr[$pix] = $attr2;
                        }
                    }
                    $this->setProperty( $propName, $row, $propAttr );
                    break;
                case Util::$CATEGORIES :
                    // fall through
                case Util::$RESOURCES :
                    if( false !== strpos( $row, Util::$COMMA )) {
                        $content = Util::commaSplit( $row );
                        if( 1 < count( $content )) {
                            foreach( $content as & $contentPart ) {
                                $contentPart = Util::strunrep( $contentPart );
                            }
                            $this->setProperty( $propName, $content, $propAttr );
                            break;
                        }
                        else {
                            $row = reset( $content );
                        }
                    } // fall through
                case Util::$COMMENT :
                    // fall through
                case Util::$CONTACT :
                // fall through
                case Util::$DESCRIPTION :
                // fall through
                case Util::$LOCATION :
                // fall through
                case Util::$SUMMARY :
                    if( empty( $row )) {
                        $propAttr = null;
                    }
                    $this->setProperty( $propName, Util::strunrep( $row ), $propAttr );
                    break;
                case Util::$REQUEST_STATUS :
                    $values    = explode( Util::$SEMIC, $row, 3 );
                    $values[1] = ( isset( $values[1] )) ? Util::strunrep( $values[1] ) : null;
                    $values[2] = ( isset( $values[2] )) ? Util::strunrep( $values[2] ) : null;
                    $this->setProperty( $propName
                        , $values[0]  // statcode
                        , $values[1]  // statdesc
                        , $values[2]  // extdata
                        , $propAttr
                    );
                    break;
                case Util::$FREEBUSY :
                    $class = get_called_class();
                    if( ! isset( $class::$UCFBTYPE )) {
                        break;
                    } // freebusy-prop in a non-freebusy component??
                    $fbtype = ( isset( $propAttr[$class::$UCFBTYPE] ))
                        ? $propAttr[$class::$UCFBTYPE] : null; // force default
                    unset( $propAttr[$class::$UCFBTYPE] );
                    $values = explode( Util::$COMMA, $row );
                    foreach( $values as $vix => $value ) {
                        $value2 = explode( $SS, $value ); // '/'
                        if( 1 < count( $value2 )) {
                            $values[$vix] = $value2;
                        }
                    }
                    $this->setProperty( $propName, $fbtype, $values, $propAttr );
                    break;
                case Util::$GEO :
                    $value = explode( Util::$SEMIC, $row, 2 );
                    if( 2 > count( $value )) {
                        $value[1] = null;
                    }
                    $this->setProperty( $propName, $value[0], $value[1], $propAttr );
                    break;
                case Util::$EXDATE :
                    $values = ( empty( $row )) ? null : \explode( Util::$COMMA, $row );
                    $this->setProperty( $propName, $values, $propAttr );
                    break;
                case Util::$RDATE :
                    if( empty( $row )) {
                        $this->setProperty( $propName, $row, $propAttr );
                        break;
                    }
                    $values = explode( Util::$COMMA, $row );
                    foreach( $values as $vix => $value ) {
                        $value2 = explode( $SS, $value );
                        if( 1 < count( $value2 )) {
                            $values[$vix] = $value2;
                        }
                    }
                    $this->setProperty( $propName, $values, $propAttr );
                    break;
                case Util::$EXRULE :
                    // fall through
                case Util::$RRULE :
                    $values = explode( Util::$SEMIC, $row );
                    $recur  = [];
                    foreach( $values as $value2 ) {
                        if( empty( $value2 )) {
                            continue;
                        } // ;-char in end position ???
                        $value3    = explode( $EQ, $value2, 2 );
                        $rulelabel = strtoupper( $value3[0] );
                        switch( $rulelabel ) {
                            case Util::$BYDAY:
                                    $value4 = explode( Util::$COMMA, $value3[1] );
                                    if( 1 < count( $value4 )) {
                                        foreach( $value4 as $v5ix => $value5 ) {
                                            $value6 = [];
                                            $dayno  = $dayname = null;
                                            $value5 = trim((string) $value5 );
                                            if(( ctype_alpha( substr( $value5, -1 ))) &&
                                               ( ctype_alpha( substr( $value5, -2, 1 )))) {
                                                $dayname = substr( $value5, -2, 2 );
                                                if( 2 < strlen( $value5 )) {
                                                    $dayno = substr( $value5, 0, ( strlen( $value5 ) - 2 ));
                                                }
                                            }
                                            if( $dayno ) {
                                                $value6[] = $dayno;
                                            }
                                            if( $dayname ) {
                                                $value6[Util::$DAY] = $dayname;
                                            }
                                            $value4[$v5ix] = $value6;
                                        }
                                    }
                                    else {
                                        $value4 = [];
                                        $dayno  = $dayname = null;
                                        $value5 = trim((string) $value3[1] );
                                        if(( ctype_alpha( substr( $value5, -1 ))) &&
                                           ( ctype_alpha( substr( $value5, -2, 1 )))) {
                                            $dayname = substr( $value5, -2, 2 );
                                            if( 2 < strlen( $value5 )) {
                                                $dayno = substr( $value5, 0, ( strlen( $value5 ) - 2 ));
                                            }
                                        }
                                        if( $dayno ) {
                                            $value4[] = $dayno;
                                        }
                                        if( $dayname ) {
                                            $value4[Util::$DAY] = $dayname;
                                        }
                                    }
                                    $recur[$rulelabel] = $value4;
                                    break;
                            default:
                                    $value4 = explode( Util::$COMMA, $value3[1] );
                                    if( 1 < count( $value4 )) {
                                        $value3[1] = $value4;
                                    }
                                    $recur[$rulelabel] = $value3[1];
                                    break;
                        } // end - switch $rulelabel
                    } // end - foreach( $values.. .
                    $this->setProperty( $propName, $recur, $propAttr );
                    break;
                case Util::$X_ :
                    $propName = ( isset( $propName2 )) ? $propName2 : $propName;
                    unset( $propName2 );
                // fall through
                case Util::$ACTION :
                // fall through
                case Util::$STATUS :
                // fall through
                case Util::$TRANSP :
                // fall through
                case Util::$UID :
                // fall through
                case Util::$TZID :
                // fall through
                case Util::$RELATED_TO :
                // fall through
                case Util::$TZNAME :
                    $row = Util::strunrep( $row );
                // fall through
                default:
                    $this->setProperty( $propName, $row, $propAttr );
                    break;
            } // end  switch( $propName.. .
        } // end foreach( $this->unparsed as $lix => $row )
        unset( $this->unparsed );
        if( $this->countComponents() > 0 ) {
            foreach( $this->components as $ckey => $component ) {
                if( ! empty( $this->components[$ckey] ) &&
                    ! empty( $this->components[$ckey]->unparsed )) {
                    $this->components[$ckey]->parse();
                }
            }
        }
        return true;
    }

    /**
     * Return calendar component subcomponent from component container
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.1 - 2018-11-17
     * @param mixed $arg1 ordno/component type/ component uid
     * @param mixed $arg2 ordno if arg1 = component type
     * @return mixed CalendarComponent|bool
     */
    public function getComponent( $arg1 = null, $arg2 = null ) {
        if( empty( $this->components )) {
            return false;
        }
        $index = $argType = null;
        switch( true ) {
            case ( is_null( $arg1 )) :
                $argType = self::$INDEX;
                $this->compix[self::$INDEX] = ( isset( $this->compix[self::$INDEX] ))
                    ? $this->compix[self::$INDEX] + 1 : 1;
                $index   = $this->compix[self::$INDEX];
                break;
            case ( ctype_digit((string) $arg1 )) :
                $argType = self::$INDEX;
                $index   = (int) $arg1;
                $this->compix = [];
                break;
            case ( Util::isCompInList( $arg1, Util::$SUBCOMPS )) : // class name
                unset( $this->compix[self::$INDEX] );
                $argType = strtolower( $arg1 );
                if( is_null( $arg2 )) {
                    $index = $this->compix[$argType] = ( isset( $this->compix[$argType] ))
                        ? $this->compix[$argType] + 1 : 1;
                }
                else {
                    $index = (int) $arg2;
                }
                break;
        }
        $index -= 1;
        $ckeys = array_keys( $this->components );
        if( ! empty( $index ) && ( $index > end( $ckeys ))) {
            return false;
        }
        $cix2gC = 0;
        foreach( $ckeys as $cix ) {
            if( empty( $this->components[$cix] )) {
                continue;
            }
            if(( self::$INDEX == $argType ) && ( $index == $cix )) {
                return clone $this->components[$cix];
            }
            elseif(( strcasecmp( $this->components[$cix]->compType, $argType ) == 0 ) ||
                ( isset( $this->components[$cix]->timezonetype ) &&
                    ( strcasecmp( $this->components[$cix]->timezonetype, $argType ) == 0 ))) {
                if( $index == $cix2gC ) {
                    return clone $this->components[$cix];
                }
                $cix2gC++;
            }
        }
        /* not found.. . */
        $this->compix = [];
        return false;
    }

    /**
     * Add calendar component as subcomponent to container for subcomponents
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  1.x.x - 2007-04-24
     * @param object $component calendar component
     * @return static
     */
    public function addSubComponent( $component ) {
        $this->setComponent( $component );
        return $this;
    }

    /**
     * Return formatted output for subcomponents
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @return string
     */
    public function createSubComponent() {
        static $DATEKEY = '%04d%02d%02d%02d%02d%02d000';
        $output = null;
        if( self::VTIMEZONE == $this->compType ) { // sort : standard, daylight, in dtstart order
            $stdarr = $dlarr = [];
            foreach( $this->components as $cix => $component ) {
                if( empty( $component )) {
                    continue;
                }
                $dt  = $component->getProperty( Util::$DTSTART );
                $key = (int) sprintf(
                    $DATEKEY,
                    (int) $dt[Util::$LCYEAR],
                    (int) $dt[Util::$LCMONTH],
                    (int) $dt[Util::$LCDAY],
                    (int) $dt[Util::$LCHOUR],
                    (int) $dt[Util::$LCMIN],
                    (int) $dt[Util::$LCSEC]
                );
                if( self::STANDARD == $component->compType ) {
                    while( isset( $stdarr[$key] )) {
                        $key += 1;
                    }
                    $stdarr[$key] = $component;
                }
                elseif( self::DAYLIGHT == $component->compType ) {
                    while( isset( $dlarr[$key] )) {
                        $key += 1;
                    }
                    $dlarr[$key] = $component;
                }
            } // end foreach(...
            $this->components = [];
            ksort( $stdarr, SORT_NUMERIC );
            foreach( $stdarr as $std ) {
                $this->components[] = $std;
            }
            unset( $stdarr );
            ksort( $dlarr, SORT_NUMERIC );
            foreach( $dlarr as $dl ) {
                $this->components[] = $dl;
            }
            unset( $dlarr );
        } // end if( Util::$VTIMEZONE == $this->compType )
        $config = $this->getConfig();
        foreach( $this->components as $cix => $component ) {
            if( empty( $component )) {
                continue;
            }
            $this->components[$cix]->setConfig( $config, false, true );
            $output .= $this->components[$cix]->createComponent();
        }
        return $output;
    }
}
