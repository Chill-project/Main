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
        $filteredPattern = mb_strtolower($pattern);
        
        $terms = $this->extractTerms($filteredPattern);      
        $terms['_domain'] = $this->extractDomain($filteredPattern);
        $terms['_default'] = $this->extractDefault($filteredPattern);
        
        return $terms;
    }
    
    private function extractDomain(&$subject)
    {
        preg_match_all( '/@([a-z]+)/', $subject, $terms);
        
        if (count($terms[0]) > 1) {
            throw new ParsingException('You should not have more than one domain');
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
        preg_match_all('/([a-z]+):([\w\-]+|\([^\(\r\n]+\))/', $subject, $matches);
        
        foreach ($matches[2] as $key => $match) {
            //remove from search pattern
            $this->mustBeExtracted[] = $matches[0][$key];
             //strip parenthesis
            if (mb_substr($match, 0, 1) === '(' && 
                  mb_substr($match, mb_strlen($match) - 1) === ')') {
                $match = mb_substr($match, 1, mb_strlen($match)-2);
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
    private function extractDefault($subject) {
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
     */
    public function getResults($pattern, $start = 0, $limit = 50)
    {
        $terms = $this->parse($pattern);
        $results = array();
        
        foreach ($searchServices as $service) {
            if ($service->supports($terms['_domain'])) {
                $results[] = $service->renderResult($terms, $start, $limit);
            }
        }
    
        return $results;
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
            return $this->searchServices; 
        } else {
          throw new UnknowSearchNameException($name);
        }
    }

    public function addSearchService(SearchInterface $service, $name)
    {
        $this->searchServices[$name] = $service;
    }
}