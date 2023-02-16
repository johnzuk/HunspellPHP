<?php
namespace HunspellPHP;

class HunspellStemResponse
{
    public string $original;
    /** @var string[] */
    public array $stems;

    /**
     * HunspellStemResponse constructor.
     * @param string $original
     * @param string[] $stems
     */
    public function __construct(string $original, array $stems = [])
    {
        $this->original = $original;
        $this->stems = $stems;
    }
}
