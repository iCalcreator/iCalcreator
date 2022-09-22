<?php

namespace Kigkonsult\Icalcreator;

use ArrayObject;

/**
 * Property Contents
 *
 * @property mixed value
 * @property array params
 * @ince 2.41.65 - 2022-09-07
 */
class Pc extends ArrayObject
{
    /**
     * @var string  Component property value, key
     */
    public static string $LCvalue        = 'value';

    /**
     * @var string  Component property parameters, key
     */
    public static string $LCparams       = 'params';

    /**
     * @var string property value type (parameter key)
     */
    private static string $paramValueKey = IcalInterface::VALUE;

    /**
     * @var array
     */
    private static array $propTmpl       = [ 'value' => null, 'params' => [] ];

    /**
     * @var string
     */
    private static string $iteratorTmpl  = 'ArrayIterator';

    /**
     * Class constructor
     *
     * @overrides
     * @param array $array         ignored
     * @param int $flags             ignored
     * @param string $iteratorClass  ArrayIterator
     */
    public function __construct( $array = [], $flags = 0, $iteratorClass = 'ArrayIterator' )
    {
        parent::__construct( self::$propTmpl, ArrayObject::ARRAY_AS_PROPS, self::$iteratorTmpl );
    }

    /**
     * Class factory method
     *
     * @param mixed|null $value
     * @param array|null $params
     * @return Pc
     */
    public static function factory( mixed $value = null, ? array $params = [] ) : Pc
    {
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
     * Inherited 'closed' methods
     */
    public function exchangeArray( mixed $array ) : array { return $this->getArrayCopy(); }
    public function setFlags( mixed $flags ) : void {}
    public function setIteratorClass( mixed $iteratorClass ) : void {}

    /**
     * Value methods
     */

    /**
     * @return array
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
     * @return mixed
     * @ince 2.41.65 - 2022-09-07
     */
    public function getParams( ? string $pKey = null, ? bool $asXparamKey = false ) : mixed
    {
        if( null === $pKey ) {
            return $this->params;
        }
        $pKey = $asXparamKey ? self::setXPrefix( $pKey ) : strtoupper( $pKey );
        return $this->hasParamKey( $pKey )
            ? $this->params[$pKey]
            : null;
    }

    /**
     * Return value of the parameter key VALUE or null
     *
     * @return string|null
     */
    public function getValueParam() : null|string
    {
        return $this->params[self::$paramValueKey] ?? null;
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
     * Return bool true if params has VALUE key, opt with spec. value
     *
     * @param null|string $pValue
     * @return bool
     */
    public function hasParamValue( ? string $pValue = null ) : bool
    {
        return $this->hasParamKey( self::$paramValueKey, $pValue );
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
     */
    public function addParam( string $pKey, mixed $pValue, ? bool $overwrite = true ) : Pc
    {
        if( $overwrite || ! $this->hasParamKey( $pKey )) {
            $this->params[strtoupper( $pKey )] = $pValue;
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
        return $this->addParam( self::$paramValueKey, $pValue, $overwrite );
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
     * @param array $params
     * @param null|bool $overwrite
     * @return Pc
     */
    public function setParams( array $params, ? bool $overwrite = true  ) : Pc
    {
        foreach( $params as $kPkey => $pValue ) {
            $this->addParam( $kPkey, $pValue, $overwrite );
        }
        return $this;
    }

    /**
     * Parameters static methods
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
}
