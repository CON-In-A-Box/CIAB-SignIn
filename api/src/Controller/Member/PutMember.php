<?php declare(strict_types=1);
/*.
    require_module 'standard';
.*/

namespace App\Controller\Member;

use Slim\Http\Request;
use Slim\Http\Response;

class PutMember extends BaseMember
{

    public $privilaged = false;


    protected static function checkBoolParam(&$body, $param)
    {
        if (array_key_exists($param, $body)) {
            $value = (int) filter_var($body[$param], FILTER_VALIDATE_BOOLEAN);
            $body[$param] = $value;
        }

    }


    protected static function checkDateParam(&$body, $param)
    {
        if (array_key_exists($param, $body)) {
            $value = date("Y-m-d", strtotime($body[$param]));
            $body[$param] = "$value";
        }

    }


    public function buildResource(Request $request, Response $response, $args): array
    {
        $data = $this->findMember($request, $response, $args, 'name');
        if (gettype($data) === 'object') {
            return [
            \App\Controller\BaseController::RESULT_TYPE,
            $data];
        }
        $accountID = $data['id'];

        if (!$this->privilaged) {
            $user = $request->getAttribute('oauth2-token')['user_id'];
            if ($accountID != $user &&
                !\ciab\RBAC::havePermission("api.put.member")) {
                return [
                \App\Controller\BaseController::RESULT_TYPE,
                $this->errorResponse($request, $response, 'Permission Denied', 'Permission Denied', 403)];
            }
        }

        $body = $request->getParsedBody();

        PutMember::checkBoolParam($body, 'Deceased');
        PutMember::checkBoolParam($body, 'DoNotContact');
        PutMember::checkBoolParam($body, 'EmailOptOut');
        PutMember::checkDateParam($body, 'Birthdate');

        \updateAccount($body, $accountID);

        $target = new \App\Controller\Member\GetMember($this->container);
        $data = $target->buildResource($request, $response, $args)[1];
        return [
        \App\Controller\BaseController::RESOURCE_TYPE,
        $target->arrayResponse($request, $response, $data)
        ];

    }


    /* end PutMember */
}
