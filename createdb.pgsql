CREATE table people (
	id serial,
	name varchar(30) not null,
	boat varchar(30) not null,
  type char(5) default 'human',
  email varchar(60),
	logintime timestamp,
	createdtime timestamp,
	lastoptime timestamp,
	balance int,
	primary key (id)
	);
CREATE table log (
	id serial,
	name varchar(30) not null,
	investment int,
	harvest int,
	balance int,
	time timestamp,
	primary key (id)
	);
CREATE table sea (
	id serial,
	time timestamp,
	stock int,
  price float,
  maintenance float,
	primary key (id)
	);
CREATE table msgs (
  id serial,
  sender varchar(30),
  recipient varchar(30),
  timesent timestamp,
  timeread timestamp,
  msg text,
  primary key (id)
  );
CREATE table phantoms (
  id serial,
  name varchar(30) not null,
  code varchar(500),
  primary key (id)
  );
GRANT all on people to public;
GRANT all on log to public;
GRANT all on sea to public;
GRANT all on msgs to public;
GRANT all on phantoms to public;
GRANT all on people_id_seq to public;
GRANT all on log_id_seq to public;
GRANT all on sea_id_seq to public;
GRANT all on msgs_id_seq to public;
GRANT all on phantoms_id_seq to public;
