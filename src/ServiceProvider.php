<?php

namespace WithCandour\StatamicImgix;

use Statamic\Providers\AddonServiceProvider;
use WithCandour\StatamicImgix\Tags\ImgixTags;

class ServiceProvider extends AddonServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        parent::boot();

        // Load imgix config
        $this->mergeConfigFrom(__DIR__ . '/../config/imgix.php', 'imgix');
        $this->publishes([
            __DIR__ . '/../config/imgix.php' => config_path('imgix.php'),
        ], 'config');
    }

    protected $tags = [
        ImgixTags::class
    ];
}
