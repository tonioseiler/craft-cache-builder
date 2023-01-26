<?php
/**
 * Cache Builder plugin for Craft CMS 3.x
 *
 * Rebuilds your cache including image transforms
 *
 * @link      https://furbo.ch
 * @copyright Copyright (c) 2022 Furbo GmbH
 */

namespace furbo\cachebuilder;

use furbo\cachebuilder\services\CacheBuilderService as CacheBuilderServiceService;
use furbo\cachebuilder\models\Settings;
use furbo\cachebuilder\utilities\CacheBuilderUtility as CacheBuilderUtilityUtility;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\console\Application as ConsoleApplication;
use craft\web\UrlManager;
use craft\services\Utilities;
use craft\services\Elements;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\elements\Entry;
use craft\elements\Category;
use craft\helpers\ElementHelper;

use yii\base\Event;
use craft\base\Element;
use craft\events\ModelEvent;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    Furbo GmbH
 * @package   CacheBuilder
 * @since     1.0.0
 *
 * @property  CacheBuilderServiceService $cacheBuilderService
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class CacheBuilder extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * CacheBuilder::$plugin
     *
     * @var CacheBuilder
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public bool $hasCpSettings = true;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public bool $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * CacheBuilder::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Add in our console commands
        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'furbo\cachebuilder\console\controllers';
        }

        // Register our site routes
        // Event::on(
        //     UrlManager::class,
        //     UrlManager::EVENT_REGISTER_SITE_URL_RULES,
        //     function (RegisterUrlRulesEvent $event) {
        //         $event->rules['siteActionTrigger1'] = 'cache-builder/default';
        //     }
        // );

        // Register our CP routes
        // Event::on(
        //     UrlManager::class,
        //     UrlManager::EVENT_REGISTER_CP_URL_RULES,
        //     function (RegisterUrlRulesEvent $event) {
        //         $event->rules['cpActionTrigger1'] = 'cache-builder/default/do-something';
        //     }
        // );

        // Register our utilities
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = CacheBuilderUtilityUtility::class;
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );

        // this is executed after a entry is saved
        Event::on(
            Entry::class,
            Entry::EVENT_AFTER_SAVE,
            function (ModelEvent $event) {
                if (
                    !$event->sender->propagating &&
                    !ElementHelper::rootElement($event->sender)->isProvisionalDraft &&
                    !ElementHelper::isRevision($event->sender)
                ) {
                    $this->buildCacheAfterSave($event->sender);
                }
            }
        );

        Event::on(
            Category::class,
            Category::EVENT_AFTER_SAVE,
            function (ModelEvent $event) {
                if (
                    ($event->sender->enabled && $event->sender->getEnabledForSite()) &&
                    !$event->sender->resaving
                ) {
                    $this->buildCacheAfterSave($event->sender);
                }
            }
        );

       

/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'cache-builder',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'cache-builder/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }

    protected function buildCacheAfterSave(Element $element) {
        $settings = CacheBuilder::$plugin->getSettings();
        if (in_array('rebuildCacheAfterSave', $settings->options)) {
            CacheBuilder::$plugin->cacheBuilderService->buildCacheForElement($element);

            //get forced urls from settings
            if (!empty($settings->forcedUrls)) {
                $urls = explode(PHP_EOL,$settings->forcedUrls);
                CacheBuilder::$plugin->cacheBuilderService->buildCacheForUrls($urls);
            }
        }
    }
}
