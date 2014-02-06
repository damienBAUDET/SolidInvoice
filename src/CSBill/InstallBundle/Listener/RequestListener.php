<?php
/*
 * This file is part of the CSBill package.
 *
 * (c) Pierre du Plessis <info@customscripts.co.za>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CSBill\InstallBundle\Listener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use CSBill\InstallBundle\Installer\Installer;

/**
 * Listener class to intercept requests
 * and redirect to the installer if necessary
 */
class RequestListener
{
    /**
     * @var ContainerInterface $container
     */
    public $container;

    /**
     * Core paths for assets
     *
     * @var array $core_paths
     */
    protected $core_paths = array('css', 'images', 'js');

    /**
     * Core routes
     *
     * @var array $core_routes
     */
    protected $core_routes = array(	Installer::INSTALLER_ROUTE,
                                    Installer::INSTALLER_SUCCESS_ROUTE,
                                    Installer::INSTALLER_RESTART_ROUTE,
                                    '_installer_step',
                                    '_profiler',
                                    '_wdt'
                                  );

    /**
     * @param  GetResponseEvent $event
     * @return null
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->getRequestType() !== HttpKernel::MASTER_REQUEST) {
            return;
        }

        $route = $event->getRequest()->get('_route');

        $map = array_map(function ($route) use ($event) {
            return strpos($event->getRequest()->getPathInfo(), $route);
        }, $this->core_paths);

        if (!in_array($route, $this->core_routes) && !in_array(true, $map)) {
            $installer = $this->container->get('csbill.installer');

            if (!$installer->isInstalled()) {
                $response = new RedirectResponse($this->container->get('router')->generate(Installer::INSTALLER_ROUTE));

                $event->setResponse($response);
            }

            return null;
        }
    }

    /**
     * Sets an instance of the service container
     *
     * @param  ContainerInterface $container
     * @return void
     */

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
