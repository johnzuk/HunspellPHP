<?php
/** @noinspection PhpUnused */
namespace HunspellPHP;

class Hunspell
{
    const OK = '*';
    const ROOT = '+';
    const MISS = '&';
    const NONE = '#';
    const COMPOUND = '-';
    const STATUSES_NAME = [
        Hunspell::OK => 'OK',
        Hunspell::ROOT => 'ROOT',
        Hunspell::MISS => 'MISS',
        Hunspell::NONE => 'NONE',
        Hunspell::COMPOUND => 'COMPOUND'
    ];

    protected string $encoding;
    protected string $dictionary;
    protected string $dictionary_path;
    protected string $matcher =
        '/(?P<type>\*|\+|&|#|-)\s?(?P<original>\w+)?\s?(?P<count>\d+)?\s?(?P<offset>\d+)?:?\s?(?P<misses>.*+)?/u';

    /**
     * @param string $dictionary Dictionary name e.g.: 'en_US' (default)
     * @param string $encoding Encoding e.g.: 'utf-8' (default)
     * @param ?string $dictionary_path Specify the directory of the dictionary file (optional)
     */
    public function __construct(
        string $dictionary = 'en_US',
        string $encoding = 'en_US.utf-8',
        ?string $dictionary_path = null
    ) {
        $this->dictionary = $this->clear($dictionary);
        $this->encoding = $this->clear($encoding);
        $this->dictionary_path = $dictionary_path;
    }


    /**
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * @return string
     */
    public function getDictionary(): string
    {
        return $this->dictionary;
    }

    /**
     * @return string
     */
    public function getDictionaryPath(): string
    {
        return $this->dictionary_path;
    }

    /**
     * @param string $dictionary Language code e.g.: 'en_US'
     */
    public function setDictionary(string $dictionary): void
    {
        $this->dictionary = $this->clear($dictionary);
    }

    /**
     * @param string $dictionary_path The path to load the dictionary files from
     */
    public function setDictionaryPath(string $dictionary_path): void
    {
        $this->dictionary_path = $dictionary_path;
    }


    /**
     * @param string $encoding Encoding value (includes language code) e.g.: 'en_US.utf-8'
     */
    public function setEncoding(string $encoding): void
    {
        $this->encoding = $this->clear($encoding);
    }

    /**
     * @param string $words
     * @return array
     * @throws InvalidMatchTypeException
     */
    public function find(string $words): array
    {
        $results = $this->preParse($this->findCommand($words), $words);

        $response = [];
        foreach ($results as $word => $result) {
            $matches = ['type' => null];
            preg_match($this->matcher, $result, $matches);
            $matches['input'] = $word;
            $matches['type'] = $matches['type'] ?? null;
            $matches['original'] = $matches['original'] ?? '';
            $matches['misses'] = $matches['misses'] ?? [];
            $matches['offset'] = $matches['offset'] ?? null;
            $matches['count'] = $matches['count'] ?? null;
            $response[] = $this->parse($matches);
        }
        return $response;
    }

    /**
     * @param string $word word to find
     * @return HunspellStemResponse
     */
    public function stem(string $word): HunspellStemResponse
    {
        $result = explode(PHP_EOL, $this->findCommand($word, true));
        $result['input'] = $word;
        return $this->stemParse($result);
    }

    /**
     * @param string $input
     * @return string
     */
    protected function clear(string $input): string
    {
        return (string)preg_replace('[^a-zA-Z0-9_-\.]', '', $input);
    }

    /**
     * @param string $input
     * @param bool $stem_mode
     * @return string
     */
    protected function findCommand(string $input, bool $stem_mode = false): string
    {
        $stem_switch = $stem_mode ? ' -s' : '';
        $dictionary = $this->dictionary_path
            ? rtrim($this->dictionary_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->dictionary
            : $this->dictionary;
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return (string)shell_exec(sprintf("powershell \"set LANG='%s'; echo '%s' | hunspell -d %s%s\"", $this->encoding, $input, $dictionary, $stem_switch));
		} else {
			return (string)shell_exec(sprintf("export LANG='%s'; echo '%s' | hunspell -d %s%s", $this->encoding, $input, $dictionary, $stem_switch));
		}
    }

    /**
     * @param string $input
     * @param string $words
     * @return array
     */
    protected function preParse(string $input, string $words): array
    {
        $result = explode("\n", trim($input));
        array_shift($result);
        $words = array_map('trim', preg_split('/\W/', $words));

        if(sizeof($result) != sizeof($words)) {
        	return [];
		}
        return array_combine($words, $result);
    }

    /**
     * @param array $matches
     * @return HunspellResponse
     * @throws InvalidMatchTypeException
     */
    protected function parse(array $matches): HunspellResponse
    {
        if ($matches['type'] == Hunspell::OK || $matches['type'] == Hunspell::COMPOUND) {
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
     */
    protected function stemParse(array $matches): HunspellStemResponse
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
