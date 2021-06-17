<?php

namespace WithCandour\StatamicImgix;

use Illuminate\Support\Str;

class ImgixHelper
{
    private static $html_params = ['accesskey', 'align', 'alt', 'border', 'class', 'contenteditable', 'contextmenu', 'dir', 'height', 'hidden', 'id', 'lang', 'loading', 'longdesc', 'sizes', 'style', 'tabindex', 'title', 'usemap', 'width'];

    private static $sorter_exclude = ['path', 'sizes', 'size-params-overrides', 'focus', 'default-focus'];

    /**
     * Sort the params and perform any required processing
     *
     * @param array $params
     *
     * @return array
     */
    public static function processParams(array $params = [])
    {
        $sorted = [
            'path' => $params['path'] ?: "",
            'html' => [],
            'imgix' => config('imgix.default_params', []),
        ];

        // Split the `focus` parameter into fp-x, fp-y and fp-z for imgix
        if (isset($params['focus']) || isset($params['default-focus'])) {
            $focus = isset($params['focus']) ? $params['focus'] : $params['default-focus'];
            $fx = explode('-', $focus)[0];
            $fy = explode('-', $focus)[1];
            $fz = explode('-', $focus)[2];
            $sorted['imgix']['fp-x'] = (int)$fx / 100;
            $sorted['imgix']['fp-y'] = (int)$fy / 100;
            $sorted['imgix']['fp-z'] = (float)$fz;
        }

        // Unset the excluded params
        $excluded = array_merge(self::$sorter_exclude, config('imgix.excluded_params', []));
        foreach($excluded as $exclude) {
            unset($params[$exclude]);
        }

        foreach ($params as $key => $val) {
            $is_html_param = in_array($key, self::$html_params);
            $is_data_param = Str::startsWith($key, 'data-');
            $is_aria_param = Str::startsWith($key, 'aria-');

            if ($is_html_param || $is_data_param || $is_aria_param) {
                $sorted['html'][$key] = $val;
            } else {
                $sorted['imgix'][$key] = $val;
            }
        }

        return $sorted;
    }
}
