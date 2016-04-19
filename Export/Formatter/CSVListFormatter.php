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
     * @param mixed[] $data collected from the current form
     * @param \Chill\MainBundle\Export\ExportInterface $export the export which is executing
     * @param \Chill\MainBundle\Export\FilterInterface[] $filters the filters applying on the export. The key will be filters aliases, and the values will be filter's data (from their own form)
     * @param \Chill\MainBundle\Export\AggregatorInterface[] $aggregators the aggregators applying on the export. The key will be aggregators aliases, and the values will be aggregator's data (from their own form)
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
        $output = fopen('php://output', 'w');
        
        foreach ($result as $row) {
            //var_dump($row);
            fputcsv($output, $row);
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
}
