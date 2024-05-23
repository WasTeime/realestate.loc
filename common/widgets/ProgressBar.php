<?php

namespace common\widgets;

use common\components\helpers\UserFileHelper;
use yii\base\Exception;
use yii\bootstrap5\Progress;
use yii\helpers\Url;

/**
 * Виджет прогресс бара
 *
 * Для работы необходимо добавить действие контроллеру:
 * ```php
 * use common\widgets\ProgressAction;
 *
 * public function actions(): array
 * {
 *     return [
 *         'progress' => ProgressAction::class
 *     ];
 * }
 * ```
 * Вывести прогресс бар на странице:
 * ```php
 * ProgressBar::widget(
 *     [
 *         'id' => $name,
 *         'updateAction' => Url::to(['progress', 'name' => $name])
 *     ]
 * );
 * ```
 * В продолжительной работе вызывать обновление счетчика:
 * ```php
 * // При создании и/или обновлении счетчика
 * ProgressBar::updateCounter($name, $current, $max);
 * // При прекращении работы
 * ProgressBar::deleteCounter($name);
 * ```
 *
 * @package widgets
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @see     ProgressAction
 */
class ProgressBar extends Progress
{
    private const PATH = 'progress';

    /**
     * Maximum count value
     */
    public ?int $max;

    /**
     * Current count value
     */
    public ?int $current;

    /**
     * Url to update action
     */
    public ?string $updateAction;

    /**
     * Progress bar update period in milliseconds
     */
    public int $refreshPeriod = 3000;

    /**
     * JS код, вызываемый по окончанию работы прогресс бара
     */
    public string $endJsCallback;

    /**
     * Обновление счетчика
     *
     * При большом числе рекомендуется разбивать процесс на куски, например, обновлять прогресс каждые 10, 100 итераций и т.д.
     *
     * @throws Exception
     */
    public static function updateCounter(string $name, int $current = 0, int $max = null): void
    {
        if (!$max) {
            $data = self::findCounter($name);
            $data['current'] = $current;
            if (!empty($data['max'])) {
                if ($data['max'] < $data['current']) {
                    UserFileHelper::saveDataToFile($data, $name, 'admin', self::PATH);
                } else {
                    self::deleteCounter($name);
                }
            }
        }
        UserFileHelper::saveDataToFile(data: ['max' => $max, 'current' => $current], filename: $name, category: self::PATH);
    }

    /**
     * Получить текущие данные счетчика
     */
    public static function findCounter(string $name): bool|array|string
    {
        return UserFileHelper::getDataFromFile(filename: $name, category: self::PATH);
    }

    /**
     * Остановить счетчик (удалить)
     */
    public static function deleteCounter(string $name): void
    {
        UserFileHelper::deleteFile(filename: $name, category: self::PATH);
    }

    /**
     * {@inheritdoc}
     */
    final public function beforeRun(): bool
    {
        $data = self::findCounter($this->id);
        $this->max = $data['max'] ?? null;
        $this->current = $data['current'] ?? null;
        if (is_null($this->max) || is_null($this->current)) {
            return false;
        }
        $this->label = "$this->current из $this->max";
        $this->percent = ($this->current / $this->max) * 100;

        if (isset($this->updateAction)) {
            $this->updateAction = Url::to([$this->updateAction, 'name' => $this->id]);
            $this->view->registerJs(
                <<<JS
$(document).on('ready', function() {
  let refreshIntervalId = setInterval(function () {
    $.ajax({ url: "$this->updateAction" })
      .done(function (data) {
        if (data) {
          const process = $('#$this->id div[role="progressbar"]'),
              width = (data.current / data.max) * 100
          process.attr('aria-valuenow', width)
          process.width(width + '%')
          process.text(data.current + ' из ' + data.max)
        } else {
          $('#$this->id').hide();
          $this->endJsCallback
          clearInterval(refreshIntervalId);
        }
      })
  }, $this->refreshPeriod)
})
JS
            );
        }
        return parent::beforeRun();
    }
}
