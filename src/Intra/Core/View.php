<?php
namespace Intra\Core;

use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Twig_Environment;
use Twig_Loader_Filesystem;

class View
{
    private $root;

    public function __construct($root)
    {
        $this->root = $root;
    }

    public function isExist($query)
    {
        $query = $this->filterQuery($query);
        $file = $this->root . '/' . $query . ".twig";

        return file_exists($file);
    }

    public function render($query, $context)
    {
        $query = $this->filterQuery($query);
        if (!$this->isExist($query)) {
            throw new FileNotFoundException($query);
        }
        $loader = new Twig_Loader_Filesystem($this->root);
        $twig = new Twig_Environment($loader, []);

        return $twig->render($query . '.twig', $context);
    }

    private function filterQuery($query)
    {
        //white list
        $query = preg_replace('/[^_\-\w\d\/\.]/', '', $query);
        //remove '/,/' '/../'
        $query = preg_replace('/\/\.+/', '', $query);
        $query = preg_replace('/\.+\//', '', $query);
        //remove trail '/'
        $query = preg_replace('/\/+$/', '', $query);

        return $query;
    }
}
