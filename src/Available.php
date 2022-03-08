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

use function sprintf;
use function strtoupper;

/**
 * iCalcreator rfc7953 Available component class
 *
 * @since 2.41.29 2022-02-24
 */
final class Available extends VAcomponent
{

    // the following are OPTIONAL but MUST NOT occur more than once (and NOT declared in VAcomponent)
    use Traits\RECURRENCE_IDtrait;
    use Traits\RRULEtrait;

    // the following are OPTIONAL and MAY occur more than once (and NOT declared in VAcomponent)
    use Traits\EXDATEtrait;
    use Traits\RDATEtrait;

    /**
     * @var string
     */
    protected static string $compSgn = 'av';

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
            $this->categories,
            $this->comment,
            $this->contact,
            $this->created,
            $this->description,
            $this->dtstamp,
            $this->dtstart,
            $this->dtend,
            $this->duration,
            $this->exdate,
            $this->lastmodified,
            $this->location,
            $this->recurrenceid,
            $this->rdate,
            $this->rrule,
            $this->summary,
            $this->uid
        );
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
            $this->createCategories() .
            $this->createComment() .
            $this->createContact() .
            $this->createCreated() .
            $this->createDescription() .
            $this->createDtstart() .
            $this->createDtend() .
            $this->createDuration() .
            $this->createExdate() .
            $this->createRrule() .
            $this->createLastmodified() .
            $this->createLocation() .
            $this->createRdate() .
            $this->createRrule() .
            $this->createRecurrenceid() .
            $this->createSummary() .
            $this->createXprop() .
            sprintf( self::$FMTEND, $compType );
    }
}
