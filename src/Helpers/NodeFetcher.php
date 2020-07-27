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
class NodeFetcher
{
    /**
     * @var NodeFetcher
     */
    private static $_instance;

    /**
     * Restituisce una istanza della classe
     *
     * @return NodeFetcher
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            $class = __CLASS__;
            self::$_instance = new $class;
        }

        return self::$_instance;
    }

    /**
     * Verifica che un determinato nodo esista
     *
     * @param $node_id
     * @return bool
     */
    public function checkNodeExists($node_id)
    {

        $idNode = null;

        $this->database->executeSingleQuery(
            "SELECT idNode FROM node_tree WHERE idNode = ?",
            [
                [Database::$INTEGER_BIND_TYPE, $node_id]
            ],
            [
                &$idNode,
            ]
        );

        return isset($idNode) && !empty($idNode);
    }

    /**
     * Effettua il fetch delle informzioni relative ad un determinato nodo
     *
     * @param $node_id
     * @return array
     */
    public function fetchNodeInfo($node_id)
    {
        $idNode = null;
        $level = null;
        $iLeft = null;
        $iRight = null;

        $this->database->executeSingleQuery(
            "SELECT idNode,level,iLeft,iRight FROM node_tree WHERE idNode = ?",
            [
                [Database::$INTEGER_BIND_TYPE, $node_id]
            ],
            [
                &$idNode,
                &$level,
                &$iLeft,
                &$iRight
            ]
        );

        $res = [
            $idNode,
            $level,
            $iLeft,
            $iRight
        ];

        return $res;
    }

    /**
     * Recupera le informazioni relative ai figli di un nodo
     *
     * @param $node_id
     * @param $language
     * @return array
     */
    public function fetchNodeChildren($node_id, $language)
    {
        list($master_id, $master_level, $master_left, $master_right) = $this->fetchNodeInfo($node_id);

        $idNode = null;
        $level = null;
        $iLeft = null;
        $iRight = null;

        $_res = $this->database->executeMutipleQuery(
            "SELECT idNode,level,iLeft,iRight FROM node_tree WHERE iLeft > ? AND iRight < ?",
            [
                [Database::$INTEGER_BIND_TYPE, $master_left],
                [Database::$INTEGER_BIND_TYPE, $master_right],
            ],
            [
                &$idNode,
                &$level,
                &$iLeft,
                &$iRight
            ]
        );

        $last = [
            $idNode,
            $level,
            $iLeft,
            $iRight
        ];

        $res = [];

        foreach ($_res as $node) {
            $children_count = 0;

            $this->database->executeSingleQuery(
                "SELECT count(*) FROM node_tree WHERE iLeft > ? AND iRight < ?",
                [
                    [Database::$INTEGER_BIND_TYPE, $node[2]],
                    [Database::$INTEGER_BIND_TYPE, $node[3]],
                ],
                [
                    &$children_count
                ]
            );

            $name = $this->fetchNodeName($node[0], $language);

            array_unshift($node, $name);

            $res[] = [
                "node_id" => $node[1],
                "name" => $node[0],
                "children_count" => $children_count
            ];
        }

        return $res;
    }

    /**
     * Recupera il nome di un nodo nella lingua richiesta
     *
     * @param $node_id
     * @param $language
     * @return null
     */
    public function fetchNodeName($node_id, $language)
    {

        $nodeName = null;

        $this->database->executeSingleQuery(
            "SELECT nodeName FROM node_tree_names WHERE idNode = ? AND language = ?",
            [
                [Database::$INTEGER_BIND_TYPE, $node_id],
                [Database::$STRING_BIND_TYPE, $language],
            ],
            [
                &$nodeName,
            ]
        );

        return $nodeName;
    }


    /**
     * @var \Andou\Tree\Helpers\Database
     */
    protected $database;

    /**
     * Api constructor.
     *
     * Imposta il fetcher dei parametri
     *
     */
    protected function __construct()
    {
        $this->database = Database::getInstance();
    }


}