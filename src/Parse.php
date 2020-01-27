<?php
namespace src;

use yii\db\Exception;

class Parse extends Command
{
    const USERAGENT = "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11";

    public $allLinks = [];
    public $allImages = [];
    public $wasLinks = [];
    public $content;

    public function searchPreg($pattern) {
        preg_match_all($pattern, $this->content, $match);
        return $match[1];
    }

    public function findImages() {
        $pattern = '/<img.*src="(.+)".*>/U';
        return $this->searchPreg($pattern);
    }

    public function findLinks() {
        $pattern = '/<a.*href="(.+)".*>/U';
        return $this->searchPreg($pattern);
    }

    public function getContent($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => self::USERAGENT,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $content = curl_exec($ch);
        curl_close($ch);

        return $content;
    }

    protected function checkParams()
    {
        $this->ensureParamExists('url');
    }

    public function notValidUrl() {
        return !filter_var($this->getParam('url'), FILTER_VALIDATE_URL);
    }
    public function execute() {
        if ($this->notValidUrl()) {
            throw new \Exception('Url ' . $this->getParam('url') . ' not valid');
        }

        if(!($this->getContent($this->getParam('url')))) {
            throw new \Exception('Url ' . $this->getParam('url') . ' don\'t have content');
        }

        $this->parser($this->getParam('url'));

        $this->writeToCsv($this->allImages, $this->getParam('url'));

    }

    public function writeToCsv($content, $urlName) {
        $fileName = $this->getDomainName($urlName);

        $filePath = dirname(__DIR__) . '/csv/';
        $fp = fopen($filePath . $fileName . '.csv', 'w');

        foreach ($content as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);

        echo "\n" . 'Csv file with result ' . $filePath . $fileName . '.csv' . "\n";

    }

    public function parser($url)
    {
        if (in_array($url, $this->wasLinks)) {
            return;
        }
        echo '.';
        $this->wasLinks[] = $url;

        $this->content = $this->getContent($url);

        $this->separateLinks();
        $this->separateImages($url);

        foreach ($this->allLinks as $link) {
            $this->parser($link);
        }
    }

    public function separateLinks() {
        foreach ($this->findLinks() as $uri) {
            $mainUrl = str_replace('/', '\/', $this->getParam('url'));
            if(preg_match('/'. $mainUrl .'/', $uri)) {
                $this->allLinks[] = $uri;
                continue;
            } elseif (preg_match('/^\//', $uri)) {
                $this->allLinks[] = $this->getParam('url') . $uri;
            }
        }
        $this->allLinks = array_values(array_unique($this->allLinks));
    }

    public function separateImages($url) {
        $temp = [];
        $temp[] = $url;
        foreach ($this->findImages() as $image) {
            if (preg_match('/^\//', $image)) {
                $temp[] = $this->getParam('url') . $image;
            } else {
                $temp[] = $image;
            }
        }
        $this->allImages[] = $temp;
    }

}