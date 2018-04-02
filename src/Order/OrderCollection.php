<?php

namespace Src\Order;

class OrderCollection
{
    const MULTIPLIER = 100;

    protected $indexKey = 1;
    /** @var Order[] */
    protected $orders = [];

    /** @var OrdersDoublet[]  */
    protected $ordersDoublets = [];

    public $longStopLossIndex = [];
    public $longTakeProfitIndex = [];

    public $shortStopLossIndex = [];
    public $shortTakeProfitIndex = [];

    public function doIt($price)
    {
        $price = (int)($price * self::MULTIPLIER);

        $this->closeLongByStopLoss($price);
        $this->closeLongByTakeProfit($price);

        $this->closeShortByStopLoss($price);
        $this->closeShortByTakeProfit($price);

        $this->indexedOrdersDoublet($this->makeOrdersDoublet($price));
    }

    public function makeOrdersDoublet($price)
    {
        return new OrdersDoublet(
            new Order($price, $price - 50 * self::MULTIPLIER, $price + 80 * self::MULTIPLIER, Order::TYPE_LONG),
            new Order($price, $price + 50 * self::MULTIPLIER, $price - 80 * self::MULTIPLIER, Order::TYPE_SHORT)
        );
    }

    public function indexedOrdersDoublet(OrdersDoublet $ordersDoublet)
    {
        $this->longStopLossIndex[$ordersDoublet->longOrder->stopLoss][] = $this->indexKey;
        $this->longTakeProfitIndex[$ordersDoublet->longOrder->takeProfit][] = $this->indexKey;
        $this->shortStopLossIndex[$ordersDoublet->shortOrder->stopLoss][] = $this->indexKey;
        $this->shortTakeProfitIndex[$ordersDoublet->shortOrder->takeProfit][] = $this->indexKey;

        $this->ordersDoublets[$this->indexKey] = $ordersDoublet;

        $this->indexKey++;
    }

    /**
     * @param $price
     */
    public function closeLongByStopLoss($price)
    {
        foreach ($this->longStopLossIndex as $stopLossPrice => $ordersIndexes) {
            if ($stopLossPrice > $price) {
                continue;
            }
            foreach ($ordersIndexes as $key => $orderIndex) {
                $this->ordersDoublets[$orderIndex]->longStopLoss($price);
                unset($this->longStopLossIndex[$stopLossPrice][$key]);
            }
        }
    }

    /**
     * @param $price
     */
    public function closeLongByTakeProfit($price)
    {
        foreach ($this->longTakeProfitIndex as $takeProfitPrice => $ordersIndexes) {
            if ($takeProfitPrice < $price) {
                continue;
            }
            foreach ($ordersIndexes as $key => $orderIndex) {
                $this->ordersDoublets[$orderIndex]->longTakeProfit($price);
                unset($this->longTakeProfitIndex[$takeProfitPrice][$key]);
            }
        }
    }

    /**
     * @param $price
     */
    public function closeShortByStopLoss($price)
    {
        foreach ($this->shortStopLossIndex as $stopLossPrice => $ordersIndexes) {
            if ($stopLossPrice < $price) {
                continue;
            }
            foreach ($ordersIndexes as $key => $orderIndex) {
                $this->ordersDoublets[$orderIndex]->shortStopLoss($price);
                unset($this->shortStopLossIndex[$stopLossPrice][$key]);
            }
        }
    }

    /**
     * @param $price
     */
    public function closeShortByTakeProfit($price)
    {
        foreach ($this->shortTakeProfitIndex as $takeProfitPrice => $ordersIndexes) {
            if ($takeProfitPrice > $price) {
                continue;
            }
            foreach ($ordersIndexes as $key => $orderIndex) {
                $this->ordersDoublets[$orderIndex]->shortTakeProfit($price);
                unset($this->shortTakeProfitIndex[$takeProfitPrice][$key]);
            }
        }
    }

    /**
     *
     */
    public function stats()
    {
        $longStopLossIndexCount = 0;
        foreach ($this->longStopLossIndex as $stopLossPrice => $ordersIndexes) {
            $longStopLossIndexCount += count($ordersIndexes);
        }
        $shortStopLossIndexCount = 0;
        foreach ($this->shortStopLossIndex as $stopLossPrice => $ordersIndexes) {
            $shortStopLossIndexCount += count($ordersIndexes);
        }
        $longTakeProfitIndexCount = 0;
        foreach ($this->longTakeProfitIndex as $takeProfitPrice => $ordersIndexes) {
            $longTakeProfitIndexCount += count($ordersIndexes);
        }
        $shortTakeProfitIndexCount = 0;
        foreach ($this->shortTakeProfitIndex as $takeProfitPrice => $ordersIndexes) {
            $shortTakeProfitIndexCount += count($ordersIndexes);
        }

        echo 'OrdersDoubletsCount = ', count($this->ordersDoublets), PHP_EOL;
        echo 'LongStopLossIndexCount = ', $longStopLossIndexCount, PHP_EOL;
        echo 'ShortStopLossIndexCount = ', $shortStopLossIndexCount, PHP_EOL;
        echo 'LongTakeProfitIndexCount = ', $longTakeProfitIndexCount, PHP_EOL;
        echo 'ShortTakeProfitIndexCount = ', $shortTakeProfitIndexCount, PHP_EOL;

        $profitOrdersCount = 0;
        $profitSum = 0;

        $lostOrdersCount = 0;
        $lostSum = 0;


        $closedDoublets = 0;
        $unclosedDoublets = 0;

        foreach ($this->ordersDoublets as $ordersDoublet) {

            if ($ordersDoublet->isClosed()) {
                $closedDoublets++;
            } else {
                $unclosedDoublets++;
            }

            if ($ordersDoublet->profit() > 0) {
                $profitOrdersCount++;
                $profitSum += $ordersDoublet->profit();
            } else {
                $lostOrdersCount++;
                $lostSum -= $ordersDoublet->profit();
            }
        }

        echo '', PHP_EOL;
        echo 'ClosedDoublets = ', $closedDoublets, PHP_EOL;
        echo 'UnclosedDoublets = ', $unclosedDoublets, PHP_EOL;
        echo '', PHP_EOL;

        $profitSum = $profitSum / self::MULTIPLIER;
        $lostSum = $lostSum / self::MULTIPLIER;

        echo 'ProfitOrdersCount = ', $profitOrdersCount, PHP_EOL;
        echo 'ProfitSum = ', $profitSum, PHP_EOL;
        echo 'LostOrdersCount = ', $lostOrdersCount, PHP_EOL;
        echo 'LostSum = ', $lostSum, PHP_EOL;

        echo 'Deposit = ', ($profitSum - $lostSum), PHP_EOL;
    }
}
