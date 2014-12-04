<?php

namespace spec\Ansilog\CMS\View\Site;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PageSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Ansilog\CMS\View\Site\Page');
    }
}
