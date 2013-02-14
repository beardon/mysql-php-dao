<?php
/**
 * ArrayList
 *
 * @version 2.7
 * @date 2013-02-12
 *
 * Original author
 * @author: Tomasz Jazwinski
 *
 * Revision 2.7
 * @author Aaron Bean <aaron.bean@beardon.com>
 */
class ArrayList
{
    private $tab;
    private $size;

    public function ArrayList()
    {
        $this->tab = array();
        $this->size = 0;
    }

    /**
     * Dodanie wartosci do listy
     */
    public function add($value)
    {
        $this->tab[$this->size] = $value;
        $this->size = ($this->size) + 1;
    }

    /**
     * Pobranie elementu o numerze podanym
     * jako parametr metody
     */
    public function get($idx)
    {
        return $this->tab[$idx];
    }

    /**
     * Pobranie ostatniego elementu
     */
    public function getLast()
    {
        if ($this->size == 0)
        {
            return null;
        }
        return $this->tab[($this->size) - 1];
    }

    /**
     * Rozmiar listy
     */
    public function size()
    {
        return $this->size;
    }

    /**
     * Czy lista jest pusta
     */
    public function isEmpty()
    {
        return ($this->size) == 0;
    }

    /**
     * Usuniecie ostatniego
     */
    public function removeLast()
    {
        return $this->size = ($this->size) - 1;
    }
}