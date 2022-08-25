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
namespace Kigkonsult\Icalcreator\Traits;

use Kigkonsult\Icalcreator\Formatter\Property\SingleProps;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\HttpFactory;
use Kigkonsult\Icalcreator\Util\ParameterFactory;
use InvalidArgumentException;

/**
 * SOURCE property functions
 *
 * @since 2.41.55 2022-08-13
 */
trait SOURCErfc7986trait
{
    /**
     * @var null|Pc component property SOURCE value
     */
    protected ? Pc $source = null;

    /**
     * Return formatted output for calendar component property source
     *
     * @return string
     */
    public function createSource() : string
    {
        return SingleProps::format(
            self::SOURCE,
            $this->source,
            $this->getConfig( self::ALLOWEMPTY )
        );
    }

    /**
     * Delete calendar component property source
     *
     * @return bool
     */
    public function deleteSource() : bool
    {
        $this->source = null;
        return true;
    }

    /**
     * Get calendar component property source
     *
     * @param null|bool   $inclParam
     * @return bool|string|Pc
     */
    public function getSource( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->source )) {
            return false;
        }
        return $inclParam ? clone $this->source : $this->source->value;
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.36 2022-04-03
     */
    public function isSourceSet() : bool
    {
        return ! empty( $this->source->value );
    }

    /**
     * Set calendar component property source
     *
     * @param null|string|Pc   $value
     * @param null|array $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.36 2022-04-03
     */
    public function setSource( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $value = ( $value instanceof Pc )
            ? clone $value
            : Pc::factory( $value, ParameterFactory::setParams( $params ));
        if( empty( $value->value )) {
            $this->assertEmptyValue( $value->value, self::SOURCE );
            $this->source = $value->setEmpty();
        }
        else {
            Util::assertString( $value->value, self::SOURCE );
            HttpFactory::urlSet( $this->source, $value );
        }
        return $this;
    }
}
