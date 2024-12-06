<?php

declare(strict_types=1);

namespace App\Admin\UserInterface\Cli;

use App\Admin\Application\Command\InitCommonDbCmd;
use Doctrine\DBAL\Exception as DBALException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'admin:init-common',
    description: 'Load initial content for common database.',
)]
class InitCommonCommand extends Command
{
    private InitCommonDbCmd $service;

    public function __construct(InitCommonDbCmd $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * @throws DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->service->initCommonDb();
        $io->success('Database populated with initial data.');

        return Command::SUCCESS;
    }
}
