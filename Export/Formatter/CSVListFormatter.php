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
 * Create a CSV List for the export
 *
 * @author Champs-Libres <info@champs-libres.coop>
 */
class CSVListFormatter implements FormatterInterface
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
        return FormatterInterface::TYPE_CSV_LIST;
    }
    
    public function getName()
    {
        return 'CSV List';
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
        // do nothing
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
        
        $output = fopen('php://output', 'w');
        
        $this->prepareHeaders($output);
        
        foreach ($result as $row) {
            $line = array();
            foreach ($row as $key => $value) {
                $line[] = $this->getLabel($key, $value);
            }
            fputcsv($output, $line);
        }
        
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        //$response->headers->set('Content-Disposition','attachment; filename="export.csv"');

        $response->setContent($csvContent);
        
        return $response;
    }
    
    protected function prepareHeaders($output)
    {
        $keys = $this->exportManager->getExport($this->exportAlias)->getQueryKeys($this->exportData);
        // we want to keep the order of the first row. So we will iterate on the first row of the results
        $first_row = count($this->result) > 0 ? $this->result[0] : array();
        $header_line = array();
        
        foreach ($first_row as $key => $value) {
            $header_line[] = $this->getLabel($key, '_header');
        }
        
        if (count($header_line) > 0) {
            fputcsv($output, $header_line);
        }
    }
    
    protected function getLabel($key, $value)
    {
        
        if ($this->labelsCache === null) { 
            $this->prepareLabels();
        } 
        
        if (!isset($this->labelsCache[$key][$value])) {
            throw new \LogicException("The label for key $key and value $value was not given "
                    . "by the export, aggregator or filter responsible for this key.");
        }
        
        return $this->labelsCache[$key][$value];
    }
    
    protected function prepareLabels()
    {
        $export = $this->exportManager->getExport($this->exportAlias);
        $keys = $export->getQueryKeys($this->exportData);
        
        foreach($keys as $key) {
            // get an array with all values for this key
            $values = array_unique(array_map(function ($v) use ($key) { return $v[$key]; }, $this->result));
            // store the label in the labelsCache property
            $this->labelsCache[$key] = $export->getLabels($key, $values, $this->exportData);
        }
    }
    
    
}
