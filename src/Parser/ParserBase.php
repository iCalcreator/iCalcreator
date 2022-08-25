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
namespace Kigkonsult\Icalcreator\Parser;

use Exception;
use Kigkonsult\Icalcreator\CalendarComponent;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Vcalendar;

use function bin2hex;
use function count;
use function explode;
use function in_array;
use function rtrim;
use function sprintf;
use function str_replace;

/**
 * @since 2.41.54 - 2022-08-00
 */
abstract class ParserBase implements IcalInterface
{
    /**
     * @var string
     */
    protected static string $COLON = ':';

    /**
     * @var string
     */
    protected static string $COMMA = ',';

    /**
     * @var string
     */
    protected static string $CRLF  = "\r\n";

    /**
     * @var string
     */
    protected static string $SEMIC = ';';

    /**
     * @var string
     */
    protected static string $SP0   = '';

    /**
     * Protocols
     *
     * @var string[]
     */
    public static array $PROTO3 = [ 'cid:', 'sms:', 'tel:', 'urn:' ];

    /**
     * @var string[]  dito
     */
    public static array $PROTO5 = [ 'https:' ];
    /**
     * @var string[]  dito
     */
    public static array $PROTO6 = [ 'mailto:', 'telnet:' ];
    /**
     * @var string[]  dito
     */
    public static array $PROTO7 = [ 'message:' ];

    /**
     * @var string[]  iCal component TEXT properties that may contain '\\', ',', ';'
     * @usedby VcalendarParser + ComponentParser
     *
     * the others are
     *    ACTION, CLASS, COLOR, RELATED-TO,
     *    PARTICIPANT_TYPE, REQUEST-STATUS, RESOURCE_TYPE
     *    STATUS, TRANSP, TZID, TZID_ALIAS_OF, TZNAME, UID
     */
    protected static array $TEXTPROPS = [
        self::CATEGORIES,
        self::COMMENT,
        self::CONTACT,
        self::DESCRIPTION,
        self::LOCATION,
        self::LOCATION_TYPE,
        self::NAME,
        self::RESOURCES,
        self::STRUCTURED_DATA,
        self::STYLED_DESCRIPTION,
        self::SUMMARY,
    ];

    /**
     * Subject to parse into
     *
     * @var Vcalendar|CalendarComponent
     */
    protected Vcalendar|CalendarComponent $subject;

    /**
     * Rows to parse
     *
     * @var string[]
     */
    protected array $unparsed = [];

    /**
     * @param Vcalendar|CalendarComponent $subject
     * @return static
     */
    public static function factory( Vcalendar|CalendarComponent $subject ) : static
    {
        $instance = new static();
        $instance->setSubject( $subject );
        return $instance;
    }

    /**
     * Return array property value and attributes
     *
     * Attributes are prefixed by ';', value by ':', BUT they may exist in both attr (quoted?) and values
     * Known bug here: property parse with param ALTREP (etc?) with unquoted url with ..>user.passwd@<.. before hostname
     *
     * @param string      $line     property content
     * @param null|string $propName
     * @return array   [ value, [ *( propAttrKey => propAttrValue) ] ]
     * @since  2.30.3 - 2021-02-14
     */
    public static function splitContent( string $line, ?string $propName = null ) : array
    {
        static $CSS = '://';
        static $EQ  = '=';
        static $QQ  = '"';
        static $URIprops = [ self::SOURCE, self::URL, self::TZURL ];
        $clnPos = strpos( $line, self::$COLON );
        if( ( false === $clnPos )) {
            return [ $line, [] ]; // no params
        }
        if( 0 === $clnPos ) { // no params,  most frequent
            return [ substr( $line, 1 ), [] ];
        }
        if( ! empty( $propName ) && in_array( strtoupper( $propName ), $URIprops, true )) {
            StringFactory::checkFixUriValue( $line );
        }
        if( self::checkSingleParam( $line )) { // one param
            $param = StringFactory::between( Util::$SEMIC, Util::$COLON, $line );
            return [
                StringFactory::after( self::$COLON, $line ),
                [
                    StringFactory::before( $EQ, $param ) =>
                        trim( StringFactory::after( $EQ, $param ), $QQ )
                ]
            ];
        } // end if
        /* more than one param here (or a tricky one...) */
        $attr         = [];
        $attrix       = -1;
        $WithinQuotes = false;
        $len          = strlen( $line );
        $cix          = 0;
        while( $cix < $len ) {
            $str1 = $line[$cix];
            $cix1 = $cix + 1;
            if( ! $WithinQuotes &&
                ( self::$COLON === $str1 ) &&
                ( $CSS !== substr( $line, $cix, 3 )) && // '://'
                ! self::colonIsPrefixedByProtocol( $line, $cix ) &&
                ! self::hasPortNUmber( substr( $line, $cix1, 7 ))) {
                $line = substr( $line, $cix1 );
                break;
            }
            if( $QQ === $str1 ) { // '"'
                $WithinQuotes = ! $WithinQuotes;
            }
            if( self::$SEMIC === $str1 ) { // ';'
                ++$attrix;
                $attr[$attrix] = self::$SP0; // initiate
            }
            else {
                $attr[$attrix] .= $str1;
            }
            ++$cix;
        } // end while...
        /* make attributes in array format */
        $propAttr = [];
        foreach( $attr as $attribute ) {
            if( ! str_contains( $attribute, $EQ )) {
                continue;// skip empty? attributes
            }
            $attrSplit               = explode( $EQ, $attribute, 2 );
            $propAttr[$attrSplit[0]] = $attrSplit[1];
        }
        return [ $line, $propAttr ];
    }

    /**
     * Return bool true if leading chars in (unquoted) string is a port number (i.e. followed by '/')
     *
     * @param string $string
     * @return bool
     * @since  2.41.49 - 2022-05-02
     */
    public static function hasPortNUmber( string $string ) : bool
    {
        $len      = strlen( $string );
        $hasDigit = false;
        for( $x = 0; $x < $len; $x++ ) {
            $str1 = $string[$x];
            if( ctype_digit( $str1 )) {
                $hasDigit = true;
                continue;
            }
            if( $hasDigit && ( Util::$SLASH === $str1 )) {
                return true;
            }
            break;
        } // end for
        return false;
    }

    /**
     * Return true if single param only (and no colon/semicolon in param values)
     *
     * 2nd most frequent
     *
     * @param string $line
     * @return bool
     * @since  2.30.3 - 2021-02-14
     */
    public static function checkSingleParam( string $line ) : bool
    {
        if( ! str_starts_with( $line, Util::$SEMIC )) {
            return false;
        }
        return ( ( 1 === substr_count( $line, Util::$SEMIC )) &&
            ( 1 === substr_count( $line, Util::$COLON )));
    }

    /**
     * Return bool true if colon-pos is prefixed by protocol
     *
     * @see  https://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml overkill !!
     *
     * @param string $line
     * @param int    $cix
     * @return bool
     * @since  2.30.2 - 2021-02-04
     */
    public static function colonIsPrefixedByProtocol( string $line, int $cix ) : bool
    {
        static $MSTZ = [ 'utc-', 'utc+', 'gmt-', 'gmt+' ];
        $line = strtolower( $line );
        return ( ( in_array( substr( $line, $cix - 6, 4 ), $MSTZ )) || // ?? -6
            ( in_array( substr( $line, $cix - 3, 4 ), self::$PROTO3, true )) ||
            ( in_array( substr( $line, $cix - 4, 5 ), StringFactory::$PROTO4, true )) ||
            ( in_array( substr( $line, $cix - 5, 6 ), self::$PROTO5, true )) ||
            ( in_array( substr( $line, $cix - 6, 7 ), self::$PROTO6, true )) ||
            ( in_array( substr( $line, $cix - 7, 8 ), self::$PROTO7, true )));
    }

    /**
     * @param null|string|string[] $unParsedText strict rfc2445 formatted, single property string or array of strings
     * @return Vcalendar|CalendarComponent
     */
    abstract public function parse( null|string|array $unParsedText ) : Vcalendar|CalendarComponent;

    /**
     * @return CalendarComponent|Vcalendar|null
     */
    public function getSubject() : Vcalendar | CalendarComponent | null
    {
        return $this->subject;
    }

    /**
     * @param CalendarComponent|Vcalendar|null $subject
     * @return static
     */
    public function setSubject( Vcalendar | CalendarComponent | null $subject ) : static
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getUnparsed() : array
    {
        return $this->unparsed;
    }

    /**
     * @param string $row
     * @return static
     */
    public function addUnparsedRow ( string $row ) : static
    {
        $this->unparsed[] = $row;
        return $this;
    }

    /**
     * @param string[] $unparsed
     * @return static
     */
    public function setUnparsed( array $unparsed ) : static
    {
        $this->unparsed = $unparsed;
        return $this;
    }

    /**
     * Return concatenated calendar rows, one row for each property
     *
     * @param string[] $rows
     * @return string[]
     * @since  2.29.20 - 2020-01-31
     */
    public static function concatRows( array $rows ) : array
    {
        static $CHARs = [ ' ', "\t" ];
        $output = [];
        $cnt    = count( $rows );
        for( $i = 0; $i < $cnt; $i++ ) {
            $line = rtrim( $rows[$i], Util::$CRLF );
            $i1 = $i + 1;
            while(( $i < $cnt ) && isset( $rows[$i1] ) &&
                ! empty( $rows[$i1] ) &&
                in_array( $rows[$i1][0], $CHARs )) {
                ++$i;
                $line .= rtrim( substr( $rows[$i], 1 ), Util::$CRLF );
                $i1 = $i + 1;
            } // end while
            $output[] = $line;
        } // end for
        return $output;
    }

    /**
     * Return strings with removed ical line folding
     *
     * Remove any line-endings that may include spaces or tabs
     * and convert all line endings (iCal default '\r\n'),
     * takes care of '\r\n', '\r' and '\n' and mixed '\r\n'+'\r', '\r\n'+'\n'
     *
     * @param string $text
     * @return string[]
     * @throws Exception
     * @since  2.29.9 - 2019-03-30
     */
    protected static function convEolChar( string & $text ) : array
    {
        static $BASEDELIM  = null;
        static $BASEDELIMs = null;
        static $EMPTYROW   = null;
        static $FMT        = '%1$s%2$75s%1$s';
        static $CRLFs      = [ "\r\n", "\n\r", "\n", "\r" ];
        static $CRLFexts   = [ "\r\n ", "\r\n\t" ];
        /* fix dummy line separator etc */
        if( empty( $BASEDELIM )) {
            $BASEDELIM  = bin2hex( StringFactory::getRandChars( 16 ));
            $BASEDELIMs = $BASEDELIM . $BASEDELIM;
            $EMPTYROW   = sprintf( $FMT, $BASEDELIM, Util::$SP0 );
        }
        /* fix eol chars */
        $text = str_replace( $CRLFs, $BASEDELIM, $text );
        /* fix empty lines */
        $text = str_replace( $BASEDELIMs, $EMPTYROW, $text );
        /* fix line folding */
        $text = str_replace( $BASEDELIM, Util::$CRLF, $text );
        $text = str_replace( $CRLFexts, Util::$SP0, $text );
        /* split in component/property lines */
        return explode( Util::$CRLF, $text );
    }
}
