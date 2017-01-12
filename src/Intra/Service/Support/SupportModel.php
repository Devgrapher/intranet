<?php

namespace Intra\Service\Support;

use Intra\Core\BaseModel;
use Intra\Service\Support\Column\SupportColumn;
use Intra\Service\Support\Column\SupportColumnAcceptUser;
use Intra\Service\Support\Column\SupportColumnComplete;
use Intra\Service\Support\Column\SupportColumnDate;

class SupportModel extends BaseModel
{
    /**
     * @param SupportDto $support_dto
     *
     * @return
     * @throws \Exception
     */
    public static function add($support_dto)
    {
        $table = self::getTableName($support_dto->target);
        $dict = $support_dto->exportDictAddRequest();
        self::getDb()->sqlInsert($table, $dict);
        return self::getDb()->insert_id();
    }

    /**
     * @param SupportColumn[] $columns
     * @param string          $target
     * @param int             $uid
     * @param string          $date
     *
     * @return array
     * @throws \Exception
     */
    public static function getDicts($columns, $target, $uid, $date)
    {
        $order_column = null;
        foreach ($columns as $column) {
            if ($column instanceof SupportColumnDate) {
                if ($column->is_ordering_column) {
                    $order_column = $column->key;
                }
            }
        }
        if ($order_column == null) {
            throw new \Exception('정렬 컬럼지정이 되어있지 않습니다.');
        }

        $table = self::getTableName($target);
        $next_date = date('Y-m-1', strtotime('+1 month', strtotime($date)));

        $where = [
            'uid' => $uid,
            $order_column => sqlRange($date, $next_date),
            'is_deleted' => 0,
        ];
        return self::getDb()->sqlDicts(
            'select * from ? where ? order by ? asc',
            sqlTable($table),
            sqlWhere($where),
            sqlColumn($order_column)
        );
    }

    /**
     * @param SupportColumn[] $columns
     * @param string          $target
     * @param string          $date
     *
     * @return array
     * @throws \Exception
     */
    public static function getDictsAll($columns, $target, $date)
    {
        $order_column = null;
        foreach ($columns as $column) {
            if ($column instanceof SupportColumnDate) {
                if ($column->is_ordering_column) {
                    $order_column = $column->key;
                }
            }
        }
        if ($order_column == null) {
            throw new \Exception('정렬 컬럼지정이 되어있지 않습니다.');
        }

        $table = self::getTableName($target);
        $next_date = date('Y-m-1', strtotime('+1 month', strtotime($date)));

        $where = [
            $order_column => sqlRange($date, $next_date),
            'is_deleted' => 0,
        ];
        return self::getDb()->sqlDicts(
            'select * from ? where ? order by ? asc',
            sqlTable($table),
            sqlWhere($where),
            sqlColumn($order_column)
        );
    }

    public static function getDictsRemainAll($columns, $target)
    {
        $order_column = null;
        $complete_columns = [];
        foreach ($columns as $column) {
            if ($column instanceof SupportColumnDate) {
                if ($column->is_ordering_column) {
                    $order_column = $column->key;
                }
            }
            if ($column instanceof SupportColumnComplete) {
                $complete_columns[] = $column->key;
            }
        }
        if ($order_column == null) {
            throw new \Exception('정렬 컬럼지정이 되어있지 않습니다.');
        }
        if (count($complete_columns) == 0) {
            throw new \Exception('승인가능한 컬럼지정이 되어있지 않습니다.');
        }

        $table = self::getTableName($target);

        $where = [
            'is_deleted' => 0,
        ];
        $or_array = [];
        foreach ($complete_columns as $complete_column) {
            $or_array[] = [$complete_column => 0];
        }
        $where[] = sqlOrArray($or_array);

        return self::getDb()->sqlDicts(
            'select * from ? where ? order by ? asc',
            sqlTable($table),
            sqlWhere($where),
            sqlColumn($order_column)
        );
    }

    public static function getDictsRemainByAccept($columns, $target, $uid)
    {
        $order_column = null;
        $accept_columns = [];
        foreach ($columns as $column) {
            if ($column instanceof SupportColumnDate) {
                if ($column->is_ordering_column) {
                    $order_column = $column->key;
                }
            }
            if ($column instanceof SupportColumnAcceptUser) {
                $accept_columns[] = $column->key;
            }
        }
        if ($order_column == null) {
            throw new \Exception('정렬 컬럼지정이 되어있지 않습니다.');
        }
        if (count($accept_columns) == 0) {
            throw new \Exception('승인가능한 컬럼지정이 되어있지 않습니다.');
        }

        $table = self::getTableName($target);

        $where = [
            'is_deleted' => 0,
        ];
        $or_array = [];
        foreach ($accept_columns as $accept_column) {
            $or_array[] = [$accept_column => $uid];
        }
        $where[] = sqlOrArray($or_array);

        return self::getDb()->sqlDicts(
            'select * from ? where ? order by ? asc',
            sqlTable($table),
            sqlWhere($where),
            sqlColumn($order_column)
        );
    }

    public static function getDict($target, $id)
    {
        $table = self::getTableName($target);

        $where = [
            'id' => $id,
            'is_deleted' => 0,
        ];
        return self::getDb()->sqlDict(
            'select * from ? where ?',
            sqlTable($table),
            sqlWhere($where)
        );
    }

    public static function edit($target, $id, $key, $value)
    {
        $table = self::getTableName($target);
        $update = [$key => $value];
        $where = [
            'id' => $id,
            'is_deleted' => 0,
        ];
        return self::getDb()->sqlUpdate($table, $update, $where);
    }

    public static function del($target, $id)
    {
        $table = self::getTableName($target);
        $update = [
            'is_deleted' => 1,
        ];
        $where = [
            'id' => $id,
            'is_deleted' => 0,
        ];
        return self::getDb()->sqlUpdate($table, $update, $where);
    }

    /**
     * @param           $columns
     * @param           $target
     * @param \DateTime $begin_datetime
     * @param \DateTime $end_datetime
     *
     * @return array
     * @throws \Exception
     */
    public static function getDictsForExcel($columns, $target, $begin_datetime, $end_datetime)
    {
        $order_column = null;
        foreach ($columns as $column) {
            if ($column instanceof SupportColumnDate) {
                if ($column->is_ordering_column) {
                    $order_column = $column->key;
                }
            }
        }
        if ($order_column == null) {
            throw new \Exception('정렬 컬럼지정이 되어있지 않습니다.');
        }

        $table = self::getTableName($target);

        $where = [
            $order_column => sqlRange($begin_datetime->format('Y-m-d'), $end_datetime->format('Y-m-d')),
            'is_deleted' => 0,
        ];
        return self::getDb()->sqlDicts(
            'select * from ? where ? order by ? asc',
            sqlTable($table),
            sqlWhere($where),
            sqlColumn($order_column)
        );
    }

    /**
     * @param $target
     *
     * @return string
     * @throws \Exception
     */
    private static function getTableName($target): string
    {
        $target = SupportPolicy::DB_TABLE[$target];
        if (!$target) {
            throw new \Exception('invalid taget mapping  from ' . $target);
        }
        $table = 'support_' . $target;
        return $table;
    }
}
