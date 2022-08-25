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
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Vcalendar;
use UnexpectedValueException;

use function array_unshift;
use function count;
use function implode;
use function in_array;
use function is_array;
use function sprintf;
use function str_starts_with;
use function stripos;
use function strtoupper;
use function substr;
use function trim;

/**
 * @since 2.41.54 - 2022-08-09
 */
final class VcalendarParser extends ParserBase
{
    /**
     * @var string
     */
    private static string $BEGIN_VCALENDAR = 'BEGIN:VCALENDAR';

    /**
     * @var string
     */
    private static string $END_VCALENDAR   = 'END:VCALENDAR';

    /**
     * @var string
     */
    private static string $NLCHARS    = '\n';

    /**
     * Parse iCal text/file into Vcalendar, components, properties and parameters
     *
     * @inheritDoc
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @since  2.41.54  2022-08-08
     */
    public function parse( null|string|array $unParsedText ) : Vcalendar
    {
        $rows = self::conformParseInput( $unParsedText );
        $this->parse2intoComps( $rows );
        $this->parse3thisProperties();
        return $this->subject;
    }

    /**
     * Return rows to parse from string or array
     *
     * @param string|string[] $unParsedText strict rfc2445 formatted, single property string or array of strings
     * @return string[]
     * @throws UnexpectedValueException
     * @throws Exception
     * @since  2.41.49 - 2022-05-01
     */
    public static function conformParseInput( string | array $unParsedText ) : array
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
                $rows[$lix] = StringFactory::trimTrailNL( $row );
            }
        }
        if( empty( $rows )) { /* err 9 */
            throw new UnexpectedValueException(
                sprintf( $ERR10, 9, Util::$SP0 )
            );
        }
        /* skip leading (empty/invalid) lines (and remove leading BOM chars etc) */
        $rows  = self::trimLeadingRows( $rows );
        /* skip trailing empty lines and ensure an end row */
        $rows  = self::trimTrailingRows( $rows );
        $cnt   = count( $rows );
        if( 2 === $cnt ) { /* err 10 */
            throw new UnexpectedValueException(
                sprintf( $ERR10, $cnt, PHP_EOL . implode( PHP_EOL, $rows ))
            );
        }
        return $rows;
    }

    /**
     * Return array to parse with leading (empty/invalid) lines removed (incl leading BOM chars etc)
     *
     * Ensure BEGIN:CALENDAR on the first row
     *
     * @param string[] $rows
     * @return string[]
     * @since  2.41.49 - 2022-05-01
     */
    private static function trimLeadingRows( array $rows ) : array
    {
        $beginFound = false;
        foreach( $rows as $lix => $row ) {
            if( false !== stripos( $row, self::$BEGIN_VCALENDAR )) {
                $rows[$lix] = self::$BEGIN_VCALENDAR;
                $beginFound = true;
                continue;
            }
            if( ! empty( trim( $row ))) {
                break;
            }
            unset( $rows[$lix] );
        } // end foreach
        if( ! $beginFound ) {
            array_unshift( $rows, self::$BEGIN_VCALENDAR );
        }
        return $rows;
    }

    /**
     * Return array to parse with trailing empty lines removed and ensured an end row
     *
     * Ensure END:CALENDAR on the last row
     *
     * @param string[] $rows
     * @return string[]
     * @since  2.41.49 - 2022-05-01
     */
    private static function trimTrailingRows( array $rows ) : array
    {
        end( $rows );
        $lix = key( $rows );
        while( 0 <= $lix ) {
            $tst = trim( $rows[$lix] );
            if(( self::$NLCHARS === $tst ) || empty( $tst )) {
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
     * Parse into calendar and comps data
     *
     * @param string[] $rows
     * @return void
     * @throws Exception
     * @throws UnexpectedValueException
     * @since  2.41.9 - 2022-01-27
     */
    private function parse2intoComps( array $rows ) : void
    {
        static $ENDSARR             = [ 'END:VAV', 'END:VEV', 'END:VFR', 'END:VJO', 'END:VTI', 'END:VTO' ];
        static $BEGIN_VAVAILABILITY = 'BEGIN:VAVAILABILITY';
        static $BEGIN_VEVENT        = 'BEGIN:VEVENT';
        static $BEGIN_VFREEBUSY     = 'BEGIN:VFREEBUSY';
        static $BEGIN_VJOURNAL      = 'BEGIN:VJOURNAL';
        static $BEGIN_VTODO         = 'BEGIN:VTODO';
        static $BEGIN_VTIMEZONE     = 'BEGIN:VTIMEZONE';
        $parser = $this;
        /* identify components and update unparsed data for components */
        foreach( $rows as $row ) {
            switch( true ) {
                case str_starts_with( $row, self::$BEGIN_VCALENDAR ) :
                    break;
                case str_starts_with( $row, self::$END_VCALENDAR ) :
                    $this->parse3thisProperties();
                    break 2;
                case ( in_array( strtoupper( substr( $row, 0, 7 )), $ENDSARR, true )) :
                    $parser->parse(); // i.e. NOT Vcalendar
                    break;
                case str_starts_with( $row, $BEGIN_VAVAILABILITY ) :
                    $parser = ComponentParser::factory( $this->subject->newVavailability());
                    break;
                case str_starts_with( $row, $BEGIN_VEVENT ) :
                    $parser = ComponentParser::factory( $this->subject->newVevent());
                    break;
                case str_starts_with( $row, $BEGIN_VFREEBUSY ) :
                    $parser = ComponentParser::factory( $this->subject->newVfreebusy());
                    break;
                case str_starts_with( $row, $BEGIN_VJOURNAL ) :
                    $parser = ComponentParser::factory( $this->subject->newVjournal());
                    break;
                case str_starts_with( $row, $BEGIN_VTODO ) :
                    $parser = ComponentParser::factory( $this->subject->newVtodo());
                    break;
                case str_starts_with( $row, $BEGIN_VTIMEZONE ) :
                    $parser = ComponentParser::factory( $this->subject->newVtimezone());
                    break;
                default : /* update component with unparsed data */
                    $parser->addUnparsedRow( $row );
                    break;
            } // switch( true )
        } // end foreach( $rows as $lix => $row )
    }

    /**
     * Parse calendar data
     *
     * @return void
     * @throws UnexpectedValueException
     * @since  2.29.22 - 2020-08-26
     */
    private function parse3thisProperties() : void
    {
        static $BEGIN     = 'BEGIN:';
        static $ERR       = 'Unknown ical component (row %d) %s';
        static $PVPROPS   = [ self::PRODID, self::VERSION ];
        static $CALPROPS  = [
            self::CALSCALE,
            self::METHOD,
            self::PRODID,
            self::VERSION,
        ];
        static $RFC7986PROPS = [
            self::COLOR,
            self::CATEGORIES,
            self::DESCRIPTION,
            self::IMAGE,
            self::NAME,
            self::LAST_MODIFIED,
            self::REFRESH_INTERVAL,
            self::SOURCE,
            self::UID,
            self::URL,
        ];
        if( ! isset( $this->unparsed ) ||
            ! is_array( $this->unparsed ) ||
            ( 1 > count( $this->unparsed ))) {
            return;
        }
        /* concatenate property values spread over several rows */
        static $TRIMCHARS = "\x00..\x1F";
        $rows = self::concatRows( $this->unparsed );
        foreach( $rows as $lix => $row ) {
            if( str_starts_with( $row, $BEGIN )) { // ??
                throw new UnexpectedValueException(
                    sprintf( $ERR, $lix, PHP_EOL . implode( PHP_EOL, $rows ))
                );
            }
            /* split property name  and  opt.params and value */
            [ $propName, $row ] = StringFactory::getPropName( $row );
            switch( true ) {
                case ( StringFactory::isXprefixed( $propName ) ||
                    in_array( $propName, $RFC7986PROPS, true )) :
                    break;
                case in_array( $propName, $PVPROPS, true ) :
                    continue 2;  // ignore version/prodid properties
                case ( ! in_array( $propName, $CALPROPS, true )) :
                    continue 2;  // skip non standard property names
            } // end switch
            /* separate attributes from value */
            [ $value, $propAttr ] = self::splitContent( $row );
            /* update Property */
            if( StringFactory::isXprefixed( $propName )) {
                $this->subject->setXprop( $propName, StringFactory::strunrep( $value ), $propAttr );
                continue;
            }
            if( ! in_array( $propName, self::$TEXTPROPS, true )) {
                $value = StringFactory::trimTrailNL( $value );
            }
            $method = StringFactory::getSetMethodName( $propName );
            switch( $propName ) {
                case self::LAST_MODIFIED :    // fall through
                case self::REFRESH_INTERVAL : // fall through
                case self::URL :
                    $this->subject->{$method}( $value, $propAttr );
                    break;
                default :
                    $value = StringFactory::strunrep( rtrim( $value, $TRIMCHARS ));
                    $this->subject->{$method}( $value, $propAttr );
            } // end switch
        } // end foreach
        $this->unparsed = [];
    }
}
