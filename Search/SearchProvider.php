<?php

namespace Chill\MainBundle\Search;

use Chill\MainBundle\Search\SearchInterface;

/**
 * a service which gather all search services defined into the bundles
 * installed into the app.
 * the service is callable from the container with
 * $container->get('chill.main.search_provider')
 */
class SearchProvider
{

    /**
     * 
     * @var SearchInterface[]
     */
    private $searchServices = array();

    /*
     * return search services in an array, ordered by
     * the order key (defined in service definition)
     * the conflicts in keys (twice the same order) are resolved
     * within the compiler : the function will preserve all services
     * defined (if two services have the same order, the will increment
     * the order of the second one.
     *
     * @return SearchInterface[], with an int as array key
     */

    public function getByOrder()
    {
        //sort the array
        uasort($this->searchServices, function(SearchInterface $a, SearchInterface $b) {
            if ($a->getOrder() == $b->getOrder()) {
                return 0;
            }
            return ($a->getOrder() < $b->getOrder()) ? -1 : 1;
        });

        return $this->searchServices;
    }

    /**
     * parse the search string to extract domain and terms
     * 
     * @param string $pattern
     * @return string[] an array where the keys are _domain, _default (residual terms) or term
     */
    public function parse($pattern)
    {
        //reset must be extracted
        $this->mustBeExtracted = array();
        //filter to lower and remove accentued
        $filteredPattern = mb_strtolower($this->remove_accents($pattern));

        $terms = $this->extractTerms($filteredPattern);
        $terms['_domain'] = $this->extractDomain($filteredPattern);
        $terms['_default'] = $this->extractDefault($filteredPattern);

        return $terms;
    }

    private function extractDomain(&$subject)
    {
        preg_match_all('/@([a-z]+)/', $subject, $terms);

        if (count($terms[0]) > 1) {
            throw new ParsingException('You should not have more than one domain.');
        }

        //add pattern to be extracted
        if (isset($terms[0][0])) {
            $this->mustBeExtracted[] = $terms[0][0];
        }

        return isset($terms[1][0]) ? $terms[1][0] : NULL;
    }

    private function extractTerms(&$subject)
    {
        $terms = array();
        preg_match_all('/([a-z\-]+):([\w\-]+|\([^\(\r\n]+\))/', $subject, $matches);

        foreach ($matches[2] as $key => $match) {
            //remove from search pattern
            $this->mustBeExtracted[] = $matches[0][$key];
            //strip parenthesis
            if (mb_substr($match, 0, 1) === '(' &&
                  mb_substr($match, mb_strlen($match) - 1) === ')') {
                $match = trim(mb_substr($match, 1, mb_strlen($match) - 2));
            }
            $terms[$matches[1][$key]] = $match;
        }

        return $terms;
    }

    /**
     * store string which must be extracted to find default arguments
     * 
     * @var string[]
     */
    private $mustBeExtracted = array();

    /**
     * extract default (residual) arguments
     * 
     * @param string $subject
     * @return string
     */
    private function extractDefault($subject)
    {
        return trim(str_replace($this->mustBeExtracted, '', $subject));
    }

    /**
     * search through services which supports domain and give
     * results as html string
     * 
     * @param string $pattern
     * @param number $start
     * @param number $limit
     * @return array of html results
     * @throws UnknowSearchDomainException if the domain is unknow
     */
    public function getSearchResults($pattern, $start = 0, $limit = 50)
    {
        $terms = $this->parse($pattern);
        $results = array();
        
        //sort searchServices by order
        $sortedSearchServices = array();
        foreach($this->searchServices as $service) {
            $sortedSearchServices[$service->getOrder()] = $service;
        }
        
        if ($terms['_domain'] !== NULL) {
            foreach ($sortedSearchServices as $service) {
                if ($service->supports($terms['_domain'])) {
                    $results[] = $service->renderResult($terms, $start, $limit);
                }
            }
            
            if (count($results) === 0) {
                throw new UnknowSearchDomainException($terms['_domain']);
            }
        } else { // no domain provided, we use default search
            foreach($sortedSearchServices as $service) {
                if ($service->isActiveByDefault()) {
                    $results[] = $service->renderResult($terms, $start, $limit);
                }
            }
        }
        
        //sort array
        ksort($results);

        return $results;
    }
    
    public function getResultByName($pattern, $name, $start = 0, $limit = 50) 
    {
        $terms = $this->parse($pattern);
        $search = $this->getByName($name);
        
        if ($terms['_domain'] !== NULL && !$search->supports($terms['_domain']))
        {
            throw new ParsingException("The domain is not supported for the search name");
        }
        
        return $search->renderResult($terms, $start, $limit);
    }

    /**
     * return search services with a specific name, defined in service
     * definition.
     *
     * @return SearchInterface
     * @throws UnknowSearchNameException if not exists
     */
    public function getByName($name)
    {
        if (isset($this->searchServices[$name])) {
            return $this->searchServices[$name];
        } else {
            throw new UnknowSearchNameException($name);
        }
    }

    public function addSearchService(SearchInterface $service, $name)
    {
        $this->searchServices[$name] = $service;
    }

    /**
     * Converts all accent characters to ASCII characters.
     *
     * If there are no accent characters, then the string given is just returned.
     *
     * Imported from wordpress : https://core.trac.wordpress.org/browser/tags/4.1/src/wp-includes/formatting.php?order=name
     *
     * @param string $string Text that might have accent characters
     * @return string Filtered string with replaced "nice" characters.
     * @license GNU GPLv2 : https://core.trac.wordpress.org/browser/tags/4.1/src/license.txt
     */
    private function remove_accents($string)
    {
        if (!preg_match('/[\x80-\xff]/', $string))
            return $string;

        //if ($this->seems_utf8($string)) { // remove from wordpress: we use only UTF-8
        if (true) {
            $chars = array(
               // Decompositions for Latin-1 Supplement
               chr(194) . chr(170) => 'a', chr(194) . chr(186) => 'o',
               chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
               chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
               chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
               chr(195) . chr(134) => 'AE', chr(195) . chr(135) => 'C',
               chr(195) . chr(136) => 'E', chr(195) . chr(137) => 'E',
               chr(195) . chr(138) => 'E', chr(195) . chr(139) => 'E',
               chr(195) . chr(140) => 'I', chr(195) . chr(141) => 'I',
               chr(195) . chr(142) => 'I', chr(195) . chr(143) => 'I',
               chr(195) . chr(144) => 'D', chr(195) . chr(145) => 'N',
               chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
               chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
               chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
               chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
               chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
               chr(195) . chr(158) => 'TH', chr(195) . chr(159) => 's',
               chr(195) . chr(160) => 'a', chr(195) . chr(161) => 'a',
               chr(195) . chr(162) => 'a', chr(195) . chr(163) => 'a',
               chr(195) . chr(164) => 'a', chr(195) . chr(165) => 'a',
               chr(195) . chr(166) => 'ae', chr(195) . chr(167) => 'c',
               chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
               chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
               chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
               chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
               chr(195) . chr(176) => 'd', chr(195) . chr(177) => 'n',
               chr(195) . chr(178) => 'o', chr(195) . chr(179) => 'o',
               chr(195) . chr(180) => 'o', chr(195) . chr(181) => 'o',
               chr(195) . chr(182) => 'o', chr(195) . chr(184) => 'o',
               chr(195) . chr(185) => 'u', chr(195) . chr(186) => 'u',
               chr(195) . chr(187) => 'u', chr(195) . chr(188) => 'u',
               chr(195) . chr(189) => 'y', chr(195) . chr(190) => 'th',
               chr(195) . chr(191) => 'y', chr(195) . chr(152) => 'O',
               // Decompositions for Latin Extended-A
               chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
               chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
               chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
               chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
               chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
               chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
               chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
               chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
               chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
               chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
               chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
               chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
               chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
               chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
               chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
               chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
               chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
               chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
               chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
               chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
               chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
               chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
               chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
               chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
               chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
               chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
               chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
               chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
               chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
               chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
               chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
               chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
               chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
               chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
               chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
               chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
               chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
               chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
               chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
               chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
               chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
               chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
               chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
               chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
               chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
               chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
               chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
               chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
               chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
               chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
               chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
               chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
               chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
               chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
               chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
               chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
               chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
               chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
               chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
               chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
               chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
               chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
               chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
               chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's',
               // Decompositions for Latin Extended-B
               chr(200) . chr(152) => 'S', chr(200) . chr(153) => 's',
               chr(200) . chr(154) => 'T', chr(200) . chr(155) => 't',
               // Euro Sign
               chr(226) . chr(130) . chr(172) => 'E',
               // GBP (Pound) Sign
               chr(194) . chr(163) => '',
               // Vowels with diacritic (Vietnamese)
               // unmarked
               chr(198) . chr(160) => 'O', chr(198) . chr(161) => 'o',
               chr(198) . chr(175) => 'U', chr(198) . chr(176) => 'u',
               // grave accent
               chr(225) . chr(186) . chr(166) => 'A', chr(225) . chr(186) . chr(167) => 'a',
               chr(225) . chr(186) . chr(176) => 'A', chr(225) . chr(186) . chr(177) => 'a',
               chr(225) . chr(187) . chr(128) => 'E', chr(225) . chr(187) . chr(129) => 'e',
               chr(225) . chr(187) . chr(146) => 'O', chr(225) . chr(187) . chr(147) => 'o',
               chr(225) . chr(187) . chr(156) => 'O', chr(225) . chr(187) . chr(157) => 'o',
               chr(225) . chr(187) . chr(170) => 'U', chr(225) . chr(187) . chr(171) => 'u',
               chr(225) . chr(187) . chr(178) => 'Y', chr(225) . chr(187) . chr(179) => 'y',
               // hook
               chr(225) . chr(186) . chr(162) => 'A', chr(225) . chr(186) . chr(163) => 'a',
               chr(225) . chr(186) . chr(168) => 'A', chr(225) . chr(186) . chr(169) => 'a',
               chr(225) . chr(186) . chr(178) => 'A', chr(225) . chr(186) . chr(179) => 'a',
               chr(225) . chr(186) . chr(186) => 'E', chr(225) . chr(186) . chr(187) => 'e',
               chr(225) . chr(187) . chr(130) => 'E', chr(225) . chr(187) . chr(131) => 'e',
               chr(225) . chr(187) . chr(136) => 'I', chr(225) . chr(187) . chr(137) => 'i',
               chr(225) . chr(187) . chr(142) => 'O', chr(225) . chr(187) . chr(143) => 'o',
               chr(225) . chr(187) . chr(148) => 'O', chr(225) . chr(187) . chr(149) => 'o',
               chr(225) . chr(187) . chr(158) => 'O', chr(225) . chr(187) . chr(159) => 'o',
               chr(225) . chr(187) . chr(166) => 'U', chr(225) . chr(187) . chr(167) => 'u',
               chr(225) . chr(187) . chr(172) => 'U', chr(225) . chr(187) . chr(173) => 'u',
               chr(225) . chr(187) . chr(182) => 'Y', chr(225) . chr(187) . chr(183) => 'y',
               // tilde
               chr(225) . chr(186) . chr(170) => 'A', chr(225) . chr(186) . chr(171) => 'a',
               chr(225) . chr(186) . chr(180) => 'A', chr(225) . chr(186) . chr(181) => 'a',
               chr(225) . chr(186) . chr(188) => 'E', chr(225) . chr(186) . chr(189) => 'e',
               chr(225) . chr(187) . chr(132) => 'E', chr(225) . chr(187) . chr(133) => 'e',
               chr(225) . chr(187) . chr(150) => 'O', chr(225) . chr(187) . chr(151) => 'o',
               chr(225) . chr(187) . chr(160) => 'O', chr(225) . chr(187) . chr(161) => 'o',
               chr(225) . chr(187) . chr(174) => 'U', chr(225) . chr(187) . chr(175) => 'u',
               chr(225) . chr(187) . chr(184) => 'Y', chr(225) . chr(187) . chr(185) => 'y',
               // acute accent
               chr(225) . chr(186) . chr(164) => 'A', chr(225) . chr(186) . chr(165) => 'a',
               chr(225) . chr(186) . chr(174) => 'A', chr(225) . chr(186) . chr(175) => 'a',
               chr(225) . chr(186) . chr(190) => 'E', chr(225) . chr(186) . chr(191) => 'e',
               chr(225) . chr(187) . chr(144) => 'O', chr(225) . chr(187) . chr(145) => 'o',
               chr(225) . chr(187) . chr(154) => 'O', chr(225) . chr(187) . chr(155) => 'o',
               chr(225) . chr(187) . chr(168) => 'U', chr(225) . chr(187) . chr(169) => 'u',
               // dot below
               chr(225) . chr(186) . chr(160) => 'A', chr(225) . chr(186) . chr(161) => 'a',
               chr(225) . chr(186) . chr(172) => 'A', chr(225) . chr(186) . chr(173) => 'a',
               chr(225) . chr(186) . chr(182) => 'A', chr(225) . chr(186) . chr(183) => 'a',
               chr(225) . chr(186) . chr(184) => 'E', chr(225) . chr(186) . chr(185) => 'e',
               chr(225) . chr(187) . chr(134) => 'E', chr(225) . chr(187) . chr(135) => 'e',
               chr(225) . chr(187) . chr(138) => 'I', chr(225) . chr(187) . chr(139) => 'i',
               chr(225) . chr(187) . chr(140) => 'O', chr(225) . chr(187) . chr(141) => 'o',
               chr(225) . chr(187) . chr(152) => 'O', chr(225) . chr(187) . chr(153) => 'o',
               chr(225) . chr(187) . chr(162) => 'O', chr(225) . chr(187) . chr(163) => 'o',
               chr(225) . chr(187) . chr(164) => 'U', chr(225) . chr(187) . chr(165) => 'u',
               chr(225) . chr(187) . chr(176) => 'U', chr(225) . chr(187) . chr(177) => 'u',
               chr(225) . chr(187) . chr(180) => 'Y', chr(225) . chr(187) . chr(181) => 'y',
               // Vowels with diacritic (Chinese, Hanyu Pinyin)
               chr(201) . chr(145) => 'a',
               // macron
               chr(199) . chr(149) => 'U', chr(199) . chr(150) => 'u',
               // acute accent
               chr(199) . chr(151) => 'U', chr(199) . chr(152) => 'u',
               // caron
               chr(199) . chr(141) => 'A', chr(199) . chr(142) => 'a',
               chr(199) . chr(143) => 'I', chr(199) . chr(144) => 'i',
               chr(199) . chr(145) => 'O', chr(199) . chr(146) => 'o',
               chr(199) . chr(147) => 'U', chr(199) . chr(148) => 'u',
               chr(199) . chr(153) => 'U', chr(199) . chr(154) => 'u',
               // grave accent
               chr(199) . chr(155) => 'U', chr(199) . chr(156) => 'u',
            );

            // Used for locale-specific rules
            /* remove from wordpress
            $locale = get_locale();

            if ('de_DE' == $locale) {
                $chars[chr(195) . chr(132)] = 'Ae';
                $chars[chr(195) . chr(164)] = 'ae';
                $chars[chr(195) . chr(150)] = 'Oe';
                $chars[chr(195) . chr(182)] = 'oe';
                $chars[chr(195) . chr(156)] = 'Ue';
                $chars[chr(195) . chr(188)] = 'ue';
                $chars[chr(195) . chr(159)] = 'ss';
            } elseif ('da_DK' === $locale) {
                $chars[chr(195) . chr(134)] = 'Ae';
                $chars[chr(195) . chr(166)] = 'ae';
                $chars[chr(195) . chr(152)] = 'Oe';
                $chars[chr(195) . chr(184)] = 'oe';
                $chars[chr(195) . chr(133)] = 'Aa';
                $chars[chr(195) . chr(165)] = 'aa';
            }*/

            $string = strtr($string, $chars);
        } /* remove from wordpress: we use only utf 8
         * else {
         
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = chr(128) . chr(131) . chr(138) . chr(142) . chr(154) . chr(158)
                  . chr(159) . chr(162) . chr(165) . chr(181) . chr(192) . chr(193) . chr(194)
                  . chr(195) . chr(196) . chr(197) . chr(199) . chr(200) . chr(201) . chr(202)
                  . chr(203) . chr(204) . chr(205) . chr(206) . chr(207) . chr(209) . chr(210)
                  . chr(211) . chr(212) . chr(213) . chr(214) . chr(216) . chr(217) . chr(218)
                  . chr(219) . chr(220) . chr(221) . chr(224) . chr(225) . chr(226) . chr(227)
                  . chr(228) . chr(229) . chr(231) . chr(232) . chr(233) . chr(234) . chr(235)
                  . chr(236) . chr(237) . chr(238) . chr(239) . chr(241) . chr(242) . chr(243)
                  . chr(244) . chr(245) . chr(246) . chr(248) . chr(249) . chr(250) . chr(251)
                  . chr(252) . chr(253) . chr(255);

            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
            $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        } */

        return $string;
    }

}
