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
 * iCalcreator PARTICIPANT component class
 *
 * @since 2.41.29 2022-02-24
 */
final class Participant extends Vcomponent
{
    /* The following are REQUIRED but MUST NOT occur more than once. */
    use Traits\PARTICIPANT_TYPErfc9073trait;
    use Traits\UIDrfc7986trait;

    /* The following are OPTIONAL but MUST NOT occur more than once. */
    use Traits\CALENDAR_ADDRESSrfc9073trait;
    use Traits\CREATEDtrait;
    use Traits\DESCRIPTIONtrait;
    use Traits\GEOtrait;
    use Traits\LAST_MODIFIEDtrait;
    use Traits\PRIORITYtrait;
    use Traits\SEQUENCEtrait;
    use Traits\STATUStrait;
    use Traits\SUMMARYtrait;
    use Traits\URLtrait;

    /* The following are OPTIONAL and MAY occur more than once. */
    use Traits\ATTACHtrait;
    use Traits\CATEGORIEStrait;
    use Traits\COMMENTtrait;
    use Traits\CONTACTtrait;
    use Traits\LOCATIONtrait;
    use Traits\REQUEST_STATUStrait;
    use Traits\RELATED_TOtrait;
    use Traits\RESOURCEStrait;
        // strucloc ??
        // strucres ??
    use Traits\STYLED_DESCRIPTIONrfc9073trait;
    use Traits\STRUCTURED_DATArfc9073trait;

    /**
     * @var string
     */
    protected static string $compSgn = 'p';

    /**
     * Destructor
     *
     * @since 2.41.4 2022-01-18
     */
    public function __destruct()
    {
        unset(
            $this->compType,
            $this->xprop,
            $this->components,
            $this->unparsed,
            $this->config,
            $this->propIx,
            $this->compix,
            $this->propDelIx
        );
        unset(
            $this->cno,
            $this->srtk
        );
        unset(
            $this->participanttype,
            $this->uid,

            $this->calendaraddress,
            $this->created,
            $this->description,
            $this->dtstamp,
            $this->geo,
            $this->lastmodified,
            $this->priority,
            $this->sequence,
            $this->status,
            $this->summary,
            $this->url,

            $this->attach,
            $this->categories,
            $this->comment,
            $this->contact,
            $this->location,
            $this->rstatus,
            $this->relatedto,
            $this->resources,
            // strucloc ??
            // strucres ??
            $this->styleddescription,
            $this->structureddata
        );
    }

    /**
     * Return formatted output for calendar component PARTICIPANT object instance
     *
     * @return string
     * @throws Exception
     * @since 2.41.29 2022-02-24
     */
    public function createComponent() : string
    {
        $compType    = strtoupper( $this->getCompType());
        return
            sprintf( self::$FMTBEGIN, $compType ) .
            $this->createUid() .
            $this->createDtstamp() .
            $this->createParticipanttype() .
            $this->createCalendaraddress() .
            $this->createContact() .
            $this->createLocation() .
            $this->createCreated() .
            $this->createSummary() .
            $this->createDescription() .
            $this->createStyleddescription() .
            $this->createStructureddata() .
            $this->createGeo() .
            $this->createLastmodified() .
            $this->createPriority() .
            $this->createSequence() .
            $this->createStatus() .
            $this->createUrl() .
            $this->createAttach() .
            $this->createCategories() .
            $this->createComment() .
            $this->createRequeststatus() .
            $this->createRelatedto() .
            $this->createResources() .
//      strucloc
//      strucres
            $this->createXprop() .
            $this->createSubComponent() .
            sprintf( self::$FMTEND, $compType );
    }
}
