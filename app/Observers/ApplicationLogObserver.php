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
        $user_id = 1; // admin
        if (!$applicationLog->author_id && \Auth::check()) {
            $auth_user = \Auth::user();
            $user_id = $auth_user->id;
        }

        $applicationLog->update([
            'author_id' => $user_id,
            'last_editor_id' => $user_id,
        ]);

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
        if ($applicationLog->report_item) {
            $applicationLog->report_item->update(['report_update_date' => $applicationLog->updated_at]);
        }
    }

    /**
     * Handle the application log "saving" (before save) event.
     *
     * @param  \App\ApplicationLog  $applicationLog
     * @return void
     */
    public function saving(ApplicationLog $applicationLog)
    {
        // Aggiorno l'autore
        $user_id = 1; // admin
        if (\Auth::check()) {
            $auth_user = \Auth::user();
            $user_id = $auth_user->id;
        }
        $applicationLog->last_editor_id = $user_id;
    }

    /**
     * Handle the application log "deleted" event.
     *
     * @param  \App\ApplicationLog  $applicationLog
     * @return void
     */
    public function deleted(ApplicationLog $applicationLog)
    {
        if ($applicationLog->report_item) {
            $applicationLog->report_item->delete();
        }
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
