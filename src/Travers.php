<?php

namespace Luur;

use Luur\Exceptions\BranchNotFoundException;

class Travers
{
    const DEFAULT_DELIMITER = '.';

    /**
     * @var array
     */
    protected $tree;

    /**
     * @var string
     */
    protected $delimiter;

    /**
     * @var bool
     */
    protected $shouldFail;

    /**
     * Travers constructor.
     * @param array $tree
     * @param bool $shouldFail
     * @param string $delimiter
     */
    public function __construct(array $tree = [], bool $shouldFail = false, string $delimiter = self::DEFAULT_DELIMITER)
    {
        $this->setTree($tree);
        $this->setShouldFail($shouldFail);
        $this->setDelimiter($delimiter);
    }

    /**
     * @param bool $shouldFail
     */
    public function setShouldFail(bool $shouldFail): void
    {
        $this->shouldFail = $shouldFail;
    }

    /**
     * @param string $path
     * @param array $tree
     * @return null
     */
    public static function get(string $path, array $tree)
    {
        return (new Travers($tree))->find($path);
    }

    /**
     * @param string $key
     * @return null
     */
    public function find(string $key)
    {
        return $this->parseParam($this->parseKeys($key), $this->tree);
    }

    /**
     * @param array $keys
     * @param $data
     * @return null
     * @throws BranchNotFoundException
     */
    protected function parseParam(array $keys, $data)
    {
        if (count($keys) < 1) {
            return $data;
        }

        $key = array_shift($keys);

        if (!is_array($data) || !array_key_exists($key, $data)) {
            return $this->handleFailure($key);
        }

        return $this->parseParam($keys, $data[$key]);
    }

    /**
     * @param string $key
     * @return null
     * @throws BranchNotFoundException
     */
    protected function handleFailure(string $key)
    {
        if ($this->shouldFail()) {
            throw new BranchNotFoundException($key);
        }

        return null;
    }

    /**
     * @return bool
     */
    public function shouldFail(): bool
    {
        return $this->shouldFail;
    }

    /**
     * @param string $key
     * @return array
     */
    protected function parseKeys(string $key): array
    {
        return explode($this->delimiter, $key);
    }

    /**
     * @param string $path
     * @param $value
     * @param array $tree
     * @return array
     */
    public static function set(string $path, $value, array $tree): array
    {
        return (new Travers($tree))->change($path, $value);
    }

    /**
     * @param string $key
     * @param $value
     * @return array
     */
    public function change(string $key, $value): array
    {
        $this->setTree($this->setParam($this->parseKeys($key), $value, $this->tree));
        return $this->getTree();
    }

    /**
     * @param array $keys
     * @param $value
     * @param $data
     * @return array
     */
    protected function setParam(array $keys, $value, $data)
    {
        if (count($keys) < 1) {
            return $data = $value;
        }

        $key = array_shift($keys);

        if (!is_array($data)) {
            $data = [];
        }

        if (!array_key_exists($key, $data)) {
            $data[$key] = [];
        }

        $data[$key] = $this->setParam($keys, $value, $data[$key]);

        return $data;
    }

    /**
     * @return array
     */
    public function getTree(): array
    {
        return $this->tree;
    }

    /**
     * @param array $tree
     */
    public function setTree(array $tree): void
    {
        $this->tree = $tree;
    }

    /**
     * @return string
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * @param string $delimiter
     */
    public function setDelimiter(string $delimiter): void
    {
        $this->delimiter = $delimiter;
    }
}