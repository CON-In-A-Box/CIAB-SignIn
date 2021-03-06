<?php declare(strict_types=1);
/*.
    require_module 'standard';
.*/

/**
 *  @OA\Get(
 *      tags={"members"},
 *      path="/member/find",
 *      summary="Search for a member based on the query",
 *      @OA\Parameter(
 *          description="Query string",
 *          in="query",
 *          name="q",
 *          required=true,
 *          @OA\Schema(type="string")
 *      ),
 *      @OA\Parameter(
 *          description="Comma seperated list of attributes to be searched, default = 'all'",
 *          in="query",
 *          name="from",
 *          required=false,
 *          @OA\Schema(
 *              type="array",
 *              @OA\Items(
 *                  type="string",
 *                  enum={"all","email","id","legal_name","name","badge"}
 *              )
 *          ),
 *          style="simple",
 *          explode=false
 *      ),
 *      @OA\Parameter(
 *          description="Allow partial matches, default is false",
 *          in="query",
 *          name="partial",
 *          required=false,
 *          @OA\Schema(type="boolean")
 *      ),
 *      @OA\Parameter(
 *          ref="#/components/parameters/max_results",
 *      ),
 *      @OA\Parameter(
 *          ref="#/components/parameters/page_token",
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Member(s) found",
 *          @OA\JsonContent(
 *           ref="#/components/schemas/member_list"
 *          ),
 *      ),
 *      @OA\Response(
 *          response=401,
 *          ref="#/components/responses/401"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          ref="#/components/responses/member_not_found"
 *      ),
 *      security={{"ciab_auth":{}}}
 *  )
 **/

namespace App\Controller\Member;

use Slim\Http\Request;
use Slim\Http\Response;
use Atlas\Query\Select;

use App\Controller\NotFoundException;
use App\Controller\InvalidParameterException;

class FindMembers extends BaseMember
{

    /**
     * boolean
     */
    public $internal = false;


    public function buildResource(Request $request, Response $response, $args): array
    {
        $q = $request->getQueryParam('q', null);
        if ($q === null) {
            throw new InvalidParameterException("'q' not present");
        }
        $from = $request->getQueryParam('from', 'all');
        if ($from == 'all') {
            $from = "email,id,legal_name,name,badge";
        }
        $from = explode(',', trim($from));
        $p = $request->getQueryParam('partial', 'false');
        $partial = filter_var($p, FILTER_VALIDATE_BOOLEAN);

        $select = Select::new($this->container->db)
            ->columns(...BaseMember::selectMapping())
            ->columns('(CASE WHEN `PreferredFirstName` IS NOT NULL THEN `PreferredFirstName` ELSE `FirstName` END) AS `first_name`')
            ->columns('(CASE WHEN `PreferredLastName` IS NOT NULL THEN `PreferredLastName` ELSE `LastName` END) AS `last_name`')
            ->from('Members')
            ->orderBy('AccountID ASC');

        if (!$this->internal && !\ciab\RBAC::havePermission('api.get.member')) {
            $id = $request->getAttribute('oauth2-token')['user_id'];
            $select->andWhere('AccountID = ', $id);
        }

        if (in_array('email', $from)) {
            if ($partial) {
                $select->orWhere('Email LIKE ', "%$q%");
            } else {
                $select->orWhere('Email = ', $q);
            }
        }
        if (in_array('id', $from)) {
            if ($q[0] == 'A') {
                $select->orWhere('AccountID = ', substr($q, 1));
            } else {
                $select->orWhere('AccountID = ', $q);
            }
        }
        if (in_array('legal_name', $from)) {
            $names = explode(" ", $q);
            if ($partial) {
                $select->orWhere("FirstName LIKE '%$names[0]%'");
                if (count($names) > 1) {
                    $select->orWhere("LastName LIKE '%$names[1]%'");
                }
            } else {
                if (count($names) != 2) {
                    throw new NotFoundException('Member Not Found');
                }
                $select->orWhere('(');
                $select->catWhere("FirstName = ", $names[0]);
                $select->catWhere(" AND LastName = ", $names[1]);
                $select->catWhere(')');
            }
        }
        if (in_array('name', $from)) {
            $names = explode(" ", $q);
            if ($partial) {
                if (count($names) > 1) {
                    $select->orWhere("PreferredLastName LIKE '%{$names[1]}%'");
                    $select->orWhere("LastName LIKE '%{$names[1]}%'");
                } else {
                    $select->orWhere("PreferredLastName LIKE '%{$names[0]}%'");
                    $select->orWhere("LastName LIKE '%{$names[0]}%'");
                }
                $select->orWhere("PreferredFirstName LIKE '%{$names[0]}%'");
                $select->orWhere("FirstName LIKE '%{$names[0]}%'");
            } else {
                if (count($names) != 2) {
                    throw new NotFoundException('Member Not Found');
                }
                $select->orWhere('((');
                $select->catWhere(' PreferredFirstName = ', $names[0]);
                $select->catWhere(' OR FirstName = ', $names[0]);
                $select->catWhere(' ) AND ( ');
                $select->catWhere('PreferredLastName = ', $names[1]);
                $select->catWhere(' OR LastName = ', $names[1]);
                $select->catWhere(' ))');
            }
        }
        if (in_array('badge', $from)) {
            $sub = $select->subselect()
                ->columns('AccountID')
                ->from('Registrations');
            if ($partial) {
                $sub->orWhere("BadgeName LIKE '%$q%'");
            } else {
                $sub->orWhere('BadgeName = ', $q);
            }
            $select->orWhere('AccountID IN ', $sub);
        }

        $result = $select->fetchAll();
        if (count($result) == 0) {
            throw new NotFoundException('Member Not Found');
        }

        $output = ['type' => 'member_list'];
        return [
        \App\Controller\BaseController::LIST_TYPE,
        $result,
        $output];

    }


    /* end FindMembers */
}
