<?php
/**
 * Created by PhpStorm.
 * User: medar
 * Date: 08/12/2018
 * Time: 01:18
 */

namespace Drupal\rir_interface\Routing;


use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

    /**
     * Alters existing routes for a specific collection.
     *
     * @param RouteCollection $collection
     *   The route collection for adding routes.
     */
    protected function alterRoutes(RouteCollection $collection): void {
        $management_routes = [
            'view.adverts.page_manage_adverts',
            'view.agents.page_manage_advertisers',
            'view.details_requests.page_manage_details_requests',
            'view.details_requests.page_manage_property_requests',
            'view.news.page_manage_news',
            'view.banners.page_manage_banners',
            'view.manage_property_requests.page_manage_property_requests'
        ];
        foreach ($collection->all() as $name => $route) {
            if (in_array($name, $management_routes)) {
                $route->setOption('_admin_route', true);
            }
        }
    }
}
