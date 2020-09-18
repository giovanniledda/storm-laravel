<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\MediaLibrary\FileManipulator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\MediaLibrary\Conversion\ConversionCollection;

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
    }

    public function handle(): bool
    {
        app(FileManipulator::class)->performConversions($this->conversions, $this->media);

        return true;
    }
}
