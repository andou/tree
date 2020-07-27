<?php

namespace Andou\Tree\Helpers;

/**
 * Nested Set Model (https://en.wikipedia.org/wiki/Nested_set_model) Query Implementation
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2020 Antonio Pastorino <antonio.pastorino@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 *
 * @author Antonio Pastorino <antonio.pastorino@gmail.com>
 * @category tree
 * @package andou/tree
 * @copyright MIT License (http://opensource.org/licenses/MIT)
 */
class Database
{
    /**
     * @var Database
     */
    private static $_instance;

    /**
     * Restituisce una istanza della classe
     *
     * @return Database
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            $class = __CLASS__;
            self::$_instance = new $class;
        }

        return self::$_instance;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////  PUBLIC ENTRY POINTS PER QUERIES  ////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Esegue una query singola (con un solo risultato)
     *
     * @param $sql
     * @param $params
     * @param $results
     * @return mixed
     */
    public function executeSingleQuery($sql, $params, $results)
    {
        $statement = $this->prepareStatement($sql);
        $this->bindParams($statement, $params);
        $this->executeStatement($statement);
        $this->bindResults($statement, $results);
        $this->fetchStatement($statement);
        $this->closeStatement($statement);

        return $results;
    }


    /**
     * Esegue una query multipla (con risultati multipli)
     *
     * @param $sql
     * @param $params
     * @param $results
     * @return array
     */
    public function executeMutipleQuery($sql, $params, $results)
    {
        $statement = $this->prepareStatement($sql);
        $res = [];
        $this->bindParams($statement, $params);
        $this->executeStatement($statement);
        $this->bindResults($statement, $results);

        while ($this->fetchStatement($statement)) {
            $copy = [];
            foreach ($results as $result) {
                $copy[] = $result;
            }
            $res[] = $copy;
        }
        $this->closeStatement($statement);

        return $res;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////  GESTIONE STATEMENTS  ///////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Prepara uno statement
     *
     * @param $sql
     * @return false|\mysqli_stmt
     */
    protected function prepareStatement($sql)
    {
        return $this->getConnection()->prepare($sql);
    }

    /**
     * Bind type integer
     *
     * @var string
     */
    public static $INTEGER_BIND_TYPE = 'i';

    /**
     * Bind type double
     *
     * @var string
     */
    public static $DOUBLE_BIND_TYPE = 'd';

    /**
     * Bind type string
     *
     * @var string
     */
    public static $STRING_BIND_TYPE = 's';

    /**
     * Bind type BLOB
     *
     * @var string
     */
    public static $BLOD_BIND_TYPE = 'b';

    /**
     * Effettua il bind dei parametri su uno statemet
     *
     * @param $statement
     * @param array $bindings
     */
    protected function bindParams($statement, $bindings = [])
    {
        $types = [];
        $call_params = [];

        if (!empty($bindings)) {
            foreach ($bindings as $binding) {
                $types[] = $binding[0];
                $call_params[] = &$binding[1];
            }
            if (!empty($call_params) && count($types) == count($call_params)) {
                array_unshift($call_params, implode("", $types));
                call_user_func_array(array($statement, 'bind_param'), $call_params);
            }
        }

    }

    /**
     * Esegue uno statement
     *
     * @param $statement
     * @return $this
     */
    protected function executeStatement($statement)
    {
        $statement->execute();
        return $this;
    }


    /**
     * Effettua il bind dei risultati
     *
     * @param $statement
     * @param $results
     * @return $this
     */
    protected function bindResults($statement, &$results)
    {
        call_user_func_array(array($statement, 'bind_result'), $results);
        return $this;
    }

    /**
     * Effettua il fetch di uno statement
     *
     * @param $statement
     * @return mixed
     */
    protected function fetchStatement($statement)
    {
        return $statement->fetch();
    }

    /**
     * Chiude uno statement
     *
     * @param $statement
     * @return $this
     */
    protected function closeStatement($statement)
    {
        $statement->close();
        return $this;
    }

    /**
     * La connessione al database
     *
     * @var \mysqli
     */
    protected $connection;

    /**
     * Ottiene una connessione al Database
     *
     * @return \mysqli
     */
    public function getConnection()
    {
        if (empty($this->connection)) {
            $mysqli = @new \mysqli(
                $this->config->getHost(),
                $this->config->getUser(),
                $this->config->getPassword(),
                $this->config->getDatabase(),
                $this->config->getPort()
            );
            if ($mysqli->connect_errno) {
                die("<strong>Failed to connect to MySQL</strong> <br/><code>(" . $mysqli->connect_errno . ")</code> <em>" . $mysqli->connect_error . "</em>");
            }
            $this->connection = $mysqli;
        }
        return $this->connection;
    }

    /**
     * @var Config
     */
    protected $config;

    /**
     * Costruttore della classe
     *
     * Database constructor.
     */
    protected function __construct()
    {
        $this->config = Config::getInstance(__DIR__ . "/../../public/config.php");
    }


}