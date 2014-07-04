<?php

use Paxifi\Store\Auth\Auth;

return array(

    'grant_types' => array(

        'password' => array(
            'class'            => 'League\OAuth2\Server\Grant\Password',
            'access_token_ttl' => 604800,
            'callback'         => function ($username, $password) {
                
                $credentials = array(
                    'email' => $username,
                    'password' => $password,
                );

                $valid = Auth::validate($credentials);

                if (!$valid) {
                    return false;
                }

                return Auth::getProvider()->retrieveByCredentials($credentials)->id;
            }
        ),

        'client_credentials' => array(
            'class' => 'League\OAuth2\Server\Grant\ClientCredentials',
            'access_token_ttl' => 31104000,
        ),

        'refresh_token' => array(
            'class'                 => 'League\OAuth2\Server\Grant\RefreshToken',
            'access_token_ttl'      => 3600,
            'refresh_token_ttl'     => 604800,
            'rotate_refresh_tokens' => false,
        ),
        
    ),

    /*
    |--------------------------------------------------------------------------
    | State Parameter
    |--------------------------------------------------------------------------
    |
    | Whether or not the state parameter is required in the query string
    |
    */
    'state_param' => false,

    /*
    |--------------------------------------------------------------------------
    | Scope Parameter
    |--------------------------------------------------------------------------
    |
    | Whether or not the scope parameter is required in the query string
    |
    */
    'scope_param' => false,

    /*
    |--------------------------------------------------------------------------
    | Scope Delimiter
    |--------------------------------------------------------------------------
    |
    | Which character to use to split the scope parameter in the query string
    |
    */
    'scope_delimiter' => ',',

    /*
    |--------------------------------------------------------------------------
    | Default Scope
    |--------------------------------------------------------------------------
    |
    | The default scope to use if not present in the query string
    |
    */
    'default_scope' => 'all',

    /*
    |--------------------------------------------------------------------------
    | Access Token TTL
    |--------------------------------------------------------------------------
    |
    | For how long the issued access token is valid (in seconds)
    | this can be also set on a per grant-type basis
    |
    */
    'access_token_ttl' => 3600,

    /*
    |--------------------------------------------------------------------------
    | Limit clients to specific grants
    |--------------------------------------------------------------------------
    |
    | Whether or not to limit clients to specific grant types
    | This is useful to allow only trusted clients to access your API differently
    |
    */
    'limit_clients_to_grants' => false,

    /*
    |--------------------------------------------------------------------------
    | Limit clients to specific scopes
    |--------------------------------------------------------------------------
    |
    | Whether or not to limit clients to specific scopes
    | This is useful to only allow specific clients to use some scopes
    |
    */
    'limit_clients_to_scopes' => false,

    /*
    |--------------------------------------------------------------------------
    | Limit scopes to specific grants
    |--------------------------------------------------------------------------
    |
    | Whether or not to limit scopes to specific grants
    | This is useful to allow certain scopes to be used only with certain grant types
    |
    */
    'limit_scopes_to_grants' => false,

    /*
    |--------------------------------------------------------------------------
    | HTTP Header Only
    |--------------------------------------------------------------------------
    |
    | This will tell the resource server where to check for the access_token.
    | By default it checks both the query string and the http headers
    |
    */
    'http_headers_only' => false,
);
