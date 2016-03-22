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

namespace Chill\MainBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Filesystem;
use Chill\MainBundle\Entity\PostalCode;

/**
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class LoadPostalCodesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('chill:main:postal-code:populate')
                ->setDescription("Add the postal code from a csv file.")
                ->setHelp("This script will try to avoid existing postal code "
                        . "using the postal code and name. \n"
                        . "The CSV file must have the following columns: "
                        . "postal code, label, country code."
                        . "The CSV file should not have any header row.")
                ->addArgument('csv_file', InputArgument::REQUIRED, "the path to "
                        . "the csv file. See the help for specifications.")
                ->addOption(
                        'delimiter', 
                        'd', 
                        InputOption::VALUE_OPTIONAL, 
                        "The delimiter character of the csv file", 
                        ",")
                ->addOption(
                        'enclosure',
                        null,
                        InputOption::VALUE_OPTIONAL,
                        "The enclosure character of the csv file",
                        '"'
                        )
                ->addOption(
                        'escape',
                        null,
                        InputOption::VALUE_OPTIONAL,
                        "The escape character of the csv file",
                        "\\"
                        )
                ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $csv = $this->getCSVResource($input);
        } catch (\RuntimeException $e) {
            $output->writeln('<error>Error during opening the csv file : '.
                    $e->getMessage().'</error>');
        }
        
        if ($output->getVerbosity() === OutputInterface::VERBOSITY_VERY_VERBOSE) {
            $output->writeln('The content of the file is ...');
            $output->write(file_get_contents($input->getArgument('csv_file')));
        }
        
        $num = 0;
        $line = 0;
        
        while (($row = fgetcsv(
                $csv, 
                0, 
                $input->getOption('delimiter'), 
                $input->getOption('enclosure'), 
                $input->getOption('escape'))) !== false) {
            
            try{
                $this->addPostalCode($row, $output);
                $num++;
            } catch (ExistingPostalCodeException $ex) {
                $output->writeln('<warning> on line '.$line.' : '.$ex->getMessage().'</warning>');
            } catch (CountryCodeNotFoundException $ex) {
                $output->writeln('<warning> on line '.$line.' : '.$ex->getMessage().'</warning>');
            } catch (PostalCodeNotValidException $ex) {
                $output->writeln('<warning> on line '.$line.' : '.$ex->getMessage().'</warning>');
            }
            $line ++;
        }
        
        $this->getContainer()->get('doctrine.orm.entity_manager')->flush();
        
        $output->writeln('<info>'.$num.' were added !</info>');
    }
    
    private function getCSVResource(InputInterface $input)
    {
        $fs = new Filesystem();
        $filename = $input->getArgument('csv_file');
        
        if (!$fs->exists($filename)) {
            throw new \RuntimeException("The file does not exists or you do not "
                    . "have the right to read it.");
        }
        
        $resource = fopen($filename, 'r');
        
        if ($resource == FALSE) {
            throw new \RuntimeException("The file '$filename' could not be opened.");
        }
        
        return $resource;
    }
    
    private function addPostalCode($row, OutputInterface $output)
    {
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln('handling row: '. $row[0].' | '. $row[1].' | '. $row[2]);
        }
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $country = $em
                ->getRepository('ChillMainBundle:Country')
                ->findOneBy(array('countryCode' => $row[2]));
        
        if ($country === NULL) {
            throw new CountryCodeNotFoundException(sprintf("The country with code %s is not found. Aborting to insert postal code with %s - %s",
                    $row[2], $row[0], $row[1]));
        }
        
        // try to find an existing postal code
        $existingPC = $em
                ->getRepository('ChillMainBundle:PostalCode')
                ->findBy(array('code' => $row[0], 'name' => $row[1]));
        
        if (count($existingPC) > 0) {
            throw new ExistingPostalCodeException(sprintf("A postal code with code : %s and name : %s already exists, skipping", 
                    $row[0], $row[1]));
        }
        
        $postalCode = (new PostalCode())
                ->setCode($row[0])
                ->setName($row[1])
                ->setCountry($country)
                ;
        
        $errors = $this->getContainer()->get('validator')->validate($postalCode);
        
        if ($errors->count() == 0) {
            $em->persist($postalCode);
        } else {
            $msg = "";
            foreach ($errors as $error) {
                $msg .= " ".$error->getMessage();
            }
            
            throw new PostalCodeNotValidException($msg);
        }
        
        
        
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $output->writeln(sprintf('Creating postal code with code: %s, name: %s, countryCode: %s',
                    $postalCode->getCode(), $postalCode->getName(), $postalCode->getCountry()->getCountryCode()));
        }
    }
}


class ExistingPostalCodeException extends \Exception
{
    
}

class CountryCodeNotFoundException extends \Exception
{
    
}

class PostalCodeNotValidException extends \Exception
{
    
}
