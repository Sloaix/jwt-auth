<?php

use Illuminate\Support\HtmlString;

if (! function_exists('jwt_field')) {
    /**
     * Generate a CSRF token form field.
     *
     * @return \Illuminate\Support\HtmlString
     */
    function jwt_field()
    {
        return new HtmlString('<input type="hidden" name="_token" value="'.jwt_token().'">');
    }
}

if (! function_exists('jwt_token')) {
    /**
     * Get the CSRF token value.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    function jwt_token()
    {
        $token = app('request')->jwtToken();

        if (isset($token)) {
            return $token;
        }

        throw new RuntimeException('JWT Token not set.');
    }
}