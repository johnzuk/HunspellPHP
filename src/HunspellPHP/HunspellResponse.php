<?php
namespace HunspellPHP;

class HunspellResponse
{
    public string $root;
    public string $original;
    public ?int $offset;
    public array $misses = [];
    public string $type;

    /**
     * HunspellResponse constructor.
     * @param string $root
     * @param string $original
     * @param ?int $offset
     * @param array $misses
     * @param string $type
     */
    public function __construct(string $root, string $original, string $type = '', ?int $offset = null, array $misses = [])
    {
        $this->root = $root;
        $this->original = $original;
        $this->offset = $offset;
        $this->misses = $misses;
        $this->type = $type;
    }
}