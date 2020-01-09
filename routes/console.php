<?php

use App\Services\InternalProgNumHandler;
use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

// Aggiornamento ID interni di Task, Project e ApplicationLog su base "Boat"
Artisan::command('update-internal-ids', function (InternalProgNumHandler $ipn_handler) {

    $this->comment('Running internal IDS sync...');
    $ipn_handler->run();
    $this->comment('...done!');

})->describe('Update (and sync) internal_progressive_number field for many Models');
