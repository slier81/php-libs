<?php
/**
 * Error Validator
 * use in Validator::invalidateValidation() to give user friendly error message after marked overall validation as failed
 */
class ErrorValidator extends ValidatorStrategy
{

    /**
     *
     * @access protected
     */
    protected $error_msg = null;


    /**
     * @param string $message
     */
    public function __construct( $message )
    {
        $this->configErrors( array( 'error' => $message ) );
        $this->isValid();
    }


    /**
     * Implement abstract method
     * @return boolean
     */
    public function isValid()
    {
        $this->messages = $this->data['errors']['error'];
        return false;
    }


    /**
     * Implement abstract method
     * @param array $attr
     */
    protected function configErrors( array $attr )
    {
        $this->data['errors']['error'] = $attr['error'];

    }


}