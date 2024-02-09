<?php

declare(strict_types=1);

namespace Ameos\Chatbot\Service;

use GuzzleHttp\RequestOptions;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ChatbotService
{
    /**
     * constructor
     * @param RequestFactory $requestFactory
     * @param ExtensionConfiguration $extensionConfiguration
     * @param ConnectionPool $connectionPool
     */
    public function __construct(
        private readonly RequestFactory $requestFactory,
        private readonly ExtensionConfiguration $extensionConfiguration,
        private readonly ConnectionPool $connectionPool,
    ) {
    }

    /**
     * ask question
     *
     * @param string $message
     * @param string $language
     * @return array
     */
    public function request(string $message, string $language = null): string
    {
        $response = $this->requestFactory->request(
            'https://api.openai.com/v1/chat/completions',
            'POST',
            [
                RequestOptions::HEADERS => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->extensionConfiguration->get('chatbot', 'chatgpt_apikey')
                ],
                RequestOptions::JSON => [
                    'model' => $this->extensionConfiguration->get('chatbot', 'chatgpt_model'),
                    'messages' => [
                        ['role' => 'user', 'content' => $message],
                        ['role' => 'system', 'content' => $this->buildSystemPrompt($language)]
                    ]
                ]
            ]
        );

        $answer = '';
        try {
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode($response->getBody()->getContents(), true);
                $answer = $responseData['choices'][0]['message']['content'] ?? '';
            } else {
                $answer = sprintf(LocalizationUtility::translate('apiError', 'chatbox'), '');
            }
        } catch (\Exception $e) {
            $answer = sprintf(LocalizationUtility::translate('apiError', 'chatbox'), $e->getMessage());
        }
        return $answer;
    }

    /**
     * build systemp prompt
     * add tree (TODO)
     * add type of record (TODO)
     *
     * @param string $language
     * @return string
     */
    private function buildSystemPrompt(string $language = null): string
    {
        $records = $this->getEnabledRecord($language);
        $pagesTree = $this->getPagesTree();

        return sprintf(
            LocalizationUtility::translate('systemPrompt', 'chatbot', null, $language),
            json_encode($records),
            json_encode($pagesTree)
        );
    }

    /**
     * return pages tree
     *
     * @param int $parent
     * @return array
     */
    private function getPagesTree(int $parent = 0): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $pages = $queryBuilder
            ->select('uid', 'title')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($parent, Connection::PARAM_INT))
            )
            ->orderBy('sorting', 'ASC')
            ->executeQuery();

        $tree = [];
        while ($page = $pages->fetchAssociative()) {
            $pageTree = [
                'uid' => (int)$page['uid'],
                'title' => $page['title'],
            ];
            $childs = $this->getPagesTree($pageTree['uid']);
            if (!empty($childs)) {
                $pageTree['childs'] = $childs;
            }
            $tree[] = $pageTree;
        }
        return $tree;
    }

    /**
     * return all record type
     *
     * @param string $language
     * @return array
     */
    private function getEnabledRecord(string $language = null): array
    {
        $records = [];
        foreach ($GLOBALS['TCA'] as $table => $configuration) {
            if (substr($configuration['ctrl']['title'], 0, 4) === 'LLL:') {
                $records[] = LocalizationUtility::translate($configuration['ctrl']['title'], null, null, $language);
            } else {
                $records[] = $configuration['ctrl']['title'];
            }
        }
        return $records;
    }
}
