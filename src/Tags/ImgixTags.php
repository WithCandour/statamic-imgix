<?php

namespace WithCandour\StatamicImgix\Tags;

use Statamic\Tags\Tags;
use WithCandour\StatamicImgix\Facades\Imgix;
use WithCandour\StatamicImgix\Facades\ImgixHelper;

class ImgixTags extends Tags
{
    protected static $handle = 'imgix';

    /**
     * @return string
     */
    public function index()
    {
        return $this->imageUrl();
    }

    /**
     * @return string
     */
    public function imageUrl()
    {
        $sorted_params = $this->sortParams($this->params);
        return Imgix::buildUrl($sorted_params['path'], $sorted_params['imgix']);
    }

    /**
     * @return string
     */
    public function imageSrcset()
    {
        $sorted_params = $this->sortParams($this->params);
        return Imgix::buildSrcset($sorted_params['path'], $sorted_params['imgix']);
    }

    /**
     * @return array
     */
    private function getMaxWidthHeight($sizes = null)
    {
        if ($sizes === null) {
            return ['width' => 0, 'height' => 0];
        }

        $maxWidth = 0;
        $maxHeight = 0;
        $maxScreenSize = 0;

        foreach ($sizes as $size) {
            preg_match('/(\d+): ?\[(\d+)x(\d+)\]/', $size, $matches);

            $screenSize = (int)$matches[1];
            $width = (int)$matches[2];
            $height = (int)$matches[3];

            if ($screenSize > $maxScreenSize) {
                $maxScreenSize = $screenSize;
                $maxWidth = $width;
                $maxHeight = $height;
            } elseif ($screenSize === $maxScreenSize) {
                $maxWidth = max($maxWidth, $width);
                $maxHeight = max($maxHeight, $height);
            }
        }

        return ['width' => $maxWidth, 'height' => $maxHeight];
    }

    /**
     * @return string
     */
    public function imageTag($sizes = null)
    {
        $src = $this->imageUrl();
        $html_attrs = $this->buildHtmlAttrs($this->sortParams($this->params));
        if($sizes) {
            $dimensions = $this->getMaxWidthHeight($sizes);
            $html_attrs .= " width=\"{$dimensions['width']}\" height=\"{$dimensions['height']}\"";
        }
        // add lazy loading attribute
        $html_attrs .= ' loading="lazy"';
        return "<img src=\"{$src}\" {$html_attrs}>";
    }

    /**
     * @return string
     */
    public function responsiveImageTag()
    {
        $sorted_params = $this->sortParams($this->params);

        $srcset = Imgix::buildSrcset($sorted_params['path'], $sorted_params['imgix']);
        $src = $this->imageUrl();
        $html_attrs = $this->buildHtmlAttrs($sorted_params);

        return "<img srcset=\"{$srcset}\" src=\"{$src}\" {$html_attrs}>";
    }

    /**
     * @return string
     */
    public function responsivePictureTag()
    {
        $sizes = $this->params->explode('sizes');
        $size_params_overrides = $this->parseSizeOverrides($this->params->explode('size-params-overrides'));
        $sorted_params = $this->sortParams($this->params);
        $html_attrs = $this->buildHtmlAttrs($sorted_params);
        $picture_attrs = $this->buildPictureAttrs($sorted_params);

        return sprintf(
            "<picture%s>%s%s</picture>",
            $picture_attrs ? $picture_attrs : '',
            $this->buildSources($sorted_params['path'], $sizes, $sorted_params['imgix'], $size_params_overrides),
            $this->imageTag($sizes)
        );

    }

    /**
     * Parse size overrides
     *
     * @param array $overrides
     *
     * @return array
     */
    private function parseSizeOverrides($overrides)
    {
        $size_categories = [];

        if (is_array($overrides)) {
            foreach ($overrides as $override) {
                if (preg_match('/(\d+): ?\[(.*)\]/', $override, $matches)) {
                    $size_categories[$matches[1]] = $matches[2];
                }
            }
        }

        return $size_categories;
    }

    /**
     * Use ImgixHelper to sort params
     *
     * @param \Statamic\Tags\Parameters $params
     *
     * @return array
     */
    protected function sortParams(\Statamic\Tags\Parameters $params)
    {
        return ImgixHelper::processParams($params->toArray());
    }

    /**
     * Build html attributes string from a list
     *
     * @param array $params
     * @return string
     */
    protected function buildHtmlAttrs($params)
    {
        $html_params = $params['html'];
        $html = '';

        foreach ($html_params as $key => $val) {
            $html .= " {$key}=\"{$val}\"";
        }

        return $html;
    }

    /**
     * Build picture attributes string from a list
     *
     * @param array $params
     * @return string
     */

    protected function buildPictureAttrs($params)
    {
        $picture_params = $params['picture'];
        $html = '';

        // these params are prefixed with picture_ and picture_ should be removed
        $picture_params = array_map(function($key) {
            return preg_replace('/^picture_/', '', $key);
        }, $picture_params);

        foreach ($picture_params as $key => $val) {
            $html .= " {$key}=\"{$val}\"";
        }

        return $html;
    }

    /**
     * Generate <picture /> sources
     *
     * @param string $path
     * @param array $sizes
     * @param array $params
     * @param array $size_params_overrides
     *
     * @return string
     */
    protected function buildSources(string $path, array $sizes, array $params, array $size_params_overrides)
    {
        $sources = [];
        foreach($sizes as $size) {

            preg_match('/(\d+): ?\[(\d+)x(\d+)\]/', $size, $matches);

            $screen_size = $matches[1];

            $params = array_merge($params, ['w' => $matches[2], 'h' => $matches[3]]);

            // Overrides
            if(!empty($size_params_overrides[$screen_size])) {
                $override_params = explode(', ', $size_params_overrides[$screen_size]);
                foreach($override_params as $override_param) {
                    [$param_name, $param_value] = explode('=', $override_param);
                    $params[$param_name] = $param_value;
                }
            }

            $srcset = Imgix::buildSrcset($path, $params);

            $sources[] = "<source media=\"(min-width:{$screen_size}px)\" srcset=\"{$srcset}\" width=\"{$params['w']}\" height=\"{$params['h']}\">";
        }
        return implode('', $sources);
    }
}
