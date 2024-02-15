<?php

declare(strict_types=1);

namespace Ameos\Chatbot\Service\Prompt;

use Ameos\Chatbot\Enum\Configuration;
use TYPO3\CMS\Backend\Tree\Repository\PageTreeRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class BackendPromptService
{
    /**
     * @var BackendUserAuthentication
     */
    private ?BackendUserAuthentication $backendUser;

    /**
     * constructor
     *
     * @param PageTreeRepository $pageTreeRepository
     */
    public function __construct(private readonly PageTreeRepository $pageTreeRepository)
    {
        $this->backendUser = Environment::isCli() ? null : $GLOBALS['BE_USER'];
    }

    /**
     * return system prompt
     *
     * @param string $language
     * @return string
     */
    public function getPrompt(string $language = null): string
    {
        return 'sysprompt';
        $records = $this->getEnabledRecord($language);
        $pagesTree = $this->cleanPageTrees($this->getAllEntryPointPageTrees());

        return sprintf(
            LocalizationUtility::translate('systemPrompt', Configuration::Extension->value, null, $language),
            json_encode($records),
            json_encode($pagesTree)
        );
    }

    /**
     * clean pages tree
     *
     * @param array $pagesTree
     * @return array
     */
    private function cleanPageTrees(array $pagesTree): array
    {
        $cleanedPagesTree = [];
        foreach ($pagesTree as $page) {
            $cleanedPage = [
                'uid' => (int)$page['uid'],
                'title' => $page['title'],
            ];
            if (isset($page['_children']) && is_array($page['_children']) && !empty($page['_children'])) {
                $cleanedPage['_children'] = $this->cleanPageTrees($page['_children']);
            }
            $cleanedPagesTree[] = $cleanedPage;
        }
        return $cleanedPagesTree;
    }

    /**
     * Fetches all pages for all tree entry points the user is allowed to see
     *
     * @return array
     */
    private function getAllEntryPointPageTrees(): array
    {
        $permClause = Environment::isCli() ? '' : $this->backendUser->getPagePermsClause(Permission::PAGE_SHOW);

        $rootRecord = [
            'uid' => 0,
            'title' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] ?: 'TYPO3',
        ];
        $entryPointRecords = [];
        $entryPointIds = null;

        //watch out for deleted pages returned as webmount
        if (Environment::isCli()) {
            $mountPoints = [0];
        } else {
            $mountPoints = array_map('intval', $this->backendUser->returnWebmounts());
            $mountPoints = array_unique($mountPoints);
        }
        
        // Switch to multiple-entryPoint-mode if the rootPage is to be mounted.
        // (other mounts would appear duplicated in the pid = 0 tree otherwise)
        if (in_array(0, $mountPoints, true)) {
            $entryPointIds = $mountPoints;
        }

        if ($entryPointIds === null) {
            $rootRecord = $this->pageTreeRepository->getTreeLevels($rootRecord, 99, $mountPoints);

            $mountPointOrdering = array_flip($mountPoints);
            if (isset($rootRecord['_children'])) {
                usort($rootRecord['_children'], static function ($a, $b) use ($mountPointOrdering) {
                    return ($mountPointOrdering[$a['uid']] ?? 0) <=> ($mountPointOrdering[$b['uid']] ?? 0);
                });
            }

            $entryPointRecords[] = $rootRecord;
        } else {
            foreach ($entryPointIds as $k => $entryPointId) {
                if ($entryPointId === 0) {
                    $entryPointRecord = $rootRecord;
                } else {
                    $entryPointRecord = BackendUtility::getRecordWSOL(
                        'pages',
                        $entryPointId,
                        'uid, title',
                        $permClause
                    );

                    if ($entryPointRecord !== null && !$this->backendUser->isInWebMount($entryPointId)) {
                        $entryPointRecord = null;
                    }
                    if ($entryPointRecord === null) {
                        continue;
                    }
                }

                $entryPointRecord['uid'] = (int)$entryPointRecord['uid'];
                $entryPointRecord = $this->pageTreeRepository->getTree($entryPointRecord['uid'], null, $entryPointIds);

                if (is_array($entryPointRecord) && !empty($entryPointRecord)) {
                    $entryPointRecords[$k] = $entryPointRecord;
                }
            }
        }

        return $entryPointRecords;
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
