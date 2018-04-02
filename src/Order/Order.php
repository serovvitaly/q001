<?php

namespace Src\Order;


class Order
{
    const TYPE_LONG = 'long';
    const TYPE_SHORT = 'short';

    protected $price;
    public $stopLoss;
    public $takeProfit;
    protected $type;

    protected $profit = null;
    public $isOpened = true;

    public function __construct($price, $stopLoss, $takeProfit, $type)
    {
        $this->price = $price;
        $this->stopLoss = $stopLoss;
        $this->takeProfit = $takeProfit;
        $this->type = $type;
    }

    public function close($price)
    {
        if (!$this->isOpened) {
            return false;
        }

        switch ($this->type) {
            case self::TYPE_LONG:
                $this->profit = $price - $this->price;
                break;
            case self::TYPE_SHORT:
                $this->profit = $this->price - $price;
                break;
            default:
                return false;
        }

        $this->isOpened = false;

        return true;
    }

    public function profit()
    {
        return $this->profit;
    }
}
