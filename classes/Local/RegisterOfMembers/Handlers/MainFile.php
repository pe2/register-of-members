<?php

namespace Local\RegisterOfMembers\Handlers;

use DateTime;
use DateTimeZone;
use Local\RegisterOfMembers\RegisterOfMembersHandler;
use Local\RegisterOfMembers\RegisterOfMembersPlugin;

class MainFile extends RegisterOfMembersHandler
{
    /** @var string File type */
    private const FILE_TYPE = 'Общая информация о компаниях';

    /** @var string Table header */
    private const TABLE_HEADER = 'Реестр организаций';

    /** @var string Status of active member */
    private const ACTIVE_STATUS = 'Член СРО';

    /** @var string Status of expelled member */
    private const EXPELLED_STATUS = 'Исключен';

    /** @var array Array with links to detail pages */
    private $arDetailPagesLinkArray;

    /** @var int Number of active register members */
    private $numberOfActiveMembers = 0;

    /** @var int Number of expelled register members */
    private $numberOfExpelledMembers = 0;

    /**
     * @param array $arDetailPagesLinkArray Array with links to detail pages
     */
    public function __construct(array $arDetailPagesLinkArray)
    {
        $this->arDetailPagesLinkArray = $arDetailPagesLinkArray;
    }

    /**
     * Method process main register file
     *
     * @return bool
     */
    public function process(): bool
    {
        $mainRegistryXMLData = $this->getDataFromXmlFile(
            $_SERVER['DOCUMENT_ROOT'] . get_option('registerFilePath'),
            self::FILE_TYPE
        );
        if (empty($mainRegistryXMLData)) {
            return false;
        }

        $mainRegistryArrayData = $this->extractDataFromXmlFile($mainRegistryXMLData, self::FILE_TYPE);
        if (!count($mainRegistryArrayData)) {
            return false;
        }

        if (!$this->composeAndWriteRegisterMainPage($mainRegistryArrayData)) {
            return false;
        }

        return true;
    }

    /**
     * Method composes and writes main register page
     *
     * @param array $mainRegistryArrayData
     *
     * @return bool
     */
    private function composeAndWriteRegisterMainPage(array $mainRegistryArrayData): bool
    {
        $tableMarkup = $this->composeRegisterTable($mainRegistryArrayData);
        $registerStatusString = $this->composeRegisterStatusString();
        $tableHeader = '<h4 _ngcontent-c0 class="table_header">' . self::TABLE_HEADER . '</h4>';

        if (!file_put_contents(
            REGISTRY_OF_MEMBERS_PLG_PATH . RegisterOfMembersPlugin::REGISTER_MARKUP_PATH,
            $registerStatusString . $tableHeader . $tableMarkup
        )) {
            $this->log('Ошибка записи табличных данных в файл "' . self::FILE_TYPE . '"', 'error');
            return false;
        }

        $this->log('Табличные данные успешно записаны в файл "' . self::FILE_TYPE . '"');
        return true;
    }

    /**
     * Method appends register data to static html file
     *
     * @param array $mainRegistryArrayData
     *
     * @return bool
     */
    private function composeRegisterTable(array $mainRegistryArrayData): string
    {
        $tableMarkup = $this->getMainTableHeader();
        foreach ($mainRegistryArrayData['company'] as $arCompany) {
            $tableMarkup .= '<tr><td data-detail-link="' . ($this->arDetailPagesLinkArray[$arCompany['REESTR_NUM']] ?? '') . '">' .
                (int)$arCompany['REESTR_NUM'] . '</td><td>' . $arCompany['STATUS'] . '</td><td><a href="' .
                ($this->arDetailPagesLinkArray[$arCompany['REESTR_NUM']] ?? '') . '">' . $arCompany['MEMBERNAME'] . '</a></td><td>' .
                $arCompany['INN'] . '</td><td>' . $arCompany['OGRN'] . '</td></tr>';

            $this->numberOfActiveMembers += intval($arCompany['STATUS'] === self::ACTIVE_STATUS);
            $this->numberOfExpelledMembers += intval($arCompany['STATUS'] === self::EXPELLED_STATUS);
        }
        $tableMarkup .= '</tbody></table></div>';

        return $tableMarkup;
    }

    /**
     * Method returns main register table header
     *
     * @return string
     */
    private function getMainTableHeader(): string
    {
        return '
            <div class="table-wrapper"><div class="spinner" style="margin: 50px auto;"></div><table id="mainRegisterTable" style="display:none;">
            <thead>
                <tr><th>№ в реестре</th><th>Статус члена</th><th>Сокращенное наименование</th> <th>ИНН</th><th>ОГРН</th></tr>
                <tr class="filter"><td></td><td></td><td></td><td></td><td></td></tr>
            </thead>
            <tbody>';
    }

    /**
     * Method returns string with current active members
     *
     * @return string
     */
    private function composeRegisterStatusString(): string
    {
        $oDate = new DateTime();
        $date = $oDate->setTimezone(new DateTimeZone('Europe/Moscow'))->format('d.m.Y');

        return "<div class='page_content_orgnumbers'>" .
            "По состоянию на {$date} года количество действующих членов СРО — {$this->numberOfActiveMembers}</div>";
    }
}