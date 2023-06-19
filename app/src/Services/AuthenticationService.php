<?php

    namespace App\Services;

    use App\Utils\Database;
    use App\Models\AccountModel;
use App\Models\TokenModel;
use App\Utils\SQLTransaction;
    use Exception;
use PDOException;

    class AuthenticationService
    {

        public static function login($email, $password) {
            try {
                // open sql transaction
                SQLTransaction::open( DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');

                $tbAccounts = new Database(new AccountModel);
                $account = $tbAccounts->findBy('email',$email);
                                
                if(!$account) {
                    throw new Exception('Usuário ou senha incorreto');
                }

                // verify user password, check rehash
                if (!\App\Auth\Authenticator::verify($password,$account->password)) {
                    throw new Exception('Usuário ou senha incorreto');
                }

                if (\App\Auth\Authenticator::needToRehash($account->password)) {
                    $account->password = \App\Auth\Authenticator::generatePassword($password);
                    $tbAccounts->update($account);
                }

            } catch (Exception $e) {
                SQLTransaction::rollback();
                throw $e;
            } finally {
                SQLTransaction::close();
            }
           
            return $account;
            
        }

        public static function addToken(TokenModel $token) {
            
            try {
                // open sql transaction
                SQLTransaction::open( DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');

                $tb = new Database(new TokenModel);
                $token = $tb->create($token);
                

            } catch (PDOException $e) {
                SQLTransaction::rollback();
            } finally {
                SQLTransaction::close();
            }

        }

        public static function removeToken($value) {
            
            try {
                // open sql transaction
                SQLTransaction::open( DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');

                $tb = new Database(new TokenModel);
                $token = $tb->findBy('value',$value);
                $tb->delete($token);

            } catch (PDOException $e) {
                SQLTransaction::rollback();
            } finally {
                SQLTransaction::close();
            }

        }

        public static function checkJWT($token) {
            
            $isValid = false;

            try {
                // open sql transaction
                SQLTransaction::open( DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');

                $tb = new Database(new TokenModel);
                $token = $tb->findBy('value',$token);

                if($token) {
                    $isValid = true;
                }
                

            } catch (PDOException $e) {
                SQLTransaction::rollback();
            } finally {
                SQLTransaction::close();
            }

            return $isValid;

        }

        

    }