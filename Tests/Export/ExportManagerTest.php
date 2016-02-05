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

namespace Chill\MainBundle\Tests\Export;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Chill\MainBundle\Export\ExportManager;
use Symfony\Component\Security\Core\Role\Role;
use Chill\MainBundle\Export\AggregatorInterface;
use Chill\MainBundle\Export\FilterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Chill\MainBundle\Export\ExportInterface;
use Prophecy\Argument;
use Doctrine\ORM\QueryBuilder;
use Chill\MainBundle\Form\Type\Export\ExportType;
use Symfony\Component\HttpFoundation\Response;
use Chill\MainBundle\Form\Type\Export\PickCenterType;

/**
 * Test the export manager
 * 
 *
 * @author Julien Fastré <julien.fastre@champs-libres.coop>
 */
class ExportManagerTest extends KernelTestCase
{
    
    use \Chill\MainBundle\Test\PrepareCenterTrait;
    use \Chill\MainBundle\Test\PrepareUserTrait;
    use \Chill\MainBundle\Test\PrepareScopeTrait;
    
    /**
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;
    
    /**
     *
     * @var Prophecy\Prophet 
     */
    private $prophet;
    
    
    
    
    
    public function setUp()
    {
        self::bootKernel();
        
        $this->container = self::$kernel->getContainer();
        
        $this->prophet = new \Prophecy\Prophet;
    }
    
    public function tearDown()
    {
        $this->prophet->checkPredictions();
    }
    
    /**
     *  Create an ExportManager where every element may be replaced by a double. 
     * 
     * If null is provided for an element, this is replaced by the equivalent
     * from the container; if the user provided is null, this is replaced by the
     * user 'center a_social' from database.
     * 
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationChecker $authorizationChecker
     * @param \Chill\MainBundle\Security\Authorization\AuthorizationHelper $authorizationHelper
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @return ExportManager
     */
    protected function createExportManager(
            \Psr\Log\LoggerInterface $logger = null,
            \Doctrine\ORM\EntityManagerInterface $em = null,
            \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker = null,
            \Chill\MainBundle\Security\Authorization\AuthorizationHelper $authorizationHelper = null,
            \Symfony\Component\Security\Core\User\UserInterface $user = null
            ) 
    {
        $localUser = $user === NULL ?  $this->container->get('doctrine.orm.entity_manager')
                ->getRepository('ChillMainBundle:User')
                ->findOneBy(array('username' => 'center a_social')) : 
            $user;
        $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken($localUser, 'password', 'provider');
        $tokenStorage = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage();
        $tokenStorage->setToken($token);
        
        return new ExportManager(
                $logger === NULL ? $this->container->get('logger') : $logger, 
                $em === NULL ? $this->container->get('doctrine.orm.entity_manager') : $em, 
                $authorizationChecker === NULL ? $this->container->get('security.authorization_checker') : $authorizationChecker,
                $authorizationHelper === NULL ? $this->container->get('chill.main.security.authorization.helper') : $authorizationHelper,
                $tokenStorage)
            ;
    }
    
    
    public function testGetExportsWithoutGranting()
    {
        $exportManager = $this->createExportManager();
        
        //create an export and add it to ExportManager
        $export = $this->prophet->prophesize();
        $export->willImplement(ExportInterface::class);
        $exportManager->addExport($export->reveal(), 'dummy');
        
        $exports = iterator_to_array($exportManager->getExports(false));

        $this->assertGreaterThan(0, count($exports));
        $this->assertContains($export->reveal(), $exports);
        $this->assertContains('dummy', array_keys($exports));
    }
    
    public function testGetExistingExportsTypes()
    {
        $exportManager = $this->createExportManager();
        
        //create an export and add it to ExportManager
        $export = $this->prophet->prophesize();
        $export->willImplement(ExportInterface::class);
        $export->getType()->willReturn('my_type');
        $exportManager->addExport($export->reveal(), 'dummy');
        
        $this->assertContains('my_type', $exportManager->getExistingExportsTypes());
        
    }
    
    public function testGetExport()
    {
        $exportManager = $this->createExportManager();
        
        //create an export and add it to ExportManager
        $export = $this->prophet->prophesize();
        $export->willImplement(ExportInterface::class);
        $exportManager->addExport($export->reveal(), 'dummy');
        
        $obtained = $exportManager->getExport('dummy');

        $this->assertInstanceof(ExportInterface::class, $obtained);
    }
    
    /**
     * @expectedException \RuntimeException
     */
    public function testGetExportNonExistant()
    {
        $exportManager = $this->createExportManager();
        
        $exportManager->getExport('non existing');
    }
    
    public function testGetFilter()
    {
        $exportManager = $this->createExportManager();
        
        //create a filter and add it to ExportManager
        $filter = $this->prophet->prophesize();
        $filter->willImplement('Chill\MainBundle\Export\FilterInterface');
        $exportManager->addFilter($filter->reveal(), 'dummy');
        
        $obtained = $exportManager->getFilter('dummy');

        $this->assertInstanceof('Chill\MainBundle\Export\FilterInterface', $obtained);
    }
    
    
    /**
     * @expectedException \RuntimeException
     */
    public function testGetFilterNonExistant()
    {
        $exportManager = $this->createExportManager();
        
        $exportManager->getFilter('non existing');
    }
    
    public function testGetFilters()
    {
        $exportManager = $this->createExportManager();
        
        //create three filters and add them to ExportManager
        $filterFoo = $this->prophet->prophesize();
        $filterFoo->willImplement('Chill\MainBundle\Export\FilterInterface');
        $filterBar = $this->prophet->prophesize();
        $filterBar->willImplement('Chill\MainBundle\Export\FilterInterface');
        $filterFooBar = $this->prophet->prophesize();
        $filterFooBar->willImplement('Chill\MainBundle\Export\FilterInterface');
        $exportManager->addFilter($filterFoo->reveal(), 'foo');
        $exportManager->addFilter($filterBar->reveal(), 'bar');
        $exportManager->addFilter($filterFooBar->reveal(), 'foobar');
        
        $obtained = iterator_to_array($exportManager->getFilters(array('foo', 'bar')));

        $this->assertContains($filterBar->reveal(), $obtained);
        $this->assertContains($filterFoo->reveal(), $obtained);
        $this->assertNotContains($filterFooBar->reveal(), $obtained);
    }
    
    public function testGetAggregator()
    {
        $exportManager = $this->createExportManager();
        
        //create a filter and add it to ExportManager
        $agg = $this->prophet->prophesize();
        $agg->willImplement('Chill\MainBundle\Export\AggregatorInterface');
        $exportManager->addAggregator($agg->reveal(), 'dummy');
        
        $obtained = $exportManager->getAggregator('dummy');

        $this->assertInstanceof('Chill\MainBundle\Export\AggregatorInterface', $obtained);
    }
    
    
    /**
     * @expectedException \RuntimeException
     */
    public function testGetAggregatorNonExistant()
    {
        $exportManager = $this->createExportManager();
        
        $exportManager->getAggregator('non existing');
    }
    
    public function testGetAggregators()
    {
        $exportManager = $this->createExportManager();
        
        //create three filters and add them to ExportManager
        $aggFoo = $this->prophet->prophesize();
        $aggFoo->willImplement('Chill\MainBundle\Export\AggregatorInterface');
        $aggBar = $this->prophet->prophesize();
        $aggBar->willImplement('Chill\MainBundle\Export\AggregatorInterface');
        $aggFooBar = $this->prophet->prophesize();
        $aggFooBar->willImplement('Chill\MainBundle\Export\AggregatorInterface');
        $exportManager->addAggregator($aggFoo->reveal(), 'foo');
        $exportManager->addAggregator($aggBar->reveal(), 'bar');
        $exportManager->addAggregator($aggFooBar->reveal(), 'foobar');
        
        $obtained = iterator_to_array($exportManager->getAggregators(array('foo', 'bar')));

        $this->assertContains($aggBar->reveal(), $obtained);
        $this->assertContains($aggFoo->reveal(), $obtained);
        $this->assertNotContains($aggFooBar->reveal(), $obtained);
    }
    
    public function testGetFormatter()
    {
        $exportManager = $this->createExportManager();
        
        //create a formatter
        $formatter = $this->prophet->prophesize();
        $formatter->willImplement('Chill\MainBundle\Export\FormatterInterface');
        $exportManager->addFormatter($formatter->reveal(), 'dummy');
        
        $obtained = $exportManager->getFormatter('dummy');
        
        $this->assertInstanceOf('Chill\MainBundle\Export\FormatterInterface', $obtained);
    }
    
    /**
     * @expectedException \RuntimeException
     */
    public function testNonExistingFormatter()
    {
        $exportManager = $this->createExportManager();
        
        $exportManager->getFormatter('non existing');
    }
    
    public function testFormattersByTypes()
    {
        $exportManager = $this->createExportManager();
        
        //create a formatter
        $formatterFoo = $this->prophet->prophesize();
        $formatterFoo->willImplement('Chill\MainBundle\Export\FormatterInterface');
        $formatterFoo->getType()->willReturn('foo');
        $formatterBar = $this->prophet->prophesize();
        $formatterBar->willImplement('Chill\MainBundle\Export\FormatterInterface');
        $formatterBar->getType()->willReturn('bar');
        $exportManager->addFormatter($formatterFoo->reveal(), 'foo');
        $exportManager->addFormatter($formatterBar->reveal(), 'bar');
        
        $obtained = $exportManager->getFormattersByTypes(array('foo'));
        
        $this->assertContains($formatterFoo->reveal(), $obtained);
        $this->assertNotContains($formatterBar->reveal(), $obtained);
    }
    
    public function testIsGrantedForElementWithExportAndUserIsGranted()
    {
        $center = $this->prepareCenter(100, 'center A');
        $user = $this->prepareUser(array());
        
        $authorizationChecker = $this->prophet->prophesize();
        $authorizationChecker->willImplement('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        $authorizationChecker->isGranted('CHILL_STAT_DUMMY', $center)
                ->willReturn(True);
        
        $exportManager = $this->createExportManager(null, null, 
                $authorizationChecker->reveal(), null, $user);
        
        $export = $this->prophet->prophesize();
        $export->willImplement(ExportInterface::class);
        $export->requiredRole()->willReturn(new Role('CHILL_STAT_DUMMY'));
        
        $result = $exportManager->isGrantedForElement($export->reveal(), null, array($center));
        
        $this->assertTrue($result);
        
    }
    
    public function testIsGrantedForElementWithExportAndUserIsGrantedNotForAllCenters()
    {
        $center = $this->prepareCenter(100, 'center A');
        $centerB = $this->prepareCenter(102, 'center B');
        $user = $this->prepareUser(array());
        
        $authorizationChecker = $this->prophet->prophesize();
        $authorizationChecker->willImplement('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
        $authorizationChecker->isGranted('CHILL_STAT_DUMMY', $center)
                ->willReturn(true);
        $authorizationChecker->isGranted('CHILL_STAT_DUMMY', $centerB)
                ->willReturn(false);
        
        $exportManager = $this->createExportManager(null, null, 
                $authorizationChecker->reveal(), null, $user);
        
        $export = $this->prophet->prophesize();
        $export->willImplement(ExportInterface::class);
        $export->requiredRole()->willReturn(new Role('CHILL_STAT_DUMMY'));
        
        $result = $exportManager->isGrantedForElement($export->reveal(), null, array($center, $centerB));
        
        $this->assertFalse($result);
        
    }
    
    public function testIsGrantedForElementWithExportEmptyCenters()
    {
        $user = $this->prepareUser(array());
        
        $exportManager = $this->createExportManager(null, null, 
                null, null, $user);
        
        $export = $this->prophet->prophesize();
        $export->willImplement(\Chill\MainBundle\Export\ExportInterface::class);
        $export->requiredRole()->willReturn(new Role('CHILL_STAT_DUMMY'));
        
        $result = $exportManager->isGrantedForElement($export->reveal(), null, array());
        
        $this->assertFalse($result);
        
    }
    
    public function testIsGrantedForElementWithModifierFallbackToExport()
    {
        $center = $this->prepareCenter(100, 'center A');
        $centerB = $this->prepareCenter(102, 'center B');
        $user = $this->prepareUser(array());
        
        $authorizationChecker = $this->prophet->prophesize();
        $authorizationChecker->willImplement(AuthorizationCheckerInterface::class);
        $authorizationChecker->isGranted('CHILL_STAT_DUMMY', $center)
                ->willReturn(true);
        $authorizationChecker->isGranted('CHILL_STAT_DUMMY', $centerB)
                ->willReturn(false);
        
        $exportManager = $this->createExportManager(null, null, 
                $authorizationChecker->reveal(), null, $user);
        
        $modifier = $this->prophet->prophesize();
        $modifier->willImplement(\Chill\MainBundle\Export\ModifierInterface::class);
        $modifier->addRole()->willReturn(NULL);
        
        $export = $this->prophet->prophesize();
        $export->willImplement(ExportInterface::class);
        $export->requiredRole()->willReturn(new Role('CHILL_STAT_DUMMY'));
        
        $result = $exportManager->isGrantedForElement($modifier->reveal(), 
                $export->reveal(), array($center, $centerB));
        
        $this->assertFalse($result);
        
    }
    
    public function testAggregatorsApplyingOn()
    {
        $center = $this->prepareCenter(100, 'center');
        $centers = array($center);
        $user = $this->prepareUser(array());
        
        $authorizationChecker = $this->prophet->prophesize();
        $authorizationChecker->willImplement(AuthorizationCheckerInterface::class);
        $authorizationChecker->isGranted('CHILL_STAT_DUMMY', $center)
                ->willReturn(true);
        
        $exportManager = $this->createExportManager(null, null, 
                $authorizationChecker->reveal(), null, $user);
        
        $exportFooBar = $this->prophet->prophesize();
        $exportFooBar->willImplement(ExportInterface::class);
        $exportFooBar->requiredRole()->willReturn(new Role('CHILL_STAT_DUMMY'));
        $exportFooBar->supportsModifiers()->willReturn(array('foo', 'bar'));
        
        $aggregatorBar = $this->prophet->prophesize();
        $aggregatorBar->willImplement(AggregatorInterface::class);
        $aggregatorBar->applyOn()->willReturn('bar');
        $aggregatorBar->addRole()->willReturn(null);
        $exportManager->addAggregator($aggregatorBar->reveal(), 'bar');
        
        $exportBar = $this->prophet->prophesize();
        $exportBar->willImplement(ExportInterface::class);
        $exportBar->requiredRole()->willReturn(new Role('CHILL_STAT_DUMMY'));
        $exportBar->supportsModifiers()->willReturn(array('bar'));
        
        $aggregatorFoo = $this->prophet->prophesize();
        $aggregatorFoo->willImplement(AggregatorInterface::class);
        $aggregatorFoo->applyOn()->willReturn('foo');
        $aggregatorFoo->addRole()->willReturn(null);
        $exportManager->addAggregator($aggregatorFoo->reveal(), 'foo');
        
        $exportFoo = $this->prophet->prophesize();
        $exportFoo->willImplement(ExportInterface::class);
        $exportFoo->requiredRole()->willReturn(new Role('CHILL_STAT_DUMMY'));
        $exportFoo->supportsModifiers()->willReturn(array('foo'));
        

        $obtained = iterator_to_array($exportManager->getAggregatorsApplyingOn($exportFoo->reveal(), $centers));
        $this->assertEquals(1, count($obtained));
        $this->assertContains('foo', array_keys($obtained));
        
        $obtained = iterator_to_array($exportManager->getAggregatorsApplyingOn($exportBar->reveal(), $centers));
        $this->assertEquals(1, count($obtained));
        $this->assertContains('bar', array_keys($obtained));
        
        $obtained = iterator_to_array($exportManager->getAggregatorsApplyingOn($exportFooBar->reveal(), $centers));
        $this->assertEquals(2, count($obtained));
        $this->assertContains('bar', array_keys($obtained));
        $this->assertContains('foo', array_keys($obtained));
        
        // test with empty centers
        $obtained = iterator_to_array($exportManager->getAggregatorsApplyingOn($exportFooBar->reveal(), array()));
        $this->assertEquals(0, count($obtained));
        
    }
    
    public function testFiltersApplyingOn()
    {
        $center = $this->prepareCenter(100, 'center');
        $centers = array($center);
        $user = $this->prepareUser(array());
        
        $authorizationChecker = $this->prophet->prophesize();
        $authorizationChecker->willImplement(AuthorizationCheckerInterface::class);
        $authorizationChecker->isGranted('CHILL_STAT_DUMMY', $center)
                ->willReturn(true);
        
        $exportManager = $this->createExportManager(null, null, 
                $authorizationChecker->reveal(), null, $user);
        
        $exportFooBar = $this->prophet->prophesize();
        $exportFooBar->willImplement(ExportInterface::class);
        $exportFooBar->requiredRole()->willReturn(new Role('CHILL_STAT_DUMMY'));
        $exportFooBar->supportsModifiers()->willReturn(array('foo', 'bar'));
        
        $filterBar = $this->prophet->prophesize();
        $filterBar->willImplement(FilterInterface::class);
        $filterBar->applyOn()->willReturn('bar');
        $filterBar->addRole()->willReturn(null);
        $exportManager->addFilter($filterBar->reveal(), 'bar');
        
        $exportBar = $this->prophet->prophesize();
        $exportBar->willImplement(ExportInterface::class);
        $exportBar->requiredRole()->willReturn(new Role('CHILL_STAT_DUMMY'));
        $exportBar->supportsModifiers()->willReturn(array('bar'));
        
        $filterFoo = $this->prophet->prophesize();
        $filterFoo->willImplement(FilterInterface::class);
        $filterFoo->applyOn()->willReturn('foo');
        $filterFoo->addRole()->willReturn(null);
        $exportManager->addFilter($filterFoo->reveal(), 'foo');
        
        $exportFoo = $this->prophet->prophesize();
        $exportFoo->willImplement(ExportInterface::class);
        $exportFoo->requiredRole()->willReturn(new Role('CHILL_STAT_DUMMY'));
        $exportFoo->supportsModifiers()->willReturn(array('foo'));
        

        $obtained = iterator_to_array($exportManager->getFiltersApplyingOn($exportFoo->reveal(), $centers));
        $this->assertEquals(1, count($obtained));
        $this->assertContains('foo', array_keys($obtained));
        
        $obtained = iterator_to_array($exportManager->getFiltersApplyingOn($exportBar->reveal(), $centers));
        $this->assertEquals(1, count($obtained));
        $this->assertContains('bar', array_keys($obtained));
        
        $obtained = iterator_to_array($exportManager->getFiltersApplyingOn($exportFooBar->reveal(), $centers));
        $this->assertEquals(2, count($obtained));
        $this->assertContains('bar', array_keys($obtained));
        $this->assertContains('foo', array_keys($obtained));
        
        $obtained = iterator_to_array($exportManager->getFiltersApplyingOn($exportFooBar->reveal(), array()));
        $this->assertEquals(0, count($obtained));
    }
    
    /**
     * Test the generation of an export
     */
    public function testGenerate()
    {
        $center = $this->prepareCenter(100, 'center');
        $user = $this->prepareUser(array());
        
        $authorizationChecker = $this->prophet->prophesize();
        $authorizationChecker->willImplement(AuthorizationCheckerInterface::class);
        $authorizationChecker->isGranted('CHILL_STAT_DUMMY', $center)
                ->willReturn(true);
        
        $exportManager = $this->createExportManager(null, null, 
                $authorizationChecker->reveal(), null, $user);
        
        $export = $this->prophet->prophesize();
        $export->willImplement(ExportInterface::class);
        $export->initiateQuery(
                    Argument::Type(QueryBuilder::class), 
                    Argument::is(array('foo')), 
                    Argument::Type('array'),
                    Argument::is(array('a' => 'b'))
                )
                ->will(function ($qb) {
                    return $qb[0]->addSelect('COUNT(user.id) as export')
                            ->from('ChillMainBundle:User', 'user');
                    
                });
        //$export->initiateQuery()->shouldBeCalled();
        $export->supportsModifiers()->willReturn(array('foo'));
        $export->requiredRole()->willReturn(new Role('CHILL_STAT_DUMMY'));
        $export->getResult(Argument::Type(QueryBuilder::class), Argument::Type('array'))->willReturn(array(
            array(
                'aggregator' => 'cat a',
                'export' => 0,
            ),
            array(
                'aggregator' => 'cat b',
                'export' => 1
            )
        ));
        $export->getLabels(
                    Argument::is('export'), 
                    Argument::is(array(0, 1)), 
                    Argument::Type('array')
                )
                ->willReturn(array( 1 => 1, 0 => 0));
        $export->getQueryKeys(Argument::Type('array'))->willReturn(array('export'));
        $export->getTitle()->willReturn('dummy title');
        $exportManager->addExport($export->reveal(), 'dummy');
        
        $filter = $this->prophet->prophesize();
        $filter->willImplement(FilterInterface::class);
        //$filter->alterQuery()->shouldBeCalled();
        $filter->alterQuery(Argument::Type(QueryBuilder::class), Argument::Type('array'))
                ->willReturn(null);
        $filter->addRole()->shouldBeCalled();
        //$filter->addRole()->shouldBeCalled();
        $filter->applyOn()->willReturn('foo');
        $exportManager->addFilter($filter->reveal(), 'filter_foo');
        
        $aggregator = $this->prophet->prophesize();
        $aggregator->willImplement(AggregatorInterface::class);
        $aggregator->applyOn()->willReturn('foo');
        $aggregator->alterQuery(Argument::Type(QueryBuilder::class), Argument::Type('array'))
                ->willReturn(null);
        //$aggregator->alterQuery()->shouldBeCalled();
        $aggregator->getQueryKeys(Argument::Type('array'))->willReturn(array('aggregator'));
        $aggregator->getLabels(
                    Argument::is('aggregator'), 
                    Argument::is(array('cat a', 'cat b')), 
                    Argument::is(array())
                )
                ->willReturn(array(
                'cat a' => 'label cat a', 'cat b' => 'label cat b'
            ));
        $aggregator->addRole()->willReturn(null);
        //$aggregator->addRole()->shouldBeCalled();
        $exportManager->addAggregator($aggregator->reveal(), 'aggregator_foo');
        
        //add csv formatter
        $formatter = new \Chill\MainBundle\Export\Formatter\CSVFormatter(
                $this->container->get('translator'), $exportManager);
        $exportManager->addFormatter($formatter, 'csv');
        
        ob_start();
        $response = $exportManager->generate('dummy', 
                array(PickCenterType::CENTERS_IDENTIFIERS => array($center)), 
                array(
                    ExportType::FILTER_KEY => array(
                        'filter_foo' => array(
                            'enabled' => true,
                            'form' => array()
                        )
                    ),
                    ExportType::AGGREGATOR_KEY => array(
                        'aggregator_foo' => array(
                            'enabled' => true,
                            'form' => array() 
                        )
                    ),
                    ExportType::PICK_FORMATTER_KEY => array(
                        'alias' => 'csv'
                    ),
                    ExportType::EXPORT_KEY => array(
                        'a' => 'b'
                    )
                ), 
                array(
                    'aggregator_foo' => array(
                        'order' => 1, 'position' => 'r'
                    )
                )
            );
        $content = ob_get_clean();
        $this->assertInstanceOf(Response::class, $response);
        $expected = <<<EOT
"dummy title"

,
"label cat a",0
"label cat b",1

EOT;
        $this->assertEquals($expected, $content);
    }
    
}
