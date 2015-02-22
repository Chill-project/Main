<?php

/*
 * Chill is a software for social workers
 * Copyright (C) 2014 Champs-Libres Coopérative <info@champs-libres.coop>
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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Chill\MainBundle\Entity\User;

/**
 * Description of SetPasswordCommand
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class SetPasswordCommand extends ContainerAwareCommand
{
    public function configure()
    {
        $this->setName('chill:user:set_password')
              ->setDescription('set a password to user')
              ->addArgument('username', InputArgument::REQUIRED, 'the user\'s '
                    . 'username you want to change password')
              ->addArgument('password', InputArgument::OPTIONAL, 'the new password')
              ;
    }
    
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->_getUser($input->getArgument('username'));
        
        if ($user === NULL) {
            throw new \LogicException("The user with username '".
                  $input->getArgument('username')."' is not found");
        }
        
        $password = $input->getArgument('password');
        if ($password === NULL) {
            $dialog = $this->getHelperSet()->get('dialog');
            $password = $dialog->askHiddenResponse($output, "<question>the new password :"
                  . "</question>");
        }
        
        $this->_setPassword($user, $password);
    }
    
    public function _getUser($username)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        return $em->getRepository('ChillMainBundle:User')
              ->findOneBy(array('username' => $username));
    }
    
    public function _setPassword(User $user, $password)
    {
        $encoder = $this->getContainer()->get('security.encoder_factory')
              ->getEncoder($user);
        $user->setPassword($encoder->encodePassword($password, $user->getSalt()));
        $this->getContainer()->get('doctrine.orm.entity_manager')
              ->flush($user);
    }
}
