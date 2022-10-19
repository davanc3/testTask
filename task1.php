<?php
use Bitrix\Main\EventManager;
use CIBlockElement;
use DateTime;

$eventManager = EventManager::getInstance();

$eventManager->addEventHandler("iblock", "OnBeforeIBlockElementUpdate", ['ElementEdit', "OnBeforeIBlockElementUpdateHandler"]);

class ElementEdit
{
    public function OnBeforeIBlockElementUpdateHandler(&$arFields)
    {
        global $APPLICATION;

        $arFilter = [
            'IBLOCK_ID' => 6, // предположим что это id каталога
            'ID' => $arParams["ID"]
        ];

        $arSelect = [
            'ID',
            'NAME',
            'DATE_CREATE'
        ];

        $rsElement = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        if ($arElement = $rsElement->Fetch()) {
            $elementCreate = new DateTime($arElement['DATE_CREATE']);
            $interval = $elementCreate->diff(new DateTime());
            $diff = intval($interval->format('%d'));
            if ($diff < 7) {
                $APPLICATION->ThrowException("Товар " . $arElement['NAME'] . " был создан менее одной недели назад и не может быть изменен.");
                return false;
            }
        }
    }
}