<?php

    namespace App\Models;

    use App\Auth\Authenticator;
use App\Services\AccountService;

    class AccountModel extends AbstractModel
    {
        const TABLENAME = 'tb_accounts';

        // Declarar os campos do banco de dados
        protected $id = null;
        protected $uuid = null;
        protected $first_name = null;
        protected $second_name = null;
        protected $email = null;
        protected $password = null;
        protected $url_image = null;
        protected $created_at = null;
        protected $updated_at = null;

        public function getID() {
            return intval($this->id);
        }

        public function getUUID() {
            return $this->uuid;
        }

        public function getFirstName() {
            return $this->first_name;
        }

        public function getSecondName() {
            return $this->second_name;
        }

        public function getName() {
            return "{$this->first_name} {$this->second_name}";
        }

        public function getEmail() {
            return $this->email;
        }

        public function getPassword() {
            return $this->password;
        }

        public function setPassword($password) {
            $this->password = Authenticator::generatePassword($password);
        }

        public function getUrlImage() {
            return $this->url_image;
        }

        public function getJWTTokens() {
            $tokens = AccountService::getJWTTokens($this);
            return $tokens;
        }

        public function hasToken($token) {
            $hasToken = AccountService::hasToken($this,$token);
            return $hasToken;
        }

        public function toJson() {
            return [
                'id' => $this->getID(),
                'uuid' => $this->getUUID(),
                'first_name' => $this->getFirstName(),
                'second_name' => $this->getSecondName(),
                'email' => $this->getEmail(),
                'url_image' => $this->getUrlImage(),
                'created_at' => $this->getCreatedAt(),
                'updated_at' => $this->getUpdatedAt(),
            ];
        }

        // public static function fromJson($jsonArray) {
        //     $account = new self;
        //     $account->id = $jsonArray['id'] ?? null;
        //     $account->first_name = $jsonArray['first_name'] ?? null;
        //     $account->second_name = $jsonArray['second_name'] ?? null;
        //     $account->email = $jsonArray['email'] ?? null;
        //     $account->status = $jsonArray['status'] ?? 1;
        //     $account->is_teacher = $jsonArray['is_teacher'] ?? 0;
        //     $account->password = $jsonArray['password'] ?? null;
        //     $account->created_at = $jsonArray['created_at'] ?? null;
        //     $account->updated_at = $jsonArray['updated_at'] ?? null;

        //     return $account;
        // }
    }