<?php

    namespace App\Controllers;

use App\Services\AccountService;
use App\Utils\CookieJar;
use App\Utils\Cookies;
use App\Utils\UUID;
use Exception;
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    

    class HtmlPageController
    {
        private $container;

        public function __construct($container) {
            $this->container = $container;
        }

        public function index(Request $request, Response $response, $args) {
            // $uuid = UUID::v4();
            // return $this->container->view->render($response,'index.html',[
            //     'uuid' => $uuid
            // ]);
            return $response->withRedirect($this->container->router->pathFor('home'));
        }

        public function login(Request $request, Response $response, $args) {
            return $this->container->view->render($response,'login.html');
        }

        public function home(Request $request, Response $response, $args) {
            // all accounts
            $accounts = AccountService::retrieveAll();
            
            return $this->container->view->render($response,'home.html',[
                'total_accounts' => count($accounts)
            ]);
        }

        public function me(Request $request, Response $response, $args) {

            $currentUser = $this->container->auth->getCurrentUser();

            return $response = $this->container->view->render($response,'me.html');
            
        }

        public function profile(Request $request, Response $response, $args) {

            return $response = $this->container->view->render($response,'me.html');
            
        }

        public function meEdit(Request $request, Response $response, $args) {
            return $response = $this->container->view->render($response,'me-edit.html');
        }

        public function getTokens(Request $request, Response $response, $args) {

            $currentUser = $this->container->auth->getCurrentUser();

            return $response = $this->container->view->render($response,'tokens.html',[
                'tokens' => $currentUser->getJWTTokens()
            ]);
            
        }

        public function addAccount(Request $request, Response $response, $args) {
            return $this->container->view->render($response,'account-add.html');
        }

        public function searchAccounts(Request $request, Response $response, $args) {
            return $this->container->view->render($response,'search.html');
        }

        public function searchAccountsParams(Request $request, Response $response, $args) {
            $query = $args['query'];
            
            try {

                // Retrieve all accounts
                $accounts = AccountService::search($query);
                
                
            } catch (Exception $e) {
                $response = CookieJar::addCookie($response,'COOKIE_MSG',$e->getMessage());
            }

            return $this->container->view->render($response,'search-params.html',[
                'accounts' => $accounts,
                'query' => $query
            ]);

        }

        public function listAccounts(Request $request, Response $response, $args) {
            
            // Retrieve all accounts
            $accounts = AccountService::retrieveAll($orderBy = 'first_name,second_name');
            
            return $this->container->view->render($response,'account-list.html',[
                'accounts' => $accounts
            ]);
        }

        public function cookies(Request $request, Response $response, $args) {
            print '<pre>';
            var_dump( \App\Utils\CookieJar::getCookies($request) );
            print '</pre>';
            
        }

        public function viewAccount(Request $request, Response $response, $args) {
            // UUID
            $uuid = $args['uuid'];

            try {
                $account = AccountService::retrieve($uuid);
                if(!$account) {
                    throw new Exception('Falha: Conta inválida.');
                }

                $response = $this->container->view->render($response,'account-view.html',[
                    'account' => $account
                ]);
            } catch (\Exception $e) {
                // Account not found
                $response = $response->withRedirect($this->container->router->pathFor('account.list'));
            }

            return $response;
            
        }

        public function editAccount(Request $request, Response $response, $args) {
            // // UUID
            // $uuid = $args['uuid'];

            // try {
            //     \App\Utils\SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
            //     $account = AccountService::retrieve($uuid);
            //     if(!$account) {
            //         throw new Exception('Falha: Conta inválida.');
            //     }
            //     $response = $this->container->view->render($response,'account-edit.html',[
            //         'account' => $account
            //     ]);
            // } catch (\Exception $e) {
            //     // Account not found
            //     $response = $response->withRedirect($this->container->router->pathFor('account.list'));
            // } finally {
            //     \App\Utils\SQLTransaction::close();
            // }

            // return $response;
        }

        public function ssrSearchAccounts(Request $request, Response $response, $args) {
                        
            $params = $request->getParams();
            $q = $params['q'] ?? '';
            
            try {
                $accounts = AccountService::search($q);
            } catch (Exception $e) {
                $accounts = [];
            }
            
            return $this->container->view->render($response,'ssr/account-search.html',[
                'accounts' => $accounts
            ]);
        }

        public function ssrMe(Request $request, Response $response, $args) {
                        
            return $response = $this->container->view->render($response,'ssr/create-account.html');
        }
    }