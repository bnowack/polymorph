<?php

namespace Polymorph\Security;

use Polymorph\Application\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Polymorph Logout Controller
 *
 */
class LogoutController
{

    /**
     * Signs a user out
     *
     * @param Application $app
     * @param Request $request
     * @param \stdClass $routeOptions The route definition as specified in the configuration file
     *
     * @return Response A Response instance
     */
    public function handleLogoutRequest(Application $app, Request $request, $routeOptions)
    {
        // validate API token
        if (!$app['security']->validateToken('logout', $request->get('token'))) {
            return new JsonResponse([ 'success' => false,  'message' => 'Invalid token']);
        }

        /** @var Session $session */
        $session = $app['session'];
        $session->set('user', null);

        return new JsonResponse([ 'success' => true, 'targetHref' => $routeOptions->targetHref]);
    }
}
