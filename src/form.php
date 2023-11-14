<?php
$clid = $_GET["clid"] ?? die("Не передан clid");
require_once("config.php")
?>
<form action="<?= HOST ?>/hook.php" method="POST">
    Имя:<input type="text" name="name"><br>
    Email:<input type="text" name="email"><br>
    Телефон:<input type="text" name="phone"><br>
    Цена:<input type="text" name="price"><br>
    <input type="text" name="clid" value="<?= $clid ?>" hidden>
    <input type="submit" value="Отправить">
</form>