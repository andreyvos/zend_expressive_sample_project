<?php

namespace Options\View\Helper;

use Options\Model\OptionsTable;

class Options
{
    /** @var OptionsTable */
    private $optionsTable;

    public function __construct(OptionsTable $optionsTable)
    {
        $this->optionsTable = $optionsTable;
    }

    public function __invoke(): OptionsTable
    {
        return $this->optionsTable;
    }
}
