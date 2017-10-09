<?php
/*
 * Class return connection to database
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
class ConnectionFactory
{

    /**
     * Get connection
     *
     * @return mysqli
     * @throws Exception
     */
    static public function getConnection()
    {
        $link = mysqli_connect(ConnectionProperty::getHost(), ConnectionProperty::getUser(), ConnectionProperty::getPassword());
        mysqli_select_db($link, ConnectionProperty::getDatabase());
        if (!$link)
        {
            throw new Exception('could not connect to database');
        }
        return $link;
    }

    /**
     * Close connection
     *
     * @param Connection $connection
     */
    static public function close($connection)
    {
        mysqli_close($connection->link);
    }
}
