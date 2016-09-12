<?php
/**
 * Object represents sql query with params
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
class SqlQuery
{
    var $txt;
    var $params = array();
    var $idx = 0;

    /**
     * Constructor
     *
     * @param string $txt
     */
    function SqlQuery($txt)
    {
        $this->txt = $txt;
    }

    /**
     * Set string param
     *
     * @param string $value
     */
    public function setString($value)
    {
        if ($value === null)
        {
            $this->params[$this->idx++] = "null";
            return;
        }
        $value = mysql_escape_string($value);
        $this->params[$this->idx++] = "'" . $value . "'";
    }

    /**
     * Set string param
     *
     * @param string $value
     */
    public function set($value)
    {
        self::setString($value);
    }


    /**
     * Set number param
     *
     * @param int $value
     * @throws Exception
     */
    public function setNumber($value)
    {
        if ($value === null)
        {
            $this->params[$this->idx++] = "null";
            return;
        }
        if (!is_numeric($value))
        {
            throw new Exception($value . ' is not a number');
        }
        $this->params[$this->idx++] = "'" . $value . "'";
    }

    /**
     * Get sql query
     *
     * @return string
     */
    public function getQuery()
    {
        if ($this->idx == 0)
        {
            return $this->txt;
        }
        $p = explode("?", $this->txt);
        $sql = '';
        for ($i = 0; $i <= $this->idx; $i++)
        {
            if ($i >= count($this->params))
            {
                $sql .= $p[$i];
            } else
            {
                $sql .= $p[$i] . $this->params[$i];
            }
        }
        return $sql;
    }
}