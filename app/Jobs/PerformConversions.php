<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use const QUEUE_PERFORM_CONVERSIONS;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\FileManipulator;
use Spatie\MediaLibrary\Models\Media;

class PerformConversions implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    /** @var \Spatie\MediaLibrary\Conversion\ConversionCollection */
    protected $conversions;

    /** @var \Spatie\MediaLibrary\Models\Media */
    protected $media;

    public $tries = 2;

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
