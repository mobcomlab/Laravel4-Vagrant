<?php

namespace App\Console\Commands;

use Carbon\Carbon;
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
        $today = Carbon::today();

        // Real file names
        $humidityFileName = 'humidity-'.$today->year.'-'.$today->month.'.csv';
        $temperatureFileName = 'temperature-'.$today->year.'-'.$today->month.'.csv';
        $powerFileName = 'power-'.$today->year.'-'.$today->month.'.csv';

        // Temp file names
        do {
            $tmpHumidityFileName = '/tmp/'.str_random(10).'.csv';
        } while (File::exists($tmpHumidityFileName));
        do {
            $tmpTemperatureFileName = '/tmp/'.str_random(10).'.csv';
        } while (File::exists($tmpTemperatureFileName));
        do {
            $tmpPowerFileName = '/tmp/'.str_random(10).'.csv';
        } while (File::exists($tmpPowerFileName));


        $this->info('Export humidity data...');
        $humidityFile = public_path('exportdata/humidity/'.$humidityFileName);
        if (File::exists($humidityFile)) {
            File::delete($humidityFile);
        }
        DB::statement("SELECT recorded_at, humid1, humid2, humid3, humid4, humid5, humid6, humid7, humid8, humid9, humid10, humid11, humid12, humid13, humid14, humid15
            INTO OUTFILE '".$tmpHumidityFileName."' 
            FROM humidity_pivot
        ");
        File::copy($tmpHumidityFileName, $humidityFile);


        $this->info('Export temperature data...');
        $temperatureFile = public_path('exportdata/temperature/'.$temperatureFileName);
        if (File::exists($temperatureFile)) {
            File::delete($temperatureFile);
        }
        DB::statement("SELECT recorded_at, tem1, tem2, tem3, tem4, tem5, tem6, tem7, tem8, tem9, tem10, tem11, tem12, tem13, tem14, tem15
            INTO OUTFILE '".$tmpTemperatureFileName."' 
            FROM temperature_pivot
        ");
        File::copy($tmpTemperatureFileName, $temperatureFile);

        
        $this->info('Export power data...');
        $powerFile = public_path('exportdata/power/'.$powerFileName);
        if (File::exists($powerFile)) {
            File::delete($powerFile);
        }
        DB::statement("
          SELECT recorded_at, sc5_214_60a, sc5_214_25a 
          INTO OUTFILE '".$tmpPowerFileName."' 
          FROM power_pivot
         ");
        File::copy($tmpPowerFileName, $powerFile);

        $this->info('Removing data that is older than one month...');
        $lastMonth = $today->subMonth();
        $startOfLastMonth = $lastMonth->year."-".$lastMonth->month."-01 00:00:00";
        DB::statement("DELETE FROM humidity WHERE recorded_at < '".$startOfLastMonth."'");
        DB::statement("DELETE FROM temperature WHERE recorded_at < '".$startOfLastMonth."'");
        DB::statement("DELETE FROM power WHERE recorded_at < '".$startOfLastMonth."'");

    }
}
