<?php

namespace Core\SecurityBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Parser as YMLParser;
use Symfony\Component\Yaml\Exception\ParseException as YMLParseException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity as SecurityObjectIdentity;
use Exception;
use ReflectionClass;
use DomainException;

/**
 * Insert entries into db
 */
class SecurityRightCommand extends ContainerAwareCommand
{
    
    /**
     * Configures this command
     */
    protected function configure()
    {
        $this
            ->setName('tmsolution:generate:rights')
            ->addOption(
                'force',
                '-F',
                InputOption::VALUE_NONE,
                'Delete old application'
            );
    }
    
    /**
     * Executes this command
     * 
     * @param Symfony\Component\Console\Input\InputInterface $input
     * @param Symfony\Component\Console\Output\OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {

        $io = new SymfonyStyle($input, $output);           
        $io->title('Application Security Right System');
        $io->block('You\'re going to initialize application right system');
        
        $appName = $io->ask(
            'Type application name',
            'app',
            function($name) { return $name; }
        );
        
        try {
            $classMapper = $this->getContainer()->get('classmapperservice');
        } catch(ServiceNotFoundException $ex) {
            $io->error('Service \'classmapperservice\' was not found.');
            return;
        }
        
        $tokens = [];
        $rows = [];
        foreach ($classMapper->getEntityNames('en') as $entityName) {
            $tokens[] = sprintf('%s_%s', $appName, $entityName);
            $rows[] = [
                $appName,
                $entityName,                                
                $tokens[count($tokens) -1]
            ];
        }
        if ($input->getOption('verbose')) {
            $io->table(['Application', 'Entity', 'Token'], $rows);
        }

        if (count($tokens) == 0) {
            $io->warning('There\'s nothing to generate');
            return;
        }
        
        $confirm = $io->confirm(
            sprintf(
                'Confirm initialization of application \'%s\'',
                $appName
            ),
            true
        );
        
        if ($confirm == false) {
            $io->note('Nothing done, bye bye!');
            return;
        }
        
        try {        
            $this->startCommand();
            $this->insertACL(
                $this->insertApplicationTokens(
                    $appName,
                    $tokens,
                    $input->getOption('force')
                )
            );
            $this->insertRoleRights();            
            $this->endCommand();            

            $io->success(
                sprintf(
                    'Application \'%s\' successfully initialized',
                    $appName
                )
            );
        
        } catch(Exception $ex) {
            $this->getContainer()->get('doctrine')->getConnection()->rollback();
            $io->error(
                sprintf(
                    'Exception %s was thrown with the message \'%s\'',
                    (new ReflectionClass($ex))->getName(),
                    $ex->getMessage()
                )
            );
        }
    }
    
    
    /**
     * Begin transaction
     */
    private function startCommand()
    {
        $this->getContainer()->get('doctrine')->getManager()->beginTransaction();
    }
    
    /**
     * Commit the transaction
     */
    private function endCommand()
    {
        $this->getContainer()->get('doctrine')->getManager()->getConnection()->commit();        
    }

    /**
     * Insert application and right tokens
     * 
     * @param string $appName
     * @param array $tokens
     * @param bool $force
     * @throws DomainException
     */
    private function insertApplicationTokens($appName, array $tokens) 
    {
        $identityModel = $this->getContainer()->get('model_factory')->getModel(
            'Core\SecurityBundle\Entity\ObjectIdentity'
        );        
        $identityTypeModel = $this->getContainer()->get('model_factory')->getModel(
            'Core\SecurityBundle\Entity\ObjectIdentityType'
        );
        
        if ($identityTypeModel->hasOneBy(['name' => $appName])) {
            throw new DomainException(
                sprintf(
                    'The application named \'%s\' exists!',
                    $appName
                )
            );
        }
        
        $objectIdentityType = $identityTypeModel->getEntity()->setName($appName);       
        $this->getContainer()->get('doctrine')->getManager()->persist(
            $objectIdentityType
        );
        
        $identities = [];
        foreach($tokens as $token) {
            $identity = $identityModel->getEntity();
            $identity->setIsBusinessObject(true);
            $identity->setName($token);
            $identity->setDisplayName($token);
            $identity->setObjectIdentityType($objectIdentityType);            
            $identities[] = $identity;
            $this->getContainer()->get('doctrine')->getManager()->persist($identity);
        }        
        
        $this->getContainer()->get('doctrine')->getManager()->flush();
        return $identities;
    }
    
    /**
     * Insert role rights
     */
    private function insertRoleRights()
    {
        $roleRightTable = $this->getRoleRightTable();        
        $identityModel = $this->getContainer()->get('model_factory')->getModel(
            'Core\SecurityBundle\Entity\ObjectIdentity'
        );
        $roleModel = $this->getContainer()->get('model_factory')->getModel(
            'Core\SecurityBundle\Entity\Role'
        );        
        $rightModel = $this->getContainer()->get('model_factory')->getModel(
            'Core\SecurityBundle\Entity\Right'
        );
        
        foreach ($roleRightTable as $roleName => $tokens) {
            $role = $roleModel->findOneBy(['technicalName' => $roleName]);
            foreach ($tokens as $name => $rightToken) {
                $identity = $identityModel->findOneBy(['name' => $name]);
                $right = $rightModel->getEntity();
                $right->setRole($role);
                $right->setObjectIdentity($identity);
                if ($rightToken == 'MASTER') {
                    $right->setMasterRight(true);
                    $right->setEditRight(false);                    
                    $right->setViewRight(false);                    
                } elseif ($rightToken == 'EDIT') {
                    $right->setMasterRight(false);
                    $right->setEditRight(true);                    
                    $right->setViewRight(false);                    
                } elseif ($rightToken == 'VIEW') {
                    $right->setMasterRight(false);
                    $right->setEditRight(false);                    
                    $right->setViewRight(true);                                        
                } else {
                    var_dump($rightToken);
                    throw new YMLParseException(
                        sprintf(
                            'Valid right tokens are \'%s\' \'%s\' \'%s\'',
                            'VIEW',
                            'EDIT',
                            'MASTER'                            
                        )
                    );
                }
                $this->getContainer()->get('doctrine')->getManager()->persist(
                    $right
                );
            }            
        }
          
        $this->getContainer()->get('doctrine')->getManager()->flush();        
    }
        
    private function insertACL(array $identities)
    {
        foreach ($identities as $identity) {
            $this->getContainer()->get('security.acl.provider')->createAcl(
                    new SecurityObjectIdentity(
                    $identity->getName(),
                    $identity->getObjectIdentityType()->getName()
                )
            );        
        }
    }
    /**
     * Read role rights from the configuration
     * 
     * @return array role right table
     */
    private function getRoleRightTable()
    {
        return (new YMLParser())->parse(
            file_get_contents(
                sprintf(
                    '%s%sconfig%s%s',
                    $this->getApplication()->getKernel()->getRootDir(),
                    DIRECTORY_SEPARATOR,
                    DIRECTORY_SEPARATOR,
                    'role_right.yml'
                )
            )
        );
    }
    
}
