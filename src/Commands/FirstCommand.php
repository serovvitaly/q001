<?php

namespace Src\Commands;

use Src\Dto\TicketDto;
use Src\Order\OrderCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FirstCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('first')
            ->setDescription('')
            ->setHelp('')
            ->addArgument('filename', InputArgument::REQUIRED, 'Файл котировок')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rows = explode("\n", file_get_contents($input->getArgument('filename')));
        unset($rows[0]);

        $byDates = [];

        foreach ($rows as $row) {
            if (empty($row)) {
                continue;
            }
            // <DATE>;<TIME>;<LAST>;<VOL>;<ID>;<OPER>
            // 20180302;095958;139.650000000;200;2803712348;B
            $fields = explode(';', $row);

            if (!isset($byDates[$fields[0]])) {
                $byDates[$fields[0]] = [];
            }
            $byDates[$fields[0]][] = new TicketDto($fields[1], $fields[2], $fields[3]);
        }

        foreach ($byDates as $date => $items) {
            $this->calculate($date, $items, new OrderCollection);
        }

        //print_r($byDates['20180302'][0]);

        $output->writeln('');
    }

    public function calculate($date, $items, OrderCollection $orderCollection)
    {
        /** @var TicketDto $ticketDto */
        foreach ($items as $ticketDto) {
            $orderCollection->doIt($ticketDto->last);
        }

        $orderCollection->stats();

        //print_r(array_keys($orderCollection->shortStopLossIndex));
    }
}
