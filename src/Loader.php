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
    const EXTENSIONS_LIST = ['png', 'jpg', 'jpeg', 'gif'];
    const STORAGE_DIR = 'storage';

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
        
        $this->printingLog("loading of images started!");
        
        $this->createDir($this->url);
        
        $this->getPageContent();
        
        $this->printingLog("html is loaded");
        
        $urls = $this->getUrls();
        
        $this->printingLog("urls list is formed [" . count($urls) . " items]");
        
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
        $tags = $dom->getElementsByTagName('a');
        
        foreach ($tags as $tag) {
            $urls = $this->getCurrentUrl($tag, $urls);
        }
        
        return $urls;
    }
    
    /**
     * 
     * @param \DOMElement $tag
     * @param array $urls
     * @return array
     */
    protected function getCurrentUrl(\DOMElement $tag, array $urls): array {
        if ($tag->getAttribute('class') == self::LINK_CONTAINER_CLASS) {
            $url = $tag->getAttribute('href');
            if ($this->isOnlyImageUrl($url)) {
                $urls[$this->getImageName($url)] = $this->setCurrentUrl($url);
           }
        }
        
        return $urls;
    }

    /**
     * 
     * @param string $url
     * @return bool
     */
    protected function isOnlyImageUrl(string $url): bool {
        $result = pathinfo($url);
        $extension = $result['extension'];
        
        if (in_array($extension, self::EXTENSIONS_LIST)) {
            return true;
        } else {
            return false;
        }
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

    /**
     * 
     */
    protected function createDir(): void {
        if (!file_exists(self::STORAGE_DIR)) {
            mkdir(self::STORAGE_DIR, 0777, true);
            $this->printingLog("dir " . self::STORAGE_DIR . " created");
        }
    }
    
    protected function loadImages(array $imagesUrls): void {
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
            $promises[$key] = $client->getAsync($url, ['sink' => self::STORAGE_DIR . "/" . $key]);
        }
        
        return $promises;
    }
    
    /**
     * 
     * @param string $message
     * @return void
     */
    protected function printingLog(string $message): void {
        echo date('Y-m-d H:m:i') . " " . $message . "\n";
    }
    
}
