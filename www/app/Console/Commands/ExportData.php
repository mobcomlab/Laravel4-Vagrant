<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;
use DB;

class ExportData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export all data';

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
    public function handle()
    {
        if (File::exists('/tmp/humidity.csv')) {
            File::delete('/tmp/humidity.csv');
        }
        if (File::exists('/tmp/temperature.csv')) {
            File::delete('/tmp/temperature.csv');
        }
        if (File::exists('/tmp/power.csv')) {
            File::delete('/tmp/power.csv');
        }

        $this->info('Export humidity data...');
        $humidityFile = public_path('export/humidity.csv');
        if (File::exists($humidityFile)) {
            File::delete($humidityFile);
        }

        DB::statement("
          SELECT 
            recorded_at,
            humid1, 
            humid2, 
            humid3, 
            humid4, 
            humid5,
            humid6,
            humid7,
            humid8,
            humid9,
            humid10,
            humid11,
            humid12,
            humid13,
            humid14,
            humid15
          INTO OUTFILE '/tmp/humidity.csv' 
          FROM humidity_pivot
         ");
        File::move('/tmp/humidity.csv', $humidityFile);

        $this->info('Export temperature data...');
        $temperatureFile = public_path('export/temperature.csv');
        if (File::exists($temperatureFile)) {
            File::delete($temperatureFile);
        }

        DB::statement("
          SELECT 
            recorded_at,
            tem1, 
            tem2, 
            tem3, 
            tem4,
            tem5,
            tem6,
            tem7,
            tem8,
            tem9,
            tem10,
            tem11,
            tem12,
            tem13,
            tem14,
            tem15
          INTO OUTFILE '/tmp/temperature.csv' 
          FROM temperature_pivot
         ");
        File::move('/tmp/temperature.csv', $temperatureFile);

        $this->info('Export power data...');
        $powerFile = public_path('export/power.csv');
        if (File::exists($powerFile)) {
            File::delete($powerFile);
        }

        DB::statement("
          SELECT 
            recorded_at,
            sc5_214_60a, 
            sc5_214_25a 
          INTO OUTFILE '/tmp/power.csv' 
          FROM power_pivot
         ");
        File::move('/tmp/power.csv', $powerFile);
    }
}
