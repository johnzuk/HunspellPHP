<?php
namespace HunspellPHP;

use HunspellPHP\Exception\InvalidMatchTypeException;
use HunspellPHP\Exception\InvalidResultException;
use HunspellPHP\Exception\WordNotFoundException;

class Hunspell
{
    const OK = '*';

    const ROOT = '+';

    const MISS = '&';

    const NONE = '#';

    const STATUSES_NAME = [
        Hunspell::OK => 'OK',
        Hunspell::ROOT => 'ROOT',
        Hunspell::MISS => 'MISS',
        Hunspell::NONE => 'NONE',
    ];

    /**
     * @var string
     */
    protected $language = "pl_PL";

    /**
     * @var string
     */
    protected $encoding = "pl_PL.utf-8";

    /**
     * @var string
     */
    protected $matcher =
        "/(?P<type>\*|\+|&|#)\s?(?P<original>\w+)?(\s(?P<count>\d+)\s(?P<offset>\d+):\s(?P<misses>.*+))?/u";

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $this->clear($language);
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @param string $encoding
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $this->clear($encoding);
    }

    /**
     * @param string $word word to find
     * @return HunspellResponse
     * @throws InvalidMatchTypeException
     * @throws InvalidResultException
     * @throws WordNotFoundException
     */
    public function find($word)
    {
        $matches = [];
        $result = $this->preParse($this->command($word));

        $match = preg_match($this->matcher, $result, $matches);

        if (!$match) {
            throw new InvalidResultException(sprintf("Invalid hunspell result."));
        }
        $matches['input'] = $word;

        return $this->parse($matches);
    }

    /**
     * @param string $input
     * @return mixed
     */
    protected function clear($input)
    {
        return preg_replace('[^a-zA-Z0-9_\-.]', '', $input);
    }

    /**
     * @return string
     * @param string $input
     */
    protected function command($input)
    {
        return shell_exec(sprintf("LANG=%s; echo '%s' | hunspell -d %s", $this->encoding, $input, $this->language));
    }

    /**
     * @param string $input
     * @return string
     */
    protected function preParse($input)
    {
        $result = explode(PHP_EOL, $input);

        return isset($result[1]) ? $result[1] : $result[0];
    }

    /**
     * @param array $matches
     * @return HunspellResponse
     * @throws InvalidMatchTypeException
     * @throws WordNotFoundException
     */
    protected function parse(array $matches)
    {
        if ($matches['type'] == Hunspell::OK) {
            return new HunspellResponse(
                $matches['input'],
                $matches['input'],
                Hunspell::STATUSES_NAME[$matches['type']]
            );
        } else if ($matches['type'] == Hunspell::ROOT) {
            return new HunspellResponse(
                $matches['original'],
                $matches['input'],
                Hunspell::STATUSES_NAME[$matches['type']]
            );
        } else if ($matches['type'] == Hunspell::MISS) {
            return new HunspellResponse(
                '',
                $matches['original'],
                Hunspell::STATUSES_NAME[$matches['type']],
                $matches['offset'],
                explode(", ", $matches['misses'])
            );
        } else if ($matches['type'] == Hunspell::NONE) {
            throw new WordNotFoundException(sprintf("Word %s not found", $matches['input']));
        }

        throw new InvalidMatchTypeException(sprintf("Match type %s is invalid", $matches['type']));
    }

}