# Importar dados SQL

1. Pegar o identificador do container mysql
```
:$ docker ps
```

2. Copiar o arquivo db/data.sql para dentro do container
```
:$ docker cp db/data.sql <ID_CONTAINER>:/tmp/
```

3. Acessar o shell do container mysql
```
:$ docker exec -it mysql bash
```

4. Acessar o MYSQL
```
:# mysql -uroot -p<senha>
```

5. Importar os dados para a base
```
mysql> USE maosdadas;
mysql> SOURCE /tmp/dump_prod_db_maosdadas1.sql
```