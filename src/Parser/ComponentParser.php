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
namespace Kigkonsult\Icalcreator\Parser;

use Exception;
use Kigkonsult\Icalcreator\CalendarComponent;
use Kigkonsult\Icalcreator\Util\StringFactory;
use RuntimeException;

use function count;
use function ctype_alpha;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function str_starts_with;
use function strcasecmp;
use function stripos;
use function strlen;
use function strtoupper;
use function substr;
use function trim;

/**
 * @since 2.41.88 2024-01-17
 */
final class ComponentParser extends ParserBase
{
    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function parse( null|array|string $unParsedText = null ) : CalendarComponent
    {
        try {
            $rows = $this->parse1prepInput( $unParsedText );
            if( ! empty( $rows ) ) {
                $this->parse2intoComps( $rows );
            }
        }
        catch( Exception $e ) {
            throw new RuntimeException( $e->getMessage(), $e->getCode(), $e );
        }
        return $this->subject;
    }

    /**
     * Return rows to parse
     *
     * @param null|string|string[] $unParsedText strict rfc2445 formatted, single property string or array of strings
     * @return string[]
     * @throws Exception
     * @since  2.29.3 - 2019-06-20
     */
    private function parse1prepInput( null|string|array $unParsedText = null ) : array
    {
        static $NLCHARS = '\n';
        static $BEGIN   = 'BEGIN:';
        switch( true ) {
            case ( ! empty( $unParsedText )) :
                $arrParse = false;
                if( is_array( $unParsedText )) {
                    $unParsedText = implode(
                        $NLCHARS . self::$CRLF,
                        $unParsedText
                    );
                    $arrParse     = true;
                }
                $rows = self::convEolChar( $unParsedText );
                if( $arrParse ) {
                    foreach( $rows as $lix => $row ) {
                        $rows[$lix] = StringFactory::trimTrailNL( $row );
                    }
                }
                break;
            case empty( $this->unparsed ) :
                $rows = [];
                break;
            default :
                $rows = $this->unparsed;
                break;
        } // end switch
        /* skip leading (empty/invalid) lines */
        foreach( $rows as $lix => $row ) {
            if( false !== ( $pos = stripos( $row, $BEGIN ))) {
                $rows[$lix] = substr( $row, $pos );
                break;
            }
            $tst = trim( $row );
            if(( $NLCHARS === $tst ) || empty( $tst )) {
                unset( $rows[$lix] );
            }
        } // end foreach
        return $rows;
    }

    /**
     * Parse into this and sub-components data
     *
     * @param string[] $rows
     * @return void
     * @throws Exception
     * @since  2.41.11 - 2022-01-27
     */
    private function parse2intoComps( array $rows ) : void
    {
        static $END_ALARM         = 'END:VALARM';
        static $END_PARTICIPANT   = 'END:PARTICIPANT';
        static $ENDSARR           = [ 'END:AV', 'END:DA', 'END:ST', 'END:VL', 'END:VR' ];
        static $END               = 'END:';
        static $BEGIN             = 'BEGIN:';
        static $BEGIN_AVAILABLE   = 'BEGIN:AVAILABLE';
        static $BEGIN_DAYLIGHT    = 'BEGIN:DAYLIGHT';
        static $BEGIN_PARTICIPANT = 'BEGIN:PARTICIPANT';
        static $BEGIN_STANDARD    = 'BEGIN:STANDARD';
        static $BEGIN_VALARM      = 'BEGIN:VALARM';
        static $BEGIN_VLOCATION   = 'BEGIN:VLOCATION';
        static $BEGIN_VRESOURCE   = 'BEGIN:VRESOURCE';
        $parser         = $this;
        $this->unparsed = [];
        $compType = strtoupper( $this->subject->getCompType());
        $beginTag = $BEGIN . $compType;
        $endTag   = $END . $compType;
        $endFound = false;
        $isParticipantCurrent = $isValarmCurrent = false;
        foreach( $rows as $row ) {
            switch( true ) {
                case str_starts_with( $row, $beginTag ) :  // begin:<thisComponent>
                    break;
                case str_starts_with( $row, $endTag ) : // end:<thisComponent>
                    $this->parse3thisProperties();
                    $endFound = true;
                    break 2;  // skip opt trailing empty lines..
                case $isValarmCurrent && str_starts_with( $row, $END_ALARM ) :
                    $parser->parse();
                    $isValarmCurrent = false;
                    break;
                case $isParticipantCurrent && str_starts_with( $row, $END_PARTICIPANT ) :
                    $parser->parse();
                    $isParticipantCurrent = false;
                    break;
                case ( $isValarmCurrent || $isParticipantCurrent ) :
                    $parser->addUnparsedRow( $row );
                    break;
                case ( in_array( strtoupper( substr( $row, 0, 6 )), $ENDSARR, true )) :
                    $parser->parse();
                    break;
                case str_starts_with( $row, $BEGIN_VALARM ) :
                    $parser = self::factory( $this->subject->newValarm());
                    $isValarmCurrent = true;
                    break;
                case str_starts_with( $row, $BEGIN_PARTICIPANT ) :
                    $parser    = self::factory( $this->subject->newParticipant());
                    $isParticipantCurrent = true;
                    break;
                case str_starts_with( $row, $BEGIN_AVAILABLE ) :
                    $parser      = self::factory( $this->subject->newAvailable());
                    break;
                case str_starts_with( $row, $BEGIN_DAYLIGHT ) :
                    $parser      = self::factory( $this->subject->newDaylight());
                    break;
                case str_starts_with( $row, $BEGIN_STANDARD ) :
                    $parser      = self::factory( $this->subject->newStandard());
                    break;
                case str_starts_with( $row, $BEGIN_VLOCATION ) :
                    $parser      = self::factory( $this->subject->newVlocation());
                    break;
                case str_starts_with( $row, $BEGIN_VRESOURCE ) :
                    $parser      = self::factory( $this->subject->newVresource());
                    break;
                default :
                    $parser->addUnparsedRow( $row );
                    break;
            } // end switch( true )
        } // end foreach( $rows as $lix => $row )
        if( ! $endFound ) {
            $this->parse3thisProperties();
        }
    }

    /**
     * Parse this properties
     *
     * @return void
     * @since 2.41.88 2024-01-17
     * @todo report invalid properties ??
     */
    private function parse3thisProperties() : void
    {
        static $STRUNREPPROPS = [
            self::CATEGORIES, self::COMMENT, self::CONTACT, self::DESCRIPTION,
            self::LOCATION, self::PROXIMITY, self::RESOURCES, self::STRUCTURED_DATA,
            self::STYLED_DESCRIPTION, self::SUMMARY,
            self::ACTION, self::BUSYTYPE, self::KLASS, self::RELATED_TO, self::STATUS,
            self::TRANSP, self::TZID, self::TZID_ALIAS_OF, self::TZNAME, self::UID,
        ];
        static $STRUNREPPROP  = 'STRUNREPPROP';
        if( empty( $this->unparsed )) {
            return;
        }
        /* concatenate property values spread over several lines */
        $this->unparsed = self::concatRows( $this->unparsed );
        /* parse each property 'line' */
        foreach( $this->unparsed as $row ) {
            /* get propname  +  split property name  and  opt.params and value */
            [ $propName, $row ] = StringFactory::getPropName( $row );
            if( StringFactory::isXprefixed( $propName )) {
                [ $value, $propAttr ] = self::splitContent( $row );
                $this->subject->setXprop( $propName, StringFactory::strunrep( $value ), $propAttr );
                continue;
            }
            if( ! property_exists( $this->subject, StringFactory::getInternalPropName( $propName ))) {
                continue; // skip property names not in comp, todo report invalid properties ??
            }
            /* separate attributes from value */
            [ $value, $propAttr ] = self::splitContent( $row, $propName );
            if( ! in_array( $propName, self::$TEXTPROPS, true )) {
                $value = StringFactory::trimTrailNL( $value );
            }
            /* call set<Propname>(.. . */
            $method = StringFactory::getSetMethodName( $propName );
            if( in_array( $propName, $STRUNREPPROPS, true )) {
                $propName = $STRUNREPPROP;
            }
            match( $propName ) {
                self::ATTENDEE =>
                    $this->subject->{$method}( $value, self::processAttendeeParams( $propAttr )),
                $STRUNREPPROP =>
                    $this->subject->{$method}( StringFactory::strunrep( $value ), $propAttr ),
                self::REQUEST_STATUS =>
                    $this->parseRequestStatus( $method, $value, $propAttr ),
                self::FREEBUSY =>
                    $this->parseFreebusy( $method, $value, $propAttr ),
                self::GEO =>
                    $this->parseGeo( $method, $value, $propAttr ),
                self::EXDATE =>
                    $this->subject->{$method}( self::parseExdate( $value ), $propAttr ),
                self::RDATE =>
                    $this->parseRdate( $method, $value, $propAttr ),
                self::EXRULE, self::RRULE =>
                    $this->subject->{$method}( self::parseRexrule( $value ), $propAttr ),
                default =>
                    $this->subject->{$method}( $value, $propAttr )
            }; // end  match( $propName.. .
        } // end foreach( $this->unparsed as $lix => $row )
        $this->unparsed = [];
    }

    /**
     * Split multiple Attendees MEMBER/DELEGATED-TO/DELEGATED-FROM into array, if found
     *
     * @param string[]|string[][] $propAttr
     * @return string[]|string[][]
     * @since  2.27.11 - 2019-01-04
     */
    private static function processAttendeeParams( array $propAttr ) : array
    {
        static $ParamArrayKeys = [ self::MEMBER, self::DELEGATED_TO, self::DELEGATED_FROM ];
        foreach( $propAttr as $pix => $attr ) {
            if( ! in_array( strtoupper( $pix ), $ParamArrayKeys, true )) {
                continue;
            }
            $attr2 = explode( self::$COMMA, (string) $attr );
            if( 1 < count( $attr2 )) {
                $propAttr[$pix] = $attr2;
            }
        }
        return $propAttr;
    }

    /**
     * Return Request-Status value array
     *
     * @param string $method
     * @param string $value
     * @param string[] $propAttr
     * @return void
     * @since 2.41.88 2024-01-17
     */
    private function parseRequestStatus( string $method, string $value, array $propAttr ) : void
    {
        $input = $this->subject::extractRequeststatus( $value );
        if( isset( $input[1] )) {
            $input[1] = StringFactory::strunrep( $input[1] );
        }
        else {
            $propAttr = [];
        }
        if( isset( $input[2] )) {
            $input[2] = StringFactory::strunrep( $input[2] );
        }
        $this->subject->{$method}( $input[0], $input[1], $input[2], $propAttr );
    }

    /**
     * Return type, value and parameters from parsed (Freebusy) row and propAttr
     *
     * @param string  $method
     * @param string  $row
     * @param string[] $propAttr
     * @return void
     * @since 2.41.88 2024-01-17
     */
    private function parseFreebusy( string $method, string $row, array $propAttr ) : void
    {
        static $SS = '/';
        $fbtype = $values = null;
        if( ! empty( $propAttr )) {
            foreach( $propAttr as $k => $v ) {
                if( 0 === strcasecmp( self::FBTYPE, $k )) {
                    $fbtype = $v;
                    unset( $propAttr[$k] );
                    break;
                }
            } // end foreach
        } // end if
        if( ! empty( $row )) {
            $values = explode( self::$COMMA, $row );
            foreach( $values as $vix => $value ) {
                $value2 = explode( $SS, $value ); // '/'
                if( 1 < count( $value2 )) {
                    $values[$vix] = $value2;
                }
            } // end foreach
        } // end if
        $this->subject->{$method}( $fbtype, $values, $propAttr );
    }

    /**
     * Return Geo value array
     *
     * @param string $method
     * @param string $value
     * @param string[] $propAttr
     * @return void
     * @since 2.41.88 2024-01-17
     */
    private function parseGeo( string $method, string $value, array $propAttr ) : void
    {
        $input = $this->subject::extractGeoLatLong( $value );
        if( ! isset( $input[0] )) {
            $propAttr = [];
        }
        $this->subject->{$method}( $input[0], $input[1], $propAttr );
    }

    /**
     * Return Exdate value
     *
     * @param string $value
     * @return null|string[]
     * @since  2.41.68 - 2022-19-24
     */
    private static function parseExdate( string $value ) : ?array
    {
        return  empty( $value ) ? null : explode( self::$COMMA, $value );
    }

    /**
     * Return value and parameters from parsed row and propAttr
     *
     * @param string  $method
     * @param string  $row
     * @param string[] $propAttr
     * @return void
     * @since  2.41.88 - 2024-01-17
     */
    private function parseRdate( string $method, string $row, array $propAttr ) : void
    {
        static $SS = '/';
        if( empty( $row )) {
            $this->subject->{$method}( null, $propAttr );
            return;
        }
        $values = explode( self::$COMMA, $row );
        foreach( $values as $vix => $value ) {
            if( ! str_contains( $value, $SS ) ) {
                continue;
            }
            $value2 = explode( $SS, $value );
            if( 1 < count( $value2 )) {
                $values[$vix] = $value2;
            }
        } // end foreach
        $this->subject->{$method}( $values, $propAttr );
    }

    /**
     * Return (array) parsed rexrule string
     *
     * @param string $row
     * @return string[]|string[][]
     * @since 2.27.3 - 2018-12-28
     */
    private static function parseRexrule( string $row ) : array
    {
        static $EQ = '=';
        $recur     = [];
        $values    = explode( self::$SEMIC, $row );
        foreach( $values as $value2 ) {
            if( empty( $value2 )) {
                continue;
            } // ;-char in end position ???
            $value3    = explode( $EQ, $value2, 2 );
            $ruleLabel = strtoupper( $value3[0] );
            $value4    = explode( self::$COMMA, $value3[1] );
            if( self::BYDAY === $ruleLabel ) {
                if( 1 < count( $value4 )) {
                    foreach( $value4 as $v5ix => $value5 ) {
                        $value4[$v5ix] = self::updateDayNoAndDayName( trim( $value5 ));
                    }
                }
                else {
                    $value4 = self::updateDayNoAndDayName( trim( $value3[1] ));
                }
                $recur[$ruleLabel] = $value4;
            } // end if
            else {
                if( 1 < count( $value4 )) {
                    $value3[1] = $value4;
                }
                $recur[$ruleLabel] = $value3[1];
            } // end else
        } // end - foreach( $values.. .
        return $recur;
    }

    /**
     * Return array, day rel pos number (opt) and day name abbr
     *
     * @param string $dayValueBase
     * @return string[]
     * @since  2.27.16 - 2019-03-03
     */
    private static function updateDayNoAndDayName( string $dayValueBase ) : array
    {
        $output = [];
        $dayno  = $dayName = false;
        if(( ctype_alpha( substr( $dayValueBase, -1 ))) &&
            ( ctype_alpha( $dayValueBase[strlen( $dayValueBase ) - 2] ))) {
            $dayName = substr( $dayValueBase, -2, 2 );
            if( 2 < strlen( $dayValueBase )) {
                $dayno = (int) substr( $dayValueBase, 0, ( strlen( $dayValueBase ) - 2 ));
            }
        }
        if( false !== $dayno ) {
            $output[] = $dayno;
        }
        if( false !== $dayName ) {
            $output[self::DAY] = $dayName;
        }
        return $output;
    }
}
