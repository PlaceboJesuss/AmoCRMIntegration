<?php

namespace Integration;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\ContactsCollection;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Collections\Leads\LeadsCollection;
use AmoCRM\Models\ContactModel;
use AmoCRM\Models\CustomFieldsValues\MultitextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\MultitextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\MultitextCustomFieldValueModel;
use AmoCRM\Models\LeadModel;
use Exception;
use Integration\Helpers\DB;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

require_once "vendor/autoload.php";
require_once "config.php";

if (!isset($_POST["clid"])) {
    die("Не передан clid");
}

$clid = $_POST["clid"];
$query = "SELECT host, access_token, refresh_token, expires FROM clients WHERE clid = :clid";
$client = DB::query($query, ["clid" => $clid])[0] ?? die("Клиент не найден");

try {

    $api = new AmoCRMApiClient(CLIENT_ID, CLIENT_SECRET, REDIRECT_URL);
    $api->setAccessToken(new AccessToken($client))->setAccountBaseDomain($client["host"])->onAccessTokenRefresh(
        function (AccessTokenInterface $accessToken, string $baseDomain) {
            $params = [
                "host" => $baseDomain,
                "access_token" => $accessToken->getToken(),
                "refresh_token" => $accessToken->getRefreshToken(),
                "expires" => $accessToken->getExpires()
            ];
            DB::query("UPDATE clients SET access_token = :access_token, refresh_token = :refresh_token, expires = :expires WHERE host = :host", $params);
        }
    );
    $leadsService = $api->leads();

    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $price = $_POST["price"];

    //получаем id кастомных полей для телефона и почты
    $cfs = $api->customFields("contacts")->get();
    $cfPhoneId = $cfs->getBy("code", "PHONE")->getId();
    $cfEmailId = $cfs->getBy("code", "EMAIL")->getId();

    //Формируем коллекцию кастомных полей для контакта
    $contactsCFS = new CustomFieldsValuesCollection();
    $contactsCFS->add(
        (new MultitextCustomFieldValuesModel())
            ->setFieldId($cfPhoneId)
            ->setValues(
                (new MultitextCustomFieldValueCollection())
                    ->add(
                        (new MultitextCustomFieldValueModel())
                            ->setValue($phone)
                    )
            )
    );
    $contactsCFS->add(
        (new MultitextCustomFieldValuesModel())
            ->setFieldId($cfEmailId)
            ->setValues(
                (new MultitextCustomFieldValueCollection())
                    ->add(
                        (new MultitextCustomFieldValueModel())
                            ->setValue($email)
                    )
            )
    );

    //формируем контакт
    $contact = (new ContactModel())->setName($name)->setCustomFieldsValues($contactsCFS);

    //Формируем сделку
    $lead = new LeadModel();
    $lead->setPrice($price)->setContacts((new ContactsCollection())->add($contact));

    //Добавляем ее
    $leadsService->addComplex((new LeadsCollection())->add($lead));
    echo "Сделка и контакт добавлены";
} catch (Exception $e) {
    echo "Обратитесь в поддержку";
}
