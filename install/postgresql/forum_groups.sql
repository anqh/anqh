CREATE TABLE "public"."forum_groups" (
  "id" SERIAL,
  "name" VARCHAR(32) NOT NULL,
  "sort" SMALLINT DEFAULT 0 NOT NULL,
  "author_id" INTEGER,
  "description" VARCHAR(250),
  "created" INTEGER,
  "status" INTEGER DEFAULT 0,
  CONSTRAINT "forum_groups_pkey" PRIMARY KEY("id")
) WITHOUT OIDS;
