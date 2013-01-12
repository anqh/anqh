CREATE TABLE "public"."users" (
  "id" SERIAL,
  "username" VARCHAR(30) NOT NULL,
  "hash" BIGINT DEFAULT 0 NOT NULL,
  "name" VARCHAR(50),
  "email" VARCHAR(150) NOT NULL,
  "homepage" VARCHAR(250),
  "picture" VARCHAR(250),
  "description" TEXT,
  "city_name" VARCHAR(30),
  "login_count" INTEGER DEFAULT 0,
  "post_count" INTEGER DEFAULT 0,
  "adds" INTEGER DEFAULT 0 NOT NULL,
  "mods" INTEGER DEFAULT 0 NOT NULL,
  "signature" VARCHAR(200),
  "avatar" VARCHAR(250),
  "title" VARCHAR(50),
  "dob" DATE,
  "views" INTEGER DEFAULT 0 NOT NULL,
  "heardfrom" VARCHAR(250),
  "hidemail" SMALLINT DEFAULT 0 NOT NULL,
  "ip" VARCHAR(15) DEFAULT ''::character varying NOT NULL,
  "hostname" VARCHAR(150) DEFAULT ''::character varying NOT NULL,
  "new_comment_count" SMALLINT DEFAULT 0,
  "comment_count" INTEGER DEFAULT 0,
  "left_comment_count" INTEGER DEFAULT 0,
  "address_street" VARCHAR(50),
  "address_city" VARCHAR(50),
  "address_zip" VARCHAR(5),
  "latitude" NUMERIC(8,4) DEFAULT 0 NOT NULL,
  "longitude" NUMERIC(8,4) DEFAULT 0 NOT NULL,
  "position" VARCHAR(100) DEFAULT ''::character varying NOT NULL,
  "stylesheet" VARCHAR(250),
  "theme" SMALLINT DEFAULT 5 NOT NULL,
  "name_aim" VARCHAR(50),
  "name_msn" VARCHAR(50),
  "name_yahoo" VARCHAR(50),
  "name_icq" VARCHAR(15),
  "name_skype" VARCHAR(32),
  "smileys" SMALLINT DEFAULT 2 NOT NULL,
  "allow_comments" SMALLINT DEFAULT 0 NOT NULL,
  "online" SMALLINT DEFAULT 0 NOT NULL,
  "window" TEXT,
  "language" SMALLINT DEFAULT 0 NOT NULL,
  "password" VARCHAR(64),
  "last_login" INTEGER,
  "gender" CHAR(1),
  "geo_city_id" INTEGER,
  "default_image_id" INTEGER,
  "old_login" INTEGER,
  "username_clean" VARCHAR(30),
  "created" INTEGER,
  "modified" INTEGER,
  CONSTRAINT "users_pkey" PRIMARY KEY("id"),
  CONSTRAINT "users_username_clean_key" UNIQUE("username_clean"),
  CONSTRAINT "users_default_image_id" FOREIGN KEY ("default_image_id")
    REFERENCES "public"."images"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE INDEX "users_username_clean" ON "public"."users"
  USING btree ("username_clean");

CREATE UNIQUE INDEX "users_username_idx" ON "public"."users"
  USING btree ("username");

CREATE UNIQUE INDEX "users_username_lower" ON "public"."users"
  USING btree ((lower((username)::text)));

CREATE UNIQUE INDEX "users_username_upper_idx" ON "public"."users"
  USING btree ((upper((username)::text)));
