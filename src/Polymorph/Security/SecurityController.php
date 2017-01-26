<?php

namespace Polymorph\Security;

use Polymorph\Application\Application;
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
     * Serves a login form
     *
     * @param Application $app Application instance
     *
     * @return Response A Response instance
     */
    public function renderLoginForm(Application $app)
    {
        $params = [
            'pageTitle' => 'Login',
            'token' => $app['security']->getToken('login'),
            'usernameLabel' => 'Email',
            'passwordLabel' => 'Password',
            'buttonLabel' => 'Sign in',
            'errorText' => 'Wrong email or password. Please try again'
        ];
        $template = $app->config('templates')->login;
        return $app->render($template, $params);
    }

    /**
     * Handles a login request
     *
     * @param Application $app
     * @param Request $request
     * @return JsonResponse With a `success` field
     */
    public function handleLoginRequest(Application $app, Request $request)
    {
        $successResponse = new JsonResponse([ 'success' => true ]);
        $errorResponse = new JsonResponse([ 'success' => false ]);

        // check token
        if (!$app['security']->validateToken('login', $request->get('token'))) {
            return $errorResponse;
        }

        // check credentials
        $username = $request->get('username', '');
        $password = $request->get('password', '');
        if (!$app['users']->validateCredentials($username, $password)) {
            return $errorResponse;
        }

        // activate user
        if (!$app['users']->setCurrentUser($username)) {
            return $errorResponse;
        }

        return $successResponse;
    }
}
