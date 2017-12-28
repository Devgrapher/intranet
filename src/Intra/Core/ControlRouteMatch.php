<?php
namespace Intra\Core;

use Symfony\Component\HttpFoundation\Request;

class ControlRouteMatch
{
    private $query;
    private $unmatchedQueryTail;
    private $parameters;
    private $request;
    private $success;

    public function __construct($uri, $unmatchedQueryTail, $parameters, Request $request)
    {
        $this->query = $uri;
        $this->unmatchedQueryTail = $unmatchedQueryTail;
        $this->parameters = $parameters;
        $this->request = $request;
        $this->success = false;
    }

    public function query($string)
    {
        $this->query = $string;
        $this->success = true;
    }

    public function __success()
    {
        return $this->success;
    }

    public function __getQuery()
    {
        return $this->query . '/' . $this->unmatchedQueryTail;
    }

    public function __getParameter()
    {
        return $this->parameters;
    }

    /**
     * @param $string
     *
     * @return ControlRouteMatch|ControlRouteNull
     * @throws \Exception
     */
    public function assertAsInt($string)
    {
        if (!preg_match('/^\d+$/', $this->parameters[$string])) {
            return new ControlRouteNull();
            //throw new \Exception("parameter $string is not integer");
        }

        return $this;
    }

    public function assertInArray($string, $array)
    {
        if (!in_array($this->parameters[$string], $array)) {
            return new ControlRouteNull();
            //throw new \Exception("parameter $string is not valid");
        }

        return $this;
    }

    /**
     * @param $string
     *
     * @return ControlRouteMatch|ControlRouteNull
     */
    public function isMethod($string)
    {
        if ($this->request->isMethod($string)) {
            return $this;
        }

        return new ControlRouteNull();
    }

    /**
     * @param $key
     * @param $value
     *
     * @return ControlRouteMatch|ControlRouteNull
     */
    public function setRequest($key, $value)
    {
        $this->request->attributes->set($key, $value);

        return $this;
    }
}
