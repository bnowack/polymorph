<?php

namespace Polymorph\Security;

use Polymorph\Application\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Polymorph Login Controller
 *
 */
class LoginController
{

    /**
     * Handles a login request
     *
     * @param Application $app
     * @param Request $request
     * @param \stdClass $routeOptions The route definition as specified in the configuration file
     *
     * @return JsonResponse|Response With a `success` field
     */
    public function handleLoginRequest(Application $app, Request $request, $routeOptions)
    {
        $apiToken = $request->get('token');

        // show form if this is not an API request
        if (!$apiToken) {
            return $this->handleLoginFormRequest($app, $routeOptions);
        }

        // validate API token
        if (!$app['security']->validateToken('login', $apiToken)) {
            return new JsonResponse([ 'success' => false,  'message' => 'Invalid token']);
        }

        // check credentials
        $username = $request->get('username', '');
        $password = $request->get('password', '');
        if (!$app['users']->validateCredentials($username, $password)) {
            return new JsonResponse([ 'success' => false,  'message' => $routeOptions->elementData->errorText]);
        }

        // activate user
        if (!$app['users']->setCurrentUser($username)) {
            return new JsonResponse([ 'success' => false,  'message' => 'Could not set user']);
        }

        // signal success
        return new JsonResponse([ 'success' => true ]);
    }

    /**
     * Creates a login form response
     *
     * @param Application $app
     * @param \stdClass $routeOptions The route definition as specified in the configuration file
     *
     * @return Response A Response instance
     */
    public function handleLoginFormRequest(Application $app, $routeOptions)
    {
        $routeOptions->elementData->token = $app['security']->getToken('login')->getValue();
        return $app->render($routeOptions->template, $routeOptions);
    }
}
