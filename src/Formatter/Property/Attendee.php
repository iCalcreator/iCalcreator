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
namespace Kigkonsult\Icalcreator\Formatter\Property;

use Kigkonsult\Icalcreator\Pc;

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
 * @since 2.41.59 - 2022-08-25
 */
final class Attendee extends PropertyBase
{
    /**
     * @param string    $propName
     * @param Pc[]      $values
     * @param bool|null $allowEmpty
     * @return string
     */
    public static function format( string $propName, array $values, ? bool $allowEmpty = true ) : string
    {
        static $AllKeys = [
            self::CUTYPE,
            self::MEMBER,
            self::ROLE,
            self::PARTSTAT,
            self::RSVP,
            self::DELEGATED_TO,
            self::DELEGATED_FROM,
            self::SENT_BY,
            self::EMAIL,
            self::DIR,
            self::CN,
            self::LANGUAGE
        ];
        static $ParamArrayKeys = [
            self::MEMBER,
            self::DELEGATED_TO,
            self::DELEGATED_FROM,
        ];
        static $KEYGRP1 = [ self::ROLE, self::PARTSTAT, self::RSVP ];
        static $KEYGRP2 = [ self::DELEGATED_TO, self::DELEGATED_FROM ];
        static $KEYGRP3 = [ self::SENT_BY, self::EMAIL ];
        static $KEYGRP4 = [ self::CN, self::LANGUAGE ];
        static $QQ      = '"';
        static $FMTKEYVALUE = ';%s=%s';
        static $FMTDIREQ    = ';%s=%s%s%s';
        if( empty( $values )) {
            return self::$SP0;
        }
        $output = self::$SP0;
        foreach( array_keys( $values ) as $aPix ) {
            $attendeePart = clone $values[$aPix]; // Pc
            if( empty( $attendeePart->value )) {
                if( $allowEmpty ) {
                    $output .= self::renderProperty( $propName );
                }
                continue;
            }
            $content     = $attendeePart->value;
            if( empty( $attendeePart->params )) {
                $output .= self::renderProperty( $propName, null, $content );
                continue;
            }
            $attributes = self::$SP0;
            foreach( $attendeePart->params as $pLabel2 => $pValue2 ) { // fix (opt) quotes
                if( is_array( $pValue2 ) || in_array( $pLabel2, $ParamArrayKeys, true )) {
                    continue;
                } // all but DELEGATED-FROM, DELEGATED-TO, MEMBER
                $attendeePart->params[$pLabel2] = self::circumflexQuoteInvoke( $pValue2 );
                if( self::hasColonOrSemicOrComma( $pValue2 )) {
                    $attendeePart->params[$pLabel2] = self::getQuotedItem( $pValue2 );
                }
            } // end foreach
            /* set attendee parameters in (almost) rfc2445 order */
            if( isset( $attendeePart->params[self::CUTYPE] )) {
                $attributes .= sprintf(
                    $FMTKEYVALUE,
                    self::CUTYPE,
                    $attendeePart->params[self::CUTYPE]
                );
            }
            if( isset( $attendeePart->params[self::MEMBER] )) {
                $attributes .= sprintf(
                    $FMTKEYVALUE,
                    self::MEMBER,
                    self::getQuotedListItems( $attendeePart->params[self::MEMBER] )
                );
            }
            foreach( $KEYGRP1 as $key ) { // ROLE, PARTSTAT, RSVP
                if( isset( $attendeePart->params[$key] )) {
                    $attributes .= sprintf( $FMTKEYVALUE, $key, $attendeePart->params[$key] );
                }
            } // end foreach
            foreach( $KEYGRP2 as $key ) { // DELEGATED_TO, DELEGATED_FROM
                if( isset( $attendeePart->params[$key] )) {
                    $attributes .= sprintf(
                        $FMTKEYVALUE,
                        $key,
                        self::getQuotedListItems( $attendeePart->params[$key] )
                    );
                }
            } // end foreach
            foreach( $KEYGRP3 as $key ) { // SENT_BY, EMAIL
                if( isset( $attendeePart->params[$key] )) {
                    $attributes .= sprintf( $FMTKEYVALUE, $key, $attendeePart->params[$key] );
                }
            } // end foreach
            if( isset( $attendeePart->params[self::DIR] )) {
                $delim = str_contains( $attendeePart->params[self::DIR], $QQ )
                    ? self::$SP0
                    : $QQ;
                $attributes .= sprintf(
                    $FMTDIREQ,
                    self::DIR,
                    $delim,
                    $attendeePart->params[self::DIR],
                    $delim
                );
            }
            foreach( $KEYGRP4 as $key ) { // CN, LANGUAGE
                if( isset( $attendeePart->params[$key] )) {
                    $attributes .= sprintf( $FMTKEYVALUE, $key, $attendeePart->params[$key] );
                }
            } // end foreach
            $xParams = [];
            foreach( $attendeePart->params as $pLabel2 => $pValue2 ) {
                if( ! in_array( $pLabel2, $AllKeys, true )) {
                    $xParams[$pLabel2] = $pValue2;
                }
            }
            if( ! empty( $xParams )) {
                ksort( $xParams, SORT_STRING );
                foreach( $xParams as $pLabel2 => $pValue2 ) {
                    $attributes .= sprintf( $FMTKEYVALUE, $pLabel2, $pValue2 );
                }
            }
            $output .= self::renderProperty( $propName, $attributes, $content );
        } // end foreach( $pc->value as $ax => $attendeePart )
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
     * Return string of comma-separated quoted array members
     *
     * @param string[] $list
     * @return string
     * @since  2.27.11 - 2019-01-03
     */
    private static function getQuotedListItems( array $list ) : string
    {
        foreach( $list as & $v ) {
            $v = self::getQuotedItem( $v );
        }
        return implode( self::$COMMA, $list );
    }
}
