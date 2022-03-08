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

use function array_keys;
use function sprintf;
use function strtoupper;

/**
 * iCalcreator rfc7953 VAVAILABILITY component class
 *
 * @since 2.41.29 2022-02-24
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
            $this->unparsed,
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
     */
    public function newAvailable(
        null|string|DateTimeInterface $dtstart = null,
        null|string|DateTimeInterface $dtend = null,
        null|string|DateInterval $duration = null
    ) : Available
    {
        $ix = $this->getNextComponentIndex();
        $this->components[$ix] = new Available( $this->getConfig());
        $this->components[$ix]->getDtstamp();
        $this->components[$ix]->getUid();
        if( null !== $dtstart ) {
            $this->components[$ix]->setDtstart( $dtstart );
        }
        if( null !== $dtend ) {
            $this->components[$ix]->setDtend( $dtend );
        }
        elseif( null !== $duration ) {
            $this->components[$ix]->setDuration( $duration );
        }
        return $this->components[$ix];
    }

    /**
     * Return formatted output for calendar component VEVENT object instance
     *
     * @return string
     * @throws Exception  (on Duration/Rdate err)
     * @since 2.41.29 2022-02-24
     */
    public function createComponent() : string
    {
        $compType    = strtoupper( $this->getCompType());
        return
            sprintf( self::$FMTBEGIN, $compType ) .
            $this->createUid() .
            $this->createDtstamp() .
            $this->createBusytype() .
            $this->createCategories() .
            $this->createClass() .
            $this->createCreated() .
            $this->createSummary() .
            $this->createDescription() .
            $this->createComment() .
            $this->createContact() .
            $this->createDtstart() .
            $this->createDtend() .
            $this->createDuration() .
            $this->createLastmodified() .
            $this->createLocation() .
            $this->createOrganizer() .
            $this->createPriority() .
            $this->createSequence() .
            $this->createUrl() .
            $this->createXprop() .
            $this->createSubComponent() .
            sprintf( self::$FMTEND, $compType );
    }
}
