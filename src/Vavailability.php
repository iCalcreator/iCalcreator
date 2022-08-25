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
use InvalidArgumentException;
use Kigkonsult\Icalcreator\Formatter\Vavailability as Formatter;

use function array_keys;

/**
 * iCalcreator rfc7953 VAVAILABILITY component class
 *
 * @since  2.41.55 - 2022-08-13
 */
final class Vavailability extends VAcomponent
{
    // the following are OPTIONAL but MUST NOT occur more than once (and NOT declared in VAcomponent)
    use Traits\BUSYTYPErfc7953trait;
    use Traits\CLASStrait;
    use Traits\ORGANIZERtrait;
    use Traits\PRIORITYtrait;
    use Traits\SEQUENCEtrait;
    use Traits\URLtrait;

    /**
     * @var string
     */
    protected static string $compSgn = 'va';

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
        $this->setDtstamp();
        $this->setUid();
    }

    /**
     * Return Vavailability object instance
     *
     * @param null|array $config
     * @param null|string $busyType
     * @param null|string|DateTimeInterface $dtstart
     * @param null|string|DateTimeInterface $dtend
     * @param null|string|DateInterval $duration
     * @return Vavailability
     * @throws InvalidArgumentException
     * @throws Exception
     * @since  2.41.53 - 2022-08-08
     */
    public static function factory(
        ? array $config = [],
        ? string $busyType = null,
        null|string|DateTimeInterface $dtstart = null,
        null|string|DateTimeInterface $dtend = null,
        null|string|DateInterval $duration = null
    ) : Vavailability
    {
        $instance = new Vavailability( $config );
        if( null !== $busyType ) {
            $instance->setBusytype( $busyType );
        }
        if( null !== $dtstart ) {
            $instance->setDtstart( $dtstart );
        }
        if( null !== $dtend ) {
            $instance->setDtend( $dtend );
        }
        elseif( null !== $duration ) {
            $instance->setDuration( $duration );
        }
        return $instance;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if( ! empty( $this->components )) {
            foreach( array_keys( $this->components ) as $cix ) {
                $this->components[$cix]->__destruct();
            }
        }
        unset(
            $this->compType,
            $this->xprop,
            $this->components,
            $this->config,
            $this->compix,
            $this->propIx,
            $this->propDelIx
        );
        unset(
            $this->cno,
            $this->srtk
        );
        unset(
            $this->busytype,
            $this->class,
            $this->comment,
            $this->contact,
            $this->created,
            $this->description,
            $this->dtend,
            $this->dtstamp,
            $this->dtstart,
            $this->duration,
            $this->lastmodified,
            $this->location,
            $this->organizer,
            $this->priority,
            $this->sequence,
            $this->summary,
            $this->uid,
            $this->url
        );
    }

    /**
     * Return Available object instance
     *
     * @param null|string|DateTimeInterface $dtstart
     * @param null|string|DateTimeInterface $dtend
     * @param null|string|DateInterval $duration
     * @return Available
     * @throws InvalidArgumentException
     * @throws Exception
     * @since 2.41.53 - 2022-08-08
     */
    public function newAvailable(
        null|string|DateTimeInterface $dtstart = null,
        null|string|DateTimeInterface $dtend = null,
        null|string|DateInterval $duration = null
    ) : Available
    {
        $ix = $this->getNextComponentIndex();
        $this->components[$ix] = Available::factory( $this->getConfig(), $dtstart, $dtend, $duration );
        return $this->components[$ix];
    }

    /**
     * Return formatted output for calendar component Vavailability object instance
     *
     * @return string
     * @throws Exception  (on Duration/Rdate err)
     * @since  2.41.55 - 2022-08-13
     */
    public function createComponent() : string
    {
        return Formatter::format( $this );
    }
}
