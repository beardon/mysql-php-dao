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
        if (!$transaction)
        {
            $connection = new Connection();
        } else
        {
            $connection = $transaction->getConnection();
        }
        $query = $sqlQuery->getQuery();
        $result = $connection->executeQuery($query);
        if (!$result)
        {
            throw new Exception(mysql_error());
        }
        $i = 0;
        $tab = array();
        while ($row = mysql_fetch_array($result))
        {
            $tab[$i++] = $row;
        }
        mysql_free_result($result);
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
        $transaction = Transaction::getCurrentTransaction();
        if (!$transaction)
        {
            $connection = new Connection();
        } else
        {
            $connection = $transaction->getConnection();
        }
        $query = $sqlQuery->getQuery();
        $result = $connection->executeQuery($query);
        if (!$result)
        {
            throw new Exception(mysql_error());
        }
        return mysql_affected_rows();
    }

    /**
     * @param SqlQuery $sqlQuery
     * @return int insert id
     */
    public static function executeInsert($sqlQuery)
    {
        QueryExecutor::executeUpdate($sqlQuery);
        return mysql_insert_id();
    }

    /**
     * @param SqlQuery $sqlQuery
     * @param mixed $fieldIndex
     * @return string
     * @throws Exception
     */
    public static function queryForString($sqlQuery, $fieldIndex = 0)
    {
        $transaction = Transaction::getCurrentTransaction();
        if (!$transaction)
        {
            $connection = new Connection();
        } else
        {
            $connection = $transaction->getConnection();
        }
        $result = $connection->executeQuery($sqlQuery->getQuery());
        if (!$result)
        {
            throw new Exception(mysql_error());
        }
        $row = mysql_fetch_array($result);
        return $row[$fieldIndex];
    }

}