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
    
    public function addFormatter(FormatterInterface $formatter, $alias)
    {
        $this->formatters[$alias] = $formatter;
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
    
    public function getFilters(array $aliases)
    {
        foreach($aliases as $alias) {
            yield $alias => $this->getFilter($alias);
        }
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
    
    public function getAggregators(array $aliases)
    {
        foreach ($aliases as $alias) {
            yield $alias => $this->getAggregator($alias);
        }
    }
    
    public function getFormatter($alias)
    {
        if (!array_key_exists($alias, $this->formatters)) {
            throw new \RuntimeException("The formatter with alias $alias is not known.");
        }
        
        return $this->formatters[$alias];
    }
    
    public function getFormattersByTypes(array $types)
    {
        foreach ($this->formatters as $alias => $formatter) {
            if (in_array($formatter->getType(), $types)) {
                yield $alias => $formatter;
            }
        }
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
        
        $result = $export->getResult($qb, array());
        
        /* @var $formatter Formatter\CSVFormatter */
        $formatter = $this->getFormatter('csv');
        $filters = array();
        $aggregators = iterator_to_array($this->retrieveUsedAggregators($data['aggregators']));
        $aggregatorsData = array_combine(array_keys($data['aggregators']), 
                array_map(function($data) { return $data['form']; }, $data['aggregators'])
            );
        
        return $formatter->getResponse($result, array(), $export, 
                $filters, $aggregators, array(), $data['filters'], $aggregatorsData);
    }
    
    /**
     * parse the data to retrieve the used filters and aggregators
     * 
     * @param mixed $data
     * @return string[]
     */
    private function retrieveUsedModifiers($data)
    {
        $usedTypes = array_merge(
                $this->retrieveUsedFiltersType($data['filters']),
                $this->retrieveUsedAggregatorsType($data['aggregators'])
                );
        
        $this->logger->debug('Required types are '.implode(', ', $usedTypes), 
                array('class' => self::class, 'function' => __FUNCTION__));
        
        return $usedTypes;
    }
    
    private function retrieveUsedFiltersType($data)
    {
        $usedTypes = array();
        foreach($data as $alias => $filterData) {
            if ($filterData['enabled'] == true){
                $filter = $this->getFilter($alias);
                if (!in_array($filter->applyOn(), $usedTypes)) {
                    array_push($usedTypes, $filter->applyOn());
                }
            }
        }
        
        return $usedTypes;
    }
    
    /**
     * 
     * @param mixed $data
     * @return string[]
     */
    private function retrieveUsedAggregatorsType($data)
    {
        $usedTypes = array();
        foreach($this->retrieveUsedAggregators($data) as $alias => $aggregator) {
            if (!in_array($aggregator->applyOn(), $usedTypes)) {
                array_push($usedTypes, $aggregator->applyOn());
            }
        }
        
        return $usedTypes;
    }
    
    /**
     * 
     * @param mixed $data
     * @return AggregatorInterface[]
     */
    private function retrieveUsedAggregators($data)
    {
        foreach($data as $alias => $aggregatorData) {
            if ($aggregatorData['enabled'] === true){
                yield $alias => $this->getAggregator($alias);
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
        //$aggregators = $this->getAggregatorsApplyingOn($export->supportsModifiers());
        $aggregators = $this->retrieveUsedAggregators($data);
        
        foreach ($aggregators as $alias => $aggregator) {
            $formData = $data[$alias];
            //if ($formData['order'] >= 0) {
                $aggregator->alterQuery($qb, $formData['form']);
            //}
        }
    }
    
}
