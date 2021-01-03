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

use Kigkonsult\Icalcreator\Vcalendar;

use function array_change_key_case;
use function array_filter;
use function array_merge;
use function ctype_digit;
use function in_array;
use function is_array;
use function ksort;
use function sprintf;
use function strcasecmp;
use function strpos;
use function strtoupper;
use function trim;

/**
 * iCalcreator iCal parameters support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.29.30 - 2020-12-09
 */
class ParameterFactory
{
    /**
     * Return formatted output for calendar component property parameters
     *
     * @param array  $inputParams
     * @param array  $ctrKeys
     * @param string $lang
     * @return string
     * @static
     * @since  2.29.25 - 2020-09-02
     */
    public static function createParams( $inputParams = null, $ctrKeys = null, $lang = null )
    {
        static $FMTFMTTYPE = ';FMTTYPE=%s%s';
        static $FMTKEQV    = '%s=%s';
        static $FMTQ       = '"%s"';
        static $FMTQTD     = ';%s=%s%s%s';
        static $FMTCMN     = ';%s=%s';
        static $DFKEYS     = [ Vcalendar::DISPLAY, Vcalendar::FEATURE ];
        static $KEYGRP1    = [
            Vcalendar::VALUE,
            Vcalendar::TZID,
            Vcalendar::RANGE,
            Vcalendar::RELTYPE
        ];
        static $KEYGRP2    = [ Vcalendar::DIR, Vcalendar::ALTREP ];
        static $KEYGRP3    = [
            Vcalendar::SENT_BY,
            Vcalendar::DISPLAY,
            Vcalendar::FEATURE,
            Vcalendar::LABEL
        ];

        if( isset( $inputParams[Util::$ISLOCALTIME ] )) {
            unset( $inputParams[Util::$ISLOCALTIME ] );
        }
        if( empty( $inputParams ) && empty( $ctrKeys ) && empty( $lang )) {
            return Util::$SP0;
        }
        if( ! is_array( $inputParams )) {
            $inputParams = [];
        }
        if( empty( $ctrKeys )) {
            $ctrKeys = [];
        }
        elseif( ! is_array( $ctrKeys )) {
            $ctrKeys = [ $ctrKeys ];
        }
        $attrLANG       = $attr1 = $attr2 = null;
        $hasCNattrKey   = in_array( Vcalendar::CN, $ctrKeys );
        $hasLANGattrKey = in_array( Vcalendar::LANGUAGE, $ctrKeys );
        $CNattrExist    = false;
        $params = $xparams = [];
        foreach(
            array_change_key_case( $inputParams, CASE_UPPER )
            as $paramKey => $paramValue
        ) {
            if(( false !== strpos( $paramValue, Util::$COLON )) ||
               ( false !== strpos( $paramValue, Util::$SEMIC )) ||
               ( false !== strpos( $paramValue, Util::$COMMA ))) {
                if( ! in_array( $paramKey, $DFKEYS )) { // DISPLAY, FEATURE
                    $paramValue = sprintf( $FMTQ, $paramValue );
                }
            }
            switch( true ) {
                case ctype_digit((string) $paramKey ) : // ??
                    $xparams[] = $paramValue;
                    break;
                case StringFactory::isXprefixed( $paramKey ) :
                    $xparams[$paramKey] = $paramValue;
                    break;
                default :
                    $params[$paramKey] = $paramValue;
                    break;
            } // end switch
        } // end foreach
        ksort( $xparams, SORT_STRING );
        foreach( $xparams as $paramKey => $paramValue ) {
            $attr2 .= Util::$SEMIC;
            $attr2 .= ( ctype_digit((string) $paramKey ))
                ? $paramValue
                : sprintf( $FMTKEQV, $paramKey, $paramValue );
        }
        if( isset( $params[Vcalendar::FMTTYPE] ) &&
            ! in_array( Vcalendar::FMTTYPE, $ctrKeys )) {
            $attr1 .= sprintf( $FMTFMTTYPE, $params[Vcalendar::FMTTYPE], $attr2 );
            $attr2 = null;
            unset( $params[Vcalendar::FMTTYPE] );
        }
        if( isset( $params[Vcalendar::ENCODING] ) &&
            ! in_array( Vcalendar::ENCODING, $ctrKeys )) {
            if( ! empty( $attr2 )) {
                $attr1 .= $attr2;
                $attr2 = null;
            }
            $attr1 .= sprintf(
                $FMTCMN,
                Vcalendar::ENCODING,
                $params[Vcalendar::ENCODING]
            );
            unset( $params[Vcalendar::ENCODING] );
        }
        foreach( $KEYGRP1 as $key ) { // VALUE, TZID, RANGE, RELTYPE
            if( isset( $params[$key] ) && ! in_array( $key, $ctrKeys )) {
                $attr1 .= sprintf( $FMTCMN, $key, $params[$key] );
                unset( $params[$key] );
            }
        } // end foreach
        if( isset( $params[Vcalendar::CN] ) && $hasCNattrKey ) {
            $attr1      .= sprintf( $FMTCMN, Vcalendar::CN, $params[Vcalendar::CN] );
            $CNattrExist = true;
            unset( $params[Vcalendar::CN] );
        }
        foreach( $KEYGRP2 as $key ) { // DIR, ALTREP
            if( isset( $params[$key] ) && in_array( $key, $ctrKeys )) {
                $delim  = ( false !== strpos( $params[$key], StringFactory::$QQ ))
                    ? null
                    : StringFactory::$QQ;
                $attr1 .= sprintf( $FMTQTD, $key, $delim, $params[$key], $delim );
                unset( $params[$key] );
            }
        } // end foreach
        foreach( $KEYGRP3 as $key ) { // SENT_BY, DISPLAY, FEATURE, LABEL
            if( isset( $params[$key] ) && in_array( $key, $ctrKeys )) {
                $attr1 .= sprintf( $FMTCMN, $key, $params[$key] );
                unset( $params[$key] );
            }
        } // end foreach
        if( isset( $params[Vcalendar::LANGUAGE] ) && $hasLANGattrKey ) {
            $attrLANG .= sprintf(
                $FMTCMN,
                Vcalendar::LANGUAGE,
                $params[Vcalendar::LANGUAGE]
            );
            unset( $params[Vcalendar::LANGUAGE] );
        }
        elseif(( $CNattrExist || $hasLANGattrKey ) && ! empty( $lang )) {
            $attrLANG .= sprintf( $FMTCMN, Vcalendar::LANGUAGE, $lang );
        }
        if( ! empty( $params )) { // accept other or iana-token (Other IANA-registered) parameter types
            foreach( $params as $paramKey => $paramValue ) {
                $attr1 .= sprintf( $FMTCMN, $paramKey, $paramValue );
            }
        }
        return $attr1 . $attrLANG . $attr2;
    }

    /**
     * Remove key/value from array (if found)
     *
     * @param array   $array          iCal property parameters
     * @param string  $expectedKey    expected key
     * @param string  $expectedValue  expected value
     * @static
     * @since  2.29.1 - 2019-06-24
     */
    public static function ifExistRemove(
        array & $array,
        $expectedKey,
        $expectedValue  = null
    ) {
        if( empty( $array )) {
            return;
        }
        foreach( $array as $key => $value ) {
            if( 0 == strcasecmp( $expectedKey, $key )) {
                if( empty( $expectedValue ) ||
                    ( 0 == strcasecmp( $expectedValue, $value ))) {
                    unset( $array[$key] );
                    $array = array_filter( $array );
                    break;
                }
            }
        } // end foreach
    }

    /**
     * Return true if property parameter VALUE is set to argument, otherwise false
     *
     * @param array  $parameterArr
     * @param string $arg
     * @return bool
     * @static
     * @since  2.27.14 - 2019-03-01
     */
    public static function isParamsValueSet( $parameterArr, $arg )
    {
        if( empty( $parameterArr ) || ! isset( $parameterArr[Util::$LCparams] )) {
            return  false;
        }
        return Util::issetKeyAndEquals(
            $parameterArr[Util::$LCparams],
            Vcalendar::VALUE,
            strtoupper( $arg )
        );
    }

    /**
     * Return param[TZID] or null
     *
     * @param array  $parameterArr
     * @return string|null
     * @static
     * @since  2.27.14 - 2019-02-10
     */
    public static function getParamTzid( $parameterArr )
    {
        return ( isset( $parameterArr[Util::$LCparams][Vcalendar::TZID] ))
            ? $parameterArr[Util::$LCparams][Vcalendar::TZID]
            : null;
    }

    /**
     * Return (conformed) iCal component property parameters
     *
     * Trim quoted values, default parameters may be set, if missing
     *
     * @param array $params
     * @param array $defaults
     * @return array
     * @static
     * @since  2.29.25 - 2020-08-25
     */
    public static function setParams( $params, $defaults = null )
    {
        $output = [];
        if( empty( $params ) && empty( $defaults )) {
            return $output;
        }
        if( ! is_array( $params )) {
            $params = [];
        }
        foreach(
            array_change_key_case( $params, CASE_UPPER )
            as $paramKey => $paramValue ) {
            if( Vcalendar::FEATURE === $paramKey ) {
                $output[Vcalendar::FEATURE] =
                    trim( $paramValue, StringFactory::$QQ ); // accept comma in value
                continue;
            }
            if( is_array( $paramValue )) {
                foreach( $paramValue as $pkey => $pValue ) {
                    $paramValue[$pkey] = trim( $pValue, StringFactory::$QQ );
                }
            }
            else {
                $paramValue = trim( $paramValue, StringFactory::$QQ );
            }
            if( Vcalendar::VALUE === $paramKey ) {
                $output[Vcalendar::VALUE] = strtoupper( $paramValue );
            }
            else {
                $output[$paramKey] = $paramValue;
            }
        } // end foreach
        if( is_array( $defaults )) {
            $output = array_merge(
                array_change_key_case(
                    $defaults, CASE_UPPER ),
                $output
            );
        }
        return $output;
    }
}
