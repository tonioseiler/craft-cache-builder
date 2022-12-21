<?php
/**
 * Cache Builder plugin for Craft CMS 3.x
 *
 * Rebuilds your cache including image transforms
 *
 * @link      https://furbo.ch
 * @copyright Copyright (c) 2022 Furbo GmbH
 */

namespace furbo\cachebuilder\utilities;

use furbo\cachebuilder\CacheBuilder;
use furbo\cachebuilder\assetbundles\cachebuilderutilityutility\CacheBuilderUtilityUtilityAsset;

use Craft;
use craft\base\Utility;

/**
 * Cache Builder Utility
 *
 * Utility is the base class for classes representing Control Panel utilities.
 *
 * https://craftcms.com/docs/plugins/utilities
 *
 * @author    Furbo GmbH
 * @package   CacheBuilder
 * @since     1.0.0
 */
class CacheBuilderUtility extends Utility
{
    // Static
    // =========================================================================

    /**
     * Returns the display name of this utility.
     *
     * @return string The display name of this utility.
     */
    public static function displayName(): string
    {
        return Craft::t('cache-builder', 'Cache Builder');
    }

    /**
     * Returns the utility’s unique identifier.
     *
     * The ID should be in `kebab-case`, as it will be visible in the URL (`admin/utilities/the-handle`).
     *
     * @return string
     */
    public static function id(): string
    {
        return 'cachebuilder-cache-builder-utility';
    }

    /**
     * Returns the path to the utility's SVG icon.
     *
     * @return string|null The path to the utility SVG icon
     */
    public static function iconPath(): ?string
    {
        return Craft::getAlias("@furbo/cachebuilder/assetbundles/cachebuilderutilityutility/dist/img/CacheBuilderUtility-icon.svg");
    }

    /**
     * Returns the number that should be shown in the utility’s nav item badge.
     *
     * If `0` is returned, no badge will be shown
     *
     * @return int
     */
    public static function badgeCount(): int
    {
        return 0;
    }

    /**
     * Returns the utility's content HTML.
     *
     * @return string
     */
    public static function contentHtml(): string
    {
        Craft::$app->getView()->registerAssetBundle(CacheBuilderUtilityUtilityAsset::class);

        $someVar = 'Have a nice day!';
        return Craft::$app->getView()->renderTemplate(
            'cache-builder/_components/utilities/CacheBuilderUtility_content',
            [
                'someVar' => $someVar
            ]
        );
    }
}
