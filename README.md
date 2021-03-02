# Statamic Imgix

![Statamic 3.0+](https://img.shields.io/badge/Statamic-3.0+-FF269E?style=for-the-badge&link=https://statamic.com)

Generate imgix URLs from your antlers templates with the Statamic Imgix addon.


## Installation

#### Install via composer:
```
composer require withcandour/statamic-imgix
```
Then publish the publishables from the service provider:
```
php artisan vendor:publish --provider="WithCandour\StatamicImgix\ServiceProvider"
```

#### Config
After publishing the config you will have a `config/imgix.php` file. You will need to provide your imgix domain in here.

## Usage
The addon will provide a set of Imgix tags for use in your antlers templates.

### Tags

This addon will provide a set of imgix tags for generating imgix URL's and elements that use them. The minimum requirement for these tags is that you provide it with a `path` parameter, this is the path or url for the asset source, it is recommended that you use the `{{ path }}` variable returned inside of a pair of asset tags.

Tags will accept any of the <a href="https://docs.imgix.com/apis/rendering" target="_blank" rel="noopener">Imgix manipulation parameters</a>. Tags which generate a tag (such as the `{{ imgix:image_tag }}` tag) will also accept all standard HTML attributes (i.e 'alt'), these will then get added to the element/tag produced.

| Tag       | Description                                                                |
| --------- | -------------------------------------------------------------------------- |
| [imgix](#tag-imgix) |  The base tag - this will simply generate an imgix URL           |
| [imgix:image_url](#tag-imgix) |  An alias of `{{ imgix }}`          |
| [imgix:image_srcset](#tag-imgix-srcset) |  Generates a srcset for use in an img tag    |
| [imgix:image_tag](#tag-imgix-image) |  Generates an `<img />;` tag from your parameters |
| [imgix:responsive_image_tag](#tag-imgix-image-responsive) |  Generates an `<img />` element with a srcset |
| [imgix:responsive_picture_tag](#tag-imgix-responsive-picture) |  Generates an `<picture />` element for displaying different images at different sizes |


<h4 id="tag-imgix">Imgix</h4>
The base `{{ imgix }}` tag will produce a simple imgix URL with any imgix manipulation parameters appended to the URL.

<h4 id="tag-imgix">Image srcset</h4>
Similarly to the base tag, the `{{ imgix:image_srcset }}` tag will produce a string containing a srcset (suitable for use in an `&lt;img /&gt;` element). By default the srcset will contain @1x and @2x image sizes (to handle retina screens). You may override this behaviour by setting the `srcset_resolutions` in the config file; this will need to be an array of numbers, representing the resolutions.

For example:

```php
return [
    'srcset_resolutions' => [1, 4, 5],
];
```

<h4 id="tag-imgix-image">Image tag</h4>
The `{{ imgix:image_tag }}` tag will produce an `&lt;img /&gt;` element with the 'src' attribute set to the image. Any and all HTML attributes may also be passed as parameters.

<h4 id="tag-imgix-image-responsive">Responsive image tag</h4>
The `{{ imgix:responsive_image_tag }}` tag will combine the powers of the srcset and the image_tag tags to produce an `&lt;img /&gt;` element with a srcset attribute.

<h4 id="tag-imgix-responsive-picture">Responsive picture tag</h4>
This tag is a little different to the others, a special `sizes` parameter will be used to produce a `&lt;picture /&gt;` element with multiple `&lt;source /&gt;` blocks. This will enable you to target specific breakpoints in order to display correctly sized images.

For example:

```
{{
    imgix:responsive_picture_tag
    :path="path"
    :alt="alt"
    fit="crop"
    crop="faces"
    sizes="768: [1500x960]|560: [920x550]|200: [600x400]"
}}
```

This tag will produce a picture tag with sources so that:
- At 200-559px a 600x400 image will be shown
- At 560-767px a 920x550 image will be shown
- At 768px+ a 1500x960 image will be shown

### Focal point cropping
This addon supports focalpoint cropping by using the `{{ focus }}` variable, generated using the focal point selector in the CMS. In order to use focalpoint cropping in your templates, combine `fit="crop"`, `crop="focalpoint"` and `focus="{focus}"` (or `:focus="focus"`) in the tag.

For example:
```
{{ my_image_field }}
    {{
        imgix:responsive_image_tag
        w="1920"
        h="1080"
        :path="path"
        :alt="alt
        fit="crop"
        crop="focalpoint"
        :focus="focus"
    }}
{{ /my_image_field }}
```