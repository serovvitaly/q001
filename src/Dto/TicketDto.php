<?php

namespace Src\Dto;

class TicketDto
{
    public $time;
    public $last;
    public $vol;

    public function __construct($time, $last, $vol)
    {
        $this->time = $time;
        $this->last = (float)$last;
        $this->vol = (int)$vol;
    }
}
