<?php declare(strict_types=1);
/*.
    require_module 'standard';
.*/
/**
 *   @OA\Response(
 *      response="artshow_configuration_not_found",
 *      description="Artshow configuration not found in the system.",
 *      @OA\JsonContent(
 *          ref="#/components/schemas/error"
 *      )
 *   )
 **/

namespace App\Modules\artshow\Controller\Configuration;

use Slim\Container;
use App\Controller\PermissionDeniedException;
use App\Modules\artshow\Controller\BaseArtshow;

abstract class BaseConfiguration extends BaseArtshow
{


    public function __construct(Container $container)
    {
        parent::__construct("configuration", $container);

    }


    protected function checkPutPermission($request, $response)
    {
        if (!\ciab\RBAC::havePermission('api.set.artshow.configuration')) {
            throw new PermissionDeniedException('Permission Denied');
        }

    }


    protected function checkPostPermission($request, $response)
    {
        if (!\ciab\RBAC::havePermission('api.set.artshow.configuration')) {
            throw new PermissionDeniedException('Permission Denied');
        }

    }


    /* End BaseConfiguration */
}
