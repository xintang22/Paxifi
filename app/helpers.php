<?php

if ( ! function_exists('cloudfront_asset'))
{
    /**
     * Generate a url for the application.
     *
     * @param  string  $path
     * @param  mixed   $parameters
     * @param  bool    $secure
     * @return string
     */
    function cloudfront_asset($path = null, $parameters = array(), $secure = null)
    {
        return getenv('CLOUDFRONT_URL') . '/' . $path;
    }
}