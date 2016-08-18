<?php
namespace HunspellPHP;

class HunspellResponse
{
    /**
     * @var string
     */
    public $root;

    /**
     * @var string
     */
    public $original;

    /**
     * @var int
     */
    public $offset;

    /**
     * @var array
     */
    public $misses = [];

    /**
     * @var string
     */
    public $code;

    /**
     * HunspellResponse constructor.
     * @param string $root
     * @param string $original
     * @param int $offset
     * @param array $misses
     * @param string $code
     */
    public function __construct($root, $original, $code = '', $offset = null, array $misses = [])
    {
        $this->root = $root;
        $this->original = $original;
        $this->offset = $offset;
        $this->misses = $misses;
        $this->code = $code;
    }
}