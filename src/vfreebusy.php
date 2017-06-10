<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * link      http://kigkonsult.se/iCalcreator/index.php
 * package   iCalcreator
 * version   2.23.16
 * license   By obtaining and/or copying the Software, iCalcreator,
 *           you (the licensee) agree that you have read, understood,
 *           and will comply with the following terms and conditions.
 *           a. The above copyright, link, package and version notices,
 *              this licence notice and
 *              the [rfc5545] PRODID as implemented and invoked in the software
 *              shall be included in all copies or substantial portions of the Software.
 *           b. The Software, iCalcreator, is for
 *              individual evaluation use and evaluation result use only;
 *              non assignable, non-transferable, non-distributable,
 *              non-commercial and non-public rights, use and result use.
 *           c. Creative Commons
 *              Attribution-NonCommercial-NoDerivatives 4.0 International License
 *              (http://creativecommons.org/licenses/by-nc-nd/4.0/)
 *           In case of conflict, a and b supercede c.
 *
 * This file is a part of iCalcreator.
 */
namespace kigkonsult\iCalcreator;
use kigkonsult\iCalcreator\util\util;
/**
 * iCalcreator VFREEBUSY component class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-02
 */
class vfreebusy extends calendarComponent {
  use traits\ATTENDEEtrait,
      traits\COMMENTtrait,
      traits\CONTACTtrait,
      traits\DTENDtrait,
      traits\DTSTAMPtrait,
      traits\DTSTARTtrait,
      traits\DURATIONtrait,
      traits\FREEBUSYtrait,
      traits\ORGANIZERtrait,
      traits\REQUEST_STATUStrait,
      traits\UIDtrait,
      traits\URLtrait;
/**
 * Constructor for calendar component VFREEBUSY object
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.20 - 2017-02-01
 * @param array $config
 * @uses calendarComponent::__contruct()
 * @uses calendarComponent::setConfig()
 * @uses util::initConfig()
 */
  public function __construct( $config = []) {
    static $F = 'f';
    parent::__construct();
    $this->setConfig( util::initConfig( $config ));
    $this->cno = $F . parent::getObjectNo();
  }
/**
 * Destructor
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-03-18
 */
  public function __destruct() {
    unset( $this->xprop,
           $this->components,
           $this->unparsed,
           $this->config );
    unset( $this->objName,
           $this->cno,
           $this->propix,
           $this->compix,
           $this->propdelix );
    unset( $this->attendee,
           $this->comment,
           $this->contact,
           $this->dtend,
           $this->dtstamp,
           $this->dtstart,
           $this->duration,
           $this->freebusy,
           $this->organizer,
           $this->requeststatus,
           $this->uid,
           $this->url );
  }
/**
 * Return formatted output for calendar component VFREEBUSY object instance
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.3.1 - 2007-11-19
 * @return string
 * @uses calendarComponent::createUid()
 * @uses calendarComponent::createDtstamp()
 * @uses vfreebusy::createAttendee()
 * @uses vfreebusy::createComment()
 * @uses vfreebusy::createContact()
 * @uses vfreebusy::createDtstart()
 * @uses vfreebusy::createDtend()
 * @uses vfreebusy::createDuration()
 * @uses vfreebusy::createFreebusy()
 * @uses vfreebusy::createOrganizer()
 * @uses vfreebusy::createRequestStatus()
 * @uses vfreebusy::createUrl()
 * @uses vfreebusy::createUrl()
 * @uses calendarComponent::createXprop()
 */
  public function createComponent() {
    $objectname =  strtoupper( $this->objName );
    $component  = sprintf( util::$FMTBEGIN, $objectname );
    $component .= $this->createUid();
    $component .= $this->createDtstamp();
    $component .= $this->createAttendee();
    $component .= $this->createComment();
    $component .= $this->createContact();
    $component .= $this->createDtstart();
    $component .= $this->createDtend();
    $component .= $this->createDuration();
    $component .= $this->createFreebusy();
    $component .= $this->createOrganizer();
    $component .= $this->createRequestStatus();
    $component .= $this->createUrl();
    $component .= $this->createXprop();
    return $component . sprintf( util::$FMTEND, $objectname );
  }
}
