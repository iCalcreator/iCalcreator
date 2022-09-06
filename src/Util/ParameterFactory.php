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

use Kigkonsult\Icalcreator\IcalInterface;

use function array_change_key_case;
use function in_array;
use function is_array;
use function is_bool;
use function is_string;
use function strtoupper;
use function trim;

/**
 * iCalcreator iCal parameters support class
 *
 * @since 2.41.63 2022-09-05
 */
class ParameterFactory
{
    /**
     * Return (conformed) iCal component property parameters
     *
     * Trim quoted values, default parameters may be set, if missing
     * Non-string values set to string
     *
     * @param null|array $params
     * @param null|string[] $defaults
     * @return string[]
     * @since 2.41.63 2022-09-05
     */
    public static function setParams( ? array $params = [], ? array $defaults = [] ) : array
    {
        static $TRUEFALSEARR = [ IcalInterface::TRUE,  IcalInterface::FALSE ];
        static $ONE = '1';
        if( empty( $params ) && empty( $defaults )) {
            return [];
        }
        $output = [];
        foreach( array_change_key_case( $params ?? [], CASE_UPPER ) as $paramKey => $paramValue ) {
            $paramValue = self::circumflexQuoteParse( $paramValue );
            switch( true ) {
                case is_array( $paramValue ) :
                    foreach( $paramValue as $pkey => $pValue ) {
                        $output[$paramKey][$pkey] =
                            trim( trim( $pValue ), StringFactory::$QQ );
                    }
                    continue 2;
                case ( IcalInterface::DERIVED === $paramKey ) :
                    if( is_bool( $paramValue )) {
                        $paramValue = $paramValue ? IcalInterface::TRUE : IcalInterface::FALSE;
                    }
                    elseif( in_array( strtoupper( $paramValue ), $TRUEFALSEARR, true )) {
                        $paramValue = strtoupper( $paramValue );
                    }
                    break;
                case ( IcalInterface::ORDER === $paramKey ) :
                    if( ! is_int( $paramValue )) {
                        $paramValue = (int) $paramValue;
                    }
                    if( 1 > $paramValue ) {
                        $paramValue = 1;
                    }
                    break;
                case ( IcalInterface::FEATURE === $paramKey ) :
                    // fall through
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
            $output[$paramKey] = ( IcalInterface::VALUE === $paramKey )
                ? strtoupper( $paramValue )
                : $paramValue;
        } // end foreach
        if( ! empty( $defaults )) {
            foreach( array_change_key_case( $defaults, CASE_UPPER ) as $paramKey => $paramValue ) {
                if( ! isset( $output[$paramKey] )) {
                    $output[$paramKey] = $paramValue;
                }
            }
        }
        return $output;
    }

    private static string $CIRCUMFLEX = '^';
    private static string $CFN        = '^n';
    private static string $CFCF       = '^^';
    private static string $CFSQ       = "^'";
    private static string $CFQQ       = '^"';
    private static string $NLCHARS    = '\n';

    /**
     * Return parsed parameter VALUE with opt. circumflex deformatted as of rfc6868
     *
     * the character sequence ^n (U+005E, U+006E) is decoded into an
     *    appropriate formatted line break according to the type of system
     *    being used
     * the character sequence ^^ (U+005E, U+005E) is decoded into the ^ character (U+005E)
     * the character sequence ^' (U+005E, U+0027) is decoded into the " character (U+0022)
     * if a ^ (U+005E) character is followed by any character other than the ones above,
     *    parsers MUST leave both the ^ and the following character in place
     *
     * Also ^" and ' are decoded into the " character (U+0022), NOT rfc6868
     *
     * @param mixed $value
     * @return mixed
     * @since 2022-01-31 2.41.15
     */
    public static function circumflexQuoteParse( mixed $value ) : mixed
    {
        static $SQUOTE = "'";
        if( ! is_string( $value )) {
            return $value;
        }
        if( str_contains( $value, self::$CFN )) {
            $value = str_replace( self::$CFN, self::$NLCHARS, $value );
        }
        if( str_contains( $value, self::$CFCF )) {
            $value = str_replace( self::$CFCF, self::$CIRCUMFLEX, $value );
        }
        if( str_contains( $value, self::$CFSQ )) {
            $value = str_replace( self::$CFSQ, StringFactory::$QQ, $value );
        }
        if( str_contains( $value, self::$CFQQ )) {
            $value = str_replace( self::$CFQQ, StringFactory::$QQ, $value );
        }
        if( str_contains( $value, $SQUOTE ) && ( 0 === ( substr_count( $value, $SQUOTE ) % 2 ))) {
            $value = str_replace( $SQUOTE, StringFactory::$QQ, $value );
        }
        return $value;
    }
}
