<?php

$query_stock = $con->query("create table `produtos`(codigo_produto int(255) not null primary key auto_increment, nome varchar(40) unique, preco varchar(6), responsavel varchar(100), codigo_barras varchar(15), quantidade_stock int(6), quantidade_entrada varchar(6), dataFab varchar(60), dataExp varchar(60), data_entrada varchar(60), editado tinyint)");

$query_empresa = $con->query("create table `empresa`(codigo_empresa int(255) not null primary key auto_increment, token varchar(100), FarmID varchar(60), nome varchar(40), telefone varchar(20), telefone2 varchar(20), email varchar(60), website varchar(100), endereco varchar(80), fornecedor varchar(4), clientes varchar(4), funcionarios varchar(4), data_registo varchar(100), editado tinyint, sincronizado tinyint)");

$query_login = $con->query("create table `login`(codigo_login int(255) not null primary key auto_increment, nome varchar(80) unique, senha varchar(200), nivel_acesso varchar(80), data_registo varchar(100), codigo_funcionario int(255) default '0', editado tinyint)");

$query_funcionarios = $con->query("create table `funcionarios`(codigo_funcionario int not null primary key auto_increment, nome varchar(60), telefone varchar(20), morada varchar(100), bi varchar(40) unique, data_nascimento varchar(60), data_registo varchar(60), responsavel varchar(100), editado tinyint)");

$query_vendas = $con->query("create table `vendas`(codigo_venda int not null primary key auto_increment, codigo_produto int, quantidade int, total int, codigo_factura varchar(6), responsavel varchar(40), data_venda varchar(60), editado tinyint)");

$query_backups = $con->query("create table `backups`(codigo_backup int not null primary key auto_increment, tipo varchar(60), data_backup varchar(40), funcionario varchar(100), editado tinyint)");

?>