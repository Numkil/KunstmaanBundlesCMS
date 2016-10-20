<?php

namespace Kunstmaan\AdminBundle\Helper;

use Kunstmaan\NodeBundle\Router\SlugRouter;

/**
 * Class AdminRouteHelper
 */
class AdminRouteHelper
{
    protected static $ADMIN_MATCH_REGEX = '/^\/(app_[a-zA-Z]+\.php\/)?([a-zA-Z_-]{2,5}\/)?%s\/(.*)/';

    /**
     * @var string $adminKey
     */
    protected $adminKey;

    /**
     * @var SlugRouter $slugRouter
     */
    protected $slugRouter;

    /**
     * @param string $adminKey
     * @param SlugRouter $slugRouter
     */
    public function __construct($adminKey, SlugRouter $slugRouter)
    {
        $this->adminKey = $this->normalizeUrlSlice($adminKey);
        $this->slugRouter = $slugRouter;
    }

    /**
     * Checks wether the given url poins to an admin route
     * @param string $url
     *
     * @return bool
     */
    public function isAdminRoute($url)
    {
        if ($this->slugRouter->matchesPreviewRoute($url)) {
            return false;
        }

        preg_match(sprintf(self::$ADMIN_MATCH_REGEX, $this->adminKey), $url, $matches);

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
