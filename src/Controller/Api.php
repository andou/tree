<?php

namespace Andou\Tree\Controller;

use Andou\Tree\Helpers\NodeFetcher;
use Andou\Tree\Helpers\ParametersFetcher;

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
class Api
{
    /**
     * @var Api
     */
    private static $_instance;

    /**
     * Restituisce una istanza della classe controller
     *
     * @return Api
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
     * @var ParametersFetcher
     */
    protected $parametersFetcher;

    /**
     * @var NodeFetcher
     */
    protected $node_fetcher;

    /**
     * Api constructor.
     *
     * Imposta il fetcher dei parametri
     *
     */
    protected function __construct()
    {
        $this->parametersFetcher = ParametersFetcher::getInstance();
        $this->node_fetcher = NodeFetcher::getInstance();
    }

    /**
     * Entry point del controller
     *
     * @param bool $echo
     * @return array
     */
    public function execute($echo = true)
    {
        $res = $this->getResponsePacket();

        if ($echo) {
            echo $res;
        }

        $this->setResponseHeaders();

        return $res;
    }


    /**
     * Costruisce il pacchetto di risposta
     *
     * @param bool $json_encode
     * @return array|false|string
     */
    protected function getResponsePacket($json_encode = true)
    {
        $data = [
            'nodes' => [],
            'error' => ""
        ];

        $input = $this->parametersFetcher->fetchParameters();

        if ($this->parametersFetcher->hasErrors()) {
            $errors = $this->parametersFetcher->getErrors();
            $data['error'] = array_shift($errors);
        } else {
            if ($this->node_fetcher->checkNodeExists($input[ParametersFetcher::$NODE_ID_PARAMETER])) {
                $data['nodes'] = $this->processInput($input);
            } else {
                $data['error'] = ParametersFetcher::$INVALID_NODE_ERROR;
            }
        }

        return $json_encode ? json_encode($data) : $data;
    }

    /**
     * Processa l'input ed effettua le necessarie chiamate al layer di business logic
     *
     * - Recupera tutti i nodi figli di un determinato nodo sulla base dei parametri in input
     * - Se necessario filtra i nodi figli sulla base di una keyword
     * - Splitta il risultato per gestire la paginazione
     *
     * @param $input
     * @return array
     */
    protected function processInput($input)
    {
        $children = $this
            ->node_fetcher
            ->fetchNodeChildren(
                $input[ParametersFetcher::$NODE_ID_PARAMETER],
                $input[ParametersFetcher::$LANGUAGE_PARAMETER]
            );

        if (!empty($input[ParametersFetcher::$SEARCH_KEYWORD_PARAMETER])) {
            $children = $this->filterChildren($children, $input[ParametersFetcher::$SEARCH_KEYWORD_PARAMETER]);
        }

        $children = array_chunk($children, $input[ParametersFetcher::$PAGE_SIZE_PARAMETER]);

        return isset($children[$input[ParametersFetcher::$PAGE_NUM_PARAMETER]]) ? $children[$input[ParametersFetcher::$PAGE_NUM_PARAMETER]] : [];
    }

    protected function filterChildren($children, $keyword)
    {
        $res = [];
        foreach ($children as $child) {
            $name = strtolower($child[0]);
            if (strpos($name, strtolower($keyword)) !== false) {
                $res[] = $child;
            }
        }
        return $res;
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////  GESTIONE HEADERS  ////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Imposta gli header di risposta
     *
     * @return $this
     */
    protected function setResponseHeaders()
    {
        header('Content-type: application/json');
        return $this;
    }


}