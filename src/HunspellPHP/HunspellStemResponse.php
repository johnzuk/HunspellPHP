<?php
namespace HunspellPHP;

class HunspellStemResponse
{
    /**
     * @var string
     */
    public $original;

    /**
     * @var string[]
     */
    public $stems;

    /**
     * HunspellStemResponse constructor.
     * @param string $original
     * @param string[] $stems
     */
    public function __construct($original, $stems = [])
    {
        $this->original = $original;
        $this->stems = $stems;
    }
}
