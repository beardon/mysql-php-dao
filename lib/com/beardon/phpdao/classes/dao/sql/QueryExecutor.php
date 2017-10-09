<?php
/**
 * Object executes sql queries
 *
 * @version 2.7
 * @date 2013-02-12
 *
 * Original author
 * @author http://phpdao.com
 *
 * Revision 2.7
 * @author Aaron Bean <aaron.bean@beardon.com>
 */
class QueryExecutor
{

    /**
     * @param SqlQuery $sqlQuery
     * @return array query results
     * @throws Exception
     */
    public static function execute($sqlQuery)
    {
        $transaction = Transaction::getCurrentTransaction();
        $connection = self::getCurrentConnection();
        $query = $sqlQuery->getQuery();
        $result = $connection->executeQuery($query);
        if (!$result)
        {
            throw new Exception(mysql_error());
        }
        $i = 0;
        $tab = array();
        while ($row = mysqli_fetch_array($result))
        {
            $tab[$i++] = $row;
        }
        mysqli_free_result($result);
        if (!$transaction)
        {
            $connection->close();
        }
        return $tab;
    }

    /**
     * @param SqlQuery $sqlQuery
     * @return int number of affected rows
     * @throws Exception
     */
    public static function executeUpdate($sqlQuery)
    {
        $connection = self::getCurrentConnection();
        $query = $sqlQuery->getQuery();
        $result = $connection->executeQuery($query);
        if (!$result)
        {
            throw new Exception(mysqli_error($connection->link));
        }
        return mysqli_affected_rows($connection->link);
    }

    /**
     * @param SqlQuery $sqlQuery
     * @return int insert id
     * @throws Exception
     */
    public static function executeInsert($sqlQuery)
    {
        $connection = self::getCurrentConnection();
        $query = $sqlQuery->getQuery();
        $result = $connection->executeQuery($query);
        if (!$result)
        {
            throw new Exception(mysqli_error($connection->link));
        }
        return mysqli_insert_id($connection->link);
    }

    /**
     * @return Connection
     */
    private static function getCurrentConnection() {
        $transaction = Transaction::getCurrentTransaction();
        if (!$transaction)
        {
            $connection = new Connection();
        } else
        {
            $connection = $transaction->getConnection();
        }
        return $connection;
    }

    /**
     * @param SqlQuery $sqlQuery
     * @param mixed $fieldIndex
     * @return string
     * @throws Exception
     */
    public static function queryForString($sqlQuery, $fieldIndex = 0)
    {
        $connection = self::getCurrentConnection();
        $result = $connection->executeQuery($sqlQuery->getQuery());
        if (!$result)
        {
            throw new Exception(mysqli_error($connection->link));
        }
        $row = mysqli_fetch_array($result);
        return $row[$fieldIndex];
    }

}
