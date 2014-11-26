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
use Symfony\Component\Console\Output\OutputInterface;

/*
 * Load or update the languages entities command
 */
class LoadAndUpdateLanguagesCommand extends ContainerAwareCommand
{
    /*
     * (non-PHPdoc)
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setName('chill:main:languages:populate')
            ->setDescription('Load or update languages in db. This command does not delete existing '.
                'languages, but will update names according to available languages');
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
            $langageDB = $em->getRepository('ChillMainBundle:Language')->find($code);

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
        }

        $em->flush();
    }
}
