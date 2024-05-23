<?php

namespace common\components;

use common\components\helpers\{ModuleHelper, UserFileHelper, UserUrl};
use Exception;
use kartik\datecontrol\Module as DateControl;
use kartik\icons\Icon;
use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap5\Html;
use yii\i18n\Formatter;

/**
 * Расширенный форматер данных
 *
 * @package common\components
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class UserFormatter extends Formatter
{
    /**
     * Форматирование вывода цвета
     */
    final public function asColor(?string $value, array $options = []): string
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        if (!$value) {
            return '';
        }
        Yii::$app->view->registerCss(
            <<<CSS
.color-preview {
  height: 20px;
  width: 60px;
  border-style: solid;
  border-width: 1px;
  border-color: black;
  border-radius: 0.25rem;
}
CSS
        );
        return Html::tag(
            'div',
            '',
            array_merge(['class' => 'color-preview', 'style' => ['background-color' => $value]], $options)
        );
    }

    /**
     * Форматирование аудио
     *
     * @throws Exception
     */
    final public function asAudio(?string $value, array $options = []): string
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        if (!$value) {
            return '';
        }
        $value = UserUrl::toAbsolute($value);
        preg_match('/\..{2,4}$/', $value, $matches);
        $type = match ($matches[0]) {
            '.ogg' => 'ogg',
            default => 'mpeg',
        };
        return Html::tag(
            'audio',
            Html::beginTag('source', ['src' => $value, 'type' => "audio/$type"]),
            array_merge(['controls' => true], $options)
        );
    }

    /**
     * Форматирование видео
     *
     * @throws Exception
     */
    final public function asVideo(?string $value, array $options = []): string
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        if (!$value) {
            return '';
        }

        // Проверка на youtube ссылку
        if (str_contains($value, 'youtube.com')) {
            parse_str(parse_url($value, PHP_URL_QUERY), $urlParams);
            if (array_key_exists('v', $urlParams)) {
                $youtubeId = $urlParams['v'];
            } else {
                preg_match('#^https://www\.youtube\.com/embed/(?<id>.*)$#', $value, $matches);
                $youtubeId = $matches['id'] ?? null;
            }
        } elseif (str_contains($value, 'youtu.be')) {
            preg_match('#^https://youtu\.be/(?<id>.*)$#', $value, $matches);
            $youtubeId = $matches['id'] ?? null;
        }
        if (!empty($youtubeId)) {
            static $id = 0;
            $domain = Yii::$app->request->hostInfo;
            return Html::tag(
                'iframe',
                null,
                array_merge(
                    [
                        'id' => 'ytplayer-' . $id++,
                        'type' => 'text/html',
                        'width' => 640,
                        'height' => 360,
                        'src' => "https://www.youtube.com/embed/$youtubeId?autoplay=0&origin=$domain",
                        'frameborder' => 0
                    ],
                    $options
                )
            );
        }

        $value = UserUrl::toAbsolute($value);
        preg_match('/\..{2,4}$/', $value, $matches);
        $type = match ($matches[0]) {
            '.ogg' => 'ogg',
            default => 'mp4',
        };
        return Html::tag(
            'video',
            Html::beginTag('source', ['src' => $value, 'type' => "video/$type"]),
            array_merge(['controls' => true, 'width' => 900], $options)
        );
    }

    /**
     * Форматирование объема данных
     */
    final public function asFilesize(int|string|null $value): int|string|null
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        return UserFileHelper::bytesToString($value);
    }

    /**
     * Форматирование для вывода телефона
     *
     * @throws InvalidConfigException
     */
    final public function asPhone($value, $options = []): string
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        if (!$value) {
            return '';
        }

        return Html::a(
            Icon::show('phone') . Html::encode($value),
            'tel:' . preg_replace('/[^+\d]/', '', $value),
            $options
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidConfigException
     */
    final public function asEmail($value, $options = []): string
    {
        if ($value === null) {
            return $this->nullDisplay;
        }
        if (!$value) {
            return '';
        }

        return Html::mailto(Icon::show('envelope') . Html::encode($value), $value, $options);
    }

    /**
     * {@inheritdoc}
     */
    final public function asDate($value, $format = null): ?string
    {
        /** @var DateControl $dateControl */
        $dateControl = Yii::$app->getModule('datecontrol');
        return parent::asDate($value, $format ?: $dateControl->displaySettings[DateControl::FORMAT_DATE] ?? null);
    }

    /**
     * {@inheritdoc}
     */
    final public function asDatetime($value, $format = null): ?string
    {
        /** @var DateControl $dateControl */
        $dateControl = Yii::$app->getModule('datecontrol');
        return parent::asDatetime(
            $value,
            $format ?: $dateControl->displaySettings[DateControl::FORMAT_DATETIME] ?? null
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function asTime($value, $format = null): ?string
    {
        /** @var DateControl $dateControl */
        $dateControl = Yii::$app->getModule('datecontrol');
        $timeZone = $this->timeZone;
        $this->timeZone = 'UTC';
        $result = parent::asTime($value, $format ?: $dateControl->displaySettings[DateControl::FORMAT_TIME] ?? null);
        $this->timeZone = $timeZone;
        return $result;
    }
}
