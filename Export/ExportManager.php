<?php

/*
 * Copyright (C) 2015 Champs-Libres <info@champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Chill\MainBundle\Export;

use Chill\MainBundle\Export\FilterInterface;
use Chill\MainBundle\Export\AggregatorInterface;
use Chill\MainBundle\Export\ExportInterface;
use Chill\MainBundle\Export\FormatterInterface;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Collects all agregators, filters and export from
 * the installed bundle. 
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ExportManager
{
    /**
     *
     * @var FilterInterface[]
     */
    private $filters = array();
    
    /**
     *
     * @var AggregatorInterface[]
     */
    private $aggregators = array();
    
    /**
     *
     * @var ExportInterface[]
     */
    private $exports = array();
    
    /**
     *
     * @var FormatterInterface[]
     */
    private $formatters = array();
    
    /**
     *
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     *
     * @var EntityManagerInterface
     */
    private $em;
    
    public function __construct(LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->logger = $logger;
        $this->em = $em;
    }
    
    public function addFilter(FilterInterface $filter, $alias)
    {
        $this->filters[$alias] = $filter;
    }
    
    public function addAggregator(AggregatorInterface $aggregator, $alias)
    {
        $this->aggregators[$alias] = $aggregator;
    }
    
    public function addExport(ExportInterface $export, $alias)
    {
        $this->exports[$alias] = $export;
    }
    
    public function addFormatter(FormatterInterface $formatter)
    {
        array_push($this->formatters, $formatter);
    }
    
    /**
     * 
     * @return string[] the existing type for known exports
     */
    public function getExistingExportsTypes()
    {
        $existingTypes = array();
        
        foreach($this->exports as $export) {
            if (!in_array($export->getType(), $existingTypes)) {
                array_push($existingTypes, $export->getType());
            }
        }
        
        return $existingTypes;
    }
    
    /**
     * Return all exports. The exports's alias are the array's keys.
     * 
     * @return ExportInterface[] an array where export's alias are keys
     */
    public function getExports()
    {
        return $this->exports;
    }
    
    /**
     * Return an export by his alias
     * 
     * @param string $alias
     * @return ExportInterface
     * @throws \RuntimeException
     */
    public function getExport($alias)
    {
        if (!array_key_exists($alias, $this->exports)) {
            throw new \RuntimeException("The export with alias $alias is not known.");
        }
        
        return $this->exports[$alias];
    }
    
    /**
     * 
     * @param string $alias
     * @return FilterInterface
     * @throws \RuntimeException if the filter is not known
     */
    public function getFilter($alias)
    {
        if (!array_key_exists($alias, $this->filters)) {
            throw new \RuntimeException("The filter with alias $alias is not known.");
        }
        
        return $this->filters[$alias];
    }
    
    /**
     * 
     * @param string $alias
     * @return AggregatorInterface
     * @throws \RuntimeException if the aggregator is not known
     */
    public function getAggregator($alias)
    {
        if (!array_key_exists($alias, $this->aggregators)) {
            throw new \RuntimeException("The aggregator with alias $alias is not known.");
        }
        
        return $this->aggregators[$alias];
    }
    
    
    /**
     * Return a \Generator containing filter which support type
     * 
     * @param string[] $types
     * @return FilterInterface[] a \Generator that contains filters. The key is the filter's alias
     */
    public function &getFiltersApplyingOn(array $types)
    {
        foreach ($this->filters as $alias => $filter) {
            if (in_array($filter->applyOn(), $types)) {
                yield $alias => $filter;
            }
        }
    }
    
    /**
     * Return a \Generator containing filter which support type
     * 
     * @param string $types
     * @return FilterInterface[] a \Generator that contains filters. The key is the filter's alias
     */
    public function &getFiltersSupportingType($type)
    {
        foreach ($this->filters as $alias => $filter) {
            if ($filter->supportsType($type)) {
                yield $alias => $filter;
            }
        }
    }
    
    /**
     * Return a \Generator containing aggregators which support type
     * 
     * @param string[] $types
     * @return AggregatorInterface[] a \Generator that contains aggretagors. The key is the filter's alias
     */
    public function &getAggregatorsApplyingOn(array $types)
    {
        foreach ($this->aggregators as $alias => $aggregator) {
            if (in_array($aggregator->applyOn(), $types)) {
                yield $alias => $aggregator;
            }
        }
    }
    
    /**
     * Generate a response which contains the requested data.
     * 
     * @param string $exportAlias
     * @param mixed[] $data
     * @return Response
     */
    public function generate($exportAlias, array $data)
    {
        $export = $this->getExport($exportAlias);
        $qb = $this->em->createQueryBuilder();
        
        $qb = $export->initiateQuery($qb, $this->retrieveUsedModifiers($data));
        
        //handle filters
        $this->handleFilters($export, $qb, $data['filters']);
        
        //handle aggregators
        $this->handleAggregators($export, $qb, $data['aggregators']);
        
        $this->logger->debug('current query is '.$qb->getDQL(), array(
            'class' => self::class, 'function' => __FUNCTION__
        ));
        
        $results = $qb->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_SCALAR);
        
        var_dump($results);
        
        return new Response('everything is fine !');
    }
    
    /**
     * parse the data to retrieve the used filters and aggregators
     * 
     * @param mixed $data
     * @return string[]
     */
    private function retrieveUsedModifiers($data)
    {
        $usedTypes = array();
        
        // used filters
        $this->retrieveUsedFilters($data, $usedTypes);
        // used aggregators
        $this->retrieveUsedAggregators($data, $usedTypes);
        
        $this->logger->debug('Required types are '.implode(', ', $usedTypes), 
                array('class' => self::class, 'function' => __FUNCTION__));
        
        return $usedTypes;
    }
    
    private function retrieveUsedFilters($data, &$usedTypes)
    {
        foreach($data['filters'] as $alias => $filterData) {
            if ($filterData['enabled'] == true){
                $filter = $this->getFilter($alias);
                if (!in_array($filter->applyOn(), $usedTypes)) {
                    array_push($usedTypes, $filter->applyOn());
                }
            }
        }
    }
    
    private function retrieveUsedAggregators($data, &$usedTypes)
    {
        foreach($data['aggregators'] as $alias => $aggregatorData) {
            if ($aggregatorData['order']> 0){
                $aggregator = $this->getAggregator($alias);
                if (!in_array($aggregator->applyOn(), $usedTypes)) {
                    array_push($usedTypes, $aggregator->applyOn());
                }
            }
        }
    }
    
    /**
     * 
     * @param ExportInterface $export
     * @param QueryBuilder $qb
     * @param mixed $data the data under the initial 'filters' data
     */
    private function handleFilters(ExportInterface $export, QueryBuilder $qb, $data)
    {
        $filters = $this->getFiltersApplyingOn($export->supportsModifiers());
                
        foreach($filters as $alias => $filter) {
            $this->logger->debug('handling filter '.$alias, array(
                'class' => self::class, 'function' => __FUNCTION__
            ));
            
            $formData = $data[$alias];
            
            if ($formData['enabled'] == true) {
                $this->logger->debug('alter query by filter '.$alias, array(
                    'class' => self::class, 'function' => __FUNCTION__
                ));
                $filter->alterQuery($qb, $formData['form']);
            } else {
                $this->logger->debug('skipping filter '.$alias.' because not enabled',
                        array('class' => self::class, 'function' => __FUNCTION__));
            }
        }
    }
    
    private function handleAggregators(ExportInterface $export, QueryBuilder $qb, $data)
    {
        $aggregators = $this->getAggregatorsApplyingOn($export->supportsModifiers());
        
        foreach ($aggregators as $alias => $aggregator) {
            $formData = $data[$alias];
            if ($formData['order'] >= 0) {
                $aggregator->alterQuery($qb, $formData['form']);
            }
        }
    }
    
}
