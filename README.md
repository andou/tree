## Description

A simple implementation of Nested Set Model (https://en.wikipedia.org/wiki/Nested_set_model) Query API

## Usage

### Clone this repository

```shell
$ git clone git@github.com:andou/tree.git
```

### Install dependencies with composer and generate the autoload file

```shell
$ cr tree
$ composer install
```

### Check the database connection configuration

All the configurations are stored in the file `public/config.php`


### Create the database structure and populate tables

```shell
$ cd database
$ php tables.php
$ php data.php
```

### Run a local php server

```shell
$ php -S 127.0.0.1:8000 -t public
```

### Open your browser

[http://127.0.0.1:8000/api.php?node_id=5&language=italian](http://127.0.0.1:8000/api.php?node_id=5&language=italian)