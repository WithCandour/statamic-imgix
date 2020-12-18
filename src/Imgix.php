<?php

namespace WithCandour\StatamicImgix;

use Imgix\UrlBuilder;
use Statamic\Facades\URL;

class Imgix
{
    /**
     * @var \Imgix\UrlBuilder
     */
    protected static $imgix;

    /**
     * @return void
     */
    public function __construct()
    {
        self::$imgix = new UrlBuilder(
            config('imgix.domain', ''),
            config('imgix.use_https', true),
            config('imgix.sign_key', ''),
            config('imgix.include_library_param', true)
        );
    }

    /**
     * Return a built imgix URL
     *
     * @param string $path
     * @param array $params
     *
     * @return string
     */
    public static function buildUrl(string $path, array $params = [])
    {
        $parsed_path = URL::makeRelative($path);
        return self::$imgix->createUrl($parsed_path, $params);
    }

    /**
     * Return a built imgix srcset
     *
     * @param string $path
     * @param array $params
     *
     * @return string
     */
    public static function buildSrcset(string $path, array $params = [])
    {
        // We won't use imgix-php's builder here as it always generates a srcset of 1-5 dpr
        $srcset = [];
        $resolutions = config('srcset_resolutions', [1,2]);

        foreach($resolutions as $resolution) {
            if($resolution !== 1) {
                $params['dpr'] = $resolution;
            }

            $url = self::buildUrl($path, $params);
            $srcset[] = "{$url} {$resolution}x";
        }

        return implode(', ', $srcset);
    }
}
