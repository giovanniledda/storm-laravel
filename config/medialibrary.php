<?php

return [

    /*
     * Here you can override the class names of the jobs used by this package. Make sure
     * your custom jobs extend the ones provided by the package.
     */
    'jobs' => [
//        'perform_conversions' => Spatie\MediaLibrary\Jobs\PerformConversions::class,
        'perform_conversions' => App\Jobs\PerformConversions::class,
        'generate_responsive_images' => Spatie\MediaLibrary\ResponsiveImages\Jobs\GenerateResponsiveImagesJob::class,
    ],
];
