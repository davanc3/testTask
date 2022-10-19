<?php
// файл подключается в init.php
use Bitrix\Main\EventManager;
use CIBlockElement;
use CEvent;
use CUser;

$eventManager = EventManager::getInstance();

$eventManager->addEventHandler("iblock", "OnBeforeIBlockElementDelete", ['ElementEdit', "OnBeforeIBlockElementDeleteHandler"]);

class ElementEdit
{
    public function OnBeforeIBlockElementDeleteHandler($ID)
    {
        global $APPLICATION, $USER;

        $arFilter = [
            'IBLOCK_ID' => 6, // предположим что это id каталога
            'ID' => $ID
        ];

        $arSelect = [
            'ID',
            'NAME',
            'SHOW_COUNTER'
        ];

        $rsElement = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        if ($arElement = $rsElement->Fetch()) {
            if ($arElement['SHOW_COUNTER'] > 10000) {
                $APPLICATION->ThrowException("Нельзя удалить данный товар, так как он очень популярный на сайте");
                
                // сообщение менеджеру будет отправляться через событие, предварительно созданное в административной панели, и сообщение создаётся и редактируется там.
                $rsUser = CUser::GetByID($USER->GetID());
                $arUser = $rsUser->Fetch();

                $filter = [
                    'GROUPS_ID' => 2 // предположим что это id группы менеджеров
                ];
                
                $rsManagers = CUser::getList(($by="id"), ($order="desc"), $filter);
                $manager = $rsManagers->Fetch();

                $fields = [
                    'ID_ПОЛЬЗОВАТЕЛЯ' => $USER->GetID(),
                    'ЛОГИН' => $arUser['LOGIN'],
                    'EMAIL_TO' => $manager['EMAIL']
                ];

                CEvent::Send(
                    'CUSTOM_ERROR_PRODUCT_DELETE',
                    SITE_ID,
                    $fields
                );
            }
        }
    }
}