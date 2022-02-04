<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteAndRecreateDatabaseWithStructureAndDataCommand extends Command
{
    protected static $defaultName = 'app:clean-db';
    protected static $defaultDescription = 'Add a short description for your command';

    protected function configure(): void
    {
        $this
            ->setDescription('Supprime et recrée la base de données avec sa structure et ses jeux de fausses données')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section("Suppression de la base de données puis création d'une nouvelle avec structure et données pré-remplies");

        $this->runSymfonyCommand($input, $output, 'doctrine:database:drop', true);

        $this->runSymfonyCommand($input, $output, 'doctrine:database:create');

        // $this->runSymfonyCommand($input, $output, 'make:migration');

        $this->runSymfonyCommand($input, $output, 'doctrine:migrations:migrate');

        $this->runSymfonyCommand($input, $output, 'doctrine:fixtures:load');

        $io->success('RAS => Base de données toute propre avec ses data.');

        return Command::SUCCESS;
    }

    private function runSymfonyCommand(
        InputInterface $input,
        OutputInterface $output,
        string $command,
        bool $forceOption = false
    ): void
    {
        $application = $this->getApplication();

        if(!$application) {
            throw new \LogicException(("No application"));
        }

        $command = $application->find($command);

        if ($forceOption) {
            $input = new ArrayInput([
                '--force' => true
            ]);
        }

        $input->setInteractive(false);

        $command->run($input, $output);
    }
}
