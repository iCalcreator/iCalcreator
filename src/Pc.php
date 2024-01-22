<?php
/**
 * iCalcreator, the PHP class package managing iCal (rfc2445/rfc5445) calendar information.
 *
 * This file is a part of iCalcreator.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2007-2024 Kjell-Inge Gustafsson, kigkonsult AB, All rights reserved
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

use ArrayObject;

use DateTimeInterface;
use Kigkonsult\Icalcreator\Util\StringFactory;

use function array_keys;
use function in_array;
use function is_array;
use function is_bool;
use function is_int;
use function is_scalar;
use function is_string;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strtoupper;
use function substr_count;
use function trim;
use function var_export;

/**
 * Property Contents
 *
 * @since 2.41.89 2024-01-20
 */
class Pc extends ArrayObject
{
    /**
     * @var string  Component property value, (array) key
     */
    public static string $LCvalue       = 'value';

    /**
     * @var string  Component property parameters, (array) key
     */
    public static string $LCparams      = 'params';

    /**
     * @var mixed[]
     */
    private static array $propTmpl      = [ 'value' => null, 'params' => [] ];

    /**
     * @var string
     */
    private static string $iteratorTmpl = 'ArrayIterator';

    /**
     * The property value
     *
     * @var mixed
     */
    public mixed $value;

    /**
     * The property parameters
     *
     * @var null|int|mixed[]
     */
    public null|int|array $params;

    /**
     * Class constructor
     *
     * @overrides
     * @param null|mixed[] $array           ignored
     * @param null|int $flags             ignored
     * @param null|string $iteratorClass  ArrayIterator
     */
    public function __construct(
        ? array $array = [],
        ? int $flags = 0,
        ? string $iteratorClass = 'ArrayIterator'
    )
    {
        parent::__construct(
            self::$propTmpl,
            ArrayObject::ARRAY_AS_PROPS,
            self::$iteratorTmpl
        );
    }

    /**
     * Class factory method
     *
     * @param mixed|null $value      ant property value or a Pc
     * @param null|string[] $params
     * @return Pc
     */
    public static function factory( mixed $value = null, ? array $params = [] ) : Pc
    {
        if( $value instanceof Pc ) {
            return clone $value;
        }
        $instance = new self();
        if( null !== $value ) {
            $instance->setValue( $value );
        }
        if( ! empty( $params )) {
            $instance->setParams( $params );
        }
        return $instance;
    }

    /**
     * Inherited but 'closed' methods
     */
    public function exchangeArray( mixed $array ) : array { return $this->getArrayCopy(); }
    public function setFlags( mixed $flags ) : void {}
    public function setIteratorClass( mixed $iteratorClass ) : void {}

    /**
     * Value methods
     */

    /**
     * @return mixed[]
     */
    public function getAsArray() : array
    {
        return $this->getArrayCopy();
    }

    /**
     * Return bool true if property value is set, i.e not null
     *
     * @return bool
     */
    public function isset() : bool
    {
        return ( null !== $this->value );
    }

    /**
     * Return value, false if not set
     *
     * @return mixed
     */
    public function getValue() : mixed
    {
        return $this->value ?? false;
    }

    /**
     * Set to 'empty' state
     *
     * @return Pc
     */
    public function setEmpty() : Pc
    {
        static $SP0   = '';
        $this->value  = $SP0;
        $this->params = [];
        return $this;
    }

    /**
     * Set value
     *
     * @param mixed|null $value
     * @return Pc
     */
    public function setValue( mixed $value = null ) : Pc
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Parameters methods
     */

    /**
     * Return all parameters (array) or single parameter key value, null if not exists
     *
     * @param null|string $pKey   parameter key
     * @param bool $asXparamKey   only if not empty pKey, opt do X-prefix pkey
     * @return null|int|string|string[]
     * @since 2.41.88 - 2024-01-18
     */
    public function getParams( ? string $pKey = null, ? bool $asXparamKey = false ) : null|int|string|array
    {
        if( null === $pKey ) {
            return $this->params;
        }
        $pKey = $asXparamKey ? self::setXPrefix( $pKey ) : strtoupper( $pKey );
        return $this->hasParamKey( $pKey ) ? $this->params[$pKey] : null;
    }

    /**
     * Return value of the parameter key VALUE or null
     *
     * @return string|null
     */
    public function getValueParam() : null|string
    {
        return $this->params[IcalInterface::VALUE] ?? null;
    }

    /**
     * Return bool true if params has key, opt with spec. value
     *
     * @param string $pKey
     * @param null|string $pValue
     * @return bool
     */
    public function hasParamKey( string $pKey, ? string $pValue = null ) : bool
    {
        $key = strtoupper( $pKey);
        return ( null === $pValue )
            ? isset( $this->params[$key] )
            : ( isset( $this->params[$key] ) && ( $pValue === $this->params[$key] ));
    }

    /**
     * Return bool true if params has ISLOCALTIME key
     *
     * @return bool
     */
    public function hasParamIsLocalTime() : bool
    {
        return $this->hasParamKey( IcalInterface::ISLOCALTIME );
    }

    /**
     * Return bool true if params has VALUE key, opt with spec. value
     *
     * @param null|string $pValue
     * @return bool
     */
    public function hasParamValue( ? string $pValue = null ) : bool
    {
        return $this->hasParamKey( IcalInterface::VALUE, $pValue );
    }

    /**
     * Return bool true if params has X-key, opt with spec. value
     *
     * @param string $pKey
     * @param null|string $pValue
     * @return bool
     */
    public function hasXparamKey( string $pKey, ? string $pValue = null ) : bool
    {
        return $this->hasParamKey( self::setXPrefix( $pKey), $pValue );
    }

    /**
     * Return array, parameter keys
     *
     * @return string[]
     */
    public function getParamKeys() : array
    {
        return array_keys( $this->params );
    }

    /**
     * Remove parameter key, opt only with spec. value
     *
     * @param null|string $pKey
     * @param null|string $pValue
     * @return Pc
     */
    public function removeParam( ? string $pKey = null, ? string $pValue = null  ) : Pc
    {
        if(( null === $pKey ) && ( null === $pValue )) {
            $this->params = [];
        }
        elseif(( null === $pValue ) || ( $this->hasParamKey( $pKey, $pValue ))) {
            unset( $this->params[strtoupper( $pKey )] );
        }
        return $this;
    }

    /**
     * Remove parameter X-key
     *
     * @param string $pKey
     * @return Pc
     */
    public function removeXparam( string $pKey ) : Pc
    {
        return $this->removeParam( self::setXPrefix( $pKey ));
    }

    /**
     * Set parameter key/value, default overwrite (key always set to uppercase)
     *
     * @param string $pKey
     * @param mixed $pValue
     * @param null|bool $overwrite
     * @return Pc
     * @since 2.41.89 2024-01-20
     */
    public function addParam( string $pKey, mixed $pValue, ? bool $overwrite = true ) : Pc
    {
        self::conformParamKeyValue( $pKey, $pValue );
        if( $overwrite || ! $this->hasParamKey( $pKey )) {
            $this->params[$pKey] = $pValue;
        }
        return $this;
    }

    /**
     * Set value for parameter key VALUE, default overwrite (key always set to uppercase)
     *
     * @param mixed $pValue
     * @param null|bool $overwrite
     * @return Pc
     */
    public function addParamValue( mixed $pValue, ? bool $overwrite = true ) : Pc
    {
        return $this->addParam( IcalInterface::VALUE, $pValue, $overwrite );
    }

    /**
     * Set parameter X-key/value, overwrite if exists (key always set to uppercase, x-prefixed if missing)
     *
     * @param string $pKey
     * @param mixed $pValue
     * @param null|bool $overwrite
     * @return Pc
     */
    public function addXparam( string $pKey, mixed $pValue, ? bool $overwrite = true  ) : Pc
    {
        return $this->addParam( self::setXPrefix( $pKey ), $pValue, $overwrite );
    }

    /**
     * Set parameter X-key/value array, default overwrite (key always set to uppercase )
     *
     * Conform parameters first
     *
     * @param string[] $params
     * @param null|bool $overwrite
     * @return Pc
     * @since 2.41.85 2024-01-19
     */
    public function setParams( array $params, ? bool $overwrite = true  ) : Pc
    {
        foreach( $params as $kPkey => $pValue ) {
            $this->addParam( $kPkey, $pValue, $overwrite );
        }
        return $this;
    }

    /**
     * @return string
     * @since 2.41.85 2024-01-16
     */
    public function __toString() : string
    {
        static $FORMAT = 'Ymd-His e';
        static $SP0    = '';
        static $GTEQ   = '=>';
        static $CMSP1  = ', ';
        $str = match ( true ) {
            null === $this->value     => $SP0,
            is_scalar( $this->value ) => (string) $this->value,
            $this->value instanceof DateTimeInterface => $this->value->format( $FORMAT ),
            default                   => str_replace( PHP_EOL, $SP0, var_export( $this->value, true  )),
        }; // end match
        if( is_array( $this->params )) {
            foreach( $this->params as $k => $v ) {
                $str .= $CMSP1 . $k . $GTEQ . $v;
            }
        }
        return rtrim( $str );
    }

    /**
     * Parameters static key methods
     */

    /**
     * @var string
     */
    protected static string $xPrefix = 'X-';

    /**
     * Return bool true if (key-)value is X-prefixed
     *
     * @param string $key
     * @return bool
     */
    public static function isXprefixed( string $key ) : bool
    {
        return str_starts_with( strtoupper( $key ), self::$xPrefix );
    }

    /**
     * Return X-prefixed key in upper case
     *
     * @param string $key
     * @return string
     */
    public static function setXPrefix( string $key ) : string
    {
        return strtoupper( self::isXprefixed( $key ) ? $key : self::$xPrefix . $key );
    }

    /**
     * Return string with opt. leading x-prefix removed
     *
     * @param string $key
     * @return string
     */
    public static function unsetXPrefix( string $key ) : string
    {
        return self::isXprefixed( $key ) ? substr( $key, 2 ) : $key;
    }

    /**
     * Parameters static value methods
     */

    private static string $CIRCUMFLEX = '^';
    private static string $CFN        = '^n';
    private static string $CFCF       = '^^';
    private static string $CFSQ       = "^'";
    private static string $CFQQ       = '^"';
    private static string $NLCHARS    = '\n';

    /**
     * Return parsed parameter VALUE with opt. circumflex deformatted as of rfc6868
     *
     * the character sequence ^n (U+005E, U+006E) is decoded into an
     *    appropriate formatted line break according to the type of system
     *    being used
     * the character sequence ^^ (U+005E, U+005E) is decoded into the ^ character (U+005E)
     * the character sequence ^' (U+005E, U+0027) is decoded into the " character (U+0022)
     * if a ^ (U+005E) character is followed by any character other than the ones above,
     *    parsers MUST leave both the ^ and the following character in place
     *
     * Also ^" and ' are decoded into the " character (U+0022), NOT rfc6868
     *
     * @param mixed $value
     * @return mixed
     * @since 2.41.89 2024-01-20
     */
    public static function circumflexQuoteParse( mixed $value ) : mixed
    {
        static $SQUOTE = "'";
        if( ! is_string( $value )) {
            return $value;
        }
        if( str_contains( $value, self::$CFN )) {
            $value = str_replace( self::$CFN, self::$NLCHARS, $value );
        }
        if( str_contains( $value, self::$CFCF )) {
            $value = str_replace( self::$CFCF, self::$CIRCUMFLEX, $value );
        }
        if( str_contains( $value, self::$CFSQ )) {
            $value = str_replace( self::$CFSQ, StringFactory::$QQ, $value );
        }
        if( str_contains( $value, self::$CFQQ )) {
            $value = str_replace( self::$CFQQ, StringFactory::$QQ, $value );
        }
        if( str_contains( $value, $SQUOTE ) && ( 0 === ( substr_count( $value, $SQUOTE ) % 2 ))) {
            $value = str_replace( $SQUOTE, StringFactory::$QQ, $value );
        }
        return $value;
    }
    /**
     * Return (conformed) iCal component property parameters
     *
     * Trim quoted values, default parameters may be set, if missing
     * Non-string values set to string
     *
     * @param string $pKey
     * @param mixed  $pValue
     * @return void
     * @since 2.41.89 2024-01-20
     */
    public static function conformParamKeyValue( string & $pKey, mixed & $pValue ) : void
    {
        static $TRUEFALSEARR = [ IcalInterface::TRUE,  IcalInterface::FALSE ];
        $pKey   = strtoupper( $pKey );
        $pValue = self::circumflexQuoteParse( $pValue );
        switch( $pKey ) {
            case IcalInterface::ISLOCALTIME :
                break;
            case IcalInterface::DERIVED :
                if( is_bool( $pValue )) {
                    $pValue = $pValue ? IcalInterface::TRUE : IcalInterface::FALSE;
                }
                elseif( in_array( strtoupper( $pValue ), $TRUEFALSEARR, true )) {
                    $pValue = strtoupper( $pValue );
                }
                break;
            case IcalInterface::ORDER :
                if( ! is_int( $pValue )) {
                    $pValue = (int) $pValue;
                }
                if( 1 > $pValue ) {
                    $pValue = 1;
                }
                break;
            case IcalInterface::VALUE :
                $pValue = strtoupper( $pValue );
                break;
            default :
                if( is_array( $pValue )) {
                    foreach( $pValue as $pkey2 => $pValue2 ) {
                        $pValue[$pkey2] = self::conformValue( $pValue2 );
                    }
                    break;
                }
                $pValue = self::conformValue( $pValue );
                break;
        } // end switch
    }

    /**
     * @param mixed $value
     * @return string
     * @since 2.41.89 2024-01-20
     */
    private static function conformValue ( mixed $value ) : string
    {
        static $ONE = '1';
        return match ( true ) {
            is_string( $value ) => trim( $value, StringFactory::$QQ ),
            is_bool( $value )   => $value ? $ONE : StringFactory::$ZERO,
            default             => (string) $value,
        }; // end match
    }
}
