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
namespace Kigkonsult\Icalcreator\Formatter\Property;

use Kigkonsult\Icalcreator\Pc;

use Kigkonsult\Icalcreator\Util\StringFactory;
use function array_keys;
use function implode;
use function in_array;
use function is_array;
use function sprintf;
use function str_contains;

/**
 * Format ATTENDEE
 *
 * 1
 * @since 2.41.88 - 2024-01-18
 */
final class Attendee extends PropertyBase
{
    /**
     * @var string
     */
    private static string $FMT     = ';%s=%s';

    /**
     * @param string    $propName
     * @param Pc[]      $values
     * @param bool|null $allowEmpty
     * @return string
     * @since 2.41.88 - 2024-01-18
     */
    public static function format( string $propName, array $values, ? bool $allowEmpty = true ) : string
    {
        static $AllKeys = [
            self::CUTYPE, self::MEMBER, self::ROLE, self::PARTSTAT, self::RSVP, self::DELEGATED_TO,
            self::DELEGATED_FROM, self::SENT_BY, self::EMAIL, self::DIR, self::CN, self::LANGUAGE
        ];
        static $KEYGRP1 = [ self::ROLE, self::PARTSTAT, self::RSVP ];
        static $KEYGRP2 = [ self::DELEGATED_TO, self::DELEGATED_FROM ];
        static $KEYGRP3 = [ self::SENT_BY, self::EMAIL ];
        static $KEYGRP4 = [ self::CN, self::LANGUAGE ];
        if( empty( $values )) {
            return StringFactory::$SP0;
        }
        $output = StringFactory::$SP0;
        foreach( array_keys( $values ) as $aPix ) {
            $attendeePart = clone $values[$aPix]; // Pc
            $pcValue      = $attendeePart->getValue();
            if( ! $attendeePart->isset() || empty( $pcValue )) {
                if( $allowEmpty ) {
                    $output .= self::renderProperty( $propName );
                }
                continue;
            }
            $pcParams    = (array) $attendeePart->getParams();
            if( empty( $pcParams)) {
                $output .= self::renderProperty( $propName, null, $pcValue );
                continue;
            }
            $aParams     = self::fixOptQuotesForParamValue( $pcParams );
            $attributes  = StringFactory::$SP0;
            /* set attendee parameters in (almost) rfc2445 order */
            if( isset( $aParams[self::CUTYPE] )) {
                $attributes .= sprintf( self::$FMT, self::CUTYPE, (string) $aParams[self::CUTYPE] );
            }
            if( isset( $aParams[self::MEMBER] )) {
                $attributes .= self::renderQuotedListItems( self::MEMBER, $aParams[self::MEMBER] );
            }
            foreach( $KEYGRP1 as $key ) { // ROLE, PARTSTAT, RSVP
                if( isset( $aParams[$key] )) {
                    $attributes .= sprintf( self::$FMT, $key, (string) $aParams[$key] );
                }
            } // end foreach
            foreach( $KEYGRP2 as $key ) { // DELEGATED_TO, DELEGATED_FROM
                if( isset( $aParams[$key] )) {
                    $attributes .= self::renderQuotedListItems( $key, $aParams[$key] );
                }
            } // end foreach
            foreach( $KEYGRP3 as $key ) { // SENT_BY, EMAIL
                if( isset( $aParams[$key] )) {
                    $attributes .= sprintf( self::$FMT, $key, (string) $aParams[$key] );
                }
            } // end foreach
            if( isset( $aParams[self::DIR] )) {
                $attributes .= self::renderDirParam((string) $aParams[self::DIR] );
            }
            foreach( $KEYGRP4 as $key ) { // CN, LANGUAGE
                if( isset( $aParams[$key] )) {
                    $attributes .= sprintf( self::$FMT, $key, (string) $aParams[$key] );
                }
            } // end foreach
            $xParams = [];
            foreach( $aParams as $pLabel2 => $pValue2 ) {
                if( ! in_array( $pLabel2, $AllKeys, true )) {
                    $xParams[$pLabel2] = $pValue2;
                }
            }
            if( ! empty( $xParams )) {
                ksort( $xParams, SORT_STRING );
                foreach( $xParams as $pLabel2 => $pValue2 ) {
                    $attributes .= sprintf( self::$FMT, $pLabel2, (string) $pValue2 );
                }
            }
            $output .= self::renderProperty( $propName, $attributes, $pcValue );
        } // end foreach( $pc->value as $ax => $attendeePart )
        return $output;
    }

    /**
     * Fix opt quoted param values, all but DELEGATED-FROM, DELEGATED-TO, MEMBER
     *
     * @param string[]|string[][] $aParams
     * @return string[]|string[][]
     * @since  2.41.68 - 2019-10-24
     */
    private static function fixOptQuotesForParamValue( array $aParams ) : array
    {
        static $ParamArrayKeys = [
            self::MEMBER,
            self::DELEGATED_TO,
            self::DELEGATED_FROM,
        ];
        $output = [];
        foreach( $aParams as $pLabel2 => $pValue2 ) { // fix (opt) quotes
            if( is_array( $pValue2 ) || in_array( $pLabel2, $ParamArrayKeys, true )) {
                $output[$pLabel2] = $pValue2;
                continue;
            }
            $pValue3 = self::circumflexQuoteInvoke( $pValue2 );
            $output[$pLabel2] = self::hasColonOrSemicOrComma( $pValue3 )
                ? self::getQuotedItem( $pValue3 )
                : $pValue3;
        } // end foreach
        return $output;
    }

    /**
     * Return quoted item
     *
     * @param string $item
     * @return string
     * @since  2.27.11 - 2019-01-03
     */
    private static function getQuotedItem( string $item ) : string
    {
        static $FMTQVALUE = '"%s"';
        return sprintf( $FMTQVALUE, $item );
    }

    /**
     * Return string. attribute with opt comma-separated quoted array members
     *
     * @param string $pLabel
     * @param string|string[] $list
     * @return string
     * @since  2.41.88 - 2024-01-17
     */
    private static function renderQuotedListItems( string $pLabel, string|array $list ) : string
    {
        if( ! is_array( $list )) {
            $list = [ $list ];
        }
        foreach( $list as $x => $v ) {
            $list[$x] = self::getQuotedItem( $v );
        }
        return sprintf(
            self::$FMT,
            $pLabel,
            implode( StringFactory::$COMMA, $list )
        );
    }

    /**
     * Return rendered DIR param
     * @param string $dir
     * @return string
     */
    private static function renderDirParam( string $dir ) : string
    {
        static $QQ       = '"';
        static $FMTDIREQ = ';%s=%s%s%s';
        $delim = str_contains( $dir, $QQ ) ? StringFactory::$SP0 : $QQ;
        return sprintf( $FMTDIREQ, self::DIR, $delim, $dir, $delim );
    }
}
