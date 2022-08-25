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
use Kigkonsult\Icalcreator\Formatter\Vresource as Formatter;

/**
 * iCalcreator Vlocation component class
 *
 * @since  2.41.55 - 2022-08-13
 */
final class Vresource extends CalendarComponent
{
    /* The following are REQUIRED but MUST NOT occur more than once. */
    use Traits\UIDrfc7986trait;

    /* The following are OPTIONAL but MUST NOT occur more than once. */
    use Traits\DESCRIPTIONtrait;
    use Traits\GEOtrait;
    use Traits\NAMErfc7986trait;
    use Traits\RESOURCE_TYPErfc9073trait;

    /* The following are OPTIONAL and MAY occur more than once. */
    use Traits\STRUCTURED_DATArfc9073trait;

    /**
     * @var string
     */
    protected static string $compSgn = 'vr';

    /**
     * Constructor
     *
     * @param null|array $config
     * @throws Exception
     * @since  2.41.53 - 2022-08-11
     */
    public function __construct( ? array $config = [] )
    {
        parent::__construct( $config );
        $this->setUid();
    }

    /**
     * Return Vresource object instance
     *
     * @param null|array $config
     * @param null|string $resourceType property RESOURCE-TYPE value
     * @param null|string $name property NAME value
     * @return Vresource
     * @throws Exception
     * @since  2.41.53 - 2022-08-08
     */
    public static function factory(
        ? array $config = [],
        ? string $resourceType = null,
        ? string $name = null
    ): Vresource
    {
        $instance = new Vresource( $config );
        if( null !== $resourceType ) {
            $instance->setResourcetype( $resourceType );
        }
        if( null !== $name ) {
            $instance->setName( $name );
        }
        return $instance;
    }

    /**
     * Destructor
     *
     * @since 2.41.7 2022-01-20
     */
    public function __destruct()
    {
        unset(
            $this->compType,
            $this->xprop,
            $this->components,
            $this->config,
            $this->propIx,
            $this->propDelIx
        );
        unset(
            $this->cno,
            $this->srtk
        );
        unset(
            $this->uid,
            $this->description,
            $this->geo,
            $this->name,
            $this->resourcetype,
            $this->structureddata
        );
    }

    /**
     * Return formatted output for calendar component VALARM object instance
     *
     * @return string
     * @throws Exception  (on Duration/Trigger err)
     * @since  2.41.55 - 2022-08-13
     */
    public function createComponent() : string
    {
        return Formatter::format( $this );
    }
}
