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
    /** @var mysqli */
    public $link;

    public function Connection()
    {
        $this->link = ConnectionFactory::getConnection();
    }

    public function close()
    {
        ConnectionFactory::close($this);
    }

    /**
     * Execute SQL with current connection
     *
     * @param string $sql
     * @return bool|mysqli_result
     */
    public function executeQuery($sql)
    {
        return mysqli_query($this->link, $sql);
    }
}
