<?php

namespace Andou\Tree\Helpers;

use Andou\Tree\Enum\Languages;

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
class ParametersFetcher
{
    /**
     * @var ParametersFetcher
     */
    private static $_instance;

    /**
     * @return ParametersFetcher
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
     * @var Validator
     */
    protected $validator;

    /**
     * ParametersFetcher constructor.
     * Costruttore della classe.
     * Istanzia il validatore
     *
     */
    protected function __construct()
    {
        $this->validator = Validator::getInstance();
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////  PARAMETERS FETCH  ////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @var string
     */
    public static $NODE_ID_PARAMETER = 'node_id';

    /**
     * @var string
     */
    public static $LANGUAGE_PARAMETER = 'language';

    /**
     * @var string
     */
    public static $SEARCH_KEYWORD_PARAMETER = 'search_keyword';

    /**
     * @var string
     */
    public static $PAGE_NUM_PARAMETER = 'page_num';

    /**
     * @var string
     */
    public static $PAGE_SIZE_PARAMETER = 'page_size';

    /**
     * @return array
     */
    protected function getParameterList()
    {
        return [
            self::$NODE_ID_PARAMETER => [
                'type' => 'int',
                'required' => true
            ],
            self::$LANGUAGE_PARAMETER => [
                'type' => 'enum',
                'enum' => Languages::GetEnum(),
                'required' => true
            ],
            self::$SEARCH_KEYWORD_PARAMETER => [
                'type' => 'string',
                'required' => false
            ],
            self::$PAGE_NUM_PARAMETER => [
                'type' => 'int',
                'required' => false
            ],
            self::$PAGE_SIZE_PARAMETER => [
                'type' => 'int',
                'required' => false
            ],
        ];
    }


    /**
     * Recupera i parametri in GET della richiesta ed effettua un controllo formale sulla loro struttura e sulla
     * mandatorietà.
     *
     * Non contiene controlli legati ai dati
     *
     * @return array
     */
    public function fetchParameters()
    {
        $res = [];

        foreach ($this->getParameterList() as $parameter_name => $parameters_data) {
            $def = false;

            switch ($parameter_name) {
                case self::$PAGE_NUM_PARAMETER:
                    $def = 0;
                    break;
                case self::$PAGE_SIZE_PARAMETER:
                    $def = 100;
                    break;
            }

            $request_parameter = $this->fetchParameter($parameter_name, $def);

            if ($parameters_data['required'] && !$this->validator->validateNotEmpty($request_parameter)) {
                $this->addError(self::$MISSING_PARAMETER_ERROR);
            }

            switch ($parameter_name) {
                case self::$NODE_ID_PARAMETER:
                    if ($this->validator->validateInt($request_parameter)) {
                        $res[$parameter_name] = intval($request_parameter);
                    } else {
                        $this->addError(self::$INVALID_NODE_ERROR);
                    }
                    break;
                case self::$LANGUAGE_PARAMETER:
                    if ($this->validator->validateEnum($request_parameter, $parameters_data['enum'])) {
                        $res[$parameter_name] = $request_parameter;
                    } else {
                        $this->addError(self::$WRONG_LANGUAGE_ERROR);
                    }
                    break;
                case self::$SEARCH_KEYWORD_PARAMETER:
                    $res[$parameter_name] = $request_parameter;
                    break;
                case self::$PAGE_NUM_PARAMETER:
                    if (
                        $this->validator->validateInt($request_parameter) &&
                        $this->validator->validateIntBase($request_parameter, 0)
                    ) {
                        $res[$parameter_name] = intval($request_parameter);
                    } else {
                        $this->addError(self::$INVALID_PAGE_NUMBER_ERROR);
                    }
                    break;
                case self::$PAGE_SIZE_PARAMETER:
                    if (
                        $this->validator->validateInt($request_parameter) &&
                        $this->validator->validateIntRange($request_parameter, 0, 1000)
                    ) {
                        $res[$parameter_name] = intval($request_parameter);
                    } else {
                        $this->addError(self::$INVALID_PAGE_SIZE_ERROR);
                    }
                    break;
            }
        }
        return $res;
    }

    protected function fetchParameter($name, $default = false)
    {
        return $this->fetchGetParameter($name, $default);

    }

    protected function fetchGetParameter($name, $default = false)
    {
        return isset($_GET[$name]) && !empty($_GET[$name]) ? $_GET[$name] : $default;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////  GESTIONE ERROrI  ////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Stringa di errore per parametro mancante
     *
     * @var string
     */
    public static $MISSING_PARAMETER_ERROR = 'Missing mandatory params';

    /**
     * Stringa di errore per lingua non valida
     * @note Questo non era richiesto
     *
     * @var string
     */
    public static $WRONG_LANGUAGE_ERROR = 'Wrong language';

    /**
     * Stringa di errore per id node non corretto
     *
     * @var string
     */
    public static $INVALID_NODE_ERROR = 'Invalid node id';

    /**
     * Stringa di errore per numero pagina non corretto
     *
     * @var string
     */
    public static $INVALID_PAGE_NUMBER_ERROR = 'Invalid page number requested';

    /**
     * Stringa di errore per dimensione pagina non corretta
     *
     * @var string
     */
    public static $INVALID_PAGE_SIZE_ERROR = 'Invalid page size requested';


    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * @param $error
     */
    public function addError($error)
    {
        $this->errors[] = $error;
    }


}