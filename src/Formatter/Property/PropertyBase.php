<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2025 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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

use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Util\StringFactory as SF;

use function array_change_key_case;
use function array_keys;
use function in_array;
use function is_array;
use function is_int;
use function is_string;
use function ord;
use function sprintf;
use function str_contains;
use function str_replace;
use function strlen;

/**
 * @since  2.41.92 - 2025-01-15
 */
abstract class PropertyBase implements IcalInterface
{
    /**
     * @var string[]
     */
    protected static array $ALTRPLANGARR  = [ self::ALTREP, self::LANGUAGE ];

    /**
     * Return formatted output for calendar component property
     *
     * @param string      $label      property name
     * @param null|string|string[] $attributes property attributes
     * @param null|string $content    property content
     * @return string
     * @since 2.41.66 2022-09-07
     */
    public static function renderProperty(
        string $label,
        null|string|array $attributes = null,
        ? string $content = null
    ) : string
    {
        $output = strtoupper( $label );
        switch( true ) {
            case empty( $attributes ) :
                break;
            case is_array( $attributes ) :
                $output .= self::formatParams( $attributes );
                break;
            default :
                $output .= trim( $attributes );
                break;
        }
        $output .= SF::$COLON . trim((string) $content );
        return self::size75( $output );
    }

    /**
     * Create iCal string of empty property
     *
     * @param string $propName
     * @param null|bool $allowEmpty
     * @return string
     * @since 2.41.63 2022-09-05
     */
    protected static function renderSinglePropEmpty( string $propName, ? bool $allowEmpty = true ) : string
    {
        return $allowEmpty ? self::renderProperty( $propName ) : SF::$SP0;
    }

    /**
     * Return formatted output for calendar component property parameters
     *
     * @param string[]|string[][] $inputParams
     * @param null|string[]    $ctrKeys
     * @param null|bool|string $lang  bool false if config lang not found
     * @return string
     * @since 2.41.68 2022-10-23
     */
    public static function formatParams(
        array $inputParams,
        ? array $ctrKeys = [],
        null|bool|string $lang = null
    ) : string
    {
        static $VALENC  = [ self::VALUE, self::ENCODING ];
        static $KEYGRP1 = [ self::TZID, self::RANGE, self::RELTYPE ];
        static $KEYGRP3 = [ self::SENT_BY, self::FEATURE, self::LABEL ];
        unset( $inputParams[self::ISLOCALTIME ] );
        if( empty( $inputParams ) && empty( $ctrKeys ) && empty( $lang )) {
            return SF::$SP0;
        }
        if( empty( $ctrKeys )) {
            $ctrKeys = [];
        }
        $attrLANG = $output   = SF::$SP0;
        $hasLANGctrKey        = in_array( self::LANGUAGE, $ctrKeys, true );
        $CNattrExist          = false;
        [ $params, $xparams ] = self::quoteParams( $inputParams );
        if( isset( $params[self::VALUE] ) && ! in_array( self::VALUE, $ctrKeys, true )) {
            $output .= self::renderKeyGroup( $VALENC, $VALENC, $params ); // VALUE+ENCODING
        }
        $output .= self::renderKeyGroup( $KEYGRP1, $ctrKeys, $params ); // TZID, RANGE, RELTYPE
        if( isset( $params[self::CN] ) && in_array( self::CN, $ctrKeys, true )) {
            $output .= self::renderParam( self::CN, $params );
            $CNattrExist = true;
        }
        $output .= self::renderKeyGroup( $KEYGRP3, $ctrKeys, $params ); // SENT_BY, FEATURE, LABEL
        if( $hasLANGctrKey && isset( $params[self::LANGUAGE] )) {
            $attrLANG .= self::renderParam( self::LANGUAGE, $params );
        }
        elseif(( $CNattrExist || $hasLANGctrKey ) && is_string( $lang ) && ! empty( $lang )) {
            $langArr   = [ self::LANGUAGE => $lang ];
            $attrLANG .= self::renderParam( self::LANGUAGE, $langArr );
        }
        if( isset( $params[self::VALUE] )) {
            $output .= self::renderKeyGroup( $VALENC, $VALENC, $params ); // VALUE+ENCODING
        }
        if( isset( $params[self::DERIVED] ) && ( self::FALSE === $params[self::DERIVED] )) {
            unset( $params[self::DERIVED] ); // skip default FALSE for DERIVED
        }
        $output .= self::renderKeyGroup( $ctrKeys, $ctrKeys, $params ); // ctrKeys in order
        if( ! empty( $params )) { // accept other or iana-token (Other IANA-registered) parameter types, last
            $paramKeys = array_keys( $params );
            $output   .= self::renderKeyGroup( $paramKeys, $paramKeys, $params );
        }
        $output .= $attrLANG;
        if( ! empty( $xparams )) { // x-params last
            $paramKeys = array_keys( $xparams );
            $output   .= self::renderKeyGroup( $paramKeys, $paramKeys, $xparams );
        }
        return $output;
    }

    /**
     * Return parameters+x-params with opt. quoted parameter value
     *
     * "-Quotes a value if it contains ':', ';' or ','
     *
     * @param string[] $inputParams
     * @return string[][]
     * @since 2.41.63 2022-09-05
     */
    protected static function quoteParams( array $inputParams ) : array
    {
        static $DFKEYS     = [ self::DISPLAY, self::FEATURE ];
        static $FMTQ       = '"%s"';
        $params = $xparams = [];
        foreach( array_change_key_case( $inputParams, CASE_UPPER ) as $paramKey => $paramValue ) {
            $paramValue = self::circumflexQuoteInvoke( $paramValue );
            if( self::hasColonOrSemicOrComma( $paramValue ) &&
                ! in_array( $paramKey, $DFKEYS, true )) { // DISPLAY, FEATURE
                $paramValue = sprintf( $FMTQ, $paramValue );
            }
            if( self::isXprefixed( $paramKey )) {
                $xparams[$paramKey] = $paramValue;
            }
            else {
                $params[$paramKey] = $paramValue;
            }
        } // end foreach
        ksort( $xparams, SORT_STRING );
        return [ $params, $xparams ];
    }

    /**
     * Return parameter VALUE with opt. circumflex formatted as of rfc6868
     *
     * formatted text line breaks are encoded into ^n (U+005E, U+006E)
     * the ^ character (U+005E) is encoded into ^^ (U+005E, U+005E)
     * the " character (U+0022) is encoded into ^' (U+005E, U+0027)
     *
     * Also ' is encoded into ^' (U+005E, U+0027), NOT rfc6868
     *
     * @param int|string $value
     * @return string
     * @since 2.41.63 - 2022-09-05
     */
    public static function circumflexQuoteInvoke( int|string $value ) : string
    {
        static $CFN        = '^n';
        static $CFCF       = '^^';
        static $CFSQ       = "^'";
        static $CIRCUMFLEX = '^';
        static $NLCHARS    = '\n';
        if( is_int( $value )) {
            return (string) $value;
        }
        $nlCharsExist = str_contains( $value, $NLCHARS );
        $cfCfExist    = str_contains( $value, $CIRCUMFLEX );
        $quotExist    = str_contains( $value, SF::$QQ );
        if( $nlCharsExist ) {
            $value = str_replace( $NLCHARS, $CFN, $value );
        }
        if( $cfCfExist ) {
            $value = str_replace( $CIRCUMFLEX, $CFCF, $value );
        }
        if( $quotExist ) {
            $value = str_replace( SF::$QQ, $CFSQ, $value );
        }
        return $value;
    }

    /**
     * Return rendered parameter (if exists)
     *
     * @param string $paramKey
     * @param string[] $params
     * @return string
     */
    protected static function renderParam( string $paramKey, array & $params ) : string
    {
        static $DIRALTR = [ self::DIR, self::ALTREP ];
        static $FMTCMN  = ';%s=%s';
        static $FMTQTD  = ';%s=%s%s%s';
        if( ! isset( $params[$paramKey] )) {
            return SF::$SP0;
        }
        if( in_array( $paramKey, $DIRALTR, true )) {
            $delim  = str_contains( $params[$paramKey], SF::$QQ ) ? SF::$SP0 : SF::$QQ;
            $output = sprintf( $FMTQTD, $paramKey, $delim, $params[$paramKey], $delim );
        }
        else {
            $output = sprintf( $FMTCMN, $paramKey, $params[$paramKey] );
        }
        unset( $params[$paramKey] );
        return $output;
    }

    /**
     * Return rendered parameter (if exists)
     *
     * @param string[] $keyGroup   keys to probe
     * @param string[] $ctrKeys    probe list
     * @param string[] $params
     * @return string
     */
    protected static function renderKeyGroup( array $keyGroup, array $ctrKeys, array & $params ) : string
    {
        $output = SF::$SP0;
        foreach( $keyGroup as $key ) {
            if( in_array( $key, $ctrKeys, true )) {
                $output .= self::renderParam( $key, $params );
            }
        } // end foreach
        return $output;
    }

    /**
     * Return bool true if string contains any of :;,
     *
     * @param mixed $string
     * @return bool
     */
    protected static function hasColonOrSemicOrComma( mixed $string ): bool
    {
        return ( is_string( $string ) &&
            ( str_contains( $string,  SF::$COLON ) ||
                str_contains( $string, SF::$SEMIC ) ||
                str_contains( $string, SF::$COMMA )));
    }

    /**
     * Fix rfc5545. 3.3.11 Text, ESCAPED-CHAR
     *
     * @param string $string
     * @return string
     * @since  2.27.14 - 2019-02-20
     */
    public static function strrep( string $string ) : string
    {
        static $BSLCN    = '\n';
        static $SPECCHAR = [ 'n', 'N', 'r', ',', ';' ];
        static $SQ       = "'";
        static $QBSLCR   = "\r";
        static $QBSLCN   = "\n";
        static $BSUCN    = '\N';
        $strLen = strlen( $string );
        $pos    = 0;
        // replace single (solo-)backslash by double ones
        while( $pos < $strLen ) {
            if( false === ( $pos = strpos( $string, SF::$BS2, $pos ))) {
                break;
            }
            if( ! in_array( $string[$pos], $SPECCHAR )) {
                $string = substr( $string, 0, $pos ) .
                    SF::$BS2 . substr( $string, ( $pos + 1 ));
                ++$pos;
            }
            ++$pos;
        } // end while
        // replace double quote by single ones
        if( str_contains( $string, SF::$QQ )) {
            $string = str_replace( SF::$QQ, $SQ, $string );
        }
        // replace comma by backslash+comma but skip any previously set of backslash+comma
        // replace semicolon by backslash+semicolon but skip any previously set of backslash+semicolon
        foreach( [ SF::$COMMA, SF::$SEMIC ] as $char ) {
            $offset = 0;
            while( false !== ( $pos = strpos( $string, $char, $offset ))) {
                if(( 0 < $pos ) && ( SF::$BS2 !== substr( $string, ( $pos - 1 )))) {
                    $string = substr( $string, 0, $pos ) .
                        SF::$BS2 . substr( $string, $pos );
                }
                $offset = $pos + 2;
            } // end while
            $string = str_replace(
                SF::$BS2 . SF::$BS2 . $char,
                SF::$BS2 . $char,
                $string
            );
        }
        // replace "\r\n" by '\n'
        if( str_contains( $string, SF::$CRLF )) {
            $string = str_replace( SF::$CRLF, $BSLCN, $string );
        }
        // or replace "\r" by '\n'
        elseif( str_contains( $string, $QBSLCR )) {
            $string = str_replace( $QBSLCR, $BSLCN, $string );
        }
        // or replace '\N' by '\n'
        elseif( str_contains( $string, $QBSLCN )) {
            $string = str_replace( $QBSLCN, $BSLCN, $string );
        }
        // replace '\N' by  '\n'
        if( str_contains( $string, $BSUCN )) {
            $string = str_replace( $BSUCN, $BSLCN, $string );
        }
        // replace "\r\n" by '\n'
        return str_replace( SF::$CRLF, $BSLCN, $string );
    }

    /**
     * Return bool true if name is X-prefixed
     *
     * @param string $name
     * @return bool
     * @since  2.29.5 - 2019-08-30
     */
    public static function isXprefixed( string $name ) : bool
    {
        static $X_ = 'X-';
        return ( 0 === stripos( $name, $X_ ));
    }

    /**
     * Return wrapped string with (byte oriented) line breaks at pos 75
     *
     * Lines of text SHOULD NOT be longer than 75 octets, excluding the line
     * break. Long content lines SHOULD be split into a multiple line
     * representations using a line "folding" technique. That is, a long
     * line can be split between any two characters by inserting a CRLF
     * immediately followed by a single linear white space character (i.e.,
     * SPACE, US-ASCII decimal 32 or HTAB, US-ASCII decimal 9). Any sequence
     * of CRLF followed immediately by a single linear white space character
     * is ignored (i.e., removed) when processing the content type.
     *
     * Edited 2007-08-26 by Anders Litzell, anders@litzell.se to fix bug where
     * the reserved expression "\n" in the arg $string could be broken up by the
     * folding of lines, causing ambiguity in the return string.
     *
     * @param string $string
     * @return string
     * @link   http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
     * @since  2.41.92 - 2025-01-15
     */
    public static function size75( string $string ) : string
    {
        static $LCN     = 'n';
        static $UCN     = 'N';
        static $SPBSLCN = ' \n';
        static $SP1     = ' ';
        $tmp    = $string;
        $inLen  = strlen( $tmp );
        $string = SF::$SP0;
        $outLen = $x = 0;
        while( true ) {
            $x1 = $x + 1;
            if( $inLen <= $x ) {
                $string .= SF::$CRLF; // loop breakes here
                break;
            }
            if(( 74 <= $outLen ) &&
                ( SF::$BS2 === $tmp[$x] ) && // '\\'
                isset( $tmp[$x1] ) &&
                (( $LCN === $tmp[$x1] ) ||
                    ( $UCN === $tmp[$x1] ))) {
                $string .= SF::$CRLF . $SPBSLCN; // don't break lines inside '\n'
                $x      += 2;
                if( $inLen < $x ) {
                    $string .= SF::$CRLF;
                    break; // or here...
                }
                $outLen = 3;
            } // end if
            elseif( 75 <= $outLen ) {
                $string .= SF::$CRLF;
                if( $inLen === $x ) {
                    break; // or here..
                }
                $string .= $SP1;
                $outLen  = 1;
            }
            if( ! isset( $tmp[$x] )) {
                $string .= SF::$CRLF;
                break; // or here?
            } // end if
            $str1    = $tmp[$x];
            $byte    = ord( $str1 );
            $string .= $str1;
            switch( true ) {
                case(( $byte >= 0x20 ) && ( $byte <= 0x7F )) :
                    ++$outLen;                     // characters U-00000000 - U-0000007F (same as ASCII)
                    break;                         // add a one byte character
                case(( $byte & 0xE0 ) === 0xC0 ) : // characters U-00000080 - U-000007FF, mask 110XXXXX
                    if( $inLen > ( $x + 1 )) {
                        ++$outLen;
                        ++$x;                      // add second byte of a two bytes character
                        $string .= $tmp[$x];
                    }
                    break;
                case(( $byte & 0xF0 ) === 0xE0 ) : // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                    if( $inLen > ( $x + 2 )) {
                        ++$outLen;
                        ++$x;
                        $string .= substr( $tmp, $x1, 2 );
                        ++$x;                      // add byte 2-3 of a three bytes character
                    }
                    break;
                case(( $byte & 0xF8 ) === 0xF0 ) : // characters U-00010000 - U-001FFFFF, mask 11110XXX
                    if( $inLen > ( $x + 3 )) {
                        ++$outLen;
                        ++$x;
                        $string .= substr( $tmp, $x1, 3 );
                        $x      += 2;              // add byte 2-4 of a four bytes character
                    }
                    break;
                case(( $byte & 0xFC ) === 0xF8 ) : // characters U-00200000 - U-03FFFFFF, mask 111110XX
                    if( $inLen > ( $x + 4 )) {
                        ++$outLen;
                        ++$x;
                        $string .= substr( $tmp, $x, 4 );
                        $x      += 3;              // add byte 2-5 of a five bytes character
                    }
                    break;
                case(( $byte & 0xFE ) === 0xFC ) : // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                    if( $inLen > ( $x + 5 )) {
                        ++$outLen;
                        ++$x;
                        $string .= substr( $tmp, $x, 5 );
                        $x      += 4;              // add byte 2-6 of a six bytes character
                    }
                    break;
                default:                           // add any other byte without counting up $cCnt
                    break;
            } // end switch( true )
            ++$x;    // next 'byte' to test
        } // end while( true )
        return $string;
    }
}
