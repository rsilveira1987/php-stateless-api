<?php

    namespace App\Utils;

    use App\Models\AbstractModel;
	use Exception;
    use PDO;
    // use App\Utils\SQLTransaction;
	// use DateTime;

    class Database
    {
        private $model;

        public function __construct(AbstractModel $model) {
            $this->model = $model;
        }

		// public function where($query, $values = [], $params = [])
		// {
		// 	// get model entity
		// 	$entity = $this->model->getEntity();

		// 	// where clause
		// 	$whereClase = $query;

		// 	// get model entity
        //     $sql = "SELECT * FROM {$entity} WHERE {$whereClase}";
		// 	foreach($params as $param) {
		// 		$sql .= " {$param}";
		// 	}

		// 	if ($conn = SQLTransaction::getInstance())
		// 	{
		// 		$result_set = [];
        //         $stmt = $conn->prepare($sql);
        //         $ret = $stmt->execute($values);
        //         if ($ret) {
        //             $result_set = $stmt->fetchAll(PDO::FETCH_CLASS, get_class($this->model));
        //         }
        //         return $result_set;
		// 	} else {
		// 		throw new Exception('Nao existe transacao ativa');
		// 	}

		// }

		// public function select($query, $values = [], $params = [])
		// {
		// 	// get model entity
		// 	$entity = $this->model->getEntity();

		// 	// where clause
		// 	$whereClase = $query;

		// 	// get model entity
        //     $sql = "SELECT * FROM {$entity} {$whereClase}";
		// 	foreach($params as $param) {
		// 		$sql .= " {$param}";
		// 	}

		// 	if ($conn = SQLTransaction::getInstance())
		// 	{
		// 		$result_set = [];
        //         $stmt = $conn->prepare($sql);
        //         $ret = $stmt->execute($values);
        //         if ($ret) {
        //             $result_set = $stmt->fetchAll(PDO::FETCH_CLASS, get_class($this->model));
        //         }
        //         return $result_set;
		// 	} else {
		// 		throw new Exception('Nao existe transacao ativa');
		// 	}

		// }

        // public function all( $orderBy = 'created_at ASC')
        // {
        //     // Obtem a classe que esta criando
        //     $entity = $this->model->getEntity();

        //     // get model entity
        //     $sql = "SELECT * FROM {$entity} ORDER BY {$orderBy}";

        //     // obtem a transacao ativa
		// 	if ($conn = SQLTransaction::getInstance())
		// 	{
        //         $result_set = [];
        //         $stmt = $conn->prepare($sql);
        //         $ret = $stmt->execute();
        //         if ($ret) {
        //             $result_set = $stmt->fetchAll(PDO::FETCH_CLASS, get_class($this->model));
        //         }
        //         return $result_set;				
		// 	} else {
		// 		throw new Exception('Nao existe transacao ativa');
		// 	}
        // }

		/*
        public function create(array $data)
		{
			            
            // get model entity
			$entity = $this->model->getEntity();
			
			// id is autoincrement
			unset($data['id']);

			// add created_at
			$now = new DateTime('now');
			$data['created_at'] = $now->format('Y-m-d H:i:s');

			// cria uma instrucao SQL para INSERT
			$colString = implode(', ', array_keys($data));			
			$placeholders = [];
			$values = [];
			foreach ($data as $key => $value) {
				$placeholders[] = ":{$key}";
				$values[":{$key}"] = $value;
			}
			$placeholderString = implode(', ', $placeholders);

			// build sql
			$sql = "INSERT INTO {$entity} ( {$colString} ) VALUES ( {$placeholderString} )";

			// obtem a transacao ativa
			if ($conn = SQLTransaction::getInstance()) {
			
				$stmt = $conn->prepare($sql);
				$ret = $stmt->execute($values);
				
				// retorna o objeto
                $id = $conn->lastInsertId();
                
				return $this->find($id);

			} else {
				throw new Exception('Nao existe transacao ativa');
			}
					
        }
		*/

		// public static function generateCreateSQL(AbstractModel $model)
		// {
		// 	// get model entity
		// 	$entity = $model->getEntity();

		// 	// get model data
		// 	$data = $model->toArray();
		// 	unset($data['id']);

		// 	// add created_at
		// 	$now = new DateTime('now');
		// 	$data['created_at'] = $now->format('Y-m-d H:i:s');

		// 	// cria uma instrucao SQL para INSERT
		// 	$colString = implode(', ', array_keys($data));			
		// 	$placeholders = [];
		// 	$values = [];
		// 	foreach ($data as $key => $value) {
		// 		$placeholders[] = ":{$key}";
		// 		$values[":{$key}"] = $value;
		// 	}

		// 	$tmp = array_map(function($v){
		// 		return is_numeric($v) ? $v : "'$v'";
		// 	},$values);

		// 	$valString = implode(', ', $tmp);

		// 	// build sql
		// 	$sql = "INSERT INTO {$entity} ( {$colString} ) VALUES ( {$valString} )";

		// 	return $sql;

		// }

		public function create(AbstractModel $model) {
			            
            // get model entity
			$entity = $model->getEntity();

			$data = $model->toArray();
			
			// id is autoincrement
			unset($data['id']);

			// add created_at
			$now = new \DateTime('now');
			$data['created_at'] = $now->format('Y-m-d H:i:s');

			// cria uma instrucao SQL para INSERT
			$colString = implode(', ', array_keys($data));			
			$placeholders = [];
			$values = [];
			foreach ($data as $key => $value) {
				$placeholders[] = ":{$key}";
				$values[":{$key}"] = $value;
			}
			$placeholderString = implode(', ', $placeholders);

			// build sql
			$sql = "INSERT INTO {$entity} ( {$colString} ) VALUES ( {$placeholderString} )";

			// obtem a transacao ativa
			if ($conn = SQLTransaction::getInstance()) {
			
				$stmt = $conn->prepare($sql);
				$ret = $stmt->execute($values);
				
				// retorna o objeto
                $id = $conn->lastInsertId();

				return $this->find($model->uuid);

			} else {
				throw new Exception('Nao existe transacao ativa');
			}
					
        }

        public function update(AbstractModel $model)
		{
			// get model entity
			$entity = $model->getEntity();
			
            $data = $model->toArray();
			unset($data['id']);
			unset($data['uuid']);

            // add updated_at
			$now = new \DateTime('now');
			$data['updated_at'] = $now->format('Y-m-d H:i:s');
			
			// set key/value placeholders
			$placeholders = [];
			$values = [];
			foreach ($data as $key => $value) {
				$placeholders[] = "{$key} = :{$key}";
				$values[":{$key}"] = $value;
			}
			// add id and uuid placeholder
			$values[':id'] = $model->id;
			
			// build sql update string
			$placeholdersList = implode(', ', $placeholders);
			$sql = "UPDATE {$entity} SET {$placeholdersList} WHERE id = :id";

			// get a transaction instance
			if ($conn = SQLTransaction::getInstance()) {
			
				$stmt = $conn->prepare($sql);
				$ret = $stmt->execute($values);

                // Load new data
                // $object = $this->find($model->id);
				//$this->fromArray( $object->toArray() );

				// return object reference
				return $model;

			} else {
				throw new Exception('Não há transação ativa');
			}
			
		}

        public function find($uuid) 
		{
            return $this->findBy('uuid',$uuid);
		}

		public function findBy($criteria, $value) 
		{
            // Obtem a classe que esta criando
            $entity = $this->model->getEntity();

			// monta a instrucao select
			$sql = "SELECT * FROM {$entity} WHERE {$criteria} = ?";
			
			// obtem a transacao ativa
			if ($conn = SQLTransaction::getInstance()) {
				$stmt = $conn->prepare($sql);
				$stmt->execute([$value]);
				
				// retorna os dados em forma de objeto
				$object = $stmt->fetchObject(get_class($this->model));
				if ($object === false) return null;
				return $object;
				
			} else {
				throw new Exception('Não existe transação ativa.');
			}
		}

        public function delete(AbstractModel $model) {
			// gets user id
			$id = ($model->id) ? $model->id : null;
			
			// grab model entity
			$entity = $model->getEntity();

			// create DELETE statement
			$sql = "DELETE FROM {$entity} WHERE id = :id";
			$values = [
				':id' => $id
			];
			
			// get transaction instance
			if ($conn = SQLTransaction::getInstance())
			{				
				$stmt = $conn->prepare($sql);
				$ret = $stmt->execute($values);
				return $ret;
				
			} else {
				throw new Exception('Não existe transação ativa.');
			}
		}

		// public static function exec($sql,$values = [])
		// {
		// 	// obtem a transacao ativa
		// 	if ($conn = SQLTransaction::getInstance()) {
		// 		$stmt = $conn->prepare($sql);
		// 		return $stmt->execute($values);
		// 	} else {
		// 		throw new Exception('Nao existe transacao ativa');
		// 	}
		// }

		public static function query($sql, $values = []) {
			// obtem a transacao ativa
			if ($conn = SQLTransaction::getInstance()) {
				$stmt = $conn->prepare($sql);
				$stmt->execute($values);
				// retorna os dados em forma de objeto
				return $stmt->fetchAll(PDO::FETCH_OBJ);
				
			} else {
				throw new Exception('Não existe transação ativa');
			}
		}


		// public static function queryToObject($sql, $values = [], $objectClass)
		// {
		// 	// obtem a transacao ativa
		// 	if ($conn = SQLTransaction::getInstance()) {
		// 		$stmt = $conn->prepare($sql);
		// 		$stmt->execute($values);
		// 		// retorna os dados em forma de objeto
		// 		$result_set = $stmt->fetchAll(PDO::FETCH_CLASS, get_class($objectClass));
		// 		return $result_set;
				
		// 	} else {
		// 		throw new Exception('Nao existe transacao ativa');
		// 	}
		// }


    }