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
class Config
{
    /**
     * @var Config
     */
    private static $_instance;

    /**
     * @return Config
     */
    public static function getInstance($config_file)
    {

        if (!isset(self::$_instance)) {
            $config = self::ReadConfigFile($config_file);

            if (!$config) {
                die("{$config_file} is not a valid config file");
            }

            list($host, $user, $password, $database, $port) = $config;

            $class = __CLASS__;
            $instance = new $class;
            self::$_instance = $instance
                ->setHost($host)
                ->setUser($user)
                ->setPassword($password)
                ->setDatabase($database)
                ->setPort($port);
        }

        return self::$_instance;
    }


    /**
     * Verifica presenza e correttezza del file di configurazioni
     *
     * @param $config_file
     * @return bool
     */
    protected static function CheckConfigFile($config_file)
    {
        if (!file_exists($config_file)) {
            return false;
        }

        $configs = require_once $config_file;

        if (
            !isset($configs) || empty($configs) ||
            !isset($configs['host']) || empty($configs['host']) ||
            !isset($configs['user']) || empty($configs['user']) ||
            !isset($configs['password']) ||
            !isset($configs['database']) || empty($configs['database']) ||
            !isset($configs['port']) || empty($configs['port'])
        ) {
            return false;
        }
        return true;
    }

    /**
     * Legge e restituisce il file di configurazioni
     *
     * @param $config_file
     * @return array|bool
     */
    protected static function ReadConfigFile($config_file)
    {
        if (!self::CheckConfigFile($config_file)) {
            return false;
        }

        $configs = require $config_file;

        return [
            $configs['host'],
            $configs['user'],
            $configs['password'],
            $configs['database'],
            $configs['port'],
        ];
    }

    /**
     * Host MySQL
     *
     * @var string
     */
    protected $host;

    /**
     * Username per la connessione al database
     *
     * @var string
     */
    protected $user;

    /**
     * Password per la connessione al database
     *
     * @var string
     */
    protected $password;

    /**
     * Il database da utilizzare
     *
     * @var string
     */
    protected $database;

    /**
     * Porta di MySQl
     *
     * @var string
     */
    protected $port;

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     * @return Config
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return Config
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return !empty($this->password) ? $this->password : null;
    }

    /**
     * @param mixed $password
     * @return Config
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param mixed $database
     * @return Config
     */
    public function setDatabase($database)
    {
        $this->database = $database;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     * @return Config
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }




}