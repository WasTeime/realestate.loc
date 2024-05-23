<?php

namespace common\modules\mail\models;

use common\models\AppModel;
use common\modules\mail\Mail;
use Yii;
use yii\bootstrap5\Html;

/**
 * This is the model class for table "{{%mail_template}}".
 *
 * @package mail\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
final class Template extends AppModel
{
    public const DEFAULT_PHP = <<<'PHP'
<?php

/**
 * @var $this yii\web\View
 */

$data = $this->params['data'] ?? [];
$this->registerCss(file_get_contents(__DIR__ . '/' . str_replace('-html.php', '.css', basename($this->viewFile))));
echo $this->render(str_replace('-html.php', '.pug', basename($this->viewFile)), $data);
PHP;

    /**
     * Pug Layout шаблон всех страниц
     */
    public ?string $pugLayout = null;

    /**
     * Стили всех страниц
     */
    public ?string $layoutStyle = null;

    /**
     * HTML шаблон
     */
    public ?string $pugHtml = null;

    /**
     * Стили шаблона
     */
    public ?string $style = null;

    /**
     * Текстовый шаблон
     */
    public ?string $text = null;

    /**
     * Возвращает содержимое шаблонов, если они есть.
     */
    public static function findFiles(string $name): self
    {
        $model = new self();

        $htmlFilename = self::getPugHtmlFilename($name);
        if (file_exists($htmlFilename)) {
            $model->pugHtml = file_get_contents($htmlFilename);
        } else {
            $model->pugHtml = '';
        }

        $styleFilename = self::getStyleFilename($name);
        if (file_exists($styleFilename)) {
            $model->style = file_get_contents($styleFilename);
        } else {
            $model->style = '';
        }

        $textFilename = self::getTextFilename($name);
        if (file_exists($textFilename)) {
            $model->text = file_get_contents($textFilename);
        } else {
            $model->text = '';
        }

        $pugLayoutFilename = self::getPugLayoutFilename();
        if (file_exists($pugLayoutFilename)) {
            $model->pugLayout = file_get_contents($pugLayoutFilename);
        } else {
            $model->pugLayout = '';
        }

        $layoutStyleFilename = self::getLayoutStyleFilename();
        if (file_exists($layoutStyleFilename)) {
            $model->layoutStyle = file_get_contents($layoutStyleFilename);
        } else {
            $model->layoutStyle = '';
        }

        return $model;
    }

    public static function getPugLayoutFilename(): string
    {
        return sprintf('%s/common/mail/layouts/html.pug', Yii::getAlias('@root'));
    }

    public static function getLayoutStyleFilename(): string
    {
        return sprintf('%s/common/mail/layouts/style.css', Yii::getAlias('@root'));
    }

    /**
     * Возвращает путь к html шаблону
     */
    private static function getPugHtmlFilename(string $name): string
    {
        return sprintf('%s/common/mail/%s.pug', Yii::getAlias('@root'), $name);
    }

    /**
     * Возвращает путь к стилям шаблона
     */
    private static function getStyleFilename(string $name): string
    {
        return sprintf('%s/common/mail/%s.css', Yii::getAlias('@root'), $name);
    }

    /**
     * Возвращает путь к основному шаблону
     */
    private static function getHtmlFilename(string $name): string
    {
        return sprintf('%s/common/mail/%s-html.php', Yii::getAlias('@root'), $name);
    }

    /**
     * Возвращает путь к текстовому шаблону
     */
    private static function getTextFilename(string $name): string
    {
        return sprintf('%s/common/mail/%s-text.php', Yii::getAlias('@root'), $name);
    }

    /**
     * Удаляет файлы шаблонов, если они есть.
     */
    public static function deleteFiles(string $name): void
    {
        $filename = self::getHtmlFilename($name);
        if (file_exists($filename)) {
            unlink($filename);
        }

        $filename1 = self::getPugHtmlFilename($name);
        if (file_exists($filename1)) {
            unlink($filename1);
        }

        $filename2 = self::getTextFilename($name);
        if (file_exists($filename2)) {
            unlink($filename2);
        }

        $filename3 = self::getStyleFilename($name);
        if (file_exists($filename3)) {
            unlink($filename3);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['pugLayout', 'layoutStyle', 'pugHtml', 'style', 'text'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'pugLayout' => Yii::t(Mail::MODULE_MESSAGES, 'Pug layout'),
            'layoutStyle' => Yii::t(Mail::MODULE_MESSAGES, 'Layout Style'),
            'pugHtml' => Yii::t(Mail::MODULE_MESSAGES, 'Html Content Template'),
            'style' => Yii::t(Mail::MODULE_MESSAGES, 'Style'),
            'text' => Yii::t(Mail::MODULE_MESSAGES, 'Text Template')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeHints(): array
    {
        return [
            'pugLayout' => Yii::t(Mail::MODULE_MESSAGES, 'Layout is same for every letter!'),
            'layoutStyle' => Yii::t(Mail::MODULE_MESSAGES, 'Styles for every letter!'),
            'pugHtml' => Yii::t(Mail::MODULE_MESSAGES, 'Use pug markup'),
            'style' => Yii::t(Mail::MODULE_MESSAGES, 'Some mail clients may cutoff these styles!') .
                ' ' . Html::a(
                    'Туториал',
                    'https://www.unisender.com/ru/blog/sovety/kak-sverstat-pismo-instruktsiya-dlya-chaynikov/',
                    ['target' => '_blank']
                ),
            'text' => Yii::t(Mail::MODULE_MESSAGES, 'Use plain text'),
        ];
    }

    /**
     * Переименует файлы шаблонов.
     */
    public function renameFiles(string $oldName, string $name): void
    {
        if ($oldName !== $name) {
            $filename = self::getHtmlFilename($oldName);
            $newFilename = self::getHtmlFilename($name);
            if (file_exists($filename)) {
                rename($filename, $newFilename);
            } else {
                file_put_contents($newFilename, self::DEFAULT_PHP);
            }

            $filename = self::getPugHtmlFilename($oldName);
            if (file_exists($filename)) {
                unlink($filename);
            }
            $filename = self::getPugHtmlFilename($name);
            file_put_contents($filename, $this->pugHtml);

            $filename = self::getTextFilename($oldName);
            if (file_exists($filename)) {
                unlink($filename);
            }
            $filename = self::getTextFilename($name);
            file_put_contents($filename, $this->text);

            $filename = self::getStyleFilename($oldName);
            if (file_exists($filename)) {
                unlink($filename);
            }
            $filename = self::getStyleFilename($name);
            file_put_contents($filename, $this->style);
        } else {
            $this->saveFiles($name);
        }
    }

    /**
     * Сохраняет файлы шаблонов.
     */
    public function saveFiles(string $name): void
    {
        $filename = self::getHtmlFilename($name);
        if (!file_exists($filename)) {
            file_put_contents($filename, self::DEFAULT_PHP);
        }
        $filename = self::getPugHtmlFilename($name);
        file_put_contents($filename, $this->pugHtml);

        $filename = self::getStyleFilename($name);
        file_put_contents($filename, $this->style);

        $filename = self::getTextFilename($name);
        file_put_contents($filename, $this->text);

        if (!empty($this->pugLayout)) {
            file_put_contents(self::getPugLayoutFilename(), $this->pugLayout);
        }
        file_put_contents(self::getLayoutStyleFilename(), $this->layoutStyle);
    }
}