<?php

namespace furbo\cachebuilder\jobs;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class BuildCacheForUrl extends \craft\queue\BaseJob
{

    protected string $url;

    public function __construct(string $url) {
        parent::__construct(['description' => 'Building cache for '.$url]);
        $this->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        //call a guzzle request for the url
        $client = new Client([
            'timeout'  => 10.0
        ]);

        $count = 1;
        $request = new Request('GET', $this->url);
        $res = $client->sendAsync($request)->wait();
        $responseContent = $res->getBody()->getContents();

        //check source for generate-transform urls and call them
        $matches = [];
        $pattern = '/[^"\'=\s]+generate-transform\?transformId\=[\d]+/';
        preg_match_all($pattern,$responseContent,$matches,PREG_PATTERN_ORDER);

        $total = (count($matches[0]) + 1);

        $this->setProgress(
            $queue,
            $count / $total,
            \Craft::t('app', '{step, number} of {total, number}', [
                'step' => $count,
                'total' => $total,
            ])
        );
        $count++;

        
        foreach($matches[0] as $transFormUrl) {
            try {
                $request = new Request('GET', $transFormUrl);
                $res = $client->sendAsync($request)->wait();
            } catch (\Exception $e) {
                
            }
            $this->setProgress(
                $queue,
                $count / $total,
                \Craft::t('app', '{step, number} of {total, number}', [
                    'step' => $count,
                    'total' => $total,
                ])
            );
            $count++;
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return \Craft::t('cache-builder', 'Build cache for url');
    }
}
