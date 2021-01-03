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

use UnexpectedValueException;

use function bin2hex;
use function count;
use function ctype_digit;
use function explode;
use function floor;
use function in_array;
use function openssl_random_pseudo_bytes;
use function ord;
use function rtrim;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function strrev;
use function strtolower;
use function strtoupper;
use function substr;
use function substr_count;
use function trim;

/**
 * iCalcreator TEXT support class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.29.30b - 2020-12-22
 */
class StringFactory
{

    /**
     * @var string
     */
    public static $BS2 = '\\';
    public static $QQ  = '"';

    /**
     * Return concatenated calendar rows, one row for each property
     *
     * @param array $rows
     * @return array
     * @since  2.29.20 - 2020-01-31
     */
    public static function concatRows( $rows )
    {
        static $CHARs = [ ' ', "\t" ];
        $output = [];
        $cnt    = count( $rows );
        for( $i = 0; $i < $cnt; $i++ ) {
            $line = rtrim( $rows[$i], Util::$CRLF );
            $i1 = $i + 1;
            while(( $i < $cnt ) && isset( $rows[$i1] ) &&
                 ! empty( $rows[$i1] ) &&
                in_array( substr( $rows[$i1], 0, 1 ), $CHARs )) {
                $i += 1;
                $line .= rtrim( substr( $rows[$i], 1 ), Util::$CRLF );
                $i1 = $i + 1;
            } // end while
            $output[] = $line;
        } // end for
        return $output;
    }

    /**
     * @var string
     */
    private static $BEGIN_VCALENDAR = 'BEGIN:VCALENDAR';
    private static $END_VCALENDAR   = 'END:VCALENDAR';
    private static $NLCHARS         = '\n';

    /**
     * Return rows to parse from string or array
     *
     * Used by Vcalendar & RegulateTimezoneFactory
     * @param string|array $unParsedText strict rfc2445 formatted, single property string or array of strings
     * @return array
     * @throws UnexpectedValueException
     * @since  2.29.3 - 2019-08-29
     */
    public static function conformParseInput( $unParsedText = null )
    {
        static $ERR10 = 'Only %d rows in calendar content :%s';
        $arrParse = false;
        if( is_array( $unParsedText )) {
            $rows     = implode( self::$NLCHARS . Util::$CRLF, $unParsedText );
            $arrParse = true;
        }
        else { // string
            $rows = $unParsedText;
        }
        /* fix line folding */
        $rows = self::convEolChar( $rows );
        if( $arrParse ) {
            foreach( $rows as $lix => $row ) {
                $rows[$lix] = self::trimTrailNL( $row );
            }
        }
        /* skip leading (empty/invalid) lines (and remove leading BOM chars etc) */
        $rows = self::trimLeadingRows( $rows );
        $cnt  = count( $rows );
        if( 3 > $cnt ) { /* err 10 */
            throw new UnexpectedValueException(
                sprintf( $ERR10, $cnt, PHP_EOL . implode( PHP_EOL, $rows ))
            );
        }
        /* skip trailing empty lines and ensure an end row */
        $rows = self::trimTrailingRows( $rows );
        return $rows;
    }

    /**
     * Return array to parse with leading (empty/invalid) lines removed (incl leading BOM chars etc)
     *
     * @param array $rows
     * @return array
     * @since  2.29.3 - 2019-08-29
     */
    private static function trimLeadingRows( $rows )
    {
        foreach( $rows as $lix => $row ) {
            if( false !== stripos( $row, self::$BEGIN_VCALENDAR )) {
                $rows[$lix] = self::$BEGIN_VCALENDAR;
                break;
            }
            unset( $rows[$lix] );
        } // end foreach
        return $rows;
    }

    /**
     * Return array to parse with trailing empty lines removed and ensured an end row
     *
     * @param array $rows
     * @return array
     * @since  2.29.3 - 2019-08-29
     */
    private static function trimTrailingRows( $rows )
    {
        $lix = array_keys( $rows );
        $lix = end( $lix );
        while( 3 < $lix ) {
            $tst = trim( $rows[$lix] );
            if(( self::$NLCHARS == $tst ) || empty( $tst )) {
                unset( $rows[$lix] );
                $lix--;
                continue;
            }
            if( false === stripos( $rows[$lix], self::$END_VCALENDAR )) {
                $rows[] = self::$END_VCALENDAR;
            }
            else {
                $rows[$lix] = self::$END_VCALENDAR;
            }
            break;
        } // end while
        return $rows;
    }

    /**
     * Return string with removed ical line folding
     *
     * Remove any line-endings that may include spaces or tabs
     * and convert all line endings (iCal default '\r\n'),
     * takes care of '\r\n', '\r' and '\n' and mixed '\r\n'+'\r', '\r\n'+'\n'
     *
     * @param string $text
     * @return array
     * @since  2.29.9 - 2019-03-30
     */
    public static function convEolChar( & $text )
    {
        static $BASEDELIM  = null;
        static $BASEDELIMs = null;
        static $EMPTYROW   = null;
        static $FMT        = '%1$s%2$75s%1$s';
        static $CRLFs      = [ "\r\n", "\n\r", "\n", "\r" ];
        static $CRLFexts   = [ "\r\n ", "\r\n\t" ];
        /* fix dummy line separator etc */
        if( empty( $BASEDELIM )) {
            $BASEDELIM  = self::getRandChars( 16 );
            $BASEDELIMs = $BASEDELIM . $BASEDELIM;
            $EMPTYROW   = sprintf( $FMT, $BASEDELIM, Util::$SP0 );
        }
        /* fix eol chars */
        $text = str_replace( $CRLFs, $BASEDELIM, $text );
        /* fix empty lines */
        $text = str_replace( $BASEDELIMs, $EMPTYROW, $text );
        /* fix line folding */
        $text = str_replace( $BASEDELIM, Util::$CRLF, $text );
        $text = str_replace( $CRLFexts, null, $text );
        /* split in component/property lines */
        return explode( Util::$CRLF, $text );
    }

    /**
     * Return formatted output for calendar component property
     *
     * @param string $label      property name
     * @param string $attributes property attributes
     * @param string $content    property content
     * @return string
     * @since  2.22.20 - 2017-01-30
     */
    public static function createElement(
        $label,
        $attributes = null,
        $content = null
    ) {
        $output = strtoupper( $label );
        if( ! empty( $attributes )) {
            $output .= trim( $attributes );
        }
        $output .= Util::$COLON . trim( $content );
        return self::size75( $output );
    }

    /**
     * Return property name and (params+)value from (string) row
     *
     * @param  string $row
     * @return array   propName and the trailing part of the row
     * @since  2.29.11 - 2019-08-26
     */
    public static function getPropName( $row )
    {
        $sclnPos = strpos( $row, Util::$SEMIC );
        $clnPos  = strpos( $row, Util::$COLON );
        switch( true ) {
            case (( false === $sclnPos ) && ( false === $clnPos )) : // no params and no value
                return [ $row, null ];
                break;
            case (( false !== $sclnPos ) && ( false === $clnPos )) : // param exist and NO value ??
                $propName = self::before( Util::$SEMIC, $row );
                break;
            case (( false === $sclnPos ) && ( false !== $clnPos )) : // no params
                $propName = self::before( Util::$COLON, $row  );
                break;
            case ( $sclnPos < $clnPos ) :                            // param(s) with value ??
                $propName = self::before( Util::$SEMIC, $row );
                break;
            default : // ie $sclnPos > $clnPos                       // no params
                $propName = self::before( Util::$COLON, $row );
                break;
        } // end switch
        return [ $propName, self::after( $propName, $row  ) ];
    }

    /**
     * Return a random (and unique) sequence of characters
     *
     * @param int $cnt
     * @return string
     * @since  2.27.3 - 2018-12-28
     */
    public static function getRandChars( $cnt )
    {
        $cnt = (int) floor( $cnt / 2 );
        $x   = 0;
        do {
            $randChars = bin2hex( openssl_random_pseudo_bytes( $cnt, $cStrong ));
            $x         += 1;
        } while(( 3 > $x ) && ( false == $cStrong ));
        return $randChars;
    }

    /**
     * Return bool true if name is X-prefixed
     *
     * @param string $name
     * @return bool
     * @since  2.29.5 - 2019-08-30
     */
    public static function isXprefixed( $name )
    {
        static $X_ = 'X-';
        return ( $X_ == strtoupper( substr( $name, 0, 2 ) ));
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
     * @since  2.29.29 - 2020-09-11
     */
    public static function size75( $string )
    {
        static $LCN     = 'n';
        static $UCN     = 'N';
        static $SPBSLCN = ' \n';
        static $SP1     = ' ';
        $tmp    = $string;
        $len    = strlen( $tmp );
        $string = null;
        $cCnt   = $x = 0;
        while( true ) {
            $x1 = $x + 1;
            if( $len < $x ) {
                $string .= Util::$CRLF; // loop breakes here
                break;
            }
            elseif(( 74 <= $cCnt ) &&
                ( self::$BS2 == substr( $tmp, $x, 1 ) ) && // '\\'
                (( $LCN == substr( $tmp, $x1, 1 )) ||
                    ( $UCN == substr( $tmp, $x1, 1 )))) {
                $string .= Util::$CRLF . $SPBSLCN; // don't break lines inside '\n'
                $x      += 2;
                if( $len < $x ) {
                    $string .= Util::$CRLF;
                    break; // or here...
                }
                $cCnt = 3;
            }
            elseif( 75 <= $cCnt ) {
                // $string .= Util::$CRLF . $SP1;
                $string .= Util::$CRLF;
                if( $len == $x ) {
                    break; // or here..
                }
                $string .= $SP1;
                $cCnt    = 1;
            }
            $str1    = substr( $tmp, $x, 1 );
            $byte    = ord( $str1 );
            $string .= $str1;
            switch( true ) {
                case(( $byte >= 0x20 ) && ( $byte <= 0x7F )) :
                    $cCnt += 1;                    // characters U-00000000 - U-0000007F (same as ASCII)
                    break;                         // add a one byte character
                case(( $byte & 0xE0 ) == 0xC0 ) : // characters U-00000080 - U-000007FF, mask 110XXXXX
                    if( $len > ( $x + 1 )) {
                        $cCnt   += 1;
                        $x      += 1;              // add second byte of a two bytes character
                        $string .= substr( $tmp, $x, 1 );
                    }
                    break;
                case(( $byte & 0xF0 ) == 0xE0 ) : // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                    if( $len > ( $x + 2 )) {
                        $cCnt   += 1;
                        $x      += 1;
                        $string .= substr( $tmp, $x1, 2 );
                        $x      += 1;              // add byte 2-3 of a three bytes character
                    }
                    break;
                case(( $byte & 0xF8 ) == 0xF0 ) : // characters U-00010000 - U-001FFFFF, mask 11110XXX
                    if( $len > ( $x + 3 )) {
                        $cCnt   += 1;
                        $x      += 1;
                        $string .= substr( $tmp, $x1, 3 );
                        $x      += 3;              // add byte 2-4 of a four bytes character
                    }
                    break;
                case(( $byte & 0xFC ) == 0xF8 ) : // characters U-00200000 - U-03FFFFFF, mask 111110XX
                    if( $len > ( $x + 4 )) {
                        $cCnt   += 1;
                        $x      += 1;
                        $string .= substr( $tmp, $x, 4 );
                        $x      += 4;              // add byte 2-5 of a five bytes character
                    }
                    break;
                case(( $byte & 0xFE ) == 0xFC ) : // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                    if( $len > ( $x + 5 )) {
                        $cCnt   += 1;
                        $x      += 1;
                        $string .= substr( $tmp, $x, 5 );
                        $x      += 5;              // add byte 2-6 of a six bytes character
                    }
                    break;
                default:                           // add any other byte without counting up $cCnt
                    break;
            } // end switch( true )
            $x += 1;    // next 'byte' to test
        } // end while( true )
        return $string;
    }

    /**
     * Return property value and attributes
     *
     * Attributes are prefixed by ';', value by ':', BUT they may exists in attr/values
     * @param string $line     property content
     * @return array           [line, [*propAttr]]
     * @todo   fix 2-5 pos port number
     * @since  2.29.22 - 2020-09-01
     */
    public static function splitContent( $line )
    {
        static $CSS    = '://';
        static $EQ     = '=';
        $clnPos        = strpos( $line, Util::$COLON );
        if(( false === $clnPos )) {
            return [ $line, [] ]; // no params
        }
        if( 0 == $clnPos ) { // no params,  most frequent
            return [ substr( $line, 1 ) , [] ];
        }
        $sclnPos       = strpos( $line, Util::$SEMIC );
        if(( 0 === $sclnPos ) &&
            ( 1 == substr_count( $line, Util::$SEMIC )) &&
            ( 1 == substr_count( $line, Util::$COLON ))) {
            // single param only (and no colons in param values), 2nd most frequent
            $param = self::between( Util::$SEMIC, Util::$COLON, $line );
            return [
                self::after( Util::$COLON, $line ),
                [
                    self::before( $EQ, $param ) =>
                        trim( self::after( $EQ, $param ), self::$QQ )
                ]
            ];
        } // end if
        /* more than one param here (or a tricky one...) */
        $attr          = [];
        $attrix        = -1;
        $WithinQuotes  = false;
        $len           = strlen( $line );
        $cix           = 0;
        while( $cix < $len ) {
            $str1 = substr( $line, $cix, 1 );
            $cix1 = $cix + 1;
            if( ! $WithinQuotes &&
                ( Util::$COLON == $str1 ) &&
                ( $CSS != substr( $line, $cix, 3 )) &&
                ! self::colonIsPrefixedByProtocol( $line, $cix ) &&
                ! self::hasPortNUmber( substr( $line, $cix1, 7 ))) {
                $line = substr( $line, $cix1 );
                break;
            }
            if( self::$QQ == $str1 ) { // '"'
                $WithinQuotes = ! $WithinQuotes;
            }
            if( Util::$SEMIC == $str1 ) { // ';'
                $attrix += 1;
                $attr[$attrix] = null; // initiate
            }
            else {
                $attr[$attrix] .= $str1;
            }
            $cix += 1;
        } // end while...
        /* make attributes in array format */
        $propAttr = [];
        foreach( $attr as $attribute ) {
            $attrSplit = explode( $EQ, $attribute, 2 );
            if( 1 < count( $attrSplit )) {
                $propAttr[$attrSplit[0]] = $attrSplit[1];
            }
        }
        return [ $line, $propAttr ];
    }

    /**
     * Return bool true if colon-pos is prefixed by protocol
     *
     * @see  https://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml overkill !!
     *
     * @param string $line
     * @param int    $cix
     * @return bool
     * @accvess private
     * @since  2.29.30b - 2020-12-23
     */
    private static function colonIsPrefixedByProtocol( $line, $cix )
    {
        static $MSTZ   = [ 'utc-', 'utc+', 'gmt-', 'gmt+' ];
        static $PROTO3 = [ 'cid:', 'sms:', 'tel:', 'urn:'
            ,'uri:' // somewhat odd type here...
        ]; // 'fax:' removed
        static $PROTO4 = [ 'crid:', 'news:', 'pres:' ];
        static $PROTO5 = [ 'mailto:', 'telnet:' ];
        $line = strtolower( $line );
        return (( in_array( substr( $line, $cix - 6, 4 ), $MSTZ )) || // ?? -6
                ( in_array( substr( $line, $cix - 3, 4 ), $PROTO3 )) ||
                ( in_array( substr( $line, $cix - 4, 5 ), $PROTO4 )) ||
                ( in_array( substr( $line, $cix - 6, 7 ), $PROTO5 )));
    }

    /**
     * Return bool true if leading chars in string is a port number (e.i. followed by '/')
     *
     * @param string $string
     * @return bool
     * @since  2.27.22 - 2020-09-01
     */
    private static function hasPortNUmber( $string )
    {
        $len      = strlen( $string );
        for( $x = 0; $x < $len; $x++ ) {
            $str1 = substr( $string, $x, 1 );
            if( ! ctype_digit( $str1 )) {
                break;
            }
            if( Util::$SLASH == $str1 ) {
                return true;
            }
        } // end for
        return false;
    }

    /**
     * Fix rfc5545. 3.3.11 Text, ESCAPED-CHAR
     *
     * @param string $string
     * @return string
     * @since  2.27.14 - 2019-02-20
     */
    public static function strrep( $string )
    {
        static $BSLCN    = '\n';
        static $SPECCHAR = [ 'n', 'N', 'r', ',', ';' ];
        static $SQ       = "'";
        static $QBSLCR   = "\r";
        static $QBSLCN   = "\n";
        static $BSUCN    = '\N';
        $string = (string) $string;
        $strLen = strlen( $string );
        $pos    = 0;
        // replace single (solo-)backslash by double ones
        while( $pos < $strLen ) {
            if( false === ( $pos = strpos( $string, self::$BS2, $pos ))) {
                break;
            }
            if( ! in_array( substr( $string, $pos, 1 ), $SPECCHAR )) {
                $string = substr( $string, 0, $pos ) .
                    self::$BS2 . substr( $string, ( $pos + 1 ));
                $pos += 1;
            }
            $pos += 1;
        } // end while
        // replace double quote by single ones
        if( false !== strpos( $string, self::$QQ )) {
            $string = str_replace( self::$QQ, $SQ, $string );
        }
        // replace comma by backslash+comma but skip any previously set of backslash+comma
        // replace semicolon by backslash+semicolon but skip any previously set of backslash+semicolon
        foreach( [ Util::$COMMA, Util::$SEMIC ] as $char ) {
            $offset = 0;
            while( false !== ( $pos = strpos( $string, $char, $offset ))) {
                if(( 0 < $pos ) && ( self::$BS2 != substr( $string, ( $pos - 1 )))) {
                    $string = substr( $string, 0, $pos ) .
                        self::$BS2 . substr( $string, $pos );
                }
                $offset = $pos + 2;
            } // end while
            $string = str_replace(
                self::$BS2 . self::$BS2 . $char,
                self::$BS2 . $char,
                $string
            );
        }
        // replace "\r\n" by '\n'
        if( false !== strpos( $string, Util::$CRLF )) {
            $string = str_replace( Util::$CRLF, $BSLCN, $string );
        }
        // or replace "\r" by '\n'
        elseif( false !== strpos( $string, $QBSLCR )) {
            $string = str_replace( $QBSLCR, $BSLCN, $string );
        }
        // or replace '\N' by '\n'
        elseif( false !== strpos( $string, $QBSLCN )) {
            $string = str_replace( $QBSLCN, $BSLCN, $string );
        }
        // replace '\N' by  '\n'
        if( false !== strpos( $string, $BSUCN )) {
            $string = str_replace( $BSUCN, $BSLCN, $string );
        }
        // replace "\r\n" by '\n'
        $string = str_replace( Util::$CRLF, $BSLCN, $string );
        return $string;
    }

    /**
     * Special characters management input
     *
     * @param string $string
     * @return string
     * @since  2.22.2 - 2015-06-25
     */
    public static function strunrep( $string )
    {
        static $BS4 = '\\\\';
        static $BSCOMMA = '\,';
        static $BSSEMIC = '\;';
        $string = str_replace( $BS4, self::$BS2, $string );
        $string = str_replace( $BSCOMMA, Util::$COMMA, $string );
        $string = str_replace( $BSSEMIC, Util::$SEMIC, $string );
        return $string;
    }

    /**
     * Return string with trimmed trailing \n
     *
     * @param string $value
     * @return string
     * @since  2.29.14 - 2019-09-03
     */
    public static function trimTrailNL( $value )
    {
        static $NL = '\n';
        $value = (string) $value;
        if( $NL == strtolower( substr( $value, -2 ))) {
            $value = substr( $value, 0, ( strlen( $value ) - 2 ));
        }
        return $value;
    }

    /**
     * @link https://php.net/manual/en/function.substr.php#112707
     */

    /**
     * @var string
     */
    private static $SP0 = '';

    /**
     * Return bool true if needle is in haystack
     *
     * Case-sensitive search for needle in haystack
     *
     * @param $needle
     * @param $haystack
     * @return bool
     */
    public static function isIn( $needle, $haystack )
    {
        return ( false !== ( $pos = strpos( $haystack, $needle )));
    }

    /**
     * Return substring after first found needle in haystack, '' on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function after( $needle, $haystack )
    {
        if( ! self::isIn( $needle, $haystack )) {
            return self::$SP0;
        }
        $pos = strpos( $haystack, $needle );
        return substr( $haystack, $pos + strlen( $needle ));
    }

    /**
     * Return substring after last found  needle in haystack, '' on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function afterLast( $needle, $haystack )
    {
        if( ! self::isIn( $needle, $haystack )) {
            return self::$SP0;
        }
        $pos = self::strrevpos( $haystack, $needle );
        return substr( $haystack, $pos + strlen( $needle ));
    }

    /**
     * Return substring before first found needle in haystack, '' on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function before( $needle, $haystack )
    {
        if( ! self::isIn( $needle, $haystack )) {
            return self::$SP0;
        }
        return substr( $haystack, 0, strpos( $haystack, $needle ));
    }

    /**
     * Return substring before last needle in haystack, '' on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function beforeLast( $needle, $haystack )
    {
        if( ! self::isIn( $needle, $haystack )) {
            return self::$SP0;
        }
        return substr( $haystack, 0, self::strrevpos( $haystack, $needle ));
    }

    /**
     * Return substring between needles in haystack
     *
     * Case-sensitive search for needles in haystack
     * If no needles found in haystack, '' is returned
     * If only needle1 found, substring after is returned
     * If only needle2 found, substring before is returned
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle1
     * @param string $needle2
     * @param string $haystack
     * @return string
     */
    public static function between( $needle1, $needle2, $haystack )
    {
        $exists1 = self::isIn( $needle1, $haystack );
        $exists2 = self::isIn( $needle2, $haystack );
        switch( true ) {
            case ( ! $exists1 && ! $exists2 ) :
                return self::$SP0;
                break;
            case ( $exists1  && ! $exists2 ) :
                return self::after( $needle1, $haystack );
                break;
            case ( ! $exists1 && $exists2 ) :
                return self::before( $needle2, $haystack );
                break;
            default :
                return self::before( $needle2, self::after( $needle1, $haystack ));
                break;
        } // end switch
    }

    /**
     * Return substring between last needles in haystack
     *
     * Case-sensitive search for needles in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle1
     * @param string $needle2
     * @param string $haystack
     * @return string
     */
    public static function betweenLast( $needle1, $needle2, $haystack )
    {
        return self::afterLast( $needle1, self::beforeLast( $needle2, $haystack ));
    }

    /**
     * Return int for length from start to last needle in haystack, false on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $haystack
     * @param string $needle
     * @return int
     */
    public static function strrevpos( $haystack, $needle )
    {
        return ( false !== ( $rev_pos = strpos( strrev( $haystack ), strrev( $needle ))))
            ? ( strlen( $haystack ) - $rev_pos - strlen( $needle ))
            : false;
    }

    /**
     * Return bool true if haystack starts with needle, false on not found or to large
     *
     * Case-sensitive search for needle in haystack
     *
     * @param string $haystack
     * @param string $needle
     * @param string $len       if found contains length of needle
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.29.11 - 2019-08-28
     */
    public static function startsWith( $haystack, $needle, & $len = null )
    {
        $len       = null;
        $needleLen = strlen( $needle );
        if( $needleLen > strlen( $haystack )) {
            return false;
        }
        if( 0 === strpos( $haystack, $needle )) {
            $len = $needleLen;
            return true;
        }
        return false;
    }

    /**
     * Return bool true if haystack ends with needle, false on not found or to large
     *
     * Case-sensitive search for needle in haystack
     *
     * @param string $haystack
     * @param string $needle
     * @param string $len       if found contains length of needle
     * @return bool
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.29.23 - 2020-07-28
     */
    public static function endsWith( $haystack, $needle, & $len = null )
    {
        $len       = null;
        $needleLen = strlen( $needle );
        if( $needleLen > strlen( $haystack )) {
            return false;
        }
        if( $needle == substr( $haystack, ( 0 - $needleLen ))) {
            $len = $needleLen;
            return true;
        }
        return false;
    }
}
