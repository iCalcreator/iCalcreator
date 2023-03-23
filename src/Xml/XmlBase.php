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
namespace Kigkonsult\Icalcreator\Xml;

/**
 * iCalcreator XML (rfc6321) formatter/parser base class
 *
 * @since 2.41.69 2022-10-04
 */
abstract class XmlBase
{
    /**
     * @var string
     */
    protected static string $binary      = 'binary';

    /**
     * @var string
     */
    protected static string $cal_address = 'cal-address';

    /**
     * @var string
     */
    protected static string $components  = 'components';

    /**
     * @var string
     */
    protected static string $date        = 'date';

    /**
     * @var string
     */
    protected static string $date_time   = 'date-time';

    /**
     * @var string
     */
    protected static string $PARAMETERS  = 'parameters';

    /**
     * @var string
     */
    protected static string $period      = 'period';

    /**
     * @var string
     */
    protected static string $properties  = 'properties';

    /**
     * @var string
     */
    protected static string $recur       = 'recur';

    /**
     * @var string
     */
    protected static string $text        = 'text';

    /**
     * @var string
     */
    protected static string $unknown     = 'unknown';

    /**
     * @var string
     */
    protected static string $uri         = 'uri';

    /**
     * @var string
     */
    protected static string $Vcalendar   = 'vcalendar';

    /**
     * @var string
     */
    protected static string $code        = 'code';

    /**
     * @var string
     */
    protected static string $description = 'description';

    /**
     * @var string
     */
    protected static string $data        = 'data';
}
