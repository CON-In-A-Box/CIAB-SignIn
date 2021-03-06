<?php declare(strict_types=1);
/*.
    require_module 'standard';
.*/
/**
 *  @OA\Tag(
 *      name="announcements",
 *      description="Features around text announcements to event and event staff"
 *  )
 *
 *  @OA\Schema(
 *      schema="announcement",
 *      @OA\Property(
 *          property="type",
 *          type="string",
 *          enum={"announcement"}
 *      ),
 *      @OA\Property(
 *          property="id",
 *          type="integer",
 *          description="announcement ID"
 *      ),
 *      @OA\Property(
 *          property="posted_on",
 *          type="string",
 *          format="date",
 *          description="Date the announcement was first posted"
 *      ),
 *      @OA\Property(
 *          property="department",
 *          description="Department for the announcement",
 *          oneOf={
 *              @OA\Schema(
 *                  ref="#/components/schemas/department"
 *              ),
 *              @OA\Schema(
 *                  type="integer",
 *                  description="Department Id"
 *              )
 *          }
 *      ),
 *      @OA\Property(
 *          property="posted_by",
 *          description="The member who created the announcement",
 *          oneOf={
 *              @OA\Schema(
 *                  ref="#/components/schemas/member"
 *              ),
 *              @OA\Schema(
 *                  type="integer",
 *                  description="Member Id"
 *              )
 *          }
 *      ),
 *      @OA\Property(
 *          property="scope",
 *          type="integer",
 *          description="The scope of the announcement"
 *      ),
 *      @OA\Property(
 *          property="text",
 *          type="string",
 *          description="Text of the announcement"
 *      )
 *  )
 *
 *  @OA\Schema(
 *      schema="announcement_list",
 *      allOf = {
 *          @OA\Schema(ref="#/components/schemas/resource_list")
 *      },
 *      @OA\Property(
 *          property="type",
 *          type="string",
 *          enum={"announcement_list"}
 *      ),
 *      @OA\Property(
 *          property="data",
 *          type="array",
 *          description="List of announcements",
 *          @OA\Items(
 *              ref="#/components/schemas/announcement"
 *          ),
 *      )
 *  )
 *
 *   @OA\Response(
 *      response="announce_not_found",
 *      description="Announcement not found in the system.",
 *      @OA\JsonContent(
 *          ref="#/components/schemas/error"
 *      )
 *   )
 **/

namespace App\Controller\Announcement;

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Atlas\Query\Select;
use App\Controller\BaseController;
use App\Controller\NotFoundException;
use App\Controller\IncludeResource;

abstract class BaseAnnouncement extends BaseController
{

    protected static $columnsToAttributes = [
    '"announcement"' => 'type',
    'AnnouncementID' => 'id',
    'DepartmentID' => 'department',
    'PostedOn' => 'posted_on',
    'PostedBy' => 'posted_by',
    'Scope' => 'scope',
    'Text' => 'text'
    ];


    public function __construct(Container $container)
    {
        parent::__construct('deadline', $container);
        \ciab\RBAC::customizeRBAC(array($this, 'customizeAnnouncementRBAC'));

        $this->includes = [
        new IncludeResource(
            '\App\Controller\Member\GetMember',
            'id',
            'posted_by'
        ),
        new IncludeResource(
            '\App\Controller\Department\GetDepartment',
            'name',
            'department'
        )
        ];

    }


    public function getAnnouncement($id)
    {
        $select = Select::new($this->container->db);
        $select->columns(...BaseAnnouncement::selectMapping());
        $select->from('Announcements');
        $select->whereEquals(['AnnouncementID' => $id]);
        $announce = $select->fetchOne();
        if (empty($announce)) {
            throw new NotFoundException('Announcement Not Found');
        }
        return $announce;

    }


    protected function filterAnnouncements($data)
    {
        if (!\ciab\RBAC::havePermission('api.get.announcement.all')) {
            foreach ($data as $index => $target) {
                if ($target['scope'] >= 2) {
                    if (!\ciab\RBAC::havePermission('api.get.announcement.'.$target['department'])) {
                        unset($data[$index]);
                    }
                } elseif ($target['scope'] == 1) {
                    if (!\ciab\RBAC::havePermission('api.get.announcement.staff')) {
                        unset($data[$index]);
                    }
                }
            }
        }

        return $data;

    }


    public function customizeAnnouncementRBAC($instance)
    {
        $positions = [];
        $select = Select::new($this->container->db);
        $select->columns('PositionID, Name')->from('ConComPositions')->orderBy('`PositionID` ASC');
        $values = $select->fetchAll();
        foreach ($values as $value) {
            $positions[intval($value['PositionID'])] = $value['Name'];
        }

        $select = Select::new($this->container->db);
        $select->columns('DepartmentID')->from('Departments');
        $values = $select->fetchAll();
        foreach ($values as $value) {
            $perm_del = 'api.delete.announcement.'.$value['DepartmentID'];
            $perm_pos = 'api.post.announcement.'.$value['DepartmentID'];
            $perm_put = 'api.put.announcement.'.$value['DepartmentID'];
            $perm_read = 'api.get.announcement.'.$value['DepartmentID'];
            $target_h = $value['DepartmentID'].'.'.array_keys($positions)[0];
            $target_r = $value['DepartmentID'].'.'.end(array_keys($positions));
            try {
                $role = $instance->getRole($target_h);
                $role->addPermission($perm_del);
                $role->addPermission($perm_pos);
                $role->addPermission($perm_put);
                $role = $instance->getRole($target_r);
                $role->addPermission($perm_read);
            } catch (Exception\InvalidArgumentException $e) {
                error_log($e);
            }
        }

        try {
            $role = $instance->getRole('all.staff');
            $role->addPermission('api.get.announcement.staff');
        } catch (Exception\InvalidArgumentException $e) {
            error_log($e);
        }

    }


    /* End BaseAnnouncement */
}
