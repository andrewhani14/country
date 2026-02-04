<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\CountrySyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'countries:sync',
    description: 'Synchronize countries from REST Countries API. Adds new, updates existing (resets to API data), removes countries no longer in the API.',
)]
class CountrySyncCommand extends Command
{
    public function __construct(
        private readonly CountrySyncService $countrySyncService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Syncing countries from REST Countries API');

        try {
            $this->countrySyncService->sync();
            $io->success('Countries synced successfully.');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('Sync failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
