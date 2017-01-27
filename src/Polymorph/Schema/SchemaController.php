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
        // render versions as simple list
        $elementPath = "bower_components/polymorph/src/Polymorph/Application/elements/polymorph-app-simple-list.html";
        $routeOptions->element = $elementPath;
        $routeOptions->elementData = [
            'heading' => $routeOptions->heading,
            'items' => $appliedVersions
        ];
        return $app->render($routeOptions->template, $routeOptions);
    }
}
