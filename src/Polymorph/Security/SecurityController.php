<?php

namespace Polymorph\Security;

use Polymorph\Application\Application;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Polymorph Security Controller
 *
 */
class SecurityController
{

    /**
     * Provides information about currently logged-in user
     *
     * @param Application $app Application instance
     * @param \stdClass $routeOptions The route definition as specified in the configuration file
     *
     * @return Response A JSON Response
     */
    public function handleAccountInfoRequest(Application $app, $routeOptions)
    {
        /* @var User $user */
        $user = $app['users']->getCurrentUser();
        $response = [
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
            'loginHref' => $routeOptions->loginHref,
            'loginLabel' => $routeOptions->loginLabel,
            'logoutHref' => $routeOptions->logoutHref,
            'logoutLabel' => $routeOptions->logoutLabel,
            'logoutToken' => $app['security']->getToken('logout')->getValue()
        ];
        return new JsonResponse($response);
    }

}
