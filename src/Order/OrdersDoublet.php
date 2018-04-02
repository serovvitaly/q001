<?php

namespace Src\Order;


class OrdersDoublet
{
    public $longOrder;
    public $shortOrder;

    public function __construct(Order $longOrder, Order $shortOrder)
    {
        $this->longOrder = $longOrder;
        $this->shortOrder = $shortOrder;
    }

    public function longStopLoss($price)
    {
        $this->longOrder->close($price);
    }

    public function longTakeProfit($price)
    {
        $this->longOrder->close($price);
    }

    public function shortStopLoss($price)
    {
        $this->shortOrder->close($price);
    }

    public function shortTakeProfit($price)
    {
        $this->shortOrder->close($price);
    }

    public function isClosed()
    {
        return !$this->longOrder->isOpened && !$this->shortOrder->isOpened;
    }

    public function profit()
    {
        //echo $this->longOrder->profit(), ' :: ', $this->shortOrder->profit(), PHP_EOL;

        return $this->longOrder->profit() + $this->shortOrder->profit();
    }
}
