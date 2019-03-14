<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * copyright (c) 2007-2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   iCalcreator
 * Version   2.26.8
 * License   Subject matter of licence is the software iCalcreator.
 *           The above copyright, link, package and version notices,
 *           this licence notice and the invariant [rfc5545] PRODID result use
 *           as implemented and invoked in iCalcreator shall be included in
 *           all copies or substantial portions of the iCalcreator.
 *
 *           iCalcreator is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           iCalcreator is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with iCalcreator. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is a part of iCalcreator.
 */

namespace Kigkonsult\Icalcreator;

use Kigkonsult\Icalcreator\Util\Util;
use Kigkonsult\Icalcreator\Util\UtilGeo;
use Kigkonsult\Icalcreator\Util\UtilSelect;
use Kigkonsult\Icalcreator\Util\UtilRedirect;
use Kigkonsult\Icalcreator\Util\VcalendarSortHandler;

use function array_change_key_case;
use function array_keys;
use function basename;
use function clearstatcache;
use function ctype_digit;
use function date;
use function end;
use function explode;
use function file_exists;
use function file_get_contents;
use function filter_var;
use function func_get_args;
use function func_num_args;
use function gethostbyname;
use function implode;
use function in_array;
use function intval;
use function is_array;
use function is_file;
use function is_null;
use function is_readable;
use function is_string;
use function is_writable;
use function ksort;
use function microtime;
use function realpath;
use function rtrim;
use function str_replace;
use function strcasecmp;
use function strlen;
use function stripos;
use function strpos;
use function strtolower;
use function strtoupper;
use function substr;
use function trim;
use function usort;

/**
 * Vcalendar class
 *
 * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @since  2.26 - 2018-11-10
 */
class Vcalendar extends IcalBase
{
    use Traits\CALSCALEtrait,
        Traits\METHODtrait,
        Traits\PRODIDtrait,
        Traits\VERSIONtrait;
    /**
     * @var string property output formats, used by CALSCALE, METHOD, PRODID and VERSION
     * @access private
     * @static
     */
    private static $FMTICAL = "%s:%s\r\n";

    /**
     * Constructor for calendar object
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-03-16
     * @param array $config
     */
    public function __construct( $config = [] ) {
        $this->setConfig( Util::$UNIQUE_ID, ( isset( $_SERVER[Util::$SERVER_NAME] ))
            ? gethostbyname( $_SERVER[Util::$SERVER_NAME] )
            : Util::$LOCALHOST
        );
        $this->setConfig( Util::initConfig( $config ));
    }

    /**
     * Destructor
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-03-18
     */
    public function __destruct() {
        if( ! empty( $this->components )) {
            foreach( $this->components as $cix => $comp ) {
                $this->components[$cix]->__destruct();
            }
        }
        unset( $this->xprop,
            $this->components,
            $this->unparsed,
            $this->config,
            $this->propix,
            $this->compix,
            $this->propdelix
        );
        unset( $this->calscale,
            $this->method,
            $this->prodid,
            $this->version
        );
    }

    /**
     * Return iCalcreator version
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.18.5 - 2013-08-29
     * @return string
     */
    public static function iCalcreatorVersion() {
        return trim( substr( ICALCREATOR_VERSION, strpos( ICALCREATOR_VERSION, Util::$SP1 )));
    }

    /**
     * Return Vcalendar config value or * calendar components, false on not found
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-02-28
     * @param mixed $config
     * @return mixed
     */
    public function getConfig( $config = null ) {
        static $YMDHIS = 'YmdHis';
        static $DOTICS = '.ics';
        if( is_null( $config )) {
            $return                    = [];
            $return[Util::$ALLOWEMPTY] = $this->getConfig( Util::$ALLOWEMPTY );
            $return[Util::$DELIMITER]  = $this->getConfig( Util::$DELIMITER );
            $return[Util::$DIRECTORY]  = $this->getConfig( Util::$DIRECTORY );
            $return[Util::$FILENAME]   = $this->getConfig( Util::$FILENAME );
            $return[Util::$DIRFILE]    = $this->getConfig( Util::$DIRFILE );
            $return[Util::$FILESIZE]   = $this->getConfig( Util::$FILESIZE );
            if( false !== ( $cfg = $this->getConfig( Util::$URL ))) {
                $return[Util::$URL] = $cfg;
            }
            if( false !== ( $cfg = $this->getConfig( Util::$LANGUAGE ))) {
                $return[Util::$LANGUAGE] = $cfg;
            }
            if( false !== ( $cfg = $this->getConfig( Util::$TZID ))) {
                $return[Util::$TZID] = $cfg;
            }
            $return[Util::$UNIQUE_ID] = $this->getConfig( Util::$UNIQUE_ID );
            return $return;
        }
        switch( strtoupper( $config )) {
            case Util::$DELIMITER :
                if( isset( $this->config[Util::$DELIMITER] )) {
                    return $this->config[Util::$DELIMITER];
                }
                break;
            case Util::$DIRECTORY :
                if( ! isset( $this->config[Util::$DIRECTORY] )) {
                    $this->config[Util::$DIRECTORY] = Util::$DOT;
                }
                return $this->config[Util::$DIRECTORY];
                break;
            case Util::$DIRFILE :
                return $this->getConfig( Util::$DIRECTORY ) .
                    $this->getConfig( Util::$DELIMITER ) .
                    $this->getConfig( Util::$FILENAME );
                break;
            case Util::$FILEINFO :
                return [
                    $this->getConfig( Util::$DIRECTORY ),
                    $this->getConfig( Util::$FILENAME ),
                    $this->getConfig( Util::$FILESIZE ),
                ];
                break;
            case Util::$FILENAME :
                if( ! isset( $this->config[Util::$FILENAME] )) {
                    $this->config[Util::$FILENAME] =
                        date( $YMDHIS, intval( microtime( true ))) . $DOTICS;
                }
                return $this->config[Util::$FILENAME];
                break;
            case Util::$FILESIZE :
                $size = 0;
                if( empty( $this->config[Util::$URL] )) {
                    $dirfile = $this->getConfig( Util::$DIRFILE );
                    if( ! is_file( $dirfile ) || ( false === ( $size = filesize( $dirfile )))) {
                        $size = 0;
                    }
                    clearstatcache();
                }
                return $size;
                break;
            case Util::$UNIQUE_ID:
                if( isset( $this->config[Util::$UNIQUE_ID] )) {
                    return $this->config[Util::$UNIQUE_ID];
                }
                break;
            case Util::$URL :
                if( ! empty( $this->config[Util::$URL] )) {
                    return $this->config[Util::$URL];
                }
                break;
            default :
                return parent::getConfig( $config );
        }
        return false;
    }

    /**
     * General Vcalendar set config
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-02-02
     * @param mixed  $config
     * @param string $value
     * @param string $arg3 (dummy)
     * @return bool
     */
    public function setConfig( $config, $value = null, $arg3 = null ) {
        static $PROTOCOLS    = [ 'HTTP://', 'WEBCAL://', 'webcal://' ];
        static $PROTOHTTP    = 'http://';
        static $LCPROTOHTTPS = 'https://';
        static $UCPROTOHTTPS = 'HTTPS://';
        static $DOTICS       = '.ics';
        if( is_array( $config )) {
            $config = array_change_key_case( $config, CASE_UPPER );
            if( isset( $config[Util::$DELIMITER] )) {
                if( false === $this->setConfig( Util::$DELIMITER, $config[Util::$DELIMITER] )) {
                    return false;
                }
                unset( $config[Util::$DELIMITER] );
            }
            if( isset( $config[Util::$DIRECTORY] )) {
                if( false === $this->setConfig( Util::$DIRECTORY, $config[Util::$DIRECTORY] )) {
                    return false;
                }
                unset( $config[Util::$DIRECTORY] );
            }
            foreach( $config as $cKey => $cValue ) {
                if( false === $this->setConfig( $cKey, $cValue )) {
                    return false;
                }
            }
            return true;
        }
        $res = false;
        switch( strtoupper( $config )) {
            case Util::$DELIMITER :
                $this->config[Util::$DELIMITER] = $value;
                return true;
                break;
            case Util::$DIRECTORY :
                if( false === ( $value = realpath( rtrim( trim( $value ), $this->config[Util::$DELIMITER] )))) {
                    return false;
                }
                else { /* local directory */
                    $this->config[Util::$DIRECTORY] = $value;
                    $this->config[Util::$URL]       = null;
                    return true;
                }
                break;
            case Util::$FILENAME :
                $value   = trim( $value );
                $dirfile = $this->config[Util::$DIRECTORY] .
                    $this->config[Util::$DELIMITER] . $value;
                if( file_exists( $dirfile )) {
                    /* local file exists */
                    if( is_readable( $dirfile ) || is_writable( $dirfile )) {
                        clearstatcache();
                        $this->config[Util::$FILENAME] = $value;
                        return true;
                    }
                    else {
                        return false;
                    }
                }
                elseif( is_readable( $this->config[Util::$DIRECTORY] ) ||
                        is_writable( $this->config[Util::$DIRECTORY] )) {
                    /* read- or writable directory */
                    \clearstatcache();
                    $this->config[Util::$FILENAME] = $value;
                    return true;
                }
                else {
                    return false;
                }
                break;
            case Util::$LANGUAGE : // set language for calendar component as defined in [RFC 1766]
                $value  = trim( $value );
                $this->config[Util::$LANGUAGE] = $value;
                $this->makeProdid();
                $subcfg = [ Util::$LANGUAGE => $value ];
                $res    = true;
                break;
            case Util::$UNIQUE_ID :
                $value  = trim( $value );
                $this->config[Util::$UNIQUE_ID] = $value;
                $this->makeProdid();
                $subcfg = [ Util::$UNIQUE_ID => $value ];
                $res    = true;
                break;
            case Util::$URL :
                /* remote file - URL */
                $value = str_replace( $PROTOCOLS, $PROTOHTTP, trim( $value ));
                $value = str_replace( $UCPROTOHTTPS, $LCPROTOHTTPS, trim( $value ));
                if(( $PROTOHTTP != substr( $value, 0, 7 )) &&
                    ( $LCPROTOHTTPS != substr( $value, 0, 8 ))) {
                    return false;
                }
                $this->config[Util::$DIRECTORY] = Util::$DOT;
                $this->config[Util::$URL]       = $value;
                if( $DOTICS != strtolower( substr( $value, -4 ))) {
                    unset( $this->config[Util::$FILENAME] );
                }
                else {
                    $this->config[Util::$FILENAME] = basename( $value );
                }
                return true;
                break;
            default:  // any unvalid config key.. .
                $res = parent::setConfig( $config, $value );
        }
        if( ! $res ) {
            return false;
        }
        if( isset( $subcfg ) && ! empty( $this->components )) {
            foreach( $subcfg as $cfgkey => $cfgValue ) {
                foreach( $this->components as $cix => $component ) {
                    $res = $this->components[$cix]->setConfig( $cfgkey, $cfgValue, true );
                    if( ! $res ) {
                        break 2;
                    }
                }
            }
        }
        return $res;
    }

    /**
     * Delete calendar property value
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.8.8 - 2011-03-15
     * @param mixed $propName bool false => X-property
     * @param int   $propix   specific property in case of multiply occurences
     * @return bool true on successfull delete
     */
    public function deleteProperty(
        $propName = null,
        $propix   = null
    ) {
        $propName = ( $propName ) ? strtoupper( $propName ) : Util::$X_PROP;
        if( ! $propix ) {
            $propix = ( isset( $this->propdelix[$propName] ) &&
                ( Util::$X_PROP != $propName ))
                ? $this->propdelix[$propName] + 2
                : 1;
        }
        $this->propdelix[$propName] = --$propix;
        switch( $propName ) {
            case Util::$CALSCALE:
                $this->calscale = null;
                break;
            case Util::$METHOD:
                $this->method = null;
                break;
            default:
                return parent::deleteXproperty( $propName, $this->xprop, $propix, $this->propdelix );
                break;
        }
        return true;
    }

    /**
     * Return calendar property value/params
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param string $propName
     * @param int    $propix specific property in case of multiply occurences
     * @param bool   $inclParam
     * @return mixed
     */
    public function getProperty(
        $propName  = null,
        $propix    = null,
        $inclParam = null
    ) {
        static $RECURRENCE_ID_UID = 'RECURRENCE-ID-UID';
        static $R_UID             = 'R-UID';
        $propName = ( $propName ) ? strtoupper( $propName ) : Util::$X_PROP;
        if( Util::$X_PROP == $propName ) {
            if( empty( $propix )) {
                $propix = ( isset( $this->propix[$propName] ))
                    ? $this->propix[$propName] + 2
                    : 1;
            }
            $this->propix[$propName] = --$propix;
        }
        switch( $propName ) {
            case Util::$ATTENDEE:
            case Util::$CATEGORIES:
            case Util::$CONTACT:
            case Util::$DTSTART:
            case Util::$GEOLOCATION:
            case Util::$LOCATION:
            case Util::$ORGANIZER:
            case Util::$PRIORITY:
            case Util::$RESOURCES:
            case Util::$STATUS:
            case Util::$SUMMARY:
            case $RECURRENCE_ID_UID:
            case Util::$RELATED_TO:
            case $R_UID:
            case Util::$UID:
            case Util::$URL:
                $output = [];
                foreach( $this->components as $cix => $component ) {
                    if( ! Util::isCompInList( $component->compType, Util::$VCOMPS )) {
                        continue;
                    }
                    if( Util::isPropInList( $propName, Util::$MPROPS1 )) {
                        $component->getProperties( $propName, $output );
                        continue;
                    }
                    elseif(( 3 < \strlen( $propName )) &&
                        ( Util::$UID == \substr( $propName, -3 ))) {
                        if( false !== ( $content = $component->getProperty( Util::$RECURRENCE_ID ))) {
                            $content = $component->getProperty( Util::$UID );
                        }
                    }
                    elseif( Util::$GEOLOCATION == $propName ) {
                        if( false === ( $geo = $component->getProperty( Util::$GEO ))) {
                            continue;
                        }
                        $loc     = $component->getProperty( Util::$LOCATION );
                        $content = ( empty( $loc )) ? null : $loc . Util::$SP1;
                        $content .= UtilGeo::geo2str2( $geo[UtilGeo::$LATITUDE], UtilGeo::$geoLatFmt ) .
                                    UtilGeo::geo2str2( $geo[UtilGeo::$LONGITUDE],  UtilGeo::$geoLongFmt ) .
                                    utiL::$L;
                    }
                    elseif( false === ( $content = $component->getProperty( $propName ))) {
                        continue;
                    }
                    if(( false === $content ) || empty( $content )) {
                        continue;
                    }
                    elseif( is_array( $content )) {
                        if( isset( $content[Util::$LCYEAR] )) {
                            $key = Util::getYMDString( $content );
                            if( ! isset( $output[$key] )) {
                                $output[$key] = 1;
                            }
                            else {
                                $output[$key] += 1;
                            }
                        }
                        else {
                            foreach( $content as $partKey => $partValue ) {
                                if( ! isset( $output[$partKey] )) {
                                    $output[$partKey] = $partValue;
                                }
                                else {
                                    $output[$partKey] += $partValue;
                                }
                            }
                        }
                    } // end elseif( is_array( $content )) {
                    elseif( ! isset( $output[$content] )) {
                        $output[$content] = 1;
                    }
                    else {
                        $output[$content] += 1;
                    }
                } // end foreach( $this->components as $cix => $component)
                if( ! empty( $output )) {
                    ksort( $output );
                }
                return $output;
                break;
            case Util::$CALSCALE:
                return ( ! empty( $this->calscale )) ? $this->calscale : false;
                break;
            case Util::$METHOD:
                return ( ! empty( $this->method )) ? $this->method : false;
                break;
            case Util::$PRODID:
                if( empty( $this->prodid )) {
                    $this->makeProdid();
                }
                return $this->prodid;
                break;
            case Util::$VERSION:
                return ( ! empty( $this->version )) ? $this->version : false;
                break;
            default:
                if( $propName != Util::$X_PROP ) {
                    if( ! isset( $this->xprop[$propName] )) {
                        return false;
                    }
                    return ( $inclParam )
                        ? [ $propName, $this->xprop[$propName] ]
                        : [ $propName, $this->xprop[$propName][Util::$LCvalue] ];
                }
                else {
                    if( empty( $this->xprop )) {
                        return false;
                    }
                    $xpropno = 0;
                    foreach( $this->xprop as $xpropKey => $xpropValue ) {
                        if( $propix == $xpropno ) {
                            return ( $inclParam )
                                ? [ $xpropKey, $xpropValue ]
                                : [ $xpropKey, $xpropValue[Util::$LCvalue] ];
                        }
                        else {
                            $xpropno++;
                        }
                    }
                    unset( $this->propix[$propName] );
                    return false; // not found ??
                }
        }
    }

    /**
     * General Vcalendar set property method
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.22.23 - 2017-04-09
     * @param mixed $args variable number of function arguments,
     *                    first argument is ALWAYS component name,
     *                    second ALWAYS component value!
     * @return mixed array|string|bool
     */
    public function setProperty( $args ) {
        $numargs = func_num_args();
        if( 1 > $numargs ) {
            return false;
        }
        $arglist = func_get_args();
        switch( strtoupper( $arglist[0] )) {
            case Util::$CALSCALE:
                return $this->setCalscale( $arglist[1] );
            case Util::$METHOD:
                return $this->setMethod( $arglist[1] );
            case Util::$VERSION:
                return $this->setVersion( $arglist[1] );
            default:
                if( ! isset( $arglist[1] )) {
                    $arglist[1] = null;
                }
                if( ! isset( $arglist[2] )) {
                    $arglist[2] = null;
                }
                return $this->setXprop( $arglist[0], $arglist[1], $arglist[2] );
        }
    }

    /**
     * Add calendar component to Vcalendar
     *
     * alias to setComponent
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  1.x.x - 2007-04-24
     * @param object $component calendar component
     * @return static
     */
    public function addComponent( $component ) {
        $this->setComponent( $component );
        return $this;
    }

    /**
     * Return clone of calendar component
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param mixed $arg1 ordno/component type/ component uid
     * @param mixed $arg2 ordno if arg1 = component type
     * @return mixed CalendarComponent|bool (false on error)
     */
    public function getComponent( $arg1 = null, $arg2 = null ) {
        $index = $argType = null;
        switch( true ) {
            case ( is_null( $arg1 )) : // first or next in component chain
                $argType = self::$INDEX;
                if( isset( $this->compix[self::$INDEX] )) {
                    $this->compix[self::$INDEX] = $this->compix[self::$INDEX] + 1;
                }
                else {
                    $this->compix[self::$INDEX] = 1;
                }
                $index = $this->compix[self::$INDEX];
                break;
            case ( is_array( $arg1 )) : // [ *[propertyName => propertyValue] ]
                $arg2 = implode( Util::$MINUS, array_keys( $arg1 ));
                if( isset( $this->compix[$arg2] )) {
                    $this->compix[$arg2] = $this->compix[$arg2] + 1;
                }
                else {
                    $this->compix[$arg2] = 1;
                }
                $index = $this->compix[$arg2];
                break;
            case ( ctype_digit((string) $arg1 )) : // specific component in chain
                $argType      = self::$INDEX;
                $index        = (int) $arg1;
                $this->compix = [];
                break;
            case ( Util::isCompInList( $arg1, Util::$MCOMPS ) &&
                ( 0 != strcasecmp( $arg1, self::VALARM ))) : // object class name
                unset( $this->compix[self::$INDEX] );
                $argType = strtolower( $arg1 );
                if( is_null( $arg2 )) {
                    if( isset( $this->compix[$argType] )) {
                        $this->compix[$argType] = $this->compix[$argType] + 1;
                    }
                    else {
                        $this->compix[$argType] = 1;
                    }
                    $index = $this->compix[$argType];
                }
                elseif( isset( $arg2 ) && ctype_digit((string) $arg2 )) {
                    $index = (int) $arg2;
                }
                break;
            case ( is_string( $arg1 )) : // assume UID as 1st argument
                if( is_null( $arg2 )) {
                    if( isset( $this->compix[$arg1] )) {
                        $this->compix[$arg1] = $this->compix[$arg1] + 1;
                    }
                    else {
                        $this->compix[$arg1] = 1;
                    }
                    $index = $this->compix[$arg1];
                }
                elseif( isset( $arg2 ) && ctype_digit((string) $arg2 )) {
                    $index = (int) $arg2;
                }
                break;
        } // end switch( true )
        if( isset( $index )) {
            $index -= 1;
        }
        $ckeys = array_keys( $this->components );
        if( ! empty( $index ) && ( $index > end( $ckeys ))) {
            return false;
        }
        $cix1gC = 0;
        foreach( $ckeys as $cix ) {
            if( empty( $this->components[$cix] )) {
                continue;
            }
            if(( self::$INDEX == $argType ) && ( $index == $cix )) {
                return clone $this->components[$cix];
            }
            elseif( 0 == strcasecmp( $argType, $this->components[$cix]->compType )) {
                if( $index == $cix1gC ) {
                    return clone $this->components[$cix];
                }
                $cix1gC++;
            }
            elseif( is_array( $arg1 )) { // [ *[propertyName => propertyValue] ]
                $hit  = [];
                foreach( $arg1 as $propName => $propValue ) {
                    if( ! Util::isPropInList( $propName, Util::$DATEPROPS ) &&
                        ! Util::isPropInList( $propName, Util::$OTHERPROPS )) {
                        continue;
                    }
                    if( Util::isPropInList( $propName, Util::$MPROPS1 )) { // multiple occurrence
                        $propValues = [];
                        $this->components[$cix]->getProperties( $propName, $propValues );
                        $hit[]      = ( \in_array( $propValue, \array_keys( $propValues )));
                        continue;
                    } // end   if(.. .// multiple occurrence
                    if( false === ( $value = $this->components[$cix]->getProperty( $propName ))) { // single occurrence
                        $hit[] = false; // missing property
                        continue;
                    }
                    if( Util::$SUMMARY == $propName ) { // exists within (any case)
                        $hit[] = ( false !== \stripos( $value, $propValue )) ? true : false;
                        continue;
                    }
                    if( Util::isPropInList( $propName, Util::$DATEPROPS )) {
                        $valueDate = Util::getYMDString( $value );
                        if( 8 < strlen( $propValue )) {
                            if( isset( $value[Util::$LCHOUR] )) {
                                if( Util::$T == substr( $propValue, 8, 1 )) {
                                    $propValue = str_replace( Util::$T, null, $propValue );
                                }
                                $valueDate .= Util::getHisString( $value );
                            }
                            else {
                                $propValue = substr( $propValue, 0, 8 );
                            }
                        }
                        $hit[] = ( $propValue == $valueDate ) ? true : false;
                        continue;
                    }
                    elseif( ! is_array( $value )) {
                        $value = [ $value ];
                    }
                    foreach( $value as $part ) {
                        $part = ( false !== strpos( $part, Util::$COMMA )) ? explode( Util::$COMMA, $part ) : [ $part ];
                        foreach( $part as $subPart ) {
                            if( $propValue == $subPart ) {
                                $hit[] = true;
                                continue 3;
                            }
                        }
                    } // end foreach( $value as $part )
                    $hit[] = false; // no hit in property
                } // end  foreach( $arg1 as $propName => $propValue )
                if( in_array( true, $hit )) {
                    if( $index == $cix1gC ) {
                        return clone $this->components[$cix];
                    }
                    $cix1gC++;
                }
            } // end elseif( is_array( $arg1 )) { // [ *[propertyName => propertyValue] ]
            elseif( ! $argType &&
                ( $arg1 == $this->components[$cix]->getProperty( Util::$UID ))) {
                if( $index == $cix1gC ) {
                    return clone $this->components[$cix];
                }
                $cix1gC++;
            }
        } // end foreach( $ckeys as $cix )
        /* not found.. . */
        $this->compix = [];
        return false;
    }

    /**
     * Return Vevent object instance, Vcalendar::newComponent() wrapper
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @return object
     */
    public function newVevent() {
        return $this->newComponent( self::VEVENT );
    }

    /**
     * Return Vtodo object instance, Vcalendar::newComponent() wrapper
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @return object
     */
    public function newVtodo() {
        return $this->newComponent( self::VTODO );
    }

    /**
     * Return Vjournal object instance, Vcalendar::newComponent() wrapper
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @return object
     */
    public function newVjournal() {
        return $this->newComponent( self::VJOURNAL );
    }

    /**
     * Return Vfreebusy object instance, Vcalendar::newComponent() wrapper
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @return object
     */
    public function newVfreebusy() {
        return $this->newComponent( self::VFREEBUSY );
    }

    /**
     * Return Vtimezone object instance, Vcalendar::newComponent() wrapper
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @return object
     */
    public function newVtimezone() {
        return $this->newComponent( self::VTIMEZONE );
    }

    /**
     * Replace calendar component in Vcalendar
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param object $component calendar component
     * @return bool
     */
    public function replaceComponent( $component ) {
        if( Util::isCompInList( $component->compType, Util::$VCOMPS )) {
            return $this->setComponent( $component, $component->getProperty( Util::$UID ));
        }
        if(( self::VTIMEZONE != $component->compType ) ||
            ( false === ( $tzid = $component->getProperty( Util::$TZID )))) {
            return false;
        }
        foreach( $this->components as $cix => $comp ) {
            if( self::VTIMEZONE != $component->compType ) {
                continue;
            }
            if( $tzid == $comp->getProperty( Util::$TZID )) {
                $component->compix      = [];
                $this->components[$cix] = $component;
                return true;
            }
        }
        return false;
    }

    /**
     * Return selected components from calendar on date or selectOption basis
     *
     * DTSTART MUST be set for every component.
     * No date check.
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param mixed $startY                  (int) start Year,  default current Year
     *                                       ALT. (obj) start date (datetime)
     *                                       ALT. array selecOptions ( *[ <propName> => <uniqueValue> ] )
     * @param mixed $startM                  (int) start Month, default current Month
     *                                       ALT. (obj) end date (datetime)
     * @param int   $startD                  start Day,   default current Day
     * @param int   $endY                    end   Year,  default $startY
     * @param int   $endM                    end   Month, default $startM
     * @param int   $endD                    end   Day,   default $startD
     * @param mixed $cType                   calendar component type(-s), default false=all else string/array type(-s)
     * @param bool  $flat                    false (default) => output : array[Year][Month][Day][]
     *                                       true            => output : array[] (ignores split)
     * @param bool  $any                     true (default) - select component(-s) that occurs within period
     *                                       false          - only component(-s) that starts within period
     * @param bool  $split                   true (default) - one component copy every DAY it occurs during the
     *                                       period (implies flat=false)
     *                                       false          - one occurance of component only in output array
     * @return mixed   array on success, bool false on error
     */
    public function selectComponents(
        $startY = null,
        $startM = null,
        $startD = null,
        $endY   = null,
        $endM   = null,
        $endD   = null,
        $cType  = null,
        $flat   = null,
        $any    = null,
        $split  = null
    ) {
        return UtilSelect::selectComponents( $this,
                                             $startY, $startM, $startD,
                                             $endY, $endM, $endD,
                                             $cType, $flat, $any, $split
        );
    }

    /**
     * Sort iCal compoments
     *
     * Ascending sort on properties (if exist) x-current-dtstart, dtstart,
     * x-current-dtend, dtend, x-current-due, due, duration, created, dtstamp, uid if called without arguments,
     * otherwise sorting on specific (argument) property values
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26 - 2018-11-10
     * @param string $sortArg
     */
    public function sort( $sortArg = null ) {
        static $SORTER = [ 'Kigkonsult\Icalcreator\Util\VcalendarSortHandler', 'cmpfcn' ];
        if( 2 > $this->countComponents()) {
            return;
        }
        if( ! is_null( $sortArg )) {
            $sortArg = strtoupper( $sortArg );
            if( ! Util::isPropInList( $sortArg, Util::$OTHERPROPS ) &&
                ( Util::$DTSTAMP != $sortArg )) {
                $sortArg = null;
            }
        }
        foreach( $this->components as $cix => $component ) {
            VcalendarSortHandler::setSortArgs( $this->components[$cix], $sortArg );
        }
        usort( $this->components, $SORTER );
    }

    /**
     * Parse iCal text/file into Vcalendar, components, properties and parameters
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.26  2018-11-10
     * @param mixed    $unparsedtext strict rfc2445 formatted, single property string or array of property strings
     * @param resource $context      PHP resource context
     * @return bool    true on success, false on parse error
     */
    public function parse( $unparsedtext = false, $context = null ) {
        static $NLCHARS         = '\n';
        static $BEGIN_VCALENDAR = 'BEGIN:VCALENDAR';
        static $END_VCALENDAR   = 'END:VCALENDAR';
        static $ENDSHORTS       = [ 'END:VE', 'END:VF', 'END:VJ', 'END:VT' ];
        static $BEGIN_VEVENT    = 'BEGIN:VEVENT';
        static $BEGIN_VFREEBUSY = 'BEGIN:VFREEBUSY';
        static $BEGIN_VJOURNAL  = 'BEGIN:VJOURNAL';
        static $BEGIN_VTODO     = 'BEGIN:VTODO';
        static $BEGIN_VTIMEZONE = 'BEGIN:VTIMEZONE';
        static $TRIMCHARS       = "\x00..\x1F";
        static $CALPROPNAMES    = null;
        static $VERSIONPRODID   = null;
        if( is_null( $CALPROPNAMES )) {
            $CALPROPNAMES = [
                Util::$CALSCALE,
                Util::$METHOD,
                Util::$PRODID,
                Util::$VERSION,
            ];
        }
        if( is_null( $VERSIONPRODID )) {
            $VERSIONPRODID = [
                Util::$VERSION,
                Util::$PRODID,
            ];
        }
        $arrParse = false;
        if( empty( $unparsedtext )) {
            /* directory+filename is set previously
               via setConfig url or directory+filename  */
            if( false === ( $file = $this->getConfig( Util::$URL ))) {
                if( false === ( $file = $this->getConfig( Util::$DIRFILE ))) {
                    return false;
                }               /* err 1 */
                if( ! \is_file( $file )) {
                    return false;
                }               /* err 2 */
                if( ! \is_readable( $file )) {
                    return false;
                }               /* err 3 */
            }
            if( ! empty( $context ) && filter_var( $file, FILTER_VALIDATE_URL )) {
                If( false === ( $rows = file_get_contents( $file, false, $context ))) {
                    return false;
                }               /* err 6 */
            }
            elseif( false === ( $rows = file_get_contents( $file ))) {
                return false;
            }                 /* err 5 */
        } // end if( empty( $unparsedtext ))
        elseif( is_array( $unparsedtext )) {
            $rows     = implode( $NLCHARS . Util::$CRLF, $unparsedtext );
            $arrParse = true;
        }
        else { // string
            $rows = $unparsedtext;
        }
        /* fix line folding */
        $rows = Util::convEolChar( $rows );
        if( $arrParse ) {
            foreach( $rows as $lix => $row ) {
                $rows[$lix] = Util::trimTrailNL( $row );
            }
        }
        /* skip leading (empty/invalid) lines
           (and remove leading BOM chars etc) */
        foreach( $rows as $lix => $row ) {
            if( false !== \stripos( $row, $BEGIN_VCALENDAR )) {
                $rows[$lix] = $BEGIN_VCALENDAR;
                break;
            }
            unset( $rows[$lix] );
        }
        if( 3 > count( $rows ))           /* err 10 */ {
            return false;
        }
        /* skip trailing empty lines and ensure an end row */
        $lix = array_keys( $rows );
        $lix = end( $lix );
        while( 3 < $lix ) {
            $tst = \trim( $rows[$lix] );
            if(( $NLCHARS == $tst ) || empty( $tst )) {
                unset( $rows[$lix] );
                $lix--;
                continue;
            }
            if( false === stripos( $rows[$lix], $END_VCALENDAR )) {
                $rows[] = $END_VCALENDAR;
            }
            else {
                $rows[$lix] = $END_VCALENDAR;
            }
            break;
        }
        $comp    = $this;
        $calSync = $compSync = 0;
        /* identify components and update unparsed data for components */
        $compClosed = true; // used in case of missing END-comp-row
        $config     = $this->getConfig();
        foreach( $rows as $lix => $row ) {
            switch( true ) {
                case ( 0 == strcasecmp( $BEGIN_VCALENDAR, substr( $row, 0, 15 ))) :
                    $calSync++;
                    break;
                case ( 0 == strcasecmp( $END_VCALENDAR, substr( $row, 0, 13 ))) :
                    if( 0 < $compSync ) {
                        $this->components[] = $comp;
                    }
                    $compSync -= 1;
                    $calSync  -= 1;
                    if( 0 != $calSync ) {
                        return false;
                    }                 /* err 20 */
                    break 2;
                case ( in_array( strtoupper( substr( $row, 0, 6 )), $ENDSHORTS )) :
                    $this->components[] = $comp;
                    $compSync  -= 1;
                    $compClosed = true;
                    break;
                case ( 0 == strcasecmp( $BEGIN_VEVENT, substr( $row, 0, 12 ))) :
                    if( ! $compClosed ) {
                        $this->components[] = $comp;
                        $compSync -= 1;
                    }
                    $comp = new Vevent( $config );
                    $compSync  += 1;
                    $compClosed = false;
                    break;
                case ( 0 == strcasecmp( $BEGIN_VFREEBUSY, substr( $row, 0, 15 ))) :
                    if( ! $compClosed ) {
                        $this->components[] = $comp;
                        $compSync -= 1;
                    }
                    $comp = new Vfreebusy( $config );
                    $compSync += 1;
                    $compClosed = false;
                    break;
                case ( 0 == strcasecmp( $BEGIN_VJOURNAL, substr( $row, 0, 14 ))) :
                    if( ! $compClosed ) {
                        $this->components[] = $comp;
                        $compSync -= 1;
                    }
                    $comp = new Vjournal( $config );
                    $compSync += 1;
                    $compClosed = false;
                    break;
                case ( 0 == strcasecmp( $BEGIN_VTODO, substr( $row, 0, 11 ))) :
                    if( ! $compClosed ) {
                        $this->components[] = $comp;
                        $compSync -= 1;
                    }
                    $comp = new Vtodo( $config );
                    $compSync  += 1;
                    $compClosed = false;
                    break;
                case ( 0 == strcasecmp( $BEGIN_VTIMEZONE, substr( $row, 0, 15 ))) :
                    if( ! $compClosed ) {
                        $this->components[] = $comp;
                        $compSync -= 1;
                    }
                    $comp = new Vtimezone( $config );
                    $compSync  += 1;
                    $compClosed = false;
                    break;
                default : /* update component with unparsed data */
                    $comp->unparsed[] = $row;
                    break;
            } // switch( true )
        } // end foreach( $rows as $lix => $row )
        /* parse data for calendar (this) object */
        if( isset( $this->unparsed ) &&
            \is_array( $this->unparsed ) &&
            ( \count( $this->unparsed ) > 0 )) {
            /* concatenate property values spread over several rows */
            foreach( Util::concatRows( $this->unparsed ) as $lx => $row ) {
                /* split property name  and  opt.params and value */
                list( $propName, $row ) = Util::getPropName( $row );
                if( ! Util::isXprefixed( $propName ) &&
                    ! Util::isPropInList( strtoupper( $propName ), $CALPROPNAMES ) &&   // skip non standard property names
                    Util::isPropInList( strtoupper( $propName ), $VERSIONPRODID ))  { // ignore version/prodid properties
                    continue;
                }
                /* separate attributes from value */
                Util::splitContent( $row, $propAttr );
                /* update Property */
                $this->setProperty( $propName, Util::strunrep( rtrim( $row, $TRIMCHARS )), $propAttr );
            } // end foreach( $propRows as $lx => $row )
        } // end if( is_array( $this->unparsed.. .
        unset( $this->unparsed );
        /* parse Components */
        if( $this->countComponents() > 0 ) {
            foreach( $this->components as $ckey => $component ) {
                if( ! empty( $this->components[$ckey] ) &&
                    ! empty( $this->components[$ckey]->unparsed )) {
                    $this->components[$ckey]->parse();
                }
            }
        }
        else {
            return false;
        }                   /* err 91 or something.. . */
        return true;
    }

    /**
     * Return formatted output for calendar object instance
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.21.07 - 2015-03-31
     * @return string
     */
    public function createCalendar() {
        static $BEGIN_VCALENDAR = "BEGIN:VCALENDAR";
        static $END_VCALENDAR   = "END:VCALENDAR";
        $calendar  = $BEGIN_VCALENDAR . Util::$CRLF;
        $calendar .= $this->createVersion();
        $calendar .= $this->createProdid();
        $calendar .= $this->createCalscale();
        $calendar .= $this->createMethod();
        $calendar .= $this->createXprop();
        $config    = $this->getConfig();
        foreach( $this->components as $cix => $component ) {
            if( ! empty( $component )) {
                $this->components[$cix]->setConfig( $config, false, true );
                $calendar .= $this->components[$cix]->createComponent();
            }
        }
        return $calendar . $END_VCALENDAR . Util::$CRLF;
    }

    /**
     * Save calendar content in a file
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.21.5 - 2015-03-29
     * @return bool true on success, false on error
     */
    public function saveCalendar() {
        $output = $this->createCalendar();
        if( false === ( $dirfile = $this->getConfig( Util::$URL ))) {
            $dirfile = $this->getConfig( Util::$DIRFILE );
        }
        return ( false === file_put_contents( $dirfile, $output, LOCK_EX )) ? false : true;
    }

    /**
     * Return created, updated and/or parsed calendar,
     * sending a HTTP redirect header.
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.6 - 2017-04-13
     * @param bool $utf8Encode
     * @param bool $gzip
     * @param bool $cdType true : Content-Disposition: attachment... (default), false : ...inline...
     * @return bool true on success, false on error
     */
    public function returnCalendar( $utf8Encode = false, $gzip = false, $cdType = true ) {
        return UtilRedirect::returnCalendar( $this, $utf8Encode, $gzip, $cdType );
    }

    /**
     * If recent version of calendar file exists (default one hour), an HTTP redirect header is sent
     * else false is returned.
     *
     * @author Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
     * @since  2.23.6 - 2017-04-13
     * @param int  $timeout default 3600 sec
     * @param bool $cdType  true : Content-Disposition: attachment... (default), false : ...inline...
     * @return bool true on success, false on error
     */
    public function useCachedCalendar( $timeout = 3600, $cdType = true ) {
        return UtilRedirect::useCachedCalendar( $this, $timeout, $cdType );
    }
}
