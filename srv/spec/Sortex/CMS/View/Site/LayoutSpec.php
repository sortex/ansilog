<?php

namespace spec\Sortex\CMS\View\Site;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LayoutSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Sortex\CMS\View\Site\Layout');
    }
}
