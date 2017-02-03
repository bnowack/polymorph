<?php

namespace Polymorph\Security;

use Polymorph\Application\Application;
use Polymorph\User\User;
use Polymorph\User\UserServiceProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Polymorph Password Controller
 *
 */
class PasswordController
{

    /**
     * @var string
     */
    protected $tokenId = 'change-password';

    /**
     * Handles a password-change request
     *
     * @param Application $app
     * @param Request $request
     * @param \stdClass $routeOptions The route definition as specified in the configuration file
     *
     * @return JsonResponse|Response With a `success` field
     */
    public function handlePasswordRequest(Application $app, Request $request, $routeOptions)
    {
        $apiToken = $request->get('token');

        $users = $app['users'];/** @var UserServiceProvider $users */

        // show form if this is not an API request
        if (!$apiToken) {
            return $this->handlePasswordFormRequest($app, $routeOptions);
        }

        // validate API token
        if (!$app['security']->validateToken($this->tokenId, $apiToken)) {
            return new JsonResponse([ 'success' => false,  'message' => 'Invalid token']);
        }

        // check credentials
        $session = $app['session'];/** @var Session $session */
        $username = $session->get('user');
        $oldPassword = $request->get('old-password', '');

        if (!$users->validateCredentials($username, $oldPassword)) {
            return new JsonResponse([ 'success' => false,  'message' => $routeOptions->elementData->errorText]);
        }

        // verify confirmed pwd
        $newPassword = $request->get('new-password', '');
        $confirmedPassword = $request->get('confirmed-password', '');
        if ($newPassword !== $confirmedPassword) {
            return new JsonResponse([ 'success' => false,  'message' => $routeOptions->elementData->errorText]);
        }

        // all good, change password
        $user = $users->getCurrentUser();/** @var User $user */
        $user->setPassword($users->encodePassword($newPassword));
        $success = $users->saveUser($user);
        if (!$success) {
            return new JsonResponse([ 'success' => false,  'message' => $routeOptions->elementData->errorText]);
        }

        // signal success
        return new JsonResponse([ 'success' => true,  'message' => $routeOptions->elementData->successText]);
    }

    /**
     * Creates a password form response
     *
     * @param Application $app
     * @param \stdClass $routeOptions The route definition as specified in the configuration file
     *
     * @return Response A Response instance
     */
    public function handlePasswordFormRequest(Application $app, $routeOptions)
    {
        $routeOptions->elementData->token = $app['security']->getToken($this->tokenId)->getValue();
        return $app->render($routeOptions->template, $routeOptions);
    }
}
