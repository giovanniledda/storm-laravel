<?php


namespace App\Listeners;
use App\ReportItem;
use Net7\Documents\Events\DocumentDeleted;

class DeleteReportItemAfterDocument
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event of Document deletion, deleting the related ReportItem.
     *
     * @param DocumentDeleted $event
     * @return void
     * @throws \Exception
     */
    public function handle(DocumentDeleted $event)
    {
        $report_item = ReportItem::findByDocumentId($event->document_id);
        if ($report_item) {
            $report_item->delete();
        }
    }
}
