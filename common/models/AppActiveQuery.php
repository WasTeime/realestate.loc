<?php

namespace common\models;

use common\components\DbConnection;
use Throwable;
use yii\base\InvalidConfigException;
use yii\db\{ActiveQuery, Query};
use yii\helpers\ArrayHelper;

/**
 * Class AppActiveQuery
 *
 * @package models
 * @author  m.kropukhinsky <m.kropukhinsky@peppers-studio.ru>
 *
 * @see     AppActiveRecord
 */
class AppActiveQuery extends ActiveQuery
{
    /** @var AppActiveRecord|string */
    public $modelClass;

    /**
     * {@inheritdoc}
     */
    final public function count($q = '*', $db = null): bool|int|string|null
    {
        $this->distinctCountQuery($q);
        return parent::count($q, $db);
    }

    /**
     * Returns the number of records.
     *
     * @param string            $q  the COUNT_BIG expression. Defaults to '*'.
     *                              Make sure you properly [quote](guide:db-dao#quoting-table-and-column-names) column names in the expression.
     * @param null|DbConnection $db the database connection used to generate the SQL statement.
     *                              If this parameter is not given (or null), the `db` application component will be used.
     *
     * @return int|string number of records. The result may be a string depending on the
     * underlying database engine and to support integer values higher than a 32bit PHP integer can handle.
     * @throws Throwable
     */
    final public function countBig(string $q = '*', DbConnection $db = null): int|string
    {
        $this->distinctCountQuery($q);
        if ($this->emulateExecution) {
            return 0;
        }

        return $this->queryScalar("COUNT_BIG($q)", $db);
    }

    private function distinctCountQuery(string &$q): void
    {
        if (
            $q === '*'
            && !$this->distinct
            && empty($this->groupBy)
            && empty($this->having)
            && empty($this->union)
        ) {
            $keys = $this->modelClass::primaryKey();
            if (count($keys) === 1) {
                $q = $this->modelClass::tableName() . '.' . $keys[0];
            } else {
                $q = $this->modelClass::tableName() . '.*';
            }
            $q = "DISTINCT($q)";
        }
    }

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function prepare($builder): Query
    {
        if (
            empty($this->select)
            && empty($this->join)
            && empty($this->link)
            && !empty($this->modelClass::externalAttributes())
        ) {
            $select = [$this->modelClass::tableName() . '.*'];
            $joinWith = [];
            foreach ($this->modelClass::externalAttributes() as $attribute) {
                $this->modelClass::parseQueryField($attribute, $select, $joinWith);
            }
            $this->select($select);
            $this->joinWith($joinWith);
        }
        $query = parent::prepare($builder);
        if (!empty($query->join)) {
            [, $alias] = $this->getTableNameAndAlias();
            $schema = $this->modelClass::getTableSchema();
            $columnNames = ArrayHelper::getColumn($schema->columns, 'name');
            if (!empty($query->where)) {
                $this->prefixTableAlias($query->where, $alias, $columnNames);
            }
            if (!empty($query->having)) {
                $this->prefixTableAlias($query->having, $alias, $columnNames);
            }
        }
        return $query;
    }

    /**
     * @param string[] $columns
     */
    private function prefixTableAlias(array &$where, string $alias, array $columns): void
    {
        foreach ($where as $key => &$item) {
            if (is_string($key) && in_array($key, $columns) && !str_contains($key, '.')) {
                $where["$alias.$key"] = $item;
                unset($where[$key]);
            }
            if (is_array($item)) {
                if (
                    array_is_list($item)
                    && count($item) === 3
                    && in_array(
                        strtolower($item[0]),
                        ['and', 'or', 'like', 'between', '>', '<', '>=', '<=', '!=', '=', 'not in', 'not']
                    )
                    && in_array($item[1], $columns, true)
                    && !str_contains($item[1], '.')
                ) {
                    $item[1] = "$alias.$item[1]";
                }
                $this->prefixTableAlias($item, $alias, $columns);
            }
        }
    }
}
