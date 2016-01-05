<?php

namespace Chill\MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    private $client;
    
    public function setUp()
    {
        self::bootKernel();
        
        $this->client = static::createClient(array(), array(
           'PHP_AUTH_USER' => 'admin',
           'PHP_AUTH_PW'   => 'password',
           'HTTP_ACCEPT_LANGUAGE' => 'fr_FR'
        ));
    }

    public function testList()
    {
        // get the list
        $crawler = $this->client->request('GET', '/fr/admin/user/');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 
                "Unexpected HTTP status code for GET /admin/user/");
        
        $link = $crawler->selectLink('Ajouter un nouvel utilisateur')->link();
        $this->assertInstanceOf('Symfony\Component\DomCrawler\Link', $link);
        $this->assertRegExp('|/fr/admin/user/new$|', $link->getUri());
    }
        
    public function testNew()
    {
        $crawler = $this->client->request('GET', '/fr/admin/user/new');
        
        $username = 'Test_user'.  uniqid();
        $password = 'Password1234!';
        // Fill in the form and submit it
        $form = $crawler->selectButton('Créer')->form(array(
            'chill_mainbundle_user[username]'  => $username,
            'chill_mainbundle_user[plainPassword][password][first]' => $password,
            'chill_mainbundle_user[plainPassword][password][second]' => $password
        ));

        $this->client->submit($form);
        $crawler = $this->client->followRedirect();

        // Check data in the show view
        $this->assertGreaterThan(0, $crawler->filter('td:contains("Test_user")')->count(), 
                'Missing element td:contains("Test user")');
        
        $update = $crawler->selectLink('Modifier')->link();
        
        $this->assertInstanceOf('Symfony\Component\DomCrawler\Link', $update);
        $this->assertRegExp('|/fr/admin/user/[0-9]{1,}/edit$|', $update->getUri());
        
        //test the auth of the new client
        $this->isPasswordValid($username, $password);
        
        return $update;
    }
    
    protected function isPasswordValid($username, $password)
    {
        /* @var $passwordEncoder \Symfony\Component\Security\Core\Encoder\UserPasswordEncoder */
        $passwordEncoder = self::$kernel->getContainer()
                ->get('security.password_encoder');
        
        $user = self::$kernel->getContainer()
                ->get('doctrine.orm.entity_manager')
                ->getRepository('ChillMainBundle:User')
                ->findOneBy(array('username' => $username));
        
        $this->assertTrue($passwordEncoder->isPasswordValid($user, $password));
    }
    
    /**
     * 
     * @param \Symfony\Component\DomCrawler\Link $update
     * @depends testNew
     */
    public function testUpdate(\Symfony\Component\DomCrawler\Link $update)
    {
        $crawler = $this->client->click($update);

        $username = 'Foo bar '.uniqid();
        $form = $crawler->selectButton('Mettre à jour')->form(array(
            'chill_mainbundle_user[username]'  => $username,
        ));
        
        $this->client->submit($form);
        $crawler = $this->client->followRedirect();
        // Check the element contains an attribute with value equals "Foo"
        $this->assertGreaterThan(0, $crawler->filter('[value="'.$username.'"]')->count(), 
                'Missing element [value="Foo bar"]');
        
        $updatePassword = $crawler->selectLink('Modifier le mot de passe')->link();
        
        $this->assertInstanceOf('Symfony\Component\DomCrawler\Link', $updatePassword);
        $this->assertRegExp('|/fr/admin/user/[0-9]{1,}/edit_password$|', 
                $updatePassword->getUri());
        
        return array('link' => $updatePassword, 'username' => $username);
    }
    
    /**
     * 
     * @param \Symfony\Component\DomCrawler\Link $updatePassword
     * @depends testUpdate
     */
    public function testUpdatePassword(array $params)
    {
        $link = $params['link'];
        $username = $params['username'];
        $newPassword = '1234Password!';
        
        $crawler = $this->client->click($link);
        
        $form = $crawler->selectButton('Changer le mot de passe')->form(array(
            'chill_mainbundle_user_password[password][first]' => $newPassword,
            'chill_mainbundle_user_password[password][second]' => $newPassword,
        ));
        
        $this->client->submit($form);
        
        $this->assertTrue($this->client->getResponse()->isRedirect(), 
                "the response is a redirection");
        $this->client->followRedirect();
        
        $this->isPasswordValid($username, $newPassword);
    }

    
}
