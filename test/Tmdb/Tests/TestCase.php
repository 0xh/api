<?php
/**
 * This file is part of the Tmdb PHP API created by Michael Roterman.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Tmdb
 * @author Michael Roterman <michael@wtfz.net>
 * @copyright (c) 2013, Michael Roterman
 * @version 0.0.1
 */
namespace Tmdb\Tests;

use Tmdb\ApiToken;
use Tmdb\Client;
use Tmdb\Common\ObjectHydrator;
use Tmdb\Common\ParameterBag;
use Tmdb\HttpClient\HttpClient;
use Tmdb\HttpClient\Request;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Assert that an array of methods and corresponding classes match
     *
     * @param $subject
     * @param  array      $instances
     * @throws \Exception
     */
    protected function assertInstancesOf($subject, array $instances = [])
    {
        foreach ($instances as $method => $instance) {
            try {
                $this->assertInstanceOf($instance, $subject->$method());
            } catch (\Exception $e) {
                throw new \Exception(sprintf(
                    'Failed asserting that calling "%s" returns an instance of expected "%s".',
                    sprintf('%s::%s', get_class($subject), $method),
                    $instance
                ));
            }
        }
    }

    /**
     * Load an json file from the Resources directory
     *
     * @param $file
     * @return mixed
     */
    protected function loadByFile($file)
    {
        return json_decode(
            file_get_contents(
                sprintf(
                    '%s/%s',
                    'test/Tmdb/Tests/Resources/',
                    $file
                )
            ),
            true
        );
    }

    /**
     * Get a TMDB Client with an mocked HTTP dependency
     *
     * @return \Tmdb\Client
     */
    protected function getClientWithMockedHttpClient(array $options = array())
    {
        $token = new ApiToken('abcdef');
        $mock  = $this->getMock('Tmdb\HttpClient\Adapter\AdapterInterface');

        return new Client($token, $mock);
    }

    /**
     * Get TMDB Client
     *
     * @return Client
     */
    protected function getMockedTmdbClient()
    {
        $token   = new ApiToken('abcdef');
        $adapter = $this->getMockBuilder('Tmdb\HttpClient\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        return $this->_client = new Client($token, $adapter);
    }

    /**
     * Get mocked http client
     *
     * @param  array                                    $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getHttpClientWithMockedAdapter($baseUrl, array $options = [])
    {
        return $this->_client = new HttpClient(
            $baseUrl,
            $options,
            $this->getMock('Tmdb\HttpClient\Adapter\AdapterInterface'),
            $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher')
        );
    }

    /**
     * Get mocked http client
     *
     * @param  array                                    $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockedHttpClient(array $methods = [])
    {
        return $this->getMock('Guzzle\Http\Client', array_merge(
            $methods,
            ['send']
        ));
    }

    /**
     * Get the expected request that will deliver a response
     *
     * @param $path
     * @param  array   $parameters
     * @param  string  $method
     * @param  array   $headers
     * @param  null    $body
     * @return Request
     */
    protected function getRequest($path, $parameters = [], $method = 'GET', $headers = [], $body = null)
    {
        if (
            $method == 'POST'  ||
            $method == 'PUT'   ||
            $method == 'PATCH' ||
            $method == 'DELETE'
        ) {
            $headers = array_merge($headers, ['Content-Type' => 'application/json']);
        }

        $request = new Request(
            $path,
            $method,
            new ParameterBag(array_merge(
                    $parameters,
                    [
                        'api_key' => 'abcdef'
                    ]
                )
            ),
            new ParameterBag(array_merge(
                    $headers,
                    [
                        'Accept'     => 'application/json',
                        'User-Agent' => sprintf('wtfzdotnet/php-tmdb-api (v%s)', Client::VERSION)
                    ]
                )
            )
        );

        $request->setOptions(new ParameterBag([
            'token'  => new ApiToken('abcdef'),
            'secure' => true,
            'cache'  => ['enabled' => true],
            'log'    => ['enabled' => false]
        ]));

        if ($body !== null) {
            $request->setBody(is_array($body) ? json_encode($body) : $body);
        }

        return $request;
    }

    /**
     * Hydrate object
     *
     * @param $object
     * @param $data
     * @return \Tmdb\Model\AbstractModel
     */
    protected function hydrate($object, array $data = [])
    {
        $objectHydrator = new ObjectHydrator();

        return $objectHydrator->hydrate($object, $data);
    }
}
