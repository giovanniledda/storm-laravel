<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use const QUEUE_PERFORM_CONVERSIONS;
use Spatie\MediaLibrary\Conversions\ConversionCollection;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PerformConversions implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    protected ConversionCollection $conversions;

    protected Media $media;

    protected bool $onlyMissing;

    public int $tries = 2;

    public function __construct(ConversionCollection $conversions, Media $media)
    {
        $this->conversions = $conversions;
        $this->media = $media;
        $this->queue = QUEUE_PERFORM_CONVERSIONS;
    }

    public function handle(): bool
    {
        app(FileManipulator::class)->performConversions($this->conversions, $this->media);

        return true;
    }
}
