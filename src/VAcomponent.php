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

/**
 * iCalcreator VAcomponents base class
 *
 * @since  2.41.53 - 2022-08-11
 */
abstract class VAcomponent extends Vcomponent
{
    //the following are REQUIRED but MUST NOT occur more than once
    use Traits\UIDrfc7986trait;

    // the following are REQUIRED but MUST NOT occur more than once : AVAILABLE
    // the following are OPTIONAL but MUST NOT occur more than once : Vavailability
    use Traits\DTSTARTtrait;

    // the following are OPTIONAL but MUST NOT occur more than once
    use Traits\CREATEDtrait;
    use Traits\DESCRIPTIONtrait;
    use Traits\LAST_MODIFIEDtrait;
    use Traits\LOCATIONtrait;
    use Traits\SUMMARYtrait;

    // Either 'dtend' or 'duration' MAY appear in an 'availableprop',
    // but 'dtend' and 'duration' MUST NOT occur in the same 'availabilityprop'/'availableprop'.
    // 'duration' MUST NOT be present if 'dtstart' is not present : VAVAILABILITYonly
    use Traits\DTENDtrait;
    use Traits\DURATIONtrait;

        //the following are OPTIONAL and MAY occur more than once
    use Traits\CATEGORIEStrait;
    use Traits\COMMENTtrait;
    use Traits\CONTACTtrait;
}
