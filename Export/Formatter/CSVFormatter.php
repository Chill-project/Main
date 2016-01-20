<?php

/*
 * Copyright (C) 2016 Champs-Libres <info@champs-libres.coop>
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

namespace Chill\MainBundle\Export\Formatter;

use Chill\MainBundle\Export\ExportInterface;
use Symfony\Component\HttpFoundation\Response;
use Chill\MainBundle\Export\FormatterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Chill\MainBundle\Export\ExportManager;
use Symfony\Component\Form\Extension\Core\Type\FormType;

// command to get the report with curl : curl --user "center a_social:password" "http://localhost:8000/fr/exports/generate/count_person?export[filters][person_gender_filter][enabled]=&export[filters][person_nationality_filter][enabled]=&export[filters][person_nationality_filter][form][nationalities]=&export[aggregators][person_nationality_aggregator][order]=1&export[aggregators][person_nationality_aggregator][form][group_by_level]=country&export[submit]=&export[_token]=RHpjHl389GrK-bd6iY5NsEqrD5UKOTHH40QKE9J1edU" --globoff

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class CSVFormatter implements FormatterInterface
{
    /**
     *
     * @var TranslatorInterface
     */
    protected $translator;
    
    protected $result;
    
    protected $formatterData;
    
    protected $export;
    
    protected $aggregators;
    
    protected $exportData;
    
    protected $aggregatorsData;
    
    /**
     *
     * @var ExportManager
     */
    protected $exportManager;
    
    
    public function __construct(TranslatorInterface $translator, 
          ExportManager $manager)
    {
        $this->translator = $translator;
        $this->exportManager = $manager;
    }
    
    public function getType()
    {
        return 'tabular';
    }
    
    public function getName()
    {
        return 'Comma separated values (CSV)';
    }
    
    /**
     * 
     * @uses appendAggregatorForm
     * @param FormBuilderInterface $builder
     * @param type $exportAlias
     * @param array $aggregatorAliases
     */
    public function buildForm(FormBuilderInterface $builder, $exportAlias, array $aggregatorAliases)
    {
        $aggregators = $this->exportManager->getAggregators($aggregatorAliases);
        $nb = count($aggregatorAliases);
        
        foreach ($aggregators as $alias => $aggregator) {
            $builderAggregator = $builder->create($alias, FormType::class, array(
               'label' => $aggregator->getTitle(),
               'block_name' => '_aggregator_placement_csv_formatter'
            ));
            $this->appendAggregatorForm($builderAggregator, $nb);
            $builder->add($builderAggregator);
        }
    }
    
    /**
     * append a form line by aggregator on the formatter form.
     * 
     * This form allow to choose the aggregator position (row or column) and 
     * the ordering
     * 
     * @param FormBuilderInterface $builder
     * @param string $nbAggregators
     */
    private function appendAggregatorForm(FormBuilderInterface $builder, $nbAggregators)
    {
        $builder->add('order', 'choice', array(
           'choices' => array_combine(
                 range(1, $nbAggregators),
                 range(1, $nbAggregators)
                 ),
           'multiple' => false,
           'expanded' => false
        ));
        
        $builder->add('position', 'choice', array(
           'choices' => array(
              'row' => 'r',
              'column' => 'c'
           ),
           'choices_as_values' => true,
           'multiple' => false,
           'expanded' => false
        ));
    }
    
    /**
     * 
     * @param mixed $result
     * @param mixed $data
     * @param \Chill\MainBundle\Export\ExportInterface $export
     * @param \Chill\MainBundle\Export\FilterInterface[] $filters
     * @param \Chill\MainBundle\Export\AggregatorInterface[] $aggregators
     */
    public function getResponse($result, $formatterData, $exportAlias, array $exportData, array $filtersData, 
            array $aggregatorsData)
    {
        $this->result = $result;
        $this->orderingHeaders($formatterData);
        $this->export = $this->exportManager->getExport($exportAlias);
        $this->aggregators = iterator_to_array($this->exportManager
                ->getAggregators(array_keys($aggregatorsData)));
        $this->exportData = $exportData;
        $this->aggregatorsData = $aggregatorsData;
        
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        //$response->headers->set('Content-Disposition','attachment; filename="export.csv"');

        $response->setContent($this->generateContent());
        
        return $response;
    }
    
    /**
     * ordering aggregators, preserving key association.
     * 
     * This function do not mind about position.
     * 
     * If two aggregators have the same order, the second given will be placed 
     * after.  This is not significant for the first ordering.
     * 
     * @param type $formatterData
     * @return type
     */
    protected function orderingHeaders($formatterData)
    {
        $this->formatterData = $formatterData;
        uasort($this->formatterData, function($a, $b) {
            
            return ($a['order'] <= $b['order'] ? -1 : 1);
        });
    }
    
    protected function generateContent()
    {
        $labels = $this->gatherLabels();
        $rowHeadersKeys = $this->getRowHeaders();
        $columnHeadersKeys = $this->getColumnHeaders();
        $resultsKeys = $this->export->getQueryKeys($this->exportData);
        print_r($columnHeadersKeys);
        
        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        //title
        fputcsv($output, array($this->translator->trans($this->export->getTitle())));
        //blank line
        fputcsv($output, array(""));
        
        //headers
        $headerLine = array();
        foreach($rowHeadersKeys as $headerKey) {
            $headerLine[] = array_key_exists('_header', $labels[$headerKey]) ? 
                    $labels[$headerKey]['_header'] : '';
        }
        foreach($resultsKeys as $key) {
            $headerLine[] = array_key_exists('_header', $labels[$key]) ? 
                    $labels[$key]['_header'] : '';
        }
        fputcsv($output, $headerLine);
        unset($headerLine); //free memory

        $content = array();
        // create an array with keys as columnHeadersKeys values, values are empty array
        $columnHeaders = array_combine($columnHeadersKeys, array_pad(array(), 
                count($columnHeadersKeys), array()));
        foreach($this->result as $row) { print_r($row);
            $line = array();
            //set horizontal headers
            foreach($rowHeadersKeys as $headerKey) {
                
                if (!array_key_exists($row[$headerKey], $labels[$headerKey])) {
                    throw new \LogicException("The value '".$row[$headerKey]."' "
                            . "is not available from the labels defined by aggregators. "
                            . "The key name was $headerKey");
                }
                
                $line[] = $labels[$headerKey][$row[$headerKey]];
            }
            
            foreach($columnHeadersKeys as $headerKey) {
                
            }
            //append result
            foreach($resultsKeys as $key) {
                $line[] = $labels[$key][$row[$key]];
            }
            // append to content
            $content[] = $line;
        }
        
        //ordering content
        //array_multisort($content);
        
        //generate CSV
        foreach($content as $line) {
            fputcsv($output, $line);
        }
        
        $text = stream_get_contents($output);
        fclose($output);
        
        return $text;
    }


    protected function getRowHeaders()
    {
        return $this->getPositionnalHeaders('r');
    }
    
    protected function getColumnHeaders()
    {
        return $this->getPositionnalHeaders('c');
    }
    
    /**
     * 
     * @param string $position may be 'c' (column) or 'r' (row)
     * @return string[]
     * @throws \RuntimeException
     */
    private function getPositionnalHeaders($position)
    {
        $headers = array();
        foreach($this->formatterData as $alias => $data) {
            if (!array_key_exists($alias, $this->aggregatorsData)) {
                throw new \RuntimeException("the formatter wants to use the "
                        . "aggregator with alias $alias, but the export do not "
                        . "contains data about it");
            }
            
            $aggregator = $this->aggregators[$alias];
            
            if ($data['position'] === $position) {
                $headers = array_merge($headers, $aggregator->getQueryKeys($this->aggregatorsData[$alias]));
            }
        }
        
        return $headers;
    }
    
    /**
     * 
     * @param mixed $result
     * @param \Chill\MainBundle\Export\AggregatorInterface[] $aggregators
     */
    protected function gatherLabels()
    {
        return array_merge(
                $this->gatherLabelsFromAggregators(),
                $this->gatherLabelsFromExport()
                );
    }
    
    protected function gatherLabelsFromAggregators()
    {
        $labels = array();
        /* @var $aggretator \Chill\MainBundle\Export\AggregatorInterface */
        foreach ($this->aggregators as $alias => $aggregator) {
            $keys = $aggregator->getQueryKeys($this->aggregatorsData[$alias]);
            
            // gather data in an array
            foreach($keys as $key) {
                $values = array_map(function($row) use ($key, $alias) {
                    if (!array_key_exists($key, $row)) {
                        throw new \LogicException("the key '".$key."' is declared by "
                                . "the aggregator with alias '".$alias."' but is not "
                                . "present in results");
                    }
                    
                    return $row[$key];
                }, $this->result);
                $labels[$key] = $aggregator->getLabels($key, array_unique($values), 
                        $this->aggregatorsData[$alias]);
            }
        }
        
        return $labels;
    }
    
    protected function gatherLabelsFromExport()
    {
        $labels = array();
        $export = $this->export;
        $keys = $this->export->getQueryKeys($this->exportData);
        
        foreach($keys as $key) {
            $values = array_map(function($row) use ($key, $export) { 
                    if (!array_key_exists($key, $row)) {
                        throw new \LogicException("the key '".$key."' is declared by "
                                . "the export with title '".$export->getTitle()."' but is not "
                                . "present in results");
                    }
                    
                    return $row[$key];
                }, $this->result);
            $labels[$key] = $this->export->getLabels($key, array_unique($values),
                    $this->exportData);
        }
        
        return $labels;
    }
    
}
