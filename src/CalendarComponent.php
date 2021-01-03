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

namespace Kigkonsult\Icalcreator;

use Exception;
use Kigkonsult\Icalcreator\Util\CalAddressFactory;
use Kigkonsult\Icalcreator\Util\RecurFactory;
use Kigkonsult\Icalcreator\Util\RexdateFactory;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use UnexpectedValueException;

use function array_keys;
use function count;
use function ctype_digit;
use function end;
use function explode;
use function get_class;
use function implode;
use function is_array;
use function is_null;
use function ksort;
use function method_exists;
use function property_exists;
use function sprintf;
use function strcasecmp;
use function stripos;
use function strpos;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;
use function ucfirst;

/**
 *  Parent class for calendar components
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.29.17 - 2020-01-25
 */
abstract class CalendarComponent extends IcalBase
{
    /**
     * @var array  compoment sort params
     */
    public $srtk = null;

    /**
     * @var string component number
     */
    public $cno = 0;

    /**
     * @var string  misc. values
     */
    protected static $FMTBEGIN      = "BEGIN:%s\r\n";
    protected static $FMTEND        = "END:%s\r\n";

    /**
     * @var string
     */
    protected static $compSgn = 'xx';

    /**
     * Constructor for calendar component
     *
     * @param array $config
     * @since  2.27.14 - 2019-07-03
     */
    public function __construct( $config = [] )
    {
        static $objectNo = 0;
        $class           = get_class( $this );
        $this->compType  = ucfirst(
            strtolower( StringFactory::afterLast( StringFactory::$BS2, $class  ))
        );
        $this->cno       = $class::$compSgn . ++$objectNo;
        $this->setConfig( $config );
    }

    /**
     * Returns calendar property unique values
     *
     * For ATTENDEE, CATEGORIES, CONTACT, RELATED_TO or RESOURCES (keys)
     * and for each, number of occurrence (values)
     *
     * @param string $propName
     * @param array  $output incremented result array
     * @since  2.29.17 - 2020-01-25
     */
    public function getProperties( $propName, & $output )
    {
        if( empty( $output )) {
            $output = [];
        }
        if( ! Util::isPropInList( $propName, self::$MPROPS1 )) {
            return;
        }
        $method = parent::getGetMethodName( $propName );
        if( ! method_exists( $this, $method )) {
            return;
        }
        while( false !== ( $content = $this->{$method}())) {
            if( empty( $content )) {
                continue;
            }
            if( is_array( $content )) {
                foreach( $content as $part ) {
                    if( false !== strpos( $part, Util::$COMMA )) {
                        $part = explode( Util::$COMMA, $part );
                        foreach( $part as $contentPart ) {
                            $contentPart = trim( $contentPart );
                            if( ! empty( $contentPart )) {
                                if( ! isset( $output[$contentPart] )) {
                                    $output[$contentPart] = 1;
                                }
                                else {
                                    $output[$contentPart] += 1;
                                }
                            }
                        } // end foreach
                    }
                    else {
                        $part = trim( $part );
                        if( ! isset( $output[$part] )) {
                            $output[$part] = 1;
                        }
                        else {
                            $output[$part] += 1;
                        }
                    }
                } // end foreach
            } // end if( is_array( $content ))
            elseif( false !== strpos( $content, Util::$COMMA )) {
                $content = explode( Util::$COMMA, $content );
                foreach( $content as $contentPart ) {
                    $contentPart = trim( $contentPart );
                    if( ! empty( $contentPart )) {
                        if( ! isset( $output[$contentPart] )) {
                            $output[$contentPart] = 1;
                        }
                        else {
                            $output[$contentPart] += 1;
                        }
                    }
                } // end foreach
            } // end elseif( false !== strpos( $content, Util::$COMMA ))
            else {
                $content = trim( $content );
                if( ! empty( $content )) {
                    if( ! isset( $output[$content] )) {
                        $output[$content] = 1;
                    }
                    else {
                        $output[$content] += 1;
                    }
                }
            }
        } // end while
        ksort( $output );
    }

    /*
     * @var string
     */
    protected static $NLCHARS = '\n';
    protected static $BEGIN   = 'BEGIN:';

    /**
     * Parse data into component properties
     *
     * @param string|array $unParsedText strict rfc2445 formatted, single property string or array of strings
     * @return static
     * @throws Exception
     * @throws UnexpectedValueException;
     * @since  2.29.3 - 2019-06-20
     * @// todo report invalid properties, Exception.. ??
     */
    public function parse( $unParsedText = null )
    {
        $rows = $this->parse1prepInput( $unParsedText );
        $this->parse2intoComps( $rows );
        $this->parse3thisProperties();
        $this->parse4subComps();
        return $this;
    }

    /**
     * Return rows to parse
     *
     * @param string|array $unParsedText strict rfc2445 formatted, single property string or array of strings
     * @return array
     * @since  2.29.3 - 2019-06-20
     */
    private function parse1prepInput( $unParsedText = null )
    {
        switch( true ) {
            case ( ! empty( $unParsedText )) :
                $arrParse = false;
                if( is_array( $unParsedText ) ) {
                    $unParsedText = implode(
                        self::$NLCHARS . Util::$CRLF,
                        $unParsedText
                    );
                    $arrParse     = true;
                }
                $rows = StringFactory::convEolChar( $unParsedText );
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
            if( false !== ( $pos = stripos( $row, self::$BEGIN ))) {
                $rows[$lix] = substr( $row, $pos );
                break;
            }
            $tst = trim( $row );
            if(( self::$NLCHARS == $tst ) || empty( $tst )) {
                unset( $rows[$lix] );
            }
        } // end foreach
        return $rows;
    }

    /**
     * Parse into this and sub-components data
     *
     * @param array $rows
     * @since  2.29.3 - 2019-08-26
     */
    private function parse2intoComps( array $rows )
    {
        static $ENDALARM        = 'END:VALARM';
        static $ENDDAYLIGHT     = 'END:DAYLIGHT';
        static $ENDSTANDARD     = 'END:STANDARD';
        static $END             = 'END:';
        static $BEGINVALARM     = 'BEGIN:VALARM';
        static $BEGINSTANDARD   = 'BEGIN:STANDARD';
        static $BEGINDAYLIGHT   = 'BEGIN:DAYLIGHT';
        $this->unparsed = [];
        $comp           = $this;
        $compSync       = $subSync = 0;
        foreach( $rows as $lix => $row ) {
            switch( true ) {
                case ( StringFactory::startsWith( $row, $ENDALARM ) ||
                       StringFactory::startsWith( $row, $ENDDAYLIGHT ) ||
                       StringFactory::startsWith( $row, $ENDSTANDARD )) :
                    if( 1 != $subSync ) {
                        throw new UnexpectedValueException(
                            self::getErrorMsg( $rows, $lix )
                        );
                    }
                    $subSync -= 1;
                    break;
                case StringFactory::startsWith( $row, $END ) :
                    if( 1 != $compSync ) { // end:<component>
                        throw new UnexpectedValueException(
                            self::getErrorMsg( $rows, $lix )
                        );
                    }
                    $compSync -= 1;
                    break 2;  /* skip trailing empty lines.. */
                case StringFactory::startsWith( $row, $BEGINVALARM ) :
                    $comp     = $this->newValarm();
                    $subSync += 1;
                    break;
                case StringFactory::startsWith( $row, $BEGINSTANDARD ) :
                    $comp     = $this->newStandard();
                    $subSync += 1;
                    break;
                case StringFactory::startsWith( $row, $BEGINDAYLIGHT ) :
                    $comp     = $this->newDaylight();
                    $subSync += 1;
                    break;
                case StringFactory::startsWith( $row, self::$BEGIN ) :
                    $compSync += 1;         // begin:<component>
                    break;
                default :
                    $comp->unparsed[] = $row;
                    break;
            } // end switch( true )
        } // end foreach( $rows as $lix => $row )
    }

    /**
     * Parse this properties
     *
     * @since  2.29.14 - 2019-09-03
     * @todo report invalid properties ??
     */
    private function parse3thisProperties()
    {
        /* concatenate property values spread over several lines */
        $this->unparsed = StringFactory::concatRows( $this->unparsed );
        /* parse each property 'line' */
        foreach( $this->unparsed as $lix => $row ) {
            /* get propname  +  split property name  and  opt.params and value */
            list( $propName, $row ) = StringFactory::getPropName( $row );
            if( StringFactory::isXprefixed( $propName )) {
                list( $value, $propAttr ) = StringFactory::splitContent( $row );
                $this->setXprop(
                    $propName,
                    StringFactory::strunrep( $value ),
                    $propAttr
                );
                continue;
            }
            if( ! property_exists( $this, parent::getInternalPropName( $propName ))) {
                continue; // todo report invalid properties ??
            } // skip property names not in comp
            /* separate attributes from value */
            list( $value, $propAttr ) = StringFactory::splitContent( $row );
            if(( self::$NLCHARS == strtolower( substr( $value, -2 ))) &&
                ! Util::isPropInList( $propName, self::$TEXTPROPS ) &&
                ( ! StringFactory::isXprefixed( $propName ))) {
                $value = StringFactory::trimTrailNL( $value );
            }
            /* call set<Propname>(.. . */
            $method = parent::getSetMethodName( $propName );
            switch( strtoupper( $propName )) {
                case self::ATTENDEE :
                    list( $value, $propAttr ) =
                        CalAddressFactory::parseAttendee( $value, $propAttr );
                    $this->{$method}( $value, $propAttr );
                    break;
                case self::CATEGORIES :   // fall through
                case self::RESOURCES :    // fall through
                case self::COLOR :        // fall through
                case self::COMMENT :      // fall through
                case self::CONTACT :      // fall through
                case self::DESCRIPTION :  // fall through
                case self::LOCATION :     // fall through
                case self::SUMMARY :
                    if( empty( $value )) {
                        $propAttr = null;
                    }
                    $this->{$method}( StringFactory::strunrep( $value ), $propAttr );
                    break;
                case self::REQUEST_STATUS :
                    $values    = explode( Util::$SEMIC, $value, 3 );
                    $values[1] = ( isset( $values[1] ))
                        ? StringFactory::strunrep( $values[1] )
                        : null;
                    $values[2] = ( isset( $values[2] ))
                        ? StringFactory::strunrep( $values[2] )
                        : null;
                    $this->{$method}( $values[0], $values[1], $values[2], $propAttr );
                    break;
                case self::FREEBUSY :
                    $class = get_class( $this );
                    list( $fbtype, $values, $propAttr ) =
                        $class::parseFreebusy( $value, $propAttr );
                    $this->{$method}( $fbtype, $values, $propAttr );
                    break;
                case self::GEO :
                    $values = explode( Util::$SEMIC, $value, 2 );
                    if( 2 > count( $values )) {
                        $values[1] = null;
                    }
                    $this->{$method}( $values[0], $values[1], $propAttr );
                    break;
                case self::EXDATE :
                    $values = ( empty( $value ))
                        ? null
                        : explode( Util::$COMMA, $value );
                    $this->{$method}( $values, $propAttr );
                    break;
                case self::RDATE :
                    list( $values, $propAttr ) =
                        RexdateFactory::parseRexdate( $value, $propAttr );
                    $this->{$method}( $values, $propAttr );
                    break;
                case self::EXRULE :     // fall through
                case self::RRULE :
                    $recur  = RecurFactory::parseRexrule( $value );
                    $this->{$method}( $recur, $propAttr );
                    break;
                case self::ACTION :     // fall through
                case self::STATUS :     // fall through
                case self::TRANSP :     // fall through
                case self::UID :        // fall through
                case self::TZID :       // fall through
                case self::RELATED_TO : // fall through
                case self::TZNAME :
                    $value = StringFactory::strunrep( $value );
                // fall through
                default:
                    $this->{$method}( $value, $propAttr );
                    break;
            } // end  switch( $propName.. .
        } // end foreach( $this->unparsed as $lix => $row )
        unset( $this->unparsed );
    }

    /**
     * Parse sub-components
     *
     * @since  2.29.3 - 2019-06-20
     */
    private function parse4subComps()
    {
        if( empty( $this->countComponents())) {
            return;
        }
        foreach( array_keys( $this->components ) as $ckey ) {
            if( ! empty( $this->components[$ckey] ) &&
                ! empty( $this->components[$ckey]->unparsed )) {
                $this->components[$ckey]->parse();
            }
        } // end foreach
    }

    /**
     * Return error message
     *
     * @param array $rows
     * @param int $lix
     * @return string
     * @since  2.26.3 - 2018-12-28
     */
    private static function getErrorMsg( array $rows, $lix )
    {
        static $ERR = 'Calendar component content not in sync (row %d)%s%s';
        return sprintf( $ERR, $lix, PHP_EOL, implode( PHP_EOL, $rows ));
    }

    /**
     * Return calendar component subcomponent from component container
     *
     * @param mixed $arg1 ordno/component type/ component uid
     * @param mixed $arg2 ordno if arg1 = component type
     * @return mixed CalendarComponent|bool
     * @since  2.26.1 - 2018-11-17
     * @todo throw InvalidArgumentException on unknown component
     */
    public function getComponent( $arg1 = null, $arg2 = null )
    {
        if( empty( $this->components )) {
            return false;
        }
        $index = $argType = null;
        switch( true ) {
            case ( is_null( $arg1 )) :
                $argType = self::$INDEX;
                $this->compix[self::$INDEX] = ( isset( $this->compix[self::$INDEX] ))
                    ? $this->compix[self::$INDEX] + 1 : 1;
                $index   = $this->compix[self::$INDEX];
                break;
            case ( ctype_digit((string) $arg1 )) :
                $argType = self::$INDEX;
                $index   = (int) $arg1;
                $this->compix = [];
                break;
            case ( Util::isCompInList( $arg1, self::$SUBCOMPS )) : // class name
                unset( $this->compix[self::$INDEX] );
                $argType = strtolower( $arg1 );
                if( is_null( $arg2 )) {
                    $index = $this->compix[$argType] =
                        ( isset( $this->compix[$argType] ))
                            ? $this->compix[$argType] + 1
                            : 1;
                }
                else {
                    $index = (int) $arg2;
                }
                break;
        } // end switch
        $index -= 1;
        $ckeys = array_keys( $this->components );
        if( ! empty( $index ) && ( $index > end( $ckeys ))) {
            return false;
        }
        $cix2gC = 0;
        foreach( $ckeys as $cix ) {
            if( empty( $this->components[$cix] )) {
                continue;
            }
            if(( self::$INDEX == $argType ) && ( $index == $cix )) {
                return clone $this->components[$cix];
            }
            elseif( 0 == strcasecmp(
                $this->components[$cix]->getCompType(),
                $argType
                )) {
                if( $index == $cix2gC ) {
                    return clone $this->components[$cix];
                }
                $cix2gC++;
            }
        } // end foreach
        /* not found.. . */
        $this->compix = [];
        return false;
    }

    /**
     * Add calendar component as subcomponent to container for subcomponents
     *
     * @param CalendarComponent $component
     * @return static
     * @since  1.x.x - 2007-04-24
     */
    public function addSubComponent( CalendarComponent $component )
    {
        $this->setComponent( $component );
        return $this;
    }

    /**
     * Return formatted output for subcomponents
     *
     * @return string
     * @since  2.27.2 - 2018-12-21
     * @throws Exception  (on Valarm/Standard/Daylight) err)
     */
    public function createSubComponent()
    {
        $config = $this->getConfig();
        $output = null;
        foreach( array_keys( $this->components ) as $cix ) {
            if( ! empty( $this->components[$cix] )) {
                $this->components[$cix]->setConfig( $config, false, true );
                $output .= $this->components[$cix]->createComponent();
            }
        }
        return $output;
    }
}
