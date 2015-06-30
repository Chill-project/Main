<?php

/*
 * Copyright (C) 2015 Champs-Libres Coopérative <info@champs-libres.coop>
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

namespace Chill\MainBundle\Timeline;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Build timeline
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class TimelineBuilder implements ContainerAwareInterface
{
    
    use \Symfony\Component\DependencyInjection\ContainerAwareTrait;
    
    /**
     *
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;
    
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    /**
     *
     * @var string references to providers services
     */
    private $providers = array();
    
    /**
     * return an HTML string with timeline
     * 
     * This function must be called from controller
     * 
     * @example https://redmine.champs-libres.coop/projects/chillperson/repository/revisions/bd2e1b1808f73e39532e9538413025df5487cad0/entry/Controller/TimelinePersonController.php#L47 the implementation in person bundle
     * 
     * @param string $context
     * @param array $args arguments defined by the bundle which create the context
     * @param int $page first page = 0
     * @param int $number number of items by page
     * @return string an HTML representation, must be included using `|raw` filter
     */
    public function getTimelineHTML($context, array $args, $page = 0, $number = 20)
    {
        $query = $this->buildUnionQuery($context, $args, $page, $number);
        $fetched = $this->runQuery($query);
        $entitiesByKey = $this->getEntities($fetched, $context);
        
        return $this->render($fetched, $entitiesByKey, $context, $args);
    }
    
    /**
     * add a provider id
     * 
     * @internal This function is called by the TimelineCompilerClass
     * 
     * @param string $context the context of the service
     * @param string $id the 
     */
    public function addProvider($context, $id)
    {
        $this->providers[$context][] = $id;
    }
    
    /**
     * Get providers by context
     * 
     * @param string $context
     * @return TimelineProviderInterface[]
     */
    public function getProvidersByContext($context)
    {
        $providers = array();
        
        foreach($this->providers[$context] as $providerId) {
            $providers[] = $this->container->get($providerId);
        }
        
        return $providers;
    }
    
    /**
     * build the UNION query with all providers
     * 
     * @uses self::buildSelectQuery to build individual SELECT queries
     * 
     * @param string $context
     * @param mixed $args
     * @param int $page
     * @param int $number
     * @return string
     * @throws \LogicException if no builder have been defined for this context
     */
    private function buildUnionQuery($context, array $args, $page, $number)
    {
        //throw an exception if no provider have been defined for this context
        if (!array_key_exists($context, $this->providers)) {
            throw new \LogicException(sprintf('No builders have been defined for "%s"'
                    . ' context', $context));
        }
        
        //append SELECT queries with UNION keyword between them
        $union = '';
        foreach($this->getProvidersByContext($context) as $provider) {  
            $select = $this->buildSelectQuery($provider, $context, $args); 
            $append = ($union === '') ?  $select : ' UNION '.$select;
            $union .= $append;
        }
        //add ORDER BY clause and LIMIT
        $union .= sprintf(' ORDER BY date LIMIT %d OFFSET %d',
                $number, $page * $number);
        
        return $union;
    }
    
    /**
     * return the SQL SELECT query as a string,
     * 
     * @uses TimelineProfiderInterface::fetchQuery use the fetchQuery function
     * @param \Chill\MainBundle\Timeline\TimelineProviderInterface $provider
     * @param string $context
     * @param mixed[] $args
     * @return string
     */
    private function buildSelectQuery(TimelineProviderInterface $provider, $context, array $args)
    {
        $data = $provider->fetchQuery($context, $args);
        
        return sprintf(
                'SELECT %s AS id, '
                . '%s AS "date", '
                . "'%s' AS type "
                . 'FROM %s '
                . 'WHERE %s',
                $data['id'],
                $data['date'],
                $data['type'],
                $data['FROM'],
                $data['WHERE']);
    }
    
    /**
     * run the UNION query and return result as an array
     * 
     * @param string $query
     * @return array
     */
    private function runQuery($query)
    {
        $resultSetMapping = (new ResultSetMapping())
                ->addScalarResult('id', 'id')
                ->addScalarResult('type', 'type')
                ->addScalarResult('date', 'date');
        
        return $this->em->createNativeQuery($query, $resultSetMapping)
                ->getArrayResult();
    }
    
    /**
     * 
     * @param array $queriedIds
     * @param string $context
     * @return array with the form array($type => [$entity, $entity, $entity])
     */
    private function getEntities(array $queriedIds, $context)
    {
        //gather entities by type to pass all id with same type to the TimelineProvider. 
        $idsByType = array();
        
        foreach($queriedIds as $result) {
            $idsByType[$result['type']][] = $result['id'];
        }
        
        //fetch entities from providers
        $entitiesByType = array();
        foreach ($idsByType as $type => $ids) {
            //iterate providers for current context
            foreach($this->getProvidersByContext($context) as $provider) {
                if ($provider->supportsType($type)) {
                    $entitiesByType[$type] = $provider->getEntities($ids);
                    break; //we assume that providers have unique keys => we break the loop
                }
            }
        }
        
        return $entitiesByType;
    }
    
    /**
     * render the timeline as HTML
     * 
     * @param array $fetched
     * @param array $entitiesByType
     * @param string $context
     * @param mixed[] $args
     * @return string the HTML representation of the timeline
     */
    private function render(array $fetched, array $entitiesByType, $context, array $args)
    {
        //add results to a pretty array
        $timelineEntries = array();
        foreach ($fetched as $result) {
            $data = $this->getTemplateData(
                    $result['type'], 
                    $entitiesByType[$result['type']][$result['id']], //the entity
                    $context,
                    $args);
            $timelineEntry['date'] = new \DateTime($result['date']);
            $timelineEntry['template'] = $data['template'];
            $timelineEntry['template_data'] = $data['template_data'];
            
            $timelineEntries[] = $timelineEntry;
        }
        
        return $this->container->get('templating')
                ->render('ChillMainBundle:Timeline:index.html.twig', array(
                    'results' => $timelineEntries
                ));
        
    }
    
    /**
     * get the template data from the provider for the given entity, by type.
     * 
     * @param string $type
     * @param mixed $entity
     * @param string $context
     * @param mixed[] $args
     * @return array the template data fetched from the provider
     */
    private function getTemplateData($type, $entity, $context, array $args)
    {
        foreach($this->getProvidersByContext($context) as $provider) {
            if ($provider->supportsType($type)) {
                return $provider->getEntityTemplate($entity, $context, $args);
            }
        }
    }
}
