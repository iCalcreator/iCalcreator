<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2023 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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

use Exception;

use function bin2hex;
use function explode;
use function floor;
use function implode;
use function random_bytes;
use function rtrim;
use function sprintf;
use function str_contains;
use function str_replace;
use function strlen;
use function stripos;
use function strpos;
use function strrev;
use function strtolower;
use function substr;
use function ucfirst;

/**
 * iCalcreator string support class
 *
 * @since  2.41.68 - 2022-10-21
 */
class StringFactory
{
    /**
     * @var string
     */
    public static string $BS2 = '\\';

    /**
     * @var string
     */
    public static string $QQ  = '"';

    /**
     * Return array property name and (params+)value from (string) row
     *
     * @param  string $row
     * @return string[]   propName and the trailing part of the row
     * @since  2.41.68 - 2022-10-21
     */
    public static function getPropName( string $row ) : array
    {
        return self::splitByFirstSQorColon( $row );
    }

    /**
     * Return array, string splitted by first found semicolon or colon, split-char excluded
     *
     * No one found return [ string, null ]
     *
     * @param string $string
     * @return string[]
     * @since  2.41.68 - 2022-10-21
     */
    public static function splitByFirstSQorColon( string $string ) : array
    {
        $sclnPos = strpos( $string, Util::$SEMIC ); // first found
        $clnPos  = strpos( $string, Util::$COLON ); // first found
        switch( true ) {
            case (( false === $sclnPos ) && ( false === $clnPos )) : // no one found
                return [ $string, Util::$SP0 ];
            case (( false !== $sclnPos ) && ( false === $clnPos )) : // split by semicolon
                $firstPart = strstr( $string, Util::$SEMIC, true );
                break;
            case (( false === $sclnPos ) && ( false !== $clnPos )) : // split by colon
                $firstPart = strstr( $string, Util::$COLON, true  );
                break;
            case ( $sclnPos < $clnPos ) :                            // split by semicolon
                $firstPart = strstr( $string, Util::$SEMIC, true );
                break;
            default : // ie $sclnPos > $clnPos                       // split by colon
                $firstPart = strstr( $string, Util::$COLON, true );
                break;
        } // end switch
        return [ $firstPart, self::after( $firstPart, $string  ) ];
    }

    /**
     * Return a random (and unique) sequence of characters
     *
     * @param int $cnt
     * @return string
     * @throws Exception
     * @since  2.40.11 - 2022-01-15
     */
    public static function getRandChars( int $cnt ) : string
    {
        $cnt = (int) floor( $cnt / 2 );
        return bin2hex( random_bytes( $cnt ));
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
     * Fix opt un-urlencoded) '<'|'>'|'@' in a SOURCE, TZURL or URL
     *
     * orginating from any Apple device
     *
     * @param string $line
     * @since  2.41.68 - 2022-10-22
     */
    public static function checkFixUrlDecode( string & $line ) : void
    {
        static $PFCHARS1 = '%3C';
        static $SFCHARS1 = '%3E';
        static $PFCHARS2 = '<';
        static $SFCHARS2 = '>';
        static $SCHAR31  = '%40';
        static $SCHAR32  = '@';
        if(( false !== stripos( $line, $PFCHARS1 )) && ( false !== stripos( $line, $SFCHARS1 ))) {
            $line = str_replace( [ $PFCHARS1, $SFCHARS1 ], Util::$SP0, $line ); // rem url-dec
        }
        elseif(( str_contains( $line, $PFCHARS2 )) && ( str_contains( $line, $SFCHARS2 ))) {
            $line = str_replace( [ $PFCHARS2, $SFCHARS2 ], Util::$SP0, $line ); // rem <>
        }
        if( str_contains( $line, $SCHAR31 )) {
            $line = str_replace( $SCHAR31, $SCHAR32, $line ); // repl with @
        }
    }

    // 'fax:' removed

    /**
     * @var string[]  dito
     */
    public static array $PROTO4 = [
        'crid:', 'news:', 'pres:',
        ':http:'
    ];

    /**
     * Replace '\\', '\,', '\;' by '\', ',', ';'
     *
     * @param string $string
     * @return string
     * @since  2.22.2 - 2015-06-25
     */
    public static function strunrep( string $string ) : string
    {
        static $BS4 = '\\\\';
        static $BSCOMMA = '\,';
        static $BSSEMIC = '\;';
        $string = str_replace( $BS4, self::$BS2, $string );
        $string = str_replace( $BSCOMMA, Util::$COMMA, $string );
        return str_replace( $BSSEMIC, Util::$SEMIC, $string );
    }

    /**
     * Return string with trimmed trailing \n (PHP_EOL)
     *
     * @param string $value
     * @return string
     * @since  2.41.36 - 2022-04-11
     */
    public static function trimTrailNL( string $value ) : string
    {
        static $NL = '\n';
        if( ! empty( $value ) && ( $NL === strtolower( substr( $value, -2 )))) {
            $value = substr( $value, 0, ( strlen( $value ) - 2 ));
        }
        return rtrim( $value, PHP_EOL );
    }

    /**
     * @link https://php.net/manual/en/function.substr.php#112707
     */

    /**
     * @var string
     */
    private static string $SP0 = '';

    /**
     * Return substring after first found needle in haystack
     *
     * Case-sensitive search for needle in haystack
     * If needle is not found in haystack, '' is returned
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function after( string $needle, string $haystack ) : string
    {
        if( ! str_contains( $haystack, $needle )) {
            return self::$SP0;
        }
        $pos = strpos( $haystack, $needle );
        return substr( $haystack, $pos + strlen( $needle ));
    }

    /**
     * Return substring after last found  needle in haystack
     *
     * Case-sensitive search for needle in haystack
     * If needle is not found in haystack, '' is returned
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function afterLast( string $needle, string $haystack ) : string
    {
        if( ! str_contains( $haystack, $needle )) {
            return self::$SP0;
        }
        $pos = self::strrevpos( $haystack, $needle );
        return substr( $haystack, $pos + strlen( $needle ));
    }

    /**
     * Return substring before first found needle in haystack
     *
     * Case-sensitive search for needle in haystack
     * If needle is not found in haystack, '' is returned
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function before( string $needle, string $haystack ) : string
    {
        if( ! str_contains( $haystack, $needle )) {
            return self::$SP0;
        }
        return substr( $haystack, 0, strpos( $haystack, $needle ));
    }

    /**
     * Return substring before last needle in haystack
     *
     * Case-sensitive search for needle in haystack
     * If needle is not found in haystack, '' is returned
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function beforeLast( string $needle, string $haystack ) : string
    {
        if( ! str_contains( $haystack, $needle )) {
            return self::$SP0;
        }
        return substr( $haystack, 0, self::strrevpos( $haystack, $needle ));
    }

    /**
     * Return substring between (first found) needles in haystack
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
     * @since 2.41.68  2022-10-21
     */
    public static function between( string $needle1, string $needle2, string $haystack ) : string
    {
        $exists1 = str_contains( $haystack, $needle1 );
        $exists2 = str_contains( $haystack, $needle2 );
        return match( true ) {
            ! $exists1 && ! $exists2 => self::$SP0,
            $exists1 && ! $exists2   => self::after( $needle1, $haystack ),
            ! $exists1 && $exists2   => strstr( $haystack, $needle2, true ),
            default                  => strstr( self::after( $needle1, $haystack ),  $needle2, true ),
        }; // end switch
    }

    /**
     * Return substring between last needles in haystack
     *
     * Case-sensitive search for needles in haystack
     * If no needles found in haystack, '' is returned
     * If only needle1 found, substring after(last) is returned
     * If only needle2 found, substring before(last) is returned
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle1
     * @param string $needle2
     * @param string $haystack
     * @return string
     */
    public static function betweenLast( string $needle1, string $needle2, string $haystack ) : string
    {
        $exists1 = str_contains( $haystack, $needle1 );
        $exists2 = str_contains( $haystack, $needle2 );
        return match( true ) {
            ! $exists1 && ! $exists2 => self::$SP0,
            $exists1 && ! $exists2   => self::afterLast( $needle1, $haystack ),
            ! $exists1 && $exists2   => self::beforeLast( $needle2, $haystack ),
            default                  => self::afterLast( $needle1, self::beforeLast( $needle2, $haystack ))
        };
    }

    /**
     * Return int for length from start to last needle in haystack, false on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $haystack
     * @param string $needle
     * @return bool|int    bool false on needle not in haystack
     */
    public static function strrevpos( string $haystack, string $needle ) : bool | int
    {
        return ( false !== ( $rev_pos = strpos( strrev( $haystack ), strrev( $needle ))))
            ? ( strlen( $haystack ) - $rev_pos - strlen( $needle ))
            : false;
    }

    /**
     * Component properties method name utility methods
     */

    /**
     * Return internal name for property
     *
     * @param string $propName
     * @return string
     * @since  2.27.1 - 2018-12-16
     */
    public static function getInternalPropName( string $propName ) : string
    {
        $internalName = strtolower( $propName );
        if( str_contains( $internalName, Util::$MINUS )) {
            $internalName = implode( explode( Util::$MINUS, $internalName ));
        }
        return $internalName;
    }

    /**
     * Return method from format and propName
     *
     * @param string $format
     * @param string $propName
     * @return string
     * @since  2.27.14 - 2019-02-18
     */
    public static function getMethodName( string $format, string $propName ) : string
    {
        return sprintf( $format, ucfirst( self::getInternalPropName( $propName )));
    }

    /**
     * Return name for property delete-method
     *
     * @param string $propName
     * @return string
     * @since  2.27.1 - 2019-01-17
     */
    public static function getCreateMethodName( string $propName ) : string
    {
        static $FMT = 'create%s';
        return self::getMethodName( $FMT, $propName );
    }

    /**
     * Return name for property delete-method
     *
     * @param string $propName
     * @return string
     * @since  2.27.1 - 2018-12-12
     */
    public static function getDeleteMethodName( string $propName ) : string
    {
        static $FMT = 'delete%s';
        return self::getMethodName( $FMT, $propName );
    }

    /**
     * Return name for property get-method
     *
     * @param string $propName
     * @return string
     * @since 2.41.35 2022-03-28
     */
    public static function getIsMethodSetName( string $propName ) : string
    {
        static $FMT = 'is%sSet';
        return self::getMethodName( $FMT, $propName );
    }

    /**
     * Return name for property getAll-method
     *
     * @param string $propName
     * @return string
     * @since  2.41.51 - 2022-08-09
     */
    public static function getGetAllMethodName( string $propName ) : string
    {
        static $FMT = 'getAll%s';
        return self::getMethodName( $FMT, $propName );
    }

    /**
     * Return name for property get-method
     *
     * @param string $propName
     * @return string
     * @since  2.27.1 - 2018-12-12
     */
    public static function getGetMethodName( string $propName ) : string
    {
        static $FMT = 'get%s';
        return self::getMethodName( $FMT, $propName );
    }

    /**
     * Return name for property set-method
     *
     * @param string $propName
     * @return string
     * @since  2.27.1 - 2018-12-16
     */
    public static function getSetMethodName( string $propName ) : string
    {
        static $FMT = 'set%s';
        return self::getMethodName( $FMT, $propName );
    }

    /**
     * Counts (unique) strings
     *
     * @param string $string
     * @param array $output
     * @return void
     */
    public static function stringCount( string $string, array & $output ) : void
    {
        $content = trim( $string );
        if( ! empty( $content ) ) {
            if( ! isset( $output[$content] ) ) {
                $output[$content] = 1;
            }
            else {
                ++$output[$content];
            }
        }
    }

    /**
     * Counts (unique) comma separated parts in string
     *
     * @param string $string
     * @param array $output
     * @return void
     */
    public static function commaSplitCount( string $string, array & $output ) : void
    {
        $content = explode( Util::$COMMA, $string );
        foreach( $content as $contentPart ) {
            self::stringCount( $contentPart, $output );
        }
    }

    /**
     * Return (rendered) compType from FQCN
     *
     * @param string $class
     * @return string
     */
    public static function compTypeFromClass( string $class ) : string
    {
        return ucfirst( strtolower( self::afterLast( self::$BS2, $class  )));
    }
}
