<?php

namespace common\components\export;

use Exception;
use kartik\export\ExportMenu as KartikExportMenu;
use ReflectionException;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap5\{Html, Progress};
use yii\helpers\{Inflector, Url};

/**
 * Class ExportMenu
 *
 * @package common\components\export
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class ExportMenu extends KartikExportMenu
{
    public string|ExportConfig $staticConfig;

    /**
     * Использование кастомной очереди
     */
    public bool $useQueue = false;

    /**
     * Жесткое лимитирование числа экспортируемых строк
     */
    public int $limit = 0;

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        $this->exportConfig[KartikExportMenu::FORMAT_TEXT] = false;
        $this->exportConfig[KartikExportMenu::FORMAT_HTML] = false;
        $this->exportConfig[KartikExportMenu::FORMAT_PDF] = false;
        $this->exportConfig[KartikExportMenu::FORMAT_EXCEL] = false;
        if ($this->useQueue) {
            $this->stream = false;
            if (empty($this->staticConfig)) {
                throw new InvalidConfigException('`staticConfig` property must be set');
            }
        }
        if (isset($this->staticConfig) && empty($this->columns)) {
            $this->columns = $this->staticConfig::getColumns();
        }
        parent::init();
    }

    /**
     * @throws ReflectionException
     * @throws InvalidConfigException
     * @throws Exception|Throwable
     */
    public function run()
    {
        if (!$this->useQueue) {
            parent::run();
            return null;
        }
        $this->initI18N(dirname(__DIR__, 4) . '/vendor/kartik-v/yii2-export/src');
        $this->initColumnSelector();
        $this->setVisibleColumns();
        $this->initExport();
        $this->registerAssets();
        if (
            Yii::$app->request->post($this->exportRequestParam, $this->triggerDownload) &&
            !ExportJob::isExportInProcess($this->id)
        ) {
            Yii::$app->queue->priority(1)->push(
                new ExportJob([
                    'id' => $this->id,
                    'dataProvider' => $this->dataProvider,
                    'staticConfig' => $this->staticConfig,
                    'filename' => $this->filename,
                    'limit' => $this->limit,
                    'exportType' => Yii::$app->request->post($this->exportTypeParam, $this->exportType)
                ])
            );
            $totalCount = $this->dataProvider->getTotalCount();
            if (!empty($this->limit)) {
                $totalCount = min($totalCount, $this->limit);
            }
            ExportJob::startProgressBar($this->id, $totalCount);
        }

        if (ExportJob::isExportInProcess($this->id)) {
            echo $this->renderProgressBar();
            $this->dropdownOptions = ['id' => "$this->id-export-dropdown", 'disabled' => true];
        }
        return $this->renderExportMenu();
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    final public function initColumnSelector(): void
    {
        parent::initColumnSelector();
        if (array_key_exists('data-toggle', $this->columnSelectorOptions) && $this->isBs(5)) {
            unset($this->columnSelectorOptions['data-toggle']);
            $this->columnSelectorOptions['data-bs-toggle'] = 'dropdown';
        }
    }

    /**
     * @throws Throwable
     */
    public function renderProgressBar(): string
    {
        $data = ExportJob::getProgressLog($this->id);
        if (!$data) {
            return '';
        }
        $startTime = date('d.m.Y H:i', $data['startTime']);
        $url = Url::to(['/export/get-export-log', 'id' => $this->id]);
        $var = Inflector::camelize($this->id);
        $this->view->registerJs(
            <<<JS
let {$var}RefreshIntervalId = setInterval(function () {
  $.ajax({ url: '$url' })
    .done(function (data) {
      if (data) {
        const process = $('#$this->id-export-process div[role="progressbar"]'),
            width = (data.currentCount / data.count) * 100;
        process.attr('aria-valuenow', width);
        process.width(width + '%');
      } else {
        $('#$this->id-export-process').hide();
        $('#$this->id-export-process-label').hide();
        $('#$this->id-export-dropdown').prop('disabled', false);
        clearInterval({$var}RefreshIntervalId);
      }
    });
}, 3000);
JS
        );
        echo Html::tag('span', "Экспорт начат: $startTime", ['id' => "$this->id-export-process-label"]);
        return Progress::widget([
            'id' => "$this->id-export-process",
            'label' => 'Прогресс экспорта',
            'percent' => $data['count'] ? (int)(($data['currentCount'] / $data['count']) * 100) : 0,
            'barOptions' => ['class' => 'progress-bar-warning'],
            'options' => ['class' => 'progress-striped']
        ]);
    }
}
