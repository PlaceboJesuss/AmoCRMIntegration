<?php

namespace Integration;

use Integration\Helpers\DB;

if (PHP_SAPI != 'cli') return;

require_once "vendor/autoload.php";

DB::query("CREATE TABLE IF NOT EXISTS clients (clid SERIAL, host TEXT, access_token TEXT, refresh_token TEXT, expires BIGINT);");
