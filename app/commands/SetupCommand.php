<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SetupCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'paxifi:setup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Setup paxifi server side environment.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        try {
            $this->call('migrate');

            // Install Sentry migrations
            $this->call('migrate', array('--package' => 'lucadegasperi/oauth2-server-laravel'));

            $this->call('db:seed');

        } catch (Exception $e) {

            $this->error($e->getMessage());
        }
	}
}
