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
     * @return Response
     */
    public function handleSchemaVersionsRequest(Application $app)
    {
        $appliedVersions = $app['schema']->checkSchema();
        $params = [
            'pageTitle' => 'Schema Versions',
            'content' => '
                <h2>Applied Schema Versions</h2>
                <ul>
                    <li>' . join('</li><li>', $appliedVersions) . '</li>
                </ul>
            '
        ];
        $template = $app->config('templates')->content;
        return $app->render($template, $params);
    }

}
