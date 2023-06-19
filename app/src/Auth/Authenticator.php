<?php

    namespace App\Auth;

use App\Models\AccountModel;
use App\Services\AccountService;
use App\Utils\Session;
use Firebase\JWT\JWT;

    class Authenticator
    {
        private $container;

        public function __construct($container) {
            $this->container = $container;
        }

        public function getCurrentUser() {
            $user = \App\Utils\Session::get('user');
            return $user;
        }

        public function setCurrentUser(AccountModel $user) {
            \App\Utils\Session::set('user',$user);
        }

        public function decodeToken($token) {

            $account = null;

            try {
                $decoded = JWT::decode($token, JWT_SECRET_KEY, array('HS256'));    
                $accountData = $decoded->account;          
                $account = AccountModel::fromArray((array)$accountData);
            } catch (\Exception $e) {
                $account = null;
            } 
           
            return $account;

        }

        // public static function getUser() {
        //     // grab current user uid
        //     $uid = \App\Utils\Session::get('uid');

        //     // open LDAP connection
        //     \App\Utils\LdapTransaction::open( CONFIG_DIR . '/preferences.ini');
                
        //     $acct = \App\Models\LdapEntry::find('uid='.$uid);
        //     if (!$acct) {
        //         $acct = null;
        //     }
        //     \App\Utils\LdapTransaction::close();

        //     return $acct;
                        
        //     //-------------------------------------
        //     // SQL user
        //     //-------------------------------------
        //     // // grab current user id
        //     // $id = \App\Utils\Session::get('user_id');

        //     // // Load user
        //     // $dbc = \App\Utils\SQLConnection::open( CONFIG_DIR . '/db.ini' );
        //     // $query = "SELECT * FROM customers WHERE id = :id";
        //     // $stmt = $dbc->prepare($query);
        //     // $r = $stmt->execute([
        //     //     ':id' => $id
        //     // ]);

        //     // $user = $stmt->fetch(\PDO::FETCH_OBJ);

        //     // // return user object or null
        //     // return $user;
        // }

        public static function generatePassword($textPwd) {
            return password_hash($textPwd, PASSWORD_DEFAULT, ['cost' => 10]);
        }

        public static function verify($textPwd, $hashedPwd) {
            // check user password
            return password_verify($textPwd, $hashedPwd);
        }

        public static function needToRehash($hashedPwd) {
            $currentHashAlgorithm = PASSWORD_DEFAULT;
            $currentHashOptions = ['cost'=>10];
            $needToRehash = password_needs_rehash(
                $hashedPwd,
                $currentHashAlgorithm,
                $currentHashOptions
            );

            if ($needToRehash) return true;

            return false;
        }

        public function logout() {
            \App\Utils\Session::destroy();
            \App\Utils\Session::set('user',null);
        }

        // public static function attempt($binddn, $password)
        // {
        //     if ($conn = \App\Utils\LdapTransaction::getInstance()) {
        //         // Bind
        //         return @ldap_bind($conn, $binddn, $password);
        //     } else {
        //         throw new \Exception("Failed to bind LDAP server", 1);
        //     }

        //     // failed to check user password
        //     return false;
        // }
    }