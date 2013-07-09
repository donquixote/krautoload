<?php

namespace Namespaced;

class WithComments
{
    /** @Boolean */
    public static $loaded = true;
}

$string = 'string shoult not be   modified {$string}';

$heredoc = (<<<HD


Heredoc should not be   modified {$string}


HD
);

$nowdoc = <<<'ND'


Nowdoc should not be   modified {$string}


ND;
