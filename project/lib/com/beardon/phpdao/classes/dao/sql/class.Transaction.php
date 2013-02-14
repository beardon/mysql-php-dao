<?php
/**
 * Database transaction
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
class Transaction
{
    /** @var ArrayList */
    private static $transactions;

    /** @var Connection */
    private $connection;

    public function Transaction()
    {
        $this->connection = new Connection();
        if (!Transaction::$transactions)
        {
            Transaction::$transactions = new ArrayList();
        }
        Transaction::$transactions->add($this);
        $this->connection->executeQuery('BEGIN');
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        $this->connection->executeQuery('COMMIT');
        $this->connection->close();
        Transaction::$transactions->removeLast();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        $this->connection->executeQuery('ROLLBACK');
        $this->connection->close();
        Transaction::$transactions->removeLast();
    }

    /**
     * Get current connection
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Returns the current transaction
     *
     * @return bool|Transaction
     */
    public static function getCurrentTransaction()
    {
        if (Transaction::$transactions)
        {
            $tran = Transaction::$transactions->getLast();
            return $tran;
        }
        return false;
    }
}