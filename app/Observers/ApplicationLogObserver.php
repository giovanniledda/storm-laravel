<?php

namespace App\Observers;

use App\ApplicationLog;
use App\ProjectUser;
use App\ReportItem;

class ApplicationLogObserver
{
    /**
     * Handle the application log "created" event.
     *
     * @param  \App\ApplicationLog  $applicationLog
     * @return void
     */
    public function created(ApplicationLog $applicationLog)
    {
        // Setto l'id interno progressivo calcolato su base "per boat"
        $applicationLog->updateInternalProgressiveNumber();

        // Aggiorno l'autore
        if (!$applicationLog->author_id && \Auth::check()) {
            $auth_user = \Auth::user();
            $applicationLog->update([
                'author_id' => $auth_user->id
            ]);
        }
        // Creo una nuova istanza di ReportItem
        ReportItem::touchForNewApplicationLog($applicationLog);
    }

    /**
     * Handle the application log "updated" event.
     *
     * @param  \App\ApplicationLog  $applicationLog
     * @return void
     */
    public function updated(ApplicationLog $applicationLog)
    {
        //
    }

    /**
     * Handle the application log "deleted" event.
     *
     * @param  \App\ApplicationLog  $applicationLog
     * @return void
     */
    public function deleted(ApplicationLog $applicationLog)
    {
        //
    }

    /**
     * Handle the application log "restored" event.
     *
     * @param  \App\ApplicationLog  $applicationLog
     * @return void
     */
    public function restored(ApplicationLog $applicationLog)
    {
        //
    }

    /**
     * Handle the application log "force deleted" event.
     *
     * @param  \App\ApplicationLog  $applicationLog
     * @return void
     */
    public function forceDeleted(ApplicationLog $applicationLog)
    {
        //
    }
}
