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
namespace Kigkonsult\Icalcreator\Traits;

use Kigkonsult\Icalcreator\Formatter\Property\SingleProps;
use Kigkonsult\Icalcreator\Pc;
use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\HttpFactory;
use InvalidArgumentException;

/**
 * SOURCE property functions
 *
 * @since 2.41.85 2024-01-18
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
     * @since 2.41.85 2024-01-18
     */
    public function getSource( ? bool $inclParam = false ) : bool | string | Pc
    {
        if( empty( $this->source )) {
            return false;
        }
        return $inclParam ? clone $this->source : $this->source->getValue();
    }

    /**
     * Return bool true if set (and ignore empty property)
     *
     * @return bool
     * @since 2.41.88 2024-01-19
     */
    public function isSourceSet() : bool
    {
        return self::isPropSet( $this->source->getValue());
    }

    /**
     * Set calendar component property source
     *
     * @param null|string|Pc   $value
     * @param null|mixed[] $params
     * @return static
     * @throws InvalidArgumentException
     * @since 2.41.85 2024-01-18
     */
    public function setSource( null|string|Pc $value = null, ? array $params = [] ) : static
    {
        $pc      = Pc::factory( $value, $params );
        $pcValue = rtrim((string) $pc->getValue());
        if( empty( $pcValue )) {
            $this->assertEmptyValue( $pcValue, self::SOURCE );
            $this->source = $pc->setEmpty();
        }
        else {
            $pc->setValue( Util::assertString( $pcValue, self::SOURCE ));
            HttpFactory::urlSet( $this->source, $pc );
        }
        return $this;
    }
}
