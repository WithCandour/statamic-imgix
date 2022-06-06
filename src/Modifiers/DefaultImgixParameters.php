<?php

namespace WithCandour\StatamicImgix\Modifiers;

use Statamic\Modifiers\Modifier;

class DefaultImgixParameters extends Modifier
{
    public function index($value)
    {
        $url = \parse_url($value);

        // only add the parameters if the host matches *.imgix.net
        if (!\str_ends_with($url['host'], '.imgix.net')) {
            return $value;
        }

        // parse the query is one exists, otherwise create an empty list
        if (isset($url['query'])) {
            \parse_str($url['query'], $query);
        } else {
            $query = [];
        }

        $defaultParameters = config('imgix.default_params', []);

        // assign the default parameters if they don't already exist in some form
        foreach ($defaultParameters as $parameterName => $parameterValue) {
            if (!isset($query[$parameterName])) {
                $query[$parameterName] = $parameterValue;
            }
        }

        // rebuild the query and URL
        return $url['scheme'] . '://' . $url['host'] . $url['path'] . '?' . \http_build_query($query);
    }
}
