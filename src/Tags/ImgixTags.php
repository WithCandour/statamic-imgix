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
     * @return string
     */
    public function imageTag()
    {
        $src = $this->imageUrl();
        $html_attrs = $this->buildHtmlAttrs($this->sortParams($this->params));
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
        $sorted_params = $this->sortParams($this->params);
        $html_attrs = $this->buildHtmlAttrs($sorted_params);
        $sources = $this->buildSources($sorted_params['path'], $sizes, $sorted_params['imgix']);
        $image_tag = $this->imageTag();
        return "<picture>{$sources}{$image_tag}</picture>";
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
     * Generate <picture /> sources
     *
     * @param string $path
     * @param array $sizes
     * @param array $params
     *
     * @return string
     */
    protected function buildSources(string $path, array $sizes, array $params)
    {
        $sources = [];
        foreach($sizes as $size) {
            // min-width: [widthxheight]
            preg_match('/(\d+): ?\[(\d+)x(\d+)\]/', $size, $matches);

            $params = array_merge($params, ['w' => $matches[2], 'h' => $matches[3]]);
            $srcset = Imgix::buildSrcset($path, $params);

            $sources[] = "<source media=\"(min-width:{$matches[1]}px)\" srcset=\"{$srcset}\">";
        }
        return implode('', $sources);
    }
}
