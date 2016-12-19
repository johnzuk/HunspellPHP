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
        "/(?P<type>\*|\+|&|#)\s?(?P<original>\w+)?\s?(?P<count>\d+)?\s?(?P<offset>\d+)?:?\s?(?P<misses>.*+)?/u";

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
     * @param $words
     * @return array
     * @throws InvalidMatchTypeException
     */
    public function find($words)
    {
        $matches = [];
        $results = $this->preParse($this->findCommand($words), $words);

        $response = [];
        foreach ($results as $word => $result) {
            $matches = [];
            $match = preg_match($this->matcher, $result, $matches);

            $matches['input'] = $word;
            $response[] = $this->parse($matches);
        }

        return $response;
    }

    /**
     * @param string $word word to find
     * @return HunspellStemResponse
     * @throws InvalidMatchTypeException
     * @throws InvalidResultException
     * @throws WordNotFoundException
     */
    public function stem($word)
    {
        $result = explode(PHP_EOL, $this->stemCommand($word));
        $result['input'] = $word;
        $result = $this->stemParse($result);
        return $result;
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
    protected function findCommand($input)
    {
        return shell_exec(sprintf("LANG=%s; echo '%s' | hunspell -d %s", $this->encoding, $input, $this->language));
    }

    /**
     * @return string
     * @param string $input
     */
    protected function stemCommand($input)
    {
        return shell_exec(sprintf("LANG=%s; echo '%s' | hunspell -d %s -s", $this->encoding, $input, $this->language));
    }

    /**
     * @param string $input
     * @param string $words
     * @return array
     */
    protected function preParse($input, $words)
    {
        $result = explode(PHP_EOL, trim($input));
        unset($result[0]);
        $words = array_map('trim', explode(" ", $words));

        return array_combine($words, $result);
    }

    /**
     * @param array $matches
     * @return HunspellResponse
     * @throws InvalidMatchTypeException
     */
    protected function parse(array $matches)
    {
        if ($matches['type'] == Hunspell::OK) {
            return new HunspellResponse(
                $matches['input'],
                $matches['input'],
                $matches['type']
            );
        } else if ($matches['type'] == Hunspell::ROOT) {
            return new HunspellResponse(
                $matches['original'],
                $matches['input'],
                $matches['type']
            );
        } else if ($matches['type'] == Hunspell::MISS) {
            return new HunspellResponse(
                '',
                $matches['original'],
                $matches['type'],
                $matches['offset'],
                explode(", ", $matches['misses'])
            );
        } else if ($matches['type'] == Hunspell::NONE) {
            return new HunspellResponse(
                '',
                $matches['input'],
                $matches['type'],
                $matches['count']
            );
        }

        throw new InvalidMatchTypeException(sprintf("Match type %s is invalid", $matches['type']));
    }

    /**
     * @param array $matches
     * @return HunspellStemResponse
     * @throws InvalidMatchTypeException
     * @throws WordNotFoundException
     */
    protected function stemParse(array $matches)
    {
        $input = $matches['input'];
        unset($matches['input']);
        $stems = [];
        foreach ($matches as $match) {
            $stem = explode(' ', $match);
            if (isset($stem[1]) && !empty($stem[1])) {
                if (!in_array($stem[1], $stems)) {
                    $stems[] = $stem[1];
                }
            } elseif (isset($stem[0]) && !empty($stem[0])) {
                if (!in_array($stem[0], $stems)) {
                    $stems[] = $stem[0];
                }
            }
        }
        return new HunspellStemResponse($input, $stems);
    }
}
