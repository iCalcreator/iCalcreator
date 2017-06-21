<?php
/**
 * iCalcreator, a PHP rfc2445/rfc5545 solution.
 *
 * copyright 2007-2017 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * link      http://kigkonsult.se/iCalcreator/index.php
 * package   iCalcreator
 * version   2.23.18
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
 * iCalcreator VALARM component class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-02
 */
class valarm extends calendarComponent {
  use traits\ACTIONtrait,
      traits\ATTACHtrait,
      traits\ATTENDEEtrait,
      traits\DESCRIPTIONtrait,
      traits\DURATIONtrait,
      traits\REPEATtrait,
      traits\SUMMARYtrait,
      traits\TRIGGERtrait;
/**
 * Constructor for calendar component VALARM object
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.20 - 2017-02-01
 * @param array $config
 */
  public function __construct( $config = []) {
    static $A = 'a';
    parent::__construct();
    $this->setConfig( util::initConfig( $config ));
    $this->cno = $A . parent::getObjectNo();
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
           $this->config,
           $this->propix,
           $this->propdelix );
    unset( $this->objName,
           $this->cno );
    unset( $this->action,
           $this->attach,
           $this->attendee,
           $this->description,
           $this->duration,
           $this->repeat,
           $this->summary,
           $this->trigger );
  }
/**
 * Return formatted output for calendar component VALARM object instance
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.5.1 - 2008-10-22
 * @return string
 */
  public function createComponent() {
    $objectname =  strtoupper( $this->objName );
    $component  = sprintf( util::$FMTBEGIN, $objectname );
    $component .= $this->createAction();
    $component .= $this->createAttach();
    $component .= $this->createAttendee();
    $component .= $this->createDescription();
    $component .= $this->createDuration();
    $component .= $this->createRepeat();
    $component .= $this->createSummary();
    $component .= $this->createTrigger();
    $component .= $this->createXprop();
    return $component . sprintf( util::$FMTEND, $objectname );
  }
/**
 * Return valarm component property value/params,
 *
 * If arg $inclParam, return array with keys VALUE/PARAMS.
 * @param string  $propName
 * @param int     $propix   specific property in case of multiply occurences
 * @param bool    $inclParam
 * @param bool    $specform
 * @return mixed
 */
  public function getProperty( $propName=false,
                               $propix=false,
                               $inclParam=false,
                               $specform=false ) {
    switch( strtoupper( $propName )) {
      case util::$ACTION:
        if( isset( $this->action[util::$LCvalue] ))
          return ( $inclParam ) ? $this->action
                                : $this->action[util::$LCvalue];
        break;
      case util::$REPEAT:
        if( isset( $this->repeat[util::$LCvalue] ))
          return ( $inclParam ) ? $this->repeat
                                : $this->repeat[util::$LCvalue];
        break;
      case util::$TRIGGER:
        if( isset( $this->trigger[util::$LCvalue] ))
          return ( $inclParam ) ? $this->trigger
                                : $this->trigger[util::$LCvalue];
        break;
      default:
        return parent::getProperty( $propName,
                                    $propix,
                                    $inclParam,
                                    $specform );
        break;
    }
    return false;
  }
}
