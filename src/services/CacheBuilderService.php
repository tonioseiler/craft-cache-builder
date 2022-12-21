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
use craft\elements\Entry;
use craft\helpers\Queue;

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
            $oldNS = CacheBuilder::$plugin->getInstance()->controllerNamespace;
            CacheBuilder::$plugin->getInstance()->controllerNamespace = 'craft\console\controllers';
            CacheBuilder::$plugin->getInstance()->runAction('clear-caches/data');
            CacheBuilder::$plugin->getInstance()->controllerNamespace = $oldNS;
        }

        //check if image transforms should also be deleted
        foreach ($settings->activeSections as $siteId => $sectionIds) {
            if (!empty($sectionIds)) {
                foreach ($sectionIds as $sectionId) {
                    $entries = Entry::find()
                        ->siteId($siteId)
                        ->sectionId($sectionId)
                        ->all();
                    foreach($entries as $entry) {
                        $this->buildCacheForEntry($entry);
                        $this->buildCacheForRelations($entry);
                    }
                }
            }
        }

        //get extra urls from settings
        if (!empty($settings->extraUrls)) {
            $urls = explode(PHP_EOL,$settings->extraUrls);
            foreach($urls as $url) {
                if ($url) {
                    $job = new BuildCacheForUrl($url);
                    Queue::push($job);
                }
            }

        }
        return $result;
    }

    public function buildCacheForEntry(Entry $entry)
    {
        $url = $entry->getUrl();
        if ($url) {
            $job = new BuildCacheForUrl($url);
            Queue::push($job);
        }

    }

    public function buildCacheForRelations(Entry $entry)
    {
        $entries = Entry::find()
                        ->siteId('*')
                        ->relatedTo($entry)
                        ->all();
        foreach($entries as $entry) {
            $this->buildCacheForEntry($entry);
        }

    }
}
