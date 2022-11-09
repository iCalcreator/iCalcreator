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
namespace Kigkonsult\Icalcreator;

use Exception;
use Kigkonsult\Icalcreator\Traits\MvalTrait;
use Kigkonsult\Icalcreator\Parser\ComponentParser;
use Kigkonsult\Icalcreator\Util\StringFactory;
use Kigkonsult\Icalcreator\Util\Util;
use UnexpectedValueException;

use function is_array;
use function ksort;
use function method_exists;
use function str_contains;

/**
 *  Parent class for calendar components
 *
 * @since 2.41.68 2022-10-08
 */
abstract class CalendarComponent extends IcalBase
{
    /**
     * @var array  component sort keyss
     */
    public array $srtk = [];

    /**
     * @var string component number
     */
    public string $cno = '';

    /**
     * @var string
     */
    protected static string $compSgn = 'xx';

    /**
     * Constructor for calendar component
     *
     * @param null|array $config
     * @since  2.27.14 - 2019-07-03
     */
    public function __construct( ? array $config = [] )
    {
        static $objectNo = 0;
        $class           = static::class;
        $this->compType  = StringFactory::compTypeFromClass( $class  );
        $this->cno       = $class::$compSgn . ++$objectNo;
        $this->setConfig( $config ?? [] );
    }

    /**
     * Returns calendar property unique values
     *
     * For ATTENDEE, CATEGORIES, CONTACT, RELATED_TO or RESOURCES (keys)
     * and for each, number of occurrence (values)
     *
     * @param string $propName
     * @param array $output incremented result array
     * @return void
     * @since 2.41.68 2022-10-08
     */
    public function getProperties( string $propName, array & $output ) : void
    {
        if( empty( $output )) {
            $output = [];
        }
        if( ! self::isMultiProp1( $propName )) {
            return;
        }
        $method = StringFactory::getGetMethodName( $propName );
        if( ! method_exists( $this, $method )) {
            return;
        }
        while( false !== ( $content = $this->{$method}())) {
            if( empty( $content )) {
                continue;
            }
            if( is_array( $content )) {
                foreach( $content as $part ) {
                    if( str_contains( $part, Util::$COMMA )) {
                        StringFactory::commaSplitCount( $part, $output );
                    } // end if
                    else {
                        StringFactory::stringCount( $part, $output );
                    } // end else
                } // end foreach
            } // end if( is_array( $content ))
            elseif( str_contains( $content, Util::$COMMA )) {
                StringFactory::commaSplitCount( $content, $output );
            }
            else {
                StringFactory::stringCount( $content, $output );
            }
        } // end while
        ksort( $output );
    }

    /**
     * Parse data into component properties
     *
     * @param null|string|string[] $unParsedText strict rfc2445 formatted, single property string or array of strings
     * @return static
     * @throws Exception
     * @throws UnexpectedValueException;
     * @since  2.41.54 - 2022-08-09
     * @// todo report invalid properties, Exception.. ??
     */
    public function parse( null|string|array $unParsedText = null ) : static
    {
        ComponentParser::factory( $this )->parse( $unParsedText );
        return $this;
    }

    /**
     * Add calendar component as subcomponent to container for subcomponents
     *
     * @param CalendarComponent $component
     * @return static
     * @since  1.x.x - 2007-04-24
     */
    public function addSubComponent( CalendarComponent $component ) : static
    {
        $this->setComponent( $component );
        return $this;
    }

    /**
     * Component multi-property help methods
     */
    use MvalTrait;
}
