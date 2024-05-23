<?php

namespace common\modules\backup\models;

use common\components\helpers\ModuleHelper;
use Yii;
use yii\base\Exception;
use yii\db\{ActiveRecord, Exception as DbException};
use yii\helpers\BaseConsole;

/**
 * Модель "обертка" для доступа к БД
 *
 * @package backup\models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 */
class DbWrap extends ActiveRecord
{
    private const RUNTIME_DIR = '@admin/runtime/';

    /**
     * Количество строчек таблиц на один запрос
     */
    protected static int $countSelectRows = 1000;

    /**
     * Название папки для хранения бекапов
     */
    protected static string $dirBackUp = 'backup_db';

    /**
     * SET @OLD_CHARACTER_SET_CLIENT - указываем кодировку на клиенте
     * SET NAMES - указываем нашу кодировку
     * SET @OLD_FOREIGN_KEY_CHECKS - отключаем проверку целостности таблицы БД на время выполнения запроса
     * SET @OLD_SQL_MODE - указываем режим работы mysql сервера
     */
    protected static string $offlineCheckForeignKey = <<<SQL
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
SQL
    . PHP_EOL;

    /**
     * Резервное копирование всей БД
     *
     * @throws Exception
     * @throws DbException
     */
    public static function exportDB(string $dateString = null): string
    {
        if (!$dateString) {
            $dateString = date('Y-m-d_H-i-s');
        }
        if (ModuleHelper::isConsoleModule()) {
            Yii::$app->controller->stdout("Getting tables list..." . PHP_EOL);
        }
        $tables = self::getTables();
        foreach ($tables as $table) {
            if (ModuleHelper::isConsoleModule()) {
                Yii::$app->controller->stdout("Exporting `$table` table..." . PHP_EOL);
            }
            try {
                self::export($table, $dateString);
                if (ModuleHelper::isConsoleModule()) {
                    Yii::$app->controller->stdout('Done' . PHP_EOL, BaseConsole::FG_GREEN);
                }
            } catch (\Exception $exception) {
                if (ModuleHelper::isConsoleModule()) {
                    Yii::$app->controller->stdout($exception->getMessage() . PHP_EOL, BaseConsole::BG_RED);
                }
            }
        }
        return $dateString;
    }

    /**
     * Резервное копирование таблицы
     *
     * @throws Exception
     * @throws DbException
     */
    public static function export(string $table, string $dateString): bool
    {
        if (!$table || !$dateString) {
            return false;
        }
        $typesNoString = [
            'float',
            'double',
            'decimal',
            'bit',
            'int',
            'smallint',
            'mediumint',
            'bigint',
            'tinyint'
        ];

        // выбираем подключение
        $dbRemote = self::getDb();

        $dbName = self::getDsnAttribute('dbname', $dbRemote->dsn);
        $file = Yii::getAlias(self::RUNTIME_DIR . self::$dirBackUp) . "/$dbName-$dateString/$table.sql";
        if (
            !file_exists($concurrentDirectory = dirname($file)) &&
            !mkdir($concurrentDirectory, 0755, true) &&
            !is_dir($concurrentDirectory)
        ) {
            throw new Exception(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        // Получим дамп на создание
        $sqlShowCreateTable = <<<SQL
SHOW CREATE TABLE `$table`;
SQL;
        $create = $dbRemote->createCommand($sqlShowCreateTable)->noCache()->queryOne()['Create Table'];

        $insertPrefix = "INSERT INTO `$table` (";
        // собираем типы данных
        $fieldTypes = [];
        $sqlColumns = <<<SQL
SHOW COLUMNS FROM `$table`;
SQL;
        $dataColumns = $dbRemote->createCommand($sqlColumns)->noCache()->queryAll();
        foreach ($dataColumns as $dataColumn) {
            if ($strPos = strpos($dataColumn['Type'], '(')) {
                $fieldTypes[$dataColumn['Field']] = trim(substr($dataColumn['Type'], 0, $strPos));
            } else {
                $fieldTypes[$dataColumn['Field']] = trim($dataColumn['Type']);
            }
            $insertPrefix .= "`{$dataColumn['Field']}`,";
        }
        $insertPrefix = trim(trim($insertPrefix, ',')) . ') VALUES ';
        $create = self::$offlineCheckForeignKey . <<<SQL
DROP TABLE IF EXISTS `$table`;
SQL
            . PHP_EOL . "$create;";

        self::w($file, $create . PHP_EOL);

        $limit = (self::$countSelectRows) ?: 100;
        // Делаем INSERT
        $sqlCount = <<<SQL
SELECT count(*) as `count` FROM `$table`
SQL;
        $count = (int)$dbRemote->createCommand($sqlCount)->noCache()->queryOne()['count'];
        $pages = ceil($count / $limit);

        for ($page = 0; $page <= $pages; $page++) {
            $offset = $page * $limit;

            $sqlSelect = <<<SQL
SELECT * FROM `$table` LIMIT $offset, $limit;
SQL;

            $list = $dbRemote->createCommand($sqlSelect)->noCache()->queryAll();

            if (!empty($list)) {
                $insert = self::_dataToQueryString($list, $fieldTypes, $typesNoString);

                $insert = $insertPrefix . trim($insert, ',') . ';';
                self::w($file, $insert . PHP_EOL);
            }
        }
        return true;
    }

    private static function _dataToQueryString(array $list, array $fieldTypes, array $typesNoString): string
    {
        $insert = '';
        foreach ($list as $item) {
            $insert .= '(';
            $str = '';
            foreach ($item as $k => $v) {
                // определяем необходимость в кавычках
                if (in_array($fieldTypes[$k], $typesNoString, true)) {
                    if (is_null($v)) {
                        $str .= 'NULL,';
                    } elseif (empty($v)) {
                        $str .= "'$v',";
                    } else {
                        $str .= "$v,";
                    }
                } else {
                    $str .= "'" . addslashes((string)$v) . "',";
                }
            }
            $insert .= trim($str, ',') . '),';
        }
        return $insert;
    }

    public static function w(string $file, string $content): bool|int
    {
        $f = fopen($file, 'ab');
        $res = fwrite($f, $content);
        fclose($f);
        return $res;
    }

    /**
     * Возвращает название хоста (например localhost)
     */
    private static function getDsnAttribute(string $name, string $dsn): string
    {
        if (preg_match("/$name=([^;]*)/", $dsn, $match)) {
            return $match[1];
        }
        return '';
    }

    /**
     * Импортирует последний бекап
     *
     * @throws DbException
     */
    public static function import(string $table, string $dateString = null): bool
    {
        if (!$table) {
            return false;
        }
        if ($dateString === null) {
            $globsBackups = glob(Yii::getAlias(self::RUNTIME_DIR) . self::$dirBackUp . '/*');
            rsort($globsBackups, SORT_STRING);
            $globsBackups = $globsBackups[0] ?? null;
            $file = glob("$globsBackups/$table.sql");
            $file = $file[0] ?? null;
        } else {
            $file = Yii::getAlias(self::RUNTIME_DIR) . self::$dirBackUp . "/$dateString/$table.sql";
        }

        if (!$file || !file_exists($file)) {
            return false;
        }

        $db = self::getDb();
        $db->createCommand(file_get_contents($file))->noCache()->execute();
        return true;
    }

    /**
     * Проверяет тип ОС
     */
    public static function isWindows(): bool
    {
        $php_uname = php_uname();
        $arr = explode('Windows', $php_uname);
        return count($arr) > 1;
    }

    /**
     * Удаляет все ранее созданные бекапы
     */
    public static function removeAll(): bool
    {
        if (self::isWindows()) {
            $command = 'RD /S/q "' .
                str_replace(
                    '/',
                    '\\',
                    Yii::getAlias(self::RUNTIME_DIR) . self::$dirBackUp
                ) . '\"';
        } else {
            $command = 'cd ' .
                Yii::getAlias(self::RUNTIME_DIR) .
                self::$dirBackUp . ' && rm -rf ';
        }
        shell_exec($command);
        return true;
    }

    /**
     * Удаляет бэкап
     */
    public static function remove(string $dateString): bool
    {
        $dbRemote = self::getDb();
        $dbName = self::getDsnAttribute('dbname', $dbRemote->dsn);
        if (self::isWindows()) {
            $command = 'RD /S/q "' .
                str_replace('/', '\\', Yii::getAlias(self::RUNTIME_DIR) . self::$dirBackUp) .
                "\\$dbName-$dateString\\\"";
        } else {
            $command = 'cd ' .
                Yii::getAlias(self::RUNTIME_DIR) . self::$dirBackUp .
                "/$dbName-$dateString && rm -rf ";
        }
        shell_exec($command);
        return true;
    }

    /**
     * Получение списка таблиц
     *
     * @throws DbException
     */
    public static function getTables(): bool|array
    {
        $sqlShowTables = <<<'SQL'
SHOW TABLES
SQL;
        $db = self::getDb();
        $tablesTemp = $db->createCommand($sqlShowTables)->queryAll();
        if (empty($tablesTemp)) {
            return false;
        }
        $temps = [];
        foreach ($tablesTemp as $temp) {
            $temps[] = array_values($temp);
        }
        return array_merge(...$temps);
    }

    /**
     * Получение списка бэкапов.
     *
     * @throws Exception
     */
    public static function getBackups(): array
    {
        $path = Yii::getAlias(self::RUNTIME_DIR) . self::$dirBackUp;
        if (!file_exists($path) && !mkdir($path) && !is_dir($path)) {
            throw new Exception(sprintf('Directory "%s" was not created', $path));
        }
        return array_values(array_diff(scandir($path), ['.', '..']));
    }
}
