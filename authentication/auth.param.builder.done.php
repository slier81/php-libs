<?php

namespace utility\authentication;

class AuthParamBuilderDone
{


    /**
     * @var null|AuthParam
     */
    protected $auth_param = null;


    /**
     * @param AuthParam $auth_param
     */
    public function __construct( AuthParam $auth_param )
    {
        $this->auth_param = $auth_param;
    }


    /**
     * Completing build the AuthParam object
     */
    public function build()
    {
        return $this->auth_param;
    }

}
