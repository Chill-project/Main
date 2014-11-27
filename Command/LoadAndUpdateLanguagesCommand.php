<?php

/*
 * Copyright (C) 2014, Champs Libres Cooperative SCRLFS, <http://www.champs-libres.coop>
 *
 * This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
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
use Symfony\Component\Intl\Intl;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * Load or update the languages entities command
 */
class LoadAndUpdateLanguagesCommand extends ContainerAwareCommand
{
    // The regional version of language are language with _ in the code
    // This array contains regional code to not exclude
    private $regionalVersionToInclude = ["ro_MD"];

    // Array of ancien languages (to exclude)
    private $ancientToExclude = ["ang", "egy", "fro", "goh", "grc", "la", "non", "peo", "pro", "sga",
        "dum", "enm", "frm", "gmh", "mga", "akk", "phn", "zxx", "got", "und"];

    const INCLUDE_REGIONAL_VERSION = 'include_regional';
    const INCLUDE_ANCIENT = 'include_ancient';

    /*
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('chill:main:languages:populate')
            ->setDescription('Load or update languages in db. This command does not delete existing '.
                'languages, but will update names according to available languages')
            ->addOption(
                    self::INCLUDE_REGIONAL_VERSION,
                    null,
                    InputOption::VALUE_NONE,
                    'Include the regional languages. The regional languages are languages with code containing _ excepted '
                        . implode(',', $this->regionalVersionToInclude) . '.')
            ->addOption(
                    self::INCLUDE_ANCIENT,
                    null,
                    InputOption::VALUE_NONE,
                    'Include the ancient languages that are languages with code '
                        . implode(', ', $this->ancientToExclude) . '.')
            ;
    }
    
    /*
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $chillAvailableLanguages = $this->getContainer()->getParameter('chill_main.available_languages');
        $languageBundle = Intl::getLanguageBundle();
        $languages = array();

        foreach ($chillAvailableLanguages as $avLang) {
            $languages[$avLang] = $languageBundle->getLanguageNames($avLang);
        }

        $languageCodes = array_keys($languages[$chillAvailableLanguages[0]]);

        foreach ($languageCodes as $code) {
            $excludeCode = (
                (
                    ! $input->getOption(self::INCLUDE_REGIONAL_VERSION)
                    and strpos($code, '_')
                    and !in_array($code, $this->regionalVersionToInclude)
                ) or (
                    ! $input->getOption(self::INCLUDE_ANCIENT)
                    and in_array($code, $this->ancientToExclude)
                )
            );

            $langageDB = $em->getRepository('ChillMainBundle:Language')->find($code);

            if(! $excludeCode) {
                if (! $langageDB) {
                    $langageDB = new \Chill\MainBundle\Entity\Language();
                    $langageDB->setId($code);
                    $em->persist($langageDB);
                }

                $avLangNames = array();

                foreach ($chillAvailableLanguages as $avLang) {
                    $avLangNames[$avLang] = $languages[$avLang][$code];
                }

                $langageDB->setName($avLangNames);
            } else {
                if($langageDB) {
                    $em->remove($langageDB);
                }
                echo "Code excluded : ".$code." - ".$languageBundle->getLanguageName($code)."\n";
            }
        }

        $em->flush();
    }
}
