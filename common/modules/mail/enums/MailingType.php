<?php

namespace common\modules\mail\enums;

use common\enums\{DictionaryInterface, DictionaryTrait};

/**
 * Interface MailingType
 *
 * @package common\modules\mail\enums
 * @author m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
enum MailingType: int implements DictionaryInterface
{
    use DictionaryTrait;

    case Single = 1;
    case Multiple = 2;

    /**
     * {@inheritdoc}
     */
    public function description(): string
    {
        return match ($this) {
            self::Single => 'Персональная',
            self::Multiple => 'Массовая'
        };
    }

    /**
     * {@inheritdoc}
     */
    public function color(): string
    {
        return match ($this) {
            self::Single, self::Multiple => 'inherit'
        };
    }
}