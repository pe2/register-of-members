<?php

namespace Local\RegisterOfMembers\Handlers;

use Local\RegisterOfMembers\RegisterOfMembersHandler;
use WP_Query;

class DetailFile extends RegisterOfMembersHandler
{
    /** @var string File type */
    private const FILE_TYPE = 'Детальная информация о компаниях';

    /** @var int Min number of fields to check success table generation */
    private const MIN_NUMBER_OF_FIELDS = 10;

    /** @var string Field name used for page slug generation */
    private const FIELD_NAME_FOR_SLUG = 'Полное наименование';

    /** @var string[] Cell names with are not suitable for colspan */
    private const NOT_COLSPAN_CELLS = ['Сайт', 'Место нахождения', 'Уровень ответственности', 'Размер взноса (руб.)'];

    /** @var array Array of links to companies detail pages */
    private $arCompaniesDetailPagesLinks = [];

    /**
     * Method process detail info file
     *
     * @return bool
     */
    public function process(): bool
    {
        $detailRegistryXMLData = $this->getDataFromXmlFile(
            $_SERVER['DOCUMENT_ROOT'] . get_option('registerDetailFilePath'),
            self::FILE_TYPE
        );
        if (empty($detailRegistryXMLData)) {
            return false;
        }

        $detailRegistryArrayData = $this->extractDataFromXmlFile($detailRegistryXMLData, self::FILE_TYPE);
        if (!count($detailRegistryArrayData)) {
            return false;
        }

        $arCompaniesDetailInfo = $this->parseDetailRegisterDataArray($detailRegistryArrayData);

        $arCompaniesDetailHtmlInfo = $this->generateTablesFromArrayData($arCompaniesDetailInfo);

        $this->writeCompaniesDataTablesAsPages($arCompaniesDetailHtmlInfo);

        return true;
    }

    /**
     * Method returns assoc. array with companies data grouped by id
     *
     * @param array $detailRegistryArrayData
     *
     * @return array
     */
    private function parseDetailRegisterDataArray(array $detailRegistryArrayData): array
    {
        $arCompaniesDetailInfo = [];
        foreach ($detailRegistryArrayData['row'] as $fieldId => $arRecord) {
            if (is_array($arRecord['colvalue']) && !count($arRecord['colvalue'])) {
                if (in_array(trim($arRecord['colname']), self::NOT_COLSPAN_CELLS)) {
                    $arRecord['colvalue'] = ' ';
                } else {
                    $arRecord['colvalue'] = 'COLSPAN_2';
                }
            }
            $arCompaniesDetailInfo[$arRecord['agent_id']][$fieldId] = [$arRecord['colname'] => $arRecord['colvalue']];
        }

        return $arCompaniesDetailInfo;
    }

    /**
     * Method composes html tables with detail info about companies
     *
     * @param array $arCompaniesDetailInfo
     *
     * @return array
     */
    private function generateTablesFromArrayData(array $arCompaniesDetailInfo): array
    {
        $arCompaniesDetailHtmlInfo = [];
        $numberOfSuccessfullyGeneratedTables = 0;
        foreach ($arCompaniesDetailInfo as $id => $arCompanyFields) {
            $html = $this->addPrintAndPDFLinks();
            $html .= '<table class="member_detail_info">';
            $numberOfFields = 0;
            $name = '';
            foreach ($arCompanyFields as $arCompanyField) {
                $fieldName = key($arCompanyField);
                $fieldValue = $arCompanyField[$fieldName];
                if ('COLSPAN_2' !== $fieldValue) {
                    $html .= '<tr><td>' . $fieldName . '</td><td>' . $fieldValue . '</td></tr>';
                } else {
                    $html .= '<tr><td colspan="2">' . $fieldName . '</td></tr>';
                }
                $numberOfFields += (int)!empty($fieldValue);
                if (self::FIELD_NAME_FOR_SLUG === $fieldName) {
                    $name = $fieldValue;
                }
            }
            $arCompaniesDetailHtmlInfo[$id] = ['table' => $html . '</table>[registerOfMembersDetailEmptyTagForStyles]', 'name' => $name];
            $numberOfSuccessfullyGeneratedTables += (int)(self::MIN_NUMBER_OF_FIELDS < $numberOfFields);
        }
        if (0 === $numberOfSuccessfullyGeneratedTables) {
            $this->log('Не получилось сгенерировать таблицы с детальной информацией о компаниях', 'error');
        } else {
            $this->log('Успешно сгенерированы с детальной информацией о компаниях в количестве ' .
                $numberOfSuccessfullyGeneratedTables . ' шт.');
        }

        return $arCompaniesDetailHtmlInfo;
    }

    /**
     * Method returns markup with print and pdf version of detailed member info
     *
     * @return string
     */
    private function addPrintAndPDFLinks(): string
    {
        return '<div class="printAndPdfLinks">' .
            '<div><span class="print-version"><img src="' . REGISTRY_OF_MEMBERS_PLG_URL .
            'img/printer-icon.png' . '" alt="print-version"/>Версия для печати</span></div>' .
            '<div><span class="pdf-version"><img src="' . REGISTRY_OF_MEMBERS_PLG_URL .
            'img/pdf-icon.png' . '" alt="print-version"/>PDF версия карточки</span></div></div>';
    }

    /**
     * Method creates/updates WP pages with detail companies data
     *
     * @param array $arCompaniesDetailHtmlInfo
     */
    private function writeCompaniesDataTablesAsPages(array $arCompaniesDetailHtmlInfo): void
    {
        $successInsert = $successUpdate = 0;
        $counter = 0;
        foreach ($arCompaniesDetailHtmlInfo as $companyId => $arCompanyDetailHtmlInfo) {
            if (2 < ++$counter) {
                break;
            }
            $arCompanyTableData = $arCompanyDetailHtmlInfo['table'];
            $name = $arCompanyDetailHtmlInfo['name'];
            $arPage = $this->getPageInfo($name);

            if (isset($arPage['ID']) && 0 < $arPage['ID']) {
                $updateResult = wp_update_post([
                    'ID' => $arPage['ID'],
                    'post_content' => $arCompanyTableData
                ], true);
                if (is_wp_error($updateResult)) {
                    $this->log($updateResult->get_error_message(), 'error');
                } else {
                    $successUpdate++;
                    $this->arCompaniesDetailPagesLinks[$companyId] = $arPage['guid'];
                }
            } else {
                $insertResult = wp_insert_post([
                    'post_title' => $name,
                    'post_type' => 'page',
                    'post_content' => $arCompanyTableData,
                    'post_status' => 'publish',
                    'post_author' => get_option('registerDefaultUser'),
                    'post_parent' => get_option('registerBasePage')
                ], true);
                if (is_wp_error($insertResult)) {
                    $this->log($insertResult->get_error_message(), 'error');
                } else {
                    $successInsert++;
                    $this->arCompaniesDetailPagesLinks[$companyId] = get_permalink($insertResult);
                }
            }
        }

        $this->log('Успешно добавлена информация о ' . $successInsert .
            ' организациях, успешно обновлена информация о ' . $successUpdate . ' организациях');
    }

    /**
     * Method returns page ids
     *
     * @param string $name Page name
     *
     * @return array ['ID', 'guid'] or empty array if page doesn't exist
     */
    private function getPageInfo(string $name): array
    {
        $query = new WP_Query(
            array(
                'post_type' => 'page',
                'title' => html_entity_decode($name),
                'post_status' => 'all',
                'posts_per_page' => 1,
                'no_found_rows' => true,
                'ignore_sticky_posts' => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
                'orderby' => 'post_date ID',
                'order' => 'ASC',
            )
        );

        if (!empty($query->post)) {
            return ['ID' => $query->post->ID, 'guid' => $query->post->guid];
        } else {
            return [];
        }
    }

    /**
     * Method returns array of links to companies detail pages
     *
     * @return array
     */
    public function getDetailPagesLinksArray(): array
    {
        return $this->arCompaniesDetailPagesLinks;
    }
}