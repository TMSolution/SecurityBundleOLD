<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Core\SecurityBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Doctrine\ORM\EntityNotFoundException;

class UpdateObjectsIdentitiesCommand extends ContainerAwareCommand
{



    protected function configure()
    {
        $this
                ->setName('tmsolution:updateobjectsidentities')
                ->setDescription('Update objects identities');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectModel = $this->getContainer()->get('model_factory')->getModel('Core\SecurityBundle\Entity\ObjectIdentity');
        $result=$objectModel->updateObjectIdentityList();

        $output->writeln("<info>Dodano klasy:</info>\r\n\r\n".implode("\r\n",$result));
    }

}
