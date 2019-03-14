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
use DateInterval;
use SimpleXMLElement;

use function array_change_key_case;
use function array_key_exists;
use function file_get_contents;
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
 * @since  2.26.7 - 2018-12-05
 */
class IcalXML
{
    private static $Vcalendar      = 'vcalendar';
    private static $calProps       = [
        'version',
        'prodid',
        'calscale',
        'method',
    ];
    private static $properties     = 'properties';
    private static $PARAMETERS     = 'parameters';
    private static $components     = 'components';
    private static $text           = 'text';
    private static $binary         = 'binary';
    private static $uri            = 'uri';
    private static $date           = 'date';
    private static $date_time      = 'date-time';
    private static $fbtype         = 'fbtype';
    private static $FBTYPE         = 'FBTYPE';
    private static $period         = 'period';
    private static $rstatus        = 'rstatus';
    private static $unknown        = 'unknown';
    private static $recur          = 'recur';
    private static $cal_address    = 'cal-address';
    private static $integer        = 'integer';
    private static $utc_offset     = 'utc-offset';
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
    private static $code           = 'code';
    private static $statcode       = 'statcode';
    private static $extdata        = 'extdata';
    private static $data           = 'data';
    private static $time           = 'time';
    private static $latitude       = 'latitude';
    private static $longitude      = 'longitude';

    /**
     * Return iCal XML (rfc6321) output, using PHP SimpleXMLElement
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-12-02
     * @param Vcalendar $calendar iCalcreator Vcalendar instance reference
     * @return string
     * @static
     */
    public static function iCal2XML( Vcalendar $calendar ) {
        static $YMDTHISZ = 'Ymd\THis\Z';
        static $XMLstart = '<?xml version="1.0" encoding="utf-8"?><icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0"><!-- created %s using kigkonsult.se %s iCal2XMl (rfc6321) --></icalendar>';
        /** fix an SimpleXMLElement instance and create root element */
        $xml       = new SimpleXMLElement( sprintf( $XMLstart, gmdate( $YMDTHISZ ), ICALCREATOR_VERSION ) );
        $Vcalendar = $xml->addChild( iCalXML::$Vcalendar );
        /** fix calendar properties */
        $properties = $Vcalendar->addChild( iCalXML::$properties );
        foreach( iCalXML::$calProps as $calProp ) {
            if( false !== ( $content = $calendar->getProperty( $calProp ))) {
                iCalXML::addXMLchild( $properties, $calProp, iCalXML::$text, $content );
            }
        }
        while( false !== ( $content = $calendar->getProperty( false, false, true ))) {
            iCalXML::addXMLchild(
                $properties,
                $content[0],
                iCalXML::$unknown,
                $content[1][Util::$LCvalue],
                $content[1][Util::$LCparams]
            );
        }
        $langCal = $calendar->getConfig( Util::$LANGUAGE );
        /** prepare to fix components with properties */
        $components = $Vcalendar->addChild( iCalXML::$components );
        /** fix component properties */
        while( false !== ( $component = $calendar->getComponent())) {
            $compName   = $component->compType;
            $child      = $components->addChild( strtolower( $compName ));
            $properties = $child->addChild( iCalXML::$properties );
            $langComp   = $component->getConfig( Util::$LANGUAGE );
            $props      = $component->getConfig( Util::$SETPROPERTYNAMES );
            foreach( $props as $pix => $propName ) {
                switch( strtoupper( $propName )) {
                    case Util::$ATTACH:          // may occur multiple times, below
                        while( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            $type = ( Util::isParamsValueSet( $content, Util::$BINARY ))
                                ? iCalXML::$binary
                                : iCalXML::$uri;
                            unset( $content[Util::$LCparams][Util::$VALUE] );
                            iCalXML::addXMLchild(
                                $properties,
                                $propName,
                                $type,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Util::$ATTENDEE:
                        while( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            if( isset( $content[Util::$LCparams][Util::$CN] ) &&
                              ! isset( $content[Util::$LCparams][Util::$LANGUAGE] )) {
                                if( $langComp ) {
                                    $content[Util::$LCparams][Util::$LANGUAGE] = $langComp;
                                }
                                elseif( $langCal ) {
                                    $content[Util::$LCparams][Util::$LANGUAGE] = $langCal;
                                }
                            }
                            iCalXML::addXMLchild(
                                $properties,
                                $propName,
                                iCalXML::$cal_address,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Util::$EXDATE:
                        while( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            $type = ( Util::isParamsValueSet( $content, Util::$DATE ))
                                ? iCalXML::$date
                                : iCalXML::$date_time;
                            unset( $content[Util::$LCparams][Util::$VALUE] );
                            iCalXML::addXMLchild(
                                $properties,
                                $propName,
                                $type,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Util::$FREEBUSY:
                        while( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            if( is_array( $content ) &&
                                isset( $content[Util::$LCvalue][iCalXML::$fbtype] )) {
                                $content[Util::$LCparams][iCalXML::$FBTYPE] =
                                    $content[Util::$LCvalue][iCalXML::$fbtype];
                                unset( $content[Util::$LCvalue][iCalXML::$fbtype] );
                            }
                            iCalXML::addXMLchild(
                                $properties,
                                $propName,
                                iCalXML::$period,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Util::$REQUEST_STATUS:
                        while( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            if( ! isset( $content[Util::$LCparams][Util::$LANGUAGE] )) {
                                if( $langComp ) {
                                    $content[Util::$LCparams][Util::$LANGUAGE] = $langComp;
                                }
                                elseif( $langCal ) {
                                    $content[Util::$LCparams][Util::$LANGUAGE] = $langCal;
                                }
                            }
                            iCalXML::addXMLchild(
                                $properties,
                                $propName,
                                iCalXML::$rstatus,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Util::$RDATE:
                        while( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            $type = iCalXML::$date_time;
                            if( Util::isParamsValueSet( $content, Util::$DATE )) {
                                $type = iCalXML::$date;
                            }
                            elseif( Util::isParamsValueSet( $content, Util::$PERIOD )) {
                                $type = iCalXML::$period;
                            }
                            unset( $content[Util::$LCparams][Util::$VALUE] );
                            iCalXML::addXMLchild(
                                $properties,
                                $propName,
                                $type,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Util::$CATEGORIES:  // fall through
                    case Util::$COMMENT:     // fall through
                    case Util::$CONTACT:     // fall through
                    case Util::$DESCRIPTION: // fall through
                    case Util::$RELATED_TO:  // fall through
                    case Util::$RESOURCES:
                        while( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            if(( Util::$RELATED_TO != $propName ) &&
                                ! isset( $content[Util::$LCparams][Util::$LANGUAGE] )) {
                                if( $langComp ) {
                                    $content[Util::$LCparams][Util::$LANGUAGE] = $langComp;
                                }
                                elseif( $langCal ) {
                                    $content[Util::$LCparams][Util::$LANGUAGE] = $langCal;
                                }
                            }
                            iCalXML::addXMLchild(
                                $properties,
                                $propName,
                                iCalXML::$text,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Util::$X_PROP:
                        while( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            iCalXML::addXMLchild(
                                $properties,
                                $content[0],
                                iCalXML::$unknown,
                                $content[1][Util::$LCvalue],
                                $content[1][Util::$LCparams]
                            );
                        }
                        break;
                    case Util::$CREATED:         // single occurence below, if set
                    case Util::$COMPLETED:       // fall through
                    case Util::$DTSTAMP:         // fall through
                    case Util::$LAST_MODIFIED:   // fall through
                    case Util::$DTSTART:         // fall through
                    case Util::$DTEND:           // fall through
                    case Util::$DUE:             // fall through
                    case Util::$RECURRENCE_ID:   // fall through
                        if( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            $type = ( Util::isParamsValueSet( $content, Util::$DATE ))
                                ? iCalXML::$date : iCalXML::$date_time;
                            unset( $content[Util::$LCparams][Util::$VALUE] );
                            if(( isset( $content[Util::$LCparams][Util::$TZID] ) &&
                                 empty( $content[Util::$LCparams][Util::$TZID] )) ||
                                @is_null( $content[Util::$LCparams][Util::$TZID] )) {
                                unset( $content[Util::$LCparams][Util::$TZID] );
                            }
                            iCalXML::addXMLchild(
                                $properties,
                                $propName,
                                $type,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Util::$DURATION:
                        if( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            iCalXML::addXMLchild(
                                $properties,
                                $propName,
                                strtolower( Util::$DURATION ),
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Util::$EXRULE:
                    case Util::$RRULE:
                        while( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            iCalXML::addXMLchild(
                                $properties,
                                $propName,
                                iCalXML::$recur,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Util::$CLASS:    // fall through
                    case Util::$LOCATION: // fall through
                    case Util::$STATUS:   // fall through
                    case Util::$SUMMARY:  // fall through
                    case Util::$TRANSP:   // fall through
                    case Util::$TZID:     // fall through
                    case Util::$UID:
                        if( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            if((( Util::$LOCATION == $propName ) || ( Util::$SUMMARY == $propName )) &&
                                ! isset( $content[Util::$LCparams][Util::$LANGUAGE] )) {
                                if( $langComp ) {
                                    $content[Util::$LCparams][Util::$LANGUAGE] = $langComp;
                                }
                                elseif( $langCal ) {
                                    $content[Util::$LCparams][Util::$LANGUAGE] = $langCal;
                                }
                            }
                            iCalXML::addXMLchild(
                                $properties,
                                $propName,
                                iCalXML::$text,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Util::$GEO:
                        if( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            iCalXML::addXMLchild(
                                $properties,
                                $propName,
                                strtolower( Util::$GEO ),
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Util::$ORGANIZER:
                        if( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            if( isset( $content[Util::$LCparams][Util::$CN] ) &&
                              ! isset( $content[Util::$LCparams][Util::$LANGUAGE] )) {
                                if( $langComp ) {
                                    $content[Util::$LCparams][Util::$LANGUAGE] = $langComp;
                                }
                                elseif( $langCal ) {
                                    $content[Util::$LCparams][Util::$LANGUAGE] = $langCal;
                                }
                            }
                            iCalXML::addXMLchild(
                                $properties,
                                $propName,
                                iCalXML::$cal_address,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Util::$PERCENT_COMPLETE: // fall through
                    case Util::$PRIORITY:         // fall through
                    case Util::$SEQUENCE:
                        if( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            iCalXML::addXMLchild(
                                $properties,
                                $propName,
                                iCalXML::$integer,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                    case Util::$TZURL:  // fall through
                    case Util::$URL:
                        if( false !== ( $content = $component->getProperty( $propName, false, true ))) {
                            iCalXML::addXMLchild(
                                $properties,
                                $propName,
                                iCalXML::$uri,
                                $content[Util::$LCvalue],
                                $content[Util::$LCparams]
                            );
                        }
                        break;
                } // end switch( $propName )
            } // end foreach( $props as $pix => $propName )
            /** fix subComponent properties, if any */
            while( false !== ( $subcomp = $component->getComponent())) {
                $subCompName  = $subcomp->compType;
                $child2       = $child->addChild( strtolower( $subCompName ));
                $properties   = $child2->addChild( iCalXML::$properties );
                $langComp     = $subcomp->getConfig( Util::$LANGUAGE );
                $subCompProps = $subcomp->getConfig( Util::$SETPROPERTYNAMES );
                foreach( $subCompProps as $pix2 => $propName ) {
                    switch( strtoupper( $propName )) {
                        case Util::$ATTACH:          // may occur multiple times, below
                            while( false !== ( $content = $subcomp->getProperty( $propName, false, true ))) {
                                $type = ( Util::isParamsValueSet( $content, Util::$BINARY ))
                                    ? iCalXML::$binary : iCalXML::$uri;
                                unset( $content[Util::$LCparams][Util::$VALUE] );
                                iCalXML::addXMLchild(
                                    $properties,
                                    $propName,
                                    $type,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        case Util::$ATTENDEE:
                            while( false !== ( $content = $subcomp->getProperty( $propName, false, true ))) {
                                if( isset( $content[Util::$LCparams][Util::$CN] ) &&
                                  ! isset( $content[Util::$LCparams][Util::$LANGUAGE] )) {
                                    if( $langComp ) {
                                        $content[Util::$LCparams][Util::$LANGUAGE] = $langComp;
                                    }
                                    elseif( $langCal ) {
                                        $content[Util::$LCparams][Util::$LANGUAGE] = $langCal;
                                    }
                                }
                                iCalXML::addXMLchild(
                                    $properties,
                                    $propName,
                                    iCalXML::$cal_address,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        case Util::$COMMENT: // fall through
                        case Util::$TZNAME:
                            while( false !== ( $content = $subcomp->getProperty( $propName, false, true ))) {
                                if( ! isset( $content[Util::$LCparams][Util::$LANGUAGE] )) {
                                    if( $langComp ) {
                                        $content[Util::$LCparams][Util::$LANGUAGE] = $langComp;
                                    }
                                    elseif( $langCal ) {
                                        $content[Util::$LCparams][Util::$LANGUAGE] = $langCal;
                                    }
                                }
                                iCalXML::addXMLchild(
                                    $properties,
                                    $propName,
                                    iCalXML::$text,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        case Util::$RDATE:
                            while( false !== ( $content = $subcomp->getProperty( $propName, false, true ))) {
                                $type = iCalXML::$date_time;
                                if( isset( $content[Util::$LCparams][Util::$VALUE] )) {
                                    if( Util::isParamsValueSet( $content, Util::$DATE )) {
                                        $type = iCalXML::$date;
                                    }
                                    elseif( Util::isParamsValueSet( $content, Util::$PERIOD )) {
                                        $type = iCalXML::$period;
                                    }
                                }
                                unset( $content[Util::$LCparams][Util::$VALUE] );
                                iCalXML::addXMLchild(
                                    $properties,
                                    $propName,
                                    $type,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        case Util::$X_PROP:
                            while( false !== ( $content = $subcomp->getProperty( $propName, false, true ))) {
                                iCalXML::addXMLchild(
                                    $properties,
                                    $content[0],
                                    iCalXML::$unknown,
                                    $content[1][Util::$LCvalue],
                                    $content[1][Util::$LCparams]
                                );
                            }
                            break;
                        case Util::$ACTION:      // single occurence below, if set
                        case Util::$DESCRIPTION: // fall through
                        case Util::$SUMMARY:
                            if( false !== ( $content = $subcomp->getProperty( $propName, false,  true ))) {
                                if(( Util::$ACTION != $propName ) &&
                                    ! isset( $content[Util::$LCparams][Util::$LANGUAGE] )) {
                                    if( $langComp ) {
                                        $content[Util::$LCparams][Util::$LANGUAGE] = $langComp;
                                    }
                                    elseif( $langCal ) {
                                        $content[Util::$LCparams][Util::$LANGUAGE] = $langCal;
                                    }
                                }
                                iCalXML::addXMLchild(
                                    $properties,
                                    $propName,
                                    iCalXML::$text,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        case Util::$DTSTART:
                            if( false !== ( $content = $subcomp->getProperty( $propName, false, true ))) {
                                unset( $content[Util::$LCvalue][Util::$LCtz],
                                    $content[Util::$LCparams][Util::$VALUE]
                                ); // always local time
                                iCalXML::addXMLchild(
                                    $properties,
                                    $propName,
                                    iCalXML::$date_time,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        case Util::$DURATION:
                            if( false !== ( $content = $subcomp->getProperty( $propName, false, true ))) {
                                iCalXML::addXMLchild(
                                    $properties,
                                    $propName,
                                    strtolower( Util::$DURATION ),
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        case Util::$REPEAT:
                            if( false !== ( $content = $subcomp->getProperty( $propName, false, true ))) {
                                iCalXML::addXMLchild(
                                    $properties,
                                    $propName,
                                    iCalXML::$integer,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        case Util::$TRIGGER:
                            if( false !== ( $content = $subcomp->getProperty( $propName, false, true ))) {
                                if(( $content[Util::$LCvalue] instanceof DateInterval ) ||
                                    ( is_array( $content[Util::$LCvalue] ) &&
                                    ( isset( $content[Util::$LCvalue][Util::$LCWEEK] ) ||
                                      isset( $content[Util::$LCvalue]['invert'] )))) {
                                    $type = strtolower( Util::$DURATION );
                                }
                                else {
                                    $type = iCalXML::$date_time;
                                }
                                iCalXML::addXMLchild(
                                    $properties,
                                    $propName,
                                    $type,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        case Util::$TZOFFSETFROM: // fall through
                        case Util::$TZOFFSETTO:
                            if( false !== ( $content = $subcomp->getProperty( $propName, false, true ))) {
                                iCalXML::addXMLchild(
                                    $properties,
                                    $propName,
                                    iCalXML::$utc_offset,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
                                );
                            }
                            break;
                        case Util::$RRULE:
                            while( false !== ( $content = $subcomp->getProperty( $propName, false, true ))) {
                                iCalXML::addXMLchild(
                                    $properties,
                                    $propName,
                                    iCalXML::$recur,
                                    $content[Util::$LCvalue],
                                    $content[Util::$LCparams]
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
     * Add XML (rfc6321) children to a SimpleXMLelement
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.7 - 2018-12-02
     * @param SimpleXMLElement $parent  a SimpleXMLelement node
     * @param string           $name    new element node name
     * @param string           $type    content type, subelement(-s) name
     * @param string           $content new subelement content
     * @param array            $params  new element 'attributes'
     * @access private
     * @static
     */
    private static function addXMLchild(
        SimpleXMLElement & $parent,
                           $name,
                           $type,
                           $content,
                           $params = []
    ) {
        static $FMTYMD       = '%04d-%02d-%02d';
        static $FMTYMDHIS    = '%04d-%02d-%02dT%02d:%02d:%02d';
        static $PLUSMINUSARR = [ '+', '-' ];
        static $BOOLEAN      = 'boolean';
        static $UNTIL        = 'until';
        static $START        = 'start';
        static $END          = 'end';
        static $SP0          = '';
        /** create new child node */
        $name  = strtolower( $name );
        $child = $parent->addChild( $name );
        if( ! empty( $params )) {
            $parameters = $child->addChild( iCalXML::$PARAMETERS );
            foreach( $params as $param => $parVal ) {
                if( Util::$VALUE == $param ) {
                    if( 0 != strcasecmp( $type, $parVal )) {
                        $type = strtolower( $parVal );
                    }
                    continue;
                }
                $param = strtolower( $param );
                if( Util::isXprefixed( $param )) {
                    $p1 = $parameters->addChild( $param );
                    $p2 = $p1->addChild( iCalXML::$unknown, htmlspecialchars( $parVal ));
                }
                else {
                    $p1 = $parameters->addChild( $param );
                    switch( $param ) {
                        case iCalXML::$altrep:
                        case iCalXML::$dir:
                            $ptype = iCalXML::$uri;
                            break;
                        case iCalXML::$delegated_from:
                        case iCalXML::$delegated_to:
                        case iCalXML::$member:
                        case iCalXML::$sent_by:
                            $ptype = iCalXML::$cal_address;
                            break;
                        case iCalXML::$rsvp:
                            $ptype = $BOOLEAN;
                            break;
                        default:
                            $ptype = iCalXML::$text;
                            break;
                    }
                    if( is_array( $parVal )) {
                        foreach( $parVal as $pV ) {
                            $p2 = $p1->addChild( $ptype, htmlspecialchars( $pV ));
                        }
                    }
                    else {
                        $p2 = $p1->addChild( $ptype, htmlspecialchars( $parVal ));
                    }
                }
            }
        } // end if( ! empty( $params ))
        if(( empty( $content ) && ( Util::$ZERO != $content )) ||
            ( ! is_array( $content ) &&
                ( Util::$MINUS != $content[0] ) &&
                ( 0 > $content ))) {
            return;
        }
        /** store content */
        switch( $type ) {
            case iCalXML::$binary:
                $v = $child->addChild( $type, $content );
                break;
            case $BOOLEAN:
                break;
            case iCalXML::$cal_address:
                $v = $child->addChild( $type, $content );
                break;
            case iCalXML::$date:
                if( array_key_exists( Util::$LCYEAR, $content )) {
                    $content = [ $content ];
                }
                foreach( $content as $date ) {
                    $str = sprintf(
                        $FMTYMD,
                        (int) $date[Util::$LCYEAR],
                        (int) $date[Util::$LCMONTH],
                        (int) $date[Util::$LCDAY]
                    );
                    $v   = $child->addChild( $type, $str );
                }
                break;
            case iCalXML::$date_time:
                if( array_key_exists( Util::$LCYEAR, $content )) {
                    $content = [ $content ];
                }
                foreach( $content as $dt ) {
                    if( ! isset( $dt[Util::$LCHOUR] )) {
                        $dt[Util::$LCHOUR] = 0;
                    }
                    if( ! isset( $dt[Util::$LCMIN] )) {
                        $dt[Util::$LCMIN] = 0;
                    }
                    if( ! isset( $dt[Util::$LCSEC] )) {
                        $dt[Util::$LCSEC] = 0;
                    }
                    $str = sprintf(
                        $FMTYMDHIS,
                        (int) $dt[Util::$LCYEAR],
                        (int) $dt[Util::$LCMONTH],
                        (int) $dt[Util::$LCDAY],
                        (int) $dt[Util::$LCHOUR],
                        (int) $dt[Util::$LCMIN],
                        (int) $dt[Util::$LCSEC]
                    );
                    if( isset( $dt[Util::$LCtz] ) && ( Util::$Z == $dt[Util::$LCtz] )) {
                        $str .= Util::$Z;
                    }
                    $v = $child->addChild( $type, $str );
                }
                break;
            case strtolower( Util::$DURATION ) :
                $str    = UtilDuration::duration2str( $content );
                if( isset( $content[UtilDuration::$BEFORE ] ) &&
                    ( false != $content[UtilDuration::$BEFORE] )) {
                    $str = Util::$MINUS . $str;
                }
                $v      = $child->addChild( $type, $str );
                break;
            case strtolower( Util::$GEO ):
                if( ! empty( $content )) {
                    $v1 = $child->addChild(
                        UtilGeo::$LATITUDE,
                        UtilGeo::geo2str2( $content[UtilGeo::$LATITUDE], UtilGeo::$geoLatFmt )
                    );
                    $v1 = $child->addChild(
                        UtilGeo::$LONGITUDE,
                        UtilGeo::geo2str2( $content[UtilGeo::$LONGITUDE], UtilGeo::$geoLongFmt ));
                }
                break;
            case iCalXML::$integer:
                $v = $child->addChild( $type, (string) $content );
                break;
            case iCalXML::$period:
                if( ! is_array( $content )) {
                    break;
                }
                foreach( $content as $period ) {
                    $v1  = $child->addChild( $type );
                    $str = sprintf(
                        $FMTYMDHIS,
                        (int) $period[0][Util::$LCYEAR],
                        (int) $period[0][Util::$LCMONTH],
                        (int) $period[0][Util::$LCDAY],
                        (int) $period[0][Util::$LCHOUR],
                        (int) $period[0][Util::$LCMIN],
                        (int) $period[0][Util::$LCSEC]
                    );
                    if( isset( $period[0][Util::$LCtz] ) && ( Util::$Z == $period[0][Util::$LCtz] )) {
                        $str .= Util::$Z;
                    }
                    $v2 = $v1->addChild( $START, $str );
                    if( $period[1] instanceof DateInterval ) {
                        $v2 = $v1->addChild(
                            strtolower( Util::$DURATION ),
                            UtilDuration::dateInterval2String( $period[1] )
                        );
                    }
                    elseif( Util::isArrayDate( $period[1] )) {
                        $str = sprintf(
                            $FMTYMDHIS,
                            (int) $period[1][Util::$LCYEAR],
                            (int) $period[1][Util::$LCMONTH],
                            (int) $period[1][Util::$LCDAY],
                            (int) $period[1][Util::$LCHOUR],
                            (int) $period[1][Util::$LCMIN],
                            (int) $period[1][Util::$LCSEC]
                        );
                        if( isset( $period[1][Util::$LCtz] ) && ( Util::$Z == $period[1][Util::$LCtz] )) {
                            $str .= Util::$Z;
                        }
                        $v2 = $v1->addChild( $END, $str );
                    }
                    else {
                        $v2 = $v1->addChild(
                            strtolower( Util::$DURATION ),
                            UtilDuration::duration2str( $period[1] )
                        );
                    }
                }
                break;
            case iCalXML::$recur:
                $content = array_change_key_case( $content );
                foreach( $content as $ruleLabel => $ruleValue ) {
                    switch( $ruleLabel ) {
                        case $UNTIL:
                            if( isset( $ruleValue[Util::$LCHOUR] )) {
                                $str = sprintf(
                                    $FMTYMDHIS,
                                    (int) $ruleValue[Util::$LCYEAR],
                                    (int) $ruleValue[Util::$LCMONTH],
                                    (int) $ruleValue[Util::$LCDAY],
                                    (int) $ruleValue[Util::$LCHOUR],
                                    (int) $ruleValue[Util::$LCMIN],
                                    (int) $ruleValue[Util::$LCSEC]
                                    ) . Util::$Z;
                            }
                            else {
                                $str = sprintf(
                                    $FMTYMD,
                                    (int) $ruleValue[Util::$LCYEAR],
                                    (int) $ruleValue[Util::$LCMONTH],
                                    (int) $ruleValue[Util::$LCDAY]
                                );
                            }
                            $v = $child->addChild( $ruleLabel, $str );
                            break;
                        case iCalXML::$bysecond:
                        case iCalXML::$byminute:
                        case iCalXML::$byhour:
                        case iCalXML::$bymonthday:
                        case iCalXML::$byyearday:
                        case iCalXML::$byweekno:
                        case iCalXML::$bymonth:
                        case iCalXML::$bysetpos:
                            if( is_array( $ruleValue )) {
                                foreach( $ruleValue as $vix => $valuePart ) {
                                    $v = $child->addChild( $ruleLabel, $valuePart );
                                }
                            }
                            else {
                                $v = $child->addChild( $ruleLabel, $ruleValue );
                            }
                            break;
                        case iCalXML::$byday:
                            if( isset( $ruleValue[Util::$DAY] )) {
                                $str  = ( isset( $ruleValue[0] )) ? $ruleValue[0] : null;
                                $str .= $ruleValue[Util::$DAY];
                                $p    = $child->addChild( $ruleLabel, $str );
                            }
                            else {
                                foreach( $ruleValue as $valuePart ) {
                                    if( isset( $valuePart[Util::$DAY] )) {
                                        $str  = ( isset( $valuePart[0] )) ? $valuePart[0] : null;
                                        $str .= $valuePart[Util::$DAY];
                                        $p    = $child->addChild( $ruleLabel, $str );
                                    }
                                    else {
                                        $p = $child->addChild( $ruleLabel, $valuePart );
                                    }
                                }
                            }
                            break;
                        case iCalXML::$freq:
                        case iCalXML::$count:
                        case iCalXML::$interval:
                        case iCalXML::$wkst:
                        default:
                            $p = $child->addChild( $ruleLabel, $ruleValue );
                            break;
                    } // end switch( $ruleLabel )
                } // end foreach( $content as $ruleLabel => $ruleValue )
                break;
            case iCalXML::$rstatus:
                $v = $child->addChild(
                    iCalXML::$code,
                    number_format((float) $content[iCalXML::$statcode], 2, Util::$DOT, $SP0 )
                );
                $v = $child->addChild(
                    strtolower( Util::$DESCRIPTION ),
                    htmlspecialchars( $content[iCalXML::$text] )
                );
                if( isset( $content[iCalXML::$extdata] )) {
                    $v = $child->addChild( iCalXML::$data, htmlspecialchars( $content[iCalXML::$extdata] ));
                }
                break;
            case iCalXML::$text:
                if( ! is_array( $content )) {
                    $content = [ $content ];
                }
                foreach( $content as $part ) {
                    $v = $child->addChild( $type, htmlspecialchars( $part ));
                }
                break;
            case iCalXML::$time:
                break;
            case iCalXML::$uri:
                $v = $child->addChild( $type, $content );
                break;
            case iCalXML::$utc_offset:
                if( in_array( $content[0], $PLUSMINUSARR )) {
                    $str     = $content[0];
                    $content = substr( $content, 1 );
                }
                else {
                    $str = Util::$PLUS;
                }
                $str .= substr( $content, 0, 2 ) . Util::$COLON . substr( $content, 2, 2 );
                if( 4 < strlen( $content )) {
                    $str .= Util::$COLON . substr( $content, 4 );
                }
                $v = $child->addChild( $type, $str );
                break;
            case iCalXML::$unknown:
            default:
                if( is_array( $content )) {
                    $content = implode( $content );
                }
                $v = $child->addChild( iCalXML::$unknown, htmlspecialchars( $content ));
                break;
        }
    }

    /**
     * Parse (rfc6321) XML file into iCalcreator instance
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.1 - 2018-12-05
     * @param  string $xmlfile
     * @param  array  $iCalcfg iCalcreator config array (opt)
     * @return mixed Vcalendar|bool (false on error)
     * @static
     */
    public static function XMLfile2iCal( $xmlfile, $iCalcfg = [] ) {
        if( false === ( $xmlstr = file_get_contents( $xmlfile ))) {
            return false;
        }
        return iCalXML::xml2iCal( $xmlstr, $iCalcfg );
    }

    /**
     * Parse (rfc6321) XML string into iCalcreator instance, alias of XML2iCal
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.16.22 - 2013-06-18
     * @param  string $xmlstr
     * @param  array  $iCalcfg iCalcreator config array (opt)
     * @return mixed  iCalcreator instance or false on error
     * @static
     */
    public static function XMLstr2iCal( $xmlstr, $iCalcfg = [] ) {
        return iCalXML::XML2iCal( $xmlstr, $iCalcfg );
    }

    /**
     * Parse (rfc6321) XML string into iCalcreator instance
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.20.23 - 2017-02-25
     * @param  string $xmlstr
     * @param  array  $iCalcfg iCalcreator config array (opt)
     * @return mixed  iCalcreator instance or false on error
     * @static
     */
    public static function XML2iCal( $xmlstr, $iCalcfg = [] ) {
        static $CRLF = [ "\r\n", "\n\r", "\n", "\r" ];
        $xmlstr = str_replace( $CRLF, null, $xmlstr );
        $xml    = iCalXML::XMLgetTagContent1( $xmlstr, iCalXML::$Vcalendar, $endIx );
        $iCal   = new Vcalendar( $iCalcfg );
        iCalXML::XMLgetComps( $iCal, $xmlstr );
        return $iCal;
    }

    /**
     * Parse (rfc6321) XML string into iCalcreator components
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.1 - 2018-12-05
     * @param IcalInterface $iCal
     * @param string    $xml
     * @return mixed Vcalendar|bool
     * @access private
     * @static
     */
    private static function XMLgetComps(
        IcalInterface $iCal,
                      $xml
    ) {
        static $PROPSTAG = '<properties>';
        static $COMPSTAG = '<components>';
        $len = strlen( $xml );
        $sx  = 0;
        while((( $sx + 12 ) < $len ) &&
            ( $PROPSTAG != substr( $xml, $sx, 12 )) &&
            ( $COMPSTAG != substr( $xml, $sx, 12 ))) {
            $sx += 1;
        }
        if(( $sx + 11 ) >= $len ) {
            return false;
        }
        if( $PROPSTAG == substr( $xml, $sx, 12 )) {
            $xml2 = iCalXML::XMLgetTagContent1( $xml, iCalXML::$properties, $endIx );
            iCalXML::XMLgetProps( $iCal, $xml2 );
            $xml = substr( $xml, $endIx );
        }
        if( $COMPSTAG == substr( $xml, 0, 12 )) {
            $xml = iCalXML::XMLgetTagContent1( $xml, iCalXML::$components, $endIx );
        }
        while( ! empty( $xml )) {
            $xml2 = iCalXML::XMLgetTagContent2( $xml, $tagName, $endIx );
            $tagName2 = ucfirst( strtolower( $tagName ));
            if( in_array( $tagName2, Util::$ALLCOMPS ) &&
                ( false !== ( $iCalComp = $iCal->newComponent( $tagName2 )))) {
                iCalXML::XMLgetComps( $iCalComp, $xml2 );
            }
            $xml = substr( $xml, $endIx );
        } // end while( ! empty( $xml ))
        return $iCal;
    }

    /**
     * Parse (rfc6321) XML into iCalcreator properties
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26.1 - 2018-12-05
     * @param  IcalInterface $iCalComp iCalcreator calendar/component instance
     * @param  string $xml
     * @access private
     * @static
     */
    private static function XMLgetProps( IcalInterface $iCalComp, $xml ) {
        static $PARAMENDTAG = '<parameters/>';
        static $PARAMTAG    = '<parameters>';
        static $DATETAGST   = '<date';
        static $PERIODTAG   = '<period>';
        while( ! empty( $xml )) {
            $xml2     = iCalXML::XMLgetTagContent2( $xml, $propName, $endIx );
            $propName = strtoupper( $propName );
            if( empty( $xml2 ) && ( Util::$ZERO != $xml2 )) {
                $iCalComp->setProperty( $propName );
                $xml = substr( $xml, $endIx );
                continue;
            }
            $params = [];
            if( $PARAMENDTAG == substr( $xml2, 0, 13 )) {
                $xml2 = substr( $xml2, 13 );
            }
            elseif( $PARAMTAG == substr( $xml2, 0, 12 )) {
                $xml3 = iCalXML::XMLgetTagContent1( $xml2, iCalXML::$PARAMETERS, $endIx2 );
                while( ! empty( $xml3 )) {
                    $xml4     = iCalXML::XMLgetTagContent2( $xml3, $paramKey, $endIx3 );
                    $pType    = false; // skip parameter valueType
                    $paramKey = strtoupper( $paramKey );
                    if( in_array( $paramKey, Util::$ATTENDEEPARKEYS )) {
                        while( ! empty( $xml4 )) {
                            $paramValue = iCalXML::XMLgetTagContent1( $xml4, iCalXML::$cal_address, $endIx4 );
                            if( ! isset( $params[$paramKey] )) {
                                $params[$paramKey] = [ $paramValue ];
                            }
                            else {
                                $params[$paramKey][] = $paramValue;
                            }
                            $xml4 = substr( $xml4, $endIx4 );
                        }
                    } // end if( in_array( $paramKey, Util::$ATTENDEEPARKEYS ))
                    else {
                        $paramValue = html_entity_decode(
                            iCalXML::XMLgetTagContent2(
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
                }
                $xml2 = substr( $xml2, $endIx2 );
            } // end elseif
            $valueType = false;
            $value     = ( ! empty( $xml2 ) || ( Util::$ZERO == $xml2 ))
                ? iCalXML::XMLgetTagContent2( $xml2, $valueType, $endIx3 ) : null;
            switch( $propName ) {
                case Util::$CATEGORIES:
                // fall through
                case Util::$RESOURCES:
                    $tValue = [];
                    while( ! empty( $xml2 )) {
                        $tValue[] = html_entity_decode(
                            iCalXML::XMLgetTagContent2(
                                $xml2,
                                $valueType,
                                $endIx4
                            )
                        );
                        $xml2     = substr( $xml2, $endIx4 );
                    }
                    $value = $tValue;
                    break;
                case Util::$EXDATE:   // multiple single-date(-times) may exist
                // fall through
                case Util::$RDATE:
                    if( iCalXML::$period != $valueType ) {
                        if( iCalXML::$date == $valueType ) {
                            $params[Util::$VALUE] = Util::$DATE;
                        }
                        $t = [];
                        while( ! empty( $xml2 ) &&
                            ( $DATETAGST == substr( $xml2, 0, 5 ))) {
                            $t[]  = iCalXML::XMLgetTagContent2( $xml2, $pType, $endIx4);
                            $xml2 = substr( $xml2, $endIx4 );
                        }
                        $value = $t;
                        break;
                    }
                case Util::$FREEBUSY:
                    if( Util::$RDATE == $propName ) {
                        $params[Util::$VALUE] = Util::$PERIOD;
                    }
                    $value = [];
                    while( ! empty( $xml2 ) &&
                        ( $PERIODTAG == substr( $xml2, 0, 8 ))) { // period
                        $xml3 = iCalXML::XMLgetTagContent1( $xml2, iCalXML::$period, $endIx4);
                        $t    = [];
                        while( ! empty( $xml3 )) { // start - end/duration
                            $t[]  = iCalXML::XMLgetTagContent2( $xml3, $pType, $endIx5 );
                            $xml3 = substr( $xml3, $endIx5 );
                        }
                        $value[] = $t;
                        $xml2    = substr( $xml2, $endIx4 );
                    }
                    break;
                case Util::$TZOFFSETTO:
                // fall through
                case Util::$TZOFFSETFROM:
                    $value = str_replace( Util::$COLON, null, $value );
                    break;
                case Util::$GEO:
                    $tValue                      = [ UtilGeo::$LATITUDE => $value ];
                    $tValue[UtilGeo::$LONGITUDE] = iCalXML::XMLgetTagContent1(
                        substr( $xml2, $endIx3 ),
                        UtilGeo::$LONGITUDE,
                        $endIx3
                    );
                    $value = $tValue;
                    break;
                case Util::$EXRULE:
                // fall through
                case Util::$RRULE:
                    $tValue    = [ $valueType => $value ];
                    $xml2      = substr( $xml2, $endIx3 );
                    $valueType = false;
                    while( ! empty( $xml2 )) {
                        $t = iCalXML::XMLgetTagContent2( $xml2, $valueType, $endIx4 );
                        switch( strtoupper( $valueType )) {
                            case Util::$FREQ:
                            case Util::$COUNT:
                            case Util::$UNTIL:
                            case Util::$INTERVAL:
                            case Util::$WKST:
                                $tValue[$valueType] = $t;
                                break;
                            case Util::$BYDAY:
                                if( 2 == strlen( $t )) {
                                    $tValue[$valueType][] = [ Util::$DAY => $t ];
                                }
                                else {
                                    $day = substr( $t, -2 );
                                    $key = substr( $t, 0, ( strlen( $t ) - 2 ));
                                    $tValue[$valueType][] = [ $key, Util::$DAY => $day ];
                                }
                                break;
                            default:
                                $tValue[$valueType][] = $t;
                        }
                        $xml2 = substr( $xml2, $endIx4 );
                    }
                    $value = $tValue;
                    break;
                case Util::$REQUEST_STATUS:
                    $tValue = [];
                    while( ! empty( $xml2 )) {
                        $t    = html_entity_decode(
                            iCalXML::XMLgetTagContent2(
                                $xml2,
                                $valueType,
                                $endIx4 )
                        );
                        $tValue[$valueType] = $t;
                        $xml2 = substr( $xml2, $endIx4 );
                    }
                    if( ! empty( $tValue )) {
                        $value = $tValue;
                    }
                    else {
                        $value = [
                            iCalXML::$code                  => null,
                            strtolower( Util::$DESCRIPTION ) => null,
                        ];
                    }
                    break;
                default:
                    switch( $valueType ) {
                        case iCalXML::$binary :
                            $params[Util::$VALUE] = Util::$BINARY;
                            break;
                        case iCalXML::$date :
                            $params[Util::$VALUE] = Util::$DATE;
                            break;
                        case iCalXML::$date_time :
                            $params[Util::$VALUE] = Util::$DATE_TIME;
                            break;
                        case iCalXML::$text :
                            // fall through
                        case iCalXML::$unknown :
                            $value = html_entity_decode( $value );
                            break;
                        default :
                            if( Util::isXprefixed( $propName ) &&
                                ( iCalXML::$unknown != strtolower( $valueType ))) {
                                $params[Util::$VALUE] = strtoupper( $valueType );
                            }
                            break;
                    }
                    break;
            } // end switch( $propName )
            if( Util::$FREEBUSY == $propName ) {
                $fbtype = $params[iCalXML::$FBTYPE];
                unset( $params[iCalXML::$FBTYPE] );
                $iCalComp->setProperty( $propName, $fbtype, $value, $params );
            }
            elseif( Util::$GEO == $propName ) {
                $iCalComp->setProperty(
                    $propName,
                    $value[iCalXML::$latitude],
                    $value[iCalXML::$longitude], $params
                );
            }
            elseif( Util::$REQUEST_STATUS == $propName ) {
                if( ! isset( $value[iCalXML::$data] )) {
                    $value[iCalXML::$data] = false;
                }
                $iCalComp->setProperty(
                    $propName,
                    $value[iCalXML::$code],
                    $value[strtolower( Util::$DESCRIPTION )],
                    $value[iCalXML::$data], $params
                );
            }
            else {
                if( empty( $value ) && ( is_array( $value ) || ( Util::$ZERO > $value ))) {
                    $value = null;
                }
                $iCalComp->setProperty( $propName, $value, $params );
            }
            $xml = substr( $xml, $endIx );
        } // end while( ! empty( $xml ))
    }

    /**
     * Fetch a specific XML tag content
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.8 - 2017-04-17
     * @param string $xml
     * @param string $tagName
     * @param int    $endIx
     * @return mixed
     * @access private
     * @static
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
                ( sprintf( $FMT0, $tagName ) == strtolower( substr( $xml, $sx1, ( $strLen + 2 ))))) {
                break;
            }
            if((( $sx1 + $strLen + 3 ) < $xmlLen ) &&
                ( sprintf( $FMT1, $tagName ) == strtolower( substr( $xml, $sx1, ( $strLen + 4 ))))) {
                $endIx = $strLen + 5;
                return null; // empty tag
            }
            if((( $sx1 + $strLen + 2 ) < $xmlLen ) &&
                ( sprintf( $FMT2, $tagName ) == strtolower( substr( $xml, $sx1, ( $strLen + 3 ))))) {
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
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.8 - 2017-04-17
     * @param string $xml
     * @param string $tagName
     * @param int    $endIx
     * @return mixed
     * @access private
     * @static
     */
    private static function XMLgetTagContent2( $xml, & $tagName, & $endIx ) {
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
                    ( $CMTSTART == substr( $xml, $sx1, 4 ))) { // skip comment
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
                ( $EMPTYTAGEND == substr( $xml, $sx2, 2 ))) { // tag with no content
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
            ( false !== ( $pos2 = stripos( $xml, $DURENDTAG, $pos1 + 1 ))) &&
            ( false !== ( $pos3 = stripos( $xml, $DURENDTAG, $pos2 + 1 ))) &&
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

