<?php
/**
 * Object represents connection to database
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
class Connection
{
    /** @var Connection */
    private $connection;

    public function Connection()
    {
        $this->connection = ConnectionFactory::getConnection();
    }

    public function close()
    {
        ConnectionFactory::close($this->connection);
    }

    /**
     * Execute SQL with current connection
     *
     * @param string $sql
     * @return resource
     */
    public function executeQuery($sql)
    {
        return mysql_query($sql, $this->connection);
    }
}