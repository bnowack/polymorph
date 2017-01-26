<?php

namespace Polymorph\Application;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Exception\FlattenException;

/**
 * Polymorph Application Controller
 *
 */
class ApplicationController
{

    /**
     * Generates an error response
     *
     * @param Application $app
     * @param \Exception $exception
     *
     * @return Response A Response instance
     */
    public function handleErrorRequest(Application $app, \Exception $exception)
    {
        // create flat trace for simplified access
        if (!$exception instanceof FlattenException) {
            $exception = FlattenException::create($exception);
        }

        // create list of exceptions, depending on debug mode
        $exceptions = $app['debug']
            ? array_merge([$exception], $exception->getAllPrevious()) // full dump when debug is true
            : [['statusCode' => $exception->getStatusCode(), 'message' => $exception->getMessage()]]; // basics only

        // prepare template parameters
        $params = [
            "pageTitle" => "Error {$exception->getStatusCode()}",
            "exceptions" => $exceptions,
            "meta" => [
                "robots" => 'noindex,nofollow'
            ]
        ];

        // render template
        $template = $app->config('templates')->error;
        return $app->render($template, $params);
    }

    /**
     * Generates a content template response
     *
     * @param Application $app
     * @param \stdClass $routeOptions The route definition as specified in the configuration file
     *
     * @return Response A Response instance
     */
    public function handleTemplateRequest(Application $app, $routeOptions = null)
    {
        // check role
        if (!empty($routeOptions->role) && !$app['users']->hasRole($routeOptions->role)) {
            $app->abort(401, $routeOptions->errorMessage);
        }

        $response = $app->render($routeOptions->template, $routeOptions);
        // set content type
        if (isset($routeOptions->contentType)) {
            $response->headers->set('Content-Type', $routeOptions->contentType);
        }
    }
}
