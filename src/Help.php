<?php


class Help
{
    public function execute()
    {
        echo 'All commands:' . "\n";
        echo '"php cli.php Parse -url=http://example.com" --- parse all images from site and write in CSV file' . "\n";
        echo '"php cli.php Report -url=http://example.com" --- show result from CSV file' . "\n";
    }
}