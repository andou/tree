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
class Validator
{
    /**
     * @var Validator
     */
    private static $_instance;

    /**
     * @return Validator
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            $class = __CLASS__;
            self::$_instance = new $class;
        }

        return self::$_instance;
    }

    public function validateNotEmpty($val)
    {
        return !empty($val);
    }

    public function validateInt($val)
    {
        return false !== filter_var(
            $val,
            FILTER_VALIDATE_INT
        );
    }

    public function validateIntBase($val, $base = 0)
    {
        return false !== filter_var(
            $val,
            FILTER_VALIDATE_INT, [
                'options' => [
                    'min_range' => $base
                ]
            ]
        );
    }

    public function validateIntRange($val, $min = 0, $max = 1000)
    {
        return false !== filter_var(
            $val,
            FILTER_VALIDATE_INT, [
                'options' => [
                    'min_range' => $min,
                    'max_range' => $max
                ]
            ]
        );
    }

    public function validateEnum($val, $enum = [])
    {
        return in_array($val, $enum);
    }


}