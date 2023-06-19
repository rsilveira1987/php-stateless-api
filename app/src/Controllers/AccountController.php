<?php

    namespace App\Controllers;

    use App\Models\AccountModel;
    use App\Services\AccountService;
    use App\Services\TokenService;
use App\Utils\CookieJar;
use App\Utils\UUID;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;

    class AccountController
    {
        private $container;

        public function __construct($container) {
            $this->container = $container;
        }

        public function create(Request $request, Response $response, $args) {
        
            // Obtem os parametros do formulario
            $params = $request->getParams();

            $account = new AccountModel;
            $account->uuid = UUID::v4();
            $account->first_name = trim($params['first_name'] ?? '');
            $account->second_name = trim($params['second_name'] ?? '');
            $account->email = trim($params['email'] ?? '');
            $account->url_image = trim($params['url_image'] ?? '');
            $account->password = trim($params['password'] ?? '');
            
            try {
                \App\Utils\SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
                $account = AccountService::create($account);
                return $response->withRedirect($this->container->router->pathFor('account.view',[
                    'uuid' => $account->uuid
                ]));
            } catch (\Exception $e) {
                // executa o rollback das alteracoes
                \App\Utils\SQLTransaction::rollback();
            } finally {
                \App\Utils\SQLTransaction::close();
            }

            return $response->withRedirect($this->container->router->pathFor('account.create'));

        }

        public function update(Request $request, Response $response, $args) {
            // UUID
            $uuid = $this->container->auth->getCurrentUser()->uuid;
            
            try {                
                $accountDTO = AccountService::retrieve($uuid);
            
                // Obtem os parametros do formulario
                $params = $request->getParams();
                
                $accountDTO->first_name = trim($params['first_name'] ?? '');
                $accountDTO->second_name = trim($params['second_name'] ?? '');
                $accountDTO->email = trim($params['email'] ?? '');
                $accountDTO->url_image = trim($params['url_image'] ?? '');
                $account = AccountService::update($accountDTO);

                // update account info
                $this->container->auth->setCurrentUser($account);

                // flash a message
                $this->container->flash->addMessage('snackbar','Dados atualizados.');
                
            } catch (\Exception $e) {
                // flash a message
                $this->container->flash->addMessage('snackbar',$e->getMessage());
            }

            return $response->withRedirect($this->container->router->pathFor('me.edit'));

        }

        public function delete(Request $request, Response $response, $args) {
            // UUID
            $uuid = $args['uuid'];    
            
            try {
                \App\Utils\SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
                $account = AccountService::retrieve($uuid);
                AccountService::delete($account);
                $response = $response->withRedirect($this->container->router->pathFor('account.list'));
                
            } catch (\Exception $e) {
                // executa o rollback das alteracoes
                \App\Utils\SQLTransaction::rollback();
                $response = $response->withRedirect($this->container->router->pathFor('account.edit',[
                    'uuid' => $account->uuid
                ]));
            } finally {
                \App\Utils\SQLTransaction::close();
            }

            return $response;

        }

        public function changePassword(Request $request, Response $response, $args) {
            // UUID
            $uuid = $this->container->auth->getCurrentUser()->uuid;  

            // Obtem os parametros do formulario
            $params = $request->getParams();

            $accountDTO = AccountService::retrieve($uuid);
            
            try {
                \App\Utils\SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
                
                $newPassword = $params['new_password'];
                $accountDTO->password = $newPassword;
                $account = AccountService::updatePassword($accountDTO);

                $this->container->auth->setCurrentUser($account);

                // Flash a message
                $this->container->flash->addMessage('snackbar','Dados atualizados.');
                
            } catch (\Exception $e) {
                // Flash a message
                $this->container->flash->addMessage('snackbar',$e->getMessage());
                // executa o rollback das alteracoes
                \App\Utils\SQLTransaction::rollback();
            } finally {
                \App\Utils\SQLTransaction::close();
            }

            return $response->withRedirect($this->container->router->pathFor('me.edit'));

        }

        public function removeToken(Request $request, Response $response, $args) {
            
            $params = $request->getParams();
            $action = $params['action'] ?? '';
            $jwt = $params['jwt'] ?? '';

            $currentAccount = $this->container->auth->getCurrentUser();

            // Remove token
            if($action === 'remove') {
                try {
                    
                    if (!$currentAccount->hasToken($jwt)){
                        return $response->withRedirect($this->container->router->pathFor('me'));
                    }
                    
                    $token = TokenService::retrieve($jwt);
                    TokenService::delete($token);
                    
                    $response = CookieJar::addCookie($response,'COOKIE_MSG','Token removido.');
    
                } catch (\Exception $e) {
                    // $response = CookieJar::addCookie($response,'COOKIE_MSG',$e->getMessage());
                }
            }
            
            return $response->withRedirect($this->container->router->pathFor('tokens'));
        }
    }