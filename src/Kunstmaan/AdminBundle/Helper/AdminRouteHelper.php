<?php

namespace Kunstmaan\AdminBundle\Helper;

/**
 * Class AdminRouteHelper
 */
class AdminRouteHelper
{

    /**
     * @var string $adminKey
     */
    protected $adminKey;

    /**
     * @param string $adminKey
     */
    public function __construct($adminKey)
    {
        $this->adminKey = $this->normalizeUrlSlice($adminKey);
    }

    /**
     * Checks wether the given url poins to an admin route
     * @param string $url
     *
     * @return bool
     */
    public function isAdminRoute($url)
    {
        //If the url contains an admin part and a preview part then it is not an admin route
        preg_match(sprintf('/^(\/app_[a-zA-Z]+\.php)?\/([a-zA-Z_-]{2,5}\/)?%s(\/.*)?\/preview/', $this->adminKey), $url, $matches);

        if (count($matches) > 0) {
            return false;
        }

        preg_match(sprintf('/^\/(app_[a-zA-Z]+\.php\/)?([a-zA-Z_-]{2,5}\/)?%s\/(.*)/', $this->adminKey), $url, $matches);

        // Check if path is part of admin area
        if (count($matches) === 0) {
            return false;
        }

        return true;
    }

    /**
     * @param string $urlSlice
     *
     * @return string
     */
    protected function normalizeUrlSlice($urlSlice)
    {
        /* Get rid of exotic characters that would break the url */
        $urlSlice = filter_var($urlSlice, FILTER_SANITIZE_URL);

        /* Remove leading and trailing slashes */
        $urlSlice = trim($urlSlice, '/');

        /* Make sure our $urlSlice is literally used in our regex */
        $urlSlice = preg_quote($urlSlice);

        return $urlSlice;
    }
}
