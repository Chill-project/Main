<?php

/*
 * Copyright (C) 2015 Julien Fastré <julien.fastre@champs-libres.coop>
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
    
    public function getTimeline($context, array $args, $page = 0, $number = 20)
    {
        $query = $this->buildQuery($context, $args, $page, $number);
        $ids = $this->runQuery($query);
        $entitiesByKey = $this->getEntities($ids, $context);
        
        return $this->render($ids, $entitiesByKey);
        
    }
    
    public function addProvider($context, $id)
    {
        $this->providers[$context][] = [$id];
    }
    
    
    private function buildQuery($context, array $args, $page, $number)
    {
        if (!array_key_exists($context, $this->providers)) {
            throw new \LogicException(sprintf('No builders have been defined for "%s"'
                    . ' context', $context));
        }
        
        $query = '';
        foreach($this->providers[$context] as $providerIds) {
            foreach ($providerIds as $providerId) {
                $provider = $this->container->get($providerId);
            
                $query .= ($query === '') ? 
                        $provider->fetchUnion($context, $args) :
                        ' UNION '.$provider->fetchUnion($context, $args);
            }
        }
        $query .= sprintf(' ORDER BY date LIMIT %d OFFSET %d',
                $number, $page * $number);
        
        return $query;
        
        
    }
    
    private function runQuery($query)
    {
        $resultSetMapping = (new ResultSetMapping())
                ->addScalarResult('id', 'id')
                ->addScalarResult('key', 'key')
                ->addScalarResult('date', 'date');
        
        return $this->em->createNativeQuery($query, $resultSetMapping)
                ->getArrayResult();
    }
    
    private function getEntities(array $queriedIds, $context)
    {
        //gather entities by key. Having all ids in the same table allow to query from providers
        $idsByKey = array();
        
        foreach($queriedIds as $result) {
            $idsByKey[$result['key']][] = $result['id'];
        }
        
        //fetch entities from providers
        $entitiesByKey = array();
        foreach ($idsByKey as $key => $ids) {
            //iterate providers for current context
            foreach($this->providers[$context] as $providerIds) {
                foreach ($providerIds as $providerId){
                    $provider = $this->container->get($providerId);

                    if ($provider->supportsKey($key)) {
                        $entitiesByKey[$key] = $provider->getEntities($ids);
                    }
                }
            }
        }
        
        return $entitiesByKey;
    }
    
    private function render(array $queriedIds, $entitiesByKey)
    {
        //add results to a pretty array
        $timelineEntries = array();
        foreach ($queriedIds as $result) {
            $timelineEntry['date'] = $result['date'];
            $timelineEntry['template'] = $entitiesByKey[$result['key']][$result['id']]['template'];
            $timelineEntry['templateArgs'] = $entitiesByKey[$result['key']][$result['id']]['entity'];
            
            $timelineEntries[] = $timelineEntry;
        }
        
        return $this->container->get('templating')
                ->render('ChillMainBundle:Timeline:index.html.twig', array(
                    'results' => $timelineEntries
                ));
        
    }
}
