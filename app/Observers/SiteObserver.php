<?php

namespace App\Observers;

use function abort_if;
use App\Models\Site;

class SiteObserver
{
    /**
     * Handle the project "updating" event.
     *
     * @param  \App\Models\Site $site
     * @return void
     */
    /*
     * Lascio commentato per documentazione. Ora tutti gli errori di "Integrity constraint violation" sono gestiti
     * nella report() di App\Exceptions\Handler.
     * Se si segue invece questa strada dell'Observer, la chiamata alla "deleting" ha la priorità...il problema è che
     * però va replicata per tutti i modelli, mettendo una abort_if per ogni modello correlato.
     *
    public function deleting(Site $site)
    {
        $base_msg = 'This action is not allowed: site ":name" has :entity related.';
        abort_if($site->has('boats'), 412, __($base_msg, ['name' => $site->name, 'entity' => 'boats']));
        abort_if($site->has('projects'), 412, __($base_msg, ['name' => $site->name, 'entity' => 'projects']));
    }
    */
}
