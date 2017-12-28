<?php
namespace Intra\Core;

use Symfony\Component\HttpFoundation\Request;

/**
 * @property mixed             routeFile
 * @property Request           request
 * @property string            $query
 * @property ControlRouteMatch lastMatch
 */
class Route
{
    private $path;
    private $isAlreadyRouted;

    public function __construct($path)
    {
        $this->path = $path;
        $this->isAlreadyRouted = false;
    }

    public function isExist()
    {
        $this->routeFile = $this->path . '/__route.php';

        return file_exists($this->routeFile);
    }

    public function route($query, $request)
    {
        $this->request = $request;
        $this->query = $query;

        include $this->routeFile;
        $this->postProcessLastMatchIfExist();

        return $this->query;
    }

    public function postProcessLastMatchIfExist()
    {
        if (isset($this->lastMatch)) {
            $lastMatched = $this->lastMatch;
            $this->lastMatch = null;

            if ($lastMatched->__success()) {
                $this->isAlreadyRouted = true;
                $this->query = $lastMatched->__getQuery();
                $parameters = $lastMatched->__getParameter();
                foreach ($parameters as $key => $value) {
                    $this->request->attributes->set($key, $value);
                }
            }
        }
    }

    /**
     * @param $pattern
     *
     * @return ControlRouteMatch|ControlRouteNull
     */
    public function matchIf($pattern)
    {
        $this->postProcessLastMatchIfExist();
        if ($this->isAlreadyRouted) {
            return new ControlRouteNull();
        }

        $regex = '/\{(\w+)\}/';

        //find paramaters

        preg_match_all($regex, $pattern, $match);
        $parameterNames = $match[1];

        //build regex for parameter

        $pattern_regex = $pattern;
        $pattern_regex = preg_replace('/^\/+|\/+$/', '', $pattern_regex);
        $pattern_regex = str_replace('/', '\\' . '/+', $pattern_regex);
        $pattern_regex = preg_replace($regex, '([^\/\\?&]+)', $pattern_regex);
        $pattern_regex = '/^' . $pattern_regex . '\/*$/';

        if (preg_match($pattern_regex, $this->query, $match)) {
            $parameters = [];
            foreach ($parameterNames as $index => $parameterName) {
                $parameters[$parameterName] = $match[$index + 1];
            }

            if (count($match) > 2) {
                $unmatchedQueryTail = end($match);
            } else {
                $unmatchedQueryTail = '';
            }

            $this->lastMatch = new ControlRouteMatch($this->query, $unmatchedQueryTail, $parameters, $this->request);

            return $this->lastMatch;
        }

        return new ControlRouteNull();
    }
}
