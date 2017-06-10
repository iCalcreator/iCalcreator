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
namespace kigkonsult\iCalcreator\traits;
use kigkonsult\iCalcreator\util\util;
use kigkonsult\iCalcreator\util\utilGeo;
/**
 * GEO property functions
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since 2.22.23 - 2017-02-02
 */
trait GEOtrait {
/**
 * @var array component property GEO value
 * @access protected
 */
  protected $geo = null;
/**
 * Return formatted output for calendar component property geo
 *
 * @return string
 * @uses calendarComponent::getConfig()
 * @uses util::createElement()
 * @uses util::createParams()
 * @uses utilGeo::geo2str2()
 */
  public function createGeo() {
    if( empty( $this->geo ))
      return null;
    if( empty( $this->geo[util::$LCvalue] ))
      return ( $this->getConfig( util::$ALLOWEMPTY )) ? util::createElement( util::$GEO ) : null;
    return util::createElement( util::$GEO,
                                util::createParams( $this->geo[util::$LCparams] ),
                                utilGeo::geo2str2( $this->geo[util::$LCvalue][utilGeo::$LATITUDE],  utilGeo::$geoLatFmt ) .
                                util::$SEMIC .
                                utilGeo::geo2str2( $this->geo[util::$LCvalue][utilGeo::$LONGITUDE], utilGeo::$geoLongFmt ));
  }
/**
 * Set calendar component property geo
 *
 * @param mixed $latitude
 * @param mixed $longitude
 * @param array $params
 * @return bool
 * @uses util::setParams()
 * @uses calendarComponent::getConfig()
 */
  public function setGeo( $latitude, $longitude, $params=null ) {
    if( isset( $latitude ) && isset( $longitude )) {
      if( ! is_array( $this->geo ))
        $this->geo = [];
      $this->geo[util::$LCvalue][utilGeo::$LATITUDE]  = floatval( $latitude );
      $this->geo[util::$LCvalue][utilGeo::$LONGITUDE] = floatval( $longitude );
      $this->geo[util::$LCparams] = util::setParams( $params );
    }
    elseif( $this->getConfig( util::$ALLOWEMPTY ))
      $this->geo = [util::$LCvalue  => util::$EMPTYPROPERTY,
                    util::$LCparams => util::setParams( $params )];
    else
      return false;
    return true;
  }
}
