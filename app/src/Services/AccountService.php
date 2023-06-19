<?php

    namespace App\Services;

    use App\Models\AbstractModel;
    use App\Utils\Database;
    use App\Models\AccountModel;
use App\Models\TokenModel;
use App\Utils\SQLTransaction;
    use App\Utils\UUID;
    use Exception;

    class AccountService
    {
        public static function create(AccountModel $accountDTO) {
           
            // grab account data
            $params = $accountDTO->toArray();

            $account = new AccountModel;
            $account->uuid = UUID::v4();
            $account->first_name = trim($params['first_name'] ?? '');
            $account->second_name = trim($params['second_name'] ?? '');
            $account->email = trim($params['email'] ?? '');
            $account->url_image = trim($params['url_image'] ?? '');
            $account->password = trim($params['password'] ?? '');

            //
            // Validate
            //
            if(empty($account->first_name))
                throw new Exception('Falha: Nome inválido.');

            if(empty($account->second_name))
                throw new Exception('Falha: Sobrenome inválido.');
            
            if(!filter_var($account->email, FILTER_VALIDATE_EMAIL))
                throw new Exception('Falha: E-mail inválido.');

            if(empty($account->password))
                throw new Exception('Falha: Senha inválida.');
            
            if(strlen($account->password) < 5 )
                throw new Exception('Falha: A senha precisa conter pelo menos 5 caracteres.');
            
            // account password hash
            $account->setPassword($account->password);

            $tbAccounts = new Database(new AccountModel);
            $account = $tbAccounts->create($account);

            return $account;
        }

        public static function retrieve($uuid) {
            return self::retrieveBy('uuid',$uuid);
        }

        public static function retrieveAll($orderBy = 'id') {
            
            // All accounts
            $accounts = [];
            
            try {
                \App\Utils\SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
                $sql = "SELECT * FROM tb_accounts ORDER BY {$orderBy} ASC";
                $accounts = Database::query($sql);

                $createAccount = function($obj) {
                    return AccountModel::fromArray((array)$obj);
                };

                $accounts = array_map($createAccount,$accounts);
                         
            } catch (\Exception $e) {
                // executa o rollback das alteracoes
                \App\Utils\SQLTransaction::rollback();
                throw $e;
            } finally {
                \App\Utils\SQLTransaction::close();
            }

            return $accounts;
            
        }

        static public function update(AccountModel $account) {
            
            //
            // Validate
            //
            if(empty($account->first_name))
                throw new Exception('Falha: Nome inválido.');

            if(empty($account->second_name))
                throw new Exception('Falha: Sobrenome inválido.');
            
            if(!filter_var($account->email, FILTER_VALIDATE_EMAIL))
                throw new Exception('Falha: E-mail inválido.');

            if(!empty($account->url_image) && !filter_var($account->url_image, FILTER_VALIDATE_URL))
                throw new Exception('Falha: URL inválida.');
            
            try {
                \App\Utils\SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
                $tbAccounts = new Database(new AccountModel);
                $account = $tbAccounts->update($account);
            } catch (\PDOException $e) {
                // executa o rollback das alteracoes
                \App\Utils\SQLTransaction::rollback();
            } finally {
                \App\Utils\SQLTransaction::close();
            }
            
            return $account;
        }

        public static function delete(AccountModel $account) {

            try {
                \App\Utils\SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
                $tbAccounts = new Database(new AccountModel);
                $account = $tbAccounts->delete($account);
    
                return true;

            } catch (Exception $e) {
                \App\Utils\SQLTransaction::rollback();
                throw $e;
            } finally {
                \App\Utils\SQLTransaction::close();
            }
            
            return false;
           
        }

        public static function updatePassword(AccountModel $accountDTO) {
            
            //
            // Validate
            //
            if(empty($accountDTO->password))
                throw new Exception('Falha: Senha inválida.');
            
            if(strlen($accountDTO->password) < 5 )
                throw new Exception('Falha: A senha precisa conter pelo menos 5 caracteres.');
            
            // account password hash
            $accountDTO->setPassword($accountDTO->password);

            $tbAccounts = new Database(new AccountModel);
            $account = $tbAccounts->update($accountDTO);

            return $account;
            
        }

        public static function retrieveBy($criteria, $value) {

            $account = null;

            try {
                \App\Utils\SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
                $tbAccounts = new Database(new AccountModel);
                $account = $tbAccounts->findBy($criteria,$value);

            } catch (\PDOException $e) {
                \App\Utils\SQLTransaction::rollback();
                $account = null;
            } finally {
                \App\Utils\SQLTransaction::close();
            }
                   
            return $account;
            
        }

        public static function search($query = '' , $params = []) {
            
            try {
                // abre uma transacao com o banco de dados                    
                SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');

                $model = new AccountModel;
                $entity = $model->getEntity();
                
                // query params
                $order_by = $params['ORDER_BY'] ?? 'first_name,second_name';
                $sort_direction = $params['SORT_DIRECTION'] ?? 'ASC';
                
                $sql = "SELECT * FROM {$entity} WHERE CONCAT( first_name, ' ', second_name ) LIKE :query OR email LIKE :query ORDER BY {$order_by} {$sort_direction}";
                $values = [
                    ':query' => "%$query%"
                ];

                $accounts = [];
                $response = Database::query($sql,$values);
                foreach($response as $r) {
                    $accounts[] = AccountModel::fromArray((array)$r);
                }
                
                // $stmt = $conn->prepare($sql);
				// $stmt->execute($values);
                // $accounts = $stmt->fetchAll(\PDO::FETCH_CLASS, get_class($model));

                return $accounts;
                
            } catch (\Exception $e) {
                // encaminha a excecao para o controlador
                throw $e;
            } finally {
                SQLTransaction::close();
            }
        }

        public static function hasToken(AccountModel $account, $token) {
            
            $hasToken = false;

            try {
                // abre uma transacao com o banco de dados                    
                SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
                $entity = (new TokenModel)->getEntity();
                $sql = "SELECT * FROM {$entity} WHERE uuid = :uuid and value = :value ORDER BY created_at DESC";
                $values = [
                    ':uuid' => $account->uuid,
                    ':value' => $token
                ];

                $response = Database::query($sql,$values);
                $hasToken = count($response) > 0 ? true : false;
                
            } catch (\PDOException $e) {
                SQLTransaction::rollback();
            } finally {
                SQLTransaction::close();
            }

            return $hasToken;
            
        }

        public static function getJWTTokens(AccountModel $account) {
            
            $jwtTokens = [];

            try {
                // abre uma transacao com o banco de dados                    
                SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
                $entity = (new TokenModel)->getEntity();
                $sql = "SELECT * FROM {$entity} WHERE uuid = :uuid ORDER BY created_at DESC";
                $values = [
                    ':uuid' => $account->uuid
                ];

                $response = Database::query($sql,$values);
                foreach($response as $data) {
                    $jwtTokens[] = TokenModel::fromArray((array)$data);
                }
                
                // $stmt = $conn->prepare($sql);
				// $stmt->execute($values);
                // $accounts = $stmt->fetchAll(\PDO::FETCH_CLASS, get_class($model));
                
            } catch (\Exception $e) {
                // encaminha a excecao para o controlador
                throw $e;
            } finally {
                SQLTransaction::close();
            }

            return $jwtTokens;
            
        }

        // public function create($email, $password)
        // {
        //     // try {
        //     //     // open sql transaction
        //     //     SQLTransaction::open( DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');

        //     //     $tbAccounts = new Database(new AccountModel);
        //     //     $account = $tbAccounts->findBy('email',$email);
                                
        //     //     if(!$account  || !$account->isActive()) {
        //     //         throw new Exception('Usuário ou senha incorreto');
        //     //     }

        //     //     // verify user password, check rehash
        //     //     if (!\App\Auth\Authenticator::verify($password,$account->password)) {
        //     //         throw new Exception('Usuário ou senha incorreto');
        //     //     }

        //     //     if (\App\Auth\Authenticator::needToRehash($account->password)) {
        //     //         $account->password = \App\Auth\Authenticator::generatePassword($password);
        //     //         $tbAccounts->update($account);
        //     //     }

        //     // } catch (Exception $e) {
        //     //     SQLTransaction::rollback();
        //     //     throw new Exception($e->getMessage());
        //     // } finally {
        //     //     SQLTransaction::close();
        //     // }
           
        //     // return $account;
            
        // }

    }