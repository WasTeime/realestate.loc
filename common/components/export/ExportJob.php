<?php

namespace common\components\export;

use Closure;
use common\components\{exceptions\ModelSaveException, helpers\UserFileHelper, queue\AppQueue};
use common\models\ExportList;
use common\modules\notification\{enums\Type, models\Notification};
use Exception;
use kartik\export\ExportMenu;
use OpenSpout\Common\Entity\{Cell, Row, Style\Border, Style\BorderPart, Style\Style};
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Writer\{CSV\Writer as CSVWriter, WriterInterface, XLSX\Options, XLSX\Writer as XlsxWriter};
use OpenSpout\Writer\Exception\{Border\InvalidNameException,
    Border\InvalidStyleException,
    Border\InvalidWidthException,
    WriterNotOpenedException};
use Yii;
use yii\base\{BaseObject, InvalidConfigException, Model};
use yii\data\ActiveDataProvider;
use yii\db\{ActiveQueryInterface, ActiveRecord, Query};
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\queue\{JobInterface, redis\Queue, RetryableJobInterface};

/**
 * Class ExportJob
 *
 * @package admin\widgets\export
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @property-read float|int $ttr
 */
class ExportJob extends BaseObject implements JobInterface, RetryableJobInterface
{
    public ActiveDataProvider $dataProvider;

    public string $id = 'default';

    public string|ExportConfig $staticConfig;

    private array $columns = [];

    public string $filename = '';

    public int $limit = 0;

    public string $exportType = ExportMenu::FORMAT_EXCEL_X;

    private string $savePath;

    private static string $progressLog = 'exportProgress';

    private int $_currentRow = 1;

    /**
     * {@inheritdoc}
     */
    public function getTtr(): float|int
    {
        return 60 * 60 * 3;
    }

    /**
     * {@inheritdoc}
     */
    public function canRetry($attempt, $error): bool
    {
        self::endProgressLog($this->id);
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        $this->savePath = Yii::getAlias('@root/admin/runtime/export');
        if (empty($this->filename)) {
            $this->filename = 'grid-export';
        }
    }

    /**
     * @throws ModelSaveException
     */
    public function execute($queue): void
    {
        $this->initColumns();
        try {
            if (
                !file_exists($this->savePath) &&
                !mkdir($concurrentDirectory = $this->savePath, 0777, true) &&
                !is_dir($concurrentDirectory)
            ) {
                throw new InvalidConfigException("Invalid permissions to write to '$this->savePath'.");
            }
            set_time_limit(0);
            switch ($this->exportType) {
                case ExportMenu::FORMAT_CSV:
                    $writer = new CSVWriter();
                    $filename = "$this->filename.csv";
                    break;
                case ExportMenu::FORMAT_EXCEL_X:
                default:
                    $options = new Options();
                    $options->setTempFolder(Yii::getAlias('@root/admin/runtime/'));
                    $writer = new XlsxWriter($options);
                    $filename = "$this->filename.xlsx";
                    break;
            }
            $writer->openToFile("$this->savePath/$filename");
            $this->writeHeader($writer);

            $dataProvider = clone $this->dataProvider;
            $dataProvider->pagination->pageSize = 100;
            $dataProvider->pagination->page = 0;
            $dataProvider->refresh();
            // do not execute multiple COUNT(*) queries
            $totalCount = $dataProvider->getTotalCount();
            if (!empty($this->limit)) {
                $totalCount = min($totalCount, $this->limit);
            }
            $models = array_values($dataProvider->getModels());
            $count = 0;
            $lastPage = 0;
            while (count($models) > 0) {
                if ($dataProvider->pagination && ($lastPage === 0 || $lastPage !== $dataProvider->pagination->page)) {
                    foreach ($models as $model) {
                        if (empty($this->limit) || ($count < $this->limit)) {
                            $this->writeRow($writer, $model, $count);
                            $count++;
                        } else {
                            break 2;
                        }
                    }
                    $this->updateProgressLog(count($models));
                    $lastPage = $dataProvider->pagination->page++;
                    $dataProvider->refresh();
                    $dataProvider->setTotalCount($totalCount);
                    $models = $dataProvider->getModels();
                } else {
                    $models = [];
                }
            }
            $writer->close();
            if ($count > 0) {
                $this->saveLog($filename, $count);
                Notification::create(
                    Type::Success,
                    "Экспорт успешно завершен! <a href=\"/admin/export/download/$filename\">Скачать $filename</a>"
                );
            }
        } catch (Exception $exception) {
            Notification::create(
                Type::Error,
                'Ошибка во время экспорта: ' . $exception->getMessage() .
                (YII_ENV_DEV ? $exception->getTraceAsString() : null)
            );
        } finally {
            self::endProgressLog($this->id);
        }
    }

    /**
     * @throws ModelSaveException
     */
    private function saveLog(string $filename, int $count): void
    {
        $log = new ExportList();
        $log->filename = $filename;
        $log->count = $count;
        $log->date = time();
        if (!$log->save()) {
            throw new ModelSaveException($log);
        }
    }

    /**
     * @throws IOException
     * @throws WriterNotOpenedException
     */
    private function writeHeader(WriterInterface $writer): void
    {
        $cells = [];
        foreach ($this->columns as $column) {
            if (empty($column['label'])) {
                $attribute = is_string($column) ? $column : ($column['attribute'] ?? null);
                if ($attribute === null) {
                    $label = '';
                } elseif ($this->dataProvider->query instanceof ActiveQueryInterface) {
                    /* @var $modelClass Model */
                    $modelClass = $this->dataProvider->query->modelClass;
                    $model = $modelClass::instance();
                    $label = $model->getAttributeLabel($attribute);
                } elseif ($this->dataProvider->modelClass !== null) {
                    /* @var $modelClass Model */
                    $modelClass = $this->dataProvider->modelClass;
                    $model = $modelClass::instance();
                    $label = $model->getAttributeLabel($attribute);
                } else {
                    $models = $this->dataProvider->getModels();
                    if (($model = reset($models)) instanceof Model) {
                        /* @var $model Model */
                        $label = $model->getAttributeLabel($attribute);
                    } else {
                        $label = Inflector::camel2words($attribute);
                    }
                }
            } else {
                $label = $column['label'];
            }
            $cells[] = Cell::fromValue($label);
        }
        $style = new Style();
        $style->setFontBold()
            ->setBorder(
                new Border(
                    new BorderPart(Border::TOP),
                    new BorderPart(Border::RIGHT),
                    new BorderPart(Border::BOTTOM),
                    new BorderPart(Border::LEFT)
                )
            );
        $writer->addRow(new Row($cells, $style));
        $this->_currentRow++;
    }

    /**
     * @throws IOException
     * @throws InvalidNameException
     * @throws InvalidStyleException
     * @throws InvalidWidthException
     * @throws WriterNotOpenedException
     * @throws Exception
     */
    private function writeRow(WriterInterface $writer, Model|ActiveRecord $model, bool $isLast = false): void
    {
        $cells = [];
        foreach ($this->columns as $key => $gridColumn) {
            if (array_key_exists('value', $gridColumn) && $gridColumn['value'] instanceof Closure) {
                $value = $gridColumn['value']($model, $key);
            } elseif (array_key_exists('attribute', $gridColumn)) {
                $value = ArrayHelper::getValue($model, $gridColumn['attribute']);
            }
            if (is_array($gridColumn) && array_key_exists('format', $gridColumn)) {
                $format = $gridColumn['format'];
            }
            if (isset($value, $format)) {
                $value = Yii::$app->formatter->format($value, $format);
            }
            $isLastColumn = $key === (count($this->columns) - 1);
            $style = new Style();
            $style->setBorder(
                new Border(
                    new BorderPart(name: Border::TOP, width: Border::WIDTH_THIN, style: Border::STYLE_DASHED),
                    new BorderPart(
                        name: Border::RIGHT,
                        width: $isLastColumn ? Border::WIDTH_MEDIUM : Border::WIDTH_THIN,
                        style: $isLastColumn ? Border::STYLE_SOLID : Border::STYLE_DASHED
                    ),
                    new BorderPart(
                        name: Border::BOTTOM,
                        width: $isLast ? Border::WIDTH_MEDIUM : Border::WIDTH_THIN,
                        style: $isLast ? Border::STYLE_SOLID : Border::STYLE_DASHED
                    ),
                    new BorderPart(name: Border::LEFT, width: Border::WIDTH_THIN, style: Border::STYLE_DASHED)
                )
            );
            $cells[] = Cell::fromValue($value ?? '', $style);
            unset($format);
        }
        $writer->addRow(new Row($cells));
        $this->_currentRow++;
    }

    /**
     * @throws \yii\base\Exception
     */
    public static function startProgressBar(string $id, int $totalCount, int $startCount = 0): void
    {
        if ($data = self::getProgressLog($id)) {
            $data['count'] += $totalCount;
        } else {
            $data = [
                'startTime' => time(),
                'updateTime' => time(),
                'count' => $totalCount,
                'currentCount' => $startCount,
            ];
        }
        UserFileHelper::saveDataToFile($data, sprintf('%s-%s', self::$progressLog, $id));
    }

    /**
     * Обновить счетчик в логе
     *
     * @throws \yii\base\Exception
     */
    private function updateProgressLog(int $diff): void
    {
        $data = UserFileHelper::getDataFromFile(sprintf('%s-%s', self::$progressLog, $this->id));
        if (is_array($data) && array_key_exists('count', $data) && array_key_exists('currentCount', $data)) {
            $data['currentCount'] += $diff;
            $data['updateTime'] = time();
            if ($data['currentCount'] >= $data['count']) {
                $data['currentCount'] = $data['count'];
            }
            UserFileHelper::saveDataToFile($data, sprintf('%s-%s', self::$progressLog, $this->id));
        }
    }

    protected function initColumns(): void
    {
        $this->columns = $this->staticConfig::getColumns();
        foreach ($this->columns as &$column) {
            if (is_string($column)) {
                if (str_contains($column, ':')) {
                    [$attribute, $format] = explode(':', $column);
                } else {
                    $attribute = $column;
                }
                $column = ['attribute' => $attribute];
                if (isset($format)) {
                    $column['format'] = $format;
                }
            }
        }
        unset($column);
    }

    public static function getProgressLog(string $id): ?array
    {
        $data = UserFileHelper::getDataFromFile(sprintf('%s-%s', self::$progressLog, $id));
        if (
            is_array($data) &&
            array_key_exists('count', $data) &&
            array_key_exists('currentCount', $data)
        ) {
            if ($data['currentCount'] > 0) {
                $data['elapsedTime'] = time() +
                    (
                        ((time() - $data['startTime']) / $data['currentCount']) *
                        ($data['count'] - $data['currentCount'])
                    );
            } else {
                $data['elapsedTime'] = time();
            }
            return $data;
        }
        return null;
    }

    /**
     * Удалить текущий лог
     */
    public static function endProgressLog(string $id): void
    {
        $path = Yii::getAlias('@root/admin/runtime/saved');
        $filename = sprintf('%s/%s-%s.json', $path, self::$progressLog, $id);
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    public static function isExportInProcess(string $id): bool
    {
        $path = Yii::getAlias('@root/admin/runtime/saved');
        $fileExists = file_exists(sprintf('%s/%s-%s.json', $path, self::$progressLog, $id));
        $queue = Yii::$app->queue;
        if (
            $fileExists
            && (
                (
                    $queue instanceof AppQueue
                    && !(new Query())
                        ->from($queue->tableName)
                        ->andWhere(['channel' => $queue->channel])
                        ->andWhere(['reserved_at' => null])
                        ->count('*', $queue->db)
                    && !(new Query())
                        ->from($queue->tableName)
                        ->andWhere(['channel' => $queue->channel])
                        ->andWhere('[[reserved_at]] is not null')
                        ->andWhere(['done_at' => null])
                        ->count('*', $queue->db)
                ) || (
                    $queue instanceof Queue
                    && $queue->isDone(Yii::$app->redis->get("$queue->channel.message_id"))
                )
            )
        ) {
            self::endProgressLog($id);
            return false;
        }
        return $fileExists;
    }
}
