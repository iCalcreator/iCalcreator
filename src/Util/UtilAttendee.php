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

namespace Kigkonsult\Icalcreator\Util;

use Kigkonsult\Icalcreator\Vcalendar;

use function array_change_key_case;
use function array_keys;
use function ctype_digit;
use function filter_var;
use function in_array;
use function is_array;
use function sprintf;
use function strcasecmp;
use function strpos;
use function substr;
use function trim;

/**
 * iCalcreator attendee support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26 - 2018-11-10
 */
class UtilAttendee
{
    /**
     * Return string after a cal-address check, prefix mail address with MAILTO
     *
     * Acceps other prefix ftp://, http://, file://, gopher://, news:, nntp://, telnet://, wais://, prospero:// etc
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param string $value
     * @param bool   $trimQuotes
     * @return string
     * @static
     * @TODO   fix in Util::splitContent() ??
     */
    public static function calAddressCheck( $value, $trimQuotes = true ) {
        static $MAILTOCOLON = 'MAILTO:';
        $value = trim( $value );
        if( $trimQuotes ) {
            $value = trim( $value, Util::$QQ );
        }
        switch( true ) {
            case( empty( $value )) :
                break;
            case( 0 == strcasecmp( $MAILTOCOLON, substr( $value, 0, 7 ))) :
                $value = $MAILTOCOLON . substr( $value, 7 ); // accept mailto:
                break;
            case( false !== ( $pos = strpos( substr( $value, 0, 9 ), Util::$COLON ))) :
                break;                                       // accept (as is) from list above
            case( filter_var( $value, FILTER_VALIDATE_EMAIL )) :
                $value = $MAILTOCOLON . $value;              // accept mail address
                break;
            default :                                        // accept as is...
                break;
        }
        return $value;
    }

    /**
     * Return formatted output for calendar component property attendee
     *
     * @param array $attendeeData
     * @param bool  $allowEmpty
     * @return string
     * @static
     */
    public static function formatAttendee( array $attendeeData, $allowEmpty ) {
        static $FMTQVALUE = '"%s"';
        static $FMTKEYVALUE = ';%s=%s';
        static $FMTKEYEQ = ';%s=';
        static $FMTDIREQ = ';%s=%s%s%s';
        $output = null;
        foreach( $attendeeData as $ax => $attendeePart ) {
            if( empty( $attendeePart[Util::$LCvalue] )) {
                if( $allowEmpty ) {
                    $output .= Util::createElement( Util::$ATTENDEE );
                }
                continue;
            }
            $attributes = $content = null;
            foreach( $attendeePart as $pLabel => $pValue ) {
                if( Util::$LCvalue == $pLabel ) {
                    $content .= $pValue;
                    continue;
                }
                if(( Util::$LCparams != $pLabel ) ||
                    ( ! is_array( $pValue ))) {
                    continue;
                }
                foreach( $pValue as $pLabel2 => $pValue2 ) { // fix (opt) quotes
                    if( is_array( $pValue2 ) ||
                        in_array( $pLabel2, Util::$ATTENDEEPARKEYS )) {
                        continue;
                    } // DELEGATED-FROM, DELEGATED-TO, MEMBER
                    if(( false !== strpos( $pValue2, Util::$COLON )) ||
                       ( false !== strpos( $pValue2, Util::$SEMIC )) ||
                       ( false !== strpos( $pValue2, Util::$COMMA ))) {
                        $pValue[$pLabel2] = sprintf( $FMTQVALUE, $pValue2 );
                    }
                }
                /* set attenddee parameters in rfc2445 order */
                if( isset( $pValue[Util::$CUTYPE] )) {
                    $attributes .= sprintf( $FMTKEYVALUE, Util::$CUTYPE, $pValue[Util::$CUTYPE] );
                }
                if( isset( $pValue[Util::$MEMBER] )) {
                    $attributes .= sprintf( $FMTKEYEQ, Util::$MEMBER ) .
                                   self::getQuotedListItems( $pValue[Util::$MEMBER] );
                }
                if( isset( $pValue[Util::$ROLE] )) {
                    $attributes .= sprintf( $FMTKEYVALUE, Util::$ROLE, $pValue[Util::$ROLE] );
                }
                if( isset( $pValue[Util::$PARTSTAT] )) {
                    $attributes .= sprintf( $FMTKEYVALUE, Util::$PARTSTAT, $pValue[Util::$PARTSTAT] );
                }
                if( isset( $pValue[Util::$RSVP] )) {
                    $attributes .= sprintf( $FMTKEYVALUE, Util::$RSVP, $pValue[Util::$RSVP] );
                }
                if( isset( $pValue[Util::$DELEGATED_TO] )) {
                    $attributes .= sprintf( $FMTKEYEQ, Util::$DELEGATED_TO ) .
                                   self::getQuotedListItems( $pValue[Util::$DELEGATED_TO] );
                }
                if( isset( $pValue[Util::$DELEGATED_FROM] )) {
                    $attributes .= sprintf( $FMTKEYEQ, Util::$DELEGATED_FROM ) .
                                   self::getQuotedListItems( $pValue[Util::$DELEGATED_FROM] );
                }
                if( isset( $pValue[Util::$SENT_BY] )) {
                    $attributes .= sprintf( $FMTKEYVALUE, Util::$SENT_BY, $pValue[Util::$SENT_BY] );
                }
                if( isset( $pValue[Util::$CN] )) {
                    $attributes .= sprintf( $FMTKEYVALUE, Util::$CN, $pValue[Util::$CN] );
                }
                if( isset( $pValue[Util::$DIR] )) {
                    $delim       = ( false === \strpos( $pValue[Util::$DIR], Util::$QQ )) ? Util::$QQ : null;
                    $attributes .= sprintf( $FMTDIREQ, Util::$DIR, $delim, $pValue[Util::$DIR], $delim );
                }
                if( isset( $pValue[Util::$LANGUAGE] )) {
                    $attributes .= sprintf( $FMTKEYVALUE, Util::$LANGUAGE, $pValue[Util::$LANGUAGE] );
                }
                $xparams = [];
                foreach( $pValue as $pLabel2 => $pValue2 ) {
                    if( ctype_digit((string) $pLabel2 )) {
                        $xparams[] = $pValue2;
                    }
                    elseif( ! in_array( $pLabel2, Util::$ATTENDEEPARALLKEYS )) {
                        $xparams[$pLabel2] = $pValue2;
                    }
                }
                if( empty( $xparams )) {
                    continue;
                }
                ksort( $xparams, SORT_STRING );
                foreach( $xparams as $pLabel2 => $pValue2 ) {
                    if( ctype_digit((string) $pLabel2 )) {
                        $attributes .= Util::$SEMIC . $pValue2;
                    } // ??
                    else {
                        $attributes .= sprintf( $FMTKEYVALUE, $pLabel2, $pValue2 );
                    }
                }
            } // end foreach( $attendeePart )) as $pLabel => $pValue )
            $output .= Util::createElement( Util::$ATTENDEE, $attributes, $content );
        } // end foreach( $attendeeData as $ax => $attendeePart )
        return $output;
    }

    /**
     * Return string of comma-separated quoted array members
     *
     * @param array $list
     * @return string
     * @access private
     * @static
     */
    private static function getQuotedListItems( array $list ) {
        static $FMTQVALUE = '"%s"';
        static $FMTCOMMAQVALUE = ',"%s"';
        $strList = null;
        foreach( $list as $x => $v ) {
            $strList .= ( 0 < $x ) ? sprintf( $FMTCOMMAQVALUE, $v ) : sprintf( $FMTQVALUE, $v );
        }
        return $strList;
    }

    /**
     * Return formatted output for calendar component property attendee
     *
     * @param array  $params
     * @param string $compType
     * @param string $lang
     * @return array
     * @static
     */
    public static function prepAttendeeParams( $params, $compType, $lang ) {
        static $NONXPROPCOMPS = null;
        if( is_null( $NONXPROPCOMPS )) {
            $NONXPROPCOMPS = [ Vcalendar::VFREEBUSY, Vcalendar::VALARM ];
        }
        $params2 = [];
        if( is_array( $params )) {
            $optArr = [];
            $params = array_change_key_case( $params, CASE_UPPER );
            foreach( $params as $pLabel => $optParamValue ) {
                if( ! Util::isXprefixed( $pLabel ) &&
                    Util::isCompInList( $compType, $NONXPROPCOMPS )) {
                    continue;
                }
                switch( $pLabel ) {
                    case Util::$MEMBER:
                    case Util::$DELEGATED_TO:
                    case Util::$DELEGATED_FROM:
                        if( ! is_array( $optParamValue )) {
                            $optParamValue = [ $optParamValue ];
                        }
                        foreach(( array_keys( $optParamValue )) as $px ) {
                            $optArr[$pLabel][] = self::calAddressCheck( $optParamValue[$px] );
                        }
                        break;
                    default:
                        if( Util::$SENT_BY == $pLabel ) {
                            $optParamValue = self::calAddressCheck( $optParamValue );
                        }
                        else {
                            $optParamValue = trim( $optParamValue, Util::$QQ );
                        }
                        $params2[$pLabel] = $optParamValue;
                        break;
                } // end switch( $pLabel.. .
            } // end foreach( $params as $pLabel => $optParamValue )
            foreach( $optArr as $pLabel => $pValue ) {
                $params2[$pLabel] = $pValue;
            }
        } // end if( is_array($params ))
        // remove defaults
        Util::existRem( $params2, Util::$CUTYPE, Util::$INDIVIDUAL );
        Util::existRem( $params2, Util::$PARTSTAT, Util::$NEEDS_ACTION );
        Util::existRem( $params2, Util::$ROLE, Util::$REQ_PARTICIPANT );
        Util::existRem( $params2, Util::$RSVP, Util::$false );
        // check language setting
        if( isset( $params2[Util::$CN] ) &&
            ! isset( $params2[Util::$LANGUAGE] ) &&
            ! empty( $lang )) {
            $params2[Util::$LANGUAGE] = $lang;
        }
        return $params2;
    }
}
