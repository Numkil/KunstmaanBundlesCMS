<?php

namespace Kunstmaan\AdminBundle\Helper;

use Kunstmaan\NodeBundle\Router\SlugRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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
     * @var Request $request
     */
    protected $request;

    /**
     * @param string $adminKey
     * @param RequestStack $requestStack
     */
    public function __construct($adminKey, RequestStack $requestStack)
    {
        $this->adminKey = $adminKey;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Checks wether the given url poins to an admin route
     * @param string $url
     *
     * @return bool
     */
    public function isAdminRoute($url)
    {
        if ($this->matchesPreviewRoute($url)) {
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
     * Tries to match a given route, if able to match it will return true if it was matched from
     * "slug_preview". All other cases it will return false
     *
     * @param string $url
     *
     * @return boolean
     */
    public function matchesPreviewRoute($url)
    {
        $routeName = $this->request->get('_route');

        return $routeName == SlugRouter::$SLUG_PREVIEW;
    }
}
