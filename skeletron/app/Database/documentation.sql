CREATE TABLE comments (id integer NOT NULL PRIMARY KEY AUTOINCREMENT,id_item integer NOT NULL,name varchar NOT NULL DEFAULT Guest,email varchar NOT NULL,comment text,id_comment_answer integer,visibly integer DEFAULT 1);
CREATE TABLE items (id integer NOT NULL PRIMARY KEY AUTOINCREMENT,title varchar,text text,time varchar,visibly integer DEFAULT 1);
