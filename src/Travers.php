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
    public function __construct(array $tree = [], $shouldFail = false, $delimiter = self::DEFAULT_DELIMITER)
    {
        $this->setTree($tree);
        $this->setShouldFail($shouldFail);
        $this->setDelimiter($delimiter);
    }

    /**
     * @param bool $shouldFail
     */
    public function setShouldFail($shouldFail)
    {
        $this->shouldFail = $shouldFail;
    }

    /**
     * @param string $path
     * @param array $tree
     * @return mixed|null
     * @throws BranchNotFoundException
     */
    public static function get($path, array $tree)
    {
        return (new Travers($tree))->find($path);
    }

    /**
     * @param string $path
     * @param array  $tree
     *
     * @return array
     * @throws BranchNotFoundException
     */
    public static function delete($path, array $tree)
    {
        return (new Travers($tree))->remove($path);
    }

    /**
     * @param string $path
     *
     * @return array
     * @throws BranchNotFoundException
     */
    public function remove($path)
    {
        $this->setTree($this->removeParam($path, $this->tree));
        return $this->getTree();
    }

    /**
     * @param string $key
     * @return mixed|null
     * @throws BranchNotFoundException
     */
    public function find($key)
    {
        return $this->parseParam($this->parseKeys($key), $this->tree);
    }

    /**
     * @param string $key
     * @param array  $tree
     *
     * @return array|mixed|null
     * @throws BranchNotFoundException
     */
    protected function removeParam($key, array $tree)
    {
        $tmp       = &$tree;
        $keysArray = $this->parseKeys($key);

        while (count($keysArray) > 1) {
            $key = array_shift($keysArray);

            if (!is_array($tmp) || !array_key_exists($key, $tmp)) {
                return $this->shouldFail() ? $this->handleFailure($key) : $tmp;
            }

            $tmp = &$tmp[$key];
        }

        $key = array_shift($keysArray);

        if (!is_string($key) || !is_array($tmp) || !array_key_exists($key, $tmp)) {
            return $this->shouldFail() ? $this->handleFailure($key) : $tree;
        }

        unset($tmp[$key]);
        return $tree;
    }

    /**
     * @param array $keys
     * @param $data
     * @return mixed|null
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
     * @return mixed|null
     * @throws BranchNotFoundException
     */
    protected function handleFailure($key)
    {
        if ($this->shouldFail()) {
            throw new BranchNotFoundException($key);
        }

        return null;
    }

    /**
     * @return bool
     */
    public function shouldFail()
    {
        return $this->shouldFail;
    }

    /**
     * @param string $key
     * @return array
     */
    protected function parseKeys($key)
    {
        return explode($this->delimiter, $key);
    }

    /**
     * @param string $path
     * @param $value
     * @param array $tree
     * @return array
     */
    public static function set($path, $value, array $tree)
    {
        return (new Travers($tree))->change($path, $value);
    }

    /**
     * @param string $key
     * @param $value
     * @return array
     */
    public function change($key, $value)
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
    public function getTree()
    {
        return $this->tree;
    }

    /**
     * @param array $tree
     */
    public function setTree(array $tree)
    {
        $this->tree = $tree;
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * @param string $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }
}
