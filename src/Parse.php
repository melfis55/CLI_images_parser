<?php


class Parse extends Command
{
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
        $ch = curl_init ();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.47 Safari/536.11",
            CURLOPT_HEADER => 0,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_MAXREDIRS => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_BINARYTRANSFER => 1,
        ]);

        $content = curl_exec($ch);
        curl_close($ch);

        return $content;
    }

    protected function checkParams()
    {
        $this->ensureParamExists('url');
    }

    public function execute() {
        if(!($this->getContent($this->getParam('url')))) {
            echo "Wrong site, or check the link." . "\n";
            exit;
        }

        $this->parser($this->getParam('url'));

        $this->writeToCsv($this->allImages, $this->getParam('url'));

    }

    public function writeToCsv($content, $urlName) {
        $fileName = $this->getDomainName($urlName);

        $fp = fopen(__DIR__ . '/../csv/' . $fileName . '.csv', 'w');

//        fputcsv($fp, array_keys($this->allImages)); //uncomment if needs keys in csv

        foreach ($content as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);

        echo "\n" . 'Csv file with result ' . __DIR__ . '/' . $fileName . '.csv' . "\n";

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