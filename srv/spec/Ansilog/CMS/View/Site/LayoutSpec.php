<?php

namespace spec\Ansilog\CMS\View\Site;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LayoutSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Ansilog\CMS\View\Site\Layout');
    }
}
