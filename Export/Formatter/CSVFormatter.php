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
    
    protected $labels;
    
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
    public function getResponse(
          $result, 
          $formatterData, 
          $exportAlias, 
          array $exportData, 
          array $filtersData, 
          array $aggregatorsData
    ) {
        $this->result = $result;
        $this->orderingHeaders($formatterData);
        $this->export = $this->exportManager->getExport($exportAlias);
        $this->aggregators = iterator_to_array($this->exportManager
                ->getAggregators(array_keys($aggregatorsData)));
        $this->exportData = $exportData;
        $this->aggregatorsData = $aggregatorsData;
        $this->labels = $this->gatherLabels();
        
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
        $rowKeysNb = count($this->getRowHeaders());
        $columnKeysNb = count($this->getColumnHeaders());
        $resultsKeysNb = count($this->export->getQueryKeys($this->exportData));
        $results = $this->getOrderedResults();
        /* @var $columnHeaders string[] the column headers associations */
        $columnHeaders = array();
        /* @var $data string[] the data of the csv file */
        $contentData = array();
        $content = array();
        
        function findColumnPosition(&$columnHeaders, $columnToFind) {
            $i = 0;
            foreach($columnHeaders as $set) {
                if ($set === $columnToFind) {
                    return $i;
                }
                $i++;
            }
            
            //we didn't find it, adding the column
            $columnHeaders[] = $columnToFind;
            
            return $i++;
        }
        
        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        //title
        fputcsv($output, array($this->translator->trans($this->export->getTitle())));
        //blank line
        fputcsv($output, array(""));
        
        // iterate on result to : 1. populate row headers, 2. populate column headers, 3. add result
        foreach ($results as $row) {
            $rowHeaders = array_slice($row, 0, $rowKeysNb);
            
            //first line : we create line and adding them row headers
            if (!isset($line)) {
                $line = array_slice($row, 0, $rowKeysNb);
            }

            // do we have to create a new line ? if the rows are equals, continue on the line, else create a next line
            if (array_slice($line, 0, $rowKeysNb) !== $rowHeaders) {
                $contentData[] = $line;
                $line = array_slice($row, 0, $rowKeysNb);
            }

            // add the column headers
            /* @var $columns string[] the column for this row */
            $columns = array_slice($row, $rowKeysNb, $columnKeysNb);
            $columnPosition = findColumnPosition($columnHeaders, $columns);
            
            //fill with blank at the position given by the columnPosition + nbRowHeaders
            for ($i=0; $i < $columnPosition; $i++) {
                if (!isset($line[$rowKeysNb + $i])) {
                    $line[$rowKeysNb + $i] = "";
                }
            }
            
            $resultData = array_slice($row, $resultsKeysNb*-1);
            foreach($resultData as $data) {
                $line[] = $data;
            }
            
        }
        
        // we add  the last line
        $contentData[] = $line;
        
        //column title headers
        for ($i=0; $i < $columnKeysNb; $i++) {
            $line = array_fill(0, $rowKeysNb, '');
                
            foreach($columnHeaders as $set) {
                $line[] = $set[$i];
            }
            
            $content[] = $line;
        }
        
        
        //row title headers
        $headerLine = array();
        foreach($this->getRowHeaders() as $headerKey) {
            $headerLine[] = array_key_exists('_header', $this->labels[$headerKey]) ? 
                    $this->labels[$headerKey]['_header'] : '';
        }
        foreach($this->export->getQueryKeys($this->exportData) as $key) {
            $headerLine[] = array_key_exists('_header', $this->labels[$key]) ? 
                    $this->labels[$key]['_header'] : '';
        }
        fputcsv($output, $headerLine);
        unset($headerLine); //free memory
        
        //generate CSV
        foreach($content as $line) {
            fputcsv($output, $line);
        }
        foreach($contentData as $line) {
            fputcsv($output, $line);
        }
        
        $text = stream_get_contents($output);
        fclose($output);
        
        return $text;
    }
    
    
    private function getOrderedResults()
    {
        $r = array();
        $results = $this->result;
        $labels = $this->labels;
        $rowKeys = $this->getRowHeaders();
        $columnKeys = $this->getColumnHeaders();
        $resultsKeys = $this->export->getQueryKeys($this->exportData);
        $headers = array_merge($rowKeys, $columnKeys);
        
        foreach ($results as $row) {
            $line = array();
            foreach ($headers as $key) {
                if (!array_key_exists($row[$key], $labels[$key])) {
                    throw new \LogicException("The value '".$row[$key]."' "
                            . "is not available from the labels defined by aggregator or report. "
                            . "The key provided by aggregator or report is '$key'");
                }
                
                $line[] = $labels[$key][$row[$key]];
            }
            
            //append result
            foreach ($resultsKeys as $key) {
                $line[] = $labels[$key][$row[$key]];
            }
            
            $r[] = $line;
        }
        
        array_multisort($r);
        
        return $r;
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
