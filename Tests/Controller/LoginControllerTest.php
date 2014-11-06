<?php

namespace Chill\MainBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class LoginControllerTest extends WebTestCase
{
    public function testLogin()
    {
        $client = static::createClient();

        //load login page and submit form
        $crawler = $client->request('GET', '/login');
        $this->assertTrue($client->getResponse()->isSuccessful());
        
        $buttonCrawlerNode = $crawler->selectButton('Login');
        $form = $buttonCrawlerNode->form();
        
        $client->submit($form, array(
           '_username' => 'center a_social',
           '_password' => 'password'
        ));
        
        //the response is a redirection
        $this->assertTrue($client->getResponse()->isRedirect());
        
        //the response is not a login page, but on a new page
        $this->assertNotRegExp('/\/login$/', $client->getResponse()
              ->headers
              ->get('location'));
        
        //on the home page, there must be a logout link
        $client->followRedirects(true);
        $crawler = $client->request('GET', '/');
        
        $this->assertRegExp('/center a_social/', $client->getResponse()
                                    ->getContent());
        $logoutLinkFilter = $crawler->filter('a:contains("Logout")');
        
        //check there is > 0 logout link
        $this->assertGreaterThan(0, $logoutLinkFilter->count());
        
        //click on logout link
        $client->followRedirects(false);
        $client->click($crawler->selectLink('Logout')->link());
        
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->followRedirect(); #redirect to login page
        
        //check we are back on login page
        $this->assertRegExp('/\/login$/', $client->getResponse()
              ->headers
              ->get('location'));
        
        
    }

}
