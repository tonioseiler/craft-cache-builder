<?php
/**
 * Cache Builder plugin for Craft CMS 3.x
 *
 * Rebuilds your cache including image transforms
 *
 * @link      https://furbo.ch
 * @copyright Copyright (c) 2022 Furbo GmbH
 */

namespace furbo\cachebuilder\services;

use furbo\cachebuilder\CacheBuilder;
use furbo\cachebuilder\jobs\BuildCacheForUrl;


use Craft;
use craft\base\Component;
use craft\base\Element;
use craft\elements\Entry;
use craft\helpers\Queue;
use craft\utilities\ClearCaches;

/**
 * CacheBuilderService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Furbo GmbH
 * @package   CacheBuilder
 * @since     1.0.0
 */
class CacheBuilderService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     CacheBuilder::$plugin->cacheBuilderService->rebuildCache()
     *
     * @return mixed
     */


    public function buildCache()
    {
        $result = 'building cache';

        $settings = CacheBuilder::$plugin->getSettings();

        //clear data cache
        if (in_array('deleteCacheBeforeRebuilding', $settings->options)) {
            $this->clearCaches(['data']);
        }

        foreach ($settings->activeSections as $siteId => $sectionIds) {
            if (!empty($sectionIds)) {
                foreach ($sectionIds as $sectionId) {
                    $entries = Entry::find()
                        ->siteId($siteId)
                        ->sectionId($sectionId)
                        ->all();
                    foreach($entries as $entry) {
                        $this->buildCacheForElement($entry);
                        $this->buildCacheForRelations($entry);
                    }
                }
            }
        }

        //get forced urls from settings
        if (!empty($settings->forcedUrls)) {
            $urls = explode(PHP_EOL,$settings->forcedUrls);
            $this->buildCacheForUrls($urls);
        }

        //get extra urls from settings
        if (!empty($settings->extraUrls)) {
            $urls = explode(PHP_EOL,$settings->extraUrls);
            $this->buildCacheForUrls($urls);
        }
        return $result;
    }

    public function buildCacheForElement(Element $element)
    {
        $site = Craft::$app->sites->getSiteById($element->siteId, true);
        if ($site->enabled && $element->enabled) {
            $url = $element->getUrl();
            if ($url) {
                $job = new BuildCacheForUrl($url);
                Queue::push($job);
            }
        }
    }

    public function buildCacheForRelations(Element $element)
    {
        $entries = Entry::find()
                        ->siteId('*')
                        ->relatedTo($element)
                        ->all();
        foreach($entries as $entry) {
            $this->buildCacheForEntry($entry);
        }

    }

    public function buildCacheForUrls($urls) {
        foreach($urls as $url) {
            $url = trim($url);
            if ($url) {
                $job = new BuildCacheForUrl($url);
                Queue::push($job);
            }
        }
    }


    protected function clearCaches($caches = ['data']) {

        foreach (ClearCaches::cacheOptions() as $cacheOption) {
            if (is_array($caches) && !in_array($cacheOption['key'], $caches, true)) {
                continue;
            }

            $action = $cacheOption['action'];

            if (is_string($action)) {
                try {
                    FileHelper::clearDirectory($action);
                } catch (InvalidArgumentException) {
                    // the directory doesn't exist
                } catch (Throwable $e) {
                    Craft::warning("Could not clear the directory $action: " . $e->getMessage(), __METHOD__);
                }
            } elseif (isset($cacheOption['params'])) {
                call_user_func_array($action, $cacheOption['params']);
            } else {
                $action();
            }
        }
    }
}
