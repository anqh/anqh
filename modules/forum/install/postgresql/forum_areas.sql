CREATE TABLE "public"."forum_areas" (
  "id" SERIAL,
  "name" VARCHAR(150) NOT NULL,
  "description" VARCHAR(250),
  "type" SMALLINT DEFAULT 1 NOT NULL,
  "sort" SMALLINT DEFAULT 0,
  "post_count" INTEGER DEFAULT 0,
  "topic_count" INTEGER DEFAULT 0,
  "forum_group_id" INTEGER,
  "last_topic_id" INTEGER,
  "access" INTEGER DEFAULT 0 NOT NULL,
  "author_id" INTEGER,
  "bind" VARCHAR(32),
  "created" INTEGER,
  "access_read" SMALLINT DEFAULT 0,
  "access_write" SMALLINT DEFAULT 0,
  "status" SMALLINT DEFAULT 0,
  "area_type" SMALLINT DEFAULT 0,
  CONSTRAINT "forum_areas_pkey" PRIMARY KEY("id"),
  CONSTRAINT "forum_areas_group_id_fkey" FOREIGN KEY ("forum_group_id")
    REFERENCES "public"."forum_groups"("id")
    ON DELETE SET NULL
    ON UPDATE CASCADE
    NOT DEFERRABLE
) WITHOUT OIDS;

CREATE INDEX "forum_areas_group_id" ON "public"."forum_areas"
  USING btree ("forum_group_id");

CREATE INDEX "forum_areas_last_topic" ON "public"."forum_areas"
  USING btree ("last_topic_id");

CREATE INDEX "forum_areas_sort" ON "public"."forum_areas"
  USING btree ("sort");
