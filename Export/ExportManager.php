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
use Chill\MainBundle\Security\Authorization\AuthorizationHelper;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Chill\MainBundle\Form\Type\Export\PickCenterType;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Chill\MainBundle\Form\Type\Export\ExportType;

/**
 * Collects all agregators, filters and export from
 * the installed bundle, and performs the export logic.
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ExportManager
{
    /**
     * The collected filters, injected by DI
     *
     * @var FilterInterface[]
     */
    private $filters = array();
    
    /**
     * The collected aggregators, injected by DI
     *
     * @var AggregatorInterface[]
     */
    private $aggregators = array();
    
    /**
     * Collected Exports, injected by DI
     *
     * @var ExportInterface[]
     */
    private $exports = array();
    
    /**
     * Collected Formatters, injected by DI
     *
     * @var FormatterInterface[]
     */
    private $formatters = array();
    
    /**
     * a logger 
     *
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     *
     * @var EntityManagerInterface
     */
    private $em;
    
    /**
     *
     * @var AuthorizationChecker
     */
    private $authorizationChecker;
    
    /**
     *
     * @var AuthorizationHelper
     */
    private $authorizationHelper;
    
    /**
     *
     * @var \Symfony\Component\Security\Core\User\UserInterface
     */
    private $user;
    
    public function __construct(
            LoggerInterface $logger, 
            EntityManagerInterface $em,
            AuthorizationCheckerInterface $authorizationChecker, 
            AuthorizationHelper $authorizationHelper,
            TokenStorageInterface $tokenStorage)
    {
        $this->logger = $logger;
        $this->em = $em;
        $this->authorizationChecker = $authorizationChecker;
        $this->authorizationHelper = $authorizationHelper;
        $this->user = $tokenStorage->getToken()->getUser();
    }
    
    /**
     * add a Filter
     * 
     * @internal Normally used by the dependency injection
     * 
     * @param FilterInterface $filter
     * @param string $alias
     */
    public function addFilter(FilterInterface $filter, $alias)
    {
        $this->filters[$alias] = $filter;
    }
    
    /**
     * add an aggregator
     * 
     * @internal used by DI
     * 
     * @param AggregatorInterface $aggregator
     * @param string $alias
     */
    public function addAggregator(AggregatorInterface $aggregator, $alias)
    {
        $this->aggregators[$alias] = $aggregator;
    }
    
    /**
     * add an export
     * 
     * @internal used by DI
     * 
     * @param ExportInterface $export
     * @param type $alias
     */
    public function addExport(ExportInterface $export, $alias)
    {
        $this->exports[$alias] = $export;
    }
    
    /**
     * add a formatter
     * 
     * @internal used by DI
     * 
     * @param FormatterInterface $formatter
     * @param type $alias
     */
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
     * @param boolean $whereUserIsGranted if true (default), restrict to user which are granted the right to execute the export
     * @return ExportInterface[] an array where export's alias are keys
     */
    public function getExports($whereUserIsGranted = true)
    {
        foreach ($this->exports as $alias => $export) {
            if ($whereUserIsGranted) {
                if ($this->isGrantedForElement($export, null, null)) {
                    yield $alias => $export;
                }
            } else {
                yield $alias => $export;
            }
        }
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
     * Return a \Generator containing filter which support type. If `$centers` is
     * not null, restrict the given filters to the center the user have access to.
     * 
     * if $centers is null, the function will returns all filters where the user 
     * has access in every centers he can reach (if the user can use the filter F in
     * center A, but not in center B, the filter F will not be returned)
     * 
     * @param \Chill\MainBundle\Entity\Center[] $centers the centers where the user have access to
     * @return FilterInterface[] a \Generator that contains filters. The key is the filter's alias
     */
    public function &getFiltersApplyingOn(ExportInterface $export, array $centers = null)
    {
        foreach ($this->filters as $alias => $filter) {
            if (in_array($filter->applyOn(), $export->supportsModifiers()) 
                    && $this->isGrantedForElement($filter, $export, $centers)) {
                yield $alias => $filter;
            }
        }
    }
    
    /**
     * Return true if the current user has access to the ExportElement for every
     * center, false if the user hasn't access to element for at least one center.
     * 
     * @param \Chill\MainBundle\Export\ExportElementInterface $element
     * @param array|null $centers, if null, the function take into account all the reachables centers for the current user and the role given by element::requiredRole
     * @return boolean
     */
    public function isGrantedForElement(ExportElementInterface $element, ExportInterface $export = NULL, array $centers = null)
    {
        if ($element instanceof ExportInterface) {
            $role = $element->requiredRole();
        } elseif ($element instanceof ModifierInterface ) {
            if (is_null($element->addRole())) {
                if (is_null($export)) {
                    throw new \LogicException("The export should not be null: as the "
                            . "ModifierInstance element is not an export, we should "
                            . "be aware of the export to determine which role is required");
                } else {
                    $role = $export->requiredRole();
                }
            } else {
                $role = $element->addRole();
            }
        } else {
            throw new \LogicException("The element is not an ModifiersInterface or "
                    . "an ExportInterface.");
        }
        
        if ($centers === null) {
            $centers = $this->authorizationHelper->getReachableCenters($this->user, 
                            $role);
        }
        
        if (count($centers) === 0) {
            return false;
        }
        
        foreach($centers as $center) {
            if ($this->authorizationChecker->isGranted($role->getRole(), $center) === false) {
                //debugging
                $this->logger->debug('user has no access to element', array(
                    'method' => __METHOD__,  
                    'type' => get_class($element), 'center' => $center->getName()
                ));
                
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Return a \Generator containing aggregators which support type
     * 
     * @return AggregatorInterface[] a \Generator that contains aggretagors. The key is the filter's alias
     */
    public function &getAggregatorsApplyingOn(ExportInterface $export, array $centers = null)
    {
        foreach ($this->aggregators as $alias => $aggregator) {
            if (in_array($aggregator->applyOn(), $export->supportsModifiers()) && 
                    $this->isGrantedForElement($aggregator, $export, $centers)) {
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
    public function generate($exportAlias, array $pickedCentersData, array $data, array $formatterData)
    {
        $export = $this->getExport($exportAlias);
        $qb = $this->em->createQueryBuilder();
        $centers = $this->getPickedCenters($pickedCentersData);
        
        $qb = $export->initiateQuery(
                $qb, 
                $this->retrieveUsedModifiers($data), 
                $this->buildCenterReachableScopes($centers, $export),
                $data[ExportType::EXPORT_KEY]
                );
        
        //handle filters
        $this->handleFilters($export, $qb, $data[ExportType::FILTER_KEY], $centers);
        
        //handle aggregators
        $this->handleAggregators($export, $qb, $data[ExportType::AGGREGATOR_KEY], $centers);
        
//        $this->logger->debug('current query is '.$qb->getDQL(), array(
//            'class' => self::class, 'function' => __FUNCTION__
//        ));
        
        $result = $export->getResult($qb, $data[ExportType::EXPORT_KEY]);
        
        /* @var $formatter Formatter\CSVFormatter */
        $formatter = $this->getFormatter($this->getFormatterAlias($data));
        $filters = array();
        
        $aggregators = $this->retrieveUsedAggregators($data[ExportType::AGGREGATOR_KEY]);
        $aggregatorsData = array();
        foreach($aggregators as $alias => $aggregator) {
            $aggregatorsData[$alias] = $data[ExportType::AGGREGATOR_KEY][$alias]['form'];
        }
        
        return $formatter->getResponse(
              $result, 
              $formatterData, 
              $exportAlias, 
              $data[ExportType::EXPORT_KEY],
              $filters, 
              $aggregatorsData);
    }
    
    /**
     * build the array required for defining centers and circles in the initiate 
     * queries of ExportElementsInterfaces
     * 
     * @param \Chill\MainBundle\Entity\Center[] $centers
     */
    private function buildCenterReachableScopes(array $centers, ExportElementInterface $element) {
        $r = array();
        
        foreach($centers as $center) {
            $r[] = array(
                'center' => $center,
                'circles' => $this->authorizationHelper->getReachableScopes($this->user, 
                        $element->requiredRole(), $center)
            );
        }
        
        return $r;
    }
    
    /**
     * get the aggregators typse used in the form export data
     * 
     * @param array $data the data from the export form
     * @return string[]
     */
    public function getUsedAggregatorsAliases(array $data)
    {
        $aggregators = $this->retrieveUsedAggregators($data[ExportType::AGGREGATOR_KEY]);
        
        return array_keys(iterator_to_array($aggregators));
    }
    
    /**
     * get the formatter alias from the form export data
     * 
     * @param array $data the data from the export form
     * @string the formatter alias
     */
    public function getFormatterAlias(array $data)
    {
        return $data[ExportType::PICK_FORMATTER_KEY]['alias'];
    }
    
    /**
     * Get the Center picked by the user for this export. The data are
     * extracted from the PickCenterType data
     * 
     * @param array $data the data from a PickCenterType
     * @return \Chill\MainBundle\Entity\Center[] the picked center
     */
    public function getPickedCenters(array $data)
    {
        return $data[PickCenterType::CENTERS_IDENTIFIERS];
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
                $this->retrieveUsedFiltersType($data[ExportType::FILTER_KEY]),
                $this->retrieveUsedAggregatorsType($data[ExportType::AGGREGATOR_KEY])
                );
        
        $this->logger->debug('Required types are '.implode(', ', $usedTypes), 
                array('class' => self::class, 'function' => __FUNCTION__));
        
        return array_unique($usedTypes);
    }
    
    /**
     * Retrieve the filter used in this export.
     * 
     * @param mixed $data the data from the `filters` key of the ExportType
     * @return array an array with types
     */
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
        foreach ($data as $alias => $aggregatorData) {
            if ($aggregatorData['enabled'] === true){
                yield $alias => $this->getAggregator($alias);
            }
        }
    }
    
    /**
     * 
     * @param type $data the data from the filter key of the ExportType
     */
    private function retrieveUsedFilters($data) 
    {
        foreach ($data as $alias => $filterData) {
            if ($filterData['enabled'] === true) {
                yield $alias => $this->getFilter($alias);
            }
        }
    }
    
    /**
     * alter the query with selected filters.
     * 
     * This function check the acl.
     * 
     * @param ExportInterface $export
     * @param QueryBuilder $qb
     * @param mixed $data the data under the initial 'filters' data
     * @param \Chill\MainBundle\Entity\Center[] $centers the picked centers
     * @throw UnauthorizedHttpException if the user is not authorized
     */
    private function handleFilters(
            ExportInterface $export, 
            QueryBuilder $qb, 
            $data, 
            array $centers)
    {
        $filters = $this->retrieveUsedFilters($data);
                
        foreach($filters as $alias => $filter) {
            if ($this->isGrantedForElement($filter, $export, $centers) === false) {
                throw new UnauthorizedHttpException("You are not authorized to "
                        . "use the filter ".$filter->getTitle());
            }
            
            $formData = $data[$alias];

            $this->logger->debug('alter query by filter '.$alias, array(
                'class' => self::class, 'function' => __FUNCTION__
            ));
            $filter->alterQuery($qb, $formData['form']);

        }
    }
    
    /**
     * Alter the query with selected aggregators
     * 
     * Check for acl. If an user is not authorized to see an aggregator, throw an
     * UnauthorizedException.
     * 
     * @param ExportInterface $export
     * @param QueryBuilder $qb
     * @param type $data
     * @param \Chill\MainBundle\Entity\Center[] $centers the picked centers
     * @throw UnauthorizedHttpException if the user is not authorized
     */
    private function handleAggregators(
            ExportInterface $export, 
            QueryBuilder $qb, 
            $data, 
            array $center)
    {
        $aggregators = $this->retrieveUsedAggregators($data);
        
        foreach ($aggregators as $alias => $aggregator) {
            $formData = $data[$alias];
            $aggregator->alterQuery($qb, $formData['form']);
        }
    }
    
}
