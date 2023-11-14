<?php

namespace Integration;

use AmoCRM\OAuth\AmoCRMOAuth;
use Exception;
use Integration\Helpers\DB;
use Integration\Helpers\RequestHelper;

require_once "vendor/autoload.php";
require_once "config.php";
try {
    $code = $_GET["code"];
    $host = $_GET["referer"];

    $oauth = new AmoCRMOAuth(CLIENT_ID, CLIENT_SECRET, REDIRECT_URL);
    $oauth->setBaseDomain($host);
    $resp = $oauth->getAccessTokenByCode($code);

    $params = [
        "host" => $host,
        "access_token" => $resp->getToken(),
        "refresh_token" => $resp->getRefreshToken(),
        "expires"=>$resp->getExpires()
    ];
    $clid = DB::query("INSERT INTO clients (host, access_token, refresh_token, expires) VALUES (:host, :access_token, :refresh_token, :expires) RETURNING clid", $params)[0]["clid"];
    $form = RequestHelper::request("http://nginx/form.php?clid=" . $clid);
    $form = htmlentities($form, ENT_QUOTES, 'utf-8');
    echo "Интеграция добавлена. HTML код формы<br><textarea rows=8>$form</textarea><br>Или перейдите <a href=\"" . HOST . "/form.php?clid=$clid\">сюда</a>";
} catch (Exception $e) {
    echo "Произошла ошибка обратитесь ..." . $e->getMessage();
}
