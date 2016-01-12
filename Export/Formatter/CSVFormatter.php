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
    
    protected $filters;
    
    protected $aggregators;
    
    protected $exportData;
    
    protected $filterData;
    
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
    public function getResponse($result, $formatterData, ExportInterface $export, $filters, 
            $aggregators, $exportData, $filterData, $aggregatorsData)
    {
        $this->result = $result;
        $this->formatterData = $formatterData;
        $this->export = $export;
        $this->filters = $filters;
        $this->aggregators = $aggregators;
        $this->exportData = $exportData;
        $this->filterData = $filterData;
        $this->aggregatorsData = $aggregatorsData;
        
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        //$response->headers->set('Content-Disposition','attachment; filename="export.csv"');

        $response->setContent($this->generateContent());
        
        return $response;
    }
    
    protected function generateContent()
    {
        $labels = $this->gatherLabels();
        $horizontalHeadersKeys = $this->getHorizontalHeaders();
        $resultsKeys = $this->export->getQueryKeys($this->exportData);
        
        
        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        //title
        fputcsv($output, array($this->translator->trans($this->export->getTitle())));
        //blank line
        fputcsv($output, array(""));
        
        //headers
        $headerLine = array();
        foreach($horizontalHeadersKeys as $headerKey) {
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
        foreach($this->result as $row) {
            $line = array();
            //set horizontal headers
            foreach($horizontalHeadersKeys as $headerKey) {
                
                if (!array_key_exists($row[$headerKey], $labels[$headerKey])) {
                    throw new \LogicException("The value '".$row[$headerKey]."' "
                            . "is not available from the labels defined by aggregators. "
                            . "The key name was $headerKey");
                }
                
                $line[] = $labels[$headerKey][$row[$headerKey]];
            }
            //append result
            foreach($resultsKeys as $key) {
                $line[] = $labels[$key][$row[$key]];
            }
            // append to content
            $content[] = $line;
        }
        
        //ordering content
        array_multisort($content);
        
        //generate CSV
        foreach($content as $line) {
            fputcsv($output, $line);
        }
        
        $text = stream_get_contents($output);
        fclose($output);
        
        return $text;
    }


    protected function getHorizontalHeaders()
    {
        $headers = array();
        /* @var $aggregator AggregatorInterface */
        foreach($this->aggregators as $alias => $aggregator) {
            $headers = array_merge($headers, $aggregator->getQueryKeys($this->aggregatorsData[$alias]));
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
