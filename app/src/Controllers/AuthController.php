<?php

    namespace App\Controllers;

use App\Models\TokenModel;
use App\Services\AuthenticationService;
use Exception;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

    class AuthController
    {
        private $container;

        public function __construct($container) {
            $this->container = $container;
        }

        // public function login($request, $response, $args) {
        //     // obtem os dados do formulario
        //     $params = $request->getParams();

        //     // Processa requisicoes POST            
        //     if ($params['action'] == 'login') {
                
        //         // Validar usuario e senha                
        //         try {

        //             $email = strtolower($params['email']);
        //             $password = $params['password'];

        //             $account = AuthenticationService::login($email, $password);
        //             $jwt = TokenModel::generate($account);

        //             $token = new TokenModel;
        //             $token->uuid = $account->uuid;
        //             $token->value = $jwt;
        //             $token->user_agent = $request->getHeader('User-Agent')[0] ?? '';
        //             AuthenticationService::addToken($token);

        //             // Add JWT cookie
        //             $response = \App\Utils\CookieJar::addCookie($response,'JWT',$jwt);
                                    
        //             // redirect to index
        //             $response = $response->withRedirect($this->container->router->pathFor('home'));
                    
        //         } catch (Exception $e) {

        //             // flash a message
        //             \App\Utils\CookieJar::addCookie($response,'COOKIE_MSG','Usuário ou senha inválido');
                    
                    
        //             // $this->container->flash->addMessage('snackbar',$e->getMessage());
        //             $response = $response->withRedirect($this->container->router->pathFor('login'));
        //         }

        //         return $response;
        //     } 
            
        // }

        public function login($request, $response, $args) {
            // obtem os dados do formulario
            $params = $request->getParams();

            // Processa requisicoes POST            
            if ($params['action'] == 'login') {
                
                // Validar usuario e senha                
                try {

                    $email = strtolower($params['email']);
                    $password = $params['password'];

                    $user = AuthenticationService::login($email, $password);
                    $this->container->auth->setCurrentUser($user);
                                    
                    // redirect to home
                    $response = $response->withRedirect($this->container->router->pathFor('home'));
                    
                } catch (Exception $e) {

                    // flash a message                   
                    
                    $this->container->flash->addMessage('snackbar',$e->getMessage());
                    $response = $response->withRedirect($this->container->router->pathFor('login'));
                }

                return $response;
            } 
            
        }

        public function logout($request, $response, $args) {
            $this->container->auth->logout();
            return $response->withRedirect($this->container->router->pathFor('login'));
        }

        

        
    }