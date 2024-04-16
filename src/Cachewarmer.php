<?php

/**
 * @file
 * Contains drunomics\SitemapCacheWarmer\Runner.
 */

namespace drunomics\SitemapCacheWarmer;

use GuzzleHttp\Client;
use Symfony\Component\Stopwatch\Stopwatch;
use vipnytt\SitemapParser;
use vipnytt\SitemapParser\Exceptions\SitemapParserException;

/**
 * The cache warmer.
 */
class Cachewarmer {

  /**
   * The config, based upon the SitemapParser config.
   *
   * @var mixed[]
   */
  protected $config;

  /**
   * Creates a new instance.
   *
   * @return static
   */
  public function __construct(array $config) {
    if (empty($config['base_url'])) {
      throw new \Exception("Missing base url");
    }
    $this->config = $config;
  }

  /**
   * Runs cache warming while printing to stdout.
   */
  public function run() {
    $client = $this->getHttpClient();
    $stopwatch = new Stopwatch();

    $stopwatch->start('urls');
    $urls = $this->getUrls($this->config['base_url']);
    $event = $stopwatch->stop('urls');
    $time = number_format($event->getDuration(), 2);
    echo "Fetched sitemap(s) in $time ms.\n";

    $stopwatch->start('warm');
    $counters = [];
    $times = [];
    foreach ($urls as $url) {
      $stopwatch->start('request');
      $response = $client->get($url, ['http_errors' => FALSE] + $this->config['guzzle']);
      $status = $response->getStatusCode();
      $event = $stopwatch->lap('request');

      $periods = $event->getPeriods();
      $time = number_format(end($periods)->getDuration(), 0);
      echo "[$status] {$time}ms - $url \n";
      $times[] = $time;
      // Count status codes.
      $group = (int) $status / 100;
      $counters[$group] = (isset($counters[$group]) ? $counters[$group] : 0) + 1;
    }
    $event = $stopwatch->stop('warm');
    $time = number_format($event->getDuration() / 1000, 2);
    $total_count = count($times);
    $average_time = round(array_sum($times) / $total_count, 2);
    echo "Fetched $total_count URLs in {$time}s, average: $average_time ms\n";
    ksort($counters);
    foreach ($counters as $group => $count) {
      echo "$count were HTTP " . $group . "xx responses\n";
    }
  }

  /**
   * Gets a new http client.
   *
   * @return Client
   */
  protected function getHttpClient() {
    return new Client();
  }

  /**
   * @param $base_url
   *
   * @throws \vipnytt\SitemapParser\Exceptions\SitemapParserException
   */
  public function getUrls($base_url) {
    try {
      $parser = new SitemapParser('Cachewarmer', $this->config);
      $parser->parseRecursive($base_url . '/robots.txt');

      // If nothing in robots.txt:
      if (!$parser->getQueue()) {
        $parser->parseRecursive($base_url . '/sitemap.xml');
      }
      $urls = array_keys($parser->getURLs());

      if (!$urls) {
        throw new \Exception("No URLs found?");
      }

      return $urls;
    }
    catch (SitemapParserException $e) {
      echo "Error parsing sitemap.";
      throw $e;
    }
  }

}
