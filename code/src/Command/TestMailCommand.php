<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'app:mail:test')]
class TestMailCommand extends Command
{
    public function __construct(private MailerInterface $mailer) { parent::__construct(); }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->mailer->send((new Email())
            ->from('kane@humblewizards.com')
            ->to('kane.g.anderson@gmail.com')
            ->subject('SMTP test from console')
            ->text('Hello from a humble wizard!'));
        $output->writeln('Sent');
        return Command::SUCCESS;
    }
}
