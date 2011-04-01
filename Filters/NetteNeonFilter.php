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
require_once __DIR__ . '/../Nette/nette.min.php';

/**
 * Filter to parse nette neon files
 * @author Eduard Kracmar
 * @copyright  Copyright (c) 2011 Eduard Kracmar
 */
class NetteNeonFilter extends AFilter implements iFilter {

    /**
     * Includes a prefix to match in { }
     * Alias for AFilter::addFunction
     *
     * @param $prefix string
     * @param $singular int
     * @param $plural int|null
     * @param $context int|null
     * @return NetteNeonFilter
     */
    public function addPrefix($prefix, $singular = 1, $plural = null, $context = null) {
        $this->keys[$prefix] = $singular;
        return $this;
    }

    /**
     * Excludes a prefix from { }
     * Alias for AFilter::removeFunction
     *
     * @param string $prefix
     * @return NetteNeonFilter
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

        $contents = file_get_contents($file);
        $neon = \Nette\Neon::decode($contents);
        foreach ($this->keys as $prefix => $singular) {
            /*
             * singular == 0 means message is key
             * singular == 1 means message is value
             */
            if (isset($neon[$prefix]) and is_array($neon[$prefix])) {
                foreach ($neon[$prefix] as $key => $value) {
                    switch ($singular) {
                        case 0:
                            $message = $key;
                            break;
                        case 1:
                            $message = $value;
                            break;
                        default:
                            throw new \Exception('Invalid singular definition.');
                            break;
                    }

                    $result = array(
                        iFilter::LINE => $this->findLine($file, $message),
                        iFilter::SINGULAR => $this->stripQuotes($this->fixEscaping($message))
                    );

                    $data[] = $result;
                }
            }
        }

        return $data;
    }

    /**
     * Searches for line of file, where message is located
     * @param string $file
     * @param string $message
     * @return integer
     */
    protected function findLine($file, $message) {
        $regex = sprintf("#%s#", $message);
        foreach (file($file) as $line => $contents) {
            if (preg_match($regex, $contents)) {
                return $line + 1;
            }
        }
        return 1;
    }

}
