<?php

abstract class Command
{
    /** @var array */
    private $params;

    public function __construct($params)
    {
        $this->params = $params;
        $this->checkParams();
    }

    abstract public function execute();

    abstract protected function checkParams();

    protected function getParam($paramName)
    {
        return $this->params[$paramName] ?? null;
    }

    public function getDomainName($name) {
        $fileName = parse_url($name, PHP_URL_HOST);
        if (!$fileName) {
            return $name;
        }
        return $fileName;
    }

    protected function ensureParamExists($paramName)
    {
        if (!isset($this->params[$paramName])) {
            throw new Exception('Param with name "' . $paramName . '" is not set! Please write "php cli.php help".');
        }
    }
}