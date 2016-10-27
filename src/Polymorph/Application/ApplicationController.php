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
     * Generates a `manifest.json` response
     *
     * @param Application $app
     *
     * @return Response A Response instance
     */
    public function handleManifestRequest(Application $app)
    {
        $template = $app->config('templates')->manifest;
        $response = $app->render($template);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

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
     * Generates a (default) welcome response
     *
     * @param Application $app
     *
     * @return Response A Response instance
     */
    public function handleHomeRequest(Application $app)
    {
        $params = [
            'pageTitle' => 'Welcome',
            'content' => 'Welcome to Polymorph'
        ];
        $template = $app->config('templates')->content;
        return $app->render($template, $params);
    }
}
