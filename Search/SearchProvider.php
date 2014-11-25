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

    /*
     * return search services with a specific name, defined in service
     * definition.
     *
     * @return SearchInterface
     */
    public function getByName($name)
    {
        return $this->searchServices[$name];
    }

    public function addSearchService(SearchInterface $service, $name)
    {
        $this->searchServices[$name] = $service;
    }
}