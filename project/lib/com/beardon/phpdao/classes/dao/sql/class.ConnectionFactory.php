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
     * @return resource
     * @throws Exception
     */
    static public function getConnection()
    {
        $conn = mysql_connect(ConnectionProperty::getHost(), ConnectionProperty::getUser(), ConnectionProperty::getPassword());
        mysql_select_db(ConnectionProperty::getDatabase());
        if (!$conn)
        {
            throw new Exception('could not connect to database');
        }
        return $conn;
    }

    /**
     * Close connection
     *
     * @param resource $connection
     */
    static public function close($connection)
    {
        mysql_close($connection);
    }
}