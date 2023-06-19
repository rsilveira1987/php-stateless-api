<?php

    namespace App\Services;

    use App\Models\AbstractModel;
use App\Models\AccountModel;
use App\Utils\Database;    
    use App\Models\TokenModel;
    use App\Utils\UUID;
    use Exception;
use Firebase\JWT\JWT;

    class TokenService
    {
        public static function create(TokenModel $tokenDTO) {
           
            // grab data
            $data = $tokenDTO->toArray();

            $token = new tokenModel;
            $token->uuid = $data['uuid'];
            $token->value = $data['value'];

            $tb = new Database(new TokenModel);
            $token = $tb->create($token);

            return $token;
        }

        public static function delete(TokenModel $token) {

            try {
                \App\Utils\SQLTransaction::open(DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');
                $tb = new Database(new TokenModel);
                $token = $tb->delete($token);
    
                return true;

            } catch (\PDOException $e) {
                \App\Utils\SQLTransaction::rollback();
                throw $e;
            } finally {
                \App\Utils\SQLTransaction::close();
            }
            
            return false;
           
        }

        public static function retrieveBy($field, $value) {

            $token = null;    
            
            try {
                // open sql transaction
                \App\Utils\SQLTransaction::open( DATABASE_PATH . DIRECTORY_SEPARATOR . 'db.ini');

                $tb = new Database(new TokenModel);
                $token = $tb->findBy($field,$value);

            } catch (\PDOException $e) {
                \App\Utils\SQLTransaction::rollback();
            } finally {
                // close sql transaction
                \App\Utils\SQLTransaction::close();
            }

            return $token;
            
        }

        public static function retrieve($jwt) {
            
            return self::retrieveBy('value',$jwt);
        }

        public static function decode($token) {

            $decodedData = null;

            try {
                $decodedData = JWT::decode($token, JWT_SECRET_KEY, array('HS256'));    
            } catch (\Exception $e) {
                $decodedData = null;
            } 
           
            return $decodedData;

        }

    }