<?php

namespace utility\validator;

/**
 * Class RegexValidator
 * @package utility\validator
 */
class RegexValidator extends ValidatorStrategy
{


    /**
     * Validation for regex
     *
     * @param string $name
     * @param string $value
     * @param string $regex
     * @param array $attr
     *
     * bool $attr['required']
     * string $attr['field']
     * string $attr['errors']['empty']
     * string $attr['errors']['regex']
     *
     * new RegexValidator( 'gender', $_POST['gender'], '/[a-z]+$/' )
     */
    public function __construct( $name, $value, $regex, array $attr = null )
    {
        $attr = !is_null( $attr ) ? $attr : array();
        $this->data['regex'] = $regex;
        $this->configValidatorGenericAttr( $name, $value, $attr );
    }


    /**
     * Perform validation
     *
     * @return bool
     */
    public function isValid()
    {

        if( empty( $this->data['value'] ) ){
            if( $this->data['required'] ){
                $this->messages = ( $this->data['errors']['empty'] ) ? $this->data['errors']['empty'] : $this->errorText( ValidatorStrategy::E_EMPTY, array( $this->data['field'] ) );
                return false;
            }
            return true;
        }

        if( !preg_match( $this->data['regex'], $this->data['value'] ) ){
            $this->messages = ( $this->data['errors']['regex'] ) ? $this->data['errors']['regex'] : $this->errorText( ValidatorStrategy::E_INVALID_CHARACTER, array( $this->data['field'] ) );
            return false;
        }

        return true;
    }


    /**
     * Config validator error attr
     *
     * @param array $attr
     * @return array
     */
    protected function configErrors( array $attr )
    {
        $cfg = array(
            'empty' => null, 'regex' => null
        );

        if( isset( $attr['errors'] ) and is_array( $attr['errors'] ) ){
            return array_merge( $cfg, $attr['errors'] );
        }

        return $cfg;
    }


}
