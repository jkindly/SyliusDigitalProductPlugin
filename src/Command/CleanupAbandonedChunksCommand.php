<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Command;

use DateTimeImmutable;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'sylius:digital-product:cleanup-chunks',
    description: 'Cleanup abandoned chunked upload files older than specified hours',
)]
final class CleanupAbandonedChunksCommand extends Command
{
    public function __construct(
        private readonly FilesystemOperator $chunksStorage,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('hours', null, InputOption::VALUE_REQUIRED, 'Delete chunks older than X hours', '24')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $hours = (int) $input->getOption('hours');

        if (1 > $hours) {
            $io->error('Hours must be greater than 0');

            return Command::FAILURE;
        }

        $cutoffTime = (new DateTimeImmutable())->modify(sprintf('-%d hours', $hours));
        $io->info(sprintf('Cleaning up chunks older than %s', $cutoffTime->format('Y-m-d H:i:s')));

        $deletedCount = 0;
        $deletedSize = 0;

        $directories = $this->chunksStorage->listContents('/', false)
            ->filter(fn (StorageAttributes $attributes) => $attributes->isDir())
        ;

        foreach ($directories as $directory) {
            $dirPath = $directory->path();
            $shouldDelete = true;
            $dirSize = 0;

            $files = $this->chunksStorage->listContents($dirPath, false);

            foreach ($files as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $lastModified = $file->lastModified();
                if (null === $lastModified) {
                    continue;
                }

                $fileTime = (new DateTimeImmutable())->setTimestamp($lastModified);

                if ($fileTime > $cutoffTime) {
                    $shouldDelete = false;

                    break;
                }

                $dirSize += $file->fileSize() ?? 0;
            }

            if ($shouldDelete && $this->chunksStorage->directoryExists($dirPath)) {
                $this->chunksStorage->deleteDirectory($dirPath);
                ++$deletedCount;
                $deletedSize += $dirSize;
                $io->writeln(sprintf('Deleted: %s (%s)', $dirPath, $this->formatBytes($dirSize)));
            }
        }

        $io->success(sprintf(
            'Cleanup completed. Deleted %d chunk directories (%s total)',
            $deletedCount,
            $this->formatBytes($deletedSize),
        ));

        return Command::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1024 ** $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
