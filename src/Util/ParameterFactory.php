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
namespace Kigkonsult\Icalcreator\Util;

use Kigkonsult\Icalcreator\Vcalendar;

use function array_change_key_case;
use function array_filter;
use function array_merge;
use function ctype_digit;
use function in_array;
use function is_array;
use function is_bool;
use function is_string;
use function ksort;
use function sprintf;
use function strcasecmp;
use function strpos;
use function strtoupper;
use function trim;

/**
 * iCalcreator iCal parameters support class
 *
 * @since  2.30.2 - 2021-02-04
 */
class ParameterFactory
{
    /**
     * Return formatted output for calendar component property parameters
     *
     * @param null|array  $inputParams
     * @param null|array  $ctrKeys
     * @param bool|string $lang  bool false if config lang not found
     * @return string
     * @since  2.29.25 - 2020-09-02
     */
    public static function createParams(
        $inputParams = null,
        $ctrKeys = null,
        $lang = null
    ) : string
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
     * @since  2.30.2 - 2021-02-04
     */
    public static function ifExistRemove(
        array & $array,
        $expectedKey,
        $expectedValue = null
    ) {
        if( empty( $array )) {
            return;
        }
        foreach( $array as $key => $value ) {
            if(( 0 == strcasecmp( $expectedKey, $key )) &&
                ( empty( $expectedValue ) ||
                ( 0 == strcasecmp( $expectedValue, $value )))) {
                unset( $array[$key] );
                $array = array_filter( $array );
                break;
            } // end if
        } // end foreach
    }

    /**
     * Return true if property parameter VALUE is set to argument, otherwise false
     *
     * @param array  $parameterArr
     * @param string $arg
     * @return bool
     * @since  2.27.14 - 2019-03-01
     */
    public static function isParamsValueSet( array $parameterArr, string $arg ) : bool
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
     * Return param[TZID] or empty string
     *
     * @param array  $parameterArr
     * @return string
     * @since  2.27.14 - 2019-02-10
     */
    public static function getParamTzid( array $parameterArr ) : string
    {
        return ( $parameterArr[Util::$LCparams][Vcalendar::TZID] ?? Util::$SP0 );
    }

    /**
     * Return (conformed) iCal component property parameters
     *
     * Trim quoted values, default parameters may be set, if missing
     * Non-string values set to string
     *
     * @param array $params
     * @param array $defaults
     * @return array
     * @since  2.29.25 - 2020-08-25
     */
    public static function setParams( array $params, $defaults = null ) : array
    {
        static $ONE = '1';
        if( empty( $params ) && empty( $defaults )) {
            return [];
        }
        $output = [];
        foreach(
            array_change_key_case( $params, CASE_UPPER )
            as $paramKey => $paramValue ) {
            switch( true ) {
                case is_array( $paramValue ) :
                    foreach( $paramValue as $pkey => $pValue ) {
                        $output[$paramKey][$pkey] =
                            trim( trim( $pValue ), StringFactory::$QQ );
                    }
                    continue 2;
                case ( Vcalendar::FEATURE === $paramKey ) :
                    $paramValue = trim( $paramValue, StringFactory::$QQ ); // accept comma in value
                    break;
                case is_string( $paramValue ) :
                    $paramValue = trim( $paramValue, StringFactory::$QQ );
                    break;
                case is_bool( $paramValue ) :
                    $paramValue = $paramValue ? $ONE : Util::$ZERO;
                    break;
                default :
                    $paramValue = (string) $paramValue;
                    break;
            } // end switch
            if( Vcalendar::VALUE === $paramKey ) {
                $output[Vcalendar::VALUE] = strtoupper( $paramValue );
            }
            else {
                $output[$paramKey] = $paramValue;
            }
        } // end foreach
        if( is_array( $defaults ) && ! empty( $defaults )) {
            $output = array_merge(
                array_change_key_case( $defaults, CASE_UPPER ),
                $output
            );
        }
        return $output;
    }
}
