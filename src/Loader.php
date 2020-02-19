<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;

/* 
 * Class Loader for download images from site arhivach.ng
 */
class Loader {
    
    const LINK_CONTAINER_CLASS = 'img_filename';
    const BASE_URL = 'https://arhivach.ng/';

    protected string $url = '';
    protected string $html = '';

    /**
     * 
     * @param type $url
     */
    public function __construct($url) {
        $this->url = $url;
    }


    public function load(): void {
        
        $this->getPageContent();
        
        $urls = $this->getUrls();
        
        $this->loadImages($urls);
        
        //echo '<pre>';var_dump($links);die();
    }
    
    /**
     * 
     * @return void
     */
    protected function getPageContent(): void {
        $this->html = file_get_contents($this->url);
    }

    /**
     * 
     * @return array
     */
    protected function getUrls(): array {
        $urls = [];
        
        $dom = new \DOMDocument();
        $dom->loadHTML($this->html);
        $tagsA = $dom->getElementsByTagName('a');
        
        foreach ($tagsA as $tag) {
            if ($tag->getAttribute('class') == self::LINK_CONTAINER_CLASS) {
                $url = $tag->getAttribute('href');
                $urls[$this->getImageName($url)] = $this->setCurrentUrl($url);
            }
        }
        
        return $urls;
    }
    
    /**
     * 
     * @param string $url
     * @return string
     */
    protected function getImageName(string $url): string {
        $result = pathinfo($url);
        return $result['basename'];
    }
    
    /**
     * 
     * @param string $url
     * @return string
     */
    protected function setCurrentUrl(string $url): string {
        $result = parse_url($url);
        return $result['path'];
    }


    protected function createDir(string $url) {
        
    }
    
    protected function loadImages(array $imagesUrls) {
        $client = new Client(['base_uri' => self::BASE_URL]);

        // Initiate each request but do not block
        $promises = $this->getPromises($client, $imagesUrls);

        // Wait for the requests to complete; throws a ConnectException
        // if any of the requests fail
        $responses = Promise\unwrap($promises);

        // Wait for the requests to complete, even if some of them fail
        $responses = Promise\settle($promises)->wait();
    }
    
    /**
     * 
     * @param Client $client
     * @param array $imagesUrls
     * @return array
     */
    protected function getPromises(Client $client, array $imagesUrls): array {
        $promises = [];
        
        foreach ($imagesUrls as $key => $url) {
            $promises[$key] = $client->getAsync($url, ['sink' => $key]);
        }
        
        return $promises;
    }
}
