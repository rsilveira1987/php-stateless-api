<?php

    namespace App\Controllers;

use App\Models\AccountModel;
use App\Models\TokenModel;
use App\Services\AccountService;
use App\Services\AuthenticationService;
use Exception;
    use Firebase\JWT\JWT;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

    class ApiController
    {
        private $container;

        public function __construct($container) {
            $this->container = $container;
        }

        public function echo(Request $request, Response $response, $args) {
            try {
                // when correctly parsed, returns an array
                $payload = $request->getParsedBody();
                $response = $response->withJson(["success" => $payload], 200);
                
            } catch (Exception $e) {
                $payload = ['message' => $e->getMessage()];
                $response = $response->withJson(["error" => $payload], 500);
            }

            return $response;

        }

        

        public function login(Request $request, Response $response, $args) {

            try {

                // grab request body
                $jsonData = $request->getParsedBody();
                $email = $jsonData['email'] ?? '';
                $password = $jsonData['password'] ?? '';

                // open sql transaction
                // SQLTransaction::open( DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
                // $account = AccountDao::findBy('email',$email);
                    
                // // Conta nao encontrada
                // if (!$account)
                //     throw new Exception('Wrong account name or password');
                
                // // verify user password, check rehash
                // if (!\App\Auth\Authenticator::verify($password,$account->password)) {
                //     throw new Exception('Wrong account name or password');
                // }
                $account = AuthenticationService::login($email,$password);

                $issued_at = time();
                $notbefore_at = $issued_at - (60 * 60);
                $expires_at = $issued_at + (60 * 1);

                $payload = array(
                    "iss" => "http://localhost:8080",
                    "aud" => "http://localhost:8080",
                    "iat" => $issued_at,
                    // "nbf" => $notbefore_at,
                    // "exp" => $expires_at,
                    "account" => [
                        "id" => intval($account->id),
                        "uuid" => $account->uuid,
                        "first_name" => $account->first_name,
                        "second_name" => $account->second_name,
                        "email" => $account->email,
                        "url_image" => $account->url_image ?? null,
                        "created_at" => $account->created_at ?? null,
                        "updated_at" => $account->updated_at ?? null
                    ]
                );

                $jwt = JWT::encode($payload, JWT_SECRET_KEY ,'HS256');
                $payload = [
                    'jwt' => $jwt
                ];

                $response = $response->withJson([
                    'status' => 'success',
                    'payload' => $payload
                ], 200);   

            } catch (\Exception $e) {
                // rollback transaction
                // SQLTransaction::rollback();
                $payload = [
                    'message'=>$e->getMessage()
                ];
                $response = $response->withJson([
                    'status' => 'failure',
                    'payload' => $payload
                ],500);
            }
            // } finally {
            //     SQLTransaction::close();
            // }

            return $response;

        }

        public function createAccount(Request $request, Response $response, $args) {
        
            try {
                \App\Utils\SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
                
                // form data
                $jsonData = $request->getParsedBody();
                $accountDTO = AccountModel::fromArray($jsonData);
                $account = AccountService::create($accountDTO);
                
                $response = $response->withJson([
                    'status' => 'success',
                    'payload' => $account->toJson()
                ], 200);
                
            } catch (Exception $e) {
                \App\Utils\SQLTransaction::rollback();
                $payload = ['message' => $e->getMessage()];
                $response = $response->withJson([
                    'status' => 'failure',
                    'payload' => $payload
                ], 500);
            } finally {
                \App\Utils\SQLTransaction::close();
            }

            return $response;

        }

        public function retrieveAccount(Request $request, Response $response, $args) {
            // grab UUID
            $uuid = $args['uuid'];

            try {
                
                $account = AccountService::retrieve($uuid);
                if(!$account)
                    throw new Exception('Conta inválida.');
                
                $payload = $account->toJson();
                $response = $response->withJson([
                    'status' => 'success',
                    'payload' => $payload
                ], 200);
                
            } catch (Exception $e) {
                $payload = ['message' => $e->getMessage()];
                $response = $response->withJson([
                    'status' => 'failure',
                    'payload' => $payload
                ], 500);
            }
            
            return $response;

        }

        public function updateAccount(Request $request, Response $response, $args) {
            // grab uuid reference
            $uuid = $args['uuid'];

            try {
                \App\Utils\SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
                // Retrieve account
                $account = AccountService::retrieve($uuid);
                if(!$account)
                    throw new Exception('Conta inválida.');
                
                // grab JSON data
                $jsonData = $request->getParsedBody();
                $accountDTO = AccountModel::fromArray($jsonData);
                
                // set new values
                $accountDTO->id = $account->id;
                $accountDTO->uuid = $account->uuid;
                $accountDTO->first_name = $accountDTO->first_name ?? $account->first_name;
                $accountDTO->second_name = $accountDTO->second_name ?? $account->second_name;
                $accountDTO->email = $accountDTO->email ?? $account->email;
                $accountDTO->url_image = $accountDTO->url_image ?? $account->url_image;
                $accountDTO->created_at = $account->created_at;

                $account = AccountService::update($accountDTO);

                $token = TokenModel::generate($account);
                $response = \App\Utils\CookieJar::addCookie($response,'JWT',$token);
                
                $response = $response->withJson([
                    'status' => 'success',
                    'payload' => $account->toJson()
                ], 200);
                
            } catch (Exception $e) {
                \App\Utils\SQLTransaction::rollback();
                $payload = ['message' => $e->getMessage()];
                $response = $response->withJson([
                    'status' => 'failure',
                    'payload' => $payload
                ], 500);
            } finally {
                \App\Utils\SQLTransaction::close();
            }

            return $response;

        }

        public function updateAccountPassword(Request $request, Response $response, $args) {
            // grab uuid reference
            $uuid = $args['uuid'];

            try {
                \App\Utils\SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
                // Retrieve account
                $accountDTO = AccountService::retrieve($uuid);
                
                // grab JSON data
                $jsonData = $request->getParsedBody();
                $newPassword = $jsonData['new_password'];
                $confirmPassword = $jsonData['confirm_password'];
                
                if ($newPassword !== $confirmPassword) {
                    throw new Exception('Senhas não conferem');
                }
                
                $accountDTO->password = $newPassword;
                $account = AccountService::updatePassword($accountDTO);
                
                $response = $response->withJson([
                    'status' => 'success',
                    'payload' => $account->toJson()
                ], 200);
                
            } catch (Exception $e) {
                \App\Utils\SQLTransaction::rollback();
                $payload = ['message' => $e->getMessage()];
                $response = $response->withJson([
                    'status' => 'failure',
                    'payload' => $payload
                ], 500);
            } finally {
                \App\Utils\SQLTransaction::close();
            }

            return $response;

        }

        public function deleteAccount(Request $request, Response $response, $args) {
        
            // grab uuid reference
            $uuid = $args['uuid'];

            try {
                \App\Utils\SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
                // Retrieve account
                $account = AccountService::retrieve($uuid);
                if(!$account)
                    throw new Exception('Conta inválida.');

                $res = AccountService::delete($account);
                
                $response = $response->withJson([
                    'status' => 'success',
                    'payload' => null
                ], 200);
                
            } catch (Exception $e) {
                \App\Utils\SQLTransaction::rollback();
                $payload = ['message' => $e->getMessage()];
                $response = $response->withJson([
                    'status' => 'failure',
                    'payload' => $payload
                ], 500);
            } finally {
                \App\Utils\SQLTransaction::close();
            }

            return $response;

        }

        public function retrieveAllAccounts(Request $request, Response $response, $args) {
            
            try {
                // Retrieve all accounts
                $accounts = AccountService::retrieveAll($orderBy = 'first_name,second_name');
                $payload = [];
                foreach($accounts as $account) {
                    $payload[] = $account->toJson();
                }
               
                $response = $response->withJson([
                    'status' => 'success',
                    'payload' => $payload
                ], 200);
                
            } catch (Exception $e) {
                $payload = ['message' => $e->getMessage()];
                $response = $response->withJson([
                    'status' => 'failure',
                    'payload' => $payload
                ], 500);
            }

            return $response;

        }

        public function getInfo(Request $request, Response $response, $args) {
            
            $account = $this->container->auth->getCurrentUser();

            try {               
                $response = $response->withJson([
                    'status' => 'success',
                    'payload' => $account->toJson()
                ], 200);
                
            } catch (Exception $e) {
                $payload = ['message' => $e->getMessage()];
                $response = $response->withJson([
                    'status' => 'failure',
                    'payload' => $payload
                ], 500);
            }

            return $response;

        }

        public function searchAccounts(Request $request, Response $response, $args) {

            try {
                // grab JSON data
                $jsonData = $request->getParsedBody();

                $q = $jsonData['q'] ?? '';

                // Retrieve all accounts
                $accounts = AccountService::search($q);
                $payload = [];
                foreach($accounts as $account) {
                    $payload[] = $account->toJson();
                }
               
                $response = $response->withJson([
                    'status' => 'success',
                    'response' => $payload
                ], 200);
                
            } catch (Exception $e) {
                $response = $response->withJson([
                    'status' => 'error',
                    'response' => $e->getMessage()
                ], 500);
            }

            return $response;

        }
    }