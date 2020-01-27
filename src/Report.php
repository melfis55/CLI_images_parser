<?php
namespace src;


class Report extends Command
{
    public function execute()
    {
        $this->readFromCsv($this->getParam('url'));
    }

    protected function checkParams()
    {
        $this->ensureParamExists('url');
    }

    public function readFromCsv($urlName)
    {
        $fileName = $this->getDomainName($urlName);

        if (!file_exists(__DIR__ . '/../csv/' . $fileName . '.csv')) {
            throw new \Exception('No such file in directory, check directory "csv" or parse this site.');
        }
        $csv = array_map('str_getcsv', file(__DIR__ . '/../csv/' . $fileName . '.csv'));
        print_r($csv);
    }
}