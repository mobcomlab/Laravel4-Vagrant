<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\Storage;

class PullPowerData extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'pull:power';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Download power data from Dropbox and populate database with new data.';

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
		
		if (Storage::disk('dropbox')->exists('Naresuan Univ')) {
			dd('found it');
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [];
	}

}
