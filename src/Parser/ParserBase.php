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
namespace Kigkonsult\Icalcreator\Parser;

use Exception;
use Kigkonsult\Icalcreator\CalendarComponent;
use Kigkonsult\Icalcreator\IcalInterface;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Vcalendar;

use function bin2hex;
use function count;
use function ctype_digit;
use function explode;
use function in_array;
use function rtrim;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function strstr;
use function stristr;
use function strtolower;
use function strtoupper;
use function substr;
use function substr_count;

/**
 * @since 2.41.70 2022-10-21
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

    protected static string $EQ  = '=';
    protected static string $QQ  = '"';

    /**
     * Return array property value and attributes
     *
     * Attributes are prefixed by ';', value by ':', BUT they may exist in both attr (quoted?) and values
     * Known bug here: property parse with param ALTREP (etc?) with unquoted url with ..>user.passwd@<.. before hostname
     *
     * @param string      $line     property content
     * @param null|string $propName
     * @return array   [ value, [ *( propAttrKey => propAttrValue) ] ]
     * @since 2.41.70 2022-10-21
     */
    public static function splitContent( string $line, ?string $propName = null ) : array
    {
        $clnPos = strpos( $line, self::$COLON );
        if( ( false === $clnPos )) {
            return [ $line, [] ]; // no params and no colon (empty property)
        }
        if( 0 === $clnPos ) { // no params,  most frequent
            return [ substr( $line, 1 ), [] ];
        }
        if( self::checkSingleParam( $line )) { // one (simpler) param
            return self::processSingleParam( $line );
        }
        if( self::mayHaveUriParam( $propName )) {
            StringFactory::checkFixUrlDecode( $line );
            $line = self::checkFixUriMessage( $line );
        }
        /* more than one param here (or a tricky one...) */
        $attr = [];
        $line = self::extractTextParams( $line, $attr ); // simpler (text) ones
        $line = self::extractMultiParams( $line, $attr );
        return [ $line, self::processAttributes( $attr) ];
    }

    /**
     * Return bool true if propName may a URI VALUE parameter
     *
     * @param string|null $propName
     * @return bool
     */
    protected static function mayHaveUriParam( ? string $propName ) : bool
    {
        static $URIprops = [ self::SOURCE, self::URL, self::TZURL ];
        return ( ! empty( $propName ) &&
            in_array( strtoupper( $propName ), $URIprops, true ));
    }

    /**
     * Remove opt 'VALUE=URI:message:'
     *
     * orginating from any Apple device
     *
     * @param string $line
     * @return string
     * @since 2.41.68 2022-10-22
     */
    protected static function checkFixUriMessage( string $line ) : string
    {
        static $Um        = 'URI:message';
        static $SQVEQUm   = ';VALUE=URI:message';
        static $SQVEQUmq  = ';VALUE=\'URI:message\'';
        static $SQVEQUmqq = ';VALUE="URI:message"';
        switch( true ) {
            case ( false === stripos( $line, $Um )) :
                return $line;
            case ( false !== stripos( $line, $SQVEQUm )) :  // no quote
                return str_ireplace( $SQVEQUm, Util::$SP0, $line );
            case ( false !== stripos( $line, $SQVEQUmq )) :  // single quote
                return str_ireplace( $SQVEQUmq, Util::$SP0, $line );
            case ( false !== stripos( $line, $SQVEQUmqq )) : // double quote
                return str_ireplace( $SQVEQUmqq, Util::$SP0, $line );
        } // end switch
        return $line;
    }

    /**
     * Extract and remove opt (simpler) TEXT parameters from line and upd attr array
     *
     * @param string $line
     * @param string[] $attr
     * @return string
     * @since 2.41.70 2022-10-20
     */
    protected static function extractTextParams( string $line, array & $attr ) : string
    {
        static $searchKeyArr = [
            'CUTYPE=',
            'ENCODING=',
            'FMTTYPE=',
            'FBTYPE=',
            'LANGUAGE=',
            'ORDER=',
            'PARTSTAT=',
            'RELATED=',
            'RELTYPE=',
            'ROLE=',
            'VALUE=',
            'TZID='
        ];
        foreach( $searchKeyArr as $needle ) {
            $search = self::$SEMIC . $needle;
            if( ! str_contains( $line, $search )) {
                continue;
            }
            $line1  = stristr( $line, $search, true );
            $temp   = StringFactory::after( $search, $line );
            [ $attrValue, $rightPart ] = StringFactory::splitByFirstSQorColon( $temp );
            $attr[] = $needle . $attrValue;
            $line   = $line1 . $rightPart;
        } // end foreach
        return $line;
    }

    /**
     * Extract and remove multi parameters from line and upd attr array
     *
     * @param string $line
     * @param string[] $attr
     * @return string
     * @since 2.41.70 2022-10-21
     */
    protected static function extractMultiParams( string $line, array & $attr ) : string
    {
        static $CSS   = '://';
        if( self::$COLON === $line[0] ) { // no params found
            return substr( $line, 1 );
        }
        $attrix       = count( $attr ) - 1;
        $withinQuotes = false;
        $len          = strlen( $line );
        $cix          = 0;
        while( $cix < $len ) {
            $str1 = $line[$cix];
            $cix1 = $cix + 1;
            if( ! $withinQuotes &&
                ( self::$COLON === $str1 ) &&
                ( $CSS !== substr( $line, $cix, 3 )) && // '://'
                ! self::colonIsPrefixedByProtocol( $line, $cix ) &&
                ! self::hasPortNUmber( substr( $line, $cix1, 7 ))) {
                $line = substr( $line, $cix1 );
                break;
            }
            if( self::$QQ === $str1 ) { // '"'
                $withinQuotes = ! $withinQuotes;
            }
            if( self::$SEMIC === $str1 ) { // ';'
                ++$attrix;
                $attr[$attrix] = self::$SP0; // initiate new param
            }
            else {
                $attr[$attrix] .= $str1;
            }
            ++$cix;
        } // end while...
        return $line;
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
    protected static function checkSingleParam( string $line ) : bool
    {
        if( Util::$SEMIC !== $line[0] ) {
            return false;
        }
        return (( 1 === substr_count( $line, Util::$SEMIC )) &&
            ( 1 === substr_count( $line, Util::$COLON )));
    }

    /**
     * Return array, property content and single param array
     *
     * @param string $line
     * @return array
     */
    protected static function processSingleParam( string $line ) : array
    {
        $param = StringFactory::between( Util::$SEMIC, Util::$COLON, $line );
        return [
            StringFactory::after( self::$COLON, $line ),
            [
                strstr( $param, self::$EQ, true ) =>
                    trim( StringFactory::after( self::$EQ, $param ), self::$QQ )
            ]
        ];
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
     * Return attributes in array format, i.e. split key and value
     *
     * @param string[] $attr
     * @return string[]
     */
    protected static function processAttributes( array $attr ) : array
    {
        $propAttr = [];
        foreach( $attr as $attribute ) {
            if( ! str_contains( $attribute, self::$EQ ) ) {
                continue;// skip empty? attributes
            }
            $attrSplit = explode( self::$EQ, $attribute, 2 );
            $propAttr[$attrSplit[0]] = $attrSplit[1];
        }
        return $propAttr;
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
