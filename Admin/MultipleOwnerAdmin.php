<?php
/**
 * Created by PhpStorm.
 * User: aleksandrgordeev
 * Date: 09.10.15
 * Time: 12:50
 */
namespace Modules\Multiple\Admin;

use Mindy\Orm\Base;
use Mindy\Utils\RenderTrait;
use Modules\Admin\Components\ModelAdmin;

abstract class MultipleOwnerAdmin extends ModelAdmin
{
    use RenderTrait;

    protected $_multiple;

    public $updateTemplate = 'multiple/owner/update.html';
    public $createTemplate = 'multiple/owner/create.html';

    public function getMultiple()
    {
        return [];
    }

    public function getMultipleInit($owner = null)
    {
        if (!$this->_multiple) {
            $this->_multiple = [];
            $model = $this->getModel();

            foreach ($this->getMultiple() as $name => $admin) {
                $class = $admin['class'];

                $foreignField = isset($admin['foreignField']) ? $admin['foreignField'] : $model->normalizeTableName($model->classNameShort());

                $filter = [];
                $primaryKey = null;
                if ($owner && $owner->pk) {
                    $primaryKey = $owner->pk;
                    $filter = [$foreignField => $primaryKey];
                }

                $admin = new $class([
                    'moduleName' => $this->getModule()->getId(),
                    'filter' => $filter,
                    'ownerModel' => $owner,
                    'ownerField' => $name
                ]);

                $params = isset($_POST['search']) ? array_merge([
                    'search' => $_POST['search']
                ], $_GET) : $_GET;
                $admin->setParams($params);

                $this->_multiple[$name] = $admin;
            }
        }
        return $this->_multiple;
    }
}