<?php declare(strict_types=1);
/*.
    require_module 'standard';
.*/

/**
 *  @OA\Get(
 *      tags={"announcements"},
 *      path="/announcement/{id}",
 *      summary="Gets an announcement",
 *      @OA\Parameter(
 *          description="Id of the announcement",
 *          in="path",
 *          name="id",
 *          required=true,
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\Parameter(
 *          ref="#/components/parameters/short_response",
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Announcement found",
 *          @OA\JsonContent(
 *           ref="#/components/schemas/announcement"
 *          ),
 *      ),
 *      @OA\Response(
 *          response=401,
 *          ref="#/components/responses/401"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          ref="#/components/responses/announce_not_found"
 *      ),
 *      security={{"ciab_auth":{}}}
 *  )
 **/

namespace App\Controller\Announcement;

use Slim\Http\Request;
use Slim\Http\Response;

class GetAnnouncement extends BaseAnnouncement
{


    public function buildResource(Request $request, Response $response, $params): array
    {
        $target = $this->getAnnouncement($params['id']);

        if ($target['scope'] >= 2) {
            $permissions = ['api.get.announcement.all',
            'api.get.announcement.'.$target['department']];
            $this->checkPermissions($permissions);
        } elseif ($target['scope'] == 1) {
            $permissions = ['api.get.announcement.all',
            'all.staff'];
            $this->checkPermissions($permissions);
        }

        return [
        \App\Controller\BaseController::RESOURCE_TYPE,
        $target
        ];

    }


    /* end GetAnnouncement */
}
