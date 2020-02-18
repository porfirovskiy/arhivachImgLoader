<?php

namespace App;

/* 
 * Class Loader for download images from site arhivach.ng
 */
class Loader {
    
    protected string $imagesClass = 'img_filename';
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
        
        $links = $this->getLinks();
        
        echo '<pre>';var_dump($links);die();
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
    protected function getLinks(): array {
        $urls = [];
        
        $dom = new \DOMDocument();
        $dom->loadHTML($this->html);
        $tagsA = $dom->getElementsByTagName('a');
        
        foreach ($tagsA as $tag) {
            if ($tag->getAttribute('class') == $this->imagesClass) {
                $urls[] = $tag->getAttribute('href');
            }
        }
        
        return $urls;
    }
    
    protected function createDir(string $url) {
        
    }
    
    protected function loadImages(array $imagesUrls) {
        
    }
    
    
    
}
