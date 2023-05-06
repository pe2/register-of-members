<?php

namespace Local\RegisterOfMembers;

use Exception;
use Local\RegisterOfMembers\Handlers\DetailFile;
use Local\RegisterOfMembers\Handlers\MainFile;

class RegisterOfMembersHandler extends RegisterOfMembersPlugin
{
    /**
     * Method process register files
     */
    public function processFiles(): void
    {
        $oDetailFileHandler = new DetailFile();
        if (!$oDetailFileHandler->process()) {
            return;
        }

        $oMainFileHandler = new MainFile($oDetailFileHandler->getDetailPagesLinksArray());
        if (!$oMainFileHandler->process()) {
            return;
        }
    }

    /**
     * Method returns file content for given path
     *
     * @param string $filePath Path to file
     * @param string $fileDescription File type (for log)
     *
     * @return string File content or empty string
     */
    protected function getDataFromXmlFile(string $filePath, string $fileDescription): string
    {
        $fileContent = file_get_contents($filePath);
        if (!is_string($fileContent) || 0 >= mb_strlen($fileContent)) {
            $this->log('Ошибка получения данных из файла "' . $fileDescription . '"', 'error');
            return '';
        } else {
            $this->log('Файл "' . $fileDescription . '" успешно прочитан');
            return $fileContent;
        }
    }

    /**
     * Method extract XML data to array
     *
     * @param string $fileContent XML content of file
     * @param string $fileDescription File type (for log)
     *
     * @return array Parsed array
     */
    protected function extractDataFromXmlFile(string $fileContent, string $fileDescription): array
    {
        $arRegister = [];
        try {
            $xmlString = simplexml_load_string($fileContent);
            $jsonString = json_encode($xmlString);
            $arRegister = json_decode($jsonString, true);
            if (!count($arRegister)) {
                throw new Exception('файл пуст.');
            }
        } catch (Exception $e) {
            $this->log('Ошибка разбора данных из файла "' . $fileDescription . '". Описание: ' . $e->getMessage(), 'error');
            return $arRegister;
        }

        $this->log('Файл "' . $fileDescription . '" успешно разобран');
        return $arRegister;
    }
}