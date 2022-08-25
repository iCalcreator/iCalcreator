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

use DateInterval;
use DateTimeInterface;
use Exception;
use Kigkonsult\Icalcreator\Formatter\Valarm as Formatter;

/**
 * iCalcreator VALARM component class
 *
 * @since  2.41.55 - 2022-08-13
 */
final class Valarm extends CalendarComponent
{
    use Traits\UIDrfc7986trait;
    use Traits\RELATED_TOtrait;
    use Traits\ACTIONtrait;
    use Traits\ATTACHtrait;
    use Traits\ATTENDEEtrait; // Valarm::emailprop
    use Traits\DESCRIPTIONtrait;
    use Traits\DURATIONtrait;
    use Traits\PROXIMITYrfc9074trait;
    use Traits\REPEATtrait;
    use Traits\STYLED_DESCRIPTIONrfc9073trait;
    use Traits\SUMMARYtrait;
    use Traits\TRIGGERtrait;
    use Traits\ACKNOWLEDGEDrfc9074trait;

    /**
     * @var string
     */
    protected static string $compSgn = 'a';

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
     * Return Valarm object instance
     *
     * @param null|array $config
     * @param null|string $action property ACTION value
     * @param null|string|DateInterval|DateTimeInterface $trigger  property TRIGGER value
     * @return Valarm
     * @throws Exception
     * @since  2.41.53 - 2022-08-08
     */
    public static function factory(
        ? array $config = null,
        ? string $action = null,
        null|string|DateInterval|DateTimeInterface $trigger = null
    ) : Valarm
    {
        $instance = new Valarm( $config );
        if( null !== $action ) {
            $instance->setAction( $action );
        }
        if( null !== $trigger ) {
            $instance->setTrigger( $trigger );
        }
        return $instance;
    }


        /**
     * Destructor
     *
     * @since 2.41.3 2022-01-17
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
            $this->relatedto,
            $this->action,
            $this->attach,
            $this->attendee,
            $this->description,
            $this->duration,
            $this->proximity,
            $this->repeat,
            $this->styleddescription,
            $this->summary,
            $this->trigger,
            $this->acknowledged
        );
    }

    /**
     * Return Vlocation object instance
     */
    use Traits\NewVlocationTrait;

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
