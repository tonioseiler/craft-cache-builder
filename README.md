# Cache Builder plugin for Craft CMS 4.x

Rebuilds your cache including image transforms

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require /cache-builder

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Cache Builder.

## Cache Builder Overview

Configure the sites and sections the cache builder will visit to force the cache to be rebuilt and force genering all image transforms.
Rebuild your cache by a cronjob on a regular basis or after saving entries in the backend.
This can speedup your craft website making pages load faster.
Use the cachebuilder after deploying a new release or after changing transform settings.

Features :
* Configure the entries which will trigger the cache builder by site and section.
* Add additinal Extra Urls.
* Manually trigger rebuilding of cache.
* Trigger rebuilding of cache by cronjob.
* Automatically rebuild cache after saving elements.

## Configuring Cache Builder

In the Control Panel go to Settings -> Plugins -> Cache Builder
### Options
* Configure if cache should be deleted before the cronjob builds the new cache.
* Configure if cache should be rebuild after saving new entries.

### Rebuild cache for the selected sections
Configure which sites and section sshould be rebuilt by the cronjob.
### Forced Url's
Configure which urls should be visited after saving any element, can be something like the home page or the product overview or similar.

### Extra Url's
Configure which urls should be visited by the cronjob but do not belong to any entry. Can be stuff like a sitemap, some custom routes or similar.

## Using Cache Builder
* After installation and configuration it is recommenden to run the console command once. The console command can also be userfull to regenerate the cache after a update.

``php <path-to-your-craft>/craft cache-builder/default/rebuild && php <path-to-your-craft>/craft queue/run ``

* If you wish to rebuild thee cache regularly for any reason, install the console command as a cronjob.
* If you configured the cache builder to rebuild after saving entries, your cache should always eb up to date.

## Cache Builder Roadmap

TBD

* Release it

Brought to you by [Furbo GmbH](https://furbo.ch)
