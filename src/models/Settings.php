<?php
/**
 * Cache Builder plugin for Craft CMS 3.x
 *
 * Rebuilds your cache including image transforms
 *
 * @link      https://furbo.ch
 * @copyright Copyright (c) 2022 Furbo GmbH
 */

namespace furbo\cachebuilder\models;

use furbo\cachebuilder\CacheBuilder;

use Craft;
use craft\base\Model;

/**
 * CacheBuilder Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    Furbo GmbH
 * @package   CacheBuilder
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public array $options = [];

    public array $activeSections = [];

    public string $extraUrls = "";

    public string $forcedUrls = "";

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }
}
