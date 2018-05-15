create table cron
(
	id int(100) auto_increment
		primary key,
	time datetime not null
)
;

create table current_count
(
	id int(100) auto_increment
		primary key,
	count int(100) not null
)
;

create table images
(
	id int auto_increment
		primary key,
	image_href text null,
	product_id varchar(50) null,
	articul varchar(255) null,
	constraint images_product_id_uindex
		unique (product_id)
)
;

create table products
(
	id int(100) auto_increment
		primary key,
	name varchar(255) not null,
	articul varchar(255) not null,
	is_remained int(1) not null,
	price decimal(10,2) not null,
	image_download_href varchar(255) null,
	product_id varchar(50) null,
	is_downloaded int(1) default '0' null,
	constraint products_external_code_uindex
		unique (product_id)
)
;


