<?php

class OAuthSeeder extends Seeder
{

    public function run()
    {
        DB::table('oauth_client_metadata')->truncate();
        DB::table('oauth_grant_scopes')->truncate();
        DB::table('oauth_client_scopes')->truncate();
        DB::table('oauth_client_grants')->truncate();
        DB::table('oauth_grants')->truncate();
        DB::table('oauth_session_authcode_scopes')->truncate();
        DB::table('oauth_session_token_scopes')->truncate();
        DB::table('oauth_scopes')->truncate();
        DB::table('oauth_session_refresh_tokens')->truncate();
        DB::table('oauth_session_redirects')->truncate();
        DB::table('oauth_session_authcodes')->truncate();
        DB::table('oauth_session_access_tokens')->truncate();
        DB::table('oauth_sessions')->truncate();
        DB::table('oauth_client_endpoints')->truncate();
        DB::table('oauth_clients')->truncate();

        // Scopes
        $scopes = array(

            array(
                'scope' => 'basic',
                'name' => 'Basic',
                'description' => 'Minimum access',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),

            array(
                'scope' => 'all',
                'name' => 'All',
                'description' => 'Full access',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),

        );

        DB::table('oauth_scopes')->insert($scopes);


        // Clients
        $clients = array(

            array(
               'id' => 'paxifi-web',
                'secret' => 'Mk5VlL7M6E6FfI4L665Z9SWnQ3iMB5E2',
                'name' => 'Web client',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),

            array(
               'id' => 'paxifi-android',
                'secret' => 'e5sEocaDFTTEWQSL305fjgTW9PC14429',
                'name' => 'Android client',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),

            array(
               'id' => 'paxifi-ios',
                'secret' => 'R4zDvRUNEdxgyFikw23eq7LzIxjksIr1',
                'name' => 'iOS client',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ),

        );

        DB::table('oauth_clients')->insert($clients);
    }


} 