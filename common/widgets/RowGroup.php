<?php

namespace common\widgets;

use common\components\AppActiveForm;
use common\models\{AppActiveRecord, AppModel};
use yii\base\Widget;

class RowGroup extends Widget
{
    public array $fields = [];

    public AppActiveForm $form;

    public AppActiveRecord|AppModel $model;

    public string $margin;

    public function init(): void
    {
        parent::init();
        if (!isset($this->margin)) {
            $this->margin = 'mb-3';
        }
    }

    public function run()
    {
        echo '<div class="form-group row '. $this->margin .'">';
        foreach ($this->fields as $field => $widget) {
            //если передан массив и у элемента нет виджета
            // 'fields' => [
            //      'title',
            //      'subtitle' => EditorClassic::class,
            // ]
            if (is_int($field)) {
                $field = $widget;
            }
            $field = $this->form->field($this->model, $field, ['options' => ['class' => 'col form-group']]);
            //можно передавать просто 'subtitle' => EditorClassic::class, без массива конфигов
            if (class_exists($widget)) {
                echo $field->widget($widget);
            } elseif (is_array($widget) && class_exists($widget['class'])) {
                //если есть массив конфигов тогда передаём как
                // 'fields' => [
                //      'title',
                //      'subtitle' => [
                //          'class' => EditorClassic::class,
                //
                //          ...options
                //          'data' => ...
                //      ],
                // ]
                $class = $widget['class'];
                unset($widget['class']);
                echo $field->widget($class, $widget);
            } else {
                echo $field->textInput(['maxlength' => true]);
            }
        }
        echo '</div>';
    }
}
