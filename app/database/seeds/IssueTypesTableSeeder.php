<?php
use Paxifi\Issue\Repository\EloquentIssueTypesRepository;

class IssueTypesTableSeeder extends Seeder {
    public function run()
    {
        DB::table('issue_types')->truncate();

        EloquentIssueTypesRepository::create(
            array(
                'name' => 'Billing',
                'email' => 'billing@paxifi.com',
                'description' => 'bill issue',
                'enabled' => true
            )
        );

        EloquentIssueTypesRepository::create(
            array(
                'name' => 'Feedback',
                'email' => 'feedback@paxifi.com',
                'description' => 'issue report',
                'enabled' => true
            )
        );

        EloquentIssueTypesRepository::create(
            array(
                'name' => 'Technical',
                'email' => 'technical@paxifi.com',
                'description' => 'technical issue report.',
                'enabled' => true
            )
        );

        EloquentIssueTypesRepository::create(
            array(
                'name' => 'Other',
                'email' => 'feedback@paxifi.com',
                'description' => 'Other issues',
                'enabled' => true
            )
        );
    }
} 