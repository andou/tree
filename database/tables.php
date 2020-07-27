#!/usr/local/bin/php
<?php

use Andou\Tree\Helpers\Database;

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

require_once '../vendor/autoload.php';

$db = Database::getInstance()->getConnection();

$filename = 'tables.sql';
$templine = '';
$lines = file($filename);
foreach ($lines as $line) {

    if (substr($line, 0, 2) == '--' || $line == '') {
        continue;
    }

    $templine .= $line;

    if (substr(trim($line), -1, 1) == ';') {
        $db->query($templine) or print('Error performing query' . $templine . '\': ' . $db->errno . ' - ' . $db->error . "\n\n");
        $templine = '';
    }
}

echo "Creation done" . "\n\n";