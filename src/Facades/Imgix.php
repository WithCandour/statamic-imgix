<?php

namespace WithCandour\StatamicImgix\Facades;

use Illuminate\Support\Facades\Facade;
use WithCandour\StatamicImgix\Imgix as StaticImgix;

class Imgix extends Facade
{
    /**
     * @method static \WithCandour\StatamicImgix\Imgix buildUrl()
     */
    protected static function getFacadeAccessor()
    {
        return StaticImgix::class;
    }
}
