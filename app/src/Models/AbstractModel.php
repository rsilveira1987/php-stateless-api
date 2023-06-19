<?php
    
	namespace App\Models;
	use DateTime;

	/*
     * AbstractModel Class
     */
	abstract class AbstractModel {
		
        public function __get($prop) {
			// normalizar
			$prop = strtolower($prop);

			// verifica se existe um getter no objeto concreto
			if (method_exists($this,'get_'.$prop)) {
				// executa o metodo get_<propriedade>
				return call_user_func([$this,'get_'.$prop]);
			}

			// TODO: deveria jogar exception?
			if(!in_array($prop,$this->getProperties()))
				return null;

            return $this->$prop;
            
		}

        public function __set($prop, $value) {
			// normalize
			$prop = strtolower($prop);

			// verifica se existe um setter no objeto concreto
			if (method_exists($this, 'set_'. $prop)) {
				// executa o metodo set_<propriedade>
				call_user_func([$this,'set_'.$prop],$value);
			} else {
				$this->$prop = $value;
			}

		}

		public function __isset($prop) {
            return (isset($this->$prop)) ? true : false;
        }

        public static function getEntity() {
            $class = get_called_class();	        // obtem o nome da class
            return constant("{$class}::TABLENAME");	// retorna a constante de classe TABLENAME
        }

        public function getProperties() {
            $reflection = new \ReflectionObject($this);
            $properties = $reflection->getProperties(\ReflectionProperty::IS_PROTECTED); // maneira para acessar as propriedades do modelo
			
			$fields = [];
			foreach($properties as $prop) {
				$fields[] = $prop->name;
			}

			return $fields;
        }

		public function toObject() {
            $array = $this->toArray();
			return (object) $array;
        }

		public function toArray() {
            $fields = $this->getProperties();
			$array = [];
			foreach($fields as $field) {
				$array[$field] = $this->$field;
			}
			return $array;
        }

		// public function fromArray(array $data = []) {
		// 	foreach($data as $field => $value) {
		// 		// executa o metodo magico __set para cada item do array
		// 		call_user_func([$this,'__set'],$field,$value);
		// 	}
		// }

		public static function fromArray($data = []) {
			$class = get_called_class();
			$object = new $class;
			foreach($data as $field => $value) {
				// executa o metodo magico __set para cada item do array
				call_user_func([$object,'__set'],$field,$value);
			}
			return $object;
		}

		public function getCreatedAt(){
            return ($this->created_at == null) ? null : (new DateTime($this->created_at))->format('Y-m-d H:i:s');
        }

		public function getUpdatedAt(){
			return ($this->updated_at == null) ? null : (new DateTime($this->updated_at))->format('Y-m-d H:i:s');
		}

    }