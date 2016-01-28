<?php
/**
 * Created by PhpStorm.
 * User: aleksandrgordeev
 * Date: 09.10.15
 * Time: 12:50
 */
namespace Modules\Multiple\Admin;

use Mindy\Base\Mindy;
use Mindy\Helper\Json;
use Mindy\Orm\Model;
use Mindy\Orm\QuerySet;
use Mindy\Utils\RenderTrait;
use Modules\Admin\AdminModule;
use Modules\Admin\Components\ModelAdmin;
use Modules\Multiple\Forms\MultipleForm;
use Modules\Multiple\MultipleModule;

abstract class MultipleAdmin extends ModelAdmin
{
    use RenderTrait;

    public $actionsTemplate = 'multiple/multiple/_actions.html';
    public $updateTemplate = 'multiple/multiple/form.html';
    public $createTemplate = 'multiple/multiple/form.html';
    public $indexTemplate = 'multiple/multiple/_list.html';
    public $successTemplate = 'multiple/multiple/_success.html';

    public $adminTableClassName = 'Modules\Multiple\Admin\Tables\MultipleAdminTable';

    public $filter = [];

    public $ownerField;

    public $showPkColumn = false;

    /**
     * @param Model $model
     * @return QuerySet
     */
    public function getQuerySet(Model $model)
    {
        return $model->objects()->getQuerySet()->filter($this->filter);
    }

    public function getActions()
    {
        return [
            'remove' => AdminModule::t('Remove'),
        ];
    }

    public function remove(array $data = [])
    {
        $models = isset($data['models']) ? $data['models'] : [];
        /* @var $qs \Mindy\Orm\QuerySet */
        $modelClass = $this->getModel();
        foreach ($models as $pk) {
            if ($model = $modelClass::objects()->get(['pk' => $pk])) {
                $model->delete();
            }
        }
        header('Content-Type: application/json');
        echo json_encode([
            'refresh'=>true
        ]);
        Mindy::app()->end();
    }

    public function delete($pk)
    {
        parent::delete($pk);
        if (isset($_GET['_next'])) {
            Mindy::app()->request->redirect($_GET['_next']);
            Mindy::app()->end();
        }
    }

    public function getCreateForm()
    {
        return MultipleForm::className();
    }

    public function getCreateFormParams()
    {
        return [
            'ownerField' => $this->ownerField,
            'ownerPk' => Mindy::app()->request->get->get($this->ownerField, null)
        ];
    }

    public function redirectNext($data, $form)
    {
        if (Mindy::app()->request->getIsAjax()) {
            echo Json::encode([
                'status' => 'success',
                'content' => $this->renderTemplate($this->successTemplate, [
                    'data' => $data,
                    'form' => $form
                ])
            ]);
            Mindy::app()->end();
        } else {
            list($route, $params) = $this->getNextRoute($data, $form);
            if ($route && $params) {
                $this->redirect($route, $params);
            }
        }
    }
}