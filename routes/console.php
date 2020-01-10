<?php

use App\Comment;
use App\History;
use App\Services\InternalProgNumHandler;
use App\Task;
use Illuminate\Foundation\Inspiring;
use Net7\Documents\Document;
use Illuminate\Support\Str;

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

// Spostamento immagini da Task a History
Artisan::command('move-task-images', function () {

    // TODO: c'è troppo codice, si potrebbe usare la Dependency Injection di un Service esterno come fatto sopra con "InternalProgNumHandler $ipn_handler"
    // ...ma voglio usare gli output a video e la progressive bar perciò per ora lascio così

    if ($this->confirm('Do you wish to continue?')) {
        $this->comment('Running images moving...');
        $tasks = App\Task::all();

        $bar = $this->output->createProgressBar(count($tasks));

        $bar->start();

        /** @var Task $resource */
        foreach ($tasks as $resource) {

            $this->info("\n");
            $this->info("- Task ({$resource->created_at}) {$resource->name} [ID: {$resource->id}]");

            $history = $resource->getFirstHistory();
            if ($history) {
                $this->info("-- History ({$history->created_at}) [ID: {$history->id}]");

                $detailed_images = $resource->detailed_images;
                $generic_images = $resource->generic_images;
                $additional_images = $resource->additional_images;
                $documents = [];

                foreach ($detailed_images as $i) {
                    $documents[] = $i->getShowApiUrl();
                }

                foreach ($generic_images as $i) {
                    $documents[] = $i->getShowApiUrl();
                }

                foreach ($additional_images as $i) {
                    $documents[] = $i->getShowApiUrl();
                }

                foreach ($documents as $document_id) {
                    $document = Document::find($document_id);
                    if ($document) {
                        $media = $document->getRelatedMedia();
                        $file_path = $media->getPath('');
                        if ($file_path && file_exists($file_path)) {
                            $trick = Str::replaceLast('/', '<', $file_path);
                            $filename = Str::after($trick, '<');
                            $new_file_dir_path = storage_path('app/'.$history->getMediaPath($media));
                            if (!is_dir($new_file_dir_path)) {
                                mkdir($new_file_dir_path, 0755, true);
                            }
                            $new_file_path = $new_file_dir_path.$filename;
                            rename($file_path, $new_file_path);
                            $this->info("--- OLD PATH ($file_path) ---> NEW PATH ($new_file_path)");
                        }

                        $this->info("--- Document ({$document->created_at}) [ID: {$document->id}]");
                        $document->documentable_type = History::class;
                        $document->documentable_id = $history->id;
                        $document->save();
                        $this->info("--- ...updated!");
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->comment("\n...done!");
    }

})->describe('Takes images from Tasks and assign them to the first History instance of each Task');


// Spostamento descrizioni da Task a History comments
Artisan::command('move-task-descriptions', function () {

    // TODO: c'è troppo codice, si potrebbe usare la Dependency Injection di un Service esterno come fatto sopra con "InternalProgNumHandler $ipn_handler"
    // ...ma voglio usare gli output a video e la progressive bar perciò per ora lascio così

    if ($this->confirm('Do you wish to continue?')) {
        $this->comment('Running description/comments moving...');
        $tasks = App\Task::all();

        $bar = $this->output->createProgressBar(count($tasks));

        $bar->start();

        /** @var Task $resource */
        foreach ($tasks as $resource) {

            $this->info("\n");
            $this->info("- Task ({$resource->created_at}) {$resource->name} [ID: {$resource->id}]");
            $description = $resource->description;
            if ($description) {
                /** @var History $history */
                $history = $resource->getFirstHistory();
                if ($history) {
                    $this->info("-- History ({$history->created_at}) [ID: {$history->id}]");

                    if ($history->comments()->count()) {
                        $comment = $history->getFirstcomment();
                        $this->info("-- Comment [ID: {$comment->id}]");
                        $comment->body = $comment->body."\n".$description;
                        $comment->save();
                        $this->info("--- ...updated!");
                    } else {
                        $comment = Comment::create([
                            'body' => $description
                        ]);
                        $history->comments()->save($comment);
                        $this->info("--- ...comment [ID: {$comment->id}] created!");
                    }
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->comment("\n...done!");
    }

})->describe('Takes description from Tasks and assign them as a comment to the first History instance of each Task');
