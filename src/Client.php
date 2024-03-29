<?php


namespace AnthraxisBR\AutoGcpSdk;


use AnthraxisBR\FastWork\CloudServices\GCP\IAM\Credentials;

use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Subscriber;

class Client extends \GuzzleHttp\Client
{

    /**
     * @var Credentials
     */
    private $credentials;

    public $getScope;

    /**
     * @var array
     */
    public $config;

    public function __construct(array $config = [])
    {
        $this->config = ['base_url'=> $this->url];

        $this->auth();
    }

    protected function auth()
    {
        $httpclient = new \Swoole\Coroutine\Http\Client('0.0.0.0', 9599);
        $httpclient->setHeaders(['Host' => "api.mp.qq.com"]);
        $httpclient->set([ 'timeout' => 1]);
        $httpclient->setDefer();
        $httpclient->get('/test');

        $http_res  = $httpclient->recv();

        var_dump($http_res);

        $this->credentials = new Credentials();

        $reauth_config = [
            "client_id" => $this->credentials->getClientId(),
            "client_secret" => $this->credentials->getClientSecret(),
            "scope" => $this->getScope(), // optional
            "state" => time(), // optional
        ];

        $grant_type = new ClientCredentials($this, $reauth_config);

        $oauth = new OAuth2Subscriber($grant_type);

        $this->config['auth'] = ['oauth'];

        parent::__construct($this->config);

        $this->getEmitter()->attach($oauth);

    }

    public function getScope()
    {
        return $this->scope;
    }

    public function setScope(string $scope)
    {
        $this->scope;
    }

    public function replaceUri(string $uri, array $args)
    {
        foreach ($args as $arg => $value) {
            $uri = str_replace($arg, $value, $uri);
        }
        return $uri;
    }

    public function setUrl($url){
        $this->url = $url;
    }
    public function prepareUrl()
    {
        $this->setUrl($this->parseArgs($this->url, $this->uri_args));
    }

    public function parseArgs($url_str = null, array  $args)
    {
        $url = '';
        if(count($args) > 0){
            $url = '?';
        }
        $c = 1;
        foreach ($args as $arg => $value){
            $url .= $arg . '=' . $value;
            if(count($args) > $c){
                $url .= '&';
            }
            $c += 1;
        }

        if($url != ''){
            if(is_null($url_str)){
                $url = $this->url . $url;
            }else{
                $url = $url_str . $url;
            }
        }

        return $url;
    }

}