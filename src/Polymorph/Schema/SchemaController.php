<?php

namespace Polymorph\Schema;

use Polymorph\Application\Application;
use Symfony\Component\HttpFoundation\Response;

/**
 * Polymorph Schema Controller
 *
 */
class SchemaController
{

    /**
     * Checks/Updates the schema an generates a list of applied versions
     *
     * @param Application $app
     * @param \stdClass $routeOptions The route definition as specified in the configuration file
     *
     * @return Response
     */
    public function handleSchemaVersionsRequest(Application $app, $routeOptions)
    {
        $appliedVersions = $app['schema']->checkSchema();
        $routeOptions->content = "
            <h2>$routeOptions->heading</h2>
            <ul>
                <li>" . join('</li><li>', $appliedVersions) . "</li>
            </ul>
        ";
        return $app->render($routeOptions->template, $routeOptions);
    }
}
