<?php

    namespace App\Models;

use Firebase\JWT\JWT;

    class TokenModel extends AbstractModel
    {
        const TABLENAME = 'tb_tokens';

        // Declarar os campos do banco de dados
        protected $id = null;
        protected $uuid = null;
        protected $value = null;
        protected $user_agent = null;
        protected $created_at = null;
        protected $updated_at = null;

        public function getID() {
            return intval($this->id);
        }

        public function getUUID() {
            return $this->id;
        }

        public function getValue() {
            return $this->value;
        }

        public function toJson() {
            return [
                'id' => $this->getID(),
                'uuid' => $this->getUUID(),
                'value' => $this->getValue(),
                'created_at' => $this->getCreatedAt(),
                'updated_at' => $this->getUpdatedAt(),
            ];
        }

        public static function fromJson($jsonArray) {
            $object = new self;
            $object->id = $jsonArray['id'] ?? null;
            $object->uuid = $jsonArray['uuid'] ?? null;
            $object->value = $jsonArray['value'] ?? null;
            $object->created_at = $jsonArray['created_at'] ?? null;
            $object->updated_at = $jsonArray['updated_at'] ?? null;

            return $object;
        }

        public static function generate(AccountModel $account) {
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

            return $jwt;
        }
    }