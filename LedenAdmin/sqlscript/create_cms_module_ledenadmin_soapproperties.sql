create table cms_module_ledenadmin_soapproperties (
   id   varchar(32) NOT NULL,
   url  varchar(80) NOT NULL,
   username varchar(80) NOT NULL,
   security_code1 varchar(80) NOT NULL,
   security_code2 varchar(80) NOT NULL,
   PRIMARY KEY (id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
