<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Class ImportRemoteDatabaseCommand
 *
 * @package App\Command
 */
class ImportRemoteDatabaseCommand extends Command
{

    /**
     * @var string
     */
    private $env;

    /**
     * @var string
     */
    private $siteBaseUrl;

    public function __construct($env, $siteBaseUrl)
    {
        $this->env         = $env;
        $this->siteBaseUrl = $siteBaseUrl;

        parent::__construct();
    }

    protected static $defaultName = 'app:database:import';

    protected function configure()
    {
        $this->setDescription('Imports database from staging or production to local.');

        $this->addArgument('server', InputArgument::REQUIRED, 'Database you want to import. Possible options are "staging", "prod"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $server = $input->getArgument('server');

        $this
            ->validate($input, $output, $server)
            ->enablePasswordlessLogin($input, $output, $server)
            ->exportDatabase($input, $output, $server)
            ->downloadDatabase($input, $output, $server)
            ->dropDatabase($input, $output, $server)
            ->createDatabase($input, $output, $server)
            ->importDatabase($input, $output, $server);

        $output->writeln("Finished database import");
    }

    private function validate(InputInterface $input, OutputInterface $output, $server)
    {

        $validServers = ["staging", "prod"];

        if (!in_array($server, $validServers)) {
            throw new \Exception(sprintf("That is not a valid server option. Valid options are (%s)", implode(",", $validServers)));
        }


        // DO NOT EVER RUN THIS ON PRODUCTION
        if ($this->siteBaseUrl !== 'http://localhost:8000') {
            throw new \Exception("This command cannot be run on production and only from your local machine");
        }

        return $this;
    }

    private function enablePasswordlessLogin(InputInterface $input, OutputInterface $output, $server)
    {

        if ($server === 'staging') {
            $process = Process::fromShellCommandline('ssh-copy-id forge@174.138.34.227 -f');
        } elseif ($server === 'prod') {
            $process = Process::fromShellCommandline('ssh-copy-id forge@174.138.34.164 -f');
        }

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();

        return $this;
    }

    private function exportDatabase(InputInterface $input, OutputInterface $output, $server)
    {

        $output->writeln("Dumping database on remote server...");


        if ($server === 'staging') {
            $process = Process::fromShellCommandline('ssh forge@174.138.34.227 "cd ~/backup && mysqldump -upintex -p\'8TH+752[T)F&C(QZ\' pintex > staging_data.sql"');
        } elseif ($server === 'prod') {
            $process = Process::fromShellCommandline('ssh forge@174.138.34.164 "cd ~/backup && mysqldump -upintex -p\'8TH+752[T)F&C(QZ\' pintex > prod_data.sql"');
        }

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();

        $output->writeln("Dump finished...");

        return $this;
    }


    private function downloadDatabase(InputInterface $input, OutputInterface $output, $server)
    {

        $output->writeln("Downloading database to your local...");

        if ($server === 'staging') {
            $process = new Process(['scp', 'forge@174.138.34.227:~/backup/staging_data.sql', '.']);
        } elseif ($server === 'prod') {
            $process = new Process(['scp', 'forge@174.138.34.164:~/backup/prod_data.sql', '.']);
        }

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();

        $output->writeln("Downloading finished...");

        return $this;
    }

    private function dropDatabase(InputInterface $input, OutputInterface $output, $server)
    {

        $output->writeln("Dropping local database...");

        $process = Process::fromShellCommandline('bin/console doctrine:database:drop --force --if-exists');

        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();

        $output->writeln("Database drop finished...");

        return $this;

    }

    private function createDatabase(InputInterface $input, OutputInterface $output, $server)
    {

        $output->writeln("Creating local database...");

        $process = Process::fromShellCommandline('bin/console doctrine:database:create');

        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();

        $output->writeln("Creating finished...");

        return $this;

    }

    private function importDatabase(InputInterface $input, OutputInterface $output, $server)
    {

        $output->writeln("Importing downloaded database into your local database...");

        if ($server === 'staging') {
            $process = Process::fromShellCommandline('mysql -u pintex -ppintex -h localhost pintex < staging_data.sql');
        } elseif ($server === 'prod') {
            $process = Process::fromShellCommandline('mysql -u pintex -ppintex -h localhost pintex < prod_data.sql');
        }

        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();

        $output->writeln("Import finished...");

        return $this;

    }
}