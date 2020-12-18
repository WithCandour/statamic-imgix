<?php

namespace WithCandour\StatamicImgix\Facades;

use Illuminate\Support\Facades\Facade;
use WithCandour\StatamicImgix\ImgixHelper as StaticImgixHelper;

class ImgixHelper extends Facade
{
    /**
     * @method static \WithCandour\StatamicImgix\ImgixHelper processParams()
     */
    protected static function getFacadeAccessor()
    {
        return StaticImgixHelper::class;
    }
}
