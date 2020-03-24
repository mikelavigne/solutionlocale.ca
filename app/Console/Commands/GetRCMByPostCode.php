<?php

namespace App\Console\Commands;

use App\Place;
use App\PostalCodeToAdminRegion;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GetRCMByPostCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'soloc:postcodeRCM';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will update all places\'s subregion to an RCM via its postal code.';

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
        $this->customLog = collect(['no_correspondance' => collect(), 'invalid_postalcode' => collect()]);

        $now = Carbon::now('America/Montreal')->toDateTimeString();
        $now_slugged = Str::slug($now);
        $log_file_name = "correspondance_log_{$now_slugged}.txt";
        
        Storage::put($log_file_name, "Correspondance du: {$now}");
        
        Place::whereNotNull('postalCode')->each(function ($place) use ($log_file_name) {

            if ($this->validatePostalCode($place->postalCode)) {
                $sanitized_postal_code = Str::of($place->postalCode)->replace(" ", "")->upper();
                $correspondance = PostalCodeToAdminRegion::where('postal_code', $sanitized_postal_code)->first();
                
                if ($correspondance) {
                    $this->setSubRegion($correspondance, $place);
                } else {
                    $this->addToCustomLog("no_correspondance", "{$place->name} ({$place->id}): No postal code -> RMC correspondance found.");
                }
            } else {
                $this->addToCustomLog("invalid_postalcode", "{$place->name} ({$place->id}): Postal code is invalid.");
            }

            return;
        });

        $this->writeCustomLog($log_file_name);

        $this->line("Done!");
    }

    private function writeCustomLog($log_file_name)
    {
        $this->customLog->each(function ($items, $type) use ($log_file_name) {
            Storage::append($log_file_name, "_____");
            Storage::append($log_file_name, $type);
            Storage::append($log_file_name, "_____");
            $items->each(function ($line) use ($log_file_name) {
                Storage::append($log_file_name, $line);
            });
        });
    }

    private function addToCustomLog($type, $line)
    {
        $this->customLog[$type]->push($line);
    }

    private function setSubRegion($correspondance, $place)
    {
        $place->subregion = $correspondance->rmc;
        $place->save();
        $this->info("RMC set for Place ID {$place->id}");
    }

    private function validatePostalCode($postal_code)
    {
        $validator = app('\Axlon\PostalCodeValidation\Validator');

        return $validator->validate('ca', $postal_code);
    }
}
