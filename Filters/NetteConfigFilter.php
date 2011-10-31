<?php

/**
 * GettextExtractor
 * 
 * Cool tool for automatic extracting gettext strings for translation
 *
 * Works best with Nette Framework
 * 
 * This source file is subject to the New BSD License.
 *
 * @copyright  Copyright (c) 2009 Karel Klíma
 * @license    New BSD License
 * @package    Nette Extras
 */
require_once dirname(__FILE__) . '/iFilter.php';
require_once dirname(__FILE__) . '/AFilter.php';

/**
 * Filter to parse nette ini config files in Nette Framework templates
 * @author Karel Klíma
 * @copyright  Copyright (c) 2009 Karel Klíma
 */
class NetteConfigFilter extends AFilter implements iFilter {

    protected $keys;

    /**
     * Includes a prefix to match in { }
     * Alias for AFilter::addFunction
     *
     * @param $prefix string
     * @param $singular int
     * @param $plural int|null
     * @param $context int|null
     * @return NetteConfigFilter
     */
    public function addPrefix($prefix, $singular = 1, $plural = null, $context = null) {
        $this->keys[$prefix] = $prefix;
        return $this;
    }

    /**
     * Excludes a prefix from { }
     * Alias for AFilter::removeFunction
     *
     * @param string $prefix
     * @return NetteConfigFilter
     */
    public function removePrefix($prefix) {
        unset($this->keys[$prefix]);
        return $this;
    }

    /**
     * Parses given file and returns found gettext phrases
     *
     * @param string $file
     * @return array
     */
    public function extract($file) {
        if (count($this->keys) === 0)
            return;
        $data = array();
        $regex = $this->createRegex();

        // parse file by lines
        foreach (file($file) as $line => $contents) {
            $matches = array();
            preg_match_all($regex, $contents, $matches, PREG_SET_ORDER);

            foreach ($matches as $message) {
                /* $message[0] = key
                 * $message[1] = 1. parameter
                 */

                $result = array(
                    iFilter::LINE => $line + 1,
                    iFilter::SINGULAR => $this->stripQuotes($this->fixEscaping($message[2]))
                );

                $data[] = $result;
            }
        }
        return $data;
    }

    /**
     * Creates regular expression
     * @return string
     */
    protected function createRegex() {
        $keys = array();
        foreach ($this->keys as $key) {
            $keys[] = $this->fixKeyRegexEscaping($key);
        }

        $pattern = "#(%s)\s*=\s*(?:\"|')?([^\s\"']+)(?:\"|')?#i";
        return sprintf($pattern, (count($keys) > 1 ? '(' . implode('|', $keys) . ')' : $keys[0]));
    }

    /**
     * Fixes escaping of regular expression
     * @param string $value
     * @return string
     */
    protected function fixKeyRegexEscaping($value) {
        $search = array(
            '.',
            '[',
            ']',
        );
        $replace = array(
            '\.',
            '\[',
            '\]'
        );
        return str_replace($search, $replace, $value);
    }

}
