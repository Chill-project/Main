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

use Symfony\Component\HttpFoundation\Response;
use Chill\MainBundle\Export\FormatterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Chill\MainBundle\Export\ExportManager;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Create a CSV List for the export where the header are printed on the 
 * first column, and the result goes from left to right.
 *
 * @author Champs-Libres <info@champs-libres.coop>
 */
class CSVPivotedListFormatter implements FormatterInterface
{
    
    /**
     * This variable cache the labels internally
     *
     * @var string[]
     */
    protected $labelsCache = null;
    
    protected $result = null;
    
    protected $exportAlias = null;
    
    protected $exportData = null;
    
    protected $formatterData = null;
    
    /**
     *
     * @var ExportManager
     */
    protected $exportManager;
    
    /**
     *
     * @var TranslatorInterface
     */
    protected $translator;
    


    public function __construct(TranslatorInterface $translator, ExportManager $manager)
    {
        $this->translator = $translator;
        $this->exportManager = $manager;
    }
    
    public function getType()
    {
        return FormatterInterface::TYPE_LIST;
    }
    
    public function getName()
    {
        return 'CSV horizontal list';
    }
    
    /**
     * build a form, which will be used to collect data required for the execution
     * of this formatter.
     * 
     * @uses appendAggregatorForm
     * @param FormBuilderInterface $builder
     * @param type $exportAlias
     * @param array $aggregatorAliases
     */
    public function buildForm(
        FormBuilderInterface $builder, 
        $exportAlias, 
        array $aggregatorAliases
    ){
        $builder->add('numerotation', ChoiceType::class, array(
            'choices' => array(
                'yes' => true,
                'no'  => false
            ),
            'expanded' => true,
            'multiple' => false,
            'label' => "Add a number on first column",
            'choices_as_values' => true,
            'data' => true
        ));
    }
    
    /**
     * Generate a response from the data collected on differents ExportElementInterface
     * 
     * @param mixed[] $result The result, as given by the ExportInterface
     * @param mixed[] $formatterData collected from the current form
     * @param string $exportAlias the id of the current export
     * @param array $filtersData an array containing the filters data. The key are the filters id, and the value are the data
     * @param array $aggregatorsData an array containing the aggregators data. The key are the filters id, and the value are the data
     * @return \Symfony\Component\HttpFoundation\Response The response to be shown
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
        $this->exportAlias = $exportAlias;
        $this->exportData = $exportData;
        $this->formatterData = $formatterData;
        
        $output = fopen('php://output', 'w');
        
        $i = 1;
        $lines = array();
        $this->prepareHeaders($lines);
        
        foreach ($result as $row) {
            $j = 0;
            
            if ($this->formatterData['numerotation'] === true) {
                $lines[$j][] = $i;
                $j++;
            }
            
            foreach ($row as $key => $value) {
                $lines[$j][] = $this->getLabel($key, $value);
                $j++;
            }
            $i++;
        }
        
        //adding the lines to the csv output
        foreach($lines as $line) {
            fputcsv($output, $line);
        }
        
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition','attachment; filename="export.csv"');

        $response->setContent($csvContent);
        
        return $response;
    }
    
    /**
     * add the headers to lines array
     * 
     * @param array $lines the lines where the header will be added
     */
    protected function prepareHeaders(array &$lines)
    {
        $keys = $this->exportManager->getExport($this->exportAlias)->getQueryKeys($this->exportData);
        // we want to keep the order of the first row. So we will iterate on the first row of the results
        $first_row = count($this->result) > 0 ? $this->result[0] : array();
        $header_line = array();
        
        if ($this->formatterData['numerotation'] === true) {
                $lines[] = array($this->translator->trans('Number'));
        }
        
        foreach ($first_row as $key => $value) {
            $lines[] = array($this->getLabel($key, '_header'));
        }
    }
    
    /**
     * Give the label corresponding to the given key and value. 
     * 
     * @param string $key
     * @param string $value
     * @return string
     * @throws \LogicException if the label is not found
     */
    protected function getLabel($key, $value)
    {
        
        if ($this->labelsCache === null) { 
            $this->prepareCacheLabels();
        }
        
        return $this->labelsCache[$key]($value);
    }
    
    /**
     * Prepare the label cache which will be used by getLabel. This function
     * should be called only once in the generation lifecycle.
     */
    protected function prepareCacheLabels()
    {
        $export = $this->exportManager->getExport($this->exportAlias);
        $keys = $export->getQueryKeys($this->exportData);
        
        foreach($keys as $key) {
            // get an array with all values for this key if possible
            $values = \array_map(function ($v) use ($key) { return $v[$key]; }, $this->result);
            // store the label in the labelsCache property
            $this->labelsCache[$key] = $export->getLabels($key, $values, $this->exportData);
        }
    }
    
    
}
