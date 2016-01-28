<?php

namespace Modules\Multiple\Fields;

use Mindy\Base\Mindy;
use Mindy\Form\Fields\Field;
use Mindy\Pagination\Pagination;
use Mindy\Utils\RenderTrait;
use Modules\Admin\Tables\AdminTable;
use Modules\Admin\Tables\SortingColumn;
use Modules\Catalog\Admin\ProductSizesAdmin;
use Modules\Multiple\Admin\MultipleAdmin;
use Modules\Multiple\Table\MultipleTable;

class MultipleField extends Field
{
    use RenderTrait;

    public $template = '/multiple/multiple/field.html';

    public $sortField = 'position';

    public $columns = [];

    public $foreign_field;

    /**
     * @var string
     */
    public $actionsTemplate = 'multiple/multiple/_actions.html';
    /**
     * @var string
     */
    public $tableTemplate = 'multiple/multiple/table.html';

    public $emptyTemplate = 'multiple/multiple/empty.html';

    public $adminClass;

    public function getOrmField()
    {
        $ormField = $this->form->getInstance()->getField($this->getName());
        return $ormField;
    }

    public function getRelatedModelClass()
    {
        return $this->getOrmField()->modelClass;
    }

    public function getRelatedModel()
    {
        $modelClass = $this->getRelatedModelClass();
        return new $modelClass();
    }


    public function getQuerySet()
    {
        $qs = $this->form->getInstance()->getField($this->getName())->getManager()->getQuerySet();
        return $qs;
    }

    public function renderInput()
    {
        $model = $this->form->getInstance();


        if ($model->isNewRecord) {
            return $this->renderTemplate($this->emptyTemplate, [
                'model' => $model,
                'relatedModel' => $this->getRelatedModel()
            ]);
        }


        return $this->renderTemplate($this->template, [
            'list_id' => $this->uniqueId(),
            'table'=>$this->admin(),
            'relatedModel' => $this->getRelatedModel(),
            'moduleName'=>$this->getRelatedModel()->getModule()->getId(),
            'admin'=>new $this->adminClass(),
            'urlParams'=>$this->getUrlParams(),
        ]);
    }

    public function uniqueId()
    {
        return $this->getId() . '_' . $this->name;
    }

    public function admin()
    {
        $primaryKey = $this->getPrimaryKeyValue();
        $filter = [$this->getForeignField() => $primaryKey];

        $admin = new $this->adminClass([
            'moduleName' => $this->getRelatedModel()->getModule()->getId(),
            'filter' => $filter
        ]);
        $params = isset($_POST['search']) ? array_merge([
            'search' => $_POST['search']
        ], $_GET) : $_GET;
        $admin->setParams($params);

        $moduleName = $admin->getModule()->getId();

        $context = $admin->index();
        $table = $this->renderTemplate($admin->indexTemplate, array_merge([
            'module' => $admin->getModule(),
            'moduleName' => $moduleName,
            'modelClass' => $admin->getModel()->classNameShort(),
            'adminClass' => $admin->classNameShort(),
            'admin' => $admin,
            'urlParams' => $this->getUrlParams(),
            'actions' => $admin->getActions(),
        ], $context));


       return $table;
    }

    public function getPrimaryKeyName(){
        $relatedModelClass = $this->getRelatedModelClass();
        return $relatedModelClass::getPkName();
    }

    public function getPrimaryKeyValue(){
        return $this->form->getInstance()->getPrimaryKey();
    }

    public function getForeignField(){
        if (!$this->foreign_field){
            $this->foreign_field = strtolower($this->form->getInstance()->classNameShort());
        }
        return $this->foreign_field;
    }

    public function getUrlParams(){
        return http_build_query([$this->getForeignField() => $this->getPrimaryKeyValue()]);
    }
}