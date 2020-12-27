<?php

declare(strict_types=1);

namespace Jorijn\Bitcoin\Dca\Command;

use Jorijn\Bitcoin\Dca\Service\WithdrawService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class WithdrawCommand extends Command
{
    protected WithdrawService $withdrawService;

    public function __construct(WithdrawService $withdrawService)
    {
        parent::__construct(null);

        $this->withdrawService = $withdrawService;
    }

    public function configure(): void
    {
        $this
            ->addArgument('asset', InputArgument::REQUIRED, 'The asset to be withdrawn')
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'If supplied, will withdraw all available assets to the configured address'
            )
            ->addOption(
                'yes',
                'y',
                InputOption::VALUE_NONE,
                'If supplied, will not confirm the withdraw go ahead immediately'
            )
            ->addOption(
                'tag',
                't',
                InputOption::VALUE_REQUIRED,
                'If supplied, will limit the withdrawal to the balance available for this tag'
            )
            ->setDescription('Withdraw asset from the exchange')
        ;
    }

    public function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('all')) {
            $io->error('Only allows withdraw for all funds right now, will be updated in the future. Supply --all to proceed.');

            return 1;
        }

        $assetToWithdraw = (string) $input->getArgument('asset');
        $assetToWithdraw = strtoupper($assetToWithdraw);

        $amountToWithdraw = $this->withdrawService->getBalance($assetToWithdraw, $input->getOption('tag'));
        $addressToWithdrawTo = $this->withdrawService->getRecipientAddress($assetToWithdraw);

        if (0 === $amountToWithdraw) {
            $io->error('No balance available, better start saving something!');

            return 0;
        }

        if (!$input->getOption('yes')) {
            $question = sprintf(
                'Ready to withdraw %s %s to address %s? A fee of %s %s will be taken as withdraw fee.',
                $amountToWithdraw,
                $assetToWithdraw,
                $addressToWithdrawTo,
                $this->withdrawService->getWithdrawFee($assetToWithdraw, $amountToWithdraw, $addressToWithdrawTo),
                $assetToWithdraw
            );

            if (!$io->confirm($question, false)) {
                return 0;
            }
        }

        $completedWithdraw = $this->withdrawService->withdraw(
            $assetToWithdraw,
            $amountToWithdraw,
            $addressToWithdrawTo,
            $input->getOption('tag')
        );

        $io->success('Withdraw is being processed as ID '.$completedWithdraw->getId());

        return 0;
    }
}
