<?php

declare(strict_types=1);

namespace Ameos\Chatbot\Command;

use Ameos\Chatbot\Service\ChatbotService;
use Ameos\Chatbot\Service\Prompt\BackendPromptService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class QuestionCommand extends Command
{
    private const OPTION_LANGUAGE = 'language';

    /**
     * @param ChatbotService $chatbotService
     * @param BackendPromptService $backendPromptService
     */
    public function __construct(
        private readonly ChatbotService $chatbotService,
        private readonly BackendPromptService $backendPromptService
    ) {
        parent::__construct();
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    public function configure()
    {
        $this
            ->setDescription('Ask question to the chatbot.')
            ->setHelp('Call it like this: vendor/bin/typo3 chatbot:question')
            ->addOption(self::OPTION_LANGUAGE, 'l', InputOption::VALUE_OPTIONAL, 'Language', 'en');
    }

    /**
     * Execute scheduler tasks
     *
     * @param InputInterface  $input  InputInterfaceObject
     * @param OutputInterface $output OutputInterfaceObject
     *
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /** @var QuestionHelper */
        $helper = $this->getHelper('question');
        $question = new Question(
            LocalizationUtility::translate(
                'needHelp',
                'chatbot',
                null,
                $input->getOption(self::OPTION_LANGUAGE)
            ) . LF
        );
        $message = $helper->ask($input, $output, $question);

        $io->title(LocalizationUtility::translate('answer', 'chatbot'));

        $answer = $this->chatbotService->request(
            $message,
            $this->backendPromptService->getPrompt($input->getOption(self::OPTION_LANGUAGE))
        );

        $io->text($answer);

        return self::SUCCESS;
    }
}
